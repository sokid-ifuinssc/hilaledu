@extends('layouts.app')

@section('title', 'Penilaian Mata Pelajaran')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-200">
                    <i class="bi bi-mortarboard-fill text-lg"></i>
                </span>
                Input & Kelola Nilai Siswa
            </h1>
            <p class="text-sm text-slate-500 mt-1">Kelola nilai tugas, UTS, UAS, dan nilai akhir mata pelajaran yang Anda ampu per rombel/kelas.</p>
        </div>
    </div>

    <!-- Alert Khusus Integrasi Eskul dengan Team Work Project & Pancasila -->
    <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-2xl p-4 flex items-start gap-3">
        <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 text-base shadow-sm">
            <i class="bi bi-stars"></i>
        </div>
        <div class="text-xs text-emerald-950 leading-relaxed">
            <span class="font-bold">Integrasi Nilai Ekstrakurikuler:</span> Khusus mata pelajaran <b>Team Work Project dan Project Pancasila</b> (P5), Anda dapat menggunakan fitur otomatis <b>"Tarik Nilai & Absensi Eskul"</b> di dalam halaman input nilai. Sistem akan otomatis menyambungkan persentase kehadiran eskul dan nilai dari pembina eskul menjadi komponen nilai siswa!
        </div>
    </div>

    <!-- Grid Kelas & Mapel yang Diampu -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($penugasanMengajar as $item)
            @php
                $mapel = $item['mapel'];
                $kelas = $item['kelas'];
                $isEskulMapel = str_contains(strtolower($mapel->nama), 'team work') || str_contains(strtolower($mapel->nama), 'pancasila') || str_contains(strtolower($mapel->nama), 'project');
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col overflow-hidden group">
                <div class="p-5 flex-1 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="w-12 h-12 rounded-xl {{ $isEskulMapel ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600' }} flex items-center justify-center text-2xl font-bold group-hover:scale-105 transition">
                            <i class="bi {{ $isEskulMapel ? 'bi-trophy-fill' : 'bi-journal-bookmark-fill' }}"></i>
                        </div>
                        @if($isEskulMapel)
                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                <i class="bi bi-link-45deg"></i> Terkoneksi Eskul
                            </span>
                        @else
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                Reguler
                            </span>
                        @endif
                    </div>

                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ $mapel->kode_mapel ?: 'MAPEL' }}</span>
                        <h3 class="text-base font-bold text-slate-800 group-hover:text-blue-600 transition">
                            {{ $mapel->nama }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5 font-medium">
                            <i class="bi bi-door-open text-slate-400"></i>
                            Kelas: <b>{{ $kelas->nama_kelas }}</b> ({{ $kelas->jurusan ?: 'Umum' }})
                        </p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Jumlah Siswa: <b>{{ $kelas->siswas_count ?? $kelas->siswas()->count() }}</b></span>
                        <span class="text-blue-600 font-semibold flex items-center gap-1">
                            <i class="bi bi-pencil-square"></i> Siap Dinilai
                        </span>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-100">
                    <a href="{{ route('guru.nilai.input', [$mapel->id, $kelas->id]) }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold {{ $isEskulMapel ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white shadow-sm transition">
                        <span>Buka Lembar Nilai</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-3xl mx-auto mb-3">
                    <i class="bi bi-journal-x"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">Belum Ada Penugasan Mengajar</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">Anda belum ditugaskan mengajar mapel di kelas manapun dalam jadwal pelajaran atau master pembagian tugas guru.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
