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
        // 1. Master Ekstrakurikuler
        if (!Schema::hasTable('ekstrakurikulers')) {
            Schema::create('ekstrakurikulers', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 100);
                $table->string('kode', 30)->nullable()->unique();
                $table->text('deskripsi')->nullable();
                $table->string('hari', 50)->default('Jumat');
                $table->time('jam_mulai')->nullable();
                $table->time('jam_selesai')->nullable();
                $table->string('tempat', 150)->nullable();
                $table->foreignId('pembina_guru_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('ketua_siswa_id')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('is_aktif')->default(true);
                $table->timestamps();
            });
        }

        // 2. Anggota Ekstrakurikuler (Siswa dikelompokkan ke eskul)
        if (!Schema::hasTable('anggota_ekstrakurikulers')) {
            Schema::create('anggota_ekstrakurikulers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->cascadeOnDelete();
                $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
                $table->string('jabatan', 50)->default('Anggota'); // Ketua, Wakil Ketua, Sekretaris, Bendahara, Anggota
                $table->string('tahun_ajaran', 20)->default('2025/2026');
                $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
                $table->decimal('nilai_angka', 5, 2)->nullable();
                $table->string('nilai_huruf', 5)->nullable(); // A, B, C, D
                $table->text('catatan_nilai')->nullable();
                $table->timestamps();

                $table->unique(['ekstrakurikuler_id', 'siswa_id', 'tahun_ajaran', 'semester'], 'anggota_eskul_unique');
            });
        }

        // 3. Rencana Kegiatan Eskul (Diinput Pembina)
        if (!Schema::hasTable('rencana_kegiatan_eskuls')) {
            Schema::create('rencana_kegiatan_eskuls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->cascadeOnDelete();
                $table->integer('pertemuan_ke')->default(1);
                $table->date('tanggal_rencana');
                $table->string('nama_kegiatan', 255);
                $table->text('deskripsi_rencana')->nullable();
                $table->text('target_pencapaian')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['ekstrakurikuler_id', 'tanggal_rencana'], 'idx_ren_eskul_tgl');
            });
        }

        // 4. Laporan Realisasi Kegiatan Eskul (Seperti Laporan Guru / Jurnal KBM)
        if (!Schema::hasTable('laporan_kegiatan_eskuls')) {
            Schema::create('laporan_kegiatan_eskuls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->cascadeOnDelete();
                $table->foreignId('rencana_kegiatan_id')->nullable()->constrained('rencana_kegiatan_eskuls')->nullOnDelete();
                $table->date('tanggal_kegiatan');
                $table->integer('pertemuan_ke')->default(1);
                $table->string('nama_kegiatan', 255);
                $table->text('ringkasan_materi');
                $table->enum('status_pelaksanaan', ['sesuai_rencana', 'penyesuaian', 'terlaksana_penuh', 'ditunda'])->default('sesuai_rencana');
                $table->text('catatan_kegiatan')->nullable();
                $table->string('foto_dokumentasi', 255)->nullable();
                $table->integer('jumlah_hadir')->default(0);
                $table->integer('jumlah_izin')->default(0);
                $table->integer('jumlah_sakit')->default(0);
                $table->integer('jumlah_alpa')->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['ekstrakurikuler_id', 'tanggal_kegiatan'], 'idx_lap_eskul_tgl');
            });
        }

        // 5. Presensi Eskul (Pembina, Siswa Mandiri, atau Ketua yang ditugaskan)
        if (!Schema::hasTable('presensi_eskuls')) {
            Schema::create('presensi_eskuls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikulers')->cascadeOnDelete();
                $table->foreignId('laporan_kegiatan_id')->nullable()->constrained('laporan_kegiatan_eskuls')->nullOnDelete();
                $table->foreignId('rencana_kegiatan_id')->nullable()->constrained('rencana_kegiatan_eskuls')->nullOnDelete();
                $table->date('tanggal');
                $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
                $table->enum('status', ['Hadir', 'Izin', 'Sakit', 'Alpa'])->default('Hadir');
                $table->string('keterangan', 255)->nullable();
                $table->enum('metode_absen', ['pembina', 'ketua', 'mandiri'])->default('pembina');
                $table->foreignId('diinput_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['ekstrakurikuler_id', 'tanggal', 'siswa_id'], 'idx_pres_eskul_uniq');
                $table->index(['ekstrakurikuler_id', 'tanggal'], 'idx_pres_eskul_tgl');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_eskuls');
        Schema::dropIfExists('laporan_kegiatan_eskuls');
        Schema::dropIfExists('rencana_kegiatan_eskuls');
        Schema::dropIfExists('anggota_ekstrakurikulers');
        Schema::dropIfExists('ekstrakurikulers');
    }
};
