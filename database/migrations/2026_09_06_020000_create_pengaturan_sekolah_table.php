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
        if (!Schema::hasTable('pengaturan_sekolah')) {
            Schema::create('pengaturan_sekolah', function (Blueprint $table) {
                $table->id();
                $table->string('nama_sekolah', 200)->default('SMK PLUS AL HILAL');
                $table->string('npsn', 50)->nullable();
                $table->unsignedBigInteger('kepala_sekolah_id')->nullable();
                $table->text('alamat')->nullable();
                $table->string('email', 150)->nullable();
                $table->string('telepon', 50)->nullable();
                $table->string('website', 150)->nullable();
                $table->string('logo', 255)->nullable();
                $table->timestamps();

                $table->foreign('kepala_sekolah_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_sekolah');
    }
};
