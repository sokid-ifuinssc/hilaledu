<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Tendik;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $tahunAjaran = TahunAjaran::aktif();

        $stats = [
            'total_siswa' => Siswa::aktif()->count(),
            'total_guru' => Guru::where('is_active', true)->count(),
            'total_tendik' => Tendik::where('is_active', true)->count(),
            'total_kelas' => $tahunAjaran ? Kelas::where('tahun_ajaran_id', $tahunAjaran->id)->count() : 0,
            'total_jurusan' => Jurusan::count(),
            'total_user' => User::where('is_active', true)->count(),
            'total_pelanggaran' => $tahunAjaran ? Pelanggaran::where('tahun_ajaran_id', $tahunAjaran->id)->count() : 0,
            'pelanggaran_belum_selesai' => $tahunAjaran ? Pelanggaran::where('tahun_ajaran_id', $tahunAjaran->id)->where('status', '!=', 'selesai')->count() : 0,
        ];

        return view('admin.dashboard', compact('stats', 'tahunAjaran'));
    }
}
