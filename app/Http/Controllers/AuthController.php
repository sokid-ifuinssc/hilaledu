<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $login = $request->input('email');
        $password = $request->input('password');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$fieldType => $login, 'password' => $password])) {
            $request->session()->regenerate();
            
            $user = Auth::user();

            // Cek apakah akun aktif
            if (!$user->is_active) {
                app(\App\Services\LoginLogService::class)->logFailed(
                    $login ?? '',
                    'Akun dinonaktifkan oleh Administrator',
                    $request,
                    $user
                );
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda dinonaktifkan oleh Administrator. Silakan hubungi pihak sekolah.',
                ])->onlyInput('email');
            }

            // Catat login berhasil
            app(\App\Services\LoginLogService::class)->logSuccess($user, $request);

            if ($user->isSuperAdmin()) {
                return redirect()->intended(route('superadmin.dashboard'));
            } elseif ($user->role === 'admin') {
                if (!empty($user->admin_role) && \Illuminate\Support\Facades\Route::has($user->admin_role . '.dashboard')) {
                    return redirect()->intended(route($user->admin_role . '.dashboard'));
                }
                return redirect()->intended(route('superadmin.dashboard'));
            } elseif ($user->isGuru()) {
                return redirect()->intended(route('guru.dashboard'));
            } elseif ($user->isSiswa()) {
                return redirect()->intended(route('siswa.dashboard'));
            } elseif ($user->isTendik()) {
                return redirect()->intended(route('tendik.dashboard'));
            }
            
            return redirect()->intended('/dashboard');
        }

        // Catat login gagal
        $attemptedUser = \App\Models\User::where($fieldType, $login)->first();
        app(\App\Services\LoginLogService::class)->logFailed(
            $login ?? '',
            $attemptedUser ? 'Kata sandi tidak sesuai' : 'Akun tidak terdaftar',
            $request,
            $attemptedUser
        );

        return back()->withErrors([
            'email' => 'Email atau kata sandi salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
