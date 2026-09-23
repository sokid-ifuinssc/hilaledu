<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class SiswaImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    protected string $duplicateAction; // 'update', 'skip', 'replace'

    protected int $createdCount = 0;
    protected int $updatedCount = 0;
    protected int $skippedCount = 0;
    protected int $replacedCount = 0;
    protected array $matchedRecords = [];
    protected array $errors = [];
    protected array $processedUserIds = [];

    public function __construct(string $duplicateAction = 'update')
    {
        $this->duplicateAction = in_array($duplicateAction, ['update', 'skip', 'replace']) ? $duplicateAction : 'update';
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Baris 1 adalah header, baris data dimulai dari 2

            // Ekstrak NIS/NISN (kolom wajib mutlak bagi siswa)
            $nis = trim((string)($row['nis_nisn'] ?? $row['nis'] ?? $row['nisn'] ?? $row['nip'] ?? $row['no_induk'] ?? ''));

            // Validasi NIS: Jika kosong, tolak baris ini dan beritahukan kolom serta kesalahannya
            if (empty($nis)) {
                $this->errors[] = [
                    'row'        => $rowNumber,
                    'column'     => 'NIS/NISN',
                    'identifier' => '-',
                    'message'    => 'Kolom NIS/NISN kosong. NIS/NISN wajib diisi sebagai identitas unik siswa.',
                ];
                continue;
            }

            // Ekstrak Nama Lengkap (jika kosong, fallback otomatis)
            $name = trim((string)($row['nama_lengkap'] ?? $row['nama'] ?? $row['nama_siswa'] ?? ''));
            if (empty($name)) {
                $name = 'Siswa ' . $nis;
            }

            // Ekstrak Username (jika kosong, buat otomatis dari NIS)
            $username = trim((string)($row['username'] ?? $row['user'] ?? ''));
            if (empty($username)) {
                $cleanNis = preg_replace('/[^a-zA-Z0-9]/', '', $nis);
                $username = 'siswa_' . $cleanNis;
            } else {
                $username = preg_replace('/[^a-zA-Z0-9_.-]/', '', strtolower($username));
            }

            // Ekstrak Email (jika kosong, buat email sekolah otomatis dari NIS)
            $email = trim((string)($row['email'] ?? $row['e_mail'] ?? ''));
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $cleanNis = preg_replace('/[^a-zA-Z0-9]/', '', $nis);
                $email = $cleanNis . '@siswa.hilaledu.sch.id';
            }

            // Ekstrak Password (default password123 jika kosong)
            $plainPassword = trim((string)($row['password'] ?? $row['kata_sandi'] ?? ''));
            if (empty($plainPassword)) {
                $plainPassword = 'password123';
            }

            // Ekstrak data opsional lainnya sesuai template
            $noHpSiswa = $this->cleanValue($row['no_hp_siswa'] ?? $row['no_hp'] ?? $row['telepon'] ?? null);
            $namaAyah  = $this->cleanValue($row['nama_ayah'] ?? null);
            $namaIbu   = $this->cleanValue($row['nama_ibu'] ?? null);
            $noHpOrtu  = $this->cleanValue($row['no_hp_ortu'] ?? $row['no_hp_orang_tua'] ?? $row['no_hp_wali'] ?? null);
            $jk        = $this->parseJenisKelamin($row['jenis_kelamin'] ?? $row['jk'] ?? null);

            $alamat    = $this->cleanValue($row['alamat'] ?? null);
            $desa      = $this->cleanValue($row['desa'] ?? $row['desa_kelurahan'] ?? null);
            $kecamatan = $this->cleanValue($row['kecamatan'] ?? null);
            $kabupaten = $this->cleanValue($row['kabupaten'] ?? $row['kabupaten_kota'] ?? null);
            $provinsi  = $this->cleanValue($row['provinsi'] ?? null);

            $sd        = $this->cleanValue($row['pendidikan_sd'] ?? $row['sd'] ?? null);
            $lulusSd   = $this->cleanValue($row['tahun_lulus_sd'] ?? null);
            $smp       = $this->cleanValue($row['pendidikan_smp'] ?? $row['smp'] ?? null);
            $lulusSmp  = $this->cleanValue($row['tahun_lulus_smp'] ?? null);

            // Cek kesamaan data (apakah siswa ini sudah ada di database)
            $existing = User::where('role', 'siswa')
                ->where(function ($q) use ($nis, $username, $email) {
                    $q->where('nip', $nis)
                      ->orWhere('username', $username)
                      ->orWhere('email', $email);
                })->first();

            if ($existing) {
                // JIKA DATA SUDAH ADA: Tindakan sesuai pilihan user (acc timpa / jangan timpa / hapus ganti baru)
                if ($this->duplicateAction === 'skip') {
                    $this->skippedCount++;
                    $this->matchedRecords[] = [
                        'row'        => $rowNumber,
                        'name'       => $existing->name,
                        'identifier' => $nis,
                        'action'     => 'Dilewati (Tidak Ditimpa)',
                    ];
                    continue;
                }

                if ($this->duplicateAction === 'replace') {
                    // Hapus data lama lalu buat baru
                    $oldName = $existing->name;
                    $existing->delete();

                    $this->createUserRecord([
                        'name'           => $name,
                        'username'       => $this->ensureUniqueUsername($username),
                        'email'          => $this->ensureUniqueEmail($email),
                        'password'       => Hash::make($plainPassword),
                        'password_plain' => $plainPassword,
                        'role'           => 'siswa',
                        'nip'            => $nis,
                        'nama_lengkap'   => $name,
                        'no_hp'          => $noHpSiswa,
                        'nama_ayah'      => $namaAyah,
                        'nama_ibu'       => $namaIbu,
                        'no_hp_ortu'     => $noHpOrtu,
                        'jenis_kelamin'  => $jk,
                        'alamat'         => $alamat,
                        'desa'           => $desa,
                        'kecamatan'      => $kecamatan,
                        'kabupaten'      => $kabupaten,
                        'provinsi'       => $provinsi,
                        'pendidikan_sd'  => $sd,
                        'tahun_lulus_sd' => $lulusSd,
                        'pendidikan_smp' => $smp,
                        'tahun_lulus_smp'=> $lulusSmp,
                        'is_active'      => true,
                    ]);

                    $this->replacedCount++;
                    $this->matchedRecords[] = [
                        'row'        => $rowNumber,
                        'name'       => "{$oldName} ➔ {$name}",
                        'identifier' => $nis,
                        'action'     => 'Dihapus & Diganti Baru',
                    ];
                    continue;
                }

                // Default: TIMPA / UPDATE DATA LAMA
                $updateData = [
                    'name'          => $name,
                    'nama_lengkap'  => $name,
                    'nip'           => $nis,
                    'is_active'     => true,
                ];

                if (!empty($noHpSiswa)) $updateData['no_hp'] = $noHpSiswa;
                if (!empty($namaAyah))  $updateData['nama_ayah'] = $namaAyah;
                if (!empty($namaIbu))   $updateData['nama_ibu'] = $namaIbu;
                if (!empty($noHpOrtu))  $updateData['no_hp_ortu'] = $noHpOrtu;
                if (!empty($jk))        $updateData['jenis_kelamin'] = $jk;
                if (!empty($alamat))    $updateData['alamat'] = $alamat;
                if (!empty($desa))      $updateData['desa'] = $desa;
                if (!empty($kecamatan)) $updateData['kecamatan'] = $kecamatan;
                if (!empty($kabupaten)) $updateData['kabupaten'] = $kabupaten;
                if (!empty($provinsi))  $updateData['provinsi'] = $provinsi;
                if (!empty($sd))        $updateData['pendidikan_sd'] = $sd;
                if (!empty($lulusSd))   $updateData['tahun_lulus_sd'] = $lulusSd;
                if (!empty($smp))       $updateData['pendidikan_smp'] = $smp;
                if (!empty($lulusSmp))  $updateData['tahun_lulus_smp'] = $lulusSmp;

                // Hanya update password jika diisi eksplisit di Excel
                if (!empty($row['password'])) {
                    $updateData['password'] = Hash::make($plainPassword);
                    $updateData['password_plain'] = $plainPassword;
                }

                $existing->update($updateData);
                $this->processedUserIds[] = $existing->id;
                $this->updatedCount++;
                $this->matchedRecords[] = [
                    'row'        => $rowNumber,
                    'name'       => $name,
                    'identifier' => $nis,
                    'action'     => 'Ditimpa / Diperbarui',
                ];
                continue;
            }

            // DATA BARU: Masukkan data sesuai template
            $this->createUserRecord([
                'name'           => $name,
                'username'       => $this->ensureUniqueUsername($username),
                'email'          => $this->ensureUniqueEmail($email),
                'password'       => Hash::make($plainPassword),
                'password_plain' => $plainPassword,
                'role'           => 'siswa',
                'nip'            => $nis,
                'nama_lengkap'   => $name,
                'no_hp'          => $noHpSiswa,
                'nama_ayah'      => $namaAyah,
                'nama_ibu'       => $namaIbu,
                'no_hp_ortu'     => $noHpOrtu,
                'jenis_kelamin'  => $jk,
                'alamat'         => $alamat,
                'desa'           => $desa,
                'kecamatan'      => $kecamatan,
                'kabupaten'      => $kabupaten,
                'provinsi'       => $provinsi,
                'pendidikan_sd'  => $sd,
                'tahun_lulus_sd' => $lulusSd,
                'pendidikan_smp' => $smp,
                'tahun_lulus_smp'=> $lulusSmp,
                'is_active'      => true,
            ]);

            $this->createdCount++;
        }
    }

    protected function createUserRecord(array $data): User
    {
        $user = User::create($data);
        $this->processedUserIds[] = $user->id;
        return $user;
    }

    protected function cleanValue($val): ?string
    {
        if ($val === null) return null;
        $t = trim((string)$val);
        return $t !== '' ? $t : null;
    }

    protected function parseJenisKelamin($val): ?string
    {
        if (!$val) return null;
        $val = strtolower(trim((string)$val));
        if (in_array($val, ['l', 'laki-laki', 'laki', 'male', 'm', 'pria'])) return 'L';
        if (in_array($val, ['p', 'perempuan', 'female', 'f', 'wanita'])) return 'P';
        return null;
    }

    protected function ensureUniqueUsername(string $username): string
    {
        $candidate = $username;
        $counter = 1;
        while (User::where('username', $candidate)->exists()) {
            $candidate = $username . '_' . $counter++;
        }
        return $candidate;
    }

    protected function ensureUniqueEmail(string $email): string
    {
        $candidate = $email;
        $counter = 1;
        while (User::where('email', $candidate)->exists()) {
            $parts = explode('@', $email);
            $candidate = $parts[0] . '_' . $counter++ . '@' . ($parts[1] ?? 'siswa.hilaledu.sch.id');
        }
        return $candidate;
    }

    public function getCreatedCount(): int { return $this->createdCount; }
    public function getUpdatedCount(): int { return $this->updatedCount; }
    public function getSkippedCount(): int { return $this->skippedCount; }
    public function getReplacedCount(): int { return $this->replacedCount; }
    public function getMatchedRecords(): array { return $this->matchedRecords; }
    public function getErrors(): array { return $this->errors; }
    public function getProcessedUserIds(): array { return $this->processedUserIds; }
}
