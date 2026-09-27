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
        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->json('detail_tunjangan_tugas')->nullable()->after('tunjangan_jabatan');
            $table->decimal('transport_per_hari', 15, 2)->default(20000)->after('tunjangan_kehadiran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->dropColumn(['detail_tunjangan_tugas', 'transport_per_hari']);
        });
    }
};
