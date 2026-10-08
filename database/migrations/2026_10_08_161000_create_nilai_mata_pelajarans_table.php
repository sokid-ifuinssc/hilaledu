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
        if (!Schema::hasTable('nilai_mata_pelajarans')) {
            Schema::create('nilai_mata_pelajarans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();
                $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
                $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('guru_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('tahun_ajaran', 20)->default('2025/2026');
                $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
                $table->decimal('nilai_tugas', 5, 2)->nullable();
                $table->decimal('nilai_uts', 5, 2)->nullable();
                $table->decimal('nilai_uas', 5, 2)->nullable();
                $table->decimal('nilai_akhir', 5, 2)->nullable();
                $table->string('predikat', 5)->nullable();
                $table->text('catatan')->nullable();
                $table->boolean('is_sync_eskul')->default(false);
                $table->timestamps();

                $table->unique(['mata_pelajaran_id', 'kelas_id', 'siswa_id', 'tahun_ajaran', 'semester'], 'nilai_mapel_siswa_unique');
                $table->index(['kelas_id', 'mata_pelajaran_id']);
                $table->index(['siswa_id', 'tahun_ajaran', 'semester']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_mata_pelajarans');
    }
};
