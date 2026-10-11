@extends('layouts.app')

@section('title', 'Peta & Sebaran Domisili Warga Sekolah')
@section('page-title', 'Peta & Sebaran Domisili')

@push('head')
<!-- Leaflet CSS via Cloudflare CDNJS & jsDelivr Fallback -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" integrity="sha512-Zcn6bjR/8RBhbYUEfl3UBlhp6frHI5f2757GVucUfeCGZKOGlsNO304NPC9Gim5HgWXNXWgYehPH4vQRCwgQGA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    /* CRITICAL: Leaflet Tile & SVG fixes against Tailwind CSS and Bootstrap resets */
    .leaflet-container img.leaflet-tile {
        max-width: none !important;
        max-height: none !important;
        width: 256px !important;
        height: 256px !important;
    }
    .leaflet-container img {
        max-width: none !important;
        max-height: none !important;
    }
    .leaflet-pane svg {
        max-width: none !important;
        max-height: none !important;
    }
    .leaflet-tile-container img {
        max-width: none !important;
    }
    #domisiliMap {
        height: 520px;
        min-height: 520px;
        width: 100%;
        border-radius: 14px;
        z-index: 10;
        background-color: #f1f5f9;
    }
    .domisili-banner {
        background: linear-gradient(135deg, #064e3b 0%, #047857 40%, #0f172a 100%) !important;
        border-radius: 20px !important;
        border: 1px solid rgba(255, 255, 255, 0.25) !important;
        box-shadow: 0 12px 30px -4px rgba(6, 78, 59, 0.4) !important;
        color: #ffffff !important;
    }
    .domisili-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06);
        transition: all 0.2s ease;
    }
    .domisili-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 8px 25px -4px rgba(15, 23, 42, 0.1);
    }
    .leaflet-popup-content-wrapper {
        border-radius: 14px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
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
@endpush

@section('content')

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
                <p class="text-white text-xs sm:text-sm max-w-2xl leading-relaxed font-medium drop-shadow-xs" style="color: #ffffff !important;">
                    @if($selectedKelas || $isWaliKelasOnly)
                        Pemetaan domisili siswa kelas binaan untuk memudahkan perencanaan kunjungan rumah (home visit), pemantauan zonasi tempat tinggal, dan komunikasi darurat dengan orang tua/wali murid.
                    @else
                        Pemetaan wilayah tempat tinggal tenaga pendidik, kependidikan, dan peserta didik SMK Plus Al Hilal untuk analisis zonasi, keterjangkauan transportasi, dan perumusan kebijakan bimbingan.
                    @endif
                </p>
                <div class="flex flex-wrap items-center gap-2 pt-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-white border border-white/35 font-bold shadow-xs"><i class="bi bi-shield-check text-emerald-300"></i> Superadmin</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-white border border-white/35 font-bold shadow-xs"><i class="bi bi-person-badge text-emerald-300"></i> Kepala Sekolah</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-white border border-white/35 font-bold shadow-xs"><i class="bi bi-diagram-3 text-emerald-300"></i> Para Wakil Kepala</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-white border border-white/35 font-bold shadow-xs"><i class="bi bi-person-lines-fill text-emerald-300"></i> Wali Kelas</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-white border border-white/35 font-bold shadow-xs"><i class="bi bi-people text-emerald-300"></i> Pembina OSIS</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-white border border-white/35 font-bold shadow-xs"><i class="bi bi-heart-pulse text-emerald-300"></i> Guru BK</span>
                </div>
            </div>

            <div class="flex items-center gap-3 self-stretch sm:self-auto flex-shrink-0">
                <button onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-bold transition flex items-center gap-2 border border-white/30 shadow-xs">
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
            <div class="w-13 h-13 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-2xl font-bold flex-shrink-0 border border-emerald-200">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="min-w-0">
                <span class="text-xs font-black text-slate-600 uppercase tracking-wider block">Total Warga Sekolah</span>
                <h4 class="text-2xl font-black text-slate-900 tracking-tight mt-0.5">{{ number_format($totalWarga) }}</h4>
                <div class="text-xs text-emerald-700 font-bold mt-0.5 flex items-center gap-1">
                    <i class="bi bi-check2-circle"></i> {{ $totalTerisi }} ({{ $totalWarga > 0 ? round(($totalTerisi / $totalWarga) * 100) : 0 }}%) data wilayah terisi
                </div>
            </div>
        </div>

        <!-- Sebaran Guru -->
        <div class="domisili-card p-5 flex items-center gap-4">
            <div class="w-13 h-13 rounded-2xl bg-indigo-100 text-indigo-800 flex items-center justify-center text-2xl font-bold flex-shrink-0 border border-indigo-200">
                <i class="bi bi-person-video3"></i>
            </div>
            <div class="min-w-0">
                <span class="text-xs font-black text-slate-600 uppercase tracking-wider block">Pendidik (Guru)</span>
                <h4 class="text-2xl font-black text-slate-900 tracking-tight mt-0.5">{{ number_format($totalGuru) }}</h4>
                <div class="text-xs text-indigo-700 font-bold mt-0.5">
                    Guru Pengajar Aktif
                </div>
            </div>
        </div>

        <!-- Sebaran Siswa -->
        <div class="domisili-card p-5 flex items-center gap-4">
            <div class="w-13 h-13 rounded-2xl bg-teal-100 text-teal-800 flex items-center justify-center text-2xl font-bold flex-shrink-0 border border-teal-200">
                <i class="bi bi-backpack4-fill"></i>
            </div>
            <div class="min-w-0">
                <span class="text-xs font-black text-slate-600 uppercase tracking-wider block">Peserta Didik (Siswa)</span>
                <h4 class="text-2xl font-black text-slate-900 tracking-tight mt-0.5">{{ number_format($totalSiswa) }}</h4>
                <div class="text-xs text-teal-700 font-bold mt-0.5">
                    Siswa Seluruh Rombel
                </div>
            </div>
        </div>

        <!-- Tendik & Konsentrasi -->
        <div class="domisili-card p-5 flex items-center gap-4">
            <div class="w-13 h-13 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-2xl font-bold flex-shrink-0 border border-amber-200">
                <i class="bi bi-building-check"></i>
            </div>
            <div class="min-w-0">
                <span class="text-xs font-black text-slate-600 uppercase tracking-wider block">Tenaga Kependidikan</span>
                <h4 class="text-2xl font-black text-slate-900 tracking-tight mt-0.5">{{ number_format($totalTendik) }}</h4>
                <div class="text-xs text-amber-700 font-bold mt-0.5 truncate" title="{{ $topKecamatan->first()->kecamatan ?? '-' }}">
                    Top: {{ $topKecamatan->first()->kecamatan ?? 'Arjawinangun' }}
                </div>
            </div>
        </div>
    </div>

    <!-- PETA SEBARAN & ANALISIS WILAYAH -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Peta Interaktif (Col-span 2) -->
        <div class="lg:col-span-2 domisili-card p-5 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
                <div>
                    <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <i class="bi bi-map-fill text-emerald-600"></i> Peta Interaktif Konsentrasi Domisili
                    </h3>
                    <p class="text-xs text-slate-600 font-medium">Klik lingkaran wilayah untuk melihat detail dan daftar warga yang tinggal di daerah tersebut.</p>
                </div>
                
                <!-- Filter Cepat Peran di Peta -->
                <div class="inline-flex rounded-xl bg-slate-200/80 p-1 text-xs font-bold">
                    @if($isWaliKelasOnly)
                        <button type="button" class="px-3.5 py-1.5 rounded-lg bg-white shadow-xs text-emerald-900 font-black transition flex items-center gap-1.5">
                            <i class="bi bi-mortarboard-fill text-emerald-600"></i> Siswa Kelas Binaan
                        </button>
                    @else
                        <button type="button" onclick="filterMapByRole('all')" id="btnRoleAll" class="px-3 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition font-black">
                            Semua
                        </button>
                        <button type="button" onclick="filterMapByRole('siswa')" id="btnRoleSiswa" class="px-3 py-1.5 rounded-lg text-slate-700 hover:text-slate-900 transition font-bold">
                            Siswa
                        </button>
                        <button type="button" onclick="filterMapByRole('guru')" id="btnRoleGuru" class="px-3 py-1.5 rounded-lg text-slate-700 hover:text-slate-900 transition font-bold">
                            Guru & Tendik
                        </button>
                    @endif
                </div>
            </div>

            <!-- Kontainer Peta -->
            <div class="relative">
                <div id="domisiliMap"></div>

                <!-- Legenda Peta Melayang -->
                <div class="absolute bottom-4 left-4 z-[400] bg-white/95 backdrop-blur-xs p-3.5 rounded-xl border border-slate-300 shadow-xl text-xs space-y-1.5">
                    <div class="font-black text-slate-900 mb-1 flex items-center gap-1.5">
                        <i class="bi bi-info-circle-fill text-emerald-600"></i> Legenda Peta
                    </div>
                    <div class="flex items-center gap-2 text-slate-700 font-semibold">
                        <span class="w-3.5 h-3.5 rounded-full bg-emerald-600 border border-white shadow-xs inline-block"></span>
                        <span>SMK Plus Al Hilal (Pusat)</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-700 font-semibold">
                        <span class="w-3.5 h-3.5 rounded-full bg-teal-500 opacity-90 inline-block"></span>
                        <span>Kluster Siswa Terbanyak</span>
                    </div>
                    @if(!$isWaliKelasOnly)
                    <div class="flex items-center gap-2 text-slate-700 font-semibold">
                        <span class="w-3.5 h-3.5 rounded-full bg-indigo-600 opacity-90 inline-block"></span>
                        <span>Kluster Guru Terbanyak</span>
                    </div>
                    @endif
                    <div class="text-[10px] text-slate-500 font-medium pt-1">
                        *Klik lingkaran untuk nama siswa, kontak WA & rute kunjungan
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-slate-600 font-medium pt-2">
                <span id="mapStatusText"><i class="bi bi-arrow-repeat spin text-emerald-600"></i> Memuat titik sebaran...</span>
                <span class="text-slate-500 font-semibold">Sumber: Database Profil HilalEdu Portal</span>
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
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi bi-table text-emerald-600"></i> Rincian Data Domisili Warga Sekolah
                </h3>
                <p class="text-xs font-semibold text-slate-600">Daftar lengkap alamat guru, tendik, dan siswa beserta status domisili yang terdaftar.</p>
            </div>

            <!-- Form Filter & Pencarian -->
            <form method="GET" action="{{ route('domisili.index') }}" class="flex flex-wrap items-center gap-2">
                <!-- Filter Rombel Kelas -->
                <select name="kelas_id" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border-2 border-slate-300 text-xs font-bold text-slate-800 bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 max-w-[210px]">
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
                <select name="role" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border-2 border-slate-300 text-xs font-bold text-slate-800 bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                    <option value="all" {{ $roleFilter === 'all' ? 'selected' : '' }}>Semua Peran</option>
                    <option value="guru" {{ $roleFilter === 'guru' ? 'selected' : '' }}>Guru (Pendidik)</option>
                    <option value="tendik" {{ $roleFilter === 'tendik' ? 'selected' : '' }}>Tenaga Kependidikan</option>
                    <option value="siswa" {{ $roleFilter === 'siswa' ? 'selected' : '' }}>Siswa</option>
                </select>
                @endif

                <!-- Filter Kabupaten -->
                <select name="kabupaten" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border-2 border-slate-300 text-xs font-bold text-slate-800 bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 max-w-[150px]">
                    <option value="">Semua Kab/Kota</option>
                    @foreach($listKabupaten as $k)
                        <option value="{{ $k }}" {{ $kabupatenFilter == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>

                <!-- Input Pencarian -->
                <div class="relative">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama / alamat / ortu..." class="pl-8 pr-3 py-2 rounded-xl border-2 border-slate-300 text-xs font-semibold text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 w-44 sm:w-56">
                    <i class="bi bi-search absolute left-2.5 top-2.5 text-slate-500 text-xs"></i>
                </div>

                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition shadow-xs">
                    Cari
                </button>

                @if(!empty($search) || (!empty($kelasFilter) && !$isWaliKelasOnly) || ($roleFilter !== 'all' && !$isWaliKelasOnly) || !empty($kabupatenFilter))
                    <a href="{{ route('domisili.index') }}" class="px-3 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold transition" title="Reset Filter">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabel Warga / Siswa dengan Kontras Tinggi -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-900 uppercase tracking-wider text-[11px] font-black border-y-2 border-slate-200">
                        <th class="py-3.5 px-4">Nama Siswa / Warga</th>
                        <th class="py-3.5 px-3">Rombel / Peran</th>
                        <th class="py-3.5 px-3">Alamat Lengkap</th>
                        <th class="py-3.5 px-3">Desa & Kecamatan</th>
                        <th class="py-3.5 px-3">Kab / Kota</th>
                        <th class="py-3.5 px-3">Orang Tua (Ayah / Ibu)</th>
                        <th class="py-3.5 px-4 text-center">Hubungi & Home Visit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-800">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/90 transition">
                            <!-- Nama & Info -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-xs flex-shrink-0 shadow-xs {{ $u->role === 'guru' ? 'bg-indigo-600 text-white' : ($u->role === 'tendik' ? 'bg-amber-600 text-white' : 'bg-emerald-600 text-white') }}">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-black text-slate-900 block truncate max-w-[170px] text-[13px]">{{ $u->name }}</span>
                                        <span class="text-[11px] font-bold text-slate-600 block">{{ $u->nip ? ($u->role === 'siswa' ? 'NIS: '.$u->nip : 'NIP: '.$u->nip) : 'User: '.$u->username }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Peran / Rombel -->
                            <td class="py-3 px-3">
                                @if($u->role === 'guru')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-extrabold bg-indigo-100 text-indigo-900 border border-indigo-300">
                                        Guru
                                    </span>
                                @elseif($u->role === 'tendik')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300">
                                        Tendik
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-extrabold bg-emerald-100 text-emerald-900 border border-emerald-300 font-mono">
                                        {{ $u->kelas->nama_kelas ?? $u->kelas->nama ?? 'Belum Rombel' }}
                                    </span>
                                @endif
                            </td>

                            <!-- Alamat Lengkap -->
                            <td class="py-3 px-3 max-w-[200px]">
                                <span class="line-clamp-2 text-slate-900 font-semibold" title="{{ $u->alamat }}">
                                    {{ $u->alamat ?: '-' }}
                                </span>
                            </td>

                            <!-- Desa & Kecamatan -->
                            <td class="py-3 px-3">
                                <span class="font-black text-slate-900 block text-[12px]">{{ $u->desa ?: '-' }}</span>
                                <span class="text-[11px] font-bold text-slate-600">Kec. {{ $u->kecamatan ?: '-' }}</span>
                            </td>

                            <!-- Kabupaten & Provinsi -->
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-900 block">{{ $u->kabupaten ?: '-' }}</span>
                                <span class="text-[11px] font-semibold text-slate-600">{{ $u->provinsi ?: 'Jawa Barat' }}</span>
                            </td>

                            <!-- Orang Tua -->
                            <td class="py-3 px-3">
                                @if($u->role === 'siswa')
                                    <div class="text-[11px] space-y-0.5">
                                        <div><span class="font-semibold text-slate-500">Ayah:</span> <strong class="text-slate-900 font-bold">{{ $u->nama_ayah ?: '-' }}</strong></div>
                                        <div><span class="font-semibold text-slate-500">Ibu:</span> <strong class="text-slate-900 font-bold">{{ $u->nama_ibu ?: '-' }}</strong></div>
                                    </div>
                                @else
                                    <span class="text-slate-400 font-bold">-</span>
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
                                        <a href="https://wa.me/{{ $cleanHp }}" target="_blank" class="w-8 h-8 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition inline-flex items-center justify-center shadow-xs" title="Chat WA {{ $u->role === 'siswa' ? 'Siswa' : 'User' }}: {{ $u->no_hp }}">
                                            <i class="bi bi-whatsapp text-sm"></i>
                                        </a>
                                    @endif

                                    {{-- WhatsApp Orang Tua --}}
                                    @if(!empty($u->no_hp_ortu))
                                        @php
                                            $cleanHpO = preg_replace('/[^0-9]/', '', $u->no_hp_ortu);
                                            if (str_starts_with($cleanHpO, '0')) $cleanHpO = '62' . substr($cleanHpO, 1);
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanHpO }}" target="_blank" class="w-8 h-8 rounded-lg bg-teal-700 text-white hover:bg-teal-800 transition inline-flex items-center justify-center shadow-xs" title="Chat WA Orang Tua ({{ $u->nama_ayah ?: 'Ortu' }}): {{ $u->no_hp_ortu }}">
                                            <i class="bi bi-telephone-outbound-fill text-sm"></i>
                                        </a>
                                    @endif

                                    {{-- Petunjuk Rute Google Maps Home Visit --}}
                                    @php
                                        $fullAddress = trim(($u->alamat ? $u->alamat.', ' : '').($u->desa ? $u->desa.', ' : '').($u->kecamatan ? $u->kecamatan.', ' : '').($u->kabupaten ?: 'Cirebon'));
                                    @endphp
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($fullAddress) }}" target="_blank" class="w-8 h-8 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition inline-flex items-center justify-center shadow-xs" title="Buka Rute Google Maps untuk Kunjungan Rumah (Home Visit): {{ $fullAddress }}">
                                        <i class="bi bi-geo-alt-fill text-sm"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-500 font-bold">
                                <i class="bi bi-geo-alt text-3xl block mb-2 text-slate-400"></i>
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

