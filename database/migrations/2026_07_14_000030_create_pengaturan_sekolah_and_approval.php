<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel pengaturan sekolah (kop surat, dll) — di master db_hilaledu
        if (!Schema::hasTable('pengaturan_sekolahs')) {
            Schema::create('pengaturan_sekolahs', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->string('label', 200)->nullable();
                $table->string('tipe', 20)->default('text'); // text, textarea, image
                $table->timestamps();
            });
        }

        // Tambah kolom approval di rekomendasis (di mysql_local)
        if (Schema::hasTable('rekomendasis') 
            && !Schema::hasColumn('rekomendasis', 'status_approval')) {
            Schema::table('rekomendasis', function (Blueprint $table) {
                $table->enum('status_approval', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu')->after('tanggal_rekomendasi');
                $table->unsignedBigInteger('approved_by')->nullable()->after('status_approval');
                $table->timestamp('approved_at')->nullable()->after('approved_by');
                $table->text('catatan_approval')->nullable()->after('approved_at');
            });
        }

        // Tambah kolom approval di rekomendasi_kaprogs (di mysql_local)
        if (Schema::hasTable('rekomendasi_kaprogs') 
            && !Schema::hasColumn('rekomendasi_kaprogs', 'status_approval')) {
            Schema::table('rekomendasi_kaprogs', function (Blueprint $table) {
                $table->enum('status_approval', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu')->after('status');
                $table->unsignedBigInteger('approved_by')->nullable()->after('status_approval');
                $table->timestamp('approved_at')->nullable()->after('approved_by');
                $table->text('catatan_approval')->nullable()->after('approved_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_sekolahs');

        if (Schema::hasColumn('rekomendasis', 'status_approval')) {
            Schema::table('rekomendasis', function (Blueprint $table) {
                $table->dropColumn(['status_approval', 'approved_by', 'approved_at', 'catatan_approval']);
            });
        }
        if (Schema::hasColumn('rekomendasi_kaprogs', 'status_approval')) {
            Schema::table('rekomendasi_kaprogs', function (Blueprint $table) {
                $table->dropColumn(['status_approval', 'approved_by', 'approved_at', 'catatan_approval']);
            });
        }
    }
};
