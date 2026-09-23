<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pengelola_akademiks')) {
            Schema::create('pengelola_akademiks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->boolean('can_manage_jadwal')->default(true);
                $table->boolean('can_manage_mapel')->default(true);
                $table->boolean('can_view_laporan_kehadiran')->default(true);
                $table->boolean('can_view_laporan_kbm')->default(true);
                $table->unsignedBigInteger('ditunjuk_oleh_user_id')->nullable();
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();

                $table->unique('user_id');
                $table->index('ditunjuk_oleh_user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengelola_akademiks');
    }
};
