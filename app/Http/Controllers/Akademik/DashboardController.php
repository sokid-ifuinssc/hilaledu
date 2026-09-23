<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AkademikJadwal;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard utama untuk Modul Akademik.
     * 
     * Algoritma:
     * 1. Hitung total jadwal mata pelajaran yang terdaftar di database.
     * 2. Hitung jumlah total siswa (User dengan role 'siswa' atau yang tidak memiliki admin_role).
     * 3. Lempar data statistik tersebut ke view 'akademik.dashboard' untuk ditampilkan pada widget.
     */
    public function index()
    {
        // Menghitung data pokok akademik
        $totalJadwal = AkademikJadwal::count();
        $totalSiswa  = User::where('role', 'siswa')->count();
        $totalGuru   = User::where('role', 'guru')->count();
        $totalMapel  = \App\Models\AkademikMapel::count();
        
        return view('akademik.dashboard', [
            'totalJadwal' => $totalJadwal,
            'totalSiswa'  => $totalSiswa,
            'totalGuru'   => $totalGuru,
            'totalMapel'  => $totalMapel,
        ]);
    }
}
