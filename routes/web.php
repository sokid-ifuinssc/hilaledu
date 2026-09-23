<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\SuperAdmin\AppController as SuperAdminAppController;
use App\Http\Controllers\SuperAdmin\CredentialController;
use App\Http\Controllers\SuperAdmin\DatabaseController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\MingguEfektifController;
use App\Http\Controllers\Guru\RencanaPembelajaranController;
use App\Http\Controllers\Guru\LaporanKbmController;
use App\Http\Controllers\Guru\AbsensiGuruController;
use App\Http\Controllers\Guru\PerangkatAjarController;
use App\Http\Controllers\Guru\CutiController;
use App\Http\Controllers\Guru\KegiatanSekolahController;
use App\Http\Controllers\Guru\KeluhanKbmController;
use App\Http\Controllers\Guru\ProfileController as GuruProfileController;
use App\Http\Controllers\Siswa\PortalController as SiswaPortalController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\KurikulumController;
use App\Http\Controllers\Admin\JadwalMatrixController;
use App\Http\Controllers\Admin\AdminKeluhanController;
use App\Http\Controllers\Admin\DirectoryController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\KalenderAkademikController;
use App\Http\Controllers\Guru\PenugasanGuruController;
use App\Http\Controllers\Guru\RealisasiTugasTambahanController;
use App\Http\Controllers\Guru\RekapPresensiController;
use App\Http\Controllers\Admin\AdminRekapPresensiController;
use App\Http\Controllers\Piket\PetugasPiketController;
use App\Http\Controllers\Admin\AdminPiketController;
use App\Http\Controllers\Tendik\DashboardController as TendikDashboardController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\BK;
use App\Http\Controllers\Kaprog;
use App\Http\Controllers\KepalaSekolah;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Siswa;
use App\Http\Controllers\WaliKelas;

// HILAL EDU BASE ROUTES
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        } elseif ($user->role === 'admin' || !empty($user->admin_role)) {
            if (!empty($user->admin_role) && \Illuminate\Support\Facades\Route::has($user->admin_role . '.dashboard')) {
                return redirect()->route($user->admin_role . '.dashboard');
            }
            return redirect()->route('superadmin.dashboard');
        } elseif ($user->isGuru()) {
            return redirect()->route('guru.dashboard');
        } elseif ($user->isSiswa()) {
            return redirect()->route('siswa.dashboard');
        } elseif ($user->isTendik()) {
            return redirect()->route('tendik.dashboard');
        }
        
        return redirect('/dashboard');
    }

    return redirect()->route('login');
})->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// SSO Launcher & App Dispatcher
Route::middleware('auth')->get('/sso/launch/{app}', [\App\Http\Controllers\SSOController::class, 'launch'])->name('sso.launch');

