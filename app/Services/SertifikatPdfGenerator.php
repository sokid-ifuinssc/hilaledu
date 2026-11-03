<?php

namespace App\Services;

class SertifikatPdfGenerator
{
    /**
     * Generate file PDF A4 Landscape (841.89 x 595.28 pt) murni PHP tanpa dependensi eksternal.
     *
     * @param array $data [
     *   'kategori' => 'kepesantrenan'|'kewirausahaan',
     *   'nama_sekolah' => 'SMK PLUS AL HILAL CIREBON',
     *   'nomor_dokumen' => '421.5/001/SERT-AH/2026',
     *   'nama_siswa' => 'AHMAD FAUZI',
     *   'nisn' => '0081234567',
     *   'kelas' => 'XII TKJ 1',
     *   'nama_kegiatan' => 'Sertifikat Khatam Juz',
     *   'deskripsi_kegiatan' => 'Telah mengikuti dan menyelesaikan evaluasi kompetensi...',
     *   'predikat' => 'SANGAT BAIK',
     *   'tempat_tanggal' => 'Cirebon, 03 November 2026',
     *   'pejabat_1_jabatan' => 'Kepala SMK Plus Al Hilal',
     *   'pejabat_1_nama' => 'H. Nama Kepala Sekolah, M.Pd.',
     *   'pejabat_1_nip' => 'NIP. -',
     *   'pejabat_2_jabatan' => 'Ketua Lembaga Kepesantrenan',
     *   'pejabat_2_nama' => 'Ust. Nama Ketua Lembaga',
     *   'pejabat_2_nip' => '',
     * ]
     */
    public static function generatePdfBytes(array $data): string
    {
        $w = 841.89; // A4 Landscape width in pt
        $h = 595.28; // A4 Landscape height in pt
        $cx = $w / 2.0;

        $isKepesantrenan = ($data['kategori'] ?? 'kepesantrenan') === 'kepesantrenan';
        // Primary color RGB (0..1)
        [$pr, $pg, $pb] = $isKepesantrenan ? [0.04, 0.42, 0.29] : [0.12, 0.23, 0.54];
        // Gold accent RGB (0..1)
        [$gr, $gg, $gb] = [0.78, 0.60, 0.18];

        $stream = [];

        // 1. Background soft cream tint
        $stream[] = "0.992 0.990 0.982 rg";
        $stream[] = sprintf("0 0 %.2f %.2f re f", $w, $h);

        // 2. Outer thick border (Primary color)
        $stream[] = sprintf("%.3f %.3f %.3f RG 4 w", $pr, $pg, $pb);
        $stream[] = sprintf("24 24 %.2f %.2f re S", $w - 48, $h - 48);

        // 3. Inner gold double border
        $stream[] = sprintf("%.3f %.3f %.3f RG 1.5 w", $gr, $gg, $gb);
        $stream[] = sprintf("31 31 %.2f %.2f re S", $w - 62, $h - 62);
        $stream[] = sprintf("0.7 w 35 35 %.2f %.2f re S", $w - 70, $h - 70);

        // 4. Top header bar ornament
        $stream[] = sprintf("%.3f %.3f %.3f rg", $pr, $pg, $pb);
        $stream[] = sprintf("35 %.2f %.2f 8 re f", $h - 43, $w - 70);
        $stream[] = sprintf("%.3f %.3f %.3f rg", $gr, $gg, $gb);
        $stream[] = sprintf("35 %.2f %.2f 3 re f", $h - 46, $w - 70);

        // 5. Institution Header
        $namaSekolah = strtoupper((string) ($data['nama_sekolah'] ?? 'SMK PLUS AL HILAL'));
        $subHeader = $isKepesantrenan
            ? 'PONDOK PESANTREN & LEMBAGA KEPESANTRENAN SISWA'
            : 'UNIT PRODUKSI & LEMBAGA KEWIRAUSAHAAN SISWA';

        $stream[] = self::centeredText($cx, $h - 78, $namaSekolah, 'F2', 14, [$pr, $pg, $pb]);
        $stream[] = self::centeredText($cx, $h - 95, $subHeader, 'F2', 9.5, [$gr, $gg, $gb]);

        // Decorative divider line
        $stream[] = sprintf("%.3f %.3f %.3f RG 1 w %.2f %.2f m %.2f %.2f l S", $gr, $gg, $gb, $cx - 160, $h - 105, $cx + 160, $h - 105);

        // 6. Main Certificate Title
        $stream[] = self::centeredText($cx, $h - 142, 'S E R T I F I K A T', 'F3', 30, [$pr, $pg, $pb]);

        $nomor = trim((string) ($data['nomor_dokumen'] ?? ''));
        if ($nomor !== '') {
            $stream[] = self::centeredText($cx, $h - 162, 'Nomor: ' . $nomor, 'F1', 10.5, [0.30, 0.35, 0.40]);
        }

        // 7. "Diberikan kepada:"
        $stream[] = self::centeredText($cx, $h - 192, 'Diberikan dengan bangga kepada:', 'F4', 12, [0.25, 0.28, 0.32]);

        // 8. Student Name & Identity
        $namaSiswa = strtoupper(trim((string) ($data['nama_siswa'] ?? 'NAMA SISWA')));
        $stream[] = self::centeredText($cx, $h - 224, $namaSiswa, 'F3', 24, [0.08, 0.12, 0.18]);

        // Underline under student name
        $stream[] = sprintf("%.3f %.3f %.3f RG 1.2 w %.2f %.2f m %.2f %.2f l S", $gr, $gg, $gb, $cx - 180, $h - 232, $cx + 180, $h - 232);

        $identitasParts = [];
        if (!empty($data['nisn'])) {
            $identitasParts[] = 'NISN/ID: ' . $data['nisn'];
        }
        if (!empty($data['kelas'])) {
            $identitasParts[] = 'Kelas: ' . $data['kelas'];
        }
        if (!empty($identitasParts)) {
            $stream[] = self::centeredText($cx, $h - 248, implode('   |   ', $identitasParts), 'F2', 10.5, [0.30, 0.35, 0.42]);
        }

        // 9. Activity / Program Description
        $namaKegiatan = trim((string) ($data['nama_kegiatan'] ?? 'Kegiatan Kompetensi Siswa'));
        $deskripsi = trim((string) ($data['deskripsi_kegiatan'] ?? ''));
        if ($deskripsi === '') {
            $deskripsi = $isKepesantrenan
                ? 'Telah mengikuti dan menyelesaikan program pembinaan serta ujian kompetensi kepesantrenan pada kegiatan:'
                : 'Telah mengikuti dan menyelesaikan program praktik serta uji kompetensi kewirausahaan pada kegiatan:';
        }

        $wrappedDesc = self::wrapText($deskripsi, 95);
        $currY = $h - 275;
        foreach (array_slice($wrappedDesc, 0, 2) as $line) {
            $stream[] = self::centeredText($cx, $currY, $line, 'F1', 11.5, [0.20, 0.23, 0.28]);
            $currY -= 16;
        }

        // Highlighted Activity Name
        $currY -= 6;
        $stream[] = self::centeredText($cx, $currY, '"' . strtoupper($namaKegiatan) . '"', 'F2', 16, [$pr, $pg, $pb]);

        // 10. Predicate Badge Box ("DENGAN PREDIKAT: SANGAT BAIK / BAIK / CUKUP")
        $predikat = strtoupper(trim((string) ($data['predikat'] ?? 'BAIK')));
        $badgeW = 250;
        $badgeH = 30;
        $badgeX = $cx - ($badgeW / 2.0);
        $badgeY = $currY - 44;

        $stream[] = sprintf("%.3f %.3f %.3f rg", $pr, $pg, $pb);
        $stream[] = sprintf("%.3f %.3f %.3f RG 1.2 w", $gr, $gg, $gb);
        $stream[] = sprintf("%.2f %.2f %.2f %.2f re B", $badgeX, $badgeY, $badgeW, $badgeH);
        $stream[] = self::centeredText($cx, $badgeY + 10, 'PREDIKAT NILAI : ' . $predikat, 'F2', 12, [1.0, 1.0, 1.0]);

        // 11. Date & Signature Blocks
        $tempatTanggal = trim((string) ($data['tempat_tanggal'] ?? ('Cirebon, ' . date('d F Y'))));
        $p1Jabatan = trim((string) ($data['pejabat_1_jabatan'] ?? 'Kepala SMK Plus Al Hilal'));
        $p1Nama    = trim((string) ($data['pejabat_1_nama'] ?? ''));
        $p1Nip     = trim((string) ($data['pejabat_1_nip'] ?? ''));

        $p2Jabatan = trim((string) ($data['pejabat_2_jabatan'] ?? ''));
        $p2Nama    = trim((string) ($data['pejabat_2_nama'] ?? ''));
        $p2Nip     = trim((string) ($data['pejabat_2_nip'] ?? ''));

        $hasTwoSigners = ($p2Jabatan !== '' || $p2Nama !== '');
        $sigTopY = 152.0;

        if ($hasTwoSigners) {
            $leftX = 210.0;
            $rightX = $w - 210.0;

            // Left Signer (Pejabat 1)
            $stream[] = self::centeredText($leftX, $sigTopY + 16, 'Mengetahui,', 'F1', 10.5, [0.25, 0.28, 0.32]);
            $stream[] = self::centeredText($leftX, $sigTopY, $p1Jabatan, 'F2', 11, [0.10, 0.14, 0.20]);
            $stream[] = self::centeredText($leftX, $sigTopY - 62, $p1Nama !== '' ? $p1Nama : '............................................', 'F2', 11.5, [0.08, 0.12, 0.18]);
            $stream[] = sprintf("0.4 0.4 0.4 RG 0.8 w %.2f %.2f m %.2f %.2f l S", $leftX - 95, $sigTopY - 66, $leftX + 95, $sigTopY - 66);
            if ($p1Nip !== '') {
                $stream[] = self::centeredText($leftX, $sigTopY - 79, $p1Nip, 'F1', 9.5, [0.35, 0.38, 0.42]);
            }

            // Right Signer (Pejabat 2 + Tanggal)
            $stream[] = self::centeredText($rightX, $sigTopY + 16, $tempatTanggal, 'F1', 10.5, [0.25, 0.28, 0.32]);
            $stream[] = self::centeredText($rightX, $sigTopY, $p2Jabatan, 'F2', 11, [0.10, 0.14, 0.20]);
            $stream[] = self::centeredText($rightX, $sigTopY - 62, $p2Nama !== '' ? $p2Nama : '............................................', 'F2', 11.5, [0.08, 0.12, 0.18]);
            $stream[] = sprintf("0.4 0.4 0.4 RG 0.8 w %.2f %.2f m %.2f %.2f l S", $rightX - 95, $sigTopY - 66, $rightX + 95, $sigTopY - 66);
            if ($p2Nip !== '') {
                $stream[] = self::centeredText($rightX, $sigTopY - 79, $p2Nip, 'F1', 9.5, [0.35, 0.38, 0.42]);
            }
        } else {
            // Single Signer Centered
            $stream[] = self::centeredText($cx, $sigTopY + 16, $tempatTanggal, 'F1', 10.5, [0.25, 0.28, 0.32]);
            $stream[] = self::centeredText($cx, $sigTopY, $p1Jabatan, 'F2', 11, [0.10, 0.14, 0.20]);
            $stream[] = self::centeredText($cx, $sigTopY - 62, $p1Nama !== '' ? $p1Nama : '............................................', 'F2', 11.5, [0.08, 0.12, 0.18]);
            $stream[] = sprintf("0.4 0.4 0.4 RG 0.8 w %.2f %.2f m %.2f %.2f l S", $cx - 100, $sigTopY - 66, $cx + 100, $sigTopY - 66);
            if ($p1Nip !== '') {
                $stream[] = self::centeredText($cx, $sigTopY - 79, $p1Nip, 'F1', 9.5, [0.35, 0.38, 0.42]);
            }
        }

        $contentStream = implode("\n", $stream);
        return self::compilePdfDocument($w, $h, $contentStream);
    }

