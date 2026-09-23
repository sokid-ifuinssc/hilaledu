<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracer_kuesioners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->constrained('tracer_alumnis')->cascadeOnDelete();
            $table->text('pertanyaan');
            $table->text('jawaban')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracer_kuesioners');
    }
};
