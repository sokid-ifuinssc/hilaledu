<?php

namespace App\Services;

use App\Models\User;
use App\Models\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Schema\Blueprint;
use Exception;
use Throwable;

class CrossAppDatabaseService
{
    /**
     * Flag untuk menonaktifkan auto-sync sementara selama proses bulk import / batch save.
     */
    public static bool $syncPaused = false;

    /**
     * Cache koneksi database yang tabel users-nya sudah diperiksa / disiapkan.
     * Mencegah pemanggilan Schema::hasColumn berulang-ulang yang memperlambat query.
     */
    protected static array $ensuredConnections = [];

    /**
     * Daftarkan konfigurasi koneksi database dinamis untuk sebuah Application jika belum ada.
     */
    public function getAppConnection(Application $app): string
    {
        $connName = $app->getDynamicConnectionName();

        // Jangan timpa jika koneksi sudah ada
        if (!config()->has("database.connections.{$connName}")) {
            config(["database.connections.{$connName}" => [
                'driver'    => 'mysql',
                'host'      => env('DB_HOST', '127.0.0.1'),
                'port'      => env('DB_PORT', '3306'),
                'database'  => env('DB_DATABASE', 'db_hilaledu'),
                'username'  => env('DB_USERNAME', 'root'),
                'password'  => env('DB_PASSWORD', ''),
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
                'strict'    => true,
                'engine'    => null,
            ]]);
        }

        return $connName;
    }

    /**
     * Dapatkan daftar database aplikasi eksternal yang tersedia dan status koneksinya.
     * Mengambil secara dinamis dari tabel applications (kecuali portal hilaledu sendiri).
     */
    public function getAvailableAppDatabases(): array
    {
        $apps = Application::whereNotNull('database_name')
            ->where('slug', '!=', 'hilaledu')
            ->orderBy('sort_order')
            ->get();

        $list = [];

        foreach ($apps as $app) {
            $test = $this->testConnection($app);

            $list[$app->slug] = [
                'id'           => $app->id,
                'key'          => $app->slug,
                'name'         => $app->name,
                'slug'         => $app->slug,
                'connection'   => $app->getDynamicConnectionName(),
                'database'     => $app->database_name,
                'url'          => $app->url,
                'status'       => $app->status,
                'auto_sync'    => (bool) $app->auto_sync,
                'is_available' => $test['is_connected'],
                'table_exists' => $test['table_exists'],
                'user_count'   => $test['user_count'],
                'error'        => $test['error'],
            ];
        }

        return $list;
    }

    /**
     * Uji koneksi ke database aplikasi tertentu dan cek tabel users.
     */
    public function testConnection(Application $app): array
    {
        if (!$app->hasDatabase()) {
            return [
                'is_connected' => false,
                'table_exists' => false,
                'user_count'   => 0,
                'error'        => 'Nama database belum dikonfigurasi.',
            ];
        }

        $conn = $this->getAppConnection($app);

        try {
            DB::connection($conn)->getPdo();
            $tableExists = Schema::connection($conn)->hasTable('users');
            $userCount = $tableExists ? DB::connection($conn)->table('users')->count() : 0;

            return [
                'is_connected' => true,
                'table_exists' => $tableExists,
                'user_count'   => $userCount,
                'error'        => null,
            ];
        } catch (Throwable $e) {
            return [
                'is_connected' => false,
                'table_exists' => false,
                'user_count'   => 0,
                'error'        => $e->getMessage(),
            ];
        }
    }

