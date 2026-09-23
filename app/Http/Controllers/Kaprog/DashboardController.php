<?php

namespace App\Http\Controllers\Kaprog;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $tahunAjaran = TahunAjaran::aktif();
        $jurusans = Jurusan::where('kaprog_id', $user->id)->get();
        $jurusanIds = $jurusans->pluck('id');

        // Get siswa in kaprog's jurusan
        $siswaIds = Siswa::aktif()->whereHas('kelas', fn($q) => $q->whereIn('jurusan_id', $jurusanIds))->pluck('id');

        $stats = [
            'total_siswa' => $siswaIds->count(),
            'total_pelanggaran' => Pelanggaran::whereIn('siswa_id', $siswaIds)
                ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))->count(),
            'pelanggaran_bulan_ini' => Pelanggaran::whereIn('siswa_id', $siswaIds)
                ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
                ->whereMonth('tanggal_pelanggaran', now()->month)->count(),
            'siswa_bermasalah' => Siswa::whereIn('id', $siswaIds)->where('poin', '<', 70)->count(),
        ];

        $pelanggaranTerbaru = Pelanggaran::with(['siswa.kelas', 'jenisPelanggaran.kategori'])
            ->whereIn('siswa_id', $siswaIds)
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->orderBy('created_at', 'desc')->limit(10)->get();

        $siswaKritis = Siswa::with('kelas')->whereIn('id', $siswaIds)->orderBy('poin')->limit(5)->get();

        return view('kaprog.dashboard', compact('stats', 'pelanggaranTerbaru', 'siswaKritis', 'jurusans', 'tahunAjaran'));
    }
}
