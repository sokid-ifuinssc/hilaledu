<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Jurnal KBM & Presensi - {{ $user->name }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            line-height: 1.35;
            color: #000;
            padding: 20px;
            margin: 0;
            background-color: #fff;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 6px;
            margin-bottom: 15px;
            position: relative;
        }
        .kop-logo {
            position: absolute;
            left: 20px;
            top: 0;
            width: 70px;
            height: 70px;
            object-fit: contain;
        }
        .kop-header {
            margin-left: 90px;
            margin-right: 90px;
        }
        .kop-header h2 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
        }
        .kop-header h1 {
            margin: 2px 0;
            font-size: 15pt;
            font-weight: bold;
        }
        .kop-header p {
            margin: 1px 0;
            font-size: 9pt;
        }
        .title-box {
            text-align: center;
            margin-bottom: 15px;
        }
        .title-box h3 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .title-box p {
            margin: 3px 0 0;
            font-size: 9.5pt;
            font-style: italic;
        }
        table.identitas {
            width: 100%;
            margin-bottom: 12px;
            font-size: 10pt;
            border-collapse: collapse;
        }
        table.identitas td {
            padding: 2px 0;
            vertical-align: top;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
        }
        table.data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .badge-hadir {
            color: #059669;
            font-weight: bold;
        }
        
        .ttd-box {
            width: 100%;
            margin-top: 25px;
            font-size: 10pt;
            page-break-inside: avoid;
        }
        .ttd-box td {
            vertical-align: top;
            text-align: center;
            width: 50%;
        }
        .signature-space {
            height: 65px;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            @page {
                size: A4 landscape;
                margin: 12mm 12mm 12mm 12mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right; display: flex; justify-content: flex-end; gap: 10px;">
        <button onclick="window.history.back()" style="padding: 7px 14px; background: #64748b; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 12px;">
            &larr; Kembali
        </button>
        <button onclick="window.print()" style="padding: 7px 16px; background: #059669; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 12px;">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- Kop Surat -->
    <div class="kop-surat">
        <img src="{{ asset('images/logo.png') }}" class="kop-logo" alt="Logo" onerror="this.style.display='none'">
        <div class="kop-header">
            <h2>YAYASAN AL-HILAL ARJAWINANGUN</h2>
            <h1>SMK PLUS AL-HILAL ARJAWINANGUN</h1>
            <p>NSS: 322021708001 &bull; NPSN: 20268571 &bull; Terakreditasi "A"</p>
            <p>Jl. Kebon Melati No. 01 Ds. Jungjang Kec. Arjawinangun Kab. Cirebon 45162 &bull; Telp. (0231) 357890</p>
        </div>
    </div>

    <!-- Judul -->
    <div class="title-box">
        <h3>BUKU JURNAL & REKAPITULASI PELAKSANAAN KBM</h3>
        <p>Catatan Realisasi Pembelajaran dan Presensi Kehadiran Siswa di Kelas</p>
    </div>

    <!-- Identitas -->
    <table class="identitas">
        <tr>
            <td style="width: 15%;">Guru Pengampu</td>
            <td style="width: 2%;">:</td>
            <td style="width: 35%;"><strong>{{ $user->name }}</strong> (NIP/NUPTK: {{ $user->nip ?? '-' }})</td>
            <td style="width: 15%;">Periode Laporan</td>
            <td style="width: 2%;">:</td>
            <td style="width: 31%;">
                @if($bulan)
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->isoFormat('MMMM Y') }}
                @elseif($tanggalMulai && $tanggalSelesai)
                    {{ \Carbon\Carbon::parse($tanggalMulai)->isoFormat('D MMM Y') }} s.d. {{ \Carbon\Carbon::parse($tanggalSelesai)->isoFormat('D MMM Y') }}
                @else
                    Semua Catatan Realisasi KBM
                @endif
            </td>
        </tr>
        <tr>
            <td>Mata Pelajaran</td>
            <td>:</td>
            <td>{{ $selectedMapel ? $selectedMapel->nama : 'Semua Mata Pelajaran yang Diampu' }}</td>
            <td>Kelas Sasaran</td>
            <td>:</td>
            <td>{{ $kelas ?: 'Semua Kelas' }}</td>
        </tr>
    </table>

    <!-- Tabel Data Rekapitulasi Jurnal KBM -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;" rowspan="2">No.</th>
                <th style="width: 11%;" rowspan="2">Hari / Tanggal</th>
                <th style="width: 8%;" rowspan="2">Waktu / Jam</th>
                <th style="width: 9%;" rowspan="2">Kelas</th>
                <th style="width: 14%;" rowspan="2">Mata Pelajaran</th>
                <th style="width: 23%;" rowspan="2">Materi / Aktivitas Pembelajaran</th>
                <th style="width: 9%;" rowspan="2">Kesesuaian Rencana</th>
                <th style="width: 15%;" colspan="4">Presensi Siswa</th>
                <th style="width: 8%;" rowspan="2">Ket.</th>
            </tr>
            <tr>
                <th style="width: 4%;">H</th>
                <th style="width: 3%;">S</th>
                <th style="width: 3%;">I</th>
                <th style="width: 3%;">A</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totHadir = 0;
                $totSakit = 0;
                $totIzin = 0;
                $totAlpa = 0;
                $totSemua = 0;
            @endphp
            @forelse($laporans as $idx => $lap)
                @php
                    $h = $lap->presensiSiswa->whereIn('status', ['hadir', 'terlambat'])->count();
                    $s = $lap->presensiSiswa->where('status', 'sakit')->count();
                    $i = $lap->presensiSiswa->where('status', 'izin')->count();
                    $a = $lap->presensiSiswa->where('status', 'alpa')->count();
                    $tot = $lap->presensiSiswa->count();

                    $totHadir += $h;
                    $totSakit += $s;
                    $totIzin += $i;
                    $totAlpa += $a;
                    $totSemua += $tot;

                    $materi = $lap->rencana ? "Pertemuan Ke-{$lap->rencana->pertemuan_ke}: {$lap->rencana->materi_pokok}" : ($lap->catatan_kegiatan ?: '-');
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($lap->tanggal_realisasi)->isoFormat('dddd, D MMM Y') }}
                    </td>
                    <td class="text-center">
                        {{ substr($lap->jadwal->jam_mulai ?? '', 0, 5) }} - {{ substr($lap->jadwal->jam_selesai ?? '', 0, 5) }}
                    </td>
                    <td class="text-center font-bold">{{ $lap->jadwal->kelas ?? '-' }}</td>
                    <td>{{ $lap->jadwal->mataPelajaran->nama ?? '-' }}</td>
                    <td>
                        {{ $materi }}
                        @if($lap->rencana && $lap->catatan_kegiatan)
                            <div style="font-size: 8pt; color: #444; font-style: italic; margin-top: 2px;">
                                Catatan: {{ $lap->catatan_kegiatan }}
                            </div>
                        @endif
                    </td>
                    <td class="text-center">
                        {{ ucfirst(str_replace('_', ' ', $lap->kesesuaian_rencana)) }}
                    </td>
                    <td class="text-center font-bold badge-hadir">{{ $h }}</td>
                    <td class="text-center">{{ $s ?: '-' }}</td>
                    <td class="text-center">{{ $i ?: '-' }}</td>
                    <td class="text-center">{{ $a ?: '-' }}</td>
                    <td class="text-center" style="font-size: 8pt;">
                        {{ ucfirst(str_replace('_', ' ', $lap->status_pelaksanaan)) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="font-style: italic; padding: 20px; color: #666;">
                        Belum ada laporan KBM yang tercatat untuk filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($laporans->isNotEmpty())
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="7" class="text-right">TOTAL REKAPITULASI KEHADIRAN SISWA:</td>
                <td class="text-center badge-hadir">{{ $totHadir }}</td>
                <td class="text-center">{{ $totSakit }}</td>
                <td class="text-center">{{ $totIzin }}</td>
                <td class="text-center">{{ $totAlpa }}</td>
                <td class="text-center">{{ $laporans->count() }} KBM</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Tanda Tangan -->
    <table class="ttd-box">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala SMK Plus Al-Hilal Arjawinangun<br>
                <div class="signature-space"></div>
                <strong><u>{{ $setting->kepala_sekolah ?? 'Muhammad Mansyur, S.Pt' }}</u></strong><br>
                NUPTK. {{ $setting->nip_kepala_sekolah ?? '6942767668130350' }}
            </td>
            <td>
                Arjawinangun, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}<br>
                Guru Pengampu Mata Pelajaran,<br>
                <div class="signature-space"></div>
                <strong><u>{{ $user->name }}</u></strong><br>
                NUPTK/NIP. {{ $user->nip ?? '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
