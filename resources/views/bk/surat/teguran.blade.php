<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><title>Surat Teguran</title>
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
    <h2 style="font-size:14pt; font-weight:bold; text-decoration:underline;">SURAT TEGURAN</h2>
    <p style="font-size:11pt;">Nomor: ......./BK/{{ date('m/Y') }}</p>
</div>

<div class="content">
    <p>Yang bertanda tangan di bawah ini, Guru Bimbingan Konseling {{ $settings['nama_sekolah'] ?? 'SMK Plus Al Hilal' }}, dengan ini memberikan <strong>TEGURAN</strong> kepada:</p>
</div>

<table class="info">
    <tr><td>Nama Siswa</td><td>: <strong>{{ $pelanggaran->siswa->nama_lengkap }}</strong></td></tr>
    <tr><td>NIS</td><td>: {{ $pelanggaran->siswa->nis }}</td></tr>
    <tr><td>Kelas</td><td>: {{ $pelanggaran->siswa?->kelas?->nama ?? '-' }}</td></tr>
    <tr><td>Jurusan</td><td>: {{ $pelanggaran->siswa?->kelas?->jurusan?->nama ?? '-' }}</td></tr>
</table>

<div class="content">
    <p>Sehubungan dengan pelanggaran yang dilakukan pada tanggal <strong>{{ $pelanggaran->tanggal_pelanggaran->translatedFormat('d F Y') }}</strong>, yaitu:</p>
</div>

<table class="info">
    <tr><td>Jenis Pelanggaran</td><td>: {{ $pelanggaran->jenisPelanggaran->nama }}</td></tr>
    <tr><td>Kategori</td><td>: {{ $pelanggaran->jenisPelanggaran->kategori->nama }}</td></tr>
    <tr><td>Poin</td><td>: -{{ $pelanggaran->poin }} poin</td></tr>
    @if($pelanggaran->keterangan)
    <tr><td>Keterangan</td><td>: {{ $pelanggaran->keterangan }}</td></tr>
    @endif
</table>

<div class="content">
    <p>Dengan surat teguran ini, siswa yang bersangkutan diperingatkan untuk <strong>tidak mengulangi pelanggaran</strong> tersebut. Apabila siswa masih melakukan pelanggaran yang sama atau pelanggaran lainnya, maka akan diberikan sanksi yang lebih berat sesuai dengan peraturan yang berlaku.</p>
    <p style="margin-top:10px;">Demikian surat teguran ini dibuat untuk dapat diperhatikan dan ditaati sebagaimana mestinya.</p>
</div>

<div class="ttd">
    <div class="col">
        <p>Orang Tua/Wali Siswa</p>
        <div class="line"></div>
        <p>(............................)</p>
    </div>
    <div class="col">
        <p>Sumedang, {{ now()->translatedFormat('d F Y') }}</p>
        <p>Guru BK</p>
        <div class="line"></div>
        <p>{{ auth()->user()->nama_lengkap ?? '............................' }}</p>
    </div>
</div>

<div style="margin-top:30px; text-align:center;">
    <p style="font-size:10pt;">Mengetahui,</p>
    <p style="font-size:10pt;">Kepala {{ $settings['nama_sekolah'] ?? 'SMK Plus Al Hilal' }}</p>
    <div style="margin-top:60px;"></div>
    <p style="font-size:10pt;">{{ $settings['nama_kepala_sekolah'] ?? '............................' }}</p>
    <p style="font-size:9pt;">NIP. {{ $settings['nip_kepala_sekolah'] ?? '............................' }}</p>
</div>
</body>
</html>
