<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mata_pelajarans')) {
            Schema::create('mata_pelajarans', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 50)->unique();
                $table->string('nama', 150);
                $table->string('kategori', 100)->nullable();
                $table->string('sub_kategori', 100)->nullable();
                $table->integer('urutan')->default(0);
                $table->string('kelompok', 50)->nullable();
                $table->string('tingkat', 50)->nullable();
                $table->integer('jam_per_minggu')->default(4);
                $table->unsignedBigInteger('guru_user_id')->nullable();
                $table->boolean('is_aktif')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('jadwal_pelajarans')) {
            Schema::create('jadwal_pelajarans', function (Blueprint $table) {
                $table->id();
                $table->string('hari', 20);
                $table->integer('jam_ke_mulai')->nullable();
                $table->integer('jam_ke_selesai')->nullable();
                $table->time('jam_mulai');
                $table->time('jam_selesai');
                $table->string('kelas', 50);
                $table->unsignedBigInteger('mata_pelajaran_id');
                $table->unsignedBigInteger('guru_user_id');
                $table->string('ruang', 50)->nullable();
                $table->string('tahun_ajaran', 20)->nullable();
                $table->string('semester', 20)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelajarans');
        Schema::dropIfExists('mata_pelajarans');
    }
};
