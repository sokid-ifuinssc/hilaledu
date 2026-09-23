<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Application;
use App\Services\CrossAppDatabaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ConnectedDatabasesAndSSOTest extends TestCase
{
    use RefreshDatabase;

    protected User $guru;
    protected User $siswa;
    protected User $tendik;
    protected Application $akademikApp;
    protected Application $tracerApp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guru = User::create([
            'name'           => 'Drs. H. Bambang Subagyo, M.Pd',
            'username'       => 'guru_bambang',
            'email'          => 'bambang@hilaledu.sch.id',
            'password'       => Hash::make('password123'),
            'password_plain' => 'password123',
            'role'           => 'guru',
            'is_active'      => true,
            'jabatan_utama'  => 'Waka Kurikulum',
            'tugas_tambahan' => ['Waka Kurikulum', 'Kepala Lab Komputer', 'Wali Kelas XII RPL 1'],
        ]);

        $this->siswa = User::create([
            'name'           => 'Rian Anggara',
            'username'       => 'siswa_rian',
            'email'          => 'rian@hilaledu.sch.id',
            'password'       => Hash::make('password123'),
            'password_plain' => 'password123',
            'role'           => 'siswa',
            'is_active'      => true,
        ]);

        $this->tendik = User::create([
            'name'           => 'Siti Rahmawati, S.Kom',
            'username'       => 'tendik_siti',
            'email'          => 'siti@hilaledu.sch.id',
            'password'       => Hash::make('password123'),
            'password_plain' => 'password123',
            'role'           => 'tendik',
            'is_active'      => true,
            'jabatan_utama'  => 'Koordinator BKK',
            'tugas_tambahan' => ['Koordinator BKK', 'Operator Dapodik'],
        ]);

        $this->akademikApp = Application::create([
            'name'             => 'Sistem Informasi Akademik',
            'slug'             => 'akademik',
            'description'      => 'Kelola kurikulum, jadwal KBM, nilai, presensi, dan raport siswa terpadu.',
            'url'              => 'http://localhost:8003',
            'database_name'    => 'db_akademik',
            'icon'             => 'fas fa-graduation-cap',
            'color'            => '#059669',
            'sort_order'       => 3,
            'status'           => 'active',
            'sso_enabled'      => true,
            'auto_sync'        => true,
            'sso_redirect_url' => 'http://localhost:8003/auth/sso-callback',
        ]);

        $this->tracerApp = Application::create([
            'name'             => 'Tracer Study Alumni',
            'slug'             => 'tracer-study',
            'description'      => 'Pelacakan karir alumni: Bekerja, Melanjutkan Kuliah, atau Berwirausaha (BMW).',
            'url'              => 'http://localhost:8004',
            'database_name'    => 'db_tracer_study',
            'icon'             => 'fas fa-user-graduate',
            'color'            => '#d97706',
            'sort_order'       => 4,
            'status'           => 'active',
            'sso_enabled'      => true,
            'auto_sync'        => true,
            'sso_redirect_url' => 'http://localhost:8004/auth/sso-callback',
        ]);
    }

    /**
     * Test aplikasi terdaftar dengan metadata dan konfigurasi SSO yang benar.
     */
    public function test_applications_registered_with_correct_sso_and_database_config(): void
    {
        $akademik = Application::where('slug', 'akademik')->first();
        $this->assertNotNull($akademik);
        $this->assertEquals('db_akademik', $akademik->database_name);
        $this->assertTrue($akademik->sso_enabled);
        $this->assertTrue($akademik->auto_sync);
        $this->assertEquals('active', $akademik->status);

        $tracer = Application::where('slug', 'tracer-study')->first();
        $this->assertNotNull($tracer);
        $this->assertEquals('db_tracer_study', $tracer->database_name);
        $this->assertTrue($tracer->sso_enabled);
        $this->assertTrue($tracer->auto_sync);
        $this->assertEquals('active', $tracer->status);
    }

    /**
     * Test SSO API Login untuk aplikasi Akademik dengan user multi-jabatan.
     */
    public function test_sso_login_for_akademik_with_multi_jabatan_user(): void
    {
        $response = $this->postJson('/api/sso/login', [
            'username' => 'guru_bambang',
            'password' => 'password123',
            'app_slug' => 'akademik',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Autentikasi berhasil.',
                'app' => [
                    'slug' => 'akademik',
                    'name' => 'Sistem Informasi Akademik',
                ],
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
                    'jabatan_utama',
                    'tugas_tambahan',
                ],
                'app',
            ]);

        $userData = $response->json('user');
        $this->assertEquals('Waka Kurikulum', $userData['jabatan_utama']);
        $this->assertContains('Kepala Lab Komputer', $userData['tugas_tambahan']);
        $this->assertContains('Wali Kelas XII RPL 1', $userData['tugas_tambahan']);
    }

    /**
     * Test SSO API Login untuk aplikasi Tracer Study dengan user siswa/alumni.
     */
    public function test_sso_login_for_tracer_study_with_siswa_user(): void
    {
        $response = $this->postJson('/api/sso/login', [
            'username' => 'siswa_rian',
            'password' => 'password123',
            'app_slug' => 'tracer-study',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Autentikasi berhasil.',
                'app' => [
                    'slug' => 'tracer-study',
                    'name' => 'Tracer Study Alumni',
                ],
            ])
            ->assertJsonPath('user.role', 'siswa')
            ->assertJsonPath('user.username', 'siswa_rian');
    }

    /**
     * Test validasi token SSO yang diterbitkan untuk aplikasi turunan.
     */
    public function test_sso_validate_token_for_connected_apps(): void
    {
        $loginRes = $this->postJson('/api/sso/login', [
            'username' => 'guru_bambang',
            'password' => 'password123',
            'app_slug' => 'akademik',
        ]);

        $token = $loginRes->json('token');
        $this->assertNotEmpty($token);

        $validateRes = $this->postJson('/api/sso/validate-token', [
            'token' => $token,
        ]);

        $validateRes->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Token SSO valid.',
            ])
            ->assertJsonPath('user.username', 'guru_bambang')
            ->assertJsonPath('user.name', 'Drs. H. Bambang Subagyo, M.Pd');
    }

    /**
     * Test redirect launcher portal SSO ke aplikasi Akademik.
     */
    public function test_sso_launcher_redirects_to_akademik_with_token(): void
    {
        $response = $this->actingAs($this->guru)
            ->get('/sso/launch/akademik');

        $response->assertStatus(302);
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('http://localhost:8003/auth/sso-callback', $redirectUrl);
        $this->assertStringContainsString('sso_token=', $redirectUrl);
    }

    /**
     * Test redirect launcher portal SSO ke aplikasi Tracer Study.
     */
    public function test_sso_launcher_redirects_to_tracer_study_with_token(): void
    {
        $response = $this->actingAs($this->siswa)
            ->get('/sso/launch/tracer-study');

        $response->assertStatus(302);
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('http://localhost:8004/auth/sso-callback', $redirectUrl);
        $this->assertStringContainsString('sso_token=', $redirectUrl);
    }

    /**
     * Test halaman login menampilkan branding SSO portal dan banner notice aplikasi terkait.
     */
    public function test_login_page_renders_sso_portal_branding_and_app_notices(): void
    {
        // 1. Akses halaman login umum
        $responseGeneral = $this->get('/login');
        $responseGeneral->assertStatus(200);
        $responseGeneral->assertSee('HilalEdu - Portal Akses Terpadu');
        $responseGeneral->assertSee('Akademik');
        $responseGeneral->assertSee('Tracer Study');

        // 2. Akses login yang diredirect dari Akademik
        $responseAkademik = $this->get('/login?app=akademik');
        $responseAkademik->assertStatus(200);
        $responseAkademik->assertSee('Anda diarahkan untuk masuk ke aplikasi');
        $responseAkademik->assertSee('Akademik');

        // 3. Akses login yang diredirect dari Tracer Study
        $responseTracer = $this->get('/login?app=tracer-study');
        $responseTracer->assertStatus(200);
        $responseTracer->assertSee('Anda diarahkan untuk masuk ke aplikasi');
        $responseTracer->assertSee('Tracer study');
    }

    /**
     * Test dashboard setiap peran menampilkan card ekosistem aplikasi Akademik & Tracer Study.
     */
    public function test_dashboards_render_connected_applications(): void
    {
        // 1. Dashboard Guru
        $guruRes = $this->actingAs($this->guru)->get('/guru/dashboard');
        $guruRes->assertStatus(200);
        $guruRes->assertSee('Akademik');
        $guruRes->assertSee('Tracer Study');
        $guruRes->assertSee('Alumni & Tracer', false);

        // 2. Dashboard Siswa
        $siswaRes = $this->actingAs($this->siswa)->get('/siswa/dashboard');
        $siswaRes->assertStatus(200);
        $siswaRes->assertSee('Akademik');
        $siswaRes->assertSee('Tracer Study');
        $siswaRes->assertSee('Alumni & Karir', false);

        // 3. Dashboard Tendik
        $tendikRes = $this->actingAs($this->tendik)->get('/tendik/dashboard');
        $tendikRes->assertStatus(200);
        $tendikRes->assertSee('Akademik');
        $tendikRes->assertSee('Tracer Study');
        $tendikRes->assertSee('Alumni & BKK', false);

        // 4. Landing Page (sekarang redirect)
        auth()->logout();
        $welcomeRes = $this->get('/');
        $welcomeRes->assertStatus(302);
        $welcomeRes->assertRedirect('/login');
    }

    /**
     * Test skema tabel akademik dan tracer study dibangun dengan benar menggunakan CrossAppDatabaseService.
     */
    public function test_cross_app_database_service_builds_complete_schema_and_syncs_users(): void
    {
        // Gunakan in-memory sqlite connection untuk pengujian isolasi unit/feature
        config([
            'database.connections.test_akademik' => [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ],
            'database.connections.test_tracer' => [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ],
        ]);

        $service = new CrossAppDatabaseService();

        // 1. Setup Akademik
        $resAkademik = $service->setupAkademikDatabase('test_akademik');
        $this->assertTrue($resAkademik['success']);
        $this->assertTrue(Schema::connection('test_akademik')->hasTable('users'));
        $this->assertTrue(Schema::connection('test_akademik')->hasTable('kurikulums'));
        $this->assertTrue(Schema::connection('test_akademik')->hasTable('mata_pelajarans'));
        $this->assertTrue(Schema::connection('test_akademik')->hasTable('jadwal_pelajarans'));
        $this->assertTrue(Schema::connection('test_akademik')->hasTable('nilais'));
        $this->assertTrue(Schema::connection('test_akademik')->hasTable('presensis'));
        $this->assertTrue(Schema::connection('test_akademik')->hasTable('raports'));

        // 2. Setup Tracer Study
        $resTracer = $service->setupTracerStudyDatabase('test_tracer');
        $this->assertTrue($resTracer['success']);
        $this->assertTrue(Schema::connection('test_tracer')->hasTable('users'));
        $this->assertTrue(Schema::connection('test_tracer')->hasTable('profil_alumnis'));
        $this->assertTrue(Schema::connection('test_tracer')->hasTable('riwayat_pekerjaans'));
        $this->assertTrue(Schema::connection('test_tracer')->hasTable('riwayat_pendidikans'));
        $this->assertTrue(Schema::connection('test_tracer')->hasTable('riwayat_wirausahas'));
        $this->assertTrue(Schema::connection('test_tracer')->hasTable('kuesioner_tracers'));
        $this->assertTrue(Schema::connection('test_tracer')->hasTable('jawaban_kuesioners'));
        $this->assertTrue(Schema::connection('test_tracer')->hasTable('statistik_tracers'));

        // 3. Sync User dengan multi tugas tambahan ke kedua database uji
        $syncAkademik = $service->syncUserToApp($this->guru, 'test_akademik');
        $this->assertTrue($syncAkademik['success']);

        $userInAkademik = DB::connection('test_akademik')->table('users')->where('username', 'guru_bambang')->first();
        $this->assertNotNull($userInAkademik);
        $this->assertEquals('Waka Kurikulum', $userInAkademik->jabatan_utama);
        $this->assertStringContainsString('Kepala Lab Komputer', (string) $userInAkademik->tugas_tambahan);

        $syncTracer = $service->syncUserToApp($this->guru, 'test_tracer');
        $this->assertTrue($syncTracer['success']);
        $userInTracer = DB::connection('test_tracer')->table('users')->where('username', 'guru_bambang')->first();
        $this->assertNotNull($userInTracer);
    }
}
