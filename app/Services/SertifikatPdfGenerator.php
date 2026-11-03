<?php

namespace App\Services;

class SertifikatPdfGenerator
{
    /**
     * Generate file PDF A4 Landscape (841.89 x 595.28 pt) murni PHP + GD (mendukung Logo Sekolah & Upload Template Sertifikat).
     *
     * @param array $data [
     *   'kategori'            => 'kepesantrenan'|'kewirausahaan',
     *   'header_instansi'     => 'SMK PLUS AL HILAL',
     *   'sub_header'          => 'PONDOK PESANTREN & LEMBAGA KEPESANTRENAN SISWA',
     *   'judul_sertifikat'    => 'SERTIFIKAT PENGHARGAAN',
     *   'nomor_dokumen'       => '421.5/001/SERT-AH/2026',
     *   'kata_pengantar'      => 'Diberikan dengan bangga kepada:',
     *   'nama_siswa'          => 'AHMAD FAUZI',
     *   'nisn'                => '0081234567',
     *   'kelas'               => 'XII TKJ 1',
     *   'deskripsi_kegiatan'  => 'Telah mengikuti dan menyelesaikan program pembinaan serta evaluasi kompetensi pada kegiatan:',
     *   'nama_kegiatan'       => 'Sertifikat Khatam Juz',
     *   'kalimat_penutup'     => '',
     *   'label_predikat'      => 'DENGAN PREDIKAT NILAI',
     *   'predikat'            => 'SANGAT BAIK',
     *   'tempat_tanggal'      => 'Cirebon, 03 November 2026',
     *   'pejabat_1_jabatan'   => 'Kepala SMK Plus Al Hilal',
     *   'pejabat_1_nama'      => 'Muhammad Mansyur, S.Pt',
     *   'pejabat_1_nip'       => 'NIP. 6942767668130350',
     *   'pejabat_2_jabatan'   => 'Ketua Lembaga Kepesantrenan',
     *   'pejabat_2_nama'      => 'Ust. Nama Ketua Lembaga',
     *   'pejabat_2_nip'       => '',
     *   'logo_path'           => 'D:/.../public/images/logo_smk.png',
     *   'show_logo'           => true,
     *   'template_bg_path'    => null, // Path gambar desain sertifikat yang diupload admin (opsional)
     *   'template_mode'       => 'full', // 'overlay_nama' (desain sudah jadi, tempel nama/predikat) | 'full' (cetak seluruh bagian teks)
     *   'show_nomor_overlay'  => true,
     *   'show_nisn_overlay'   => true,
     *   'show_kegiatan_overlay' => false,
     *   'show_predikat_overlay' => true,
     *   'show_ttd_overlay'    => false,
     *   'nama_y_percent'      => 52, // Posisi vertikal nama siswa (0=atas, 100=bawah) untuk mode upload desain
     *   'text_color_hex'      => '#0f172a',
     * ]
     */
    public static function generatePdfBytes(array $data): string
    {
        $w = 841.89; // A4 Landscape width in pt
        $h = 595.28; // A4 Landscape height in pt
        $cx = $w / 2.0;

        $isKepesantrenan = ($data['kategori'] ?? 'kepesantrenan') === 'kepesantrenan';
        // Warna Utama (Hijau Emerald Tua untuk Kepesantrenan, Biru Navy Tua untuk Kewirausahaan)
        [$pr, $pg, $pb] = $isKepesantrenan ? [0.024, 0.373, 0.275] : [0.118, 0.227, 0.541];
        // Warna Aksen Emas
        [$gr, $gg, $gb] = [0.769, 0.545, 0.086];

        $images = [];
        $stream = [];

        // Cek apakah menggunakan file gambar template sertifikat yang diupload
        $templateBgPath = !empty($data['template_bg_path']) ? (string) $data['template_bg_path'] : '';
        $hasCustomBg = false;
        if ($templateBgPath !== '' && is_file($templateBgPath)) {
            $bgJpeg = self::prepareJpegForPdf($templateBgPath, [255, 253, 247], 1600);
            if ($bgJpeg !== null) {
                $images['ImBg'] = $bgJpeg;
                $hasCustomBg = true;
            }
        }

        $templateMode = (string) ($data['template_mode'] ?? 'full');

        // =========================================================================
        // MODE KHUSUS: FILE DESAIN SERTIFIKAT SUDAH JADI (TINGGAL TEMPEL NAMA SISWA DARI DATABASE)
        // =========================================================================
        if ($hasCustomBg && $templateMode === 'overlay_nama') {
            // Gambar file sertifikat yang diupload memenuhi halaman A4 Landscape
            $stream[] = sprintf("q %.2f 0 0 %.2f 0 0 cm /ImBg Do Q", $w, $h);

            [$tr, $tg, $tb] = self::hexToRgb((string) ($data['text_color_hex'] ?? '#0f172a'));

            $yPercent = (float) ($data['nama_y_percent'] ?? 52);
            $yPercent = max(15.0, min(85.0, $yPercent));
            // Konversi persentase dari atas (0% atas -> 100% bawah) ke koordinat Y PDF (0 bawah -> $h atas)
            $nameY = $h * (1.0 - ($yPercent / 100.0));

            // 1. Opsional Nomor Sertifikat di atas nama
            $nomor = trim((string) ($data['nomor_dokumen'] ?? ''));
            if (!empty($data['show_nomor_overlay']) && $nomor !== '') {
                $stream[] = self::centeredText($cx, $nameY + 58, 'Nomor: ' . $nomor, 'F2', 11.5, [$tr, $tg, $tb]);
            }

            // 2. Nama Siswa dari Database (Utama)
            $namaSiswa = strtoupper(trim((string) ($data['nama_siswa'] ?? 'NAMA SISWA')));
            $nameFontSize = strlen($namaSiswa) > 28 ? 22.0 : 26.0;
            $stream[] = self::centeredText($cx, $nameY, $namaSiswa, 'F3', $nameFontSize, [$tr, $tg, $tb]);

            $curY = $nameY - 20.0;

            // 3. Opsional NISN & Kelas di bawah nama siswa
            if (!empty($data['show_nisn_overlay'])) {
                $identitasParts = [];
                if (!empty($data['nisn'])) {
                    $identitasParts[] = 'NISN: ' . $data['nisn'];
                }
                if (!empty($data['kelas'])) {
                    $identitasParts[] = 'Kelas: ' . $data['kelas'];
                }
                if (!empty($identitasParts)) {
                    $stream[] = self::centeredText($cx, $curY, implode('   |   ', $identitasParts), 'F2', 11.0, [$tr, $tg, $tb]);
                    $curY -= 26.0;
                }
            }

            // 4. Opsional Narasi & Nama Kegiatan
            if (!empty($data['show_kegiatan_overlay'])) {
                $deskripsi = trim((string) ($data['deskripsi_kegiatan'] ?? ''));
                if ($deskripsi !== '') {
                    foreach (array_slice(self::wrapText($deskripsi, 90), 0, 2) as $line) {
                        $stream[] = self::centeredText($cx, $curY, $line, 'F1', 11.5, [$tr, $tg, $tb]);
                        $curY -= 16.0;
                    }
                }
                $namaKegiatan = trim((string) ($data['nama_kegiatan'] ?? ''));
                if ($namaKegiatan !== '') {
                    $curY -= 4.0;
                    $stream[] = self::centeredText($cx, $curY, '"' . strtoupper($namaKegiatan) . '"', 'F2', 15.0, [$pr, $pg, $pb]);
                    $curY -= 24.0;
                }
            }

            // 5. Opsional Predikat Nilai
            $predikat = strtoupper(trim((string) ($data['predikat'] ?? '')));
            $labelPredikat = strtoupper(trim((string) ($data['label_predikat'] ?? 'PREDIKAT NILAI')));
            if (!empty($data['show_predikat_overlay']) && $predikat !== '') {
                $predText = ($labelPredikat !== '' ? $labelPredikat . ' : ' : '') . $predikat;
                $badgeW = max(220.0, self::estimateTextWidth($predText, 'F2', 11.5) + 44.0);
                $badgeH = 26.0;
                $badgeX = $cx - ($badgeW / 2.0);
                $badgeY = $curY - 12.0;

                $stream[] = sprintf("%.3f %.3f %.3f rg", $pr, $pg, $pb);
                $stream[] = sprintf("%.3f %.3f %.3f RG 1.2 w", $gr, $gg, $gb);
                $stream[] = sprintf("%.2f %.2f %.2f %.2f re B", $badgeX, $badgeY, $badgeW, $badgeH);
                $stream[] = self::centeredText($cx, $badgeY + 8.5, $predText, 'F2', 11.5, [1.0, 1.0, 1.0]);
            }

            // 6. Opsional Blok Tanda Tangan Pejabat
            if (!empty($data['show_ttd_overlay'])) {
                self::appendSignatureBlocks($stream, $data, $w, $cx, 128.0, [$tr, $tg, $tb]);
            }

            return self::compilePdfDocument($w, $h, implode("\n", $stream), $images);
        }

        // =========================================================================
        // MODE DESAIN SERTIFIKAT LENGKAP (DENGAN BINGKAI RESMI ATAU BACKGROUND CUSTOM + LOGO SEKOLAH)
        // =========================================================================
        if ($hasCustomBg) {
            // Gunakan gambar background yang diupload
            $stream[] = sprintf("q %.2f 0 0 %.2f 0 0 cm /ImBg Do Q", $w, $h);
        } else {
            // 1. Background kertas sertifikat krem lembut
            $stream[] = "0.996 0.993 0.980 rg";
            $stream[] = sprintf("0 0 %.2f %.2f re f", $w, $h);

            // 2. Bingkai Luar Tebal (Warna Utama Unit)
            $stream[] = sprintf("%.3f %.3f %.3f RG 5.0 w", $pr, $pg, $pb);
            $stream[] = sprintf("22 22 %.2f %.2f re S", $w - 44, $h - 44);

            // 3. Bingkai Dalam Ganda Emas
            $stream[] = sprintf("%.3f %.3f %.3f RG 1.8 w", $gr, $gg, $gb);
            $stream[] = sprintf("30 30 %.2f %.2f re S", $w - 60, $h - 60);
            $stream[] = sprintf("0.8 w 34 34 %.2f %.2f re S", $w - 68, $h - 68);

            // 4. Ornamen Sudut Klasik (Corner Ornaments) di Keempat Pojok
            $cLen = 26.0;
            $cOff = 40.0;
            $stream[] = sprintf("%.3f %.3f %.3f RG 2.2 w", $gr, $gg, $gb);
            // Kiri Atas
            $stream[] = sprintf("%.2f %.2f m %.2f %.2f l %.2f %.2f l S", $cOff, $h - $cOff - $cLen, $cOff, $h - $cOff, $cOff + $cLen, $h - $cOff);
            // Kanan Atas
            $stream[] = sprintf("%.2f %.2f m %.2f %.2f l %.2f %.2f l S", $w - $cOff - $cLen, $h - $cOff, $w - $cOff, $h - $cOff, $w - $cOff, $h - $cOff - $cLen);
            // Kiri Bawah
            $stream[] = sprintf("%.2f %.2f m %.2f %.2f l %.2f %.2f l S", $cOff, $cOff + $cLen, $cOff, $cOff, $cOff + $cLen, $cOff);
            // Kanan Bawah
            $stream[] = sprintf("%.2f %.2f m %.2f %.2f l %.2f %.2f l S", $w - $cOff - $cLen, $cOff, $w - $cOff, $cOff, $w - $cOff, $cOff + $cLen);

            // Pita Aksen Atas
            $stream[] = sprintf("%.3f %.3f %.3f rg 34 %.2f %.2f 6 re f", $pr, $pg, $pb, $h - 40, $w - 68);
            $stream[] = sprintf("%.3f %.3f %.3f rg 34 %.2f %.2f 2.2 re f", $gr, $gg, $gb, $h - 42.2, $w - 68);
        }

        // 5. Logo Sekolah di Tengah Atas Kop Sertifikat
        $showLogo = ($data['show_logo'] ?? true) !== false;
        $logoPath = !empty($data['logo_path']) ? (string) $data['logo_path'] : self::resolveDefaultSchoolLogoPath();
        $topCursorY = $h - 62.0;

        if ($showLogo && $logoPath !== '' && is_file($logoPath)) {
            $logoJpeg = self::prepareJpegForPdf($logoPath, [254, 253, 250], 300);
            if ($logoJpeg !== null) {
                $images['ImLogo'] = $logoJpeg;
                $logoSize = 52.0;
                $logoX = $cx - ($logoSize / 2.0);
                $logoY = $h - 104.0;
                $stream[] = sprintf("q %.2f 0 0 %.2f %.2f %.2f cm /ImLogo Do Q", $logoSize, $logoSize, $logoX, $logoY);
                $topCursorY = $h - 122.0;
            }
        }

        // 6. Kop Instansi & Sub-Unit (Dapat Dikustomisasi Admin)
        $headerInstansi = strtoupper(trim((string) ($data['header_instansi'] ?? ($data['nama_sekolah'] ?? 'SMK PLUS AL HILAL'))));
        $subHeader = strtoupper(trim((string) ($data['sub_header'] ?? '')));
        if ($subHeader === '') {
            $subHeader = $isKepesantrenan
                ? 'PONDOK PESANTREN & LEMBAGA KEPESANTRENAN SISWA'
                : 'UNIT PRODUKSI & LEMBAGA KEWIRAUSAHAAN SISWA';
        }

        $stream[] = self::centeredText($cx, $topCursorY, $headerInstansi, 'F2', 14.5, [$pr, $pg, $pb]);
        $topCursorY -= 15.0;
        $stream[] = self::centeredText($cx, $topCursorY, $subHeader, 'F2', 9.5, [$gr, $gg, $gb]);

        // Garis Pembatas Kop Emas dengan Ornamen Berlian Tengah
        $divY = $topCursorY - 9.0;
        $stream[] = sprintf("%.3f %.3f %.3f RG 1.0 w %.2f %.2f m %.2f %.2f l S", $gr, $gg, $gb, $cx - 190, $divY, $cx + 190, $divY);
        $stream[] = sprintf("%.3f %.3f %.3f rg %.2f %.2f m %.2f %.2f l %.2f %.2f l %.2f %.2f l f",
            $gr, $gg, $gb,
            $cx, $divY + 3.5,
            $cx + 4.5, $divY,
            $cx, $divY - 3.5,
            $cx - 4.5, $divY
        );

        // 7. Judul Utama Sertifikat & Nomor Sertifikat
        $judulSertifikat = strtoupper(trim((string) ($data['judul_sertifikat'] ?? 'SERTIFIKAT PENGHARGAAN')));
        if ($judulSertifikat === '') {
            $judulSertifikat = 'SERTIFIKAT PENGHARGAAN';
        }
        $titleY = $divY - 32.0;
        $titleFontSize = strlen($judulSertifikat) > 22 ? 24.0 : 28.0;
        $stream[] = self::centeredText($cx, $titleY, $judulSertifikat, 'F3', $titleFontSize, [$pr, $pg, $pb]);

        $nomor = trim((string) ($data['nomor_dokumen'] ?? ''));
        $afterTitleY = $titleY - 17.0;
        if ($nomor !== '') {
            $stream[] = self::centeredText($cx, $afterTitleY, 'Nomor : ' . $nomor, 'F1', 10.5, [0.28, 0.33, 0.39]);
            $afterTitleY -= 22.0;
        } else {
            $afterTitleY -= 10.0;
        }

        // 8. Kata Pengantar Penerima ("Diberikan dengan bangga kepada:")
        $kataPengantar = trim((string) ($data['kata_pengantar'] ?? 'Diberikan dengan bangga kepada:'));
        if ($kataPengantar === '') {
            $kataPengantar = 'Diberikan dengan bangga kepada:';
        }
        $stream[] = self::centeredText($cx, $afterTitleY, $kataPengantar, 'F4', 12.0, [0.22, 0.26, 0.32]);

        // 9. Nama Siswa & Identitas Kelas/NISN
        $namaSiswa = strtoupper(trim((string) ($data['nama_siswa'] ?? 'NAMA SISWA')));
        $studentNameY = $afterTitleY - 28.0;
        $studentFontSize = strlen($namaSiswa) > 28 ? 20.0 : 24.0;
        $stream[] = self::centeredText($cx, $studentNameY, $namaSiswa, 'F3', $studentFontSize, [0.06, 0.09, 0.16]);

        // Garis bawah emas proporsional di bawah Nama Siswa
        $nameHalfW = max(140.0, min(260.0, (self::estimateTextWidth($namaSiswa, 'F3', $studentFontSize) / 2.0) + 30.0));
        $stream[] = sprintf("%.3f %.3f %.3f RG 1.4 w %.2f %.2f m %.2f %.2f l S", $gr, $gg, $gb, $cx - $nameHalfW, $studentNameY - 6.0, $cx + $nameHalfW, $studentNameY - 6.0);

        $identitasParts = [];
        if (!empty($data['nisn'])) {
            $identitasParts[] = 'NISN/ID: ' . $data['nisn'];
        }
        if (!empty($data['kelas'])) {
            $identitasParts[] = 'Kelas: ' . $data['kelas'];
        }
        $bodyStartY = $studentNameY - 21.0;
        if (!empty($identitasParts)) {
            $stream[] = self::centeredText($cx, $bodyStartY, implode('   |   ', $identitasParts), 'F2', 10.5, [0.28, 0.33, 0.40]);
            $bodyStartY -= 22.0;
        } else {
            $bodyStartY -= 8.0;
        }

        // 10. Kata-Kata / Narasi Isi Sertifikat & Nama Kegiatan
        $deskripsi = trim((string) ($data['deskripsi_kegiatan'] ?? ''));
        if ($deskripsi === '') {
            $deskripsi = $isKepesantrenan
                ? 'Telah mengikuti dan menyelesaikan dengan baik program pembinaan serta evaluasi kompetensi kepesantrenan pada kegiatan:'
                : 'Telah mengikuti dan menyelesaikan dengan baik program praktik serta evaluasi kompetensi kewirausahaan pada kegiatan:';
        }

        $wrappedDesc = self::wrapText($deskripsi, 92);
        foreach (array_slice($wrappedDesc, 0, 3) as $line) {
            $stream[] = self::centeredText($cx, $bodyStartY, $line, 'F1', 11.2, [0.18, 0.22, 0.27]);
            $bodyStartY -= 15.0;
        }

        $namaKegiatan = trim((string) ($data['nama_kegiatan'] ?? ''));
        if ($namaKegiatan !== '') {
            $bodyStartY -= 5.0;
            $actFontSize = strlen($namaKegiatan) > 48 ? 13.5 : 15.5;
            $stream[] = self::centeredText($cx, $bodyStartY, '"' . strtoupper($namaKegiatan) . '"', 'F2', $actFontSize, [$pr, $pg, $pb]);
            $bodyStartY -= 18.0;
        }

        // Kalimat Penutup Tambahan (Opsional)
        $kalimatPenutup = trim((string) ($data['kalimat_penutup'] ?? ''));
        if ($kalimatPenutup !== '') {
            $stream[] = self::centeredText($cx, $bodyStartY, $kalimatPenutup, 'F4', 10.5, [0.25, 0.29, 0.35]);
            $bodyStartY -= 16.0;
        }

        // 11. Pita / Kotak Predikat Nilai ("DENGAN PREDIKAT NILAI : SANGAT BAIK")
        $predikat = strtoupper(trim((string) ($data['predikat'] ?? '')));
        $labelPredikat = strtoupper(trim((string) ($data['label_predikat'] ?? 'DENGAN PREDIKAT NILAI')));
        if ($predikat !== '' && $predikat !== '-') {
            $fullPredText = ($labelPredikat !== '' ? $labelPredikat . ' : ' : '') . $predikat;
            $badgeW = max(230.0, self::estimateTextWidth($fullPredText, 'F2', 11.0) + 46.0);
            $badgeH = 25.0;
            $badgeX = $cx - ($badgeW / 2.0);
            $badgeY = max(168.0, $bodyStartY - 14.0);

            $stream[] = sprintf("%.3f %.3f %.3f rg", $pr, $pg, $pb);
            $stream[] = sprintf("%.3f %.3f %.3f RG 1.4 w", $gr, $gg, $gb);
            $stream[] = sprintf("%.2f %.2f %.2f %.2f re B", $badgeX, $badgeY, $badgeW, $badgeH);
            $stream[] = self::centeredText($cx, $badgeY + 8.2, $fullPredText, 'F2', 11.0, [1.0, 1.0, 1.0]);
        }

        // 12. Blok Tanggal & Tanda Tangan Pejabat 1 & Pejabat 2 (Sejajar & Simetris di Bagian Bawah)
        self::appendSignatureBlocks($stream, $data, $w, $cx, 134.0, [0.08, 0.12, 0.18]);

        $contentStream = implode("\n", $stream);
        return self::compilePdfDocument($w, $h, $contentStream, $images);
    }

