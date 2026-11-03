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
                $table->boolean('is_generated')->default(false);
                $table->string('nama_kegiatan')->nullable();
                $table->text('deskripsi_kegiatan')->nullable();
                $table->string('predikat', 50)->nullable();
                $table->string('tempat_terbit', 100)->nullable();
                $table->string('tanggal_terbit', 100)->nullable();
                $table->string('pejabat_1_jabatan', 150)->nullable();
                $table->string('pejabat_1_nama', 150)->nullable();
                $table->string('pejabat_1_nip', 100)->nullable();
                $table->string('pejabat_2_jabatan', 150)->nullable();
                $table->string('pejabat_2_nama', 150)->nullable();
                $table->string('pejabat_2_nip', 100)->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'akses_dokumen_dibatasi')) {
                    $table->boolean('akses_dokumen_dibatasi')->default(false)->after('is_active');
                }
                if (!Schema::hasColumn('users', 'catatan_pembatasan_dokumen')) {
                    $table->string('catatan_pembatasan_dokumen', 500)->nullable()->after('akses_dokumen_dibatasi');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_sertifikat_siswas');
        Schema::dropIfExists('jenis_sertifikats');
    }
};

