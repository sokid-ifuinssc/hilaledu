<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leger Nilai {{ $mode === 'kumulatif' ? 'Kumulatif 6 Semester' : 'Semester ' . ucfirst($semester) }} - Kelas {{ $kelas->nama_kelas }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.8cm 0.8cm 0.8cm 0.8cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            font-size: 7pt;
            line-height: 1.15;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .header h2 {
            margin: 0;
            font-size: 12pt;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .header h3 {
            margin: 2px 0 0 0;
            font-size: 9.5pt;
            font-weight: bold;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 6px;
            font-size: 7.5pt;
        }
        .meta-table td {
            padding: 1px 3px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.5pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #333;
            padding: 2px 3px;
            text-align: center;
        }
        table.data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #0f172a;
        }
        .th-group-umum { background-color: #f8fafc; }
        .th-group-p5 { background-color: #ecfdf5; color: #065f46; }
        .th-group-keahlian { background-color: #f5f3ff; color: #5b21b6; }
        .text-left { text-align: left !important; }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }
        .ttd-section {
            margin-top: 18px;
            width: 100%;
            page-break-inside: avoid;
        }
        .ttd-box {
            float: right;
            width: 240px;
            text-align: center;
            font-size: 7.5pt;
        }
        .ttd-box-left {
            float: left;
            width: 240px;
            text-align: center;
            font-size: 7.5pt;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .kategori-legend {
            margin-top: 8px;
            font-size: 6.5pt;
            color: #334155;
            page-break-inside: avoid;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 12px; padding: 8px; background: #e0f2fe; border: 1px solid #7dd3fc; border-radius: 6px; text-align: center; font-size: 8.5pt;">
        <button onclick="window.print()" style="padding: 6px 16px; background: #0284c7; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak Dokumen Leger
        </button>
        <a href="{{ route('walikelas.leger.print', ['kelas_id' => $kelas->id, 'mode' => 'kumulatif']) }}" style="margin-left: 8px; padding: 6px 12px; background: {{ $mode === 'kumulatif' ? '#0369a1' : '#fff' }}; color: {{ $mode === 'kumulatif' ? '#fff' : '#0369a1' }}; border: 1px solid #0284c7; border-radius: 4px; text-decoration: none; font-weight: bold;">
            Mode Kumulatif 6 Semester
        </a>
        <a href="{{ route('walikelas.leger.print', ['kelas_id' => $kelas->id, 'mode' => 'semester']) }}" style="margin-left: 4px; padding: 6px 12px; background: {{ $mode === 'semester' ? '#0369a1' : '#fff' }}; color: {{ $mode === 'semester' ? '#fff' : '#0369a1' }}; border: 1px solid #0284c7; border-radius: 4px; text-decoration: none; font-weight: bold;">
            Mode Semester Berjalan
        </a>
        <button onclick="window.close()" style="padding: 6px 14px; background: #64748b; color: white; border: none; border-radius: 4px; margin-left: 8px; cursor: pointer;">
            Tutup
        </button>
    </div>

    <!-- Header Dokumen -->
    <div class="header">
        @if($mode === 'kumulatif')
            <h2>LEGER NILAI HASIL BELAJAR SISWA (KUMULATIF 6 SEMESTER)</h2>
            <h3>SEKOLAH MENENGAH KEJURUAN HILALEDU - TAHUN AJARAN {{ $tahunAjaran }}</h3>
        @else
            <h2>LEGER NILAI HASIL BELAJAR SISWA (SEMESTER {{ strtoupper($semester) }})</h2>
            <h3>TAHUN AJARAN {{ $tahunAjaran }} - SEMESTER {{ strtoupper($semester) }}</h3>
        @endif
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 12%; font-weight: bold;">Kelas / Rombel</td>
            <td style="width: 38%;">: {{ $kelas->nama_kelas }} ({{ $kelas->jurusan?->nama ?: ($kelas->jurusan?->singkatan ?: 'Umum') }})</td>
            <td style="width: 15%; font-weight: bold;">Wali Kelas</td>
            <td style="width: 35%;">: {{ $kelas->waliKelasGuru->name ?? ($kelas->wali_kelas ?: '-') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Program Keahlian</td>
            <td>: {{ $kelas->jurusan?->nama ?: 'Semua Program' }}</td>
            <td style="font-weight: bold;">Status Leger</td>
            <td>: {{ $mode === 'kumulatif' ? 'Kumulatif (X Ganjil - XII Genap)' : 'Semester ' . ucfirst($semester) . ' ' . $tahunAjaran }}</td>
        </tr>
    </table>

    @if($mode === 'kumulatif')
        <!-- ======================================================== -->
        <!-- FORMAT KUMULATIF 6 SEMESTER (X Ganjil s.d XII Genap)    -->
        <!-- Kategori Dasar-dasar Keahlian dan Keahlian DIGABUNG     -->
        <!-- Format: No | Nama Siswa | Mapel (6 Semester) | Rata     -->
        <!-- ======================================================== -->
        @php
            $umumMapels = $cumulativeMapels->filter(fn($m) => $m->kat_group === 'umum');
            $projectMapels = $cumulativeMapels->filter(fn($m) => $m->kat_group === 'project');
            $keahlianMapels = $cumulativeMapels->filter(fn($m) => str_contains($m->kat_group, 'kejuruan'));
        @endphp

        <table class="data-table">
            <thead>
                <!-- Baris 1: Super Kategori (Dasar & Konsentrasi Keahlian DIGABUNG dalam satu grup Kejuruan) -->
                <tr>
                    <th rowspan="3" style="width: 22px;">No</th>
                    <th rowspan="3" style="min-width: 140px; text-align: left;">Nama Siswa</th>

                    @if($umumMapels->count() > 0)
                        <th colspan="{{ $umumMapels->count() * 6 }}" class="th-group-umum" style="border-bottom: 1.5px solid #64748b; font-size: 6.5pt; font-weight: 800; text-transform: uppercase; letter-spacing: 0.3px;">
                            A. Kelompok Mata Pelajaran Umum
                        </th>
                    @endif

                    @if($projectMapels->count() > 0)
                        <th colspan="{{ $projectMapels->count() * 6 }}" class="th-group-p5" style="border-bottom: 1.5px solid #059669; font-size: 6.5pt; font-weight: 800; text-transform: uppercase; letter-spacing: 0.3px;">
                            B. Project Pancasila & Team Work (Integrasi Eskul)
                        </th>
                    @endif

                    @if($keahlianMapels->count() > 0)
                        <th colspan="{{ $keahlianMapels->count() * 6 }}" class="th-group-keahlian" style="border-bottom: 1.5px solid #7c3aed; font-size: 6.5pt; font-weight: 800; text-transform: uppercase; letter-spacing: 0.3px;">
                            C. Mata Pelajaran Keahlian / Kejuruan (Gabungan Dasar-Dasar Keahlian & Konsentrasi Keahlian)
                        </th>
                    @endif

                    <th rowspan="3" style="width: 38px; background: #e0e7ff;">Rata-rata Kumulatif</th>
                </tr>

                <!-- Baris 2: Nama Mapel (colspan 6 per mapel) -->
                <tr>
                    @foreach($cumulativeMapels as $m)
                        @php
                            $isP5 = $m->kat_group === 'project';
                            $isKeahlian = str_contains($m->kat_group, 'kejuruan');
                        @endphp
                        <th colspan="6" class="{{ $isP5 ? 'th-group-p5' : ($isKeahlian ? 'th-group-keahlian' : 'th-group-umum') }}">
                            <div style="font-size: 6pt; font-weight: bold; line-height: 1.15;">
                                {{ $m->nama }}
                                @if($isP5) <span style="font-size: 5pt; color: #047857; display: block;">(Terintegrasi Eskul)</span> @endif
                            </div>
                        </th>
                    @endforeach
                </tr>

                <!-- Baris 3: Sub-kolom 6 Semester per Mapel (X Ganjil, X Genap, XI Ganjil, XI Genap, XII Ganjil, XII Genap) -->
                <tr style="font-size: 5.5pt;">
                    @foreach($cumulativeMapels as $m)
                        <th style="width: 14px;" title="Kelas X Semester Ganjil">X-1</th>
                        <th style="width: 14px;" title="Kelas X Semester Genap">X-2</th>
                        <th style="width: 14px;" title="Kelas XI Semester Ganjil">XI-1</th>
                        <th style="width: 14px;" title="Kelas XI Semester Genap">XI-2</th>
                        <th style="width: 14px;" title="Kelas XII Semester Ganjil">XII-1</th>
                        <th style="width: 14px;" title="Kelas XII Semester Genap">XII-2</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($cumulativeRows as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="text-left font-bold" style="white-space: nowrap;">{{ $row['siswa']->nama }}</td>

                        @foreach($cumulativeMapels as $m)
                            @php
                                $g = $row['grades'][$m->nama] ?? [];
                                $isP5 = $m->kat_group === 'project';
                            @endphp
                            <td style="{{ $isP5 ? 'background: #f0fdf4; font-weight: bold;' : '' }}">{{ $g['X_ganjil'] !== null ? number_format($g['X_ganjil'], 0) : '-' }}</td>
                            <td style="{{ $isP5 ? 'background: #f0fdf4; font-weight: bold;' : '' }}">{{ $g['X_genap'] !== null ? number_format($g['X_genap'], 0) : '-' }}</td>
                            <td style="{{ $isP5 ? 'background: #f0fdf4; font-weight: bold;' : '' }}">{{ $g['XI_ganjil'] !== null ? number_format($g['XI_ganjil'], 0) : '-' }}</td>
                            <td style="{{ $isP5 ? 'background: #f0fdf4; font-weight: bold;' : '' }}">{{ $g['XI_genap'] !== null ? number_format($g['XI_genap'], 0) : '-' }}</td>
                            <td style="{{ $isP5 ? 'background: #f0fdf4; font-weight: bold;' : '' }}">{{ $g['XII_ganjil'] !== null ? number_format($g['XII_ganjil'], 0) : '-' }}</td>
                            <td style="{{ $isP5 ? 'background: #f0fdf4; font-weight: bold;' : '' }}">{{ $g['XII_genap'] !== null ? number_format($g['XII_genap'], 0) : '-' }}</td>
                        @endforeach

                        <td class="font-bold" style="background: #eef2ff;">
                            {{ $row['total_terisi'] > 0 ? number_format($row['rata_rata'], 1) : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 3 + count($cumulativeMapels) * 6 }}" style="padding: 10px; color: #64748b;">
                            Tidak ada data siswa untuk ditampilkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Keterangan Penggabungan Kategori Keahlian -->
        <div class="kategori-legend">
            <b>Keterangan Pengelompokan Mata Pelajaran:</b><br>
            • <b>Kelompok Umum:</b> Pendidikan Agama & BP, Pancasila, Bhs. Indonesia, Matematika, Bhs. Inggris, PJOK, Sejarah, Seni Budaya, Akhlak, Informatika, IPAS, BK.<br>
            • <b>Project Pancasila & Team Work (Eskul):</b> Team Work Project dan Project Pancasila (diintegrasikan langsung dari akumulasi nilai & kehadiran pembina ekstrakurikuler).<br>
            • <b>Kelompok Keahlian / Kejuruan (Gabungan Dasar & Konsentrasi):</b> Mata pelajaran Dasar-dasar Program Keahlian dan Konsentrasi Keahlian digabungkan secara utuh dalam satu rumpun Keahlian/Kejuruan.<br>
            • <b>Keterangan Semester:</b> X-1 (Kelas X Ganjil), X-2 (Kelas X Genap), XI-1 (Kelas XI Ganjil), XI-2 (Kelas XI Genap), XII-1 (Kelas XII Ganjil), XII-2 (Kelas XII Genap).
        </div>

    @else
        <!-- ======================================================== -->
        <!-- FORMAT LEGER SEMESTER BERJALAN                           -->
        <!-- ======================================================== -->
        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 20px;">Rnk</th>
                    <th rowspan="2" style="min-width: 140px; text-align: left;">Nama Siswa</th>
                    <th rowspan="2" style="width: 65px;">NISN</th>
                    <th colspan="{{ $mapels->count() }}">Mata Pelajaran</th>
                    <th rowspan="2" style="width: 35px;">Total</th>
                    <th rowspan="2" style="width: 32px;">Rata</th>
                    <th rowspan="2" style="width: 110px;">Ekstrakurikuler</th>
                    <th colspan="4" style="width: 70px;">Kehadiran</th>
                </tr>
                <tr>
                    @foreach($mapels as $m)
                        <th style="font-size: 5.5pt;" title="{{ $m->nama }}">{{ $m->kode ?: ($m->kode_mapel ?: substr($m->nama, 0, 5)) }}</th>
                    @endforeach
                    <th style="width: 14px;">H</th>
                    <th style="width: 14px;">I</th>
                    <th style="width: 14px;">S</th>
                    <th style="width: 14px;">A</th>
                </tr>
            </thead>
            <tbody>
                @forelse($legerData as $row)
                    <tr>
                        <td class="font-bold">{{ $row['rank'] }}</td>
                        <td class="text-left font-bold">{{ $row['siswa']->nama }}</td>
                        <td style="font-size: 6pt;">{{ $row['siswa']->nisn }}</td>

                        @foreach($mapels as $m)
                            @php $n = $row['nilai_mapels'][$m->id] ?? null; @endphp
                            <td>{{ $n !== null ? number_format($n, 0) : '-' }}</td>
                        @endforeach

                        <td class="font-bold">{{ number_format($row['total_nilai'], 0) }}</td>
                        <td class="font-bold">{{ number_format($row['rata_rata'], 1) }}</td>

                        <td class="text-left" style="font-size: 5.5pt;">
                            @if(count($row['eskul_list']) > 0)
                                @foreach($row['eskul_list'] as $es)
                                    <div>{{ $es['nama'] }} ({{ $es['predikat'] ?: ($es['nilai'] ? number_format($es['nilai'],0) : '-') }})</div>
                                @endforeach
                            @else
                                <span style="color:#94a3b8;">-</span>
                            @endif
                        </td>

                        <td>{{ $row['absensi']['H'] }}</td>
                        <td>{{ $row['absensi']['I'] }}</td>
                        <td>{{ $row['absensi']['S'] }}</td>
                        <td>{{ $row['absensi']['A'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 7 + $mapels->count() }}" style="padding: 10px; color: #64748b;">
                            Tidak ada data siswa untuk ditampilkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <!-- Tanda Tangan -->
    <div class="ttd-section clearfix">
        <div class="ttd-box-left">
            Mengetahui,<br>
            Kepala Sekolah<br><br><br><br>
            <b>Drs. H. Mumu Muhaemin, M.M.Pd</b><br>
            <span>NIP. -</span>
        </div>
        <div class="ttd-box">
            Kuningan, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
            Wali Kelas {{ $kelas->nama_kelas }}<br><br><br><br>
            <b>{{ $kelas->waliKelasGuru->name ?? ($kelas->wali_kelas ?: 'Wali Kelas') }}</b><br>
            <span>NIP. {{ $kelas->waliKelasGuru->nip ?? '-' }}</span>
        </div>
    </div>
</body>
</html>