    /**
     * Pastikan tabel users ada pada database target. Jika belum ada, buatkan otomatis
     * beserta kolom lengkap agar akun dapat login langsung.
     */
    public function ensureUserTableExists(string $connection): void
    {
        // Jika koneksi ini sudah dipastikan tabel dan kolomnya pada request saat ini, skip query schema
        if (isset(static::$ensuredConnections[$connection])) {
            return;
        }

        if (!Schema::connection($connection)->hasTable('users')) {
            Schema::connection($connection)->create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('username')->nullable()->unique();
                $table->string('email')->nullable()->unique();
                $table->string('password');
                $table->string('password_plain')->nullable();
                $table->string('role')->default('siswa');
                $table->string('nip')->nullable();
                $table->string('no_hp', 30)->nullable();
                $table->string('no_hp_ortu', 30)->nullable();
                $table->string('nama_ayah', 150)->nullable();
                $table->string('nama_ibu', 150)->nullable();
                $table->text('alamat')->nullable();
                $table->string('desa', 100)->nullable();
                $table->string('kecamatan', 100)->nullable();
                $table->string('kabupaten', 100)->nullable();
                $table->string('provinsi', 100)->nullable();
                $table->json('tugas_tambahan')->nullable();
                $table->string('jabatan_utama', 100)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        } else {
            // Cek dan tambahkan kolom jika belum ada
            $extraCols = [
                'username'       => 'string_null',
                'password_plain' => 'string_null',
                'role'           => 'string_default_siswa',
                'nip'            => 'string_null',
                'tugas_tambahan' => 'json_null',
                'jabatan_utama'  => 'string_null',
                'is_active'      => 'boolean_default_true',
                'no_hp'          => 'string_null',
                'no_hp_ortu'     => 'string_null',
                'nama_ayah'      => 'string_null',
                'nama_ibu'       => 'string_null',
                'alamat'         => 'text_null',
                'desa'           => 'string_null',
                'kecamatan'      => 'string_null',
                'kabupaten'      => 'string_null',
                'provinsi'       => 'string_null',
            ];

            foreach ($extraCols as $col => $typeDef) {
                if (!Schema::connection($connection)->hasColumn('users', $col)) {
                    Schema::connection($connection)->table('users', function (Blueprint $table) use ($col, $typeDef) {
                        if ($typeDef === 'text_null') {
                            $table->text($col)->nullable();
                        } elseif ($typeDef === 'json_null') {
                            $table->json($col)->nullable();
                        } elseif ($typeDef === 'boolean_default_true') {
                            $table->boolean($col)->default(true);
                        } elseif ($typeDef === 'string_default_siswa') {
                            $table->string($col, 50)->default('siswa');
                        } else {
                            $table->string($col, 150)->nullable();
                        }
                    });
                }
            }
        }

        static::$ensuredConnections[$connection] = true;
    }

