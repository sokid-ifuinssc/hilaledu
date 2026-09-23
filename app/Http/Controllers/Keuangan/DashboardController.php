<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\Pembayaran;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard utama untuk Modul Keuangan.
     * 
     * Algoritma:
     * 1. Hitung total pemasukan dari seluruh pembayaran.
     * 2. Hitung jumlah total tagihan yang ada di sistem.
     * 3. Kirim data ke view 'keuangan.dashboard'.
     */
    public function index()
    {
        $totalPemasukan = Pembayaran::sum('nominal_bayar');
        $totalTagihan = Tagihan::count();
        
        return view('keuangan.dashboard', [
            'totalPemasukan' => $totalPemasukan,
            'totalTagihan' => $totalTagihan
        ]);
    }
}
