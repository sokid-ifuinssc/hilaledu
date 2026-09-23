<?php

namespace App\Http\Controllers\SuperAdmin\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\PayrollPeriode;
use App\Models\User;

class PayrollReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Payroll::with(['periode', 'user', 'items'])->whereHas('periode');

        if ($request->filled('guru_id')) {
            $query->where('user_id', $request->guru_id);
        }
        if ($request->filled('bulan')) {
            $query->whereHas('periode', function ($q) use ($request) {
                $q->where('bulan', $request->bulan);
            });
        }
        if ($request->filled('tahun')) {
            $query->whereHas('periode', function ($q) use ($request) {
                $q->where('tahun', $request->tahun);
            });
        }
        if ($request->filled('kegiatan')) {
            $query->whereHas('periode', function ($q) use ($request) {
                $q->where('nama_periode', 'like', '%' . $request->kegiatan . '%');
            });
        }

        $payrolls = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        // Filter options
        $gurus = User::whereHas('guruHilal')->orderBy('name')->get();
        $bulans = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $tahuns = PayrollPeriode::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        
        return view('superadmin.payroll.laporan.index', compact('payrolls', 'gurus', 'bulans', 'tahuns'));
    }

    public function print(Request $request)
    {
        $query = Payroll::with(['periode', 'user', 'items'])->whereHas('periode');

        if ($request->filled('guru_id')) {
            $query->where('user_id', $request->guru_id);
        }
        if ($request->filled('bulan')) {
            $query->whereHas('periode', function ($q) use ($request) {
                $q->where('bulan', $request->bulan);
            });
        }
        if ($request->filled('tahun')) {
            $query->whereHas('periode', function ($q) use ($request) {
                $q->where('tahun', $request->tahun);
            });
        }
        if ($request->filled('kegiatan')) {
            $query->whereHas('periode', function ($q) use ($request) {
                $q->where('nama_periode', 'like', '%' . $request->kegiatan . '%');
            });
        }

        $payrolls = $query->orderBy('created_at', 'desc')->get();

        return view('superadmin.payroll.laporan.print', compact('payrolls'));
    }
}
