<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ProgramKerjaTugasTambahan;
use App\Models\RealisasiTugasTambahan;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RealisasiKesesuaianTugasTambahanTest extends TestCase
{
    use RefreshDatabase;

    protected User $guru;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guru = User::factory()->create([
            'username'        => 'guru_kesesuaian_' . uniqid(),
            'role'            => 'guru',
            'is_active'       => true,
            'tugas_tambahan'  => ['Wali Kelas X RPL 1', 'Wakasek Kurikulum'],
        ]);
    }

    public function test_guru_can_see_realisasi_tugas_tambahan_index_with_program_kerja(): void
    {
        $pk = ProgramKerjaTugasTambahan::create([
            'guru_user_id'           => $this->guru->id,
            'tugas_tambahan'         => 'Wakasek Kurikulum',
            'tahun_ajaran'           => '2026/2027',
            'semester'               => 'Ganjil',
            'nama_program'           => 'Rapat Koordinasi Silabus & Modul Ajar',
            'tujuan'                 => 'Penyelarasan kurikulum',
            'target_waktu'           => 'September 2026',
            'indikator_keberhasilan' => '100% silabus tersinkronisasi industri',
            'anggaran'               => 500000,
            'status'                 => 'terencana',
        ]);

        $response = $this->actingAs($this->guru)
            ->get(route('guru.realisasi-tugas-tambahan.index'));

        $response->assertStatus(200);
        $response->assertSee('Rapat Koordinasi Silabus & Modul Ajar');
        $response->assertSee('Wakasek Kurikulum');
        $response->assertSee('Belum Diisi Kesesuaiannya');
    }

    public function test_guru_can_fill_atribut_kesesuaian_for_planned_program(): void
    {
        $pk = ProgramKerjaTugasTambahan::create([
            'guru_user_id'           => $this->guru->id,
            'tugas_tambahan'         => 'Wakasek Kurikulum',
            'tahun_ajaran'           => '2026/2027',
            'semester'               => 'Ganjil',
            'nama_program'           => 'Supervisi Administrasi Pembelajaran',
            'tujuan'                 => 'Evaluasi perangkat ajar guru',
            'target_waktu'           => 'Oktober 2026',
            'indikator_keberhasilan' => 'Semua guru mengumpulkan modul ajar',
            'anggaran'               => 300000,
            'status'                 => 'terencana',
        ]);

        // 1. Cek halaman form create kesesuaian
        $createRes = $this->actingAs($this->guru)
            ->get(route('guru.realisasi-tugas-tambahan.create', ['program_id' => $pk->id]));

        $createRes->assertStatus(200);
        $createRes->assertSee('Supervisi Administrasi Pembelajaran');

        // 2. Simpan atribut kesesuaian
        $storeRes = $this->actingAs($this->guru)
            ->post(route('guru.realisasi-tugas-tambahan.store'), [
                'program_kerja_id'      => $pk->id,
                'tugas_tambahan'        => 'Wakasek Kurikulum',
                'tanggal_pelaksanaan'   => '2026-10-15',
                'status_kesesuaian'     => 'sesuai',
                'persentase_kesesuaian' => 100,
                'keterangan_kesesuaian' => 'Seluruh perangkat ajar telah diperiksa dan sesuai standar kurikulum.',
                'uraian_kegiatan'       => 'Pemeriksaan modul ajar bersama tim kurikulum.',
                'hasil_capaian'         => 'Terkumpul 25 modul ajar terverifikasi.',
                'realisasi_anggaran'    => 285000,
                'status_program'        => 'tercapai',
            ]);

        $storeRes->assertRedirect(route('guru.realisasi-tugas-tambahan.index'));

        // Pastikan atribut kesesuaian tersimpan di database
        $this->assertDatabaseHas('realisasi_tugas_tambahans', [
            'guru_user_id'          => $this->guru->id,
            'program_kerja_id'      => $pk->id,
            'status_kesesuaian'     => 'sesuai',
            'persentase_kesesuaian' => 100,
            'realisasi_anggaran'    => 285000,
        ]);

        // Status rencana program kerja terupdate
        $this->assertEquals('tercapai', $pk->fresh()->status);
    }

    public function test_guru_can_edit_atribut_kesesuaian(): void
    {
        $pk = ProgramKerjaTugasTambahan::create([
            'guru_user_id'           => $this->guru->id,
            'tugas_tambahan'         => 'Wali Kelas X RPL 1',
            'nama_program'           => 'Home Visit Siswa Indisipliner',
            'status'                 => 'sedang_berjalan',
        ]);

        $realisasi = RealisasiTugasTambahan::create([
            'guru_user_id'          => $this->guru->id,
            'program_kerja_id'      => $pk->id,
            'tugas_tambahan'        => 'Wali Kelas X RPL 1',
            'tanggal_pelaksanaan'   => '2026-10-20',
            'status_kesesuaian'     => 'sebagian_sesuai',
            'persentase_kesesuaian' => 75,
            'keterangan_kesesuaian' => 'Sebagian siswa telah dikunjungi orang tuanya.',
            'judul_kegiatan'        => 'Home Visit Siswa Indisipliner',
            'uraian_kegiatan'       => 'Kunjungan ke rumah siswa',
            'hasil_capaian'         => '2 dari 3 siswa selesai dikunjungi',
        ]);

        $editRes = $this->actingAs($this->guru)
            ->get(route('guru.realisasi-tugas-tambahan.edit', $realisasi));
        $editRes->assertStatus(200);
        $editRes->assertSee('Home Visit Siswa Indisipliner');

        $updateRes = $this->actingAs($this->guru)
            ->put(route('guru.realisasi-tugas-tambahan.update', $realisasi), [
                'status_kesesuaian'     => 'sesuai',
                'persentase_kesesuaian' => 100,
                'keterangan_kesesuaian' => 'Seluruh kunjungan rumah terselesaikan 100%.',
                'tanggal_pelaksanaan'   => '2026-10-25',
                'uraian_kegiatan'       => 'Penyelesaian kunjungan ke siswa ke-3.',
                'hasil_capaian'         => '3 dari 3 siswa selesai dikunjungi.',
                'status_program'        => 'tercapai',
            ]);

        $updateRes->assertRedirect(route('guru.realisasi-tugas-tambahan.index'));

        $this->assertDatabaseHas('realisasi_tugas_tambahans', [
            'id'                    => $realisasi->id,
            'status_kesesuaian'     => 'sesuai',
            'persentase_kesesuaian' => 100,
        ]);
        $this->assertEquals('tercapai', $pk->fresh()->status);
    }

    public function test_guru_can_print_laporan_kesesuaian(): void
    {
        $response = $this->actingAs($this->guru)
            ->get(route('guru.realisasi-tugas-tambahan.print'));

        $response->assertStatus(200);
        $response->assertSee('LAPORAN EVALUASI DAN KESESUAIAN REALISASI TUGAS TAMBAHAN');
    }
}
