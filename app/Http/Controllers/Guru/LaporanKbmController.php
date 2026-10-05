<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanKbm;
use App\Models\LaporanKbmPresensi;
use App\Models\JadwalPelajaran;
use App\Models\RencanaPembelajaran;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\User;
use App\Models\MataPelajaran;
use App\Models\PengaturanSekolah;
use App\Models\KalenderAkademikEvent;
use Carbon\Carbon;

class LaporanKbmController extends Controller
{
    /**
     * Halaman Utama Laporan KBM:
     * - Tergenerate otomatis per tanggal dan slot jadwal mengajar
     * - Guru dapat langsung melihat tanggal berapa dan mapel apa yang sudah atau belum dilaporkan
     * - Admin dan Superadmin dapat melihat dan memantau seluruh guru dan mata pelajaran
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isExecutive = $user->isSuperAdmin() || $user->isWakaKurikulum() || $user->isKepalaSekolah();

        $bulan = $request->query('bulan', date('Y-m'));
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $bulan)) {
            $bulan = date('Y-m');
        }

        $guruIdFilter = $request->query('guru_id');
        if (!$isExecutive) {
            $guruIdFilter = $user->id;
        } elseif (empty($guruIdFilter)) {
            // Default untuk admin: 'all' (bisa melihat semua guru)
            $guruIdFilter = 'all';
        }

        $kelas = $request->query('kelas');
        $mapelId = $request->query('mapel_id');
        $statusFilter = $request->query('status', 'all'); // all, sudah_lapor, belum_lapor

        $start = Carbon::createFromFormat('Y-m-d', $bulan . '-01')->startOfDay();
        $end = $start->copy()->endOfMonth()->startOfDay();
        $today = Carbon::today();

        // 1. Ambil daftar event libur dari Kalender Akademik
        $liburMap = [];
        try {
            $events = KalenderAkademikEvent::where(function($q) use ($start, $end) {
                $q->whereBetween('tanggal_mulai', [$start->toDateString(), $end->toDateString()])
                  ->orWhereBetween('tanggal_selesai', [$start->toDateString(), $end->toDateString()]);
            })->where('tipe_kegiatan', 'libur')->get();

            foreach ($events as $ev) {
                $evStart = Carbon::parse($ev->tanggal_mulai);
                $evEnd = Carbon::parse($ev->tanggal_selesai ?: $ev->tanggal_mulai);
                for ($d = $evStart->copy(); $d->lte($evEnd); $d->addDay()) {
                    $liburMap[$d->toDateString()] = $ev->judul_kegiatan ?? $ev->judul ?? 'Libur Kalender Akademik';
                }
            }
        } catch (\Throwable $e) {}

        // 2. Query Jadwal Pelajaran
        $jadwalQuery = JadwalPelajaran::with(['mataPelajaran', 'guru']);
        if ($guruIdFilter && $guruIdFilter !== 'all') {
            $jadwalQuery->where('guru_user_id', $guruIdFilter);
        }
        if ($kelas) {
            $jadwalQuery->where('kelas', $kelas);
        }
        if ($mapelId) {
            $jadwalQuery->where('mata_pelajaran_id', $mapelId);
        }
        $allJadwals = $jadwalQuery->orderBy('jam_ke_mulai')->orderBy('jam_mulai')->get();
        $jadwalByHari = $allJadwals->groupBy(fn($j) => strtolower(trim((string)$j->hari)));

        // 3. Ambil data LaporanKbm yang sudah ada
        $laporanQuery = LaporanKbm::with(['jadwal.mataPelajaran', 'rencana.tujuanPembelajaran', 'guru', 'presensiSiswa'])
            ->whereDate('tanggal_realisasi', '>=', $start->toDateString())
            ->whereDate('tanggal_realisasi', '<=', $end->toDateString());
        if ($guruIdFilter && $guruIdFilter !== 'all') {
            $laporanQuery->where('guru_user_id', $guruIdFilter);
        }
        if ($kelas) {
            $laporanQuery->whereHas('jadwal', fn($q) => $q->where('kelas', $kelas));
        }
        if ($mapelId) {
            $laporanQuery->whereHas('jadwal', fn($q) => $q->where('mata_pelajaran_id', $mapelId));
        }
        $existingLaporans = $laporanQuery->get();

        // Indexing laporan berdasarkan: tanggal . '_' . jadwal_id
        $laporanMap = [];
        foreach ($existingLaporans as $lap) {
            $tgl = Carbon::parse($lap->tanggal_realisasi)->toDateString();
            $laporanMap[$tgl . '_' . $lap->jadwal_pelajaran_id] = $lap;
        }

        // 4. Generate Slot KBM Harian (1 s.d akhir bulan, ascending)
        $namaHariMap = [
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
            7 => 'minggu',
        ];

        $slots = [];
        $stat = [
            'total_sesi'       => 0,
            'wajib_lapor'      => 0,
            'sudah_lapor'      => 0,
            'belum_lapor'      => 0,
            'jadwal_mendatang' => 0,
            'persen_lapor'     => 0,
        ];

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $tglStr = $d->toDateString();
            $dayOfWeek = $d->dayOfWeekIso; // 1 = Senin, 7 = Minggu
            $hariKey = $namaHariMap[$dayOfWeek] ?? '';

            $isLibur = isset($liburMap[$tglStr]);
            $liburKet = $liburMap[$tglStr] ?? null;

            $jadwalHariIni = $jadwalByHari->get($hariKey, collect());
            if ($jadwalHariIni->isEmpty()) {
                continue;
            }

            foreach ($jadwalHariIni as $j) {
                $stat['total_sesi']++;

                $key = $tglStr . '_' . $j->id;
                $lap = $laporanMap[$key] ?? null;

                $statusSlot = 'belum_lapor';
                if ($lap) {
                    $statusSlot = 'sudah_lapor';
                    $stat['sudah_lapor']++;
                    $stat['wajib_lapor']++;
                } elseif ($d->gt($today)) {
                    $statusSlot = 'jadwal_mendatang';
                    $stat['jadwal_mendatang']++;
                } else {
                    $statusSlot = 'belum_lapor';
                    $stat['belum_lapor']++;
                    $stat['wajib_lapor']++;
                }

                // Filter status jika dipilih
                if ($statusFilter && $statusFilter !== 'all') {
                    if ($statusFilter !== $statusSlot) {
                        continue;
                    }
                }

                $slots[] = [
                    'tanggal'         => $tglStr,
                    'carbon'          => $d->copy(),
                    'hari'            => ucfirst($hariKey),
                    'is_libur'        => $isLibur,
                    'libur_ket'       => $liburKet,
                    'jadwal'          => $j,
                    'laporan'         => $lap,
                    'status'          => $statusSlot,
                    'is_past'         => $d->lte($today),
                ];
            }
        }

        if ($stat['wajib_lapor'] > 0) {
            $stat['persen_lapor'] = round(($stat['sudah_lapor'] / $stat['wajib_lapor']) * 100, 1);
        }

        $gurus = $isExecutive ? User::where('role', 'guru')->where('is_active', true)->orderBy('name')->get() : collect();
        $kelasList = Kelas::where('is_aktif', true)->orderBy('nama')->get();
        $mapelList = MataPelajaran::orderBy('nama')->get();
        $selectedGuru = ($guruIdFilter && $guruIdFilter !== 'all') ? User::find($guruIdFilter) : null;

        return view('guru.laporan_kbm.index', compact(
            'slots',
            'stat',
            'bulan',
            'guruIdFilter',
            'kelas',
            'mapelId',
            'statusFilter',
            'gurus',
            'kelasList',
            'mapelList',
            'isExecutive',
            'selectedGuru',
            'user'
        ));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $isExecutive = $user->isSuperAdmin() || $user->isWakaKurikulum() || $user->isKepalaSekolah();

        $jadwalId = $request->query('jadwal_id');
        $rencanaId = $request->query('rencana_id');
        $tanggalDefault = $request->query('tanggal', date('Y-m-d'));

        $jadwalsQuery = JadwalPelajaran::with(['mataPelajaran', 'guru']);
        if (!$isExecutive) {
            $jadwalsQuery->where('guru_user_id', $user->id);
        }
        $jadwals = $jadwalsQuery->orderBy('hari')->orderBy('jam_mulai')->get();

        $selectedJadwal = null;
        $students = collect();
        $rencana = null;
        $rencanaList = collect();

        if ($jadwalId) {
            $selectedJadwal = JadwalPelajaran::with(['mataPelajaran', 'guru'])->find($jadwalId);
            if ($selectedJadwal) {
                $kelasNama = trim($selectedJadwal->kelas);
                $kelasObj = Kelas::where('nama_kelas', $kelasNama)->orWhere('nama', $kelasNama)->first();
                if ($kelasObj) {
                    $students = Siswa::where('kelas_id', $kelasObj->id)
                        ->where('status', 'aktif')
                        ->orderBy('nama_lengkap')
                        ->get();
                }

                $rencanaList = RencanaPembelajaran::with('tujuanPembelajaran')
                    ->where('jadwal_pelajaran_id', $jadwalId)
                    ->orderBy('pertemuan_ke')
                    ->get();

                if ($rencanaId) {
                    $rencana = $rencanaList->firstWhere('id', $rencanaId) ?: RencanaPembelajaran::with('tujuanPembelajaran')->find($rencanaId);
                } else {
                    $rencana = $rencanaList->last();
                }
            }
        } elseif ($rencanaId) {
            $rencana = RencanaPembelajaran::with(['jadwal.mataPelajaran', 'tujuanPembelajaran'])->find($rencanaId);
            if ($rencana && $rencana->jadwal) {
                $selectedJadwal = $rencana->jadwal;
                $jadwalId = $selectedJadwal->id;
                $kelasNama = trim($selectedJadwal->kelas);
                $kelasObj = Kelas::where('nama_kelas', $kelasNama)->orWhere('nama', $kelasNama)->first();
                if ($kelasObj) {
                    $students = Siswa::where('kelas_id', $kelasObj->id)
                        ->where('status', 'aktif')
                        ->orderBy('nama_lengkap')
                        ->get();
                }
                $rencanaList = RencanaPembelajaran::with('tujuanPembelajaran')
                    ->where('jadwal_pelajaran_id', $jadwalId)
                    ->orderBy('pertemuan_ke')
                    ->get();
            }
        }

        return view('guru.laporan_kbm.create', compact(
            'jadwals',
            'selectedJadwal',
            'students',
            'rencana',
            'rencanaList',
            'jadwalId',
            'tanggalDefault',
            'isExecutive'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jadwal_pelajaran_id'   => 'required|exists:jadwal_pelajarans,id',
            'tanggal_realisasi'     => 'required|date',
            'kesesuaian_rencana'    => 'required|in:sesuai,sebagian,tidak_sesuai,materi_pengganti',
            'status_pelaksanaan'    => 'required|in:sesuai_jadwal,ganti_hari,jam_tambahan,lainnya',
            'presensi'              => 'required|array',
            'foto'                  => 'nullable|image|max:3072',
        ]);

        $user = Auth::user();
        $jadwal = JadwalPelajaran::findOrFail($request->jadwal_pelajaran_id);
        
        $guruId = ($user->isSuperAdmin() || $user->isWakaKurikulum() || $user->isKepalaSekolah())
            ? ($jadwal->guru_user_id ?: $user->id)
            : $user->id;

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('kbm_dokumentasi', 'public');
        }

        $laporan = LaporanKbm::create([
            'jadwal_pelajaran_id'   => $request->jadwal_pelajaran_id,
            'rencana_pembelajaran_id'=> $request->rencana_pembelajaran_id ?: null,
            'guru_user_id'          => $guruId,
            'tanggal_realisasi'     => $request->tanggal_realisasi,
            'kesesuaian_rencana'    => $request->kesesuaian_rencana,
            'keterangan_kesesuaian' => $request->keterangan_kesesuaian,
            'status_pelaksanaan'    => $request->status_pelaksanaan,
            'keterangan_pelaksanaan'=> $request->keterangan_pelaksanaan,
            'catatan_kegiatan'      => $request->catatan_kegiatan,
            'foto_dokumentasi'      => $fotoPath,
        ]);

        $hadirCount = 0;
        $tidakHadirCount = 0;

        foreach ($request->presensi as $siswaId => $status) {
            $siswa = Siswa::find($siswaId);
            $ket = $request->keterangan_presensi[$siswaId] ?? null;

            LaporanKbmPresensi::create([
                'laporan_kbm_id' => $laporan->id,
                'siswa_id'       => $siswaId,
                'nama_siswa'     => $siswa ? $siswa->nama_lengkap : 'Siswa',
                'status'         => $status,
                'keterangan'     => $ket,
            ]);

            if (in_array($status, ['hadir', 'terlambat'])) {
                $hadirCount++;
            } else {
                $tidakHadirCount++;
            }
        }

        $laporan->update([
            'jumlah_siswa_hadir'       => $hadirCount,
            'jumlah_siswa_tidak_hadir' => $tidakHadirCount,
            'jumlah_siswa_total'       => $hadirCount + $tidakHadirCount,
        ]);

        return redirect()->route('guru.laporan-kbm.index')
            ->with('success', "Laporan KBM dan Presensi Siswa berhasil disimpan! (Hadir: {$hadirCount}, Tidak Hadir: {$tidakHadirCount})");
    }

    public function show(LaporanKbm $laporanKbm)
    {
        $laporanKbm->load(['jadwal.mataPelajaran', 'rencana', 'guru', 'presensiSiswa.siswa']);
        return view('guru.laporan_kbm.show', compact('laporanKbm'));
    }

    public function print(LaporanKbm $laporanKbm)
    {
        $laporanKbm->load(['jadwal.mataPelajaran', 'rencana', 'guru', 'presensiSiswa.siswa']);
        $setting = PengaturanSekolah::getSetting();
        return view('guru.laporan_kbm.print', compact('laporanKbm', 'setting'));
    }

    /**
     * Cetak Rekapitulasi Jurnal Pelaksanaan KBM & Presensi Siswa
     */
    public function printRekap(Request $request)
    {
        $user = Auth::user();
        $isExecutive = $user->isSuperAdmin() || $user->isWakaKurikulum() || $user->isKepalaSekolah();

        $bulan = $request->query('bulan', date('Y-m'));
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $bulan)) {
            $bulan = date('Y-m');
        }

        $guruIdFilter = $request->query('guru_id');
        if (!$isExecutive) {
            $guruIdFilter = $user->id;
        }

        $kelas = $request->query('kelas');
        $mapelId = $request->query('mapel_id');
        $statusFilter = $request->query('status', 'all');

        $start = Carbon::createFromFormat('Y-m-d', $bulan . '-01')->startOfDay();
        $end = $start->copy()->endOfMonth()->startOfDay();
        $today = Carbon::today();

        // 1. Query Jadwal Pelajaran
        $jadwalQuery = JadwalPelajaran::with(['mataPelajaran', 'guru']);
        if ($guruIdFilter && $guruIdFilter !== 'all') {
            $jadwalQuery->where('guru_user_id', $guruIdFilter);
        }
        if ($kelas) {
            $jadwalQuery->where('kelas', $kelas);
        }
        if ($mapelId) {
            $jadwalQuery->where('mata_pelajaran_id', $mapelId);
        }
        $allJadwals = $jadwalQuery->orderBy('jam_ke_mulai')->orderBy('jam_mulai')->get();
        $jadwalByHari = $allJadwals->groupBy(fn($j) => strtolower(trim((string)$j->hari)));

        // 2. Ambil LaporanKbm
        $laporanQuery = LaporanKbm::with(['jadwal.mataPelajaran', 'rencana.tujuanPembelajaran', 'guru', 'presensiSiswa'])
            ->whereDate('tanggal_realisasi', '>=', $start->toDateString())
            ->whereDate('tanggal_realisasi', '<=', $end->toDateString());
        if ($guruIdFilter && $guruIdFilter !== 'all') {
            $laporanQuery->where('guru_user_id', $guruIdFilter);
        }
        if ($kelas) {
            $laporanQuery->whereHas('jadwal', fn($q) => $q->where('kelas', $kelas));
        }
        if ($mapelId) {
            $laporanQuery->whereHas('jadwal', fn($q) => $q->where('mata_pelajaran_id', $mapelId));
        }
        $existingLaporans = $laporanQuery->get();

        $laporanMap = [];
        foreach ($existingLaporans as $lap) {
            $tgl = Carbon::parse($lap->tanggal_realisasi)->toDateString();
            $laporanMap[$tgl . '_' . $lap->jadwal_pelajaran_id] = $lap;
        }

        // 3. Generate slots KBM
        $namaHariMap = [
            1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 7 => 'minggu',
        ];

        $slots = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $tglStr = $d->toDateString();
            $dayOfWeek = $d->dayOfWeekIso;
            $hariKey = $namaHariMap[$dayOfWeek] ?? '';

            $jadwalHariIni = $jadwalByHari->get($hariKey, collect());
            if ($jadwalHariIni->isEmpty()) continue;

            foreach ($jadwalHariIni as $j) {
                $key = $tglStr . '_' . $j->id;
                $lap = $laporanMap[$key] ?? null;

                $statusSlot = $lap ? 'sudah_lapor' : ($d->gt($today) ? 'jadwal_mendatang' : 'belum_lapor');

                if ($statusFilter && $statusFilter !== 'all') {
                    if ($statusFilter !== $statusSlot) continue;
                }

                $slots[] = [
                    'tanggal'  => $tglStr,
                    'carbon'   => $d->copy(),
                    'hari'     => ucfirst($hariKey),
                    'jadwal'   => $j,
                    'laporan'  => $lap,
                    'status'   => $statusSlot,
                    'is_past'  => $d->lte($today),
                ];
            }
        }

        $setting = PengaturanSekolah::getSetting();
        $selectedGuru = ($guruIdFilter && $guruIdFilter !== 'all') ? User::find($guruIdFilter) : null;
        $selectedMapel = $mapelId ? MataPelajaran::find($mapelId) : null;

        return view('guru.laporan_kbm.print_rekap', compact(
            'slots',
            'existingLaporans',
            'user',
            'setting',
            'kelas',
            'mapelId',
            'selectedMapel',
            'selectedGuru',
            'guruIdFilter',
            'bulan',
            'isExecutive'
        ));
    }

    public function destroy(LaporanKbm $laporanKbm)
    {
        $user = Auth::user();
        if ($laporanKbm->guru_user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403);
        }

        $laporanKbm->presensiSiswa()->delete();
        $laporanKbm->delete();

        return redirect()->route('guru.laporan-kbm.index')->with('success', 'Laporan KBM berhasil dihapus.');
    }
}
