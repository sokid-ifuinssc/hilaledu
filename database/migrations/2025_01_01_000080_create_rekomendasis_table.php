<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('rekomendasis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pelanggaran_id');
            $table->unsignedBigInteger('guru_bk_id');
            $table->enum('jenis_rekomendasi', [
                'peringatan_lisan',
                'peringatan_tertulis',
                'panggilan_orang_tua',
                'skorsing',
                'pengembalian'
            ]);
            $table->text('deskripsi_rekomendasi');
            $table->date('batas_waktu')->nullable();
            $table->enum('periode_laporan', ['mingguan', 'bulanan', 'pra_uts', 'pra_uas']);
            $table->date('tanggal_rekomendasi');
            $table->timestamps();

            $table->index('pelanggaran_id');
            $table->index('guru_bk_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekomendasis');
    }
};