    /**
     * Sinkronkan satu pengguna ke satu database aplikasi tertentu.
     * Menerima instance Application atau slug/connection key.
     */
    public function syncUserToApp(User $user, Application|string $appOrKey, ?string $plainPassword = null): array
    {
        if (is_string($appOrKey)) {
            // Cari aplikasi berdasarkan slug atau database_name
            $app = Application::where('slug', $appOrKey)
                ->orWhere('database_name', $appOrKey)
                ->orWhere('database_name', 'db_' . str_replace('-', '_', $appOrKey))
                ->first();

            if (!$app) {
                // Fallback untuk koneksi statis jika ada di config
                if (config()->has("database.connections.{$appOrKey}")) {
                    $conn = $appOrKey;
                    $appName = ucfirst(str_replace('_', ' ', $appOrKey));
                    $dbName = config("database.connections.{$appOrKey}.database");
                } else {
                    return [
                        'success' => false,
                        'app'     => $appOrKey,
                        'message' => "Aplikasi atau database '{$appOrKey}' tidak ditemukan.",
                    ];
                }
            } else {
                $conn = $this->getAppConnection($app);
                $appName = $app->name;
                $dbName = $app->database_name;
            }
        } else {
            $app = $appOrKey;
            $conn = $this->getAppConnection($app);
            $appName = $app->name;
            $dbName = $app->database_name;
        }

        try {
            $this->ensureUserTableExists($conn);

            $passwordToUse = $plainPassword ?? $user->password_plain ?? 'password123';
            // Gunakan password hash asli dari HilalEdu agar enkripsinya 100% konsisten
            $hashedPassword = !empty($user->password) && empty($plainPassword)
                ? $user->password
                : Hash::make($passwordToUse);

            // Cek apakah user sudah ada di target database berdasarkan ID atau username atau email
            $targetQuery = DB::connection($conn)->table('users');
            if ($user->id) {
                $targetQuery->where('id', $user->id);
            }
            if ($user->username) {
                $targetQuery->orWhere('username', $user->username);
            }
            if ($user->email) {
                $targetQuery->orWhere('email', $user->email);
            }
            $targetUser = $targetQuery->first();

            $data = [
                'name'           => $user->name,
                'username'       => $user->username,
                'email'          => $user->email,
                'password'       => $hashedPassword,
                'password_plain' => $passwordToUse,
                'role'           => $user->role,
                'nip'            => $user->nip,
                'no_hp'          => $user->no_hp,
                'no_hp_ortu'     => $user->no_hp_ortu,
                'nama_ayah'      => $user->nama_ayah,
                'nama_ibu'       => $user->nama_ibu,
                'alamat'         => $user->alamat,
                'desa'           => $user->desa,
                'kecamatan'      => $user->kecamatan,
                'kabupaten'      => $user->kabupaten,
                'provinsi'       => $user->provinsi,
                'tugas_tambahan' => !empty($user->tugas_tambahan) ? json_encode($user->tugas_tambahan) : null,
                'jabatan_utama'  => $user->jabatan_utama ?? ($user->role === 'guru' ? 'Guru Pengajar' : null),
                'is_active'      => (bool) $user->is_active,
                'updated_at'     => now(),
            ];

            if ($targetUser) {
                DB::connection($conn)->table('users')
                    ->where('id', $targetUser->id)
                    ->update($data);
                $action = 'diperbarui';
            } else {
                $data['id'] = $user->id;
                $data['created_at'] = now();
                DB::connection($conn)->table('users')->insert($data);
                $action = 'ditambahkan';
            }

            return [
                'success'  => true,
                'app_key'  => $app->slug ?? $appOrKey,
                'app_name' => $appName,
                'database' => $dbName,
                'message'  => "Akun {$user->name} ({$user->username}) berhasil {$action} di {$dbName}.",
            ];
        } catch (Throwable $e) {
            return [
                'success'  => false,
                'app_key'  => $app->slug ?? $appOrKey,
                'app_name' => $appName,
                'database' => $dbName,
                'message'  => "Gagal sinkron ke {$appName}: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Sinkronkan satu pengguna ke beberapa database aplikasi sekaligus (dipakai CredentialController).
     */
    public function syncUserToDatabases(User $user, array $targetAppKeys, ?string $plainPassword = null): array
    {
        $results = [];
        foreach ($targetAppKeys as $appKey) {
            $results[$appKey] = $this->syncUserToApp($user, $appKey, $plainPassword);
        }
        return $results;
    }

    /**
     * Otomatis sinkronkan user ke SELURUH aplikasi aktif yang mengaktifkan auto_sync.
     * Dipanggil saat pembuatan user, edit user, atau reset password.
     */
    public function syncUserToAllActiveApps(User $user, ?string $plainPassword = null): array
    {
        $activeApps = Application::whereNotNull('database_name')
            ->where('slug', '!=', 'hilaledu')
            ->where('status', 'active')
            ->where('auto_sync', true)
            ->get();

        $results = [];
        foreach ($activeApps as $app) {
            $results[$app->slug] = $this->syncUserToApp($user, $app, $plainPassword);
        }

        return $results;
    }

    /**
     * Sinkronkan sekumpulan User ID secara efisien ke semua aplikasi aktif.
     * Digunakan setelah bulk import untuk mencegah pemanggilan berulang di dalam loop import.
     */
    public function syncUserIdsToAllActiveApps(array $userIds): void
    {
        if (empty($userIds)) {
            return;
        }

        $activeApps = Application::whereNotNull('database_name')
            ->where('slug', '!=', 'hilaledu')
            ->where('status', 'active')
            ->where('auto_sync', true)
            ->get();

        if ($activeApps->isEmpty()) {
            return;
        }

        $users = User::whereIn('id', $userIds)->get();

        foreach ($activeApps as $app) {
            $conn = $this->getAppConnection($app);
            $this->ensureUserTableExists($conn);

            foreach ($users as $user) {
                try {
                    $this->syncUserToApp($user, $app);
                } catch (\Throwable $e) {
                    // Lanjutkan sinkronisasi untuk user berikutnya jika ada error
                }
            }
        }
    }

    /**
     * Sinkronisasi MASSAL: Sinkronkan SELURUH pengguna di HilalEdu ke SATU aplikasi tertentu.
     */
    public function syncAllUsersToApp(Application $app): array
    {
        if (!$app->hasDatabase()) {
            return [
                'success' => false,
                'message' => "Aplikasi {$app->name} belum memiliki nama database.",
                'synced'  => 0,
            ];
        }

        $users = User::all();
        $successCount = 0;
        $failedCount  = 0;
        $errors       = [];

        $conn = $this->getAppConnection($app);

        try {
            $this->ensureUserTableExists($conn);

            foreach ($users as $user) {
                $res = $this->syncUserToApp($user, $app);
                if ($res['success']) {
                    $successCount++;
                } else {
                    $failedCount++;
                    $errors[] = $res['message'];
                }
            }

            return [
                'success'       => $failedCount === 0,
                'app_name'      => $app->name,
                'database'      => $app->database_name,
                'total_users'   => $users->count(),
                'synced_count'  => $successCount,
                'failed_count'  => $failedCount,
                'errors'        => array_unique($errors),
                'message'       => "Berhasil menyinkronkan {$successCount} dari {$users->count()} pengguna ke database {$app->database_name}.",
            ];
        } catch (Throwable $e) {
            return [
                'success'      => false,
                'app_name'     => $app->name,
                'database'     => $app->database_name,
                'total_users'  => $users->count(),
                'synced_count' => 0,
                'failed_count' => $users->count(),
                'errors'       => [$e->getMessage()],
                'message'      => "Gagal koneksi ke database {$app->database_name}: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Sinkronisasi MASSAL: Sinkronkan SELURUH pengguna di HilalEdu ke SEMUA aplikasi aktif yang memiliki database.
     */
    public function syncAllUsersToAllApps(): array
    {
        $apps = Application::whereNotNull('database_name')
            ->where('slug', '!=', 'hilaledu')
            ->where('status', 'active')
            ->get();

        $results = [];
        $totalSynced = 0;

        foreach ($apps as $app) {
            $res = $this->syncAllUsersToApp($app);
            $results[$app->slug] = $res;
            if ($res['success']) {
                $totalSynced += $res['synced_count'];
            }
        }

        return [
            'apps_count'   => $apps->count(),
            'total_synced' => $totalSynced,
            'details'      => $results,
        ];
    }

    /**
     * Inisialisasi skema tabel lengkap untuk Sistem Informasi Akademik (db_akademik).
     */
    public function setupAkademikDatabase(string $connection = 'akademik'): array
    {
        $this->ensureUserTableExists($connection);
        $tablesCreated = ['users'];

        // 1. Kurikulum
        if (!Schema::connection($connection)->hasTable('kurikulums')) {
            Schema::connection($connection)->create('kurikulums', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 20)->unique();
                $table->string('nama', 100);
                $table->year('tahun_mulai')->default(date('Y'));
                $table->boolean('is_aktif')->default(true);
                $table->text('deskripsi')->nullable();
                $table->timestamps();
            });
            $tablesCreated[] = 'kurikulums';
        }

        // 2. Mata Pelajaran
        if (!Schema::connection($connection)->hasTable('mata_pelajarans')) {
            Schema::connection($connection)->create('mata_pelajarans', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 20)->unique();
                $table->string('nama', 150);
                $table->enum('kelompok', ['umum', 'kejuruan', 'muatan_lokal'])->default('umum');
                $table->enum('tingkat', ['X', 'XI', 'XII', 'semua'])->default('semua');
                $table->integer('jam_per_minggu')->default(2);
                $table->boolean('is_aktif')->default(true);
                $table->timestamps();
            });
            $tablesCreated[] = 'mata_pelajarans';
        }

        // 3. Jadwal Pelajaran
        if (!Schema::connection($connection)->hasTable('jadwal_pelajarans')) {
            Schema::connection($connection)->create('jadwal_pelajarans', function (Blueprint $table) {
                $table->id();
                $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
                $table->time('jam_mulai');
                $table->time('jam_selesai');
                $table->string('kelas', 50);
                $table->unsignedBigInteger('mata_pelajaran_id');
                $table->unsignedBigInteger('guru_user_id')->nullable();
                $table->string('ruang', 50)->nullable();
                $table->string('tahun_ajaran', 20)->default('2025/2026');
                $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
                $table->timestamps();
            });
            $tablesCreated[] = 'jadwal_pelajarans';
        }

        // 4. Nilai
        if (!Schema::connection($connection)->hasTable('nilais')) {
            Schema::connection($connection)->create('nilais', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('siswa_user_id');
                $table->unsignedBigInteger('guru_user_id')->nullable();
                $table->unsignedBigInteger('mata_pelajaran_id');
                $table->string('kelas', 50);
                $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
                $table->string('tahun_ajaran', 20)->default('2025/2026');
                $table->string('jenis_penilaian', 50);
                $table->decimal('nilai_angka', 5, 2);
                $table->text('capaian_kompetensi')->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
            $tablesCreated[] = 'nilais';
        }

        // 5. Presensi
        if (!Schema::connection($connection)->hasTable('presensis')) {
            Schema::connection($connection)->create('presensis', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('kelas', 50);
                $table->unsignedBigInteger('siswa_user_id');
                $table->enum('status', ['hadir', 'sakit', 'izin', 'alpa'])->default('hadir');
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();
            });
            $tablesCreated[] = 'presensis';
        }

        // 6. Raport
        if (!Schema::connection($connection)->hasTable('raports')) {
            Schema::connection($connection)->create('raports', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('siswa_user_id');
                $table->string('kelas', 50);
                $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
                $table->string('tahun_ajaran', 20)->default('2025/2026');
                $table->text('catatan_wali_kelas')->nullable();
                $table->string('status_kenaikan', 100)->nullable();
                $table->string('file_raport_pdf', 255)->nullable();
                $table->timestamps();
            });
            $tablesCreated[] = 'raports';
        }

        return [
            'success'  => true,
            'database' => config("database.connections.{$connection}.database", 'db_akademik'),
            'tables'   => $tablesCreated,
        ];
    }

    /**
     * Inisialisasi skema tabel lengkap untuk Sistem Informasi Tracer Study (db_tracer_study).
     */
    public function setupTracerStudyDatabase(string $connection = 'tracer_study'): array
    {
        $this->ensureUserTableExists($connection);
        $tablesCreated = ['users'];

        // 1. Profil Alumni
        if (!Schema::connection($connection)->hasTable('profil_alumnis')) {
            Schema::connection($connection)->create('profil_alumnis', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->string('nis', 30)->nullable();
                $table->string('nama', 150);
                $table->year('tahun_masuk')->nullable();
                $table->year('tahun_lulus');
                $table->string('jurusan', 50);
                $table->string('no_hp', 30)->nullable();
                $table->string('email', 150)->nullable();
                $table->string('linkedin', 255)->nullable();
                $table->text('alamat_domisili')->nullable();
                $table->enum('status_saat_ini', ['bekerja', 'kuliah', 'wirausaha', 'mencari_kerja', 'lainnya'])->default('mencari_kerja');
                $table->date('tanggal_update_status')->nullable();
                $table->timestamps();
            });
            $tablesCreated[] = 'profil_alumnis';
        }

        // 2. Riwayat Pekerjaan (Alumni Bekerja)
        if (!Schema::connection($connection)->hasTable('riwayat_pekerjaans')) {
            Schema::connection($connection)->create('riwayat_pekerjaans', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('profil_alumni_id');
                $table->string('nama_perusahaan', 150);
                $table->string('bidang_usaha', 100)->nullable();
                $table->string('jabatan', 100);
                $table->enum('jenis_pekerjaan', ['full_time', 'part_time', 'kontrak', 'freelance'])->default('full_time');
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai')->nullable();
                $table->boolean('masih_bekerja')->default(true);
                $table->string('kisaran_gaji', 50)->nullable();
                $table->enum('relevansi_jurusan', ['sangat_relevan', 'relevan', 'kurang_relevan', 'tidak_relevan'])->default('relevan');
                $table->text('alamat_kantor')->nullable();
                $table->timestamps();
            });
            $tablesCreated[] = 'riwayat_pekerjaans';
        }

