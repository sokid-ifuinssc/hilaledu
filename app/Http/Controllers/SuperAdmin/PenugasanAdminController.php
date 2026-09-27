<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Application;
use App\Models\AppCoordinator;
use App\Models\PengelolaAkademik;
use Illuminate\Http\Request;

class PenugasanAdminController extends Controller
{
    /**
     * Konfigurasi 6 Peran Admin Unit / Modul di HilalEdu
     */
    public static function getAdminRoleDefinitions(): array
    {
        return [
            'akademik' => [
                'role'      => 'akademik',
                'name'      => 'Admin Akademik & KBM',
                'badge'     => 'Akademik',
                'icon'      => 'bi-book-fill',
                'color'     => '#38bdf8',
                'bg_soft'   => 'rgba(56, 189, 248, 0.12)',
                'border'    => 'rgba(56, 189, 248, 0.3)',
                'desc'      => 'Mengelola jadwal pelajaran, kurikulum, beban ajar, rekap nilai siswa, presensi mengajar guru, dan kalender pendidikan.',
                'app_slug'  => 'akademik',
            ],
            'prakerin' => [
                'role'      => 'prakerin',
                'name'      => 'Admin Prakerin / PKL',
                'badge'     => 'Prakerin / PKL',
                'icon'      => 'bi-briefcase-fill',
                'color'     => '#fb923c',
                'bg_soft'   => 'rgba(251, 146, 60, 0.12)',
                'border'    => 'rgba(251, 146, 60, 0.3)',
                'desc'      => 'Mengelola kemitraan mitra industri (DU/DI), penempatan siswa magang, bimbingan, verifikasi jurnal dan penilaian PKL.',
                'app_slug'  => 'prakerin',
            ],
            'bk' => [
                'role'      => 'bk',
                'name'      => 'Admin Monitoring BK',
                'badge'     => 'Bimbingan Konseling',
                'icon'      => 'bi-shield-check',
                'color'     => '#f87171',
                'bg_soft'   => 'rgba(248, 113, 113, 0.12)',
                'border'    => 'rgba(248, 113, 113, 0.3)',
                'desc'      => 'Mengelola pencatatan pelanggaran tata tertib, akumulasi poin kedisiplinan siswa, dan progres konseling pembinaan.',
                'app_slug'  => 'monitoring-bk',
            ],
            'koperasi' => [
                'role'      => 'koperasi',
                'name'      => 'Admin Koperasi Sekolah',
                'badge'     => 'Koperasi',
                'icon'      => 'bi-shop',
                'color'     => '#fbbf24',
                'bg_soft'   => 'rgba(251, 191, 36, 0.12)',
                'border'    => 'rgba(251, 191, 36, 0.3)',
                'desc'      => 'Mengelola keanggotaan koperasi sekolah (guru, tendik, siswa), katalog produk, transaksi penjualan toko, dan kas.',
                'app_slug'  => 'koperasi',
            ],
            'keuangan' => [
                'role'      => 'keuangan',
                'name'      => 'Admin Keuangan & SPP',
                'badge'     => 'Keuangan / SPP',
                'icon'      => 'bi-cash-stack',
                'color'     => '#34d399',
                'bg_soft'   => 'rgba(52, 211, 153, 0.12)',
                'border'    => 'rgba(52, 211, 153, 0.3)',
                'desc'      => 'Mengelola pos tagihan SPP bulanan siswa, konfirmasi pembayaran, cetak kwitansi tanda terima, dan laporan kas masuk.',
                'app_slug'  => 'keuangan',
            ],
            'tracer' => [
                'role'      => 'tracer',
                'name'      => 'Admin Tracer Study (BKK)',
                'badge'     => 'Tracer Study',
                'icon'      => 'bi-mortarboard',
                'color'     => '#c084fc',
                'bg_soft'   => 'rgba(192, 132, 252, 0.12)',
                'border'    => 'rgba(192, 132, 252, 0.3)',
                'desc'      => 'Mengelola data alumni SMK, tracking karir BMW (Bekerja, Melanjutkan, Wirausaha), dan pengisian kuesioner kelulusan.',
                'app_slug'  => 'tracer-study',
            ],
            'payroll' => [
                'role'      => 'payroll',
                'name'      => 'Admin HilalPay (Penggajian)',
                'badge'     => 'HilalPay / Gaji',
                'icon'      => 'bi-cash-coin',
                'color'     => '#10b981',
                'bg_soft'   => 'rgba(16, 185, 129, 0.12)',
                'border'    => 'rgba(16, 185, 129, 0.3)',
                'desc'      => 'Mengelola master komponen gaji, penyesuaian gaji & transport guru/tendik, generate & cetak slip gaji digital, dan laporan penggajian.',
                'app_slug'  => 'payroll',
            ],
        ];
    }

