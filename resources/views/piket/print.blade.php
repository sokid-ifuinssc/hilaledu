<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian Petugas Piket - {{ $hari }}, {{ \Carbon\Carbon::parse($tanggal)->isoFormat('D MMMM Y') }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10.5pt; line-height: 1.35; color: #000; padding: 20px; margin: 0; }
        .kop-surat { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px; position: relative; }
        .kop-logo { position: absolute; left: 10px; top: 0; width: 65px; height: 65px; object-fit: contain; }
        .kop-header { margin-left: 75px; margin-right: 75px; }
        .kop-header h2 { margin: 0; font-size: 13pt; font-weight: bold; }
        .kop-header h1 { margin: 2px 0; font-size: 15pt; font-weight: bold; }
        .kop-header p { margin: 1px 0; font-size: 8.5pt; }
        .title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; margin-bottom: 10px; }
        table.identitas { width: 100%; margin-bottom: 10px; font-size: 10pt; }
        table.identitas td { padding: 2px 0; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9pt; }
        table.data th, table.data td { border: 1px solid #000; padding: 4px 5px; }
        table.data th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .ttd-box { width: 100%; margin-top: 25px; font-size: 10pt; }
        .ttd-box td { vertical-align: top; text-align: center; width: 50%; }
        .signature-space { height: 60px; }
        .kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9pt; }
        .kpi-table td { border: 1px solid #333; padding: 4px 6px; text-align: center; }
        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0f172a; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
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

    <div class="title">LAPORAN HARIAN PETUGAS PIKET & PRESENSI DEWAN GURU</div>

    <table class="identitas">
        <tr>
            <td width="20%">Hari / Tanggal</td>
            <td width="2%">:</td>
            <td width="38%"><strong>{{ $hari }}, {{ \Carbon\Carbon::parse($tanggal)->isoFormat('D MMMM Y') }}</strong></td>
            <td width="18%">Waktu Cetak</td>
            <td width="2%">:</td>
            <td width="20%">{{ now()->format('H:i') }} WIB</td>
        </tr>
        <tr>
            <td>Petugas Piket</td>
            <td>:</td>
            <td colspan="4">
                @if($petugasHariIni->isEmpty())
                <em>-</em>
                @else
                {{ $petugasHariIni->map(fn($p) => $p->user->name . ' (' . ($p->user->role === 'tendik' ? 'Tendik' : 'Guru') . ')')->join(', ') }}
                @endif
            </td>
        </tr>
    </table>

    <!-- Ringkasan Statistik -->
    <table class="kpi-table">
        <tr style="background:#f0f0f0; font-weight:bold;">
            <td>Total Guru</td>
            <td>Hadir Tepat (&le;07.30)</td>
            <td>Hadir Terlambat</td>
            <td>Izin</td>
            <td>Sakit</td>
            <td>Tugas Luar</td>
            <td>Pulang Tepat (&ge;14.10)</td>
            <td>Pulang Cepat (&lt;14.00)</td>
        </tr>
        <tr>
            <td><strong>{{ $rekap['total_guru'] }}</strong></td>
            <td><strong>{{ $rekap['hadir_masuk'] }}</strong></td>
            <td><strong>{{ $rekap['terlambat'] }}</strong></td>
            <td><strong>{{ $rekap['izin'] }}</strong></td>
            <td><strong>{{ $rekap['sakit'] }}</strong></td>
            <td><strong>{{ $rekap['tugas_luar'] }}</strong></td>
            <td><strong>{{ $rekap['pulang_tepat'] }}</strong></td>
            <td><strong>{{ $rekap['pulang_cepat'] }}</strong></td>
        </tr>
    </table>

    <!-- Tabel Data Presensi -->
    <table class="data">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="24%">Nama Guru / NIP</th>
                <th width="22%">Jadwal KBM Hari Ini</th>
                <th width="14%">Presensi Masuk</th>
                <th width="14%">Presensi Pulang</th>
                <th width="22%">Status Jeda / Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $idx => $row)
            @php
                $guru = $row['guru'];
                $p = $row['presensi'];
                $jadwals = $row['jadwals'];
            @endphp
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <strong>{{ $guru->name }}</strong>
                    <div style="font-size: 8pt; color: #444;">{{ $guru->nip ?: 'NIP: -' }}</div>
                </td>
                <td>
                    @if($jadwals->isEmpty())
                        <em style="color:#666;">Tidak Ada Jam KBM</em>
                    @else
                        @foreach($jadwals as $j)
                        <div>&bull; {{ $j->jam_ke_label }}: {{ $j->mataPelajaran->nama }} (Kls {{ $j->kelas }})</div>
                        @endforeach
                    @endif
                </td>
                <td class="text-center">
                    @if($row['has_presensi_masuk'])
                        <strong>{{ substr($p->jam_masuk, 0, 5) }}</strong>
                        <div style="font-size: 8pt;">{{ $row['status_masuk_label'] }}</div>
                    @else
                        <span style="color:#900;">Belum Masuk</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($row['has_presensi_pulang'])
                        <strong>{{ substr($p->jam_pulang, 0, 5) }}</strong>
                        <div style="font-size: 8pt;">{{ $row['status_pulang_label'] }}</div>
                    @else
                        <span style="color:#666;">-</span>
                    @endif
                </td>
                <td>
                    @if($p && $p->status_pelaksanaan_jeda === 'izin_keluar')
                        <strong>[Izin Keluar]</strong> {{ $p->izin_keluar_alasan }} (s/d {{ $p->izin_keluar_jam_kembali ? substr($p->izin_keluar_jam_kembali, 0, 5) : '-' }})
                    @elseif($p && $p->catatan)
                        {{ $p->catatan }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <table class="ttd-box">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Kepala SMK Plus Al-Hilal</strong>
                <div class="signature-space"></div>
                <strong><u>H. Abdurrahman, M.Pd.I</u></strong><br>
                NIP. 19780512 200501 1 003
            </td>
            <td>
                Arjawinangun, {{ \Carbon\Carbon::parse($tanggal)->isoFormat('D MMMM Y') }}<br>
                <strong>Koordinator Petugas Piket</strong>
                <div class="signature-space"></div>
                <strong><u>{{ $petugasHariIni->first()->user->name ?? 'Petugas Piket' }}</u></strong><br>
                NIP. {{ $petugasHariIni->first()->user->nip ?? '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
