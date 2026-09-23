<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Tahun Ajaran ----
        DB::table('tahun_ajarans')->updateOrInsert(
            ['nama' => '2024/2025'],
            ['tahun_mulai' => 2024, 'tahun_selesai' => 2025, 'is_aktif' => false,
             'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('tahun_ajarans')->updateOrInsert(
            ['nama' => '2025/2026'],
            ['tahun_mulai' => 2025, 'tahun_selesai' => 2026, 'is_aktif' => true,
             'created_at' => now(), 'updated_at' => now()]
        );

        // ---- Jurusan ----
        $jurusans = [
            ['kode' => 'TKJ',  'nama' => 'Teknik Komputer dan Jaringan',         'singkatan' => 'TKJ',  'is_aktif' => true],
            ['kode' => 'RPL',  'nama' => 'Rekayasa Perangkat Lunak',             'singkatan' => 'RPL',  'is_aktif' => true],
            ['kode' => 'AK',   'nama' => 'Akuntansi dan Keuangan Lembaga',       'singkatan' => 'AK',   'is_aktif' => true],
            ['kode' => 'OTKP', 'nama' => 'Otomatisasi dan Tata Kelola Perkantoran','singkatan' => 'OTKP','is_aktif' => true],
            ['kode' => 'PM',   'nama' => 'Pemasaran',                            'singkatan' => 'PM',   'is_aktif' => true],
        ];

        foreach ($jurusans as $j) {
            DB::table('jurusans')->updateOrInsert(
                ['kode' => $j['kode']],
                array_merge($j, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $this->command->info('✓ Data master (tahun ajaran, jurusan) berhasil di-seed.');
    }
}
