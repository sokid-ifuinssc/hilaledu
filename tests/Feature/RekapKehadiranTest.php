<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekapKehadiranTest extends TestCase
{
    use RefreshDatabase;
    public function test_superadmin_can_access_rekap_pegawai_and_mengajar()
    {
        $superadmin = User::where('role', 'superadmin')->first();
        if (!$superadmin) {
            $superadmin = User::factory()->create([
                'role' => 'superadmin',
                'name' => 'Super Administrator',
            ]);
        }

        $res = $this->actingAs($superadmin)->get(route('rekap-kehadiran.pegawai'));
        $res->assertStatus(200);
        $res->assertSee('Daftar Hadir Pegawai');
        $res->assertSee('Persentase Kehadiran');

        $resMengajar = $this->actingAs($superadmin)->get(route('rekap-kehadiran.mengajar'));
        $resMengajar->assertStatus(200);
        $resMengajar->assertSee('Rekap Kehadiran Mengajar Guru');
        $resMengajar->assertSee('Jumlah Jam Mengajar / Minggu');

        // Test filter semester & tahun
        $resFilter = $this->actingAs($superadmin)->get(route('rekap-kehadiran.pegawai', [
            'mode' => 'semester',
            'semester' => '1',
            'tahun_ajaran' => '2026/2027',
        ]));
        $resFilter->assertStatus(200);

        // Test print pegawai & mengajar
        $resPrintPegawai = $this->actingAs($superadmin)->get(route('rekap-kehadiran.pegawai.print'));
        $resPrintPegawai->assertStatus(200);
        $resPrintPegawai->assertSee('DAFTAR HADIR PEGAWAI');

        $resPrintMengajar = $this->actingAs($superadmin)->get(route('rekap-kehadiran.mengajar.print'));
        $resPrintMengajar->assertStatus(200);
        $resPrintMengajar->assertSee('REKAP KEHADIRAN MENGAJAR GURU');
    }

    public function test_guru_can_access_rekap_saya()
    {
        $guru = User::where('role', 'guru')->first();
        if (!$guru) {
            $guru = User::factory()->create([
                'role' => 'guru',
                'name' => 'Guru Pengajar',
            ]);
        }

        $res = $this->actingAs($guru)->get(route('rekap-kehadiran.saya'));
        $res->assertStatus(200);
        $res->assertSee('Rekap Kehadiran Saya');
        $res->assertSee($guru->name);

        $resPrint = $this->actingAs($guru)->get(route('rekap-kehadiran.saya.print', ['jenis' => 'mengajar']));
        $resPrint->assertStatus(200);
        $resPrint->assertSee('REKAP KEHADIRAN MENGAJAR GURU');
    }

    public function test_bendahara_can_access_rekap_pegawai_and_mengajar()
    {
        $bendahara = User::where('role', 'guru')
            ->where(function ($q) {
                $q->where('tugas_tambahan', 'like', '%Bendahara%')
                  ->orWhere('jabatan_utama', 'like', '%Bendahara%');
            })->first();

        if (!$bendahara) {
            $bendahara = User::where('admin_role', 'payroll')
                ->orWhere('admin_role', 'keuangan')
                ->first();
        }

        if (!$bendahara) {
            $bendahara = User::factory()->create([
                'role' => 'tendik',
                'name' => 'Bendahara Sekolah',
                'tugas_tambahan' => ['Bendahara Sekolah'],
            ]);
        }

        $res = $this->actingAs($bendahara)->get(route('rekap-kehadiran.pegawai'));
        $res->assertStatus(200);

        $resMengajar = $this->actingAs($bendahara)->get(route('rekap-kehadiran.mengajar'));
        $resMengajar->assertStatus(200);
    }

    public function test_rekap_pegawai_calculates_total_active_working_days_in_month()
    {
        $tendik = User::factory()->create([
            'role' => 'tendik',
            'name' => 'Staf Tata Usaha',
        ]);

        $service = app(\App\Services\RekapKehadiranService::class);
        $periode = $service->resolvePeriode('bulan', '2026-07', '2026/2027', '1');

        $rekap = $service->rekapPegawai($periode, collect([$tendik]), true);
        $this->assertEquals(1, $rekap['rows']->count());

        $row = $rekap['rows']->first();
        // Pada Juli 2026 (31 hari) dengan 6 hari kerja/pekan (Senin..Sabtu = 27 hari kerja jika tanpa libur)
        // Hari kerja aktif dihitung untuk 1 bulan penuh
        $this->assertGreaterThan(20, $row['hari_kerja']);
        $this->assertEquals($rekap['hari_kerja'], $row['hari_kerja']);
    }

    public function test_guru_yang_sudah_absen_hari_ini_langsung_muncul_di_rekap_meskipun_sebelum_jam_15()
    {
        // Simulasikan waktu jam 08:30 pagi
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::today()->setTime(8, 30));

        $guru = User::factory()->create([
            'role' => 'guru',
            'name' => 'Guru Hadir Hari Ini',
            'is_active' => true,
        ]);

        // Guru absen masuk hari ini
        \App\Models\PresensiHarianGuru::create([
            'guru_user_id' => $guru->id,
            'tanggal'      => date('Y-m-d'),
            'jam_masuk'    => '07:15:00',
            'status_masuk' => 'hadir',
        ]);

        $service = app(\App\Services\RekapKehadiranService::class);
        $periode = $service->resolvePeriode('bulan', date('Y-m'), null, null);

        $rekap = $service->rekapPegawai($periode, collect([$guru]), true);
        $row = $rekap['rows']->first();

        // Hari ini harus langsung terhitung hadir dan detailnya tercatat
        $this->assertGreaterThanOrEqual(1, $row['hadir']);
        $detailHariIni = collect($row['detail'])->firstWhere('tanggal', date('Y-m-d'));
        $this->assertNotNull($detailHariIni);
        $this->assertEquals('hadir', $detailHariIni['status']);
        $this->assertEquals('07:15', $detailHariIni['jam_masuk']);

        \Carbon\Carbon::setTestNow(); // Reset mock time
    }

    public function test_guru_rekap_kehadiran_saya_menampilkan_jadwal_per_hari_dan_keterangan_per_jam_mapel()
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'name' => 'Guru Mengajar Test',
            'is_active' => true,
        ]);

        $mapel = \App\Models\MataPelajaran::create([
            'nama' => 'Pemrograman Web & Perangkat Bergerak',
            'kode' => 'PWPB-01',
        ]);

        // Buat jadwal mengajar pada hari ini (misal Senin)
        $hariIni = \App\Models\JadwalPelajaran::class;
        $hariName = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'][date('N')];

        $jadwal1 = \App\Models\JadwalPelajaran::create([
            'hari'              => $hariName,
            'jam_ke_mulai'      => 1,
            'jam_ke_selesai'    => 2,
            'jam_mulai'         => '07:00:00',
            'jam_selesai'       => '08:30:00',
            'kelas'             => 'XII RPL 1',
            'mata_pelajaran_id' => $mapel->id,
            'guru_user_id'      => $guru->id,
            'ruang'             => 'Lab RPL 1',
        ]);

        // Presensi harian masuk pagi
        \App\Models\PresensiHarianGuru::create([
            'guru_user_id' => $guru->id,
            'tanggal'      => date('Y-m-d'),
            'jam_masuk'    => '07:05:00',
            'status_masuk' => 'hadir',
        ]);

        // 1. Akses menu guru.absensi.index (sekarang Rekap Kehadiran Saya)
        $res = $this->actingAs($guru)->get(route('guru.absensi.index'));
        $res->assertStatus(200);
        $res->assertSee('Rekap Kehadiran Saya');
        $res->assertSee('Pemrograman Web & Perangkat Bergerak');
        $res->assertSee('Kelas XII RPL 1');
        $res->assertSee('Hadir');

        // 2. Akses print
        $resPrint = $this->actingAs($guru)->get(route('guru.absensi.print'));
        $resPrint->assertStatus(200);
        $resPrint->assertSee('REKAPITULASI KEHADIRAN MENGAJAR GURU');
        $resPrint->assertSee('Pemrograman Web & Perangkat Bergerak');

        // 3. Akses route lama guru.rekap-presensi.index ter-redirect
        $resRedirect = $this->actingAs($guru)->get(route('guru.rekap-presensi.index'));
        $resRedirect->assertRedirect(route('guru.absensi.index'));
    }

    public function test_cetak_sk_mengajar_dan_tugas_tambahan_menampilkan_nama_kepala_sekolah()
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'name' => 'Guru Penugasan Test',
            'is_active' => true,
            'tugas_tambahan' => ['Kepala Perpustakaan'],
        ]);

        // 1. Cetak SK Jam Mengajar
        $resSkMengajar = $this->actingAs($guru)->get(route('guru.penugasan.sk_mengajar.print'));
        $resSkMengajar->assertStatus(200);
        $resSkMengajar->assertSee('Lampiran SK Pembagian Tugas Jam Mengajar Guru');
        $resSkMengajar->assertSee('Kepala Sekolah');
        $resSkMengajar->assertDontSee('.....................................');

        // 2. Cetak SK Tugas Tambahan
        $resSkTambahan = $this->actingAs($guru)->get(route('guru.penugasan.sk_tugas_tambahan.print'));
        $resSkTambahan->assertStatus(200);
        $resSkTambahan->assertSee('Lampiran SK Tugas Tambahan Guru');
        $resSkTambahan->assertSee('Kepala Sekolah');
        $resSkTambahan->assertSee('Kepala Perpustakaan');
        $resSkTambahan->assertDontSee('.....................................');
    }

    public function test_modul_minggu_efektif_dan_rpp_membaca_semester_genap_dengan_benar()
    {
        $guru = User::where('role', 'guru')->has('kurikulums')->first();
        if (!$guru) {
            $guru = User::factory()->create(['role' => 'guru', 'is_active' => true]);
        }

        // 1. Akses modul minggu efektif dengan filter semester genap
        $resGenap = $this->actingAs($guru)->get(route('guru.minggu-efektif.index', [
            'tahun_ajaran' => '2026/2027',
            'semester'     => 'genap',
        ]));
        $resGenap->assertStatus(200);
        $resGenap->assertSee('Rincian Minggu');
        $resGenap->assertSee('Semester Genap');

        // 2. Akses form buat RPP dengan semester genap
        $resRppGenap = $this->actingAs($guru)->get(route('guru.rencana-pembelajaran.rpp.create', [
            'semester' => 'genap',
        ]));
        $resRppGenap->assertStatus(200);
        $resRppGenap->assertSee('Semester Genap (Jan - Jun)');
    }

    public function test_cetak_rencana_pembelajaran_dan_rekap_kbm()
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'name' => 'Guru Cetak Test',
            'is_active' => true,
        ]);

        $mapel = \App\Models\MataPelajaran::firstOrCreate(
            ['kode' => 'TEST_CETAK_01'],
            ['nama' => 'Mata Pelajaran Uji Cetak', 'tingkat' => 'X', 'kelompok' => 'kejuruan']
        );

        $jadwal = \App\Models\JadwalPelajaran::create([
            'mata_pelajaran_id' => $mapel->id,
            'guru_user_id'      => $guru->id,
            'kelas'             => 'X TKJ 1',
            'hari'              => 'Senin',
            'jam_mulai'         => '07:30',
            'jam_selesai'       => '09:00',
            'ruang'             => 'Lab 1',
        ]);

        $cp = \App\Models\CapaianPembelajaran::create([
            'guru_user_id'      => $guru->id,
            'mata_pelajaran_id' => $mapel->id,
            'fase'              => 'E',
            'tingkat'           => 'X',
            'elemen'            => 'Elemen Jaringan Komputer',
            'deskripsi'         => 'Peserta didik mampu memahami dasar-dasar jaringan komputer.',
            'tahun_ajaran'      => '2024/2025',
            'semester'          => 'ganjil',
        ]);

        $tp = \App\Models\TujuanPembelajaran::create([
            'capaian_pembelajaran_id' => $cp->id,
            'kode_tp'                 => 'TP.1.1',
            'deskripsi'               => 'Menjelaskan konsep dasar TCP/IP.',
            'materi'                  => 'Dasar Jaringan',
            'perkiraan_jp'            => 4,
        ]);

        $atp = \App\Models\AlurTujuanPembelajaran::create([
            'guru_user_id'           => $guru->id,
            'mata_pelajaran_id'      => $mapel->id,
            'capaian_pembelajaran_id'=> $cp->id,
            'tujuan_pembelajaran_id' => $tp->id,
            'kode_atp'               => 'ATP.1.1',
            'alur_ke'                => 1,
            'fase'                   => 'E',
            'tingkat'                => 'X',
            'semester'               => 'ganjil',
            'materi_pokok'           => 'Dasar Jaringan',
            'perkiraan_jp'           => 4,
        ]);

        $rpp = \App\Models\RencanaPembelajaran::create([
            'jadwal_pelajaran_id'   => $jadwal->id,
            'guru_user_id'          => $guru->id,
            'tujuan_pembelajaran_id'=> $tp->id,
            'pertemuan_ke'          => 1,
            'tanggal_rencana'       => '2026-10-06',
            'materi_pokok'          => 'Pengenalan Jaringan dan IP Address',
            'aktivitas_pendahuluan' => 'Apersepsi dan pembagian kelompok',
            'aktivitas_inti'        => 'Praktik konfigurasi IP',
            'aktivitas_penutup'     => 'Refleksi dan kesimpulan',
        ]);

        // 1. Cetak Rencana Pembelajaran
        $resCetakRencana = $this->actingAs($guru)->get(route('guru.rencana-pembelajaran.print', [
            'mapel_id' => $mapel->id,
            'tingkat'  => 'X',
        ]));
        $resCetakRencana->assertStatus(200);
        $resCetakRencana->assertSee('PERANGKAT RENCANA PEMBELAJARAN');
        $resCetakRencana->assertSee('Mata Pelajaran Uji Cetak');
        $resCetakRencana->assertSee('Elemen Jaringan Komputer');
        $resCetakRencana->assertSee('TP.1.1');
        $resCetakRencana->assertSee('Pengenalan Jaringan dan IP Address');
        $resCetakRencana->assertSee('Muhammad Mansyur, S.Pt');

        // Buat Laporan KBM untuk pengujian cetak laporan
        $laporanKbm = \App\Models\LaporanKbm::create([
            'guru_user_id'             => $guru->id,
            'jadwal_pelajaran_id'      => $jadwal->id,
            'rencana_pembelajaran_id'  => $rpp->id,
            'tanggal_realisasi'        => '2026-10-06',
            'kesesuaian_rencana'       => 'sesuai',
            'status_pelaksanaan'       => 'sesuai_jadwal',
            'catatan_kegiatan'         => 'KBM berlangsung aktif dan lancar.',
            'jumlah_siswa_hadir'       => 20,
            'jumlah_siswa_tidak_hadir' => 0,
            'jumlah_siswa_total'       => 20,
        ]);

        // 2. Cetak Laporan KBM Satuan
        $resCetakLaporan = $this->actingAs($guru)->get(route('guru.laporan-kbm.print', $laporanKbm));
        $resCetakLaporan->assertStatus(200);
        $resCetakLaporan->assertSee('JURNAL HARIAN REALISASI KBM & PRESENSI SISWA', false);
        $resCetakLaporan->assertSee('Mata Pelajaran Uji Cetak');
        $resCetakLaporan->assertSee('Muhammad Mansyur, S.Pt');

        // 3. Cetak Rekapitulasi Jurnal KBM
        $resCetakRekap = $this->actingAs($guru)->get(route('guru.laporan-kbm.rekap.print', [
            'kelas' => 'X TKJ 1',
        ]));
        $resCetakRekap->assertStatus(200);
        $resCetakRekap->assertSee('BUKU JURNAL & REKAPITULASI PELAKSANAAN KBM', false);
        $resCetakRekap->assertSee('X TKJ 1');
        $resCetakRekap->assertSee('Muhammad Mansyur, S.Pt');
    }

    public function test_laporan_kbm_otomatis_tergenerate_per_tanggal_dan_monitoring_admin()
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'name' => 'Guru KBM Otomatis',
            'is_active' => true,
        ]);

        $admin = User::factory()->create([
            'role' => 'superadmin',
            'name' => 'Super Admin Monitoring',
            'is_active' => true,
        ]);

        $mapel = \App\Models\MataPelajaran::firstOrCreate(
            ['kode' => 'MAPEL_AUTO_01'],
            ['nama' => 'Administrasi Pajak Terpadu', 'tingkat' => 'XII', 'kelompok' => 'kejuruan']
        );

        $jadwal = \App\Models\JadwalPelajaran::create([
            'mata_pelajaran_id' => $mapel->id,
            'guru_user_id'      => $guru->id,
            'kelas'             => 'XII AKL 1',
            'hari'              => 'Senin',
            'jam_mulai'         => '07:30',
            'jam_selesai'       => '09:00',
            'jam_ke_mulai'      => 1,
            'jam_ke_selesai'    => 2,
            'ruang'             => 'Lab Akuntansi',
        ]);

        // 1. Guru membuka halaman Laporan KBM, slot tanggal otomatis tergenerate
        $resGuru = $this->actingAs($guru)->get(route('guru.laporan-kbm.index', [
            'bulan' => '2026-10',
        ]));
        $resGuru->assertStatus(200);
        $resGuru->assertSee('Laporan Realisasi KBM');
        $resGuru->assertSee('Administrasi Pajak Terpadu');
        $resGuru->assertSee('XII AKL 1');
        // Tanggal 5 Okt 2026 (Senin) muncul otomatis dengan status Belum Dilaporkan
        $resGuru->assertSee('05 Okt 2026');
        $resGuru->assertSee('Belum Dilaporkan');
        $resGuru->assertSee('Isi Laporan');

        // 2. Klik Isi Laporan otomatis mengisi jadwal & tanggal KBM
        $resCreate = $this->actingAs($guru)->get(route('guru.laporan-kbm.create', [
            'jadwal_id' => $jadwal->id,
            'tanggal'   => '2026-10-05',
        ]));
        $resCreate->assertStatus(200);
        $resCreate->assertSee('value="2026-10-05"', false);
        $resCreate->assertSee('XII AKL 1');

        // 3. Superadmin membuka halaman Laporan KBM bisa melihat semua guru & semua mapel
        $resAdmin = $this->actingAs($admin)->get(route('guru.laporan-kbm.index', [
            'bulan'   => '2026-10',
            'guru_id' => 'all',
        ]));
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('Semua Guru (Monitoring)');
        $resAdmin->assertSee('Guru KBM Otomatis');
        $resAdmin->assertSee('Administrasi Pajak Terpadu');
    }
}



