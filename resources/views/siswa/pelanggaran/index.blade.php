@extends('layouts.app')
@section('title', 'Pelanggaran Saya')
@section('content')
<div class="space-y-5">
    @if($siswa)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
        <div class="w-14 h-14 rounded-full flex items-center justify-center text-xl font-bold {{ $siswa->poin >= 70 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $siswa->poin }}</div>
        <div><p class="font-semibold text-gray-800">{{ $siswa->nama_lengkap }}</p><p class="text-sm text-gray-500">Sisa Poin: {{ $siswa->poin }}/100 | Status: {{ $siswa->status_poin }}</p></div>
    </div>
    <div class="table-container"><table><thead><tr><th>Tanggal</th><th>Pelanggaran</th><th>Kategori</th><th>Poin</th><th>Tindakan</th><th>Status</th></tr></thead><tbody>
    @forelse($pelanggarans as $p)
    <tr><td>{{ $p->tanggal_pelanggaran->format('d/m/Y') }}</td><td class="font-medium">{{ $p->jenisPelanggaran->nama }}</td><td><span class="badge" style="background-color: {{ $p->jenisPelanggaran->kategori->warna }}20; color: {{ $p->jenisPelanggaran->kategori->warna }}">{{ $p->jenisPelanggaran->kategori->nama }}</span></td><td><span class="text-red-600 font-semibold">-{{ $p->poin }}</span></td><td>@if($p->progresPelanggaran)<span class="badge text-xs {{ $p->progresPelanggaran->jenis_tindakan_badge }}">{{ $p->progresPelanggaran->jenis_tindakan_label }}</span>@else<span class="text-gray-400 text-xs">-</span>@endif</td><td><span class="badge {{ $p->status_badge }}">{{ $p->status_label }}</span></td></tr>
    @empty<tr><td colspan="6" class="text-center py-8 text-gray-500">🎉 Tidak ada pelanggaran!</td></tr>@endforelse
    </tbody></table></div><div>{{ $pelanggarans->links() }}</div>
    @endif
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #fef2f2; color: #dc2626;">📋</span> Pelanggaran Saya</h3>
    <p>Halaman ini menampilkan seluruh riwayat pelanggaran yang pernah Anda lakukan beserta status penanganannya.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Lihat daftar pelanggaran beserta <strong>tanggal, jenis, poin,</strong> dan <strong>status</strong></li>
        <li>Pantau status: <strong>Dicatat → Dalam Proses → Selesai</strong></li>
    </ol>
</div>
@endsection