// ============================================================
// SUPER ADMIN Routes (Pusat Kendali Seluruh Aplikasi HilalEdu)
// ============================================================
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'superadmin'])->name('dashboard');

    // Manajemen Pengguna (Users)
    Route::resource('users', SuperAdminUserController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::patch('/users/{user}/toggle-active', [SuperAdminUserController::class, 'toggleActive'])->name('users.toggle-active');

    // ---- Manajemen Guru ----
    Route::get('/guru/export',    [GuruController::class, 'export'])->name('guru.export');
    Route::get('/guru/import',    [GuruController::class, 'importForm'])->name('guru.import');
    Route::post('/guru/import',   [GuruController::class, 'import'])->name('guru.import.process');
    Route::get('/guru/template',  [GuruController::class, 'downloadTemplate'])->name('guru.template');
    Route::resource('guru', GuruController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::patch('/guru/{guru}/toggle-active', [GuruController::class, 'toggleActive'])->name('guru.toggle-active');

    // ---- Manajemen Tendik ----
    Route::get('/tendik/export',    [\App\Http\Controllers\Admin\TendikController::class, 'export'])->name('tendik.export');
    Route::get('/tendik/import',    [\App\Http\Controllers\Admin\TendikController::class, 'importForm'])->name('tendik.import');
    Route::post('/tendik/import',   [\App\Http\Controllers\Admin\TendikController::class, 'import'])->name('tendik.import.process');
    Route::get('/tendik/template',  [\App\Http\Controllers\Admin\TendikController::class, 'downloadTemplate'])->name('tendik.template');
    Route::resource('tendik', \App\Http\Controllers\Admin\TendikController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::patch('/tendik/{tendik}/toggle-active', [\App\Http\Controllers\Admin\TendikController::class, 'toggleActive'])->name('tendik.toggle-active');

    // ---- Manajemen Siswa ----
    Route::get('/siswa/export',    [SiswaController::class, 'export'])->name('siswa.export');
    Route::get('/siswa/import',    [SiswaController::class, 'importForm'])->name('siswa.import');
    Route::post('/siswa/import',   [SiswaController::class, 'import'])->name('siswa.import.process');
    Route::get('/siswa/template',  [SiswaController::class, 'downloadTemplate'])->name('siswa.template');
    Route::resource('siswa', SiswaController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::post('/siswa/migrasi',  [SiswaController::class, 'migrasiProses'])->name('siswa.migrasi');

    // ---- Penunjukan Admin Unit (Guru & TU) ----
    Route::get('/penugasan-admin',              [\App\Http\Controllers\SuperAdmin\PenugasanAdminController::class, 'index'])->name('penugasan-admin.index');
    Route::post('/penugasan-admin',             [\App\Http\Controllers\SuperAdmin\PenugasanAdminController::class, 'store'])->name('penugasan-admin.store');
    Route::delete('/penugasan-admin/{user}',    [\App\Http\Controllers\SuperAdmin\PenugasanAdminController::class, 'destroy'])->name('penugasan-admin.destroy');

    // ---- Pengaturan Rombel Kelas ----
    Route::prefix('rombel')->name('rombel.')->group(function () {
        Route::get('/',                    [\App\Http\Controllers\Admin\RombelController::class, 'index'])->name('index');
        Route::post('/pindah',             [\App\Http\Controllers\Admin\RombelController::class, 'pindah'])->name('pindah');
        Route::get('/{kela}',              [\App\Http\Controllers\Admin\RombelController::class, 'show'])->name('show');
        Route::post('/{kela}/assign',      [\App\Http\Controllers\Admin\RombelController::class, 'assign'])->name('assign');
        Route::post('/{kela}/remove-bulk', [\App\Http\Controllers\Admin\RombelController::class, 'removeBulk'])->name('remove-bulk');
        Route::post('/{kela}/remove/{user}', [\App\Http\Controllers\Admin\RombelController::class, 'remove'])->name('remove');
    });

    // ---- Data Master ----
    Route::prefix('master')->name('master.')->group(function () {
        Route::get('/export',    [MasterController::class, 'export'])->name('export');
        Route::get('/import',    [MasterController::class, 'importForm'])->name('import');
        Route::post('/import',   [MasterController::class, 'import'])->name('import.process');
        Route::get('/template',  [MasterController::class, 'downloadTemplate'])->name('template');

        Route::get('/tahun-ajaran',  [MasterController::class, 'tahunAjaran'])->name('tahun-ajaran');
        Route::post('/tahun-ajaran', [MasterController::class, 'storeTahunAjaran'])->name('tahun-ajaran.store');
        Route::patch('/tahun-ajaran/{tahunAjaran}/set-aktif', [MasterController::class, 'setAktifTahunAjaran'])->name('tahun-ajaran.set-aktif');
        Route::delete('/tahun-ajaran/{tahunAjaran}', [MasterController::class, 'destroyTahunAjaran'])->name('tahun-ajaran.destroy');

        Route::get('/jurusan',           [MasterController::class, 'jurusan'])->name('jurusan');
        Route::post('/jurusan',          [MasterController::class, 'storeJurusan'])->name('jurusan.store');
        Route::put('/jurusan/{jurusan}', [MasterController::class, 'updateJurusan'])->name('jurusan.update');
        Route::delete('/jurusan/{jurusan}', [MasterController::class, 'destroyJurusan'])->name('jurusan.destroy');

        Route::get('/kelas',           [MasterController::class, 'kelas'])->name('kelas');
        Route::post('/kelas',          [MasterController::class, 'storeKelas'])->name('kelas.store');
        Route::put('/kelas/{kela}',    [MasterController::class, 'updateKelas'])->name('kelas.update');
        Route::delete('/kelas/{kela}', [MasterController::class, 'destroyKelas'])->name('kelas.destroy');

        Route::get('/sekolah', [MasterController::class, 'pengaturanSekolah'])->name('sekolah');
        Route::put('/sekolah', [MasterController::class, 'updatePengaturanSekolah'])->name('sekolah.update');

        Route::get('/tugas-tambahan',                        [MasterController::class, 'tugasTambahan'])->name('tugas-tambahan');
        Route::post('/tugas-tambahan',                       [MasterController::class, 'storeTugasTambahan'])->name('tugas-tambahan.store');
        Route::put('/tugas-tambahan/{tugasTambahan}',        [MasterController::class, 'updateTugasTambahan'])->name('tugas-tambahan.update');
        Route::delete('/tugas-tambahan/{tugasTambahan}',     [MasterController::class, 'destroyTugasTambahan'])->name('tugas-tambahan.destroy');
    });

    // ---- Kredensial & Reset Password Pengguna (Guru, Siswa, Tendik) ----
    Route::get('/credentials', [CredentialController::class, 'index'])->name('credentials.index');
    Route::post('/credentials/{user}/reset', [CredentialController::class, 'resetPassword'])->name('credentials.reset');
    Route::post('/credentials/{user}/sync-external', [CredentialController::class, 'syncExternal'])->name('credentials.sync-external');
    Route::get('/credentials/print', [CredentialController::class, 'printCards'])->name('credentials.print');

    // ---- Manajemen Aplikasi, Pengaturan SSO & Penugasan Admin/Koordinator ----
    Route::get('/apps', [SuperAdminAppController::class, 'index'])->name('apps.index');
    Route::get('/apps/create', [SuperAdminAppController::class, 'create'])->name('apps.create');
    Route::post('/apps', [SuperAdminAppController::class, 'store'])->name('apps.store');
    Route::post('/apps/sync-all-users', [SuperAdminAppController::class, 'syncAllAppsUsers'])->name('apps.sync-all-users');
    Route::get('/apps/{application}/edit', [SuperAdminAppController::class, 'edit'])->name('apps.edit');
    Route::put('/apps/{application}', [SuperAdminAppController::class, 'update'])->name('apps.update');
    Route::delete('/apps/{application}', [SuperAdminAppController::class, 'destroy'])->name('apps.destroy');
    Route::get('/apps/{application}', [SuperAdminAppController::class, 'show'])->name('apps.show');
    Route::patch('/apps/{application}/toggle-status', [SuperAdminAppController::class, 'toggleStatus'])->name('apps.toggle-status');
    Route::post('/apps/{application}/test-db', [SuperAdminAppController::class, 'testDbConnection'])->name('apps.test-db');
    Route::post('/apps/{application}/sync-users', [SuperAdminAppController::class, 'syncUsers'])->name('apps.sync-users');
    Route::post('/apps/{application}/regenerate-api-key', [SuperAdminAppController::class, 'regenerateApiKey'])->name('apps.regenerate-api-key');
    Route::post('/apps/{application}/assign-coordinator', [SuperAdminAppController::class, 'assignCoordinator'])->name('apps.assign-coordinator');
    Route::delete('/apps/{application}/coordinators/{user}', [SuperAdminAppController::class, 'removeCoordinator'])->name('apps.remove-coordinator');

    // ---- Backup & Restore Database ----
    Route::prefix('database')->name('database.')->group(function () {
        Route::get('/',                 [DatabaseController::class, 'index'])->name('index');
        Route::get('/export',           [DatabaseController::class, 'export'])->name('export');
        Route::post('/import',          [DatabaseController::class, 'import'])->name('import');
        Route::get('/download/{file}',  [DatabaseController::class, 'downloadBackup'])->name('download');
        Route::delete('/delete/{file}', [DatabaseController::class, 'deleteBackup'])->name('delete');
        Route::post('/restore/{file}',  [DatabaseController::class, 'restoreBackup'])->name('restore');
    });

    // ============================================================
    // HilalPay: Sistem Penggajian & Bisyarah Guru & Tendik
    // ============================================================
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollDashboardController::class, 'index'])->name('dashboard');

        // Master Komponen Gaji
        Route::resource('komponen', \App\Http\Controllers\SuperAdmin\Payroll\PayrollKomponenController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::patch('/komponen/{komponen}/toggle-active', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollKomponenController::class, 'toggleActive'])->name('komponen.toggle-active');

        // Pengaturan Gaji Pegawai
        Route::get('/setting', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollSettingController::class, 'index'])->name('setting.index');
        Route::get('/setting/{user}/edit', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollSettingController::class, 'edit'])->name('setting.edit');
        Route::put('/setting/{user}', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollSettingController::class, 'update'])->name('setting.update');
        Route::post('/setting/sync-tugas-tambahan', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollSettingController::class, 'syncTugasTambahan'])->name('setting.sync-tugas-tambahan');

        // Periode & Proses Penggajian
        Route::get('/periode', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollPeriodeController::class, 'index'])->name('periode.index');
        Route::get('/periode/create', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollPeriodeController::class, 'create'])->name('periode.create');
        Route::post('/periode', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollPeriodeController::class, 'store'])->name('periode.store');
        Route::get('/periode/{periode}', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollPeriodeController::class, 'show'])->name('periode.show');
        Route::delete('/periode/{periode}', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollPeriodeController::class, 'destroy'])->name('periode.destroy');
        Route::post('/periode/{periode}/generate', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollPeriodeController::class, 'generate'])->name('periode.generate');
        Route::post('/periode/{periode}/finalize', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollPeriodeController::class, 'finalize'])->name('periode.finalize');
        Route::post('/periode/{periode}/mark-paid', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollPeriodeController::class, 'markPaid'])->name('periode.mark-paid');
        Route::get('/periode/{periode}/rekap', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollPeriodeController::class, 'printRekap'])->name('periode.rekap');
        Route::get('/periode/{periode}/slip/{payroll}', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollPeriodeController::class, 'printSlip'])->name('periode.slip');
        Route::get('/periode/{periode}/slip/{payroll}/edit', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollPeriodeController::class, 'editSlip'])->name('periode.slip.edit');
        Route::put('/periode/{periode}/slip/{payroll}', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollPeriodeController::class, 'updateSlip'])->name('periode.slip.update');
        
        // Laporan Penggajian
        Route::get('/laporan', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollReportController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/print', [\App\Http\Controllers\SuperAdmin\Payroll\PayrollReportController::class, 'print'])->name('laporan.print');
    });
});

