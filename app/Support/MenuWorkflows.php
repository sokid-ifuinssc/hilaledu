<?php

namespace App\Support;

class MenuWorkflows
{
    public static function forRoute(?string $routeName): array
    {
        $route = (string) $routeName;

        if ($route === 'tentang-aplikasi.index') {
            return [
                'id'       => 'tentang-aplikasi',
                'title'    => 'Tentang Aplikasi & Panduan Role HilalEdu',
                'subtitle' => 'Ringkasan tugas utama dan cara kerja garis besar setiap role pengguna',
                'icon'     => 'bi-info-circle-fill',
                'color'    => 'sky',
                'actors'   => 'Seluruh Pengguna (Superadmin, Admin, Kepsek, Guru, Tendik, Siswa)',
                'steps'    => [
                    ['actor' => 'Pengguna', 'title' => '1. Kenali Peran Anda', 'desc' => 'Bagian atas halaman menyoroti tugas utama dan langkah kerja sesuai role serta tugas tambahan akun Anda.'],
                    ['actor' => 'Pengguna', 'title' => '2. Pahami Kolaborasi Antar Role', 'desc' => 'Pelajari peran role lainnya agar koordinasi KBM, BK, PKL, keuangan, dan dokumen berjalan lancar.'],
                    ['actor' => 'Pengguna', 'title' => '3. Buka Peta Alur Sistem', 'desc' => 'Gunakan tautan Peta Alur Sistem HilalEdu atau tombol Alur Kerja di topbar untuk detail teknis tiap menu.'],
                ],
                'result' => 'Setiap warga sekolah memahami peran, tugas pokok, dan cara kerja aplikasi HilalEdu secara menyeluruh.',
            ];
        }

        foreach (self::allModules() as $module) {
            foreach ($module['patterns'] as $pattern) {
                if ($route !== '' && \Illuminate\Support\Str::is($pattern, $route)) {
                    return $module;
                }
            }
        }

        return [
            'id'       => 'portal-umum',
            'title'    => 'Portal Sistem HilalEdu',
            'subtitle' => 'Panduan navigasi beranda dan layanan pengguna sesuai peran Anda',
            'icon'     => 'bi-compass',
            'color'    => 'emerald',
            'actors'   => 'Seluruh Warga Sekolah',
            'steps'    => [
                ['actor' => 'Pengguna', 'title' => 'Masuk Portal', 'desc' => 'Login sesuai akun masing-masing untuk menampilkan menu berdasarkan hak akses dan tugas tambahan.'],
                ['actor' => 'Pengguna', 'title' => 'Pantau Ringkasan', 'desc' => 'Cek statistik harian, notifikasi, jadwal aktif, atau tugas yang menunggu tindakan pada dashboard.'],
                ['actor' => 'Pengguna', 'title' => 'Eksekusi Menu', 'desc' => 'Pilih modul kerja di sidebar kiri (BK, KBM, Kurikulum, Tugas Tambahan, Prakerin, Keuangan, dll).'],
                ['actor' => 'Sistem', 'title' => 'Rekap Terpusat', 'desc' => 'Setiap input otomatis tersimpan, memicu notifikasi terkait, dan masuk laporan pimpinan.'],
            ],
            'result' => 'Seluruh aktivitas akademik, kesiswaan, dan administrasi terpantau secara real-time dalam satu sistem terpadu.',
        ];
    }

