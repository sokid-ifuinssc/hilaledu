<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Pendidikan {{ $kalender->tahun_ajaran }} - {{ $schoolInfo['nama'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: #fff; color: #1e293b; font-size: 11px; line-height: 1.3; padding: 15px; }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }

        /* Kop Surat */
        .kop-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #000;
            position: relative;
            margin-bottom: 12px;
        }
        .kop-container::after {
            content: '';
            position: absolute;
            left: 0; right: 0; bottom: -4px;
            height: 1px;
            background: #000;
        }
        .kop-logo { width: 65px; height: 65px; object-fit: contain; }
        .kop-text { text-align: center; }
        .kop-title { font-size: 14px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; }
        .kop-school { font-size: 17px; font-weight: 900; text-transform: uppercase; color: #0f172a; }
        .kop-desc { font-size: 10px; color: #475569; margin-top: 2px; }

        /* Judul Dokumen */
        .doc-title { text-align: center; margin-bottom: 12px; }
        .doc-title h2 { font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; }
        .doc-title h3 { font-size: 12px; font-weight: 800; color: #1e3a8a; text-transform: uppercase; }

        /* Matriks Grid */
        .month-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }
        .month-card {
            border: 1.5px solid #64748b;
            border-radius: 4px;
            overflow: hidden;
        }
        .month-header {
            background: #1d4ed8;
            color: #fff;
            text-align: center;
            font-weight: 900;
            font-size: 10px;
            padding: 3px;
            text-transform: uppercase;
        }
        .month-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 9px;
        }
        .month-table th {
            padding: 2px 0;
            background: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            font-weight: 800;
            font-size: 8.5px;
        }
        .month-table th.sun { color: #dc2626; background: #fee2e2; }
        .month-table td {
            height: 18px;
            padding: 1px;
            font-weight: 700;
            border: 0.5px solid #e2e8f0;
        }
        .cell-red { background: #dc2626 !important; color: #fff !important; font-weight: 900; }
        .cell-yellow { background: #fde047 !important; color: #0f172a !important; font-weight: 900; }
        .cell-green { background: #bbf7d0 !important; color: #064e3b !important; font-weight: 800; }
        .cell-blue { background: #bfdbfe !important; color: #1e3a8a !important; font-weight: 800; }
        .cell-purple { background: #e9d5ff !important; color: #581c87 !important; font-weight: 800; }
        .cell-cyan { background: #a5f3fc !important; color: #164e63 !important; font-weight: 800; }

        /* Tabel Keterangan & Asesmen */
        .desc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-top: 8px;
        }
        .desc-table th {
            background: #86efac;
            color: #064e3b;
            padding: 5px 8px;
            font-weight: 900;
            text-align: left;
            border: 1px solid #475569;
            text-transform: uppercase;
        }
        .desc-table td {
            padding: 3.5px 8px;
            border: 1px solid #64748b;
            vertical-align: top;
        }

        /* Penilaian Table */
        .assessment-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-top: 10px;
            margin-bottom: 8px;
        }
        .assessment-table th {
            background: #86efac;
            color: #064e3b;
            padding: 5px 8px;
            font-weight: 900;
            text-align: left;
            border: 1px solid #475569;
            text-transform: uppercase;
        }
        .assessment-table td {
            padding: 3.5px 8px;
            border: 1px solid #64748b;
        }

        /* Tanda Tangan */
        .signature-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 16px;
            font-size: 10px;
            page-break-inside: avoid;
        }
        .sig-box { width: 45%; text-align: center; }
        .sig-space { height: 45px; }

        /* Floating action bar */
        .action-bar {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(8px);
            padding: 8px 16px;
            border-radius: 9999px;
            display: flex;
            gap: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            z-index: 999;
        }
        .action-btn {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .action-btn.secondary { background: #475569; }
    </style>
</head>
<body>

    <!-- Floating Buttons for Quick Print & Return -->
    <div class="action-bar no-print">
        @php
            $canManage = auth()->user() && auth()->user()->canManageAcademic();
            $backRoute = $canManage ? 'admin.kalender.index' : 'guru.kalender.index';
        @endphp
        <a href="{{ route($backRoute, ['tahun_ajaran' => $kalender->tahun_ajaran, 'semester' => $semester]) }}" class="action-btn secondary">
            &larr; Kembali ke Kalender
        </a>
        <button onclick="window.print()" class="action-btn">
            🖨️ Cetak Dokumen Ini
        </button>
    </div>

    <!-- SEMESTER 1 SECTION -->
    @if($semester === '1' || $semester === 'all')
    <div class="calendar-page">
        <!-- KOP SURAT SEKOLAH -->
        <div class="kop-container">
            <img src="{{ asset($schoolInfo['logo']) }}" alt="Logo" class="kop-logo" onerror="this.src='{{ asset('images/logo.png') }}'">
            <div class="kop-text">
                <div class="kop-title">Yayasan Pendidikan Islam Al-Hilal Arjawinangun</div>
                <div class="kop-school">{{ $schoolInfo['nama'] }} Arjawinangun</div>
                <div class="kop-desc">NPSN: {{ $schoolInfo['npsn'] }} &bull; Program Keahlian: TJKT, TO, AKL</div>
                <div class="kop-desc">{{ $schoolInfo['alamat'] }}</div>
            </div>
        </div>

        <!-- JUDUL KALENDER -->
        <div class="doc-title">
            <h2>Kalender Pendidikan Dan Akademik SMK Plus Al-Hilal</h2>
            <h3>Tahun Ajaran {{ $kalender->tahun_ajaran }} — SEMESTER 1</h3>
        </div>

        <!-- GRID 6 BULAN (JULI - DESEMBER) -->
        <div class="month-grid">
            @foreach($matrixSmt1 as $mData)
            <div class="month-card">
                <div class="month-header">{{ $mData['month_name'] }} {{ $mData['year'] }}</div>
                <table class="month-table">
                    <thead>
                        <tr>
                            <th class="sun">Minggu</th>
                            <th>Senin</th>
                            <th>Selasa</th>
                            <th>Rabu</th>
                            <th>Kamis</th>
                            <th>Jumat</th>
                            <th>Sabtu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mData['weeks'] as $week)
                        <tr>
                            @foreach($week as $cell)
                            @php
                                $cls = '';
                                if ($cell['is_current']) {
                                    if ($cell['primary_event']) {
                                        $cls = match($cell['primary_event']->warna_bg) {
                                            'red' => 'cell-red',
                                            'yellow' => 'cell-yellow',
                                            'green' => 'cell-green',
                                            'blue' => 'cell-blue',
                                            'purple' => 'cell-purple',
                                            'cyan' => 'cell-cyan',
                                            default => 'cell-red'
                                        };
                                    } elseif ($cell['is_sunday']) {
                                        $cls = 'cell-red';
                                    }
                                }
                            @endphp
                            <td class="{{ $cls }}">
                                {{ $cell['is_current'] ? $cell['day'] : '' }}
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach
        </div>

        <!-- TABEL KETERANGAN KEGIATAN SEMESTER 1 -->
        <div style="font-weight: 900; font-size: 10px; text-transform: uppercase; margin-top: 6px;">KETERANGAN :</div>
        <table class="desc-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Tanggal</th>
                    <th style="width: 75%;">Kegiatan / Hari Libur</th>
                </tr>
            </thead>
            <tbody>
                @forelse($eventsSmt1 as $ev)
                <tr>
                    <td style="font-weight: 700; white-space: nowrap;">{{ $ev->formatted_tanggal }}</td>
                    <td>
                        <strong>{{ $ev->judul_kegiatan }}</strong>
                        @if($ev->keterangan)
                        <span style="color: #64748b;">({{ $ev->keterangan }})</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" style="text-align: center; color: #94a3b8;">Belum ada rincian keterangan agenda Semester 1.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- TANDA TANGAN PENGESAHAN KEPSEK & WAKA -->
        <div class="signature-container">
            <div class="sig-box">
                <div>Mengetahui,</div>
                <div style="font-weight: 800;">Kepala SMK Plus Al-Hilal</div>
                <div class="sig-space"></div>
                <div style="font-weight: 900; text-decoration: underline;">{{ $schoolInfo['kepsek'] }}</div>
                <div>NIP. {{ $schoolInfo['nip_kepsek'] }}</div>
            </div>

            <div class="sig-box">
                <div>{{ $schoolInfo['titimangsa'] }}</div>
                <div style="font-weight: 800;">Wakasek Kurikulum & Akademik</div>
                <div class="sig-space"></div>
                <div style="font-weight: 900; text-decoration: underline;">{{ $schoolInfo['waka_kur'] }}</div>
                <div>NIP. {{ $schoolInfo['nip_waka'] }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- SEMESTER 2 SECTION -->
    @if($semester === '2' || $semester === 'all')
    <div class="calendar-page {{ $semester === 'all' ? 'page-break' : '' }}">
        <!-- KOP SURAT SEKOLAH -->
        <div class="kop-container">
            <img src="{{ asset($schoolInfo['logo']) }}" alt="Logo" class="kop-logo" onerror="this.src='{{ asset('images/logo.png') }}'">
            <div class="kop-text">
                <div class="kop-title">Yayasan Pendidikan Islam Al-Hilal Arjawinangun</div>
                <div class="kop-school">{{ $schoolInfo['nama'] }} Arjawinangun</div>
                <div class="kop-desc">NPSN: {{ $schoolInfo['npsn'] }} &bull; Program Keahlian: TJKT, TO, AKL</div>
                <div class="kop-desc">{{ $schoolInfo['alamat'] }}</div>
            </div>
        </div>

        <!-- JUDUL KALENDER -->
        <div class="doc-title">
            <h2>Kalender Pendidikan Dan Akademik SMK Plus Al-Hilal</h2>
            <h3>Tahun Ajaran {{ $kalender->tahun_ajaran }} — SEMESTER 2</h3>
        </div>

        <!-- GRID BULAN SEMESTER 2 (JANUARI - JULI) -->
        <div class="month-grid">
            @foreach($matrixSmt2 as $mData)
            <div class="month-card">
                <div class="month-header">{{ $mData['month_name'] }} {{ $mData['year'] }}</div>
                <table class="month-table">
                    <thead>
                        <tr>
                            <th class="sun">Minggu</th>
                            <th>Senin</th>
                            <th>Selasa</th>
                            <th>Rabu</th>
                            <th>Kamis</th>
                            <th>Jumat</th>
                            <th>Sabtu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mData['weeks'] as $week)
                        <tr>
                            @foreach($week as $cell)
                            @php
                                $cls = '';
                                if ($cell['is_current']) {
                                    if ($cell['primary_event']) {
                                        $cls = match($cell['primary_event']->warna_bg) {
                                            'red' => 'cell-red',
                                            'yellow' => 'cell-yellow',
                                            'green' => 'cell-green',
                                            'blue' => 'cell-blue',
                                            'purple' => 'cell-purple',
                                            'cyan' => 'cell-cyan',
                                            default => 'cell-red'
                                        };
                                    } elseif ($cell['is_sunday']) {
                                        $cls = 'cell-red';
                                    }
                                }
                            @endphp
                            <td class="{{ $cls }}">
                                {{ $cell['is_current'] ? $cell['day'] : '' }}
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endforeach
        </div>

        <!-- TABEL PERKIRAAN JADWAL PENILAIAN AKHIR JENJANG -->
        <div style="font-weight: 900; font-size: 10px; text-transform: uppercase; margin-top: 4px;">
            PERKIRAAN JADWAL PENILAIAN AKHIR JENJANG TAHUN AJARAN {{ $kalender->tahun_ajaran }}
        </div>
        <table class="assessment-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Satuan Pendidikan</th>
                    <th style="width: 40%;">Asesmen Sumatif Akhir Jenjang (ASAJ)</th>
                    <th style="width: 35%;">Uji Kompetensi Keahlian (UKK) / TKA</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background: #eff6ff; font-weight: 800;">
                    <td style="color: #1e3a8a;">SMK (Sekolah Menengah Kejuruan)</td>
                    <td>22 Maret &ndash; 2 April 2027</td>
                    <td style="color: #581c87;">29 Maret &ndash; 30 April 2027 (UKK SMK)</td>
                </tr>
                <tr>
                    <td>SMA / SMALB / MA</td>
                    <td>22 Maret &ndash; 2 April 2027</td>
                    <td>25 Oktober &ndash; 5 November 2026 (TKA)</td>
                </tr>
                <tr>
                    <td>SMP / SMPLB / MTs</td>
                    <td>26 April &ndash; 7 Mei 2027</td>
                    <td>5 &ndash; 16 April 2027 (TKA)</td>
                </tr>
            </tbody>
        </table>

        <!-- TABEL KETERANGAN KEGIATAN SEMESTER 2 -->
        <div style="font-weight: 900; font-size: 10px; text-transform: uppercase; margin-top: 6px;">KETERANGAN :</div>
        <table class="desc-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Tanggal</th>
                    <th style="width: 75%;">Kegiatan / Hari Libur</th>
                </tr>
            </thead>
            <tbody>
                @forelse($eventsSmt2 as $ev)
                <tr>
                    <td style="font-weight: 700; white-space: nowrap;">{{ $ev->formatted_tanggal }}</td>
                    <td>
                        <strong>{{ $ev->judul_kegiatan }}</strong>
                        @if($ev->keterangan)
                        <span style="color: #64748b;">({{ $ev->keterangan }})</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" style="text-align: center; color: #94a3b8;">Belum ada rincian keterangan agenda Semester 2.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- TANDA TANGAN PENGESAHAN KEPSEK & WAKA -->
        <div class="signature-container">
            <div class="sig-box">
                <div>Mengetahui,</div>
                <div style="font-weight: 800;">Kepala SMK Plus Al-Hilal</div>
                <div class="sig-space"></div>
                <div style="font-weight: 900; text-decoration: underline;">{{ $schoolInfo['kepsek'] }}</div>
                <div>NIP. {{ $schoolInfo['nip_kepsek'] }}</div>
            </div>

            <div class="sig-box">
                <div>{{ $schoolInfo['titimangsa'] }}</div>
                <div style="font-weight: 800;">Wakasek Kurikulum & Akademik</div>
                <div class="sig-space"></div>
                <div style="font-weight: 900; text-decoration: underline;">{{ $schoolInfo['waka_kur'] }}</div>
                <div>NIP. {{ $schoolInfo['nip_waka'] }}</div>
            </div>
        </div>
    </div>
    @endif

</body>
</html>
