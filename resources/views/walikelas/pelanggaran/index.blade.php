@extends('layouts.app')
@section('title', 'Pelanggaran Kelas')
@section('content')
<div class="space-y-5">
    <form method="GET" class="flex gap-3"><select name="status" class="form-select w-44" onchange="this.form.submit()"><option value="">Semua Status</option><option value="dicatat" {{ request('status') == 'dicatat' ? 'selected' : '' }}>Dicatat</option><option value="proses" {{ request('status') == 'proses' ? 'selected' : '' }}>Dalam Proses</option><option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option></select></form>
    <div class="table-container"><table><thead><tr><th>Tanggal</th><th>Siswa</th><th>Pelanggaran</th><th>Poin</th><th>Tindakan</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
    @forelse($pelanggarans as $p)
    <tr><td>{{ $p->tanggal_pelanggaran->format('d/m/Y') }}</td><td class="font-medium">{{ $p->siswa->nama_lengkap }}</td><td>{{ $p->jenisPelanggaran->nama }}</td><td><span class="text-red-600 font-semibold">-{{ $p->poin }}</span></td><td>@if($p->progresPelanggaran)<span class="badge text-xs {{ $p->progresPelanggaran->jenis_tindakan_badge }}">{{ $p->progresPelanggaran->jenis_tindakan_label }}</span>@else<span class="text-gray-400">-</span>@endif</td><td><span class="badge {{ $p->status_badge }}">{{ $p->status_label }}</span></td><td class="flex gap-2"><a href="{{ route('walikelas.pelanggaran.show', $p) }}" class="text-indigo-600 hover:underline text-sm">Detail</a>@if($p->progresPelanggaran)<a href="{{ route('walikelas.progres.show', $p) }}" class="text-emerald-600 hover:underline text-sm">Progres</a>@endif</td></tr>
    @empty<tr><td colspan="7" class="text-center py-8 text-gray-500">Tidak ada pelanggaran</td></tr>@endforelse
    </tbody></table></div><div>{{ $pelanggarans->links() }}</div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #fef2f2; color: #dc2626;">📋</span> Pelanggaran Kelas</h3>
    <p>Daftar pelanggaran siswa di kelas yang Anda ampu. Klik detail untuk melihat informasi lengkap dan progres penanganan.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Lihat daftar pelanggaran siswa di kelas Anda</li>
        <li>Klik <strong>"Detail"</strong> untuk melihat informasi lengkap</li>
        <li>Klik <strong>"Progres"</strong> untuk approve atau isi laporan</li>
    </ol>
</div>
@endsection
