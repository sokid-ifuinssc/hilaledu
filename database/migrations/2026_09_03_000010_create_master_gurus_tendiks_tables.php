<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk setup tabel master di db_hilaledu.
 * 
 * Tabel master ini bisa diakses oleh aplikasi lain yang terhubung
 * ke database db_hilaledu. Migration ini SAFE — hanya membuat tabel
 * jika belum ada.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * Semua tabel dibuat di koneksi default (mysql = db_hilaledu)
     */
    public function up(): void
    {
        // =====================================================
        // TABEL USERS — sudah ada dari migration sebelumnya,
        // akan dibuat otomatis oleh 0001_01_01_000000 migration
        // =====================================================

        // =====================================================
        // TABEL GURUS (BARU)
        // Data lengkap guru/pendidik, terpisah dari tabel users
        // =====================================================
        if (!Schema::hasTable('gurus')) {
            Schema::create('gurus', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('nip')->nullable()->unique();
                $table->string('nuptk')->nullable()->unique();
                $table->string('nama_lengkap');
                $table->enum('jenis_kelamin', ['L', 'P']);
                $table->string('tempat_lahir')->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->text('alamat')->nullable();
                $table->string('no_hp')->nullable();
                $table->string('email')->nullable();
                $table->string('jabatan')->nullable(); // guru mapel, guru BK, waka, dll
                $table->string('bidang_studi')->nullable(); // matematika, bahasa indonesia, dll
                $table->enum('status_kepegawaian', ['PNS', 'non-PNS', 'honorer', 'kontrak'])->default('non-PNS');
                $table->string('foto')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // =====================================================
        // TABEL TENDIKS (BARU)
        // Data lengkap tenaga kependidikan (TU, bendahara, dll)
        // =====================================================
        if (!Schema::hasTable('tendiks')) {
            Schema::create('tendiks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('nip')->nullable()->unique();
                $table->string('nama_lengkap');
                $table->enum('jenis_kelamin', ['L', 'P']);
                $table->string('tempat_lahir')->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->text('alamat')->nullable();
                $table->string('no_hp')->nullable();
                $table->string('email')->nullable();
                $table->string('jabatan')->nullable(); // TU, bendahara, operator, satpam, dll
                $table->string('bagian')->nullable(); // tata usaha, keuangan, perpustakaan, dll
                $table->enum('status_kepegawaian', ['PNS', 'non-PNS', 'honorer', 'kontrak'])->default('non-PNS');
                $table->string('foto')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tendiks');
        Schema::dropIfExists('gurus');
    }
};
