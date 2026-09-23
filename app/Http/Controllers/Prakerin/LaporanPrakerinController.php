<?php

namespace App\Http\Controllers\Prakerin;

use App\Http\Controllers\Controller;
use App\Models\Penempatan;
use App\Models\PeriodePrakerin;
use App\Models\Dudi;
use App\Models\Guru;
use App\Models\PembimbingDudi;
use App\Models\JurnalPrakerin;
use Illuminate\Http\Request;

/**
 * Controller Laporan & Rekapitulasi Kegiatan Prakerin Terpadu
 * Menggabungkan seluruh data penempatan, statistik industri mitra, dan log jurnal prakerin.
 */
class LaporanPrakerinController extends Controller
{
    /**
     * Menampilkan laporan kegiatan dan statistik keseluruhan penempatan prakerin
     */
    public function index(Request $request)
    {
        $periodeList = PeriodePrakerin::with('tahunAjaran')->orderBy('tanggal_mulai', 'desc')->get();
        $dudiList    = Dudi::where('status', true)->orderBy('nama')->get();
        $guruList    = Guru::where('is_active', true)->orderBy('nama_lengkap')->get();

        $periodeId = $request->get('periode_id');
        $dudiId    = $request->get('dudi_id');
        $guruId    = $request->get('guru_id');
        $status    = $request->get('status');

        $query = Penempatan::with([
            'periodePrakerin.tahunAjaran',
            'siswa.kelas.jurusan',
            'dudi',
            'guru',
            'pembimbingDudi',
        ]);

        if ($periodeId) {
            $query->where('periode_prakerin_id', $periodeId);
        }
        if ($dudiId) {
            $query->where('dudi_id', $dudiId);
        }
        if ($guruId) {
            $query->where('guru_id', $guruId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $penempatan = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        // Statistik Penempatan
        $baseQuery = Penempatan::query();
        if ($periodeId) {
            $baseQuery->where('periode_prakerin_id', $periodeId);
        }

        $totalSiswa   = (clone $baseQuery)->count();
        $totalAktif   = (clone $baseQuery)->where('status', 'aktif')->count();
        $totalSelesai = (clone $baseQuery)->where('status', 'selesai')->count();
        $totalBelum   = (clone $baseQuery)->where('status', 'belum_mulai')->count();
        $totalDudi    = Dudi::count();
        $totalPembimbing = PembimbingDudi::count();

        // Statistik per DU/DI
        $allPenempatan = (clone $baseQuery)->with('dudi')->get();
        $perDudi = $allPenempatan->groupBy(fn($p) => $p->dudi->nama ?? 'Lainnya')
            ->map(fn($items) => $items->count())
            ->sortDesc()
            ->take(5);

        return view('prakerin.laporan.index', compact(
            'penempatan', 'periodeList', 'dudiList', 'guruList',
            'periodeId', 'dudiId', 'guruId', 'status',
            'totalSiswa', 'totalAktif', 'totalSelesai', 'totalBelum',
            'totalDudi', 'totalPembimbing', 'perDudi'
        ));
    }

    /**
     * Cetak dokumen rekapitulasi laporan penempatan prakerin resmi sekolah.
     */
    public function print(Request $request)
    {
        $query = Penempatan::with([
            'periodePrakerin.tahunAjaran',
            'siswa.kelas.jurusan',
            'dudi',
            'guru',
            'pembimbingDudi',
        ]);

        if ($request->filled('periode_id')) {
            $query->where('periode_prakerin_id', $request->periode_id);
        }
        if ($request->filled('dudi_id')) {
            $query->where('dudi_id', $request->dudi_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $penempatans = $query->orderBy('created_at', 'desc')->get();
        $dudi        = $request->filled('dudi_id') ? Dudi::find($request->dudi_id) : null;
        $periode     = $request->filled('periode_id') ? PeriodePrakerin::find($request->periode_id) : null;

        return view('prakerin.laporan.print', compact('penempatans', 'dudi', 'periode'));
    }

    /**
     * Export data rekapitulasi prakerin ke format CSV.
     */
    public function export(Request $request)
    {
        $query = Penempatan::with([
            'periodePrakerin.tahunAjaran',
            'siswa.kelas.jurusan',
            'dudi',
            'guru',
            'pembimbingDudi',
        ]);

        if ($request->filled('periode_id')) {
            $query->where('periode_prakerin_id', $request->periode_id);
        }
        if ($request->filled('dudi_id')) {
            $query->where('dudi_id', $request->dudi_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $penempatans = $query->orderBy('created_at', 'desc')->get();

        $filename = 'rekap_prakerin_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($penempatans) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'No',
                'NIS',
                'Nama Siswa',
                'Kelas / Jurusan',
                'Mitra DU/DI',
                'Guru Pembimbing',
                'Instruktur DU/DI',
                'Tanggal Mulai',
                'Tanggal Selesai',
                'Status'
            ]);

            foreach ($penempatans as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->siswa->nis ?? '-',
                    $item->siswa->nama_lengkap ?? '-',
                    ($item->siswa->kelas->nama_lengkap ?? '-') . ' - ' . ($item->siswa->kelas->jurusan->nama ?? '-'),
                    $item->dudi->nama ?? '-',
                    $item->guru->nama_lengkap ?? '-',
                    $item->pembimbingDudi->nama ?? '-',
                    $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-',
                    $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/Y') : '-',
                    $item->status_label,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
