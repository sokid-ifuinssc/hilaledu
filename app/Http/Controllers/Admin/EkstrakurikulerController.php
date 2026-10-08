<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekstrakurikuler;
use App\Models\AnggotaEkstrakurikuler;
use App\Models\RencanaKegiatanEskul;
use App\Models\LaporanKegiatanEskul;
use App\Models\PresensiEskul;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Models\PengaturanSekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EkstrakurikulerController extends Controller
{
    /**
     * Tampilkan daftar seluruh ekstrakurikuler sekolah
     */
    public function index(Request $request)
    {
        $query = Ekstrakurikuler::with(['pembina', 'ketua'])
            ->withCount([
                'anggotas as total_anggota' => fn($q) => $q->where('status', 'aktif'),
                'rencanaKegiatans as total_rencana',
                'laporanKegiatans as total_laporan',
            ]);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%")
                  ->orWhere('kode', 'like', "%{$q}%")
                  ->orWhere('tempat', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_aktif', $request->status === 'aktif');
        }

        $eskuls = $query->orderBy('nama')->get();

        $gurus = User::whereIn('role', ['guru', 'admin', 'superadmin'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $activeTa = PengaturanSekolah::getActiveTahunAjaran();
        $activeSem = PengaturanSekolah::getActiveSemester();

        // Ringkasan statistik
        $totalEskulAktif = Ekstrakurikuler::where('is_aktif', true)->count();
        $totalSiswaEskul = AnggotaEkstrakurikuler::where('status', 'aktif')->distinct('siswa_id')->count('siswa_id');
        $totalKegiatanMingguIni = LaporanKegiatanEskul::whereBetween('tanggal_kegiatan', [now()->startOfWeek(), now()->endOfWeek()])->count();

        return view('admin.ekstrakurikuler.index', compact(
            'eskuls',
            'gurus',
            'activeTa',
            'activeSem',
            'totalEskulAktif',
            'totalSiswaEskul',
            'totalKegiatanMingguIni'
        ));
    }

    /**
     * Simpan data ekstrakurikuler baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'            => 'required|string|max:100|unique:ekstrakurikulers,nama',
            'kode'            => 'nullable|string|max:30|unique:ekstrakurikulers,kode',
            'deskripsi'       => 'nullable|string',
            'hari'            => 'required|string|max:50',
            'jam_mulai'       => 'nullable',
            'jam_selesai'     => 'nullable',
            'tempat'          => 'nullable|string|max:150',
            'pembina_guru_id' => 'nullable|exists:users,id',
            'is_aktif'        => 'nullable|boolean',
        ], [
            'nama.required' => 'Nama ekstrakurikuler wajib diisi.',
            'nama.unique'   => 'Nama ekstrakurikuler sudah terdaftar.',
            'kode.unique'   => 'Kode ekstrakurikuler sudah digunakan.',
        ]);

        $validated['is_aktif'] = $request->has('is_aktif') ? (bool)$request->is_aktif : true;
        if (empty($validated['kode'])) {
            $slug = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $validated['nama']), 0, 4));
            $validated['kode'] = 'ESK-' . $slug;
        }

        $eskul = Ekstrakurikuler::create($validated);

        return redirect()->route('admin.ekstrakurikuler.index')
            ->with('success', "Ekstrakurikuler {$eskul->nama} berhasil ditambahkan!");
    }

    /**
     * Tampilkan detail eskul (Anggota, Rencana, Laporan, Presensi, Penilaian)
     */
    public function show(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $ekstrakurikuler->load(['pembina', 'ketua']);

        $activeTa = $request->input('tahun_ajaran', PengaturanSekolah::getActiveTahunAjaran());
        $activeSem = $request->input('semester', PengaturanSekolah::getActiveSemester());

        $tab = $request->input('tab', 'anggota');

        // 1. Data Anggota
        $anggotas = AnggotaEkstrakurikuler::with(['siswa.kelas'])
            ->where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('tahun_ajaran', $activeTa)
            ->where('semester', $activeSem)
            ->orderByRaw("FIELD(jabatan, 'Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Anggota')")
            ->get();

        // 2. Data Rencana Kegiatan
        $rencanas = RencanaKegiatanEskul::with(['laporan', 'creator'])
            ->where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->orderBy('pertemuan_ke')
            ->get();

        // 3. Data Laporan Realisasi
        $laporans = LaporanKegiatanEskul::with(['rencanaKegiatan', 'creator', 'presensis'])
            ->where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->orderByDesc('tanggal_kegiatan')
            ->get();

        // 4. Data Presensi
        $tanggalPresensiList = PresensiEskul::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->select('tanggal')
            ->distinct()
            ->orderByDesc('tanggal')
            ->pluck('tanggal');

        $selectedTanggal = $request->input('tanggal', $tanggalPresensiList->first() ?? date('Y-m-d'));

        $presensis = PresensiEskul::with('siswa.kelas')
            ->where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('tanggal', $selectedTanggal)
            ->get()
            ->keyBy('siswa_id');

        // Master Guru & Siswa untuk modal input
        $gurus = User::whereIn('role', ['guru', 'admin', 'superadmin'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $kelases = Kelas::where('is_aktif', true)->orderBy('nama')->get();

        // Daftar siswa yang belum terdaftar di eskul ini
        $existingSiswaIds = $anggotas->pluck('siswa_id')->toArray();
        $availableSiswas = User::where('role', 'siswa')
            ->where('is_active', true)
            ->whereNotIn('id', $existingSiswaIds)
            ->with('kelas')
            ->orderBy('name')
            ->get();

        return view('admin.ekstrakurikuler.show', compact(
            'ekstrakurikuler',
            'anggotas',
            'rencanas',
            'laporans',
            'tanggalPresensiList',
            'selectedTanggal',
            'presensis',
            'gurus',
            'kelases',
            'availableSiswas',
            'activeTa',
            'activeSem',
            'tab'
        ));
    }

    /**
     * Update data ekstrakurikuler
     */
    public function update(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $validated = $request->validate([
            'nama'            => 'required|string|max:100|unique:ekstrakurikulers,nama,' . $ekstrakurikuler->id,
            'kode'            => 'nullable|string|max:30|unique:ekstrakurikulers,kode,' . $ekstrakurikuler->id,
            'deskripsi'       => 'nullable|string',
            'hari'            => 'required|string|max:50',
            'jam_mulai'       => 'nullable',
            'jam_selesai'     => 'nullable',
            'tempat'          => 'nullable|string|max:150',
            'pembina_guru_id' => 'nullable|exists:users,id',
            'ketua_siswa_id'  => 'nullable|exists:users,id',
            'is_aktif'        => 'nullable|boolean',
        ]);

        $validated['is_aktif'] = $request->has('is_aktif') ? (bool)$request->is_aktif : false;

        $ekstrakurikuler->update($validated);

        return back()->with('success', "Data ekstrakurikuler {$ekstrakurikuler->nama} berhasil diperbarui.");
    }

    /**
     * Hapus ekstrakurikuler
     */
    public function destroy(Ekstrakurikuler $ekstrakurikuler)
    {
        $nama = $ekstrakurikuler->nama;
        $ekstrakurikuler->delete();

        return redirect()->route('admin.ekstrakurikuler.index')
            ->with('success', "Ekstrakurikuler {$nama} berhasil dihapus.");
    }

    /**
     * Tambahkan siswa ke dalam eskul (bisa single maupun multi-select)
     */
    public function storeAnggota(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $request->validate([
            'siswa_ids'   => 'required|array|min:1',
            'siswa_ids.*' => 'exists:users,id',
            'jabatan'     => 'nullable|string|max:50',
        ], [
            'siswa_ids.required' => 'Pilih minimal satu siswa untuk ditambahkan.',
        ]);

        $ta = PengaturanSekolah::getActiveTahunAjaran();
        $sem = PengaturanSekolah::getActiveSemester();
        $jabatan = $request->input('jabatan', 'Anggota');

        $added = 0;
        foreach ($request->siswa_ids as $siswaId) {
            $anggota = AnggotaEkstrakurikuler::firstOrCreate(
                [
                    'ekstrakurikuler_id' => $ekstrakurikuler->id,
                    'siswa_id'           => $siswaId,
                    'tahun_ajaran'       => $ta,
                    'semester'           => $sem,
                ],
                [
                    'jabatan' => $jabatan,
                    'status'  => 'aktif',
                ]
            );
            if ($anggota->wasRecentlyCreated) {
                $added++;
            }
        }

        return back()->with('success', "Berhasil menambahkan {$added} siswa ke dalam {$ekstrakurikuler->nama}.");
    }

    /**
     * Hapus anggota dari eskul
     */
    public function destroyAnggota(Ekstrakurikuler $ekstrakurikuler, AnggotaEkstrakurikuler $anggota)
    {
        if ($ekstrakurikuler->ketua_siswa_id === $anggota->siswa_id) {
            $ekstrakurikuler->update(['ketua_siswa_id' => null]);
        }

        $anggota->delete();

        return back()->with('success', 'Siswa berhasil dikeluarkan dari keanggotaan eskul.');
    }

    /**
     * Tugaskan siswa sebagai Ketua Eskul
     */
    public function setKetua(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $request->validate([
            'siswa_id' => 'required|exists:users,id',
        ]);

        $siswaId = (int)$request->siswa_id;

        // Reset jabatan ketua lama menjadi anggota jika ada
        AnggotaEkstrakurikuler::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('jabatan', 'Ketua')
            ->update(['jabatan' => 'Anggota']);

        // Set jabatan ketua baru
        AnggotaEkstrakurikuler::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('siswa_id', $siswaId)
            ->update(['jabatan' => 'Ketua']);

        $ekstrakurikuler->update(['ketua_siswa_id' => $siswaId]);

        $siswa = User::find($siswaId);

        return back()->with('success', "{$siswa->name} berhasil ditugaskan sebagai Ketua {$ekstrakurikuler->nama}!");
    }

    /**
     * MONITORING MINGGUAN KESISWAAN
     * Waka Kesiswaan dapat melihat kegiatan dan absensi eskul perminggunya
     */
    public function monitoringMingguan(Request $request)
    {
        $mingguOffset = (int)$request->input('minggu_offset', 0);
        $startDate = $request->filled('tanggal_mulai') ? \Carbon\Carbon::parse($request->tanggal_mulai) : now()->startOfWeek()->addWeeks($mingguOffset);
        $endDate   = $request->filled('tanggal_selesai') ? \Carbon\Carbon::parse($request->tanggal_selesai) : (clone $startDate)->endOfWeek();

        $allEskul = Ekstrakurikuler::orderBy('nama')->get();
        $selectedEskulId = $request->input('ekstrakurikuler_id');

        $eskulsQuery = Ekstrakurikuler::where('is_aktif', true)
            ->with(['pembina', 'ketua'])
            ->withCount(['anggotas' => fn($q) => $q->where('status', 'aktif')]);

        if ($selectedEskulId) {
            $eskulsQuery->where('id', $selectedEskulId);
        }

        $eskuls = $eskulsQuery->get();

        // Ambil laporan kegiatan pada rentang minggu ini
        $laporansQuery = LaporanKegiatanEskul::with(['ekstrakurikuler.pembina', 'rencanaKegiatan'])
            ->whereBetween('tanggal_kegiatan', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($selectedEskulId) {
            $laporansQuery->where('ekstrakurikuler_id', $selectedEskulId);
        }

        $laporans = $laporansQuery->orderBy('tanggal_kegiatan')->get();

        // Ambil rencana kegiatan pada rentang minggu ini
        $rencanasQuery = RencanaKegiatanEskul::with('ekstrakurikuler')
            ->whereBetween('tanggal_rencana', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($selectedEskulId) {
            $rencanasQuery->where('ekstrakurikuler_id', $selectedEskulId);
        }

        $rencanas = $rencanasQuery->orderBy('tanggal_rencana')->get();

        // Rekap presensi mingguan
        $presensiStats = PresensiEskul::whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->select(
                'ekstrakurikuler_id',
                DB::raw('COUNT(*) as total_absen'),
                DB::raw("SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as hadir"),
                DB::raw("SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as izin"),
                DB::raw("SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as sakit"),
                DB::raw("SUM(CASE WHEN status = 'Alpa' THEN 1 ELSE 0 END) as alpa")
            )
            ->groupBy('ekstrakurikuler_id')
            ->get()
            ->keyBy('ekstrakurikuler_id');

        return view('admin.ekstrakurikuler.monitoring_mingguan', [
            'eskuls'          => $eskuls,
            'allEskul'        => $allEskul,
            'selectedEskulId' => $selectedEskulId,
            'laporans'        => $laporans,
            'rencanas'        => $rencanas,
            'presensiStats'   => $presensiStats,
            'startDate'       => $startDate->format('Y-m-d'),
            'endDate'         => $endDate->format('Y-m-d'),
            'mingguOffset'    => $mingguOffset,
        ]);
    }

    /**
     * REKAP PER KELAS
     * Waka Kesiswaan & Walikelas melihat keanggotaan siswa dalam satu kelas:
     * Nama Siswa, Eskul yang diikuti, Persentase Absensi, Nilai dari Pembina
     */
    public function rekapPerKelas(Request $request)
    {
        $kelases = Kelas::orderBy('nama_kelas')->get();
        $selectedKelasId = $request->input('kelas_id', $kelases->first()?->id);
        $selectedKelas = $selectedKelasId ? Kelas::with('waliKelasGuru')->find($selectedKelasId) : null;

        $ta = $request->input('tahun_ajaran', PengaturanSekolah::getActiveTahunAjaran());
        $sem = $request->input('semester', PengaturanSekolah::getActiveSemester());

        $rekapData = collect();
        if ($selectedKelas) {
            $siswaList = Siswa::where('kelas_id', $selectedKelas->id)
                ->orderBy('nama_lengkap')
                ->get();

            $allSiswaIds = $siswaList->pluck('id')->merge($siswaList->pluck('user_id'))->filter()->unique()->values();

            $anggotaMap = AnggotaEkstrakurikuler::with('ekstrakurikuler.pembina')
                ->whereIn('siswa_id', $allSiswaIds)
                ->where('tahun_ajaran', $ta)
                ->where('semester', $sem)
                ->get()
                ->groupBy('siswa_id');

            $rekapData = $siswaList->map(function($s) use ($anggotaMap) {
                $sKeys = array_filter([$s->user_id, $s->id]);
                $myEskuls = collect();
                foreach ($sKeys as $key) {
                    if (isset($anggotaMap[$key])) {
                        $myEskuls = $myEskuls->merge($anggotaMap[$key]);
                    }
                }
                $myEskuls = $myEskuls->unique('ekstrakurikuler_id');

                $eskulDetails = [];
                foreach ($myEskuls as $k) {
                    $stats = $k->statistik_kehadiran;
                    $eskulDetails[] = [
                        'eskul_nama'           => $k->ekstrakurikuler->nama ?? '-',
                        'jabatan'              => strtolower($k->jabatan ?: 'anggota'),
                        'persentase_kehadiran' => $stats['persentase'],
                        'hadir'                => $stats['hadir'],
                        'total_pertemuan'      => $stats['total'],
                        'nilai_angka'          => $k->nilai_angka,
                        'nilai_huruf'          => $k->nilai_huruf ?: $k->predikat_nilai,
                        'catatan_nilai'        => $k->catatan_nilai,
                    ];
                }

                return [
                    'siswa'         => $s,
                    'jumlah_eskul'  => count($eskulDetails),
                    'eskul_details' => $eskulDetails,
                ];
            });
        }

        return view('admin.ekstrakurikuler.rekap_kelas', [
            'kelasList'       => $kelases,
            'kelases'         => $kelases,
            'selectedKelas'   => $selectedKelas,
            'selectedKelasId' => $selectedKelasId,
            'rekapData'       => $rekapData,
            'siswas'          => $siswaList ?? collect(),
            'ta'              => $ta,
            'sem'             => $sem,
        ]);
    }

    /**
     * Cetak Rekap Per Kelas
     */
    public function printRekapKelas(Request $request, Kelas $kelas)
    {
        $ta = $request->input('tahun_ajaran', PengaturanSekolah::getActiveTahunAjaran());
        $sem = $request->input('semester', PengaturanSekolah::getActiveSemester());

        $siswaList = Siswa::where('kelas_id', $kelas->id)
            ->orderBy('nama_lengkap')
            ->get();

        $allSiswaIds = $siswaList->pluck('id')->merge($siswaList->pluck('user_id'))->filter()->unique()->values();

        $anggotaMap = AnggotaEkstrakurikuler::with('ekstrakurikuler.pembina')
            ->whereIn('siswa_id', $allSiswaIds)
            ->where('tahun_ajaran', $ta)
            ->where('semester', $sem)
            ->get()
            ->groupBy('siswa_id');

        $rekapData = $siswaList->map(function($s) use ($anggotaMap) {
            $sKeys = array_filter([$s->user_id, $s->id]);
            $myEskuls = collect();
            foreach ($sKeys as $key) {
                if (isset($anggotaMap[$key])) {
                    $myEskuls = $myEskuls->merge($anggotaMap[$key]);
                }
            }
            $myEskuls = $myEskuls->unique('ekstrakurikuler_id');

            $eskulDetails = [];
            foreach ($myEskuls as $k) {
                $stats = $k->statistik_kehadiran;
                $eskulDetails[] = [
                    'eskul_nama'           => $k->ekstrakurikuler->nama ?? '-',
                    'jabatan'              => strtolower($k->jabatan ?: 'anggota'),
                    'persentase_kehadiran' => $stats['persentase'],
                    'hadir'                => $stats['hadir'],
                    'total_pertemuan'      => $stats['total'],
                    'nilai_angka'          => $k->nilai_angka,
                    'nilai_huruf'          => $k->nilai_huruf ?: $k->predikat_nilai,
                    'catatan_nilai'        => $k->catatan_nilai,
                ];
            }

            return [
                'siswa'         => $s,
                'jumlah_eskul'  => count($eskulDetails),
                'eskul_details' => $eskulDetails,
            ];
        });

        $sekolah = PengaturanSekolah::first();

        return view('admin.ekstrakurikuler.print_rekap_kelas', compact(
            'kelas',
            'rekapData',
            'ta',
            'sem',
            'sekolah'
        ));
    }
}
