<?php

namespace App\Http\Controllers\Prakerin;

use App\Http\Controllers\Controller;
use App\Models\PeriodePrakerin;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

/**
 * Controller Pengelolaan Periode & Gelombang Prakerin
 * Modul Prakerin SMK Plus Al Hilal
 */
class PeriodePrakerinController extends Controller
{
    /**
     * Menampilkan daftar periode prakerin
     */
    public function index()
    {
        $periode = PeriodePrakerin::with('tahunAjaran')->withCount('penempatans')->orderBy('tanggal_mulai', 'desc')->get();
        return view('prakerin.periode.index', compact('periode'));
    }

    /**
     * Form tambah periode prakerin
     */
    public function create()
    {
        $tahunAjaran = TahunAjaran::orderBy('nama', 'desc')->get();
        return view('prakerin.periode.create', compact('tahunAjaran'));
    }

    /**
     * Simpan data periode prakerin
     */
    public function store(Request $request)
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'nama'            => 'required|max:100',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ], [
            'tahun_ajaran_id.required' => 'Tahun ajaran wajib dipilih.',
            'nama.required'            => 'Nama periode wajib diisi.',
            'tanggal_mulai.required'   => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        PeriodePrakerin::create($request->only('tahun_ajaran_id', 'nama', 'tanggal_mulai', 'tanggal_selesai')
            + ['aktif' => $request->has('aktif')]);

        return redirect()->route('prakerin.periode.index')
            ->with('success', 'Data periode prakerin berhasil ditambahkan.');
    }

    /**
     * Form edit periode prakerin
     */
    public function edit(PeriodePrakerin $periode)
    {
        $tahunAjaran = TahunAjaran::orderBy('nama', 'desc')->get();
        return view('prakerin.periode.edit', compact('periode', 'tahunAjaran'));
    }

    /**
     * Perbarui data periode prakerin
     */
    public function update(Request $request, PeriodePrakerin $periode)
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'nama'            => 'required|max:100',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ], [
            'tahun_ajaran_id.required' => 'Tahun ajaran wajib dipilih.',
            'nama.required'            => 'Nama periode wajib diisi.',
            'tanggal_mulai.required'   => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        $periode->update($request->only('tahun_ajaran_id', 'nama', 'tanggal_mulai', 'tanggal_selesai')
            + ['aktif' => $request->has('aktif')]);

        return redirect()->route('prakerin.periode.index')
            ->with('success', 'Data periode prakerin berhasil diperbarui.');
    }

    /**
     * Hapus periode prakerin
     */
    public function destroy(PeriodePrakerin $periode)
    {
        $periode->delete();
        return redirect()->route('prakerin.periode.index')
            ->with('success', 'Data periode prakerin berhasil dihapus.');
    }
}
