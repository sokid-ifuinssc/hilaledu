<?php

namespace Database\Seeders;

use App\Models\TugasTambahan;
use Illuminate\Database\Seeder;

class MasterTugasTambahanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Manajemen & Pimpinan Sekolah
            ['nama' => 'Kepala Sekolah', 'kode' => 'KEPAS', 'kategori' => 'Manajemen', 'deskripsi' => 'Penanggung jawab utama operasional dan manajemen sekolah'],
            ['nama' => 'Wakasek Kurikulum & Akademik', 'kode' => 'WAKASEK_KUR', 'kategori' => 'Manajemen', 'deskripsi' => 'Pengembang & pengawas kegiatan akademik serta kurikulum sekolah'],
            ['nama' => 'Wakasek Kesiswaan', 'kode' => 'WAKASEK_KSW', 'kategori' => 'Manajemen', 'deskripsi' => 'Pengelola kedisiplinan, kegiatan, dan tata tertib kesiswaan'],
            ['nama' => 'Wakasek Humas & Hubungan Industri', 'kode' => 'WAKASEK_HUMAS', 'kategori' => 'Manajemen', 'deskripsi' => 'Penghubung kemitraan dunia usaha/dunia industri (DUDI) & Prakerin'],
            ['nama' => 'Wakasek Sarana & Prasarana', 'kode' => 'WAKASEK_SARPRAS', 'kategori' => 'Manajemen', 'deskripsi' => 'Pengelola fasilitas, gedung, dan infrastruktur sekolah'],
            
            // Program Keahlian & Akademik
            ['nama' => 'Ketua Program Keahlian (Kaprog)', 'kode' => 'KAPROG', 'kategori' => 'Akademik', 'deskripsi' => 'Kepala program studi keahlian produktif SMK'],
            ['nama' => 'Wali Kelas', 'kode' => 'WALIKELAS', 'kategori' => 'Akademik', 'deskripsi' => 'Pembimbing dan penanggung jawab rombel kelas siswa'],
            ['nama' => 'Guru Bimbingan Konseling (BK)', 'kode' => 'GURU_BK', 'kategori' => 'Akademik', 'deskripsi' => 'Konselor bimbingan konseling dan pengembangan karakter siswa'],
            ['nama' => 'Kepala Laboratorium', 'kode' => 'KALAB', 'kategori' => 'Akademik', 'deskripsi' => 'Pengelola fasilitas laboratorium praktikum'],
            ['nama' => 'Kepala Perpustakaan', 'kode' => 'KAPERPUS', 'kategori' => 'Akademik', 'deskripsi' => 'Pengelola layanan perpustakaan dan literasi sekolah'],

            // Sistem Prakerin / PKL
            ['nama' => 'Koordinator Prakerin / PKL', 'kode' => 'KOR_PRAKERIN', 'kategori' => 'Prakerin', 'deskripsi' => 'Koordinator utama penempatan & pemantauan Prakerin/PKL siswa'],
            ['nama' => 'Guru Pembimbing Prakerin / PKL', 'kode' => 'PEMBIMBING_PKL', 'kategori' => 'Prakerin', 'deskripsi' => 'Pembimbing lapangan & supervisor penanganan Prakerin/PKL siswa'],

            // Kesiswaan & Extrakulikuler
            ['nama' => 'Pembina OSIS', 'kode' => 'PEMBINA_OSIS', 'kategori' => 'Kesiswaan', 'deskripsi' => 'Pembina organisasi siswa intra sekolah'],
            ['nama' => 'Pembina Ekstrakulikuler', 'kode' => 'PEMBINA_EKSKUL', 'kategori' => 'Kesiswaan', 'deskripsi' => 'Pembina kegiatan ekstrakulikuler sekolah'],
            ['nama' => 'Bendahara Sekolah / BOS', 'kode' => 'BENDAHARA', 'kategori' => 'Manajemen', 'deskripsi' => 'Pengelola keuangan & anggaran sekolah'],
        ];

        foreach ($data as $item) {
            TugasTambahan::updateOrCreate(
                ['nama' => $item['nama']],
                $item
            );
        }
    }
}
