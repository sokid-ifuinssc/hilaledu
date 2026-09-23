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
        // 1. Tambah ke tabel mata_pelajarans
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            if (!Schema::hasColumn('mata_pelajarans', 'kategori')) {
                $table->string('kategori', 100)->default('A. KELOMPOK MATA PELAJARAN UMUM')->after('nama');
            }
            if (!Schema::hasColumn('mata_pelajarans', 'sub_kategori')) {
                $table->string('sub_kategori', 150)->nullable()->after('kategori');
            }
            if (!Schema::hasColumn('mata_pelajarans', 'urutan')) {
                $table->integer('urutan')->default(0)->after('sub_kategori');
            }
        });

        // 2. Tambah ke tabel kurikulums
        Schema::table('kurikulums', function (Blueprint $table) {
            if (!Schema::hasColumn('kurikulums', 'kategori')) {
                $table->string('kategori', 100)->nullable()->after('mata_pelajaran_id');
            }
            if (!Schema::hasColumn('kurikulums', 'sub_kategori')) {
                $table->string('sub_kategori', 150)->nullable()->after('kategori');
            }
            if (!Schema::hasColumn('kurikulums', 'urutan')) {
                $table->integer('urutan')->default(0)->after('sub_kategori');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            if (Schema::hasColumn('mata_pelajarans', 'urutan')) $table->dropColumn('urutan');
            if (Schema::hasColumn('mata_pelajarans', 'sub_kategori')) $table->dropColumn('sub_kategori');
            if (Schema::hasColumn('mata_pelajarans', 'kategori')) $table->dropColumn('kategori');
        });

        Schema::table('kurikulums', function (Blueprint $table) {
            if (Schema::hasColumn('kurikulums', 'urutan')) $table->dropColumn('urutan');
            if (Schema::hasColumn('kurikulums', 'sub_kategori')) $table->dropColumn('sub_kategori');
            if (Schema::hasColumn('kurikulums', 'kategori')) $table->dropColumn('kategori');
        });
    }
};
