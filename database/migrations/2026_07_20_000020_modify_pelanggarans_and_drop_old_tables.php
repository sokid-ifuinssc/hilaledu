<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up(): void
    {
        // Update status enum pelanggaran untuk alur baru
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pelanggarans MODIFY COLUMN status ENUM('dicatat', 'proses', 'selesai') DEFAULT 'dicatat'");
        }

        // Drop tabel-tabel yang tidak terpakai lagi
        Schema::dropIfExists('tindak_lanjuts');
        Schema::dropIfExists('rekomendasi_kaprogs');
        Schema::dropIfExists('rekomendasis');
        Schema::dropIfExists('jenis_rekomendasis');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pelanggarans MODIFY COLUMN status ENUM('dicatat', 'direkomendasikan', 'ditindaklanjuti', 'disetujui', 'selesai') DEFAULT 'dicatat'");
        }
    }
};
