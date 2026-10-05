<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder Jurusan dan Kelas — data asli SMK Plus Al-Hilal (Oktober 2026).
 * 3 jurusan: TKJT, TO, AKL | 9 kelas TA 2026/2027.
 */
class JurusanKelasSeeder extends Seeder
{
    public function run(): void
    {
        // ── Jurusan ───────────────────────────────────────────────────────────
        $jurusans = [
            [
                'kode'          => 'TKJT',
                'nama'          => 'Teknik Komputer dan Jaringan Telekomunikasi',
                'singkatan'     => 'TKJT',
                'ketua_jurusan' => 'Moh. Roghib, S.Kom',
                'is_aktif'      => 1,
            ],
            [
                'kode'          => 'TO',
                'nama'          => 'Teknik Otomotif',
                'singkatan'     => 'TO',
                'ketua_jurusan' => 'NIDZOMUDDIN, Amd',
                'is_aktif'      => 1,
            ],
            [
                'kode'          => 'AKL',
                'nama'          => 'Akuntansi dan Keuangan Lembaga',
                'singkatan'     => 'AKL',
                'ketua_jurusan' => 'Rizki Dwi Safitri, S.Pd',
                'is_aktif'      => 1,
            ],
        ];

        foreach ($jurusans as $j) {
            DB::table('jurusans')->updateOrInsert(
                ['kode' => $j['kode']],
                array_merge($j, ['created_at' => now(), 'updated_at' => now()])
            );
        }
        $this->command->info('Jurusan seeded.');

        // ── Kelas ─────────────────────────────────────────────────────────────
        $tkjtId = DB::table('jurusans')->where('kode', 'TKJT')->value('id');
        $toId   = DB::table('jurusans')->where('kode', 'TO')->value('id');
        $aklId  = DB::table('jurusans')->where('kode', 'AKL')->value('id');
        $taId = DB::table('tahun_ajarans')->where('nama', '2026/2027')->value('id');
        if (!$taId) {
            $taId = DB::table('tahun_ajarans')->insertGetId([
                'nama' => '2026/2027',
                'tahun_mulai' => 2026,
                'tahun_selesai' => 2027,
                'is_aktif' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $kelas = [
            // AKL
            ['nama' => 'X AKL',   'nama_kelas' => 'X AKL',   'tingkat' => 'X',   'jurusan_id' => $aklId,  'tahun_ajaran_id' => $taId, 'wali_kelas' => 'Khayatun Nufus, S.Pd',            'is_aktif' => 1],
            ['nama' => 'XI AKL',  'nama_kelas' => 'XI AKL',  'tingkat' => 'XI',  'jurusan_id' => $aklId,  'tahun_ajaran_id' => $taId, 'wali_kelas' => 'NUR AFIFAH, S.Pd',                'is_aktif' => 1],
            ['nama' => 'XII AKL', 'nama_kelas' => 'XII AKL', 'tingkat' => 'XII', 'jurusan_id' => $aklId,  'tahun_ajaran_id' => $taId, 'wali_kelas' => 'Silvi Dwi Manitik S.pd',           'is_aktif' => 1],
            // TKJT
            ['nama' => 'X TKJT',  'nama_kelas' => 'X TKJT',  'tingkat' => 'X',   'jurusan_id' => $tkjtId, 'tahun_ajaran_id' => $taId, 'wali_kelas' => 'Dewi Priyatin, S.Pd',              'is_aktif' => 1],
            ['nama' => 'XI TKJT', 'nama_kelas' => 'XI TKJT', 'tingkat' => 'XI',  'jurusan_id' => $tkjtId, 'tahun_ajaran_id' => $taId, 'wali_kelas' => 'ALI MUSTOPA, S.Pd',                'is_aktif' => 1],
            ['nama' => 'XII TKJT','nama_kelas' => 'XII TKJT','tingkat' => 'XII', 'jurusan_id' => $tkjtId, 'tahun_ajaran_id' => $taId, 'wali_kelas' => 'Yuliyanti, S.Pd.I',                'is_aktif' => 1],
            // TO
            ['nama' => 'X TO',    'nama_kelas' => 'X TO',    'tingkat' => 'X',   'jurusan_id' => $toId,   'tahun_ajaran_id' => $taId, 'wali_kelas' => 'M Sabiqul Huda',                   'is_aktif' => 1],
            ['nama' => 'XI TO',   'nama_kelas' => 'XI TO',   'tingkat' => 'XI',  'jurusan_id' => $toId,   'tahun_ajaran_id' => $taId, 'wali_kelas' => 'Ani Mawaddatul Mukhlishoh, S.Pd',  'is_aktif' => 1],
            ['nama' => 'XII TO',  'nama_kelas' => 'XII TO',  'tingkat' => 'XII', 'jurusan_id' => $toId,   'tahun_ajaran_id' => $taId, 'wali_kelas' => 'Jefri Handa, A.Md',                'is_aktif' => 1],
        ];

        foreach ($kelas as $k) {
            DB::table('kelas')->updateOrInsert(
                ['nama' => $k['nama'], 'tahun_ajaran_id' => $k['tahun_ajaran_id']],
                array_merge($k, ['created_at' => now(), 'updated_at' => now()])
            );
        }
        $this->command->info('Kelas seeded.');
    }
}
