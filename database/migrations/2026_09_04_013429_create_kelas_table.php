<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel 'kelas' sudah ada dari sistem sebelumnya, skip jika sudah ada
        if (Schema::hasTable('kelas')) {
            // Pastikan kolom wali_kelas ada (mungkin berbeda nama)
            if (!Schema::hasColumn('kelas', 'wali_kelas') && !Schema::hasColumn('kelas', 'wali_kelas_id')) {
                Schema::table('kelas', function (Blueprint $table) {
                    $table->string('wali_kelas')->nullable()->after('nama_kelas');
                });
            }
            if (!Schema::hasColumn('kelas', 'is_aktif')) {
                Schema::table('kelas', function (Blueprint $table) {
                    $table->boolean('is_aktif')->default(true)->after('wali_kelas_id');
                });
            }
            return;
        }

        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurusan_id')->constrained('jurusans')->onDelete('cascade');
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->onDelete('cascade');
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->string('nama', 50)->nullable();
            $table->string('nama_kelas', 50)->nullable();
            $table->foreignId('wali_kelas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('wali_kelas')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
