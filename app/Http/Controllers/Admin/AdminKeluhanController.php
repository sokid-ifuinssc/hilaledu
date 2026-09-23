<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KeluhanKbm;
use App\Models\User;
use App\Models\PengaturanSekolah;

class AdminKeluhanController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->query('status');
        $kelasFilter = $request->query('kelas');
        $kategoriFilter = $request->query('kategori');
        $search = $request->query('q');

        $query = KeluhanKbm::with(['targetGuru', 'mataPelajaran', 'siswa', 'saranPerbaikan.author'])
            ->orderBy('created_at', 'desc');

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        if ($kelasFilter) {
            $query->where('kelas', $kelasFilter);
        }
        if ($kategoriFilter) {
            $query->where('kategori_masalah', $kategoriFilter);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('isi_keluhan', 'like', "%{$search}%")
                  ->orWhere('harapan_siswa', 'like', "%{$search}%")
                  ->orWhereHas('siswa', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  })
                  ->orWhereHas('targetGuru', function ($gq) use ($search) {
                      $gq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $keluhans = $query->paginate(20)->withQueryString();

        // Kategori dari pengaturan
        $kategoriList = PengaturanSekolah::get('kategori_keluhan', [
            'Penjelasan Materi',
            'Metode Mengajar',
            'Sarana Prasarana & Lab',
            'Sikap & Komunikasi Guru',
            'Penilaian & Tugas',
            'Kedisiplinan & Waktu KBM',
            'Lainnya',
        ]);

        $kelasList = ['X AKL', 'X TKJT', 'X TO', 'XI AKL', 'XI TKJT', 'XI TO', 'XII AKL', 'XII TKJT', 'XII TO'];

        $stats = [
            'total'    => KeluhanKbm::count(),
            'baru'     => KeluhanKbm::where('status', 'baru')->count(),
            'diproses' => KeluhanKbm::where('status', 'diproses')->count(),
            'selesai'  => KeluhanKbm::where('status', 'selesai')->count(),
        ];

        return view('admin.keluhan.index', compact('keluhans', 'stats', 'kategoriList', 'kelasList', 'statusFilter', 'kelasFilter', 'kategoriFilter', 'search'));
    }
}
