<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom yang belum ada di tabel users lama:
     * username, role, avatar — tanpa menghapus data existing.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cek dan tambahkan kolom 'username' jika belum ada
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->unique()->nullable()->after('name');
            }

            // Cek dan tambahkan kolom 'role' jika belum ada
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['superadmin', 'admin', 'operator', 'guru', 'tendik', 'siswa'])
                      ->default('siswa')
                      ->after('password');
            }

            // Cek dan tambahkan kolom 'avatar' jika belum ada
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('is_active');
            }
        });

        // Isi username dari nama jika null (untuk data existing)
        // Gunakan email prefix sebagai username default
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                UPDATE users
                SET username = CONCAT(LOWER(REPLACE(SUBSTRING_INDEX(email, '@', 1), '.', '_')), '_', id)
                WHERE username IS NULL OR username = ''
            ");
        } else {
            DB::statement("
                UPDATE users
                SET username = 'user_' || id
                WHERE username IS NULL OR username = ''
            ");
        }

        // Setelah data diisi, baru buat NOT NULL
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'role', 'avatar']);
        });
    }
};
