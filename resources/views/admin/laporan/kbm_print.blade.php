<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Realisasi KBM Guru - SMK Plus Al-Hilal Arjawinangun</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
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

        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 10pt;
        }

        .meta-table td {
            padding: 3px 6px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 20px;
        }

        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px 7px;
        }

        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5pt;
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
            background: #1e3a8a;
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
        <h2>Laporan Rekapitulasi Realisasi KBM & Pembelajaran Guru</h2>
        <p>Tercatat pada HilalEdu SMK Plus Al-Hilal Arjawinangun</p>
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
            <td>{{ $selectedGuru ? $selectedGuru->name : 'Seluruh Guru' }}</td>
            <td><b>Waktu Cetak</b></td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y, HH:mm') }} WIB</td>
        </tr>
        @if($kelas || $kesesuaian)
        <tr>
            <td><b>Filter Lainnya</b></td>
            <td>:</td>
            <td colspan="4">
                {{ $kelas ? "Kelas: {$kelas} " : '' }}
                {{ $kesesuaian ? "| Kesesuaian: " . ucfirst(str_replace('_', ' ', $kesesuaian)) : '' }}
            </td>
        </tr>
        @endif
    </table>

    <!-- Tabel Data -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 14%;">Tanggal & Hari</th>
                <th style="width: 18%;">Nama Guru</th>
                <th style="width: 18%;">Mapel & Kelas</th>
                <th style="width: 14%;">Kesesuaian RPP</th>
                <th style="width: 12%;">Presensi Siswa</th>
                <th style="width: 20%;">Materi / Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporans as $idx => $lap)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td>
                    <b>{{ \Carbon\Carbon::parse($lap->tanggal_realisasi)->isoFormat('dddd') }}</b>,
                    {{ \Carbon\Carbon::parse($lap->tanggal_realisasi)->format('d/m/Y') }}
                </td>
                <td><b>{{ $lap->guru?->name ?? '-' }}</b></td>
                <td>
                    {{ $lap->jadwal->mataPelajaran->nama ?? '-' }}
                    <br><small>Kelas: <b>{{ $lap->jadwal->kelas ?? '-' }}</b></small>
                </td>
                <td style="text-align: center;">
                    <b>{{ ucfirst(str_replace('_', ' ', $lap->kesesuaian_rencana)) }}</b>
                    <br><small style="color:#555;">({{ ucwords(str_replace('_', ' ', $lap->status_pelaksanaan)) }})</small>
                </td>
                <td style="text-align: center;">
                    Hadir: <b>{{ $lap->jumlah_siswa_hadir }}</b><br>
                    Tdk: {{ $lap->jumlah_siswa_tidak_hadir }} (Tot: {{ $lap->jumlah_siswa_total }})
                </td>
                <td>
                    {{ $lap->catatan_kegiatan ?: ($lap->keterangan_kesesuaian ?: '-') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">
                    Tidak ada data laporan KBM untuk periode filter ini.
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
