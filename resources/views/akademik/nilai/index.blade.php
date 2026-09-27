@extends('layouts.app')

@section('title', 'Rekap Nilai Siswa - HilalEdu')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-blue-800 via-indigo-800 to-slate-900 rounded-3xl p-6 text-white shadow-xl">
        <div>
            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-blue-200">
                <i class="bi-award mr-1"></i> Akademik
            </span>
            <h1 class="text-2xl md:text-3xl font-black mt-2 tracking-tight">Rekap Nilai Siswa</h1>
            <p class="text-slate-300 text-xs md:text-sm mt-1 max-w-2xl">
                Modul Penilaian sedang dalam tahap pengembangan. Fitur ini akan segera hadir.
            </p>
        </div>
    </div>

    <div class="bg-white p-12 rounded-3xl border border-slate-200 shadow-sm flex flex-col items-center justify-center text-center">
        <div class="w-24 h-24 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mb-6">
            <i class="bi-cone-striped text-4xl"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-2">Modul Sedang Dikembangkan</h3>
        <p class="text-slate-500 max-w-md">Fitur rekapitulasi nilai dan e-rapor sedang dalam proses penyempurnaan dan akan tersedia pada rilis pembaruan berikutnya.</p>
        
        <a href="{{ route('akademik.dashboard') }}" class="mt-6 px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition">
            Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection

