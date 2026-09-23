<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Penggajian - {{ $periode->nama_periode }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #222;
            padding: 30px 15px;
        }
        .report-container {
            max-width: 1080px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 40px;
            border: 1px solid #e2e8f0;
        }
        .kop-header {
            text-align: center;
            border-bottom: 3px double #1a5632;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .kop-yayasan {
            font-size: 0.95rem;
            font-weight: 600;
            color: #444;
            letter-spacing: 1px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .kop-sekolah {
            font-size: 1.45rem;
            font-weight: 800;
            color: #1a5632;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .kop-alamat {
            font-size: 0.78rem;
            color: #666;
            line-height: 1.4;
        }
        .report-title {
            text-align: center;
            margin-bottom: 24px;
        }
        .report-title h4 {
            font-size: 1.25rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #1a5632;
            margin-bottom: 4px;
        }
        .table-rekap {
            font-size: 0.8rem;
            width: 100%;
        }
        .table-rekap th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 600;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: center;
            vertical-align: middle;
        }
        .table-rekap td {
            border: 1px solid #e2e8f0;
            padding: 7px 10px;
            vertical-align: middle;
        }
        .action-bar {
            max-width: 1080px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .report-container { box-shadow: none; border: none; padding: 10px 0; max-width: 100%; }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <button onclick="window.close();" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-x-circle me-1"></i> Tutup
        </button>
        <button onclick="window.print();" class="btn btn-success btn-sm px-4 fw-semibold" style="background: #10b981; border: none;">
            <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF
        </button>
    </div>

    <div class="report-container">
        {{-- Kop Resmi Sekolah --}}
        <div class="kop-header">
            <div class="kop-yayasan">YAYASAN PONDOK PESANTREN AL HILAL</div>
            <div class="kop-sekolah">{{ $sekolah->nama_sekolah ?? 'SMK PLUS AL HILAL' }}</div>
            <div class="kop-alamat">
                {{ $sekolah->alamat ?? 'Jl. H. Manshur No 7 Lap. Bima Rembes, Ds. Tegalgubug, Kec. Arjawinangun, Kab. Cirebon' }}<br>
                Telp: {{ $sekolah->telepon ?? '-' }} | Website: {{ $sekolah->website ?? 'smkplusalhilal.sch.id' }} | Email: {{ $sekolah->email ?? 'smkpal2021@gmail.com' }}
            </div>
        </div>

        <div class="report-title">
            <h4>REKAPITULASI PENGGAJIAN & BISYARAH</h4>
            <div class="text-muted small">Periode: <strong>{{ $periode->nama_periode }}</strong> &bull; Status: <strong>{{ strtoupper($periode->status) }}</strong></div>
        </div>

        <div class="table-responsive">
            <table class="table table-rekap mb-4">
                <thead>
                    <tr>
                        <th style="width: 35px;">No</th>
                        <th>Nama Pegawai</th>
                        <th>Peran & Jabatan</th>
                        <th>Gaji Pokok</th>
                        <th>Honor Jam</th>
                        <th>Tunjangan</th>
                        <th>Total Kotor</th>
                        <th>Potongan</th>
                        <th>Take Home Pay</th>
                        <th>Rekening</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totGajiPokok = 0;
                        $totHonorJam = 0;
                        $totTunjangan = 0;
                        $totPenerimaan = 0;
                        $totPotongan = 0;
                        $totTHP = 0;
                    @endphp
                    @foreach($payrolls as $index => $p)
                        @php
                            $totGajiPokok += (float)$p->gaji_pokok;
                            $totHonorJam += (float)$p->total_honor_jam;
                            $totTunjangan += (float)$p->total_tunjangan;
                            $totPenerimaan += (float)$p->total_penerimaan;
                            $totPotongan += (float)$p->total_potongan;
                            $totTHP += (float)$p->gaji_bersih;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="fw-semibold">
                                {{ $p->user->name }}
                                <div class="text-muted small" style="font-size:0.72rem;">NIP: {{ $p->user->nip ?? '-' }}</div>
                            </td>
                            <td>
                                {{ ucfirst($p->user->role) }}
                                <div class="text-muted small" style="font-size:0.72rem;">
                                    {{ is_array($p->user->tugas_tambahan) && count($p->user->tugas_tambahan) > 0 ? implode(', ', $p->user->tugas_tambahan) : ($p->user->jabatan_utama ?? '-') }}
                                </div>
                            </td>
                            <td class="text-end">Rp {{ number_format($p->gaji_pokok, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($p->total_honor_jam, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($p->total_tunjangan, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold text-success">Rp {{ number_format($p->total_penerimaan, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold text-danger">Rp {{ number_format($p->total_potongan, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($p->gaji_bersih, 0, ',', '.') }}</td>
                            <td class="small" style="font-size:0.72rem;">
                                {{ $p->user->payrollSetting?->nomor_rekening ? $p->user->payrollSetting->rekening_bank . ' ' . $p->user->payrollSetting->nomor_rekening : 'Tunai' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold" style="background:#e2e8f0;">
                        <td colspan="3" class="text-center">TOTAL KESELURUHAN ({{ count($payrolls) }} PEGAWAI)</td>
                        <td class="text-end">Rp {{ number_format($totGajiPokok, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($totHonorJam, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($totTunjangan, 0, ',', '.') }}</td>
                        <td class="text-end text-success">Rp {{ number_format($totPenerimaan, 0, ',', '.') }}</td>
                        <td class="text-end text-danger">Rp {{ number_format($totPotongan, 0, ',', '.') }}</td>
                        <td class="text-end fw-bolder">Rp {{ number_format($totTHP, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Tanda Tangan --}}
        <div class="d-flex justify-content-between text-center mt-5" style="font-size:0.85rem;">
            <div>
                Mengetahui,<br>
                <strong>Ketua Yayasan Ponpes Al Hilal</strong>
                <div style="height: 70px;"></div>
                <div style="border-bottom:1px solid #333; display:inline-block; min-width:180px;">
                    KH. Mukhammad Mansyur, S.Pt
                </div>
            </div>

            <div>
                Cirebon, {{ date('d F Y') }}<br>
                <strong>Bendahara Sekolah / Keuangan</strong>
                <div style="height: 70px;"></div>
                <div style="border-bottom:1px solid #333; display:inline-block; min-width:180px;">
                    Bendahara HilalEdu
                </div>
            </div>
        </div>
    </div>

</body>
</html>
