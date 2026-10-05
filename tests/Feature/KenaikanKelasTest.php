<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\TracerAlumni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KenaikanKelasTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected TahunAjaran $taAktif;
    protected Jurusan $jurusan;
    protected Kelas $kelasX;
    protected Kelas $kelasXI;
    protected Kelas $kelasXII;
    protected User $siswa1;
    protected User $siswa2;
    protected User $siswaXii;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'username'  => 'superadmin_test',
            'role'      => 'superadmin',
            'is_active' => true,
        ]);

        $this->taAktif = TahunAjaran::create([
            'nama'          => '2026/2027',
            'tahun_mulai'   => 2026,
            'tahun_selesai' => 2027,
            'is_aktif'      => true,
        ]);

        $this->jurusan = Jurusan::create([
            'kode'      => 'TKJT',
            'nama'      => 'Teknik Jaringan Telekomunikasi',
            'singkatan' => 'TKJT',
        ]);

        $this->kelasX = Kelas::create([
            'tingkat'         => 'X',
            'nama'            => 'X TKJT 1',
            'nama_kelas'      => 'X TKJT 1',
            'jurusan_id'      => $this->jurusan->id,
            'tahun_ajaran_id' => $this->taAktif->id,
            'is_aktif'        => true,
        ]);

        $this->kelasXI = Kelas::create([
            'tingkat'         => 'XI',
            'nama'            => 'XI TKJT 1',
            'nama_kelas'      => 'XI TKJT 1',
            'jurusan_id'      => $this->jurusan->id,
            'tahun_ajaran_id' => $this->taAktif->id,
            'is_aktif'        => true,
        ]);

        $this->kelasXII = Kelas::create([
            'tingkat'         => 'XII',
            'nama'            => 'XII TKJT 1',
            'nama_kelas'      => 'XII TKJT 1',
            'jurusan_id'      => $this->jurusan->id,
            'tahun_ajaran_id' => $this->taAktif->id,
            'is_aktif'        => true,
        ]);

        // Buat Siswa Kelas X
        $this->siswa1 = User::factory()->create([
            'role'      => 'siswa',
            'kelas_id'  => $this->kelasX->id,
            'is_active' => true,
        ]);
        Siswa::create([
            'user_id'      => $this->siswa1->id,
            'kelas_id'     => $this->kelasX->id,
            'nis'          => 'NIS-1001',
            'nama_lengkap' => $this->siswa1->name,
            'jenis_kelamin' => 'L',
            'status'       => 'aktif',
        ]);

        $this->siswa2 = User::factory()->create([
            'role'      => 'siswa',
            'kelas_id'  => $this->kelasX->id,
            'is_active' => true,
        ]);
        Siswa::create([
            'user_id'      => $this->siswa2->id,
            'kelas_id'     => $this->kelasX->id,
            'nis'          => 'NIS-1002',
            'nama_lengkap' => $this->siswa2->name,
            'jenis_kelamin' => 'L',
            'status'       => 'aktif',
        ]);

        // Buat Siswa Kelas XII (Calon Alumni)
        $this->siswaXii = User::factory()->create([
            'role'      => 'siswa',
            'kelas_id'  => $this->kelasXII->id,
            'is_active' => true,
        ]);
        Siswa::create([
            'user_id'      => $this->siswaXii->id,
            'kelas_id'     => $this->kelasXII->id,
            'nis'          => 'NIS-1201',
            'nama_lengkap' => $this->siswaXii->name,
            'jenis_kelamin' => 'L',
            'status'       => 'aktif',
        ]);
    }

    public function test_halaman_kenaikan_kelas_dapat_diakses_oleh_superadmin(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('superadmin.kenaikan-kelas.index'));
        $response->assertStatus(200);
        $response->assertSee('Kenaikan Kelas, Kelulusan', false);
        $response->assertSee('2026/2027');
        $response->assertSee('X TKJT 1');
    }

    public function test_proses_kenaikan_kelas_siswa_berhasil(): void
    {
        $payload = [
            'kelas_asal_id'   => $this->kelasX->id,
            'kelas_tujuan_id' => $this->kelasXI->id,
            'siswa'           => [
                $this->siswa1->id => 'naik',
                $this->siswa2->id => 'tinggal',
            ],
        ];

        $response = $this->actingAs($this->superAdmin)->post(route('superadmin.kenaikan-kelas.naik'), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Siswa 1 harus naik ke Kelas XI
        $this->siswa1->refresh();
        $this->assertEquals($this->kelasXI->id, $this->siswa1->kelas_id);
        $this->assertDatabaseHas('siswas', [
            'user_id'  => $this->siswa1->id,
            'kelas_id' => $this->kelasXI->id,
            'status'   => 'aktif',
        ]);

        // Siswa 2 tinggal kelas di Kelas X
        $this->siswa2->refresh();
        $this->assertEquals($this->kelasX->id, $this->siswa2->kelas_id);
        $this->assertDatabaseHas('siswas', [
            'user_id'  => $this->siswa2->id,
            'kelas_id' => $this->kelasX->id,
            'status'   => 'aktif',
        ]);
    }

    public function test_proses_kelulusan_siswa_kelas_xii_otomatis_menjadi_alumni(): void
    {
        $payload = [
            'siswa_ids'     => [$this->siswaXii->id],
            'tahun_lulus'   => 2027,
            'status_tracer' => 'Belum Mengisi',
        ];

        $response = $this->actingAs($this->superAdmin)->post(route('superadmin.kenaikan-kelas.lulus'), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // User kelas_id harus null tapi tetap aktif agar bisa isi tracer
        $this->siswaXii->refresh();
        $this->assertNull($this->siswaXii->kelas_id);
        $this->assertTrue((bool)$this->siswaXii->is_active);

        // Siswa status harus 'lulus' dan kelas_id null
        $this->assertDatabaseHas('siswas', [
            'user_id'  => $this->siswaXii->id,
            'kelas_id' => null,
            'status'   => 'lulus',
        ]);

        // Harus otomatis tercatat di tabel tracer_alumnis
        $this->assertDatabaseHas('tracer_alumnis', [
            'user_id'     => $this->siswaXii->id,
            'tahun_lulus' => 2027,
        ]);
    }

    public function test_pergantian_tahun_ajaran_baru_berkelanjutan(): void
    {
        $payload = [
            'nama'            => '2027/2028',
            'tahun_mulai'     => 2027,
            'tahun_selesai'   => 2028,
            'semester'        => 'ganjil',
            'duplikasi_kelas' => 1,
        ];

        $response = $this->actingAs($this->superAdmin)->post(route('superadmin.kenaikan-kelas.tahun-ajaran-baru'), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Tahun ajaran lama harus dinonaktifkan
        $this->taAktif->refresh();
        $this->assertFalse((bool)$this->taAktif->is_aktif);

        // Tahun ajaran baru harus aktif
        $this->assertDatabaseHas('tahun_ajarans', [
            'nama'        => '2027/2028',
            'tahun_mulai' => 2027,
            'is_aktif'    => true,
        ]);

        // Kelas aktif harus terikat ke tahun ajaran baru
        $taBaru = TahunAjaran::where('nama', '2027/2028')->first();
        $this->assertNotNull($taBaru);
        $this->assertEquals($taBaru->id, Kelas::find($this->kelasX->id)->tahun_ajaran_id);
    }

    public function test_fallback_redirect_admin_kenaikan_kelas(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.kenaikan-kelas.index'));
        $response->assertRedirect(route('superadmin.kenaikan-kelas.index'));
    }
}
