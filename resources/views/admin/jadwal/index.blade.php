@extends('layouts.app')
@section('title', 'Atur Jadwal Pelajaran')

@section('content')
<div class="space-y-6" x-data="{ 
    tambahModal: {{ request('tambah') || (request('jam_ke_mulai') && !request('edit')) ? 'true' : 'false' }}, 
    importModal: false,
    editModal: {{ (!empty($editItem)) ? 'true' : 'false' }},
    editData: {
        id: {{ !empty($editItem) ? $editItem->id : 'null' }},
        hari: '{{ !empty($editItem) ? $editItem->hari : 'Senin' }}',
        jam_ke_mulai: {{ !empty($editItem) ? ($editItem->jam_ke_mulai ?? 1) : 1 }},
        jam_ke_selesai: {{ !empty($editItem) ? ($editItem->jam_ke_selesai ?? 1) : 2 }},
        kelas: '{{ !empty($editItem) ? $editItem->kelas : '' }}',
        mata_pelajaran_id: {{ !empty($editItem) ? $editItem->mata_pelajaran_id : "''" }},
        guru_user_id: {{ !empty($editItem) ? ($editItem->guru_user_id ?? "''") : "''" }},
        ruang: '{{ !empty($editItem) ? ($editItem->ruang ?? '') : '' }}',
        actionUrl: '{{ !empty($editItem) ? url(request()->segment(1) . '/jadwal/' . $editItem->id) : '' }}'
    },
    openEditModal(item) {
        this.editData = {
            id: item.id,
            hari: item.hari,
            jam_ke_mulai: parseInt(item.jam_ke_mulai) || 1,
            jam_ke_selesai: parseInt(item.jam_ke_selesai) || parseInt(item.jam_ke_mulai) || 1,
            kelas: item.kelas,
            mata_pelajaran_id: item.mata_pelajaran_id,
            guru_user_id: item.guru_user_id || '',
            ruang: item.ruang || '',
            actionUrl: '{{ url(request()->segment(1) . '/jadwal') }}/' + item.id,
            inputMode: 'single',
            sesiList: [{
                hari: item.hari,
                jam_ke_mulai: parseInt(item.jam_ke_mulai) || 1,
                jam_ke_selesai: parseInt(item.jam_ke_selesai) || parseInt(item.jam_ke_mulai) || 1
            }]
        };
        this.editModal = true;
    },
    tambahSesiEdit() {
        let lastHari = this.editData.sesiList.length > 0 ? this.editData.sesiList[this.editData.sesiList.length - 1].hari : 'Senin';
        let hariOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        let nextIdx = (hariOrder.indexOf(lastHari) + 1) % hariOrder.length;
        this.editData.sesiList.push({
            hari: hariOrder[nextIdx],
            jam_ke_mulai: 1,
            jam_ke_selesai: 2
        });
    },
    hapusSesiEdit(idx) {
        if (this.editData.sesiList.length > 1) {
            this.editData.sesiList.splice(idx, 1);
        }
    },
    get editTotalJpMulti() {
        return this.editData.sesiList.reduce((sum, s) => {
            let m = parseInt(s.jam_ke_mulai) || 1;
            let e = parseInt(s.jam_ke_selesai) || m;
            return sum + (e >= m ? (e - m + 1) : 1);
        }, 0);
    },
    onEditMapelChange(e) {
        let mapelId = parseInt(e.target.value);
        if (!mapelId) return;
        if (this.editData.kelas && this.kurikulumData[this.editData.kelas]) {
            let match = this.kurikulumData[this.editData.kelas].find(k => k.mata_pelajaran_id === mapelId);
            if (match && match.guru_user_id) {
                this.editData.guru_user_id = match.guru_user_id;
            }
        }
    },
    get editMaxPeriod() {
        return this.editData.hari === 'Jumat' ? 6 : 8;
    },
    get editWaktuKbm() {
        let map = this.editData.hari === 'Jumat' ? this.periodsJumat : this.periodsReguler;
        let maxP = this.editMaxPeriod;
        let m = Math.max(1, Math.min(parseInt(this.editData.jam_ke_mulai) || 1, maxP));
        let s = Math.max(m, Math.min(parseInt(this.editData.jam_ke_selesai) || m, maxP));
        let start = (map[m] || '07.00').split(' - ')[0];
        let end = (map[s] || '14.00').split(' - ')[1];
        let count = (s - m + 1);
        return `${start} s.d. ${end} (${count} Jam Pelajaran)`;
    },
    get editKurikulumMapels() {
        if (!this.editData.kelas || !this.kurikulumData[this.editData.kelas]) {
            return [];
        }
        return this.kurikulumData[this.editData.kelas];
    },
    pickEditKurikulum(item) {
        this.editData.mata_pelajaran_id = item.mata_pelajaran_id;
        if (item.guru_user_id) {
            this.editData.guru_user_id = item.guru_user_id;
        }
    },
    inputMode: '{{ request('jam_ke_mulai') ? 'single' : 'multi' }}',
    selectedHari: '{{ request('hari', 'Senin') }}',
    jamKeMulai: {{ request('jam_ke_mulai', 1) }},
    jamKeSelesai: {{ request('jam_ke_selesai', request('jam_ke_mulai', 2)) }},
    selectedKelas: '{{ request('kelas', '') }}',
    selectedMapelId: '',
    selectedGuruId: '',
    targetJp: 6,
    mapelsData: {{ json_encode($mapels->keyBy('id')) }},
    kurikulumData: {{ json_encode($kurikulumByKelas ?? []) }},
    activeKurikulumInfo: null,
    get activeKurikulumMapels() {
        if (!this.selectedKelas || !this.kurikulumData[this.selectedKelas]) {
            return [];
        }
        return this.kurikulumData[this.selectedKelas];
    },
    pickKurikulum(item) {
        this.selectedMapelId = item.mata_pelajaran_id;
        this.selectedGuruId = item.guru_user_id || '';
        this.targetJp = item.alokasi_jam || 4;
        this.activeKurikulumInfo = item;
    },
    
    // Multi Sesi (Pecah Jam)
    sesiList: [
        { hari: '{{ request('hari', 'Senin') }}', jam_ke_mulai: {{ request('jam_ke_mulai', 1) }}, jam_ke_selesai: {{ request('jam_ke_selesai', request('jam_ke_mulai') ? request('jam_ke_mulai') + 1 : 2) }} },
        { hari: 'Selasa', jam_ke_mulai: 3, jam_ke_selesai: 6 }
    ],

    tambahSesi() {
        let lastHari = this.sesiList.length > 0 ? this.sesiList[this.sesiList.length - 1].hari : 'Senin';
        let hariOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        let nextIdx = (hariOrder.indexOf(lastHari) + 1) % hariOrder.length;
        this.sesiList.push({
            hari: hariOrder[nextIdx],
            jam_ke_mulai: 1,
            jam_ke_selesai: 2
        });
    },

    hapusSesi(idx) {
        if (this.sesiList.length > 1) {
            this.sesiList.splice(idx, 1);
        }
    },

    applyPreset(preset) {
        if (preset === '2_4') {
            this.targetJp = 6;
            this.sesiList = [
                { hari: 'Senin', jam_ke_mulai: 1, jam_ke_selesai: 2 },
                { hari: 'Selasa', jam_ke_mulai: 3, jam_ke_selesai: 6 }
            ];
        } else if (preset === '4_2') {
            this.targetJp = 6;
            this.sesiList = [
                { hari: 'Senin', jam_ke_mulai: 1, jam_ke_selesai: 4 },
                { hari: 'Kamis', jam_ke_mulai: 1, jam_ke_selesai: 2 }
            ];
        } else if (preset === '3_3') {
            this.targetJp = 6;
            this.sesiList = [
                { hari: 'Senin', jam_ke_mulai: 1, jam_ke_selesai: 3 },
                { hari: 'Rabu', jam_ke_mulai: 1, jam_ke_selesai: 3 }
            ];
        } else if (preset === '2_2_2') {
            this.targetJp = 6;
            this.sesiList = [
                { hari: 'Senin', jam_ke_mulai: 1, jam_ke_selesai: 2 },
                { hari: 'Rabu', jam_ke_mulai: 1, jam_ke_selesai: 2 },
                { hari: 'Kamis', jam_ke_mulai: 1, jam_ke_selesai: 2 }
            ];
        } else if (preset === '2_2') {
            this.targetJp = 4;
            this.sesiList = [
                { hari: 'Senin', jam_ke_mulai: 1, jam_ke_selesai: 2 },
                { hari: 'Kamis', jam_ke_mulai: 1, jam_ke_selesai: 2 }
            ];
        }
    },

    onMapelSelect(e) {
        let id = e.target.value;
        this.selectedMapelId = id;
        if (this.mapelsData[id] && this.mapelsData[id].jam_per_minggu) {
            this.targetJp = this.mapelsData[id].jam_per_minggu;
        }
    },

    get totalJpMulti() {
        return this.sesiList.reduce((sum, s) => {
            let m = parseInt(s.jam_ke_mulai) || 1;
            let e = parseInt(s.jam_ke_selesai) || m;
            return sum + (e >= m ? (e - m + 1) : 1);
        }, 0);
    },

    getWaktuSesi(hari, m, s) {
        let map = (hari === 'Jumat') ? this.periodsJumat : this.periodsReguler;
        let maxP = (hari === 'Jumat') ? 6 : 8;
        m = Math.max(1, Math.min(parseInt(m) || 1, maxP));
        s = Math.max(m, Math.min(parseInt(s) || m, maxP));
        let start = (map[m] || '07.00').split(' - ')[0];
        let end = (map[s] || '14.00').split(' - ')[1];
        let count = (s - m + 1);
        return `${start} s.d. ${end} (${count} JP)`;
    },

    periodsReguler: {
        1: '07.00 - 07.45', 2: '07.45 - 08.30', 3: '08.30 - 09.15', 4: '09.15 - 10.00',
        5: '10.30 - 11.15', 6: '11.15 - 12.00', 7: '12.30 - 13.15', 8: '13.15 - 14.00'
    },
    periodsJumat: {
        1: '07.00 - 07.30', 2: '07.30 - 08.00', 3: '08.00 - 08.30', 4: '08.30 - 09.00',
        5: '09.30 - 10.00', 6: '10.00 - 10.30'
    },
    get maxPeriod() {
        return this.selectedHari === 'Jumat' ? 6 : 8;
    },
    get waktuKbm() {
        let map = this.selectedHari === 'Jumat' ? this.periodsJumat : this.periodsReguler;
        let m = Math.min(this.jamKeMulai, this.maxPeriod);
        let s = Math.min(Math.max(this.jamKeSelesai, m), this.maxPeriod);
        let start = (map[m] || '07.00').split(' - ')[0];
        let end = (map[s] || '14.00').split(' - ')[1];
        let count = (s - m + 1);
        return `${start} s.d. ${end} (${count} Jam Pelajaran)`;
    },
    selected: [],
    allIds: {{ json_encode($jadwals->pluck('id')->all()) }},
    get allSelected() {
        return this.allIds.length > 0 && this.selected.length === this.allIds.length;
    },
    toggleAll() {
        if (this.allSelected) {
            this.selected = [];
        } else {
            this.selected = [...this.allIds];
        }
    }
}">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-calendar2-range text-blue-600"></i>
                <span>Pengaturan Master Jadwal Pelajaran</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Input jadwal mengajar guru berdasarkan Jam ke-1 s.d. Jam ke-8 (Senin-Sabtu) & Jam ke-1 s.d. Jam ke-6 (Jumat)</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <!-- Export Dropdown / Buttons -->
            <div class="flex items-center rounded-xl bg-emerald-50 border border-emerald-200 overflow-hidden text-xs font-bold text-emerald-800">
                <span class="px-2.5 py-2 flex items-center gap-1.5 border-r border-emerald-200">
                    <i class="bi-download"></i> Export:
                </span>
                <a href="{{ route('akademik.jadwal.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}" class="px-2.5 py-2 hover:bg-emerald-100 transition" title="Export ke Excel XLSX">
                    Excel (.xlsx)
                </a>
                <a href="{{ route('akademik.jadwal.export', array_merge(request()->query(), ['format' => 'csv'])) }}" class="px-2.5 py-2 hover:bg-emerald-100 border-l border-emerald-200 transition" title="Export ke CSV">
                    CSV
                </a>
            </div>

            <!-- Matriks Jadwal Resmi Button -->
            <a href="{{ route('akademik.jadwal.matrix') }}" class="px-3.5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-rose-600/20 transition">
                <i class="bi-file-earmark-spreadsheet-fill text-sm"></i>
                <span>Matriks Jadwal Resmi (Cetak)</span>
            </a>

            <!-- Import Button -->
            <button type="button" @click="importModal = true" class="px-3.5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-purple-600/20 transition">
                <i class="bi-file-earmark-arrow-up text-sm"></i>
                <span>Import Excel/CSV</span>
            </button>

            <!-- Sinkronisasi Auto Generate -->
            <form action="{{ route('akademik.jadwal.autogenerate') }}" method="POST" class="inline" onsubmit="return confirm('Proses ini akan men-generate draft jadwal (Hari Senin) secara otomatis untuk Kurikulum yang belum memiliki jadwal. Anda bisa menyesuaikan hari dan jamnya nanti. Lanjutkan?')">
                @csrf
                <button type="submit" class="px-3.5 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-cyan-600/20 transition">
                    <i class="bi-arrow-repeat text-sm"></i>
                    <span>Sinkronisasi Kurikulum</span>
                </button>
            </form>

            <!-- Tambah Button -->
            <button type="button" @click="tambahModal = true" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-blue-600/20 transition">
                <i class="bi-plus-circle text-sm"></i>
                <span>Tambah Jadwal</span>
            </button>
        </div>
    </div>

    <!-- Alert Error / Bentrok Jadwal -->
    @if(session('error'))
    <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-900 rounded-2xl shadow-sm text-xs flex items-start gap-3">
        <i class="bi-exclamation-octagon-fill text-rose-600 text-base mt-0.5 flex-shrink-0"></i>
        <div>
            <div class="font-bold mb-0.5">Peringatan Jadwal:</div>
            <div class="leading-relaxed">{!! session('error') !!}</div>
        </div>
    </div>
    @endif

    <!-- Alert Warning (Kelebihan Jam / Catatan) -->
    @if(session('warning'))
    <div class="p-4 bg-amber-50 border-l-4 border-amber-500 text-amber-900 rounded-2xl shadow-sm text-xs flex items-start gap-3">
        <i class="bi-exclamation-triangle-fill text-amber-600 text-base mt-0.5 flex-shrink-0"></i>
        <div>
            <div class="font-bold mb-0.5">Perhatian Jadwal:</div>
            <div class="leading-relaxed">{!! session('warning') !!}</div>
        </div>
    </div>
    @endif

    <!-- Banner Kelebihan Jam Mengajar Terhadap Kurikulum -->
    @if(!empty($overAllocations))
    <div class="p-4 bg-amber-50/90 border border-amber-300 rounded-3xl shadow-sm text-xs text-amber-900 space-y-2">
        <div class="flex items-center gap-2 font-black text-amber-800 text-sm">
            <i class="bi-exclamation-triangle-fill text-amber-600 text-base"></i>
            <span>Peringatan: Terdeteksi Kelebihan Jam Mengajar Terhadap Kurikulum</span>
        </div>
        <p class="text-slate-600 text-xs">
            Beberapa mata pelajaran memiliki akumulasi jam KBM terjadwal yang melampaui alokasi kurikulum yang telah ditetapkan:
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 mt-2">
            @foreach($overAllocations as $oa)
            <div class="p-3 bg-white rounded-2xl border border-amber-200 flex items-center justify-between shadow-xs">
                <div>
                    <span class="font-bold text-slate-800">{{ $oa['kelas'] }}</span> - <span class="text-slate-700 font-medium">{{ $oa['mapel'] }}</span>
                    <div class="text-[10.5px] text-slate-500 mt-0.5">Alokasi: {{ $oa['alokasi'] }} JP | Terjadwal: {{ $oa['terjadwal'] }} JP</div>
                </div>
                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 font-black text-xs rounded-xl border border-amber-200">
                    +{{ $oa['kelebihan'] }} JP
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Feedback / Import Errors Alert -->
    @if(session('import_errors'))
    <div class="p-4 bg-amber-50 border-l-4 border-amber-500 text-amber-900 rounded-2xl shadow-sm text-xs space-y-2">
        <div class="font-bold flex items-center gap-2">
            <i class="bi-exclamation-triangle-fill text-amber-600"></i>
            <span>Catatan Hasil Import:</span>
        </div>
        <ul class="list-disc list-inside space-y-1 text-slate-700 max-h-40 overflow-y-auto">
            @foreach(session('import_errors') as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4 text-xs">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div>
                <select name="hari" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="">Semua Hari</option>
                    @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h)
                    <option value="{{ $h }}" {{ $hari === $h ? 'selected' : '' }}>Hari {{ $h }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="kelas" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->nama_kelas ?? $k->nama }}" {{ $kelas === ($k->nama_kelas ?? $k->nama) ? 'selected' : '' }}>
                        Kelas {{ $k->nama_kelas ?? $k->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="guru_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="">Semua Guru</option>
                    @foreach($guruList as $g)
                    <option value="{{ $g->id }}" {{ $guruId == $g->id ? 'selected' : '' }}>
                        {{ $g->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @if($hari || $kelas || $guruId)
            <div>
                <a href="{{ route('akademik.jadwal.index') }}" class="text-xs font-bold text-rose-600 hover:underline">Reset</a>
            </div>
            @endif
        </form>
        <div class="text-slate-500">
            Total Jadwal: <strong>{{ $jadwals->total() }} Slot</strong>
        </div>
    </div>

    <!-- Selection Action Bar (Hapus Terpilih) -->
    <div x-show="selected.length > 0" x-cloak class="p-4 bg-rose-50 border-2 border-rose-200 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-3 shadow-sm transition-all">
        <div class="flex items-center gap-3">
            <span class="w-3 h-3 rounded-full bg-rose-500 animate-pulse"></span>
            <span class="text-xs font-bold text-rose-900">
                <strong class="text-sm font-black text-rose-700" x-text="selected.length"></strong> jadwal pelajaran dipilih untuk dihapus
            </span>
        </div>
        <form id="bulkDeleteJadwalForm" action="{{ route('akademik.jadwal.bulk-delete') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ' + selected.length + ' jadwal pelajaran yang dipilih? Tindakan ini tidak dapat dibatalkan.');">
            @csrf
            <button type="submit" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2">
                <i class="bi-trash3-fill text-sm"></i>
                <span>Hapus Jadwal Terpilih (<span x-text="selected.length"></span>)</span>
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                    <tr>
                        <th class="w-12 px-4 py-4 text-center">
                            <input type="checkbox" 
                                   @change="toggleAll()" 
                                   :checked="allSelected" 
                                   title="Pilih Semua di Halaman Ini"
                                   class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="px-6 py-4">Hari & Jam Pelajaran</th>
                        <th class="px-4 py-4">Kelas</th>
                        <th class="px-4 py-4">Mata Pelajaran</th>
                        <th class="px-4 py-4">Guru Pengajar</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($jadwals as $j)
                    <tr class="hover:bg-slate-50/70 transition" :class="selected.includes({{ $j->id }}) ? 'bg-rose-50/50' : ''">
                        <td class="w-12 px-4 py-4 text-center">
                            <input type="checkbox" 
                                   form="bulkDeleteJadwalForm"
                                   name="ids[]"
                                   value="{{ $j->id }}" 
                                   x-model.number="selected" 
                                   class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-extrabold text-slate-900 text-sm block">{{ $j->hari }}</span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold text-[11px]">
                                    {{ $j->jam_ke_label }}
                                </span>
                                <span class="text-slate-400 font-mono text-[10.5px]">({{ $j->waktu_formatted }})</span>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 font-bold rounded-lg border border-indigo-200 text-xs">
                                {{ $j->kelas }}
                            </span>
                        </td>
                        <td class="px-4 py-4 font-bold text-slate-900">
                            {{ $j->mataPelajaran->nama ?? 'Mapel #' . $j->mata_pelajaran_id }}
                            <span class="text-[10px] text-slate-400 font-mono">({{ $j->mataPelajaran->kode ?? '-' }})</span>
                        </td>
                        <td class="px-4 py-4 font-bold">
                            @if($j->guru)
                                <span class="text-blue-700">{{ $j->guru?->name }}</span>
                            @else
                                <span class="inline-flex items-center gap-1 text-amber-700 bg-amber-50 px-2 py-0.5 rounded text-xs font-semibold border border-amber-200">
                                    <i class="bi-exclamation-triangle"></i> Belum Ditentukan
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" 
                                        @click="openEditModal({{ json_encode([
                                            'id' => $j->id,
                                            'hari' => $j->hari,
                                            'jam_ke_mulai' => $j->jam_ke_mulai,
                                            'jam_ke_selesai' => $j->jam_ke_selesai,
                                            'kelas' => $j->kelas,
                                            'mata_pelajaran_id' => $j->mata_pelajaran_id,
                                            'guru_user_id' => $j->guru_user_id,
                                            'is_locked' => $j->is_locked,
                                            'ruang' => $j->ruang,
                                        ]) }})"
                                        class="p-2 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition cursor-pointer" 
                                        title="Edit Jadwal Ini">
                                    <i class="bi-pencil-square text-base"></i>
                                </button>
                                <form action="{{ route('akademik.jadwal.destroy', $j) }}" method="POST" onsubmit="return confirm('Hapus slot jadwal ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition cursor-pointer" title="Hapus Jadwal">
                                        <i class="bi-trash text-base"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="max-w-md mx-auto space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto text-xl shadow-xs">
                                    <i class="bi-calendar2-plus"></i>
                                </div>
                                <div class="font-bold text-slate-800 text-sm">Data Jadwal Pelajaran Masih Kosong</div>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Belum ada slot jadwal pelajaran yang diinput. Anda dapat menambahkan jadwal langsung melalui tombol di bawah atau mengambil acuan dari menu Kurikulum.
                                </p>
                                <div class="flex items-center justify-center gap-2 pt-2">
                                    <button type="button" @click="tambahModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs inline-flex items-center gap-1.5 shadow-sm transition cursor-pointer">
                                        <i class="bi-plus-circle"></i>
                                        <span>Tambah Jadwal Sekarang</span>
                                    </button>
                                    <a href="{{ route('akademik.kurikulum.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs inline-flex items-center gap-1.5 transition">
                                        <i class="bi-diagram-3"></i>
                                        <span>Lihat Master Kurikulum</span>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $jadwals->links() }}
        </div>
    </div>

    <!-- MODAL TAMBAH JADWAL (DUKUNGAN PECAH JAM / MULTI-SESI & SATU SESI) -->
    <div x-show="tambahModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto"
         style="display: none;">
        
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative my-8"
             @click.outside="tambahModal = false">
            
            <button @click="tambahModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <!-- Modal Header -->
            <div class="mb-4">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-[11px] font-bold border border-blue-200 mb-1.5">
                    <i class="bi-calendar-check-fill text-blue-600"></i>
                    <span>Penjadwalan KBM Terpadu</span>
                </div>
                <h3 class="text-xl font-black text-slate-900">Input Jadwal Pelajaran</h3>
                <p class="text-xs text-slate-500 mt-0.5">Mendukung pembagian alokasi jam mengajar guru (misal: 6 Jam dipecah Senin 2 jam & Selasa 4 jam).</p>
            </div>

            <!-- Tab Switcher Mode -->
            <div class="flex items-center p-1 bg-slate-100 rounded-2xl mb-6">
                <button type="button" @click="inputMode = 'multi'" 
                        :class="inputMode === 'multi' ? 'bg-white text-blue-700 shadow-sm font-black' : 'text-slate-500 font-bold hover:text-slate-800'"
                        class="flex-1 py-2.5 rounded-xl text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                    <i class="bi-diagram-3-fill"></i>
                    <span>Pecah Jam (Multi-Sesi)</span>
                    <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-black">Rekomendasi</span>
                </button>
                <button type="button" @click="inputMode = 'single'" 
                        :class="inputMode === 'single' ? 'bg-white text-slate-900 shadow-sm font-black' : 'text-slate-500 font-bold hover:text-slate-800'"
                        class="flex-1 py-2.5 rounded-xl text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                    <i class="bi-clock-fill"></i>
                    <span>Satu Sesi Biasa</span>
                </button>
            </div>

            <!-- FORM 1: MODE PECAH JAM (MULTI-SESI) -->
            <form x-show="inputMode === 'multi'" method="POST" action="{{ route('akademik.jadwal.store') }}" class="space-y-5 text-xs">
                @csrf

                <!-- Informasi Utama KBM (Kelas, Mapel, Guru) -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-3.5">
                    <!-- Pilihan Cepat dari Kurikulum Kelas -->
                    <div x-show="activeKurikulumMapels.length > 0" class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-2xl space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-extrabold text-emerald-900 flex items-center gap-1.5">
                                <i class="bi-journal-check text-emerald-600"></i>
                                <span>Pilih dari Kurikulum Kelas <strong x-text="selectedKelas"></strong>:</span>
                            </span>
                            <span class="text-[10.5px] text-emerald-700 font-bold" x-text="activeKurikulumMapels.length + ' Mapel Dialokasikan'"></span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 max-h-40 overflow-y-auto pr-1">
                            <template x-for="item in activeKurikulumMapels" :key="item.id">
                                <button type="button" 
                                        @click="pickKurikulum(item)"
                                        :class="selectedMapelId == item.mata_pelajaran_id ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-400 font-bold' : 'bg-white hover:bg-emerald-100/60 text-slate-800 border border-slate-200'"
                                        class="p-2 rounded-xl text-left transition flex items-start justify-between gap-2 cursor-pointer">
                                    <div class="min-w-0">
                                        <div class="font-bold text-[11px] truncate" x-text="item.mapel_nama"></div>
                                        <div class="text-[10px] mt-0.5 truncate" :class="selectedMapelId == item.mata_pelajaran_id ? 'text-emerald-100' : 'text-slate-500'">
                                            <span x-text="item.guru_nama"></span>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-black" 
                                              :class="item.status_jadwal === 'lengkap' ? 'bg-emerald-100 text-emerald-800' : (item.status_jadwal === 'sebagian' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700')" 
                                              x-text="item.terjadwal_jp + '/' + item.alokasi_jam + ' JP'">
                                        </span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Kelas -->
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Kelas <span class="text-rose-500">*</span></label>
                            <select name="kelas" x-model="selectedKelas" required class="w-full p-2.5 bg-white border border-slate-300 rounded-xl font-bold text-slate-900">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelasList as $k)
                                <option value="{{ $k->nama_kelas ?? $k->nama }}">{{ $k->nama_kelas ?? $k->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Mata Pelajaran -->
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                            <select name="mata_pelajaran_id" x-model="selectedMapelId" @change="onMapelSelect($event)" required class="w-full p-2.5 bg-white border border-slate-300 rounded-xl font-bold text-slate-900">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($mapels as $m)
                                <option value="{{ $m->id }}">
                                    {{ $m->nama }} ({{ $m->kode }}) - {{ $m->jam_per_minggu ?? 4 }} JP
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Guru Pengajar -->
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Guru Pengajar <span class="text-rose-500">*</span></label>
                        <select name="guru_user_id" x-model="selectedGuruId" required class="w-full p-2.5 bg-white border border-slate-300 rounded-xl font-black text-blue-800">
                            <option value="">-- Pilih Guru Pengajar --</option>
                            @foreach($guruList as $g)
                            <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->jabatan_utama ?: 'Guru' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Target Alokasi JP & Preset Cepat -->
                    <div class="pt-2 border-t border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-700">Target Alokasi Jam:</span>
                            <div class="inline-flex items-center gap-1.5">
                                <input type="number" x-model.number="targetJp" min="1" max="16" class="w-16 p-1.5 bg-white border border-slate-300 rounded-lg text-center font-black text-blue-700">
                                <span class="font-bold text-slate-500">JP / Minggu</span>
                            </div>
                        </div>
                        <!-- Tombol Preset -->
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] font-bold uppercase text-slate-400">Preset:</span>
                            <button type="button" @click="applyPreset('2_4')" class="px-2 py-1 bg-white hover:bg-blue-50 text-blue-700 font-bold border border-blue-200 rounded-lg text-[10.5px] transition cursor-pointer" title="Senin 2 Jam + Selasa 4 Jam">
                                2 + 4 JP
                            </button>
                            <button type="button" @click="applyPreset('3_3')" class="px-2 py-1 bg-white hover:bg-blue-50 text-blue-700 font-bold border border-blue-200 rounded-lg text-[10.5px] transition cursor-pointer" title="Senin 3 Jam + Rabu 3 Jam">
                                3 + 3 JP
                            </button>
                            <button type="button" @click="applyPreset('2_2_2')" class="px-2 py-1 bg-white hover:bg-blue-50 text-blue-700 font-bold border border-blue-200 rounded-lg text-[10.5px] transition cursor-pointer" title="Senin 2 Jam + Rabu 2 Jam + Kamis 2 Jam">
                                2 + 2 + 2 JP
                            </button>
                            <button type="button" @click="applyPreset('2_2')" class="px-2 py-1 bg-white hover:bg-blue-50 text-blue-700 font-bold border border-blue-200 rounded-lg text-[10.5px] transition cursor-pointer" title="Senin 2 Jam + Kamis 2 Jam">
                                2 + 2 JP
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Bagian Daftar Sesi Pecahan Jam KBM -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="bi-layers-fill text-blue-600"></i>
                            <span>Distribusi Sesi KBM (Pecah Jam Mengajar):</span>
                        </label>
                        <button type="button" @click="tambahSesi()" class="px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-lg text-[11px] border border-blue-200 transition inline-flex items-center gap-1 cursor-pointer">
                            <i class="bi-plus-circle-fill"></i>
                            <span>Tambah Sesi Hari Lain</span>
                        </button>
                    </div>

                    <!-- List Baris Sesi -->
                    <div class="space-y-2.5">
                        <template x-for="(sesi, index) in sesiList" :key="'sesi-' + index">
                            <div class="p-3 bg-white rounded-2xl border-2 border-slate-200 hover:border-blue-300 transition space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 font-black text-[11px]">
                                        <span x-text="'Sesi ' + (index + 1)"></span>
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-slate-700 text-[11px]" x-text="getWaktuSesi(sesi.hari, sesi.jam_ke_mulai, sesi.jam_ke_selesai)"></span>
                                        <button type="button" @click="hapusSesi(index)" x-show="sesiList.length > 1" class="text-rose-500 hover:text-rose-700 p-1 cursor-pointer" title="Hapus sesi ini">
                                            <i class="bi-trash3-fill"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
                                    <!-- Hari -->
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Hari</label>
                                        <select :name="'sesi[' + index + '][hari]'" x-model="sesi.hari" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800">
                                            @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h)
                                            <option value="{{ $h }}">Hari {{ $h }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Dari Jam Ke -->
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Dari Jam Ke</label>
                                        <select :name="'sesi[' + index + '][jam_ke_mulai]'" x-model.number="sesi.jam_ke_mulai" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800">
                                            @for($i = 1; $i <= 8; $i++)
                                            <option :value="{{ $i }}" {{ $i > 6 ? 'x-show="sesi.hari !== \'Jumat\'"' : '' }}>Jam ke-{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <!-- Sampai Jam Ke -->
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Sampai Jam Ke</label>
                                        <select :name="'sesi[' + index + '][jam_ke_selesai]'" x-model.number="sesi.jam_ke_selesai" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800">
                                            @for($i = 1; $i <= 8; $i++)
                                            <option :value="{{ $i }}" :disabled="sesi.jam_ke_mulai > {{ $i }}" {{ $i > 6 ? 'x-show="sesi.hari !== \'Jumat\'"' : '' }}>Jam ke-{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Live Indicator Total Jam Terbagi vs Target -->
                <div class="p-3.5 rounded-2xl border flex items-center justify-between"
                     :class="totalJpMulti === targetJp ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : (totalJpMulti < targetJp ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-rose-50 border-rose-200 text-rose-900')">
                    <div class="flex items-center gap-2">
                        <i :class="totalJpMulti === targetJp ? 'bi-check-circle-fill text-emerald-600' : (totalJpMulti < targetJp ? 'bi-exclamation-circle-fill text-amber-600' : 'bi-exclamation-triangle-fill text-rose-600')" class="text-lg"></i>
                        <div>
                            <div class="font-extrabold" x-text="'Total Terbagi: ' + totalJpMulti + ' dari ' + targetJp + ' JP'"></div>
                            <div class="text-[11px] font-medium" x-text="totalJpMulti === targetJp ? 'Alokasi jam pas sesuai target minggu.' : (totalJpMulti < targetJp ? 'Masih kurang ' + (targetJp - totalJpMulti) + ' JP dari target.' : 'Melebihi target ' + (totalJpMulti - targetJp) + ' JP.')"></div>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-xl text-xs font-black"
                          :class="totalJpMulti === targetJp ? 'bg-emerald-200 text-emerald-900' : (totalJpMulti < targetJp ? 'bg-amber-200 text-amber-900' : 'bg-rose-200 text-rose-900')"
                          x-text="totalJpMulti + ' JP'"></span>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" @click="tambahModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold rounded-xl transition shadow-lg shadow-blue-600/30 inline-flex items-center gap-1.5 cursor-pointer">
                        <i class="bi-floppy2-fill"></i>
                        <span x-text="'Simpan Semua Sesi (' + totalJpMulti + ' JP)'"></span>
                    </button>
                </div>
            </form>

            <!-- FORM 2: MODE SATU SESI BIASA -->
            <form x-show="inputMode === 'single'" method="POST" action="{{ route('akademik.jadwal.store') }}" class="space-y-4 text-xs">
                @csrf

                <!-- Hari -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Hari</label>
                    <select name="hari" x-model="selectedHari" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                        @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h)
                        <option value="{{ $h }}">Hari {{ $h }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Dari Jam Ke s.d. Sampai Jam Ke -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Dari Jam Ke</label>
                        <select name="jam_ke_mulai" x-model.number="jamKeMulai" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                            @for($i = 1; $i <= 8; $i++)
                            <option :value="{{ $i }}" {{ $i > 6 ? 'x-show="selectedHari !== \'Jumat\'"' : '' }}>Jam ke-{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Sampai Jam Ke</label>
                        <select name="jam_ke_selesai" x-model.number="jamKeSelesai" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                            @for($i = 1; $i <= 8; $i++)
                            <option :value="{{ $i }}" :disabled="jamKeMulai > {{ $i }}" {{ $i > 6 ? 'x-show="selectedHari !== \'Jumat\'"' : '' }}>Jam ke-{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <!-- Estimasi Waktu Otomatis -->
                <div class="p-3 bg-blue-50/80 border border-blue-200 rounded-2xl text-blue-900 text-xs flex items-center justify-between">
                    <span class="font-bold flex items-center gap-1.5">
                        <i class="bi-clock-fill text-blue-600"></i>
                        <span>Estimasi Waktu:</span>
                    </span>
                    <span class="font-mono font-black text-blue-700" x-text="waktuKbm"></span>
                </div>

                <!-- Kelas (Dari Database) -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Kelas</label>
                    <select name="kelas" x-model="selectedKelas" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->nama_kelas ?? $k->nama }}">{{ $k->nama_kelas ?? $k->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Pilihan Cepat dari Kurikulum Kelas -->
                <div x-show="activeKurikulumMapels.length > 0" class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-2xl space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-extrabold text-emerald-900 flex items-center gap-1.5">
                            <i class="bi-journal-check text-emerald-600"></i>
                            <span>Pilih dari Kurikulum Kelas <strong x-text="selectedKelas"></strong>:</span>
                        </span>
                        <span class="text-[10.5px] text-emerald-700 font-bold" x-text="activeKurikulumMapels.length + ' Mapel'"></span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 max-h-36 overflow-y-auto pr-1">
                        <template x-for="item in activeKurikulumMapels" :key="'single-' + item.id">
                            <button type="button" 
                                    @click="pickKurikulum(item)"
                                    :class="selectedMapelId == item.mata_pelajaran_id ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-400 font-bold' : 'bg-white hover:bg-emerald-100/60 text-slate-800 border border-slate-200'"
                                    class="p-2 rounded-xl text-left transition flex items-start justify-between gap-2 cursor-pointer">
                                <div class="min-w-0">
                                    <div class="font-bold text-[11px] truncate" x-text="item.mapel_nama"></div>
                                    <div class="text-[10px] mt-0.5 truncate" :class="selectedMapelId == item.mata_pelajaran_id ? 'text-emerald-100' : 'text-slate-500'">
                                        <span x-text="item.guru_nama"></span>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-black" 
                                          :class="item.status_jadwal === 'lengkap' ? 'bg-emerald-100 text-emerald-800' : (item.status_jadwal === 'sebagian' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700')" 
                                          x-text="item.terjadwal_jp + '/' + item.alokasi_jam + ' JP'">
                                    </span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Mata Pelajaran -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Mata Pelajaran</label>
                    <select name="mata_pelajaran_id" x-model="selectedMapelId" @change="onMapelSelect($event)" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($mapels as $m)
                        <option value="{{ $m->id }}">{{ $m->nama }} ({{ $m->kode }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Guru Pengajar -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Guru Pengajar</label>
                    <select name="guru_user_id" x-model="selectedGuruId" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold text-blue-800">
                        <option value="">-- Pilih Guru Pengajar --</option>
                        @foreach($guruList as $g)
                        <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->jabatan_utama ?: 'Guru' }})</option>
                        @endforeach
                    </select>
                </div>

                                  <!-- is_locked Checkbox -->
                  <div class="flex items-center gap-2 mt-4 bg-yellow-50 p-3 rounded-xl border border-yellow-200 mb-2">
                      <input type="hidden" name="is_locked" value="0">
                      <input type="checkbox" name="is_locked" id="is_locked_tambah" value="1" class="w-5 h-5 text-yellow-600 rounded border-yellow-300 focus:ring-yellow-500">
                      <label for="is_locked_tambah" class="font-bold text-yellow-800 text-sm cursor-pointer">Kunci Jadwal Ini (Prioritas)</label>
                      <p class="text-[10px] text-yellow-700 ml-auto max-w-[200px] text-right">Mencegah jadwal diubah oleh fitur Acak Otomatis.</p>
                  </div>

                  <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-[11px] text-slate-500">
                    <i class="bi-info-circle text-slate-400 mr-1"></i>
                    <span>Tahun ajaran & semester aktif otomatis disinkronkan dari pengaturan sekolah. Ruangan tidak diperlukan.</span>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" @click="tambahModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition shadow-md shadow-blue-600/30 cursor-pointer">
                        + Simpan Jadwal Baru
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Modal Import Jadwal Pelajaran -->
    <div x-show="importModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="importModal = false" class="bg-white max-w-lg w-full rounded-3xl p-6 shadow-2xl space-y-5">
            
            <div class="flex items-center justify-between border-b pb-3">
                <div class="flex items-center gap-2 text-slate-900 font-black text-lg">
                    <i class="bi-file-earmark-excel text-emerald-600 text-xl"></i>
                    <span>Import Jadwal Pelajaran</span>
                </div>
                <button type="button" @click="importModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="bi-x-lg text-lg"></i>
                </button>
            </div>

            <!-- Download Template Callout -->
            <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 text-xs text-emerald-900 space-y-2">
                <div class="font-bold flex items-center gap-1.5 text-emerald-800">
                    <i class="bi-info-circle-fill"></i>
                    <span>Unduh Template Format:</span>
                </div>
                <p class="text-[11px] leading-relaxed text-emerald-700">
                    Gunakan template resmi untuk mengimport file jadwal pelajaran massal.
                </p>
                <div class="flex items-center gap-2 pt-1">
                    <a href="{{ route('akademik.jadwal.template', ['format' => 'xlsx']) }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg inline-flex items-center gap-1 transition shadow-xs">
                        <i class="bi-download"></i> Template Excel (.xlsx)
                    </a>
                    <a href="{{ route('akademik.jadwal.template', ['format' => 'csv']) }}" class="px-3 py-1.5 bg-white hover:bg-slate-50 text-emerald-800 font-bold border border-emerald-300 rounded-lg inline-flex items-center gap-1 transition">
                        <i class="bi-filetype-csv"></i> Template CSV
                    </a>
                </div>
            </div>

            <form action="{{ route('akademik.jadwal.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih File Spreadsheet</label>
                    <input type="file" name="file" required accept=".xlsx,.xls,.csv" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="importModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow-md shadow-emerald-600/30">
                        Unggah & Import
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- MODAL EDIT JADWAL -->
    <div x-show="editModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
         style="display: none;">
        
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative my-8"
             @click.outside="editModal = false">
            
            <button @click="editModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition cursor-pointer">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <!-- Modal Header -->
            <div class="mb-5">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-[11px] font-bold border border-blue-200 mb-1.5">
                    <i class="bi-pencil-square text-blue-600"></i>
                    <span>Modifikasi Slot Jadwal</span>
                </div>
                <h3 class="text-xl font-black text-slate-900">Edit Jadwal Pelajaran</h3>
                <p class="text-xs text-slate-500 mt-0.5">Perbarui hari, jam pelajaran, mata pelajaran, atau guru pengampu slot ini.</p>
            </div>

            <!-- Tab Switcher Mode di Edit -->
            <div class="flex items-center p-1 bg-slate-100 rounded-2xl mb-6">
                <button type="button" @click="editData.inputMode = 'single'" 
                        :class="editData.inputMode === 'single' ? 'bg-white text-slate-900 shadow-sm font-black' : 'text-slate-500 font-bold hover:text-slate-800'"
                        class="flex-1 py-2.5 rounded-xl text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                    <i class="bi-clock-fill"></i>
                    <span>Satu Sesi Biasa</span>
                </button>
                <button type="button" @click="editData.inputMode = 'multi'" 
                        :class="editData.inputMode === 'multi' ? 'bg-white text-blue-700 shadow-sm font-black' : 'text-slate-500 font-bold hover:text-slate-800'"
                        class="flex-1 py-2.5 rounded-xl text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                    <i class="bi-diagram-3-fill"></i>
                    <span>Pecah Jam (Multi-Sesi)</span>
                </button>
            </div>

            <!-- Form Edit -->
            <form method="POST" :action="editData.actionUrl" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <!-- ============================ -->
                <!-- SINGLE MODE WIDGETS          -->
                <!-- ============================ -->
                <div x-show="editData.inputMode === 'single'" class="space-y-4">
                    <!-- Hari -->
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Hari</label>
                        <select name="hari" x-model="editData.hari" :required="editData.inputMode === 'single'" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800">
                            @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h)
                            <option value="{{ $h }}">Hari {{ $h }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dari Jam Ke s.d. Sampai Jam Ke -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Dari Jam Ke</label>
                            <select name="jam_ke_mulai" x-model.number="editData.jam_ke_mulai" :required="editData.inputMode === 'single'" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800">
                                @for($i = 1; $i <= 8; $i++)
                                <option :value="{{ $i }}" {{ $i > 6 ? 'x-show="editData.hari !== \'Jumat\'"' : '' }}>Jam ke-{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Sampai Jam Ke</label>
                            <select name="jam_ke_selesai" x-model.number="editData.jam_ke_selesai" :required="editData.inputMode === 'single'" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800">
                                @for($i = 1; $i <= 8; $i++)
                                <option :value="{{ $i }}" :disabled="editData.jam_ke_mulai > {{ $i }}" {{ $i > 6 ? 'x-show="editData.hari !== \'Jumat\'"' : '' }}>Jam ke-{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ============================ -->
                <!-- MULTI MODE WIDGETS           -->
                <!-- ============================ -->
                <div x-show="editData.inputMode === 'multi'" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="bi-layers-fill text-blue-600"></i>
                            <span>Distribusi Sesi KBM (Pecah Jam Mengajar):</span>
                        </label>
                        <button type="button" @click="tambahSesiEdit()" class="px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-lg text-[11px] border border-blue-200 transition inline-flex items-center gap-1 cursor-pointer">
                            <i class="bi-plus-circle-fill"></i>
                            <span>Tambah Sesi</span>
                        </button>
                    </div>

                    <div class="space-y-2.5">
                        <template x-for="(sesi, index) in editData.sesiList" :key="'edit-sesi-' + index">
                            <div class="p-3 bg-white rounded-2xl border-2 border-slate-200 hover:border-blue-300 transition space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 font-black text-[11px]">
                                        <span x-text="'Sesi ' + (index + 1)"></span>
                                        <span x-show="index === 0" class="ml-1 text-[9px] uppercase tracking-wider px-1.5 py-0.5 bg-blue-200 text-blue-900 rounded">(Update Saat Ini)</span>
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-slate-700 text-[11px]" x-text="getWaktuSesi(sesi.hari, sesi.jam_ke_mulai, sesi.jam_ke_selesai)"></span>
                                        <button type="button" @click="hapusSesiEdit(index)" x-show="editData.sesiList.length > 1 && index > 0" class="text-rose-500 hover:text-rose-700 p-1 cursor-pointer" title="Hapus sesi ini">
                                            <i class="bi-trash3-fill"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Hari</label>
                                        <select :name="'sesi[' + index + '][hari]'" x-model="sesi.hari" :required="editData.inputMode === 'multi'" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800">
                                            @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h)
                                            <option value="{{ $h }}">Hari {{ $h }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Dari Jam Ke</label>
                                        <select :name="'sesi[' + index + '][jam_ke_mulai]'" x-model.number="sesi.jam_ke_mulai" :required="editData.inputMode === 'multi'" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800">
                                            @for($i = 1; $i <= 8; $i++)
                                            <option :value="{{ $i }}" {{ $i > 6 ? 'x-show="sesi.hari !== \'Jumat\'"' : '' }}>Jam ke-{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Sampai Jam Ke</label>
                                        <select :name="'sesi[' + index + '][jam_ke_selesai]'" x-model.number="sesi.jam_ke_selesai" :required="editData.inputMode === 'multi'" class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800">
                                            @for($i = 1; $i <= 8; $i++)
                                            <option :value="{{ $i }}" :disabled="sesi.jam_ke_mulai > {{ $i }}" {{ $i > 6 ? 'x-show="sesi.hari !== \'Jumat\'"' : '' }}>Jam ke-{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Estimasi Waktu Otomatis -->
                <div x-show="editData.inputMode === 'single'" class="p-3 bg-blue-50/80 border border-blue-200 rounded-2xl text-blue-900 text-xs flex items-center justify-between">
                    <span class="font-bold flex items-center gap-1.5">
                        <i class="bi-clock-fill text-blue-600"></i>
                        <span>Waktu KBM:</span>
                    </span>
                    <span class="font-mono font-black text-blue-700" x-text="editWaktuKbm"></span>
                </div>

                <div x-show="editData.inputMode === 'multi'" class="p-3 bg-blue-50/80 border border-blue-200 rounded-2xl text-blue-900 text-xs flex items-center justify-between">
                    <span class="font-bold flex items-center gap-1.5">
                        <i class="bi-layers-fill text-blue-600"></i>
                        <span>Total Jam (Semua Sesi):</span>
                    </span>
                    <span class="font-black text-blue-700" x-text="editTotalJpMulti + ' JP'"></span>
                </div>

                <!-- Kelas -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Kelas</label>
                    <select name="kelas" x-model="editData.kelas" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $k)
                        <option value="{{ $k->nama_kelas ?? $k->nama }}">{{ $k->nama_kelas ?? $k->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Pilihan Cepat dari Kurikulum Kelas Jika Ada -->
                <div x-show="editKurikulumMapels.length > 0" class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-2xl space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-extrabold text-emerald-900 flex items-center gap-1.5">
                            <i class="bi-journal-check text-emerald-600"></i>
                            <span>Sinkron ke Kurikulum <strong x-text="editData.kelas"></strong>:</span>
                        </span>
                        <span class="text-[10.5px] text-emerald-700 font-bold" x-text="editKurikulumMapels.length + ' Mapel'"></span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 max-h-32 overflow-y-auto pr-1">
                        <template x-for="item in editKurikulumMapels" :key="'edit-kuri-' + item.id">
                            <button type="button" 
                                    @click="pickEditKurikulum(item)"
                                    :class="editData.mata_pelajaran_id == item.mata_pelajaran_id ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-400 font-bold' : 'bg-white hover:bg-emerald-100/60 text-slate-800 border border-slate-200'"
                                    class="p-2 rounded-xl text-left transition flex items-start justify-between gap-1 cursor-pointer">
                                <div class="min-w-0">
                                    <div class="font-bold text-[11px] truncate" x-text="item.mapel_nama"></div>
                                    <div class="text-[10px] truncate" :class="editData.mata_pelajaran_id == item.mata_pelajaran_id ? 'text-emerald-100' : 'text-slate-500'" x-text="item.guru_nama"></div>
                                </div>
                                <span class="px-1.5 py-0.5 rounded text-[9.5px] font-black flex-shrink-0"
                                      :class="editData.mata_pelajaran_id == item.mata_pelajaran_id ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-700'"
                                      x-text="item.alokasi_jam + ' JP'"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Mata Pelajaran -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Mata Pelajaran</label>
                    <select name="mata_pelajaran_id" x-model.number="editData.mata_pelajaran_id" @change="onEditMapelChange($event)" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($mapels as $m)
                        <option value="{{ $m->id }}">{{ $m->nama }} ({{ $m->kode }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Guru Pengampu -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Guru Pengampu</label>
                    <select name="guru_user_id" x-model.number="editData.guru_user_id" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800">
                        <option value="">-- Pilih Guru Pengampu --</option>
                        @foreach($guruList as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Ruang (Opsional) -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Ruang Belajar / Praktik (Opsional)</label>
                    <input type="text" name="ruang" x-model="editData.ruang" placeholder="Contoh: R. Teori 1, Lab TKJT, Bengkel TO" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-slate-800">
                </div>

                <!-- Tombol Aksi Modal -->
                                <!-- is_locked Checkbox -->
                <div class="flex items-center gap-2 mt-4 bg-yellow-50 p-3 rounded-xl border border-yellow-200">
                    <input type="hidden" name="is_locked" value="0">
                    <input type="checkbox" name="is_locked" id="is_locked_edit" value="1" x-model="editData.is_locked" class="w-5 h-5 text-yellow-600 rounded border-yellow-300 focus:ring-yellow-500">
                    <label for="is_locked_edit" class="font-bold text-yellow-800 text-sm cursor-pointer">Kunci Jadwal Ini (Prioritas)</label>
                    <p class="text-[10px] text-yellow-700 ml-auto max-w-[200px] text-right">Mencegah jadwal diubah oleh fitur Acak Otomatis.</p>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="editModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold rounded-xl transition shadow-lg shadow-blue-600/30 inline-flex items-center gap-1.5 cursor-pointer">
                        <i class="bi-check-circle-fill"></i>
                        <span>Simpan Perubahan Jadwal</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
