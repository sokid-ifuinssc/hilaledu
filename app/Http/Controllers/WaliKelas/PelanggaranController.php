<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class PelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $kelasIds = Kelas::where('wali_kelas_id', $user->id)->pluck('id');
        $siswaIds = Siswa::whereIn('kelas_id', $kelasIds)->pluck('id');
        $tahunAjaran = TahunAjaran::aktif();

        $query = Pelanggaran::with(['siswa.kelas', 'jenisPelanggaran.kategori', 'progresPelanggaran'])
            ->whereIn('siswa_id', $siswaIds)
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id));

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pelanggarans = $query->orderBy('tanggal_pelanggaran', 'desc')->paginate(15)->withQueryString();
        return view('walikelas.pelanggaran.index', compact('pelanggarans'));
    }

    public function show(Pelanggaran $pelanggaran)
    {
        $pelanggaran->load(['siswa.kelas.jurusan', 'jenisPelanggaran.kategori', 'pencatat', 'progresPelanggaran']);
        return view('walikelas.pelanggaran.show', compact('pelanggaran'));
    }
}
