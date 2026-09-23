<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusans = Jurusan::with('kaprog')->paginate(10);
        return view('admin.jurusan.index', compact('jurusans'));
    }

    public function create()
    {
        $kaprogs = User::role('kaprog')->where('is_active', true)->get();
        return view('admin.jurusan.create', compact('kaprogs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:jurusans',
            'nama' => 'required|string|max:255',
            'kaprog_id' => 'nullable|exists:users,id',
        ]);

        Jurusan::create($validated);

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Program Keahlian berhasil ditambahkan.');
    }

    public function edit(Jurusan $jurusan)
    {
        $kaprogs = User::role('kaprog')->where('is_active', true)->get();
        return view('admin.jurusan.edit', compact('jurusan', 'kaprogs'));
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:jurusans,kode,' . $jurusan->id,
            'nama' => 'required|string|max:255',
            'kaprog_id' => 'nullable|exists:users,id',
        ]);

        $jurusan->update($validated);

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Program Keahlian berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan)
    {
        $jurusan->delete();

        return redirect()->route('admin.jurusan.index')
            ->with('success', 'Program Keahlian berhasil dihapus.');
    }
}
