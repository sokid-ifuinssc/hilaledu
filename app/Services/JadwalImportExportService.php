<?php

namespace App\Services;

use App\Models\JadwalPelajaran;
use App\Models\MataPelajaran;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JadwalImportExportService
{
    /**
     * Download template kosong dengan contoh pengisian valid
     */
    public function downloadTemplate(string $format = 'xlsx'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Jadwal');

        $headers = [
            'Hari',
            'Jam Mulai',
            'Jam Selesai',
            'Kelas',
            'Kode Mapel',
            'Nama Mapel',
            'Username/NIP Guru',
            'Nama Guru',
            'Ruang',
            'Tahun Ajaran',
            'Semester',
        ];

        // Header Styling
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $sheet->getStyle('A1:K1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:K1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F766E');
        $sheet->getStyle('A1:K1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Contoh Data
        $currentTa = \App\Models\PengaturanSekolah::getActiveTahunAjaran();
        $sampleData = [
            ['Senin', '07:30', '09:30', 'X TKJT', 'MP-TKJT-01', 'Dasar-Dasar TKJT', 'sokidbae', 'Sokid, ST, M.Kom', 'Lab Komputer 1', $currentTa, 'ganjil'],
            ['Senin', '09:45', '11:45', 'XI AKL', 'MP-AKL-01', 'Praktikum Akuntansi Perusahaan', 'rizkidwisafitri', 'Rizki Dwi Safitri, S.Pd', 'Ruang Teori AKL', $currentTa, 'ganjil'],
            ['Selasa', '07:30', '09:30', 'XII TO', 'MP-TO-01', 'Pemeliharaan Mesin Kendaraan', 'nidzomuddin', 'NIDZOMUDDIN, Amd', 'Bengkel Otomotif', $currentTa, 'ganjil'],
            ['Rabu', '08:00', '10:00', 'X TKJT', 'MP-UMUM-01', 'Pendidikan Agama Islam', 'syarif01', 'Syarif Hidayatullah, S.Pd', 'Ruang Kelas 1', $currentTa, 'ganjil'],
        ];

        $row = 2;
        foreach ($sampleData as $data) {
            $col = 'A';
            foreach ($data as $val) {
                $sheet->setCellValue($col . $row, $val);
                $col++;
            }
            $row++;
        }

        $filename = 'template_jadwal_pelajaran_' . date('Ymd') . '.' . $format;

        return response()->streamDownload(function () use ($spreadsheet, $format) {
            if ($format === 'csv') {
                $writer = new Csv($spreadsheet);
                $writer->setUseBOM(true);
            } else {
                $writer = new Xlsx($spreadsheet);
            }
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export data jadwal ke Excel/CSV
     */
    public function export($jadwals, string $format = 'xlsx'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jadwal Pelajaran');

        $headers = [
            'No',
            'Hari',
            'Jam Mulai',
            'Jam Selesai',
            'Kelas',
            'Kode Mapel',
            'Mata Pelajaran',
            'NIP/Username Guru',
            'Nama Guru Pengajar',
            'Ruang',
            'Tahun Ajaran',
            'Semester',
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $sheet->getStyle('A1:L1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:L1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('047857');
        $sheet->getStyle('A1:L1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 2;
        $no = 1;
        foreach ($jadwals as $j) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $j->hari);
            $sheet->setCellValue('C' . $row, substr($j->jam_mulai, 0, 5));
            $sheet->setCellValue('D' . $row, substr($j->jam_selesai, 0, 5));
            $sheet->setCellValue('E' . $row, $j->kelas);
            $sheet->setCellValue('F' . $row, $j->mataPelajaran->kode ?? '-');
            $sheet->setCellValue('G' . $row, $j->mataPelajaran->nama ?? '-');
            $sheet->setCellValue('H' . $row, $j->guru->nip ?? $j->guru->username ?? '-');
            $sheet->setCellValue('I' . $row, $j->guru->name ?? '-');
            $sheet->setCellValue('J' . $row, $j->ruang ?? 'Ruang Kelas');
            $sheet->setCellValue('K' . $row, $j->tahun_ajaran);
            $sheet->setCellValue('L' . $row, ucfirst($j->semester));
            $row++;
        }

        $filename = 'export_jadwal_pelajaran_' . date('Ymd_His') . '.' . $format;

        return response()->streamDownload(function () use ($spreadsheet, $format) {
            if ($format === 'csv') {
                $writer = new Csv($spreadsheet);
                $writer->setUseBOM(true);
            } else {
                $writer = new Xlsx($spreadsheet);
            }
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // IMPORT JADWAL
    // ─────────────────────────────────────────────────────────────────────────

    const EXPECTED_HEADERS = [
        'Hari',
        'Jam Mulai',
        'Jam Selesai',
        'Kelas',
        'Kode Mapel',
        'Nama Mapel',
        'Username/NIP Guru',
        'Nama Guru',
        'Ruang',
        'Tahun Ajaran',
        'Semester',
    ];

    const HEADER_MAP = [
        'hari'         => ['hari', 'day'],
        'jam_mulai'    => ['jam mulai', 'jam_mulai', 'mulai', 'waktu mulai', 'start'],
        'jam_selesai'  => ['jam selesai', 'jam_selesai', 'selesai', 'waktu selesai', 'end'],
        'kelas'        => ['kelas', 'rombel'],
        'kode_mapel'   => ['kode mapel', 'kode', 'kode_mapel', 'mapel code'],
        'nama_mapel'   => ['nama mapel', 'mata pelajaran', 'nama mata pelajaran', 'mapel'],
        'user_guru'    => ['username/nip guru', 'nip/username guru', 'username guru', 'nip guru', 'nip/username', 'username/nip', 'nip'],
        'nama_guru'    => ['nama guru pengajar', 'nama guru', 'guru pengajar', 'nama pengajar', 'guru'],
        'ruang'        => ['ruang', 'ruangan', 'tempat'],
        'tahun_ajaran' => ['tahun ajaran', 'tahun pelajaran', 'ta'],
        'semester'     => ['semester', 'smt'],
    ];

    /**
     * Import jadwal pelajaran dari file Excel/CSV
     */
    public function import($file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) === 0) {
            return [
                'success' => 0,
                'errors'  => ['❌ File kosong. Silakan gunakan template yang telah disediakan.'],
            ];
        }

        // ── Baca & petakan header ke index kolom ──────────────────────────
        $rawHeader = array_map(fn($v) => strtolower(trim((string)($v ?? ''))), $rows[0]);
        $colMap    = $this->mapHeaderColumns($rawHeader);

        // Validasi kolom wajib: minimal hari, jam_mulai, jam_selesai, kelas
        if (!isset($colMap['hari']) || !isset($colMap['jam_mulai']) || !isset($colMap['jam_selesai']) || !isset($colMap['kelas'])) {
            $errors = ['❌ Kolom wajib jadwal tidak ditemukan di file yang diunggah.'];
            $detected = array_filter(array_map('ucfirst', $rawHeader));
            $errors[] = 'Header yang terdeteksi: ' . (empty($detected) ? '(tidak ada)' : implode(' | ', $detected));
            if (!isset($colMap['hari'])) $errors[] = '❌ Kolom "Hari" tidak ditemukan.';
            if (!isset($colMap['jam_mulai'])) $errors[] = '❌ Kolom "Jam Mulai" tidak ditemukan.';
            if (!isset($colMap['jam_selesai'])) $errors[] = '❌ Kolom "Jam Selesai" tidak ditemukan.';
            if (!isset($colMap['kelas'])) $errors[] = '❌ Kolom "Kelas" tidak ditemukan.';
            $errors[] = '--- Format Kolom yang Diharapkan (Gunakan tombol Template) ---';
            $errors[] = implode(' | ', self::EXPECTED_HEADERS);
            $errors[] = 'ℹ️ File Export Jadwal juga dapat langsung diimport kembali secara otomatis.';
            return ['success' => 0, 'errors' => $errors];
        }

        if (count($rows) <= 1) {
            return [
                'success' => 0,
                'errors'  => ['⚠️ File hanya berisi baris judul tanpa baris data jadwal.'],
            ];
        }

        $successCount = 0;
        $errors = [];
        $validDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        for ($i = 1; $i < count($rows); $i++) {
            $rowNum = $i + 1;
            $row = $rows[$i];

            // Cek jika baris kosong
            if (empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $hari         = trim((string)($row[$colMap['hari']] ?? ''));
            $jamMulai     = trim((string)($row[$colMap['jam_mulai']] ?? ''));
            $jamSelesai   = trim((string)($row[$colMap['jam_selesai']] ?? ''));
            $kelas        = trim((string)($row[$colMap['kelas']] ?? ''));
            $kodeMapel    = strtoupper(trim((string)($row[$colMap['kode_mapel'] ?? -1] ?? '')));
            $namaMapel    = trim((string)($row[$colMap['nama_mapel'] ?? -1] ?? ''));
            $userGuru     = trim((string)($row[$colMap['user_guru'] ?? -1] ?? ''));
            $namaGuru     = trim((string)($row[$colMap['nama_guru'] ?? -1] ?? ''));
            $ruang        = trim((string)($row[$colMap['ruang'] ?? -1] ?? 'Ruang Kelas'));
            $tahunAjaran  = trim((string)($row[$colMap['tahun_ajaran'] ?? -1] ?? \App\Models\PengaturanSekolah::getActiveTahunAjaran()));
            $semester     = strtolower(trim((string)($row[$colMap['semester'] ?? -1] ?? \App\Models\PengaturanSekolah::getActiveSemester())));

            // Validasi Hari
            $hariCapital = ucfirst(strtolower($hari));
            if (!in_array($hariCapital, $validDays)) {
                $errors[] = "⚠️ Baris {$rowNum}: Hari '{$hari}' tidak valid. Harus salah satu dari: " . implode(', ', $validDays);
                continue;
            }

            // Normalisasi Jam
            $jamMulaiNorm = $this->normalizeTime($jamMulai);
            $jamSelesaiNorm = $this->normalizeTime($jamSelesai);
            if (!$jamMulaiNorm || !$jamSelesaiNorm) {
                $errors[] = "⚠️ Baris {$rowNum}: Format jam mulai '{$jamMulai}' atau jam selesai '{$jamSelesai}' tidak valid (Gunakan format JJ:MM).";
                continue;
            }

            if ($jamMulaiNorm >= $jamSelesaiNorm) {
                $errors[] = "⚠️ Baris {$rowNum}: Jam selesai harus lebih besar dari jam mulai ({$jamMulaiNorm} s/d {$jamSelesaiNorm}).";
                continue;
            }

            if (empty($kelas)) {
                $errors[] = "⚠️ Baris {$rowNum}: Kolom Kelas tidak boleh kosong.";
                continue;
            }

            // Cari Mata Pelajaran
            $mapel = null;
            if (!empty($kodeMapel)) {
                $mapel = MataPelajaran::where('kode', $kodeMapel)->first();
            }
            if (!$mapel && !empty($namaMapel)) {
                $mapel = MataPelajaran::where('nama', 'like', "%{$namaMapel}%")->first();
            }
            if (!$mapel) {
                // Auto create mapel jika belum ada
                if (!empty($kodeMapel) && !empty($namaMapel)) {
                    $mapel = MataPelajaran::create([
                        'kode'           => strtoupper($kodeMapel),
                        'nama'           => $namaMapel,
                        'kelompok'       => 'umum',
                        'tingkat'        => 'semua',
                        'jam_per_minggu' => 2,
                        'is_aktif'       => true,
                    ]);
                } else {
                    $errors[] = "⚠️ Baris {$rowNum}: Mata Pelajaran '{$kodeMapel} - {$namaMapel}' tidak ditemukan.";
                    continue;
                }
            }

            // Cari Guru Pengajar
            $guru = null;
            if (!empty($userGuru)) {
                $guru = User::where('username', $userGuru)
                    ->orWhere('nip', $userGuru)
                    ->first();
            }
            if (!$guru && !empty($namaGuru)) {
                $guru = User::where('role', 'guru')
                    ->where('name', 'like', "%{$namaGuru}%")
                    ->first();
            }
            if (!$guru) {
                $errors[] = "⚠️ Baris {$rowNum}: Guru '{$userGuru} / {$namaGuru}' tidak ditemukan di sistem.";
                continue;
            }

            // Simpan / Update Jadwal
            JadwalPelajaran::updateOrCreate(
                [
                    'hari'              => $hariCapital,
                    'kelas'             => $kelas,
                    'jam_mulai'         => $jamMulaiNorm,
                    'tahun_ajaran'      => $tahunAjaran ?: \App\Models\PengaturanSekolah::getActiveTahunAjaran(),
                    'semester'          => in_array($semester, ['ganjil', 'genap']) ? $semester : \App\Models\PengaturanSekolah::getActiveSemester(),
                ],
                [
                    'jam_selesai'       => $jamSelesaiNorm,
                    'mata_pelajaran_id' => $mapel->id,
                    'guru_user_id'      => $guru->id,
                    'ruang'             => $ruang ?: 'Ruang Kelas',
                ]
            );

            $successCount++;
        }

        return [
            'success' => $successCount,
            'errors'  => $errors,
        ];
    }

    /**
     * Memetakan nama header ke index kolom berdasarkan alias yang dikenali.
     */
    private function mapHeaderColumns(array $headerRow): array
    {
        $colMap = [];
        $usedIndices = [];

        $normalizedHeaders = [];
        foreach ($headerRow as $idx => $val) {
            $normalizedHeaders[$idx] = strtolower(trim((string)$val));
        }

        // Pass 1: Exact matches (prioritas tertinggi)
        foreach (self::HEADER_MAP as $field => $aliases) {
            foreach ($normalizedHeaders as $idx => $headerVal) {
                if (in_array($idx, $usedIndices)) continue;
                foreach ($aliases as $alias) {
                    if ($headerVal === strtolower($alias)) {
                        $colMap[$field] = $idx;
                        $usedIndices[] = $idx;
                        break 2;
                    }
                }
            }
        }

        // Pass 2: Partial/substring matches untuk kolom yang belum terpetakan
        foreach (self::HEADER_MAP as $field => $aliases) {
            if (isset($colMap[$field])) continue;
            foreach ($normalizedHeaders as $idx => $headerVal) {
                if (in_array($idx, $usedIndices) || empty($headerVal)) continue;
                foreach ($aliases as $alias) {
                    $alias = strtolower($alias);
                    if (strlen($alias) >= 3 && str_contains($headerVal, $alias)) {
                        $colMap[$field] = $idx;
                        $usedIndices[] = $idx;
                        break 2;
                    }
                }
            }
        }

        return $colMap;
    }

    private function normalizeTime(string $time): ?string
    {
        $time = trim($time);
        if (is_numeric($time) && (float)$time > 0 && (float)$time < 1) {
            $seconds = (int) round((float)$time * 86400);
            return gmdate('H:i:s', $seconds);
        }
        if (preg_match('/^(\d{1,2})[:.](\d{2})/', $time, $matches)) {
            $h = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $m = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            return "{$h}:{$m}:00";
        }
        return null;
    }
}
