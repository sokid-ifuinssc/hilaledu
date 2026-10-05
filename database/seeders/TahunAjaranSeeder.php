<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder Tahun Ajaran — data asli dari sistem (Oktober 2026).
 * Menggunakan updateOrInsert agar aman dijalankan berulang kali.
 */
class TahunAjaranSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama'          => '2026/2027',
                'tahun_mulai'   => 2026,
                'tahun_selesai' => 2027,
                'is_aktif'      => 1,
            ],
        ];

        foreach ($data as $row) {
            DB::table('tahun_ajarans')->updateOrInsert(
                ['nama' => $row['nama']],
                array_merge($row, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Tahun Ajaran seeded.');
    }
}
