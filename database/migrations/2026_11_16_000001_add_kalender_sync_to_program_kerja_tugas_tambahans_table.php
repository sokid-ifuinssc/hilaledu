<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('program_kerja_tugas_tambahans')) {
            return;
        }

        Schema::table('program_kerja_tugas_tambahans', function (Blueprint $table) {
            if (!Schema::hasColumn('program_kerja_tugas_tambahans', 'tanggal_mulai')) {
                $table->date('tanggal_mulai')->nullable()->after('target_waktu');
            }
            if (!Schema::hasColumn('program_kerja_tugas_tambahans', 'tanggal_selesai')) {
                $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            }
            if (!Schema::hasColumn('program_kerja_tugas_tambahans', 'kalender_akademik_event_id')) {
                $table->unsignedBigInteger('kalender_akademik_event_id')->nullable()->after('catatan');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('program_kerja_tugas_tambahans')) {
            return;
        }

        Schema::table('program_kerja_tugas_tambahans', function (Blueprint $table) {
            foreach (['kalender_akademik_event_id', 'tanggal_selesai', 'tanggal_mulai'] as $col) {
                if (Schema::hasColumn('program_kerja_tugas_tambahans', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
