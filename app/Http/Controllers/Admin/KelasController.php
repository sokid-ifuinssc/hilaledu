<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with(['jurusan', 'tahunAjaran', 'waliKelas'])
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->paginate(15);
        return view('admin.kelas.index', compact('kelas'));
    }

    public function create()
    {
        $jurusans = Jurusan::all();
        $tahunAjarans = TahunAjaran::orderBy('created_at', 'desc')->get();
        $waliKelas = User::role('wali_kelas')->where('is_active', true)->get();
        return view('admin.kelas.create', compact('jurusans', 'tahunAjarans', 'waliKelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:50',
            'tingkat' => 'required|in:X,XI,XII',
            'jurusan_id' => 'required|exists:jurusans,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        Kelas::create($validated);

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kela)
    {
        $jurusans = Jurusan::all();
        $tahunAjarans = TahunAjaran::orderBy('created_at', 'desc')->get();
        $waliKelas = User::role('wali_kelas')->where('is_active', true)->get();
        return view('admin.kelas.edit', ['kelas' => $kela, 'jurusans' => $jurusans, 'tahunAjarans' => $tahunAjarans, 'waliKelas' => $waliKelas]);
    }

    public function update(Request $request, Kelas $kela)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:50',
            'tingkat' => 'required|in:X,XI,XII',
            'jurusan_id' => 'required|exists:jurusans,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'wali_kelas_id' => 'nullable|exists:users,id',
        ]);

        $kela->update($validated);

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kela)
    {
        $kela->delete();

        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
