<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('kategori_pelanggarans', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // "Ringan", "Sedang", "Berat"
            $table->integer('bobot_poin'); // 5, 10, 25
            $table->string('warna')->nullable(); // untuk badge warna di UI
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_pelanggarans');
    }
};
