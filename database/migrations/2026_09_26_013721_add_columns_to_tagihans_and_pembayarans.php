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
        Schema::table('tagihans', function (Blueprint $table) {
            $table->foreignId('tagihan_master_id')->nullable()->after('siswa_id')->constrained('tagihan_masters')->nullOnDelete();
            $table->string('jenis')->default('sekali')->after('nama_tagihan'); // spp, sekali, tahunan
            $table->string('bulan')->nullable()->after('jenis'); // untuk SPP
            $table->decimal('terbayar', 15, 2)->default(0)->after('nominal');
            $table->text('keterangan')->nullable()->after('status');
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->string('kode_transaksi')->unique()->after('id');
            $table->foreignId('penerima_id')->nullable()->after('metode_pembayaran')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropForeign(['tagihan_master_id']);
            $table->dropColumn(['tagihan_master_id', 'jenis', 'bulan', 'terbayar', 'keterangan']);
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropForeign(['penerima_id']);
            $table->dropColumn(['kode_transaksi', 'penerima_id']);
        });
    }
};
