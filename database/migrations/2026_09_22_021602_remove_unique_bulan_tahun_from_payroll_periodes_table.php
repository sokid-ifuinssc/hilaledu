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
        Schema::table('payroll_periodes', function (Blueprint $table) {
            $table->dropUnique('payroll_periodes_bulan_tahun_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_periodes', function (Blueprint $table) {
            $table->unique(['bulan', 'tahun']);
        });
    }
};
