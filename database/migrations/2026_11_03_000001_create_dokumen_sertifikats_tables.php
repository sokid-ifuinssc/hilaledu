<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('jenis_sertifikats')) {
            Schema::create('jenis_sertifikats', function (Blueprint $table) {
                $table->id();
                $table->string('kategori', 50)->index(); // kepesantrenan, kewirausahaan
                $table->string('nama_sertifikat');
                $table->string('deskripsi')->nullable();
                $table->boolean('is_aktif')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dokumen_sertifikat_siswas')) {
            Schema::create('dokumen_sertifikat_siswas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('siswa_id')->index();
                $table->string('kategori', 50)->index(); // kepesantrenan, kewirausahaan, skl, ijazah
                $table->unsignedBigInteger('jenis_sertifikat_id')->nullable()->index();
                $table->string('nama_dokumen');
                $table->string('nomor_dokumen')->nullable();
                $table->string('file_path');
                $table->string('file_name')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->text('keterangan')->nullable();
                $table->unsignedBigInteger('uploaded_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_sertifikat_siswas');
        Schema::dropIfExists('jenis_sertifikats');
    }
};

