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
        if (!Schema::hasTable('alur_tujuan_pembelajarans')) {
            Schema::create('alur_tujuan_pembelajarans', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('guru_user_id');
                $table->unsignedBigInteger('mata_pelajaran_id');
                $table->unsignedBigInteger('capaian_pembelajaran_id')->nullable();
                $table->unsignedBigInteger('tujuan_pembelajaran_id')->nullable();
                $table->enum('fase', ['E', 'F'])->default('E');
                $table->enum('tingkat', ['X', 'XI', 'XII'])->default('X');
                $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
                $table->integer('alur_ke')->default(1);
                $table->string('kode_atp', 30);
                $table->string('materi_pokok', 255);
                $table->integer('alokasi_jp')->default(4);
                $table->string('profil_pelajar_pancasila', 255)->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->index(['guru_user_id', 'mata_pelajaran_id']);
                $table->index('tujuan_pembelajaran_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alur_tujuan_pembelajarans');
    }
};
