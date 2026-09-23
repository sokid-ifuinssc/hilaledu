<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaKelasAndRombelTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected Kelas $kelas1;
    protected Kelas $kelas2;
    protected User $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'username'  => 'superadmin_test',
            'role'      => 'superadmin',
            'is_active' => true,
        ]);

        $ta = TahunAjaran::create([
            'nama'          => '2025/2026',
            'tahun_mulai'   => '2025',
            'tahun_selesai' => '2026',
            'is_aktif'      => true,
        ]);

        $jurusan = Jurusan::create([
            'kode'      => 'TKJ',
            'nama'      => 'Teknik Komputer dan Jaringan',
            'singkatan' => 'TKJ',
        ]);

        $this->kelas1 = Kelas::create([
            'tingkat'         => 'X',
            'nama'            => 'X TKJ 1',
            'nama_kelas'      => 'X TKJ 1',
            'jurusan_id'      => $jurusan->id,
            'tahun_ajaran_id' => $ta->id,
            'is_aktif'        => true,
        ]);

        $this->kelas2 = Kelas::create([
            'tingkat'         => 'X',
            'nama'            => 'X TKJ 2',
            'nama_kelas'      => 'X TKJ 2',
            'jurusan_id'      => $jurusan->id,
            'tahun_ajaran_id' => $ta->id,
            'is_aktif'        => true,
        ]);

        $this->siswa = User::factory()->create([
            'username'  => 'siswa_test_1',
            'role'      => 'siswa',
            'is_active' => true,
            'kelas_id'  => null,
        ]);
    }

    public function test_can_access_siswa_edit_page_without_error()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('superadmin.siswa.edit', $this->siswa));

        $response->assertStatus(200);
        $response->assertSee($this->siswa->name);
    }

    public function test_can_update_siswa_kelas()
    {
        $response = $this->actingAs($this->superAdmin)
            ->put(route('superadmin.siswa.update', $this->siswa), [
                'name'          => 'Siswa Updated',
                'username'      => $this->siswa->username,
                'email'         => $this->siswa->email,
                'kelas_id'      => $this->kelas1->id,
                'is_active'     => '1',
            ]);

        $response->assertRedirect(route('superadmin.siswa.index'));
        $this->assertDatabaseHas('users', [
            'id'       => $this->siswa->id,
            'kelas_id' => $this->kelas1->id,
        ]);
    }

    public function test_can_filter_siswa_by_kelas()
    {
        $this->siswa->update(['kelas_id' => $this->kelas1->id]);

        $siswa2 = User::factory()->create([
            'username'  => 'siswa_test_2',
            'role'      => 'siswa',
            'is_active' => true,
            'kelas_id'  => $this->kelas2->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('superadmin.siswa.index', ['kelas_id' => $this->kelas1->id]));

        $response->assertStatus(200);
        $response->assertSee($this->siswa->name);
        $response->assertDontSee($siswa2->name);
    }

    public function test_can_access_rombel_index_and_show()
    {
        $responseIndex = $this->actingAs($this->superAdmin)
            ->get(route('superadmin.rombel.index'));

        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Pengaturan Rombongan Belajar');

        $responseShow = $this->actingAs($this->superAdmin)
            ->get(route('superadmin.rombel.show', $this->kelas1));

        $responseShow->assertStatus(200);
        $responseShow->assertSee($this->kelas1->nama_lengkap);
    }

    public function test_can_assign_siswa_to_rombel()
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('superadmin.rombel.assign', $this->kelas1), [
                'siswa_ids' => [$this->siswa->id],
            ]);

        $response->assertRedirect(route('superadmin.rombel.show', $this->kelas1));
        $this->assertDatabaseHas('users', [
            'id'       => $this->siswa->id,
            'kelas_id' => $this->kelas1->id,
        ]);
    }

    public function test_can_pindah_siswa_between_rombel()
    {
        $this->siswa->update(['kelas_id' => $this->kelas1->id]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('superadmin.rombel.pindah'), [
                'siswa_ids'       => [$this->siswa->id],
                'target_kelas_id' => $this->kelas2->id,
            ]);

        $this->assertDatabaseHas('users', [
            'id'       => $this->siswa->id,
            'kelas_id' => $this->kelas2->id,
        ]);
    }

    public function test_can_remove_siswa_from_rombel()
    {
        $this->siswa->update(['kelas_id' => $this->kelas1->id]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('superadmin.rombel.remove', [$this->kelas1, $this->siswa]));

        $response->assertRedirect(route('superadmin.rombel.show', $this->kelas1));
        $this->assertDatabaseHas('users', [
            'id'       => $this->siswa->id,
            'kelas_id' => null,
        ]);
    }

    public function test_can_remove_bulk_siswas_from_rombel()
    {
        $this->siswa->update(['kelas_id' => $this->kelas1->id]);

        $siswa2 = User::factory()->create([
            'username'  => 'siswa_test_bulk_2',
            'role'      => 'siswa',
            'is_active' => true,
            'kelas_id'  => $this->kelas1->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('superadmin.rombel.remove-bulk', $this->kelas1), [
                'siswa_ids' => [$this->siswa->id, $siswa2->id],
            ]);

        $response->assertRedirect(route('superadmin.rombel.show', $this->kelas1));
        $this->assertDatabaseHas('users', ['id' => $this->siswa->id, 'kelas_id' => null]);
        $this->assertDatabaseHas('users', ['id' => $siswa2->id, 'kelas_id' => null]);
    }
}
