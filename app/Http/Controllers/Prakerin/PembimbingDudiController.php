<?php

namespace App\Http\Controllers\Prakerin;

use App\Http\Controllers\Controller;
use App\Models\PembimbingDudi;
use App\Models\Dudi;
use Illuminate\Http\Request;

/**
 * Controller Pengelolaan Instruktur / Pembimbing Lapangan DU/DI
 * Modul Prakerin SMK Plus Al Hilal
 */
class PembimbingDudiController extends Controller
{
    /**
     * Menampilkan daftar pembimbing DU/DI
     */
    public function index(Request $request)
    {
        $query = PembimbingDudi::with('dudi')->withCount('penempatans');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'like', "%{$s}%")
                  ->orWhere('jabatan', 'like', "%{$s}%")
                  ->orWhere('no_hp', 'like', "%{$s}%")
                  ->orWhereHas('dudi', function ($dq) use ($s) {
                      $dq->where('nama', 'like', "%{$s}%");
                  });
            });
        }

        if ($request->filled('dudi_id')) {
            $query->where('dudi_id', $request->dudi_id);
        }

        $pembimbing = $query->orderBy('nama')->paginate(15)->withQueryString();
        $dudiList = Dudi::where('status', true)->orderBy('nama')->get();

        return view('prakerin.pembimbing_dudi.index', compact('pembimbing', 'dudiList'));
    }

    /**
     * Form tambah pembimbing DU/DI
     */
    public function create()
    {
        $dudi = Dudi::where('status', true)->orderBy('nama')->get();
        return view('prakerin.pembimbing_dudi.create', compact('dudi'));
    }

    /**
     * Simpan data pembimbing DU/DI
     */
    public function store(Request $request)
    {
        $request->validate([
            'dudi_id'  => 'required|exists:dudi,id',
            'nama'     => 'required|max:100',
            'jabatan'  => 'nullable|max:100',
            'no_hp'    => 'nullable|max:20',
            'email'    => 'nullable|email|max:100',
        ], [
            'dudi_id.required' => 'DU/DI wajib dipilih.',
            'nama.required'    => 'Nama pembimbing wajib diisi.',
            'email.email'      => 'Format email tidak valid.',
        ]);

        PembimbingDudi::create($request->only('dudi_id', 'nama', 'jabatan', 'no_hp', 'email') + ['status' => true]);

        return redirect()->route('prakerin.pembimbing-dudi.index')
            ->with('success', 'Data pembimbing DU/DI berhasil ditambahkan.');
    }

    /**
     * Detail pembimbing DU/DI beserta siswa bimbingannya
     */
    public function show(PembimbingDudi $pembimbingDudi)
    {
        $pembimbingDudi->load([
            'dudi',
            'penempatans.siswa.kelas',
            'penempatans.periodePrakerin',
            'penempatans.guru',
        ]);

        return view('prakerin.pembimbing_dudi.show', compact('pembimbingDudi'));
    }

    /**
     * Form edit pembimbing DU/DI
     */
    public function edit(PembimbingDudi $pembimbingDudi)
    {
        $dudi = Dudi::where('status', true)->orderBy('nama')->get();
        return view('prakerin.pembimbing_dudi.edit', compact('pembimbingDudi', 'dudi'));
    }

    /**
     * Update pembimbing DU/DI
     */
    public function update(Request $request, PembimbingDudi $pembimbingDudi)
    {
        $request->validate([
            'dudi_id'  => 'required|exists:dudi,id',
            'nama'     => 'required|max:100',
            'jabatan'  => 'nullable|max:100',
            'no_hp'    => 'nullable|max:20',
            'email'    => 'nullable|email|max:100',
        ], [
            'dudi_id.required' => 'DU/DI wajib dipilih.',
            'nama.required'    => 'Nama pembimbing wajib diisi.',
            'email.email'      => 'Format email tidak valid.',
        ]);

        $pembimbingDudi->update(
            $request->only('dudi_id', 'nama', 'jabatan', 'no_hp', 'email')
            + ['status' => $request->boolean('status')]
        );

        return redirect()->route('prakerin.pembimbing-dudi.index')
            ->with('success', 'Data pembimbing DU/DI berhasil diubah.');
    }

    /**
     * Hapus pembimbing DU/DI
     */
    public function destroy(PembimbingDudi $pembimbingDudi)
    {
        $pembimbingDudi->delete();
        return redirect()->route('prakerin.pembimbing-dudi.index')
            ->with('success', 'Data pembimbing DU/DI berhasil dihapus.');
    }

    /**
     * Toggle status aktif/non-aktif
     */
    public function toggleStatus(PembimbingDudi $pembimbingDudi)
    {
        $pembimbingDudi->update(['status' => !$pembimbingDudi->status]);

        $statusText = $pembimbingDudi->status ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('prakerin.pembimbing-dudi.index')
            ->with('success', "Pembimbing {$pembimbingDudi->nama} berhasil {$statusText}.");
    }
}
