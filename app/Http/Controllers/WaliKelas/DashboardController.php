<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\ProgresPelanggaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $tahunAjaran = TahunAjaran::aktif();
        $kelasSaya = Kelas::where('wali_kelas_id', $user->id)->with('jurusan')->get();
        $kelasIds = $kelasSaya->pluck('id');
        $siswaIds = Siswa::whereIn('kelas_id', $kelasIds)->pluck('id');

        // Hitung progres yang perlu aksi dari walikelas
        $progresPerluAksi = ProgresPelanggaran::whereHas('pelanggaran', fn($q) => $q->whereIn('siswa_id', $siswaIds))
            ->where(function($q) {
                $q->where(function($q2) {
                    // Perlu approve
                    $q2->where('status', 'menunggu_approval')
                       ->where('approval_walikelas', 'belum')
                       ->whereIn('jenis_tindakan', ['sp1', 'sp2', 'sp3']);
                })->orWhere(function($q2) {
                    // Perlu laporan
                    $q2->where('status', 'menunggu_laporan')
                       ->whereNull('laporan_walikelas')
                       ->whereIn('jenis_tindakan', ['teguran_lisan', 'home_visit', 'pemanggilan_ortu', 'sp1', 'sp2', 'sp3']);
                });
            })->count();

        $stats = [
            'total_siswa' => $siswaIds->count(),
            'total_pelanggaran' => Pelanggaran::whereIn('siswa_id', $siswaIds)
                ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))->count(),
            'perlu_aksi' => $progresPerluAksi,
            'siswa_bermasalah' => Siswa::whereIn('id', $siswaIds)->where('poin', '<', 70)->count(),
        ];

        $pelanggaranTerbaru = Pelanggaran::with(['siswa', 'jenisPelanggaran.kategori', 'progresPelanggaran'])
            ->whereIn('siswa_id', $siswaIds)
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->orderBy('created_at', 'desc')->limit(10)->get();

        return view('walikelas.dashboard', compact('stats', 'pelanggaranTerbaru', 'kelasSaya', 'tahunAjaran'));
    }

    /**
     * Tampilan Jadwal Lengkap Kelas Bimbingan Wali Kelas
     */
    public function jadwalKelas()
    {
        $user = auth()->user();
        $tahunAjaran = \App\Models\PengaturanSekolah::getActiveTahunAjaran();
        $semester    = \App\Models\PengaturanSekolah::getActiveSemester();

        // Cari kelas bimbingan wali kelas
        $kelasSaya = Kelas::where('wali_kelas_id', $user->id)->first();
        if (!$kelasSaya && !empty($user->name)) {
            $kelasSaya = Kelas::where('wali_kelas', $user->name)->first();
        }
        if (!$kelasSaya && $user->hasAnyTugasTambahan()) {
            foreach (Kelas::all() as $k) {
                if (stripos($user->tugas_tambahan, $k->nama_kelas) !== false || stripos($user->tugas_tambahan, $k->nama) !== false) {
                    $kelasSaya = $k;
                    break;
                }
            }
        }
        if (!$kelasSaya) {
            $kelasSaya = Kelas::first();
        }

        $namaKelas = $kelasSaya ? ($kelasSaya->nama_kelas ?? $kelasSaya->nama) : 'X AKL';

        // Ambil jadwal lengkap seminggu penuh (Senin - Sabtu) hanya untuk kelas bimbingannya
        $jadwalLengkap = \App\Models\JadwalPelajaran::with(['mataPelajaran', 'guru'])
            ->where('kelas', $namaKelas)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('jam_ke_mulai')
            ->get()
            ->groupBy('hari');

        // Ringkasan guru pengajar di kelas ini
        $ringkasanMapel = \App\Models\JadwalPelajaran::with(['mataPelajaran', 'guru'])
            ->where('kelas', $namaKelas)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->get()
            ->groupBy('mata_pelajaran_id');

        $totalJp = \App\Models\JadwalPelajaran::where('kelas', $namaKelas)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->get()
            ->sum(fn($j) => max(1, ($j->jam_ke_selesai - $j->jam_ke_mulai + 1)));

        return view('walikelas.jadwal', compact(
            'user',
            'kelasSaya',
            'namaKelas',
            'jadwalLengkap',
            'ringkasanMapel',
            'totalJp',
            'tahunAjaran',
            'semester'
        ));
    }

    /**
     * Cetak Lembar Jadwal Pelajaran Kelas Bimbingan
     */
    public function printJadwalKelas()
    {
        $user = auth()->user();
        $tahunAjaran = \App\Models\PengaturanSekolah::getActiveTahunAjaran();
        $semester    = \App\Models\PengaturanSekolah::getActiveSemester();

        $kelasSaya = Kelas::where('wali_kelas_id', $user->id)->first();
        if (!$kelasSaya && !empty($user->name)) {
            $kelasSaya = Kelas::where('wali_kelas', $user->name)->first();
        }
        if (!$kelasSaya) {
            $kelasSaya = Kelas::first();
        }

        $namaKelas = $kelasSaya ? ($kelasSaya->nama_kelas ?? $kelasSaya->nama) : 'X AKL';

        $jadwalLengkap = \App\Models\JadwalPelajaran::with(['mataPelajaran', 'guru'])
            ->where('kelas', $namaKelas)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('jam_ke_mulai')
            ->get()
            ->groupBy('hari');

        $settings = \App\Models\PengaturanSekolah::getAllSettings();

        return view('walikelas.jadwal_print', compact(
            'user',
            'kelasSaya',
            'namaKelas',
            'jadwalLengkap',
            'tahunAjaran',
            'semester',
            'settings'
        ));
    }
}
