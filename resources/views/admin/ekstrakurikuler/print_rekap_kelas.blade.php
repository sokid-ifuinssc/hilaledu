<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Ekstrakurikuler Kelas {{ $kelas->nama_kelas }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111;
            font-size: 11pt;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .header h2 {
            margin: 0;
            font-size: 15pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header h3 {
            margin: 2px 0;
            font-size: 13pt;
            font-weight: bold;
        }
        .header p {
            margin: 0;
            font-size: 9.5pt;
            color: #333;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 14px;
            font-size: 10pt;
        }
        .meta-table td {
            padding: 2px 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #333;
            padding: 5px 6px;
        }
        table.data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .ttd-section {
            margin-top: 35px;
            width: 100%;
        }
        .ttd-box {
            float: right;
            width: 250px;
            text-align: center;
            font-size: 10.5pt;
        }
        .ttd-box-left {
            float: left;
            width: 250px;
            text-align: center;
            font-size: 10.5pt;
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
    <div class="no-print" style="margin-bottom: 20px; padding: 10px; background: #e0f2fe; border: 1px solid #7dd3fc; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0284c7; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak Dokumen / Simpan PDF
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: white; border: none; border-radius: 4px; margin-left: 8px; cursor: pointer;">
            Tutup
        </button>
    </div>

    <div class="header">
        <h2>LAPORAN KEGIATAN & NILAI EKSTRAKURIKULER SISWA</h2>
        <h3>TAHUN AJARAN {{ date('Y') }}/{{ date('Y') + 1 }}</h3>
        <p>Sistem Informasi Akademik & Kesiswaan Terpadu HilalEdu</p>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%; font-weight: bold;">Kelas / Rombel</td>
            <td style="width: 35%;">: {{ $kelas->nama_kelas }} ({{ $kelas->jurusan ?: 'Umum' }})</td>
            <td style="width: 18%; font-weight: bold;">Tingkat</td>
            <td style="width: 32%;">: Kelas {{ $kelas->tingkat ?: '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Wali Kelas</td>
            <td>: {{ $kelas->waliKelas->name ?? '-' }}</td>
            <td style="font-weight: bold;">Jumlah Siswa</td>
            <td>: {{ $rekapData->count() }} Siswa</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 24%;">Nama Siswa</th>
                <th style="width: 12%;">NISN/NIS</th>
                <th style="width: 22%;">Ekstrakurikuler</th>
                <th style="width: 10%;">Kehadiran</th>
                <th style="width: 8%;">Nilai</th>
                <th style="width: 8%;">Predikat</th>
                <th style="width: 12%;">Catatan Pembina</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekapData as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $row['siswa']->nama }}</td>
                    <td class="text-center">{{ $row['siswa']->nisn ?: ($row['siswa']->nis ?: '-') }}</td>
                    
                    @if(count($row['eskul_details']) > 0)
                        <td>
                            @foreach($row['eskul_details'] as $det)
                                <div>• {{ $det['eskul_nama'] }} ({{ ucfirst($det['jabatan']) }})</div>
                            @endforeach
                        </td>
                        <td class="text-center font-bold">
                            @foreach($row['eskul_details'] as $det)
                                <div>{{ $det['persentase_kehadiran'] }}%</div>
                            @endforeach
                        </td>
                        <td class="text-center font-bold">
                            @foreach($row['eskul_details'] as $det)
                                <div>{{ $det['nilai_angka'] !== null ? number_format($det['nilai_angka'], 0) : '-' }}</div>
                            @endforeach
                        </td>
                        <td class="text-center font-bold">
                            @foreach($row['eskul_details'] as $det)
                                <div>{{ $det['nilai_huruf'] ?: '-' }}</div>
                            @endforeach
                        </td>
                        <td style="font-size: 8.5pt;">
                            @foreach($row['eskul_details'] as $det)
                                <div>{{ $det['catatan_nilai'] ?: '-' }}</div>
                            @endforeach
                        </td>
                    @else
                        <td colspan="5" style="text-align: center; color: #777; font-style: italic;">
                            Belum Mengikuti Ekstrakurikuler
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data siswa.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="ttd-section clearfix">
        <div class="ttd-box-left">
            <p>Mengetahui,<br>Waka Kesiswaan</p>
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
