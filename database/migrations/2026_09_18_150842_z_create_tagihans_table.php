<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_tagihan'); // misal: SPP Bulan Juli, Uang Gedung
            $table->decimal('nominal', 15, 2);
            $table->date('jatuh_tempo')->nullable();
            $table->string('status')->default('belum_lunas'); // belum_lunas, lunas
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};
