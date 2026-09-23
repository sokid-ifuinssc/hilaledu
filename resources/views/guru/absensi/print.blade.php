<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Kehadiran Mengajar Guru - {{ $user->name }}</title>
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
        table.identitas td { padding: 2px 0; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 10pt; }
        table.data th, table.data td { border: 1px solid #000; padding: 5px 6px; }
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

    <div class="title">REKAPITULASI PRESENSI KEHADIRAN MENGAJAR GURU</div>

    <table class="identitas">
        <tr>
            <td width="20%">Nama Guru</td>
            <td width="2%">:</td>
            <td width="38%"><strong>{{ $user->name }}</strong></td>
            <td width="20%">Bulan / Periode</td>
            <td width="2%">:</td>
            <td width="18%">{{ \Carbon\Carbon::parse($bulan . '-01')->isoFormat('MMMM Y') }}</td>
        </tr>
        <tr>
            <td>NIP / ID Guru</td>
            <td>:</td>
            <td>{{ $user->nip ?: $user->username }}</td>
            <td>Total Kehadiran</td>
            <td>:</td>
            <td><strong>{{ $persentase }}%</strong> ({{ $totalHadir + $totalTerlambat + $totalTugasLuar }}/{{ $totalSesi }} Sesi)</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="15%">Tanggal</th>
                <th width="15%">Waktu Absen</th>
                <th width="30%">Mata Pelajaran & Kelas</th>
                <th width="20%">Status Kehadiran</th>
                <th width="15%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayat as $idx => $r)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td>
                <td class="text-center">{{ $r->jam_absen }}</td>
                <td>{{ $r->jadwal->mataPelajaran->nama ?? 'Mata Pelajaran' }} (Kelas {{ $r->jadwal->kelas ?? '-' }})</td>
                <td class="text-center" style="font-weight: bold;">{{ strtoupper($r->status_label) }}</td>
                <td>{{ $r->catatan ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data kehadiran pada bulan ini.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <td colspan="3" class="text-center">RINGKASAN KEHADIRAN</td>
                <td colspan="3">
                    Hadir Tepat Waktu: {{ $totalHadir }} &bull; 
                    Terlambat: {{ $totalTerlambat }} &bull; 
                    Izin: {{ $totalIzin }} &bull; 
                    Sakit: {{ $totalSakit }} &bull; 
                    Tugas Luar: {{ $totalTugasLuar }}
                </td>
            </tr>
        </tfoot>
    </table>

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
                Guru Bersangkutan,<br>
                <div class="signature-space"></div>
                <strong><u>{{ $user->name }}</u></strong><br>
                NIP. {{ $user->nip ?: '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
