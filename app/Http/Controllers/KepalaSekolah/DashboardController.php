<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\ProgresPelanggaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;

class DashboardController extends Controller
{
    public function index()
    {
        $tahunAjaran = TahunAjaran::aktif();
        $tahunAjaranId = $tahunAjaran?->id;

        $stats = [
            'total_siswa' => Siswa::aktif()->count(),
            'total_pelanggaran' => Pelanggaran::when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))->count(),
            'pelanggaran_selesai' => Pelanggaran::when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))->where('status', 'selesai')->count(),
            'pelanggaran_proses' => Pelanggaran::when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))->where('status', 'proses')->count(),
            'menunggu_approval' => ProgresPelanggaran::where('status', 'menunggu_approval')->count(),
            'total_jurusan' => Jurusan::count(),
            'total_kelas' => $tahunAjaran ? Kelas::where('tahun_ajaran_id', $tahunAjaranId)->count() : 0,
        ];

        // Per jurusan stats
        $jurusanStats = Jurusan::withCount(['siswas' => fn($q) => $q->where('users.is_active', true)])->get()->map(function ($j) use ($tahunAjaranId) {
            $siswaIds = $j->siswas()->pluck('users.id');
            $j->total_pelanggaran = Pelanggaran::whereIn('siswa_id', $siswaIds)
                ->when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))->count();
            return $j;
        });

        // Chart data
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $chartData[] = [
                'bulan' => $date->translatedFormat('M Y'),
                'jumlah' => Pelanggaran::when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
                    ->whereMonth('tanggal_pelanggaran', $date->month)
                    ->whereYear('tanggal_pelanggaran', $date->year)->count(),
            ];
        }

        $pelanggaranTerbaru = Pelanggaran::with(['siswa.kelas.jurusan', 'jenisPelanggaran', 'progresPelanggaran'])
            ->when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->orderBy('created_at', 'desc')->limit(10)->get();

        return view('kepsek.dashboard', compact('stats', 'jurusanStats', 'chartData', 'pelanggaranTerbaru', 'tahunAjaran'));
    }
}
