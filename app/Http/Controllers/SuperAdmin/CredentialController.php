<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CrossAppDatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CredentialController extends Controller
{
    protected CrossAppDatabaseService $syncService;

    public function __construct(CrossAppDatabaseService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Tampilkan daftar seluruh kredensial akun pengguna (Guru, Siswa, Tendik)
     * beserta username dan password yang dapat dilihat dan di-reset.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Default tampilkan guru, tendik, dan siswa (kecualikan superadmin dari daftar default jika tidak difilter spesifik)
        if ($request->filled('role') && in_array($request->role, ['guru', 'tendik', 'siswa', 'superadmin'])) {
            $query->where('role', $request->role);
        } else {
            // Default exclude superadmin from the list for security unless explicitly selected
            $query->whereIn('role', ['guru', 'tendik', 'siswa']);
        }

        // Filter pencarian
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('username', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('nip', 'like', "%{$s}%");
            });
        }

        // Filter status aktif
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter kelas_id jika role siswa
        if ($request->role === 'siswa' && $request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate(20)->withQueryString();

        // Statistik
        $counts = [
            'total'   => User::whereIn('role', ['guru', 'tendik', 'siswa'])->count(),
            'guru'    => User::where('role', 'guru')->count(),
            'tendik'  => User::where('role', 'tendik')->count(),
            'siswa'   => User::where('role', 'siswa')->count(),
        ];

        // Database aplikasi eksternal yang tersedia untuk sinkronisasi
        $availableDbs = $this->syncService->getAvailableAppDatabases();
        $kelasList = \App\Models\Kelas::orderBy('nama')->get();

        return view('superadmin.credentials.index', compact('users', 'counts', 'availableDbs', 'kelasList'));
    }

    /**
     * Reset password akun pengguna di HilalEdu, dan opsional sinkronkan ke database aplikasi lain.
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'reset_type'   => 'required|in:custom,default_role,random',
            'new_password' => 'nullable|required_if:reset_type,custom|string|min:6',
            'sync_apps'    => 'nullable|array',
            'sync_apps.*'  => 'string',
        ], [
            'new_password.required_if' => 'Password baru wajib diisi jika memilih opsi kustom.',
            'new_password.min'         => 'Password baru minimal 6 karakter.',
        ]);

        // Tentukan password baru berdasarkan tipe reset
        $plainPassword = match ($request->reset_type) {
            'default_role' => match ($user->role) {
                'guru'   => 'Guru#2026',
                'tendik' => 'Tendik#2026',
                'siswa'  => 'Siswa#2026',
                default  => 'Hilal#2026',
            },
            'random'  => Str::random(4) . rand(1000, 9999) . '!',
            'custom'  => $request->new_password,
        };

        // Update di HilalEdu
        $user->update([
            'password'       => Hash::make($plainPassword),
            'password_plain' => $plainPassword,
        ]);

        $feedback = "Password akun {$user->name} ({$user->username}) berhasil di-reset menjadi: '{$plainPassword}'.";

        // Sinkronisasi ke database aplikasi lain jika dicentang
        $targetApps = $request->input('sync_apps', []);
        if (!empty($targetApps)) {
            $syncResults = $this->syncService->syncUserToDatabases($user, $targetApps, $plainPassword);
            $syncSuccess = [];
            $syncFailed  = [];

            foreach ($syncResults as $appKey => $res) {
                if ($res['success']) {
                    $syncSuccess[] = $res['app_name'] ?? $appKey;
                } else {
                    $syncFailed[] = ($res['app_name'] ?? $appKey) . ' (' . $res['message'] . ')';
                }
            }

            if (!empty($syncSuccess)) {
                $feedback .= " Berhasil disinkronkan ke database: " . implode(', ', $syncSuccess) . ".";
            }
            if (!empty($syncFailed)) {
                $feedback .= " Gagal sinkron ke: " . implode(', ', $syncFailed) . ".";
            }
        }

        return redirect()->back()->with('success', $feedback);
    }

    /**
     * Sinkronkan pengguna ke database aplikasi lain tanpa mengubah password.
     */
    public function syncExternal(Request $request, User $user)
    {
        $request->validate([
            'sync_apps'   => 'required|array|min:1',
            'sync_apps.*' => 'string',
        ], [
            'sync_apps.required' => 'Pilih minimal satu aplikasi target.',
        ]);

        $plainPassword = $user->password_plain ?? 'password123';
        $syncResults = $this->syncService->syncUserToDatabases($user, $request->sync_apps, $plainPassword);

        $messages = [];
        $hasError = false;
        foreach ($syncResults as $r) {
            $messages[] = $r['message'];
            if (!$r['success']) {
                $hasError = true;
            }
        }

        $allMessage = implode(' | ', $messages);

        if ($hasError) {
            return redirect()->back()->with('warning', $allMessage);
        }

        return redirect()->back()->with('success', "Sinkronisasi berhasil: " . $allMessage);
    }

    /**
     * Tampilan cetak kartu kredensial / login siswa, guru, tendik.
     */
    public function printCards(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        } else {
            $query->whereIn('role', ['guru', 'tendik', 'siswa']);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('username', 'like', "%{$s}%")
                  ->orWhere('nip', 'like', "%{$s}%");
            });
        }

        if ($request->role === 'siswa') {
            $query->with('kelas');
            if ($request->filled('kelas_id')) {
                $query->where('kelas_id', $request->kelas_id);
            }
        }

        $users = $query->orderBy('role')->orderBy('name')->get();

        return view('superadmin.credentials.print', compact('users'));
    }
}
