<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('tanggal_pentings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tahun_ajaran_id');
            $table->string('nama'); // "UTS Ganjil", "UAS Ganjil", "Mulai Sekolah"
            $table->enum('jenis', ['mulai_sekolah', 'uts', 'uas', 'libur', 'lainnya']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('tahun_ajaran_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tanggal_pentings');
    }
};
