<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PengaturanSekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class PengaturanSekolahSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $guruA;
    protected User $guruB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::create([
            'name'           => 'Super Administrator',
            'username'       => 'superadmin',
            'email'          => 'admin@hilaledu.sch.id',
            'password'       => Hash::make('password123'),
            'password_plain' => 'password123',
            'role'           => 'superadmin',
            'is_active'      => true,
        ]);

        $this->guruA = User::create([
            'name'           => 'Drs. H. Ahmad Sanusi',
            'username'       => 'guru_sanusi',
            'email'          => 'sanusi@hilaledu.sch.id',
            'password'       => Hash::make('password123'),
            'password_plain' => 'password123',
            'role'           => 'guru',
            'is_active'      => true,
            'jabatan_utama'  => 'Guru Pengajar',
            'tugas_tambahan' => [],
        ]);

        $this->guruB = User::create([
            'name'           => 'Dr. Hendra Gunawan, M.Pd',
            'username'       => 'guru_hendra',
            'email'          => 'hendra@hilaledu.sch.id',
            'password'       => Hash::make('password123'),
            'password_plain' => 'password123',
            'role'           => 'guru',
            'is_active'      => true,
            'jabatan_utama'  => 'Guru Pengajar',
            'tugas_tambahan' => ['Pembina OSIS'],
        ]);
    }

    public function test_can_access_pengaturan_sekolah_index(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->get(route('superadmin.master.sekolah'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Identitas Sekolah');
    }

    public function test_appointing_kepala_sekolah_automatically_syncs_tugas_tambahan(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->put(route('superadmin.master.sekolah.update'), [
                'nama_sekolah'      => 'SMK PLUS AL HILAL',
                'npsn'              => '20269999',
                'kepala_sekolah_id' => $this->guruA->id,
            ]);

        $response->assertRedirect(route('superadmin.master.sekolah'));

        $setting = PengaturanSekolah::getSetting();
        $this->assertEquals($this->guruA->id, $setting->kepala_sekolah_id);

        // Guru A harus otomatis memperoleh Tugas Tambahan "Kepala Sekolah"
        $this->guruA->refresh();
        $this->assertContains('Kepala Sekolah', $this->guruA->tugas_tambahan);
        $this->assertEquals('Kepala Sekolah', $this->guruA->jabatan_utama);
    }

    public function test_changing_kepala_sekolah_transfers_tugas_tambahan_between_gurus(): void
    {
        // 1. Tunjuk Guru A sebagai Kepala Sekolah awal
        PengaturanSekolah::getSetting()->update(['kepala_sekolah_id' => $this->guruA->id]);

        $this->guruA->refresh();
        $this->assertContains('Kepala Sekolah', $this->guruA->tugas_tambahan);

        // 2. Ganti Kepala Sekolah ke Guru B
        $response = $this->actingAs($this->superadmin)
            ->put(route('superadmin.master.sekolah.update'), [
                'nama_sekolah'      => 'SMK PLUS AL HILAL',
                'npsn'              => '20269999',
                'kepala_sekolah_id' => $this->guruB->id,
            ]);

        $response->assertRedirect(route('superadmin.master.sekolah'));

        // Guru A harus dibersihkan dari "Kepala Sekolah"
        $this->guruA->refresh();
        $this->assertNotContains('Kepala Sekolah', $this->guruA->tugas_tambahan ?? []);

        // Guru B harus memperoleh "Kepala Sekolah" dan "Pembina OSIS" tetap utuh
        $this->guruB->refresh();
        $this->assertContains('Kepala Sekolah', $this->guruB->tugas_tambahan);
        $this->assertContains('Pembina OSIS', $this->guruB->tugas_tambahan);
    }
}
