<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('progres_pelanggarans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pelanggaran_id');
            $table->enum('jenis_tindakan', [
                'peringatan_lisan',
                'teguran_lisan',
                'home_visit',
                'pemanggilan_ortu',
                'sp1',
                'sp2',
                'sp3',
            ]);

            // Approval fields per role
            $table->enum('approval_walikelas', ['belum', 'disetujui', 'ditolak'])->default('belum');
            $table->timestamp('approval_walikelas_at')->nullable();
            $table->text('catatan_walikelas')->nullable();
            $table->string('dokumen_walikelas')->nullable();

            $table->enum('approval_kaprog', ['belum', 'disetujui', 'ditolak'])->default('belum');
            $table->timestamp('approval_kaprog_at')->nullable();
            $table->text('catatan_kaprog')->nullable();
            $table->string('dokumen_kaprog')->nullable();

            $table->enum('approval_waka', ['belum', 'disetujui', 'ditolak'])->default('belum');
            $table->timestamp('approval_waka_at')->nullable();
            $table->text('catatan_waka')->nullable();
            $table->string('dokumen_waka')->nullable();

            $table->enum('approval_kepsek', ['belum', 'disetujui', 'ditolak'])->default('belum');
            $table->timestamp('approval_kepsek_at')->nullable();
            $table->text('catatan_kepsek')->nullable();
            $table->string('dokumen_kepsek')->nullable();

            // Surat
            $table->boolean('surat_dicetak')->default(false);
            $table->timestamp('surat_dicetak_at')->nullable();

            // Laporan per role
            $table->text('laporan_walikelas')->nullable();
            $table->string('dokumen_laporan_walikelas')->nullable();

            $table->text('laporan_kaprog')->nullable();
            $table->string('dokumen_laporan_kaprog')->nullable();

            $table->text('laporan_waka')->nullable();
            $table->string('dokumen_laporan_waka')->nullable();

            $table->text('laporan_kepsek')->nullable();
            $table->string('dokumen_laporan_kepsek')->nullable();

            $table->text('laporan_bk')->nullable();
            $table->string('dokumen_laporan_bk')->nullable();

            // Kesimpulan (terutama untuk SP3)
            $table->text('kesimpulan')->nullable();

            // Status keseluruhan progres
            $table->enum('status', [
                'menunggu_approval',
                'menunggu_cetak_surat',
                'menunggu_laporan',
                'selesai',
            ])->default('menunggu_approval');

            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index('pelanggaran_id');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progres_pelanggarans');
    }
};
