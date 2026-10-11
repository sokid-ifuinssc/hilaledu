<?php

namespace App\Http\Controllers;

use App\Services\WilayahIndonesiaService;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    /**
     * Dapatkan Seluruh Dataset Wilayah untuk Dropdown Bertingkat
     */
    public function getWilayahData()
    {
        return response()->json([
            'provinsi'  => WilayahIndonesiaService::getProvinsi(),
            'kabupaten' => WilayahIndonesiaService::getKabupatenPerProvinsi(),
            'kecamatan' => WilayahIndonesiaService::getKecamatanPerKabupaten(),
            'desa'      => WilayahIndonesiaService::getDesaPerKecamatan(),
        ]);
    }
}
