<?php

namespace App\Http\Controllers\Prakerin;

use App\Http\Controllers\Controller;
use App\Models\Industri;
use Illuminate\Http\Request;

class IndustriController extends Controller
{
    /**
     * Menampilkan daftar industri mitra prakerin.
     */
    public function index(Request $request)
    {
        $query = Industri::withCount('jurnalPrakerins');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('kontak', 'like', "%{$search}%");
        }

        $industris = $query->orderBy('nama', 'asc')->paginate(15)->withQueryString();

        return view('prakerin.industri.index', compact('industris'));
    }

    /**
     * Simpan data industri baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'kontak' => 'nullable|string|max:100',
        ]);

        Industri::create($validated);

        return redirect()->route('prakerin.industri.index')->with('success', 'Data Industri Mitra berhasil ditambahkan.');
    }

    /**
     * Update data industri.
     */
    public function update(Request $request, Industri $industri)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'kontak' => 'nullable|string|max:100',
        ]);

        $industri->update($validated);

        return redirect()->route('prakerin.industri.index')->with('success', 'Data Industri Mitra berhasil diperbarui.');
    }

    /**
     * Hapus data industri.
     */
    public function destroy(Industri $industri)
    {
        $industri->delete();

        return redirect()->route('prakerin.industri.index')->with('success', 'Industri Mitra berhasil dihapus.');
    }
}
