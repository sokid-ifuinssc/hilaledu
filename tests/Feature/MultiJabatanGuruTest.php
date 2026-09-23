<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Application;
use App\Models\AppCoordinator;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class MultiJabatanGuruTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $guru;
    protected Application $testApp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::create([
            'name'           => 'Super Administrator',
            'username'       => 'superadmin',
            'email'          => 'superadmin@hilaledu.sch.id',
            'password'       => Hash::make('password123'),
            'password_plain' => 'password123',
            'role'           => 'superadmin',
            'is_active'      => true,
        ]);

        $this->guru = User::create([
            'name'           => 'Budi Santoso, M.Kom',
            'username'       => 'budi_santoso',
            'email'          => 'budi@hilaledu.sch.id',
            'password'       => Hash::make('password123'),
            'password_plain' => 'password123',
            'role'           => 'guru',
            'nip'            => '198501012010011001',
            'is_active'      => true,
        ]);

        $this->testApp = Application::create([
            'name'          => 'Aplikasi PKL & Magang',
            'slug'          => 'aplikasi-pkl',
            'url'           => 'http://localhost:8002',
            'database_name' => 'db_pkl_magang',
            'status'        => 'active',
            'sso_enabled'   => true,
            'api_key'       => 'hila_test_key_123456789012345678901234',
        ]);
    }

    /**
     * Test satu guru dapat memiliki multi tugas tambahan di lingkungan sekolah.
     */
    public function test_guru_can_have_multiple_tugas_tambahan(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->post(route('superadmin.guru.store'), [
                'name'           => 'Siti Rahmawati, S.Pd',
                'username'       => 'siti_rahma',
                'email'          => 'siti@hilaledu.sch.id',
                'password'       => 'password123',
                'password_confirmation' => 'password123',
                'jabatan_utama'  => 'Guru Produktif TKJ',
                'tugas_tambahan' => ['Wali Kelas', 'Kepala Bengkel / Lab', 'Pembina Paskibra', 'Koordinator Literasi'],
                'is_active'      => '1',
            ]);

        $response->assertRedirect(route('superadmin.guru.index'));

        $guruBaru = User::where('username', 'siti_rahma')->first();
        $this->assertNotNull($guruBaru);
        $this->assertEquals('Guru Produktif TKJ', $guruBaru->jabatan_utama);
        $this->assertIsArray($guruBaru->tugas_tambahan);
        $this->assertContains('Wali Kelas', $guruBaru->tugas_tambahan);
        $this->assertContains('Kepala Bengkel / Lab', $guruBaru->tugas_tambahan);
        $this->assertContains('Pembina Paskibra', $guruBaru->tugas_tambahan);
        $this->assertContains('Koordinator Literasi', $guruBaru->tugas_tambahan);

        $this->assertTrue($guruBaru->hasTugasTambahan('Wali Kelas'));
        $this->assertTrue($guruBaru->hasTugasTambahan('Koordinator Literasi'));
        $this->assertFalse($guruBaru->hasTugasTambahan('Kepala Perpustakaan'));

        // Cek daftar jabatan lengkap
        $this->assertContains('Guru Produktif TKJ', $guruBaru->daftar_jabatan);
        $this->assertContains('Wali Kelas', $guruBaru->daftar_jabatan);
        $this->assertStringContainsString('Wali Kelas', $guruBaru->tugas_tambahan_label);
    }

    /**
     * Test satu guru dapat ditugaskan lebih dari satu peran di aplikasi yang sama.
     */
    public function test_same_guru_can_have_multiple_roles_in_same_application(): void
    {
        // 1. Tugaskan peran Admin Aplikasi
        $res1 = $this->actingAs($this->superadmin)
            ->post(route('superadmin.apps.assign-coordinator', $this->testApp), [
                'user_id'          => $this->guru->id,
                'coordinator_role' => 'admin_app',
            ]);
        $res1->assertSessionHas('success');

        // 2. Tugaskan peran Pembimbing pada aplikasi yang sama
        $res2 = $this->actingAs($this->superadmin)
            ->post(route('superadmin.apps.assign-coordinator', $this->testApp), [
                'user_id'          => $this->guru->id,
                'coordinator_role' => 'pembimbing',
            ]);
        $res2->assertSessionHas('success');

        // Pastikan ada 2 record penugasan untuk guru yang sama di aplikasi ini
        $coordinators = AppCoordinator::where('application_id', $this->testApp->id)
            ->where('user_id', $this->guru->id)
            ->get();
        $this->assertCount(2, $coordinators);

        $roles = $coordinators->pluck('coordinator_role')->all();
        $this->assertContains('admin_app', $roles);
        $this->assertContains('pembimbing', $roles);

        // 3. Menugaskan peran yang persis sama lagi harus ditolak
        $res3 = $this->actingAs($this->superadmin)
            ->post(route('superadmin.apps.assign-coordinator', $this->testApp), [
                'user_id'          => $this->guru->id,
                'coordinator_role' => 'admin_app',
            ]);
        $res3->assertSessionHas('error');
        $this->assertCount(2, AppCoordinator::where('application_id', $this->testApp->id)->where('user_id', $this->guru->id)->get());
    }

    /**
     * Test pencabutan salah satu peran tidak menghapus peran lainnya untuk guru yang sama.
     */
    public function test_revoking_one_role_keeps_other_roles_intact(): void
    {
        $coordAdmin = AppCoordinator::create([
            'application_id'   => $this->testApp->id,
            'user_id'          => $this->guru->id,
            'coordinator_role' => 'admin_app',
            'assigned_by'      => $this->superadmin->id,
        ]);

        $coordPembimbing = AppCoordinator::create([
            'application_id'   => $this->testApp->id,
            'user_id'          => $this->guru->id,
            'coordinator_role' => 'pembimbing',
            'assigned_by'      => $this->superadmin->id,
        ]);

        // Cabut hanya peran pembimbing dengan menyertakan coordinator_id
        $response = $this->actingAs($this->superadmin)
            ->delete(route('superadmin.apps.remove-coordinator', [$this->testApp, $this->guru]), [
                'coordinator_id' => $coordPembimbing->id,
            ]);

        $response->assertSessionHas('success');

        // Record pembimbing harus terhapus, tetapi admin_app harus tetap ada
        $this->assertDatabaseMissing('app_coordinators', ['id' => $coordPembimbing->id]);
        $this->assertDatabaseHas('app_coordinators', ['id' => $coordAdmin->id]);
    }

    /**
     * Test pembuatan Tahun Ajaran dan Jurusan & Kelas tanpa error SQL legacy column.
     */
    public function test_master_data_creation_with_teacher_assignments(): void
    {
        // 1. Tahun Ajaran
        $responseTa = $this->actingAs($this->superadmin)
            ->post(route('superadmin.master.tahun-ajaran.store'), [
                'nama'          => '2026/2027',
                'tahun_mulai'   => 2026,
                'tahun_selesai' => 2027,
            ]);
        $responseTa->assertRedirect(route('superadmin.master.tahun-ajaran'));

        $ta = TahunAjaran::where('tahun_mulai', 2026)->first();
        $this->assertNotNull($ta);
        $this->assertEquals('2026/2027', $ta->nama);

        // 2. Jurusan dengan penugasan Kaprog
        $responseJurusan = $this->actingAs($this->superadmin)
            ->post(route('superadmin.master.jurusan.store'), [
                'kode'          => 'TKJT',
                'nama'          => 'Teknik Jaringan Komputer dan Telekomunikasi',
                'singkatan'     => 'TKJT',
                'kaprog_id'     => $this->guru->id,
            ]);
        $responseJurusan->assertRedirect(route('superadmin.master.jurusan'));

        $jurusan = Jurusan::where('kode', 'TKJT')->first();
        $this->assertNotNull($jurusan);
        $this->assertEquals($this->guru->id, $jurusan->kaprog_id);
        $this->assertEquals($this->guru->name, $jurusan->ketua_jurusan);
        $this->assertEquals($this->guru->name, $jurusan->kaprogGuru->name);

        // 3. Kelas dengan penugasan Wali Kelas
        $responseKelas = $this->actingAs($this->superadmin)
            ->post(route('superadmin.master.kelas.store'), [
                'jurusan_id'      => $jurusan->id,
                'tahun_ajaran_id' => $ta->id,
                'tingkat'         => 'X',
                'nama_kelas'      => 'X TKJT 1',
                'wali_kelas_id'   => $this->guru->id,
            ]);
        $responseKelas->assertRedirect(route('superadmin.master.kelas'));

        $kelas = Kelas::where('nama', 'X TKJT 1')->first();
        $this->assertNotNull($kelas);
        $this->assertEquals($this->guru->id, $kelas->wali_kelas_id);
        $this->assertEquals($this->guru->name, $kelas->wali_kelas);
        $this->assertEquals($this->guru->name, $kelas->waliKelasGuru->name);
    }

    /**
     * Test SSO login mengembalikan seluruh peran guru di aplikasi yang diakses.
     */
    public function test_sso_returns_all_assigned_app_roles_for_guru(): void
    {
        AppCoordinator::create([
            'application_id'   => $this->testApp->id,
            'user_id'          => $this->guru->id,
            'coordinator_role' => 'admin_app',
            'assigned_by'      => $this->superadmin->id,
        ]);

        AppCoordinator::create([
            'application_id'   => $this->testApp->id,
            'user_id'          => $this->guru->id,
            'coordinator_role' => 'pembimbing',
            'assigned_by'      => $this->superadmin->id,
        ]);

        $response = $this->postJson('/api/sso/login', [
            'username' => $this->guru->username,
            'password' => 'password123',
            'app_slug' => $this->testApp->slug,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $user = $response->json('user');
        $this->assertIsArray($user['app_roles']);
        $this->assertContains('admin_app', $user['app_roles']);
        $this->assertContains('pembimbing', $user['app_roles']);
        $this->assertEquals('admin_app', $user['app_role']);
    }
}
