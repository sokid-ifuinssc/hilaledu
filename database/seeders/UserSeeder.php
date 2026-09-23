<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menggunakan updateOrCreate agar aman dijalankan berulang kali.
     */
    public function run(): void
    {
        $users = [
            [
                'name'          => 'Super Administrator',
                'username'      => 'superadmin',
                'email'         => 'superadmin@hilaledu.sch.id',
                'password'      => 'password123',
                'password_plain'=> 'password123',
                'role'          => 'superadmin',
                'is_active'     => true,
                'nama_lengkap'  => 'Super Administrator',
            ],
            [
                'name'          => 'Ahmad Fauzi, S.Pd',
                'username'      => 'guru01',
                'email'         => 'guru01@hilaledu.sch.id',
                'password'      => 'password123',
                'password_plain'=> 'password123',
                'role'          => 'guru',
                'jabatan_utama' => 'Guru',
                'is_active'     => true,
                'nama_lengkap'  => 'Ahmad Fauzi',
                'nip'           => '198501012010011001',
                'no_hp'         => '081234567890',
                'jenis_kelamin' => 'L',
                'tahun_masuk'   => '2018',
                'lulusan_tahun' => '2014',
                'alamat'        => 'Jl. Pendidikan No. 10',
                'desa'          => 'Sukamaju',
                'kecamatan'     => 'Cibadak',
                'kabupaten'     => 'Sukabumi',
                'provinsi'      => 'Jawa Barat',
                'pendidikan_sd' => 'SDN 1 Sukamaju',
                'tahun_lulus_sd'=> '2003',
                'pendidikan_smp'=> 'SMPN 1 Cibadak',
                'tahun_lulus_smp'=> '2006',
                'pendidikan_sma'=> 'SMAN 1 Cibadak',
                'tahun_lulus_sma'=> '2009',
                'pendidikan_s1' => 'Universitas Pendidikan Indonesia - Pend. Matematika',
                'tahun_lulus_s1'=> '2014',
            ],
            [
                'name'          => 'Siti Nurhaliza, S.Kom',
                'username'      => 'tendik01',
                'email'         => 'tendik01@hilaledu.sch.id',
                'password'      => 'password123',
                'password_plain'=> 'password123',
                'role'          => 'tendik',
                'jabatan_utama' => 'Tendik',
                'is_active'     => true,
                'nama_lengkap'  => 'Siti Nurhaliza',
                'nip'           => '2345678901234567',
                'no_hp'         => '081234567891',
                'jenis_kelamin' => 'P',
                'tahun_masuk'   => '2019',
                'lulusan_tahun' => '2015',
                'alamat'        => 'Jl. Sukajadi No. 12',
                'desa'          => 'Cibadak',
                'kecamatan'     => 'Cibadak',
                'kabupaten'     => 'Sukabumi',
                'provinsi'      => 'Jawa Barat',
                'pendidikan_sd' => 'SDN 1 Sukajadi',
                'tahun_lulus_sd'=> '2004',
                'pendidikan_smp'=> 'SMPN 1 Cibadak',
                'tahun_lulus_smp'=> '2007',
                'pendidikan_sma'=> 'SMKN 1 Sukabumi',
                'tahun_lulus_sma'=> '2010',
                'pendidikan_s1' => 'Universitas Bina Sarana Informatika - Sistem Informasi',
                'tahun_lulus_s1'=> '2015',
            ],
            [
                'name'          => 'Muhammad Rizki',
                'username'      => 'siswa01',
                'email'         => 'siswa01@hilaledu.sch.id',
                'password'      => 'password123',
                'password_plain'=> 'password123',
                'role'          => 'siswa',
                'is_active'     => true,
                'nama_lengkap'  => 'Muhammad Rizki',
                'nip'           => '0071234567',
                'no_hp'         => '085712345678',
                'no_hp_ortu'    => '081298765432',
                'nama_ayah'     => 'Bambang Pratama',
                'nama_ibu'      => 'Siti Aminah',
                'jenis_kelamin' => 'L',
                'alamat'        => 'Jl. Raya Cibadak No. 45, RT 02 / RW 04',
                'desa'          => 'Cibadak',
                'kecamatan'     => 'Cibadak',
                'kabupaten'     => 'Sukabumi',
                'provinsi'      => 'Jawa Barat',
                'pendidikan_sd' => 'SDN 1 Cibadak',
                'tahun_lulus_sd'=> '2021',
                'pendidikan_smp'=> 'SMPN 1 Cibadak',
                'tahun_lulus_smp'=> '2024',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['username' => $userData['username']],
                $userData
            );
        }
    }
}
