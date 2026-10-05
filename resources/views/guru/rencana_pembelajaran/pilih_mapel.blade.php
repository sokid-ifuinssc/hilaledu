@extends('layouts.app')

@section('title', 'Pilih Mapel - Rencana Pembelajaran')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-journal-check text-blue-600"></i>
                <span>Perangkat Rencana Pembelajaran</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Pilih mata pelajaran yang Anda ampu dari jadwal KBM untuk menyusun perangkat Kurikulum Merdeka.</p>
        </div>
    </div>

    <!-- Informasi Kalender Akademik Sekolah -->
    @if(isset($activeKalender) && $activeKalender)
    @php
        $efektifData = $activeKalender->calculateEffectiveWeeks();
        $smt1Efektif = $efektifData['semester1']['total_minggu_efektif'] ?? 19;
        $smt2Efektif = $efektifData['semester2']['total_minggu_efektif'] ?? 17;
        $upcomingEvents = $activeKalender->events->where('tanggal_mulai', '>=', date('Y-m-d'))->take(3);
    @endphp
    <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white rounded-3xl p-5 sm:p-6 shadow-md relative overflow-hidden">
        <div class="absolute right-0 top-0 -mt-10 -mr-10 w-72 h-72 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="space-y-2 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-[10px] uppercase tracking-wider">
                        Acuan Kalender Akademik Sekolah
                    </span>
                    <span class="text-xs text-slate-300 font-semibold">&bull;</span>
                    <span class="text-xs text-blue-200 font-bold">Tahun Ajaran {{ $activeKalender->tahun_ajaran }}</span>
                </div>
                <h3 class="font-extrabold text-lg text-white">
                    {{ $activeKalender->nama_kalender ?: 'Kalender Pendidikan SMK Plus Al-Hilal' }}
                </h3>
                <p class="text-xs text-slate-300 leading-relaxed max-w-2xl">
                    Jadwal efektif belajar KBM menjadi dasar alokasi CP, TP, ATP, dan Modul Ajar Harian. Semester Ganjil: <strong>{{ $smt1Efektif }} Minggu Efektif</strong> &bull; Semester Genap: <strong>{{ $smt2Efektif }} Minggu Efektif</strong>.
                </p>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="{{ route('guru.kalender.index') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition">
                    <i class="bi-calendar3"></i>
                    <span>Buka Kalender Lengkap</span>
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Daftar Mapel Ditugaskan -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-2">
            <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
                <i class="bi-card-list text-blue-600"></i>
                <span>Daftar Mata Pelajaran & Kelas yang Diampu</span>
            </h3>
            <span class="text-[11px] text-slate-500 bg-slate-100 px-3 py-1 rounded-full font-bold">
                Bersumber dari Jadwal
            </span>
        </div>

        @if($assignedMapels->isEmpty())
        <div class="bg-amber-50 border border-amber-300 rounded-3xl p-6 space-y-2 text-center">
            <i class="bi-exclamation-triangle-fill text-amber-500 text-3xl block mb-2"></i>
            <h4 class="font-black text-amber-800">Belum Ada Jadwal Mengajar</h4>
            <p class="text-xs text-amber-700 leading-relaxed max-w-md mx-auto">
                Anda belum ditugaskan mengajar di jadwal pelajaran manapun. Silakan hubungi <strong>Admin Akademik</strong> atau <strong>Waka Kurikulum</strong> untuk ploting jadwal terlebih dahulu.
            </p>
            <a href="{{ route('guru.jadwal.index') }}" class="inline-block px-4 py-2 mt-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition">
                Cek Jadwal Saya
            </a>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($assignedMapels as $mapel)
            <div class="border border-slate-200 rounded-2xl p-5 hover:border-blue-300 hover:shadow-md transition bg-slate-50 hover:bg-white group">
                <div class="flex justify-between items-start mb-3">
                    <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-lg text-[10px] font-black tracking-wide border border-blue-200">
                        Fase {{ $mapel['fase'] }} &bull; Kelas {{ $mapel['tingkat'] }}
                    </span>
                    <i class="bi-journal-check text-slate-300 group-hover:text-blue-500 text-xl transition"></i>
                </div>
                <h4 class="font-extrabold text-slate-800 text-sm mb-1 line-clamp-2">
                    {{ $mapel['nama_mapel'] }}
                </h4>
                <p class="text-[11px] text-slate-500 mb-4">
                    Susun perangkat CP, TP, ATP, dan Modul Ajar khusus untuk kelas tingkat ini.
                </p>
                <div class="flex items-center gap-2">
                    <a href="{{ route('guru.rencana-pembelajaran.index', ['mapel_id' => $mapel['mata_pelajaran_id'], 'tingkat' => $mapel['tingkat']]) }}" class="flex-1 text-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-sm transition">
                        Kelola Perangkat &rarr;
                    </a>
                    <a href="{{ route('guru.rencana-pembelajaran.print', ['mapel_id' => $mapel['mata_pelajaran_id'], 'tingkat' => $mapel['tingkat']]) }}" target="_blank" title="Cetak Rencana Pembelajaran" class="px-3 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white border border-emerald-200 rounded-xl text-xs font-bold transition flex items-center gap-1">
                        <i class="bi-printer"></i>
                        <span>Cetak</span>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
