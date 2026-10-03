<?php

namespace App\Http\Controllers\SuperAdmin\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\PayrollKomponen;
use Illuminate\Http\Request;

class PayrollKomponenController extends Controller
{
    public function index(Request $request)
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

        // Pastikan komponen inti dasar (Honor Jam Mengajar & Transport) tersedia
        PayrollKomponen::firstOrCreate(
            ['kode' => 'HJM01'],
            [
                'nama'            => 'Honor Jam Mengajar',
                'jenis'           => 'penerimaan',
                'tipe'            => 'per_jam',
                'nominal_default' => 35000,
                'is_aktif'        => true,
                'keterangan'      => 'Honor per jam tatap muka pelajaran guru',
            ]
        );

        PayrollKomponen::firstOrCreate(
            ['kode' => 'TK01'],
            [
                'nama'            => 'Tunjangan Kehadiran & Transport',
                'jenis'           => 'penerimaan',
                'tipe'            => 'per_kehadiran',
                'nominal_default' => 20000,
                'is_aktif'        => true,
                'keterangan'      => 'Uang transportasi dan kehadiran mengajar harian',
            ]
        );

        $queryPenerimaan = PayrollKomponen::where('jenis', 'penerimaan');
        $queryPotongan   = PayrollKomponen::where('jenis', 'potongan');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $queryPenerimaan->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhere('tipe', 'like', "%{$search}%");
            });
            $queryPotongan->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhere('tipe', 'like', "%{$search}%");
            });
        }

        // Komponen Jam Mengajar & Transport dijadikan paling atas pada daftar komponen
        $penerimaan = $queryPenerimaan
            ->orderByRaw("
                CASE 
                    WHEN tipe = 'per_jam' OR kode = 'HJM01' OR nama LIKE '%Jam Mengajar%' OR nama LIKE '%Honor Jam%' THEN 1
                    WHEN tipe = 'per_kehadiran' OR kode = 'TK01' OR nama LIKE '%Transport%' THEN 2
                    WHEN kode = 'GP01' OR nama LIKE '%Gaji Pokok%' THEN 3
                    ELSE 4
                END ASC, kode ASC
            ")
            ->get();

        $potongan = $queryPotongan->orderBy('kode')->get();

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

        // Otomatis hubungkan dan perbarui pengaturan gaji pegawai & slip draft guru
        \App\Models\Payroll\PayrollSetting::syncAllFromMasterKomponen(true);

        return redirect()->route('superadmin.payroll.komponen.index')
            ->with('success', 'Komponen gaji berhasil ditambahkan dan otomatis terhubung ke pengaturan gaji pegawai.');
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

        // Jika nama komponen cocok dengan master_tugas_tambahan, update nominal_gaji
        \App\Models\TugasTambahan::where('nama', $komponen->nama)
            ->update(['nominal_gaji' => $komponen->nominal_default]);

        // Otomatis hubungkan dan perbarui pengaturan gaji pegawai & slip draft guru
        \App\Models\Payroll\PayrollSetting::syncAllFromMasterKomponen(true);

        return redirect()->route('superadmin.payroll.komponen.index')
            ->with('success', 'Komponen gaji berhasil diperbarui dan otomatis disinkronkan ke seluruh pengaturan gaji pegawai.');
    }

    public function destroy(PayrollKomponen $komponen)
    {
        $komponen->delete();

        // Otomatis hubungkan dan perbarui pengaturan gaji pegawai & slip draft guru
        \App\Models\Payroll\PayrollSetting::syncAllFromMasterKomponen(true);

        return redirect()->route('superadmin.payroll.komponen.index')
            ->with('success', 'Komponen gaji berhasil dihapus.');
    }

    public function toggleActive(PayrollKomponen $komponen)
    {
        $komponen->update(['is_aktif' => !$komponen->is_aktif]);

        \App\Models\Payroll\PayrollSetting::syncAllFromMasterKomponen(true);

        return back()->with('success', 'Status komponen gaji berhasil diubah.');
    }
}
