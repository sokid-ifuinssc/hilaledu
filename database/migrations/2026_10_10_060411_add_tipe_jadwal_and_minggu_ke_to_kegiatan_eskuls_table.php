<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('rencana_kegiatan_eskuls')) {
            Schema::table('rencana_kegiatan_eskuls', function (Blueprint $table) {
                if (!Schema::hasColumn('rencana_kegiatan_eskuls', 'tipe_jadwal')) {
                    $table->enum('tipe_jadwal', ['minggu_efektif', 'kegiatan_tambahan'])->default('minggu_efektif')->after('pertemuan_ke');
                }
                if (!Schema::hasColumn('rencana_kegiatan_eskuls', 'minggu_ke')) {
                    $table->unsignedTinyInteger('minggu_ke')->nullable()->after('tipe_jadwal');
                }
            });
        }

        if (Schema::hasTable('laporan_kegiatan_eskuls')) {
            Schema::table('laporan_kegiatan_eskuls', function (Blueprint $table) {
                if (!Schema::hasColumn('laporan_kegiatan_eskuls', 'tipe_jadwal')) {
                    $table->enum('tipe_jadwal', ['minggu_efektif', 'kegiatan_tambahan'])->default('minggu_efektif')->after('pertemuan_ke');
                }
                if (!Schema::hasColumn('laporan_kegiatan_eskuls', 'minggu_ke')) {
                    $table->unsignedTinyInteger('minggu_ke')->nullable()->after('tipe_jadwal');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('rencana_kegiatan_eskuls')) {
            Schema::table('rencana_kegiatan_eskuls', function (Blueprint $table) {
                $table->dropColumn(['tipe_jadwal', 'minggu_ke']);
            });
        }

        if (Schema::hasTable('laporan_kegiatan_eskuls')) {
            Schema::table('laporan_kegiatan_eskuls', function (Blueprint $table) {
                $table->dropColumn(['tipe_jadwal', 'minggu_ke']);
            });
        }
    }
};
