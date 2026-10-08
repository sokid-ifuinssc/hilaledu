<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leger Nilai Kelas {{ $kelas->nama_kelas }} - {{ $tahunAjaran }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm 1cm 1cm 1cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            font-size: 8pt;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 13pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header h3 {
            margin: 2px 0;
            font-size: 10.5pt;
            font-weight: bold;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 8px;
            font-size: 8.5pt;
        }
        .meta-table td {
            padding: 2px 3px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #444;
            padding: 3px 4px;
        }
        table.data-table th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .ttd-section {
            margin-top: 25px;
            width: 100%;
        }
        .ttd-box {
            float: right;
            width: 240px;
            text-align: center;
            font-size: 8.5pt;
        }
        .ttd-box-left {
            float: left;
            width: 240px;
            text-align: center;
            font-size: 8.5pt;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px; padding: 8px; background: #e0f2fe; border: 1px solid #7dd3fc; text-align: center;">
        <button onclick="window.print()" style="padding: 6px 14px; background: #0284c7; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak Leger Nilai
        </button>
        <button onclick="window.close()" style="padding: 6px 14px; background: #64748b; color: white; border: none; border-radius: 4px; margin-left: 8px; cursor: pointer;">
            Tutup
        </button>
    </div>

    <div class="header">
        <h2>LEGER NILAI HASIL BELAJAR SISWA</h2>
        <h3>TAHUN AJARAN {{ $tahunAjaran }} - SEMESTER {{ strtoupper($semester) }}</h3>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 12%; font-weight: bold;">Kelas / Rombel</td>
            <td style="width: 38%;">: {{ $kelas->nama_kelas }} ({{ $kelas->jurusan ?: 'Umum' }})</td>
            <td style="width: 15%; font-weight: bold;">Wali Kelas</td>
            <td style="width: 35%;">: {{ $kelas->waliKelas->name ?? '-' }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 2%;">Rnk</th>
                <th rowspan="2" style="width: 16%;">Nama Siswa</th>
                <th rowspan="2" style="width: 7%;">NISN</th>
                <th colspan="{{ $mapels->count() }}">Mata Pelajaran</th>
                <th rowspan="2" style="width: 4%;">Total</th>
                <th rowspan="2" style="width: 4%;">Rata</th>
                <th rowspan="2" style="width: 14%;">Ekstrakurikuler</th>
                <th colspan="4">Kehadiran</th>
            </tr>
            <tr>
                @foreach($mapels as $m)
                    <th style="font-size: 6.5pt;">{{ $m->kode_mapel ?: substr($m->nama, 0, 5) }}</th>
                @endforeach
                <th style="width: 2%;">H</th>
                <th style="width: 2%;">I</th>
                <th style="width: 2%;">S</th>
                <th style="width: 2%;">A</th>
            </tr>
        </thead>
        <tbody>
            @forelse($legerData as $row)
                <tr>
                    <td class="text-center font-bold">{{ $row['rank'] }}</td>
                    <td class="font-bold">{{ $row['siswa']->nama }}</td>
                    <td class="text-center">{{ $row['siswa']->nisn ?: ($row['siswa']->nis ?: '-') }}</td>

                    @foreach($mapels as $m)
                        @php $n = $row['nilai_mapels'][$m->id] ?? null; @endphp
                        <td class="text-center">{{ $n !== null ? number_format($n, 0) : '-' }}</td>
                    @endforeach

                    <td class="text-center font-bold">{{ number_format($row['total_nilai'], 0) }}</td>
                    <td class="text-center font-bold">{{ number_format($row['rata_rata'], 1) }}</td>

                    <td>
                        @if(count($row['eskul_list']) > 0)
                            @foreach($row['eskul_list'] as $es)
                                <span>{{ $es['nama'] }} ({{ $es['predikat'] ?: ($es['nilai'] ? number_format($es['nilai'],0) : '-') }}) </span>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>

                    <td class="text-center">{{ $row['absensi']['H'] }}</td>
                    <td class="text-center">{{ $row['absensi']['I'] }}</td>
                    <td class="text-center">{{ $row['absensi']['S'] }}</td>
                    <td class="text-center">{{ $row['absensi']['A'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 8 + $mapels->count() }}" class="text-center">Tidak ada data siswa.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="ttd-section clearfix">
        <div class="ttd-box-left">
            <p>Mengetahui,<br>Kepala Sekolah</p>
            <br><br><br>
            <p><b><u>( ........................................... )</u></b><br>NIP. -</p>
        </div>
        <div class="ttd-box">
            <p>Ditetapkan di: Sekolah<br>Wali Kelas {{ $kelas->nama_kelas }}</p>
            <br><br><br>
            <p><b><u>{{ $kelas->waliKelas->name ?? '( ........................................... )' }}</u></b><br>NIP. {{ $kelas->waliKelas->nip ?? '-' }}</p>
        </div>
    </div>
</body>
</html>
