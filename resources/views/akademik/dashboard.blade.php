@extends('layouts.app')
@section('title', 'Dashboard Akademik')

@section('content')
<div class="space-y-5">

    <x-dashboard-hero
        icon="bi-book-fill"
        badge="Layanan Akademik Terpadu"
        title="Pusat Pembelajaran &amp; Kurikulum"
        description="Kelola jadwal pelajaran, kurikulum, kehadiran mengajar guru, nilai siswa, dan kalender pendidikan dalam satu tempat yang praktis." />

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-3 hover:shadow-sm transition">
            <div class="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="bi-calendar3 text-emerald-600 text-xl"></i>
            </div>
            <div class="min-w-0">
                <div class="text-2xl font-black text-slate-800">{{ $totalJadwal ?? 0 }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Jadwal KBM</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-3 hover:shadow-sm transition">
            <div class="w-11 h-11 bg-sky-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="bi-people-fill text-sky-600 text-xl"></i>
            </div>
            <div class="min-w-0">
                <div class="text-2xl font-black text-slate-800">{{ $totalSiswa ?? 0 }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Siswa</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-3 hover:shadow-sm transition">
            <div class="w-11 h-11 bg-purple-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="bi-person-badge text-purple-600 text-xl"></i>
            </div>
            <div class="min-w-0">
                <div class="text-2xl font-black text-slate-800">{{ $totalGuru ?? 0 }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Guru</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-3 hover:shadow-sm transition">
            <div class="w-11 h-11 bg-amber-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="bi-bookmarks text-amber-600 text-xl"></i>
            </div>
            <div class="min-w-0">
                <div class="text-2xl font-black text-slate-800">{{ $totalMapel ?? 0 }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Mapel</div>
            </div>
        </div>
    </div>

    {{-- Menu Pintasan --}}
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs">
        <h3 class="font-extrabold text-sm text-slate-900 mb-4 flex items-center gap-2">
            <i class="bi-lightning-charge-fill text-amber-500"></i>
            Menu Pintasan Pembelajaran
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <a href="{{ route('akademik.jadwal.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50 transition group">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg shrink-0 group-hover:bg-emerald-200 transition">
                    <i class="bi-clock-history"></i>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-800">Jadwal Pelajaran</div>
                    <div class="text-xs text-slate-500">Kelola alokasi jam &amp; guru pengampu</div>
                </div>
            </a>
            <a href="{{ route('akademik.jadwal.matrix') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-sky-400 hover:bg-sky-50 transition group">
                <div class="w-10 h-10 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center text-lg shrink-0 group-hover:bg-sky-200 transition">
                    <i class="bi-grid-3x3-gap"></i>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-800">Matriks Jadwal Resmi</div>
                    <div class="text-xs text-slate-500">Tampilan tabel per kelas dan hari</div>
                </div>
            </a>
            <a href="{{ route('akademik.nilai.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-purple-400 hover:bg-purple-50 transition group">
                <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-lg shrink-0 group-hover:bg-purple-200 transition">
                    <i class="bi-award"></i>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-800">Rekap Nilai Siswa</div>
                    <div class="text-xs text-slate-500">Asesmen harian, PTS, dan PAS</div>
                </div>
            </a>
            <a href="{{ route('akademik.kalender.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-amber-400 hover:bg-amber-50 transition group">
                <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-lg shrink-0 group-hover:bg-amber-200 transition">
                    <i class="bi-calendar-event"></i>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-800">Kalender Pendidikan</div>
                    <div class="text-xs text-slate-500">Agenda kegiatan &amp; hari libur</div>
                </div>
            </a>
            <a href="{{ route('akademik.rekap-presensi.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50 transition group">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg shrink-0 group-hover:bg-emerald-200 transition">
                    <i class="bi-clipboard-check"></i>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-800">Rekapitulasi Presensi</div>
                    <div class="text-xs text-slate-500">Pantau kehadiran guru &amp; siswa</div>
                </div>
            </a>
            <a href="{{ route('akademik.piket.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition group">
                <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-lg shrink-0 group-hover:bg-slate-200 transition">
                    <i class="bi-shield-shaded"></i>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-800">Jadwal Guru Piket</div>
                    <div class="text-xs text-slate-500">Rotasi petugas piket harian</div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
