<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $hasAccess = in_array($user->role, $roles) || $user->isSuperAdmin();

        if (!$hasAccess) {
            foreach ($roles as $r) {
                if ($r === 'admin' && ($user->isAdmin() || !empty($user->admin_role) || $user->isSuperAdmin())) {
                    $hasAccess = true; break;
                }
                if ($r === 'superadmin') {
                    if ($user->isSuperAdmin() || ($user->hasAdminRole('payroll') && $request->is('superadmin/payroll*'))) {
                        $hasAccess = true; break;
                    }
                }
                if ($r === 'payroll' && ($user->isSuperAdmin() || $user->hasAdminRole('payroll'))) {
                    $hasAccess = true; break;
                }
                if ($r === 'guru' && $user->role === 'guru') {
                    // Guru dengan admin_role tetap diizinkan mengakses halaman guru
                    $hasAccess = true; break;
                }
                if ($r === 'guru_bk' && ($user->hasTugasTambahan('Guru BK') || $user->hasTugas('BK') || $user->hasTugas('Konseling') || $user->hasAdminRole('bk') || $user->isSuperAdmin())) {
                    $hasAccess = true; break;
                }
                if ($r === 'kaprog' && ($user->hasRoleCategory('Kaprog') || $user->hasTugas('Kaprog') || $user->hasTugas('Kepala Program') || $user->isKaprog() || $user->hasAdminRole('akademik') || $user->isSuperAdmin())) {
                    $hasAccess = true; break;
                }
                if ($r === 'wali_kelas' && ($user->hasRoleCategory('Wali Kelas') || $user->hasTugas('Wali Kelas') || $user->isWaliKelas() || $user->hasAdminRole('akademik') || $user->isSuperAdmin())) {
                    $hasAccess = true; break;
                }
                if ($r === 'kepala_sekolah' && ($user->hasTugasTambahan('Kepala Sekolah') || $user->hasTugas('Kepala Sekolah') || $user->isSuperAdmin())) {
                    $hasAccess = true; break;
                }
                if ($r === 'waka_kesiswaan' && ($user->hasRoleCategory('Kesiswaan') || $user->hasTugas('Kesiswaan') || $user->isWakaKesiswaan() || $user->hasAdminRole('bk') || $user->isSuperAdmin())) {
                    $hasAccess = true; break;
                }
                if ($r === 'pembina_osis' && ($user->isPembinaOsis() || $user->hasTugas('OSIS') || $user->isSuperAdmin())) {
                    $hasAccess = true; break;
                }
                if ($r === 'pembina_eskul' && ($user->isPembinaEskul() || $user->isSuperAdmin())) {
                    $hasAccess = true; break;
                }
            }
        }

        if (!$hasAccess) {
            // Redirect to user's own dashboard
            return redirect()->route($user->dashboardRoute())
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        if (!auth()->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Hubungi administrator.');
        }

        return $next($request);
    }
}
