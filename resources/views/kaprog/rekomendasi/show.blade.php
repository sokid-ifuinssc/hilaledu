@extends('layouts.app')
@section('title', 'Detail Rekomendasi')
@section('subtitle', 'Rekomendasi Kaprog')

@section('content')
<div class="max-w-3xl space-y-5">
    <div class="stat-card">
        <h3 class="font-semibold text-gray-800 mb-4">Info Pelanggaran</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Siswa:</span> <span class="font-medium">{{ $rekomendasiKaprog->pelanggaran->siswa->nama_lengkap }}</span></div>
            <div><span class="text-gray-500">Kelas:</span> <span class="font-medium">{{ $rekomendasiKaprog->pelanggaran->siswa?->kelas?->nama ?? '-' }}</span></div>
            <div><span class="text-gray-500">Jurusan:</span> <span class="font-medium">{{ $rekomendasiKaprog->pelanggaran->siswa?->kelas?->jurusan?->nama ?? '-' }}</span></div>
            <div><span class="text-gray-500">Tanggal:</span> <span class="font-medium">{{ $rekomendasiKaprog->pelanggaran->tanggal_pelanggaran->format('d M Y') }}</span></div>
            <div><span class="text-gray-500">Poin:</span> <span class="font-medium text-red-600">-{{ $rekomendasiKaprog->pelanggaran->poin }}</span></div>
            <div><span class="text-gray-500">Status:</span> <span class="badge {{ $rekomendasiKaprog->pelanggaran->status_badge }}">{{ $rekomendasiKaprog->pelanggaran->status_label }}</span></div>
        </div>
    </div>

    <div class="stat-card">
        <h3 class="font-semibold text-gray-800 mb-4">Detail Rekomendasi</h3>
        <div class="space-y-3 text-sm">
            <div><span class="text-gray-500">Jenis:</span> <span class="badge bg-teal-100 text-teal-700 ml-1">{{ $rekomendasiKaprog->jenis_rekomendasi_label ?? ucfirst(str_replace('_', ' ', $rekomendasiKaprog->jenis_rekomendasi)) }}</span></div>
            <div><span class="text-gray-500">Deskripsi:</span> <p class="mt-1 text-gray-700">{{ $rekomendasiKaprog->deskripsi_rekomendasi }}</p></div>
            <div><span class="text-gray-500">Kaprog:</span> <span class="font-medium">{{ $rekomendasiKaprog->kaprog->nama_lengkap ?? '-' }}</span></div>
            @if($rekomendasiKaprog->batas_waktu)
            <div><span class="text-gray-500">Batas Waktu:</span> <span class="font-medium">{{ $rekomendasiKaprog->batas_waktu->format('d M Y') }}</span></div>
            @endif
        </div>
    </div>

    @if($rekomendasiKaprog->tindakLanjut)
    <div class="stat-card border-green-200 bg-green-50/50">
        <h3 class="font-semibold text-green-800 mb-3">Tindak Lanjut</h3>
        <div class="space-y-2 text-sm">
            <div><span class="text-gray-500">Wali Kelas:</span> <span class="font-medium">{{ $rekomendasiKaprog->tindakLanjut->waliKelas->nama_lengkap ?? '-' }}</span></div>
            <div><span class="text-gray-500">Tindakan:</span> <p class="text-gray-700">{{ $rekomendasiKaprog->tindakLanjut->tindakan_yang_dilakukan }}</p></div>
            <div><span class="text-gray-500">Status:</span> <span class="badge {{ $rekomendasiKaprog->tindakLanjut->status_badge }}">{{ $rekomendasiKaprog->tindakLanjut->status_label }}</span></div>
            @if($rekomendasiKaprog->tindakLanjut->hasil)
            <div><span class="text-gray-500">Hasil:</span> <p class="text-gray-700">{{ $rekomendasiKaprog->tindakLanjut->hasil }}</p></div>
            @endif
        </div>
    </div>
    @endif

    <a href="{{ route('kaprog.rekomendasi.index') }}" class="btn-secondary">← Kembali</a>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">🔍</span> Detail Rekomendasi Kaprog</h3>
    <p>Detail rekomendasi yang telah Anda buat, termasuk pelanggaran terkait dan status tindak lanjut.</p>
</div>
@endsection

