@extends('layouts.app')
@section('title', 'Detail Rekomendasi Kaprog')

@section('content')
@php $prefix = auth()->user()->isWakaKesiswaan() ? 'waka' : 'kepsek'; @endphp
<div class="max-w-3xl space-y-5">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Detail Pelanggaran</h3>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div><span class="text-gray-500">Siswa:</span> <span class="font-medium">{{ $rekomendasiKaprog->pelanggaran->siswa->nama_lengkap }}</span></div>
            <div><span class="text-gray-500">Kelas:</span> <span class="font-medium">{{ $rekomendasiKaprog->pelanggaran->siswa?->kelas?->nama ?? '-' }}</span></div>
            <div><span class="text-gray-500">Pelanggaran:</span> <span class="font-medium">{{ $rekomendasiKaprog->pelanggaran->jenisPelanggaran->nama }}</span></div>
            <div><span class="text-gray-500">Poin:</span> <span class="font-bold text-red-600">-{{ $rekomendasiKaprog->pelanggaran->poin }}</span></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Rekomendasi dari Kaprog</h3>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div><span class="text-gray-500">Kaprog:</span> <span class="font-medium">{{ $rekomendasiKaprog->kaprog->nama_lengkap }}</span></div>
            <div><span class="text-gray-500">Tanggal:</span> {{ $rekomendasiKaprog->tanggal_rekomendasi?->format('d M Y') }}</div>
            <div><span class="text-gray-500">Jenis:</span> <span class="font-medium">{{ $rekomendasiKaprog->jenis_rekomendasi_label }}</span></div>
            <div><span class="text-gray-500">Batas Waktu:</span> {{ $rekomendasiKaprog->batas_waktu?->format('d M Y') ?? '-' }}</div>
            <div class="col-span-2"><span class="text-gray-500">Deskripsi:</span><p class="mt-1 bg-gray-50 rounded p-3">{{ $rekomendasiKaprog->deskripsi_rekomendasi }}</p></div>
        </div>
        <div class="mt-3">
            <span class="text-gray-500 text-sm">Status:</span>
            <span class="badge {{ $rekomendasiKaprog->status_approval_badge }}">{{ $rekomendasiKaprog->status_approval_label }}</span>
        </div>
    </div>

    @if($rekomendasiKaprog->status_approval == 'menunggu')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Tindakan Persetujuan</h3>
        <form method="POST" action="{{ route("{$prefix}.approval.approve-kaprog", $rekomendasiKaprog) }}">
            @csrf
            <div class="space-y-4">
                <div><label class="form-label">Catatan (opsional)</label><textarea name="catatan_approval" rows="3" class="form-textarea" placeholder="Catatan...">{{ old('catatan_approval') }}</textarea></div>
                <div class="flex gap-3">
                    <button type="submit" name="aksi" value="disetujui" class="btn-primary bg-green-600 hover:bg-green-700">✓ Setujui</button>
                    <button type="submit" name="aksi" value="ditolak" class="btn-primary bg-red-600 hover:bg-red-700" onclick="return confirm('Yakin?')">✗ Tolak</button>
                    <a href="{{ route("{$prefix}.approval.index") }}" class="btn-secondary">Kembali</a>
                </div>
            </div>
        </form>
    </div>
    @else
    <a href="{{ route("{$prefix}.approval.index") }}" class="btn-secondary inline-block">← Kembali</a>
    @endif
</div>
@endsection
