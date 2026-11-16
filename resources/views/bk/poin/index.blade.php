@extends('layouts.app')

@section('title', 'Akumulasi Poin Kedisiplinan Siswa - HilalEdu')
@section('page-title', 'Akumulasi Poin Siswa')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold border border-amber-200 mb-2">
                <i class="bi bi-award"></i>
                <span>Kedisiplinan & Tata Tertib</span>
            </div>
            <h2 class="text-xl font-bold text-slate-800">Akumulasi Poin Kedisiplinan Siswa</h2>
            <p class="text-sm text-slate-500 mt-0.5">Pantau skor kedisiplinan siswa. Siswa dengan poin di bawah ambang batas memerlukan bimbingan khusus BK & Wali Kelas.</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">
                {{ $siswas->total() }} Siswa Aktif
            </span>
            <a href="{{ route('bk.pelanggaran.create') }}" class="btn-primary text-sm inline-flex items-center gap-1.5">
                <i class="bi bi-plus-circle"></i>
                <span>Catat Pelanggaran</span>
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('bk.poin.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1">Cari Siswa / NIS</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-input w-full text-sm" placeholder="Ketik nama lengkap atau NIS siswa...">
            </div>
            <div class="w-full sm:w-64">
                <label class="block text-xs font-semibold text-slate-500 mb-1">Filter Kelas</label>
                <select name="kelas_id" class="form-select w-full text-sm">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama }} {{ $k->jurusan?->singkatan ? '(' . $k->jurusan->singkatan . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-sm inline-flex items-center gap-1.5">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                @if(request()->anyFilled(['search', 'kelas_id']))
                    <a href="{{ route('bk.poin.index') }}" class="btn-secondary text-sm inline-flex items-center gap-1">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Poin --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider w-14">No</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">NIS</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Lengkap Siswa</th>
                        <th class="py-3 px-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kelas & Jurusan</th>
                        <th class="py-3 px-4 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Sisa Poin</th>
                        <th class="py-3 px-4 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Kedisiplinan</th>
                        <th class="py-3 px-4 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($siswas as $index => $item)
                        @php
                            $poin = (int) ($item->poin ?? 100);
                            $badgeClass = $poin >= 80
                                ? 'bg-emerald-100 text-emerald-700 border border-emerald-200'
                                : ($poin >= 50
                                    ? 'bg-amber-100 text-amber-800 border border-amber-200'
                                    : 'bg-rose-100 text-rose-700 border border-rose-200');
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 text-center text-slate-500 font-mono text-xs">{{ $siswas->firstItem() + $index }}</td>
                            <td class="py-3 px-4 font-mono text-xs text-slate-600">{{ $item->nis ?: '-' }}</td>
                            <td class="py-3 px-4">
                                <p class="font-semibold text-slate-800">{{ $item->nama_lengkap }}</p>
                                <p class="text-xs text-slate-400">{{ $item->jenis_kelamin === 'L' ? 'Laki-laki' : ($item->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</p>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-medium">
                                    {{ $item->kelas?->nama ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold {{ $badgeClass }}">
                                    {{ $poin }} / 100
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($poin >= 80)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700">
                                        <i class="bi bi-check-circle-fill"></i> Sangat Baik
                                    </span>
                                @elseif($poin >= 50)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Perlu Perhatian
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-600">
                                        <i class="bi bi-x-octagon-fill"></i> Panggilan Ortu
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('bk.pelanggaran.create', ['siswa_id' => $item->id]) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors">
                                    <i class="bi bi-pencil-plus"></i> Tindak
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-slate-400 text-sm">
                                Tidak ada data siswa yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($siswas->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $siswas->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
