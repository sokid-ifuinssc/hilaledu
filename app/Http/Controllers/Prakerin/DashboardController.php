<?php

namespace App\Http\Controllers\Prakerin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dudi;
use App\Models\PembimbingDudi;
use App\Models\PeriodePrakerin;
use App\Models\Penempatan;
use App\Models\Industri;
use App\Models\JurnalPrakerin;

/**
 * Controller Dashboard Utama Modul Prakerin SMK Plus Al Hilal
 */
class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard utama untuk Modul Prakerin.
     * 
     * Algoritma Agregasi Data:
     * 1. Hitung total kemitraan DU/DI & Pembimbing Industri
     * 2. Hitung jumlah gelombang periode prakerin
     * 3. Ambil statistik penempatan siswa (total, aktif, selesai)
     * 4. Ambil 5 penempatan siswa terbaru
     */
    public function index()
    {
        $totalDudi        = Dudi::count();
        $totalPembimbing  = PembimbingDudi::count();
        $totalPeriode     = PeriodePrakerin::count();
        $totalPenempatan  = Penempatan::count();
        $penempatanAktif  = Penempatan::where('status', 'aktif')->count();
        $penempatanSelesai = Penempatan::where('status', 'selesai')->count();

        $recentPenempatan = Penempatan::with(['siswa.kelas.jurusan', 'dudi', 'guru', 'pembimbingDudi'])
            ->latest()
            ->take(5)
            ->get();

        // Legacy metrics
        $totalIndustri = Industri::count();
        $totalJurnal   = JurnalPrakerin::count();
        
        return view('prakerin.dashboard', compact(
            'totalDudi',
            'totalPembimbing',
            'totalPeriode',
            'totalPenempatan',
            'penempatanAktif',
            'penempatanSelesai',
            'recentPenempatan',
            'totalIndustri',
            'totalJurnal'
        ));
    }
}
