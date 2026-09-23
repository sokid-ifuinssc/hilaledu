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
                if ($r === 'guru_bk' && ($user->hasTugasTambahan('Guru BK') || $user->admin_role === 'bk' || $user->isSuperAdmin())) {
                    $hasAccess = true; break;
                }
                if ($r === 'kaprog' && ($user->hasRoleCategory('Kaprog') || $user->admin_role === 'akademik' || $user->isSuperAdmin())) {
                    $hasAccess = true; break;
                }
                if ($r === 'wali_kelas' && ($user->hasRoleCategory('Wali Kelas') || $user->admin_role === 'akademik' || $user->isSuperAdmin())) {
                    $hasAccess = true; break;
                }
                if ($r === 'kepala_sekolah' && ($user->hasTugasTambahan('Kepala Sekolah') || $user->isSuperAdmin())) {
                    $hasAccess = true; break;
                }
                if ($r === 'waka_kesiswaan' && ($user->hasRoleCategory('Kesiswaan') || $user->admin_role === 'bk' || $user->isSuperAdmin())) {
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
