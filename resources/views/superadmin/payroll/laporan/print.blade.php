<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penggajian & Bisyarah</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #fff;
            padding: 20px;
            margin: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        @page {
            size: A4 landscape;
            margin: 15mm;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #059669;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            color: #064e3b;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            color: #64748b;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
        }
        th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            text-align: center;
            text-transform: uppercase;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        
        .summary-box {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        .summary-content {
            border: 2px solid #059669;
            padding: 15px;
            border-radius: 8px;
            background: #ecfdf5;
            width: 300px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 50px;
        }
        .signature {
            text-align: center;
            width: 250px;
        }
        .signature-title {
            margin-bottom: 70px;
        }

        @media print {
            .btn-print { display: none; }
        }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()" style="position:fixed; top:20px; right:20px; padding:10px 20px; background:#059669; color:#fff; border:none; border-radius:5px; cursor:pointer;">Cetak Laporan</button>

    <div class="header">
        <h2>REKAPITULASI PENGGAJIAN & BISYARAH</h2>
        <p>SMK Plus Al Hilal - Laporan Hasil Filter</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="10%">No Slip</th>
                <th width="15%">Nama Pegawai</th>
                <th width="12%">Periode Kegiatan</th>
                <th width="15%">Penerimaan (Rp)</th>
                <th width="15%">Potongan (Rp)</th>
                <th width="15%">Gaji Bersih (Rp)</th>
                <th width="15%">Ttd Penerima</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalPenerimaan = 0;
                $totalPotongan = 0;
                $totalBersih = 0;
            @endphp

            @forelse($payrolls as $index => $p)
                @php
                    $penerimaan = $p->gaji_pokok + $p->total_honor_jam + $p->total_tunjangan;
                    $potongan = $p->total_potongan;
                    $bersih = $p->gaji_bersih;

                    $totalPenerimaan += $penerimaan;
                    $totalPotongan += $potongan;
                    $totalBersih += $bersih;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $p->nomor_slip }}</td>
                    <td>{{ $p->user->name }}</td>
                    <td class="text-center">{{ $p->periode->nama_periode }}</td>
                    <td class="text-right">{{ number_format($penerimaan, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($potongan, 0, ',', '.') }}</td>
                    <td class="text-right fw-bold">{{ number_format($bersih, 0, ',', '.') }}</td>
                    <td></td> <!-- Kolom tanda tangan -->
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data penggajian.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($payrolls) > 0)
        <tfoot>
            <tr style="background: #f8fafc;">
                <td colspan="4" class="text-center fw-bold">TOTAL KESELURUHAN</td>
                <td class="text-right fw-bold">{{ number_format($totalPenerimaan, 0, ',', '.') }}</td>
                <td class="text-right fw-bold">{{ number_format($totalPotongan, 0, ',', '.') }}</td>
                <td class="text-right fw-bold" style="color: #059669;">{{ number_format($totalBersih, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        <div class="signature">
            <div class="signature-title">
                Cirebon, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Mengetahui,<br>
                <strong>Bendahara / Kepala Sekolah</strong>
            </div>
            <div>
                <strong>___________________________</strong><br>
                NIP. 
            </div>
        </div>
    </div>
</body>
</html>
