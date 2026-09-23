<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisRekomendasi;
use Illuminate\Http\Request;

class JenisRekomendasiController extends Controller
{
    public function index()
    {
        $jenisRekomendasis = JenisRekomendasi::orderBy('untuk')->orderBy('nama')->paginate(15);
        return view('admin.jenis-rekomendasi.index', compact('jenisRekomendasis'));
    }

    public function create()
    {
        return view('admin.jenis-rekomendasi.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:50|unique:jenis_rekomendasis,kode|regex:/^[a-z_]+$/',
            'untuk' => 'required|in:bk,kaprog,semua',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        JenisRekomendasi::create($validated);

        return redirect()->route('admin.jenis-rekomendasi.index')
            ->with('success', 'Jenis rekomendasi berhasil ditambahkan.');
    }

    public function edit(JenisRekomendasi $jenisRekomendasi)
    {
        return view('admin.jenis-rekomendasi.edit', compact('jenisRekomendasi'));
    }

    public function update(Request $request, JenisRekomendasi $jenisRekomendasi)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:50|unique:jenis_rekomendasis,kode,' . $jenisRekomendasi->id . '|regex:/^[a-z_]+$/',
            'untuk' => 'required|in:bk,kaprog,semua',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $jenisRekomendasi->update($validated);

        return redirect()->route('admin.jenis-rekomendasi.index')
            ->with('success', 'Jenis rekomendasi berhasil diperbarui.');
    }

    public function destroy(JenisRekomendasi $jenisRekomendasi)
    {
        $jenisRekomendasi->delete();

        return redirect()->route('admin.jenis-rekomendasi.index')
            ->with('success', 'Jenis rekomendasi berhasil dihapus.');
    }
}
