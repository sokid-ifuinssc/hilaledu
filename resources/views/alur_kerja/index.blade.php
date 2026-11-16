@extends('layouts.app')

@section('title', 'Peta Alur Kerja Sistem HilalEdu')
@section('page-title', 'Peta Alur Kerja Sistem HilalEdu')

@section('content')
@php
    $modules = \App\Support\MenuWorkflows::allModules();
    $overallFlow = \App\Support\MenuWorkflows::overallSystemFlow();
    $colorClasses = [
        'rose'    => 'border-rose-200 bg-rose-50 text-rose-700',
        'indigo'  => 'border-indigo-200 bg-indigo-50 text-indigo-700',
        'blue'    => 'border-blue-200 bg-blue-50 text-blue-700',
        'amber'   => 'border-amber-200 bg-amber-50 text-amber-800',
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'purple'  => 'border-purple-200 bg-purple-50 text-purple-700',
        'sky'     => 'border-sky-200 bg-sky-50 text-sky-700',
        'slate'   => 'border-slate-200 bg-slate-100 text-slate-700',
    ];
@endphp

<div class="space-y-8" x-data="{ search: '' }">
    {{-- Gambaran Keseluruhan Sistem HilalEdu --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-5 border-b border-slate-100">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold mb-2">
                    <i class="bi bi-diagram-3-fill"></i> Arsitektur Proses HilalEdu
                </span>
                <h2 class="text-xl font-extrabold text-slate-900">Gambaran Keseluruhan Alur Kerja & Hasil Akhir HilalEdu</h2>
                <p class="text-sm text-slate-500 mt-1">Alur proses terpadu dari persiapan awal tahun ajaran hingga pelaporan akhir, sertifikasi, kelulusan, dan tracer alumni.</p>
            </div>
            <div class="w-full md:w-72">
                <input type="text" x-model="search" placeholder="Cari alur modul (misal: BK, PKL, Gaji)..." class="form-input text-sm">
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 lg:grid-cols-5 gap-3">
            @foreach($overallFlow as $idx => $phase)
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 flex flex-col justify-between gap-3">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="w-7 h-7 rounded-full bg-slate-900 text-white text-xs font-black flex items-center justify-center">{{ $idx + 1 }}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white border border-slate-200 text-slate-600">{{ $phase['badge'] }}</span>
                        </div>
                        <h3 class="font-bold text-sm text-slate-900 leading-snug mb-2">{{ $phase['phase'] }}</h3>
                        <ul class="space-y-1.5 text-xs text-slate-600 list-disc pl-4">
                            @foreach($phase['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-[11px] font-semibold text-emerald-800">
                        <i class="bi bi-flag-fill me-1"></i> {{ $phase['output'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Alur Kerja Tiap Menu / Modul --}}
    <div class="space-y-5">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                <i class="bi bi-collection-fill text-indigo-600"></i>
                <span>Rincian Alur Kerja Setiap Menu & Modul</span>
            </h3>
            <span class="text-xs font-semibold text-slate-500">Total {{ count($modules) }} Modul Utama</span>
        </div>

        <div class="space-y-4">
            @foreach($modules as $mod)
                @php
                    $badgeStyle = $colorClasses[$mod['color'] ?? 'slate'] ?? $colorClasses['slate'];
                    $searchBlob = strtolower($mod['title'] . ' ' . $mod['subtitle'] . ' ' . $mod['actors'] . ' ' . $mod['result']);
                @endphp
                <div x-show="search === '' || {{ Js::from($searchBlob) }}.includes(search.toLowerCase())"
                     class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex flex-wrap items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl border flex items-center justify-center text-lg shrink-0 {{ $badgeStyle }}">
                                <i class="bi {{ $mod['icon'] }}"></i>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-base text-slate-900">{{ $mod['title'] }}</h4>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $mod['subtitle'] }}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $badgeStyle }}">
                            Aktor: {{ $mod['actors'] }}
                        </span>
                    </div>

                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($mod['steps'] as $step)
                                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between gap-2">
                                    <div>
                                        <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full border mb-1.5 {{ $badgeStyle }}">
                                            {{ $step['actor'] }}
                                        </span>
                                        <h5 class="font-bold text-sm text-slate-800">{{ $step['title'] }}</h5>
                                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $step['desc'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 flex items-start gap-2">
                            <i class="bi bi-check-circle-fill text-emerald-600 mt-0.5 shrink-0"></i>
                            <div><strong>Hasil Akhir Modul Ini:</strong> {{ $mod['result'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
