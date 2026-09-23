<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('jenis_rekomendasis', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('kode', 50)->unique();
            $table->enum('untuk', ['bk', 'kaprog', 'semua'])->default('semua');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_rekomendasis');
    }
};
