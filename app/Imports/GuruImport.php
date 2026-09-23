<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class GuruImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
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
            $rowNumber = $index + 2;

            // Ekstrak NUPTK (kolom wajib mutlak bagi guru)
            $nuptk = trim((string)($row['nuptk'] ?? $row['nip'] ?? $row['no_nuptk'] ?? $row['id_guru'] ?? ''));

            // Validasi NUPTK: Jika kosong, tolak baris ini dan beritahukan kolom serta kesalahannya
            if (empty($nuptk)) {
                $this->errors[] = [
                    'row'        => $rowNumber,
                    'column'     => 'NUPTK',
                    'identifier' => '-',
                    'message'    => 'Kolom NUPTK kosong. NUPTK wajib diisi sebagai identitas unik guru.',
                ];
                continue;
            }

            // Ekstrak Nama Lengkap (jika kosong, fallback otomatis)
            $name = trim((string)($row['nama_lengkap'] ?? $row['nama'] ?? $row['nama_guru'] ?? ''));
            if (empty($name)) {
                $name = 'Guru ' . $nuptk;
            }

            // Ekstrak Username (jika kosong, buat otomatis dari NUPTK)
            $username = trim((string)($row['username'] ?? $row['user'] ?? ''));
            if (empty($username)) {
                $cleanNuptk = preg_replace('/[^a-zA-Z0-9]/', '', $nuptk);
                $username = 'guru_' . $cleanNuptk;
            } else {
                $username = preg_replace('/[^a-zA-Z0-9_.-]/', '', strtolower($username));
            }

            // Ekstrak Email (jika kosong, buat email sekolah otomatis dari NUPTK)
            $email = trim((string)($row['email'] ?? $row['e_mail'] ?? ''));
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $cleanNuptk = preg_replace('/[^a-zA-Z0-9]/', '', $nuptk);
                $email = $cleanNuptk . '@guru.hilaledu.sch.id';
            }

            // Ekstrak Password (default password123 jika kosong)
            $plainPassword = trim((string)($row['password'] ?? $row['kata_sandi'] ?? ''));
            if (empty($plainPassword)) {
                $plainPassword = 'password123';
            }

            // Ekstrak data opsional lainnya sesuai template
            $noHp         = $this->cleanValue($row['no_hp'] ?? $row['telepon'] ?? null);
            $jk           = $this->parseJenisKelamin($row['jenis_kelamin'] ?? $row['jk'] ?? null);
            $tahunMasuk   = $this->cleanValue($row['tahun_masuk'] ?? null);
            $lulusanTahun = $this->cleanValue($row['lulusan_tahun'] ?? null);

            $alamat    = $this->cleanValue($row['alamat'] ?? null);
            $desa      = $this->cleanValue($row['desa'] ?? $row['desa_kelurahan'] ?? null);
            $kecamatan = $this->cleanValue($row['kecamatan'] ?? null);
            $kabupaten = $this->cleanValue($row['kabupaten'] ?? $row['kabupaten_kota'] ?? null);
            $provinsi  = $this->cleanValue($row['provinsi'] ?? null);

            $sd        = $this->cleanValue($row['pendidikan_sd'] ?? $row['sd'] ?? null);
            $lulusSd   = $this->cleanValue($row['tahun_lulus_sd'] ?? null);
            $smp       = $this->cleanValue($row['pendidikan_smp'] ?? $row['smp'] ?? null);
            $lulusSmp  = $this->cleanValue($row['tahun_lulus_smp'] ?? null);
            $sma       = $this->cleanValue($row['pendidikan_sma'] ?? $row['sma'] ?? null);
            $lulusSma  = $this->cleanValue($row['tahun_lulus_sma'] ?? null);
            $s1        = $this->cleanValue($row['pendidikan_s1'] ?? $row['s1'] ?? null);
            $lulusS1   = $this->cleanValue($row['tahun_lulus_s1'] ?? null);
            $s2        = $this->cleanValue($row['pendidikan_s2'] ?? $row['s2'] ?? null);
            $lulusS2   = $this->cleanValue($row['tahun_lulus_s2'] ?? null);

            $tugasTambahanRaw = $row['tugas_tambahan_jabatan'] ?? $row['tugas_tambahan'] ?? $row['jabatan'] ?? $row['jabatan_tugas_tambahan'] ?? null;
            $tugasTambahan    = $this->parseTugasTambahan($tugasTambahanRaw);
            $jabatanUtama     = !empty($tugasTambahan) ? $tugasTambahan[0] : 'Guru';

            // Cek kesamaan data (apakah guru ini sudah ada di database)
            $existing = User::where('role', 'guru')
                ->where(function ($q) use ($nuptk, $username, $email) {
                    $q->where('nip', $nuptk)
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
                        'identifier' => $nuptk,
                        'action'     => 'Dilewati (Tidak Ditimpa)',
                    ];
                    continue;
                }

                if ($this->duplicateAction === 'replace') {
                    $oldName = $existing->name;
                    $existing->delete();

                    $this->createUserRecord([
                        'name'           => $name,
                        'username'       => $this->ensureUniqueUsername($username),
                        'email'          => $this->ensureUniqueEmail($email),
                        'password'       => Hash::make($plainPassword),
                        'password_plain' => $plainPassword,
                        'role'           => 'guru',
                        'nip'            => $nuptk,
                        'nama_lengkap'   => $name,
                        'no_hp'          => $noHp,
                        'jenis_kelamin'  => $jk,
                        'tahun_masuk'    => $tahunMasuk,
                        'lulusan_tahun'  => $lulusanTahun,
                        'alamat'         => $alamat,
                        'desa'           => $desa,
                        'kecamatan'      => $kecamatan,
                        'kabupaten'      => $kabupaten,
                        'provinsi'       => $provinsi,
                        'pendidikan_sd'  => $sd,
                        'tahun_lulus_sd' => $lulusSd,
                        'pendidikan_smp' => $smp,
                        'tahun_lulus_smp'=> $lulusSmp,
                        'pendidikan_sma' => $sma,
                        'tahun_lulus_sma'=> $lulusSma,
                        'pendidikan_s1'  => $s1,
                        'tahun_lulus_s1' => $lulusS1,
                        'pendidikan_s2'  => $s2,
                        'tahun_lulus_s2' => $lulusS2,
                        'tugas_tambahan' => $tugasTambahan,
                        'jabatan_utama'  => $jabatanUtama,
                        'is_active'      => true,
                    ]);

                    $this->replacedCount++;
                    $this->matchedRecords[] = [
                        'row'        => $rowNumber,
                        'name'       => "{$oldName} ➔ {$name}",
                        'identifier' => $nuptk,
                        'action'     => 'Dihapus & Diganti Baru',
                    ];
                    continue;
                }

                // Default: TIMPA / UPDATE DATA LAMA
                $updateData = [
                    'name'         => $name,
                    'nama_lengkap' => $name,
                    'nip'          => $nuptk,
                    'is_active'    => true,
                ];

                if (!empty($noHp))         $updateData['no_hp'] = $noHp;
                if (!empty($jk))           $updateData['jenis_kelamin'] = $jk;
                if (!empty($tahunMasuk))   $updateData['tahun_masuk'] = $tahunMasuk;
                if (!empty($lulusanTahun)) $updateData['lulusan_tahun'] = $lulusanTahun;
                if (!empty($alamat))       $updateData['alamat'] = $alamat;
                if (!empty($desa))         $updateData['desa'] = $desa;
                if (!empty($kecamatan))    $updateData['kecamatan'] = $kecamatan;
                if (!empty($kabupaten))    $updateData['kabupaten'] = $kabupaten;
                if (!empty($provinsi))     $updateData['provinsi'] = $provinsi;
                if (!empty($sd))           $updateData['pendidikan_sd'] = $sd;
                if (!empty($lulusSd))      $updateData['tahun_lulus_sd'] = $lulusSd;
                if (!empty($smp))          $updateData['pendidikan_smp'] = $smp;
                if (!empty($lulusSmp))     $updateData['tahun_lulus_smp'] = $lulusSmp;
                if (!empty($sma))          $updateData['pendidikan_sma'] = $sma;
                if (!empty($lulusSma))     $updateData['tahun_lulus_sma'] = $lulusSma;
                if (!empty($s1))           $updateData['pendidikan_s1'] = $s1;
                if (!empty($lulusS1))      $updateData['tahun_lulus_s1'] = $lulusS1;
                if (!empty($s2))           $updateData['pendidikan_s2'] = $s2;
                if (!empty($lulusS2))      $updateData['tahun_lulus_s2'] = $lulusS2;

                if ($tugasTambahan !== null) {
                    $updateData['tugas_tambahan'] = $tugasTambahan;
                    $updateData['jabatan_utama']  = $jabatanUtama;
                }

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
                    'identifier' => $nuptk,
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
                'role'           => 'guru',
                'nip'            => $nuptk,
                'nama_lengkap'   => $name,
                'no_hp'          => $noHp,
                'jenis_kelamin'  => $jk,
                'tahun_masuk'    => $tahunMasuk,
                'lulusan_tahun'  => $lulusanTahun,
                'alamat'         => $alamat,
                'desa'           => $desa,
                'kecamatan'      => $kecamatan,
                'kabupaten'      => $kabupaten,
                'provinsi'       => $provinsi,
                'pendidikan_sd'  => $sd,
                'tahun_lulus_sd' => $lulusSd,
                'pendidikan_smp' => $smp,
                'tahun_lulus_smp'=> $lulusSmp,
                'pendidikan_sma' => $sma,
                'tahun_lulus_sma'=> $lulusSma,
                'pendidikan_s1'  => $s1,
                'tahun_lulus_s1' => $lulusS1,
                'pendidikan_s2'  => $s2,
                'tahun_lulus_s2' => $lulusS2,
                'tugas_tambahan' => $tugasTambahan,
                'jabatan_utama'  => $jabatanUtama,
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

    protected function parseTugasTambahan($val): ?array
    {
        if (!$val) return null;
        if (is_array($val)) return array_values(array_filter($val));
        $val = trim((string)$val);
        if ($val === '') return null;

        $items = preg_split('/[,;]+/', $val);
        $result = [];
        foreach ($items as $item) {
            $cleaned = trim($item);
            if (!empty($cleaned)) {
                $result[] = $cleaned;
            }
        }
        return !empty($result) ? array_values(array_unique($result)) : null;
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
            $candidate = $parts[0] . '_' . $counter++ . '@' . ($parts[1] ?? 'guru.hilaledu.sch.id');
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
