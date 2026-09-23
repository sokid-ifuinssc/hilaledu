<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracer_alumnis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('tahun_lulus');
            $table->string('status_saat_ini')->nullable(); // Bekerja, Kuliah, Wirausaha, Mencari Kerja
            $table->string('nama_instansi')->nullable(); // Nama Tempat Kerja / Kampus
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracer_alumnis');
    }
};
