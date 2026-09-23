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
        Schema::table('users', function (Blueprint $table) {
            // Informasi Kepegawaian / Akademik Tambahan
            if (!Schema::hasColumn('users', 'tahun_masuk')) {
                $table->string('tahun_masuk', 10)->nullable()->after('nip');
            }
            if (!Schema::hasColumn('users', 'lulusan_tahun')) {
                $table->string('lulusan_tahun', 10)->nullable()->after('tahun_masuk');
            }

            // Alamat Lengkap
            if (!Schema::hasColumn('users', 'alamat')) {
                $table->text('alamat')->nullable()->after('no_hp');
            }
            if (!Schema::hasColumn('users', 'desa')) {
                $table->string('desa', 100)->nullable()->after('alamat');
            }
            if (!Schema::hasColumn('users', 'kecamatan')) {
                $table->string('kecamatan', 100)->nullable()->after('desa');
            }
            if (!Schema::hasColumn('users', 'kabupaten')) {
                $table->string('kabupaten', 100)->nullable()->after('kecamatan');
            }
            if (!Schema::hasColumn('users', 'provinsi')) {
                $table->string('provinsi', 100)->nullable()->after('kabupaten');
            }

            // Data Orang Tua / Wali (Khusus Siswa)
            if (!Schema::hasColumn('users', 'nama_ayah')) {
                $table->string('nama_ayah', 150)->nullable()->after('provinsi');
            }
            if (!Schema::hasColumn('users', 'nama_ibu')) {
                $table->string('nama_ibu', 150)->nullable()->after('nama_ayah');
            }
            if (!Schema::hasColumn('users', 'no_hp_ortu')) {
                $table->string('no_hp_ortu', 30)->nullable()->after('nama_ibu');
            }

            // Riwayat Pendidikan
            if (!Schema::hasColumn('users', 'pendidikan_sd')) {
                $table->string('pendidikan_sd', 150)->nullable()->after('no_hp_ortu');
            }
            if (!Schema::hasColumn('users', 'tahun_lulus_sd')) {
                $table->string('tahun_lulus_sd', 10)->nullable()->after('pendidikan_sd');
            }
            if (!Schema::hasColumn('users', 'pendidikan_smp')) {
                $table->string('pendidikan_smp', 150)->nullable()->after('tahun_lulus_sd');
            }
            if (!Schema::hasColumn('users', 'tahun_lulus_smp')) {
                $table->string('tahun_lulus_smp', 10)->nullable()->after('pendidikan_smp');
            }
            if (!Schema::hasColumn('users', 'pendidikan_sma')) {
                $table->string('pendidikan_sma', 150)->nullable()->after('tahun_lulus_smp');
            }
            if (!Schema::hasColumn('users', 'tahun_lulus_sma')) {
                $table->string('tahun_lulus_sma', 10)->nullable()->after('pendidikan_sma');
            }
            if (!Schema::hasColumn('users', 'pendidikan_s1')) {
                $table->string('pendidikan_s1', 150)->nullable()->after('tahun_lulus_sma');
            }
            if (!Schema::hasColumn('users', 'tahun_lulus_s1')) {
                $table->string('tahun_lulus_s1', 10)->nullable()->after('pendidikan_s1');
            }
            if (!Schema::hasColumn('users', 'pendidikan_s2')) {
                $table->string('pendidikan_s2', 150)->nullable()->after('tahun_lulus_s1');
            }
            if (!Schema::hasColumn('users', 'tahun_lulus_s2')) {
                $table->string('tahun_lulus_s2', 10)->nullable()->after('pendidikan_s2');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'tahun_masuk',
                'lulusan_tahun',
                'alamat',
                'desa',
                'kecamatan',
                'kabupaten',
                'provinsi',
                'nama_ayah',
                'nama_ibu',
                'no_hp_ortu',
                'pendidikan_sd',
                'tahun_lulus_sd',
                'pendidikan_smp',
                'tahun_lulus_smp',
                'pendidikan_sma',
                'tahun_lulus_sma',
                'pendidikan_s1',
                'tahun_lulus_s1',
                'pendidikan_s2',
                'tahun_lulus_s2',
            ]);
        });
    }
};
