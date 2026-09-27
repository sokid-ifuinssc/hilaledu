<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan Tagihan - HilalEdu</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; margin: 0; padding: 2cm; }
        .header { text-align: center; border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 16pt; text-transform: uppercase; }
        .header h2 { margin: 5px 0; font-size: 14pt; }
        .header p { margin: 0; font-size: 11pt; }
        .report-title { text-align: center; margin-bottom: 20px; text-transform: uppercase; font-weight: bold; font-size: 14pt; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 2px 0; vertical-align: top; }
        .info-table td:first-child { width: 150px; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 11pt; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        .data-table th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
        .data-table td.money { text-align: right; }
        .summary-box { border: 1px solid #000; padding: 15px; margin-bottom: 30px; }
        .summary-box h3 { margin-top: 0; text-align: center; font-size: 12pt; }
        .summary-box table { width: 100%; }
        .summary-box td { padding: 5px 0; }
        .summary-box .money { text-align: right; font-weight: bold; }
        .footer { width: 100%; margin-top: 50px; }
        .signature-box { float: right; width: 300px; text-align: center; }
        .signature-box p { margin: 0; }
        .signature-space { height: 80px; }
        @media print {
            body { padding: 0; }
            @page { margin: 2cm; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SMK Plus Al Hilal</h1>
        <h2>Laporan Keuangan & Tagihan Siswa</h2>
        <p>Jl. Raya Pembangunan No. 1, Kabupaten Cirebon, Jawa Barat</p>
    </div>

    <div class="report-title">
        REKAPITULASI TAGIHAN SISWA
    </div>

    <table class="info-table">
        <tr>
            <td>Bulan / Tahun</td>
            <td>: {{ date('F', mktime(0,0,0,$bulan,1)) }} {{ $tahun }}</td>
        </tr>
        <tr>
            <td>Tanggal Dicetak</td>
            <td>: {{ date('d F Y') }}</td>
        </tr>
    </table>

    <div class="summary-box">
        <h3>RINGKASAN KEUANGAN</h3>
        <table>
            <tr>
                <td>Total Tagihan Keseluruhan</td>
                <td class="money">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Dana Masuk (Telah Terbayar)</td>
                <td class="money">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Tunggakan</td>
                <td class="money">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="2"><hr style="border-top:1px dashed #000; margin:10px 0;"></td>
            </tr>
            <tr>
                <td><strong>Total Pembayaran Masuk Bulan Ini ({{ date('F', mktime(0,0,0,$bulan,1)) }})</strong></td>
                <td class="money" style="font-size: 13pt;">Rp {{ number_format($pembayaranBulanIni, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 30%">Nama Siswa</th>
                <th style="width: 15%">Kelas</th>
                <th style="width: 16%">Total Tagihan</th>
                <th style="width: 16%">Telah Terbayar</th>
                <th style="width: 18%">Sisa Tunggakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($siswas as $index => $siswa)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $siswa->name }}</td>
                <td style="text-align: center;">{{ $siswa->kelasModel->nama ?? '-' }}</td>
                <td class="money">Rp {{ number_format($siswa->total_tagihan, 0, ',', '.') }}</td>
                <td class="money">Rp {{ number_format($siswa->total_terbayar, 0, ',', '.') }}</td>
                <td class="money">Rp {{ number_format($siswa->sisa_tunggakan, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">Belum ada data siswa.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            <p>Mengetahui,</p>
            <p><strong>Kepala Sekolah</strong></p>
            <div class="signature-space"></div>
            <p>_______________________</p>
            <p>NIP. </p>
        </div>
        <div class="signature-box" style="float: left;">
            <p>Cirebon, {{ date('d F Y') }}</p>
            <p><strong>Bendahara Sekolah</strong></p>
            <div class="signature-space"></div>
            <p>_______________________</p>
            <p>NIP. </p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
