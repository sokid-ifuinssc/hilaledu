<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('siswas')) {
            if (DB::getDriverName() !== 'sqlite') {
                Schema::table('siswas', function (Blueprint $table) {
                    try {
                        $table->dropForeign('siswas_kelas_id_foreign');
                    } catch (\Throwable $e) {}

                    $table->unsignedBigInteger('kelas_id')->nullable()->change();
                    $table->foreign('kelas_id')->references('id')->on('kelas')->nullOnDelete();
                });
            } else {
                Schema::table('siswas', function (Blueprint $table) {
                    $table->unsignedBigInteger('kelas_id')->nullable()->change();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('siswas')) {
            if (DB::getDriverName() !== 'sqlite') {
                Schema::table('siswas', function (Blueprint $table) {
                    try {
                        $table->dropForeign(['kelas_id']);
                    } catch (\Throwable $e) {}

                    $table->unsignedBigInteger('kelas_id')->nullable(false)->change();
                    $table->foreign('kelas_id')->references('id')->on('kelas')->cascadeOnDelete();
                });
            } else {
                Schema::table('siswas', function (Blueprint $table) {
                    $table->unsignedBigInteger('kelas_id')->nullable(false)->change();
                });
            }
        }
    }
};
