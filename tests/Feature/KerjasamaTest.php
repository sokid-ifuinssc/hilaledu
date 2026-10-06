<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Dudi;
use App\Models\Kerjasama;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class KerjasamaTest extends TestCase
{
    use RefreshDatabase;

    protected function getSuperAdmin(): User
    {
        return User::firstOrCreate(
            ['username' => 'test_superadmin_kerjasama'],
            [
                'name'     => 'Super Admin Test',
                'email'    => 'superadmin_kerjasama@test.com',
                'password' => bcrypt('password'),
                'role'     => 'superadmin',
            ]
        );
    }

    public function test_superadmin_can_access_kerjasama_pages(): void
    {
        $admin = $this->getSuperAdmin();

        $this->actingAs($admin)
            ->get(route('prakerin.kerjasama.index'))
            ->assertStatus(200)
            ->assertSee('Pendataan Kerjasama Mitra DU/DI');

        $this->actingAs($admin)
            ->get(route('prakerin.kerjasama.create'))
            ->assertStatus(200)
            ->assertSee('Input Pendataan Kerjasama');
    }

    public function test_kerjasama_can_be_stored_and_automatically_creates_dudi(): void
    {
        Storage::fake('public');
        $admin = $this->getSuperAdmin();

        $namaMitra = 'PT Solusi Teknologi Cerdas ' . time();
        $file = UploadedFile::fake()->create('mou_kerjasama.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('prakerin.kerjasama.store'), [
            'nama_mitra'        => $namaMitra,
            'bidang_mitra'      => 'Teknologi Informasi & Software',
            'alamat'            => 'Jl. Industri Kreatif No. 12, Bandung',
            'no_telp'           => '022-87654321',
            'email'             => 'hrd@solusitek.co.id',
            'nomor_mou'         => '001/MOU/SMK-HILAL/2026',
            'bentuk_kerjasama'  => ['Sinkronisasi Kurikulum', 'Pelaksanaan Prakerin / PKL', 'Penggajian / Payroll'],
            'tahun_mulai'       => '2024',
            'tahun_berakhir'    => '2027',
            'file_kerjasama'    => $file,
            'link_drive'        => 'https://drive.google.com/drive/folders/test12345',
            'pic_nama'          => 'Budi Santoso, S.T.',
            'pic_jabatan'       => 'Head of HR & Cooperation',
            'pic_kontak'        => '081234567890',
            'keterangan'        => 'Kerjasama mencakup kurikulum industri dan penempatan 5 siswa magang per tahun.',
        ]);

        $response->assertRedirect(route('prakerin.kerjasama.index'));
        $response->assertSessionHas('success');

        // Pastikan record Kerjasama tersimpan
        $this->assertDatabaseHas('kerjasamas', [
            'nama_mitra'     => $namaMitra,
            'bidang_mitra'   => 'Teknologi Informasi & Software',
            'tahun_mulai'    => '2024',
            'tahun_berakhir' => '2027',
            'link_drive'     => 'https://drive.google.com/drive/folders/test12345',
        ]);

        // VERIFIKASI UTAMA: Pastikan otomatis terdaftar di tabel dudi (Mitra DU/DI Prakerin)!
        $this->assertDatabaseHas('dudi', [
            'nama'         => $namaMitra,
            'bidang_usaha' => 'Teknologi Informasi & Software',
            'alamat'       => 'Jl. Industri Kreatif No. 12, Bandung',
            'status'       => 1,
        ]);

        $dudi = Dudi::where('nama', $namaMitra)->first();
        $this->assertNotNull($dudi);

        // Cek bahwa Kerjasama terhubung dengan Dudi
        $kerjasama = Kerjasama::where('nama_mitra', $namaMitra)->first();
        $this->assertEquals($dudi->id, $kerjasama->dudi_id);

        // Pastikan muncul di list Mitra DU/DI Prakerin
        $this->actingAs($admin)
            ->get(route('prakerin.dudi.index'))
            ->assertStatus(200)
            ->assertSee($namaMitra);
    }

    public function test_kerjasama_detail_and_update(): void
    {
        $admin = $this->getSuperAdmin();

        $kerjasama = Kerjasama::create([
            'nama_mitra'        => 'PT Industri Otomotif Sejahtera',
            'bidang_mitra'      => 'Otomotif & Manufaktur',
            'alamat'            => 'Kawasan Industri Cikarang',
            'bentuk_kerjasama'  => ['Pelaksanaan Prakerin / PKL', 'Guru Tamu / Praktisi Mengajar'],
            'tahun_mulai'       => '2025',
            'tahun_berakhir'    => '2028',
            'link_drive'        => 'https://drive.google.com/test',
            'status'            => 'aktif',
            'created_by'        => $admin->id,
        ]);

        // Cek halaman detail
        $this->actingAs($admin)
            ->get(route('prakerin.kerjasama.show', $kerjasama->id))
            ->assertStatus(200)
            ->assertSee('PT Industri Otomotif Sejahtera')
            ->assertSee('Otomotif & Manufaktur');

        // Update kerjasama
        $response = $this->actingAs($admin)->put(route('prakerin.kerjasama.update', $kerjasama->id), [
            'nama_mitra'        => 'PT Industri Otomotif Sejahtera Tbk',
            'bidang_mitra'      => 'Manufaktur Modern',
            'alamat'            => 'Kawasan Industri MM2100 Cikarang',
            'bentuk_kerjasama'  => ['Pelaksanaan Prakerin / PKL', 'Kelas Industri / Teaching Factory'],
            'tahun_mulai'       => '2025',
            'tahun_berakhir'    => '2029',
            'link_drive'        => 'https://drive.google.com/test-updated',
        ]);

        $response->assertRedirect(route('prakerin.kerjasama.index'));
        $this->assertDatabaseHas('kerjasamas', [
            'id'          => $kerjasama->id,
            'nama_mitra'  => 'PT Industri Otomotif Sejahtera Tbk',
            'tahun_berakhir' => '2029',
        ]);
    }
}
