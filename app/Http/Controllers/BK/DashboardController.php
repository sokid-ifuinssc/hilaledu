<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
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
            'total_pelanggaran' => Pelanggaran::when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))->count(),
            'pelanggaran_bulan_ini' => Pelanggaran::when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
                ->whereMonth('tanggal_pelanggaran', now()->month)
                ->whereYear('tanggal_pelanggaran', now()->year)
                ->count(),
            'dalam_proses' => Pelanggaran::when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
                ->where('status', 'proses')->count(),
            'menunggu_approval' => ProgresPelanggaran::where('status', 'menunggu_approval')->count(),
            'menunggu_laporan' => ProgresPelanggaran::where('status', 'menunggu_laporan')->count(),
            'selesai' => Pelanggaran::when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
                ->where('status', 'selesai')->count(),
            'siswa_bermasalah' => Siswa::aktif()->where('poin', '<', 70)->count(),
        ];

        // Pelanggaran terbaru
        $pelanggaranTerbaru = Pelanggaran::with(['siswa.kelas', 'jenisPelanggaran.kategori', 'progresPelanggaran'])
            ->when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Siswa dengan poin terendah
        $siswaKritis = Siswa::with('kelas.jurusan')
            ->aktif()
            ->orderBy('poin')
            ->limit(10)
            ->get();

        // Data chart - pelanggaran per bulan (6 bulan terakhir)
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $chartData[] = [
                'bulan' => $date->translatedFormat('M Y'),
                'jumlah' => Pelanggaran::when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
                    ->whereMonth('tanggal_pelanggaran', $date->month)
                    ->whereYear('tanggal_pelanggaran', $date->year)
                    ->count(),
            ];
        }

        return view('bk.dashboard', compact('stats', 'pelanggaranTerbaru', 'siswaKritis', 'chartData', 'tahunAjaran'));
    }
}
