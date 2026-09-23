<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Penempatan Prakerin - SMK Plus Al Hilal</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #999;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .text-center { text-align: center; }
        .footer {
            margin-top: 40px;
            float: right;
            text-align: center;
            width: 250px;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>SMK PLUS AL HILAL</h2>
        <p>REKAPITULASI PENEMPATAN PRAKTIK KERJA LAPANGAN (PRAKERIN)</p>
        @if($periode) <p><strong>Periode: {{ $periode->nama }}</strong></p> @endif
        @if($dudi) <p><strong>Mitra DU/DI: {{ $dudi->nama }}</strong></p> @endif
        <p style="font-size: 10px;">Dicetak pada: {{ date('d F Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="12%">NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas / Jurusan</th>
                <th>Mitra DU/DI</th>
                <th>Guru Pembimbing</th>
                <th>Instruktur DU/DI</th>
                <th>Periode Waktu</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penempatans as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->siswa->nis ?? '-' }}</td>
                    <td><strong>{{ $item->siswa->nama_lengkap ?? '-' }}</strong></td>
                    <td>{{ $item->siswa?->kelas?->nama_lengkap ?? '-' }}</td>
                    <td>{{ $item->dudi->nama ?? '-' }}</td>
                    <td>{{ $item->guru?->nama_lengkap ?? '-' }}</td>
                    <td>{{ $item->pembimbingDudi->nama ?? '-' }}</td>
                    <td class="text-center small">
                        {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/y') : '-' }} -
                        {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/y') : '-' }}
                    </td>
                    <td class="text-center">{{ $item->status_label }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data penempatan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Cianjur, {{ date('d F Y') }}<br>Kepala Program Keahlian / Hubin,</p>
        <br><br><br>
        <p><strong>__________________________</strong><br>NIP/NUPTK: -</p>
    </div>
</body>
</html>
