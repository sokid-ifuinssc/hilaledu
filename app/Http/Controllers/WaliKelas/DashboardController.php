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
}
