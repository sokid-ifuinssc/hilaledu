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
            if (!Schema::hasColumn('payroll_settings', 'hari_transport_default')) {
                $table->integer('hari_transport_default')->nullable()->after('transport_per_hari');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_settings', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_settings', 'hari_transport_default')) {
                $table->dropColumn('hari_transport_default');
            }
        });
    }
};
