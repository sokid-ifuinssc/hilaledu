<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RekapPresensiService;
use App\Models\User;
use App\Models\PengaturanSekolah;

class RekapPresensiController extends Controller
{
    protected RekapPresensiService $service;

    public function __construct(RekapPresensiService $service)
    {
        $this->service = $service;
    }

    /**
     * Halaman Rekap Presensi disinkronkan ke Rekap Kehadiran Saya
     */
    public function index(Request $request)
    {
        return redirect()->route('guru.absensi.index', $request->query());
    }

    /**
     * Cetak Lembar Rekap Presensi dialihkan ke cetak Rekap Kehadiran Saya
     */
    public function print(Request $request)
    {
        return redirect()->route('guru.absensi.print', $request->query());
    }
}
