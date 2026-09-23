<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class SSOTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $guru;
    protected User $siswa;
    protected Application $bkApp;

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
            'name'           => 'Ahmad Fauzi, S.Pd',
            'username'       => 'guru01',
            'email'          => 'guru01@hilaledu.sch.id',
            'password'       => Hash::make('password123'),
            'password_plain' => 'password123',
            'role'           => 'guru',
            'is_active'      => true,
        ]);

        $this->siswa = User::create([
            'name'           => 'Muhammad Rizki',
            'username'       => 'siswa01',
            'email'          => 'siswa01@hilaledu.sch.id',
            'password'       => Hash::make('password123'),
            'password_plain' => 'password123',
            'role'           => 'siswa',
            'is_active'      => true,
        ]);

        $this->bkApp = Application::create([
            'name'          => 'Monitoring BK',
            'slug'          => 'monitoring-bk',
            'url'           => 'http://localhost:8001',
            'database_name' => 'db_monitoring_bk',
            'status'        => 'active',
            'sso_enabled'   => true,
            'auto_sync'     => false,
        ]);
    }

    /**
     * Test SSO API Login dengan kredensial yang benar.
     */
    public function test_sso_login_successful(): void
    {
        $response = $this->postJson('/api/sso/login', [
            'username' => 'guru01',
            'password' => 'password123',
            'app_slug' => 'monitoring-bk',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Autentikasi berhasil.',
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'token',
                'expires_at',
                'user' => [
                    'id',
                    'name',
                    'username',
                    'email',
                    'role',
                ],
                'app' => [
                    'name',
                    'slug',
                ],
            ]);
    }

    /**
     * Test SSO API Login gagal jika password salah.
     */
    public function test_sso_login_fails_with_wrong_password(): void
    {
        $response = $this->postJson('/api/sso/login', [
            'username' => 'guru01',
            'password' => 'passwordsalah',
            'app_slug' => 'monitoring-bk',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status'  => 'error',
                'message' => 'Password yang Anda masukkan salah.',
            ]);
    }

    /**
     * Test validasi token SSO yang diterbitkan.
     */
    public function test_sso_validate_token(): void
    {
        $loginRes = $this->postJson('/api/sso/login', [
            'username' => 'siswa01',
            'password' => 'password123',
        ]);

        $token = $loginRes->json('token');

        $validateRes = $this->postJson('/api/sso/validate-token', [
            'token' => $token,
        ]);

        $validateRes->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Token SSO valid.',
            ])
            ->assertJsonPath('user.username', 'siswa01');
    }

    /**
     * Test info server SSO HilalEdu.
     */
    public function test_sso_app_info(): void
    {
        $response = $this->getJson('/api/sso/app-info?slug=monitoring-bk');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'app' => [
                    'name' => 'Monitoring BK',
                    'slug' => 'monitoring-bk',
                ],
            ]);
    }

    /**
     * Test SuperAdmin dapat membuka halaman daftar aplikasi & pengaturan SSO.
     */
    public function test_superadmin_can_view_apps_index(): void
    {
        $response = $this->actingAs($this->superadmin)->get('/superadmin/apps');

        $response->assertStatus(200);
        $response->assertSee('Modul Terpadu HilalEdu', false);
        $response->assertSee('Monitoring BK');
    }

    /**
     * Test SuperAdmin dapat menambahkan aplikasi baru ("dan nanti akan buat lagi").
     */
    public function test_superadmin_can_create_new_application(): void
    {
        $response = $this->actingAs($this->superadmin)->post('/superadmin/apps', [
            'name'          => 'Sistem Ujian CBT',
            'slug'          => 'cbt-online',
            'url'           => 'http://localhost:8005',
            'database_name' => 'db_cbt_test',
            'status'        => 'active',
            'auto_sync'     => '0',
            'sso_enabled'   => '1',
            'description'   => 'Ujian daring sekolah',
        ]);

        $response->assertRedirect('/superadmin/apps');
        $this->assertDatabaseHas('applications', [
            'slug' => 'cbt-online',
            'name' => 'Sistem Ujian CBT',
        ]);
    }

    /**
     * Test SSO Portal Launcher mengarahkan user dengan parameter sso_token.
     */
    public function test_sso_portal_launcher_redirects_with_token(): void
    {
        $response = $this->actingAs($this->guru)->get('/sso/launch/monitoring-bk');

        $response->assertRedirect();
        $target = $response->headers->get('Location');
        $this->assertStringContainsString('http://localhost:8001', $target);
        $this->assertStringContainsString('sso_token=', $target);
        $this->assertStringContainsString('username=guru01', $target);
    }
}
