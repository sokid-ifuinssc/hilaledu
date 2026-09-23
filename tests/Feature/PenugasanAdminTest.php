<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Application;
use App\Models\PengelolaAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenugasanAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function getSuperAdmin()
    {
        return User::where('role', 'superadmin')->first() ?? User::factory()->create([
            'name'      => 'Super Administrator',
            'username'  => 'superadmin_test',
            'email'     => 'superadmin_test@hilaledu.sch.id',
            'role'      => 'superadmin',
            'is_active' => true,
        ]);
    }

    protected function getGuru()
    {
        return User::where('role', 'guru')->first() ?? User::factory()->create([
            'name'      => 'Guru Pengajar Test',
            'username'  => 'guru_test',
            'email'     => 'guru_test@hilaledu.sch.id',
            'role'      => 'guru',
            'is_active' => true,
        ]);
    }

    protected function getTendik()
    {
        return User::where('role', 'tendik')->first() ?? User::factory()->create([
            'name'      => 'Staf TU Test',
            'username'  => 'tendik_test',
            'email'     => 'tendik_test@hilaledu.sch.id',
            'role'      => 'tendik',
            'is_active' => true,
        ]);
    }

    public function test_superadmin_can_access_penugasan_admin_page()
    {
        $superadmin = $this->getSuperAdmin();

        $response = $this->actingAs($superadmin)->get(route('superadmin.penugasan-admin.index'));

        $response->assertStatus(200);
        $response->assertSee('Penunjukan Admin Unit (Guru & TU)');
        $response->assertSee('Admin Akademik & KBM');
        $response->assertSee('Admin Prakerin / PKL');
        $response->assertSee('Admin Monitoring BK');
        $response->assertSee('Admin Koperasi Sekolah');
        $response->assertSee('Admin Keuangan & SPP');
        $response->assertSee('Admin Tracer Study (BKK)');
    }

    public function test_superadmin_can_appoint_guru_as_admin_prakerin()
    {
        $superadmin = $this->getSuperAdmin();
        $guru = $this->getGuru();

        $response = $this->actingAs($superadmin)->post(route('superadmin.penugasan-admin.store'), [
            'user_id'    => $guru->id,
            'admin_role' => 'prakerin',
            'keterangan' => 'Ditunjuk mengelola kemitraan DU/DI',
        ]);

        $response->assertRedirect(route('superadmin.penugasan-admin.index'));
        $response->assertSessionHas('success');

        $guru->refresh();
        $this->assertEquals('prakerin', $guru->admin_role);
    }

    public function test_superadmin_can_appoint_tendik_as_admin_keuangan()
    {
        $superadmin = $this->getSuperAdmin();
        $tendik = $this->getTendik();

        $response = $this->actingAs($superadmin)->post(route('superadmin.penugasan-admin.store'), [
            'user_id'    => $tendik->id,
            'admin_role' => 'keuangan',
            'keterangan' => 'Penugasan bendahara SPP sekolah',
        ]);

        $response->assertRedirect(route('superadmin.penugasan-admin.index'));
        $response->assertSessionHas('success');

        $tendik->refresh();
        $this->assertEquals('keuangan', $tendik->admin_role);
    }

    public function test_superadmin_can_appoint_guru_as_admin_akademik_and_syncs_pengelola_akademik()
    {
        $superadmin = $this->getSuperAdmin();
        $guru = $this->getGuru();

        $response = $this->actingAs($superadmin)->post(route('superadmin.penugasan-admin.store'), [
            'user_id'    => $guru->id,
            'admin_role' => 'akademik',
            'keterangan' => 'Penugasan admin kurikulum & jadwal KBM',
        ]);

        $response->assertRedirect(route('superadmin.penugasan-admin.index'));
        $response->assertSessionHas('success');

        $guru->refresh();
        $this->assertEquals('akademik', $guru->admin_role);

        // Check PengelolaAkademik record
        $pengelola = PengelolaAkademik::where('user_id', $guru->id)->first();
        $this->assertNotNull($pengelola);
        $this->assertTrue($pengelola->can_manage_jadwal);
        $this->assertTrue($pengelola->can_manage_mapel);
        $this->assertTrue($pengelola->can_view_laporan_kehadiran);
        $this->assertTrue($pengelola->can_view_laporan_kbm);
    }

    public function test_superadmin_can_revoke_admin_assignment()
    {
        $superadmin = $this->getSuperAdmin();
        $guru = $this->getGuru();
        $guru->update(['admin_role' => 'bk']);

        $response = $this->actingAs($superadmin)->delete(route('superadmin.penugasan-admin.destroy', $guru));

        $response->assertRedirect(route('superadmin.penugasan-admin.index'));
        $response->assertSessionHas('success');

        $guru->refresh();
        $this->assertNull($guru->admin_role);
    }

    public function test_non_superadmin_cannot_access_penugasan_admin_page()
    {
        $guru = $this->getGuru();

        $response = $this->actingAs($guru)->get(route('superadmin.penugasan-admin.index'));

        // RoleMiddleware redirects non-superadmin
        $response->assertRedirect();
    }
}
