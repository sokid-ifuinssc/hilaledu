@extends('layouts.app')
@section('title', 'Dashboard Tracer Study')

@section('content')
<div class="space-y-5">

    <x-dashboard-hero
        icon="bi-mortarboard-fill"
        badge="Bursa Kerja Khusus &amp; Penelusuran Alumni"
        title="Tracer Study Alumni (BMW)"
        description="Pantau sebaran jejak lulusan yang Bekerja, Melanjutkan studi, atau Berwirausaha secara terdata dan terukur." />

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border border-purple-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-mortarboard text-purple-600 text-2xl"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Alumni Terdata</div>
                <div class="text-2xl font-black text-slate-800 mt-0.5">{{ $totalAlumni ?? 0 }}</div>
                <div class="text-xs text-purple-600 font-medium mt-0.5">Lulusan Semua Angkatan</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-emerald-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-clipboard-check text-emerald-600 text-2xl"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Kuesioner Terisi</div>
                <div class="text-2xl font-black text-emerald-600 mt-0.5">{{ $totalKuesioner ?? 0 }}</div>
                <div class="text-xs text-slate-500 font-medium mt-0.5">Responden Terverifikasi</div>
            </div>
        </div>
    </div>

    {{-- Menu Pintasan --}}
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs">
        <h3 class="font-extrabold text-sm text-slate-900 mb-4 flex items-center gap-2">
            <i class="bi-lightning-charge-fill text-amber-500"></i>
            Menu Pintasan Tracer Study
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="{{ route('tracer.alumni.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-purple-400 hover:bg-purple-50 transition group">
                <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-lg shrink-0 group-hover:bg-purple-200 transition">
                    <i class="bi-person-lines-fill"></i>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-800">Direktori Data Alumni</div>
                    <div class="text-xs text-slate-500">Database profil lulusan per tahun kelulusan &amp; jurusan</div>
                </div>
            </a>
            <a href="{{ route('tracer.kuesioner.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50 transition group">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg shrink-0 group-hover:bg-emerald-200 transition">
                    <i class="bi-question-diamond"></i>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-800">Kuesioner Penelusuran Karir</div>
                    <div class="text-xs text-slate-500">Formulir status BMW (Bekerja, Melanjutkan, Wirausaha)</div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
