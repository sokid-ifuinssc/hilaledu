<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuleAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = auth()->user();

        // Algoritma Pengecekan Akses Modul:
        // 1. Pastikan pengguna sudah login. Jika belum, arahkan ke halaman login.
        if (!$user) {
            return redirect()->route('login');
        }

        // 2. Jika pengguna adalah superadmin, maka berikan akses ke semua modul tanpa batasan.
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // 3. Jika pengguna adalah admin biasa, periksa apakah 'admin_role' mereka cocok dengan modul yang sedang diakses.
        // Contoh: Admin dengan admin_role 'akademik' hanya bisa mengakses rute yang ber-middleware 'module_access:akademik'.
        if ($user->role === 'admin' && $user->admin_role === $module) {
            return $next($request);
        }

        // 4. Periksa hak akses guru
        if ($user->role === 'guru') {
            if (in_array($module, ['akademik', 'prakerin', 'bk', 'koperasi', 'tracer', 'keuangan'])) {
                return $next($request);
            }
        }

        // 5. Periksa hak akses siswa
        if ($user->role === 'siswa') {
            if (in_array($module, ['akademik', 'prakerin', 'keuangan', 'tracer', 'koperasi'])) {
                return $next($request);
            }
        }

        // 6. Periksa hak akses tendik
        if ($user->role === 'tendik') {
            if (in_array($module, ['akademik', 'keuangan', 'koperasi', 'bk', 'prakerin', 'tracer'])) {
                return $next($request);
            }
        }

        // 7. Jika pengguna tidak memiliki wewenang, tolak akses.
        return redirect()->route($user->dashboardRoute())->with('error', 'Anda tidak memiliki akses ke modul ini.');
    }
}
