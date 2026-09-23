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
        // 1. Minggu Efektif
        if (!Schema::hasTable('minggu_efektifs')) {
            Schema::create('minggu_efektifs', function (Blueprint $table) {
                $table->id();
                $table->string('tahun_ajaran', 20)->default('2025/2026');
                $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
                $table->unsignedBigInteger('mata_pelajaran_id');
                $table->unsignedBigInteger('guru_user_id');
                $table->string('kelas', 50);
                $table->integer('total_minggu')->default(26);
                $table->integer('total_tidak_efektif')->default(8);
                $table->integer('total_efektif')->default(18);
                $table->integer('jam_per_minggu')->default(4);
                $table->integer('total_jam_efektif')->default(72);
                $table->json('rincian_bulanan')->nullable();
                $table->json('distribusi_jam')->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->index(['guru_user_id', 'tahun_ajaran', 'semester']);
            });
        }

        // 2. Capaian Pembelajaran (CP)
        if (!Schema::hasTable('capaian_pembelajarans')) {
            Schema::create('capaian_pembelajarans', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('mata_pelajaran_id');
                $table->unsignedBigInteger('guru_user_id');
                $table->enum('fase', ['E', 'F'])->default('E');
                $table->enum('tingkat', ['X', 'XI', 'XII'])->default('X');
                $table->string('elemen', 150);
                $table->text('deskripsi');
                $table->string('tahun_ajaran', 20)->default('2025/2026');
                $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
                $table->timestamps();

                $table->index(['guru_user_id', 'mata_pelajaran_id']);
            });
        }

        // 3. Tujuan Pembelajaran (TP)
        if (!Schema::hasTable('tujuan_pembelajarans')) {
            Schema::create('tujuan_pembelajarans', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('capaian_pembelajaran_id');
                $table->string('kode_tp', 30);
                $table->text('deskripsi');
                $table->text('kktp')->nullable();
                $table->integer('alokasi_jp')->default(4);
                $table->string('lingkup_materi', 255)->nullable();
                $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
                $table->integer('urutan')->default(1);
                $table->timestamps();

                $table->index('capaian_pembelajaran_id');
            });
        }

        // 4. Rencana Pembelajaran Harian (Modul Ajar / RPP Sesuai Jadwal)
        if (!Schema::hasTable('rencana_pembelajarans')) {
            Schema::create('rencana_pembelajarans', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('jadwal_pelajaran_id');
                $table->unsignedBigInteger('guru_user_id');
                $table->unsignedBigInteger('tujuan_pembelajaran_id')->nullable();
                $table->date('tanggal_rencana');
                $table->integer('pertemuan_ke')->default(1);
                $table->string('materi_pokok', 255);
                $table->text('aktivitas_pendahuluan')->nullable();
                $table->text('aktivitas_inti')->nullable();
                $table->text('aktivitas_penutup')->nullable();
                $table->text('media_sumber')->nullable();
                $table->string('bentuk_asesmen', 150)->default('Formatif (Observasi & Lembar Tugas)');
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->index(['guru_user_id', 'tanggal_rencana']);
                $table->index('jadwal_pelajaran_id');
            });
        }

        // 5. Laporan Realisasi KBM
        if (!Schema::hasTable('laporan_kbms')) {
            Schema::create('laporan_kbms', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('jadwal_pelajaran_id');
                $table->unsignedBigInteger('rencana_pembelajaran_id')->nullable();
                $table->unsignedBigInteger('guru_user_id');
                $table->date('tanggal_realisasi');
                $table->enum('kesesuaian_rencana', ['sesuai', 'sebagian', 'tidak_sesuai', 'materi_pengganti'])->default('sesuai');
                $table->text('keterangan_kesesuaian')->nullable();
                $table->enum('status_pelaksanaan', ['sesuai_jadwal', 'ganti_hari', 'jam_tambahan', 'lainnya'])->default('sesuai_jadwal');
                $table->text('keterangan_pelaksanaan')->nullable();
                $table->text('catatan_kegiatan')->nullable();
                $table->string('foto_dokumentasi', 255)->nullable();
                $table->integer('jumlah_siswa_hadir')->default(0);
                $table->integer('jumlah_siswa_tidak_hadir')->default(0);
                $table->integer('jumlah_siswa_total')->default(0);
                $table->timestamps();

                $table->index(['guru_user_id', 'tanggal_realisasi']);
                $table->index('jadwal_pelajaran_id');
            });
        }

        // 6. Presensi Siswa per Sesi KBM
        if (!Schema::hasTable('laporan_kbm_presensis')) {
            Schema::create('laporan_kbm_presensis', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('laporan_kbm_id');
                $table->unsignedBigInteger('siswa_user_id');
                $table->enum('status', ['hadir', 'sakit', 'izin', 'alpa', 'terlambat'])->default('hadir');
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();

                $table->index(['laporan_kbm_id', 'siswa_user_id']);
            });
        }

        // 7. Absensi Kehadiran Guru Mengajar
        if (!Schema::hasTable('absensi_gurus')) {
            Schema::create('absensi_gurus', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('jadwal_pelajaran_id');
                $table->unsignedBigInteger('guru_user_id');
                $table->date('tanggal');
                $table->time('jam_absen');
                $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'tugas_luar'])->default('hadir');
                $table->integer('terlambat_menit')->default(0);
                $table->text('catatan')->nullable();
                $table->string('lampiran_bukti', 255)->nullable();
                $table->timestamps();

                $table->index(['guru_user_id', 'tanggal']);
                $table->index('jadwal_pelajaran_id');
            });
        }

        // 8. Link Perangkat Pembelajaran (Google Drive)
        if (!Schema::hasTable('perangkat_ajars')) {
            Schema::create('perangkat_ajars', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('guru_user_id');
                $table->unsignedBigInteger('mata_pelajaran_id');
                $table->string('kelas', 50);
                $table->string('tahun_ajaran', 20)->default('2025/2026');
                $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
                $table->string('judul', 200);
                $table->string('link_gdrive', 500);
                $table->json('kelengkapan_berkas')->nullable();
                $table->enum('status', ['menunggu_review', 'disetujui', 'perlu_perbaikan'])->default('menunggu_review');
                $table->text('catatan_waka')->nullable();
                $table->unsignedBigInteger('verified_by_user_id')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();

                $table->index(['guru_user_id', 'tahun_ajaran', 'semester']);
            });
        }

        // 9. Cuti Guru & Penugasan Inval
        if (!Schema::hasTable('cuti_gurus')) {
            Schema::create('cuti_gurus', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('guru_user_id');
                $table->unsignedBigInteger('guru_pengganti_id')->nullable();
                $table->string('jenis_cuti', 100);
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->text('alasan');
                $table->string('lampiran_surat', 255)->nullable();
                $table->enum('status', ['diajukan', 'disetujui_waka', 'disetujui_kepsek', 'ditolak'])->default('diajukan');
                $table->text('catatan_approval')->nullable();
                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->timestamps();

                $table->index(['guru_user_id', 'status']);
            });
        }

        // 10. Kegiatan Sekolah (Diinput Waka Kurikulum/Sarpras/Kesiswaan/Hubin/Pembina OSIS)
        if (!Schema::hasTable('kegiatan_sekolahs')) {
            Schema::create('kegiatan_sekolahs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('author_user_id');
                $table->enum('bidang', ['waka_kurikulum', 'waka_sarpras', 'waka_kesiswaan', 'waka_hubin', 'pembina_osis']);
                $table->string('judul', 255);
                $table->string('kategori', 100)->default('Pengumuman');
                $table->date('tanggal_kegiatan');
                $table->time('waktu_mulai')->nullable();
                $table->time('waktu_selesai')->nullable();
                $table->string('tempat', 150)->default('SMK Plus Al-Hilal');
                $table->string('sasaran', 100)->default('Semua Guru & Siswa');
                $table->text('deskripsi');
                $table->string('lampiran', 255)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['bidang', 'tanggal_kegiatan']);
            });
        }

        // 11. Keluhan KBM Siswa (Identitas Siswa strictly tersembunyi / anonim di antarmuka publik)
        if (!Schema::hasTable('keluhan_kbms')) {
            Schema::create('keluhan_kbms', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('siswa_user_id'); // Disimpan internal, TIDAK PERNAH ditampilkan di UI
                $table->unsignedBigInteger('target_guru_user_id');
                $table->unsignedBigInteger('mata_pelajaran_id')->nullable();
                $table->string('kelas', 50);
                $table->date('tanggal_kbm');
                $table->string('kategori_masalah', 100);
                $table->text('isi_keluhan');
                $table->text('harapan_siswa')->nullable();
                $table->enum('status', ['baru', 'diproses', 'selesai'])->default('baru');
                $table->text('catatan_tindak_lanjut')->nullable();
                $table->timestamps();

                $table->index(['target_guru_user_id', 'status']);
                $table->index(['kelas', 'status']);
            });
        }

        // 12. Saran Perbaikan & Arahan Pembinaan
        if (!Schema::hasTable('saran_perbaikans')) {
            Schema::create('saran_perbaikans', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('keluhan_kbm_id')->nullable();
                $table->unsignedBigInteger('target_guru_user_id');
                $table->unsignedBigInteger('author_user_id');
                $table->string('role_author', 100); // Kepala Sekolah, Waka Kurikulum, Waka Kesiswaan, Kaprog, Wali Kelas
                $table->string('judul_arahan', 200);
                $table->text('arahan_pembinaan');
                $table->text('rekomendasi_tindakan')->nullable();
                $table->text('tanggapan_guru')->nullable();
                $table->enum('status', ['dibaca', 'ditanggapi', 'selesai'])->default('dibaca');
                $table->timestamps();

                $table->index(['target_guru_user_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saran_perbaikans');
        Schema::dropIfExists('keluhan_kbms');
        Schema::dropIfExists('kegiatan_sekolahs');
        Schema::dropIfExists('cuti_gurus');
        Schema::dropIfExists('perangkat_ajars');
        Schema::dropIfExists('absensi_gurus');
        Schema::dropIfExists('laporan_kbm_presensis');
        Schema::dropIfExists('laporan_kbms');
        Schema::dropIfExists('rencana_pembelajarans');
        Schema::dropIfExists('tujuan_pembelajarans');
        Schema::dropIfExists('capaian_pembelajarans');
        Schema::dropIfExists('minggu_efektifs');
    }
};
