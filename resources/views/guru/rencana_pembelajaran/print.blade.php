<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perangkat Rencana Pembelajaran - {{ $mapel->nama }} Kelas {{ $tingkat }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            padding: 25px;
            margin: 0;
            background-color: #fff;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 18px;
            position: relative;
        }
        .kop-logo {
            position: absolute;
            left: 10px;
            top: 0;
            width: 75px;
            height: 75px;
            object-fit: contain;
        }
        .kop-header {
            margin-left: 85px;
            margin-right: 85px;
        }
        .kop-header h2 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .kop-header h1 {
            margin: 2px 0;
            font-size: 15pt;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .kop-header p {
            margin: 1px 0;
            font-size: 9pt;
        }
        .title-box {
            text-align: center;
            margin-bottom: 20px;
        }
        .title-box h3 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .title-box p {
            margin: 4px 0 0;
            font-size: 10pt;
            font-style: italic;
        }
        table.identitas {
            width: 100%;
            margin-bottom: 18px;
            font-size: 10.5pt;
            border-collapse: collapse;
        }
        table.identitas td {
            padding: 3px 0;
            vertical-align: top;
        }
        .section-header {
            font-size: 11pt;
            font-weight: bold;
            background-color: #f2f2f2;
            padding: 5px 8px;
            border-left: 4px solid #000;
            margin-top: 20px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 9.5pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 5px 7px;
            vertical-align: top;
        }
        table.data-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .page-break { page-break-before: always; }
        
        .rpp-card {
            border: 1px solid #000;
            margin-bottom: 15px;
            padding: 10px 12px;
            page-break-inside: avoid;
        }
        .rpp-title {
            font-weight: bold;
            font-size: 10.5pt;
            border-bottom: 1px dashed #666;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        .ttd-box {
            width: 100%;
            margin-top: 35px;
            font-size: 10.5pt;
            page-break-inside: avoid;
        }
        .ttd-box td {
            vertical-align: top;
            text-align: center;
            width: 50%;
        }
        .signature-space {
            height: 70px;
        }
        
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            @page {
                size: A4 portrait;
                margin: 15mm 15mm 15mm 15mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right; display: flex; justify-content: flex-end; gap: 10px;">
        <button onclick="window.history.back()" style="padding: 8px 16px; background: #64748b; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 12px;">
            &larr; Kembali
        </button>
        <button onclick="window.print()" style="padding: 8px 18px; background: #059669; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 12px; display: inline-flex; items-center; gap: 6px;">
            <span>🖨️ Cetak / Simpan PDF</span>
        </button>
    </div>

    <!-- Kop Surat -->
    <div class="kop-surat">
        <img src="{{ asset('images/logo.png') }}" class="kop-logo" alt="Logo Sekolah" onerror="this.style.display='none'">
        <div class="kop-header">
            <h2>YAYASAN AL-HILAL ARJAWINANGUN</h2>
            <h1>SMK PLUS AL-HILAL ARJAWINANGUN</h1>
            <p>NSS: 322021708001 &bull; NPSN: 20268571 &bull; Terakreditasi "A"</p>
            <p>Jl. Kebon Melati No. 01 Ds. Jungjang Kec. Arjawinangun Kab. Cirebon 45162 &bull; Telp. (0231) 357890</p>
        </div>
    </div>

    <!-- Judul Dokumen -->
    <div class="title-box">
        <h3>PERANGKAT RENCANA PEMBELAJARAN</h3>
        <p>KURIKULUM MERDEKA &bull; TAHUN AJARAN {{ $tahunAjaran }}</p>
    </div>

    <!-- Identitas Mata Pelajaran -->
    <table class="identitas">
        <tr>
            <td style="width: 22%;">Satuan Pendidikan</td>
            <td style="width: 2%;">:</td>
            <td style="width: 40%;"><strong>SMK Plus Al-Hilal Arjawinangun</strong></td>
            <td style="width: 16%;">Fase / Tingkat</td>
            <td style="width: 2%;">:</td>
            <td style="width: 18%;"><strong>Fase {{ $fase }} / Kelas {{ $tingkat }}</strong></td>
        </tr>
        <tr>
            <td>Mata Pelajaran</td>
            <td>:</td>
            <td><strong>{{ $mapel->nama }}</strong></td>
            <td>Semester</td>
            <td>:</td>
            <td><strong>{{ $semester ? ucfirst($semester) : 'Ganjil & Genap' }}</strong></td>
        </tr>
        <tr>
            <td>Guru Pengampu</td>
            <td>:</td>
            <td><strong>{{ $user->name }}</strong></td>
            <td>Tahun Ajaran</td>
            <td>:</td>
            <td>{{ $tahunAjaran }}</td>
        </tr>
    </table>

    <!-- ========================================== -->
    <!-- BAGIAN 1: CAPAIAN PEMBELAJARAN (CP) -->
    <!-- ========================================== -->
    <div class="section-header">I. CAPAIAN PEMBELAJARAN (CP)</div>
    @if($cpList->isEmpty())
        <p style="font-style: italic; font-size: 10pt; color: #555;">Belum ada Capaian Pembelajaran (CP) yang diinputkan untuk mata pelajaran ini.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 25%;">Elemen</th>
                    <th style="width: 12%;">Semester</th>
                    <th style="width: 58%;">Deskripsi Capaian Pembelajaran (CP)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cpList as $idx => $cp)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $cp->elemen }}</td>
                    <td class="text-center">{{ ucfirst($cp->semester) }}</td>
                    <td style="text-align: justify;">{{ $cp->deskripsi }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- ========================================== -->
    <!-- BAGIAN 2: TUJUAN PEMBELAJARAN (TP) -->
    <!-- ========================================== -->
    <div class="section-header">II. TUJUAN PEMBELAJARAN (TP)</div>
    @if($tpList->isEmpty())
        <p style="font-style: italic; font-size: 10pt; color: #555;">Belum ada Tujuan Pembelajaran (TP) yang diturunkan dari CP.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 15%;">Kode TP</th>
                    <th style="width: 25%;">Lingkup / Materi Pokok</th>
                    <th style="width: 45%;">Deskripsi Tujuan Pembelajaran</th>
                    <th style="width: 10%;">Alokasi JP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tpList as $idx => $tp)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center font-bold">{{ $tp->kode_tp }}</td>
                    <td>{{ $tp->materi ?? '-' }}</td>
                    <td>{{ $tp->deskripsi }}</td>
                    <td class="text-center">{{ $tp->perkiraan_jp ? $tp->perkiraan_jp . ' JP' : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- ========================================== -->
    <!-- BAGIAN 3: ALUR TUJUAN PEMBELAJARAN (ATP) -->
    <!-- ========================================== -->
    <div class="section-header">III. ALUR TUJUAN PEMBELAJARAN (ATP)</div>
    @if($atpList->isEmpty())
        <p style="font-style: italic; font-size: 10pt; color: #555;">Belum ada Alur Tujuan Pembelajaran (ATP) yang dirumuskan.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%;">Alur Ke-</th>
                    <th style="width: 12%;">Kode TP</th>
                    <th style="width: 45%;">Tujuan Pembelajaran</th>
                    <th style="width: 12%;">Semester</th>
                    <th style="width: 10%;">JP</th>
                    <th style="width: 13%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($atpList as $atp)
                <tr>
                    <td class="text-center font-bold">{{ $atp->alur_ke }}</td>
                    <td class="text-center font-bold">{{ $atp->tujuanPembelajaran->kode_tp ?? '-' }}</td>
                    <td>{{ $atp->tujuanPembelajaran->deskripsi ?? ($atp->materi ?: '-') }}</td>
                    <td class="text-center">{{ ucfirst($atp->semester) }}</td>
                    <td class="text-center">{{ $atp->perkiraan_jp ? $atp->perkiraan_jp . ' JP' : ($atp->tujuanPembelajaran->perkiraan_jp ? $atp->tujuanPembelajaran->perkiraan_jp . ' JP' : '-') }}</td>
                    <td class="text-center" style="font-size: 8.5pt;">{{ $atp->keterangan ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- ========================================== -->
    <!-- BAGIAN 4: MODUL AJAR HARIAN / RPP -->
    <!-- ========================================== -->
    <div class="section-header page-break">IV. MODUL AJAR HARIAN (RPP PERTEMUAN)</div>
    @if($rppList->isEmpty())
        <p style="font-style: italic; font-size: 10pt; color: #555;">Belum ada Modul Ajar Harian (RPP) yang dibuat. Modul Ajar Harian dapat disusun berkala menjelang pelaksanaan KBM.</p>
    @else
        @foreach($rppList as $idx => $rpp)
        <div class="rpp-card">
            <div class="rpp-title">
                Pertemuan Ke-{{ $rpp->pertemuan_ke }} &bull; Tanggal Rencana: {{ $rpp->tanggal_rencana ? \Carbon\Carbon::parse($rpp->tanggal_rencana)->isoFormat('dddd, D MMMM Y') : '-' }} &bull; Kelas: {{ $rpp->jadwal->kelas ?? "Kelas {$tingkat}" }}
            </div>
            <table class="identitas" style="font-size: 9.5pt; margin-bottom: 6px;">
                <tr>
                    <td style="width: 22%;"><strong>Materi Pokok</strong></td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 76%;">{{ $rpp->materi_pokok }}</td>
                </tr>
                @if($rpp->tujuanPembelajaran)
                <tr>
                    <td><strong>Tujuan Pembelajaran</strong></td>
                    <td>:</td>
                    <td>[{{ $rpp->tujuanPembelajaran->kode_tp }}] {{ $rpp->tujuanPembelajaran->deskripsi }}</td>
                </tr>
                @endif
                <tr>
                    <td><strong>Bentuk Asesmen</strong></td>
                    <td>:</td>
                    <td>{{ $rpp->bentuk_asesmen ?? 'Formatif (Observasi/Diskusi/Tes)' }}</td>
                </tr>
                @if($rpp->media_sumber)
                <tr>
                    <td><strong>Media & Sumber</strong></td>
                    <td>:</td>
                    <td>{{ $rpp->media_sumber }}</td>
                </tr>
                @endif
            </table>

            <table class="data-table" style="margin-bottom: 4px;">
                <thead>
                    <tr>
                        <th style="width: 25%;">Pendahuluan</th>
                        <th style="width: 55%;">Kegiatan Inti</th>
                        <th style="width: 20%;">Penutup</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $rpp->aktivitas_pendahuluan ?: 'Orientasi, apersepsi, motivasi, dan penyampaian tujuan.' }}</td>
                        <td>{{ $rpp->aktivitas_inti ?: 'Pelaksanaan pembelajaran sesuai alur merdeka / model interaktif.' }}</td>
                        <td>{{ $rpp->aktivitas_penutup ?: 'Refleksi, simpulan, umpan balik, dan doa.' }}</td>
                    </tr>
                </tbody>
            </table>
            @if($rpp->catatan)
            <div style="font-size: 8.5pt; font-style: italic; color: #444; margin-top: 3px;">
                * Catatan: {{ $rpp->catatan }}
            </div>
            @endif
        </div>
        @endforeach
    @endif

    <!-- Tanda Tangan Resmi -->
    <table class="ttd-box">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala SMK Plus Al-Hilal Arjawinangun<br>
                <div class="signature-space"></div>
                <strong><u>{{ $setting->kepala_sekolah ?? 'Muhammad Mansyur, S.Pt' }}</u></strong><br>
                NUPTK. {{ $setting->nip_kepala_sekolah ?? '6942767668130350' }}
            </td>
            <td>
                Arjawinangun, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}<br>
                Guru Mata Pelajaran,<br>
                <div class="signature-space"></div>
                <strong><u>{{ $user->name }}</u></strong><br>
                NUPTK/NIP. {{ $user->nip ?? '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