// ============================================================
// MODUL APLIKASI HILALEDU (Monolith)
// ============================================================
Route::middleware(['auth'])->group(function () {

    Route::prefix('prakerin')->name('prakerin.')->middleware('module_access:prakerin')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Prakerin\DashboardController::class, 'index'])->name('dashboard');
        
        // Master Mitra DU/DI & Pembimbing
        Route::resource('dudi', \App\Http\Controllers\Prakerin\DudiController::class);
        Route::resource('pembimbing-dudi', \App\Http\Controllers\Prakerin\PembimbingDudiController::class);
        Route::post('/pembimbing-dudi/{pembimbingDudi}/toggle', [\App\Http\Controllers\Prakerin\PembimbingDudiController::class, 'toggleStatus'])->name('pembimbing-dudi.toggle');

        // Master Periode & Penempatan Prakerin Siswa
        Route::resource('periode', \App\Http\Controllers\Prakerin\PeriodePrakerinController::class);
        Route::resource('penempatan', \App\Http\Controllers\Prakerin\PenempatanController::class);

        // Industri & Jurnal Harian (Legacy & Live)
        Route::resource('industri', \App\Http\Controllers\Prakerin\IndustriController::class);
        Route::resource('jurnal', \App\Http\Controllers\Prakerin\JurnalPrakerinController::class);
        Route::post('/jurnal/{jurnal}/approve', [\App\Http\Controllers\Prakerin\JurnalPrakerinController::class, 'approve'])->name('jurnal.approve');
        Route::post('/jurnal/{jurnal}/reject', [\App\Http\Controllers\Prakerin\JurnalPrakerinController::class, 'reject'])->name('jurnal.reject');

        // Laporan Prakerin
        Route::get('/laporan', [\App\Http\Controllers\Prakerin\LaporanPrakerinController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/print', [\App\Http\Controllers\Prakerin\LaporanPrakerinController::class, 'print'])->name('laporan.print');
        Route::get('/laporan/export', [\App\Http\Controllers\Prakerin\LaporanPrakerinController::class, 'export'])->name('laporan.export');
    });
    Route::prefix('keuangan')->name('keuangan.')->middleware('module_access:keuangan')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Keuangan\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('tagihan', \App\Http\Controllers\Keuangan\TagihanController::class);
        Route::resource('pembayaran', \App\Http\Controllers\Keuangan\PembayaranController::class);
    });
    Route::prefix('koperasi')->name('koperasi.')->middleware('module_access:koperasi')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Koperasi\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('anggota', \App\Http\Controllers\Koperasi\KoperasiAnggotaController::class);
        Route::resource('transaksi', \App\Http\Controllers\Koperasi\KoperasiTransaksiController::class);
    });

    Route::prefix('tracer')->name('tracer.')->middleware('module_access:tracer')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Tracer\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('alumni', \App\Http\Controllers\Tracer\TracerAlumniController::class);
        Route::resource('kuesioner', \App\Http\Controllers\Tracer\TracerKuesionerController::class);
    });
});

// ============================================================
// Redirect Fallback dari rute lama /admin/* ke /superadmin/*
// ============================================================
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn() => redirect()->route('superadmin.dashboard'))->name('dashboard');
    Route::get('/guru', fn() => redirect()->route('superadmin.guru.index'))->name('guru.index');
    Route::get('/guru/create', fn() => redirect()->route('superadmin.guru.create'))->name('guru.create');
    Route::get('/siswa', fn() => redirect()->route('superadmin.siswa.index'))->name('siswa.index');
    Route::get('/siswa/create', fn() => redirect()->route('superadmin.siswa.create'))->name('siswa.create');
    Route::get('/master/tahun-ajaran', fn() => redirect()->route('superadmin.master.tahun-ajaran'))->name('master.tahun-ajaran');
    Route::get('/master/jurusan', fn() => redirect()->route('superadmin.master.jurusan'))->name('master.jurusan');
    Route::get('/master/kelas', fn() => redirect()->route('superadmin.master.kelas'))->name('master.kelas');
    Route::get('/apps', fn() => redirect()->route('superadmin.apps.index'))->name('apps.index');
    Route::get('/apps/{application}', fn($app) => redirect()->route('superadmin.apps.show', $app))->name('apps.show');
    Route::get('/database', fn() => redirect()->route('superadmin.database.index'))->name('database.index');
    Route::get('/{any}', fn() => redirect()->route('superadmin.dashboard'))->where('any', '.*');
});

// ============================================================
// Redirect Fallback dari rute lama /operator/* ke login
// ============================================================
Route::get('/operator/{any?}', fn() => redirect()->route('login'))->where('any', '.*');

// ============================================================
// GURU Routes
// ============================================================
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'guru'])->name('dashboard');
    Route::get('/profile', [App\Http\Controllers\Guru\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\Guru\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/payroll', [\App\Http\Controllers\Guru\GuruPayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/{payroll}/print', [\App\Http\Controllers\Guru\GuruPayrollController::class, 'print'])->name('payroll.print');
});

// ============================================================
// TENDIK Routes
// ============================================================
Route::middleware(['auth', 'role:tendik'])->prefix('tendik')->name('tendik.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'tendik'])->name('dashboard');
    Route::get('/profile', [\App\Http\Controllers\Tendik\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Tendik\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/payroll', [\App\Http\Controllers\Tendik\TendikPayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/{payroll}/print', [\App\Http\Controllers\Tendik\TendikPayrollController::class, 'print'])->name('payroll.print');
});

// ============================================================
// SISWA Routes
// ============================================================
Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'siswa'])->name('dashboard');
    Route::get('/profile', [App\Http\Controllers\Siswa\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\Siswa\ProfileController::class, 'update'])->name('profile.update');
});

// SIAKADEMIK ROUTES
/*
|--------------------------------------------------------------------------
| Web Routes - SI-AKAD HILAL (SMK Plus Al-Hilal Arjawinangun)
|--------------------------------------------------------------------------
*/

// Root


// Autentikasi Mandiri & Single Sign-On (SSO) HilalEdu


// Callback SSO dari Portal HilalEdu (Terbuka untuk login direct dari token launcher)


