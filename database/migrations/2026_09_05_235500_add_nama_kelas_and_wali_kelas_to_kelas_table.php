<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kelas')) {
            Schema::table('kelas', function (Blueprint $table) {
                if (!Schema::hasColumn('kelas', 'nama_kelas')) {
                    $table->string('nama_kelas', 50)->nullable()->after('nama');
                }
                if (!Schema::hasColumn('kelas', 'wali_kelas')) {
                    $table->string('wali_kelas', 255)->nullable()->after('wali_kelas_id');
                }
            });

            // Sinkronkan nama_kelas dengan nama jika nama_kelas masih null/kosong
            if (Schema::hasColumn('kelas', 'nama_kelas') && Schema::hasColumn('kelas', 'nama')) {
                DB::table('kelas')
                    ->where(function ($q) {
                        $q->whereNull('nama_kelas')->orWhere('nama_kelas', '');
                    })
                    ->update(['nama_kelas' => DB::raw('nama')]);
            }

            // Sinkronkan nama wali_kelas dari tabel users jika wali_kelas_id terisi
            if (Schema::hasColumn('kelas', 'wali_kelas') && Schema::hasColumn('kelas', 'wali_kelas_id')) {
                $kelasList = DB::table('kelas')->whereNotNull('wali_kelas_id')->get();
                foreach ($kelasList as $item) {
                    $guru = DB::table('users')->where('id', $item->wali_kelas_id)->first();
                    if ($guru) {
                        DB::table('kelas')->where('id', $item->id)->update(['wali_kelas' => $guru->name]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kelas')) {
            Schema::table('kelas', function (Blueprint $table) {
                if (Schema::hasColumn('kelas', 'nama_kelas')) {
                    $table->dropColumn('nama_kelas');
                }
                if (Schema::hasColumn('kelas', 'wali_kelas')) {
                    $table->dropColumn('wali_kelas');
                }
            });
        }
    }
};
