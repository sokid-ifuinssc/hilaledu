<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Imports\SiswaImport;
use App\Imports\GuruImport;
use App\Imports\TendikImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

class ExcelImportValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_import_fails_when_nis_is_empty_and_records_column_error()
    {
        $import = new SiswaImport('update');
        $rows = new Collection([
            [
                'nis'          => '', // EMPTY NIS
                'nama_lengkap' => 'Budi Santoso',
                'username'     => 'budisantoso',
                'email'        => 'budi@gmail.com',
            ],
            [
                'nis'          => '12345678', // VALID NIS, other fields empty
                'nama_lengkap' => '',
                'username'     => '',
                'email'        => '',
            ]
        ]);

        $import->collection($rows);

        // Row 1 (Excel row 2) should fail with column 'NIS / NISN'
        $errors = $import->getErrors();
        $this->assertCount(1, $errors);
        $this->assertEquals(2, $errors[0]['row']);
        $this->assertEquals('NIS/NISN', $errors[0]['column']);
        $this->assertStringContainsString("wajib diisi", $errors[0]['message']);

        // Row 2 (Excel row 3) should succeed with fallbacks
        $this->assertEquals(1, $import->getCreatedCount());
        $user = User::where('nip', '12345678')->first();
        $this->assertNotNull($user);
        $this->assertEquals('siswa', $user->role);
        $this->assertEquals('Siswa 12345678', $user->name);
        $this->assertStringContainsString('12345678@siswa.hilaledu.sch.id', $user->email);
    }

    public function test_siswa_import_handles_duplicate_action_update()
    {
        // Existing user
        User::create([
            'name'           => 'Lama Banget',
            'username'       => 'nis999',
            'email'          => 'lama@siswa.hilaledu.sch.id',
            'password'       => bcrypt('password123'),
            'password_plain' => 'password123',
            'role'           => 'siswa',
            'nip'            => '99999',
            'is_active'      => true,
        ]);

        $import = new SiswaImport('update');
        $rows = new Collection([
            [
                'nis'          => '99999',
                'nama_lengkap' => 'Nama Baru Diperbarui',
                'desa'         => 'Desa Sukamaju',
            ]
        ]);

        $import->collection($rows);

        $this->assertEquals(1, $import->getUpdatedCount());
        $user = User::where('nip', '99999')->first();
        $this->assertEquals('Nama Baru Diperbarui', $user->name);
        $this->assertEquals('Desa Sukamaju', $user->desa);
        $this->assertCount(1, $import->getMatchedRecords());
    }

    public function test_siswa_import_handles_duplicate_action_skip()
    {
        User::create([
            'name'           => 'Nama Asli Tetap',
            'username'       => 'nis888',
            'email'          => 'asli@siswa.hilaledu.sch.id',
            'password'       => bcrypt('password123'),
            'password_plain' => 'password123',
            'role'           => 'siswa',
            'nip'            => '88888',
            'is_active'      => true,
        ]);

        $import = new SiswaImport('skip');
        $rows = new Collection([
            [
                'nis'          => '88888',
                'nama_lengkap' => 'Nama Yang Harusnya Dilewati',
            ]
        ]);

        $import->collection($rows);

        $this->assertEquals(1, $import->getSkippedCount());
        $user = User::where('nip', '88888')->first();
        $this->assertEquals('Nama Asli Tetap', $user->name);
    }

    public function test_siswa_import_handles_duplicate_action_replace()
    {
        User::create([
            'name'           => 'User Mau Dihapus',
            'username'       => 'nis777',
            'email'          => 'replace@siswa.hilaledu.sch.id',
            'password'       => bcrypt('password123'),
            'password_plain' => 'password123',
            'role'           => 'siswa',
            'nip'            => '77777',
            'is_active'      => true,
        ]);

        $import = new SiswaImport('replace');
        $rows = new Collection([
            [
                'nis'          => '77777',
                'nama_lengkap' => 'User Baru Pengganti',
            ]
        ]);

        $import->collection($rows);

        $this->assertEquals(1, $import->getReplacedCount());
        $user = User::where('nip', '77777')->first();
        $this->assertEquals('User Baru Pengganti', $user->name);
    }

    public function test_guru_import_fails_when_nuptk_is_empty_and_records_column_error()
    {
        $import = new GuruImport('update');
        $rows = new Collection([
            [
                'nuptk'        => '', // EMPTY NUPTK
                'nama_lengkap' => 'Dra. Siti Aminah',
            ],
            [
                'nuptk'        => '1987654321', // VALID NUPTK
                'nama_lengkap' => '',
            ]
        ]);

        $import->collection($rows);

        // Row 1 (Excel row 2) fails on NUPTK
        $errors = $import->getErrors();
        $this->assertCount(1, $errors);
        $this->assertEquals(2, $errors[0]['row']);
        $this->assertEquals('NUPTK', $errors[0]['column']);
        $this->assertStringContainsString("wajib diisi", $errors[0]['message']);

        // Row 2 succeeds
        $this->assertEquals(1, $import->getCreatedCount());
        $guru = User::where('nip', '1987654321')->first();
        $this->assertNotNull($guru);
        $this->assertEquals('guru', $guru->role);
        $this->assertEquals('Guru 1987654321', $guru->name);
    }

    public function test_tendik_import_fails_when_nuptk_is_empty_and_records_column_error()
    {
        $import = new TendikImport('update');
        $rows = new Collection([
            [
                'nuptk'        => '', // EMPTY NUPTK
                'nama_lengkap' => 'Staf Tata Usaha',
            ],
            [
                'nuptk'        => '5544332211', // VALID NUPTK
                'nama_lengkap' => 'Bambang TU',
            ]
        ]);

        $import->collection($rows);

        $errors = $import->getErrors();
        $this->assertCount(1, $errors);
        $this->assertEquals(2, $errors[0]['row']);
        $this->assertEquals('NUPTK', $errors[0]['column']);

        $this->assertEquals(1, $import->getCreatedCount());
        $tendik = User::where('nip', '5544332211')->first();
        $this->assertNotNull($tendik);
        $this->assertEquals('tendik', $tendik->role);
        $this->assertEquals('Bambang TU', $tendik->name);
    }

    public function test_http_siswa_import_processes_file_and_redirects_with_summary()
    {
        $superadmin = User::create([
            'name'           => 'Super Admin',
            'username'       => 'admin_super',
            'email'          => 'admin@hilaledu.sch.id',
            'password'       => bcrypt('password123'),
            'password_plain' => 'password123',
            'role'           => 'superadmin',
            'is_active'      => true,
        ]);

        $csvContent = "nis,nama_lengkap,email\n";
        $csvContent .= "987654321,Ahmad Siswa,ahmad@siswa.hilaledu.sch.id\n";
        $csvContent .= ",Siswa Tanpa NIS,\n"; // Invalid row: missing NIS

        $tempFile = tempnam(sys_get_temp_dir(), 'import_test_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempFile,
            'siswa.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->actingAs($superadmin)->post(route('superadmin.siswa.import.process'), [
            'file'             => $uploadedFile,
            'duplicate_action' => 'update',
        ]);

        $response->assertRedirect(route('superadmin.siswa.index'));
        $response->assertSessionHas('import_summary');

        $summary = session('import_summary');
        $this->assertEquals(1, $summary['created']);
        $this->assertCount(1, $summary['errors']);
        $this->assertEquals('NIS/NISN', $summary['errors'][0]['column']);

        @unlink($tempFile);
    }
}
