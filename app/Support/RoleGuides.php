<?php

namespace App\Support;

use App\Models\User;

class RoleGuides
{
    public static function allGuides(): array
    {
        return [
            [
                'key'         => 'superadmin',
                'role_label'  => 'Superadmin',
                'badge'       => 'Kendali Penuh Sistem',
                'icon'        => 'bi-shield-lock-fill',
                'color'       => 'amber',
                'ringkasan'   => 'Bertanggung jawab menjaga kesiapan fondasi data sekolah, pengelolaan akun dan hak akses seluruh pengguna, serta memastikan semua menu berjalan bebas error.',
                'tugas_utama' => [
                    'Menyiapkan Data Master Pokok (Tahun Ajaran, Jurusan, Kelas, Rombel, Master Tugas Tambahan).',
                    'Mengelola akun Guru, Tendik, Siswa, serta menunjuk Admin Unit sesuai bidangnya.',
                    'Melakukan audit tampilan dan hak akses menu tiap pengguna melalui fitur "Lihat Sebagai User".',
                    'Memantau Log Aktivitas, Backup Database, serta proses Kenaikan Kelas & Kelulusan Siswa.',
                ],
                'cara_kerja'  => [
                    'Awal tahun: aktifkan Tahun Ajaran, cek Jurusan/Kelas/Rombel dan akun pengguna.',
                    'Tugaskan admin unit (Akademik, BK, Kesiswaan, Prakerin, Keuangan, Koperasi, Payroll, Dokumen).',
                    'Klik tombol "Lihat Sebagai User" di topbar atau Manajemen Akun untuk mencoba menu tiap role secara langsung.',
                    'Akhir tahun: jalankan Kenaikan Kelas / Kelulusan agar alumni masuk ke modul Tracer Study.',
                ],
            ],
            [
                'key'         => 'admin',
                'role_label'  => 'Admin Unit / Pengelola Bidang',
                'badge'       => 'Operator & Pengelola Unit',
                'icon'        => 'bi-person-workspace',
                'color'       => 'blue',
                'ringkasan'   => 'Mengelola data dan layanan operasional pada unit spesifik yang ditugaskan (Akademik, BK, Kesiswaan, Prakerin, Keuangan, Koperasi, Payroll, atau Dokumen/Sertifikat).',
                'tugas_utama' => [
                    'Admin Akademik: menyusun Kalender Akademik, Plotting Kurikulum, Jadwal Pelajaran, serta memantau Laporan Kehadiran & KBM.',
                    'Admin BK / Kesiswaan: mengelola master poin pelanggaran, memantau kasus pelanggaran, serta mengelola Ekstrakurikuler.',
                    'Admin Prakerin: mengelola Kerja Sama DUDI, Data DU/DI, Periode PKL, Penempatan Siswa, dan Laporan Prakerin.',
                    'Admin Keuangan / Koperasi: menerbitkan tagihan siswa, mencatat pembayaran, dan mengelola transaksi anggota koperasi.',
                    'Admin Dokumen & Payroll: menerbitkan sertifikat/SKL/ijazah siswa serta menyiapkan periode & slip gaji pegawai.',
                ],
                'cara_kerja'  => [
                    'Masuk ke dashboard unit sesuai role admin yang diberikan.',
                    'Lengkapi data master unit terlebih dahulu sebelum input transaksi/operasional.',
                    'Verifikasi pengajuan dari guru atau siswa (seperti validasi jurnal, pembayaran, akses unduh sertifikat).',
                    'Cetak laporan berkala unit untuk diserahkan kepada pimpinan sekolah.',
                ],
            ],
            [
                'key'         => 'kepala_sekolah',
                'role_label'  => 'Kepala Sekolah',
                'badge'       => 'Pimpinan & Approval Akhir',
                'icon'        => 'bi-award-fill',
                'color'       => 'indigo',
                'ringkasan'   => 'Melakukan pengawasan menyeluruh lintas bidang, memberi persetujuan tingkat puncak (seperti SP-3 dan cuti), serta mengevaluasi kinerja tugas tambahan.',
                'tugas_utama' => [
                    'Memantau rekap kehadiran guru/pegawai, keterlaksanaan KBM, dan statistik pelanggaran siswa.',
                    'Memberi persetujuan (Setujui/Tolak) dan mengisi laporan pimpinan pada kasus disiplin berat (SP-3).',
                    'Mengevaluasi kesesuaian antara Rencana Program Kerja dan Realisasi Tugas Tambahan guru/wakasek.',
                    'Menyetujui pengajuan cuti pegawai serta memantau laporan keuangan dan kesiswaan.',
                ],
                'cara_kerja'  => [
                    'Cek Dashboard Kepsek & notifikasi untuk melihat item yang menunggu persetujuan.',
                    'Buka menu Persetujuan Kepala Sekolah pada kasus SP-3 untuk meninjau kronologi dan mengambil keputusan.',
                    'Pantau menu Program Kerja Tambahan & Laporan Realisasi untuk menilai ketercapaian target kerja unit.',
                    'Gunakan Laporan Berkala BK, Rekap Kehadiran, dan Laporan KBM sebagai bahan rapat evaluasi.',
                ],
            ],
            [
                'key'         => 'guru',
                'role_label'  => 'Guru Mata Pelajaran',
                'badge'       => 'Pendidik & Pelaksana KBM',
                'icon'        => 'bi-easel2-fill',
                'color'       => 'emerald',
                'ringkasan'   => 'Menjalankan kegiatan belajar mengajar sesuai jadwal, mencatat kehadiran diri dan siswa, meninggalkan tugas bila berhalangan, serta menginput nilai mapel.',
                'tugas_utama' => [
                    'Melakukan Presensi Masuk/Pulang harian dan Absensi Mengajar di setiap jam pelajaran.',
                    'Jika berhalangan hadir (Izin, Sakit, atau Tugas Luar), mengunggah instruksi/file Tugas Siswa langsung dari modal Absensi Mapel.',
                    'Menyusun Perangkat Ajar, Minggu Efektif, Rencana Pembelajaran, dan mengisi Jurnal KBM beserta absensi siswa.',
                    'Menginput Nilai Mata Pelajaran tepat waktu dan memeriksa Slip Gaji / Bisyarah pribadi.',
                ],
                'cara_kerja'  => [
                    'Cek SK Mengajar & jadwal hari ini pada Dashboard Guru.',
                    'Saat datang ke sekolah klik Presensi Masuk; saat jam mengajar tiba klik "Klik Absen Kehadiran".',
                    'Bila hadir mengajar: isi Jurnal KBM & presensi siswa. Bila berhalangan: pilih Izin/Sakit/Tugas Luar lalu isi tugas & unggah file untuk siswa.',
                    'Di akhir periode penilaian, buka menu Input Nilai Mapel agar otomatis terhimpun ke Leger Wali Kelas.',
                ],
            ],
            [
                'key'         => 'tugas_tambahan',
                'role_label'  => 'Guru dengan Tugas Tambahan (Waka / Wali Kelas / Kaprog / BK / Pembina / Piket)',
                'badge'       => 'Koordinator & Pembimbing',
                'icon'        => 'bi-diagram-3-fill',
                'color'       => 'purple',
                'ringkasan'   => 'Menjalankan peran struktural tambahan seperti perencanaan program kerja, pembimbingan kelas/jurusan, layanan BK, kesiswaan, PKL, maupun piket harian.',
                'tugas_utama' => [
                    'Semua pemegang tugas tambahan (Hubin, Kurikulum, Kesiswaan, Sarpras, OSIS, dll): menyusun Rencana Program Kerja & tanggal kegiatannya (otomatis tampil di Kalender Akademik) serta melaporkan realisasinya.',
                    'Guru BK: mencatat pelanggaran, menetapkan jenis tindakan, mencetak surat panggilan/SP, dan menutup kasus setelah tuntas.',
                    'Wali Kelas & Kaprog: memantau jadwal, leger nilai, tagihan, eskul kelas/jurusan, serta menyetujui/melaporkan progres BK.',
                    'Waka Kesiswaan & Pembina OSIS: menyetujui SP-2/SP-3, mengelola ekstrakurikuler, dan memantau kedisiplinan.',
                    'Petugas Piket: memantau kehadiran harian guru & siswa serta mencatat laporan piket.',
                ],
                'cara_kerja'  => [
                    'Buka menu Penugasan & SK Guru -> buat Rencana Program Kerja dan tentukan Tanggal Mulai/Selesai agar otomatis masuk Kalender Akademik.',
                    'Pantau menu khusus peran (Portal Wali Kelas, Portal Kaprog, Monitoring BK, Persetujuan Kesiswaan, Monitoring Eskul, atau Piket).',
                    'Tindak lanjuti notifikasi persetujuan atau laporan kasus yang masuk sesuai kewenangan masing-masing.',
                    'Setelah program terlaksana, isi Laporan Realisasi Program dengan foto/dokumen bukti.',
                ],
            ],
            [
                'key'         => 'tendik',
                'role_label'  => 'Tenaga Kependidikan (Tendik)',
                'badge'       => 'Layanan & Dukungan Operasional',
                'icon'        => 'bi-person-badge-fill',
                'color'       => 'sky',
                'ringkasan'   => 'Mendukung kelancaran administrasi, ketatausahaan, layanan piket, atau tugas tambahan lain yang ditetapkan sekolah.',
                'tugas_utama' => [
                    'Melakukan pencatatan kehadiran harian pegawai sesuai ketentuan jam kerja.',
                    'Menjalankan tugas operasional, pembinaan eskul, atau petugas piket jika ditunjuk.',
                    'Jika memiliki Tugas Tambahan, menyusun Rencana Program Kerja dan melaporkan Realisasi Pelaksanaannya.',
                    'Memperbarui biodata/profil pribadi serta memantau Slip Gaji bulanan.',
                ],
                'cara_kerja'  => [
                    'Masuk ke Beranda Tendik setiap hari kerja untuk melihat ringkasan kehadiran dan agenda sekolah.',
                    'Buka menu Program Kerja Tambahan atau Eskul Binaan apabila mendapat surat penugasan tambahan.',
                    'Periksa menu Slip Gaji Saya setiap periode penggajian dipublikasikan.',
                ],
            ],
            [
                'key'         => 'siswa',
                'role_label'  => 'Siswa',
                'badge'       => 'Peserta Didik',
                'icon'        => 'bi-mortarboard-fill',
                'color'       => 'rose',
                'ringkasan'   => 'Mengikuti kegiatan pembelajaran harian, mengerjakan tugas saat guru berhalangan hadir, memantau kehadiran, tagihan, eskul, hingga mengunduh dokumen resmi.',
                'tugas_utama' => [
                    'Melakukan Absen Hadir harian dan mengikuti jadwal pelajaran kelas setiap hari.',
                    'Memantau kartu "Tugas & Materi Mapel (Guru Berhalangan Hadir)" di Dashboard Siswa bila ada guru yang Izin/Sakit/Tugas Luar.',
                    'Berpartisipasi pada Ekstrakurikuler pilihan (dan absensi mandiri) serta mengisi Jurnal Prakerin/PKL bagi siswa peserta PKL.',
                    'Memantau Tagihan Sekolah, poin kedisiplinan, serta mengunduh Sertifikat / SKL / Ijazah ketika akses telah dibuka admin.',
                ],
                'cara_kerja'  => [
                    'Login ke Portal Siswa setiap pagi lalu klik "Absen Hadir" dan cek Jadwal Hari Ini.',
                    'Jika ada guru berhalangan hadir, lihat instruksi tugas dan klik "Unduh / Lihat File Tugas" pada kartu mapel terkait.',
                    'Gunakan menu Ekstrakurikuler Saya, Jurnal Prakerin, Tagihan Saya, dan Dokumen & Sertifikat sesuai kebutuhan.',
                    'Jika ada kendala pembelajaran, sampaikan melalui Suara Siswa (Keluhan KBM Anonim) secara konstruktif.',
                ],
            ],
        ];
    }

    public static function currentUserRoleKeys(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $keys = [];
        if ($user->isSuperAdmin()) {
            $keys[] = 'superadmin';
        }
        if ($user->role === 'admin' || !empty($user->admin_roles)) {
            $keys[] = 'admin';
        }
        if ($user->isKepalaSekolah() && !$user->isSuperAdmin()) {
            $keys[] = 'kepala_sekolah';
        }
        if ($user->role === 'guru') {
            $keys[] = 'guru';
        }
        if ($user->role !== 'siswa' && $user->role !== 'superadmin' && $user->hasAnyTugasTambahan()) {
            $keys[] = 'tugas_tambahan';
        }
        if ($user->role === 'tendik') {
            $keys[] = 'tendik';
        }
        if ($user->role === 'siswa') {
            $keys[] = 'siswa';
        }
        if (in_array($user->role, ['guru_bk', 'waka_kesiswaan', 'kaprog', 'wali_kelas'], true)) {
            $keys[] = 'tugas_tambahan';
        }

        return array_values(array_unique($keys));
    }
}
