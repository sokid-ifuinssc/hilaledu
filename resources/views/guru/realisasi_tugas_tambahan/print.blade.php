<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Realisasi & Kesesuaian Tugas Tambahan - {{ $user->name }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; line-height: 1.35; color: #000; padding: 20px; margin: 0; }
        .kop-surat { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 15px; position: relative; }
        .kop-logo { position: absolute; left: 10px; top: 0; width: 70px; height: 70px; object-fit: contain; }
        .kop-header { margin-left: 80px; margin-right: 80px; }
        .kop-header h2 { margin: 0; font-size: 13pt; font-weight: bold; }
        .kop-header h1 { margin: 2px 0; font-size: 15pt; font-weight: bold; }
        .kop-header p { margin: 1px 0; font-size: 8.5pt; }
        .title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; margin-bottom: 12px; }
        table.identitas { width: 100%; margin-bottom: 12px; font-size: 10pt; }
        table.identitas td { padding: 2px 0; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 9pt; }
        table.data th, table.data td { border: 1px solid #000; padding: 5px 6px; vertical-align: top; }
        table.data th { background-color: #f2f2f2; text-align: center; font-weight: bold; font-size: 9pt; }
        .text-center { text-align: center; }
        .badge-status { font-weight: bold; font-size: 8pt; display: inline-block; padding: 1px 4px; border: 1px solid #000; border-radius: 3px; }
        .ttd-box { width: 100%; margin-top: 25px; font-size: 10pt; }
        .ttd-box td { vertical-align: top; text-align: center; width: 50%; }
        .signature-space { height: 60px; }
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

    <div class="title">LAPORAN EVALUASI DAN KESESUAIAN REALISASI TUGAS TAMBAHAN</div>

    <table class="identitas">
        <tr>
            <td width="20%">Nama Guru</td>
            <td width="2%">:</td>
            <td width="38%"><strong>{{ $user->name }}</strong></td>
            <td width="20%">Tugas Tambahan</td>
            <td width="2%">:</td>
            <td width="18%">{{ $tugas ?: 'Seluruh Tugas' }}</td>
        </tr>
        <tr>
            <td>NUPTK / ID Guru</td>
            <td>:</td>
            <td>{{ $user->nip ?: ($user->nuptk ?: '-') }}</td>
            <td>Tahun Ajaran / Semester</td>
            <td>:</td>
            <td>{{ \App\Models\PengaturanSekolah::getActiveTahunAjaran() }} ({{ \App\Models\PengaturanSekolah::getActiveSemester() }})</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="15%">Tugas Tambahan</th>
                <th width="22%">Rencana Program & Target</th>
                <th width="20%">Indikator Capaian Terencana</th>
                <th width="20%">Realisasi & Keterangan</th>
                <th width="20%">Evaluasi Kesesuaian</th>
            </tr>
        </thead>
        <tbody>
            @forelse($programKerjas as $idx => $pk)
            @php $r = $pk->realisasi; @endphp
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <strong>{{ $pk->tugas_tambahan }}</strong>
                </td>
                <td>
                    <strong>{{ $pk->nama_program }}</strong><br>
                    <span style="font-size: 8pt; color: #444;">
                        Target: {{ $pk->target_waktu ?: '-' }}<br>
                        Anggaran: Rp {{ number_format($pk->anggaran, 0, ',', '.') }}
                    </span>
                </td>
                <td>
                    {{ $pk->indikator_keberhasilan ?: '-' }}
                </td>
                <td>
                    @if($r)
                        <strong>Tgl:</strong> {{ \Carbon\Carbon::parse($r->tanggal_pelaksanaan)->format('d/m/Y') }}<br>
                        <span style="font-size: 8.5pt;">{{ $r->uraian_kegiatan }}</span><br>
                        @if($r->hasil_capaian)
                        <i style="font-size: 8pt;">Output: {{ $r->hasil_capaian }}</i>
                        @endif
                    @else
                        <i style="color: #666;">Belum terealisasi</i>
                    @endif
                </td>
                <td class="text-center">
                    @if($r)
                        <span class="badge-status">{{ strtoupper($r->status_kesesuaian_label) }}</span>
                        @if($r->keterangan_kesesuaian)
                        <div style="font-size: 8pt; text-align: left; margin-top: 4px;">
                            <i>{{ $r->keterangan_kesesuaian }}</i>
                        </div>
                        @endif
                        @if($r->realisasi_anggaran > 0)
                        <div style="font-size: 7.5pt; color: #444; margin-top: 2px;">
                            Realisasi: Rp {{ number_format($r->realisasi_anggaran, 0, ',', '.') }}
                        </div>
                        @endif
                    @else
                        <span style="color: #888;">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada rencana program kerja tugas tambahan pada periode ini.</td>
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
                <strong><u>{{ $settings['nama_kepala_sekolah'] ?? '...' }}</u></strong><br>
                NIP. {{ $settings['nip_kepala_sekolah'] ?? '-' }}
            </td>
            <td>
                {{ $settings['titimangsa'] ?? '...' }}<br>
                Guru Pengemban Tugas,<br>
                <div class="signature-space"></div>
                <strong><u>{{ $user->name }}</u></strong><br>
                NUPTK. {{ $user->nip ?: '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
