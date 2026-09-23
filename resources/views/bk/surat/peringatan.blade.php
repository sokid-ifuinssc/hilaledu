<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><title>Surat Peringatan {{ $tingkat }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Times New Roman',serif; font-size:12pt; line-height:1.6; color:#000; padding:30px 50px; }
        .no-print { text-align:center; margin:20px 0; }
        .no-print button { background:#4f46e5; color:white; border:none; padding:10px 30px; font-size:14px; border-radius:8px; cursor:pointer; margin:0 5px; }
        .no-print button.secondary { background:#6b7280; }
        table.info { margin:10px 0; }
        table.info td { padding:2px 10px 2px 0; font-size:12pt; vertical-align:top; }
        table.info td:first-child { width:150px; }
        .content { margin:15px 0; text-align:justify; }
        .ttd { display:flex; justify-content:space-between; margin-top:40px; }
        .ttd .col { text-align:center; width:45%; }
        .ttd .line { margin-top:60px; }
        .sp-badge { display:inline-block; background:#dc2626; color:white; padding:3px 15px; border-radius:4px; font-weight:bold; font-size:14pt; }
        @media print { .no-print{display:none!important;} body{padding:0;} @page{margin:2cm;size:A4;} }
    </style>
</head>
<body>
<div class="no-print">
    <button onclick="window.print()">🖨️ Cetak Surat</button>
    <button class="secondary" onclick="window.close()">✕ Tutup</button>
</div>

@include('components.kop-surat', ['settings' => $settings])

<div style="text-align:center; margin:25px 0;">
    <h2 style="font-size:14pt; font-weight:bold; text-decoration:underline;">SURAT PERINGATAN {{ $tingkat }}</h2>
    <span class="sp-badge">SP-{{ $tingkat }}</span>
    <p style="font-size:11pt; margin-top:5px;">Nomor: ......./SP{{ $tingkat }}/BK/{{ date('m/Y') }}</p>
</div>

<div class="content">
    <p>Yang bertanda tangan di bawah ini, Kepala {{ $settings['nama_sekolah'] ?? 'SMK Plus Al Hilal' }}, dengan ini memberikan <strong>Surat Peringatan ke-{{ $tingkat }} (SP-{{ $tingkat }})</strong> kepada:</p>
</div>

<table class="info">
    <tr><td>Nama Siswa</td><td>: <strong>{{ $pelanggaran->siswa->nama_lengkap }}</strong></td></tr>
    <tr><td>NIS</td><td>: {{ $pelanggaran->siswa->nis }}</td></tr>
    <tr><td>Kelas</td><td>: {{ $pelanggaran->siswa?->kelas?->nama ?? '-' }}</td></tr>
    <tr><td>Jurusan</td><td>: {{ $pelanggaran->siswa?->kelas?->jurusan?->nama ?? '-' }}</td></tr>
</table>

<div class="content">
    <p>Berdasarkan catatan pelanggaran siswa tersebut, yang telah melakukan pelanggaran sebagai berikut:</p>
</div>

<table class="info">
    <tr><td>Pelanggaran</td><td>: {{ $pelanggaran->jenisPelanggaran->nama }}</td></tr>
    <tr><td>Kategori</td><td>: {{ $pelanggaran->jenisPelanggaran->kategori->nama }}</td></tr>
    <tr><td>Tanggal Kejadian</td><td>: {{ $pelanggaran->tanggal_pelanggaran->translatedFormat('d F Y') }}</td></tr>
    <tr><td>Poin Pelanggaran</td><td>: -{{ $pelanggaran->poin }} poin</td></tr>
    <tr><td>Total Pelanggaran</td><td>: {{ $totalPelanggaran }} kali</td></tr>
    <tr><td>Total Poin</td><td>: {{ $totalPoin }} poin</td></tr>
    <tr><td>Sisa Poin Siswa</td><td>: {{ $pelanggaran->siswa->poin }}/100</td></tr>
</table>

@if($pelanggaran->rekomendasi)
<div class="content">
    <p><strong>Rekomendasi BK:</strong> {{ $pelanggaran->rekomendasi->jenis_rekomendasi_label }} — {{ $pelanggaran->rekomendasi->deskripsi_rekomendasi }}</p>
</div>
@endif

@if($pelanggaran->rekomendasiKaprog)
<div class="content">
    <p><strong>Rekomendasi Kaprog:</strong> {{ $pelanggaran->rekomendasiKaprog->jenis_rekomendasi_label }} — {{ $pelanggaran->rekomendasiKaprog->deskripsi_rekomendasi }}</p>
</div>
@endif

{{-- Histori Lengkap Pelanggaran --}}
@if($historiPelanggaran->count() > 1)
<div class="content">
    <p><strong>Riwayat Pelanggaran Siswa:</strong></p>
</div>
<table style="width:100%; border-collapse:collapse; font-size:10pt; margin:10px 0;">
    <thead>
        <tr style="background:#f3f4f6;">
            <th style="border:1px solid #d1d5db; padding:5px; text-align:center;">No</th>
            <th style="border:1px solid #d1d5db; padding:5px; text-align:left;">Tanggal</th>
            <th style="border:1px solid #d1d5db; padding:5px; text-align:left;">Pelanggaran</th>
            <th style="border:1px solid #d1d5db; padding:5px; text-align:left;">Kategori</th>
            <th style="border:1px solid #d1d5db; padding:5px; text-align:center;">Poin</th>
            <th style="border:1px solid #d1d5db; padding:5px; text-align:left;">Rekomendasi</th>
            <th style="border:1px solid #d1d5db; padding:5px; text-align:left;">Tindak Lanjut</th>
        </tr>
    </thead>
    <tbody>
    @foreach($historiPelanggaran as $idx => $hp)
        <tr>
            <td style="border:1px solid #d1d5db; padding:4px; text-align:center;">{{ $idx + 1 }}</td>
            <td style="border:1px solid #d1d5db; padding:4px;">{{ $hp->tanggal_pelanggaran->format('d/m/Y') }}</td>
            <td style="border:1px solid #d1d5db; padding:4px;">{{ $hp->jenisPelanggaran->nama }}</td>
            <td style="border:1px solid #d1d5db; padding:4px;">{{ $hp->jenisPelanggaran->kategori->nama ?? '-' }}</td>
            <td style="border:1px solid #d1d5db; padding:4px; text-align:center;">-{{ $hp->poin }}</td>
            <td style="border:1px solid #d1d5db; padding:4px; font-size:9pt;">
                @if($hp->rekomendasi){{ $hp->rekomendasi->jenis_rekomendasi_label }}@endif
                @if($hp->rekomendasiKaprog) / {{ $hp->rekomendasiKaprog->jenis_rekomendasi_label }}@endif
                @if(!$hp->rekomendasi && !$hp->rekomendasiKaprog)-@endif
            </td>
            <td style="border:1px solid #d1d5db; padding:4px; font-size:9pt;">
                @if($hp->rekomendasi && $hp->rekomendasi->tindakLanjut){{ $hp->rekomendasi->tindakLanjut->waliKelas->nama_lengkap ?? '-' }}: {{ Str::limit($hp->rekomendasi->tindakLanjut->tindakan_yang_dilakukan, 40) }}@else - @endif
            </td>
        </tr>
    @endforeach
        <tr style="background:#fef2f2; font-weight:bold;">
            <td colspan="4" style="border:1px solid #d1d5db; padding:4px; text-align:right;">Total Poin Dikurangi:</td>
            <td style="border:1px solid #d1d5db; padding:4px; text-align:center;">-{{ $historiPelanggaran->sum('poin') }}</td>
            <td colspan="2" style="border:1px solid #d1d5db; padding:4px;"></td>
        </tr>
    </tbody>
</table>
@endif

<div class="content">
    @if($tingkat == 1)
    <p>Dengan surat peringatan ini, siswa yang bersangkutan diminta untuk <strong>memperbaiki sikap dan perilaku</strong> serta tidak mengulangi pelanggaran. Apabila masih melakukan pelanggaran, akan diberikan Surat Peringatan ke-2 (SP-2).</p>
    @elseif($tingkat == 2)
    <p>Dengan surat peringatan kedua ini, siswa <strong>wajib menunjukkan perubahan sikap</strong> secara signifikan. Orang tua/wali siswa <strong>wajib hadir</strong> ke sekolah untuk pembinaan bersama. Apabila masih melakukan pelanggaran, akan diberikan Surat Peringatan ke-3 (SP-3) yang dapat berujung pada sanksi skorsing atau pengembalian ke orang tua.</p>
    @else
    <p>Surat Peringatan ke-3 (SP-3) ini merupakan <strong>peringatan terakhir</strong>. Apabila siswa masih melakukan pelanggaran setelah SP-3, maka pihak sekolah berhak mengambil tindakan tegas berupa <strong>skorsing atau pengembalian siswa ke orang tua/wali</strong>.</p>
    <p style="margin-top:10px;">Orang tua/wali siswa <strong>WAJIB</strong> hadir ke sekolah untuk menandatangani surat pernyataan kesanggupan.</p>
    @endif
    <p style="margin-top:10px;">Demikian surat peringatan ini dibuat agar menjadi perhatian semua pihak.</p>
</div>

<div class="ttd">
    <div class="col">
        <p>Orang Tua/Wali Siswa</p>
        <div class="line"></div>
        <p>(............................)</p>
    </div>
    <div class="col">
        <p>Sumedang, {{ now()->translatedFormat('d F Y') }}</p>
        <p>Kepala {{ $settings['nama_sekolah'] ?? 'SMK Plus Al Hilal' }}</p>
        <div class="line"></div>
        <p>{{ $settings['nama_kepala_sekolah'] ?? '............................' }}</p>
        <p style="font-size:9pt;">NIP. {{ $settings['nip_kepala_sekolah'] ?? '............................' }}</p>
    </div>
</div>

<div style="margin-top:20px;">
    <p style="font-size:10pt;">Tembusan:</p>
    <ol style="font-size:10pt; margin-left:20px;">
        <li>Guru BK</li>
        <li>Wali Kelas</li>
        <li>Ketua Program Keahlian</li>
        <li>Arsip</li>
    </ol>
</div>

<div style="margin-top:30px; border-top:1px solid #ccc; padding-top:10px;">
    <p style="font-size:10pt;">Siswa yang bersangkutan menyatakan telah menerima dan memahami isi surat peringatan ini:</p>
    <div style="margin-top:30px; display:flex; justify-content:space-around;">
        <div style="text-align:center;">
            <p style="font-size:10pt;">Siswa,</p>
            <div style="margin-top:40px;"></div>
            <p style="font-size:10pt;">({{ $pelanggaran->siswa->nama_lengkap }})</p>
        </div>
    </div>
</div>
</body>
</html>