        // 3. Riwayat Pendidikan (Alumni Kuliah / Studi Lanjut)
        if (!Schema::connection($connection)->hasTable('riwayat_pendidikans')) {
            Schema::connection($connection)->create('riwayat_pendidikans', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('profil_alumni_id');
                $table->string('perguruan_tinggi', 150);
                $table->enum('jenjang', ['D3', 'D4', 'S1', 'S2'])->default('S1');
                $table->string('program_studi', 100);
                $table->year('tahun_masuk');
                $table->year('tahun_lulus')->nullable();
                $table->enum('status_studi', ['masih_kuliah', 'lulus', 'cuti'])->default('masih_kuliah');
                $table->string('akreditasi', 10)->nullable();
                $table->enum('biaya_studi', ['beasiswa', 'mandiri'])->default('mandiri');
                $table->timestamps();
            });
            $tablesCreated[] = 'riwayat_pendidikans';
        }

        // 4. Riwayat Wirausaha (Alumni Berwirausaha)
        if (!Schema::connection($connection)->hasTable('riwayat_wirausahas')) {
            Schema::connection($connection)->create('riwayat_wirausahas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('profil_alumni_id');
                $table->string('nama_usaha', 150);
                $table->string('bidang_usaha', 100);
                $table->string('jenis_produk', 150)->nullable();
                $table->year('tahun_berdiri')->nullable();
                $table->integer('jumlah_karyawan')->default(0);
                $table->string('omzet_bulanan', 50)->nullable();
                $table->string('status_legalitas', 100)->nullable();
                $table->string('website_sosmed', 255)->nullable();
                $table->text('alamat_usaha')->nullable();
                $table->timestamps();
            });
            $tablesCreated[] = 'riwayat_wirausahas';
        }

        // 5. Kuesioner Tracer
        if (!Schema::connection($connection)->hasTable('kuesioner_tracers')) {
            Schema::connection($connection)->create('kuesioner_tracers', function (Blueprint $table) {
                $table->id();
                $table->string('judul', 200);
                $table->text('deskripsi')->nullable();
                $table->year('tahun_lulusan_target')->nullable();
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->boolean('is_aktif')->default(true);
                $table->timestamps();
            });
            $tablesCreated[] = 'kuesioner_tracers';
        }

        // 6. Jawaban Kuesioner
        if (!Schema::connection($connection)->hasTable('jawaban_kuesioners')) {
            Schema::connection($connection)->create('jawaban_kuesioners', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('kuesioner_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('profil_alumni_id')->nullable();
                $table->json('data_jawaban');
                $table->enum('status', ['draft', 'submitted'])->default('submitted');
                $table->dateTime('submitted_at')->nullable();
                $table->timestamps();
            });
            $tablesCreated[] = 'jawaban_kuesioners';
        }

        // 7. Statistik Tracer (Rekap BKK)
        if (!Schema::connection($connection)->hasTable('statistik_tracers')) {
            Schema::connection($connection)->create('statistik_tracers', function (Blueprint $table) {
                $table->id();
                $table->year('tahun_lulus')->unique();
                $table->integer('total_alumni')->default(0);
                $table->integer('total_mengisi')->default(0);
                $table->integer('jumlah_bekerja')->default(0);
                $table->integer('jumlah_kuliah')->default(0);
                $table->integer('jumlah_wirausaha')->default(0);
                $table->integer('jumlah_mencari_kerja')->default(0);
                $table->decimal('rata_rata_masa_tunggu_bulan', 4, 1)->default(0);
                $table->timestamps();
            });
            $tablesCreated[] = 'statistik_tracers';
        }

        return [
            'success'  => true,
            'database' => config("database.connections.{$connection}.database", 'db_tracer_study'),
            'tables'   => $tablesCreated,
        ];
    }
}
