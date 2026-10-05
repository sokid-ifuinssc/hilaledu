@extends('layouts.app')

@section('title', 'Rekap Kehadiran Saya - HilalEdu')
@section('page-title', 'Rekap Kehadiran Saya')

@section('content')
@php
    $query = request()->query();
    $pegawaiRow = $pegawai['rows']->first();
    $mengajarRow = $mengajar ? $mengajar['rows']->first() : null;
@endphp
<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-emerald-800 via-teal-800 to-slate-900 rounded-3xl p-6 text-white shadow-xl">
        <div>
            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-emerald-200">
                <i class="bi bi-person-check-fill mr-1"></i> Rekap Pribadi
            </span>
            <h1 class="text-2xl md:text-3xl font-black mt-2 tracking-tight">Rekap Kehadiran Saya</h1>
            <p class="text-slate-300 text-xs md:text-sm mt-1">{{ $user->name }} &mdash; {{ $periode['label'] }}</p>
        </div>
    </div>

    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs">
        @include('rekap_kehadiran._filter', ['action' => route('rekap-kehadiran.saya'), 'periode' => $periode, 'taOptions' => $taOptions])
    </div>

    {{-- Daftar Hadir Pegawai (diri sendiri) --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
            <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="bi bi-people text-emerald-600"></i><span>Daftar Hadir Pegawai</span>
            </h2>
            <a href="{{ route('rekap-kehadiran.saya.print', array_merge($query, ['jenis' => 'pegawai'])) }}" target="_blank"
               class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl font-bold text-xs flex items-center gap-1.5 transition">
                <i class="bi bi-printer"></i><span>Cetak</span>
            </a>
        </div>
        @include('rekap_kehadiran._table', ['rekap' => $pegawai, 'mengajar' => false])
    </div>
    @if($pegawaiRow)
        @include('rekap_kehadiran._detail', ['detail' => $pegawaiRow, 'mengajar' => false])
    @endif

    {{-- Rekap Mengajar (khusus guru) --}}
    @if($isGuru)
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
            <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="bi bi-person-video3 text-indigo-600"></i><span>Rekap Kehadiran Mengajar</span>
            </h2>
            <a href="{{ route('rekap-kehadiran.saya.print', array_merge($query, ['jenis' => 'mengajar'])) }}" target="_blank"
               class="px-3 py-1.5 bg-indigo-700 hover:bg-indigo-800 text-white rounded-xl font-bold text-xs flex items-center gap-1.5 transition">
                <i class="bi bi-printer"></i><span>Cetak</span>
            </a>
        </div>
        @include('rekap_kehadiran._table', ['rekap' => $mengajar, 'mengajar' => true])
    </div>
    @if($mengajarRow)
        @include('rekap_kehadiran._detail', ['detail' => $mengajarRow, 'mengajar' => true])
    @endif
    @endif

    <p class="text-[11px] text-slate-400">
        Jumlah tidak hadir = tanpa keterangan + sakit + ijin + dinas luar. Bila ada data yang tidak sesuai, hubungi petugas piket atau Bidang Kurikulum.
    </p>
</div>
@endsection
