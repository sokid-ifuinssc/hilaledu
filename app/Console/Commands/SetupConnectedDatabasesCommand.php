<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Application;
use App\Services\CrossAppDatabaseService;
use Throwable;

class SetupConnectedDatabasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-connected-databases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Buat database db_akademik dan db_tracer_study, inisialisasi skema tabel, dan sinkronkan pengguna dari db_hilaledu';

    /**
     * Execute the console command.
     */
    public function handle(CrossAppDatabaseService $syncService): int
    {
        $this->info("==========================================================");
        $this->info("  SETUP DATABASE TERHUBUNG: AKADEMIK & TRACER STUDY");
        $this->info("==========================================================");

        $isMysql = DB::connection()->getDriverName() === 'mysql';

        // 1. Pembuatan Database di MySQL
        if ($isMysql) {
            $this->line("\n[1/4] Memeriksa keberadaan database di MySQL Server...");

            try {
                DB::statement("CREATE DATABASE IF NOT EXISTS `db_akademik` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $this->info("  ✓ Database 'db_akademik' siap (terhubung)");
            } catch (Throwable $e) {
                $this->warn("  ! Gagal membuat database 'db_akademik': " . $e->getMessage());
            }

            try {
                DB::statement("CREATE DATABASE IF NOT EXISTS `db_tracer_study` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $this->info("  ✓ Database 'db_tracer_study' siap (terhubung)");
            } catch (Throwable $e) {
                $this->warn("  ! Gagal membuat database 'db_tracer_study': " . $e->getMessage());
            }
        } else {
            $this->line("\n[1/4] Menggunakan driver SQLite (Pengujian / In-Memory)...");
        }

        // 2. Inisialisasi Skema Tabel di Kedua Database
        $this->line("\n[2/4] Menginisialisasi skema tabel relasional...");

        try {
            $akademikRes = $syncService->setupAkademikDatabase('akademik');
            $this->info("  ✓ Skema 'db_akademik' berhasil dibuat: " . implode(', ', $akademikRes['tables']));
        } catch (Throwable $e) {
            $this->error("  ✗ Error setup tabel 'db_akademik': " . $e->getMessage());
        }

        try {
            $tracerRes = $syncService->setupTracerStudyDatabase('tracer_study');
            $this->info("  ✓ Skema 'db_tracer_study' berhasil dibuat: " . implode(', ', $tracerRes['tables']));
        } catch (Throwable $e) {
            $this->error("  ✗ Error setup tabel 'db_tracer_study': " . $e->getMessage());
        }

        // 3. Pendaftaran / Pembaruan Aplikasi di Portal db_hilaledu
        $this->line("\n[3/4] Mendaftarkan aplikasi ke portal HilalEdu (tabel applications)...");

        // Aplikasi Akademik
        $akademikApp = Application::firstOrNew(['slug' => 'akademik']);
        $akademikApp->name             = 'Sistem Informasi Akademik';
        $akademikApp->description      = 'Pengelolaan data akademik terpadu: kurikulum, jadwal pelajaran, penilaian harian/semester, presensi, dan raport digital.';
        $akademikApp->icon             = 'bi-mortarboard-fill';
        $akademikApp->color            = '#9b59b6';
        $akademikApp->database_name    = 'db_akademik';
        $akademikApp->url              = $akademikApp->url ?: 'http://localhost:8004';
        $akademikApp->sso_redirect_url = $akademikApp->sso_redirect_url ?: 'http://localhost:8004/sso-login';
        $akademikApp->status           = 'active';
        $akademikApp->sso_enabled      = true;
        $akademikApp->auto_sync        = true;
        $akademikApp->features         = [
            'Kurikulum & Mapel',
            'Jadwal Pelajaran',
            'Input Nilai & Asesmen',
            'Raport Digital & Presensi'
        ];
        if (empty($akademikApp->api_key)) {
            $akademikApp->api_key = 'hila_' . Str::random(40);
        }
        $akademikApp->sort_order = 5;
        $akademikApp->save();
        $this->info("  ✓ Aplikasi 'Sistem Informasi Akademik' aktif & terdaftar.");

        // Aplikasi Tracer Study
        $tracerApp = Application::firstOrNew(['slug' => 'tracer-study']);
        $tracerApp->name             = 'Sistem Informasi Tracer Study';
        $tracerApp->description      = 'Penelusuran tamatan dan alumni sekolah: deteksi karier bekerja, kuliah/pendidikan lanjut, dan wirausaha (BMW).';
        $tracerApp->icon             = 'bi-compass-fill';
        $tracerApp->color            = '#e67e22';
        $tracerApp->database_name    = 'db_tracer_study';
        $tracerApp->url              = $tracerApp->url ?: 'http://localhost:8005';
        $tracerApp->sso_redirect_url = $tracerApp->sso_redirect_url ?: 'http://localhost:8005/sso-login';
        $tracerApp->status           = 'active';
        $tracerApp->sso_enabled      = true;
        $tracerApp->auto_sync        = true;
        $tracerApp->features         = [
            'Tracer Status (Kerja/Kuliah/Wirausaha)',
            'Pendataan Profil Alumni',
            'Kuesioner Masa Tunggu & Gaji',
            'Statistik & Laporan BKK'
        ];
        if (empty($tracerApp->api_key)) {
            $tracerApp->api_key = 'hila_' . Str::random(40);
        }
        $tracerApp->sort_order = 6;
        $tracerApp->save();
        $this->info("  ✓ Aplikasi 'Sistem Informasi Tracer Study' aktif & terdaftar.");

        // 4. Sinkronisasi Seluruh Pengguna ke db_akademik dan db_tracer_study
        $this->line("\n[4/4] Sinkronisasi awal akun pengguna dari db_hilaledu...");

        $syncAkademik = $syncService->syncAllUsersToApp($akademikApp);
        $this->info("  ✓ db_akademik: " . $syncAkademik['message']);

        $syncTracer = $syncService->syncAllUsersToApp($tracerApp);
        $this->info("  ✓ db_tracer_study: " . $syncTracer['message']);

        $this->info("\n==========================================================");
        $this->info("  SUKSES: DATABASE TERHUBUNG DAN SSO SIAP DIGUNAKAN!");
        $this->info("==========================================================\n");

        return Command::SUCCESS;
    }
}
