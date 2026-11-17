@extends('layouts.app')

@section('title', 'Tentang Aplikasi & Panduan Penggunaan')
@section('page-title', 'Tentang Aplikasi')

@section('content')
@php
    $user = auth()->user();
    $guides = \App\Support\RoleGuides::allGuides();
    $activeKeys = \App\Support\RoleGuides::currentUserRoleKeys($user);
    $myGuides = collect($guides)->filter(fn($g) => in_array($g['key'], $activeKeys, true));
    $otherGuides = collect($guides)->reject(fn($g) => in_array($g['key'], $activeKeys, true));

    $colorClasses = [
        'amber'   => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-800', 'icon' => 'bg-amber-100 text-amber-700'],
        'blue'    => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'icon' => 'bg-blue-100 text-blue-700'],
        'indigo'  => ['bg' => 'bg-indigo-50', 'border' => 'border-indigo-200', 'text' => 'text-indigo-800', 'icon' => 'bg-indigo-100 text-indigo-700'],
        'emerald' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-800', 'icon' => 'bg-emerald-100 text-emerald-700'],
        'purple'  => ['bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'text' => 'text-purple-800', 'icon' => 'bg-purple-100 text-purple-700'],
        'sky'     => ['bg' => 'bg-sky-50', 'border' => 'border-sky-200', 'text' => 'text-sky-800', 'icon' => 'bg-sky-100 text-sky-700'],
        'rose'    => ['bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'text' => 'text-rose-800', 'icon' => 'bg-rose-100 text-rose-700'],
    ];

    $tugasList = is_array($user->tugas_tambahan) ? array_filter($user->tugas_tambahan) : [];
@endphp

<div class="space-y-6" x-data="{ filterRole: '' }">
    {{-- Header Tentang Aplikasi HilalEdu --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div class="space-y-2 max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>Tentang Aplikasi HilalEdu</span>
                </div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                    Sistem Informasi Manajemen Sekolah Terpadu — SMK Plus Al Hilal
                </h1>
                <p class="text-sm text-slate-600 leading-relaxed">
                    <strong>HilalEdu</strong> mengintegrasikan seluruh aktivitas sekolah dalam satu platform: mulai dari pengelolaan data master & SDM, kurikulum & jadwal, presensi KBM, penanganan disiplin BK, program kerja & kalender akademik, PKL/Prakerin, ekstrakurikuler, keuangan & koperasi, penggajian pegawai, penerbitan sertifikat/ijazah digital, hingga tracer alumni.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <a href="{{ route('alur-kerja.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-xs">
                    <i class="bi bi-diagram-3-fill text-emerald-400"></i>
                    <span>Buka Peta Alur Sistem Lengkap</span>
                </a>
            </div>
        </div>

        {{-- Info Akun Pengguna Saat Ini --}}
        <div class="mt-5 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-2 text-xs text-slate-600">
            <span class="font-bold text-slate-800">Akun Anda Saat Ini:</span>
            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-bold">{{ $user->name }}</span>
            <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 font-bold uppercase">
                Role: {{ str_replace('_', ' ', $user->role) }}
            </span>
            @if(!empty($user->admin_roles))
                <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 font-bold">
                    Admin: {{ implode(', ', array_map('ucfirst', $user->admin_roles)) }}
                </span>
            @endif
            @if(!empty($tugasList))
                <span class="px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 border border-purple-200 font-bold">
                    Tugas Tambahan: {{ implode(', ', $tugasList) }}
                </span>
            @endif
        </div>
    </div>

    {{-- Panduan Khusus untuk Peran User yang Sedang Login --}}
    @if($myGuides->isNotEmpty())
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="bi bi-person-check-fill text-emerald-600"></i>
                <span>Petunjuk Penggunaan Sesuai Peran Anda</span>
            </h2>
            <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                {{ $myGuides->count() }} Peran Aktif
            </span>
        </div>

        <div class="space-y-4">
            @foreach($myGuides as $g)
                @php $clr = $colorClasses[$g['color']] ?? $colorClasses['emerald']; @endphp
                <div class="bg-white rounded-2xl border-2 {{ $clr['border'] }} shadow-sm overflow-hidden">
                    <div class="p-5 {{ $clr['bg'] }} border-b {{ $clr['border'] }} flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl {{ $clr['icon'] }} flex items-center justify-center text-xl shrink-0">
                                <i class="bi {{ $g['icon'] }}"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base font-extrabold text-slate-900">{{ $g['role_label'] }}</h3>
                                    <span class="px-2.5 py-0.5 rounded-full bg-white {{ $clr['text'] }} border {{ $clr['border'] }} text-[11px] font-bold">
                                        {{ $g['badge'] }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 mt-1">{{ $g['ringkasan'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div class="space-y-2.5">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                <i class="bi bi-list-task text-indigo-600"></i>
                                <span>Tugas Utama</span>
                            </h4>
                            <ul class="space-y-2 text-xs text-slate-700 leading-relaxed">
                                @foreach($g['tugas_utama'] as $t)
                                    <li class="flex items-start gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                        <i class="bi bi-check2-circle text-emerald-600 mt-0.5 shrink-0"></i>
                                        <span>{{ $t }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="space-y-2.5">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                <i class="bi bi-arrow-repeat text-emerald-600"></i>
                                <span>Cara Kerja Garis Besar</span>
                            </h4>
                            <ol class="space-y-2 text-xs text-slate-700 leading-relaxed">
                                @foreach($g['cara_kerja'] as $idx => $step)
                                    <li class="flex items-start gap-2.5 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                        <span class="w-5 h-5 rounded-full bg-slate-900 text-white text-[10px] font-extrabold flex items-center justify-center shrink-0 mt-0.5">
                                            {{ $idx + 1 }}
                                        </span>
                                        <span>{{ $step }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Panduan Garis Besar Semua Role Lainnya --}}
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="bi bi-people-fill text-indigo-600"></i>
                    <span>Panduan Peran Pengguna Lainnya di HilalEdu</span>
                </h2>
                <p class="text-xs text-slate-500">Gambaran garis besar tugas dan cara kerja seluruh peran agar kolaborasi antar bagian berjalan selaras.</p>
            </div>
            <div class="w-full sm:w-64">
                <input type="text" x-model="filterRole" placeholder="Cari peran (misal: Guru, Siswa, Admin)..." class="form-input text-xs">
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($otherGuides as $g)
                @php
                    $clr = $colorClasses[$g['color']] ?? $colorClasses['slate'];
                    $blob = strtolower($g['role_label'] . ' ' . $g['ringkasan'] . ' ' . implode(' ', $g['tugas_utama']));
                @endphp
                <div x-show="filterRole === '' || {{ Js::from($blob) }}.includes(filterRole.toLowerCase())"
                     class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="p-4 {{ $clr['bg'] }} border-b border-slate-100 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $clr['icon'] }} flex items-center justify-center text-lg shrink-0">
                                <i class="bi {{ $g['icon'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-extrabold text-sm text-slate-900">{{ $g['role_label'] }}</h3>
                                <span class="text-[11px] font-semibold {{ $clr['text'] }}">{{ $g['badge'] }}</span>
                            </div>
                        </div>
                        <div class="p-4 space-y-3 text-xs">
                            <p class="text-slate-600 leading-relaxed">{{ $g['ringkasan'] }}</p>

                            <div>
                                <div class="font-extrabold text-slate-800 mb-1.5">Tugas Utama:</div>
                                <ul class="space-y-1 text-slate-600 list-disc pl-4">
                                    @foreach($g['tugas_utama'] as $t)
                                        <li>{{ $t }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            <div>
                                <div class="font-extrabold text-slate-800 mb-1.5">Cara Kerja Garis Besar:</div>
                                <ol class="space-y-1 text-slate-600 list-decimal pl-4">
                                    @foreach($g['cara_kerja'] as $step)
                                        <li>{{ $step }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
