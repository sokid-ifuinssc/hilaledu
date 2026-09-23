@extends('layouts.app')
@section('title', 'Detail Rekomendasi')
@section('subtitle', 'Rekomendasi BK')

@section('content')
@php $prefix = auth()->user()->isWakaKesiswaan() ? 'waka' : 'bk'; @endphp
<div class="max-w-3xl space-y-5">
    <div class="stat-card">
        <h3 class="font-semibold text-gray-800 mb-4">Info Pelanggaran</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Siswa:</span> <span class="font-medium">{{ $rekomendasi->pelanggaran->siswa->nama_lengkap }}</span></div>
            <div><span class="text-gray-500">Kelas:</span> <span class="font-medium">{{ $rekomendasi->pelanggaran->siswa?->kelas?->nama ?? '-' }}</span></div>
            <div><span class="text-gray-500">Pelanggaran:</span> <span class="font-medium">{{ $rekomendasi->pelanggaran->jenisPelanggaran->nama ?? '-' }}</span></div>
            <div><span class="text-gray-500">Tanggal:</span> <span class="font-medium">{{ $rekomendasi->pelanggaran->tanggal_pelanggaran->format('d M Y') }}</span></div>
            <div><span class="text-gray-500">Poin:</span> <span class="font-medium text-red-600">-{{ $rekomendasi->pelanggaran->poin }}</span></div>
            <div><span class="text-gray-500">Status:</span> <span class="badge {{ $rekomendasi->pelanggaran->status_badge }}">{{ $rekomendasi->pelanggaran->status_label }}</span></div>
        </div>
    </div>

    <div class="stat-card">
        <h3 class="font-semibold text-gray-800 mb-4">Detail Rekomendasi</h3>
        <div class="space-y-3 text-sm">
            <div><span class="text-gray-500">Jenis:</span> <span class="badge bg-indigo-100 text-indigo-700 ml-1">{{ $rekomendasi->jenis_rekomendasi_label ?? ucfirst(str_replace('_', ' ', $rekomendasi->jenis_rekomendasi)) }}</span></div>
            <div><span class="text-gray-500">Deskripsi:</span> <p class="mt-1 text-gray-700">{{ $rekomendasi->deskripsi_rekomendasi }}</p></div>
            <div><span class="text-gray-500">Guru BK:</span> <span class="font-medium">{{ $rekomendasi->guruBK->nama_lengkap ?? '-' }}</span></div>
            @if($rekomendasi->batas_waktu)
            <div><span class="text-gray-500">Batas Waktu:</span> <span class="font-medium">{{ $rekomendasi->batas_waktu->format('d M Y') }}</span></div>
            @endif
        </div>
    </div>

    @if($rekomendasi->tindakLanjut)
    <div class="stat-card border-green-200 bg-green-50/50">
        <h3 class="font-semibold text-green-800 mb-3">Tindak Lanjut</h3>
        <div class="space-y-2 text-sm">
            <div><span class="text-gray-500">Wali Kelas:</span> <span class="font-medium">{{ $rekomendasi->tindakLanjut->waliKelas->nama_lengkap ?? '-' }}</span></div>
            <div><span class="text-gray-500">Tindakan:</span> <p class="text-gray-700">{{ $rekomendasi->tindakLanjut->tindakan_yang_dilakukan }}</p></div>
            <div><span class="text-gray-500">Status:</span> <span class="badge {{ $rekomendasi->tindakLanjut->status_badge }}">{{ $rekomendasi->tindakLanjut->status_label }}</span></div>
            @if($rekomendasi->tindakLanjut->hasil)
            <div><span class="text-gray-500">Hasil:</span> <p class="text-gray-700">{{ $rekomendasi->tindakLanjut->hasil }}</p></div>
            @endif
        </div>
    </div>
    @endif

    <a href="{{ route("{$prefix}.rekomendasi.index") }}" class="btn-secondary">← Kembali</a>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">🔍</span> Detail Rekomendasi</h3>
    <p>Halaman ini menampilkan detail rekomendasi tindak lanjut, termasuk pelanggaran terkait, isi rekomendasi, dan status pelaksanaan.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Informasi yang Tersedia</h3>
    <ul>
        <li><strong>Data Pelanggaran</strong> — Detail pelanggaran yang direkomendasikan</li>
        <li><strong>Isi Rekomendasi</strong> — Tindakan yang direkomendasikan</li>
        <li><strong>Status</strong> — Apakah sudah ditindaklanjuti oleh Wali Kelas</li>
    </ul>
</div>
@endsection

