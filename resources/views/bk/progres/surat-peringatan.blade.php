@extends('layouts.app')
@section('title', 'Surat Peringatan ' . $tingkat)

@section('content')
<div class="max-w-4xl mx-auto bg-white p-8 print:p-4" id="suratArea">
    <style>
        @media print { body * { visibility: hidden; } #suratArea, #suratArea * { visibility: visible; } #suratArea { position: absolute; left: 0; top: 0; width: 100%; } .no-print { display: none !important; } }
    </style>

    {{-- Kop Surat --}}
    <div class="text-center border-b-4 border-double border-black pb-4 mb-6">
        <h2 class="text-lg font-bold uppercase">{{ $settings['nama_sekolah'] ?? 'SMK AL HILAL' }}</h2>
        <p class="text-sm">{{ $settings['alamat_sekolah'] ?? '' }}</p>
        <p class="text-sm">{{ $settings['telepon_sekolah'] ?? '' }} | {{ $settings['email_sekolah'] ?? '' }}</p>
    </div>

    <h3 class="text-center font-bold text-lg underline mb-1">SURAT PERINGATAN {{ $tingkat }}</h3>
    <p class="text-center text-sm mb-6">Nomor: ......./SP-{{ $tingkat }}/BK/{{ now()->format('m/Y') }}</p>

    <div class="text-sm space-y-3">
        <p>Yang bertanda tangan di bawah ini, Guru Bimbingan dan Konseling {{ $settings['nama_sekolah'] ?? 'SMK AL HILAL' }}, memberikan Surat Peringatan {{ $tingkat }} (SP-{{ $tingkat }}) kepada:</p>

        <table class="text-sm">
            <tr><td class="pr-4 py-1">Nama</td><td>: <strong>{{ $pelanggaran->siswa->nama_lengkap }}</strong></td></tr>
            <tr><td class="pr-4 py-1">NIS</td><td>: {{ $pelanggaran->siswa->nis }}</td></tr>
            <tr><td class="pr-4 py-1">Kelas</td><td>: {{ $pelanggaran->siswa?->kelas?->nama ?? '-' }}</td></tr>
            <tr><td class="pr-4 py-1">Program Keahlian</td><td>: {{ $pelanggaran->siswa?->kelas?->jurusan?->nama ?? '-' }}</td></tr>
        </table>

        <p>Berdasarkan pelanggaran yang dilakukan:</p>
        <table class="text-sm">
            <tr><td class="pr-4 py-1">Jenis Pelanggaran</td><td>: {{ $pelanggaran->jenisPelanggaran->nama }}</td></tr>
            <tr><td class="pr-4 py-1">Kategori</td><td>: {{ $pelanggaran->jenisPelanggaran->kategori->nama ?? '-' }}</td></tr>
            <tr><td class="pr-4 py-1">Tanggal</td><td>: {{ $pelanggaran->tanggal_pelanggaran->format('d F Y') }}</td></tr>
            <tr><td class="pr-4 py-1">Poin</td><td>: {{ $pelanggaran->poin }} poin</td></tr>
            <tr><td class="pr-4 py-1">Total Pelanggaran</td><td>: {{ $totalPelanggaran }} kali</td></tr>
            <tr><td class="pr-4 py-1">Total Poin Dikurangi</td><td>: {{ $totalPoin }} poin</td></tr>
        </table>

        <p class="mt-4">Deskripsi: {{ $pelanggaran->deskripsi }}</p>

        @if($historiPelanggaran->count() > 1)
        <div class="mt-4">
            <p class="font-semibold">Riwayat Pelanggaran:</p>
            <table class="w-full text-xs mt-2 border">
                <thead><tr class="bg-gray-100"><th class="border p-1">No</th><th class="border p-1">Tanggal</th><th class="border p-1">Pelanggaran</th><th class="border p-1">Poin</th></tr></thead>
                <tbody>
                @foreach($historiPelanggaran as $i => $hp)
                <tr><td class="border p-1 text-center">{{ $i+1 }}</td><td class="border p-1">{{ $hp->tanggal_pelanggaran->format('d/m/Y') }}</td><td class="border p-1">{{ $hp->jenisPelanggaran->nama }}</td><td class="border p-1 text-center">{{ $hp->poin }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <p class="mt-4">Dengan ini siswa tersebut diberikan Surat Peringatan {{ $tingkat }} (SP-{{ $tingkat }}). Apabila siswa mengulangi pelanggaran yang sama atau melakukan pelanggaran lainnya, maka akan diberikan sanksi yang lebih berat sesuai ketentuan yang berlaku.</p>

        <div class="mt-8 grid grid-cols-2 gap-8 text-center text-sm">
            <div>
                <p>Orang Tua/Wali,</p>
                <div class="h-20"></div>
                <p class="border-t border-gray-400 pt-1">( ........................... )</p>
            </div>
            <div>
                <p>{{ $settings['kota_sekolah'] ?? 'Tempat' }}, {{ now()->format('d F Y') }}</p>
                <p>Guru BK,</p>
                <div class="h-20"></div>
                <p class="border-t border-gray-400 pt-1">( {{ auth()->user()->nama_lengkap }} )</p>
            </div>
        </div>

        <div class="mt-6 text-center text-sm">
            <p>Mengetahui,</p>
            <div class="grid grid-cols-{{ $tingkat >= 3 ? 4 : ($tingkat >= 2 ? 3 : 2) }} gap-4 mt-2">
                <div>
                    <p>Wali Kelas,</p>
                    <div class="h-20"></div>
                    <p class="border-t border-gray-400 pt-1">( ........................... )</p>
                </div>
                <div>
                    <p>Ketua Program,</p>
                    <div class="h-20"></div>
                    <p class="border-t border-gray-400 pt-1">( ........................... )</p>
                </div>
                @if($tingkat >= 2)
                <div>
                    <p>Waka Kesiswaan,</p>
                    <div class="h-20"></div>
                    <p class="border-t border-gray-400 pt-1">( ........................... )</p>
                </div>
                @endif
                @if($tingkat >= 3)
                <div>
                    <p>Kepala Sekolah,</p>
                    <div class="h-20"></div>
                    <p class="border-t border-gray-400 pt-1">( ........................... )</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="max-w-4xl mx-auto mt-4 no-print flex gap-3">
    <button onclick="window.print()" class="btn-primary">🖨️ Cetak Surat</button>
    <a href="{{ route('bk.progres.show', $pelanggaran) }}" class="btn-secondary">← Kembali</a>
</div>
@endsection
