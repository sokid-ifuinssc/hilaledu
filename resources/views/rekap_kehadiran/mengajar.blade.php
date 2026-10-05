@extends('layouts.app')

@section('title', 'Rekap Kehadiran Mengajar Guru - HilalEdu')
@section('page-title', 'Rekap Mengajar Guru')

@section('content')
@php
    $query = request()->except('detail');
    $rows = $rekap['rows'];
    $t = $rekap['total'];
    $bulanMode = $periode['mode'] === 'bulan';
@endphp
<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-indigo-800 via-teal-800 to-slate-900 rounded-3xl p-6 text-white shadow-xl">
        <div>
            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-indigo-200">
                <i class="bi bi-person-video3 mr-1"></i> Kehadiran Mengajar
            </span>
            <h1 class="text-2xl md:text-3xl font-black mt-2 tracking-tight">Rekap Kehadiran Mengajar Guru</h1>
            <p class="text-slate-300 text-xs md:text-sm mt-1 max-w-2xl">
                Rekap hari &amp; jam mengajar seluruh guru &mdash; {{ $periode['label'] }}. Dasar acuan penyesuaian honor mengajar.
            </p>
        </div>
        <a href="{{ route('rekap-kehadiran.mengajar.print', $query) }}" target="_blank"
           class="px-4 py-2.5 bg-amber-400 hover:bg-amber-500 text-slate-950 font-bold rounded-2xl text-xs flex items-center gap-2 shadow-lg shadow-amber-400/20 transition self-start md:self-auto">
            <i class="bi bi-printer-fill text-sm"></i><span>Cetak (TTD Bidang Kurikulum)</span>
        </a>
    </div>

    <div class="flex items-center gap-2 border-b border-slate-200 pb-px text-xs font-bold">
        <a href="{{ route('rekap-kehadiran.pegawai', $query) }}" class="px-4 py-2.5 text-slate-500 hover:text-slate-800 hover:bg-slate-50 rounded-t-xl flex items-center gap-2 transition">
            <i class="bi bi-people"></i><span>Daftar Hadir Pegawai</span>
        </a>
        <a href="{{ route('rekap-kehadiran.mengajar', $query) }}" class="px-4 py-2.5 border-b-2 border-emerald-600 text-emerald-700 bg-emerald-50/50 rounded-t-xl flex items-center gap-2">
            <i class="bi bi-person-video3"></i><span>Rekap Kehadiran Mengajar Guru</span>
        </a>
    </div>

    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs">
        @include('rekap_kehadiran._filter', ['action' => route('rekap-kehadiran.mengajar'), 'periode' => $periode, 'taOptions' => $taOptions])
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
        <div class="bg-white p-4 rounded-2xl border border-slate-200">
            <div class="text-[11px] font-semibold text-slate-500">Guru Mengajar</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $rows->count() }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200">
            <div class="text-[11px] font-semibold text-slate-500">Total Jam / Minggu</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $t['jam_per_minggu'] }}</div>
        </div>
        <div class="bg-emerald-50/80 p-4 rounded-2xl border border-emerald-200">
            <div class="text-[11px] font-semibold text-emerald-800">Rata-rata Kehadiran Mengajar</div>
            <div class="text-2xl font-black text-emerald-700 mt-1">{{ $t['persen_hadir'] }}%</div>
        </div>
        <div class="bg-rose-50/80 p-4 rounded-2xl border border-rose-200">
            <div class="text-[11px] font-semibold text-rose-800">Tanpa Keterangan</div>
            <div class="text-2xl font-black text-rose-700 mt-1">{{ $t['tanpa_keterangan'] }}</div>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="bi bi-table text-emerald-600"></i><span>Rekap Kehadiran Mengajar &mdash; {{ $periode['label'] }}</span>
            </h2>
            <span class="text-[11px] text-slate-400">{{ $periode['start']->format('d/m/Y') }} s.d. {{ $periode['end']->format('d/m/Y') }}</span>
        </div>
        @include('rekap_kehadiran._table', [
            'rekap' => $rekap,
            'mengajar' => true,
            'detailUrl' => fn ($id) => route('rekap-kehadiran.mengajar', array_merge($query, ['detail' => $id])) . '#rincian',
            'logUrl' => ($canKbmLog && $bulanMode)
                ? fn ($id) => route('akademik.laporan.kehadiran.index', ['bulan' => $periode['bulan'], 'guru_id' => $id])
                : null,
        ])
    </div>

    <p class="text-[11px] text-slate-400">
        Hari kerja mengajar = hari kerja yang jatuh pada hari guru memiliki jadwal (dikurangi libur Kalender Akademik).
        Hadir bila ada absensi mengajar/jurnal KBM atau presensi harian hadir. Tanpa catatan = tanpa keterangan.
        @if($canKbmLog && $bulanMode) Ikon jam membuka log presensi per sesi mengajar (detail yang sudah ada). @endif
    </p>

    @if($detail)
    <div id="rincian">
        @include('rekap_kehadiran._detail', ['detail' => $detail, 'mengajar' => true, 'closeUrl' => route('rekap-kehadiran.mengajar', $query)])
    </div>
    @endif
</div>
@endsection