    /**
     * Render blok tanda tangan Pejabat 1 & Pejabat 2 secara simetris
     */
    protected static function appendSignatureBlocks(array &$stream, array $data, float $w, float $cx, float $sigTopY, array $textRgb): void
    {
        $tempatTanggal = trim((string) ($data['tempat_tanggal'] ?? ('Cirebon, ' . date('d F Y'))));
        $p1Jabatan = trim((string) ($data['pejabat_1_jabatan'] ?? 'Kepala SMK Plus Al Hilal'));
        $p1Nama    = trim((string) ($data['pejabat_1_nama'] ?? ''));
        $p1Nip     = trim((string) ($data['pejabat_1_nip'] ?? ''));

        $p2Jabatan = trim((string) ($data['pejabat_2_jabatan'] ?? ''));
        $p2Nama    = trim((string) ($data['pejabat_2_nama'] ?? ''));
        $p2Nip     = trim((string) ($data['pejabat_2_nip'] ?? ''));

        $hasTwoSigners = ($p2Jabatan !== '' || $p2Nama !== '');

        if ($hasTwoSigners) {
            $leftX  = 215.0;
            $rightX = $w - 215.0;

            // Pejabat 1 (Kiri)
            $stream[] = self::centeredText($leftX, $sigTopY + 14.0, 'Mengetahui,', 'F1', 10.0, [0.25, 0.28, 0.33]);
            $stream[] = self::centeredText($leftX, $sigTopY, $p1Jabatan, 'F2', 10.8, $textRgb);
            $p1Display = $p1Nama !== '' ? $p1Nama : '............................................';
            $stream[] = self::centeredText($leftX, $sigTopY - 54.0, $p1Display, 'F2', 11.2, $textRgb);
            $lineW1 = max(85.0, min(130.0, (self::estimateTextWidth($p1Display, 'F2', 11.2) / 2.0) + 12.0));
            $stream[] = sprintf("0.30 0.33 0.38 RG 0.8 w %.2f %.2f m %.2f %.2f l S", $leftX - $lineW1, $sigTopY - 57.5, $leftX + $lineW1, $sigTopY - 57.5);
            if ($p1Nip !== '') {
                $stream[] = self::centeredText($leftX, $sigTopY - 69.0, $p1Nip, 'F1', 9.5, [0.30, 0.34, 0.40]);
            }

            // Pejabat 2 (Kanan + Tempat & Tanggal)
            $stream[] = self::centeredText($rightX, $sigTopY + 14.0, $tempatTanggal, 'F1', 10.0, [0.25, 0.28, 0.33]);
            $stream[] = self::centeredText($rightX, $sigTopY, $p2Jabatan, 'F2', 10.8, $textRgb);
            $p2Display = $p2Nama !== '' ? $p2Nama : '............................................';
            $stream[] = self::centeredText($rightX, $sigTopY - 54.0, $p2Display, 'F2', 11.2, $textRgb);
            $lineW2 = max(85.0, min(130.0, (self::estimateTextWidth($p2Display, 'F2', 11.2) / 2.0) + 12.0));
            $stream[] = sprintf("0.30 0.33 0.38 RG 0.8 w %.2f %.2f m %.2f %.2f l S", $rightX - $lineW2, $sigTopY - 57.5, $rightX + $lineW2, $sigTopY - 57.5);
            if ($p2Nip !== '') {
                $stream[] = self::centeredText($rightX, $sigTopY - 69.0, $p2Nip, 'F1', 9.5, [0.30, 0.34, 0.40]);
            }
        } else {
            // 1 Pejabat Penandatangan (Tengah)
            $stream[] = self::centeredText($cx, $sigTopY + 14.0, $tempatTanggal, 'F1', 10.0, [0.25, 0.28, 0.33]);
            $stream[] = self::centeredText($cx, $sigTopY, $p1Jabatan, 'F2', 10.8, $textRgb);
            $p1Display = $p1Nama !== '' ? $p1Nama : '............................................';
            $stream[] = self::centeredText($cx, $sigTopY - 54.0, $p1Display, 'F2', 11.2, $textRgb);
            $lineW1 = max(90.0, min(140.0, (self::estimateTextWidth($p1Display, 'F2', 11.2) / 2.0) + 12.0));
            $stream[] = sprintf("0.30 0.33 0.38 RG 0.8 w %.2f %.2f m %.2f %.2f l S", $cx - $lineW1, $sigTopY - 57.5, $cx + $lineW1, $sigTopY - 57.5);
            if ($p1Nip !== '') {
                $stream[] = self::centeredText($cx, $sigTopY - 69.0, $p1Nip, 'F1', 9.5, [0.30, 0.34, 0.40]);
            }
        }
    }

