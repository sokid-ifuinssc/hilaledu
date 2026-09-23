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
        if (!Schema::hasTable('master_tugas_tambahan')) {
            Schema::create('master_tugas_tambahan', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 150)->unique();
                $table->string('kode', 50)->nullable();
                $table->string('kategori', 50)->default('Sekolah');
                $table->text('deskripsi')->nullable();
                $table->boolean('is_aktif')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_tugas_tambahan');
    }
};
