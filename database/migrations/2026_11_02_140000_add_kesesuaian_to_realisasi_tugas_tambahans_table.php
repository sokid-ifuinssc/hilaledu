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
        Schema::table('realisasi_tugas_tambahans', function (Blueprint $table) {
            if (!Schema::hasColumn('realisasi_tugas_tambahans', 'status_kesesuaian')) {
                $table->enum('status_kesesuaian', ['sesuai', 'sebagian_sesuai', 'tidak_sesuai'])
                    ->default('sesuai')
                    ->after('program_kerja_id');
            }
            if (!Schema::hasColumn('realisasi_tugas_tambahans', 'persentase_kesesuaian')) {
                $table->unsignedTinyInteger('persentase_kesesuaian')
                    ->default(100)
                    ->after('status_kesesuaian');
            }
            if (!Schema::hasColumn('realisasi_tugas_tambahans', 'keterangan_kesesuaian')) {
                $table->text('keterangan_kesesuaian')
                    ->nullable()
                    ->after('persentase_kesesuaian');
            }
            if (!Schema::hasColumn('realisasi_tugas_tambahans', 'realisasi_anggaran')) {
                $table->decimal('realisasi_anggaran', 14, 2)
                    ->nullable()
                    ->default(0)
                    ->after('hasil_capaian');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_tugas_tambahans', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('realisasi_tugas_tambahans', 'status_kesesuaian')) {
                $columnsToDrop[] = 'status_kesesuaian';
            }
            if (Schema::hasColumn('realisasi_tugas_tambahans', 'persentase_kesesuaian')) {
                $columnsToDrop[] = 'persentase_kesesuaian';
            }
            if (Schema::hasColumn('realisasi_tugas_tambahans', 'keterangan_kesesuaian')) {
                $columnsToDrop[] = 'keterangan_kesesuaian';
            }
            if (Schema::hasColumn('realisasi_tugas_tambahans', 'realisasi_anggaran')) {
                $columnsToDrop[] = 'realisasi_anggaran';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
