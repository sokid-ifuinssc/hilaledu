@extends('layouts.app')
@section('title', 'Rekomendasi')
@section('subtitle', 'Daftar rekomendasi yang telah dibuat')

@section('content')
@php $prefix = auth()->user()->isWakaKesiswaan() ? 'waka' : 'bk'; @endphp
<div class="space-y-5">
    <div class="flex items-center gap-3">
        <form method="GET" class="flex items-center gap-3">
            <select name="status" class="form-select w-48" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="dicatat" {{ request('status') == 'dicatat' ? 'selected' : '' }}>Dicatat</option>
                <option value="direkomendasikan" {{ request('status') == 'direkomendasikan' ? 'selected' : '' }}>Direkomendasikan</option>
                <option value="ditindaklanjuti" {{ request('status') == 'ditindaklanjuti' ? 'selected' : '' }}>Ditindaklanjuti</option>
                <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Jenis Rekomendasi</th><th>Status</th><th>Tindak Lanjut</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($rekomendasis as $r)
                <tr>
                    <td>{{ $r->tanggal_rekomendasi?->format('d/m/Y') ?? $r->created_at->format('d/m/Y') }}</td>
                    <td class="font-medium">{{ $r->pelanggaran->siswa->nama_lengkap ?? '-' }}</td>
                    <td>{{ $r->pelanggaran->siswa?->kelas?->nama ?? '-' }}</td>
                    <td><span class="badge bg-indigo-100 text-indigo-700">{{ $r->jenis_rekomendasi_label ?? ucfirst(str_replace('_', ' ', $r->jenis_rekomendasi)) }}</span></td>
                    <td><span class="badge {{ $r->pelanggaran->status_badge }}">{{ $r->pelanggaran->status_label }}</span></td>
                    <td>
                        @if($r->tindakLanjut)
                            <span class="badge bg-green-100 text-green-700">Sudah</span>
                        @else
                            <span class="badge bg-gray-100 text-gray-500">Belum</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route("{$prefix}.rekomendasi.show", $r) }}" class="text-indigo-600 hover:underline text-sm">Detail</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-8 text-gray-500">Belum ada rekomendasi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $rekomendasis->links() }}</div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">📋</span> Daftar Rekomendasi</h3>
    <p>Halaman ini menampilkan semua rekomendasi tindak lanjut yang telah dibuat oleh Guru BK untuk pelanggaran siswa.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Klik <strong>"Buat Rekomendasi"</strong> untuk membuat rekomendasi tindak lanjut baru</li>
        <li>Pilih <strong>pelanggaran</strong> yang akan diberi rekomendasi</li>
        <li>Lihat <strong>status</strong> setiap rekomendasi (Menunggu/Ditindaklanjuti/Selesai)</li>
        <li>Klik <strong>"Detail"</strong> untuk melihat informasi lengkap rekomendasi</li>
    </ol>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Rekomendasi tindak lanjut tersampaikan kepada Wali Kelas melalui notifikasi, sehingga proses penanganan dapat segera dilaksanakan.</p>
</div>
@endsection

