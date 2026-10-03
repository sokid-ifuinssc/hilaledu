<?php

namespace App\Http\Controllers\Tendik;

use App\Http\Controllers\Controller;
use App\Models\Payroll\Payroll;
use App\Models\PengaturanSekolah;
use Illuminate\Http\Request;

class TendikPayrollController extends Controller
{
    /**
     * Tampilkan riwayat slip gaji tenaga kependidikan yang sedang login
     */
    public function index()
    {
        $user = auth()->user();

        \App\Models\Payroll\PayrollSetting::ensureColumnsExist();
        \App\Models\Payroll\PayrollPeriode::ensureColumnsExist();

        $hasTampilGuru = \Illuminate\Support\Facades\Schema::hasColumn('payroll_periodes', 'tampil_ke_guru');

        $query = Payroll::where('user_id', $user->id)
            ->with(['periode', 'items'])
            ->join('payroll_periodes', 'payrolls.payroll_periode_id', '=', 'payroll_periodes.id');

        if ($hasTampilGuru) {
            $query->where('payroll_periodes.tampil_ke_guru', true);
        }

        $payrolls = $query->orderByDesc('payroll_periodes.tahun')
            ->orderByDesc('payroll_periodes.bulan')
            ->select('payrolls.*')
            ->paginate(12);

        $setting = $user->payrollSetting;

        $totalQuery = Payroll::where('user_id', $user->id)
            ->whereIn('payrolls.status', ['approved', 'paid'])
            ->join('payroll_periodes', 'payrolls.payroll_periode_id', '=', 'payroll_periodes.id');

        if ($hasTampilGuru) {
            $totalQuery->where('payroll_periodes.tampil_ke_guru', true);
        }

        $totalDiterima = $totalQuery->sum('gaji_bersih');

        return view('tendik.payroll.index', compact('payrolls', 'setting', 'totalDiterima'));
    }

    /**
     * Cetak slip gaji tendik
     */
    public function print(Payroll $payroll)
    {
        if ($payroll->user_id !== auth()->id()) {
            abort(403, 'Anda tidak berhak melihat slip gaji orang lain.');
        }

        if ($payroll->periode && isset($payroll->periode->tampil_ke_guru) && !$payroll->periode->tampil_ke_guru) {
            abort(403, 'Periode slip gaji ini sedang disembunyikan atau belum dipublikasikan oleh pengelola.');
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
