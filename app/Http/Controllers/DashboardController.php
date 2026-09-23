<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Application;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\AppCoordinator;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Superadmin dashboard (Pusat Kendali HilalEdu & Seluruh Data Sekolah).
     */
    public function superadmin()
    {
        $stats = [
            'total_users'    => User::count(),
            'active_users'   => User::where('is_active', true)->count(),
            'total_apps'     => Application::count(),
            'active_apps'    => Application::where('status', 'active')->count(),
            'total_guru'     => User::where('role', 'guru')->count(),
            'total_siswa'    => User::where('role', 'siswa')->count(),
            'total_tendik'   => User::where('role', 'tendik')->count(),
            'total_jurusan'  => Jurusan::where('is_aktif', true)->count(),
            'total_kelas'    => Kelas::count(),
            'tahun_aktif'    => TahunAjaran::where('is_aktif', true)->first()?->nama ?? '-',
            'users_by_role'  => User::selectRaw('role, count(*) as count')
                ->groupBy('role')
                ->pluck('count', 'role'),
        ];

        // Aplikasi terintegrasi dengan jumlah koordinator
        $apps = Application::withCount(['coordinatorUsers'])
            ->orderBy('sort_order')
            ->get();

        $recentUsers = User::latest()->take(5)->get();
        $recentGuru  = User::where('role', 'guru')->latest()->take(5)->get();

        return view('superadmin.dashboard', compact('stats', 'apps', 'recentUsers', 'recentGuru'));
    }

    /**
     * Admin redirect fallback ke Superadmin dashboard.
     */
    public function admin()
    {
        return redirect()->route('superadmin.dashboard');
    }

    /**
     * Operator redirect fallback.
     */
    public function operator()
    {
        return redirect()->route('login');
    }

    /**
     * Portal Pemilihan Aplikasi untuk Guru.
     */
    public function guru()
    {
        $user = auth()->user();
        $applications = Application::orderBy('sort_order')->get();
        $coordinatorRoles = AppCoordinator::where('user_id', $user->id)
            ->get()
            ->keyBy('application_id');

        $tahunAktif = TahunAjaran::where('is_aktif', true)->first()?->nama ?? '2024/2025';

        return view('guru.dashboard', compact('applications', 'coordinatorRoles', 'tahunAktif'));
    }

    /**
     * Portal Pemilihan Aplikasi untuk Tendik.
     */
    public function tendik()
    {
        $user = auth()->user();
        $applications = Application::orderBy('sort_order')->get();
        $coordinatorRoles = AppCoordinator::where('user_id', $user->id)
            ->get()
            ->keyBy('application_id');

        $tahunAktif = TahunAjaran::where('is_aktif', true)->first()?->nama ?? '2024/2025';

        return view('tendik.dashboard', compact('applications', 'coordinatorRoles', 'tahunAktif'));
    }

    /**
     * Portal Pemilihan Aplikasi untuk Siswa.
     */
    public function siswa()
    {
        $applications = Application::orderBy('sort_order')->get();
        $tahunAktif = TahunAjaran::where('is_aktif', true)->first()?->nama ?? '2024/2025';

        return view('siswa.dashboard', compact('applications', 'tahunAktif'));
    }
}
