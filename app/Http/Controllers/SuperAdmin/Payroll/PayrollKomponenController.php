<?php

namespace App\Http\Controllers\SuperAdmin\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\PayrollKomponen;
use Illuminate\Http\Request;

class PayrollKomponenController extends Controller
{
    public function index()
    {
        // 1. Auto-sync tugas tambahan dari akademik ke komponen payroll
        $tugasTambahanList = \App\Models\TugasTambahan::where('is_aktif', true)->get();
        foreach ($tugasTambahanList as $tugas) {
            // Kita gunakan firstOrCreate berdasarkan 'nama' agar jika sudah ada tidak duplikat
            \App\Models\Payroll\PayrollKomponen::firstOrCreate(
                ['nama' => $tugas->nama],
                [
                    'kode'            => 'TGS-' . strtoupper(\Illuminate\Support\Str::slug(substr($tugas->nama, 0, 10), '') . rand(100, 999)),
                    'jenis'           => 'penerimaan',
                    'tipe'            => 'tetap',
                    'nominal_default' => 0,
                    'keterangan'      => 'Tunjangan Tugas Tambahan (Otomatis Tersinkron)',
                    'is_aktif'        => true,
                ]
            );
        }

        $penerimaan = PayrollKomponen::where('jenis', 'penerimaan')->orderBy('kode')->get();
        $potongan   = PayrollKomponen::where('jenis', 'potongan')->orderBy('kode')->get();

        return view('superadmin.payroll.komponen.index', compact('penerimaan', 'potongan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'            => 'required|string|max:30|unique:payroll_komponens,kode',
            'nama'            => 'required|string|max:150',
            'jenis'           => 'required|in:penerimaan,potongan',
            'tipe'            => 'required|in:tetap,per_jam,per_kehadiran,persentase',
            'nominal_default' => 'required|numeric|min:0',
            'keterangan'      => 'nullable|string|max:255',
            'is_aktif'        => 'boolean',
        ]);

        $validated['is_aktif'] = $request->boolean('is_aktif', true);

        PayrollKomponen::create($validated);

        return redirect()->route('superadmin.payroll.komponen.index')
            ->with('success', 'Komponen gaji berhasil ditambahkan.');
    }

    public function update(Request $request, PayrollKomponen $komponen)
    {
        $validated = $request->validate([
            'nama'            => 'required|string|max:150',
            'jenis'           => 'required|in:penerimaan,potongan',
            'tipe'            => 'required|in:tetap,per_jam,per_kehadiran,persentase',
            'nominal_default' => 'required|numeric|min:0',
            'keterangan'      => 'nullable|string|max:255',
            'is_aktif'        => 'boolean',
        ]);

        $validated['is_aktif'] = $request->boolean('is_aktif', true);

        $komponen->update($validated);

        return redirect()->route('superadmin.payroll.komponen.index')
            ->with('success', 'Komponen gaji berhasil diperbarui.');
    }

    public function destroy(PayrollKomponen $komponen)
    {
        $komponen->delete();

        return redirect()->route('superadmin.payroll.komponen.index')
            ->with('success', 'Komponen gaji berhasil dihapus.');
    }

    public function toggleActive(PayrollKomponen $komponen)
    {
        $komponen->update(['is_aktif' => !$komponen->is_aktif]);

        return back()->with('success', 'Status komponen gaji berhasil diubah.');
    }
}
