<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\User;
use App\Services\LoginLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginLogController extends Controller
{
    protected LoginLogService $logService;

    public function __construct(LoginLogService $logService)
    {
        $this->logService = $logService;
    }

    /**
     * Halaman Utama Menu Log Akses, Peta & Keaktifan Akun
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'peta'); // 'peta', 'riwayat', 'audit'

        // 1. Ringkasan Statistik Global
        $totalLogins = LoginLog::count();
        $successCount = LoginLog::where('status', 'success')->count();
        $failedCount = LoginLog::where('status', 'failed')->count();
        $uniqueUsersCount = LoginLog::where('status', 'success')->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $totalRegisteredUsers = User::count();

        // Keaktifan 7 hari terakhir
        $activeUsers7d = LoginLog::where('status', 'success')
            ->whereNotNull('user_id')
            ->where('login_at', '>=', now()->subDays(7))
            ->distinct('user_id')
            ->count('user_id');

        $citiesCount = LoginLog::whereNotNull('city')->where('city', '!=', '')->distinct('city')->count('city');
        $suspiciousCount = LoginLog::where('is_suspicious', true)->count();

        // Hitung sesi aktif saat ini di database
        $activeSessionsCount = 0;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                $activeSessionsCount = DB::table('sessions')
                    ->whereNotNull('user_id')
                    ->where('last_activity', '>=', now()->subMinutes(120)->timestamp)
                    ->distinct('user_id')
                    ->count('user_id');
            }
        } catch (\Throwable $e) {}

        // 2. Data untuk Tab 1: Peta & Pemantauan Real-time
        $topCities = LoginLog::select('city', 'region', DB::raw('count(*) as total'))
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('city', 'region')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $recentFeed = LoginLog::with('user')
            ->latest('login_at')
            ->limit(12)
            ->get();

        $topDevices = LoginLog::select('device_type', DB::raw('count(*) as total'))
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->orderByDesc('total')
            ->get();

        // 3. Data untuk Tab 2: Riwayat Log Lengkap (Filter & Pagination)
        $riwayatQuery = LoginLog::with('user')->latest('login_at');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $riwayatQuery->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('login_input', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('region', 'like', "%{$search}%")
                  ->orWhere('browser', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $riwayatQuery->where('user_role', $request->input('role'));
        }

        if ($request->filled('status')) {
            $riwayatQuery->where('status', $request->input('status'));
        }

        if ($request->filled('period')) {
            $period = $request->input('period');
            if ($period === 'today') {
                $riwayatQuery->whereDate('login_at', today());
            } elseif ($period === '7days') {
                $riwayatQuery->where('login_at', '>=', now()->subDays(7));
            } elseif ($period === '30days') {
                $riwayatQuery->where('login_at', '>=', now()->subDays(30));
            }
        }

        $logs = $riwayatQuery->paginate(20)->withQueryString();

        // 4. Data untuk Tab 3: Audit Keaktifan Akun Terdaftar
        $userQuery = User::query();

        if ($request->filled('audit_search')) {
            $aSearch = trim($request->input('audit_search'));
            $userQuery->where(function ($q) use ($aSearch) {
                $q->where('name', 'like', "%{$aSearch}%")
                  ->orWhere('username', 'like', "%{$aSearch}%")
                  ->orWhere('email', 'like', "%{$aSearch}%")
                  ->orWhere('nip', 'like', "%{$aSearch}%");
            });
        }

        if ($request->filled('audit_role')) {
            $userQuery->where('role', $request->input('audit_role'));
        }

        // Ambil data agregat log per user untuk efisiensi tinggi
        $userLogStats = LoginLog::select(
                'user_id',
                DB::raw('count(case when status = "success" then 1 end) as total_success'),
                DB::raw('count(case when status = "failed" then 1 end) as total_failed'),
                DB::raw('max(login_at) as last_login'),
                DB::raw('GROUP_CONCAT(DISTINCT city ORDER BY city SEPARATOR ", ") as unique_cities')
            )
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $users = $userQuery->orderBy('role')->orderBy('name')->paginate(20, ['*'], 'audit_page')->withQueryString();

        // Pasangkan info keaktifan pada setiap user
        $users->getCollection()->transform(function ($u) use ($userLogStats) {
            $stat = $userLogStats->get($u->id);
            $u->total_success = $stat ? (int) $stat->total_success : 0;
            $u->total_failed  = $stat ? (int) $stat->total_failed : 0;
            $u->last_login    = $stat && $stat->last_login ? Carbon::parse($stat->last_login) : null;
            $u->cities_list   = $stat && $stat->unique_cities ? explode(', ', $stat->unique_cities) : [];

            // Status Keaktifan
            if (!$u->is_active) {
                $u->keaktifan_badge = 'Dinonaktifkan';
                $u->keaktifan_class = 'danger';
            } elseif ($u->total_failed >= 3 && (!$u->last_login || $u->last_login->lt(now()->subHours(24)))) {
                $u->keaktifan_badge = 'Bermasalah (Gagal >= 3x)';
                $u->keaktifan_class = 'warning';
            } elseif (!$u->last_login) {
                $u->keaktifan_badge = 'Belum Pernah Login';
                $u->keaktifan_class = 'secondary';
            } elseif ($u->last_login->gte(now()->subDays(3))) {
                $u->keaktifan_badge = 'Sangat Aktif';
                $u->keaktifan_class = 'success';
            } elseif ($u->last_login->gte(now()->subDays(7))) {
                $u->keaktifan_badge = 'Aktif';
                $u->keaktifan_class = 'info';
            } else {
                $u->keaktifan_badge = 'Jarang Aktif (> 7 hari)';
                $u->keaktifan_class = 'dark';
            }

            return $u;
        });

        return view('superadmin.log.index', compact(
            'activeTab',
            'totalLogins',
            'successCount',
            'failedCount',
            'uniqueUsersCount',
            'totalRegisteredUsers',
            'activeUsers7d',
            'citiesCount',
            'suspiciousCount',
            'activeSessionsCount',
            'topCities',
            'recentFeed',
            'topDevices',
            'logs',
            'users'
        ));
    }

    /**
     * Endpoint API JSON untuk Data Titik Peta (Leaflet.js)
     */
    public function geoData(Request $request)
    {
        $period = $request->query('period', 'all');
        $status = $request->query('status', 'all');

        $query = LoginLog::select(
                'city',
                'region',
                'country',
                'latitude',
                'longitude',
                DB::raw('count(*) as total_logins'),
                DB::raw('count(distinct coalesce(user_id, login_input)) as unique_users'),
                DB::raw('count(case when status = "success" then 1 end) as success_count'),
                DB::raw('count(case when status = "failed" then 1 end) as failed_count'),
                DB::raw('max(login_at) as latest_login')
            )
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($period === 'today') {
            $query->whereDate('login_at', today());
        } elseif ($period === '7days') {
            $query->where('login_at', '>=', now()->subDays(7));
        } elseif ($period === '30days') {
            $query->where('login_at', '>=', now()->subDays(30));
        }

        $points = $query->groupBy('city', 'region', 'country', 'latitude', 'longitude')->get();

        // Tambahkan ringkasan user terakhir per lokasi
        $points->transform(function ($pt) {
            $recentUsers = LoginLog::where('city', $pt->city)
                ->where('region', $pt->region)
                ->latest('login_at')
                ->limit(4)
                ->get(['user_name', 'user_role', 'status', 'login_at']);

            $pt->recent_users = $recentUsers;
            $pt->latest_login_formatted = $pt->latest_login ? Carbon::parse($pt->latest_login)->diffForHumans() : '-';
            return $pt;
        });

        return response()->json([
            'status' => 'success',
            'total_locations' => $points->count(),
            'locations' => $points,
        ]);
    }

    /**
     * Endpoint Detail Riwayat dan Lokasi Satu Pengguna (Modal Audit Trail)
     */
    public function userAudit(User $user)
    {
        $logs = LoginLog::where('user_id', $user->id)
            ->latest('login_at')
            ->limit(30)
            ->get();

        $activeSessions = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                $activeSessions = DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->orderByDesc('last_activity')
                    ->get()
                    ->map(function ($s) {
                        return [
                            'id' => $s->id,
                            'ip_address' => $s->ip_address,
                            'user_agent' => $s->user_agent,
                            'last_active' => Carbon::createFromTimestamp($s->last_activity)->diffForHumans(),
                        ];
                    });
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => (bool) $user->is_active,
                'password_plain' => $user->password_plain,
            ],
            'logs' => $logs,
            'sessions' => $activeSessions,
        ]);
    }

    /**
     * Penyelesaian Masalah Akun Langsung dari Menu Log
     */
    public function resolveUserIssue(Request $request, User $user)
    {
        $action = $request->input('action');

        if ($action === 'toggle_status') {
            $user->is_active = !$user->is_active;
            $user->save();

            $statusText = $user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';
            return back()->with('success', "Akun {$user->name} ({$user->username}) berhasil {$statusText}.");
        }

        if ($action === 'reset_password') {
            $newPassword = $request->input('new_password');
            if (empty($newPassword)) {
                $newPassword = Str::random(8);
            }

            $user->password = Hash::make($newPassword);
            $user->password_plain = $newPassword;
            $user->save();

            // Putuskan sesi lama agar user harus login dengan password baru
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                    DB::table('sessions')->where('user_id', $user->id)->delete();
                }
            } catch (\Throwable $e) {}

            return back()->with('success', "Kata sandi untuk {$user->name} berhasil diubah menjadi: '{$newPassword}'. Sesi sebelumnya telah diputus.");
        }

        if ($action === 'revoke_sessions') {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                    $deleted = DB::table('sessions')->where('user_id', $user->id)->delete();
                }
                $user->remember_token = null;
                $user->save();

                return back()->with('success', "Semua sesi aktif untuk pengguna {$user->name} berhasil diputuskan.");
            } catch (\Throwable $e) {
                return back()->with('error', "Gagal memutuskan sesi: " . $e->getMessage());
            }
        }

        return back()->with('error', 'Aksi penyelesaian masalah tidak dikenali.');
    }

    /**
     * Ekspor Log ke File CSV
     */
    public function exportLogs(Request $request)
    {
        $filename = 'log_aktivitas_login_' . date('Y-m-d_His') . '.csv';
        $logs = LoginLog::with('user')->latest('login_at')->limit(5000)->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($logs) {
            $output = fopen('php://output', 'w');
            // UTF-8 BOM untuk Excel
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, [
                'ID',
                'Waktu Login',
                'Nama Pengguna',
                'Username / Email Input',
                'Role',
                'Status',
                'Alasan Gagal',
                'IP Address',
                'Kota',
                'Provinsi',
                'Negara',
                'Koordinat Lat',
                'Koordinat Lng',
                'Perangkat',
                'Browser',
                'Sistem Operasi',
                'ISP / Provider',
                'Mencurigakan',
            ]);

            foreach ($logs as $log) {
                fputcsv($output, [
                    $log->id,
                    $log->login_at ? $log->login_at->format('Y-m-d H:i:s') : '',
                    $log->user_name,
                    $log->login_input,
                    strtoupper($log->user_role ?? '-'),
                    strtoupper($log->status),
                    $log->failure_reason ?? '-',
                    $log->ip_address,
                    $log->city ?? '-',
                    $log->region ?? '-',
                    $log->country ?? '-',
                    $log->latitude ?? '-',
                    $log->longitude ?? '-',
                    $log->device_type ?? '-',
                    $log->browser ?? '-',
                    $log->platform ?? '-',
                    $log->isp ?? '-',
                    $log->is_suspicious ? 'YA' : 'TIDAK',
                ]);
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bersihkan Log Lama
     */
    public function clearLogs(Request $request)
    {
        $type = $request->input('type', 'older_30');

        if ($type === 'older_30') {
            $count = LoginLog::where('created_at', '<', now()->subDays(30))->delete();
            return back()->with('success', "{$count} data log yang lebih lama dari 30 hari berhasil dibersihkan.");
        } elseif ($type === 'all') {
            $count = LoginLog::count();
            LoginLog::truncate();
            return back()->with('success', "Seluruh {$count} data riwayat log login berhasil dikosongkan.");
        }

        return back()->with('error', 'Pilihan pembersihan tidak valid.');
    }

    /**
     * Fitur Generate Data Sampel Realistis untuk Inisialisasi Peta
     */
    public function generateSampleData(Request $request)
    {
        $count = (int) $request->input('count', 35);
        $created = $this->logService->generateRealisticSampleLogs($count);

        return back()->with('success', "Berhasil membuat {$created} riwayat log simulasi login dari berbagai kota di Indonesia. Peta dan keaktifan akun kini siap dieksplorasi!");
    }
}
