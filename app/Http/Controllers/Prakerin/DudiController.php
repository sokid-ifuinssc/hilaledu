<?php

namespace App\Http\Controllers\Prakerin;

use App\Http\Controllers\Controller;
use App\Models\Dudi;
use Illuminate\Http\Request;

/**
 * Controller Pengelolaan Mitra DU/DI (Dunia Usaha & Dunia Industri)
 * Modul Prakerin SMK Plus Al Hilal
 */
class DudiController extends Controller
{
    /**
     * Menampilkan daftar mitra DU/DI
     */
    public function index(Request $request)
    {
        $query = Dudi::withCount(['pembimbingDudi', 'penempatans']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'like', "%{$s}%")
                  ->orWhere('bidang_usaha', 'like', "%{$s}%")
                  ->orWhere('alamat', 'like', "%{$s}%");
            });
        }

        $dudi = $query->orderBy('nama')->paginate(15)->withQueryString();
        return view('prakerin.dudi.index', compact('dudi'));
    }

    /**
     * Form tambah mitra DU/DI baru
     */
    public function create()
    {
        return view('prakerin.dudi.create');
    }

    /**
     * Simpan data mitra DU/DI
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'          => 'required|max:150',
            'bidang_usaha'  => 'nullable|max:100',
            'no_telp'       => 'nullable|max:20',
            'email'         => 'nullable|email|max:100',
            'alamat'        => 'nullable|string',
        ], [
            'nama.required' => 'Nama DU/DI wajib diisi.',
            'email.email'   => 'Format email tidak valid.',
        ]);

        Dudi::create($request->only('nama', 'alamat', 'no_telp', 'email', 'bidang_usaha') + ['status' => true]);

        return redirect()->route('prakerin.dudi.index')
            ->with('success', 'Data Mitra DU/DI berhasil ditambahkan.');
    }

    /**
     * Form edit data mitra DU/DI
     */
    public function edit(Dudi $dudi)
    {
        return view('prakerin.dudi.edit', compact('dudi'));
    }

    /**
     * Perbarui data mitra DU/DI
     */
    public function update(Request $request, Dudi $dudi)
    {
        $request->validate([
            'nama'          => 'required|max:150',
            'bidang_usaha'  => 'nullable|max:100',
            'no_telp'       => 'nullable|max:20',
            'email'         => 'nullable|email|max:100',
            'alamat'        => 'nullable|string',
        ], [
            'nama.required' => 'Nama DU/DI wajib diisi.',
            'email.email'   => 'Format email tidak valid.',
        ]);

        $dudi->update($request->only('nama', 'alamat', 'no_telp', 'email', 'bidang_usaha') + [
            'status' => $request->has('status') ? (bool)$request->status : true
        ]);

        return redirect()->route('prakerin.dudi.index')
            ->with('success', 'Data Mitra DU/DI berhasil diperbarui.');
    }

    /**
     * Hapus mitra DU/DI
     */
    public function destroy(Dudi $dudi)
    {
        $dudi->delete();
        return redirect()->route('prakerin.dudi.index')
            ->with('success', 'Data Mitra DU/DI berhasil dihapus.');
    }
}
