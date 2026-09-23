<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Presensi Kehadiran Dewan Guru - {{ $bulan ? \Carbon\Carbon::parse($bulan . '-01')->isoFormat('MMMM Y') : "T.A. {$tahunAjaran}" }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.2cm 1.5cm;
        }
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 10pt; 
            line-height: 1.35; 
            color: #000; 
            padding: 15px; 
            margin: 0; 
        }
        .kop-surat { 
            text-align: center; 
            border-bottom: 3px double #000; 
            padding-bottom: 8px; 
            margin-bottom: 12px; 
            position: relative; 
        }
        .kop-logo { 
            position: absolute; 
            left: 10px; 
            top: 0; 
            width: 65px; 
            height: 65px; 
            object-fit: contain; 
        }
        .kop-header { 
            margin-left: 75px; 
            margin-right: 75px; 
        }
        .kop-header h2 { margin: 0; font-size: 13pt; font-weight: bold; }
        .kop-header h1 { margin: 2px 0; font-size: 15pt; font-weight: bold; }
        .kop-header p { margin: 1px 0; font-size: 8.5pt; }
        .title { 
            text-align: center; 
            font-weight: bold; 
            font-size: 12pt; 
            text-decoration: underline; 
            margin-top: 10px;
            margin-bottom: 4px; 
        }
        .subtitle { 
            text-align: center; 
            font-size: 10pt; 
            font-weight: bold;
            margin-bottom: 14px; 
        }
        table.data { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
            font-size: 9pt; 
        }
        table.data th, table.data td { 
            border: 1px solid #000; 
            padding: 4px 6px; 
        }
        table.data th { 
            background-color: #f2f2f2; 
            text-align: center; 
            font-weight: bold; 
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .predikat-sangat-baik { font-weight: bold; color: #065f46; }
        .predikat-baik { font-weight: bold; color: #1e40af; }
        .predikat-cukup { font-weight: bold; color: #b45309; }
        .predikat-kurang { font-weight: bold; color: #b91c1c; }
        .summary-box { 
            border: 1px solid #000; 
            padding: 6px 10px; 
            margin-bottom: 15px; 
            font-size: 9pt; 
            background-color: #fafafa; 
        }
        .ttd-box { 
            width: 100%; 
            margin-top: 25px; 
            font-size: 10pt; 
        }
        .ttd-box td { 
            vertical-align: top; 
            text-align: center; 
            width: 50%; 
        }
        .signature-space { height: 60px; }
        @media print { 
            .no-print { display: none; } 
            body { padding: 0; } 
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 18px; background: #0b7b4b; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 11pt; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
            🖨️ Cetak / Ekspor PDF Seluruh Guru
        </button>
    </div>

    <!-- Kop Surat -->
    <div class="kop-surat">
        <img src="{{ asset('images/logo.png') }}" class="kop-logo" alt="Logo SMK Plus Al-Hilal">
        <div class="kop-header">
            <h2>YAYASAN AL-HILAL ARJAWINANGUN</h2>
            <h1>SMK PLUS AL-HILAL ARJAWINANGUN</h1>
            <p>NSS: 322021708001 &bull; NPSN: 20268571 &bull; Terakreditasi "A"</p>
            <p>Jl. Kebon Melati No. 01 Ds. Jungjang Kec. Arjawinangun Kab. Cirebon 45162 &bull; Telp. (0231) 357890</p>
        </div>
    </div>

    <div class="title">REKAPITULASI PRESENSI & KEHADIRAN DEWAN GURU</div>
    <div class="subtitle">
        {{ $bulan ? 'Periode Bulan: ' . \Carbon\Carbon::parse($bulan . '-01')->isoFormat('MMMM Y') : "Periode: Seluruh Tahun Ajaran {$tahunAjaran}" }}
    </div>

    <div class="summary-box">
        <strong>Keterangan:</strong> Rekapitulasi di bawah ini menggabungkan presensi tatap muka kegiatan belajar mengajar (KBM) di kelas dan presensi resmi pada agenda/kegiatan sekolah.
        Total Dewan Guru: <strong>{{ $rekapSemua['totalGuru'] }} Guru</strong> &bull; Rata-rata Kehadiran Sekolah: <strong>{{ $rekapSemua['rataRataSekolah'] }}%</strong>.
    </div>

    <table class="data">
        <thead>
            <tr>
                <th rowspan="2" width="3%">No</th>
                <th rowspan="2" width="22%">Nama Guru & NIP</th>
                <th rowspan="2" width="15%">Tugas / Jabatan</th>
                <th colspan="3" width="20%">Presensi KBM di Kelas</th>
                <th colspan="2" width="14%">Kegiatan Sekolah</th>
                <th colspan="3" width="18%">Total Akumulasi</th>
                <th rowspan="2" width="8%">Predikat</th>
            </tr>
            <tr>
                <th width="7%">Tepat</th>
                <th width="6%">Telat</th>
                <th width="7%">Izin/Sakit</th>
                <th width="7%">Hadir</th>
                <th width="7%">Agenda</th>
                <th width="6%">Hadir</th>
                <th width="6%">Sesi</th>
                <th width="6%">% Hadir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekapSemua['rows'] as $item)
            <tr>
                <td class="text-center">{{ $item['no'] }}</td>
                <td>
                    <strong>{{ $item['nama'] }}</strong>
                    @if($item['nip'] && $item['nip'] !== '-')
                        <br><span style="font-size: 8pt; color: #555;">NIP. {{ $item['nip'] }}</span>
                    @elseif($item['kode_guru'])
                        <br><span style="font-size: 8pt; color: #555;">Kode: {{ $item['kode_guru'] }}</span>
                    @endif
                </td>
                <td>
                    {{ $item['jabatan'] }}
                </td>
                <td class="text-center">{{ $item['kbm_hadir'] }}</td>
                <td class="text-center">{{ $item['kbm_terlambat'] }}</td>
                <td class="text-center">{{ $item['kbm_izin_sakit'] }}</td>
                <td class="text-center">{{ $item['kegiatan_hadir'] }}</td>
                <td class="text-center">{{ $item['kegiatan_total'] }}</td>
                <td class="text-center"><strong>{{ $item['total_hadir'] }}</strong></td>
                <td class="text-center">{{ $item['total_sesi'] }}</td>
                <td class="text-center">
                    <strong>{{ $item['persentase'] }}%</strong>
                </td>
                <td class="text-center">
                    @php
                        $pred = $item['predikat'];
                        $predClass = match($pred) {
                            'Sangat Baik' => 'predikat-sangat-baik',
                            'Baik' => 'predikat-baik',
                            'Cukup' => 'predikat-cukup',
                            default => 'predikat-kurang'
                        };
                    @endphp
                    <span class="{{ $predClass }}">{{ $pred }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center" style="padding: 15px;">Belum ada data presensi dewan guru pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd-box">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala SMK Plus Al-Hilal<br>
                <div class="signature-space"></div>
                <strong><u>{{ $settings['nama_kepala_sekolah'] }}</u></strong><br>
                NIP. {{ $settings['nip_kepala_sekolah'] ?: '-' }}
            </td>
            <td>
                {{ $settings['titimangsa'] }}<br>
                Waka Bidang Kurikulum & Akademik,<br>
                <div class="signature-space"></div>
                <strong><u>{{ $settings['nama_waka_kurikulum'] }}</u></strong><br>
                NIP. {{ $settings['nip_waka_kurikulum'] ?: '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
