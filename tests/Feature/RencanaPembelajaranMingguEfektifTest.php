<?php

namespace Tests\Feature;

use App\Models\JadwalPelajaran;
use App\Models\KalenderAkademik;
use App\Models\KalenderAkademikEvent;
use App\Models\MataPelajaran;
use App\Models\RencanaPembelajaran;
use App\Models\User;
use App\Services\MingguEfektifService;
use App\Services\RekapKehadiranService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RencanaPembelajaranMingguEfektifTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_can_view_create_rpp_with_auto_generated_effective_dates()
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'name' => 'Guru Kejuruan TKJ',
        ]);

        $mapel = MataPelajaran::create([
            'nama' => 'Jaringan Komputer',
            'kode' => 'JK-01',
        ]);

        $kalender = KalenderAkademik::create([
            'nama_kalender'   => 'Kalender Akademik 2026/2027',
            'tahun_ajaran'    => '2026/2027',
            'tanggal_mulai'   => '2026-07-01',
            'tanggal_selesai' => '2027-06-30',
            'tanggal_mulai_smt1'   => '2026-07-01',
            'tanggal_selesai_smt1' => '2026-12-31',
            'tanggal_mulai_smt2'   => '2027-01-01',
            'tanggal_selesai_smt2' => '2027-06-30',
            'deskripsi'       => 'Kalender Akademik 2026/2027',
            'is_aktif'        => true,
        ]);

        // Buat jadwal mengajar di hari Senin
        $jadwal = JadwalPelajaran::create([
            'guru_user_id' => $guru->id,
            'mata_pelajaran_id' => $mapel->id,
            'kelas' => 'X TKJ 1',
            'hari' => 'Senin',
            'jam_ke_mulai' => 1,
            'jam_ke_selesai' => 4,
            'jam_mulai' => '07:15:00',
            'jam_selesai' => '10:15:00',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'ganjil',
        ]);

        $response = $this->actingAs($guru)->get(route('guru.rencana-pembelajaran.rpp.create'));
        $response->assertStatus(200);
        $response->assertSee('Susun Modul Ajar Harian (RPP)');
        $response->assertSee('Tanggal Pelaksanaan (Minggu Efektif)');
        $response->assertSee('Jaringan Komputer');
        $response->assertSee('X TKJ 1');

        // Test store RPP
        $postRes = $this->actingAs($guru)->post(route('guru.rencana-pembelajaran.rpp.store'), [
            'jadwal_pelajaran_id' => $jadwal->id,
            'tanggal_rencana' => '2026-07-20',
            'pertemuan_ke' => 1,
            'materi_pokok' => 'Pengenalan Topologi Jaringan & Subnetting',
            'aktivitas_pendahuluan' => 'Doa dan presensi kehadiran siswa.',
            'aktivitas_inti' => 'Mempraktikkan crimping kabel UTP dan topologi star.',
            'aktivitas_penutup' => 'Refleksi materi dan doa penutup.',
            'bentuk_asesmen' => 'Formatif Praktik',
        ]);

        $postRes->assertRedirect();
        $this->assertDatabaseHas('rencana_pembelajarans', [
            'guru_user_id' => $guru->id,
            'jadwal_pelajaran_id' => $jadwal->id,
            'tanggal_rencana' => '2026-07-20',
            'pertemuan_ke' => 1,
            'materi_pokok' => 'Pengenalan Topologi Jaringan & Subnetting',
        ]);
    }

    public function test_rekap_mengajar_guru_calculates_hari_kerja_from_effective_teaching_days()
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'name' => 'Guru Mapel Efektif',
        ]);

        $mapel = MataPelajaran::create([
            'nama' => 'Pemrograman Web',
            'kode' => 'PW-01',
        ]);

        $kalender = KalenderAkademik::create([
            'nama_kalender'   => 'Kalender Akademik 2026/2027',
            'tahun_ajaran'    => '2026/2027',
            'tanggal_mulai'   => '2026-07-01',
            'tanggal_selesai' => '2027-06-30',
            'tanggal_mulai_smt1'   => '2026-07-01',
            'tanggal_selesai_smt1' => '2026-12-31',
            'tanggal_mulai_smt2'   => '2027-01-01',
            'tanggal_selesai_smt2' => '2027-06-30',
            'deskripsi'       => 'Kalender Akademik 2026/2027',
            'is_aktif'        => true,
        ]);

        // Jadwal hari Senin di bulan Juli 2026
        // Juli 2026: Senin jatuh pada tanggal 6, 13, 20, 27 (4 hari)
        $jadwal = JadwalPelajaran::create([
            'guru_user_id' => $guru->id,
            'mata_pelajaran_id' => $mapel->id,
            'kelas' => 'XI RPL 1',
            'hari' => 'Senin',
            'jam_ke_mulai' => 1,
            'jam_ke_selesai' => 4,
            'jam_mulai' => '07:15:00',
            'jam_selesai' => '10:15:00',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'ganjil',
        ]);

        // Tambah event libur pada tanggal 13 Juli 2026
        KalenderAkademikEvent::create([
            'kalender_akademik_id' => $kalender->id,
            'judul_kegiatan' => 'Libur Awal Semester',
            'tanggal_mulai' => '2026-07-13',
            'tanggal_selesai' => '2026-07-13',
            'is_libur' => true,
            'kategori' => 'libur_sekolah',
            'semester' => '1',
        ]);

        $service = app(RekapKehadiranService::class);
        $periode = $service->resolvePeriode('bulan', '2026-07', '2026/2027', '1');

        $rekap = $service->rekapMengajar($periode, collect([$guru]), true);

        // Dari 4 hari Senin di Juli 2026, 1 tanggal (13 Juli) libur.
        // Maka Jumlah Hari Kerja Mengajar yang terhitung dari minggu efektif adalah 3 hari!
        $this->assertEquals(1, $rekap['rows']->count());
        $row = $rekap['rows']->first();

        $this->assertEquals(3, $row['hari_kerja'], 'Jumlah hari kerja mengajar harus sesuai hari efektif mengajar pada bulan tersebut (4 Senin - 1 Libur = 3)');
    }
}