@push('scripts')
<!-- Leaflet JS via Cloudflare CDNJS with fallback -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
    // Fallback jika CDN Cloudflare terblokir ISP lokal
    if (typeof L === 'undefined') {
        const fallbackScript = document.createElement('script');
        fallbackScript.src = 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js';
        fallbackScript.onload = function() {
            if (typeof initLeafletMap === 'function') initLeafletMap();
        };
        fallbackScript.onerror = function() {
            const unpkgScript = document.createElement('script');
            unpkgScript.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            unpkgScript.onload = function() {
                if (typeof initLeafletMap === 'function') initLeafletMap();
            };
            document.head.appendChild(unpkgScript);
        };
        document.head.appendChild(fallbackScript);
    }
</script>

<script>
let mapInstance = null;
let markersLayer = null;
let currentGeoRole = '{{ $isWaliKelasOnly ? "siswa" : "all" }}';
const currentKelasId = '{{ $kelasFilter }}';

// Koordinat SMK Plus Al Hilal (Pusat Sekolah di Arjawinangun, Cirebon)
const SCHOOL_COORDS = [-6.6465, 108.4110];

document.addEventListener('DOMContentLoaded', function () {
    startMapInitialization();
});

window.addEventListener('load', function() {
    if (mapInstance) {
        mapInstance.invalidateSize();
    } else {
        startMapInitialization();
    }
});

