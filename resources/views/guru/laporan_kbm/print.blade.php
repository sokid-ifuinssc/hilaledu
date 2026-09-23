<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Mengajar & Presensi KBM - {{ $laporanKbm->jadwal->mataPelajaran->nama ?? 'Mata Pelajaran' }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.4; color: #000; padding: 20px; margin: 0; }
        .kop-surat { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 15px; position: relative; }
        .kop-logo { position: absolute; left: 10px; top: 0; width: 70px; height: 70px; object-fit: contain; }
        .kop-header { margin-left: 80px; margin-right: 80px; }
        .kop-header h2 { margin: 0; font-size: 14pt; font-weight: bold; }
        .kop-header h1 { margin: 2px 0; font-size: 16pt; font-weight: bold; }
        .kop-header p { margin: 1px 0; font-size: 9pt; }
        .title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; margin-bottom: 15px; }
        table.identitas { width: 100%; margin-bottom: 12px; font-size: 10.5pt; }
        table.identitas td { padding: 2px 0; vertical-align: top; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 10pt; }
        table.data th, table.data td { border: 1px solid #000; padding: 4px 6px; }
        table.data th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .ttd-box { width: 100%; margin-top: 30px; font-size: 10.5pt; }
        .ttd-box td { vertical-align: top; text-align: center; width: 50%; }
        .signature-space { height: 65px; }
        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0b7b4b; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- Kop Surat -->
    <div class="kop-surat">
        <img src="{{ asset('images/logo.png') }}" class="kop-logo" alt="Logo">
        <div class="kop-header">
            <h2>YAYASAN AL-HILAL ARJAWINANGUN</h2>
            <h1>SMK PLUS AL-HILAL ARJAWINANGUN</h1>
            <p>NSS: 322021708001 &bull; NPSN: 20268571 &bull; Terakreditasi "A"</p>
            <p>Jl. Kebon Melati No. 01 Ds. Jungjang Kec. Arjawinangun Kab. Cirebon 45162 &bull; Telp. (0231) 357890</p>
        </div>
    </div>

    <div class="title">JURNAL HARIAN REALISASI KBM & PRESENSI SISWA</div>

    <table class="identitas">
        <tr>
            <td width="20%">Mata Pelajaran</td>
            <td width="2%">:</td>
            <td width="40%"><strong>{{ $laporanKbm->jadwal->mataPelajaran->nama ?? 'Mata Pelajaran' }}</strong></td>
            <td width="18%">Hari / Tanggal</td>
            <td width="2%">:</td>
            <td width="18%">{{ \Carbon\Carbon::parse($laporanKbm->tanggal_realisasi)->isoFormat('dddd, D MMM Y') }}</td>
        </tr>
        <tr>
            <td>Kelas / Ruang</td>
            <td>:</td>
            <td>{{ $laporanKbm->jadwal->kelas ?? '-' }} ({{ $laporanKbm->jadwal->ruang ?? 'Ruang Kelas' }})</td>
            <td>Waktu KBM</td>
            <td>:</td>
            <td>{{ substr($laporanKbm->jadwal->jam_mulai ?? '00:00',0,5) }} - {{ substr($laporanKbm->jadwal->jam_selesai ?? '00:00',0,5) }} WIB</td>
        </tr>
        <tr>
            <td>Guru Pengampu</td>
            <td>:</td>
            <td>{{ $laporanKbm->guru?->name ?? '-' }}</td>
            <td>Kesesuaian Rencana</td>
            <td>:</td>
            <td>{{ ucfirst(str_replace('_', ' ', $laporanKbm->kesesuaian_rencana)) }}</td>
        </tr>
        <tr>
            <td>Status Jadwal</td>
            <td>:</td>
            <td colspan="4">{{ ucfirst(str_replace('_', ' ', $laporanKbm->status_pelaksanaan)) }} {{ $laporanKbm->keterangan_pelaksanaan ? "({$laporanKbm->keterangan_pelaksanaan})" : '' }}</td>
        </tr>
    </table>

    <div style="font-weight: bold; margin-bottom: 4px; font-size: 10.5pt;">I. CATATAN AKTIVITAS PEMBELAJARAN</div>
    <div style="border: 1px solid #000; padding: 8px; margin-bottom: 12px; font-size: 10pt; min-height: 45px;">
        {{ $laporanKbm->catatan_kegiatan ?: '-' }}
    </div>

    <div style="font-weight: bold; margin-bottom: 4px; font-size: 10.5pt;">
        II. REKAPITULASI PRESENSI SISWA (Hadir: {{ $laporanKbm->jumlah_siswa_hadir }}, Tidak Hadir: {{ $laporanKbm->jumlah_siswa_tidak_hadir }}, Total: {{ $laporanKbm->jumlah_siswa_total }} Siswa)
    </div>
    <table class="data">
        <thead>
            <tr>
                <th width="8%">No.</th>
                <th width="45%">Nama Siswa</th>
                <th width="20%">Status Kehadiran</th>
                <th width="27%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporanKbm->presensiSiswa as $idx => $p)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ $p->nama_siswa }}</td>
                <td class="text-center" style="font-weight: bold;">{{ strtoupper($p->status) }}</td>
                <td>{{ $p->keterangan ?: '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="ttd-box">
        <tr>
            <td>
                Mengetahui,<br>
                Waka Kurikulum & Akademik<br>
                <div class="signature-space"></div>
                <strong><u>SOKID, ST, M.Kom</u></strong><br>
                NIP. -
            </td>
            <td>
                Arjawinangun, {{ \Carbon\Carbon::parse($laporanKbm->tanggal_realisasi)->isoFormat('D MMMM Y') }}<br>
                Guru Mata Pelajaran,<br>
                <div class="signature-space"></div>
                <strong><u>{{ $laporanKbm->guru?->name ?? 'Guru Pengampu' }}</u></strong><br>
                NIP. {{ $laporanKbm->guru->nip ?? '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
