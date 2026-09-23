@extends('layouts.app')
@section('title', 'Data Pelanggaran')
@section('subtitle', 'Kelola pelanggaran siswa')

@section('content')
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex items-center gap-3 flex-wrap">
            <input type="text" name="search" placeholder="Cari siswa..." value="{{ request('search') }}" class="form-input w-56">
            <select name="tahun_ajaran_id" class="form-select w-48" onchange="this.form.submit()">
                <option value="semua" {{ $filterTahun == 'semua' ? 'selected' : '' }}>Semua Tahun Ajaran</option>
                @foreach($tahunAjarans as $ta)
                <option value="{{ $ta->id }}" {{ $filterTahun == $ta->id ? 'selected' : '' }}>{{ $ta->nama }} - {{ $ta->semester }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select w-44" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="dicatat" {{ request('status') == 'dicatat' ? 'selected' : '' }}>Dicatat</option>
                <option value="proses" {{ request('status') == 'proses' ? 'selected' : '' }}>Dalam Proses</option>
                <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
            <select name="kelas_id" class="form-select w-44" onchange="this.form.submit()">
                <option value="">Semua Kelas</option>
                @foreach($kelasList as $k)
                <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
        <a href="{{ route('bk.pelanggaran.create') }}" class="btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Input Pelanggaran
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Pelanggaran</th><th>Kategori</th><th>Poin</th><th>Tindakan</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($pelanggarans as $p)
                <tr>
                    <td>{{ $p->tanggal_pelanggaran->format('d/m/Y') }}</td>
                    <td class="font-medium">{{ $p->siswa->nama_lengkap }}</td>
                    <td>{{ $p->siswa?->kelas?->nama ?? '-' }}</td>
                    <td>{{ $p->jenisPelanggaran->nama }}</td>
                    <td><span class="badge" style="background-color: {{ $p->jenisPelanggaran->kategori->warna }}20; color: {{ $p->jenisPelanggaran->kategori->warna }}">{{ $p->jenisPelanggaran->kategori->nama }}</span></td>
                    <td><span class="text-red-600 font-semibold">-{{ $p->poin }}</span></td>
                    <td>
                        @if($p->progresPelanggaran)
                        <span class="badge text-xs {{ $p->progresPelanggaran->jenis_tindakan_badge }}">{{ $p->progresPelanggaran->jenis_tindakan_label }}</span>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td><span class="badge {{ $p->status_badge }}">{{ $p->status_label }}</span></td>
                    <td class="flex gap-2">
                        <a href="{{ route('bk.pelanggaran.show', $p) }}" class="text-indigo-600 hover:underline text-sm">Detail</a>
                        @if($p->progresPelanggaran)
                        <a href="{{ route('bk.progres.show', $p) }}" class="text-emerald-600 hover:underline text-sm">Progres</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-8 text-gray-500">Belum ada pelanggaran</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $pelanggarans->links() }}</div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #fef2f2; color: #dc2626;">📋</span> Data Pelanggaran</h3>
    <p>Halaman ini menampilkan seluruh pelanggaran siswa yang telah dicatat. Anda bisa menambah, melihat detail, dan mengelola setiap pelanggaran.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Klik <strong>"Input Pelanggaran"</strong> untuk mencatat pelanggaran baru</li>
        <li>Gunakan <strong>pencarian</strong> untuk menemukan pelanggaran berdasarkan nama siswa</li>
        <li>Klik <strong>"Detail"</strong> untuk melihat informasi lengkap pelanggaran</li>
        <li>Klik <strong>"Progres"</strong> untuk melihat progres penanganan pelanggaran</li>
        <li>Lihat <strong>status</strong> setiap pelanggaran (Dicatat, Dalam Proses, Selesai)</li>
    </ol>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Semua pelanggaran tercatat dengan lengkap dan dapat dipantau progresnya melalui menu Progres Data Pelanggaran.</p>
</div>
@endsection
