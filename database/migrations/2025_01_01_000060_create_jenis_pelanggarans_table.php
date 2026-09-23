<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('jenis_pelanggarans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kategori_pelanggaran_id');
            $table->string('kode');
            $table->string('nama'); // "Terlambat", "Bolos", "Merokok"
            $table->integer('poin'); // Poin yang dikurangi
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->index('kategori_pelanggaran_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_pelanggarans');
    }
};
