<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Slip Gaji Guru - {{ $periode->nama_periode }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            color: #1e293b;
            background: #f1f5f9;
            margin: 0;
            padding: 30px 20px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .action-bar {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            padding: 12px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-print {
            background: #059669;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        .btn-print:hover {
            background: #047857;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
        }
        .btn-close {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }
        .btn-close:hover {
            background: #e2e8f0;
        }
        .sheet {
            background: #fff;
            width: 800px;
            min-height: 1050px;
            padding: 50px 60px;
            box-sizing: border-box;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-radius: 6px;
            margin: 0 auto 40px auto;
            page-break-after: always;
        }
        .sheet:last-of-type {
            page-break-after: auto;
            margin-bottom: 0;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #059669;
            padding-bottom: 18px;
            margin-bottom: 25px;
        }
        .header-left {
            display: flex;
            flex-direction: column;
        }
        .school-name {
            font-size: 22px;
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
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .document-period {
            font-size: 13px;
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
            gap: 16px;
            background: #f8fafc;
            padding: 16px 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 25px;
        }
        .info-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .info-row {
            display: flex;
            align-items: center;
            font-size: 13px;
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
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            font-size: 13px;
        }
        .payroll-table th, .payroll-table td {
            padding: 11px 16px;
            text-align: left;
        }
        .payroll-table thead {
            background: #f8fafc;
        }
        .payroll-table th {
            font-weight: 700;
            color: #475569;
            font-size: 12px;
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
            font-size: 14px;
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
            margin-bottom: 35px;
        }
        .grand-total-content {
            background: #064e3b;
            color: white;
            padding: 16px 24px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 10px 20px rgba(6, 78, 59, 0.15);
        }
        .grand-total-label {
            font-size: 14px;
            font-weight: 600;
            opacity: 0.9;
        }
        .grand-total-value {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: auto;
            padding-top: 20px;
        }
        .notes {
            flex: 1;
            padding-right: 30px;
        }
        .notes h4 {
            margin: 0 0 6px 0;
            font-size: 13px;
            color: #475569;
        }
        .notes p {
            margin: 0;
            font-size: 11px;
            color: #64748b;
            line-height: 1.5;
            background: #f8fafc;
            padding: 12px;
            border-radius: 8px;
            border: 1px dashed #cbd5e1;
        }
        .signature-box {
            width: 230px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .signature-date {
            font-size: 13px;
            color: #475569;
            margin-bottom: 6px;
        }
        .signature-role {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 65px;
        }
        .signature-name {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        .signature-nip {
            font-size: 12px;
            color: #64748b;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 110px;
            font-weight: 900;
            color: rgba(0, 0, 0, 0.02);
            white-space: nowrap;
            pointer-events: none;
            z-index: 0;
        }
        @media print {
            @page {
                size: A5 portrait;
                margin: 8mm;
            }
            body {
                background: #fff;
                padding: 0;
                margin: 0;
                display: block;
                font-size: 11px;
            }
            .action-bar, .no-print {
                display: none !important;
            }
            .sheet {
                width: 100% !important;
                min-height: auto !important;
                box-shadow: none !important;
                padding: 10px !important;
                margin: 0 !important;
                page-break-after: always !important;
            }
            .sheet:last-of-type {
                page-break-after: auto !important;
            }
            .grand-total-content {
                box-shadow: none !important;
                border: 2px solid #064e3b;
            }
        }
    </style>
</head>
<body>

    <div class="action-bar no-print">
        <button onclick="window.print();" class="btn-print">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Seluruh Slip Guru ({{ $payrolls->count() }})
        </button>
        <button onclick="window.close();" class="btn-close">
            Tutup
        </button>
    </div>

    @forelse($payrolls as $payroll)
        @php
            $honorPerJam = $payroll->user->payrollSetting->honor_per_jam ?? 0;
            $jamMengajar = $payroll->jumlah_jam_mengajar ?? 0;
            
            $itemHonor = $payroll->penerimaanItems->firstWhere('nama_komponen', 'Honor Jam Mengajar');
            $totalHonorJam = $itemHonor ? $itemHonor->nominal : ($jamMengajar * $honorPerJam);

            $hariKehadiran = $payroll->jumlah_kehadiran ?? 0;
            
            $itemTransport = $payroll->penerimaanItems->first(function($i) {
                return in_array($i->nama_komponen, ['Uang Transport Kehadiran / KBM', 'Tunjangan Kehadiran & Transport']);
            });
            $totalTransport = $itemTransport ? $itemTransport->nominal : 0;
            $rateTransport = ($hariKehadiran > 0 && $totalTransport > 0) 
                ? ($totalTransport / $hariKehadiran) 
                : ($payroll->user->payrollSetting->transport_per_hari ?? 20000);

            $itemGajiPokok = $payroll->penerimaanItems->firstWhere('nama_komponen', 'Gaji Pokok');

            $tunjanganLainnya = $payroll->penerimaanItems->filter(function($item) {
                return !in_array($item->nama_komponen, [
                    'Gaji Pokok', 
                    'Honor Jam Mengajar', 
                    'Tunjangan Kehadiran & Transport', 
                    'Uang Transport Kehadiran / KBM'
                ]);
            });
        @endphp

        <div class="sheet">
            <div class="watermark">HILAL EDU</div>

            <div class="header">
                <div class="header-left" style="display: flex; align-items: center; gap: 14px;">
                    <img src="{{ $sekolah->logo_url }}" alt="Logo" style="height: 52px; width: 52px; object-fit: contain;">
                    <div>
                        <div class="school-name">{{ strtoupper($sekolah->nama_sekolah ?? 'SMK PLUS AL HILAL') }}</div>
                        <div class="school-address">Terakreditasi "B" - Sistem Informasi Manajemen Akademik & Kepegawaian</div>
                    </div>
                </div>
                <div class="header-right">
                    <div class="document-title">SLIP GAJI & BISYARAH GURU</div>
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
                        <div class="info-label">NUPTK</div>
                        <div class="info-value">: {{ $payroll->user->nip ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">No. Slip</div>
                        <div class="info-value">: {{ $payroll->nomor_slip }}</div>
                    </div>
                </div>
                <div class="info-group">
                    <div class="info-row">
                        <div class="info-label">Status / Peran</div>
                        <div class="info-value">: Guru Pendidik</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Tugas Tambahan</div>
                        <div class="info-value">: 
                            @php $daftarJabatan = $payroll->user->daftar_jabatan; @endphp
                            {{ count($daftarJabatan) > 0 ? implode(', ', $daftarJabatan) : '-' }}
                        </div>
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

                    @if($itemGajiPokok && $itemGajiPokok->nominal > 0)
                    <tr>
                        <td>Gaji Pokok</td>
                        <td style="text-align: center; color: #64748b;">Gaji pokok bulanan</td>
                        <td class="amount-cell text-green">{{ number_format($itemGajiPokok->nominal, 0, ',', '.') }}</td>
                    </tr>
                    @endif

                    <tr>
                        <td>Honor Jam Mengajar</td>
                        <td style="text-align: center; color: #64748b;">{{ $jamMengajar }} Jam × Rp{{ number_format($honorPerJam, 0, ',', '.') }}</td>
                        <td class="amount-cell text-green">{{ number_format($totalHonorJam, 0, ',', '.') }}</td>
                    </tr>

                    <tr>
                        <td>{{ $itemTransport ? $itemTransport->nama_komponen : 'Uang Transport Kehadiran / KBM' }}</td>
                        <td style="text-align: center; color: #64748b;">{{ $hariKehadiran }} Hari × Rp{{ number_format($rateTransport, 0, ',', '.') }}</td>
                        <td class="amount-cell text-green">{{ number_format($totalTransport, 0, ',', '.') }}</td>
                    </tr>

                    @if($tunjanganLainnya->count() > 0)
                        @foreach($tunjanganLainnya as $item)
                            <tr>
                                <td>{{ str_replace('Tugas Tambahan: ', '', $item->nama_komponen) }}</td>
                                <td style="text-align: center; color: #64748b;">{{ $item->keterangan ?? '-' }}</td>
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
                                <td style="text-align: center; color: #64748b;">-</td>
                                <td class="amount-cell text-red">- {{ number_format($item->nominal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr class="sub-total">
                            <td colspan="2" style="text-align: right;">TOTAL POTONGAN :</td>
                            <td class="amount-cell text-red">- Rp {{ number_format($payroll->total_potongan, 0, ',', '.') }}</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="3" style="text-align: center; color: #94a3b8; font-style: italic; padding: 14px;">
                                Tidak ada data potongan.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div class="grand-total-box">
                <div class="grand-total-content">
                    <div class="grand-total-label">TOTAL BISYARAH BERSIH (THP) :</div>
                    <div class="grand-total-value">Rp {{ number_format($payroll->gaji_bersih, 0, ',', '.') }}</div>
                </div>
            </div>

            @php
                $bendahara = \App\Models\PengaturanSekolah::getBendaharaSekolah();
            @endphp
            <div class="footer">
                <div class="notes">
                    <h4>Catatan:</h4>
                    <p>
                        1. Slip gaji ini adalah dokumen rahasia dan sah, dicetak otomatis dari Sistem HilalEdu.<br>
                        2. Apabila terdapat ketidaksesuaian perhitungan jam kehadiran atau potongan, harap menghubungi Bagian Keuangan/Bendahara maksimal 3 hari kerja setelah slip diterbitkan.
                    </p>
                </div>
                <div class="signature-box">
                    <div class="signature-date">Arjawinangun, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                    <div class="signature-role">Bendahara Sekolah</div>
                    
                    <div class="signature-name">{{ $bendahara ? ($bendahara->nama_lengkap ?? $bendahara->name) : 'Elin Tamaya, S.E' }}</div>
                    <div class="signature-nip">NUPTK: {{ $bendahara?->nip ?? '-' }}</div>
                </div>
            </div>
        </div>
    @empty
        <div style="background: white; max-width: 600px; margin: 50px auto; padding: 40px; text-align: center; border-radius: 12px;">
            <h3>Tidak Ada Data Guru</h3>
            <p style="color: #64748b;">Belum ada slip gaji yang dihitung untuk Guru pada periode ini.</p>
        </div>
    @endforelse

</body>
</html>
