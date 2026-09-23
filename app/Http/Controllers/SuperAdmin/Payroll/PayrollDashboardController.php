<?php

namespace App\Http\Controllers\SuperAdmin\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll\PayrollPeriode;
use App\Models\Payroll\Payroll;
use App\Models\Payroll\PayrollSetting;
use App\Models\User;
use Illuminate\Http\Request;

class PayrollDashboardController extends Controller
{
    public function index()
    {
        $totalPegawai      = User::whereIn('role', ['guru', 'tendik'])->where('is_active', true)->count();
        $totalGuru         = User::where('role', 'guru')->where('is_active', true)->count();
        $totalTendik       = User::where('role', 'tendik')->where('is_active', true)->count();
        $totalPeriode      = PayrollPeriode::count();
        $totalUangDibayar  = PayrollPeriode::where('status', 'paid')->sum('total_dibayarkan');
        $periodeTerakhir   = PayrollPeriode::latest('tahun')->latest('bulan')->first();

        $recentPeriodes    = PayrollPeriode::withCount('payrolls')->latest()->take(6)->get();

        // Rekapitulasi per bulan untuk grafik
        $rekapTahunIni = PayrollPeriode::where('tahun', date('Y'))
            ->orderBy('bulan')
            ->get(['bulan', 'nama_periode', 'total_penerimaan', 'total_potongan', 'total_dibayarkan']);

        // Cek apakah ada pegawai yang belum diset gajinya
        $pegawaiTanpaSetting = User::whereIn('role', ['guru', 'tendik'])
            ->where('is_active', true)
            ->doesntHave('payrollSetting')
            ->count();

        return view('superadmin.payroll.dashboard', compact(
            'totalPegawai',
            'totalGuru',
            'totalTendik',
            'totalPeriode',
            'totalUangDibayar',
            'periodeTerakhir',
            'recentPeriodes',
            'rekapTahunIni',
            'pegawaiTanpaSetting'
        ));
    }
}