    /**
     * Cari path file Logo Sekolah resmi dari PengaturanSekolah atau folder public/images
     */
    public static function resolveDefaultSchoolLogoPath(): string
    {
        try {
            $setting = \App\Models\PengaturanSekolah::first();
            if ($setting && !empty($setting->logo)) {
                $p1 = public_path('storage/' . $setting->logo);
                if (is_file($p1)) {
                    return $p1;
                }
                $p2 = storage_path('app/public/' . $setting->logo);
                if (is_file($p2)) {
                    return $p2;
                }
            }
        } catch (\Throwable $e) {}

        foreach (['images/logo_smk.png', 'images/logo.png', 'images/logo.jpg'] as $rel) {
            $full = public_path($rel);
            if (is_file($full)) {
                return $full;
            }
        }
        return '';
    }

    /**
     * Konversi gambar PNG/JPG/WEBP menjadi JPEG RGB murni untuk PDF XObject (/DCTDecode)
     *
     * @return array{width:int, height:int, data:string}|null
     */
    protected static function prepareJpegForPdf(string $filePath, array $bgRgb = [255, 255, 255], int $maxDim = 1400): ?array
    {
        if (!extension_loaded('gd') || !is_file($filePath)) {
            return null;
        }

        $info = @getimagesize($filePath);
        if (!$info || empty($info[0]) || empty($info[1])) {
            return null;
        }

        $srcW = (int) $info[0];
        $srcH = (int) $info[1];
        $type = (int) ($info[2] ?? 0);

        $srcImg = match ($type) {
            IMAGETYPE_PNG  => @imagecreatefrompng($filePath),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($filePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($filePath) : false,
            IMAGETYPE_GIF  => @imagecreatefromgif($filePath),
            default        => @imagecreatefromstring((string) @file_get_contents($filePath)),
        };

        if (!$srcImg) {
            return null;
        }

        $dstW = $srcW;
        $dstH = $srcH;
        if ($maxDim > 0 && ($srcW > $maxDim || $srcH > $maxDim)) {
            $ratio = min($maxDim / $srcW, $maxDim / $srcH);
            $dstW = max(1, (int) round($srcW * $ratio));
            $dstH = max(1, (int) round($srcH * $ratio));
        }

        $canvas = imagecreatetruecolor($dstW, $dstH);
        $bgColor = imagecolorallocate($canvas, $bgRgb[0], $bgRgb[1], $bgRgb[2]);
        imagefilledrectangle($canvas, 0, 0, $dstW, $dstH, $bgColor);
        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $srcImg, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        ob_start();
        imagejpeg($canvas, null, 92);
        $jpegBytes = (string) ob_get_clean();

        imagedestroy($srcImg);
        imagedestroy($canvas);

        if ($jpegBytes === '') {
            return null;
        }

        return [
            'width'  => $dstW,
            'height' => $dstH,
            'data'   => $jpegBytes,
        ];
    }

    /**
     * Hitung lebar teks secara presisi menggunakan tabel lebar karakter proporsional Type1 PDF
     */
    protected static function estimateTextWidth(string $text, string $font, float $size): float
    {
        $clean = self::sanitizePdfString($text);
        $len = strlen($clean);
        $totalUnits = 0;

        $isBold = in_array($font, ['F2', 'F3'], true);
        $isSerif = in_array($font, ['F3', 'F4'], true);

        for ($i = 0; $i < $len; $i++) {
            $ch = $clean[$i];
            if ($ch === ' ') {
                $totalUnits += $isSerif ? 250 : 278;
            } elseif (in_array($ch, ['I', 'i', 'l', 'j', '!', '|', '.', ',', ':', ';', '\'', '`'], true)) {
                $totalUnits += $isBold ? 310 : 260;
            } elseif (in_array($ch, ['f', 't', 'r', '(', ')', '[', ']', '-', '/'], true)) {
                $totalUnits += $isBold ? 360 : 320;
            } elseif (in_array($ch, ['M', 'W', 'm', '@', '%', '&'], true)) {
                $totalUnits += $isBold ? 890 : 830;
            } elseif (in_array($ch, ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'K', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'Z', 'w'], true)) {
                $totalUnits += $isSerif ? ($isBold ? 690 : 660) : ($isBold ? 700 : 670);
            } elseif ($ch >= '0' && $ch <= '9') {
                $totalUnits += 556;
            } else {
                // Huruf kecil rata-rata (a-z)
                $totalUnits += $isSerif ? ($isBold ? 510 : 480) : ($isBold ? 560 : 520);
            }
        }

        return ($totalUnits / 1000.0) * $size;
    }