    public static function allModules(): array
    {
        return [
            [
                'id'       => 'monitoring-bk',
                'title'    => 'Monitoring BK & Pembinaan Kedisiplinan',
                'subtitle' => 'Alur pencatatan pelanggaran, penetapan tindakan, persetujuan berjenjang, surat resmi, dan penyelesaian kasus siswa',
                'icon'     => 'bi-shield-check',
                'color'    => 'rose',
                'actors'   => 'Guru BK, Wali Kelas, Kaprog, Waka Kesiswaan, Kepala Sekolah, Siswa',
                'patterns' => [
                    'bk.*',
                    'admin.jenis-pelanggaran.*',
                    'admin.jenis-rekomendasi.*',
                    'waka.progres.*',
                    'waka.dashboard',
                    'kepsek.*',
                    'kaprog.pelanggaran.*',
                    'kaprog.progres.*',
                    'walikelas.pelanggaran.*',
                    'walikelas.progres.*',
                    'siswa.pelanggaran.*',
                ],
                'steps' => [
                    [
                        'actor' => 'BK / Admin',
                        'title' => '1. Data Pelanggaran & Poin',
                        'desc'  => 'Tetapkan jenis pelanggaran & bobot poin di Data Pelanggaran. Setiap siswa mulai dengan 100 poin kedisiplinan.',
                    ],
                    [
                        'actor' => 'Guru BK',
                        'title' => '2. Input Pelanggaran & Pilih Tindakan',
                        'desc'  => 'Catat kejadian di Catatan Pelanggaran (poin siswa otomatis berkurang) lalu pilih Jenis Tindakan. Untuk data lama tanpa tindakan, klik "+ Mulai Progres" di Detail Pelanggaran.',
                    ],
                    [
                        'actor' => 'Wali Kelas / Kaprog / Waka / Kepsek',
                        'title' => '3. Persetujuan Berjenjang (Approval)',
                        'desc'  => 'Peringatan Lisan langsung selesai. Teguran/Home Visit langsung ke laporan Wali Kelas. SP-1 disetujui Wali Kelas & Kaprog; SP-2 ditambah Waka Kesiswaan; SP-3 ditambah Kepala Sekolah.',
                    ],
                    [
                        'actor' => 'Guru BK',
                        'title' => '4. Cetak Surat Resmi',
                        'desc'  => 'Setelah disetujui (atau untuk Pemanggilan Ortu), buka Detail Progres lalu cetak Surat Panggilan / SP-1 / SP-2 / SP-3 sebagai bukti administrasi.',
                    ],
                    [
                        'actor' => 'Semua Pihak Terkait',
                        'title' => '5. Isi Laporan Tindak Lanjut',
                        'desc'  => 'Wali Kelas, Kaprog, Waka Kesiswaan, Kepsek (untuk SP-3), dan Guru BK mengisikan laporan hasil pembinaan & unggah dokumen pendukung di halaman Progres.',
                    ],
                    [
                        'actor' => 'Guru BK',
                        'title' => '6. Tutup Kasus & Laporan Berkala',
                        'desc'  => 'Saat semua syarat selesai, Guru BK klik "Selesaikan Kasus". Rekap kasus otomatis masuk ke Laporan Berkala BK (Mingguan, Bulanan, Pra-Ujian, Semester, Akhir Tahun).',
                    ],
                ],
                'result' => 'Setiap kasus pelanggaran memiliki jejak persetujuan, surat resmi, laporan pembinaan lintas peran, status tuntas (Selesai), dan laporan berkala siap cetak untuk pimpinan.',
            ],
            [
                'id'       => 'kalender-program-kerja',
                'title'    => 'Program Kerja Tugas Tambahan & Kalender Akademik',
                'subtitle' => 'Perencanaan kegiatan seluruh tugas tambahan yang otomatis tersinkron ke Kalender Akademik sekolah hingga laporan realisasinya',
                'icon'     => 'bi-calendar-range',
                'color'    => 'indigo',
                'actors'   => 'Waka Kurikulum, Hubin, Kesiswaan, Sarpras, Pembina OSIS/Eskul, Kaprog, Wali Kelas, Kepsek',
                'patterns' => [
                    'guru.penugasan.*',
                    'guru.program-kerja-tambahan.*',
                    'guru.realisasi-tugas-tambahan.*',
                    'guru.kegiatan.*',
                    'guru.kalender.*',
                    'akademik.kalender.*',
                ],
                'steps' => [
                    [
                        'actor' => 'Admin / Kepsek',
                        'title' => '1. Penugasan & Kalender Dasar',
                        'desc'  => 'Tetapkan tahun ajaran, libur nasional, dan agenda pokok pada Kalender Akademik serta tugaskan jabatan tambahan ke guru/tendik.',
                    ],
                    [
                        'actor' => 'Pemegang Tugas Tambahan',
                        'title' => '2. Input Rencana & Tanggal Kegiatan',
                        'desc'  => 'Isi Rencana Program Kerja Tugas Tambahan (Hubin, Kurikulum, Kesiswaan, Sarpras, OSIS, dll) beserta Tanggal Mulai & Selesai.',
                    ],
                    [
                        'actor' => 'Sistem HilalEdu',
                        'title' => '3. Sinkron Otomatis ke Kalender Akademik',
                        'desc'  => 'Agenda ber-tanggal otomatis tampil di Kalender Akademik dengan penanda warna bidangnya agar seluruh warga sekolah mengetahui jadwalnya.',
                    ],
                    [
                        'actor' => 'Pemegang Tugas & Kepsek',
                        'title' => '4. Pelaksanaan, Presensi & Realisasi',
                        'desc'  => 'Saat kegiatan berjalan, catat Kegiatan Sekolah/Presensi lalu laporkan hasil capaian serta kesesuaian program di menu Laporan Realisasi Program.',
                    ],
                ],
                'result' => 'Seluruh agenda kerja pimpinan & unit terbaca rapi dalam satu Kalender Akademik terpadu beserta bukti realisasi dan evaluasi ketercapaian program.',
            ],
            [
                'id'       => 'kbm-kurikulum',
                'title'    => 'Kurikulum, Jadwal, Perangkat Ajar, KBM & Nilai',
                'subtitle' => 'Siklus pembelajaran lengkap dari pemetaan kurikulum hingga leger nilai akhir kelas & jurusan',
                'icon'     => 'bi-journal-bookmark-fill',
                'color'    => 'blue',
                'actors'   => 'Waka Kurikulum, Admin Akademik, Guru Mapel, Petugas Piket, Wali Kelas, Kaprog, Siswa',
                'patterns' => [
                    'akademik.dashboard',
                    'akademik.kurikulum.*',
                    'akademik.jadwal.*',
                    'akademik.laporan.*',
                    'akademik.nilai.*',
                    'guru.perangkat-ajar.*',
                    'guru.minggu-efektif.*',
                    'guru.rencana-pembelajaran.*',
                    'guru.laporan-kbm.*',
                    'guru.nilai.*',
                    'guru.absensi.*',
                    'guru.rekap-presensi.*',
                    'rekap-kehadiran.*',
                    'presensi-harian.*',
                    'kaprog.jadwal.*',
                    'kaprog.leger*',
                    'walikelas.jadwal*',
                    'walikelas.leger*',
                    'siswa.jadwal*',
                ],
                'steps' => [
                    [
                        'actor' => 'Kurikulum',
                        'title' => '1. Plotting Kurikulum & Jadwal',
                        'desc'  => 'Atur mata pelajaran per kelas, guru pengampu, dan susun Matriks Jadwal Pelajaran serta cetak SK Mengajar.',
                    ],
                    [
                        'actor' => 'Guru Mapel',
                        'title' => '2. Perangkat Ajar & Minggu Efektif',
                        'desc'  => 'Susun Perangkat Ajar (ATP/Modul), hitung Minggu Efektif berdasarkan Kalender Akademik, dan buat Rencana Pembelajaran.',
                    ],
                    [
                        'actor' => 'Guru & Piket',
                        'title' => '3. Pelaksanaan KBM & Presensi',
                        'desc'  => 'Guru mengisi Laporan KBM + presensi siswa per jam pelajaran; siswa/guru dicatat kehadirannya di Monitoring Absensi.',
                    ],
                    [
                        'actor' => 'Guru & Wali Kelas',
                        'title' => '4. Input Nilai & Leger',
                        'desc'  => 'Guru menginput nilai mapel; Wali Kelas & Kaprog memantau rekap nilai terpadu di Leger Nilai Kelas/Jurusan siap cetak.',
                    ],
                ],
                'result' => 'Rekap kehadiran guru/siswa, keterlaksanaan jam mengajar, dan Leger Nilai akhir semester tersaji akurat tanpa rekap manual ulang.',
            ],
            [
                'id'       => 'prakerin-hubin',
                'title'    => 'Praktik Kerja Industri (Prakerin / PKL Hubin)',
                'subtitle' => 'Alur kemitraan DUDI, penempatan peserta PKL, pengisian jurnal harian siswa, dan penilaian industri',
                'icon'     => 'bi-briefcase-fill',
                'color'    => 'amber',
                'actors'   => 'Waka Hubin, Admin Prakerin, Guru Pembimbing, Siswa, DU/DI',
                'patterns' => ['prakerin.*', 'kerjasama.*'],
                'steps' => [
                    [
                        'actor' => 'Hubin / Admin PKL',
                        'title' => '1. Kerja Sama & Data DU/DI',
                        'desc'  => 'Catat MoU Kerja Sama, profil Data DU/DI mitra, dan daftarkan Pembimbing DU/DI maupun Guru Pembimbing.',
                    ],
                    [
                        'actor' => 'Admin PKL / Hubin',
                        'title' => '2. Periode & Penempatan Siswa',
                        'desc'  => 'Buat gelombang Periode PKL lalu plot penempatan siswa ke masing-masing DU/DI beserta pembimbingnya.',
                    ],
                    [
                        'actor' => 'Siswa & Pembimbing',
                        'title' => '3. Jurnal Harian Prakerin',
                        'desc'  => 'Siswa mengisi Jurnal Harian kegiatan selama PKL; pembimbing memantau dan memvalidasi jurnal siswa.',
                    ],
                    [
                        'actor' => 'Hubin / Pembimbing',
                        'title' => '4. Evaluasi & Laporan PKL',
                        'desc'  => 'Susun laporan akhir pelaksanaan dan nilai Prakerin untuk arsip sertifikat dan kelengkapan nilai siswa.',
                    ],
                ],
                'result' => 'Penempatan PKL tertib, aktivitas harian siswa di industri terpantau, dan laporan Prakerin siap untuk penerbitan sertifikat PKL.',
            ],
            [
                'id'       => 'ekstrakurikuler',
                'title'    => 'Ekstrakurikuler (Kesiswaan & Pembina)',
                'subtitle' => 'Pengelolaan unit ekstrakurikuler, keanggotaan siswa, ketua eskul, dan absensi kegiatan mingguan',
                'icon'     => 'bi-award-fill',
                'color'    => 'emerald',
                'actors'   => 'Waka Kesiswaan, Pembina OSIS, Pembina Eskul, Ketua Eskul, Siswa, Wali Kelas',
                'patterns' => [
                    'admin.ekstrakurikuler.*',
                    'guru.ekstrakurikuler.*',
                    'waka.ekstrakurikuler.*',
                    'siswa.ekstrakurikuler.*',
                    'walikelas.eskul*',
                    'walikelas.ekstrakurikuler.*',
                ],
                'steps' => [
                    [
                        'actor' => 'Kesiswaan / Admin',
                        'title' => '1. Data Master & Pembina Eskul',
                        'desc'  => 'Buat daftar ekstrakurikuler, jadwal hari latihan, dan tetapkan Guru/Tendik Pembinanya.',
                    ],
                    [
                        'actor' => 'Kesiswaan / Siswa',
                        'title' => '2. Plotting Peserta & Ketua Eskul',
                        'desc'  => 'Siswa mendaftar atau diinput oleh Kesiswaan/Pembina, lalu ditetapkan Ketua Eskul per unit.',
                    ],
                    [
                        'actor' => 'Siswa / Ketua / Pembina',
                        'title' => '3. Presensi & Kegiatan Mingguan',
                        'desc'  => 'Pelaksanaan latihan dicatat melalui absensi mandiri siswa, ketua eskul, dan validasi Pembina.',
                    ],
                    [
                        'actor' => 'Kesiswaan & Wali Kelas',
                        'title' => '4. Monitoring Mingguan & Rekap Kelas',
                        'desc'  => 'Kesiswaan memantau keaktifan mingguan dan Wali Kelas melihat rekap keikutsertaan eskul untuk nilai rapor.',
                    ],
                ],
                'result' => 'Data keaktifan ekstrakurikuler tiap siswa terekam rapi per minggu dan siap dicetak sebagai laporan kesiswaan maupun nilai eskul rapor.',
            ],
            [
                'id'       => 'dokumen-sertifikat',
                'title'    => 'Dokumen & Sertifikat Siswa (Kepesantrenan, Kewirausahaan, SKL, Ijazah)',
                'subtitle' => 'Pengelolaan template, upload atau generate sertifikat massal, pengaturan akses siswa, hingga unduhan portal siswa',
                'icon'     => 'bi-patch-check-fill',
                'color'    => 'purple',
                'actors'   => 'Superadmin, Admin Dokumen/Sertifikat, Siswa',
                'patterns' => ['dokumen-sertifikat.*', 'siswa.dokumen-sertifikat.*'],
                'steps' => [
                    [
                        'actor' => 'Admin Dokumen',
                        'title' => '1. Jenis Sertifikat & Desain / File',
                        'desc'  => 'Tambahkan jenis sertifikat atau siapkan file PDF/gambar yang akan diterbitkan per kategori.',
                    ],
                    [
                        'actor' => 'Admin Dokumen',
                        'title' => '2. Pilih Siswa & Pratinjau',
                        'desc'  => 'Pilih siswa penerima dari database, lengkapi penandatangan (otomatis atau manual), cek Preview Hasil sebelum generate.',
                    ],
                    [
                        'actor' => 'Admin Dokumen',
                        'title' => '3. Generate Massal / Upload & Kontrol Akses',
                        'desc'  => 'Terbitkan sertifikat atau unggah SKL/Ijazah dan atur hak akses buka/tutup unduhan per siswa (misal syarat administrasi).',
                    ],
                    [
                        'actor' => 'Siswa',
                        'title' => '4. Unduh & Cetak di Portal Siswa',
                        'desc'  => 'Siswa yang dibuka aksesnya dapat melihat pratinjau dan mengunduh dokumen/sertifikat resminya langsung dari akun sendiri.',
                    ],
                ],
                'result' => 'Sertifikat, SKL, dan Ijazah siswa terdokumentasi digital dengan nomor teratur dan dapat diakses aman oleh siswa yang memenuhi syarat.',
            ],
            [
                'id'       => 'keuangan-koperasi',
                'title'    => 'Keuangan Sekolah & Koperasi',
                'subtitle' => 'Pengaturan tarif tagihan, pencatatan pembayaran siswa, monitoring tunggakan, dan transaksi koperasi warga sekolah',
                'icon'     => 'bi-cash-coin',
                'color'    => 'emerald',
                'actors'   => 'Admin Keuangan, Admin Koperasi, Wali Kelas, Siswa',
                'patterns' => ['keuangan.*', 'walikelas.tagihan.*', 'siswa.tagihan.*', 'koperasi.*'],
                'steps' => [
                    [
                        'actor' => 'Admin Keuangan / Koperasi',
                        'title' => '1. Master Jenis Tagihan / Anggota',
                        'desc'  => 'Buat master tagihan SPP/kegiatan atau daftarkan anggota koperasi sekolah.',
                    ],
                    [
                        'actor' => 'Admin Unit',
                        'title' => '2. Generate Tagihan & Catat Transaksi',
                        'desc'  => 'Terbitkan tagihan per kelas/siswa dan input setiap pembayaran cicilan/lunas atau transaksi simpan-pinjam koperasi.',
                    ],
                    [
                        'actor' => 'Wali Kelas & Siswa',
                        'title' => '3. Pantau Status Tagihan',
                        'desc'  => 'Siswa mengecek tagihan & riwayat bayar; Wali Kelas memantau rekap tunggakan kelas bimbingannya.',
                    ],
                    [
                        'actor' => 'Admin & Pimpinan',
                        'title' => '4. Laporan Realisasi Kas',
                        'desc'  => 'Rekap pemasukan, tunggakan, dan saldo koperasi tersedia otomatis untuk pertanggungjawaban.',
                    ],
                ],
                'result' => 'Transparansi tagihan dan pembayaran siswa terjaga, piutang terpantau wali kelas, dan kas koperasi tercatat rapi.',
            ],
            [
                'id'       => 'payroll-sdm',
                'title'    => 'Payroll / Bisyarah & Administrasi Kepegawaian',
                'subtitle' => 'Pengolahan honor jam mengajar, transport kehadiran, tunjangan tugas tambahan, pengajuan cuti, dan slip gaji',
                'icon'     => 'bi-wallet2',
                'color'    => 'sky',
                'actors'   => 'Admin Payroll, Superadmin, Guru, Tendik, Kepala Sekolah',
                'patterns' => ['superadmin.payroll.*', 'guru.payroll.*', 'tendik.payroll.*', 'guru.cuti.*'],
                'steps' => [
                    [
                        'actor' => 'Admin Payroll',
                        'title' => '1. Setting Tarif & Komponen Gaji',
                        'desc'  => 'Atur besaran honor per JP, transport harian, serta nominal tunjangan per Tugas Tambahan.',
                    ],
                    [
                        'actor' => 'Guru / Tendik / Piket',
                        'title' => '2. Rekam Kehadiran, KBM & Cuti',
                        'desc'  => 'Kehadiran pegawai, laporan KBM, serta pengajuan cuti menjadi dasar perhitungan hari kerja & jam mengajar.',
                    ],
                    [
                        'actor' => 'Admin Payroll',
                        'title' => '3. Generate Periode & Verifikasi Slip',
                        'desc'  => 'Buat periode penggajian bulanan, periksa komponen potongan/tunjangan, lalu aktifkan "Tampil ke Guru".',
                    ],
                    [
                        'actor' => 'Guru & Tendik',
                        'title' => '4. Akses & Cetak Slip Bisyarah',
                        'desc'  => 'Pegawai melihat rincian penerimaan bersih dan mencetak slip gaji pribadi dari akun masing-masing.',
                    ],
                ],
                'result' => 'Perhitungan gaji dan bisyarah berlangsung objektif berbasis data kehadiran & penugasan, serta slip tersedia mandiri.',
            ],
            [
                'id'       => 'data-master-sdm',
                'title'    => 'Data Master, SDM, Hak Akses & Tracer Alumni',
                'subtitle' => 'Fondasi struktur sekolah, pengelolaan akun pengguna, pratinjau menu Superadmin, pemetaan domisili, dan pelacakan lulusan',
                'icon'     => 'bi-building-gear',
                'color'    => 'slate',
                'actors'   => 'Superadmin, Admin Kesiswaan/Akademik, Alumni',
                'patterns' => [
                    'superadmin.master.*',
                    'superadmin.rombel.*',
                    'superadmin.kenaikan-kelas.*',
                    'admin.mapel.*',
                    'superadmin.guru.*',
                    'superadmin.tendik.*',
                    'superadmin.siswa.*',
                    'superadmin.users.*',
                    'superadmin.penugasan-admin.*',
                    'superadmin.credentials.*',
                    'superadmin.database.*',
                    'superadmin.log-aktivitas.*',
                    'domisili.*',
                    'tracer.*',
                    'akademik.piket.*',
                    'akademik.keluhan.*',
                    'guru.keluhan.*',
                    'akademik.pengaturan.*',
                ],
                'steps' => [
                    [
                        'actor' => 'Superadmin',
                        'title' => '1. Fondasi Data Master Pokok',
                        'desc'  => 'Siapkan Tahun Ajaran aktif, Jurusan, Kelas, Rombel, Mata Pelajaran, dan Master Tugas Tambahan.',
                    ],
                    [
                        'actor' => 'Superadmin',
                        'title' => '2. SDM, Siswa & Penugasan Admin Unit',
                        'desc'  => 'Kelola data Guru, Tendik, Siswa (termasuk domisili), lalu berikan tugas tambahan dan role Admin Unit sesuai tanggung jawab.',
                    ],
                    [
                        'actor' => 'Superadmin',
                        'title' => '3. Audit Menu via Lihat Sebagai User',
                        'desc'  => 'Gunakan fitur "Lihat Sebagai User" di topbar atau Manajemen Akun untuk memantau menu setiap peran dan memastikan nol error.',
                    ],
                    [
                        'actor' => 'Admin & Alumni',
                        'title' => '4. Kenaikan Kelas, Kelulusan & Tracer Study',
                        'desc'  => 'Di akhir tahun, naikkan kelas atau luluskan siswa kelas XII sehingga otomatis tercatat di data Alumni untuk pengisian Tracer Study.',
                    ],
                ],
                'result' => 'Struktur data sekolah selalu mutakhir dari siswa baru masuk hingga menjadi alumni yang terlacak jejak kariernya.',
            ],
        ];
    }

