<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder Pengaturan Sekolah — data asli SMK Plus Al-Hilal (Oktober 2026).
 */
class PengaturanSekolahSeeder extends Seeder
{
    public function run(): void
    {
        // Kepala sekolah: id 516 = Ismail Fahmi, ST
        $kepalaId = DB::table('users')->where('username', 'ismailfahmi')->value('id');

        DB::table('pengaturan_sekolah')->updateOrInsert(
            ['npsn' => '69758451'],
            [
                'nama_sekolah'       => 'SMK PLUS AL HILAL',
                'npsn'               => '69758451',
                'kepala_sekolah_id'  => $kepalaId,
                'alamat'             => 'Jl H Manshur No 7 Lap Bima Rembes Desa Tegalgubug Kec. Arjawinangun Kab. Cirebon',
                'email'              => 'smkpal2021@gmail.com',
                'telepon'            => null,
                'website'            => 'https://smkpluslahilal.sch.id',
                'logo'               => null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]
        );

        // Pengaturan key-value (tabel pengaturan_sekolahs)
        $settings = [
            ['key' => 'nama_sekolah',    'value' => 'SMK Plus Al-Hilal',                                                             'tipe' => 'text'],
            ['key' => 'npsn',            'value' => '69758451',                                                                       'tipe' => 'text'],
            ['key' => 'alamat_sekolah',  'value' => 'Jl. H. Manshur No 7 Lap. Bima Rembes, Ds. Tegalgubug, Kec. Arjawinangun, Kab. Cirebon', 'tipe' => 'text'],
            ['key' => 'telepon_sekolah', 'value' => null,                                                                             'tipe' => 'text'],
            ['key' => 'email_sekolah',   'value' => 'smkpal2021@gmail.com',                                                           'tipe' => 'text'],
        ];

        foreach ($settings as $s) {
            DB::table('pengaturan_sekolahs')->updateOrInsert(
                ['key' => $s['key']],
                array_merge($s, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $this->command->info('Pengaturan Sekolah seeded.');
    }
}
