<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_pelajarans', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_pelajarans', 'jam_ke_mulai')) {
                $table->unsignedTinyInteger('jam_ke_mulai')->nullable()->after('hari');
            }
            if (!Schema::hasColumn('jadwal_pelajarans', 'jam_ke_selesai')) {
                $table->unsignedTinyInteger('jam_ke_selesai')->nullable()->after('jam_ke_mulai');
            }
            // Pastikan ruang nullable
            if (Schema::hasColumn('jadwal_pelajarans', 'ruang')) {
                $table->string('ruang', 50)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_pelajarans', function (Blueprint $table) {
            if (Schema::hasColumn('jadwal_pelajarans', 'jam_ke_mulai')) {
                $table->dropColumn('jam_ke_mulai');
            }
            if (Schema::hasColumn('jadwal_pelajarans', 'jam_ke_selesai')) {
                $table->dropColumn('jam_ke_selesai');
            }
        });
    }
};
