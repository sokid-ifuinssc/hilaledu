<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Integrasi Modul Prakerin SMK Plus Al Hilal
     */
    public function up(): void
    {
        // 1. Tabel DU/DI (Dunia Usaha & Dunia Industri) Mitra
        if (!Schema::hasTable('dudi')) {
            Schema::create('dudi', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 150);
                $table->text('alamat')->nullable();
                $table->string('no_telp', 20)->nullable();
                $table->string('email', 100)->nullable();
                $table->string('bidang_usaha', 100)->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        // 2. Tabel Pembimbing dari pihak DU/DI
        if (!Schema::hasTable('pembimbing_dudi')) {
            Schema::create('pembimbing_dudi', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dudi_id')->constrained('dudi')->cascadeOnDelete();
                $table->string('nama', 100);
                $table->string('jabatan', 100)->nullable();
                $table->string('no_hp', 20)->nullable();
                $table->string('email', 100)->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        // 3. Tabel Periode Prakerin
        if (!Schema::hasTable('periode_prakerin')) {
            Schema::create('periode_prakerin', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
                $table->string('nama', 100);
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->boolean('aktif')->default(false);
                $table->timestamps();
            });
        }

        // 4. Tabel Penempatan Prakerin Siswa
        if (!Schema::hasTable('penempatan')) {
            Schema::create('penempatan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('periode_prakerin_id')->constrained('periode_prakerin')->cascadeOnDelete();
                $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
                $table->foreignId('dudi_id')->constrained('dudi')->cascadeOnDelete();
                $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
                $table->foreignId('pembimbing_dudi_id')->constrained('pembimbing_dudi')->cascadeOnDelete();

                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->enum('status', ['belum_mulai', 'aktif', 'selesai'])->default('belum_mulai');
                $table->text('keterangan')->nullable();

                $table->timestamps();

                // Satu siswa hanya satu penempatan per periode
                $table->unique(['periode_prakerin_id', 'siswa_id'], 'penempatan_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penempatan');
        Schema::dropIfExists('periode_prakerin');
        Schema::dropIfExists('pembimbing_dudi');
        Schema::dropIfExists('dudi');
    }
};
