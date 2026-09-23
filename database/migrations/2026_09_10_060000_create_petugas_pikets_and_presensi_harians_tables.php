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
        // 1. Tabel Penugasan Petugas Piket (Tendik & Guru)
        if (!Schema::hasTable('petugas_pikets')) {
            Schema::create('petugas_pikets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'])->nullable();
                $table->date('tanggal')->nullable(); // Untuk penugasan tanggal spesifik / insidental
                $table->string('tahun_ajaran', 20)->default('2026/2027');
                $table->enum('semester', ['Ganjil', 'Genap'])->default('Ganjil');
                $table->string('lokasi_pos', 100)->default('Pos Piket Utama & Gerbang');
                $table->text('keterangan')->nullable();
                $table->boolean('is_aktif')->default(true);
                $table->foreignId('ditugaskan_oleh_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['user_id', 'hari', 'is_aktif'], 'idx_piket_user_hari');
                $table->index(['tanggal', 'is_aktif'], 'idx_piket_tgl');
            });
        }

        // 2. Tabel Presensi Kehadiran Harian Guru (Masuk, Pulang, dan Jeda Pelaksanaan)
        if (!Schema::hasTable('presensi_harian_gurus')) {
            Schema::create('presensi_harian_gurus', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guru_user_id')->constrained('users')->onDelete('cascade');
                $table->date('tanggal');
                
                // Presensi Masuk
                $table->time('jam_masuk')->nullable();
                $table->enum('status_masuk', [
                    'hadir',               // Hadir tepat waktu (07.00 - 07.30)
                    'terlambat',           // Hadir lewat dari 07.30
                    'hadir_sesuai_jam',    // Hadir langsung di jam ke-3+ tanpa absen masuk pagi
                    'izin',                // Izin resmi seharian
                    'sakit',               // Sakit seharian
                    'tugas_luar',          // Dinas / tugas luar sekolah
                    'alpa'                 // Tanpa keterangan
                ])->default('hadir');
                $table->integer('terlambat_masuk_menit')->default(0);

                // Presensi Pulang
                $table->time('jam_pulang')->nullable();
                $table->enum('status_pulang', [
                    'belum_pulang',        // Belum melakukan absen pulang
                    'tepat_waktu',         // Pulang mulai 14.10 WIB
                    'pulang_cepat'         // Pulang sebelum jam 14.00 WIB
                ])->default('belum_pulang');
                $table->integer('pulang_cepat_menit')->default(0);

                // Status Pelaksanaan Jam Jeda KBM (Jika jam mengajar pertama jam ke-3 ke atas dan sudah absen masuk 07.00 - 07.30)
                $table->enum('status_pelaksanaan_jeda', [
                    'tidak_ada',           // Mengajar jam 1 atau tidak ada jeda KBM
                    'tugas_mandiri',       // Standby / Tugas Mandiri / Persiapan Ajar di Sekolah
                    'standby',             // Standby di Ruang Guru
                    'izin_keluar'          // Izin keluar sementara sebelum jam mengajar tiba
                ])->default('tidak_ada');
                $table->text('keterangan_pelaksanaan_jeda')->nullable();
                
                // Form Izin Keluar (Jika memilih izin keluar)
                $table->text('izin_keluar_alasan')->nullable();
                $table->time('izin_keluar_jam_mulai')->nullable();
                $table->time('izin_keluar_jam_kembali')->nullable();
                $table->enum('izin_keluar_status', [
                    'diajukan',            // Notifikasi muncul di Petugas Piket
                    'disetujui_piket',     // Petugas piket sudah memverifikasi / mengetahui
                    'ditolak_piket'        // Petugas piket menolak izin
                ])->default('diajukan');
                $table->text('catatan_piket_izin_keluar')->nullable();

                // Catatan Umum & Dokumen Bukti
                $table->text('catatan')->nullable();
                $table->string('lampiran_bukti', 255)->nullable();

                // Auditor Absensi (Bila diabsenkan atau status diubah oleh Petugas Piket)
                $table->foreignId('diabsenkan_oleh_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('metode_masuk', ['mandiri', 'piket'])->default('mandiri');
                $table->enum('metode_pulang', ['mandiri', 'piket'])->default('mandiri');

                $table->timestamps();

                $table->unique(['guru_user_id', 'tanggal'], 'uniq_phg_guru_tgl');
                $table->index(['tanggal', 'status_masuk'], 'idx_phg_tgl_status');
                $table->index(['status_pelaksanaan_jeda', 'izin_keluar_status'], 'idx_phg_jeda_izin');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_harian_gurus');
        Schema::dropIfExists('petugas_pikets');
    }
};
