<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisPelanggaran;
use App\Models\KategoriPelanggaran;
use Illuminate\Http\Request;

class JenisPelanggaranController extends Controller
{
    public function index()
    {
        $jenisPelanggarans = JenisPelanggaran::with('kategori')->orderBy('kode')->paginate(15);
        return view('admin.jenis-pelanggaran.index', compact('jenisPelanggarans'));
    }

    public function create()
    {
        $kategoris = KategoriPelanggaran::all();
        return view('admin.jenis-pelanggaran.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_pelanggaran_id' => 'required|exists:kategori_pelanggarans,id',
            'kode' => 'required|string|max:10|unique:jenis_pelanggarans',
            'nama' => 'required|string|max:255',
            'poin' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
        ]);

        JenisPelanggaran::create($validated);

        return redirect()->route('admin.jenis-pelanggaran.index')
            ->with('success', 'Jenis pelanggaran berhasil ditambahkan.');
    }

    public function edit(JenisPelanggaran $jenisPelanggaran)
    {
        $kategoris = KategoriPelanggaran::all();
        return view('admin.jenis-pelanggaran.edit', compact('jenisPelanggaran', 'kategoris'));
    }

    public function update(Request $request, JenisPelanggaran $jenisPelanggaran)
    {
        $validated = $request->validate([
            'kategori_pelanggaran_id' => 'required|exists:kategori_pelanggarans,id',
            'kode' => 'required|string|max:10|unique:jenis_pelanggarans,kode,' . $jenisPelanggaran->id,
            'nama' => 'required|string|max:255',
            'poin' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
        ]);

        $jenisPelanggaran->update($validated);

        return redirect()->route('admin.jenis-pelanggaran.index')
            ->with('success', 'Jenis pelanggaran berhasil diperbarui.');
    }

    public function destroy(JenisPelanggaran $jenisPelanggaran)
    {
        $jenisPelanggaran->delete();

        return redirect()->route('admin.jenis-pelanggaran.index')
            ->with('success', 'Jenis pelanggaran berhasil dihapus.');
    }
}
