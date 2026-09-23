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
        Schema::table('kegiatan_sekolahs', function (Blueprint $table) {
            if (!Schema::hasColumn('kegiatan_sekolahs', 'tanggal_selesai')) {
                $table->date('tanggal_selesai')->nullable()->after('tanggal_kegiatan');
            }
            if (!Schema::hasColumn('kegiatan_sekolahs', 'kalender_akademik_event_id')) {
                $table->unsignedBigInteger('kalender_akademik_event_id')->nullable()->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan_sekolahs', function (Blueprint $table) {
            if (Schema::hasColumn('kegiatan_sekolahs', 'tanggal_selesai')) {
                $table->dropColumn('tanggal_selesai');
            }
            if (Schema::hasColumn('kegiatan_sekolahs', 'kalender_akademik_event_id')) {
                $table->dropColumn('kalender_akademik_event_id');
            }
        });
    }
};
