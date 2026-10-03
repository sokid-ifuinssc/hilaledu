<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payroll_periodes') && !Schema::hasColumn('payroll_periodes', 'tampil_ke_guru')) {
            Schema::table('payroll_periodes', function (Blueprint $table) {
                $table->boolean('tampil_ke_guru')->default(true)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payroll_periodes') && Schema::hasColumn('payroll_periodes', 'tampil_ke_guru')) {
            Schema::table('payroll_periodes', function (Blueprint $table) {
                $table->dropColumn('tampil_ke_guru');
            });
        }
    }
};
