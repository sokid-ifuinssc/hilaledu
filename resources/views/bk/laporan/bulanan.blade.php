@extends('layouts.app')
@section('title', 'Laporan Bulanan')
@php $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; @endphp
@section('subtitle', $namaBulan[$bulan] . ' ' . $tahun)

@section('content')
<div class="space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Bulan</label>
            <select name="bulan" class="form-select w-44">
                @for($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>{{ $namaBulan[$i] }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Tahun</label>
            <select name="tahun" class="form-select w-32">
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Filter Kelas</label>
            <select name="kelas_id" class="form-select w-48">
                <option value="">Semua Kelas</option>
                @foreach($kelasList as $k)
                <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary">Tampilkan</button>
    </form>
    <a href="{{ route('bk.laporan.cetak', array_merge(['jenis' => 'bulanan', 'bulan' => $bulan, 'tahun' => $tahun], request('kelas_id') ? ['kelas_id' => request('kelas_id')] : [])) }}" target="_blank" class="btn-secondary">
        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>Cetak Laporan
    </a>
    </div>

    @if(request('kelas_id'))
    <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2 text-sm text-indigo-700 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
        Filter aktif: <strong>{{ $kelasList->firstWhere('id', request('kelas_id'))->nama ?? '-' }}</strong>
        <a href="{{ route('bk.laporan.bulanan', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="ml-2 text-indigo-500 hover:underline text-xs">× Hapus filter</a>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="stat-card"><p class="text-sm text-gray-500">Total Pelanggaran</p><p class="text-2xl font-bold text-red-600">{{ $pelanggarans->count() }}</p></div>
        <div class="stat-card"><p class="text-sm text-gray-500">Total Poin Dikurangi</p><p class="text-2xl font-bold text-orange-600">{{ $pelanggarans->sum('poin') }}</p></div>
        <div class="stat-card"><p class="text-sm text-gray-500">Siswa Terlibat</p><p class="text-2xl font-bold text-indigo-600">{{ $pelanggarans->pluck('siswa_id')->unique()->count() }}</p></div>
    </div>

    <div class="table-container">
        <table>
            <thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Pelanggaran</th><th>Poin</th><th>Status</th><th>Dokumen</th></tr></thead>
            <tbody>
                @forelse($pelanggarans as $p)
                <tr>
                    <td>{{ $p->tanggal_pelanggaran->format('d/m/Y') }}</td>
                    <td class="font-medium">{{ $p->siswa->nama_lengkap }}</td>
                    <td>{{ $p->siswa?->kelas?->nama ?? '-' }}</td>
                    <td>{{ $p->jenisPelanggaran->nama }}</td>
                    
                    <td><span class="text-red-600 font-semibold">-{{ $p->poin }}</span></td>
                    <td><span class="badge {{ $p->status_badge }}">{{ $p->status_label }}</span></td>
                    <td>@include('components.dokumen-progres-links', ['p' => $p])</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-8 text-gray-500">Tidak ada pelanggaran pada bulan ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #ecfdf5; color: #059669;">📊</span> Laporan Bulanan</h3>
    <p>Rekapitulasi pelanggaran siswa per bulan. Pilih bulan, tahun, dan filter kelas untuk melihat data spesifik.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Pilih <strong>bulan</strong> dan <strong>tahun</strong> yang diinginkan</li>
        <li>Gunakan <strong>filter kelas</strong> untuk menampilkan pelanggaran kelas tertentu saja</li>
        <li>Klik <strong>"Tampilkan"</strong> untuk memuat data</li>
        <li>Lihat ringkasan: total pelanggaran, poin dikurangi, dan siswa terlibat</li>
    </ol>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Laporan bulanan membantu evaluasi perilaku siswa per bulan dan membandingkan antar kelas.</p>
</div>
@endsection
