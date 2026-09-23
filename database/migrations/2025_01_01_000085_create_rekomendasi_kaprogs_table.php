<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('rekomendasi_kaprogs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pelanggaran_id');
            $table->unsignedBigInteger('kaprog_id');
            $table->enum('jenis_rekomendasi', [
                'pembinaan',
                'peringatan',
                'konsultasi_ortu',
                'pengarahan',
                'mutasi_kelas'
            ]);
            $table->text('deskripsi_rekomendasi');
            $table->date('batas_waktu')->nullable();
            $table->date('tanggal_rekomendasi');
            $table->enum('status', ['dikirim', 'dibaca', 'ditindaklanjuti', 'selesai'])->default('dikirim');
            $table->timestamps();

            $table->index('pelanggaran_id');
            $table->index('kaprog_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_kaprogs');
    }
};
