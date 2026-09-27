<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Pelajaran Kelas {{ $kelasNama }} - HilalEdu</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #111;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 13px;
            margin: 0 0 4px 0;
        }
        .header p {
            margin: 0;
            font-size: 11px;
            color: #444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }
        th, td {
            border: 1px solid #333;
            padding: 5px 4px;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 10px;
        }
        .mapel-cell {
            text-align: left;
            padding: 4px 6px;
            background-color: #fafafa;
        }
        .mapel-nama {
            font-weight: bold;
            font-size: 10px;
        }
        .guru-nama {
            font-size: 9px;
            color: #047857;
        }
        .istirahat {
            background-color: #fffbeb;
            font-weight: bold;
            font-size: 9px;
        }
        .footer {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; font-weight: bold; background: #059669; color: #fff; border: none; border-radius: 6px; cursor: pointer;">
            🖨️ Cetak Dokumen Ini
        </button>
    </div>

    <div class="header">
        <h1>SMK PLUS AL-HILAL ARJAWINANGUN</h1>
        <h2>JADWAL PELAJARAN KELAS {{ strtoupper($kelasNama) }}</h2>
        <p>Tahun Ajaran {{ $tahunAjaran }} &bull; Semester {{ ucfirst($semester) }} &bull; Wali Kelas: {{ $waliKelas?->name ?? '-' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 55px;">Jam Ke</th>
                <th style="width: 80px;">Waktu</th>
                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h)
                <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @for($jam = 1; $jam <= 8; $jam++)
            <tr>
                <td><strong>Jam {{ $jam }}</strong></td>
                <td>
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
                        echo '<td style="background:#eee; font-size:9px; color:#888;">Pulang (10.30)</td>';
                        continue;
                    }
                    if ($h === 'Senin' && $jam === 1) {
                        echo '<td style="background:#fef3c7; font-weight:bold; font-size:9px;">Upacara Bendera</td>';
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
                    <div style="font-size: 8px; color: #888;">{{ $sesi->ruang }}</div>
                    @endif
                </td>
                @else
                <td style="color: #ccc;">-</td>
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

    <div class="footer">
        <div>
            Dicetak melalui Sistem Informasi Akademik <strong>HilalEdu</strong> pada: {{ date('d/m/Y H:i') }}
        </div>
        <div style="text-align: right;">
            Mengetahui,<br>
            <strong>Wali Kelas {{ $kelasNama }}</strong><br><br><br>
            <strong>{{ $waliKelas?->name ?? '_______________________' }}</strong>
        </div>
    </div>
</body>
</html>
