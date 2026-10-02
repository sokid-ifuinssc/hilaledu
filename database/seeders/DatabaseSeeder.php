<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Urutan eksekusi penting — pastikan TahunAjaran & Jurusan tersedia
     * sebelum Kelas, dan Kelas tersedia sebelum Siswa.
     */
    public function run(): void
    {
        $this->call([
            // ── Data master lama (tetap dipertahankan) ──────────────────────
            ApplicationSeeder::class,
            PayrollSeeder::class,

            // ── Data asli produksi SMK Plus Al-Hilal ────────────────────────
            TahunAjaranSeeder::class,        // TA 2026/2027 (aktif)
            JurusanKelasSeeder::class,       // 3 jurusan + 9 kelas
            PengaturanSekolahSeeder::class,  // info sekolah
            MasterTugasTambahanSeeder::class,// jabatan tugas tambahan
            GuruTendikSeeder::class,         // 1 superadmin + 32 guru + 4 tendik
            SiswaSeeder::class,              // 220 siswa (9 kelas)
        ]);
    }
}
