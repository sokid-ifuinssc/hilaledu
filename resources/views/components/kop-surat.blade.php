{{-- Kop Surat Partial - include in surat/laporan cetak --}}
<div class="header" style="text-align:center; border-bottom: 3px double #000; padding-bottom: 15px; margin-bottom: 20px;">
    <div style="text-align:center;">
        <p style="font-size:11pt; margin:0;">{{ $settings['kop_baris1'] ?? 'YAYASAN PONDOK PESANTREN AL HILAL' }}</p>
        <h1 style="font-size:16pt; font-weight:bold; text-transform:uppercase; margin:4px 0;">{{ $settings['kop_baris2'] ?? $settings['nama_sekolah'] ?? 'SMK PLUS AL HILAL' }}</h1>
        <p style="font-size:10pt; margin:2px 0;">{{ $settings['kop_baris3'] ?? $settings['alamat_sekolah'] ?? 'Jl. Cagak Rt 05/02, Ds. Pamulihan, Kec. Pamulihan, Kab. Sumedang' }}</p>
        @if(!empty($settings['kop_baris4']))
        <p style="font-size:10pt; margin:2px 0;">{{ $settings['kop_baris4'] }}</p>
        @endif
    </div>
</div>
