<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatan_sekolahs', function (Blueprint $table) {
            if (!Schema::hasColumn('kegiatan_sekolahs', 'edited_by_user_id')) {
                $table->foreignId('edited_by_user_id')->nullable()->after('author_user_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('kegiatan_sekolahs', function (Blueprint $table) {
            if (Schema::hasColumn('kegiatan_sekolahs', 'edited_by_user_id')) {
                $table->dropForeign(['edited_by_user_id']);
                $table->dropColumn('edited_by_user_id');
            }
        });
    }
};
