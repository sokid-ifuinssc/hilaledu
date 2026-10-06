<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Pendataan Kerjasama dengan Mitra DU/DI (Dunia Usaha & Dunia Industri)
     */
    public function up(): void
    {
        if (!Schema::hasTable('kerjasamas')) {
            Schema::create('kerjasamas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dudi_id')->nullable()->constrained('dudi')->nullOnDelete();
                $table->string('nama_mitra', 150);
                $table->string('bidang_mitra', 100)->nullable();
                $table->text('alamat')->nullable();
                $table->string('no_telp', 30)->nullable();
                $table->string('email', 100)->nullable();
                $table->string('nomor_mou', 100)->nullable();
                $table->text('bentuk_kerjasama'); // JSON array: sinkronisasi kurikulum, pelaksanaan prakerin, payroll, dll.
                $table->string('tahun_mulai', 10);
                $table->string('tahun_berakhir', 10);
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_berakhir')->nullable();
                $table->string('file_kerjasama', 255)->nullable();
                $table->string('file_nama_asli', 255)->nullable();
                $table->text('link_drive')->nullable();
                $table->string('pic_nama', 100)->nullable();
                $table->string('pic_jabatan', 100)->nullable();
                $table->string('pic_kontak', 50)->nullable();
                $table->string('status', 20)->default('aktif'); // aktif, berakhir, draft
                $table->text('keterangan')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kerjasamas');
    }
};
