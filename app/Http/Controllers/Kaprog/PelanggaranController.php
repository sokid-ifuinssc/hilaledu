<?php

namespace App\Http\Controllers\Kaprog;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\Pelanggaran;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class PelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $jurusanIds = Jurusan::where('kaprog_id', $user->id)->pluck('id');
        $tahunAjaran = TahunAjaran::aktif();

        $query = Pelanggaran::with(['siswa.kelas.jurusan', 'jenisPelanggaran.kategori', 'progresPelanggaran'])
            ->whereHas('siswa.kelas', fn($q) => $q->whereIn('jurusan_id', $jurusanIds))
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('siswa', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pelanggarans = $query->orderBy('tanggal_pelanggaran', 'desc')->paginate(15)->withQueryString();
        return view('kaprog.pelanggaran.index', compact('pelanggarans'));
    }

    public function show(Pelanggaran $pelanggaran)
    {
        $pelanggaran->load(['siswa.kelas.jurusan', 'jenisPelanggaran.kategori', 'pencatat', 'progresPelanggaran']);
        return view('kaprog.pelanggaran.show', compact('pelanggaran'));
    }
}
