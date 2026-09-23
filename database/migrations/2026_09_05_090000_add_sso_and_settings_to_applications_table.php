<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('database_name')->nullable()->after('url');
            $table->string('api_key', 64)->nullable()->unique()->after('database_name');
            $table->boolean('sso_enabled')->default(true)->after('api_key');
            $table->boolean('auto_sync')->default(true)->after('sso_enabled');
            $table->string('login_url')->nullable()->after('auto_sync');
            $table->string('sso_redirect_url')->nullable()->after('login_url');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'database_name',
                'api_key',
                'sso_enabled',
                'auto_sync',
                'login_url',
                'sso_redirect_url',
            ]);
        });
    }
};
