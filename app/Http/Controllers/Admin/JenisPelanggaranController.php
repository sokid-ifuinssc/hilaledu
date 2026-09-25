<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisPelanggaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JenisPelanggaranExport;
use App\Imports\JenisPelanggaranImport;

class JenisPelanggaranController extends Controller
{
    public function index()
    {
        $jenisPelanggarans = JenisPelanggaran::orderBy('id', 'desc')->paginate(15);
        return view('admin.jenis-pelanggaran.index', compact('jenisPelanggarans'));
    }

    public function create()
    {
        return view('admin.jenis-pelanggaran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'poin' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
        ]);

        JenisPelanggaran::create($validated);

        return redirect()->route(request()->routeIs('admin.*') ? 'admin.jenis-pelanggaran.index' : 'bk.jenis-pelanggaran.index')
            ->with('success', 'Data pelanggaran berhasil ditambahkan.');
    }

    public function edit(JenisPelanggaran $jenisPelanggaran)
    {
        return view('admin.jenis-pelanggaran.edit', compact('jenisPelanggaran'));
    }

    public function update(Request $request, JenisPelanggaran $jenisPelanggaran)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'poin' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
        ]);

        $jenisPelanggaran->update($validated);

        return redirect()->route(request()->routeIs('admin.*') ? 'admin.jenis-pelanggaran.index' : 'bk.jenis-pelanggaran.index')
            ->with('success', 'Data pelanggaran berhasil diperbarui.');
    }

    public function destroy(JenisPelanggaran $jenisPelanggaran)
    {
        $jenisPelanggaran->delete();

        return redirect()->route(request()->routeIs('admin.*') ? 'admin.jenis-pelanggaran.index' : 'bk.jenis-pelanggaran.index')
            ->with('success', 'Data pelanggaran berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new JenisPelanggaranExport, 'jenis_pelanggaran_' . date('YmdHis') . '.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new JenisPelanggaranImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data pelanggaran berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }
}
