<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan_presensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_sekolah_id')->constrained('kegiatan_sekolahs')->cascadeOnDelete();
            $table->foreignId('guru_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit'])->default('hadir');
            $table->timestamp('waktu_presensi')->nullable();
            $table->text('catatan')->nullable();
            $table->string('foto_bukti')->nullable();
            $table->timestamps();

            $table->unique(['kegiatan_sekolah_id', 'guru_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_presensis');
    }
};
