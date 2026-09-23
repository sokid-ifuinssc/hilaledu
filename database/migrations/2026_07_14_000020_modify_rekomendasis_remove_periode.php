<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up(): void
    {
        // Ubah jenis_rekomendasi dari enum ke string di rekomendasis
        Schema::table('rekomendasis', function (Blueprint $table) {
            $table->string('jenis_rekomendasi', 100)->change();
        });

        // Drop kolom periode_laporan dari rekomendasis
        Schema::table('rekomendasis', function (Blueprint $table) {
            $table->dropColumn('periode_laporan');
        });

        // Ubah jenis_rekomendasi dari enum ke string di rekomendasi_kaprogs
        Schema::table('rekomendasi_kaprogs', function (Blueprint $table) {
            $table->string('jenis_rekomendasi', 100)->change();
        });
    }

    public function down(): void
    {
        Schema::table('rekomendasis', function (Blueprint $table) {
            $table->enum('periode_laporan', ['mingguan', 'bulanan', 'pra_uts', 'pra_uas'])->after('batas_waktu');
        });
    }
};
