<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations on db_akademik (connection: mysql).
     * DO NOT MODIFY db_hilaledu structure.
     */
    public function up(): void
    {
        // 1. Tabel Utama Kalender Akademik
        if (!Schema::hasTable('kalender_akademiks')) {
            Schema::create('kalender_akademiks', function (Blueprint $table) {
                $table->id();
                $table->string('tahun_ajaran', 30)->index();          // e.g. "2026/2027"
                $table->string('nama_kalender', 150);                  // e.g. "Kalender Akademik SMK Plus Al-Hilal 2026/2027"
                $table->date('tanggal_mulai');                         // 2026-07-01
                $table->date('tanggal_selesai');                       // 2027-06-30
                $table->date('tanggal_mulai_smt1')->nullable();        // 2026-07-01
                $table->date('tanggal_selesai_smt1')->nullable();      // 2026-12-31
                $table->date('tanggal_mulai_smt2')->nullable();        // 2027-01-01
                $table->date('tanggal_selesai_smt2')->nullable();      // 2027-06-30
                $table->boolean('is_aktif')->default(true)->index();
                $table->text('deskripsi')->nullable();
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->timestamps();
            });
        }

        // 2. Tabel Detail Kegiatan & Hari Libur Kalender Akademik
        if (!Schema::hasTable('kalender_akademik_events')) {
            Schema::create('kalender_akademik_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kalender_akademik_id')
                      ->constrained('kalender_akademiks')
                      ->onDelete('cascade');
                $table->date('tanggal_mulai')->index();
                $table->date('tanggal_selesai')->index();
                $table->enum('semester', ['1', '2'])->default('1')->index();
                $table->string('judul_kegiatan', 255);
                $table->enum('kategori', [
                    'libur_nasional', 
                    'libur_sekolah', 
                    'libur_semester', 
                    'kegiatan_sekolah', 
                    'ujian_asesmen', 
                    'pembagian_rapor',
                    'hari_efektif_khusus'
                ])->default('kegiatan_sekolah');
                $table->string('warna_bg', 30)->default('red'); // 'red', 'yellow', 'green', 'blue', 'purple', 'emerald'
                $table->text('keterangan')->nullable();
                $table->boolean('is_libur')->default(false);    // 1 = Bukan hari belajar efektif
                $table->string('sumber', 50)->default('manual'); // 'manual', 'google_calendar', 'disdik_jabar'
                $table->string('google_event_id', 150)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kalender_akademik_events');
        Schema::dropIfExists('kalender_akademiks');
    }
};
