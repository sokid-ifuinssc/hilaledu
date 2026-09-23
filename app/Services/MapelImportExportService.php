<?php

namespace App\Services;

use App\Models\MataPelajaran;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MapelImportExportService
{
    /**
     * Download template mata pelajaran
     */
    public function downloadTemplate(string $format = 'xlsx'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Mapel');

        $headers = [
            'Kode Mapel',
            'Nama Mata Pelajaran',
            'Kelompok',
            'Tingkat Kelas',
            'Jam Pelajaran (JP/Minggu)',
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $sheet->getStyle('A1:E1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F766E');
        $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sampleData = [
            ['MP-TKJT-02', 'Administrasi Infrastruktur Jaringan', 'kejuruan', 'XI', 4],
            ['MP-AKL-02', 'Akuntansi Keuangan Lembaga', 'kejuruan', 'XI', 4],
            ['MP-TO-02', 'Pemeliharaan Kelistrikan Kendaraan Ringan', 'kejuruan', 'XII', 4],
            ['MP-UMUM-05', 'Bahasa Inggris Vokasi', 'umum', 'semua', 2],
            ['MP-MULOK-02', 'Bahasa & Budaya Cirebon', 'muatan_lokal', 'X', 2],
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

        $filename = 'template_mata_pelajaran_' . date('Ymd') . '.' . $format;

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
     * Export data mata pelajaran
     */
    public function export($mapels, string $format = 'xlsx'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Mata Pelajaran');

        $headers = [
            'No',
            'Kode Mapel',
            'Nama Mata Pelajaran',
            'Kelompok (umum/kejuruan/muatan_lokal)',
            'Tingkat Kelas (X/XI/XII/semua)',
            'Beban JP / Minggu',
            'Status Aktif',
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $sheet->getStyle('A1:G1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('047857');
        $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 2;
        $no = 1;
        foreach ($mapels as $m) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $m->kode);
            $sheet->setCellValue('C' . $row, $m->nama);
            $sheet->setCellValue('D' . $row, ucfirst(str_replace('_', ' ', $m->kelompok)));
            $sheet->setCellValue('E' . $row, $m->tingkat == 'semua' ? 'Semua Tingkat' : "Kelas {$m->tingkat}");
            $sheet->setCellValue('F' . $row, $m->jam_per_minggu);
            $sheet->setCellValue('G' . $row, $m->is_aktif ? 'Aktif' : 'Non-Aktif');
            $row++;
        }

        $filename = 'export_mata_pelajaran_' . date('Ymd_His') . '.' . $format;

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
    // IMPORT
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Kolom yang diharapkan di template (urutan: A, B, C, D, E)
     */
    const EXPECTED_HEADERS = [
        0 => 'Kode Mapel',
        1 => 'Nama Mata Pelajaran',
        2 => 'Kelompok',
        3 => 'Tingkat Kelas',
        4 => 'Jam Pelajaran (JP/Minggu)',
    ];

    /**
     * Peta alias nama header yang dikenali sistem
     * Key = nama field internal, Value = array alias yang mungkin digunakan
     */
    const HEADER_MAP = [
        'kode'     => ['kode mapel', 'kode', 'kode_mapel', 'mapel code', 'code'],
        'nama'     => ['nama mata pelajaran', 'nama', 'nama mapel', 'mata pelajaran', 'nama_mapel', 'nama_mata_pelajaran'],
        'kelompok' => ['kelompok', 'kelompok (umum/kejuruan/muatan_lokal)', 'group', 'kategori'],
        'tingkat'  => ['tingkat kelas', 'tingkat', 'tingkat kelas (x/xi/xii/semua)', 'jenjang', 'kelas'],
        'jp'       => ['jam pelajaran (jp/minggu)', 'jp/minggu', 'jam per minggu', 'jam', 'beban jp / minggu', 'jp'],
    ];

    /**
     * Import mata pelajaran.
     *
     * Membaca header baris pertama untuk memetakan kolom secara otomatis.
     * Mendukung:
     *   - File Template  : A=Kode Mapel, B=Nama Mata Pelajaran, C=Kelompok, D=Tingkat, E=JP
     *   - File Export    : A=No, B=Kode Mapel, C=Nama Mata Pelajaran, D=Kelompok, E=Tingkat, F=JP, G=Status
     */
    public function import($file, $duplicateAction = 'update'): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray();

        if (count($rows) === 0) {
            return [
                'success' => 0,
                'errors'  => ['❌ File kosong. Silakan gunakan template yang telah disediakan.'],
            ];
        }

        // ── Baca & petakan header ke index kolom ──────────────────────────
        $rawHeader = array_map(fn($v) => strtolower(trim((string)($v ?? ''))), $rows[0]);
        $colMap    = $this->mapHeaderColumns($rawHeader);

        // Pastikan minimal kolom kode dan nama ditemukan
        if (!isset($colMap['kode']) || !isset($colMap['nama'])) {
            $errors   = ['❌ Kolom wajib tidak ditemukan di file yang diunggah.'];
            $detected = array_filter(array_map('ucfirst', $rawHeader));
            $errors[] = 'Header yang terdeteksi: ' . (empty($detected) ? '(tidak ada)' : implode(' | ', $detected));
            if (!isset($colMap['kode'])) {
                $errors[] = '❌ Kolom KODE MAPEL tidak ditemukan. Pastikan ada kolom dengan nama "Kode Mapel" atau "Kode".';
            }
            if (!isset($colMap['nama'])) {
                $errors[] = '❌ Kolom NAMA MATA PELAJARAN tidak ditemukan. Pastikan ada kolom "Nama Mata Pelajaran" atau "Nama Mapel".';
            }
            $errors[] = '--- Template yang diharapkan (gunakan tombol Download Template) ---';
            foreach (self::EXPECTED_HEADERS as $idx => $label) {
                $errors[] = 'Kolom ' . chr(65 + $idx) . ': ' . $label;
            }
            $errors[] = 'ℹ️ File Export dapat langsung diimport kembali — sistem akan mengenali kolomnya otomatis.';
            return ['success' => 0, 'errors' => $errors];
        }

        if (count($rows) <= 1) {
            return [
                'success' => 0,
                'errors'  => ['⚠️ File hanya berisi baris judul tanpa data. Silakan isi data mata pelajaran.'],
            ];
        }

        $successCount       = 0;
        $errors             = [];
        $validKelompok      = ['umum', 'kejuruan', 'muatan_lokal'];
        $validKelompokLabel = implode(', ', $validKelompok);

        for ($i = 1; $i < count($rows); $i++) {
            $rowNum = $i + 1;
            $row    = $rows[$i];

            // Lewati baris kosong
            if (empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) {
                continue;
            }

            // Ambil nilai berdasarkan nama kolom (bukan posisi index)
            $kode     = strtoupper(trim((string)($row[$colMap['kode']]           ?? '')));
            $nama     = trim((string)($row[$colMap['nama']]                       ?? ''));
            $kelompok = strtolower(str_replace([' ', '-'], '_', trim((string)($row[$colMap['kelompok'] ?? -1] ?? 'umum'))));
            $tingkat  = strtoupper(trim((string)($row[$colMap['tingkat']  ?? -1] ?? 'semua')));
            $jp       = max(1, (int)($row[$colMap['jp'] ?? -1] ?? 2));

            // Deteksi cerdas jika kolom kode dan nama tertukar (misal kode berisi teks nama panjang)
            if (mb_strlen($kode) > 25 && (empty($nama) || (mb_strlen($nama) <= 20 && !str_contains($nama, ' ')))) {
                $temp = $kode;
                $kode = strtoupper($nama);
                $nama = $temp;
                if (empty($kode)) {
                    $words = explode(' ', $nama);
                    $initials = '';
                    foreach ($words as $w) {
                        if (!empty($w)) $initials .= strtoupper(substr($w, 0, 1));
                    }
                    $kode = 'MP-' . substr($initials, 0, 8);
                }
            }

            // Validasi wajib
            if (empty($kode)) {
                $errors[] = "⚠️ Baris {$rowNum}: Kolom 'Kode Mapel' kosong. Wajib diisi (contoh: MP-TKJT-01).";
                continue;
            }

            if (mb_strlen($kode) > 30) {
                $errors[] = "⚠️ Baris {$rowNum}: Kolom 'Kode Mapel' berisi \"{$kode}\" (terlalu panjang, maksimal 20 karakter). Mohon pastikan kolom 'Kode Mapel' dan 'Nama Mata Pelajaran' tidak tertukar.";
                continue;
            }

            if (empty($nama)) {
                $errors[] = "⚠️ Baris {$rowNum} [Kode: {$kode}]: Kolom 'Nama Mata Pelajaran' kosong. Wajib diisi.";
                continue;
            }

            // Validasi kelompok
            if (!in_array($kelompok, $validKelompok)) {
                $kelompokAsli = $row[$colMap['kelompok'] ?? -1] ?? '';
                $errors[] = "⚠️ Baris {$rowNum} [{$kode}]: Nilai Kelompok \"{$kelompokAsli}\" tidak valid. "
                    . "Gunakan: {$validKelompokLabel}. Baris ini dilewati.";
                continue;
            }

            // Normalisasi tingkat: terima "Semua Tingkat" (dari export) maupun "semua"
            $tingkatNorm = str_replace(['SEMUA TINGKAT', 'KELAS '], ['semua', ''], $tingkat);
            if (!in_array($tingkatNorm, ['X', 'XI', 'XII', 'SEMUA', 'semua'])) {
                $tingkatAsli  = $row[$colMap['tingkat'] ?? -1] ?? '';
                $errors[] = "⚠️ Baris {$rowNum} [{$kode}]: Tingkat Kelas \"{$tingkatAsli}\" tidak valid. "
                    . "Gunakan: X, XI, XII, atau semua. Menggunakan default 'semua'.";
                $tingkat = 'semua';
            } else {
                $tingkat = strtolower($tingkatNorm) === 'semua' ? 'semua' : strtoupper($tingkatNorm);
            }

            // Penanganan Aksi Duplikat
            $existing = MataPelajaran::where('kode', $kode)->first();
            if ($existing) {
                if ($duplicateAction === 'skip') {
                    $errors[] = "ℹ️ Baris {$rowNum} [{$kode}]: Kode Mapel sudah ada. Dilewati sesuai pilihan Anda.";
                    continue; // Skip data ini
                } elseif ($duplicateAction === 'add_new') {
                    // Buat kode baru agar unik (tambahkan suffix random/angka)
                    $suffix = 1;
                    $newKode = $kode . '-' . $suffix;
                    while (MataPelajaran::where('kode', $newKode)->exists()) {
                        $suffix++;
                        $newKode = $kode . '-' . $suffix;
                    }
                    $kode = $newKode;
                    $errors[] = "ℹ️ Baris {$rowNum}: Kode Mapel sudah ada. Disimpan sebagai mapel baru dengan kode '{$kode}'.";
                }
            }

            MataPelajaran::updateOrCreate(
                ['kode' => $kode],
                [
                    'nama'           => $nama,
                    'kelompok'       => $kelompok,
                    'tingkat'        => $tingkat,
                    'jam_per_minggu' => $jp,
                    'is_aktif'       => true,
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
     * Mendukung file template (header di baris 1) dan file export (ada kolom No, Status, dll).
     *
     * @param  array  $headerRow  Array header lowercase dari baris pertama
     * @return array  ['kode' => colIdx, 'nama' => colIdx, ...]
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
}
