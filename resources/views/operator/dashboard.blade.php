@extends('layouts.app')

@section('title', 'Dashboard Operator')

@section('content')
<div class="space-y-6">

    {{-- Hero Banner Operator --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-800 via-slate-900 to-slate-950 p-6 sm:p-8 text-white shadow-lg border border-slate-700/40">
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-900/85 to-slate-950/90 pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-500 text-white flex items-center justify-center text-2xl shadow-lg shrink-0">
                    <i class="bi-pc-display-horizontal"></i>
                </div>
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold border border-emerald-500/30">
                        <i class="bi-gear-wide-connected"></i>
                        <span>Operator Sekolah</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h1>
                    <p class="text-slate-300 text-sm">Kelola data operasional sekolah dari sini.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-mono font-bold text-slate-300 bg-white/10 px-3 py-2 rounded-xl border border-white/10 shrink-0">
                <i class="bi-clock-fill"></i>
                <span>{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-file-earmark-text-fill text-emerald-600 text-xl"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-800">0</div>
                <div class="text-xs text-slate-500 font-medium mt-0.5">Surat Masuk</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-database-fill text-blue-600 text-xl"></i>
            </div>
            <div>
                <div class="text-sm font-black text-slate-800">Dapodik</div>
                <div class="text-xs text-slate-500 font-medium mt-0.5">Sinkronisasi Data</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-printer-fill text-purple-600 text-xl"></i>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-800">0</div>
                <div class="text-xs text-slate-500 font-medium mt-0.5">Cetak Hari Ini</div>
            </div>
        </div>
    </div>

    {{-- Pengembangan Notice --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-10 text-center">
        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="bi-kanban text-slate-400 text-3xl"></i>
        </div>
        <h3 class="font-extrabold text-slate-700 text-base mb-2">Menu Sedang Dikembangkan</h3>
        <p class="text-slate-500 text-sm max-w-md mx-auto">
            Fitur operator seperti input data siswa, manajemen surat, dan sinkronisasi Dapodik akan segera tersedia.
        </p>
    </div>
</div>
@endsection