    /**
     * Halaman Utama Penunjukan Admin Unit
     */
    public function index(Request $request)
    {
        $roleDefinitions = self::getAdminRoleDefinitions();

        // Query pegawai yang telah ditunjuk sebagai admin unit
        $query = User::whereNotNull('admin_role')
            ->where('admin_role', '!=', '');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_filter')) {
            $rf = $request->role_filter;
            $query->where(function($q) use ($rf) {
                $q->where('admin_role', $rf)
                  ->orWhere('admin_role', 'like', "{$rf},%")
                  ->orWhere('admin_role', 'like', "%,{$rf}")
                  ->orWhere('admin_role', 'like', "%,{$rf},%");
            });
        }

        if ($request->filled('tipe_user')) {
            $query->where('role', $request->tipe_user);
        }

        $assignedUsers = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Rekap admin saat ini per modul
        $currentAdminsByRole = [];
        foreach ($roleDefinitions as $key => $def) {
            $currentAdminsByRole[$key] = User::where(function($q) use ($key) {
                $q->where('admin_role', $key)
                  ->orWhere('admin_role', 'like', "{$key},%")
                  ->orWhere('admin_role', 'like', "%,{$key}")
                  ->orWhere('admin_role', 'like', "%,{$key},%");
            })->get();
        }

        // Daftar calon pegawai (Dewan Guru & Tenaga Kependidikan / TU)
        $candidates = User::whereIn('role', ['guru', 'tendik', 'admin'])
            ->where('is_active', true)
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $totalGuru   = User::where('role', 'guru')->count();
        $totalTendik = User::where('role', 'tendik')->count();
        $totalAdminDitunjuk = User::whereNotNull('admin_role')->where('admin_role', '!=', '')->count();