let initRetries = 0;
function startMapInitialization() {
    if (typeof L === 'undefined') {
        if (initRetries < 15) {
            initRetries++;
            setTimeout(startMapInitialization, 250);
        } else {
            const mapEl = document.getElementById('domisiliMap');
            if (mapEl) {
                mapEl.innerHTML = `
                    <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; text-align:center; padding:20px; color:#475569;">
                        <i class="bi bi-wifi-off" style="font-size:36px; color:#ef4444; margin-bottom:12px;"></i>
                        <h4 style="font-weight:800; font-size:16px; margin-bottom:6px; color:#0f172a;">Koneksi Peta Sedang Terhambat</h4>
                        <p style="font-size:12px; max-width:360px; margin-bottom:14px;">Pustaka peta interaktif memerlukan sambungan internet aktif. Data statistik dan tabel domisili di bawah tetap berfungsi penuh.</p>
                        <button onclick="location.reload()" style="background:#059669; color:white; border:none; padding:8px 18px; border-radius:10px; font-weight:700; font-size:12px; cursor:pointer;">
                            <i class="bi bi-arrow-clockwise"></i> Coba Muat Ulang
                        </button>
                    </div>
                `;
            }
        }
        return;
    }

    initLeafletMap();
    loadMapGeoData(currentGeoRole);
}

