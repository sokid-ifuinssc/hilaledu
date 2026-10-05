<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir Pegawai - {{ $periode['label'] }}</title>
    <style>
        @page { size: A4 landscape; margin: 1.2cm 1.5cm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; line-height: 1.35; color: #000; padding: 15px; margin: 0; }
        .kop-surat { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px; position: relative; }
        .kop-logo { position: absolute; left: 10px; top: 0; width: 65px; height: 65px; object-fit: contain; }
        .kop-header { margin-left: 75px; margin-right: 75px; }
        .kop-header h2 { margin: 0; font-size: 13pt; font-weight: bold; }
        .kop-header h1 { margin: 2px 0; font-size: 15pt; font-weight: bold; }
        .kop-header p { margin: 1px 0; font-size: 8.5pt; }
        .title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; margin-top: 10px; margin-bottom: 4px; }
        .subtitle { text-align: center; font-size: 10pt; font-weight: bold; margin-bottom: 12px; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 9pt; }
        table.data th, table.data td { border: 1px solid #000; padding: 4px 5px; }
        table.data th { background-color: #f2f2f2; text-align: center; font-weight: bold; vertical-align: middle; }
        table.data tfoot td { font-weight: bold; background: #fafafa; }
        .text-center { text-align: center; }
        .nip { font-size: 8pt; color: #555; }
        .note { font-size: 8.5pt; margin-bottom: 10px; }
        .ttd-box { width: 100%; margin-top: 20px; font-size: 10pt; page-break-inside: avoid; }
        .ttd-box td { vertical-align: top; text-align: center; }
        .signature-space { height: 60px; }
        @media print { .no-print { display: none; } body { padding: 0; } thead { display: table-header-group; } tr { page-break-inside: avoid; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 18px; background: #0b7b4b; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 11pt;">
            Cetak / Simpan PDF
        </button>
    </div>

    <div class="kop-surat">
        <img src="{{ asset('images/logo.png') }}" class="kop-logo" alt="Logo SMK Plus Al-Hilal">
        <div class="kop-header">
            <h2>YAYASAN AL-HILAL ARJAWINANGUN</h2>
            <h1>SMK PLUS AL-HILAL ARJAWINANGUN</h1>
            <p>NSS: 322021708001 &bull; NPSN: 20268571 &bull; Terakreditasi "A"</p>
            <p>Jl. Kebon Melati No. 01 Ds. Jungjang Kec. Arjawinangun Kab. Cirebon 45162 &bull; Telp. (0231) 357890</p>
        </div>
    </div>

    <div class="title">DAFTAR HADIR PEGAWAI</div>
    <div class="subtitle">
        {{ strtoupper($periode['label']) }}
        &nbsp;({{ $periode['start']->format('d/m/Y') }} s.d. {{ $periode['end']->format('d/m/Y') }})
        @if($selfName)<br>Atas nama: {{ $selfName }}@endif
    </div>

    @php $rows = $rekap['rows']; $t = $rekap['total']; @endphp
    <table class="data">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th>Nama</th>
                <th width="8%">Jumlah Hari Kerja</th>
                <th width="8%">Jumlah Kehadiran</th>
                <th width="8%">Jumlah Tidak Hadir</th>
                <th width="8%">Tanpa Keterangan</th>
                <th width="6%">Sakit</th>
                <th width="6%">Ijin</th>
                <th width="7%">Dinas Luar</th>
                <th width="9%">Persentase Kehadiran</th>
                <th width="9%">Persentase Tidak Hadir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $r)
            <tr>
                <td class="text-center">{{ $r['no'] }}</td>
                <td>
                    <strong>{{ $r['nama'] }}</strong>
                    <span class="nip">({{ $r['jenis'] }}{{ $r['nip'] ? ' - ' . $r['nip'] : '' }})</span>
                </td>
                <td class="text-center">{{ $r['hari_kerja'] }}</td>
                <td class="text-center">{{ $r['hadir'] }}</td>
                <td class="text-center">{{ $r['tidak_hadir'] }}</td>
                <td class="text-center">{{ $r['tanpa_keterangan'] }}</td>
                <td class="text-center">{{ $r['sakit'] }}</td>
                <td class="text-center">{{ $r['izin'] }}</td>
                <td class="text-center">{{ $r['dinas_luar'] }}</td>
                <td class="text-center"><strong>{{ $r['persen_hadir'] }}%</strong></td>
                <td class="text-center">{{ $r['persen_tidak'] }}%</td>
            </tr>
            @empty
            <tr><td colspan="11" class="text-center" style="padding: 15px;">Belum ada data pada periode ini.</td></tr>
            @endforelse
        </tbody>
        @if($rows->count() > 1)
        <tfoot>
            <tr>
                <td colspan="2" style="text-align:right;">TOTAL / RATA-RATA</td>
                <td class="text-center">{{ $t['hari_kerja'] }}</td>
                <td class="text-center">{{ $t['hadir'] }}</td>
                <td class="text-center">{{ $t['tidak_hadir'] }}</td>
                <td class="text-center">{{ $t['tanpa_keterangan'] }}</td>
                <td class="text-center">{{ $t['sakit'] }}</td>
                <td class="text-center">{{ $t['izin'] }}</td>
                <td class="text-center">{{ $t['dinas_luar'] }}</td>
                <td class="text-center">{{ $t['persen_hadir'] }}%</td>
                <td class="text-center">{{ $t['persen_tidak'] }}%</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="note">
        Keterangan: Jumlah tidak hadir = tanpa keterangan + sakit + ijin + dinas luar. Hari kerja tidak termasuk hari libur Kalender Akademik.
    </div>

    <table class="ttd-box">
        <tr>
            <td width="60%"></td>
            <td width="40%">
                {{ $settings['titimangsa'] }}<br>
                Kepala SMK Plus Al-Hilal Arjawinangun,
                <div class="signature-space"></div>
                <strong><u>{{ $settings['nama_kepala_sekolah'] }}</u></strong><br>
                NIP. {{ $settings['nip_kepala_sekolah'] ?: '-' }}
            </td>
        </tr>
    </table>
</body>
</html>
