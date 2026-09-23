<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Payroll\Payroll;
use App\Models\PengaturanSekolah;
use Illuminate\Http\Request;

class GuruPayrollController extends Controller
{
    /**
     * Tampilkan riwayat slip gaji guru yang sedang login
     */
    public function index()
    {
        $user = auth()->user();
        $payrolls = Payroll::where('user_id', $user->id)
            ->with(['periode', 'items'])
            ->join('payroll_periodes', 'payrolls.payroll_periode_id', '=', 'payroll_periodes.id')
            ->orderByDesc('payroll_periodes.tahun')
            ->orderByDesc('payroll_periodes.bulan')
            ->select('payrolls.*')
            ->paginate(12);

        $setting = $user->payrollSetting;

        $totalDiterima = Payroll::where('user_id', $user->id)
            ->whereIn('payrolls.status', ['approved', 'paid'])
            ->sum('gaji_bersih');

        return view('guru.payroll.index', compact('payrolls', 'setting', 'totalDiterima'));
    }

    /**
     * Cetak slip gaji guru
     */
    public function print(Payroll $payroll)
    {
        if ($payroll->user_id !== auth()->id()) {
            abort(403, 'Anda tidak berhak melihat slip gaji orang lain.');
        }

        $payroll->load(['periode', 'items', 'user', 'user.payrollSetting']);
        $sekolah = PengaturanSekolah::getSetting();

        return view('superadmin.payroll.periode.slip', [
            'periode' => $payroll->periode,
            'payroll' => $payroll,
            'sekolah' => $sekolah,
        ]);
    }
}
