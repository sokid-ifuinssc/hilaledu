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
        Schema::table('master_tugas_tambahan', function (Blueprint $table) {
            if (!Schema::hasColumn('master_tugas_tambahan', 'nominal_gaji')) {
                $table->decimal('nominal_gaji', 15, 2)->default(0)->after('kategori');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_tugas_tambahan', function (Blueprint $table) {
            if (Schema::hasColumn('master_tugas_tambahan', 'nominal_gaji')) {
                $table->dropColumn('nominal_gaji');
            }
        });
    }
};
