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
}

