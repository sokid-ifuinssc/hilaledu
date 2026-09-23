<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Membuat kolom legacy (nama_lengkap, nip, dll) menjadi nullable
     * agar bisa insert user baru tanpa perlu mengisi kolom warisan sistem lama.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'nama_lengkap')) {
            DB::statement("UPDATE users SET nama_lengkap = name WHERE nama_lengkap IS NULL OR nama_lengkap = ''");

            Schema::table('users', function (Blueprint $table) {
                $table->string('nama_lengkap')->nullable()->default(null)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nama_lengkap')->nullable(false)->change();
        });
    }
};
