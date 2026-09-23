<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Kehadiran Guru - SMK Plus Al-Hilal Arjawinangun</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.3;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }

        .kop-surat {
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
            text-align: center;
            position: relative;
        }

        .kop-logo {
            position: absolute;
            left: 10px;
            top: 5px;
            width: 75px;
            height: 75px;
            object-fit: contain;
        }

        .kop-yayasan {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-sekolah {
            font-size: 16pt;
            font-weight: 900;
            color: #047857;
            text-transform: uppercase;
            margin: 2px 0;
        }

        .kop-alamat {
            font-size: 9pt;
            font-style: italic;
            color: #333;
        }

        .judul-laporan {
            text-align: center;
            margin-bottom: 18px;
        }

        .judul-laporan h2 {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 0 0 4px 0;
        }

        .judul-laporan p {
            font-size: 10pt;
            margin: 0;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 10.5pt;
        }

        .meta-table td {
            padding: 3px 6px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 20px;
        }

        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px 8px;
        }

        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9pt;
        }

        .stats-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 10pt;
            border: 1px solid #000;
            padding: 8px 12px;
            background-color: #fafafa;
        }

        .ttd-container {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .ttd-table {
            width: 100%;
            font-size: 11pt;
            text-align: center;
        }

        .ttd-space {
            height: 70px;
        }

        .no-print {
            position: fixed;
            top: 15px;
            right: 15px;
            background: #047857;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            z-index: 1000;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print">🖨️ Cetak / Simpan PDF</button>

    <!-- Kop Surat -->
    <div class="kop-surat">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="kop-logo" onerror="this.style.display='none'">
        <div class="kop-yayasan">Yayasan Pendidikan Al-Hilal Arjawinangun</div>
        <div class="kop-sekolah">SMK PLUS AL-HILAL ARJAWINANGUN</div>
        <div class="kop-alamat">
            NSS: 402021707055 | NPSN: 69753443 | Akreditasi "B"<br>
            Kompleks Ponpes Kebon Jambu Babakan Ciwaringin - Cirebon 45162 | Telp: (0231) 358899 | Email: smkplusalhilal@gmail.com
        </div>
    </div>

    <!-- Judul -->
    <div class="judul-laporan">
        <h2>Laporan Rekapitulasi Kehadiran & Presensi Mengajar Guru</h2>
        <p>Berdasarkan Data Presensi Real-Time HilalEdu SMK Plus Al-Hilal</p>
    </div>

    <!-- Meta Informasi -->
    <table class="meta-table">
        <tr>
            <td style="width: 18%;"><b>Periode Bulan</b></td>
            <td style="width: 3%;">:</td>
            <td style="width: 40%;"><b>{{ \Carbon\Carbon::parse($bulan . '-01')->isoFormat('MMMM Y') }}</b></td>
            <td style="width: 15%;"><b>Dicetak Oleh</b></td>
            <td style="width: 3%;">:</td>
            <td>{{ auth()->user()->name }}</td>
        </tr>
        <tr>
            <td><b>Filter Guru</b></td>
            <td>:</td>
            <td>{{ $selectedGuru ? $selectedGuru->name . ' (' . ($selectedGuru->nip ?: '@'.$selectedGuru->username) . ')' : 'Seluruh Guru Pengajar' }}</td>
            <td><b>Waktu Cetak</b></td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WIB</td>
        </tr>
        @if($status)
        <tr>
            <td><b>Status Khusus</b></td>
            <td>:</td>
            <td colspan="4">{{ ucfirst(str_replace('_', ' ', $status)) }}</td>
        </tr>
        @endif
    </table>

    <!-- Statistik Ringkas -->
    <div class="stats-box">
        <div><b>Total Sesi:</b> {{ $totalSesi }} Sesi</div>
        <div><b>Tepat Waktu:</b> {{ $totalHadir }} (≤ 10 mnt)</div>
        <div><b>Terlambat:</b> {{ $totalTerlambat }} (&gt; 10 mnt)</div>
        <div><b>Izin:</b> {{ $totalIzin }}</div>
        <div><b>Sakit:</b> {{ $totalSakit }}</div>
        <div><b>Tugas Luar:</b> {{ $totalTugasLuar }}</div>
        <div><b>Persentase Disiplin:</b> <b>{{ $persentase }}%</b></div>
    </div>

    <!-- Tabel Rincian Kehadiran -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 16%;">Tanggal & Hari</th>
                <th style="width: 20%;">Nama Guru</th>
                <th style="width: 22%;">Mata Pelajaran & Kelas</th>
                <th style="width: 12%;">Jadwal KBM</th>
                <th style="width: 10%;">Jam Masuk</th>
                <th style="width: 15%;">Status Presensi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayat as $idx => $absen)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td>
                    <b>{{ \Carbon\Carbon::parse($absen->tanggal)->isoFormat('dddd') }}</b>, 
                    {{ \Carbon\Carbon::parse($absen->tanggal)->format('d/m/Y') }}
                </td>
                <td>
                    <b>{{ $absen->guru?->name ?? '-' }}</b>
                    @if($absen->guru && $absen->guru->nip)
                    <br><small style="color:#555;">NIP: {{ $absen->guru->nip }}</small>
                    @endif
                </td>
                <td>
                    {{ $absen->jadwal->mataPelajaran->nama ?? '-' }}
                    <br><small><b>Kelas: {{ $absen->jadwal->kelas ?? '-' }}</b></small>
                </td>
                <td style="text-align: center; font-family: monospace;">
                    {{ substr($absen->jadwal->jam_mulai ?? '00:00', 0, 5) }} - {{ substr($absen->jadwal->jam_selesai ?? '00:00', 0, 5) }}
                </td>
                <td style="text-align: center; font-family: monospace;">
                    {{ substr($absen->jam_absen, 0, 5) }}
                </td>
                <td style="text-align: center;">
                    @if($absen->status == 'hadir')
                        <b>Hadir Tepat Waktu</b>
                    @elseif($absen->status == 'terlambat')
                        <b>Terlambat ({{ $absen->terlambat_menit }} mnt)</b>
                    @elseif($absen->status == 'izin')
                        <i>Izin</i>
                    @elseif($absen->status == 'sakit')
                        <i>Sakit</i>
                    @else
                        <i>Tugas Luar</i>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">
                    Tidak ada rekaman presensi pada filter dan periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="ttd-container">
        <table class="ttd-table">
            <tr>
                <td style="width: 50%;">
                    Mengetahui,<br>
                    Kepala SMK Plus Al-Hilal Arjawinangun
                    <div class="ttd-space"></div>
                    <b><u>H. Moch. Ridwan, M.Pd.I</u></b><br>
                    NIP. 19780412 200501 1 008
                </td>
                <td style="width: 50%;">
                    Arjawinangun, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}<br>
                    Wakasek Bidang Kurikulum & Akademik
                    <div class="ttd-space"></div>
                    <b><u>Sokid, ST, M.Kom</u></b><br>
                    NIP. 19850614 201101 1 005
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
