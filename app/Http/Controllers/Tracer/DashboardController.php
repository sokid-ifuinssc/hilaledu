<?php

namespace App\Http\Controllers\Tracer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TracerAlumni;
use App\Models\TracerKuesioner;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard utama untuk Modul Tracer Studi.
     * 
     * Algoritma:
     * 1. Hitung total data alumni yang tersimpan.
     * 2. Hitung jumlah total kuesioner yang telah diisi alumni.
     * 3. Kirim data tersebut ke view 'tracer.dashboard' dalam bentuk agregat statistik.
     */
    public function index()
    {
        $totalAlumni = TracerAlumni::count();
        $totalKuesioner = TracerKuesioner::count();
        
        return view('tracer.dashboard', [
            'totalAlumni' => $totalAlumni,
            'totalKuesioner' => $totalKuesioner
        ]);
    }
}
