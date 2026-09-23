<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel pivot: guru yang ditugaskan sebagai koordinator/admin
     * pada setiap sub-aplikasi (Prakerin, Monitoring BK).
     */
    public function up(): void
    {
        Schema::create('app_coordinators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // Role spesifik di aplikasi tersebut
            $table->enum('coordinator_role', ['koordinator', 'admin_app', 'pembimbing'])->default('koordinator');
            $table->timestamp('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['application_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_coordinators');
    }
};
