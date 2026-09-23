<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jurusans')) {
            return;
        }

        Schema::create('jurusans', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();   // e.g. "TKJ", "RPL"
            $table->string('nama');                  // e.g. "Teknik Komputer & Jaringan"
            $table->string('singkatan', 10);         // e.g. "TKJ"
            $table->foreignId('kaprog_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ketua_jurusan')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurusans');
    }
};
