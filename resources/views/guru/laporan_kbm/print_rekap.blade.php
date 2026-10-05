<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Jurnal & Realisasi KBM - {{ $selectedGuru ? $selectedGuru->name : ($guruIdFilter === 'all' ? 'Semua Guru' : $user->name) }}</title>
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
        
        .badge-lapor {
            color: #059669;
            font-weight: bold;
        }
        .badge-belum {
            color: #dc2626;
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
        <p>Laporan Keterlaksanaan Pembelajaran dan Kehadiran Siswa di Kelas</p>
    </div>

    <!-- Identitas -->
    <table class="identitas">
        <tr>
            <td style="width: 15%;">Guru Pengampu</td>
            <td style="width: 2%;">:</td>
            <td style="width: 35%;">
                @if($selectedGuru)
                    <strong>{{ $selectedGuru->name }}</strong> (NIP/NUPTK: {{ $selectedGuru->nip ?? '-' }})
                @elseif($guruIdFilter === 'all')
                    <strong>Seluruh Guru SMK Plus Al-Hilal</strong> (Rekap Monitoring Sekolah)
                @else
                    <strong>{{ $user->name }}</strong> (NIP/NUPTK: {{ $user->nip ?? '-' }})
                @endif
            </td>
            <td style="width: 15%;">Periode Laporan</td>
            <td style="width: 2%;">:</td>
            <td style="width: 31%;">
                <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->isoFormat('MMMM Y') }}</strong>
            </td>
        </tr>
        <tr>
            <td>Mata Pelajaran</td>
            <td>:</td>
            <td>{{ $selectedMapel ? $selectedMapel->nama : 'Semua Mata Pelajaran' }}</td>
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
                @if($guruIdFilter === 'all')
                <th style="width: 12%;" rowspan="2">Guru Pengampu</th>
                @endif
                <th style="width: 21%;" rowspan="2">Materi / Aktivitas Pembelajaran</th>
                <th style="width: 9%;" rowspan="2">Status Laporan</th>
                <th style="width: 13%;" colspan="4">Presensi Siswa</th>
            </tr>
            <tr>
                <th style="width: 3.5%;">H</th>
                <th style="width: 3%;">S</th>
                <th style="width: 3%;">I</th>
                <th style="width: 3.5%;">A</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totHadir = 0;
                $totSakit = 0;
                $totIzin = 0;
                $totAlpa = 0;
                $countLapor = 0;
                $countBelum = 0;
            @endphp
            @forelse($slots as $idx => $slot)
                @php
                    $lap = $slot['laporan'];
                    $j = $slot['jadwal'];
                    
                    if ($lap) {
                        $countLapor++;
                        $h = $lap->presensiSiswa->whereIn('status', ['hadir', 'terlambat'])->count();
                        $s = $lap->presensiSiswa->where('status', 'sakit')->count();
                        $i = $lap->presensiSiswa->where('status', 'izin')->count();
                        $a = $lap->presensiSiswa->where('status', 'alpa')->count();

                        $totHadir += $h;
                        $totSakit += $s;
                        $totIzin += $i;
                        $totAlpa += $a;

                        $materi = $lap->rencana ? "Pertemuan {$lap->rencana->pertemuan_ke}: {$lap->rencana->materi_pokok}" : ($lap->catatan_kegiatan ?: 'KBM Terlaksana');
                        $statusTeks = 'Sudah Dilaporkan';
                        $statusClass = 'badge-lapor';
                    } elseif ($slot['status'] === 'jadwal_mendatang') {
                        $materi = 'Jadwal belum berjalan';
                        $statusTeks = 'Jadwal Mendatang';
                        $statusClass = 'text-slate-400';
                        $h = $s = $i = $a = '-';
                    } else {
                        $countBelum++;
                        $materi = 'Belum ada catatan jurnal KBM';
                        $statusTeks = 'Belum Dilaporkan';
                        $statusClass = 'badge-belum';
                        $h = $s = $i = $a = '-';
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($slot['tanggal'])->isoFormat('dddd, D MMM Y') }}
                    </td>
                    <td class="text-center">
                        {{ substr($j->jam_mulai ?? '', 0, 5) }} - {{ substr($j->jam_selesai ?? '', 0, 5) }}
                    </td>
                    <td class="text-center font-bold">{{ $j->kelas ?? '-' }}</td>
                    <td>{{ $j->mataPelajaran->nama ?? '-' }}</td>
                    @if($guruIdFilter === 'all')
                    <td>{{ $j->guru->name ?? '-' }}</td>
                    @endif
                    <td>
                        {{ $materi }}
                        @if($lap && $lap->catatan_kegiatan && $lap->rencana)
                            <div style="font-size: 8pt; color: #444; font-style: italic; margin-top: 1px;">
                                Catatan: {{ $lap->catatan_kegiatan }}
                            </div>
                        @endif
                    </td>
                    <td class="text-center {{ $statusClass }}">
                        {{ $statusTeks }}
                    </td>
                    <td class="text-center font-bold {{ is_numeric($h) ? 'badge-lapor' : '' }}">{{ $h }}</td>
                    <td class="text-center">{{ $s }}</td>
                    <td class="text-center">{{ $i }}</td>
                    <td class="text-center">{{ $a }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $guruIdFilter === 'all' ? 12 : 11 }}" class="text-center" style="font-style: italic; padding: 20px; color: #666;">
                        Belum ada data slot pembelajaran pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($slots) > 0)
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="{{ $guruIdFilter === 'all' ? 7 : 6 }}" class="text-right">
                    TOTAL SESI (Dilaporkan: {{ $countLapor }}, Belum: {{ $countBelum }}):
                </td>
                <td class="text-center">{{ $countLapor }}/{{ count($slots) }}</td>
                <td class="text-center badge-lapor">{{ $totHadir }}</td>
                <td class="text-center">{{ $totSakit }}</td>
                <td class="text-center">{{ $totIzin }}</td>
                <td class="text-center">{{ $totAlpa }}</td>
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
                @if($selectedGuru)
                    Guru Pengampu Mata Pelajaran,<br>
                    <div class="signature-space"></div>
                    <strong><u>{{ $selectedGuru->name }}</u></strong><br>
                    NUPTK/NIP. {{ $selectedGuru->nip ?? '-' }}
                @elseif($guruIdFilter === 'all')
                    Waka Kurikulum & Akademik,<br>
                    <div class="signature-space"></div>
                    <strong><u>{{ $user->name }}</u></strong><br>
                    NUPTK/NIP. {{ $user->nip ?? '-' }}
                @else
                    Guru Pengampu Mata Pelajaran,<br>
                    <div class="signature-space"></div>
                    <strong><u>{{ $user->name }}</u></strong><br>
                    NUPTK/NIP. {{ $user->nip ?? '-' }}
                @endif
            </td>
        </tr>
    </table>

</body>
</html>
