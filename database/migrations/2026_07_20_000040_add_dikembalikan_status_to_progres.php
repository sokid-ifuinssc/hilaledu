<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up(): void
    {
        // Tambah status 'dikembalikan' ke enum status progres_pelanggarans
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE progres_pelanggarans MODIFY COLUMN status ENUM('menunggu_approval','menunggu_cetak_surat','menunggu_laporan','dikembalikan','selesai') DEFAULT 'menunggu_approval'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE progres_pelanggarans MODIFY COLUMN status ENUM('menunggu_approval','menunggu_cetak_surat','menunggu_laporan','selesai') DEFAULT 'menunggu_approval'");
        }
    }
};
