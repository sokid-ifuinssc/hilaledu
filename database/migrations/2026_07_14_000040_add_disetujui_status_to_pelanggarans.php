<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up(): void
    {
        // Tambah status 'disetujui' ke enum pelanggaran
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pelanggarans MODIFY COLUMN status ENUM('dicatat', 'direkomendasikan', 'ditindaklanjuti', 'disetujui', 'selesai') DEFAULT 'dicatat'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pelanggarans MODIFY COLUMN status ENUM('dicatat', 'direkomendasikan', 'ditindaklanjuti', 'selesai') DEFAULT 'dicatat'");
        }
    }
};
