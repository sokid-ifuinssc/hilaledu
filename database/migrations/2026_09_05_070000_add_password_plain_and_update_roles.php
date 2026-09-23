<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom password_plain jika belum ada
        if (!Schema::hasColumn('users', 'password_plain')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('password_plain')->nullable()->after('password');
            });
        }

        // 2. Hapus user dengan role 'operator' atau username 'operator'
        DB::table('users')->where('role', 'operator')->orWhere('username', 'operator')->delete();

        // 3. Update ENUM role di tabel users (menghapus admin dan operator)
        // Menggunakan raw statement MySQL karena DB::statement mendukung alter enum langsung
        try {
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('superadmin', 'guru', 'tendik', 'siswa') NOT NULL DEFAULT 'siswa'");
        } catch (\Throwable $e) {
            // Fallback jika ada constraint tertentu
        }

        // 4. Isi password_plain untuk akun demo dan data existing jika masih null
        // Superadmin, guru, tendik, siswa
        DB::table('users')->whereNull('password_plain')->orWhere('password_plain', '')->update([
            'password_plain' => 'password123',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'password_plain')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('password_plain');
            });
        }

        try {
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('superadmin', 'admin', 'operator', 'guru', 'tendik', 'siswa') NOT NULL DEFAULT 'siswa'");
        } catch (\Throwable $e) {
        }
    }
};
