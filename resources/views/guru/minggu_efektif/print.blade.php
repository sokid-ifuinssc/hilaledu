<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rincian Pekan Efektif - {{ $mingguEfektif->mataPelajaran->nama ?? 'Mata Pelajaran' }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 20px;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            position: relative;
        }
        .kop-logo {
            position: absolute;
            left: 10px;
            top: 0;
            width: 75px;
            height: 75px;
            object-fit: contain;
        }
        .kop-header {
            margin-left: 80px;
            margin-right: 80px;
        }
        .kop-header h2 { margin: 0; font-size: 15pt; font-weight: bold; }
        .kop-header h1 { margin: 2px 0; font-size: 17pt; font-weight: bold; }
        .kop-header p { margin: 2px 0; font-size: 10pt; }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            text-decoration: underline;
            margin-bottom: 15px;
        }
        .identitas {
            width: 100%;
            margin-bottom: 15px;
            font-size: 11pt;
        }
        .identitas td { padding: 3px 0; vertical-align: top; }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11pt;
        }
        table.data th, table.data td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        table.data th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .ttd-box {
            width: 100%;
            margin-top: 40px;
            font-size: 11pt;
        }
        .ttd-box td {
            vertical-align: top;
            text-align: center;
            width: 50%;
        }
        .signature-space { height: 75px; }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0b7b4b; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- Kop Surat Resmi -->
    <div class="kop-surat">
        <img src="{{ asset('images/logo.png') }}" class="kop-logo" alt="Logo SMK Plus Al-Hilal">
        <div class="kop-header">
            <h2>YAYASAN AL-HILAL ARJAWINANGUN</h2>
            <h1>SMK PLUS AL-HILAL ARJAWINANGUN</h1>
            <p>NSS: 322021708001 &bull; NPSN: 20268571 &bull; Terakreditasi "A"</p>
            <p>Jl. Kebon Melati No. 01 Ds. Jungjang Kec. Arjawinangun Kab. Cirebon 45162 &bull; Telp. (0231) 357890</p>
        </div>
    </div>

    <div class="title">RINCIAN PEKAN EFEKTIF (RPE) KURIKULUM MERDEKA</div>

    <!-- Identitas -->
    <table class="identitas">
        <tr>
            <td width="20%">Satuan Pendidikan</td>
            <td width="2%">:</td>
            <td width="38%">SMK Plus Al-Hilal Arjawinangun</td>
            <td width="20%">Tahun Pelajaran</td>
            <td width="2%">:</td>
            <td width="18%">{{ $mingguEfektif->tahun_ajaran }}</td>
        </tr>
        <tr>
            <td>Mata Pelajaran</td>
            <td>:</td>
            <td><strong>{{ $mingguEfektif->mataPelajaran->nama ?? 'Mata Pelajaran' }}</strong></td>
            <td>Semester</td>
            <td>:</td>
            <td>{{ ucfirst($mingguEfektif->semester) }}</td>
        </tr>
        <tr>
            <td>Tingkat / Kelas</td>
            <td>:</td>
            <td>{{ $mingguEfektif->kelas }}</td>
            <td>Alokasi Waktu</td>
            <td>:</td>
            <td>{{ $mingguEfektif->jam_per_minggu }} JP / Pekan</td>
        </tr>
        <tr>
            <td>Guru Pengampu</td>
            <td>:</td>
            <td>{{ $mingguEfektif->guru?->name ?? '-' }}</td>
            <td>Jadwal KBM</td>
            <td>:</td>
            <td>
                @php
                    $jadwalNotes = '';
                    if (!empty($mingguEfektif->catatan) && str_contains($mingguEfektif->catatan, 'jadwal mengajar:')) {
                        $parts = explode('jadwal mengajar:', $mingguEfektif->catatan);
                        $jadwalNotes = trim(rtrim($parts[1] ?? '', '.'));
                    }
                @endphp
                {{ $jadwalNotes ?: ($mingguEfektif->jam_per_minggu . ' JP / Pekan') }}
            </td>
        </tr>
    </table>

    <!-- 1. Perhitungan Jumlah Pekan -->
    <div style="font-weight: bold; margin-bottom: 6px;">I. JUMLAH PEKAN DALAM SEMESTER {{ strtoupper($mingguEfektif->semester) }}</div>
    <table class="data">
        <thead>
            <tr>
                <th width="6%">No.</th>
                <th width="24%">Nama Bulan</th>
                <th width="16%">Jumlah Pekan</th>
                <th width="18%">Pekan Tidak Efektif</th>
                <th width="18%">Pekan Efektif</th>
                <th width="18%">Jam Efektif (JP)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mingguEfektif->rincian_bulanan ?? [] as $i => $item)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $item['bulan'] }}</td>
                <td class="text-center">{{ $item['total_minggu'] }}</td>
                <td class="text-center">{{ $item['tidak_efektif'] }}</td>
                <td class="text-center" style="font-weight: bold;">{{ $item['efektif'] }}</td>
                <td class="text-center" style="font-weight: bold;">{{ $item['jam_efektif'] ?? ($item['efektif'] * $mingguEfektif->jam_per_minggu) }} JP</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f9f9f9;">
                <td colspan="2" class="text-center">JUMLAH</td>
                <td class="text-center">{{ $mingguEfektif->total_minggu }}</td>
                <td class="text-center">{{ $mingguEfektif->total_tidak_efektif }}</td>
                <td class="text-center">{{ $mingguEfektif->total_efektif }}</td>
                <td class="text-center" style="font-size: 11pt;">{{ $mingguEfektif->total_jam_efektif }} JP</td>
            </tr>
        </tfoot>
    </table>

    <!-- 2. Rincian Pekan Tidak Efektif -->
    <div style="font-weight: bold; margin-bottom: 6px;">II. KETERANGAN PEKAN TIDAK EFEKTIF</div>
    <table class="data">
        <thead>
            <tr>
                <th width="8%">No.</th>
                <th width="25%">Bulan</th>
                <th width="17%">Jumlah Pekan</th>
                <th width="50%">Uraian Kegiatan / Agenda</th>
            </tr>
        </thead>
        <tbody>
            @php $noAgenda = 1; @endphp
            @foreach($mingguEfektif->rincian_bulanan ?? [] as $item)
                @if(intval($item['tidak_efektif'] ?? 0) > 0)
                <tr>
                    <td class="text-center">{{ $noAgenda++ }}</td>
                    <td>{{ $item['bulan'] }}</td>
                    <td class="text-center">{{ $item['tidak_efektif'] }}</td>
                    <td>{{ $item['keterangan'] ?: '-' }}</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <!-- 3. Distribusi Alokasi Waktu -->
    <div style="font-weight: bold; margin-bottom: 6px;">III. DISTRIBUSI ALOKASI WAKTU EFEKTIF (JAM PELAJARAN)</div>
    <table class="data">
        <thead>
            <tr>
                <th width="8%">No.</th>
                <th width="62%">Komponen Alokasi Pembelajaran</th>
                <th width="30%">Alokasi Jam Pelajaran (JP)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Tatap Muka Teori & Praktik Kejuruan</td>
                <td class="text-center">{{ $mingguEfektif->distribusi_jam['tatap_muka'] ?? '-' }} JP</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Asesmen Formatif (Penilaian Harian)</td>
                <td class="text-center">{{ $mingguEfektif->distribusi_jam['asesmen_formatif'] ?? '-' }} JP</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Asesmen Sumatif (Tengah & Akhir Semester)</td>
                <td class="text-center">{{ $mingguEfektif->distribusi_jam['asesmen_sumatif'] ?? '-' }} JP</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Cadangan Jam KBM</td>
                <td class="text-center">{{ $mingguEfektif->distribusi_jam['cadangan'] ?? '-' }} JP</td>
            </tr>
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <td colspan="2" class="text-center">JUMLAH TOTAL JAM EFEKTIF ({{ $mingguEfektif->total_efektif }} Pekan x {{ $mingguEfektif->jam_per_minggu }} JP)</td>
                <td class="text-center" style="font-size: 12pt;">{{ $mingguEfektif->total_jam_efektif }} JP</td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda Tangan Resmi -->
    <table class="ttd-box">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala SMK Plus Al-Hilal Arjawinangun<br>
                <div class="signature-space"></div>
                <strong><u>MUHAMMAD MANSYUR, S.Pt</u></strong><br>
                NIP. -
            </td>
            <td>
                Arjawinangun, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}<br>
                Guru Mata Pelajaran,<br>
                <div class="signature-space"></div>
                <strong><u>{{ $mingguEfektif->guru?->name ?? 'Guru Pengampu' }}</u></strong><br>
                NIP. {{ $mingguEfektif->guru->nip ?? '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
