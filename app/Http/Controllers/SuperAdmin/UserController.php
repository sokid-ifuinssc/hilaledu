<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users with search & filter.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name, username, or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $roles = ['superadmin', 'guru', 'tendik', 'siswa'];
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();

        return view('superadmin.users.index', compact('users', 'roles', 'totalUsers', 'activeUsers'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = ['superadmin', 'guru', 'tendik', 'siswa'];
        return view('superadmin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username|alpha_dash',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:superadmin,guru,tendik,siswa',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, dash, dan underscore.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Role wajib dipilih.',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['password_plain'] = $validated['password'];

        User::create($validated);

        return redirect()->route('superadmin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Show the form for editing a user.
     */
    public function edit(User $user)
    {
        $roles = ['superadmin', 'guru', 'tendik', 'siswa'];
        return view('superadmin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:superadmin,guru,tendik,siswa',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, dash, dan underscore.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Role wajib dipilih.',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Only update password if provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password_plain'] = $validated['password'];
        }

        $user->update($validated);

        return redirect()->route('superadmin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return redirect()->route('superadmin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('superadmin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Toggle user active status.
     */
    public function toggleActive(User $user)
    {
        // Prevent deactivating own account
        if ($user->id === auth()->id()) {
            return redirect()->route('superadmin.users.index')
                ->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('superadmin.users.index')
            ->with('success', "Pengguna berhasil {$status}.");
    }

    /**
     * Searchable JSON list of users for Superadmin quick-impersonate modal.
     */
    public function impersonateUsers(Request $request)
    {
        $impersonator = $this->resolveImpersonatorActor();
        if (!$impersonator) {
            abort(403, 'Hanya Superadmin yang dapat melihat daftar pratinjau user.');
        }

        $q = trim((string) $request->query('q', ''));
        $role = trim((string) $request->query('role', ''));

        $users = User::query()
            ->select(['id', 'name', 'username', 'email', 'role', 'admin_role', 'tugas_tambahan', 'jabatan_utama', 'is_active'])
            ->where('id', '!=', $impersonator->id)
            ->when($role !== '', fn($query) => $query->where('role', $role))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('username', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('jabatan_utama', 'like', "%{$q}%");
                });
            })
            ->orderByRaw("FIELD(role, 'admin', 'guru', 'tendik', 'siswa', 'guru_bk', 'waka_kesiswaan', 'kaprog', 'wali_kelas', 'kepala_sekolah', 'superadmin') ASC")
            ->orderBy('name')
            ->limit(60)
            ->get()
            ->map(function (User $u) {
                $tugas = is_array($u->tugas_tambahan) ? implode(', ', array_filter($u->tugas_tambahan)) : (string) ($u->tugas_tambahan ?? '');
                return [
                    'id'            => $u->id,
                    'name'          => $u->name,
                    'username'      => $u->username,
                    'role'          => $u->role,
                    'admin_role'    => $u->admin_role,
                    'tugas'         => $tugas,
                    'jabatan'       => $u->jabatan_utama,
                    'is_active'     => (bool) $u->is_active,
                    'is_current'    => $u->id === auth()->id(),
                    'url'           => route('impersonate.start', $u),
                ];
            });

        return response()->json(['users' => $users]);
    }

    /**
     * Impersonate any target user so Superadmin can inspect their menu & detect errors.
     */
    public function impersonateStart(Request $request, User $user)
    {
        $impersonator = $this->resolveImpersonatorActor();
        if (!$impersonator) {
            abort(403, 'Hanya Superadmin yang diizinkan menggunakan fitur Lihat Sebagai User.');
        }

        if ($user->id === $impersonator->id) {
            return $this->impersonateStop($request);
        }

        session()->forget('active_role');
        Auth::login($user);
        $request->session()->regenerate();
        session([
            'impersonator_id' => $impersonator->id,
            'impersonated_at' => now()->format('H:i'),
        ]);

        return redirect($this->resolveDashboardUrl($user))
            ->with('success', "Anda sedang melihat aplikasi sebagai {$user->name} (" . strtoupper(str_replace('_', ' ', $user->role)) . ").");
    }

    /**
     * Stop impersonation and restore the original Superadmin session.
     */
    public function impersonateStop(Request $request)
    {
        $impersonatorId = session('impersonator_id');
        if (!$impersonatorId) {
            return redirect()->route('home');
        }

        $originalUser = User::find($impersonatorId);
        session()->forget(['impersonator_id', 'impersonated_at', 'active_role']);

        if (!$originalUser || !$originalUser->isSuperAdmin()) {
            return redirect()->route('home')
                ->with('error', 'Sesi Superadmin asal tidak ditemukan.');
        }

        Auth::login($originalUser);
        $request->session()->regenerate();

        return redirect()->route('superadmin.users.index')
            ->with('success', "Berhasil kembali ke akun Superadmin ({$originalUser->name}).");
    }

    private function resolveImpersonatorActor(): ?User
    {
        $impersonatorId = session('impersonator_id');
        if ($impersonatorId) {
            $orig = User::find($impersonatorId);
            return ($orig && $orig->isSuperAdmin()) ? $orig : null;
        }

        $current = auth()->user();
        return ($current && $current->isSuperAdmin()) ? $current : null;
    }

    private function resolveDashboardUrl(User $user): string
    {
        if ($user->isSuperAdmin()) {
            return route('superadmin.dashboard');
        }
        if ($user->role === 'admin') {
            if ($user->admin_role === 'payroll') {
                return route('superadmin.payroll.dashboard');
            }
            if ($user->hasAnyAdminRole(['sertifikat_kepesantrenan', 'sertifikat_kewirausahaan', 'skl_ijazah', 'dokumen'])) {
                return route('dokumen-sertifikat.index');
            }
            if (!empty($user->admin_role) && Route::has($user->admin_role . '.dashboard')) {
                return route($user->admin_role . '.dashboard');
            }
            return route('superadmin.dashboard');
        }
        if ($user->role === 'guru') return route('guru.dashboard');
        if ($user->role === 'siswa') return route('siswa.dashboard');
        if ($user->role === 'tendik') return route('tendik.dashboard');
        if ($user->role === 'guru_bk') return route('bk.dashboard');
        if ($user->role === 'kaprog') return route('kaprog.dashboard');
        if ($user->role === 'wali_kelas') return route('walikelas.dashboard');
        if ($user->role === 'kepala_sekolah') return route('kepsek.dashboard');
        if ($user->role === 'waka_kesiswaan') return route('waka.dashboard');

        return route('home');
    }
}