        return view('superadmin.penugasan-admin.index', compact(
            'roleDefinitions',
            'assignedUsers',
            'currentAdminsByRole',
            'candidates',
            'totalGuru',
            'totalTendik',
            'totalAdminDitunjuk'
        ));
    }

    /**
     * Tunjuk / Delegasikan peran admin unit kepada Guru atau TU
     * Mendukung penugasan multi-peran (1 pegawai bisa mengelola lebih dari 1 unit admin)
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'admin_roles'   => 'nullable|array',
            'admin_roles.*' => 'in:akademik,prakerin,bk,koperasi,keuangan,tracer,payroll',
            'admin_role'    => 'nullable|in:akademik,prakerin,bk,koperasi,keuangan,tracer,payroll',
            'keterangan'    => 'nullable|string|max:255',
            'action_mode'   => 'nullable|in:set,append',
        ], [
            'user_id.required' => 'Silakan pilih pegawai yang ingin ditunjuk.',
            'user_id.exists'   => 'Pegawai yang dipilih tidak ditemukan.',
        ]);

        $user = User::findOrFail($request->user_id);
        $roleDefinitions = self::getAdminRoleDefinitions();

        // Ambil roles dari checkbox array atau radio single fallback
        $selectedRoles = $request->input('admin_roles', []);
        if (empty($selectedRoles) && $request->filled('admin_role')) {
            $selectedRoles = [$request->input('admin_role')];
        }

        if (empty($selectedRoles) && $request->input('action_mode') !== 'append') {
            return back()->withErrors(['admin_roles' => 'Silakan pilih minimal 1 unit layanan yang ditugaskan.']);
        }

        $validKeys = array_keys($roleDefinitions);
        $selectedRoles = array_values(array_intersect($validKeys, (array)$selectedRoles));

        if ($request->input('action_mode') === 'append') {
            $finalRoles = array_values(array_unique(array_merge($user->admin_roles, $selectedRoles)));
        } else {
            $finalRoles = array_values(array_unique($selectedRoles));
        }

        // Simpan peran (bisa multi peran dipisahkan koma)
        $user->admin_role = count($finalRoles) > 0 ? implode(',', $finalRoles) : null;
        $user->save();

        // 1. Integrasi Khusus Admin Akademik (Sinkronkan izin tabel PengelolaAkademik)
        if ($user->hasAdminRole('akademik')) {
            PengelolaAkademik::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'can_manage_jadwal'          => true,
                    'can_manage_mapel'           => true,
                    'can_view_laporan_kehadiran' => true,
                    'can_view_laporan_kbm'       => true,
                    'ditunjuk_oleh_user_id'      => auth()->id(),
                    'keterangan'                 => $request->keterangan ?? 'Ditunjuk sebagai Admin Akademik oleh Superadmin',
                ]
            );
        } else {
            PengelolaAkademik::where('user_id', $user->id)->delete();
        }

        // 2. Integrasi AppCoordinator untuk sinkronisasi Pusat Modul Ekosistem
        foreach ($finalRoles as $rKey) {
            $roleInfo = $roleDefinitions[$rKey] ?? null;
            if ($roleInfo && !empty($roleInfo['app_slug'])) {
                $app = Application::where('slug', $roleInfo['app_slug'])->first();
                if ($app) {
                    AppCoordinator::updateOrCreate(
                        [
                            'application_id' => $app->id,
                            'user_id'        => $user->id,
                        ],
                        [
                            'coordinator_role' => 'admin_app',
                            'assigned_at'      => now(),
                            'assigned_by'      => auth()->id(),
                        ]
                    );
                }
            }
        }

        // 3. Sinkronkan tunjangan jabatan pada HilalPay
        \App\Models\Payroll\PayrollSetting::syncTunjanganForUser($user->fresh());

        $userType = $user->role === 'guru' ? 'Guru' : ($user->role === 'tendik' ? 'Tenaga Kependidikan (TU)' : 'Admin');
        $badges = array_map(fn($r) => $roleDefinitions[$r]['badge'] ?? ucfirst($r), $finalRoles);
        $roleStr = implode(', ', $badges);

        return redirect()->route('superadmin.penugasan-admin.index')
            ->with('success', "Berhasil memperbarui wewenang {$user->name} ({$userType}) mengelola unit: {$roleStr}. Akses panel telah diaktifkan.");
    }

    /**
     * Cabut penugasan admin unit dari pegawai
     * Jika role_to_remove diberikan, hanya mencabut role tersebut tanpa mengganggu peran admin lainnya.
     */
    public function destroy(Request $request, User $user)
    {
        $roleToRemove = $request->input('role_to_remove', $request->query('role'));
        $roleDefinitions = self::getAdminRoleDefinitions();

        if (!empty($roleToRemove)) {
            $user->removeAdminRole($roleToRemove);

            if ($roleToRemove === 'akademik') {
                PengelolaAkademik::where('user_id', $user->id)->delete();
            }

            $roleName = $roleDefinitions[$roleToRemove]['name'] ?? ucfirst($roleToRemove);
            \App\Models\Payroll\PayrollSetting::syncTunjanganForUser($user->fresh());

            return redirect()->route('superadmin.penugasan-admin.index')
                ->with('success', "Wewenang {$roleName} berhasil dicabut dari {$user->name}. Peran admin lainnya tetap aktif.");
        }

        // Cabut seluruh peran admin jika tidak dispesifikasikan
        $user->update([
            'admin_role' => null,
        ]);

        PengelolaAkademik::where('user_id', $user->id)->delete();
        AppCoordinator::where('user_id', $user->id)->delete();
        \App\Models\Payroll\PayrollSetting::syncTunjanganForUser($user->fresh());

        return redirect()->route('superadmin.penugasan-admin.index')
            ->with('success', "Seluruh wewenang admin berhasil dicabut dari {$user->name}.");
    }
}
