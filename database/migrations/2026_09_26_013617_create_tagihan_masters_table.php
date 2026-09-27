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
        Schema::create('tagihan_masters', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tagihan'); // e.g. SPP Bulan Juli, Seragam, Ujian Akhir
            $table->string('jenis'); // spp, sekali, tahunan, kondisional
            $table->decimal('nominal', 15, 2);
            $table->boolean('is_rutin')->default(false); // kalau true mungkin buat generate massal SPP otomatis
            $table->string('tingkat_kelas')->nullable(); // X, XI, XII atau null untuk semua
            $table->string('jurusan')->nullable(); // TKR, TKJ, atau null untuk semua
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->nullOnDelete();
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan_masters');
    }
};
