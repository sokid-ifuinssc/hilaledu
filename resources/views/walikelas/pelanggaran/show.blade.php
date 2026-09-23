@extends('layouts.app')
@section('title', 'Detail Pelanggaran')
@section('content')
<div class="space-y-6 max-w-4xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Informasi Pelanggaran</h3>
            <span class="badge text-sm {{ $pelanggaran->status_badge }}">{{ $pelanggaran->status_label }}</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div><p class="text-xs text-gray-500">Siswa</p><p class="text-sm font-medium">{{ $pelanggaran->siswa->nama_lengkap }}</p></div>
            <div><p class="text-xs text-gray-500">Kelas</p><p class="text-sm font-medium">{{ $pelanggaran->siswa?->kelas?->nama ?? '-' }}</p></div>
            <div><p class="text-xs text-gray-500">Tanggal</p><p class="text-sm font-medium">{{ $pelanggaran->tanggal_pelanggaran->format('d M Y') }}</p></div>
            <div><p class="text-xs text-gray-500">Status</p><span class="badge {{ $pelanggaran->status_badge }}">{{ $pelanggaran->status_label }}</span></div>
            <div class="col-span-2"><p class="text-xs text-gray-500">Jenis</p><p class="text-sm font-medium">{{ $pelanggaran->jenisPelanggaran->nama }} ({{ $pelanggaran->jenisPelanggaran->kategori->nama }})</p></div>
            <div><p class="text-xs text-gray-500">Poin</p><p class="text-sm font-bold text-red-600">-{{ $pelanggaran->poin }}</p></div>
            <div><p class="text-xs text-gray-500">Dicatat</p><p class="text-sm">{{ $pelanggaran->pencatat->nama_lengkap ?? '-' }}</p></div>
            <div class="col-span-4"><p class="text-xs text-gray-500">Deskripsi</p><p class="text-sm">{{ $pelanggaran->deskripsi }}</p></div>
        </div>
    </div>

    {{-- Progres Penanganan --}}
    @php $progres = $pelanggaran->progresPelanggaran; @endphp
    @if($progres)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Progres Penanganan</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div><p class="text-xs text-gray-500">Jenis Tindakan</p><span class="badge text-sm {{ $progres->jenis_tindakan_badge }}">{{ $progres->jenis_tindakan_label }}</span></div>
            <div><p class="text-xs text-gray-500">Status Progres</p><span class="badge text-sm {{ $progres->status_badge }}">{{ $progres->status_label }}</span></div>
        </div>
        <a href="{{ route('walikelas.progres.show', $pelanggaran) }}" class="btn-primary text-sm">📋 Lihat Detail Progres →</a>
    </div>
    @endif

    <div class="flex gap-3">
        <a href="{{ route('walikelas.pelanggaran.index') }}" class="btn-secondary">← Kembali</a>
        @if($progres)
        <a href="{{ route('walikelas.progres.show', $pelanggaran) }}" class="btn-primary">📋 Detail Progres</a>
        @endif
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">🔍</span> Detail Pelanggaran Siswa</h3>
    <p>Informasi lengkap pelanggaran beserta progres penanganan. Gunakan menu Progres Data Pelanggaran untuk approve atau isi laporan.</p>
</div>
@endsection
