@extends('layouts.app')
@section('title', 'Dashboard Prakerin')

@section('content')
<div class="space-y-5">

    <x-dashboard-hero
        icon="bi-briefcase-fill"
        badge="Platform Terpadu Prakerin &amp; Hubin"
        title="Praktik Kerja Lapangan (Prakerin)"
        description="Kelola mitra industri (DU/DI), instruktur lapangan, periode gelombang, penempatan siswa, serta jurnal kegiatan secara terpadu." />

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-3 hover:shadow-sm transition">
            <div class="w-11 h-11 bg-sky-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="bi-buildings text-sky-600 text-xl"></i>
            </div>
            <div class="min-w-0">
                <div class="text-2xl font-black text-slate-800">{{ $totalDudi }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Mitra DU/DI</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-3 hover:shadow-sm transition">
            <div class="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="bi-people text-emerald-600 text-xl"></i>
            </div>
            <div class="min-w-0">
                <div class="text-2xl font-black text-slate-800">{{ $totalPenempatan }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Penempatan</div>
                <div class="text-[9px] text-emerald-600 font-semibold">{{ $penempatanAktif }} Aktif</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-3 hover:shadow-sm transition">
            <div class="w-11 h-11 bg-amber-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="bi-person-badge text-amber-600 text-xl"></i>
            </div>
            <div class="min-w-0">
                <div class="text-2xl font-black text-slate-800">{{ $totalPembimbing }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Pembimbing</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-3 hover:shadow-sm transition">
            <div class="w-11 h-11 bg-purple-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="bi-calendar-range text-purple-600 text-xl"></i>
            </div>
            <div class="min-w-0">
                <div class="text-2xl font-black text-slate-800">{{ $totalPeriode }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Periode</div>
            </div>
        </div>
    </div>

    {{-- Aksi Cepat --}}
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs">
        <h3 class="font-extrabold text-sm text-slate-900 mb-4 flex items-center gap-2">
            <i class="bi-lightning-charge-fill text-amber-500"></i>
            Aksi Cepat &amp; Navigasi Operasional
        </h3>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('prakerin.penempatan.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition">
                <i class="bi-person-plus-fill"></i> Plotting Penempatan Siswa
            </a>
            <a href="{{ route('prakerin.dudi.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl transition">
                <i class="bi-building-add"></i> Tambah Mitra DU/DI
            </a>
            <a href="{{ route('prakerin.pembimbing-dudi.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition">
                <i class="bi-person-plus-fill"></i> Tambah Pembimbing
            </a>
            <a href="{{ route('prakerin.periode.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition">
                <i class="bi-calendar-plus"></i> Buat Periode Gelombang
            </a>
            <a href="{{ route('prakerin.laporan.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 transition">
                <i class="bi-file-earmark-bar-graph"></i> Rekapitulasi &amp; Nilai
            </a>
        </div>
    </div>

    {{-- Tabel Penempatan Terkini --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                    <i class="bi-clock-history text-emerald-600"></i>
                    Penempatan Siswa Terkini
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar siswa yang baru saja ditempatkan ke tempat praktik kerja industri.</p>
            </div>
            <a href="{{ route('prakerin.penempatan.index') }}"
               class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                Lihat Semua <i class="bi-arrow-right"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Mitra DU/DI</th>
                        <th>Guru Pembimbing</th>
                        <th>Instruktur DU/DI</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPenempatan as $item)
                    <tr>
                        <td>
                            <div class="font-semibold text-sm text-slate-800">{{ $item->siswa->nama_lengkap ?? '-' }}</div>
                            <div class="text-xs text-slate-500">NIS: {{ $item->siswa->nis ?? '-' }}</div>
                        </td>
                        <td><span class="badge bg-slate-100 text-slate-700">{{ $item->siswa?->kelas?->nama_lengkap ?? '-' }}</span></td>
                        <td class="text-sm font-medium text-sky-700">{{ $item->dudi->nama ?? '-' }}</td>
                        <td class="text-sm text-slate-500">{{ $item->guru?->nama_lengkap ?? '-' }}</td>
                        <td class="text-sm text-slate-500">{{ $item->pembimbingDudi->nama ?? '-' }}</td>
                        <td><span class="badge badge-{{ $item->status_color }}">{{ $item->status_label }}</span></td>
                        <td class="text-center">
                            <a href="{{ route('prakerin.penempatan.show', $item->id) }}"
                               class="text-xs font-semibold text-indigo-600 hover:underline">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-400">
                            <i class="bi-inbox text-2xl block mb-2"></i>
                            Belum ada penempatan. Klik "Plotting Penempatan Siswa" untuk memulai.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
