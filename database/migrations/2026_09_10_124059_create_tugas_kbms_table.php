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
        Schema::create('tugas_kbms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_pelajaran_id')->constrained('jadwal_pelajarans')->cascadeOnDelete();
            $table->date('tanggal');
            $table->foreignId('guru_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dibuat_oleh_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('deskripsi_tugas');
            $table->string('file_lampiran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas_kbms');
    }
};
