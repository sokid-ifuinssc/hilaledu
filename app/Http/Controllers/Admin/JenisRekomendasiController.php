<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisRekomendasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class JenisRekomendasiController extends Controller
{
    private function indexRouteName(): string
    {
        return request()->routeIs('admin.*') ? 'admin.jenis-rekomendasi.index' : 'bk.jenis-rekomendasi.index';
    }

    public function index()
    {
        if (!Schema::hasTable('jenis_rekomendasis')) {
            return redirect()->route('bk.progres.index')
                ->with('info', 'Master jenis rekomendasi sudah digantikan oleh pilihan Jenis Tindakan pada Progres Kasus & Bimbingan.');
        }
        $jenisRekomendasis = JenisRekomendasi::orderBy('untuk')->orderBy('nama')->paginate(15);
        return view('admin.jenis-rekomendasi.index', compact('jenisRekomendasis'));
    }

    public function create()
    {
        if (!Schema::hasTable('jenis_rekomendasis')) {
            return redirect()->route('bk.progres.index');
        }
        return view('admin.jenis-rekomendasi.create');
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('jenis_rekomendasis')) {
            return redirect()->route('bk.progres.index');
        }
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:50|unique:jenis_rekomendasis,kode|regex:/^[a-z_]+$/',
            'untuk' => 'required|in:bk,kaprog,semua',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        JenisRekomendasi::create($validated);

        return redirect()->route($this->indexRouteName())
            ->with('success', 'Jenis rekomendasi berhasil ditambahkan.');
    }

    public function edit(JenisRekomendasi $jenisRekomendasi)
    {
        if (!Schema::hasTable('jenis_rekomendasis')) {
            return redirect()->route('bk.progres.index');
        }
        return view('admin.jenis-rekomendasi.edit', compact('jenisRekomendasi'));
    }

    public function update(Request $request, JenisRekomendasi $jenisRekomendasi)
    {
        if (!Schema::hasTable('jenis_rekomendasis')) {
            return redirect()->route('bk.progres.index');
        }
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:50|unique:jenis_rekomendasis,kode,' . $jenisRekomendasi->id . '|regex:/^[a-z_]+$/',
            'untuk' => 'required|in:bk,kaprog,semua',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $jenisRekomendasi->update($validated);

        return redirect()->route($this->indexRouteName())
            ->with('success', 'Jenis rekomendasi berhasil diperbarui.');
    }

    public function destroy(JenisRekomendasi $jenisRekomendasi)
    {
        if (!Schema::hasTable('jenis_rekomendasis')) {
            return redirect()->route('bk.progres.index');
        }
        $jenisRekomendasi->delete();

        return redirect()->route($this->indexRouteName())
            ->with('success', 'Jenis rekomendasi berhasil dihapus.');
    }
}
