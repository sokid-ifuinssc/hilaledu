@extends('layouts.app')
@section('title', 'Dashboard Wali Kelas')

@section('content')
<div class="space-y-5">

    {{-- Hero Banner Wali Kelas --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-900 via-indigo-950 to-slate-950 p-6 sm:p-8 text-white shadow-lg border border-blue-800/40">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-950/95 via-indigo-950/85 to-slate-950/90 pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-400 text-slate-950 flex items-center justify-center text-2xl shadow-lg shrink-0">
                    <i class="bi-people-fill"></i>
                </div>
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-xs font-semibold border border-blue-500/30">
                        <i class="bi-person-check-fill"></i>
                        <span>Wali Kelas — Monitoring Siswa</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h1>
                    @if($kelasSaya->count())
                    <div class="flex flex-wrap gap-2 mt-1">
                        @foreach($kelasSaya as $k)
                        <span class="px-2.5 py-1 rounded-lg bg-white/15 text-white text-xs font-semibold border border-white/20">
                            {{ $k->nama }} — {{ $k->jurusan?->nama ?? '' }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-mono font-bold text-blue-300 bg-white/10 px-3 py-2 rounded-xl border border-white/10 shrink-0">
                <i class="bi-clock-fill"></i>
                <span>{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Notifikasi Perlu Aksi --}}
    @if($stats['perlu_aksi'] > 0)
    <div class="flex items-center gap-4 p-4 bg-amber-50 border border-amber-200 rounded-2xl">
        <div class="w-10 h-10 bg-amber-400 rounded-xl flex items-center justify-center text-slate-950 text-lg shrink-0">
            <i class="bi-bell-fill"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-amber-800">
                Ada <strong>{{ $stats['perlu_aksi'] }}</strong> progres pelanggaran yang membutuhkan aksi Anda.
            </p>
            <p class="text-xs text-amber-600 mt-0.5">Segera approve atau isi laporan tindak lanjut.</p>
        </div>
        <a href="{{ route('walikelas.progres.index') }}"
           class="shrink-0 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-black rounded-xl transition">
            Lihat Progres →
        </a>
    </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 text-center hover:shadow-sm transition">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Siswa</div>
            <div class="text-2xl font-black text-indigo-700 mt-1">{{ $stats['total_siswa'] }}</div>
            <div class="text-[10px] text-slate-500 font-medium">Di Kelas Anda</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 text-center hover:shadow-sm transition">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Pelanggaran</div>
            <div class="text-2xl font-black text-red-600 mt-1">{{ $stats['total_pelanggaran'] }}</div>
            <div class="text-[10px] text-slate-500 font-medium">Tahun Ini</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 text-center hover:shadow-sm transition">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Perlu Aksi</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $stats['perlu_aksi'] }}</div>
            <div class="text-[10px] text-slate-500 font-medium">Persetujuan/Laporan</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 text-center hover:shadow-sm transition">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Siswa Bermasalah</div>
            <div class="text-2xl font-black text-orange-600 mt-1">{{ $stats['siswa_bermasalah'] }}</div>
            <div class="text-[10px] text-slate-500 font-medium">Poin Rendah</div>
        </div>
    </div>

    {{-- Pelanggaran Terbaru --}}
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
            <i class="bi-clock-history text-rose-500"></i>
            <h3 class="font-extrabold text-sm text-slate-900">Pelanggaran Terbaru di Kelas Anda</h3>
        </div>
        <div class="space-y-2.5">
            @forelse($pelanggaranTerbaru as $p)
            <div class="flex items-center gap-3 p-3 rounded-2xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center shrink-0">
                    <span class="text-red-600 font-black text-xs">-{{ $p->poin }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $p->siswa->nama_lengkap }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ $p->jenisPelanggaran->nama }} &bull; {{ $p->tanggal_pelanggaran?->format('d M Y') }}</p>
                </div>
                @if($p->progresPelanggaran)
                <span class="shrink-0 badge text-xs {{ $p->progresPelanggaran->jenis_tindakan_badge }}">
                    {{ $p->progresPelanggaran->jenis_tindakan_label }}
                </span>
                @endif
            </div>
            @empty
            <p class="text-sm text-slate-400 text-center py-8">
                <i class="bi-check-circle-fill text-2xl text-emerald-400 block mb-2"></i>
                Belum ada pelanggaran di kelas Anda
            </p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #dbeafe; color: #2563eb;">📊</span> Dashboard Wali Kelas</h3>
    <p>Dashboard ini menampilkan ringkasan pelanggaran siswa di kelas yang Anda ampu.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ul>
        <li><strong>Total Siswa</strong> — Jumlah siswa di kelas Anda</li>
        <li><strong>Total Pelanggaran</strong> — Pelanggaran siswa di kelas Anda</li>
        <li><strong>Perlu Aksi Anda</strong> — Progres yang membutuhkan approval atau laporan dari Anda</li>
        <li><strong>Siswa Bermasalah</strong> — Siswa dengan poin rendah</li>
    </ul>
</div>
@endsection
