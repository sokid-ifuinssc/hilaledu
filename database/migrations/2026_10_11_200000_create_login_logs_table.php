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
        if (Schema::hasTable('login_logs')) {
            return;
        }

        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('login_input', 150)->nullable()->comment('Email atau username yang diinput');
            $table->string('user_name', 150)->nullable();
            $table->string('user_role', 50)->nullable();
            $table->string('ip_address', 45)->index();
            $table->enum('status', ['success', 'failed'])->default('success')->index();
            $table->string('failure_reason', 255)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 30)->nullable()->comment('Desktop, Mobile, Tablet');
            $table->string('browser', 60)->nullable();
            $table->string('platform', 60)->nullable()->comment('OS');
            $table->string('country', 100)->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('region', 100)->nullable()->comment('Provinsi / State');
            $table->string('city', 100)->nullable()->index()->comment('Kota / Kabupaten');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('timezone', 60)->nullable();
            $table->string('isp', 150)->nullable();
            $table->boolean('is_suspicious')->default(false)->index();
            $table->timestamp('login_at')->index();
            $table->timestamps();

            $table->index(['latitude', 'longitude']);
            $table->index(['status', 'login_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
