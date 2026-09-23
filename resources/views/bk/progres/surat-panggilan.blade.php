@extends('layouts.app')
@section('title', 'Surat Panggilan Orang Tua')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-8 print:p-4" id="suratArea">
    <style>
        @media print { body * { visibility: hidden; } #suratArea, #suratArea * { visibility: visible; } #suratArea { position: absolute; left: 0; top: 0; width: 100%; } .no-print { display: none !important; } }
    </style>

    <div class="text-center border-b-4 border-double border-black pb-4 mb-6">
        <h2 class="text-lg font-bold uppercase">{{ $settings['nama_sekolah'] ?? 'SMK AL HILAL' }}</h2>
        <p class="text-sm">{{ $settings['alamat_sekolah'] ?? '' }}</p>
        <p class="text-sm">{{ $settings['telepon_sekolah'] ?? '' }} | {{ $settings['email_sekolah'] ?? '' }}</p>
    </div>

    <h3 class="text-center font-bold text-lg underline mb-1">SURAT PANGGILAN ORANG TUA / WALI</h3>
    <p class="text-center text-sm mb-6">Nomor: ......./POT/BK/{{ now()->format('m/Y') }}</p>

    <div class="text-sm space-y-3">
        <p>Kepada Yth.<br>Orang Tua/Wali dari:<br></p>

        <table class="text-sm">
            <tr><td class="pr-4 py-1">Nama</td><td>: <strong>{{ $pelanggaran->siswa->nama_lengkap }}</strong></td></tr>
            <tr><td class="pr-4 py-1">NIS</td><td>: {{ $pelanggaran->siswa->nis }}</td></tr>
            <tr><td class="pr-4 py-1">Kelas</td><td>: {{ $pelanggaran->siswa?->kelas?->nama ?? '-' }}</td></tr>
        </table>

        <p>Dengan hormat,</p>
        <p>Sehubungan dengan pelanggaran yang dilakukan oleh putra/putri Bapak/Ibu, kami memohon kehadiran Bapak/Ibu untuk menghadiri pertemuan di sekolah guna membahas permasalahan tersebut.</p>

        <table class="text-sm">
            <tr><td class="pr-4 py-1">Jenis Pelanggaran</td><td>: {{ $pelanggaran->jenisPelanggaran->nama }}</td></tr>
            <tr><td class="pr-4 py-1">Tanggal Pelanggaran</td><td>: {{ $pelanggaran->tanggal_pelanggaran->format('d F Y') }}</td></tr>
            <tr><td class="pr-4 py-1">Total Pelanggaran</td><td>: {{ $totalPelanggaran }} kali</td></tr>
        </table>

        <p>Kami berharap Bapak/Ibu dapat hadir pada:<br>
        Hari/Tanggal: ..............................<br>
        Waktu: ..............................<br>
        Tempat: Ruang BK {{ $settings['nama_sekolah'] ?? 'SMK AL HILAL' }}</p>

        <p>Atas perhatian dan kehadiran Bapak/Ibu, kami ucapkan terima kasih.</p>

        <div class="mt-8 grid grid-cols-2 gap-8 text-center text-sm">
            <div></div>
            <div>
                <p>{{ $settings['kota_sekolah'] ?? 'Tempat' }}, {{ now()->format('d F Y') }}</p>
                <p>Guru BK,</p>
                <div class="h-20"></div>
                <p class="border-t border-gray-400 pt-1">( {{ auth()->user()->nama_lengkap }} )</p>
            </div>
        </div>
    </div>
</div>

<div class="max-w-4xl mx-auto mt-4 no-print flex gap-3">
    <button onclick="window.print()" class="btn-primary">🖨️ Cetak Surat</button>
    <a href="{{ route('bk.progres.show', $pelanggaran) }}" class="btn-secondary">← Kembali</a>
</div>
@endsection
