@extends('layouts.app')
@section('title', 'Detail Pelanggaran')
@section('subtitle', 'Pelanggaran Saya')

@section('content')
<div class="max-w-3xl space-y-5">
    <div class="stat-card">
        <h3 class="font-semibold text-gray-800 mb-4">Informasi Pelanggaran</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Tanggal:</span> <span class="font-medium">{{ $pelanggaran->tanggal_pelanggaran->format('d M Y') }}</span></div>
            <div><span class="text-gray-500">Waktu:</span> <span class="font-medium">{{ $pelanggaran->waktu_pelanggaran ?? '-' }}</span></div>
            <div><span class="text-gray-500">Jenis:</span> <span class="font-medium">{{ $pelanggaran->jenisPelanggaran->nama }}</span></div>
            
            <div><span class="text-gray-500">Poin Dikurangi:</span> <span class="font-semibold text-red-600">-{{ $pelanggaran->poin }}</span></div>
            <div><span class="text-gray-500">Status:</span> <span class="badge {{ $pelanggaran->status_badge }}">{{ $pelanggaran->status_label }}</span></div>
        </div>
        @if($pelanggaran->deskripsi)
        <div class="mt-4 text-sm"><span class="text-gray-500">Deskripsi:</span><p class="mt-1 text-gray-700">{{ $pelanggaran->deskripsi }}</p></div>
        @endif
    </div>

    {{-- Progres Penanganan --}}
    @php $progres = $pelanggaran->progresPelanggaran; @endphp
    @if($progres)
    <div class="stat-card border-blue-200 bg-blue-50/50">
        <h3 class="font-semibold text-blue-800 mb-3">📋 Progres Penanganan</h3>
        <div class="space-y-2 text-sm">
            <div><span class="text-gray-500">Jenis Tindakan:</span> <span class="badge text-sm {{ $progres->jenis_tindakan_badge }}">{{ $progres->jenis_tindakan_label }}</span></div>
            <div><span class="text-gray-500">Status Progres:</span> <span class="badge text-sm {{ $progres->status_badge }}">{{ $progres->status_label }}</span></div>
        </div>
    </div>
    @endif

    <a href="{{ route('siswa.pelanggaran.index') }}" class="btn-secondary">← Kembali</a>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">🔍</span> Detail Pelanggaran</h3>
    <p>Informasi lengkap tentang pelanggaran Anda, termasuk jenis, kategori, poin yang dikurangi, dan progres penanganan.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Informasi yang Tersedia</h3>
    <ul>
        <li><strong>Detail Pelanggaran</strong> — Jenis, tanggal, poin yang dikurangi</li>
        <li><strong>Status</strong> — Progres penanganan dari pencatatan hingga penyelesaian</li>
        <li><strong>Progres</strong> — Jenis tindakan yang diambil dan statusnya</li>
    </ul>
</div>
@endsection
