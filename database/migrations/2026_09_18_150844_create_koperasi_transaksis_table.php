<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('koperasi_transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_id')->constrained('koperasi_anggotas')->cascadeOnDelete();
            $table->string('jenis_transaksi'); // simpanan_pokok, simpanan_wajib, penarikan, pinjaman, angsuran
            $table->decimal('nominal', 15, 2);
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('koperasi_transaksis');
    }
};
