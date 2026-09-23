<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;
use Illuminate\Support\Str;

class ApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apps = [
            [
                'name'             => 'Monitoring BK',
                'slug'             => 'monitoring-bk',
                'description'      => 'Sistem monitoring bimbingan dan konseling siswa. Mencatat pelanggaran, konseling, dan perkembangan siswa.',
                'icon'             => 'bi-shield-check',
                'color'            => '#e74c3c',
                'url'              => 'http://localhost:8001',
                'database_name'    => 'db_monitoring_bk',
                'sso_enabled'      => true,
                'auto_sync'        => true,
                'status'           => 'active',
                'features'         => ['Catatan Pelanggaran', 'Jadwal Konseling', 'Rekap Poin Siswa', 'Laporan BK'],
                'sort_order'       => 1,
            ],
            [
                'name'             => 'Sistem Informasi Prakerin',
                'slug'             => 'prakerin',
                'description'      => 'Manajemen praktik kerja industri (PKL). Penempatan, monitoring, dan penilaian prakerin siswa.',
                'icon'             => 'bi-briefcase-fill',
                'color'            => '#3498db',
                'url'              => 'http://localhost:8002',
                'database_name'    => 'db_prakerin',
                'sso_enabled'      => true,
                'auto_sync'        => true,
                'status'           => 'active',
                'features'         => ['Data DU/DI', 'Penempatan Siswa', 'Jurnal Prakerin', 'Penilaian Industri'],
                'sort_order'       => 2,
            ],
            [
                'name'             => 'Perpustakaan Digital',
                'slug'             => 'perpustakaan',
                'description'      => 'Manajemen perpustakaan sekolah. Katalog buku, peminjaman, pengembalian, dan denda.',
                'icon'             => 'bi-book-half',
                'color'            => '#2ecc71',
                'url'              => null,
                'database_name'    => 'db_perpustakaan',
                'sso_enabled'      => true,
                'auto_sync'        => true,
                'status'           => 'coming_soon',
                'features'         => ['Katalog Buku', 'Peminjaman & Pengembalian', 'Denda Otomatis', 'Laporan Perpustakaan'],
                'sort_order'       => 3,
            ],
            [
                'name'             => 'Koperasi Sekolah',
                'slug'             => 'koperasi',
                'description'      => 'Sistem manajemen koperasi sekolah. Penjualan, stok barang, simpan pinjam, dan laporan keuangan.',
                'icon'             => 'bi-shop',
                'color'            => '#f39c12',
                'url'              => null,
                'database_name'    => 'db_koperasi',
                'sso_enabled'      => true,
                'auto_sync'        => true,
                'status'           => 'coming_soon',
                'features'         => ['Penjualan (POS)', 'Stok Barang', 'Simpan Pinjam', 'Laporan Keuangan'],
                'sort_order'       => 4,
            ],
            [
                'name'             => 'Sistem Informasi Akademik',
                'slug'             => 'akademik',
                'description'      => 'Pengelolaan data akademik lengkap. Kurikulum, jadwal, nilai, absensi, dan raport digital.',
                'icon'             => 'bi-mortarboard-fill',
                'color'            => '#9b59b6',
                'url'              => null,
                'database_name'    => 'db_akademik',
                'sso_enabled'      => true,
                'auto_sync'        => true,
                'status'           => 'coming_soon',
                'features'         => ['Kurikulum & Mapel', 'Jadwal Pelajaran', 'Input Nilai', 'Raport Digital'],
                'sort_order'       => 5,
            ],
            [
                'name'             => 'HilalEdu (Portal Utama)',
                'slug'             => 'hilaledu',
                'description'      => 'Portal utama HilalEdu. Single Sign-On, manajemen pengguna, dan pusat integrasi seluruh aplikasi SMK Plus Al Hilal.',
                'icon'             => 'bi-grid-3x3-gap-fill',
                'color'            => '#2d8a4e',
                'url'              => 'http://localhost:8000',
                'database_name'    => 'db_hilaledu',
                'sso_enabled'      => true,
                'auto_sync'        => false,
                'status'           => 'active',
                'features'         => ['Single Sign-On (SSO)', 'Manajemen Pengguna', 'Integrasi Aplikasi', 'Dashboard Multi-Role'],
                'sort_order'       => 0,
            ],
            [
                'name'             => 'HilalPay',
                'slug'             => 'hilalpay',
                'description'      => 'Sistem Penggajian & Bisyarah terpadu untuk Guru dan Tenaga Kependidikan SMK Plus Al Hilal.',
                'icon'             => 'bi-cash-coin',
                'color'            => '#10b981',
                'url'              => 'http://localhost:8000/superadmin/payroll',
                'database_name'    => 'db_hilaledu',
                'sso_enabled'      => true,
                'auto_sync'        => false,
                'status'           => 'active',
                'features'         => ['Gaji Pokok & Tunjangan', 'Honor Jam Mengajar', 'Slip Gaji Digital & Cetak', 'Laporan Penggajian'],
                'sort_order'       => 6,
            ],
        ];

        foreach ($apps as $app) {
            $existing = Application::where('slug', $app['slug'])->first();
            if ($existing) {
                if (empty($existing->api_key)) {
                    $app['api_key'] = 'hila_' . Str::random(40);
                }
                $existing->update($app);
            } else {
                $app['api_key'] = 'hila_' . Str::random(40);
                Application::create($app);
            }
        }
    }
}
