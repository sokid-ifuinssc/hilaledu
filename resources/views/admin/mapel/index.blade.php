@extends('layouts.app')
@section('title', 'Manajemen Mata Pelajaran - HilalEdu')

@section('content')
<div class="space-y-6" x-data="{
    selected: [],
    allIds: {{ json_encode($mapels->pluck('id')->all()) }},
    filterTingkat: 'all',
    filterKelompok: 'all',
    searchQuery: '',
    groupByTingkat: true,
    editModal: false,
    editForm: {
        id: '',
        kode: '',
        nama: '',
        kelompok: 'umum',
        tingkat: 'semua',
        jam_per_minggu: 2,
        guru_user_id: ''
    },
    get allSelected() {
        return this.allIds.length > 0 && this.selected.length === this.allIds.length;
    },
    toggleAll() {
        if (this.allSelected) {
            this.selected = [];
        } else {
            this.selected = [...this.allIds];
        }
    },
    openEdit(mapel) {
        this.editForm = { ...mapel };
        this.editModal = true;
    },
    matches(tingkat, kelompok, text) {
        if (this.filterTingkat !== 'all' && tingkat !== this.filterTingkat) return false;
        if (this.filterKelompok !== 'all' && kelompok !== this.filterKelompok) return false;
        if (this.searchQuery.trim() !== '') {
            const q = this.searchQuery.toLowerCase();
            if (!text.toLowerCase().includes(q)) return false;
        }
        return true;
    },
    countTingkat(t) {
        if (t === 'all') return {{ $mapels->count() }};
        return {{ json_encode($mapels->groupBy('tingkat')->map->count()) }}[t] || 0;
    }
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-emerald-700 via-emerald-800 to-teal-800 rounded-2xl p-6 text-white shadow-lg">
        <div>
            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-emerald-100">
                <i class="bi bi-book-half mr-1"></i> Kurikulum Merdeka
            </span>
            <h1 class="text-2xl md:text-3xl font-bold mt-2">Daftar Mata Pelajaran</h1>
            <p class="text-emerald-100 text-sm mt-1">Kelola data mata pelajaran umum, kejuruan, dan muatan lokal di SMK Plus Al-Hilal Arjawinangun</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <!-- Export Buttons -->
            <div class="flex items-center rounded-xl bg-white/10 backdrop-blur-md border border-white/20 overflow-hidden text-xs font-bold text-white">
                <span class="px-2.5 py-2 flex items-center gap-1 border-r border-white/20 text-emerald-200">
                    <i class="bi-download"></i> Export:
                </span>
                <a href="{{ route('admin.mapel.export', ['format' => 'xlsx']) }}" class="px-2.5 py-2 hover:bg-white/20 transition">
                    Excel (.xlsx)
                </a>
                <a href="{{ route('admin.mapel.export', ['format' => 'csv']) }}" class="px-2.5 py-2 hover:bg-white/20 border-l border-white/20 transition">
                    CSV
                </a>
            </div>

            <!-- Import Button -->
            <button onclick="document.getElementById('modalImportMapel').classList.remove('hidden')" class="px-3.5 py-2.5 bg-teal-600 hover:bg-teal-500 text-white rounded-xl text-sm font-bold shadow-md transition flex items-center gap-2 cursor-pointer">
                <i class="bi-file-earmark-arrow-up text-sm"></i>
                <span>Import Mapel</span>
            </button>

            <!-- Tambah Mapel Button -->
            <button onclick="document.getElementById('modalTambahMapel').classList.remove('hidden')" class="px-4 py-2.5 bg-amber-400 hover:bg-amber-500 text-slate-900 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition flex items-center gap-2 cursor-pointer">
                <i class="bi-plus-circle-fill"></i> Tambah Mapel Baru
            </button>
        </div>
    </div>

    <!-- Import Errors Alert -->
    @if(session('import_errors'))
    @php
        $importErrors = session('import_errors');
        $hasCritical = collect($importErrors)->contains(fn($e) => str_starts_with($e, '❌'));
        $alertClass = $hasCritical
            ? 'bg-rose-50 border-rose-400 text-rose-900'
            : 'bg-amber-50 border-amber-400 text-amber-900';
        $iconClass = $hasCritical ? 'bi-x-circle-fill text-rose-600' : 'bi-exclamation-triangle-fill text-amber-600';
        $titleText = $hasCritical ? 'Import Gagal – Masalah Format File' : 'Catatan Hasil Import – Ada Data yang Dilewati';
    @endphp
    <div class="p-4 {{ $alertClass }} border-l-4 rounded-2xl shadow-sm text-xs space-y-2">
        <div class="font-bold flex items-center gap-2 text-sm">
            <i class="bi {{ $iconClass }}"></i>
            <span>{{ $titleText }}</span>
        </div>
        @if($hasCritical)
        <div class="bg-white/70 rounded-xl p-3 space-y-1.5">
            <p class="font-semibold text-xs text-slate-700">Panduan Kolom Template yang Benar:</p>
            <div class="grid grid-cols-5 gap-1 text-[10px]">
                <div class="bg-teal-600 text-white rounded px-2 py-1 text-center font-bold">A: Kode Mapel</div>
                <div class="bg-teal-600 text-white rounded px-2 py-1 text-center font-bold">B: Nama Mata Pelajaran</div>
                <div class="bg-teal-600 text-white rounded px-2 py-1 text-center font-bold">C: Kelompok</div>
                <div class="bg-teal-600 text-white rounded px-2 py-1 text-center font-bold">D: Tingkat Kelas</div>
                <div class="bg-teal-600 text-white rounded px-2 py-1 text-center font-bold">E: JP/Minggu</div>
            </div>
            <p class="text-[10px] text-slate-600">
                <strong>Contoh Kode:</strong> MP-TKJT-01 &nbsp;|&nbsp; 
                <strong>Kelompok:</strong> umum / kejuruan / muatan_lokal &nbsp;|&nbsp;
                <strong>Tingkat:</strong> X / XI / XII / semua
            </p>
        </div>
        @endif
        <ul class="space-y-1 text-slate-700 max-h-48 overflow-y-auto pl-1">
            @foreach($importErrors as $err)
            <li class="flex items-start gap-1.5">
                @if(str_starts_with($err, '❌'))
                    <i class="bi-x-circle-fill text-rose-500 shrink-0 mt-0.5"></i>
                @elseif(str_starts_with($err, '⚠️'))
                    <i class="bi-exclamation-triangle-fill text-amber-500 shrink-0 mt-0.5"></i>
                @elseif(str_starts_with($err, '---'))
                    <i class="bi-dash shrink-0 mt-0.5"></i>
                @else
                    <i class="bi-info-circle text-slate-400 shrink-0 mt-0.5"></i>
                @endif
                <span>{{ $err }}</span>
            </li>
            @endforeach
        </ul>
        @if($hasCritical)
        <div class="flex gap-2 pt-1">
            <a href="{{ route('admin.mapel.template') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-xs transition">
                <i class="bi-download"></i> Download Template yang Benar
            </a>
        </div>
        @endif
    </div>
    @endif


    <!-- Alert Messages -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-xl flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <i class="bi bi-check-circle-fill text-xl text-emerald-600"></i>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 cursor-pointer"><i class="bi bi-x-lg"></i></button>
    </div>
    @endif

    @if(isset($errors) && $errors->any())
    <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-xl shadow-sm">
        <div class="flex items-center gap-2 font-semibold text-sm mb-1">
            <i class="bi bi-exclamation-triangle-fill text-rose-600"></i> Ada kesalahan input:
        </div>
        <ul class="list-disc list-inside text-xs space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-4 cursor-pointer hover:border-emerald-300 transition"
             @click="filterTingkat = 'all'; filterKelompok = 'all'">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-collection"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500">Total Mata Pelajaran</p>
                <p class="text-xl font-bold text-slate-800">{{ $mapels->count() }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-4 cursor-pointer hover:border-blue-300 transition"
             @click="filterKelompok = 'umum'">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-journal-text"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500">Mapel Umum</p>
                <p class="text-xl font-bold text-slate-800">{{ $mapels->where('kelompok', 'umum')->count() }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-4 cursor-pointer hover:border-purple-300 transition"
             @click="filterKelompok = 'kejuruan'">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-cpu"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500">Kejuruan (TKJT/AKL/TO)</p>
                <p class="text-xl font-bold text-slate-800">{{ $mapels->where('kelompok', 'kejuruan')->count() }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-4 cursor-pointer hover:border-amber-300 transition"
             @click="filterKelompok = 'muatan_lokal'">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-flower1"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500">Muatan Lokal</p>
                <p class="text-xl font-bold text-slate-800">{{ $mapels->where('kelompok', 'muatan_lokal')->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Filter & Pengelompokan Toolbar -->
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 space-y-4">
        
        <!-- Baris 1: Filter Tab Jenjang / Tingkat Kelas -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 flex-wrap text-xs font-bold">
                <span class="text-slate-400 mr-1 text-[11px] uppercase tracking-wider">Jenjang Kelas:</span>
                
                <button type="button" 
                        @click="filterTingkat = 'all'"
                        :class="filterTingkat === 'all' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                    <span>Semua Jenjang</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="filterTingkat === 'all' ? 'bg-white/30 text-white' : 'bg-slate-200 text-slate-700'">
                        {{ $mapels->count() }}
                    </span>
                </button>

                <button type="button" 
                        @click="filterTingkat = 'X'"
                        :class="filterTingkat === 'X' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                    <span>Kelas X</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="filterTingkat === 'X' ? 'bg-white/30 text-white' : 'bg-slate-200 text-slate-700'">
                        {{ $mapels->where('tingkat', 'X')->count() }}
                    </span>
                </button>

                <button type="button" 
                        @click="filterTingkat = 'XI'"
                        :class="filterTingkat === 'XI' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                    <span>Kelas XI</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="filterTingkat === 'XI' ? 'bg-white/30 text-white' : 'bg-slate-200 text-slate-700'">
                        {{ $mapels->where('tingkat', 'XI')->count() }}
                    </span>
                </button>

                <button type="button" 
                        @click="filterTingkat = 'XII'"
                        :class="filterTingkat === 'XII' ? 'bg-purple-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                    <span>Kelas XII</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="filterTingkat === 'XII' ? 'bg-white/30 text-white' : 'bg-slate-200 text-slate-700'">
                        {{ $mapels->where('tingkat', 'XII')->count() }}
                    </span>
                </button>

                <button type="button" 
                        @click="filterTingkat = 'semua'"
                        :class="filterTingkat === 'semua' ? 'bg-teal-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 cursor-pointer"
                        title="Mata Pelajaran yang diajarkan pada semua jenjang">
                    <span>Lintas Jenjang (Semua)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="filterTingkat === 'semua' ? 'bg-white/30 text-white' : 'bg-slate-200 text-slate-700'">
                        {{ $mapels->where('tingkat', 'semua')->count() }}
                    </span>
                </button>
            </div>

            <!-- Toggle Pengelompokan (Group by) -->
            <div class="flex items-center gap-2">
                <button type="button" 
                        @click="groupByTingkat = !groupByTingkat"
                        :class="groupByTingkat ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-slate-50 text-slate-600 border-slate-200'"
                        class="px-3 py-1.5 rounded-xl border text-xs font-bold transition flex items-center gap-1.5 cursor-pointer hover:shadow-xs">
                    <i :class="groupByTingkat ? 'bi-diagram-3-fill text-emerald-600' : 'bi-list-ul text-slate-400'"></i>
                    <span x-text="groupByTingkat ? 'Pengelompokan: Per Jenjang' : 'Pengelompokan: Daftar Rata'"></span>
                </button>
            </div>
        </div>

        <!-- Baris 2: Filter Kelompok Mapel & Live Search Input -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 pt-2 border-t border-slate-100 text-xs">
            <div class="md:col-span-5 flex items-center gap-2">
                <span class="text-slate-400 text-[11px] uppercase font-bold tracking-wider shrink-0">Kelompok:</span>
                <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0 scrollbar-none font-semibold">
                    <button type="button" 
                            @click="filterKelompok = 'all'"
                            :class="filterKelompok === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg transition text-xs shrink-0 cursor-pointer">
                        Semua
                    </button>
                    <button type="button" 
                            @click="filterKelompok = 'umum'"
                            :class="filterKelompok === 'umum' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg transition text-xs shrink-0 cursor-pointer">
                        Umum
                    </button>
                    <button type="button" 
                            @click="filterKelompok = 'kejuruan'"
                            :class="filterKelompok === 'kejuruan' ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg transition text-xs shrink-0 cursor-pointer">
                        Kejuruan
                    </button>
                    <button type="button" 
                            @click="filterKelompok = 'muatan_lokal'"
                            :class="filterKelompok === 'muatan_lokal' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-lg transition text-xs shrink-0 cursor-pointer">
                        Muatan Lokal
                    </button>
                </div>
            </div>

            <div class="md:col-span-7 flex items-center justify-end gap-2">
                <div class="relative w-full max-w-md">
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Cari kode atau nama mata pelajaran..." 
                           class="w-full pl-9 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none transition">
                    <i class="bi bi-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <button type="button" 
                            x-show="searchQuery.length > 0" 
                            @click="searchQuery = ''" 
                            class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs cursor-pointer">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Selection Action Bar (Hapus Terpilih) -->
    <div x-show="selected.length > 0" x-cloak class="p-4 bg-rose-50 border-2 border-rose-200 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-3 shadow-sm transition-all">
        <div class="flex items-center gap-3">
            <span class="w-3 h-3 rounded-full bg-rose-500 animate-pulse"></span>
            <span class="text-xs font-bold text-rose-900">
                <strong class="text-sm font-black text-rose-700" x-text="selected.length"></strong> mata pelajaran dipilih untuk dihapus
            </span>
        </div>
        <form id="bulkDeleteMapelForm" action="{{ route('admin.mapel.bulk-delete') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ' + selected.length + ' mata pelajaran yang dipilih? Tindakan ini tidak dapat dibatalkan.');">
            @csrf
            <button type="submit" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer">
                <i class="bi-trash3-fill text-sm"></i>
                <span>Hapus Mapel Terpilih (<span x-text="selected.length"></span>)</span>
            </button>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <h2 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="bi bi-list-stars text-emerald-600"></i> Katalog Kurikulum & Mata Pelajaran
            </h2>
            <div class="text-xs text-slate-500">
                Menampilkan <strong class="text-slate-800">{{ $mapels->count() }}</strong> mata pelajaran aktif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/80 text-xs uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="w-12 px-4 py-3.5 text-center">
                            <input type="checkbox" 
                                   @change="toggleAll()" 
                                   :checked="allSelected" 
                                   title="Pilih Semua di Halaman Ini"
                                   class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                        </th>
                        <th class="px-6 py-3.5">Kode</th>
                        <th class="px-6 py-3.5">Nama Mata Pelajaran</th>
                        <th class="px-6 py-3.5">Kelompok</th>
                        <th class="px-6 py-3.5">Tingkat Kelas</th>
                        <th class="px-6 py-3.5 text-center">Beban (JP/Minggu)</th>
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $groupedMapels = [
                            'X' => [
                                'label' => 'Jenjang Kelas X (Fase E)',
                                'badge' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                'dot' => 'bg-indigo-600',
                                'items' => $mapels->where('tingkat', 'X'),
                            ],
                            'XI' => [
                                'label' => 'Jenjang Kelas XI (Fase F)',
                                'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'dot' => 'bg-blue-600',
                                'items' => $mapels->where('tingkat', 'XI'),
                            ],
                            'XII' => [
                                'label' => 'Jenjang Kelas XII (Fase F Lanjutan)',
                                'badge' => 'bg-purple-100 text-purple-800 border-purple-200',
                                'dot' => 'bg-purple-600',
                                'items' => $mapels->where('tingkat', 'XII'),
                            ],
                            'semua' => [
                                'label' => 'Semua Tingkat / Lintas Jenjang (Kelas X, XI, XII)',
                                'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'dot' => 'bg-emerald-600',
                                'items' => $mapels->where('tingkat', 'semua'),
                            ],
                        ];
                    @endphp

                    @forelse($groupedMapels as $groupKey => $group)
                        <!-- Group Header Row (Visible when groupByTingkat is ON) -->
                        <tr x-show="groupByTingkat && (filterTingkat === 'all' || filterTingkat === '{{ $groupKey }}')"
                            class="bg-slate-50/90 border-y border-slate-200/80 font-bold text-slate-800 text-xs">
                            <td colspan="7" class="px-6 py-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full {{ $group['dot'] }}"></span>
                                        <span class="font-black text-slate-900">{{ $group['label'] }}</span>
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-bold border {{ $group['badge'] }}">
                                        {{ $group['items']->count() }} Mata Pelajaran
                                    </span>
                                </div>
                            </td>
                        </tr>

                        <!-- Group Items -->
                        @foreach($group['items'] as $mapel)
                        <tr x-show="matches('{{ $mapel->tingkat }}', '{{ $mapel->kelompok }}', '{{ addslashes($mapel->kode . ' ' . $mapel->nama . ' ' . ($mapel->guru?->name ?? '')) }}')"
                            class="hover:bg-slate-50/60 transition"
                            :class="selected.includes({{ $mapel->id }}) ? 'bg-rose-50/50' : ''">
                            <td class="w-12 px-4 py-4 text-center">
                                <input type="checkbox" 
                                       form="bulkDeleteMapelForm"
                                       name="ids[]"
                                       value="{{ $mapel->id }}" 
                                       x-model.number="selected" 
                                       class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-emerald-700">
                                {{ $mapel->kode }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 leading-snug">{{ $mapel->nama }}</div>
                                <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                                    @if($mapel->guru)
                                    <span class="text-indigo-700 font-medium flex items-center gap-1">
                                        <i class="bi bi-person-badge"></i> {{ $mapel->guru?->name }}
                                    </span>
                                    @else
                                    <span class="text-slate-400 italic">Guru pengampu belum diset</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($mapel->kelompok == 'kejuruan')
                                    <span class="px-2.5 py-1 bg-purple-50 text-purple-700 border border-purple-200 rounded-lg text-xs font-semibold inline-flex items-center gap-1">
                                        <i class="bi bi-tools"></i> Kejuruan
                                    </span>
                                @elseif($mapel->kelompok == 'muatan_lokal')
                                    <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-xs font-semibold inline-flex items-center gap-1">
                                        <i class="bi bi-geo-alt"></i> Muatan Lokal
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-xs font-semibold inline-flex items-center gap-1">
                                        <i class="bi bi-book"></i> Umum
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 rounded-md text-xs font-semibold">
                                    {{ $mapel->tingkat == 'semua' ? 'Semua Tingkat (X, XI, XII)' : 'Kelas ' . $mapel->tingkat }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-50 text-emerald-700 font-bold text-sm border border-emerald-200">
                                    {{ $mapel->jam_per_minggu }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Tombol Edit Mapel -->
                                    <button type="button" 
                                            @click="openEdit({
                                                id: {{ $mapel->id }},
                                                kode: '{{ addslashes($mapel->kode) }}',
                                                nama: '{{ addslashes($mapel->nama) }}',
                                                kelompok: '{{ $mapel->kelompok }}',
                                                tingkat: '{{ $mapel->tingkat }}',
                                                jam_per_minggu: {{ $mapel->jam_per_minggu }},
                                                guru_user_id: '{{ $mapel->guru_user_id ?? '' }}'
                                            })"
                                            class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-xl transition border border-transparent hover:border-blue-200 cursor-pointer" 
                                            title="Edit Mata Pelajaran Ini">
                                        <i class="bi bi-pencil-square text-base"></i>
                                    </button>

                                    <!-- Tombol Hapus Mapel -->
                                    <form action="{{ route('admin.mapel.destroy', $mapel->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mapel {{ addslashes($mapel->nama) }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition border border-transparent hover:border-rose-200 cursor-pointer" title="Hapus Mata Pelajaran">
                                            <i class="bi bi-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="max-w-md mx-auto space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto text-xl shadow-xs">
                                    <i class="bi-journal-plus"></i>
                                </div>
                                <div class="font-bold text-slate-800 text-sm">Katalog Mata Pelajaran Masih Kosong</div>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Data dummy telah dikosongkan. Silakan input mata pelajaran resmi sekolah Anda dengan klik tombol di bawah, kemudian tentukan guru pengampu dan alokasi JP per kelas di menu Kurikulum.
                                </p>
                                <div class="flex items-center justify-center gap-2 pt-2">
                                    <button type="button" onclick="document.getElementById('modalTambahMapel').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs inline-flex items-center gap-1.5 shadow-sm transition cursor-pointer">
                                        <i class="bi-plus-circle"></i>
                                        <span>+ Tambah Mapel Baru</span>
                                    </button>
                                    <button type="button" onclick="document.getElementById('modalImportMapel').classList.remove('hidden')" class="px-3.5 py-2 bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold rounded-xl text-xs border border-teal-200 inline-flex items-center gap-1.5 transition cursor-pointer">
                                        <i class="bi-file-earmark-arrow-up"></i>
                                        <span>Import File Excel</span>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL EDIT MAPEL -->
    <div x-show="editModal" 
         x-cloak
         @keydown.escape.window="editModal = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none;">
        
        <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 border border-slate-200"
             @click.outside="editModal = false">
            
            <div class="px-6 py-4 bg-gradient-to-r from-blue-700 to-indigo-800 text-white flex items-center justify-between">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <i class="bi bi-pencil-square"></i> Edit Data Mata Pelajaran
                </h3>
                <button type="button" @click="editModal = false" class="text-white/70 hover:text-white text-xl cursor-pointer">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form method="POST" :action="'{{ url(request()->segment(1) . '/mapel') }}/' + editForm.id" class="p-6 space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Kode Mapel <span class="text-rose-500">*</span></label>
                    <input type="text" name="kode" required x-model="editForm.kode" placeholder="Contoh: MP-TKJT-01" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 uppercase font-mono text-sm">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" required x-model="editForm.nama" placeholder="Contoh: Dasar-Dasar Kejuruan TKJT" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm font-semibold">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Kelompok <span class="text-rose-500">*</span></label>
                        <select name="kelompok" required x-model="editForm.kelompok" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 text-xs font-semibold">
                            <option value="umum">Umum</option>
                            <option value="kejuruan">Kejuruan</option>
                            <option value="muatan_lokal">Muatan Lokal</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Tingkat Kelas <span class="text-rose-500">*</span></label>
                        <select name="tingkat" required x-model="editForm.tingkat" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 text-xs font-semibold">
                            <option value="semua">Semua Tingkat (X, XI, XII)</option>
                            <option value="X">Kelas X</option>
                            <option value="XI">Kelas XI</option>
                            <option value="XII">Kelas XII</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Beban Jam Pelajaran (JP/Minggu) <span class="text-rose-500">*</span></label>
                        <input type="number" name="jam_per_minggu" min="1" max="18" required x-model="editForm.jam_per_minggu" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm font-bold">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Guru Pengampu Utama</label>
                        <select name="guru_user_id" x-model="editForm.guru_user_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-600 text-xs font-medium">
                            <option value="">-- Belum Ditentukan --</option>
                            @foreach($guruList as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="editModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/20 transition cursor-pointer flex items-center gap-1.5">
                        <i class="bi bi-check-circle-fill"></i> Simpan Perubahan Mapel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Modal Tambah Mapel Baru -->
<div id="modalTambahMapel" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 border border-slate-200">
        <div class="px-6 py-4 bg-gradient-to-r from-emerald-700 to-teal-800 text-white flex items-center justify-between">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i class="bi bi-plus-circle"></i> Tambah Mata Pelajaran Baru
            </h3>
            <button onclick="document.getElementById('modalTambahMapel').classList.add('hidden')" class="text-white/70 hover:text-white text-xl cursor-pointer">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.mapel.store') }}" method="POST" class="p-6 space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Kode Mapel <span class="text-rose-500">*</span></label>
                <input type="text" name="kode" required placeholder="Contoh: MP-TKJT-01" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 uppercase font-mono text-sm">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Mata Pelajaran <span class="text-rose-500">*</span></label>
                <input type="text" name="nama" required placeholder="Contoh: Dasar-Dasar Kejuruan TKJT" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm font-semibold">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Kelompok <span class="text-rose-500">*</span></label>
                    <select name="kelompok" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs font-semibold">
                        <option value="umum">Umum</option>
                        <option value="kejuruan">Kejuruan</option>
                        <option value="muatan_lokal">Muatan Lokal</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Tingkat Kelas <span class="text-rose-500">*</span></label>
                    <select name="tingkat" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs font-semibold">
                        <option value="semua">Semua Tingkat (X, XI, XII)</option>
                        <option value="X">Kelas X</option>
                        <option value="XI">Kelas XI</option>
                        <option value="XII">Kelas XII</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Beban Jam Pelajaran (JP/Minggu) <span class="text-rose-500">*</span></label>
                    <input type="number" name="jam_per_minggu" min="1" max="18" value="2" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm font-bold">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Guru Pengampu Utama</label>
                    <select name="guru_user_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs font-medium">
                        <option value="">-- Belum Ditentukan --</option>
                        @foreach($guruList as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalTambahMapel').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition cursor-pointer flex items-center gap-1.5">
                    <i class="bi bi-check-circle-fill"></i> Simpan Mata Pelajaran
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import Mapel -->
<div id="modalImportMapel" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 border border-slate-200">
        <div class="px-6 py-4 bg-gradient-to-r from-teal-700 to-emerald-800 text-white flex items-center justify-between">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i class="bi-file-earmark-arrow-up"></i> Import Data Mata Pelajaran
            </h3>
            <button onclick="document.getElementById('modalImportMapel').classList.add('hidden')" class="text-white/70 hover:text-white text-xl cursor-pointer">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="p-6 space-y-4">
            <!-- Panduan Kolom Template -->
            <div class="p-4 bg-teal-50 border border-teal-200 rounded-2xl text-xs text-teal-900 space-y-3">
                <div class="font-bold flex items-center gap-1.5 text-teal-800">
                    <i class="bi-info-circle-fill"></i>
                    <span>Panduan Urutan Kolom Template:</span>
                </div>
                <!-- Visual kolom preview -->
                <div class="grid grid-cols-5 gap-1 text-[10px] text-center">
                    <div class="bg-teal-600 text-white rounded-lg py-2 px-1 font-bold">
                        <div class="text-teal-200 text-[9px] mb-0.5">Kolom A</div>
                        Kode Mapel
                        <div class="text-teal-200 mt-1 font-normal">MP-TKJT-01</div>
                    </div>
                    <div class="bg-teal-600 text-white rounded-lg py-2 px-1 font-bold">
                        <div class="text-teal-200 text-[9px] mb-0.5">Kolom B</div>
                        Nama Mapel
                        <div class="text-teal-200 mt-1 font-normal">Administrasi Jar.</div>
                    </div>
                    <div class="bg-teal-500 text-white rounded-lg py-2 px-1 font-bold">
                        <div class="text-teal-200 text-[9px] mb-0.5">Kolom C</div>
                        Kelompok
                        <div class="text-teal-200 mt-1 font-normal">umum / kejuruan</div>
                    </div>
                    <div class="bg-teal-500 text-white rounded-lg py-2 px-1 font-bold">
                        <div class="text-teal-200 text-[9px] mb-0.5">Kolom D</div>
                        Tingkat
                        <div class="text-teal-200 mt-1 font-normal">X / XI / XII / semua</div>
                    </div>
                    <div class="bg-teal-500 text-white rounded-lg py-2 px-1 font-bold">
                        <div class="text-teal-200 text-[9px] mb-0.5">Kolom E</div>
                        JP/Minggu
                        <div class="text-teal-200 mt-1 font-normal">2 / 4</div>
                    </div>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-2.5 text-amber-800 text-[10px]">
                    ⚠️ <strong>Penting:</strong> Kolom A HARUS berisi <strong>Kode Mapel</strong> (bukan nama). Pastikan urutan kolom sesuai template. Gunakan tombol download di bawah untuk mendapatkan template yang sudah benar.
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <a href="{{ route('admin.mapel.template', ['format' => 'xlsx']) }}" class="px-3 py-1.5 bg-teal-700 hover:bg-teal-800 text-white font-bold rounded-lg inline-flex items-center gap-1 transition shadow-xs text-xs">
                        <i class="bi-download"></i> Template Excel (.xlsx)
                    </a>
                    <a href="{{ route('admin.mapel.template', ['format' => 'csv']) }}" class="px-3 py-1.5 bg-white hover:bg-slate-50 text-teal-800 font-bold border border-teal-300 rounded-lg inline-flex items-center gap-1 transition text-xs">
                        <i class="bi-filetype-csv"></i> Template CSV
                    </a>
                </div>
            </div>

            <!-- Form Upload -->
            <form action="{{ route('admin.mapel.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih Berkas Excel / CSV (.xlsx, .xls, .csv)</label>
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-2xl file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-teal-700 file:text-white hover:file:bg-teal-800 cursor-pointer">
                    <p class="text-slate-400 mt-1.5 text-[10px]">Pastikan file yang diupload menggunakan format template di atas. Jika kolom salah/terbalik, sistem akan menolak dan memberitahu apa yang perlu diperbaiki.</p>
                </div>

                <div class="mt-4 p-3 bg-amber-50/50 border border-amber-200/50 rounded-xl">
                    <label class="block font-bold text-amber-800 uppercase tracking-wider mb-2">Jika Data Mapel Sudah Ada:</label>
                    <p class="text-amber-700/80 mt-1 mb-2 text-[10px]">Pemberitahuan: Jika sistem menemukan mapel dengan kode yang sama di database, apa yang harus dilakukan?</p>
                    <select name="duplicate_action" class="w-full p-2.5 bg-white border border-amber-300 rounded-xl text-xs font-semibold text-slate-700 focus:ring-teal-500 focus:border-teal-500 cursor-pointer">
                        <option value="update">Timpa / Update data yang sudah ada (Default)</option>
                        <option value="add_new">Tambah sebagai data baru (Kode mapel akan disesuaikan agar unik)</option>
                        <option value="skip">Lewati (Abaikan baris tersebut)</option>
                    </select>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modalImportMapel').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-teal-700 hover:bg-teal-800 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer">
                        <i class="bi-upload"></i> Mulai Import Mapel
                    </button>
                </div>
            </form>
        </div>
</div>
@endsection
