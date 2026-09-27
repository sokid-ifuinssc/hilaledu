<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->query('bulan', date('m'));
        $tahun = $request->query('tahun', date('Y'));

        // Query tagihan & pembayaran 
        $siswas = User::where('role', 'siswa')
            ->where('is_active', true)
            ->with(['tagihans.master', 'kelasModel', 'tagihans.pembayarans' => function($q) use ($bulan, $tahun) {
                // If you want to filter payments by month/year in a specific report
            }])
            ->orderBy('name')
            ->get();

        // Calculate totals
        $totalTagihan = 0;
        $totalTerbayar = 0;
        $totalTunggakan = 0;

        foreach ($siswas as $siswa) {
            foreach ($siswa->tagihans as $tagihan) {
                $totalTagihan += $tagihan->nominal;
                $totalTerbayar += $tagihan->terbayar;
            }
            $siswa->total_tagihan = $siswa->tagihans->sum('nominal');
            $siswa->total_terbayar = $siswa->tagihans->sum('terbayar');
            $siswa->sisa_tunggakan = $siswa->total_tagihan - $siswa->total_terbayar;
            
            $totalTunggakan += $siswa->sisa_tunggakan;
        }

        // Laporan per siklus (Total Pembayaran Masuk Bulan Ini)
        $pembayaranBulanIni = Pembayaran::whereMonth('tanggal_bayar', $bulan)
                                        ->whereYear('tanggal_bayar', $tahun)
                                        ->sum('nominal_bayar');

        return view('keuangan.laporan.index', compact(
            'bulan', 'tahun', 'siswas', 
            'totalTagihan', 'totalTerbayar', 'totalTunggakan', 'pembayaranBulanIni'
        ));
    }

    public function print(Request $request)
    {
        $bulan = $request->query('bulan', date('m'));
        $tahun = $request->query('tahun', date('Y'));

        $siswas = User::where('role', 'siswa')
            ->where('is_active', true)
            ->with('tagihans.master', 'kelasModel')
            ->orderBy('name')
            ->get();

        $totalTagihan = 0;
        $totalTerbayar = 0;
        
        foreach ($siswas as $siswa) {
            $siswa->total_tagihan = $siswa->tagihans->sum('nominal');
            $siswa->total_terbayar = $siswa->tagihans->sum('terbayar');
            $siswa->sisa_tunggakan = $siswa->total_tagihan - $siswa->total_terbayar;

            $totalTagihan += $siswa->total_tagihan;
            $totalTerbayar += $siswa->total_terbayar;
        }
        $totalTunggakan = $totalTagihan - $totalTerbayar;
        $pembayaranBulanIni = Pembayaran::whereMonth('tanggal_bayar', $bulan)
                                        ->whereYear('tanggal_bayar', $tahun)
                                        ->sum('nominal_bayar');

        return view('keuangan.laporan.print', compact(
            'bulan', 'tahun', 'siswas',
            'totalTagihan', 'totalTerbayar', 'totalTunggakan', 'pembayaranBulanIni'
        ));
    }
}
