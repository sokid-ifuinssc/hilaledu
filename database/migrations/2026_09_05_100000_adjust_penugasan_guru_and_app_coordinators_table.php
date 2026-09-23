<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom tugas_tambahan & jabatan_utama pada tabel users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'tugas_tambahan')) {
                $table->json('tugas_tambahan')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'jabatan_utama')) {
                $table->string('jabatan_utama', 100)->nullable()->default('Guru Pengajar')->after('tugas_tambahan');
            }
        });

        // 2. Modifikasi unique constraint pada app_coordinators
        // Mengizinkan satu guru memiliki lebih dari satu peran/tugas tambahan di aplikasi yang sama
        if (Schema::hasTable('app_coordinators')) {
            Schema::table('app_coordinators', function (Blueprint $table) {
                // Buat index biasa untuk application_id terlebih dahulu agar foreign key MySQL tidak terputus
                try {
                    $table->index('application_id', 'app_coordinators_app_id_idx');
                } catch (\Throwable $e) {}

                // Drop unique index lama
                try {
                    $table->dropUnique('app_coordinators_application_id_user_id_unique');
                } catch (\Throwable $e) {}

                // Tambahkan unique baru: kombinasi application_id + user_id + coordinator_role
                try {
                    $table->unique(['application_id', 'user_id', 'coordinator_role'], 'app_coords_app_user_role_unique');
                } catch (\Throwable $e) {}
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('app_coordinators')) {
            Schema::table('app_coordinators', function (Blueprint $table) {
                try {
                    $table->dropUnique('app_coords_app_user_role_unique');
                } catch (\Throwable $e) {}

                try {
                    $table->unique(['application_id', 'user_id']);
                } catch (\Throwable $e) {}
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tugas_tambahan')) {
                $table->dropColumn('tugas_tambahan');
            }
            if (Schema::hasColumn('users', 'jabatan_utama')) {
                $table->dropColumn('jabatan_utama');
            }
        });
    }
};
