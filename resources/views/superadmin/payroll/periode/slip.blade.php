<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Honor - {{ $payroll->user->name }} - {{ $periode->nama_periode }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            color: #1e293b;
            background: #f1f5f9;
            display: flex;
            justify-content: center;
            padding: 40px;
            margin: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .action-bar {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            padding: 12px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            z-index: 100;
        }
        .btn-print {
            background: #059669;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        .btn-print:hover {
            background: #047857;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
        }
        .slip-wrapper {
            background: #fff;
            width: 800px; /* A4 width roughly */
            min-height: 1050px;
            padding: 60px 80px;
            box-sizing: border-box;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-radius: 4px;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #059669;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header-left {
            display: flex;
            flex-direction: column;
        }
        .school-name {
            font-size: 24px;
            font-weight: 800;
            color: #064e3b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .school-address {
            font-size: 12px;
            color: #64748b;
        }
        .header-right {
            text-align: right;
        }
        .document-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .document-period {
            font-size: 14px;
            font-weight: 600;
            color: #059669;
            background: #ecfdf5;
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-block;
        }
        
        .employee-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            background: #f8fafc;
            padding: 20px 25px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 35px;
        }
        .info-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .info-row {
            display: flex;
            align-items: center;
            font-size: 14px;
        }
        .info-label {
            width: 120px;
            color: #64748b;
            font-weight: 500;
        }
        .info-value {
            font-weight: 700;
            color: #0f172a;
        }

        .payroll-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }
        .payroll-table th, .payroll-table td {
            padding: 14px 20px;
            text-align: left;
        }
        .payroll-table thead {
            background: #f8fafc;
        }
        .payroll-table th {
            font-weight: 700;
            color: #475569;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }
        .payroll-table td {
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-weight: 500;
        }
        .payroll-table tbody tr:last-child td {
            border-bottom: none;
        }
        .amount-cell {
            text-align: right !important;
            font-family: monospace;
            font-size: 15px;
        }
        .section-header td {
            background: #f1f5f9;
            font-weight: 700 !important;
            color: #0f172a !important;
        }
        .text-green {
            color: #059669 !important;
        }
        .text-red {
            color: #dc2626 !important;
        }
        .sub-total td {
            font-weight: 700 !important;
            border-top: 2px solid #e2e8f0;
            background: #f8fafc;
        }

        .grand-total-box {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 50px;
        }
        .grand-total-content {
            background: #064e3b;
            color: white;
            padding: 20px 30px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 30px;
            box-shadow: 0 10px 25px rgba(6, 78, 59, 0.2);
        }
        .grand-total-label {
            font-size: 16px;
            font-weight: 500;
            opacity: 0.9;
        }
        .grand-total-value {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: auto;
            padding-top: 40px;
        }
        .notes {
            flex: 1;
            padding-right: 40px;
        }
        .notes h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #475569;
        }
        .notes p {
            margin: 0;
            font-size: 12px;
            color: #64748b;
            line-height: 1.6;
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border: 1px dashed #cbd5e1;
        }
        .signature-box {
            width: 250px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .signature-date {
            font-size: 14px;
            color: #475569;
            margin-bottom: 10px;
        }
        .signature-role {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 80px;
        }
        .signature-name {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            text-decoration: underline;
            margin-bottom: 4px;
        }
        .signature-nip {
            font-size: 13px;
            color: #64748b;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 120px;
            font-weight: 900;
            color: rgba(0, 0, 0, 0.02);
            white-space: nowrap;
            pointer-events: none;
            z-index: 0;
        }

        @media print {
            @page {
                size: A5 portrait; /* Setengah A4 */
                margin: 10mm;
            }
            body {
                background: #fff;
                padding: 0;
                display: block;
                font-size: 12px; /* Dikecilkan untuk pas di A5 */
            }
            .action-bar {
                display: none !important;
            }
            .slip-wrapper {
                width: 100%;
                min-height: auto;
                box-shadow: none;
                padding: 0;
            }
            .grand-total-content {
                box-shadow: none;
                border: 2px solid #064e3b;
            }
        }
    </style>
</head>
<body>

    <div class="action-bar no-print">
        <button onclick="window.print();" class="btn-print">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Dokumen
        </button>
    </div>

    @php
        // Persiapkan Data
        $honorPerJam = $payroll->user->payrollSetting->honor_per_jam ?? 0;
        $jamMengajar = $payroll->jumlah_jam_mengajar ?? 0;
        
        $itemHonor = $payroll->penerimaanItems->firstWhere('nama_komponen', 'Honor Jam Mengajar');
        $totalHonorJam = $itemHonor ? $itemHonor->nominal : ($jamMengajar * $honorPerJam);

        $hariKehadiran = $payroll->jumlah_kehadiran ?? 0;
        
        $itemTransport = $payroll->penerimaanItems->firstWhere('nama_komponen', 'Tunjangan Kehadiran & Transport');
        $totalTransport = $itemTransport ? $itemTransport->nominal : 0;
        $rateTransport = ($hariKehadiran > 0) ? ($totalTransport / $hariKehadiran) : 0;

        $tunjanganLainnya = $payroll->penerimaanItems->filter(function($item) {
            return !in_array($item->nama_komponen, ['Honor Jam Mengajar', 'Tunjangan Kehadiran & Transport']);
        });
    @endphp

    <div class="slip-wrapper">
        <div class="watermark">HILAL EDU</div>

        <div class="header">
            <div class="header-left">
                <div class="school-name">{{ strtoupper($sekolah->nama_sekolah ?? 'SMK PLUS AL HILAL') }}</div>
                <div class="school-address">Terakreditasi "B" - Sistem Informasi Manajemen Akademik & Kepegawaian</div>
            </div>
            <div class="header-right">
                <div class="document-title">SLIP GAJI & BISYARAH PEGAWAI</div>
                <div class="document-period">PERIODE: {{ strtoupper($periode->nama_periode) }}</div>
            </div>
        </div>
        
        <div class="employee-info">
            <div class="info-group">
                <div class="info-row">
                    <div class="info-label">Nama Lengkap</div>
                    <div class="info-value">: {{ $payroll->user->name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">ID Pegawai</div>
                    <div class="info-value">: {{ $payroll->user->username ?? '-' }}</div>
                </div>
            </div>
            <div class="info-group">
                <div class="info-row">
                    <div class="info-label">Status/Jabatan</div>
                    <div class="info-value" style="text-transform: capitalize;">: {{ $payroll->user->role }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Cetak</div>
                    <div class="info-value">: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                </div>
            </div>
        </div>

        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Deskripsi Komponen</th>
                    <th style="text-align: center;">Keterangan</th>
                    <th style="text-align: right;">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <!-- PENERIMAAN -->
                <tr class="section-header">
                    <td colspan="3">A. PENERIMAAN</td>
                </tr>
                <tr>
                    <td>Honor Jam Mengajar</td>
                    <td style="text-align: center; color: #64748b; font-size: 13px;">{{ $jamMengajar }} Jam × Rp{{ number_format($honorPerJam, 0, ',', '.') }}</td>
                    <td class="amount-cell text-green">{{ number_format($totalHonorJam, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tunjangan Kehadiran & Transport</td>
                    <td style="text-align: center; color: #64748b; font-size: 13px;">{{ $hariKehadiran }} Hari × Rp{{ number_format($rateTransport, 0, ',', '.') }}</td>
                    <td class="amount-cell text-green">{{ number_format($totalTransport, 0, ',', '.') }}</td>
                </tr>

                @if($tunjanganLainnya->count() > 0)
                    @foreach($tunjanganLainnya as $item)
                        <tr>
                            <td>{{ str_replace('Tugas Tambahan: ', '', $item->nama_komponen) }}</td>
                            <td style="text-align: center; color: #64748b; font-size: 13px;">-</td>
                            <td class="amount-cell text-green">{{ number_format($item->nominal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif
                <tr class="sub-total">
                    <td colspan="2" style="text-align: right;">TOTAL PENERIMAAN KOTOR :</td>
                    <td class="amount-cell text-green">Rp {{ number_format($payroll->total_penerimaan, 0, ',', '.') }}</td>
                </tr>

                <!-- POTONGAN -->
                <tr class="section-header">
                    <td colspan="3">B. POTONGAN</td>
                </tr>
                @if($payroll->potonganItems->count() > 0)
                    @foreach($payroll->potonganItems as $item)
                        <tr>
                            <td>{{ $item->nama_komponen }}</td>
                            <td style="text-align: center; color: #64748b; font-size: 13px;">-</td>
                            <td class="amount-cell text-red">- {{ number_format($item->nominal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="sub-total">
                        <td colspan="2" style="text-align: right;">TOTAL POTONGAN :</td>
                        <td class="amount-cell text-red">- Rp {{ number_format($payroll->total_potongan, 0, ',', '.') }}</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="3" style="text-align: center; color: #94a3b8; font-style: italic; padding: 20px;">
                            Tidak ada data potongan.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="grand-total-box">
            <div class="grand-total-content">
                <div class="grand-total-label">TOTAL PENERIMAAN BERSIH :</div>
                <div class="grand-total-value">Rp {{ number_format($payroll->gaji_bersih, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="footer">
            <div class="notes">
                <h4>Catatan:</h4>
                <p>
                    1. Slip gaji ini adalah dokumen rahasia dan sah, dicetak secara otomatis dari Sistem Informasi HilalEdu.<br>
                    2. Apabila terdapat ketidaksesuaian perhitungan jam kehadiran atau potongan, harap segera menghubungi Bagian Keuangan/Bendahara maksimal 3 hari kerja setelah slip diterbitkan.<br>
                </p>
            </div>
            <div class="signature-box">
                <div class="signature-date">Arjawinangun, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                <div class="signature-role">Bendahara Sekolah</div>
                
                <div class="signature-name">Elin Tamaya, S.E</div>
                <div class="signature-nip">NIP/NUPTK: -</div>
            </div>
        </div>
    </div>

</body>
</html>