    public static function overallSystemFlow(): array
    {
        return [
            [
                'phase' => 'Fase 1 · Persiapan & Fondasi Data',
                'badge' => 'Awal Tahun Ajaran',
                'color' => 'emerald',
                'items' => [
                    'Superadmin menetapkan Tahun Ajaran aktif, Jurusan, Kelas, Rombel, Mata Pelajaran, serta Pengaturan Sekolah.',
                    'Data Guru, Tendik, dan Siswa dilengkapi (termasuk domisili wilayah) beserta plotting Tugas Tambahan & Admin Unit.',
                ],
                'output' => 'Struktur organisasi & database warga sekolah siap digunakan semua modul.',
            ],
            [
                'phase' => 'Fase 2 · Perencanaan & Kalender Sekolah',
                'badge' => 'Sebelum KBM Efektif',
                'color' => 'blue',
                'items' => [
                    'Kurikulum menyusun Plotting Mapel & Matriks Jadwal Pelajaran; Guru menyusun Perangkat Ajar, Minggu Efektif, dan RPP.',
                    'Pemegang Tugas Tambahan (Hubin, Kesiswaan, Kurikulum, Sarpras, OSIS, Wali Kelas) mengisi Rencana Program Kerja & tanggalnya -> otomatis masuk Kalender Akademik.',
                ],
                'output' => 'Kalender Akademik, Jadwal Mengajar, dan Rencana Kerja sekolah terpadu dalam satu acuan.',
            ],
            [
                'phase' => 'Fase 3 · Operasional Harian, KBM & Pembinaan',
                'badge' => 'Berjalan Harian / Mingguan',
                'color' => 'indigo',
                'items' => [
                    'KBM & Piket: Guru mengisi Laporan KBM + presensi siswa; Piket memantau kehadiran guru & siswa serta keluhan KBM.',
                    'Monitoring BK: Guru BK mencatat pelanggaran -> penetapan Tindakan -> persetujuan berjenjang (Wali Kelas, Kaprog, Waka Kesiswaan, Kepsek) -> cetak surat -> laporan -> selesai.',
                    'Kesiswaan, Hubin & Keuangan: Pembinaan Ekstrakurikuler mingguan, Penempatan & Jurnal PKL, serta pencatatan Tagihan/Pembayaran & Koperasi.',
                ],
                'output' => 'Seluruh aktivitas belajar, disiplin siswa, PKL, eskul, dan keuangan tercatat real-time & memicu notifikasi otomatis.',
            ],
            [
                'phase' => 'Fase 4 · Evaluasi, Pelaporan & Kesejahteraan',
                'badge' => 'Bulanan & Akhir Semester',
                'color' => 'amber',
                'items' => [
                    'Guru menginput Nilai Mapel -> terhimpun otomatis pada Leger Nilai Kelas & Jurusan.',
                    'Realisasi Program Kerja Tugas Tambahan & Kegiatan Sekolah dievaluasi kesesuaiannya oleh Kepala Sekolah.',
                    'Admin Payroll menarik data kehadiran, JP mengajar, dan tugas tambahan untuk menerbitkan Slip Gaji / Bisyarah.',
                ],
                'output' => 'Leger Nilai, Laporan BK Berkala, Realisasi Kinerja, dan Slip Gaji pegawai terverifikasi.',
            ],
            [
                'phase' => 'Fase 5 · Kenaikan Kelas, Sertifikasi, Kelulusan & Alumni',
                'badge' => 'Akhir Tahun & Hasil Akhir Sistem',
                'color' => 'purple',
                'items' => [
                    'Admin menerbitkan Sertifikat (Kepesantrenan, Kewirausahaan, dll), SKL, dan Ijazah digital untuk diunduh siswa.',
                    'Migrasi siswa (Naik Kelas atau Lulus) memindahkan lulusan otomatis ke database Tracer Alumni untuk pemantauan serapan kerja/kuliah/wirausaha.',
                ],
                'output' => 'Ekosistem sekolah tuntas dari hulu ke hilir: siswa terlayani hingga lulus & terlacak sebagai alumni, guru/pegawai tertib administrasi & bisyarah, pimpinan memperoleh laporan menyeluruh.',
            ],
        ];
    }
}
