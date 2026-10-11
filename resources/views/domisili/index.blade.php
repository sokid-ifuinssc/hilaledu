@extends('layouts.app')

@section('title', 'Peta & Sebaran Domisili Warga Sekolah')
@section('page-title', 'Peta & Sebaran Domisili')

@section('content')
<!-- Leaflet CSS & JS via CDN -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .domisili-banner {
        background: linear-gradient(135deg, #064e3b 0%, #047857 40%, #0f172a 100%) !important;
        border-radius: 20px !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        box-shadow: 0 12px 30px -4px rgba(6, 78, 59, 0.4) !important;
        color: #ffffff !important;
    }
    .domisili-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
        transition: all 0.2s ease;
    }
    .domisili-card:hover {
        border-color: #e2e8f0;
        box-shadow: 0 8px 25px -4px rgba(15, 23, 42, 0.08);
    }
    #domisiliMap {
        height: 520px;
        width: 100%;
        border-radius: 14px;
        z-index: 10;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 14px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15);
        padding: 4px;
    }
    .school-pulse-marker {
        width: 22px;
        height: 22px;
        background: #10b981;
        border: 3.5px solid #ffffff;
        border-radius: 50%;
        box-shadow: 0 0 0 rgba(16, 185, 129, 0.7);
        animation: schoolPulse 2s infinite;
    }
    @keyframes schoolPulse {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.8); }
        70% { box-shadow: 0 0 0 18px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
</style>

<div class="space-y-6 pb-12">
    <!-- Header Banner dengan Kontras Tinggi & Teks Jelas -->
    <div class="domisili-banner p-6 sm:p-8 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-15 pointer-events-none text-white">
            <i class="bi bi-geo-alt-fill text-[200px]"></i>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="space-y-2.5">
                @if($selectedKelas || $isWaliKelasOnly)
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-400/25 text-amber-200 text-xs font-bold border border-amber-300/40 shadow-xs">
                        <i class="bi bi-people-fill text-amber-300"></i> Mode Monitoring Wali Kelas: {{ $selectedKelas->nama_lengkap ?? $selectedKelas->nama ?? ($myKelasList->first()->nama ?? 'Kelas Binaan') }}
                    </div>
                @else
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-400/20 text-emerald-200 text-xs font-bold border border-emerald-300/30 shadow-xs">
                        <i class="bi bi-geo-alt-fill text-emerald-300"></i> Pemetaan Geografis Warga Sekolah
                    </div>
                @endif
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3 drop-shadow-sm" style="color: #ffffff !important;">
                    @if($selectedKelas || $isWaliKelasOnly)
                        Sebaran Domisili Siswa Kelas {{ $selectedKelas->nama ?? ($myKelasList->first()->nama ?? '') }}
                    @else
                        Sebaran Domisili Guru & Siswa
                    @endif
                </h1>
                <p class="text-emerald-100 text-xs sm:text-sm max-w-2xl leading-relaxed font-medium" style="color: #ecfdf5 !important;">
                    @if($selectedKelas || $isWaliKelasOnly)
                        Pemetaan domisili siswa kelas binaan untuk memudahkan perencanaan kunjungan rumah (home visit), pemantauan zonasi tempat tinggal, dan komunikasi darurat dengan orang tua/wali murid.
                    @else
                        Pemetaan wilayah tempat tinggal tenaga pendidik, kependidikan, dan peserta didik SMK Plus Al Hilal untuk analisis zonasi, keterjangkauan transportasi, dan perumusan kebijakan bimbingan.
                    @endif
                </p>
                <div class="flex flex-wrap items-center gap-2 pt-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 text-white border border-white/25 font-semibold"><i class="bi bi-shield-check text-emerald-300"></i> Superadmin</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 text-white border border-white/25 font-semibold"><i class="bi bi-person-badge text-emerald-300"></i> Kepala Sekolah</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 text-white border border-white/25 font-semibold"><i class="bi bi-diagram-3 text-emerald-300"></i> Para Wakil Kepala</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 text-white border border-white/25 font-semibold"><i class="bi bi-person-lines-fill text-emerald-300"></i> Wali Kelas</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 text-white border border-white/25 font-semibold"><i class="bi bi-people text-emerald-300"></i> Pembina OSIS</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 text-white border border-white/25 font-semibold"><i class="bi bi-heart-pulse text-emerald-300"></i> Guru BK</span>
                </div>
            </div>

            <div class="flex items-center gap-3 self-stretch sm:self-auto flex-shrink-0">
                <button onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white text-xs font-bold transition flex items-center gap-2 border border-white/20 shadow-xs">
                    <i class="bi bi-printer"></i> Cetak Ringkasan
                </button>
                <button onclick="focusSchoolLocation()" class="px-4 py-2.5 rounded-xl bg-emerald-400 hover:bg-emerald-300 text-slate-950 text-xs font-extrabold transition flex items-center gap-2 shadow-lg shadow-emerald-950/20">
                    <i class="bi bi-crosshair"></i> Fokus Kampus
                </button>
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik (KPI Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Warga Terdata -->
        <div class="domisili-card p-5 flex items-center gap-4">
            <div class="w-13 h-13 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="min-w-0">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Total Warga Sekolah</span>
                <h4 class="text-2xl font-black text-slate-800 tracking-tight mt-0.5">{{ number_format($totalWarga) }}</h4>
                <div class="text-[11px] text-emerald-600 font-semibold mt-0.5 flex items-center gap-1">
                    <i class="bi bi-check2-circle"></i> {{ $totalTerisi }} ({{ $totalWarga > 0 ? round(($totalTerisi / $totalWarga) * 100) : 0 }}%) data wilayah terisi
                </div>
            </div>
        </div>

        <!-- Sebaran Guru -->
        <div class="domisili-card p-5 flex items-center gap-4">
            <div class="w-13 h-13 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                <i class="bi bi-person-video3"></i>
            </div>
            <div class="min-w-0">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Pendidik (Guru)</span>
                <h4 class="text-2xl font-black text-slate-800 tracking-tight mt-0.5">{{ number_format($totalGuru) }}</h4>
                <div class="text-[11px] text-indigo-600 font-medium mt-0.5">
                    Guru Pengajar Aktif
                </div>
            </div>
        </div>

        <!-- Sebaran Siswa -->
        <div class="domisili-card p-5 flex items-center gap-4">
            <div class="w-13 h-13 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                <i class="bi bi-backpack4-fill"></i>
            </div>
            <div class="min-w-0">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Peserta Didik (Siswa)</span>
                <h4 class="text-2xl font-black text-slate-800 tracking-tight mt-0.5">{{ number_format($totalSiswa) }}</h4>
                <div class="text-[11px] text-teal-600 font-medium mt-0.5">
                    Siswa Seluruh Rombel
                </div>
            </div>
        </div>

        <!-- Tendik & Konsentrasi -->
        <div class="domisili-card p-5 flex items-center gap-4">
            <div class="w-13 h-13 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                <i class="bi bi-building-check"></i>
            </div>
            <div class="min-w-0">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Tenaga Kependidikan</span>
                <h4 class="text-2xl font-black text-slate-800 tracking-tight mt-0.5">{{ number_format($totalTendik) }}</h4>
                <div class="text-[11px] text-amber-600 font-semibold mt-0.5 truncate" title="{{ $topKecamatan->first()->kecamatan ?? '-' }}">
                    Top: {{ $topKecamatan->first()->kecamatan ?? 'Arjawinangun' }}
                </div>
            </div>
        </div>
    </div>

    <!-- PETA SEBARAN & ANALISIS WILAYAH -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Peta Interaktif (Col-span 2) -->
        <div class="lg:col-span-2 domisili-card p-5 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-map text-emerald-600"></i> Peta Interaktif Konsentrasi Domisili
                    </h3>
                    <p class="text-xs text-slate-500">Klik lingkaran wilayah untuk melihat detail dan daftar warga yang tinggal di daerah tersebut.</p>
                </div>
                
                <!-- Filter Cepat Peran di Peta -->
                <div class="inline-flex rounded-xl bg-slate-100 p-1 text-xs font-semibold">
                    @if($isWaliKelasOnly)
                        <button type="button" class="px-3 py-1.5 rounded-lg bg-white shadow-xs text-emerald-800 font-bold transition flex items-center gap-1.5">
                            <i class="bi bi-mortarboard-fill text-emerald-600"></i> Siswa Kelas Binaan
                        </button>
                    @else
                        <button type="button" onclick="filterMapByRole('all')" id="btnRoleAll" class="px-3 py-1.5 rounded-lg bg-white shadow-xs text-slate-800 transition font-bold">
                            Semua
                        </button>
                        <button type="button" onclick="filterMapByRole('siswa')" id="btnRoleSiswa" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition">
                            Siswa
                        </button>
                        <button type="button" onclick="filterMapByRole('guru')" id="btnRoleGuru" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition">
                            Guru & Tendik
                        </button>
                    @endif
                </div>
            </div>

            <!-- Kontainer Peta -->
            <div class="relative">
                <div id="domisiliMap"></div>

                <!-- Legenda Peta Melayang -->
                <div class="absolute bottom-4 left-4 z-[400] bg-white/95 backdrop-blur-xs p-3 rounded-xl border border-slate-200/80 shadow-md text-xs space-y-1.5">
                    <div class="font-bold text-slate-800 mb-1 flex items-center gap-1.5">
                        <i class="bi bi-info-circle-fill text-emerald-600"></i> Legenda Peta
                    </div>
                    <div class="flex items-center gap-2 text-slate-600">
                        <span class="w-3.5 h-3.5 rounded-full bg-emerald-600 border border-white shadow-xs inline-block"></span>
                        <span>SMK Plus Al Hilal (Pusat)</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-600">
                        <span class="w-3.5 h-3.5 rounded-full bg-teal-500 opacity-80 inline-block"></span>
                        <span>Kluster Siswa Terbanyak</span>
                    </div>
                    @if(!$isWaliKelasOnly)
                    <div class="flex items-center gap-2 text-slate-600">
                        <span class="w-3.5 h-3.5 rounded-full bg-indigo-600 opacity-80 inline-block"></span>
                        <span>Kluster Guru Terbanyak</span>
                    </div>
                    @endif
                    <div class="text-[10px] text-slate-400 pt-1">
                        *Klik lingkaran untuk nama siswa, kontak WA & rute kunjungan
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-slate-500 pt-2">
                <span id="mapStatusText"><i class="bi bi-arrow-repeat spin"></i> Memuat titik sebaran...</span>
                <span class="text-slate-400">Sumber: Database Profil HilalEdu Portal</span>
            </div>
        </div>

        <!-- Analisis Wilayah Terpadat (Col-span 1) -->
        <div class="space-y-6">
            <!-- Top Kecamatan Terbanyak -->
            <div class="domisili-card p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-bar-chart-fill text-emerald-600"></i> Top Kecamatan Domisili
                    </h3>
                    <span class="text-xs text-slate-400 font-semibold">{{ $topKecamatan->count() }} Wilayah</span>
                </div>

                <div class="space-y-3">
                    @forelse($topKecamatan as $kec)
                        @php
                            $persen = $totalWarga > 0 ? round(($kec->total / $totalWarga) * 100, 1) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-semibold text-slate-700 truncate max-w-[160px]">
                                    {{ $kec->kecamatan }}
                                </span>
                                <span class="text-slate-500 font-medium">
                                    <strong class="text-slate-800">{{ $kec->total }}</strong> warga ({{ $persen }}%)
                                </span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500" style="width: {{ min(100, max(5, $persen * 2)) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-400 text-xs">Belum ada data kecamatan terdata.</div>
                    @endforelse
                </div>
            </div>

            <!-- Top Kabupaten / Kota -->
            <div class="domisili-card p-5 space-y-3">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-buildings-fill text-indigo-600"></i> Sebaran Kabupaten / Kota
                    </h3>
                </div>

                <div class="space-y-2">
                    @forelse($topKabupaten as $kab)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 transition text-xs">
                            <span class="font-semibold text-slate-700 truncate">
                                <i class="bi bi-geo text-slate-400 mr-1.5"></i>{{ $kab->kabupaten }}
                            </span>
                            <span class="px-2 py-0.5 rounded-lg bg-white border border-slate-200 font-bold text-slate-800">
                                {{ $kab->total }} orang
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-slate-400 text-xs">Belum ada data kabupaten.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL FILTER & DETAIL DATA WARGA SEKOLAH -->
    <div class="domisili-card p-5 sm:p-6 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-table text-emerald-600"></i> Rincian Data Domisili Warga Sekolah
                </h3>
                <p class="text-xs text-slate-500">Daftar lengkap alamat guru, tendik, dan siswa beserta status domisili yang terdaftar.</p>
            </div>

            <!-- Form Filter & Pencarian -->
            <form method="GET" action="{{ route('domisili.index') }}" class="flex flex-wrap items-center gap-2">
                <!-- Filter Rombel Kelas -->
                <select name="kelas_id" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 max-w-[210px]">
                    @if($isWaliKelasOnly)
                        @foreach($myKelasList as $mk)
                            <option value="{{ $mk->id }}" {{ $kelasFilter == $mk->id ? 'selected' : '' }}>
                                ⭐ {{ $mk->nama_lengkap ?? $mk->nama ?? $mk->nama_kelas }} (Kelas Binaan)
                            </option>
                        @endforeach
                    @else
                        <option value="">Semua Rombel Kelas</option>
                        @if($isWaliKelas && $myKelasList->count())
                            <optgroup label="Kelas Binaan Saya (Wali Kelas)">
                                @foreach($myKelasList as $mk)
                                    <option value="{{ $mk->id }}" {{ $kelasFilter == $mk->id ? 'selected' : '' }}>
                                        ⭐ {{ $mk->nama_lengkap ?? $mk->nama ?? $mk->nama_kelas }} (Kelas Saya)
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                        <optgroup label="Seluruh Rombel">
                            @foreach($allKelasList as $ak)
                                <option value="{{ $ak->id }}" {{ $kelasFilter == $ak->id ? 'selected' : '' }}>
                                    {{ $ak->nama_lengkap ?? $ak->nama ?? $ak->nama_kelas }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>

                @if(!$isWaliKelasOnly)
                <!-- Filter Peran (Hanya jika pimpinan) -->
                <select name="role" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    <option value="all" {{ $roleFilter === 'all' ? 'selected' : '' }}>Semua Peran</option>
                    <option value="guru" {{ $roleFilter === 'guru' ? 'selected' : '' }}>Guru (Pendidik)</option>
                    <option value="tendik" {{ $roleFilter === 'tendik' ? 'selected' : '' }}>Tenaga Kependidikan</option>
                    <option value="siswa" {{ $roleFilter === 'siswa' ? 'selected' : '' }}>Siswa</option>
                </select>
                @endif

                <!-- Filter Kabupaten -->
                <select name="kabupaten" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 max-w-[150px]">
                    <option value="">Semua Kab/Kota</option>
                    @foreach($listKabupaten as $k)
                        <option value="{{ $k }}" {{ $kabupatenFilter == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>

                <!-- Input Pencarian -->
                <div class="relative">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama / alamat / ortu..." class="pl-8 pr-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 w-44 sm:w-56">
                    <i class="bi bi-search absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                </div>

                <button type="submit" class="px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                    Cari
                </button>

                @if(!empty($search) || (!empty($kelasFilter) && !$isWaliKelasOnly) || ($roleFilter !== 'all' && !$isWaliKelasOnly) || !empty($kabupatenFilter))
                    <a href="{{ route('domisili.index') }}" class="px-2.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition" title="Reset Filter">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabel Warga / Siswa -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 uppercase tracking-wider text-[11px] font-bold border-y border-slate-100">
                        <th class="py-3 px-4">Nama Siswa / Warga</th>
                        <th class="py-3 px-3">Rombel / Peran</th>
                        <th class="py-3 px-3">Alamat Lengkap</th>
                        <th class="py-3 px-3">Desa & Kecamatan</th>
                        <th class="py-3 px-3">Kab / Kota</th>
                        <th class="py-3 px-3">Orang Tua (Ayah / Ibu)</th>
                        <th class="py-3 px-4 text-center">Hubungi & Home Visit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Nama & Info -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs flex-shrink-0 {{ $u->role === 'guru' ? 'bg-indigo-100 text-indigo-700' : ($u->role === 'tendik' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-slate-800 block truncate max-w-[170px]">{{ $u->name }}</span>
                                        <span class="text-[11px] text-slate-400 block">{{ $u->nip ? ($u->role === 'siswa' ? 'NIS: '.$u->nip : 'NIP: '.$u->nip) : 'User: '.$u->username }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Peran / Rombel -->
                            <td class="py-3 px-3">
                                @if($u->role === 'guru')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                                        Guru
                                    </span>
                                @elseif($u->role === 'tendik')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                                        Tendik
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 font-mono">
                                        {{ $u->kelas->nama_kelas ?? $u->kelas->nama ?? 'Belum Rombel' }}
                                    </span>
                                @endif
                            </td>

                            <!-- Alamat Lengkap -->
                            <td class="py-3 px-3 max-w-[190px]">
                                <span class="line-clamp-2 text-slate-700" title="{{ $u->alamat }}">
                                    {{ $u->alamat ?: '-' }}
                                </span>
                            </td>

                            <!-- Desa & Kecamatan -->
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-800 block">{{ $u->desa ?: '-' }}</span>
                                <span class="text-[11px] text-slate-500">Kec. {{ $u->kecamatan ?: '-' }}</span>
                            </td>

                            <!-- Kabupaten & Provinsi -->
                            <td class="py-3 px-3">
                                <span class="font-semibold text-slate-800 block">{{ $u->kabupaten ?: '-' }}</span>
                                <span class="text-[11px] text-slate-400">{{ $u->provinsi ?: 'Jawa Barat' }}</span>
                            </td>

                            <!-- Orang Tua -->
                            <td class="py-3 px-3">
                                @if($u->role === 'siswa')
                                    <div class="text-[11px] space-y-0.5">
                                        <div><span class="text-slate-400">Ayah:</span> <strong>{{ $u->nama_ayah ?: '-' }}</strong></div>
                                        <div><span class="text-slate-400">Ibu:</span> <strong>{{ $u->nama_ibu ?: '-' }}</strong></div>
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            <!-- Aksi Hubungi & Kunjungan (Home Visit) -->
                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    {{-- WhatsApp Siswa / User --}}
                                    @if(!empty($u->no_hp))
                                        @php
                                            $cleanHp = preg_replace('/[^0-9]/', '', $u->no_hp);
                                            if (str_starts_with($cleanHp, '0')) $cleanHp = '62' . substr($cleanHp, 1);
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanHp }}" target="_blank" class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition inline-flex items-center justify-center shadow-xs" title="Chat WA {{ $u->role === 'siswa' ? 'Siswa' : 'User' }}: {{ $u->no_hp }}">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    @endif

                                    {{-- WhatsApp Orang Tua --}}
                                    @if(!empty($u->no_hp_ortu))
                                        @php
                                            $cleanHpO = preg_replace('/[^0-9]/', '', $u->no_hp_ortu);
                                            if (str_starts_with($cleanHpO, '0')) $cleanHpO = '62' . substr($cleanHpO, 1);
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanHpO }}" target="_blank" class="w-7 h-7 rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-600 hover:text-white transition inline-flex items-center justify-center shadow-xs" title="Chat WA Orang Tua ({{ $u->nama_ayah ?: 'Ortu' }}): {{ $u->no_hp_ortu }}">
                                            <i class="bi bi-telephone-outbound-fill"></i>
                                        </a>
                                    @endif

                                    {{-- Petunjuk Rute Google Maps Home Visit --}}
                                    @php
                                        $fullAddress = trim(($u->alamat ? $u->alamat.', ' : '').($u->desa ? $u->desa.', ' : '').($u->kecamatan ? $u->kecamatan.', ' : '').($u->kabupaten ?: 'Cirebon'));
                                    @endphp
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($fullAddress) }}" target="_blank" class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition inline-flex items-center justify-center shadow-xs" title="Buka Rute Google Maps untuk Kunjungan Rumah (Home Visit): {{ $fullAddress }}">
                                        <i class="bi bi-geo-alt-fill"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">
                                <i class="bi bi-geo-alt text-3xl block mb-2 text-slate-300"></i>
                                Tidak ditemukan data warga sesuai kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-between flex-wrap gap-4">
            <span class="text-xs text-slate-500">
                Menampilkan <strong>{{ $users->firstItem() ?? 0 }}</strong> - <strong>{{ $users->lastItem() ?? 0 }}</strong> dari <strong>{{ $users->total() }}</strong> warga sekolah
            </span>
            <div>
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>

<script>
let mapInstance = null;
let markersLayer = null;
let currentGeoRole = '{{ $isWaliKelasOnly ? "siswa" : "all" }}';
const currentKelasId = '{{ $kelasFilter }}';

// Koordinat SMK Plus Al Hilal (Pusat Sekolah di Arjawinangun, Cirebon)
const SCHOOL_COORDS = [-6.6465, 108.4110];

document.addEventListener('DOMContentLoaded', function () {
    initLeafletMap();
    loadMapGeoData(currentGeoRole);
});

function initLeafletMap() {
    mapInstance = L.map('domisiliMap', {
        center: SCHOOL_COORDS,
        zoom: 11,
        zoomControl: true,
        scrollWheelZoom: true
    });

    // Layer OpenStreetMap Resmi (100% Gratis, Jernih, Lengkap & Bebas API Key)
    const osmLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> kontributor',
        maxZoom: 19
    }).addTo(mapInstance);

    // Alternatif Layer Satelit Esri (Bebas API Key)
    const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; Esri World Imagery',
        maxZoom: 18
    });

    // Tambahkan kontrol pemilih jenis peta (Peta Jalan vs Satelit)
    L.control.layers({
        "🗺️ Peta Jalan": osmLayer,
        "🛰️ Citra Satelit": satelliteLayer
    }, null, { position: 'topright' }).addTo(mapInstance);

    setTimeout(() => {
        if (mapInstance) mapInstance.invalidateSize();
    }, 300);

    // Marker Pusat Sekolah SMK Plus Al Hilal
    const schoolIcon = L.divIcon({
        className: 'custom-school-icon',
        html: `
            <div style="position: relative; display: flex; align-items: center; justify-content: center;">
                <div class="school-pulse-marker"></div>
                <div style="position: absolute; top: -30px; background: #064e3b; color: #fff; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 11px; white-space: nowrap; box-shadow: 0 4px 10px rgba(0,0,0,0.15); border: 1px solid rgba(255,255,255,0.2);">
                    🏛️ SMK Plus Al Hilal
                </div>
            </div>
        `,
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });

    L.marker(SCHOOL_COORDS, { icon: schoolIcon })
        .addTo(mapInstance)
        .bindPopup(`
            <div style="font-family: inherit; padding: 4px;">
                <h4 style="font-size: 13px; font-weight: 800; color: #064e3b; margin: 0 0 4px 0;">SMK Plus Al Hilal</h4>
                <p style="font-size: 11px; color: #475569; margin: 0 0 6px 0;">Pusat Kegiatan Belajar Mengajar & Kampus Utama</p>
                <div style="font-size: 10px; background: #ecfdf5; color: #047857; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                    📍 Jl. KH. Masduki Ali No. 1, Arjawinangun, Cirebon
                </div>
            </div>
        `);

    markersLayer = L.layerGroup().addTo(mapInstance);
}

function loadMapGeoData(role) {
    currentGeoRole = role;
    const statusText = document.getElementById('mapStatusText');
    if (statusText) {
        statusText.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Memuat data kluster sebaran...';
    }

    const apiUrl = `{{ route('domisili.geo-data') }}?role=${encodeURIComponent(role)}&kelas_id=${encodeURIComponent(currentKelasId)}`;

    fetch(apiUrl)
        .then(res => res.json())
        .then(data => {
            if (!data || !data.clusters) return;

            markersLayer.clearLayers();

            data.clusters.forEach(cluster => {
                let radius = Math.max(10, Math.min(32, cluster.total * 2.5));

                // Tentukan warna berdasarkan dominasi
                let fillColor = '#0d9488'; // Teal (siswa)
                let strokeColor = '#042f2e';

                if (cluster.guru > 0 && cluster.siswa === 0) {
                    fillColor = '#4f46e5'; // Indigo (guru)
                    strokeColor = '#1e1b4b';
                } else if (cluster.tendik > 0 && cluster.siswa === 0) {
                    fillColor = '#d97706'; // Amber (tendik)
                    strokeColor = '#78350f';
                }

                // Beri sedikit random offset kecil agar tidak saling menumpuk jika satu koordinat
                let lat = cluster.lat + (Math.random() - 0.5) * 0.003;
                let lng = cluster.lng + (Math.random() - 0.5) * 0.003;

                const circle = L.circleMarker([lat, lng], {
                    radius: radius,
                    fillColor: fillColor,
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(markersLayer);

                // Tooltip
                circle.bindTooltip(`<strong>${cluster.label}</strong><br/>${cluster.total} terdaftar (Siswa: ${cluster.siswa})`, {
                    direction: 'top',
                    offset: [0, -radius]
                });

                // Sample Siswa / Warga dengan Tombol WhatsApp & Rute Google Maps Home Visit
                let sampleHtml = '';
                if (cluster.sample && cluster.sample.length > 0) {
                    sampleHtml = `
                        <div style="margin-top: 10px; border-top: 1px solid #e2e8f0; padding-top: 8px; max-height: 220px; overflow-y: auto;">
                            <div style="font-size: 10px; font-weight: 800; color: #475569; margin-bottom: 6px; text-transform: uppercase;">
                                Daftar Siswa / Warga di Wilayah Ini:
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 6px;">
                    ` +
                    cluster.sample.map(s => {
                        let roleBadge = s.role === 'siswa' 
                            ? `<span style="background: #ecfdf5; color: #047857; padding: 1px 5px; border-radius: 4px; font-size: 9px; font-weight: 700;">${s.kelas || 'Siswa'}</span>`
                            : `<span style="background: #e0e7ff; color: #3730a3; padding: 1px 5px; border-radius: 4px; font-size: 9px; font-weight: 700;">${s.role}</span>`;

                        // WhatsApp Siswa
                        let waBtn = '';
                        if (s.no_hp) {
                            let cleanHp = s.no_hp.replace(/[^0-9]/g, '');
                            if (cleanHp.startsWith('0')) cleanHp = '62' + cleanHp.substring(1);
                            waBtn = `<a href="https://wa.me/${cleanHp}" target="_blank" title="Chat WA Siswa (${s.no_hp})" style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 6px; background: #25d366; color: white; text-decoration: none; font-size: 10px; font-weight: bold;">WA</a>`;
                        }

                        // WhatsApp Ortu
                        let waOrtuBtn = '';
                        if (s.no_hp_ortu) {
                            let cleanHpO = s.no_hp_ortu.replace(/[^0-9]/g, '');
                            if (cleanHpO.startsWith('0')) cleanHpO = '62' + cleanHpO.substring(1);
                            waOrtuBtn = `<a href="https://wa.me/${cleanHpO}" target="_blank" title="Chat WA Orang Tua (${s.nama_ayah || 'Ortu'}: ${s.no_hp_ortu})" style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 6px; background: #0d9488; color: white; text-decoration: none; font-size: 9px; font-weight: bold;">Ortu</a>`;
                        }

                        // Link Rute Google Maps Home Visit
                        let fullAdr = [s.alamat, cluster.desa, cluster.kecamatan, cluster.kabupaten].filter(Boolean).join(', ');
                        let mapsBtn = `<a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(fullAdr)}" target="_blank" title="Buka Rute Home Visit: ${fullAdr}" style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 6px; background: #2563eb; color: white; text-decoration: none; font-size: 11px;">📍</a>`;

                        let ortuInfo = '';
                        if (s.nama_ayah || s.nama_ibu) {
                            ortuInfo = `<div style="font-size: 9.5px; color: #64748b; margin-top: 2px;">Ortu: <strong>${s.nama_ayah || s.nama_ibu}</strong></div>`;
                        }

                        return `
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 8px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px;">
                                    <span style="font-weight: 700; font-size: 11px; color: #1e293b;">${s.name}</span>
                                    ${roleBadge}
                                </div>
                                ${ortuInfo}
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 4px; padding-top: 4px; border-top: 1px dashed #cbd5e1;">
                                    <span style="font-size: 9.5px; color: #64748b; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${s.alamat || cluster.label}">${s.alamat || cluster.label}</span>
                                    <div style="display: flex; gap: 3px;">
                                        ${waBtn}
                                        ${waOrtuBtn}
                                        ${mapsBtn}
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('') +
                    `</div></div>`;
                }

                // Popup Wilayah
                let mapsRegionUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(cluster.label)}`;
                circle.bindPopup(`
                    <div style="font-family: inherit; min-width: 210px; max-width: 280px;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 4px; margin-bottom: 4px;">
                            <h4 style="font-size: 13px; font-weight: 800; color: #0f172a; margin: 0;">${cluster.label}</h4>
                            <a href="${mapsRegionUrl}" target="_blank" title="Buka Daerah di Google Maps" style="font-size: 10px; color: #2563eb; text-decoration: none; font-weight: 700; white-space: nowrap;">Peta ↗</a>
                        </div>
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 6px;">
                            Total: <strong>${cluster.total}</strong> Warga Sekolah Terdaftar
                        </div>
                        <div style="display: flex; gap: 4px; font-size: 10px;">
                            <span style="background: #ccfbf1; color: #0f766e; padding: 2px 6px; border-radius: 4px; font-weight: 700;">Siswa: ${cluster.siswa}</span>
                            <span style="background: #eef2ff; color: #4338ca; padding: 2px 6px; border-radius: 4px; font-weight: 700;">Guru: ${cluster.guru}</span>
                            <span style="background: #fef3c7; color: #b45309; padding: 2px 6px; border-radius: 4px; font-weight: 700;">Tendik: ${cluster.tendik}</span>
                        </div>
                        ${sampleHtml}
                    </div>
                `);
            });

            if (statusText) {
                statusText.innerHTML = `<i class="bi bi-check2-circle text-emerald-600"></i> ${data.clusters.length} titik konsentrasi domisili (${data.total_mapped} warga terpetakan).`;
            }
        })
        .catch(err => {
            console.error('Error load geo data:', err);
            if (statusText) {
                statusText.innerHTML = '<span class="text-rose-500">Gagal memuat koordinat domisili.</span>';
            }
        });
}

function filterMapByRole(role) {
    const btnAll = document.getElementById('btnRoleAll');
    const btnSiswa = document.getElementById('btnRoleSiswa');
    const btnGuru = document.getElementById('btnRoleGuru');

    if (btnAll) {
        btnAll.className = role === 'all' 
            ? 'px-3 py-1.5 rounded-lg bg-white shadow-xs text-slate-800 transition font-bold' 
            : 'px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition';
    }

    if (btnSiswa) {
        btnSiswa.className = role === 'siswa' 
            ? 'px-3 py-1.5 rounded-lg bg-white shadow-xs text-slate-800 transition font-bold' 
            : 'px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition';
    }

    if (btnGuru) {
        btnGuru.className = role === 'guru' 
            ? 'px-3 py-1.5 rounded-lg bg-white shadow-xs text-slate-800 transition font-bold' 
            : 'px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition';
    }

    loadMapGeoData(role);
}

function focusSchoolLocation() {
    if (mapInstance) {
        mapInstance.flyTo(SCHOOL_COORDS, 13, { duration: 1.5 });
    }
}
</script>
@endsection
