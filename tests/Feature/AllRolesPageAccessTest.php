<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AllRolesPageAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pastikan semua halaman utama per role dapat diakses tanpa 500 ErrorException
     */
    public function test_all_major_pages_render_without_500_error(): void
    {
        $this->withoutExceptionHandling();
        // 1. Superadmin test
        $superadmin = User::where('role', 'superadmin')->first() ?? User::factory()->create(['role' => 'superadmin', 'is_active' => true]);

        $superadminUrls = [
            '/superadmin/dashboard',
            '/superadmin/guru',
            '/superadmin/tendik',
            '/superadmin/siswa',
            '/superadmin/users',
            '/akademik/dashboard',
            '/akademik/kalender',
            '/prakerin/dashboard',
            '/keuangan/dashboard',
            '/koperasi/dashboard',
            '/tracer/dashboard',
        ];

        foreach ($superadminUrls as $url) {
            $response = $this->actingAs($superadmin)->get($url);
            $this->assertNotEquals(500, $response->status(), "URL [{$url}] menghasilkan status 500 Internal Server Error untuk Superadmin!");
        }

        // 2. Guru dashboard
        $guru = User::where('role', 'guru')->first() ?? User::factory()->create(['role' => 'guru', 'is_active' => true]);
        $response = $this->actingAs($guru)->get('/guru/dashboard');
        $this->assertNotEquals(500, $response->status(), "URL [/guru/dashboard] menghasilkan status 500 untuk Guru!");

        // 3. Siswa dashboard
        $siswa = User::where('role', 'siswa')->first() ?? User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $response = $this->actingAs($siswa)->get('/siswa/dashboard');
        $this->assertNotEquals(500, $response->status(), "URL [/siswa/dashboard] menghasilkan status 500 untuk Siswa!");

        // 4. Tendik dashboard
        $tendik = User::where('role', 'tendik')->first() ?? User::factory()->create(['role' => 'tendik', 'is_active' => true]);
        $response = $this->actingAs($tendik)->get('/tendik/dashboard');
        $this->assertNotEquals(500, $response->status(), "URL [/tendik/dashboard] menghasilkan status 500 untuk Tendik!");

        // 5. BK dashboard
        $bk = User::where('role', 'guru_bk')->first() ?? User::factory()->create(['role' => 'guru_bk', 'is_active' => true]);
        $response = $this->actingAs($bk)->get('/bk/dashboard');
        $this->assertNotEquals(500, $response->status(), "URL [/bk/dashboard] menghasilkan status 500 untuk BK!");

        // 6. Kepsek dashboard
        $kepsek = User::where('role', 'kepala_sekolah')->first() ?? User::factory()->create(['role' => 'kepala_sekolah', 'is_active' => true]);
        $response = $this->actingAs($kepsek)->get('/kepsek/dashboard');
        $this->assertNotEquals(500, $response->status(), "URL [/kepsek/dashboard] menghasilkan status 500 untuk Kepsek!");

        // 7. Kaprog dashboard
        $kaprog = User::where('role', 'kaprog')->first() ?? User::factory()->create(['role' => 'kaprog', 'is_active' => true]);
        $response = $this->actingAs($kaprog)->get('/kaprog/dashboard');
        $this->assertNotEquals(500, $response->status(), "URL [/kaprog/dashboard] menghasilkan status 500 untuk Kaprog!");

        // 8. Walikelas dashboard
        $walikelas = User::where('role', 'wali_kelas')->first() ?? User::factory()->create(['role' => 'wali_kelas', 'is_active' => true]);
        $response = $this->actingAs($walikelas)->get('/walikelas/dashboard');
        $this->assertNotEquals(500, $response->status(), "URL [/walikelas/dashboard] menghasilkan status 500 untuk Walikelas!");

        // 9. Operator dashboard
        $operator = User::where('role', 'operator')->first() ?? User::factory()->create(['role' => 'operator', 'is_active' => true]);
        $response = $this->actingAs($operator)->get('/operator/dashboard');
        $this->assertNotEquals(500, $response->status(), "URL [/operator/dashboard] menghasilkan status 500 untuk Operator!");
    }
}
