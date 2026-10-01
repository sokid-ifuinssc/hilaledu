<?php

namespace Tests\Feature;

use App\Models\TugasTambahan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterTugasTambahanTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'username'  => 'superadmin_tugas',
            'role'      => 'superadmin',
            'is_active' => true,
        ]);
    }

    public function test_can_access_master_tugas_tambahan_index()
    {
        TugasTambahan::create([
            'nama'      => 'Koordinator PKL',
            'kode'      => 'KOR_PKL',
            'kategori'  => 'Prakerin',
            'deskripsi' => 'Pengelola PKL siswa',
            'is_aktif'  => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('superadmin.master.tugas-tambahan'));

        $response->assertStatus(200);
        $response->assertSee('Koordinator PKL');
    }

    public function test_can_create_master_tugas_tambahan()
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('superadmin.master.tugas-tambahan.store'), [
                'nama'         => 'Guru Pembimbing Industri',
                'kode'         => 'PEMBIMBING_IND',
                'kategori'     => 'Prakerin',
                'nominal_gaji' => 250000,
                'deskripsi'    => 'Pembimbing siswa di industri',
            ]);

        $response->assertRedirect(route('superadmin.master.tugas-tambahan'));
        $this->assertDatabaseHas('master_tugas_tambahan', [
            'nama' => 'Guru Pembimbing Industri',
            'kode' => 'PEMBIMBING_IND',
            'nominal_gaji' => 250000,
        ]);
    }

    public function test_can_update_master_tugas_tambahan()
    {
        $item = TugasTambahan::create([
            'nama'         => 'Wakasek Humas',
            'kode'         => 'HUMAS',
            'kategori'     => 'Manajemen',
            'nominal_gaji' => 100000,
            'is_aktif'     => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->put(route('superadmin.master.tugas-tambahan.update', $item), [
                'nama'         => 'Wakasek Humas & Hubin',
                'kode'         => 'HUMAS_HUBIN',
                'kategori'     => 'Prakerin',
                'nominal_gaji' => 300000,
                'deskripsi'    => 'Diperbarui',
                'is_aktif'     => '1',
            ]);

        $response->assertRedirect(route('superadmin.master.tugas-tambahan'));
        $this->assertDatabaseHas('master_tugas_tambahan', [
            'id'           => $item->id,
            'nama'         => 'Wakasek Humas & Hubin',
            'kode'         => 'HUMAS_HUBIN',
            'nominal_gaji' => 300000,
        ]);
    }

    public function test_can_delete_master_tugas_tambahan()
    {
        $item = TugasTambahan::create([
            'nama'      => 'Tugas Sementara',
            'kategori'  => 'Akademik',
            'is_aktif'  => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->delete(route('superadmin.master.tugas-tambahan.destroy', $item));

        $response->assertRedirect(route('superadmin.master.tugas-tambahan'));
        $this->assertDatabaseMissing('master_tugas_tambahan', [
            'id' => $item->id,
        ]);
    }

    public function test_guru_create_page_loads_master_tugas_tambahan()
    {
        TugasTambahan::create([
            'nama'      => 'Pembina Pramuka',
            'kategori'  => 'Kesiswaan',
            'is_aktif'  => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('superadmin.guru.create'));

        $response->assertStatus(200);
        $response->assertSee('Pembina Pramuka');
    }

    public function test_guru_edit_page_loads_without_error()
    {
        TugasTambahan::create([
            'nama'      => 'Pembina PMR',
            'kategori'  => 'Kesiswaan',
            'is_aktif'  => true,
        ]);

        $guru = User::factory()->create([
            'name'           => 'Guru Budi, M.Pd',
            'username'       => 'budi_guru',
            'email'          => 'budi@hilaledu.test',
            'role'           => 'guru',
            'tugas_tambahan' => ['Pembina PMR'],
            'is_active'      => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('superadmin.guru.edit', $guru));

        $response->assertStatus(200);
        $response->assertSee('Edit Data Guru');
        $response->assertSee('Guru Budi, M.Pd');
        $response->assertSee('Pembina PMR');
        $response->assertDontSee('Undefined variable $roles');
    }

    public function test_guru_update_saves_data()
    {
        $guru = User::factory()->create([
            'name'           => 'Guru Asli',
            'username'       => 'guru_asli',
            'email'          => 'asli@hilaledu.test',
            'role'           => 'guru',
            'is_active'      => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->put(route('superadmin.guru.update', $guru), [
                'name'           => 'Guru Asli Updated, S.Kom',
                'username'       => 'guru_asli',
                'email'          => 'asli_baru@hilaledu.test',
                'nip'            => '198701012020011001',
                'no_hp'          => '081234567890',
                'jenis_kelamin'  => 'L',
                'jabatan_utama'  => 'Guru Kejuruan',
                'tugas_tambahan' => ['Koordinator PKL'],
                'is_active'      => '1',
            ]);

        $response->assertRedirect(route('superadmin.guru.index'));
        $guru->refresh();
        $this->assertEquals('Guru Asli Updated, S.Kom', $guru->name);
        $this->assertEquals('asli_baru@hilaledu.test', $guru->email);
        $this->assertEquals('198701012020011001', $guru->nip);
        $this->assertContains('Koordinator PKL', $guru->tugas_tambahan);
    }
}
