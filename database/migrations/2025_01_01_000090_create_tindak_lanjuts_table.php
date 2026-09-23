<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('tindak_lanjuts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rekomendasi_id')->nullable();
            $table->unsignedBigInteger('rekomendasi_kaprog_id')->nullable();
            $table->unsignedBigInteger('wali_kelas_id');
            $table->text('tindakan_yang_dilakukan');
            $table->date('tanggal_tindak_lanjut');
            $table->text('hasil')->nullable();
            $table->string('bukti_tindak_lanjut')->nullable();
            $table->enum('status', ['belum_diproses', 'sedang_diproses', 'selesai'])->default('belum_diproses');
            $table->text('catatan_bk')->nullable();
            $table->timestamps();

            $table->index('rekomendasi_id');
            $table->index('rekomendasi_kaprog_id');
            $table->index('wali_kelas_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjuts');
    }
};
