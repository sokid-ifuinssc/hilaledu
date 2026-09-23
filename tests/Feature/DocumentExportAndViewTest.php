<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\MasterTahunAjaran;
use App\Models\MasterJurusan;
use App\Models\MasterKelas;

class DocumentExportAndViewTest extends TestCase
{
    use RefreshDatabase;

    protected function getSuperAdmin()
    {
        return User::where('role', 'superadmin')->first() ?? User::factory()->create(['role' => 'superadmin']);
    }

    public function test_master_data_exports_and_templates_work(): void
    {
        $admin = $this->getSuperAdmin();

        $this->actingAs($admin)->get(route('superadmin.guru.template'))->assertStatus(200);
        $this->actingAs($admin)->get(route('superadmin.guru.export'))->assertStatus(200);

        $this->actingAs($admin)->get(route('superadmin.siswa.template'))->assertStatus(200);
        $this->actingAs($admin)->get(route('superadmin.siswa.export'))->assertStatus(200);

        $this->actingAs($admin)->get(route('superadmin.tendik.template'))->assertStatus(200);
        $this->actingAs($admin)->get(route('superadmin.tendik.export'))->assertStatus(200);

        $this->actingAs($admin)->get(route('superadmin.master.template'))->assertStatus(200);
        $this->actingAs($admin)->get(route('superadmin.master.export'))->assertStatus(200);
    }

    public function test_akademik_exports_templates_and_prints_work(): void
    {
        $admin = $this->getSuperAdmin();

        $this->actingAs($admin)->get(route('akademik.jadwal.template'))->assertStatus(200);
        $this->actingAs($admin)->get(route('akademik.jadwal.export'))->assertStatus(200);

        $this->actingAs($admin)->get(route('akademik.kurikulum.template'))->assertStatus(200);
        $this->actingAs($admin)->get(route('akademik.kurikulum.export'))->assertStatus(200);

        $this->actingAs($admin)->get(route('akademik.mapel.template'))->assertStatus(200);
        $this->actingAs($admin)->get(route('akademik.mapel.export'))->assertStatus(200);

        $this->actingAs($admin)->get(route('akademik.kalender.template-csv'))->assertStatus(200);

        $this->actingAs($admin)->get(route('akademik.jadwal.matrix.print'))->assertStatus(200);
        $this->actingAs($admin)->get(route('akademik.kurikulum.print'))->assertStatus(200);
        $this->actingAs($admin)->get(route('akademik.laporan.kbm.print'))->assertStatus(200);
        $this->actingAs($admin)->get(route('akademik.laporan.kehadiran.print'))->assertStatus(200);
        $this->actingAs($admin)->get(route('akademik.rekap-presensi.print-all'))->assertStatus(200);
    }

    public function test_bk_exports_templates_and_cetak_work(): void
    {
        $admin = $this->getSuperAdmin();

        $this->actingAs($admin)->get(route('bk.kategori-pelanggaran.export'))->assertStatus(200);
        $this->actingAs($admin)->get(route('bk.kategori-pelanggaran.template'))->assertStatus(200);
        $this->actingAs($admin)->get(route('bk.laporan.cetak'))->assertStatus(200);
    }

    public function test_prakerin_export_and_print_work(): void
    {
        $admin = $this->getSuperAdmin();

        $this->actingAs($admin)->get(route('prakerin.laporan.export'))->assertStatus(200);
        $this->actingAs($admin)->get(route('prakerin.laporan.print'))->assertStatus(200);
    }

    public function test_all_roles_access_their_dashboards_smoothly(): void
    {
        // 1. Super Admin
        $superAdmin = $this->getSuperAdmin();
        $this->actingAs($superAdmin)->get(route('superadmin.dashboard'))->assertStatus(200);

        // 2. Guru
        $guru = User::factory()->create(['role' => 'guru']);
        $this->actingAs($guru)->get(route('guru.dashboard'))->assertStatus(200);
        $this->actingAs($guru)->get(route('guru.profile.edit'))->assertStatus(200);

        // 3. Siswa
        $siswa = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($siswa)->get(route('siswa.dashboard'))->assertStatus(200);
        $this->actingAs($siswa)->get(route('siswa.profile.edit'))->assertStatus(200);

        // 4. Tendik
        $tendik = User::factory()->create(['role' => 'tendik']);
        $this->actingAs($tendik)->get(route('tendik.dashboard'))->assertStatus(200);
        $this->actingAs($tendik)->get(route('tendik.profile.edit'))->assertStatus(200);

        // 5. Admin Biasa
        $admin = User::factory()->create(['role' => 'admin', 'admin_role' => 'akademik']);
        $this->actingAs($admin)->get(route('akademik.dashboard'))->assertStatus(200);
    }
}
