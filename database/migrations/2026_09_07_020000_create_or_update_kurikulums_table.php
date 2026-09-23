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
        if (!Schema::hasTable('kurikulums')) {
            Schema::create('kurikulums', function (Blueprint $table) {
                $table->id();
                $table->string('tahun_ajaran', 50)->default('2025 - 2026');
                $table->string('semester', 20)->default('Ganjil');
                $table->string('jenjang', 10)->default('X'); // X, XI, XII
                $table->string('jurusan', 50)->default('TKJT'); // TKJT, TO, AKL
                $table->unsignedBigInteger('jurusan_id')->nullable();
                $table->string('kelas', 50); // X TKJT, XI AKL, etc.
                $table->unsignedBigInteger('kelas_id')->nullable();
                $table->unsignedBigInteger('mata_pelajaran_id');
                $table->unsignedBigInteger('guru_user_id')->nullable();
                $table->integer('alokasi_jam')->default(2); // JP per minggu
                $table->string('keterangan')->nullable();
                $table->boolean('is_aktif')->default(true);
                $table->timestamps();

                $table->index(['kelas', 'mata_pelajaran_id']);
                $table->index(['tahun_ajaran', 'semester']);
                $table->index(['jenjang', 'jurusan']);
            });
        } else {
            Schema::table('kurikulums', function (Blueprint $table) {
                if (!Schema::hasColumn('kurikulums', 'tahun_ajaran')) {
                    $table->string('tahun_ajaran', 50)->default('2025 - 2026')->after('id');
                }
                if (!Schema::hasColumn('kurikulums', 'semester')) {
                    $table->string('semester', 20)->default('Ganjil')->after('tahun_ajaran');
                }
                if (!Schema::hasColumn('kurikulums', 'jenjang')) {
                    $table->string('jenjang', 10)->default('X')->after('semester');
                }
                if (!Schema::hasColumn('kurikulums', 'jurusan')) {
                    $table->string('jurusan', 50)->default('TKJT')->after('jenjang');
                }
                if (!Schema::hasColumn('kurikulums', 'jurusan_id')) {
                    $table->unsignedBigInteger('jurusan_id')->nullable()->after('jurusan');
                }
                if (!Schema::hasColumn('kurikulums', 'kelas')) {
                    $table->string('kelas', 50)->default('')->after('jurusan_id');
                }
                if (!Schema::hasColumn('kurikulums', 'kelas_id')) {
                    $table->unsignedBigInteger('kelas_id')->nullable()->after('kelas');
                }
                if (!Schema::hasColumn('kurikulums', 'mata_pelajaran_id')) {
                    $table->unsignedBigInteger('mata_pelajaran_id')->default(1)->after('kelas_id');
                }
                if (!Schema::hasColumn('kurikulums', 'guru_user_id')) {
                    $table->unsignedBigInteger('guru_user_id')->nullable()->after('mata_pelajaran_id');
                }
                if (!Schema::hasColumn('kurikulums', 'alokasi_jam')) {
                    $table->integer('alokasi_jam')->default(2)->after('guru_user_id');
                }
                if (!Schema::hasColumn('kurikulums', 'keterangan')) {
                    $table->string('keterangan')->nullable()->after('alokasi_jam');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep table intact or rollback columns
    }
};
