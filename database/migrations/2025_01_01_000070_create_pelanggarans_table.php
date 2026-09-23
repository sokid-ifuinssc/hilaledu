<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('pelanggarans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('jenis_pelanggaran_id');
            $table->date('tanggal_pelanggaran');
            $table->time('waktu_pelanggaran')->nullable();
            $table->text('deskripsi');
            $table->string('bukti')->nullable(); // path file foto/dokumen
            $table->integer('poin'); // poin yang dikurangi (copy dari jenis saat dicatat)
            $table->unsignedBigInteger('dicatat_oleh');
            $table->unsignedBigInteger('tahun_ajaran_id');
            $table->enum('status', ['dicatat', 'direkomendasikan', 'ditindaklanjuti', 'selesai'])->default('dicatat');
            $table->timestamps();

            $table->index('siswa_id');
            $table->index('jenis_pelanggaran_id');
            $table->index('dicatat_oleh');
            $table->index('tahun_ajaran_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggarans');
    }
};
