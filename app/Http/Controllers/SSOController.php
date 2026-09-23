<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SSOController extends Controller
{
    /**
     * API Login untuk SSO
     */
    public function login(Request $request)
    {
        $login = $request->input('username') ?? $request->input('email');
        $password = $request->input('password');

        if (!$login || !$password) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Username dan password wajib diisi.',
            ], 422);
        }

        $user = User::where('username', $login)
            ->orWhere('email', $login)
            ->first();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pengguna tidak ditemukan.',
            ], 401);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Password yang Anda masukkan salah.',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun Anda tidak aktif.',
            ], 403);
        }

        $appSlug = $request->input('app_slug');
        $app = null;
        $appRoles = [];
        if ($appSlug) {
            $app = Application::where('slug', $appSlug)->first();
            if ($app) {
                $appRoles = \App\Models\AppCoordinator::where('application_id', $app->id)
                    ->where('user_id', $user->id)
                    ->pluck('coordinator_role')
                    ->toArray();
            }
        }

        $token = Str::random(64);
        Cache::put('sso_token_' . $token, [
            'user_id'   => $user->id,
            'app_id'    => $app?->id,
            'app_roles' => $appRoles,
        ], now()->addHours(2));

        return response()->json([
            'status'     => 'success',
            'message'    => 'Autentikasi berhasil.',
            'token'      => $token,
            'expires_at' => now()->addHours(2)->toIso8601String(),
            'user'       => [
                'id'             => $user->id,
                'name'           => $user->name,
                'username'       => $user->username,
                'email'          => $user->email,
                'role'           => $user->role,
                'jabatan_utama'  => $user->jabatan_utama,
                'tugas_tambahan' => $user->tugas_tambahan,
                'app_roles'      => $appRoles,
                'app_role'       => $appRoles[0] ?? null,
            ],
            'app'        => [
                'name' => $app ? $app->name : 'HilalEdu',
                'slug' => $app ? $app->slug : 'hilaledu',
            ],
        ]);
    }

    /**
     * API Validasi Token SSO
     */
    public function validateToken(Request $request)
    {
        $token = $request->input('token');

        if (!$token) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token wajib disertakan.',
            ], 422);
        }

        $cached = Cache::get('sso_token_' . $token);
        if (!$cached) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Token SSO tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $userId = is_array($cached) ? $cached['user_id'] : $cached;
        $appRoles = is_array($cached) ? ($cached['app_roles'] ?? []) : [];

        $user = User::find($userId);
        if (!$user || !$user->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pengguna tidak ditemukan atau tidak aktif.',
            ], 401);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Token SSO valid.',
            'user'    => [
                'id'             => $user->id,
                'name'           => $user->name,
                'username'       => $user->username,
                'email'          => $user->email,
                'role'           => $user->role,
                'jabatan_utama'  => $user->jabatan_utama,
                'tugas_tambahan' => $user->tugas_tambahan,
                'app_roles'      => $appRoles,
                'app_role'       => $appRoles[0] ?? null,
            ],
        ]);
    }

    /**
     * API Informasi Aplikasi SSO
     */
    public function appInfo(Request $request)
    {
        $slug = $request->query('slug');
        $app = Application::where('slug', $slug)->first();

        if (!$app) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Aplikasi tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'app'    => [
                'name'        => $app->name,
                'slug'        => $app->slug,
                'url'         => $app->url,
                'description' => $app->description,
            ],
        ]);
    }

    /**
     * Launcher SSO Portal menuju aplikasi tujuan
     */
    public function launch(Request $request, $app)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Dapatkan objek dan slug aplikasi baik jika dilempar sebagai instance model, id, atau slug string
        $appObj = is_object($app)
            ? $app
            : (Application::where('slug', $app)->orWhere('id', $app)->first());
        $slug = $appObj ? $appObj->slug : $app;

        // 1. Khusus modul HilalPay / Penggajian
        if ($slug === 'hilalpay' || $slug === 'payroll') {
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.payroll.dashboard');
            } elseif ($user->role === 'guru') {
                return redirect()->route('guru.payroll.index');
            } elseif ($user->role === 'tendik') {
                return redirect()->route('tendik.payroll.index');
            }
            return redirect()->route('superadmin.payroll.dashboard');
        }

        // 2. Terbitkan token SSO
        $token = Str::random(64);
        Cache::put('sso_token_' . $token, $user->id, now()->addMinutes(15));

        // 3. Jika aplikasi memiliki target URL eksternal (http/https)
        if ($appObj && str_starts_with($appObj->url, 'http')) {
            $separator = str_contains($appObj->url, '?') ? '&' : '?';
            
            // Format callback untuk siakademik & tracer-study
            if ($slug === 'akademik' || $slug === 'tracer-study') {
                $baseUrl = rtrim(explode('?', $appObj->url)[0], '/');
                if (!str_ends_with($baseUrl, '/auth/sso-callback')) {
                    $baseUrl .= '/auth/sso-callback';
                }
                $target = $baseUrl . '?' . http_build_query([
                    'sso_token' => $token,
                    'username'  => $user->username,
                ]);
            } else {
                $target = $appObj->url . $separator . http_build_query([
                    'sso_token' => $token,
                    'username'  => $user->username,
                ]);
            }

            return redirect()->away($target);
        }

        // 4. Integrasi Internal Monolith (Internal routes)
        switch ($slug) {
            case 'akademik':
                return redirect()->route('akademik.dashboard');
            case 'monitoring-bk':
            case 'bk':
                return redirect()->route('bk.dashboard');
            case 'prakerin':
            case 'laporanprakerin':
                return redirect()->route('prakerin.dashboard');
            case 'keuangan':
                return redirect()->route('keuangan.dashboard');
            case 'koperasi':
                return redirect()->route('koperasi.dashboard');
            case 'tracer':
                return redirect()->route('tracer.dashboard');
            default:
                if ($appObj && str_starts_with($appObj->url, '/')) {
                    return redirect($appObj->url);
                }
                return redirect()->route('superadmin.dashboard');
        }
    }
}
