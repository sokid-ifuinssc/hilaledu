<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        if (Schema::hasTable('audit_logs')) {
            return;
        }

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('auditable'); // auditable_type + auditable_id (auto-creates index)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('aksi', 50);
            $table->string('status_dari', 50)->nullable();
            $table->string('status_ke', 50)->nullable();
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('aksi');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
