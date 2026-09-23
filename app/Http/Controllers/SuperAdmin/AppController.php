<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\User;
use App\Models\AppCoordinator;
use App\Services\CrossAppDatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppController extends Controller
{
    protected CrossAppDatabaseService $syncService;

    public function __construct(CrossAppDatabaseService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Tampilkan daftar seluruh aplikasi yang terhubung, beserta status database dan SSO.
     */
    public function index()
    {
        $apps = Application::withCount(['admins', 'coordinators'])
            ->orderBy('sort_order')
            ->get();

        $totalApps   = $apps->count();
        $activeApps  = $apps->where('status', 'active')->count();
        $totalUsers  = User::count();

        // Uji status koneksi database untuk setiap aplikasi
        $dbStatus = [];
        foreach ($apps as $app) {
            if ($app->hasDatabase() && $app->slug !== 'hilaledu') {
                $dbStatus[$app->id] = $this->syncService->testConnection($app);
            }
        }

        return view('superadmin.apps.index', compact('apps', 'totalApps', 'activeApps', 'totalUsers', 'dbStatus'));
    }

    /**
     * Form penambahan aplikasi baru ("dan nanti akan buat lagi").
     */
    public function create()
    {
        return view('superadmin.apps.create');
    }

    /**
     * Simpan aplikasi baru ke database HilalEdu.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:150',
            'slug'             => 'nullable|string|max:100|unique:applications,slug',
            'url'              => 'nullable|url|max:255',
            'database_name'    => 'nullable|string|max:100',
            'icon'             => 'nullable|string|max:50',
            'color'            => 'nullable|string|max:20',
            'status'           => 'required|in:active,coming_soon,inactive',
            'description'      => 'nullable|string|max:1000',
            'features_raw'     => 'nullable|string',
            'sso_enabled'      => 'nullable|boolean',
            'auto_sync'        => 'nullable|boolean',
            'login_url'        => 'nullable|url|max:255',
            'sso_redirect_url' => 'nullable|url|max:255',
        ], [
            'name.required' => 'Nama aplikasi wajib diisi.',
            'slug.unique'   => 'Slug atau identifier aplikasi sudah digunakan.',
            'url.url'       => 'Format URL aplikasi harus berupa alamat web yang valid (contoh: http://localhost:8001).',
        ]);

        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name);

        // Format fitur dari textarea per baris atau koma
        $features = [];
        if ($request->filled('features_raw')) {
            $lines = preg_split('/[\r\n,]+/', $request->features_raw);
            foreach ($lines as $line) {
                $t = trim($line);
                if (!empty($t)) {
                    $features[] = $t;
                }
            }
        }

        $apiKey = 'hila_' . Str::random(40);

        $app = Application::create([
            'name'             => $request->name,
            'slug'             => $slug,
            'url'              => $request->url,
            'database_name'    => $request->filled('database_name') ? trim($request->database_name) : null,
            'api_key'          => $apiKey,
            'icon'             => $request->input('icon', 'bi-app'),
            'color'            => $request->input('color', '#2d8a4e'),
            'status'           => $request->status,
            'description'      => $request->description,
            'features'         => !empty($features) ? $features : null,
            'sso_enabled'      => $request->boolean('sso_enabled', true),
            'auto_sync'        => $request->boolean('auto_sync', true),
            'login_url'        => $request->login_url,
            'sso_redirect_url' => $request->sso_redirect_url,
            'sort_order'       => Application::max('sort_order') + 1,
        ]);

        $syncMsg = '';
        // Jika database diisi dan auto_sync dicentang, sinkronkan semua pengguna yang ada ke DB baru ini
        if ($app->hasDatabase() && $app->auto_sync) {
            $syncRes = $this->syncService->syncAllUsersToApp($app);
            if ($syncRes['success']) {
                $syncMsg = " Serta {$syncRes['synced_count']} pengguna berhasil disinkronkan ke database {$app->database_name}.";
            } else {
                $syncMsg = " (Catatan database: {$syncRes['message']})";
            }
        }

        return redirect()->route('superadmin.apps.index')
            ->with('success', "Aplikasi '{$app->name}' berhasil ditambahkan ke portal HilalEdu.{$syncMsg}");
    }

    /**
     * Form edit konfigurasi aplikasi.
     */
    public function edit(Application $application)
    {
        return view('superadmin.apps.edit', compact('application'));
    }

    /**
     * Simpan perubahan konfigurasi aplikasi.
     */
    public function update(Request $request, Application $application)
    {
        $request->validate([
            'name'             => 'required|string|max:150',
            'slug'             => 'required|string|max:100|unique:applications,slug,' . $application->id,
            'url'              => 'nullable|url|max:255',
            'database_name'    => 'nullable|string|max:100',
            'icon'             => 'nullable|string|max:50',
            'color'            => 'nullable|string|max:20',
            'status'           => 'required|in:active,coming_soon,inactive',
            'description'      => 'nullable|string|max:1000',
            'features_raw'     => 'nullable|string',
            'sso_enabled'      => 'nullable|boolean',
            'auto_sync'        => 'nullable|boolean',
            'login_url'        => 'nullable|url|max:255',
            'sso_redirect_url' => 'nullable|url|max:255',
        ]);

        $features = [];
        if ($request->filled('features_raw')) {
            $lines = preg_split('/[\r\n,]+/', $request->features_raw);
            foreach ($lines as $line) {
                $t = trim($line);
                if (!empty($t)) {
                    $features[] = $t;
                }
            }
        }

        $application->update([
            'name'             => $request->name,
            'slug'             => Str::slug($request->slug),
            'url'              => $request->url,
            'database_name'    => $request->filled('database_name') ? trim($request->database_name) : null,
            'icon'             => $request->input('icon', $application->icon ?? 'bi-app'),
            'color'            => $request->input('color', $application->color ?? '#2d8a4e'),
            'status'           => $request->status,
            'description'      => $request->description,
            'features'         => !empty($features) ? $features : $application->features,
            'sso_enabled'      => $request->boolean('sso_enabled', true),
            'auto_sync'        => $request->boolean('auto_sync', true),
            'login_url'        => $request->login_url,
            'sso_redirect_url' => $request->sso_redirect_url,
        ]);

        return redirect()->route('superadmin.apps.index')
            ->with('success', "Pengaturan aplikasi '{$application->name}' berhasil diperbarui.");
    }

    /**
     * Hapus aplikasi dari portal.
     */
    public function destroy(Application $application)
    {
        if ($application->slug === 'hilaledu') {
            return redirect()->back()->with('error', 'Aplikasi portal utama HilalEdu tidak dapat dihapus.');
        }

        $name = $application->name;
        $application->coordinators()->delete();
        $application->delete();

        return redirect()->route('superadmin.apps.index')
            ->with('success', "Aplikasi '{$name}' berhasil dihapus dari portal.");
    }

    /**
     * Uji koneksi ke database aplikasi tertentu.
     */
    public function testDbConnection(Application $application)
    {
        $test = $this->syncService->testConnection($application);

        if ($test['is_connected']) {
            $userCount = $test['user_count'];
            $tbl = $test['table_exists'] ? "Tabel 'users' ditemukan ({$userCount} akun pengguna)" : "Tabel 'users' belum ada (akan dibuat otomatis saat sinkronisasi)";
            return redirect()->back()->with('success', "Koneksi ke database '{$application->database_name}' BERHASIL! {$tbl}.");
        }

        return redirect()->back()->with('error', "Gagal terhubung ke database '{$application->database_name}': " . $test['error']);
    }

    /**
     * Sinkronkan seluruh pengguna ke SATU aplikasi tertentu.
     */
    public function syncUsers(Application $application)
    {
        $res = $this->syncService->syncAllUsersToApp($application);

        if ($res['success']) {
            return redirect()->back()->with('success', $res['message']);
        }

        return redirect()->back()->with('warning', $res['message'] . (!empty($res['errors']) ? ' Detail: ' . implode(', ', $res['errors']) : ''));
    }

    /**
     * Sinkronkan seluruh pengguna ke SEMUA database aplikasi yang aktif.
     */
    public function syncAllAppsUsers()
    {
        $res = $this->syncService->syncAllUsersToAllApps();

        $appsCount = $res['apps_count'];
        $totalSynced = $res['total_synced'];

        $details = [];
        foreach ($res['details'] as $appSlug => $info) {
            $details[] = "{$info['app_name']} ({$info['synced_count']} user)";
        }

        $detailStr = implode(', ', $details);

        return redirect()->back()->with('success', "Sinkronisasi massal berhasil! Total {$totalSynced} data akun disinkronkan ke {$appsCount} aplikasi ({$detailStr}).");
    }

    /**
     * Regenerate API Key / SSO Secret untuk aplikasi.
     */
    public function regenerateApiKey(Application $application)
    {
        $newKey = 'hila_' . Str::random(40);
        $application->update(['api_key' => $newKey]);

        return redirect()->back()->with('success', "API Key untuk aplikasi {$application->name} berhasil diperbarui.");
    }

    /**
     * Display detail of specific application.
     */
    /**
     * Display detail of specific application.
     */
    public function show(Application $application)
    {
        $coordinators = AppCoordinator::with('user')
            ->where('application_id', $application->id)
            ->orderBy('coordinator_role')
            ->get();

        // Seluruh staf Guru dan Tendik aktif tersedia untuk ditugaskan
        $availableUsers = User::whereIn('role', ['guru', 'tendik'])
            ->where('is_active', true)
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $coordinatorRoles = [
            'admin_app'   => 'Admin Aplikasi',
            'koordinator' => 'Koordinator Layanan',
            'pembimbing'  => 'Pembimbing',
        ];

        $dbTest = $application->hasDatabase() ? $this->syncService->testConnection($application) : null;

        return view('superadmin.apps.show', compact('application', 'coordinators', 'availableUsers', 'coordinatorRoles', 'dbTest'));
    }

    /**
     * Tugaskan Guru atau Tendik sebagai Admin / Koordinator / Pembimbing aplikasi.
     * Satu guru dapat memiliki lebih dari satu peran/tugas tambahan di aplikasi yang sama.
     */
    public function assignCoordinator(Request $request, Application $application)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'coordinator_role' => 'required|in:admin_app,koordinator,pembimbing',
        ], [
            'user_id.required'          => 'Pilih staf guru atau tendik.',
            'coordinator_role.required' => 'Pilih peran penugasan.',
        ]);

        $user = User::findOrFail($request->user_id);

        if (!in_array($user->role, ['guru', 'tendik'])) {
            return redirect()->route('superadmin.apps.show', $application)
                ->with('error', 'Hanya Guru atau Tendik yang dapat ditugaskan sebagai admin/koordinator aplikasi.');
        }

        $roleLabels = [
            'admin_app'   => 'Admin Aplikasi',
            'koordinator' => 'Koordinator Layanan',
            'pembimbing'  => 'Pembimbing',
        ];
        $roleLabel = $roleLabels[$request->coordinator_role] ?? $request->coordinator_role;

        // Cek apakah guru sudah mengemban peran spesifik tersebut di aplikasi ini
        $alreadyHasRole = AppCoordinator::where('application_id', $application->id)
            ->where('user_id', $user->id)
            ->where('coordinator_role', $request->coordinator_role)
            ->exists();

        if ($alreadyHasRole) {
            return redirect()->route('superadmin.apps.show', $application)
                ->with('error', "{$user->name} sudah ditugaskan sebagai {$roleLabel} di {$application->name}.");
        }

        AppCoordinator::create([
            'application_id'   => $application->id,
            'user_id'          => $user->id,
            'coordinator_role' => $request->coordinator_role,
            'assigned_at'      => now(),
            'assigned_by'      => auth()->id(),
        ]);

        return redirect()->route('superadmin.apps.show', $application)
            ->with('success', "{$user->name} ({$user->role}) berhasil ditugaskan sebagai {$roleLabel} di {$application->name}.");
    }

    /**
     * Cabut penugasan admin/koordinator dari aplikasi.
     * Dapat mencabut tugas spesifik per ID penugasan tanpa menghapus tugas lain milik guru.
     */
    public function removeCoordinator(Request $request, Application $application, $user)
    {
        if ($request->filled('coordinator_id')) {
            $coord = AppCoordinator::find($request->coordinator_id);
            if ($coord && $coord->application_id == $application->id) {
                $userName = $coord->user?->name ?? 'Staf';
                $roleLabel = $coord->roleLabel();
                $coord->delete();
                return redirect()->route('superadmin.apps.show', $application)
                    ->with('success', "Penugasan {$userName} sebagai {$roleLabel} berhasil dicabut dari {$application->name}.");
            }
        }

        $userModel = User::find($user);
        $userName = $userModel?->name ?? 'Staf';

        if ($request->filled('role')) {
            AppCoordinator::where('application_id', $application->id)
                ->where('user_id', $user)
                ->where('coordinator_role', $request->role)
                ->delete();
            return redirect()->route('superadmin.apps.show', $application)
                ->with('success', "Wewenang {$userName} berhasil dicabut dari {$application->name}.");
        }

        AppCoordinator::where('application_id', $application->id)
            ->where('user_id', $user)
            ->delete();

        return redirect()->route('superadmin.apps.show', $application)
            ->with('success', "Seluruh penugasan {$userName} berhasil dicabut dari {$application->name}.");
    }

    /**
     * Toggle application status (active/inactive).
     */
    public function toggleStatus(Application $application)
    {
        $newStatus = $application->status === 'active' ? 'inactive' : 'active';
        $application->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('superadmin.apps.index')
            ->with('success', "Aplikasi {$application->name} berhasil {$label}.");
    }
}
