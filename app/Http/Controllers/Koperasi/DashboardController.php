<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KoperasiAnggota;
use App\Models\KoperasiTransaksi;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard utama untuk Modul Koperasi.
     * 
     * Algoritma:
     * 1. Hitung total anggota koperasi.
     * 2. Ambil total transaksi simpanan/pinjaman (contoh agregat).
     * 3. Kirim data statistik tersebut ke view 'koperasi.dashboard'.
     */
    public function index()
    {
        $totalAnggota = KoperasiAnggota::count();
        $totalTransaksi = KoperasiTransaksi::count();
        
        return view('koperasi.dashboard', [
            'totalAnggota' => $totalAnggota,
            'totalTransaksi' => $totalTransaksi
        ]);
    }
}
