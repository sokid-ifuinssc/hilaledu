@extends('layouts.app')
@section('title', 'Detail Rekomendasi BK')

@section('content')
@php $prefix = auth()->user()->isWakaKesiswaan() ? 'waka' : 'kepsek'; @endphp
<div class="max-w-3xl space-y-5">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Detail Pelanggaran</h3>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div><span class="text-gray-500">Siswa:</span> <span class="font-medium">{{ $rekomendasi->pelanggaran->siswa->nama_lengkap }}</span></div>
            <div><span class="text-gray-500">Kelas:</span> <span class="font-medium">{{ $rekomendasi->pelanggaran->siswa?->kelas?->nama ?? '-' }}</span></div>
            <div><span class="text-gray-500">Pelanggaran:</span> <span class="font-medium">{{ $rekomendasi->pelanggaran->jenisPelanggaran->nama }}</span></div>
            <div><span class="text-gray-500">Poin:</span> <span class="font-bold text-red-600">-{{ $rekomendasi->pelanggaran->poin }}</span></div>
            <div><span class="text-gray-500">Tanggal:</span> {{ $rekomendasi->pelanggaran->tanggal_pelanggaran->format('d M Y') }}</div>
            <div><span class="text-gray-500">Kategori:</span> {{ $rekomendasi->pelanggaran->jenisPelanggaran->kategori->nama }}</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Rekomendasi dari Guru BK</h3>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div><span class="text-gray-500">Guru BK:</span> <span class="font-medium">{{ $rekomendasi->guruBK->nama_lengkap }}</span></div>
            <div><span class="text-gray-500">Tanggal:</span> {{ $rekomendasi->tanggal_rekomendasi?->format('d M Y') }}</div>
            <div><span class="text-gray-500">Jenis:</span> <span class="font-medium">{{ $rekomendasi->jenis_rekomendasi_label }}</span></div>
            <div><span class="text-gray-500">Batas Waktu:</span> {{ $rekomendasi->batas_waktu?->format('d M Y') ?? '-' }}</div>
            <div class="col-span-2"><span class="text-gray-500">Deskripsi:</span><p class="mt-1 bg-gray-50 rounded p-3">{{ $rekomendasi->deskripsi_rekomendasi }}</p></div>
        </div>
        <div class="mt-3">
            <span class="text-gray-500 text-sm">Status Approval:</span>
            <span class="badge {{ $rekomendasi->status_approval_badge }}">{{ $rekomendasi->status_approval_label }}</span>
            @if($rekomendasi->approver)
            <span class="text-xs text-gray-400 ml-2">oleh {{ $rekomendasi->approver->nama_lengkap }} ({{ $rekomendasi->approved_at?->format('d/m/Y H:i') }})</span>
            @endif
        </div>
        @if($rekomendasi->catatan_approval)
        <div class="mt-2 text-sm"><span class="text-gray-500">Catatan:</span> {{ $rekomendasi->catatan_approval }}</div>
        @endif
    </div>

    @if($rekomendasi->status_approval == 'menunggu')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Tindakan Persetujuan</h3>
        <form method="POST" action="{{ route("{$prefix}.approval.approve-bk", $rekomendasi) }}">
            @csrf
            <div class="space-y-4">
                <div><label class="form-label">Catatan (opsional)</label><textarea name="catatan_approval" rows="3" class="form-textarea" placeholder="Catatan persetujuan...">{{ old('catatan_approval') }}</textarea></div>
                <div class="flex gap-3">
                    <button type="submit" name="aksi" value="disetujui" class="btn-primary bg-green-600 hover:bg-green-700">✓ Setujui</button>
                    <button type="submit" name="aksi" value="ditolak" class="btn-primary bg-red-600 hover:bg-red-700" onclick="return confirm('Yakin tolak rekomendasi ini?')">✗ Tolak</button>
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
