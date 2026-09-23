<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PayrollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Daftarkan / Update Aplikasi HilalPay di Tabel Applications
        Application::updateOrCreate(
            ['slug' => 'hilalpay'],
            [
                'name'             => 'HilalPay',
                'description'      => 'Sistem Penggajian & Bisyarah terpadu untuk Guru dan Tenaga Kependidikan SMK Plus Al Hilal.',
                'icon'             => 'bi-cash-coin',
                'color'            => '#10b981',
                'url'              => url('/superadmin/payroll'),
                'database_name'    => 'db_hilaledu',
                'sso_enabled'      => true,
                'auto_sync'        => false,
                'status'           => 'active',
                'features'         => ['Gaji Pokok & Tunjangan', 'Honor Jam Mengajar', 'Slip Gaji Digital & Cetak', 'Laporan Penggajian'],
                'sort_order'       => 6,
            ]
        );

        // 2. Default Komponen Gaji
        $komponens = [
            [
                'kode'            => 'GP01',
                'nama'            => 'Gaji Pokok',
                'jenis'           => 'penerimaan',
                'tipe'            => 'tetap',
                'nominal_default' => 1500000,
                'is_aktif'        => true,
                'keterangan'      => 'Gaji pokok bulanan pendidik / tenaga kependidikan',
            ],
            [
                'kode'            => 'HJM01',
                'nama'            => 'Honor Jam Mengajar',
                'jenis'           => 'penerimaan',
                'tipe'            => 'per_jam',
                'nominal_default' => 35000,
                'is_aktif'        => true,
                'keterangan'      => 'Honor per jam tatap muka pelajaran',
            ],
            [
                'kode'            => 'TJ01',
                'nama'            => 'Tunjangan Jabatan / Tugas Tambahan',
                'jenis'           => 'penerimaan',
                'tipe'            => 'tetap',
                'nominal_default' => 250000,
                'is_aktif'        => true,
                'keterangan'      => 'Tunjangan untuk wali kelas, kaprog, kepsek, wakasek, dll',
            ],
            [
                'kode'            => 'TK01',
                'nama'            => 'Tunjangan Kehadiran & Transport',
                'jenis'           => 'penerimaan',
                'tipe'            => 'per_kehadiran',
                'nominal_default' => 15000,
                'is_aktif'        => true,
                'keterangan'      => 'Uang transportasi dan kehadiran mengajar',
            ],
            [
                'kode'            => 'BONUS01',
                'nama'            => 'Insentif & Bonus Khusus',
                'jenis'           => 'penerimaan',
                'tipe'            => 'tetap',
                'nominal_default' => 0,
                'is_aktif'        => true,
                'keterangan'      => 'Insentif pencapaian atau kegiatan sekolah',
            ],
            [
                'kode'            => 'PBP01',
                'nama'            => 'Potongan BPJS Ketenagakerjaan',
                'jenis'           => 'potongan',
                'tipe'            => 'tetap',
                'nominal_default' => 45000,
                'is_aktif'        => true,
                'keterangan'      => 'Iuran jaminan sosial ketenagakerjaan',
            ],
            [
                'kode'            => 'PKOP01',
                'nama'            => 'Potongan Koperasi Sekolah',
                'jenis'           => 'potongan',
                'tipe'            => 'tetap',
                'nominal_default' => 50000,
                'is_aktif'        => true,
                'keterangan'      => 'Simpanan wajib koperasi SMK Plus Al Hilal',
            ],
            [
                'kode'            => 'PINF01',
                'nama'            => 'Infaq & Kas Sosial Yayasan',
                'jenis'           => 'potongan',
                'tipe'            => 'tetap',
                'nominal_default' => 25000,
                'is_aktif'        => true,
                'keterangan'      => 'Infaq bulanan kas sosial yayasan Al-Hilal',
            ],
        ];

        foreach ($komponens as $k) {
            DB::table('payroll_komponens')->updateOrInsert(
                ['kode' => $k['kode']],
                array_merge($k, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // 3. Setting Default Gaji Guru & Tendik yang belum diset
        $pegawais = User::whereIn('role', ['guru', 'tendik'])->get();
        foreach ($pegawais as $pegawai) {
            $isGuru = $pegawai->role === 'guru';

            // Hitung tunjangan jabatan berdasarkan tugas tambahan
            $tunjanganJabatan = 0;
            $tugas = is_array($pegawai->tugas_tambahan) ? $pegawai->tugas_tambahan : [];
            foreach ($tugas as $t) {
                if (str_contains($t, 'Kepala Sekolah')) {
                    $tunjanganJabatan += 1000000;
                } elseif (str_contains($t, 'Wakasek') || str_contains($t, 'Wakil Kepala')) {
                    $tunjanganJabatan += 500000;
                } elseif (str_contains($t, 'Kaprog') || str_contains($t, 'Ketua Program')) {
                    $tunjanganJabatan += 350000;
                } elseif (str_contains($t, 'Wali Kelas')) {
                    $tunjanganJabatan += 250000;
                } elseif (str_contains($t, 'Bendahara')) {
                    $tunjanganJabatan += 300000;
                } elseif (str_contains($t, 'Pembina') || str_contains($t, 'Kepala Perpustakaan') || str_contains($t, 'Kepala Lab')) {
                    $tunjanganJabatan += 150000;
                }
            }

            DB::table('payroll_settings')->updateOrInsert(
                ['user_id' => $pegawai->id],
                [
                    'gaji_pokok'           => $isGuru ? 1500000 : 1800000,
                    'honor_per_jam'        => $isGuru ? 35000 : 0,
                    'jam_mengajar_default' => $isGuru ? 24 : 0,
                    'tunjangan_jabatan'    => $tunjanganJabatan,
                    'tunjangan_kehadiran'  => $isGuru ? 200000 : 250000,
                    'tunjangan_lain'       => 0,
                    'potongan_bpjs'        => 45000,
                    'potongan_koperasi'    => 50000,
                    'potongan_lain'        => 25000, // Infaq
                    'rekening_bank'        => 'BSI (Bank Syariah Indonesia)',
                    'nomor_rekening'       => '71' . rand(10000000, 99999999),
                    'atas_nama_rekening'   => $pegawai->name,
                    'catatan'              => 'Pengaturan default awal sistem HilalPay',
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]
            );
        }
    }
}
