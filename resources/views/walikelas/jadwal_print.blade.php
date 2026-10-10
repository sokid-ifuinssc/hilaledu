<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matriks Jadwal Pelajaran Kelas {{ $namaKelas }} - Kertas F4</title>
    <style>
        @page {
            size: 330mm 215mm; /* Kertas F4 / Folio Landscape (330 x 215 mm) */
            margin: 3.5mm 5mm 3.5mm 5mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #0f172a;
            margin: 0;
            padding: 8px;
            background: #f8fafc;
        }
        .no-print {
            max-width: 900px;
            margin: 0 auto 12px auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 10px 16px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .btn-print {
            padding: 8px 18px;
            background: #0284c7;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 9pt;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-back {
            padding: 7px 14px;
            background: #e2e8f0;
            color: #1e293b;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 8.5pt;
        }
        .print-f4-page {
            width: 100%;
            background: #ffffff;
            padding: 4px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000000;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }
        .header h1 {
            font-size: 13pt;
            margin: 0;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 10.5pt;
            margin: 2px 0 0 0;
            font-weight: 800;
            text-transform: uppercase;
            color: #0369a1;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 8pt;
            font-weight: 600;
            color: #334155;
        }
        table.matrix-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 7.5pt;
        }
        table.matrix-table th, table.matrix-table td {
            border: 1px solid #1e293b;
            padding: 2.5px 2px;
        }
        table.matrix-table th {
            background-color: #f1f5f9;
            font-weight: 800;
            font-size: 7.5pt;
            color: #0f172a;
        }
        .th-hari {
            background-color: #0284c7 !important;
            color: #ffffff !important;
            font-size: 8pt;
            font-weight: 800;
            text-transform: uppercase;
        }
        .th-jumat {
            background-color: #059669 !important;
            color: #ffffff !important;
        }
        .row-pembiasaan {
            background-color: #ecfdf5;
            font-weight: bold;
            color: #065f46;
            font-size: 7pt;
        }
        .mapel-cell {
            text-align: left;
            padding: 2px 4px !important;
            background-color: #ffffff;
            vertical-align: middle;
        }
        .mapel-nama {
            font-weight: 800;
            font-size: 7.5pt;
            color: #0f172a;
            line-height: 1.15;
        }
        .guru-nama {
            font-size: 7pt;
            color: #1d4ed8;
            font-weight: 700;
            line-height: 1.1;
        }
        .ruang-badge {
            font-size: 6pt;
            color: #64748b;
            font-weight: 600;
        }
        .istirahat {
            background-color: #fffbeb !important;
            font-weight: 800;
            font-size: 7pt;
            color: #92400e;
            letter-spacing: 0.5px;
        }
        .catatan-section {
            margin-top: 3px;
            padding-top: 2px;
            border-top: 1px solid #94a3b8;
            font-size: 7pt;
            color: #334155;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ttd-grid {
            margin-top: 4px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            font-size: 8pt;
            text-align: center;
        }
        .ttd-box {
            padding: 0 20px;
        }
        .ttd-space {
            height: 32px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-f4-page {
                width: 100% !important;
                height: 100% !important;
                max-height: 208mm !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                overflow: hidden !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
            }
            table.matrix-table th, table.matrix-table td {
                padding: 1.5px 2px !important;
                line-height: 1.1 !important;
            }
            * {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

    <!-- TOOLBAR (NO-PRINT) -->
    <div class="no-print">
        <div>
            <a href="{{ route('walikelas.jadwal') }}" class="btn-back">&larr; Kembali ke Jadwal</a>
            <span style="margin-left: 10px; font-weight: bold; color: #047857; font-size: 8.5pt;">
                📄 Format Kertas F4 / Folio Landscape (330 x 215 mm) &bull; Pas 1 Halaman
            </span>
        </div>
        <div>
            <button onclick="window.print()" class="btn-print">
                🖨️ Cetak Jadwal Kelas (Kertas F4 - 1 Halaman)
            </button>
        </div>
    </div>

    <!-- MAIN PRINT F4 CONTAINER -->
    <div class="print-f4-page">
        <!-- HEADER KOP RESMI -->
        <div class="header">
            <h1>SMK PLUS AL-HILAL ARJAWINANGUN</h1>
            <h2>MATRIKS JADWAL PELAJARAN KELAS {{ strtoupper($namaKelas) }}</h2>
            <p>
                Tahun Ajaran: <b>{{ $tahunAjaran }}</b> &bull; Semester: <b>{{ ucfirst($semester) }}</b> &bull; 
                Wali Kelas: <b>{{ $user->name }}</b> &bull; 
                Program Keahlian: <b>{{ $kelasSaya->jurusan?->nama ?? 'Umum' }}</b>
            </p>
        </div>

        <!-- TABEL MATRIKS JADWAL -->
        <table class="matrix-table">
            <thead>
                <tr>
                    <th style="width: 50px;">Jam Ke</th>
                    <th style="width: 75px;">Waktu</th>
                    <th class="th-hari">Senin</th>
                    <th class="th-hari">Selasa</th>
                    <th class="th-hari">Rabu</th>
                    <th class="th-hari">Kamis</th>
                    <th class="th-hari th-jumat">Jumat *)</th>
                    <th class="th-hari">Sabtu</th>
                </tr>
            </thead>
            <tbody>
                <!-- PEMBIASAAN AL-QUR'AN -->
                <tr class="row-pembiasaan">
                    <td colspan="2" style="font-family: monospace;">06.50 - 07.00</td>
                    <td colspan="6" style="background: #a7f3d0; color: #064e3b; font-weight: 800;">
                        10 Menit Pembiasaan Membaca Al-Qur'an Sebelum Memulai KBM
                    </td>
                </tr>

                @for($jam = 1; $jam <= 8; $jam++)
                <tr>
                    <td style="font-weight: 800; background: #f8fafc;">Jam {{ $jam }}</td>
                    <td style="font-family: monospace; font-size: 7pt; background: #f8fafc;">
                        @if($jam == 1) 07.00 - 07.45
                        @elseif($jam == 2) 07.45 - 08.30
                        @elseif($jam == 3) 08.30 - 09.15
                        @elseif($jam == 4) 09.15 - 10.00
                        @elseif($jam == 5) 10.30 - 11.15
                        @elseif($jam == 6) 11.15 - 12.00
                        @elseif($jam == 7) 12.30 - 13.15
                        @elseif($jam == 8) 13.15 - 14.00
                        @endif
                    </td>
                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h)
                    @php
                        if ($h === 'Jumat' && $jam > 6) {
                            echo '<td style="background:#f1f5f9; font-size:6.5pt; color:#94a3b8; font-weight:600;">Pulang (10.30)</td>';
                            continue;
                        }
                        if ($h === 'Senin' && $jam === 1) {
                            echo '<td style="background:#fef3c7; font-weight:800; font-size:7pt; color:#92400e;">Upacara Bendera</td>';
                            continue;
                        }

                        $sesi = $jadwalLengkap->get($h, collect())->first(function($j) use ($jam) {
                            return $jam >= $j->jam_ke_mulai && $jam <= $j->jam_ke_selesai;
                        });
                    @endphp
                    @if($sesi)
                    <td class="mapel-cell">
                        <div class="mapel-nama">{{ $sesi->mataPelajaran->nama ?? '-' }}</div>
                        <div class="guru-nama">{{ $sesi->guru?->name ?? 'Belum Ditugaskan' }}</div>
                        @if($sesi->ruang)
                        <div class="ruang-badge">R: {{ $sesi->ruang }}</div>
                        @endif
                    </td>
                    @else
                    <td style="color: #cbd5e1; font-weight: bold;">-</td>
                    @endif
                    @endforeach
                </tr>

                @if($jam == 4)
                <tr class="istirahat">
                    <td colspan="2">ISTIRAHAT 1</td>
                    <td colspan="6">ISTIRAHAT PERTAMA (10.00 - 10.30 WIB / Jumat: 09.00 - 09.30)</td>
                </tr>
                @endif
                @if($jam == 6)
                <tr class="istirahat">
                    <td colspan="2">ISTIRAHAT 2</td>
                    <td colspan="6">ISTIRAHAT KEDUA & SHOLAT DZUHUR (12.00 - 12.30 WIB)</td>
                </tr>
                @endif
                @endfor
            </tbody>
        </table>

        <!-- CATATAN KHUSUS & LEGENDA -->
        <div class="catatan-section">
            <div>
                <b>*) Catatan Khusus Hari Jumat:</b> Jam KBM @ 30 menit, istirahat setelah jam ke-4 (09.00-09.30 WIB), setelah jam ke-6 siswa pulang (10.30 WIB).
            </div>
            <div>
                Dicetak melalui SIM Akademik <b>HilalEdu</b> &bull; Tanggal: {{ date('d/m/Y') }}
            </div>
        </div>

        <!-- TANDA TANGAN -->
        <div class="ttd-grid">
            <div class="ttd-box">
                <p style="margin: 0; font-weight: 600;">Mengetahui,</p>
                <p style="margin: 2px 0 0 0; font-weight: 800;">Kepala SMK Plus Al-Hilal Arjawinangun</p>
                <div class="ttd-space"></div>
                <p style="margin: 0; font-weight: 800; text-decoration: underline; text-transform: uppercase;">
                    {{ $settings['nama_kepala_sekolah'] ?? 'Mukhammad Mansyur, S.Pt' }}
                </p>
                <p style="margin: 1px 0 0 0; font-size: 7pt; color: #475569;">
                    NIP. {{ $settings['nip_kepala_sekolah'] ?? '-' }}
                </p>
            </div>

            <div class="ttd-box">
                <p style="margin: 0; font-weight: 600;">{{ $settings['titimangsa'] ?? ('Arjawinangun, ' . date('d F Y')) }}</p>
                <p style="margin: 2px 0 0 0; font-weight: 800;">Wali Kelas {{ $namaKelas }}</p>
                <div class="ttd-space"></div>
                <p style="margin: 0; font-weight: 800; text-decoration: underline; text-transform: uppercase;">
                    {{ $user->name }}
                </p>
                <p style="margin: 1px 0 0 0; font-size: 7pt; color: #475569;">
                    NIP. {{ $user->nip ?? '-' }}
                </p>
            </div>
        </div>
    </div>

</body>
</html>
