@extends('layouts.app')
@section('title', 'Kurikulum & Pembagian Tugas Mengajar - HilalEdu')

@section('content')
<div class="space-y-6" x-data="{
    selected: [],
    allIds: {{ json_encode($kurikulums->pluck('id')->all()) }},
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
    editModal: false,
    editData: { id: null, kelas: '', mata_pelajaran_id: '', mapel_nama: '', guru_user_id: '', alokasi_jam: 2, keterangan: '', kategori: '', sub_kategori: '' },
    openEdit(item) {
        this.editData = { ...item };
        this.editModal = true;
    }
}">

    <!-- HERO / BANNER HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-emerald-800 via-teal-900 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-bold uppercase tracking-wider mb-2">
                <i class="bi-journal-bookmark-fill"></i>
                <span>Data Master Kurikulum & Pembagian Tugas KBM</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Master Kurikulum</h1>
            <p class="text-xs sm:text-sm text-emerald-100/90 mt-1 max-w-2xl">
                Atur mata pelajaran setiap kelas dan jenjang, tetapkan guru pengampu dan alokasi JP per minggu. Data ini menjadi acuan utama jadwal pelajaran dan matriks KBM.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 relative z-10">
            <!-- Filter Print Link -->
            <a href="{{ route('admin.kurikulum.print', request()->query()) }}" target="_blank" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black shadow-md hover:shadow-lg transition flex items-center gap-2" title="Cetak Format Lembar Resmi Kurikulum">
                <i class="bi-printer-fill text-sm"></i>
                <span>Cetak Dokumen</span>
            </a>

            <!-- Export Buttons -->
            <div class="flex items-center rounded-xl bg-white/10 backdrop-blur-md border border-white/20 overflow-hidden text-xs font-bold text-white">
                <span class="px-3 py-2 flex items-center gap-1 border-r border-white/20 text-emerald-200">
                    <i class="bi-download"></i> Export:
                </span>
                <a href="{{ route('admin.kurikulum.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}" class="px-3 py-2 hover:bg-white/20 transition" title="Export Excel">
                    Excel (.xlsx)
                </a>
                <a href="{{ route('admin.kurikulum.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="px-3 py-2 hover:bg-white/20 border-l border-white/20 transition" title="Export CSV">
                    CSV
                </a>
            </div>



            <!-- Import Mapel Button -->
            <button onclick="document.getElementById('modalImportMapel').classList.remove('hidden')" class="px-3.5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5" title="Import Master Mapel">
                <i class="bi-file-earmark-arrow-up text-sm"></i>
                <span>Import Mapel</span>
            </button>

            <!-- Import Kurikulum Button -->
            <button onclick="document.getElementById('modalImportKurikulum').classList.remove('hidden')" class="px-3.5 py-2.5 bg-teal-600 hover:bg-teal-500 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5" title="Import Data Kurikulum">
                <i class="bi-file-earmark-arrow-up text-sm"></i>
                <span>Import CSV Kurikulum</span>
            </button>

            <!-- Salin / Duplikasi Antar Kelas -->
            <button onclick="document.getElementById('modalDuplicateKurikulum').classList.remove('hidden')" class="px-3.5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5" title="Salin Struktur Kurikulum ke Rombel Lain">
                <i class="bi-copy text-sm"></i>
                <span>Salin Rombel</span>
            </button>


            <!-- Tambah Alokasi Button -->
            <button onclick="document.getElementById('modalTambahKurikulum').classList.remove('hidden')" class="px-4 py-2.5 bg-amber-400 hover:bg-amber-500 text-slate-950 rounded-xl text-xs font-black shadow-md hover:shadow-lg transition flex items-center gap-2">
                <i class="bi-plus-circle-fill text-base"></i>
                <span>+ Tambah Alokasi</span>
            </button>
        </div>
    </div>

    <!-- NOTIFIKASI SUKSES / ERROR -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-2xl flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <i class="bi-check-circle-fill text-xl text-emerald-600"></i>
            <span class="font-bold text-xs sm:text-sm">{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800"><i class="bi-x-lg"></i></button>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-2xl flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <i class="bi-exclamation-triangle-fill text-xl text-rose-600"></i>
            <span class="font-bold text-xs sm:text-sm">{{ session('error') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800"><i class="bi-x-lg"></i></button>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-2xl shadow-sm text-xs space-y-1.5 mt-4 mb-4">
        <div class="font-black flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <i class="bi-exclamation-triangle-fill text-rose-600"></i>
                <span>Terdapat Kesalahan Input:</span>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-rose-600 hover:text-rose-800"><i class="bi-x-lg"></i></button>
        </div>
        <ul class="list-disc list-inside space-y-1 text-rose-700 font-medium">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session('import_errors'))
    <div class="p-4 bg-amber-50 border-l-4 border-amber-500 text-amber-900 rounded-2xl shadow-sm text-xs space-y-1.5">
        <div class="font-black flex items-center gap-2">
            <i class="bi-exclamation-triangle-fill text-amber-600"></i>
            <span>Catatan Import Data:</span>
        </div>
        <ul class="list-disc list-inside space-y-1 text-slate-700 max-h-36 overflow-y-auto font-medium">
            @foreach(session('import_errors') as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- QUICK KPI STATS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl font-bold border border-emerald-100">
                <i class="bi-clock-history"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Beban Alokasi</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-900">{{ $totalAlokasiJp }} <span class="text-xs text-slate-500 font-bold">JP / Minggu</span></h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-xl font-bold border border-blue-100">
                <i class="bi-book-half"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Mapel Terdistribusi</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-900">{{ $totalMapelDiampu }} <span class="text-xs text-slate-500 font-bold">Penugasan</span></h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center text-xl font-bold border border-purple-100">
                <i class="bi-person-badge"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Guru Pengampu</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-900">{{ $totalGuruPengampu }} <span class="text-xs text-slate-500 font-bold">Guru Aktif</span></h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-xl font-bold border border-amber-100">
                <i class="bi-mortarboard"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Rombel Tercover</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-900">{{ $totalRombel }} <span class="text-xs text-slate-500 font-bold">Rombel Kelas</span></h3>
            </div>
        </div>
    </div>

    <!-- FILTER BAR (3 LEVEL: JURUSAN, JENJANG, KELAS) -->
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <i class="bi-funnel-fill text-emerald-600 text-lg"></i>
                <h3 class="font-extrabold text-slate-900 text-sm">Filter Kurikulum & Pembagian Tugas</h3>
                <span class="text-[11px] text-slate-400 font-medium">(Tahun {{ $tahunAjaran }} - Semester {{ $semester }})</span>
            </div>
            @if($jurusan !== 'all' || $jenjang !== 'all' || $kelas !== 'all')
            <a href="{{ route('admin.kurikulum.index') }}" class="text-xs text-rose-600 hover:text-rose-800 font-bold flex items-center gap-1 transition">
                <i class="bi-x-circle"></i> Reset Semua Filter
            </a>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Filter Jurusan -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">1. Pilih Jurusan</label>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <a href="{{ route('admin.kurikulum.index', array_merge(request()->query(), ['jurusan' => 'all'])) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $jurusan === 'all' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Semua
                    </a>
                    @foreach($jurusans as $j)
                    <a href="{{ route('admin.kurikulum.index', array_merge(request()->query(), ['jurusan' => $j->kode])) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $jurusan === $j->kode ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $j->kode }}
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Filter Jenjang / Tingkat -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">2. Pilih Jenjang</label>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <a href="{{ route('admin.kurikulum.index', array_merge(request()->query(), ['jenjang' => 'all'])) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $jenjang === 'all' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Semua
                    </a>
                    @foreach($jenjangList as $jg)
                    <a href="{{ route('admin.kurikulum.index', array_merge(request()->query(), ['jenjang' => $jg])) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $jenjang === $jg ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Kelas {{ $jg }}
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Filter Kelas / Rombel -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">3. Pilih Rombel Kelas</label>
                <form method="GET" action="{{ route('admin.kurikulum.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="jurusan" value="{{ $jurusan }}">
                    <input type="hidden" name="jenjang" value="{{ $jenjang }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <select name="kelas" onchange="this.form.submit()" class="w-full text-xs font-bold p-2 bg-slate-50 border border-slate-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="all" {{ $kelas === 'all' ? 'selected' : '' }}>-- Semua Kelas / Rombel --</option>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->nama }}" {{ $kelas === $k->nama ? 'selected' : '' }}>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <!-- Cari Mapel -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">4. Cari Mata Pelajaran</label>
                <form method="GET" action="{{ route('admin.kurikulum.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="jurusan" value="{{ $jurusan }}">
                    <input type="hidden" name="jenjang" value="{{ $jenjang }}">
                    <input type="hidden" name="kelas" value="{{ $kelas }}">
                    <div class="relative w-full">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama mapel..." class="w-full text-xs font-bold p-2 pl-8 bg-slate-50 border border-slate-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500">
                        <i class="bi-search absolute left-2.5 top-2.5 text-slate-400"></i>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- BULK DELETE BAR -->
    <div x-show="selected.length > 0" x-cloak class="p-3.5 bg-rose-50 border border-rose-200 rounded-2xl flex items-center justify-between shadow-sm animate-pulse">
        <div class="flex items-center gap-2 text-rose-800 text-xs font-bold">
            <i class="bi-check2-square text-base text-rose-600"></i>
            <span><strong x-text="selected.length"></strong> data alokasi kurikulum terpilih</span>
        </div>
        <form id="bulkDeleteForm" action="{{ route('admin.kurikulum.bulk-delete') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data terpilih?')">
            @csrf
            <template x-for="id in selected" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
            <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black shadow transition flex items-center gap-1.5">
                <i class="bi-trash"></i> Hapus Terpilih
            </button>
        </form>
    </div>

    <!-- TABEL DATA ALOKASI KURIKULUM -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-black text-slate-900 text-base flex items-center gap-2">
                    <i class="bi-list-stars text-emerald-600"></i>
                    <span>Daftar Alokasi Kurikulum & Pengampu KBM</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Menampilkan {{ $kurikulums->total() }} entri data alokasi</p>
            </div>
            
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Lengkap
                </span>
                <span class="inline-flex items-center gap-1 text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg font-bold">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Sebagian
                </span>
                <span class="inline-flex items-center gap-1 text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg font-bold">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span> Belum
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 font-bold uppercase text-[10.5px] text-slate-500 tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="w-12 px-4 py-4 text-center">
                            <input type="checkbox" @change="toggleAll()" :checked="allSelected" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                        </th>
                        <th class="px-4 py-4">Rombel & Jenjang</th>
                        <th class="px-4 py-4">Mata Pelajaran</th>
                        <th class="px-4 py-4">Guru Pengampu</th>
                        <th class="px-4 py-4 text-center">Alokasi JP</th>
                        <th class="px-4 py-4">Status Jadwal KBM</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($kurikulums as $item)
                    <tr class="hover:bg-slate-50/80 transition" :class="selected.includes({{ $item->id }}) ? 'bg-emerald-50/40' : ''">
                        <td class="w-12 px-4 py-4 text-center">
                            <input type="checkbox" value="{{ $item->id }}" x-model.number="selected" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg font-black text-xs {{ str_contains($item->kelas, 'TKJT') ? 'bg-blue-50 text-blue-700 border border-blue-200' : (str_contains($item->kelas, 'TO') ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200') }}">
                                    {{ $item->kelas }}
                                </span>
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-bold text-[10px]">
                                    Kls {{ $item->jenjang }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-extrabold text-slate-900 text-sm">
                                {{ $item->mataPelajaran->nama ?? 'Mapel #' . $item->mata_pelajaran_id }}
                            </div>
                            <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                <span class="text-[10.5px] font-mono text-slate-500 font-bold">Kode: {{ $item->mataPelajaran->kode ?? '-' }}</span>
                                <span class="text-[9.5px] px-2 py-0.5 rounded-md font-black 
                                    {{ str_starts_with($item->kategori, 'A.') ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 
                                       (str_starts_with($item->kategori, 'B.') ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 
                                       'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                    {{ str_starts_with($item->kategori, 'A.') ? 'A. UMUM' : (str_starts_with($item->kategori, 'B.') ? 'B. KEJURUAN' : 'C. MULOK') }}
                                </span>
                                @if($item->sub_kategori)
                                <span class="text-[9.5px] px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200 font-semibold">
                                    {{ $item->sub_kategori }}
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            @if($item->guru)
                            <div class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                <i class="bi-person-check-fill text-emerald-600"></i>
                                <span>{{ $item->guru?->name }}</span>
                            </div>
                            <span class="text-[10.5px] text-slate-400 block">{{ $item->guru->jabatan_utama ?: 'Guru Pengampu' }}</span>
                            @else
                            <span class="inline-flex items-center gap-1 text-slate-400 text-xs italic font-normal">
                                <i class="bi-dash-circle"></i> Belum ditentukan
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-xl bg-slate-100 font-black text-slate-900 text-xs border border-slate-200">
                                {{ $item->alokasi_jam }} JP
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">per minggu</span>
                        </td>
                        <td class="px-4 py-4">
                            @php
                                $terjadwal = $item->terjadwal_jp;
                                $status = $item->status_jadwal;
                            @endphp
                            @if($status === 'lengkap')
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 text-xs">
                                <i class="bi-check-circle-fill text-emerald-600"></i>
                                <span>Lengkap ({{ $terjadwal }}/{{ $item->alokasi_jam }} JP)</span>
                            </div>
                            @elseif($status === 'sebagian')
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-amber-50 text-amber-700 font-bold border border-amber-200 text-xs">
                                <i class="bi-hourglass-split text-amber-600"></i>
                                <span>Sebagian ({{ $terjadwal }}/{{ $item->alokasi_jam }} JP)</span>
                            </div>
                            @else
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 text-slate-500 font-bold border border-slate-200 text-xs">
                                <i class="bi-circle text-slate-400"></i>
                                <span>Belum Masuk Jadwal (0/{{ $item->alokasi_jam }} JP)</span>
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <!-- Tombol Edit -->
                                <button type="button" 
                                        @click="openEdit({
                                            id: {{ $item->id }},
                                            kelas: '{{ addslashes($item->kelas) }}',
                                            mata_pelajaran_id: '{{ $item->mata_pelajaran_id }}',
                                            mapel_nama: '{{ addslashes($item->mataPelajaran->nama ?? '') }}',
                                            guru_user_id: '{{ $item->guru_user_id ?? '' }}',
                                            alokasi_jam: {{ $item->alokasi_jam }},
                                            keterangan: '{{ addslashes($item->keterangan ?? '') }}',
                                            kategori: '{{ addslashes($item->kategori ?? '') }}',
                                            sub_kategori: '{{ addslashes($item->sub_kategori ?? '') }}'
                                        })" 
                                        class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition" 
                                        title="Edit Alokasi">
                                    <i class="bi-pencil-square text-sm"></i>
                                </button>

                                <!-- Tombol Hapus -->
                                <form action="{{ route('admin.kurikulum.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus mapel {{ addslashes($item->mataPelajaran->nama ?? '') }} dari kurikulum {{ $item->kelas }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Hapus Alokasi">
                                        <i class="bi-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <i class="bi-inbox text-3xl block mb-2 text-slate-300"></i>
                            <span class="font-bold">Belum ada alokasi kurikulum yang sesuai filter.</span>
                            <p class="text-xs text-slate-400 mt-1">Klik "+ Tambah Alokasi" atau "Import CSV" untuk mengisi data kurikulum.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100">
            {{ $kurikulums->links() }}
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 1: TAMBAH ALOKASI KURIKULUM          -->
    <!-- ========================================== -->
    <div id="modalTambahKurikulum" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative my-8">
            <button onclick="document.getElementById('modalTambahKurikulum').classList.add('hidden')" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <div class="mb-5">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-bold border border-emerald-200 mb-1.5">
                    <i class="bi-plus-circle-fill"></i>
                    <span>Formulir Alokasi Kurikulum</span>
                </div>
                <h3 class="text-xl font-black text-slate-900">Tambah Alokasi Mata Pelajaran</h3>
                <p class="text-xs text-slate-500 mt-0.5">Tetapkan mata pelajaran dan guru pengampu untuk rombel kelas tertentu.</p>
            </div>

            <form action="{{ route('admin.kurikulum.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf

                <!-- Pilihan Kelas -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Rombel Kelas <span class="text-rose-500">*</span></label>
                    <select name="kelas" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-900 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">-- Pilih Rombel Kelas --</option>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->nama }}">{{ $k->nama }} (Tingkat {{ $k->tingkat }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Pilihan Mata Pelajaran -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="mata_pelajaran_id" list="mapel-list" required placeholder="Ketik nama Mapel baru atau pilih dari list..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-900 focus:ring-emerald-500 focus:border-emerald-500" autocomplete="off">
                    <datalist id="mapel-list">
                        @foreach($mapels as $m)
                        <option value="{{ $m->id }}">
                            {{ $m->nama }} ({{ $m->kode }}) - Default {{ $m->jam_per_minggu ?? 2 }} JP
                        </option>
                        @endforeach
                    </datalist>
                    <p class="text-[10.5px] text-slate-400 mt-1">Pilih ID mapel dari list, atau ketik nama mapel baru untuk menambah otomatis.</p>
                </div>

                <!-- Pilihan Guru Pengampu -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Guru Pengampu</label>
                    <select name="guru_user_id" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-blue-900 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">-- Pilih Guru Pengampu (Opsional) --</option>
                        @foreach($guruList as $g)
                        <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->jabatan_utama ?: 'Guru' }})</option>
                        @endforeach
                    </select>
                    <p class="text-[10.5px] text-slate-400 mt-1">Guru dapat ditetapkan sekarang atau disesuaikan kemudian.</p>
                </div>

                <!-- Pengelompokan Cetak Kurikulum -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-indigo-50 border border-indigo-100 rounded-xl">
                    <div>
                        <label class="block font-bold text-indigo-900 uppercase tracking-wider mb-1.5 text-[10px]">Kategori (Kelompok Mapel Cetak)</label>
                        <select name="kategori" class="w-full p-2.5 bg-white border border-indigo-200 rounded-xl font-semibold text-slate-700 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Bawaan Master Mapel --</option>
                            <option value="A. KELOMPOK MATA PELAJARAN UMUM">A. KELOMPOK MATA PELAJARAN UMUM</option>
                            <option value="B. KELOMPOK MATA PELAJARAN KEJURUAN">B. KELOMPOK MATA PELAJARAN KEJURUAN</option>
                            <option value="C. KELOMPOK MATA PELAJARAN MUATAN LOKAL">C. KELOMPOK MATA PELAJARAN MUATAN LOKAL</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-indigo-900 uppercase tracking-wider mb-1.5 text-[10px]">Sub Kategori (Khusus Kejuruan)</label>
                        <select name="sub_kategori" class="w-full p-2.5 bg-white border border-indigo-200 rounded-xl font-semibold text-slate-700 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Tidak Ada / Default --</option>
                            <option value="Dasar-dasar Program Keahlian">1. Dasar-dasar Program Keahlian</option>
                            <option value="Mata Pelajaran [Konsentrasi Keahlian]***">2. Mata Pelajaran [Konsentrasi Keahlian]</option>
                            <option value="Mata Pelajaran Pilihan****">3. Mata Pelajaran Pilihan</option>
                            <option value="Projek Kreatif dan Kewirausahaan">4. Projek Kreatif dan Kewirausahaan</option>
                        </select>
                    </div>
                    <div class="col-span-1 sm:col-span-2 text-[10px] text-indigo-600 leading-tight">
                        <i class="bi-info-circle-fill"></i> Pilihan di atas akan menentukan di mana mapel ini ditampilkan saat mencetak <b>Struktur Kurikulum Resmi</b>. Jika kosong, sistem otomatis mengikuti setelan Master Mapel.
                    </div>
                </div>

                <!-- Alokasi Jam (JP / Minggu) -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Beban Alokasi Jam (JP per Minggu) <span class="text-rose-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="alokasi_jam" value="2" min="1" max="24" required class="w-24 p-3 bg-slate-50 border border-slate-300 rounded-xl text-center font-black text-slate-900 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="font-bold text-slate-500">Jam Pelajaran (JP) / Minggu</span>
                    </div>
                </div>

                <!-- Keterangan Tambahan -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan (Opsional)</label>
                    <input type="text" name="keterangan" placeholder="Contoh: Teori & Praktik Lab Bengkel" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modalTambahKurikulum').classList.add('hidden')" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl shadow-md transition flex items-center gap-2">
                        <i class="bi-check-lg"></i>
                        <span>Simpan Alokasi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 2: EDIT ALOKASI KURIKULUM            -->
    <!-- ========================================== -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative my-8" @click.outside="editModal = false">
            <button @click="editModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <div class="mb-5">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-[11px] font-bold border border-blue-200 mb-1.5">
                    <i class="bi-pencil-square"></i>
                    <span>Perbarui Data Kurikulum</span>
                </div>
                <h3 class="text-xl font-black text-slate-900">Perbarui Alokasi</h3>
                <p class="text-xs text-slate-500 mt-0.5">Edit rombel kelas atau mata pelajaran untuk kurikulum ini.</p>
            </div>

            <form :action="'{{ url(request()->segment(1) . '/kurikulum') }}/' + editData.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <!-- Pilihan Kelas -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Rombel Kelas <span class="text-rose-500">*</span></label>
                    <select name="kelas" x-model="editData.kelas" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-900 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Rombel Kelas --</option>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->nama }}">{{ $k->nama }} (Tingkat {{ $k->tingkat }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Pilihan Mata Pelajaran -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="mata_pelajaran_id" x-model="editData.mata_pelajaran_id" list="mapel-edit-list" required placeholder="Ketik nama Mapel baru atau pilih dari list..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-900 focus:ring-blue-500 focus:border-blue-500" autocomplete="off">
                    <datalist id="mapel-edit-list">
                        @foreach($mapels as $m)
                        <option value="{{ $m->id }}">
                            {{ $m->nama }} ({{ $m->kode }}) - Default {{ $m->jam_per_minggu ?? 2 }} JP
                        </option>
                        @endforeach
                    </datalist>
                </div>

                <!-- Pilihan Guru Pengampu -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Guru Pengampu</label>
                    <select name="guru_user_id" x-model="editData.guru_user_id" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-blue-900 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Guru Pengampu --</option>
                        @foreach($guruList as $g)
                        <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->jabatan_utama ?: 'Guru' }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Pengelompokan Cetak Kurikulum -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-blue-50 border border-blue-100 rounded-xl">
                    <div>
                        <label class="block font-bold text-blue-900 uppercase tracking-wider mb-1.5 text-[10px]">Kategori (Kelompok Mapel Cetak)</label>
                        <select name="kategori" x-model="editData.kategori" class="w-full p-2.5 bg-white border border-blue-200 rounded-xl font-semibold text-slate-700 text-xs focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Bawaan Master Mapel --</option>
                            <option value="A. KELOMPOK MATA PELAJARAN UMUM">A. KELOMPOK MATA PELAJARAN UMUM</option>
                            <option value="B. KELOMPOK MATA PELAJARAN KEJURUAN">B. KELOMPOK MATA PELAJARAN KEJURUAN</option>
                            <option value="C. KELOMPOK MATA PELAJARAN MUATAN LOKAL">C. KELOMPOK MATA PELAJARAN MUATAN LOKAL</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-blue-900 uppercase tracking-wider mb-1.5 text-[10px]">Sub Kategori (Khusus Kejuruan)</label>
                        <select name="sub_kategori" x-model="editData.sub_kategori" class="w-full p-2.5 bg-white border border-blue-200 rounded-xl font-semibold text-slate-700 text-xs focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Tidak Ada / Default --</option>
                            <option value="Dasar-dasar Program Keahlian">1. Dasar-dasar Program Keahlian</option>
                            <option value="Mata Pelajaran [Konsentrasi Keahlian]***">2. Mata Pelajaran [Konsentrasi Keahlian]</option>
                            <option value="Mata Pelajaran Pilihan****">3. Mata Pelajaran Pilihan</option>
                            <option value="Projek Kreatif dan Kewirausahaan">4. Projek Kreatif dan Kewirausahaan</option>
                        </select>
                    </div>
                    <div class="col-span-1 sm:col-span-2 text-[10px] text-blue-600 leading-tight">
                        <i class="bi-info-circle-fill"></i> Mengatur letak tabel saat dicetak. Kosongkan jika ingin mengikuti pengaturan Master Mapel.
                    </div>
                </div>

                <!-- Alokasi Jam (JP / Minggu) -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Beban Alokasi Jam (JP per Minggu) <span class="text-rose-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="alokasi_jam" x-model.number="editData.alokasi_jam" min="1" max="24" required class="w-24 p-3 bg-slate-50 border border-slate-300 rounded-xl text-center font-black text-slate-900 text-sm focus:ring-blue-500 focus:border-blue-500">
                        <span class="font-bold text-slate-500">Jam Pelajaran (JP) / Minggu</span>
                    </div>
                </div>

                <!-- Keterangan Tambahan -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan</label>
                    <input type="text" name="keterangan" x-model="editData.keterangan" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="editModal = false" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-xl shadow-md transition flex items-center gap-2">
                        <i class="bi-check-lg"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 3: IMPORT DATA KURIKULUM (CSV)       -->
    <!-- ========================================== -->
    <div id="modalImportKurikulum" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative my-8">
            <button onclick="document.getElementById('modalImportKurikulum').classList.add('hidden')" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <div class="mb-5">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-teal-50 text-teal-700 text-[11px] font-bold border border-teal-200 mb-1.5">
                    <i class="bi-file-earmark-arrow-up-fill"></i>
                    <span>Unggah File Spreadsheet</span>
                </div>
                <h3 class="text-xl font-black text-slate-900">Import Alokasi Kurikulum</h3>
                <p class="text-xs text-slate-500 mt-0.5">Unggah berkas CSV alokasi mata pelajaran, guru, dan JP untuk diproses otomatis.</p>
            </div>

            <!-- Download Template Box -->
            <div class="p-4 bg-teal-50/70 border border-teal-200 rounded-2xl mb-5 flex items-center justify-between">
                <div>
                    <h4 class="font-bold text-teal-900 text-xs">Unduh Format Template</h4>
                    <p class="text-[11px] text-teal-700 mt-0.5">Gunakan format kolom baku agar proses import lancar.</p>
                </div>
                <a href="{{ route('admin.kurikulum.template') }}" class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-xs shadow transition flex items-center gap-1.5">
                    <i class="bi-download"></i> Template CSV
                </a>
            </div>

            <form action="{{ route('admin.kurikulum.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pilih File CSV / Excel <span class="text-rose-500">*</span></label>
                    <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-700 focus:ring-teal-500 focus:border-teal-500">
                    <span class="text-[10.5px] text-slate-400 block mt-1">Format: CSV, XLS, XLSX (Maksimal 10MB)</span>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modalImportKurikulum').classList.add('hidden')" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-black rounded-xl shadow-md transition flex items-center gap-2">
                        <i class="bi-cloud-arrow-up"></i>
                        <span>Mulai Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 4: SALIN / DUPLIKASI STRUKTUR ROMBEL -->
    <!-- ========================================== -->
    <div id="modalDuplicateKurikulum" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative my-8">
            <button onclick="document.getElementById('modalDuplicateKurikulum').classList.add('hidden')" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <div class="mb-5">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 text-[11px] font-bold border border-indigo-200 mb-1.5">
                    <i class="bi-copy"></i>
                    <span>Cepat & Efisien</span>
                </div>
                <h3 class="text-xl font-black text-slate-900">Salin Struktur Kurikulum</h3>
                <p class="text-xs text-slate-500 mt-0.5">Duplikasi daftar mata pelajaran dari satu kelas ke kelas lainnya tanpa perlu menginput ulang satu per satu.</p>
            </div>

            <form action="{{ route('admin.kurikulum.duplicate') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kelas Sumber (Yang Ditiru) <span class="text-rose-500">*</span></label>
                    <select name="from_kelas" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-900">
                        <option value="">-- Pilih Kelas Sumber --</option>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->nama }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kelas Tujuan (Yang Menerima) <span class="text-rose-500">*</span></label>
                    <select name="to_kelas" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-indigo-900">
                        <option value="">-- Pilih Kelas Tujuan --</option>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->nama }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                        <input type="checkbox" name="with_guru" value="1" checked class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                        <span>Sertakan juga guru pengampu yang sama</span>
                    </label>
                    <p class="text-[10.5px] text-slate-400 ml-6 mt-0.5">Jika dicentang, guru pengampu di kelas sumber akan disalin ke kelas tujuan.</p>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modalDuplicateKurikulum').classList.add('hidden')" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl shadow-md transition flex items-center gap-2">
                        <i class="bi-check-all text-base"></i>
                        <span>Proses Duplikasi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 5: TAMBAH MAPEL MANUAL (Dari Master) -->
    <!-- ========================================== -->
    <div id="modalTambahMapel" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative my-8">
            <button onclick="document.getElementById('modalTambahMapel').classList.add('hidden')" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <div class="mb-5">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-bold border border-emerald-200 mb-1.5">
                    <i class="bi-plus-circle"></i>
                    <span>Master Data</span>
                </div>
                <h3 class="text-xl font-black text-slate-900">Tambah Mapel Master</h3>
                <p class="text-xs text-slate-500 mt-0.5">Tambah data Mata Pelajaran baru ke dalam master sistem.</p>
            </div>

            <form action="{{ route('admin.mapel.store') }}" method="POST" class="space-y-4 text-xs">
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

                <!-- Pengelompokan Cetak Kurikulum -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-indigo-50 border border-indigo-100 rounded-xl">
                    <div>
                        <label class="block font-bold text-indigo-900 uppercase tracking-wider mb-1 text-[10px]">Kategori (Kelompok Mapel Cetak)</label>
                        <select name="kategori" class="w-full px-3.5 py-2.5 bg-white border border-indigo-200 rounded-xl font-semibold text-slate-700 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="A. KELOMPOK MATA PELAJARAN UMUM">A. KELOMPOK MATA PELAJARAN UMUM</option>
                            <option value="B. KELOMPOK MATA PELAJARAN KEJURUAN">B. KELOMPOK MATA PELAJARAN KEJURUAN</option>
                            <option value="C. KELOMPOK MATA PELAJARAN MUATAN LOKAL">C. KELOMPOK MATA PELAJARAN MUATAN LOKAL</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-indigo-900 uppercase tracking-wider mb-1 text-[10px]">Sub Kategori (Khusus Kejuruan)</label>
                        <select name="sub_kategori" class="w-full px-3.5 py-2.5 bg-white border border-indigo-200 rounded-xl font-semibold text-slate-700 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Tidak Ada / Default --</option>
                            <option value="Dasar-dasar Program Keahlian">1. Dasar-dasar Program Keahlian</option>
                            <option value="Mata Pelajaran [Konsentrasi Keahlian]***">2. Mata Pelajaran [Konsentrasi Keahlian]</option>
                            <option value="Mata Pelajaran Pilihan****">3. Mata Pelajaran Pilihan</option>
                            <option value="Projek Kreatif dan Kewirausahaan">4. Projek Kreatif dan Kewirausahaan</option>
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
                    <button type="button" onclick="document.getElementById('modalTambahMapel').classList.add('hidden')" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl shadow-md transition flex items-center gap-2">
                        <i class="bi-check-lg text-base"></i>
                        <span>Simpan Mapel Baru</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 6: IMPORT MAPEL MASTER               -->
    <!-- ========================================== -->
    <div id="modalImportMapel" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative my-8">
            <button onclick="document.getElementById('modalImportMapel').classList.add('hidden')" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <div class="mb-5">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-teal-50 text-teal-700 text-[11px] font-bold border border-teal-200 mb-1.5">
                    <i class="bi-file-earmark-arrow-up-fill"></i>
                    <span>Master Mapel</span>
                </div>
                <h3 class="text-xl font-black text-slate-900">Import Master Mapel</h3>
                <p class="text-xs text-slate-500 mt-0.5">Unggah berkas CSV/Excel untuk menambahkan banyak mapel master sekaligus.</p>
            </div>

            <div class="p-4 bg-teal-50 border border-teal-200 rounded-2xl text-xs text-teal-900 space-y-3 mb-4">
                <div class="font-bold flex items-center gap-1.5 text-teal-800">
                    <i class="bi-info-circle-fill"></i>
                    <span>Panduan Urutan Kolom Template:</span>
                </div>
                <div class="grid grid-cols-5 gap-1 text-[10px] text-center">
                    <div class="bg-teal-600 text-white rounded-lg py-2 px-1 font-bold">
                        <div class="text-teal-200 text-[9px] mb-0.5">Kolom A</div>Kode Mapel
                    </div>
                    <div class="bg-teal-600 text-white rounded-lg py-2 px-1 font-bold">
                        <div class="text-teal-200 text-[9px] mb-0.5">Kolom B</div>Nama Mapel
                    </div>
                    <div class="bg-teal-500 text-white rounded-lg py-2 px-1 font-bold">
                        <div class="text-teal-200 text-[9px] mb-0.5">Kolom C</div>Kelompok
                    </div>
                    <div class="bg-teal-500 text-white rounded-lg py-2 px-1 font-bold">
                        <div class="text-teal-200 text-[9px] mb-0.5">Kolom D</div>Tingkat
                    </div>
                    <div class="bg-teal-500 text-white rounded-lg py-2 px-1 font-bold">
                        <div class="text-teal-200 text-[9px] mb-0.5">Kolom E</div>JP/Minggu
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <a href="{{ route('admin.mapel.template', ['format' => 'xlsx']) }}" class="px-3 py-1.5 bg-teal-700 hover:bg-teal-800 text-white font-bold rounded-lg inline-flex items-center gap-1 transition shadow-xs text-xs">
                        <i class="bi-download"></i> Template Excel
                    </a>
                </div>
            </div>

            <form action="{{ route('admin.mapel.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih Berkas Excel / CSV</label>
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl cursor-pointer">
                </div>

                <div class="p-3 bg-amber-50/50 border border-amber-200/50 rounded-xl">
                    <label class="block font-bold text-amber-800 uppercase tracking-wider mb-2">Jika Data Mapel Sudah Ada:</label>
                    <select name="duplicate_action" class="w-full p-2.5 bg-white border border-amber-300 rounded-xl text-xs font-semibold text-slate-700 cursor-pointer">
                        <option value="update">Timpa / Update data yang sudah ada (Default)</option>
                        <option value="add_new">Tambah sebagai data baru (Kode menyesuaikan)</option>
                        <option value="skip">Lewati (Abaikan baris tersebut)</option>
                    </select>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modalImportMapel').classList.add('hidden')" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-black rounded-xl shadow-md transition flex items-center gap-2">
                        <i class="bi-upload text-base"></i>
                        <span>Mulai Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
