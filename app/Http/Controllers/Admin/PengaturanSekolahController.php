<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanSekolah;
use Illuminate\Http\Request;

class PengaturanSekolahController extends Controller
{
    public function index()
    {
        $settings = PengaturanSekolah::orderBy('id')->get();
        return view('admin.pengaturan-sekolah.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $keys = [
            'nama_sekolah', 'npsn', 'alamat_sekolah', 'telepon_sekolah',
            'email_sekolah', 'website_sekolah', 'nama_kepala_sekolah',
            'nip_kepala_sekolah', 'nama_yayasan', 'kop_baris1', 'kop_baris2',
            'kop_baris3', 'kop_baris4',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                PengaturanSekolah::updateOrCreate(
                    ['key' => $key],
                    ['value' => $request->input($key)]
                );
            }
        }

        return redirect()->route('admin.pengaturan-sekolah.index')
            ->with('success', 'Pengaturan sekolah berhasil disimpan.');
    }
}
