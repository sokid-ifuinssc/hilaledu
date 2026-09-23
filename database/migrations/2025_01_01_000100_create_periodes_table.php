<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('periodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tahun_ajaran_id');
            $table->enum('jenis', ['mingguan', 'bulanan', 'pra_uts', 'pra_uas']);
            $table->string('nama'); // "Minggu ke-1 Juli 2026"
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index('tahun_ajaran_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodes');
    }
};
