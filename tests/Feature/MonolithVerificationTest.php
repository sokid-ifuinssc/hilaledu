<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class MonolithVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function getSuperAdmin()
    {
        return User::where('role', 'superadmin')->first() ?? User::factory()->create(['role' => 'superadmin']);
    }

    public function test_superadmin_can_access_akademik_modules(): void
    {
        $user = $this->getSuperAdmin();
        
        $this->actingAs($user)->get(route('akademik.dashboard'))->assertStatus(200);
        $this->actingAs($user)->get(route('akademik.kalender.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('akademik.kurikulum.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('akademik.jadwal.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('akademik.jadwal.matrix'))->assertStatus(200);
        $this->actingAs($user)->get(route('akademik.nilai.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('akademik.mapel.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('akademik.laporan.kehadiran.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('akademik.laporan.kbm.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('akademik.rekap-presensi.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('akademik.piket.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('akademik.keluhan.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('akademik.pengaturan.index'))->assertStatus(200);
    }

    public function test_superadmin_can_access_bk_modules(): void
    {
        $user = $this->getSuperAdmin();
        
        $this->actingAs($user)->get(route('bk.dashboard'))->assertStatus(200);
        $this->actingAs($user)->get(route('bk.pelanggaran.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('bk.poin.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('bk.kategori-pelanggaran.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('bk.jenis-pelanggaran.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('bk.progres.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('bk.laporan.index'))->assertStatus(200);
    }

    public function test_superadmin_can_access_prakerin_modules(): void
    {
        $user = $this->getSuperAdmin();
        
        $this->actingAs($user)->get(route('prakerin.dashboard'))->assertStatus(200);
        $this->actingAs($user)->get(route('prakerin.dudi.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('prakerin.pembimbing-dudi.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('prakerin.periode.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('prakerin.penempatan.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('prakerin.industri.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('prakerin.jurnal.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('prakerin.laporan.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('prakerin.laporan.print'))->assertStatus(200);
        $this->actingAs($user)->get(route('prakerin.laporan.export'))->assertStatus(200);
    }

    public function test_superadmin_can_access_keuangan_modules(): void
    {
        $user = $this->getSuperAdmin();
        
        $this->actingAs($user)->get(route('keuangan.dashboard'))->assertStatus(200);
        $this->actingAs($user)->get(route('keuangan.tagihan.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('keuangan.pembayaran.index'))->assertStatus(200);
    }

    public function test_superadmin_can_access_koperasi_modules(): void
    {
        $user = $this->getSuperAdmin();
        
        $this->actingAs($user)->get(route('koperasi.dashboard'))->assertStatus(200);
        $this->actingAs($user)->get(route('koperasi.anggota.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('koperasi.transaksi.index'))->assertStatus(200);
    }

    public function test_superadmin_can_access_tracer_modules(): void
    {
        $user = $this->getSuperAdmin();
        
        $this->actingAs($user)->get(route('tracer.dashboard'))->assertStatus(200);
        $this->actingAs($user)->get(route('tracer.alumni.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('tracer.kuesioner.index'))->assertStatus(200);
    }

    public function test_superadmin_dashboard_renders_cleanly(): void
    {
        $user = $this->getSuperAdmin();
        $this->actingAs($user)->get(route('superadmin.dashboard'))->assertStatus(200);
    }

    public function test_guru_can_access_dashboard_and_services(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $this->actingAs($guru)->get(route('guru.dashboard'))->assertStatus(200);
        $this->actingAs($guru)->get(route('guru.profile.edit'))->assertStatus(200);
        $this->actingAs($guru)->get(route('akademik.dashboard'))->assertStatus(200);
        $this->actingAs($guru)->get(route('prakerin.dashboard'))->assertStatus(200);
        $this->actingAs($guru)->get(route('koperasi.dashboard'))->assertStatus(200);
        $this->actingAs($guru)->get(route('tracer.dashboard'))->assertStatus(200);
        $this->actingAs($guru)->get(route('guru.payroll.index'))->assertStatus(200);

        // Guru dengan tugas tambahan BK dapat mengakses BK
        $guruBk = User::factory()->create(['role' => 'guru', 'tugas_tambahan' => ['Guru BK']]);
        $this->actingAs($guruBk)->get(route('bk.dashboard'))->assertStatus(200);
    }

    public function test_siswa_can_access_dashboard_and_services(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($siswa)->get(route('siswa.dashboard'))->assertStatus(200);
        $this->actingAs($siswa)->get(route('siswa.profile.edit'))->assertStatus(200);
        $this->actingAs($siswa)->get(route('akademik.dashboard'))->assertStatus(200);
        $this->actingAs($siswa)->get(route('prakerin.dashboard'))->assertStatus(200);
        $this->actingAs($siswa)->get(route('keuangan.dashboard'))->assertStatus(200);
        $this->actingAs($siswa)->get(route('tracer.dashboard'))->assertStatus(200);
    }

    public function test_tendik_can_access_dashboard_and_services(): void
    {
        $tendik = User::factory()->create(['role' => 'tendik']);
        $this->actingAs($tendik)->get(route('tendik.dashboard'))->assertStatus(200);
        $this->actingAs($tendik)->get(route('tendik.profile.edit'))->assertStatus(200);
        $this->actingAs($tendik)->get(route('akademik.dashboard'))->assertStatus(200);
        $this->actingAs($tendik)->get(route('koperasi.dashboard'))->assertStatus(200);
        $this->actingAs($tendik)->get(route('tracer.dashboard'))->assertStatus(200);
        $this->actingAs($tendik)->get(route('tendik.payroll.index'))->assertStatus(200);
    }
}
