<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\TahunAjaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class MasterKelasWaliKelasSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $guruA;
    protected User $guruB;
    protected Jurusan $jurusan;
    protected TahunAjaran $tahunAjaran;

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
            'name'           => 'Khayatun Nufus, S.Pd',
            'username'       => 'guru_khayatun',
            'email'          => 'khayatun@hilaledu.sch.id',
            'password'       => Hash::make('password123'),
            'password_plain' => 'password123',
            'role'           => 'guru',
            'is_active'      => true,
            'jabatan_utama'  => 'Guru Pengajar',
            'tugas_tambahan' => [],
        ]);

        $this->guruB = User::create([
            'name'           => 'Ahmad Fauzi, S.Pd',
            'username'       => 'guru_fauzi',
            'email'          => 'fauzi@hilaledu.sch.id',
            'password'       => Hash::make('password123'),
            'password_plain' => 'password123',
            'role'           => 'guru',
            'is_active'      => true,
            'jabatan_utama'  => 'Guru Pengajar',
            'tugas_tambahan' => ['Pembina OSIS'],
        ]);

        $this->jurusan = Jurusan::create([
            'kode'          => 'AKL',
            'nama'          => 'Akuntansi dan Keuangan Lembaga',
            'singkatan'     => 'AKL',
            'is_aktif'      => true,
        ]);

        $this->tahunAjaran = TahunAjaran::create([
            'nama'          => '2025/2026',
            'tahun_mulai'   => 2025,
            'tahun_selesai' => 2026,
            'is_aktif'      => true,
        ]);
    }

    /**
     * Test menyimpan data kelas berhasil tanpa error nama_kelas dan otomatis mengisi tugas tambahan guru.
     */
    public function test_store_kelas_saves_nama_kelas_and_syncs_wali_kelas_to_guru_tugas_tambahan(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->post(route('superadmin.master.kelas.store'), [
                'jurusan_id'      => $this->jurusan->id,
                'tahun_ajaran_id' => $this->tahunAjaran->id,
                'tingkat'         => 'X',
                'nama_kelas'      => 'X AKL',
                'wali_kelas_id'   => $this->guruA->id,
            ]);

        $response->assertRedirect(route('superadmin.master.kelas'));
        $response->assertSessionHas('success');

        // Pastikan kelas tersimpan dengan nama dan nama_kelas konsisten
        $kelas = Kelas::where('nama_kelas', 'X AKL')->first();
        $this->assertNotNull($kelas);
        $this->assertEquals('X AKL', $kelas->nama);
        $this->assertEquals('X AKL', $kelas->nama_kelas);
        $this->assertEquals($this->guruA->id, $kelas->wali_kelas_id);
        $this->assertEquals('Khayatun Nufus, S.Pd', $kelas->wali_kelas);

        // Pastikan tugas tambahan Guru A otomatis tersinkron tanpa duplikasi generik
        $this->guruA->refresh();
        $this->assertTrue($this->guruA->hasRoleCategory('Wali Kelas'));
        $this->assertContains('Wali Kelas X AKL', $this->guruA->tugas_tambahan);
        $this->assertEquals('Wali Kelas X AKL', $this->guruA->jabatan_utama);
    }

    /**
     * Test mengedit kelas dan mengganti wali kelas otomatis memindahkan tugas tambahan.
     */
    public function test_update_kelas_changes_wali_kelas_and_syncs_both_gurus(): void
    {
        // 1. Buat kelas awal dengan Guru A
        $kelas = Kelas::create([
            'jurusan_id'      => $this->jurusan->id,
            'tahun_ajaran_id' => $this->tahunAjaran->id,
            'tingkat'         => 'X',
            'nama_kelas'      => 'X AKL',
            'wali_kelas_id'   => $this->guruA->id,
        ]);

        $this->guruA->refresh();
        $this->assertContains('Wali Kelas X AKL', $this->guruA->tugas_tambahan);

        // 2. Edit kelas: ganti wali kelas ke Guru B
        $response = $this->actingAs($this->superadmin)
            ->put(route('superadmin.master.kelas.update', $kelas), [
                'jurusan_id'      => $this->jurusan->id,
                'tahun_ajaran_id' => $this->tahunAjaran->id,
                'tingkat'         => 'X',
                'nama_kelas'      => 'X AKL',
                'wali_kelas_id'   => $this->guruB->id,
            ]);

        $response->assertRedirect(route('superadmin.master.kelas'));
        $response->assertSessionHas('success');

        // Guru A harus dibersihkan dari tugas Wali Kelas X AKL
        $this->guruA->refresh();
        $this->assertNotContains('Wali Kelas X AKL', $this->guruA->tugas_tambahan ?? []);
        $this->assertNotContains('Wali Kelas', $this->guruA->tugas_tambahan ?? []);

        // Guru B harus memiliki tugas Wali Kelas X AKL dan tugas sebelumnya (Pembina OSIS) tetap utuh
        $this->guruB->refresh();
        $this->assertContains('Pembina OSIS', $this->guruB->tugas_tambahan);
        $this->assertTrue($this->guruB->hasRoleCategory('Wali Kelas'));
        $this->assertContains('Wali Kelas X AKL', $this->guruB->tugas_tambahan);
    }

    /**
     * Test mengubah nama kelas mengupdate nama jabatan di tugas tambahan guru.
     */
    public function test_update_kelas_renames_class_and_updates_guru_tugas_tambahan(): void
    {
        $kelas = Kelas::create([
            'jurusan_id'      => $this->jurusan->id,
            'tahun_ajaran_id' => $this->tahunAjaran->id,
            'tingkat'         => 'X',
            'nama_kelas'      => 'X AKL 1',
            'wali_kelas_id'   => $this->guruA->id,
        ]);

        $this->guruA->refresh();
        $this->assertContains('Wali Kelas X AKL 1', $this->guruA->tugas_tambahan);

        // Rename menjadi X AKL 2
        $response = $this->actingAs($this->superadmin)
            ->put(route('superadmin.master.kelas.update', $kelas), [
                'jurusan_id'      => $this->jurusan->id,
                'tahun_ajaran_id' => $this->tahunAjaran->id,
                'tingkat'         => 'X',
                'nama_kelas'      => 'X AKL 2',
                'wali_kelas_id'   => $this->guruA->id,
            ]);

        $response->assertRedirect(route('superadmin.master.kelas'));

        $this->guruA->refresh();
        $this->assertNotContains('Wali Kelas X AKL 1', $this->guruA->tugas_tambahan);
        $this->assertContains('Wali Kelas X AKL 2', $this->guruA->tugas_tambahan);
        $this->assertEquals('Wali Kelas X AKL 2', $this->guruA->jabatan_utama);
    }

    /**
     * Test menghapus kelas otomatis mencabut penugasan wali kelas dari guru terkait.
     */
    public function test_destroy_kelas_removes_assignment_from_guru(): void
    {
        $kelas = Kelas::create([
            'jurusan_id'      => $this->jurusan->id,
            'tahun_ajaran_id' => $this->tahunAjaran->id,
            'tingkat'         => 'X',
            'nama_kelas'      => 'X AKL',
            'wali_kelas_id'   => $this->guruA->id,
        ]);

        $this->guruA->refresh();
        $this->assertContains('Wali Kelas X AKL', $this->guruA->tugas_tambahan);

        $response = $this->actingAs($this->superadmin)
            ->delete(route('superadmin.master.kelas.destroy', $kelas));

        $response->assertRedirect(route('superadmin.master.kelas'));

        $this->guruA->refresh();
        $this->assertNotContains('Wali Kelas X AKL', $this->guruA->tugas_tambahan ?? []);
        $this->assertNotContains('Wali Kelas', $this->guruA->tugas_tambahan ?? []);
    }

    /**
     * Test penugasan kaprog pada master jurusan otomatis mensinkronkan jabatan kaprog ke guru.
     */
    public function test_jurusan_kaprog_assignment_automatically_syncs_to_guru(): void
    {
        $response = $this->actingAs($this->superadmin)
            ->put(route('superadmin.master.jurusan.update', $this->jurusan), [
                'kode'          => 'AKL',
                'nama'          => 'Akuntansi dan Keuangan Lembaga',
                'singkatan'     => 'AKL',
                'kaprog_id'     => $this->guruB->id,
            ]);

        $response->assertRedirect(route('superadmin.master.jurusan'));

        $this->guruB->refresh();
        $this->assertTrue($this->guruB->hasRoleCategory('Kaprog'));
        $this->assertContains('Kaprog AKL', $this->guruB->tugas_tambahan);
    }
}
