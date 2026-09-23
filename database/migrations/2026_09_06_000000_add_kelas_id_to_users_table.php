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
        if (!Schema::hasColumn('users', 'kelas_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('kelas_id')->nullable()->after('role')->constrained('kelas')->nullOnDelete();
            });
        }

        // Backfill data kelas_id dari tabel legacy siswas jika ada
        if (Schema::hasTable('siswas') && Schema::hasColumn('siswas', 'kelas_id') && Schema::hasColumn('siswas', 'user_id')) {
            try {
                DB::statement("
                    UPDATE users u
                    INNER JOIN siswas s ON u.id = s.user_id
                    SET u.kelas_id = s.kelas_id
                    WHERE s.kelas_id IS NOT NULL AND u.role = 'siswa'
                ");
            } catch (\Throwable $e) {
                // Abaikan jika tabel siswas kosong atau ada kecocokan yang gagal
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'kelas_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['kelas_id']);
                $table->dropColumn('kelas_id');
            });
        }
    }
};
