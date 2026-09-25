@extends('layouts.app')
@php $isWaka = auth()->user()->isWakaKesiswaan(); @endphp
@section('title', 'Monitoring Seluruh Pelanggaran')
@section('content')
<div class="space-y-5">
    <form method="GET" class="flex gap-3 flex-wrap">
        <input type="text" name="search" placeholder="Cari siswa..." value="{{ request('search') }}" class="form-input w-56">
        <select name="status" class="form-select w-44" onchange="this.form.submit()"><option value="">Semua Status</option><option value="dicatat" {{ request('status') == 'dicatat' ? 'selected' : '' }}>Dicatat</option><option value="direkomendasikan" {{ request('status') == 'direkomendasikan' ? 'selected' : '' }}>Direkomendasikan</option><option value="ditindaklanjuti" {{ request('status') == 'ditindaklanjuti' ? 'selected' : '' }}>Ditindaklanjuti</option><option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option></select>
        <button type="submit" class="btn-secondary">Filter</button>
    </form>
    <div class="table-container"><table><thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Jurusan</th><th>Pelanggaran</th><th>Poin</th><th>Status</th></tr></thead><tbody>
    @forelse($pelanggarans as $p)
    <tr><td>{{ $p->tanggal_pelanggaran->format('d/m/Y') }}</td><td class="font-medium">{{ $p->siswa->nama_lengkap }}</td><td>{{ $p->siswa?->kelas?->nama ?? '-' }}</td><td>{{ $p->siswa?->kelas?->jurusan->kode ?? '-' }}</td><td>{{ $p->jenisPelanggaran->nama }}</td><td><span class="text-red-600 font-semibold">-{{ $p->poin }}</span></td><td><span class="badge {{ $p->status_badge }}">{{ $p->status_label }}</span></td></tr>
    @empty<tr><td colspan="8" class="text-center py-8 text-gray-500">Tidak ada data</td></tr>@endforelse
    </tbody></table></div><div>{{ $pelanggarans->links() }}</div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #ecfdf5; color: #059669;">👁️</span> Monitoring Keseluruhan</h3>
    <p>Halaman ini menampilkan seluruh data pelanggaran dan proses penanganannya dari semua kelas dan jurusan.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Lihat daftar seluruh pelanggaran dari semua kelas dan jurusan</li>
        <li>Filter berdasarkan <strong>status, kelas, atau jurusan</strong></li>
        <li>Pantau <strong>progres penanganan</strong> setiap pelanggaran</li>
    </ol>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Kepala Sekolah dapat memastikan semua pelanggaran ditangani dengan baik oleh tim BK, Kaprog, dan Wali Kelas.</p>
</div>
@endsection

