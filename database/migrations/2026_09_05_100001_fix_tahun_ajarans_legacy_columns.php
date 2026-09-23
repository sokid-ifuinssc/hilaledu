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
        if (Schema::hasTable('tahun_ajarans')) {
            // Ubah kolom legacy tanggal_mulai, tanggal_selesai, semester agar nullable / default
            try {
                DB::statement("ALTER TABLE `tahun_ajarans` MODIFY `semester` ENUM('ganjil','genap') NULL DEFAULT 'ganjil'");
                DB::statement("ALTER TABLE `tahun_ajarans` MODIFY `tanggal_mulai` DATE NULL DEFAULT NULL");
                DB::statement("ALTER TABLE `tahun_ajarans` MODIFY `tanggal_selesai` DATE NULL DEFAULT NULL");
            } catch (\Throwable $e) {}

            // Tambahkan kolom is_aktif jika belum ada dan sinkronkan dengan is_active
            Schema::table('tahun_ajarans', function (Blueprint $table) {
                if (!Schema::hasColumn('tahun_ajarans', 'is_aktif')) {
                    $table->boolean('is_aktif')->default(false)->after('is_active');
                }
            });

            try {
                DB::statement("UPDATE `tahun_ajarans` SET `is_aktif` = `is_active`");
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tahun_ajarans') && Schema::hasColumn('tahun_ajarans', 'is_aktif')) {
            Schema::table('tahun_ajarans', function (Blueprint $table) {
                $table->dropColumn('is_aktif');
            });
        }
    }
};
