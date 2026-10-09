<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Ekstrakurikuler;
use App\Models\AnggotaEkstrakurikuler;
use App\Models\RencanaKegiatanEskul;
use App\Models\LaporanKegiatanEskul;
use App\Models\PresensiEskul;
use App\Models\PengaturanSekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiswaEkstrakurikulerController extends Controller
{
    /**
     * Dashboard Ekstrakurikuler Siswa
     */
    public function index()
    {
        $user = auth()->user();
        $ta = PengaturanSekolah::getActiveTahunAjaran();
        $sem = PengaturanSekolah::getActiveSemester();

        // Eskul yang diikuti siswa ini
        $keanggotaans = AnggotaEkstrakurikuler::with(['ekstrakurikuler.pembina', 'ekstrakurikuler.ketua'])
            ->where('siswa_id', $user->id)
            ->where('tahun_ajaran', $ta)
            ->where('semester', $sem)
            ->where('status', 'aktif')
            ->get();

        $eskulIds = $keanggotaans->pluck('ekstrakurikuler_id')->toArray();

        // Kegiatan eskul terbaru
        $laporans = LaporanKegiatanEskul::with('ekstrakurikuler')
            ->whereIn('ekstrakurikuler_id', $eskulIds)
            ->orderByDesc('tanggal_kegiatan')
            ->take(10)
            ->get();

        $rencanas = RencanaKegiatanEskul::with('ekstrakurikuler')
            ->whereIn('ekstrakurikuler_id', $eskulIds)
            ->where('tanggal_rencana', '>=', now()->toDateString())
            ->orderBy('tanggal_rencana')
            ->take(5)
            ->get();

        // Riwayat absensi siswa ini di eskul
        $riwayatPresensi = PresensiEskul::with(['ekstrakurikuler', 'laporanKegiatan'])
            ->where('siswa_id', $user->id)
            ->orderByDesc('tanggal')
            ->get();

        // Cek apakah ada sesi eskul hari ini untuk absen mandiri
        $hariIni = now()->format('Y-m-d');
        $sesiHariIni = Ekstrakurikuler::whereIn('id', $eskulIds)
            ->where('is_aktif', true)
            ->get()
            ->map(function ($eskul) use ($hariIni, $user) {
                $sudahAbsen = PresensiEskul::where('ekstrakurikuler_id', $eskul->id)
                    ->where('tanggal', $hariIni)
                    ->where('siswa_id', $user->id)
                    ->first();

                $laporan = LaporanKegiatanEskul::where('ekstrakurikuler_id', $eskul->id)
                    ->where('tanggal_kegiatan', $hariIni)
                    ->first();

                $rencana = RencanaKegiatanEskul::where('ekstrakurikuler_id', $eskul->id)
                    ->where('tanggal_rencana', $hariIni)
                    ->first();

                return [
                    'eskul'       => $eskul,
                    'sudah_absen' => $sudahAbsen,
                    'laporan'     => $laporan,
                    'rencana'     => $rencana,
                ];
            });

        // Eskul yang diketuai oleh siswa ini (jika ada)
        $eskulDiketuai = Ekstrakurikuler::where(function ($q) use ($user) {
                $q->where('ketua_siswa_id', $user->id)
                  ->orWhereHas('anggotas', fn($a) => $a->where('siswa_id', $user->id)->where('jabatan', 'Ketua'));
            })
            ->where('is_aktif', true)
            ->with(['pembina'])
            ->get();

        // Seluruh eskul aktif di sekolah (untuk siswa memilih/mendaftar eskul)
        $semuaEskuls = Ekstrakurikuler::where('is_aktif', true)
            ->with(['pembina'])
            ->withCount(['anggotas as total_anggota' => fn($q) => $q->where('status', 'aktif')])
            ->orderBy('nama')
            ->get();

        return view('siswa.ekstrakurikuler.index', compact(
            'keanggotaans',
            'semuaEskuls',
            'laporans',
            'rencanas',
            'riwayatPresensi',
            'sesiHariIni',
            'eskulDiketuai',
            'hariIni',
            'ta',
            'sem'
        ));
    }

    /**
     * Siswa memilih / mendaftar ke ekstrakurikuler
     */
    public function join(Ekstrakurikuler $ekstrakurikuler)
    {
        $user = auth()->user();
        $ta = PengaturanSekolah::getActiveTahunAjaran();
        $sem = PengaturanSekolah::getActiveSemester();

        $anggota = AnggotaEkstrakurikuler::firstOrCreate(
            [
                'ekstrakurikuler_id' => $ekstrakurikuler->id,
                'siswa_id'           => $user->id,
                'tahun_ajaran'       => $ta,
                'semester'           => $sem,
            ],
            [
                'jabatan' => 'Anggota',
                'status'  => 'aktif',
            ]
        );

        if (!$anggota->wasRecentlyCreated && $anggota->status !== 'aktif') {
            $anggota->update(['status' => 'aktif']);
        }

        return back()->with('success', "Selamat! Anda berhasil bergabung ke dalam ekstrakurikuler {$ekstrakurikuler->nama}.");
    }

    /**
     * Siswa keluar / membatalkan keikutsertaan eskul
     */
    public function leave(Ekstrakurikuler $ekstrakurikuler)
    {
        $user = auth()->user();
        $ta = PengaturanSekolah::getActiveTahunAjaran();
        $sem = PengaturanSekolah::getActiveSemester();

        AnggotaEkstrakurikuler::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('siswa_id', $user->id)
            ->where('tahun_ajaran', $ta)
            ->where('semester', $sem)
            ->delete();

        if ($ekstrakurikuler->ketua_siswa_id === $user->id) {
            $ekstrakurikuler->update(['ketua_siswa_id' => null]);
        }

        return back()->with('success', "Anda telah membatalkan keikutsertaan pada ekstrakurikuler {$ekstrakurikuler->nama}.");
    }

    /**
     * Siswa absen masing-masing (mandiri) pada sesi hari ini
     */
    public function presensiMandiri(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $user = auth()->user();
        $tanggal = now()->format('Y-m-d');

        // Pastikan siswa terdaftar sebagai anggota aktif
        $anggota = AnggotaEkstrakurikuler::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('siswa_id', $user->id)
            ->where('status', 'aktif')
            ->first();

        if (!$anggota) {
            return back()->with('error', 'Anda tidak terdaftar sebagai anggota aktif ekstrakurikuler ini.');
        }

        $request->validate([
            'status'     => 'required|in:Hadir,Izin,Sakit',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $laporan = LaporanKegiatanEskul::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('tanggal_kegiatan', $tanggal)
            ->first();

        $rencana = RencanaKegiatanEskul::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('tanggal_rencana', $tanggal)
            ->first();

        $presensi = PresensiEskul::updateOrCreate(
            [
                'ekstrakurikuler_id' => $ekstrakurikuler->id,
                'tanggal'            => $tanggal,
                'siswa_id'           => $user->id,
            ],
            [
                'laporan_kegiatan_id' => $laporan?->id,
                'rencana_kegiatan_id' => $rencana?->id,
                'status'              => $request->status,
                'keterangan'          => $request->keterangan ?: 'Absen Mandiri Siswa',
                'metode_absen'        => 'mandiri',
                'diinput_oleh'        => $user->id,
            ]
        );

        // Update jumlah hadir di laporan jika ada
        if ($laporan) {
            $laporan->increment($request->status === 'Hadir' ? 'jumlah_hadir' : ($request->status === 'Izin' ? 'jumlah_izin' : 'jumlah_sakit'));
        }

        // Sinkronkan absensi mandiri ke mapel Team Work Project dan Project Pancasila
        \App\Services\EskulSyncService::syncPresensiSiswa(
            $ekstrakurikuler,
            $tanggal,
            $user->id,
            $request->status,
            $request->keterangan ?: 'Absen Mandiri Siswa'
        );

        return back()->with('success', "Absensi mandiri berhasil dicatat dan disinkronkan ke Mapel Team Work Project & Project Pancasila: Status {$request->status} pada {$ekstrakurikuler->nama}.");
    }

    /**
     * Tampilan / Form Khusus Ketua Eskul untuk mengabsen rekannya
     */
    public function ketuaAbsensiIndex(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $user = auth()->user();

        // Pastikan user adalah ketua eskul ini
        $isKetua = ($ekstrakurikuler->ketua_siswa_id === $user->id) ||
            AnggotaEkstrakurikuler::where('ekstrakurikuler_id', $ekstrakurikuler->id)
                ->where('siswa_id', $user->id)
                ->where('jabatan', 'Ketua')
                ->exists();

        if (!$isKetua) {
            abort(403, 'Akses ditolak. Anda bukan ketua yang ditugaskan untuk ekstrakurikuler ini.');
        }

        $ta = PengaturanSekolah::getActiveTahunAjaran();
        $sem = PengaturanSekolah::getActiveSemester();

        $anggotas = AnggotaEkstrakurikuler::with('siswa.kelas')
            ->where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('status', 'aktif')
            ->where('tahun_ajaran', $ta)
            ->where('semester', $sem)
            ->orderByRaw("FIELD(jabatan, 'Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Anggota')")
            ->get();

        $tanggal = $request->input('tanggal', now()->format('Y-m-d'));

        $presensis = PresensiEskul::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('tanggal', $tanggal)
            ->get()
            ->keyBy('siswa_id');

        return view('siswa.ekstrakurikuler.ketua_absensi', compact(
            'ekstrakurikuler',
            'anggotas',
            'tanggal',
            'presensis'
        ));
    }

    /**
     * Ketua Eskul menginput / menyimpan presensi rekannya
     */
    public function ketuaAbsensiStore(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $user = auth()->user();

        $isKetua = ($ekstrakurikuler->ketua_siswa_id === $user->id) ||
            AnggotaEkstrakurikuler::where('ekstrakurikuler_id', $ekstrakurikuler->id)
                ->where('siswa_id', $user->id)
                ->where('jabatan', 'Ketua')
                ->exists();

        if (!$isKetua) {
            abort(403, 'Akses ditolak. Anda bukan ketua eskul ini.');
        }

        $request->validate([
            'tanggal'        => 'required|date',
            'presensi'       => 'required|array',
            'presensi.*'     => 'in:Hadir,Izin,Sakit,Alpa',
            'keterangan'     => 'nullable|array',
            'keterangan.*'   => 'nullable|string|max:255',
        ]);

        $tanggal = $request->tanggal;

        $laporan = LaporanKegiatanEskul::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('tanggal_kegiatan', $tanggal)
            ->first();

        $rencana = RencanaKegiatanEskul::where('ekstrakurikuler_id', $ekstrakurikuler->id)
            ->where('tanggal_rencana', $tanggal)
            ->first();

        DB::beginTransaction();
        try {
            foreach ($request->presensi as $siswaId => $status) {
                $ket = $request->keterangan[$siswaId] ?? null;

                PresensiEskul::updateOrCreate(
                    [
                        'ekstrakurikuler_id' => $ekstrakurikuler->id,
                        'tanggal'            => $tanggal,
                        'siswa_id'           => $siswaId,
                    ],
                    [
                        'laporan_kegiatan_id' => $laporan?->id,
                        'rencana_kegiatan_id' => $rencana?->id,
                        'status'              => $status,
                        'keterangan'          => $ket,
                        'metode_absen'        => 'ketua',
                        'diinput_oleh'        => $user->id,
                    ]
                );

                // Sinkronkan ke mapel Team Work Project dan Project Pancasila
                \App\Services\EskulSyncService::syncPresensiSiswa(
                    $ekstrakurikuler,
                    $tanggal,
                    (int)$siswaId,
                    $status,
                    $ket ?: 'Absensi oleh Ketua Eskul'
                );
            }

            // Update statistik laporan jika ada
            if ($laporan) {
                $rekap = PresensiEskul::where('ekstrakurikuler_id', $ekstrakurikuler->id)
                    ->where('tanggal', $tanggal)
                    ->get();

                $laporan->update([
                    'jumlah_hadir' => $rekap->where('status', 'Hadir')->count(),
                    'jumlah_izin'  => $rekap->where('status', 'Izin')->count(),
                    'jumlah_sakit' => $rekap->where('status', 'Sakit')->count(),
                    'jumlah_alpa'  => $rekap->where('status', 'Alpa')->count(),
                ]);
            }

            DB::commit();
            return back()->with('success', "Presensi eskul oleh Ketua berhasil disimpan untuk tanggal {$tanggal}!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan presensi: ' . $e->getMessage());
        }
    }
}
