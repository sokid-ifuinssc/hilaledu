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
        // 1. Master Komponen Gaji
        if (!Schema::hasTable('payroll_komponens')) {
            Schema::create('payroll_komponens', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 30)->unique();
                $table->string('nama', 150);
                $table->enum('jenis', ['penerimaan', 'potongan'])->default('penerimaan');
                $table->enum('tipe', ['tetap', 'per_jam', 'per_kehadiran', 'persentase'])->default('tetap');
                $table->decimal('nominal_default', 15, 2)->default(0);
                $table->boolean('is_aktif')->default(true);
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();
            });
        }

        // 2. Setting Gaji Pegawai (Guru & Tendik)
        if (!Schema::hasTable('payroll_settings')) {
            Schema::create('payroll_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
                $table->decimal('gaji_pokok', 15, 2)->default(0);
                $table->decimal('honor_per_jam', 15, 2)->default(0); // Tarif per jam tatap muka untuk guru
                $table->integer('jam_mengajar_default')->default(0);  // Estimasi jam per minggu/bulan
                $table->decimal('tunjangan_jabatan', 15, 2)->default(0); // Wali Kelas, Kaprog, Kepsek, dll
                $table->decimal('tunjangan_kehadiran', 15, 2)->default(0); // Uang transport / makan
                $table->decimal('tunjangan_lain', 15, 2)->default(0);
                $table->decimal('potongan_bpjs', 15, 2)->default(0);
                $table->decimal('potongan_koperasi', 15, 2)->default(0);
                $table->decimal('potongan_lain', 15, 2)->default(0);
                $table->string('rekening_bank', 100)->nullable();
                $table->string('nomor_rekening', 60)->nullable();
                $table->string('atas_nama_rekening', 150)->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }

        // 3. Periode Penggajian Bulanan
        if (!Schema::hasTable('payroll_periodes')) {
            Schema::create('payroll_periodes', function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger('bulan'); // 1 - 12
                $table->unsignedSmallInteger('tahun'); // 2026
                $table->string('nama_periode', 100);  // "September 2026"
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->date('tanggal_pembayaran')->nullable();
                $table->enum('status', ['draft', 'finalized', 'paid'])->default('draft');
                $table->decimal('total_penerimaan', 15, 2)->default(0);
                $table->decimal('total_potongan', 15, 2)->default(0);
                $table->decimal('total_dibayarkan', 15, 2)->default(0);
                $table->text('catatan')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->unique(['bulan', 'tahun']);
            });
        }

        // 4. Rekap Gaji per Pegawai dalam Periode
        if (!Schema::hasTable('payrolls')) {
            Schema::create('payrolls', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_slip', 50)->unique();
                $table->foreignId('payroll_periode_id')->constrained('payroll_periodes')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->decimal('gaji_pokok', 15, 2)->default(0);
                $table->decimal('total_honor_jam', 15, 2)->default(0);
                $table->decimal('total_tunjangan', 15, 2)->default(0);
                $table->decimal('total_penerimaan', 15, 2)->default(0);
                $table->decimal('total_potongan', 15, 2)->default(0);
                $table->decimal('gaji_bersih', 15, 2)->default(0); // Take Home Pay
                $table->integer('jumlah_jam_mengajar')->default(0);
                $table->integer('jumlah_kehadiran')->default(0);
                $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
                $table->date('tanggal_dibayar')->nullable();
                $table->enum('metode_pembayaran', ['transfer', 'tunai'])->default('transfer');
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->unique(['payroll_periode_id', 'user_id']);
            });
        }

        // 5. Rincian Item Komponen Gaji per Slip
        if (!Schema::hasTable('payroll_items')) {
            Schema::create('payroll_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payroll_id')->constrained('payrolls')->onDelete('cascade');
                $table->string('nama_komponen', 150);
                $table->enum('jenis', ['penerimaan', 'potongan'])->default('penerimaan');
                $table->decimal('nominal', 15, 2)->default(0);
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('payroll_periodes');
        Schema::dropIfExists('payroll_settings');
        Schema::dropIfExists('payroll_komponens');
    }
};
