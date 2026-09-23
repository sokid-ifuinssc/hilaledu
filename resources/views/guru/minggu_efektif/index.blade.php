@extends('layouts.app')

@section('title', 'Minggu & Jam Efektif Mengajar (Otomatis)')

@section('content')
<div class="space-y-6" x-data="{
    detailModalOpen: false,
    activeItem: null,
    openDetail(item) {
        this.activeItem = item;
        this.detailModalOpen = true;
    }
}">

    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-teal-100 text-teal-800 border border-teal-200">
                    <i class="bi-lightning-charge-fill text-amber-500"></i> Terhitung Otomatis
                </span>
                <span class="text-xs text-slate-400">&bull;</span>
                <span class="text-xs font-semibold text-slate-500">Tahun Ajaran {{ $tahun }} (Semester {{ ucfirst($semester) }})</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <i class="bi-calendar2-range-fill text-teal-600"></i>
                <span>Rincian Minggu & Jam Efektif Mengajar</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                Dihitung 100% otomatis mengikuti <strong class="text-slate-700">hari dan jam mengajar Anda pada setiap mapel</strong> serta disinkronkan dengan hari libur dan kegiatan resmi di Kalender Akademik.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center flex-wrap gap-2.5">
            <a href="{{ route('guru.kalender.index') }}" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold rounded-xl text-xs flex items-center gap-2 shadow-2xs transition">
                <i class="bi-calendar3 text-amber-600"></i>
                <span>Kalender Akademik</span>
            </a>

            <form action="{{ route('guru.minggu-efektif.sync') }}" method="POST" class="inline-block">
                @csrf
                <input type="hidden" name="tahun_ajaran" value="{{ $tahun }}">
                <input type="hidden" name="semester" value="{{ $semester }}">
                @if($isManager && request()->filled('guru_id'))
                <input type="hidden" name="guru_id" value="{{ request('guru_id') }}">
                @endif
                <button type="submit" class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-teal-600/20 transition">
                    <i class="bi-arrow-repeat text-sm"></i>
                    <span>Sinkronkan Ulang Sekarang</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Alert / Feedback Notif -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-xs text-emerald-800 font-medium">
        <i class="bi-check-circle-fill text-emerald-600 text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('info'))
    <div class="p-4 bg-blue-50 border border-blue-200 rounded-2xl flex items-center gap-3 text-xs text-blue-800 font-medium">
        <i class="bi-info-circle-fill text-blue-600 text-base"></i>
        <span>{{ session('info') }}</span>
    </div>
    @endif

    <!-- Metric Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1 -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Mapel & Kelas Diampu</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ count($calculatedItems) }} <span class="text-xs font-semibold text-slate-400">Penugasan</span></h3>
                <p class="text-[10px] text-slate-500 mt-0.5">{{ $selectedGuru->name ?? 'Guru' }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-black">
                <i class="bi-mortarboard"></i>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Beban Mengajar Mingguan</p>
                <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $totalJpSeminggu }} <span class="text-xs font-semibold text-emerald-700">JP / Minggu</span></h3>
                <p class="text-[10px] text-slate-500 mt-0.5">Total jadwal tatap muka</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-black">
                <i class="bi-clock-history"></i>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Jam Efektif Semester Ini</p>
                <h3 class="text-2xl font-black text-blue-700 mt-1">{{ $totalJamEfektifSemester }} <span class="text-xs font-semibold text-blue-700">JP Efektif</span></h3>
                <p class="text-[10px] text-slate-500 mt-0.5">Sudah memotong hari libur</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-black">
                <i class="bi-calendar-check-fill"></i>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Acuan Kalender</p>
                <h3 class="text-sm font-black text-amber-700 mt-1">TA {{ $tahun }}</h3>
                <p class="text-[10px] text-emerald-600 font-bold mt-0.5 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                    Sinkron Hari Libur & KBM
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-black">
                <i class="bi-shield-check"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Teacher Selector Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4 text-xs">
        <form method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <!-- Filter Tahun Ajaran -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Tahun Pelajaran</label>
                <select name="tahun_ajaran" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-bold text-xs text-slate-800 focus:ring-2 focus:ring-teal-600">
                    @forelse($kalenders as $k)
                    <option value="{{ $k->tahun_ajaran }}" {{ $tahun === $k->tahun_ajaran ? 'selected' : '' }}>
                        {{ $k->tahun_ajaran }} {{ $k->is_aktif ? '(Aktif)' : '' }}
                    </option>
                    @empty
                    <option value="2026/2027" {{ $tahun === '2026/2027' ? 'selected' : '' }}>2026/2027 (Aktif)</option>
                    <option value="2027/2028" {{ $tahun === '2027/2028' ? 'selected' : '' }}>2027/2028</option>
                    @endforelse
                </select>
            </div>

            <!-- Filter Semester -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Semester</label>
                <select name="semester" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-bold text-xs text-slate-800 focus:ring-2 focus:ring-teal-600">
                    <option value="ganjil" {{ $semester === 'ganjil' ? 'selected' : '' }}>Semester Ganjil (Jul - Des)</option>
                    <option value="genap" {{ $semester === 'genap' ? 'selected' : '' }}>Semester Genap (Jan - Jun)</option>
                </select>
            </div>

            <!-- Filter Guru Khusus Admin / Waka -->
            @if($isManager && $gurus->isNotEmpty())
            <div>
                <label class="block text-[10px] font-bold uppercase text-indigo-600 mb-1">Pilih Guru Pengampu</label>
                <select name="guru_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-indigo-50 border border-indigo-200 rounded-xl font-bold text-xs text-indigo-900 focus:ring-2 focus:ring-indigo-600">
                    @foreach($gurus as $g)
                    <option value="{{ $g->id }}" {{ $selectedGuru->id === $g->id ? 'selected' : '' }}>
                        {{ $g->name }} ({{ $g->role }})
                    </option>
                    @endforeach
                </select>
            </div>
            @endif
        </form>

        <div class="text-slate-500 text-xs flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-teal-500"></span>
            <span>Perangkat Terhitung: <strong>{{ count($calculatedItems) }} Mata Pelajaran</strong></span>
        </div>
    </div>

    <!-- Main Content Table / List -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Mata Pelajaran & Kelas</th>
                        <th class="px-4 py-4">Jadwal Mengajar (Hari & JP)</th>
                        <th class="px-4 py-4 text-center">Pekan Total</th>
                        <th class="px-4 py-4 text-center text-rose-600">Tidak Efektif</th>
                        <th class="px-4 py-4 text-center text-emerald-600 font-extrabold">Pekan Efektif</th>
                        <th class="px-4 py-4 text-center">Beban / Pekan</th>
                        <th class="px-4 py-4 text-center font-extrabold text-blue-700">Total Jam Efektif</th>
                        <th class="px-6 py-4 text-right">Aksi & Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($calculatedItems as $idx => $item)
                    @php
                        $rec = $item['record'];
                        $as = $item['assignment'];
                        $calc = $item['calc'];
                    @endphp
                    <tr class="hover:bg-teal-50/20 transition">
                        <!-- Mapel & Kelas -->
                        <td class="px-6 py-4">
                            <div class="font-extrabold text-slate-900 text-sm flex items-center gap-1.5">
                                <span>{{ $as['mata_pelajaran_nama'] }}</span>
                                @if(!empty($as['mata_pelajaran_kode']))
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">{{ $as['mata_pelajaran_kode'] }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 font-bold rounded-md border border-indigo-200 text-[11px]">
                                    Kelas {{ $as['kelas'] }}
                                </span>
                                <span class="text-[11px] text-slate-400">&bull; {{ $as['tahun_ajaran'] }} Sem {{ ucfirst($as['semester']) }}</span>
                            </div>
                        </td>

                        <!-- Jadwal Hari & JP -->
                        <td class="px-4 py-4">
                            <div class="flex flex-wrap items-center gap-1.5 max-w-xs">
                                @foreach($as['hari_sessions'] as $session)
                                <span class="px-2.5 py-1 rounded-lg bg-teal-50 text-teal-800 border border-teal-200 text-[11px] font-bold flex items-center gap-1" title="{{ $session['slot_label'] }}">
                                    <i class="bi-calendar-event text-teal-600"></i>
                                    <span>{{ $session['hari'] }}: {{ $session['jp'] }} JP</span>
                                </span>
                                @endforeach
                            </div>
                        </td>

                        <!-- Total Pekan -->
                        <td class="px-4 py-4 text-center font-bold text-slate-700">
                            {{ $calc['total_minggu'] }}
                        </td>

                        <!-- Tidak Efektif -->
                        <td class="px-4 py-4 text-center font-bold text-rose-600">
                            <span class="px-2 py-0.5 rounded-full bg-rose-50 border border-rose-200">
                                {{ $calc['total_tidak_efektif'] }} mgg
                            </span>
                        </td>

                        <!-- Minggu Efektif -->
                        <td class="px-4 py-4 text-center font-black text-emerald-700 bg-emerald-50/40">
                            <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs">
                                {{ $calc['total_efektif'] }} Pekan
                            </span>
                        </td>

                        <!-- JP per Minggu -->
                        <td class="px-4 py-4 text-center font-bold text-slate-700">
                            {{ $calc['jam_per_minggu'] }} JP
                        </td>

                        <!-- Total Jam Efektif -->
                        <td class="px-4 py-4 text-center font-black text-sm text-blue-700 bg-blue-50/50">
                            <span class="px-2.5 py-1 rounded-lg bg-blue-600 text-white shadow-2xs inline-block">
                                {{ $calc['total_jam_efektif'] }} JP
                            </span>
                        </td>

                        <!-- Aksi -->
                        <td class="px-6 py-4 text-right space-x-1 whitespace-nowrap">
                            <!-- Tombol Modal Quick View -->
                            <button type="button" @click="openDetail({{ json_encode($calc) }})" 
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs inline-flex items-center gap-1.5 transition" title="Lihat Rincian Bulanan">
                                <i class="bi-eye text-sm text-slate-600"></i>
                                <span>Rincian</span>
                            </button>

                            <!-- Cetak Lembar Resmi -->
                            <a href="{{ route('guru.minggu-efektif.print', $rec) }}" target="_blank" 
                               class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs inline-flex items-center gap-1.5 shadow-2xs transition" title="Cetak Format Resmi Sekolah">
                                <i class="bi-printer text-xs"></i>
                                <span>Cetak RPE</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center text-slate-400">
                            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-3xl mx-auto mb-3">
                                <i class="bi-calendar2-x"></i>
                            </div>
                            <h4 class="font-extrabold text-slate-700 text-sm">Tidak Ditemukan Jadwal Mengajar</h4>
                            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                                Guru ini belum memiliki penugasan jadwal pelajaran di <strong>Tahun Ajaran {{ $tahun }} (Semester {{ ucfirst($semester) }})</strong>.
                                Pastikan jadwal pelajaran sudah diinput di menu Jadwal Pelajaran agar minggu efektif terhitung otomatis.
                            </p>
                            @if($isManager)
                            <a href="{{ route('admin.jadwal.index') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white font-bold rounded-xl text-xs hover:bg-indigo-700 transition">
                                <i class="bi-calendar-plus"></i>
                                <span>Atur Jadwal Pelajaran</span>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL RINCIAN BULANAN & DISTRIBUSI JAM (ALPINE.JS) -->
    <div x-show="detailModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="detailModalOpen = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-4xl rounded-3xl bg-white p-6 sm:p-8 shadow-2xl border border-slate-100 text-xs space-y-6" @click.stop>
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b pb-4">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-teal-600 mb-0.5">Rincian Pekan Efektif (RPE) Otomatis</div>
                        <h3 class="text-lg font-black text-slate-900" x-text="activeItem ? (activeItem.mata_pelajaran_nama + ' - Kelas ' + activeItem.kelas) : ''"></h3>
                        <p class="text-xs text-slate-500 mt-0.5" x-text="activeItem ? ('Hari Mengajar: ' + activeItem.hari_label + ' | TA ' + activeItem.tahun_ajaran + ' (' + activeItem.semester.toUpperCase() + ')') : ''"></p>
                    </div>
                    <button type="button" @click="detailModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition">
                        <i class="bi-x-lg font-bold"></i>
                    </button>
                </div>

                <!-- 4 Top Cards inside modal -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center" x-if="activeItem">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="text-[10px] text-slate-500 font-bold uppercase">Total Pekan</div>
                        <div class="text-xl font-black text-slate-800 mt-0.5" x-text="activeItem ? activeItem.total_minggu : 0"></div>
                    </div>
                    <div class="p-3 bg-rose-50 rounded-xl border border-rose-200">
                        <div class="text-[10px] text-rose-700 font-bold uppercase">Tidak Efektif</div>
                        <div class="text-xl font-black text-rose-600 mt-0.5" x-text="activeItem ? activeItem.total_tidak_efektif : 0"></div>
                    </div>
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                        <div class="text-[10px] text-emerald-700 font-bold uppercase">Pekan Efektif</div>
                        <div class="text-xl font-black text-emerald-600 mt-0.5" x-text="activeItem ? activeItem.total_efektif : 0"></div>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-xl border border-blue-200">
                        <div class="text-[10px] text-blue-700 font-bold uppercase">Total Jam Efektif</div>
                        <div class="text-xl font-black text-blue-700 mt-0.5" x-text="activeItem ? (activeItem.total_jam_efektif + ' JP') : '0 JP'"></div>
                    </div>
                </div>

                <!-- Monthly Table -->
                <div class="space-y-2">
                    <h4 class="font-extrabold text-slate-900 text-xs border-b pb-1">Tabel Perhitungan Jumlah Pekan Efektif Per Bulan</h4>
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-[10px] font-bold uppercase text-slate-500 border-b">
                                <tr>
                                    <th class="px-3 py-2.5">Bulan</th>
                                    <th class="px-3 py-2.5 text-center">Jml Pekan</th>
                                    <th class="px-3 py-2.5 text-center text-rose-600">Tidak Efektif</th>
                                    <th class="px-3 py-2.5 text-center text-emerald-600 font-extrabold">Pekan Efektif</th>
                                    <th class="px-3 py-2.5 text-center text-blue-700 font-extrabold">Jam Efektif</th>
                                    <th class="px-3 py-2.5">Keterangan Hari Libur / Agenda Non-KBM</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <template x-for="row in (activeItem ? activeItem.rincian_bulanan : [])" :key="row.bulan">
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="px-3 py-2 font-bold text-slate-800" x-text="row.bulan"></td>
                                        <td class="px-3 py-2 text-center" x-text="row.total_minggu"></td>
                                        <td class="px-3 py-2 text-center font-bold text-rose-600" x-text="row.tidak_efektif"></td>
                                        <td class="px-3 py-2 text-center font-bold text-emerald-600 bg-emerald-50/40" x-text="row.efektif"></td>
                                        <td class="px-3 py-2 text-center font-black text-blue-700 bg-blue-50/40" x-text="row.jam_efektif + ' JP'"></td>
                                        <td class="px-3 py-2 text-slate-600 text-[11px]" x-text="row.keterangan || '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Distribution Cards -->
                <div class="space-y-2">
                    <h4 class="font-extrabold text-slate-900 text-xs border-b pb-1">Distribusi Alokasi Jam Pembelajaran (JP)</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs" x-if="activeItem">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-slate-500 text-[10px] block">Tatap Muka & Praktik</span>
                            <span class="text-sm font-black text-slate-800 mt-1 block" x-text="activeItem ? (activeItem.distribusi_jam.tatap_muka + ' JP (80%)') : ''"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-slate-500 text-[10px] block">Asesmen Formatif</span>
                            <span class="text-sm font-black text-slate-800 mt-1 block" x-text="activeItem ? (activeItem.distribusi_jam.asesmen_formatif + ' JP (10%)') : ''"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-slate-500 text-[10px] block">Asesmen Sumatif</span>
                            <span class="text-sm font-black text-slate-800 mt-1 block" x-text="activeItem ? (activeItem.distribusi_jam.asesmen_sumatif + ' JP (5%)') : ''"></span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <span class="text-slate-500 text-[10px] block">Cadangan Jam</span>
                            <span class="text-sm font-black text-slate-800 mt-1 block" x-text="activeItem ? (activeItem.distribusi_jam.cadangan + ' JP (5%)') : ''"></span>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between pt-2 border-t">
                    <div class="text-[11px] text-slate-500 italic" x-text="activeItem ? activeItem.catatan : ''"></div>
                    <button type="button" @click="detailModalOpen = false" class="px-5 py-2.5 bg-slate-800 text-white font-bold rounded-xl text-xs hover:bg-slate-900 transition">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection
