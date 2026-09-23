@extends('layouts.app')
@section('title', 'Dashboard Kaprog')

@section('content')
<div class="space-y-6">

    {{-- Hero Banner Kaprog --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-purple-900 via-indigo-950 to-slate-950 p-6 sm:p-8 text-white shadow-lg border border-purple-800/40">
        <div class="absolute inset-0 bg-gradient-to-r from-purple-950/95 via-indigo-950/85 to-slate-950/90 pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-400 text-slate-950 flex items-center justify-center text-2xl shadow-lg shrink-0">
                    <i class="bi-diagram-3-fill"></i>
                </div>
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-500/20 text-purple-300 text-xs font-semibold border border-purple-500/30">
                        <i class="bi-award-fill"></i>
                        <span>Ketua Program Keahlian</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Dashboard Kaprog
                    </h1>
                    @if($jurusans->count())
                    <p class="text-purple-300 text-sm font-semibold">
                        Program: {{ $jurusans->pluck('nama')->join(', ') }}
                    </p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-mono font-bold text-purple-300 bg-white/10 px-3 py-2 rounded-xl border border-white/10 shrink-0">
                <i class="bi-clock-fill"></i>
                <span>{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-indigo-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-mortarboard-fill text-indigo-600 text-lg"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Siswa</div>
                <div class="text-2xl font-black text-slate-800 mt-0.5">{{ $stats['total_siswa'] }}</div>
                <div class="text-[10px] text-slate-500">Di Jurusan Ini</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-exclamation-diamond-fill text-amber-600 text-lg"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Pelanggaran</div>
                <div class="text-2xl font-black text-amber-600 mt-0.5">{{ $stats['total_pelanggaran'] }}</div>
                <div class="text-[10px] text-slate-500">Tahun Ini</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-calendar-x-fill text-orange-600 text-lg"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Bulan Ini</div>
                <div class="text-2xl font-black text-orange-600 mt-0.5">{{ $stats['pelanggaran_bulan_ini'] }}</div>
                <div class="text-[10px] text-slate-500">Pelanggaran Baru</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-person-x-fill text-red-600 text-lg"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Siswa Bermasalah</div>
                <div class="text-2xl font-black text-red-600 mt-0.5">{{ $stats['siswa_bermasalah'] }}</div>
                <div class="text-[10px] text-slate-500">Poin &lt; 40</div>
            </div>
        </div>
    </div>

    {{-- Dua Kolom: Siswa Kritis & Pelanggaran Terbaru --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Siswa Poin Terendah --}}
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="bi-person-exclamation text-rose-600"></i>
                <h3 class="font-extrabold text-sm text-slate-900">Siswa Poin Terendah</h3>
            </div>
            <div class="space-y-2.5">
                @forelse($siswaKritis as $s)
                <div class="flex items-center justify-between p-3 rounded-2xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-black shrink-0
                            {{ $s->poin < 40 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $s->poin }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ $s->nama_lengkap }}</p>
                            <p class="text-xs text-slate-500">{{ $s->kelas?->nama ?? '-' }}</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold
                        {{ $s->poin < 40 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $s->status_poin }}
                    </span>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-6">
                    <i class="bi-check-circle-fill text-emerald-400 block text-2xl mb-2"></i>
                    Semua siswa dalam kondisi baik
                </p>
                @endforelse
            </div>
        </div>

        {{-- Pelanggaran Terbaru --}}
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="bi-clock-history text-rose-500"></i>
                <h3 class="font-extrabold text-sm text-slate-900">Pelanggaran Terbaru</h3>
            </div>
            <div class="space-y-2.5">
                @forelse($pelanggaranTerbaru as $p)
                <a href="{{ route('kaprog.pelanggaran.show', $p) }}"
                   class="flex items-center justify-between p-3 rounded-2xl hover:bg-slate-50 transition border border-slate-100 hover:border-slate-200 group">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate group-hover:text-indigo-700">{{ $p->siswa->nama_lengkap }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ $p->jenisPelanggaran->nama }} &bull; {{ $p->tanggal_pelanggaran->format('d/m/Y') }}</p>
                    </div>
                    <span class="badge {{ $p->status_badge }} shrink-0 ml-2">{{ $p->status_label }}</span>
                </a>
                @empty
                <p class="text-sm text-slate-400 text-center py-6">
                    <i class="bi-check-circle-fill text-emerald-400 block text-2xl mb-2"></i>
                    Belum ada pelanggaran
                </p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #faf5ff; color: #7c3aed;">📊</span> Dashboard Kaprog</h3>
    <p>Dashboard ini menampilkan ringkasan pelanggaran siswa di program keahlian Anda: total siswa, total pelanggaran, pelanggaran bulan ini, dan siswa bermasalah.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Membaca Dashboard</h3>
    <ul>
        <li><strong>Total Siswa</strong> — Jumlah siswa aktif di jurusan Anda</li>
        <li><strong>Total Pelanggaran</strong> — Semua pelanggaran siswa di jurusan Anda</li>
        <li><strong>Bulan Ini</strong> — Pelanggaran yang terjadi pada bulan berjalan</li>
        <li><strong>Siswa Bermasalah</strong> — Siswa dengan poin rendah (&lt; 40) yang perlu perhatian</li>
    </ul>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Kaprog dapat memantau kondisi pelanggaran siswa di jurusannya dan segera membuat rekomendasi jika diperlukan.</p>
</div>
@endsection