Route::middleware('auth')->group(function () {
    
    Route::post('/switch-role', [AuthController::class, 'switchRole'])->name('switch-role');

    // Presensi Harian Siswa (Siswa, Ketua Kelas, Piket, Walikelas, dsb.)
    Route::prefix('presensi-harian')->name('presensi-harian.')->group(function () {
        Route::get('/', [\App\Http\Controllers\PresensiHarianController::class, 'index'])->name('index');
        Route::post('/hadir', [\App\Http\Controllers\PresensiHarianController::class, 'absenHadir'])->name('absen-hadir');
        Route::post('/absen-kelas', [\App\Http\Controllers\PresensiHarianController::class, 'absenKelas'])->name('absen-kelas');
        Route::post('/set-ketua-kelas', [\App\Http\Controllers\PresensiHarianController::class, 'setKetuaKelas'])->name('set-ketua-kelas');
    });

    // Shared API Perhitungan Kalender & Minggu Efektif (Akses Terbuka untuk Guru & Pengelola)
    Route::get('/kalender/efektif-api/{kalender?}', [KalenderAkademikController::class, 'getCalculatedEfektif'])->name('kalender.efektif-api');
    Route::get('/monitoring-kelas/data', [GuruDashboardController::class, 'monitoringData'])->name('monitoring-kelas.data');

    // =========================================================================
    // MODUL GURU & TUGAS TAMBAHAN (WAKA, KAPROG, WALIKELAS, BK, KEPALA SEKOLAH)
    // =========================================================================
    Route::prefix('guru')->name('guru.')->group(function () {
        // Beranda Realtime Guru (Menu 9)
        Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
        Route::get('/monitoring-kelas/data', [GuruDashboardController::class, 'monitoringData'])->name('monitoring-kelas.data');

        // 0. Menu Kalender Akademik Sekolah (Acuan Hari Libur & KBM)
        Route::get('/kalender', [KalenderAkademikController::class, 'index'])->name('kalender.index');
        Route::get('/kalender/{kalender}/print', [KalenderAkademikController::class, 'print'])->name('kalender.print');

        // 1. Menu Minggu Efektif (Otomatis)
        Route::get('/minggu-efektif', [MingguEfektifController::class, 'index'])->name('minggu-efektif.index');
        Route::post('/minggu-efektif/sync', [MingguEfektifController::class, 'sync'])->name('minggu-efektif.sync');
        Route::get('/minggu-efektif/create', [MingguEfektifController::class, 'create'])->name('minggu-efektif.create');
        Route::post('/minggu-efektif', [MingguEfektifController::class, 'store'])->name('minggu-efektif.store');
        Route::get('/minggu-efektif/{mingguEfektif}', [MingguEfektifController::class, 'show'])->name('minggu-efektif.show');
        Route::get('/minggu-efektif/{mingguEfektif}/print', [MingguEfektifController::class, 'print'])->name('minggu-efektif.print');
        Route::delete('/minggu-efektif/{mingguEfektif}', [MingguEfektifController::class, 'destroy'])->name('minggu-efektif.destroy');

        // 2. Menu Rencana Pembelajaran (CP, TP, RPP Kurikulum Merdeka)
        Route::get('/rencana-pembelajaran', [RencanaPembelajaranController::class, 'index'])->name('rencana-pembelajaran.index');
        Route::post('/rencana-pembelajaran/cp', [RencanaPembelajaranController::class, 'storeCp'])->name('rencana-pembelajaran.cp.store');
        Route::delete('/rencana-pembelajaran/cp/{cp}', [RencanaPembelajaranController::class, 'destroyCp'])->name('rencana-pembelajaran.cp.destroy');
        Route::post('/rencana-pembelajaran/tp', [RencanaPembelajaranController::class, 'storeTp'])->name('rencana-pembelajaran.tp.store');
        Route::delete('/rencana-pembelajaran/tp/{tp}', [RencanaPembelajaranController::class, 'destroyTp'])->name('rencana-pembelajaran.tp.destroy');
        Route::post('/rencana-pembelajaran/atp', [RencanaPembelajaranController::class, 'storeAtp'])->name('rencana-pembelajaran.atp.store');
        Route::delete('/rencana-pembelajaran/atp/{atp}', [RencanaPembelajaranController::class, 'destroyAtp'])->name('rencana-pembelajaran.atp.destroy');
        Route::post('/rencana-pembelajaran/atp/generate', [RencanaPembelajaranController::class, 'generateAtpFromTp'])->name('rencana-pembelajaran.atp.generate');
        Route::get('/rencana-pembelajaran/rpp/create', [RencanaPembelajaranController::class, 'createRpp'])->name('rencana-pembelajaran.rpp.create');
        Route::post('/rencana-pembelajaran/rpp', [RencanaPembelajaranController::class, 'storeRpp'])->name('rencana-pembelajaran.rpp.store');
        Route::get('/rencana-pembelajaran/rpp/{rencana}', [RencanaPembelajaranController::class, 'showRpp'])->name('rencana-pembelajaran.rpp.show');
        Route::delete('/rencana-pembelajaran/rpp/{rencana}', [RencanaPembelajaranController::class, 'destroyRpp'])->name('rencana-pembelajaran.rpp.destroy');

        // 3. Menu Laporan KBM (Realisasi Pembelajaran & Presensi Siswa)
        Route::get('/laporan-kbm', [LaporanKbmController::class, 'index'])->name('laporan-kbm.index');
        Route::get('/laporan-kbm/create', [LaporanKbmController::class, 'create'])->name('laporan-kbm.create');
        Route::post('/laporan-kbm', [LaporanKbmController::class, 'store'])->name('laporan-kbm.store');
        Route::get('/laporan-kbm/{laporanKbm}', [LaporanKbmController::class, 'show'])->name('laporan-kbm.show');
        Route::get('/laporan-kbm/{laporanKbm}/print', [LaporanKbmController::class, 'print'])->name('laporan-kbm.print');
        Route::delete('/laporan-kbm/{laporanKbm}', [LaporanKbmController::class, 'destroy'])->name('laporan-kbm.destroy');

        // 3.1 Menu Realisasi Kerja Tugas Tambahan (Dibawah Laporan Realisasi KBM)
        Route::get('/realisasi-tugas-tambahan', [RealisasiTugasTambahanController::class, 'index'])->name('realisasi-tugas-tambahan.index');
        Route::get('/realisasi-tugas-tambahan/create', [RealisasiTugasTambahanController::class, 'create'])->name('realisasi-tugas-tambahan.create');
        Route::post('/realisasi-tugas-tambahan', [RealisasiTugasTambahanController::class, 'store'])->name('realisasi-tugas-tambahan.store');
        Route::get('/realisasi-tugas-tambahan/print', [RealisasiTugasTambahanController::class, 'print'])->name('realisasi-tugas-tambahan.print');
        Route::get('/realisasi-tugas-tambahan/{realisasi}', [RealisasiTugasTambahanController::class, 'show'])->name('realisasi-tugas-tambahan.show');
        Route::delete('/realisasi-tugas-tambahan/{realisasi}', [RealisasiTugasTambahanController::class, 'destroy'])->name('realisasi-tugas-tambahan.destroy');

        // 4 & 5. Menu Absensi Mengajar & Informasi Riwayat Kehadiran Guru
        Route::get('/absensi', [AbsensiGuruController::class, 'index'])->name('absensi.index');
        Route::post('/absensi', [AbsensiGuruController::class, 'store'])->name('absensi.store');
        Route::get('/absensi/print', [AbsensiGuruController::class, 'print'])->name('absensi.print');

        // Presensi Harian Mandiri (Masuk 07.00-07.30, Pulang 14.10, dan Jeda Pelaksanaan)
        Route::post('/presensi-harian/masuk', [AbsensiGuruController::class, 'presensiMasukHarian'])->name('presensi-harian.masuk');
        Route::post('/presensi-harian/pulang', [AbsensiGuruController::class, 'presensiPulangHarian'])->name('presensi-harian.pulang');
        Route::post('/presensi-harian/jeda', [AbsensiGuruController::class, 'updateJedaHarian'])->name('presensi-harian.jeda');

        // Menu Input Tugas untuk Guru Absen
        Route::post('/tugas-absen', [\App\Http\Controllers\Guru\TugasKbmController::class, 'store'])->name('tugas-absen.store');

        // 5.1 Sub-Menu Rekap Presensi (KBM & Kegiatan Sekolah)
        Route::get('/rekap-presensi', [RekapPresensiController::class, 'index'])->name('rekap-presensi.index');
        Route::get('/rekap-presensi/print', [RekapPresensiController::class, 'print'])->name('rekap-presensi.print');

        // Menu Informasi Penugasan Guru (Mengajar & Tugas Tambahan)
        Route::get('/penugasan', [PenugasanGuruController::class, 'index'])->name('penugasan.index');
        Route::post('/penugasan/program-kerja', [PenugasanGuruController::class, 'storeProgramKerja'])->name('penugasan.program-kerja.store');
        Route::delete('/penugasan/program-kerja/{programKerja}', [PenugasanGuruController::class, 'destroyProgramKerja'])->name('penugasan.program-kerja.destroy');

        // 6. Menu Link Perangkat Pembelajaran (Google Drive)
        Route::get('/perangkat-ajar', [PerangkatAjarController::class, 'index'])->name('perangkat-ajar.index');
        Route::get('/perangkat-ajar/create', [PerangkatAjarController::class, 'create'])->name('perangkat-ajar.create');
        Route::post('/perangkat-ajar', [PerangkatAjarController::class, 'store'])->name('perangkat-ajar.store');
        Route::patch('/perangkat-ajar/{perangkatAjar}/verify', [PerangkatAjarController::class, 'verify'])->name('perangkat-ajar.verify');
        Route::delete('/perangkat-ajar/{perangkatAjar}', [PerangkatAjarController::class, 'destroy'])->name('perangkat-ajar.destroy');

        // 7. Menu Pengajuan Cuti Guru
        Route::get('/cuti', [CutiController::class, 'index'])->name('cuti.index');
        Route::get('/cuti/create', [CutiController::class, 'create'])->name('cuti.create');
        Route::post('/cuti', [CutiController::class, 'store'])->name('cuti.store');
        Route::patch('/cuti/{cuti}/approve', [CutiController::class, 'approve'])->name('cuti.approve');

        // 8. Menu Notifikasi Kegiatan Sekolah
        Route::get('/kegiatan', [KegiatanSekolahController::class, 'index'])->name('kegiatan.index');
        Route::get('/kegiatan/create', [KegiatanSekolahController::class, 'create'])->name('kegiatan.create');
        Route::post('/kegiatan', [KegiatanSekolahController::class, 'store'])->name('kegiatan.store');
        Route::get('/kegiatan/{kegiatan}', [KegiatanSekolahController::class, 'show'])->name('kegiatan.show');
        Route::get('/kegiatan/{kegiatan}/edit', [KegiatanSekolahController::class, 'edit'])->name('kegiatan.edit');
        Route::put('/kegiatan/{kegiatan}', [KegiatanSekolahController::class, 'update'])->name('kegiatan.update');
        Route::post('/kegiatan/{kegiatan}/presensi', [KegiatanSekolahController::class, 'storePresensi'])->name('kegiatan.presensi');
        Route::delete('/kegiatan/{kegiatan}', [KegiatanSekolahController::class, 'destroy'])->name('kegiatan.destroy');

        // 10. Menu Suara Siswa & Saran Perbaikan
        Route::get('/keluhan', [KeluhanKbmController::class, 'index'])->name('keluhan.index');
        Route::get('/keluhan/{keluhan}', [KeluhanKbmController::class, 'show'])->name('keluhan.show');
        Route::patch('/keluhan/{keluhan}/update-status', [KeluhanKbmController::class, 'updateStatus'])->name('keluhan.update-status');
        Route::post('/keluhan/{keluhan}/saran', [KeluhanKbmController::class, 'storeSaran'])->name('keluhan.saran.store');
        Route::patch('/keluhan/saran/{saran}/tanggapi', [KeluhanKbmController::class, 'tanggapiSaran'])->name('keluhan.saran.tanggapi');

        // 11. Menu Ubah Profil Guru (Sinkronisasi Penuh db_hilaledu)
        Route::get('/profil', [GuruProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profil', [GuruProfileController::class, 'update'])->name('profile.update');
    });

    // =========================================================================
    // MODUL TENDIK (TENAGA KEPENDIDIKAN) — DASHBOARD PRIBADI
    // =========================================================================
    Route::prefix('tendik')->name('tendik.')->group(function () {
        Route::get('/dashboard', [TendikDashboardController::class, 'index'])->name('dashboard');
    });

    // =========================================================================
    // MODUL PETUGAS PIKET (TENDIK & GURU PIKET)
    // =========================================================================
    Route::prefix('piket')->name('piket.')->group(function () {
        Route::get('/', [PetugasPiketController::class, 'index'])->name('index');
        Route::post('/absen-guru', [PetugasPiketController::class, 'storeAbsenGuru'])->name('absen-guru');
        Route::post('/update-status', [PetugasPiketController::class, 'updateStatusGuru'])->name('update-status');
        Route::post('/izin-keluar/{presensi}', [PetugasPiketController::class, 'tanggapiIzinKeluar'])->name('izin-keluar.tanggapi');
        Route::get('/print', [PetugasPiketController::class, 'print'])->name('print');
    });

    // =========================================================================
    // MODUL SISWA (JADWAL HARIAN, JADWAL LENGKAP, PRESENSI, SUARA SISWA ANONIM)
    // =========================================================================
    Route::prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', [SiswaPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/keluhan/create', [SiswaPortalController::class, 'createKeluhan'])->name('keluhan.create');
        Route::post('/keluhan', [SiswaPortalController::class, 'storeKeluhan'])->name('keluhan.store');
    });

    // =========================================================================
    // MODUL PENGELOLA AKADEMIK (ADMIN, WAKA KURIKULUM, KAPROG, BENDAHARA BOS, TENDIK DITUNJUK)
    // =========================================================================
    Route::prefix('akademik')->name('akademik.')->middleware(['auth', 'module_access:akademik'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Master Kalender Akademik & Hari Libur Nasional (Google Calendar)
        Route::get('/kalender', [KalenderAkademikController::class, 'index'])->name('kalender.index');
        Route::post('/kalender', [KalenderAkademikController::class, 'store'])->name('kalender.store');
        Route::put('/kalender/{kalender}', [KalenderAkademikController::class, 'update'])->name('kalender.update');
        Route::post('/kalender/{kalender}/event', [KalenderAkademikController::class, 'storeEvent'])->name('kalender.event.store');
        Route::put('/kalender/event/{event}', [KalenderAkademikController::class, 'updateEvent'])->name('kalender.event.update');
        Route::delete('/kalender/event/{event}', [KalenderAkademikController::class, 'destroyEvent'])->name('kalender.event.destroy');
        Route::post('/kalender/{kalender}/sync-holidays', [KalenderAkademikController::class, 'syncGoogleHolidays'])->name('kalender.sync-holidays');
        Route::post('/kalender/{kalender}/seed-official', [KalenderAkademikController::class, 'seedOfficialEvents'])->name('kalender.seed-official');
        Route::post('/kalender/{kalender}/import-events', [KalenderAkademikController::class, 'importEvents'])->name('kalender.import-events');
        Route::get('/kalender/template-csv', [KalenderAkademikController::class, 'downloadTemplateCsv'])->name('kalender.template-csv');
        Route::get('/kalender/{kalender}/print', [KalenderAkademikController::class, 'print'])->name('kalender.print');
        Route::get('/kalender/{kalender}/efektif-api', [KalenderAkademikController::class, 'getCalculatedEfektif'])->name('kalender.efektif-api');

        // Master Kurikulum & Pembagian Tugas Mengajar KBM (Filter Jurusan, Jenjang, Kelas + Import/Export/Cetak)
        Route::get('/kurikulum', [KurikulumController::class, 'index'])->name('kurikulum.index');
        Route::post('/kurikulum/autogenerate', [KurikulumController::class, 'autoGenerate'])->name('kurikulum.autogenerate');
        Route::post('/kurikulum', [KurikulumController::class, 'store'])->name('kurikulum.store');
        Route::put('/kurikulum/{kurikulum}', [KurikulumController::class, 'update'])->name('kurikulum.update');
        Route::delete('/kurikulum/{kurikulum}', [KurikulumController::class, 'destroy'])->name('kurikulum.destroy');
        Route::post('/kurikulum/bulk-delete', [KurikulumController::class, 'bulkDelete'])->name('kurikulum.bulk-delete');
        Route::post('/kurikulum/duplicate', [KurikulumController::class, 'duplicate'])->name('kurikulum.duplicate');
        Route::get('/kurikulum/export', [KurikulumController::class, 'export'])->name('kurikulum.export');
        Route::post('/kurikulum/import', [KurikulumController::class, 'import'])->name('kurikulum.import');
        Route::get('/kurikulum/template', [KurikulumController::class, 'template'])->name('kurikulum.template');
        Route::get('/kurikulum/print', [KurikulumController::class, 'print'])->name('kurikulum.print');
        Route::get('/kurikulum/by-kelas', [KurikulumController::class, 'getByKelas'])->name('kurikulum.by-kelas');

        // Jadwal Pelajaran (CRUD + Import / Export / Template + Bulk Delete)
        Route::get('/jadwal', [AdminController::class, 'jadwalIndex'])->name('jadwal.index');
        Route::post('/jadwal/autogenerate', [AdminController::class, 'autoGenerateJadwal'])->name('jadwal.autogenerate');
        Route::post('/jadwal', [AdminController::class, 'jadwalStore'])->name('jadwal.store');
        Route::put('/jadwal/{jadwal}', [AdminController::class, 'jadwalUpdate'])->name('jadwal.update');
        Route::post('/jadwal/bulk-delete', [AdminController::class, 'jadwalBulkDelete'])->name('jadwal.bulk-delete');
        Route::delete('/jadwal/{jadwal}', [AdminController::class, 'jadwalDestroy'])->name('jadwal.destroy');
        Route::get('/jadwal/export', [AdminController::class, 'jadwalExport'])->name('jadwal.export');
        Route::post('/jadwal/import', [AdminController::class, 'jadwalImport'])->name('jadwal.import');
        Route::get('/jadwal/template', [AdminController::class, 'jadwalTemplate'])->name('jadwal.template');

        // Nilai
        Route::get('/nilai', [AdminController::class, 'nilaiIndex'])->name('nilai.index');

        // Mata Pelajaran (CRUD + Import / Export / Template + Bulk Delete)
        Route::get('/mapel', [AdminController::class, 'mapelIndex'])->name('mapel.index');
        Route::post('/mapel', [AdminController::class, 'mapelStore'])->name('mapel.store');
        Route::put('/mapel/{mapel}', [AdminController::class, 'mapelUpdate'])->name('mapel.update');
        Route::post('/mapel/bulk-delete', [AdminController::class, 'mapelBulkDelete'])->name('mapel.bulk-delete');
        Route::delete('/mapel/{mapel}', [AdminController::class, 'mapelDestroy'])->name('mapel.destroy');
        Route::get('/mapel/export', [AdminController::class, 'mapelExport'])->name('mapel.export');
        Route::post('/mapel/import', [AdminController::class, 'mapelImport'])->name('mapel.import');
        Route::get('/mapel/template', [AdminController::class, 'mapelTemplate'])->name('mapel.template');

        // Laporan Kehadiran Guru (Rekap apa yang diinputkan guru, filter bulan & guru, cetak)
        Route::get('/laporan/kehadiran', [AdminController::class, 'laporanKehadiranIndex'])->name('laporan.kehadiran.index');
        Route::get('/laporan/kehadiran/print', [AdminController::class, 'laporanKehadiranPrint'])->name('laporan.kehadiran.print');

        // Laporan Realisasi KBM Guru (Filter bulan, guru, kelas, cetak)
        Route::get('/laporan/kbm', [AdminController::class, 'laporanKbmIndex'])->name('laporan.kbm.index');
        Route::get('/laporan/kbm/print', [AdminController::class, 'laporanKbmPrint'])->name('laporan.kbm.print');

        // Rekap Presensi Dewan Guru (KBM & Kegiatan Sekolah - Cetak Keseluruhan / Per Guru)
        Route::get('/rekap-presensi', [AdminRekapPresensiController::class, 'index'])->name('rekap-presensi.index');
        Route::get('/rekap-presensi/print-all', [AdminRekapPresensiController::class, 'printAll'])->name('rekap-presensi.print-all');
        Route::get('/rekap-presensi/print-guru/{guru}', [AdminRekapPresensiController::class, 'printGuru'])->name('rekap-presensi.print-guru');

        // Penunjukan Tendik Akademik (Wewenang Superadmin)
        Route::get('/tendik', [AdminController::class, 'tendikIndex'])->name('tendik.index');
        Route::post('/tendik', [AdminController::class, 'tendikStore'])->name('tendik.store');
        Route::delete('/tendik/{pengelola}', [AdminController::class, 'tendikDestroy'])->name('tendik.destroy');

        // Penugasan Petugas Piket Sekolah (Tendik & Guru)
        Route::get('/penugasan-piket', [AdminPiketController::class, 'index'])->name('piket.index');
        Route::post('/penugasan-piket', [AdminPiketController::class, 'store'])->name('piket.store');
        Route::delete('/penugasan-piket/{piket}', [AdminPiketController::class, 'destroy'])->name('piket.destroy');

        // Matriks Jadwal Resmi (Format Sesuai Lembar Cetak Sekolah)
        Route::get('/jadwal/matrix', [JadwalMatrixController::class, 'matrix'])->name('jadwal.matrix');
        Route::get('/jadwal/matrix/print', [JadwalMatrixController::class, 'printMatrix'])->name('jadwal.matrix.print');

        // Monitoring Suara Siswa & Evaluasi KBM (Identitas Terbuka untuk Pimpinan)
        Route::get('/keluhan', [AdminKeluhanController::class, 'index'])->name('keluhan.index');

        // Direktori Data Personel Sekolah (Guru, Siswa, Tendik)
        Route::get('/gurus', [DirectoryController::class, 'gurus'])->name('gurus.index');
        Route::post('/gurus/{user}', [DirectoryController::class, 'updateGuru'])->name('gurus.update');

        Route::get('/siswas', [DirectoryController::class, 'siswas'])->name('siswas.index');
        Route::post('/siswas/{siswa}', [DirectoryController::class, 'updateSiswa'])->name('siswas.update');

        Route::get('/tendiks', [DirectoryController::class, 'tendiks'])->name('tendiks.index');
        Route::post('/tendiks/{tendik}', [DirectoryController::class, 'updateTendik'])->name('tendiks.update');

        // Master Pengaturan Sekolah & Kategori Keluhan
        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::post('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
        Route::post('/pengaturan/sync-hilaledu', [PengaturanController::class, 'syncFromHilalEdu'])->name('pengaturan.sync-hilaledu');
        Route::post('/pengaturan/reset-guru', [PengaturanController::class, 'resetPenugasanGuru'])->name('pengaturan.reset-guru');

        // Sinkronisasi Pengguna dari db_hilaledu
        Route::post('/sync-users', [AdminController::class, 'syncUsers'])->name('sync-users');
    });

    // Dual-alias Academic routes (admin.*) for seamless siakademik blade view compatibility
    Route::prefix('admin-akademik')->name('admin.')->middleware(['auth', 'module_access:akademik'])->group(function () {
        Route::get('/kalender', [KalenderAkademikController::class, 'index'])->name('kalender.index');
        Route::post('/kalender', [KalenderAkademikController::class, 'store'])->name('kalender.store');
        Route::put('/kalender/{kalender}', [KalenderAkademikController::class, 'update'])->name('kalender.update');
        Route::post('/kalender/{kalender}/event', [KalenderAkademikController::class, 'storeEvent'])->name('kalender.event.store');
        Route::put('/kalender/event/{event}', [KalenderAkademikController::class, 'updateEvent'])->name('kalender.event.update');
        Route::delete('/kalender/event/{event}', [KalenderAkademikController::class, 'destroyEvent'])->name('kalender.event.destroy');
        Route::post('/kalender/{kalender}/sync-holidays', [KalenderAkademikController::class, 'syncGoogleHolidays'])->name('kalender.sync-holidays');
        Route::post('/kalender/{kalender}/seed-official', [KalenderAkademikController::class, 'seedOfficialEvents'])->name('kalender.seed-official');
        Route::post('/kalender/{kalender}/import-events', [KalenderAkademikController::class, 'importEvents'])->name('kalender.import-events');
        Route::get('/kalender/template-csv', [KalenderAkademikController::class, 'downloadTemplateCsv'])->name('kalender.template-csv');
        Route::get('/kalender/{kalender}/print', [KalenderAkademikController::class, 'print'])->name('kalender.print');
        Route::get('/kalender/{kalender}/efektif-api', [KalenderAkademikController::class, 'getCalculatedEfektif'])->name('kalender.efektif-api');

        Route::get('/kurikulum', [KurikulumController::class, 'index'])->name('kurikulum.index');
        Route::post('/kurikulum/autogenerate', [KurikulumController::class, 'autoGenerate'])->name('kurikulum.autogenerate');
        Route::post('/kurikulum', [KurikulumController::class, 'store'])->name('kurikulum.store');
        Route::put('/kurikulum/{kurikulum}', [KurikulumController::class, 'update'])->name('kurikulum.update');
        Route::delete('/kurikulum/{kurikulum}', [KurikulumController::class, 'destroy'])->name('kurikulum.destroy');
        Route::post('/kurikulum/bulk-delete', [KurikulumController::class, 'bulkDelete'])->name('kurikulum.bulk-delete');
        Route::post('/kurikulum/duplicate', [KurikulumController::class, 'duplicate'])->name('kurikulum.duplicate');
        Route::get('/kurikulum/export', [KurikulumController::class, 'export'])->name('kurikulum.export');
        Route::post('/kurikulum/import', [KurikulumController::class, 'import'])->name('kurikulum.import');
        Route::get('/kurikulum/template', [KurikulumController::class, 'template'])->name('kurikulum.template');
        Route::get('/kurikulum/print', [KurikulumController::class, 'print'])->name('kurikulum.print');
        Route::get('/kurikulum/by-kelas', [KurikulumController::class, 'getByKelas'])->name('kurikulum.by-kelas');

        Route::get('/jadwal', [AdminController::class, 'jadwalIndex'])->name('jadwal.index');
        Route::post('/jadwal/autogenerate', [AdminController::class, 'autoGenerateJadwal'])->name('jadwal.autogenerate');
        Route::post('/jadwal', [AdminController::class, 'jadwalStore'])->name('jadwal.store');
        Route::put('/jadwal/{jadwal}', [AdminController::class, 'jadwalUpdate'])->name('jadwal.update');
        Route::post('/jadwal/bulk-delete', [AdminController::class, 'jadwalBulkDelete'])->name('jadwal.bulk-delete');
        Route::delete('/jadwal/{jadwal}', [AdminController::class, 'jadwalDestroy'])->name('jadwal.destroy');
        Route::get('/jadwal/export', [AdminController::class, 'jadwalExport'])->name('jadwal.export');
        Route::post('/jadwal/import', [AdminController::class, 'jadwalImport'])->name('jadwal.import');
        Route::get('/jadwal/template', [AdminController::class, 'jadwalTemplate'])->name('jadwal.template');
        Route::get('/jadwal/matrix', [JadwalMatrixController::class, 'matrix'])->name('jadwal.matrix');
        Route::get('/jadwal/matrix/print', [JadwalMatrixController::class, 'printMatrix'])->name('jadwal.matrix.print');

        Route::get('/nilai', [AdminController::class, 'nilaiIndex'])->name('nilai.index');

        Route::get('/mapel', [AdminController::class, 'mapelIndex'])->name('mapel.index');
        Route::post('/mapel', [AdminController::class, 'mapelStore'])->name('mapel.store');
        Route::put('/mapel/{mapel}', [AdminController::class, 'mapelUpdate'])->name('mapel.update');
        Route::post('/mapel/bulk-delete', [AdminController::class, 'mapelBulkDelete'])->name('mapel.bulk-delete');
        Route::delete('/mapel/{mapel}', [AdminController::class, 'mapelDestroy'])->name('mapel.destroy');
        Route::get('/mapel/export', [AdminController::class, 'mapelExport'])->name('mapel.export');
        Route::post('/mapel/import', [AdminController::class, 'mapelImport'])->name('mapel.import');
        Route::get('/mapel/template', [AdminController::class, 'mapelTemplate'])->name('mapel.template');

        Route::get('/laporan/kehadiran', [AdminController::class, 'laporanKehadiranIndex'])->name('laporan.kehadiran.index');
        Route::get('/laporan/kehadiran/print', [AdminController::class, 'laporanKehadiranPrint'])->name('laporan.kehadiran.print');

        Route::get('/laporan/kbm', [AdminController::class, 'laporanKbmIndex'])->name('laporan.kbm.index');
        Route::get('/laporan/kbm/print', [AdminController::class, 'laporanKbmPrint'])->name('laporan.kbm.print');

        Route::get('/rekap-presensi', [AdminRekapPresensiController::class, 'index'])->name('rekap-presensi.index');
        Route::get('/rekap-presensi/print-all', [AdminRekapPresensiController::class, 'printAll'])->name('rekap-presensi.print-all');
        Route::get('/rekap-presensi/print-guru/{guru}', [AdminRekapPresensiController::class, 'printGuru'])->name('rekap-presensi.print-guru');

        Route::get('/tendik', [AdminController::class, 'tendikIndex'])->name('tendik.index');
        Route::post('/tendik', [AdminController::class, 'tendikStore'])->name('tendik.store');
        Route::delete('/tendik/{pengelola}', [AdminController::class, 'tendikDestroy'])->name('tendik.destroy');

        Route::get('/penugasan-piket', [AdminPiketController::class, 'index'])->name('piket.index');
        Route::post('/penugasan-piket', [AdminPiketController::class, 'store'])->name('piket.store');
        Route::delete('/penugasan-piket/{piket}', [AdminPiketController::class, 'destroy'])->name('piket.destroy');

        Route::get('/keluhan', [AdminKeluhanController::class, 'index'])->name('keluhan.index');

        Route::get('/gurus', [DirectoryController::class, 'gurus'])->name('gurus.index');
        Route::post('/gurus/{user}', [DirectoryController::class, 'updateGuru'])->name('gurus.update');

        Route::get('/siswas', [DirectoryController::class, 'siswas'])->name('siswas.index');
        Route::post('/siswas/{siswa}', [DirectoryController::class, 'updateSiswa'])->name('siswas.update');

        Route::get('/tendiks', [DirectoryController::class, 'tendiks'])->name('tendiks.index');
        Route::post('/tendiks/{tendik}', [DirectoryController::class, 'updateTendik'])->name('tendiks.update');

        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::post('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
        Route::post('/pengaturan/sync-hilaledu', [PengaturanController::class, 'syncFromHilalEdu'])->name('pengaturan.sync-hilaledu');
        Route::post('/pengaturan/reset-guru', [PengaturanController::class, 'resetPenugasanGuru'])->name('pengaturan.reset-guru');

        Route::post('/sync-users', [AdminController::class, 'syncUsers'])->name('sync-users');
    });
});

// MONITORING BK ROUTES
// Landing page - redirect to dashboard based on role


// Auth routes (from Breeze)


// Authenticated routes
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/{notifikasi}/read', [NotificationController::class, 'markAsRead'])->name('notifikasi.read');
    Route::post('/notifikasi/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifikasi.readAll');

    // ==========================================
    // MONITORING BK (SUPERADMIN, ADMIN BK & GURU BK)
    // ==========================================
    Route::prefix('bk')->name('bk.')->middleware('role:admin,guru_bk')->group(function () {
        Route::get('/dashboard', [BK\DashboardController::class, 'index'])->name('dashboard');

        // Master Pelanggaran & Poin
        Route::resource('kategori-pelanggaran', Admin\KategoriPelanggaranController::class)->except('show');
        Route::resource('jenis-pelanggaran', Admin\JenisPelanggaranController::class)->except('show');
        Route::get('/kategori-pelanggaran-export', [Admin\KategoriPelanggaranController::class, 'export'])->name('kategori-pelanggaran.export');
        Route::get('/kategori-pelanggaran-template', [Admin\KategoriPelanggaranController::class, 'downloadTemplate'])->name('kategori-pelanggaran.template');
        Route::post('/kategori-pelanggaran-import', [Admin\KategoriPelanggaranController::class, 'import'])->name('kategori-pelanggaran.import');
        Route::get('/poin', [Admin\SiswaController::class, 'poinIndex'])->name('poin.index');

        // Pelanggaran Siswa
        Route::resource('pelanggaran', BK\PelanggaranController::class)->except('destroy');
        Route::get('/pelanggaran-histori-siswa', [BK\PelanggaranController::class, 'getHistoriSiswa'])->name('pelanggaran.histori-siswa');

        // Progres Data Pelanggaran & Sanksi
        Route::get('/progres', [BK\ProgresController::class, 'index'])->name('progres.index');
        Route::get('/progres/{pelanggaran}', [BK\ProgresController::class, 'show'])->name('progres.show');
        Route::post('/progres/{pelanggaran}/cetak-surat', [BK\ProgresController::class, 'cetakSurat'])->name('progres.cetak-surat');
        Route::get('/progres/{pelanggaran}/lihat-surat', [BK\ProgresController::class, 'lihatSurat'])->name('progres.lihat-surat');
        Route::post('/progres/{pelanggaran}/laporan', [BK\ProgresController::class, 'isiLaporan'])->name('progres.laporan');
        Route::post('/progres/{pelanggaran}/selesai', [BK\ProgresController::class, 'selesaikan'])->name('progres.selesai');

        // Laporan Berkala BK
        Route::get('/laporan', [BK\LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/mingguan', [BK\LaporanController::class, 'mingguan'])->name('laporan.mingguan');
        Route::get('/laporan/bulanan', [BK\LaporanController::class, 'bulanan'])->name('laporan.bulanan');
        Route::get('/laporan/pra-ujian', [BK\LaporanController::class, 'praUjian'])->name('laporan.pra-ujian');
        Route::get('/laporan/semester', [BK\LaporanController::class, 'semester'])->name('laporan.semester');
        Route::get('/laporan/akhir-tahun', [BK\LaporanController::class, 'akhirTahun'])->name('laporan.akhir-tahun');
        Route::get('/laporan/cetak', [BK\LaporanController::class, 'cetak'])->name('laporan.cetak');
    });

    // Aliases for BK Master Pelanggaran (Dual naming support admin.* and bk.*)
    Route::as('admin.')->middleware('role:admin,guru_bk')->group(function () {
        Route::resource('bk-kategori-pelanggaran', Admin\KategoriPelanggaranController::class, ['names' => 'kategori-pelanggaran'])->except('show');
        Route::resource('bk-jenis-pelanggaran', Admin\JenisPelanggaranController::class, ['names' => 'jenis-pelanggaran'])->except('show');
        Route::get('bk-kategori-pelanggaran-export', [Admin\KategoriPelanggaranController::class, 'export'])->name('kategori-pelanggaran.export');
        Route::get('bk-kategori-pelanggaran-template', [Admin\KategoriPelanggaranController::class, 'downloadTemplate'])->name('kategori-pelanggaran.template');
        Route::post('bk-kategori-pelanggaran-import', [Admin\KategoriPelanggaranController::class, 'import'])->name('kategori-pelanggaran.import');
    });

    // ==========================================
    // KAPROG ROUTES
    // ==========================================
    Route::prefix('kaprog')->name('kaprog.')->middleware('role:kaprog')->group(function () {
        Route::get('/dashboard', [Kaprog\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/pelanggaran', [Kaprog\PelanggaranController::class, 'index'])->name('pelanggaran.index');
        Route::get('/pelanggaran/{pelanggaran}', [Kaprog\PelanggaranController::class, 'show'])->name('pelanggaran.show');

        // Progres Data Pelanggaran
        Route::get('/progres', [Kaprog\ProgresController::class, 'index'])->name('progres.index');
        Route::get('/progres/{pelanggaran}', [Kaprog\ProgresController::class, 'show'])->name('progres.show');
        Route::post('/progres/{pelanggaran}/approve', [Kaprog\ProgresController::class, 'approve'])->name('progres.approve');
        Route::post('/progres/{pelanggaran}/laporan', [Kaprog\ProgresController::class, 'isiLaporan'])->name('progres.laporan');
    });

    // ==========================================
    // WALI KELAS ROUTES
    // ==========================================
    Route::prefix('walikelas')->name('walikelas.')->middleware('role:wali_kelas')->group(function () {
        Route::get('/dashboard', [WaliKelas\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/pelanggaran', [WaliKelas\PelanggaranController::class, 'index'])->name('pelanggaran.index');
        Route::get('/pelanggaran/{pelanggaran}', [WaliKelas\PelanggaranController::class, 'show'])->name('pelanggaran.show');

        // Progres Data Pelanggaran
        Route::get('/progres', [WaliKelas\ProgresController::class, 'index'])->name('progres.index');
        Route::get('/progres/{pelanggaran}', [WaliKelas\ProgresController::class, 'show'])->name('progres.show');
        Route::post('/progres/{pelanggaran}/approve', [WaliKelas\ProgresController::class, 'approve'])->name('progres.approve');
        Route::post('/progres/{pelanggaran}/laporan', [WaliKelas\ProgresController::class, 'isiLaporan'])->name('progres.laporan');
    });

    // ==========================================
    // KEPALA SEKOLAH ROUTES
    // ==========================================
    Route::prefix('kepsek')->name('kepsek.')->middleware('role:kepala_sekolah')->group(function () {
        Route::get('/dashboard', [KepalaSekolah\DashboardController::class, 'index'])->name('dashboard');

        // Progres Data Pelanggaran
        Route::get('/progres', [KepalaSekolah\ProgresController::class, 'index'])->name('progres.index');
        Route::get('/progres/{pelanggaran}', [KepalaSekolah\ProgresController::class, 'show'])->name('progres.show');
        Route::post('/progres/{pelanggaran}/approve', [KepalaSekolah\ProgresController::class, 'approve'])->name('progres.approve');
        Route::post('/progres/{pelanggaran}/laporan', [KepalaSekolah\ProgresController::class, 'isiLaporan'])->name('progres.laporan');
    });

    // ==========================================
    // WAKA KESISWAAN ROUTES
    // ==========================================
    Route::prefix('waka')->name('waka.')->middleware('role:waka_kesiswaan')->group(function () {
        Route::get('/dashboard', [KepalaSekolah\DashboardController::class, 'index'])->name('dashboard');

        // Progres Data Pelanggaran
        Route::get('/progres', [KepalaSekolah\ProgresController::class, 'index'])->name('progres.index');
        Route::get('/progres/{pelanggaran}', [KepalaSekolah\ProgresController::class, 'show'])->name('progres.show');
        Route::post('/progres/{pelanggaran}/approve', [KepalaSekolah\ProgresController::class, 'approve'])->name('progres.approve');
        Route::post('/progres/{pelanggaran}/laporan', [KepalaSekolah\ProgresController::class, 'isiLaporan'])->name('progres.laporan');
    });

    // ==========================================
    // SISWA ROUTES
    // ==========================================
    Route::prefix('siswa')->name('siswa.')->middleware('role:siswa')->group(function () {
        Route::get('/dashboard', [Siswa\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/pelanggaran', [Siswa\PelanggaranController::class, 'index'])->name('pelanggaran.index');
        Route::get('/pelanggaran/{pelanggaran}', [Siswa\PelanggaranController::class, 'show'])->name('pelanggaran.show');
    });
});

