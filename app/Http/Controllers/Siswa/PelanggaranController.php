<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class PelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $siswa = Siswa::where('user_id', $user->id)->first();
        if (!$siswa) { return redirect()->route('siswa.dashboard'); }

        $tahunAjaran = TahunAjaran::aktif();

        $pelanggarans = $siswa->pelanggarans()
            ->with(['jenisPelanggaran.kategori', 'progresPelanggaran'])
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->orderBy('tanggal_pelanggaran', 'desc')->paginate(15);

        return view('siswa.pelanggaran.index', compact('pelanggarans', 'siswa'));
    }

    public function show(Pelanggaran $pelanggaran)
    {
        $user = auth()->user();
        $siswa = Siswa::where('user_id', $user->id)->firstOrFail();

        // Only allow viewing own violations
        if ($pelanggaran->siswa_id !== $siswa->id) { abort(403); }

        $pelanggaran->load(['jenisPelanggaran.kategori', 'progresPelanggaran']);
        return view('siswa.pelanggaran.show', compact('pelanggaran', 'siswa'));
    }
}