function initLeafletMap() {
    if (mapInstance) return;

    const container = document.getElementById('domisiliMap');
    if (!container) return;

    mapInstance = L.map('domisiliMap', {
        center: SCHOOL_COORDS,
        zoom: 11,
        zoomControl: true,
        scrollWheelZoom: true
    });

    // 1. Layer Utama: Google Maps Jalan (Sangat Detail di Indonesia, Cepat, Bebas API Key & Tanpa Watermark)
    const googleRoadLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: '&copy; Google Maps'
    }).addTo(mapInstance);

    // 2. Layer Alternatif: Google Satelit + Jalan (Hybrid - Foto Udara dengan Label Wilayah Lengkap)
    const googleHybridLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: '&copy; Google Maps Satelit'
    });

    // 3. Layer Alternatif: Esri Peta Jalan (World Street Map - Jernih & Stabil)
    const esriStreetLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; Esri Street Map',
        maxZoom: 18
    });

    // 4. Layer Alternatif: OpenStreetMap Resmi
    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        subdomains: ['a', 'b', 'c'],
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    });

    // 5. Layer Alternatif: Esri Satelit (Foto Udara Resolusi Tinggi)
    const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; Esri World Imagery',
        maxZoom: 18
    });

    // Tambahkan kontrol pemilih jenis peta
    L.control.layers({
        "🗺️ Google Maps": googleRoadLayer,
        "🛰️ Google Satelit": googleHybridLayer,
        "🛣️ Esri Peta Jalan": esriStreetLayer,
        "📍 OpenStreetMap": osmLayer,
        "🌍 Citra Satelit Esri": satelliteLayer
    }, null, { position: 'topright' }).addTo(mapInstance);

    // Otomatis invalidateSize berkala agar tile tidak pernah terpotong / abu-abu
    const forceRedraw = () => { if (mapInstance) mapInstance.invalidateSize(); };
    setTimeout(forceRedraw, 80);
    setTimeout(forceRedraw, 250);
    setTimeout(forceRedraw, 600);
    setTimeout(forceRedraw, 1200);
    setTimeout(forceRedraw, 2500);
    window.addEventListener('resize', forceRedraw);

    if (window.ResizeObserver) {
        new ResizeObserver(forceRedraw).observe(container);
    }

    // Marker Pusat Sekolah SMK Plus Al Hilal
    const schoolIcon = L.divIcon({
        className: 'custom-school-icon',
        html: `
            <div style="position: relative; display: flex; align-items: center; justify-content: center;">
                <div class="school-pulse-marker"></div>
                <div style="position: absolute; top: -32px; background: #064e3b; color: #ffffff; padding: 4px 10px; border-radius: 8px; font-weight: 800; font-size: 11px; white-space: nowrap; box-shadow: 0 4px 12px rgba(0,0,0,0.25); border: 1.5px solid rgba(255,255,255,0.4); text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                    🏛️ SMK Plus Al Hilal
                </div>
            </div>
        `,
        iconSize: [22, 22],
        iconAnchor: [11, 11]
    });

    L.marker(SCHOOL_COORDS, { icon: schoolIcon })
        .addTo(mapInstance)
        .bindPopup(`
            <div style="font-family: inherit; padding: 6px;">
                <h4 style="font-size: 14px; font-weight: 800; color: #064e3b; margin: 0 0 4px 0;">🏛️ SMK Plus Al Hilal</h4>
                <p style="font-size: 11.5px; color: #334155; margin: 0 0 8px 0; font-weight: 600;">Kampus Utama & Pusat Pembelajaran</p>
                <div style="font-size: 11px; background: #ecfdf5; color: #047857; padding: 6px 10px; border-radius: 8px; font-weight: 700; border: 1px solid #a7f3d0;">
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
        statusText.innerHTML = '<span class="inline-flex items-center gap-1.5 font-bold text-slate-700"><i class="bi bi-arrow-repeat spin text-emerald-600"></i> Memuat titik sebaran domisili...</span>';
    }

    const apiUrl = `{{ route('domisili.geo-data') }}?role=${encodeURIComponent(role)}&kelas_id=${encodeURIComponent(currentKelasId)}`;

    fetch(apiUrl)
        .then(res => res.json())
        .then(data => {
            if (!data || !data.clusters) return;

            if (markersLayer) markersLayer.clearLayers();

            data.clusters.forEach(cluster => {
                let radius = Math.max(12, Math.min(34, cluster.total * 2.8));

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

                if (markersLayer) {
                    const circle = L.circleMarker([lat, lng], {
                        radius: radius,
                        fillColor: fillColor,
                        color: '#ffffff',
                        weight: 2.5,
                        opacity: 1,
                        fillOpacity: 0.85
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
                                <div style="font-size: 10px; font-weight: 800; color: #334155; margin-bottom: 6px; text-transform: uppercase;">
                                    Daftar Siswa / Warga di Wilayah Ini:
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                        ` +
                        cluster.sample.map(s => {
                            let roleBadge = s.role === 'siswa' 
                                ? `<span style="background: #ecfdf5; color: #047857; padding: 2px 6px; border-radius: 4px; font-size: 9.5px; font-weight: 800; border: 1px solid #a7f3d0;">${s.kelas || 'Siswa'}</span>`
                                : `<span style="background: #e0e7ff; color: #3730a3; padding: 2px 6px; border-radius: 4px; font-size: 9.5px; font-weight: 800; border: 1px solid #c7d2fe;">${s.role}</span>`;

                            // WhatsApp Siswa
                            let waBtn = '';
                            if (s.no_hp) {
                                let cleanHp = s.no_hp.replace(/[^0-9]/g, '');
                                if (cleanHp.startsWith('0')) cleanHp = '62' + cleanHp.substring(1);
                                waBtn = `<a href="https://wa.me/${cleanHp}" target="_blank" title="Chat WA Siswa (${s.no_hp})" style="display: inline-flex; align-items: center; justify-content: center; padding: 3px 7px; border-radius: 6px; background: #25d366; color: white; text-decoration: none; font-size: 10px; font-weight: 800; box-shadow: 0 1px 3px rgba(0,0,0,0.15);">WA</a>`;
                            }

                            // WhatsApp Ortu
                            let waOrtuBtn = '';
                            if (s.no_hp_ortu) {
                                let cleanHpO = s.no_hp_ortu.replace(/[^0-9]/g, '');
                                if (cleanHpO.startsWith('0')) cleanHpO = '62' + cleanHpO.substring(1);
                                waOrtuBtn = `<a href="https://wa.me/${cleanHpO}" target="_blank" title="Chat WA Orang Tua (${s.nama_ayah || 'Ortu'}: ${s.no_hp_ortu})" style="display: inline-flex; align-items: center; justify-content: center; padding: 3px 6px; border-radius: 6px; background: #0d9488; color: white; text-decoration: none; font-size: 9.5px; font-weight: 800; box-shadow: 0 1px 3px rgba(0,0,0,0.15);">Ortu</a>`;
                            }

                            // Link Rute Google Maps Home Visit
                            let fullAdr = [s.alamat, cluster.desa, cluster.kecamatan, cluster.kabupaten].filter(Boolean).join(', ');
                            let mapsBtn = `<a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(fullAdr)}" target="_blank" title="Buka Rute Home Visit: ${fullAdr}" style="display: inline-flex; align-items: center; justify-content: center; padding: 3px 6px; border-radius: 6px; background: #2563eb; color: white; text-decoration: none; font-size: 10px; font-weight: 800; box-shadow: 0 1px 3px rgba(0,0,0,0.15);">📍 Peta</a>`;

                            let ortuInfo = '';
                            if (s.nama_ayah || s.nama_ibu) {
                                ortuInfo = `<div style="font-size: 10px; color: #475569; margin-top: 2px;">Ortu: <strong>${s.nama_ayah || s.nama_ibu}</strong></div>`;
                            }

                            return `
                                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 7px 9px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px;">
                                        <span style="font-weight: 800; font-size: 11.5px; color: #0f172a;">${s.name}</span>
                                        ${roleBadge}
                                    </div>
                                    ${ortuInfo}
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 5px; padding-top: 5px; border-top: 1px dashed #cbd5e1;">
                                        <span style="font-size: 10px; color: #475569; font-weight: 600; max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${s.alamat || cluster.label}">${s.alamat || cluster.label}</span>
                                        <div style="display: flex; gap: 4px;">
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
                        <div style="font-family: inherit; min-width: 230px; max-width: 290px; padding: 4px;">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 6px; margin-bottom: 6px;">
                                <h4 style="font-size: 13.5px; font-weight: 800; color: #0f172a; margin: 0;">${cluster.label}</h4>
                                <a href="${mapsRegionUrl}" target="_blank" title="Buka Daerah di Google Maps" style="font-size: 11px; color: #2563eb; text-decoration: none; font-weight: 800; white-space: nowrap; background: #eff6ff; padding: 2px 7px; border-radius: 5px; border: 1px solid #bfdbfe;">Peta ↗</a>
                            </div>
                            <div style="font-size: 11.5px; color: #334155; margin-bottom: 7px; font-weight: 600;">
                                Total Terdaftar: <strong style="color: #0f172a; font-weight: 900;">${cluster.total}</strong> Warga
                            </div>
                            <div style="display: flex; gap: 5px; font-size: 10.5px; margin-bottom: 6px;">
                                <span style="background: #ccfbf1; color: #0f766e; padding: 2.5px 7px; border-radius: 5px; font-weight: 800; border: 1px solid #99f6e4;">Siswa: ${cluster.siswa}</span>
                                <span style="background: #eef2ff; color: #4338ca; padding: 2.5px 7px; border-radius: 5px; font-weight: 800; border: 1px solid #c7d2fe;">Guru: ${cluster.guru}</span>
                                <span style="background: #fef3c7; color: #b45309; padding: 2.5px 7px; border-radius: 5px; font-weight: 800; border: 1px solid #fde68a;">Tendik: ${cluster.tendik}</span>
                            </div>
                            ${sampleHtml}
                        </div>
                    `);
                }
            });

            if (statusText) {
                statusText.innerHTML = `<span class="inline-flex items-center gap-1.5 font-bold text-emerald-800"><i class="bi bi-check2-circle text-emerald-600 text-sm"></i> <strong>${data.clusters.length}</strong> titik konsentrasi domisili (<strong>${data.total_mapped}</strong> warga terpetakan).</span>`;
            }
        })
        .catch(err => {
            console.error('Error load geo data:', err);
            if (statusText) {
                statusText.innerHTML = '<span class="text-rose-600 font-bold"><i class="bi bi-exclamation-triangle-fill"></i> Gagal memuat koordinat domisili. Silakan muat ulang.</span>';
            }
        });
}

function filterMapByRole(role) {
    const btnAll = document.getElementById('btnRoleAll');
    const btnSiswa = document.getElementById('btnRoleSiswa');
    const btnGuru = document.getElementById('btnRoleGuru');

    if (btnAll) {
        btnAll.className = role === 'all' 
            ? 'px-3 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition font-black' 
            : 'px-3 py-1.5 rounded-lg text-slate-700 hover:text-slate-900 transition font-bold';
    }

    if (btnSiswa) {
        btnSiswa.className = role === 'siswa' 
            ? 'px-3 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition font-black' 
            : 'px-3 py-1.5 rounded-lg text-slate-700 hover:text-slate-900 transition font-bold';
    }

    if (btnGuru) {
        btnGuru.className = role === 'guru' 
            ? 'px-3 py-1.5 rounded-lg bg-white shadow-xs text-slate-900 transition font-black' 
            : 'px-3 py-1.5 rounded-lg text-slate-700 hover:text-slate-900 transition font-bold';
    }

    loadMapGeoData(role);
}

function focusSchoolLocation() {
    if (mapInstance) {
        mapInstance.flyTo(SCHOOL_COORDS, 13, { duration: 1.5 });
    }
}
</script>
@endpush
@endsection
