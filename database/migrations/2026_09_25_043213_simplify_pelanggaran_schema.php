<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove columns from jenis_pelanggarans
        // Drop index first (required for SQLite compatibility before dropping column)
        if (Schema::hasColumn('jenis_pelanggarans', 'kategori_pelanggaran_id')) {
            try {
                Schema::table('jenis_pelanggarans', function (Blueprint $table) {
                    $table->dropIndex('jenis_pelanggarans_kategori_pelanggaran_id_index');
                });
            } catch (\Exception $e) {
                // Index might not exist, continue
            }

            Schema::table('jenis_pelanggarans', function (Blueprint $table) {
                $table->dropColumn('kategori_pelanggaran_id');
            });
        }

        if (Schema::hasColumn('jenis_pelanggarans', 'kode')) {
            Schema::table('jenis_pelanggarans', function (Blueprint $table) {
                $table->dropColumn('kode');
            });
        }

        // Drop kategori_pelanggarans table
        Schema::dropIfExists('kategori_pelanggarans');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('kategori_pelanggarans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->integer('bobot_poin');
            $table->string('warna')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::table('jenis_pelanggarans', function (Blueprint $table) {
            $table->foreignId('kategori_pelanggaran_id')->nullable()->constrained('kategori_pelanggarans')->cascadeOnDelete();
            $table->string('kode')->nullable();
        });
    }
};
