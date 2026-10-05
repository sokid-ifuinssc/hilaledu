<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Kehadiran Mengajar Guru - {{ $user->name }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; line-height: 1.35; color: #000; padding: 20px; margin: 0; }
        .kop-surat { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px; position: relative; }
        .kop-logo { position: absolute; left: 10px; top: 0; width: 65px; height: 65px; object-fit: contain; }
        .kop-header { margin-left: 80px; margin-right: 80px; }
        .kop-header h2 { margin: 0; font-size: 13pt; font-weight: bold; }
        .kop-header h1 { margin: 2px 0; font-size: 15pt; font-weight: bold; }
        .kop-header p { margin: 1px 0; font-size: 8.5pt; }
        .title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; margin-bottom: 12px; }
        
        table.identitas { width: 100%; margin-bottom: 10px; font-size: 10pt; border-collapse: collapse; }
        table.identitas td { padding: 2px 4px; vertical-align: top; }
        
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9pt; }
        table.data th, table.data td { border: 1px solid #000; padding: 4px 6px; }
        table.data th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .day-header { background-color: #eaeded; font-weight: bold; }
        
        table.ringkasan { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 9.5pt; }
        table.ringkasan td { padding: 4px 6px; border: 1px solid #ddd; }
        
        .ttd-box { width: 100%; margin-top: 25px; font-size: 10pt; page-break-inside: avoid; }
        .ttd-box td { vertical-align: top; text-align: center; width: 50%; }
        .signature-space { height: 60px; }
        @media print { 
            .no-print { display: none; } 
            body { padding: 0; } 
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0b7b4b; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- Kop Surat Sekolah -->
    <div class="kop-surat">
        <img src="{{ asset('images/logo.png') }}" class="kop-logo" alt="Logo">
        <div class="kop-header">
            <h2>YAYASAN AL-HILAL ARJAWINANGUN</h2>
            <h1>SMK PLUS AL-HILAL ARJAWINANGUN</h1>
            <p>NSS: 322021708001 &bull; NPSN: 20268571 &bull; Terakreditasi "A"</p>
            <p>Jl. Kebon Melati No. 01 Ds. Jungjang Kec. Arjawinangun Kab. Cirebon 45162 &bull; Telp. (0231) 357890</p>
        </div>
    </div>

    <div class="title">REKAPITULASI KEHADIRAN MENGAJAR GURU</div>

    <!-- Identitas Guru & Periode -->
    <table class="identitas">
        <tr>
            <td width="18%">Nama Guru</td>
            <td width="2%">:</td>
            <td width="40%"><strong>{{ $user->name }}</strong></td>
            <td width="18%">Periode</td>
            <td width="2%">:</td>
            <td width="20%"><strong>{{ $periode['label'] }}</strong></td>
        </tr>
        <tr>
            <td>NIP / NUPTK</td>
            <td>:</td>
            <td>{{ $user->nip ?: ($user->nuptk ?: ($user->username ?: '-')) }}</td>
            <td>Beban Mengajar</td>
            <td>:</td>
            <td>{{ $stat['jam_per_minggu'] }} Jam Pelajaran / Minggu</td>
        </tr>
        <tr>
            <td>Jabatan / Peran</td>
            <td>:</td>
            <td>Guru Pengajar</td>
            <td>Persentase Kehadiran</td>
            <td>:</td>
            <td><strong>{{ $stat['persen_hadir'] }}%</strong> ({{ $stat['sesi_hadir'] }} / {{ $stat['sesi_hadir'] + $stat['sesi_tidak_hadir'] }} Sesi)</td>
        </tr>
    </table>

    <!-- Ringkasan Statistik -->
    <table class="ringkasan">
        <tr style="background:#f8f9f9; font-weight:bold;">
            <td class="text-center">Hari Efektif KBM</td>
            <td class="text-center">Total Sesi Terjadwal</td>
            <td class="text-center">Sesi Hadir</td>
            <td class="text-center">Tanpa Keterangan</td>
            <td class="text-center">Sakit</td>
            <td class="text-center">Izin</td>
            <td class="text-center">Dinas Luar</td>
        </tr>
        <tr class="text-center">
            <td>{{ $stat['total_hari_efektif'] }} Hari ({{ $stat['hari_berjalan'] }} berjalan)</td>
            <td>{{ $stat['total_sesi'] }} Sesi</td>
            <td><strong>{{ $stat['sesi_hadir'] }}</strong> Sesi</td>
            <td style="{{ $stat['sesi_tanpa_ket'] > 0 ? 'color:red;font-weight:bold;' : '' }}">{{ $stat['sesi_tanpa_ket'] }}</td>
            <td>{{ $stat['sesi_sakit'] }}</td>
            <td>{{ $stat['sesi_izin'] }}</td>
            <td>{{ $stat['sesi_dinas_luar'] }}</td>
        </tr>
    </table>

    <!-- Tabel Rincian Kehadiran Per Hari & Per Jam Mapel -->
    <table class="data">
        <thead>
            <tr>
                <th width="4%">No.</th>
                <th width="15%">Tanggal & Hari</th>
                <th width="15%">Jam & Sesi</th>
                <th width="28%">Mata Pelajaran & Kelas</th>
                <th width="16%">Status Kehadiran</th>
                <th width="22%">Keterangan / Alasan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($hariList as $hari)
                @foreach($hari['mapel_list'] as $mIdx => $m)
                <tr>
                    @if($mIdx === 0)
                    <td class="text-center" rowspan="{{ count($hari['mapel_list']) }}" style="vertical-align: top; font-weight: bold;">
                        {{ $no++ }}
                    </td>
                    <td rowspan="{{ count($hari['mapel_list']) }}" style="vertical-align: top;">
                        <strong>{{ $hari['hari'] }}</strong><br>
                        <span style="font-size: 8.5pt;">{{ \Carbon\Carbon::parse($hari['tanggal'])->format('d/m/Y') }}</span>
                        @if($hari['presensi_harian'])
                        <div style="font-size: 7.5pt; color: #555; margin-top: 3px; border-top: 1px dotted #ccc; padding-top: 2px;">
                            Pagi: {{ $hari['presensi_harian']['jam_masuk'] ?: '-' }}<br>
                            Plg: {{ $hari['presensi_harian']['jam_pulang'] ?: '-' }}
                        </div>
                        @endif
                    </td>
                    @endif

                    <td class="text-center font-mono" style="font-size: 8.5pt;">
                        <strong>{{ $m['jam_ke'] }}</strong><br>
                        ({{ $m['jam_waktu'] }})
                    </td>

                    <td>
                        <strong>{{ $m['mapel'] }}</strong><br>
                        <span style="font-size: 8.5pt;">Kelas {{ $m['kelas'] }} &bull; R. {{ $m['ruang'] }}</span>
                    </td>

                    <td class="text-center">
                        <strong>{{ strtoupper($m['label']) }}</strong>
                    </td>

                    <td style="font-size: 8.5pt;">
                        {{ $m['keterangan'] }}
                    </td>
                </tr>
                @endforeach
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 15px;">Tidak ada jadwal mengajar pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <table class="ttd-box">
        <tr>
            <td>
                Mengetahui,<br>
                Waka Kurikulum / Kepala Sekolah
                <div class="signature-space"></div>
                <strong><u>{{ $settings['nama_waka_kurikulum'] ?? 'Bidang Kurikulum' }}</u></strong><br>
                NIP. {{ $settings['nip_waka_kurikulum'] ?? '-' }}
            </td>
            <td>
                {{ $settings['titimangsa'] ?? ('Arjawinangun, ' . date('d F Y')) }}<br>
                Guru Pengajar Bersangkutan
                <div class="signature-space"></div>
                <strong><u>{{ $user->name }}</u></strong><br>
                NIP. {{ $user->nip ?: ($user->nuptk ?: '-') }}
            </td>
        </tr>
    </table>

</body>
</html>