    protected static function centeredText(float $cx, float $y, string $text, string $font, float $size, array $rgb): string
    {
        $clean = self::sanitizePdfString($text);
        $estWidth = self::estimateTextWidth($clean, $font, $size);
        $x = max(38.0, $cx - ($estWidth / 2.0));
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

    protected static function hexToRgb(string $hex): array
    {
        $clean = ltrim(trim($hex), '#');
        if (strlen($clean) === 6) {
            return [
                hexdec(substr($clean, 0, 2)) / 255.0,
                hexdec(substr($clean, 2, 2)) / 255.0,
                hexdec(substr($clean, 4, 2)) / 255.0,
            ];
        }
        return [0.06, 0.09, 0.16];
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

    protected static function compilePdfDocument(float $w, float $h, string $stream, array $images = []): string
    {
        $objects = [];
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[2] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";

        $objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objects[5] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";
        $objects[6] = "<< /Type /Font /Subtype /Type1 /BaseFont /Times-Bold >>";
        $objects[7] = "<< /Type /Font /Subtype /Type1 /BaseFont /Times-Italic >>";
        $objects[8] = sprintf("<< /Length %d >>\nstream\n%s\nendstream", strlen($stream), $stream);

        $nextObjId = 9;
        $xObjectRefs = [];
        foreach ($images as $name => $img) {
            $objId = $nextObjId++;
            $len = strlen($img['data']);
            $objects[$objId] = sprintf(
                "<< /Type /XObject /Subtype /Image /Width %d /Height %d /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length %d >>\nstream\n%s\nendstream",
                $img['width'],
                $img['height'],
                $len,
                $img['data']
            );
            $xObjectRefs[] = "/{$name} {$objId} 0 R";
        }

        $xObjectDict = !empty($xObjectRefs)
            ? ' /XObject << ' . implode(' ', $xObjectRefs) . ' >>'
            : '';

        $objects[3] = sprintf(
            "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2f %.2f] /Resources << /Font << /F1 4 0 R /F2 5 0 R /F3 6 0 R /F4 7 0 R >>%s >> /Contents 8 0 R >>",
            $w,
            $h,
            $xObjectDict
        );

        ksort($objects);

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
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