    protected static function centeredText(float $cx, float $y, string $text, string $font, float $size, array $rgb): string
    {
        $clean = self::sanitizePdfString($text);
        // Approximate width in standard PDF font
        $charFactor = in_array($font, ['F2', 'F3'], true) ? 0.54 : 0.49;
        $estWidth = strlen($clean) * $size * $charFactor;
        $x = max(42.0, $cx - ($estWidth / 2.0));
        $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $clean);

        return sprintf(
            "BT /%s %.2f Tf %.3f %.3f %.3f rg %.2f %.2f Td (%s) Tj ET",
            $font,
            $size,
            $rgb[0],
            $rgb[1],
            $rgb[2],
            $x,
            $y,
            $escaped
        );
    }

    protected static function sanitizePdfString(string $str): string
    {
        $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $str);
        return $converted !== false ? $converted : preg_replace('/[^\x20-\x7E]/', '', $str);
    }

    protected static function wrapText(string $text, int $maxChars): array
    {
        $wrapped = wordwrap(trim($text), $maxChars, "\n", true);
        return array_values(array_filter(array_map('trim', explode("\n", $wrapped))));
    }

    protected static function compilePdfDocument(float $w, float $h, string $stream): string
    {
        $objects = [];
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[2] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[3] = sprintf(
            "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2f %.2f] /Resources << /Font << /F1 4 0 R /F2 5 0 R /F3 6 0 R /F4 7 0 R >> >> /Contents 8 0 R >>",
            $w,
            $h
        );
        $objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objects[5] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";
        $objects[6] = "<< /Type /Font /Subtype /Type1 /BaseFont /Times-Bold >>";
        $objects[7] = "<< /Type /Font /Subtype /Type1 /BaseFont /Times-Italic >>";
        $objects[8] = sprintf("<< /Length %d >>\nstream\n%s\nendstream", strlen($stream), $stream);

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $count = count($objects) + 1;
        $pdf .= "xref\n0 {$count}\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";
        return $pdf;
    }
}

