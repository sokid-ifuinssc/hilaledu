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
        // 1. Tabel Program Kerja / Rencana Tugas Tambahan
        Schema::create('program_kerja_tugas_tambahans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_user_id')->constrained('users')->onDelete('cascade');
            $table->string('tugas_tambahan'); // e.g. Wakasek Kurikulum & Akademik, Kepala Sekolah, Wali Kelas, dll.
            $table->string('tahun_ajaran')->default('2026/2027');
            $table->string('semester')->default('Ganjil'); // Ganjil, Genap, 1 Tahun
            $table->string('nama_program'); // Nama kegiatan / program kerja
            $table->text('tujuan')->nullable();
            $table->string('target_waktu')->nullable(); // misal: September 2026 / Triwulan I
            $table->text('indikator_keberhasilan')->nullable();
            $table->decimal('anggaran', 14, 2)->nullable()->default(0);
            $table->enum('status', ['terencana', 'sedang_berjalan', 'tercapai', 'tertunda'])->default('terencana');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Realisasi Kerja Tugas Tambahan
        Schema::create('realisasi_tugas_tambahans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('program_kerja_id')->nullable()->constrained('program_kerja_tugas_tambahans')->nullOnDelete();
            $table->string('tugas_tambahan');
            $table->date('tanggal_pelaksanaan');
            $table->string('judul_kegiatan');
            $table->text('uraian_kegiatan');
            $table->text('hasil_capaian');
            $table->text('kendala_solusi')->nullable();
            $table->string('foto_dokumentasi')->nullable();
            $table->string('dokumen_pendukung')->nullable();
            $table->enum('status_validasi', ['draft', 'diajukan', 'disetujui_kepsek'])->default('disetujui_kepsek');
            $table->text('catatan_pimpinan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasi_tugas_tambahans');
        Schema::dropIfExists('program_kerja_tugas_tambahans');
    }
};
