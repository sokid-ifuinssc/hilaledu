<?php

namespace App\Http\Controllers\SuperAdmin\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\PayrollSetting;
use App\Models\User;
use Illuminate\Http\Request;

class PayrollSettingController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereIn('role', ['guru', 'tendik'])->with('payrollSetting');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $pegawais = $query->orderBy('role')->orderBy('name')->paginate(15)->withQueryString();

        $totalGuru   = User::where('role', 'guru')->count();
        $totalTendik = User::where('role', 'tendik')->count();

        return view('superadmin.payroll.setting.index', compact('pegawais', 'totalGuru', 'totalTendik'));
    }

    public function edit(User $user)
    {
        if (!in_array($user->role, ['guru', 'tendik'])) {
            abort(404, 'Pengguna bukan guru atau tendik.');
        }

        $setting = $user->payrollSetting ?? new PayrollSetting([
            'user_id'              => $user->id,
            'gaji_pokok'           => $user->role === 'guru' ? 1500000 : 1800000,
            'honor_per_jam'        => $user->role === 'guru' ? 35000 : 0,
            'jam_mengajar_default' => $user->role === 'guru' ? 24 : 0,
            'tunjangan_jabatan'    => 0,
            'tunjangan_kehadiran'  => 200000,
            'tunjangan_lain'       => 0,
            'potongan_bpjs'        => 45000,
            'potongan_koperasi'    => 50000,
            'potongan_lain'        => 25000,
            'atas_nama_rekening'   => $user->name,
        ]);

        return view('superadmin.payroll.setting.edit', compact('user', 'setting'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'gaji_pokok'           => 'required|numeric|min:0',
            'honor_per_jam'        => 'required|numeric|min:0',
            'jam_mengajar_default' => 'required|integer|min:0',
            'tunjangan_jabatan'    => 'required|numeric|min:0',
            'tunjangan_kehadiran'  => 'required|numeric|min:0',
            'tunjangan_lain'       => 'nullable|numeric|min:0',
            'potongan_bpjs'        => 'nullable|numeric|min:0',
            'potongan_koperasi'    => 'nullable|numeric|min:0',
            'potongan_lain'        => 'nullable|numeric|min:0',
            'rekening_bank'        => 'nullable|string|max:100',
            'nomor_rekening'       => 'nullable|string|max:60',
            'atas_nama_rekening'   => 'nullable|string|max:150',
            'catatan'              => 'nullable|string',
        ]);

        $validated['tunjangan_lain']    = $validated['tunjangan_lain'] ?? 0;
        $validated['potongan_bpjs']     = $validated['potongan_bpjs'] ?? 0;
        $validated['potongan_koperasi'] = $validated['potongan_koperasi'] ?? 0;
        $validated['potongan_lain']     = $validated['potongan_lain'] ?? 0;

        PayrollSetting::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return redirect()->route('superadmin.payroll.setting.index')
            ->with('success', "Pengaturan gaji untuk {$user->name} berhasil diperbarui.");
    }

    /**
     * Hitung otomatis & sinkronkan tunjangan jabatan berdasarkan data tugas tambahan guru saat ini.
     */
    public function syncTugasTambahan()
    {
        $pegawais = User::whereIn('role', ['guru', 'tendik'])->get();
        $updated = 0;

        foreach ($pegawais as $p) {
            PayrollSetting::syncTunjanganForUser($p);
            $updated++;
        }

        return redirect()->route('superadmin.payroll.setting.index')
            ->with('success', "Berhasil mensinkronkan tunjangan jabatan untuk {$updated} pendidik & tendik.");
    }
}
