@extends('layouts.app')

@section('title', 'Rekap Nilai Siswa - HilalEdu')

@section('content')
<div class="space-y-6" x-data="{ tab: 'kumulatif' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <span>Akademik</span>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <span class="text-slate-800 font-medium">Rekapitulasi Nilai & Leger Siswa</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white shadow-md shadow-blue-200">
                    <i class="bi bi-award-fill text-lg"></i>
                </span>
                Rekap & Leger Nilai Seluruh Rombel
            </h1>
            <p class="text-sm text-slate-500 mt-1">Pantau matriks nilai siswa per rombel, rekapitulasi kumulatif 6 semester (X Ganjil s.d XII Genap), dan perolehan nilai project eskul.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($selectedKelas)
                <a href="{{ route('walikelas.leger.print', ['kelas_id' => $selectedKelas->id, 'mode' => 'kumulatif']) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                    <i class="bi bi-printer-fill"></i>
                    Cetak Leger Kumulatif
                </a>
                <a href="{{ route('walikelas.leger.print', ['kelas_id' => $selectedKelas->id, 'mode' => 'semester']) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-white text-indigo-700 hover:bg-indigo-50 border border-indigo-200 transition">
                    <i class="bi bi-printer"></i>
                    Cetak Semester Ini
                </a>
                <a href="{{ route('admin.ekstrakurikuler.rekap-kelas', ['kelas_id' => $selectedKelas->id]) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition">
                    <i class="bi bi-award"></i>
                    Rekap Eskul Kelas Ini
                </a>
            @endif
        </div>
    </div>

    <!-- Filter Pilih Rombel / Kelas -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
        <form method="GET" action="{{ route('akademik.nilai.index') }}" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="flex-1 w-full">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pilih Kelas / Rombongan Belajar</label>
                <select name="kelas_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                    <option value="">-- Pilih Rombel --</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ $selectedKelas && $selectedKelas->id == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }} ({{ $k->jurusan?->singkatan ?: ($k->jurusan?->nama ?: 'Umum') }}) - Wali: {{ $k->waliKelasGuru->name ?? ($k->wali_kelas ?: 'Belum diset') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition">
                <i class="bi bi-search"></i> Tampilkan Rekap Nilai
            </button>
        </form>
    </div>

    @if($selectedKelas)
        <!-- Tab Mode Tampilan -->
        <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
            <button type="button" @click="tab = 'kumulatif'" :class="tab === 'kumulatif' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="bi bi-columns-gap"></i>
                Leger 6 Semester (X Ganjil - XII Genap)
            </button>
            <button type="button" @click="tab = 'semester'" :class="tab === 'semester' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="bi bi-calendar3"></i>
                Rekap Nilai Semester Berjalan
            </button>
        </div>

        <!-- TAB 1: MATRIKS LEGER 6 SEMESTER -->
        <div x-show="tab === 'kumulatif'" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-table text-indigo-600"></i>
                        Matriks Leger Kumulatif 6 Semester: {{ $selectedKelas->nama_kelas }} ({{ count($cumulativeRows) }} Siswa)
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Wali Kelas: <b>{{ $selectedKelas->waliKelasGuru->name ?? ($selectedKelas->wali_kelas ?: '-') }}</b> • Jurusan: {{ $selectedKelas->jurusan?->nama ?: 'Umum' }}
                    </p>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">
                        <i class="bi bi-stars"></i> TWP / P5 Terintegrasi Eskul
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto max-h-[650px] relative">
                <table class="w-full text-[11px] text-left border-collapse whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 sticky top-0 z-20 shadow-xs">
                        <tr>
                            <th rowspan="2" class="px-2.5 py-2 text-center border-r border-slate-300 sticky left-0 bg-slate-100 z-30 w-10">No</th>
                            <th rowspan="2" class="px-3 py-2 border-r border-slate-300 sticky left-10 bg-slate-100 z-30 min-w-[180px]">Nama Siswa</th>
                            <th rowspan="2" class="px-2.5 py-2 text-center border-r border-slate-300 min-w-[90px]">NISN</th>

                            @foreach($cumulativeMapels as $m)
                                @php
                                    $isP5 = $m->kat_group === 'project';
                                    $isKejuruan = str_contains($m->kat_group, 'kejuruan');
                                @endphp
                                <th colspan="6" class="px-2 py-2 text-center border-r border-slate-300 {{ $isP5 ? 'bg-emerald-50 text-emerald-900 border-emerald-200 font-extrabold' : ($isKejuruan ? 'bg-purple-50 text-purple-900 border-purple-200' : 'bg-slate-50 text-slate-800') }}" title="{{ $m->nama }}">
                                    <div class="truncate max-w-[260px] mx-auto text-xs">
                                        {{ $m->nama }}
                                        @if($isP5) <span class="text-[9px] text-emerald-700 bg-emerald-100/80 px-1 rounded ml-1">Eskul</span> @endif
                                    </div>
                                </th>
                            @endforeach

                            <th rowspan="2" class="px-3 py-2 text-center border-l border-slate-300 bg-indigo-50 text-indigo-900 font-extrabold min-w-[70px]">Rata Kumulatif</th>
                        </tr>
                        <tr class="bg-slate-100/80 text-[10px] text-slate-600 font-semibold border-b border-slate-300">
                            @foreach($cumulativeMapels as $m)
                                <th class="px-1.5 py-1 text-center border-r border-slate-200 w-11">X-Gj</th>
                                <th class="px-1.5 py-1 text-center border-r border-slate-200 w-11">X-Gn</th>
                                <th class="px-1.5 py-1 text-center border-r border-slate-200 w-11">XI-Gj</th>
                                <th class="px-1.5 py-1 text-center border-r border-slate-200 w-11">XI-Gn</th>
                                <th class="px-1.5 py-1 text-center border-r border-slate-200 w-11">XII-Gj</th>
                                <th class="px-1.5 py-1 text-center border-r border-slate-300 w-11">XII-Gn</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($cumulativeRows as $row)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-2.5 py-2 text-center font-bold text-slate-600 border-r border-slate-100 sticky left-0 bg-white z-10">{{ $loop->iteration }}</td>
                                <td class="px-3 py-2 font-bold text-slate-800 border-r border-slate-100 sticky left-10 bg-white z-10 whitespace-nowrap">{{ $row['siswa']->nama }}</td>
                                <td class="px-2.5 py-2 text-center text-slate-500 border-r border-slate-100 text-[10px]">{{ $row['siswa']->nisn }}</td>

                                @foreach($cumulativeMapels as $m)
                                    @php
                                        $g = $row['grades'][$m->nama] ?? [];
                                        $isP5 = $m->kat_group === 'project';
                                    @endphp
                                    <td class="px-1.5 py-1.5 text-center border-r border-slate-100 {{ $isP5 ? 'bg-emerald-50/20 font-bold' : '' }}">
                                        {{ $g['X_ganjil'] !== null ? number_format($g['X_ganjil'], 0) : '-' }}
                                    </td>
                                    <td class="px-1.5 py-1.5 text-center border-r border-slate-100 {{ $isP5 ? 'bg-emerald-50/20 font-bold' : '' }}">
                                        {{ $g['X_genap'] !== null ? number_format($g['X_genap'], 0) : '-' }}
                                    </td>
                                    <td class="px-1.5 py-1.5 text-center border-r border-slate-100 {{ $isP5 ? 'bg-emerald-50/20 font-bold' : '' }}">
                                        {{ $g['XI_ganjil'] !== null ? number_format($g['XI_ganjil'], 0) : '-' }}
                                    </td>
                                    <td class="px-1.5 py-1.5 text-center border-r border-slate-100 {{ $isP5 ? 'bg-emerald-50/20 font-bold' : '' }}">
                                        {{ $g['XI_genap'] !== null ? number_format($g['XI_genap'], 0) : '-' }}
                                    </td>
                                    <td class="px-1.5 py-1.5 text-center border-r border-slate-100 {{ $isP5 ? 'bg-emerald-50/20 font-bold' : '' }}">
                                        {{ $g['XII_ganjil'] !== null ? number_format($g['XII_ganjil'], 0) : '-' }}
                                    </td>
                                    <td class="px-1.5 py-1.5 text-center border-r border-slate-200 {{ $isP5 ? 'bg-emerald-50/20 font-bold' : '' }}">
                                        {{ $g['XII_genap'] !== null ? number_format($g['XII_genap'], 0) : '-' }}
                                    </td>
                                @endforeach

                                <td class="px-3 py-2 text-center border-l border-slate-200 font-extrabold text-indigo-700 bg-indigo-50/40">
                                    {{ $row['total_terisi'] > 0 ? number_format($row['rata_rata'], 1) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 4 + count($cumulativeMapels) * 6 }}" class="px-4 py-8 text-center text-slate-400">
                                    Tidak ada data siswa di kelas ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: MATRIKS LEGER SEMESTER BERJALAN -->
        <div x-show="tab === 'semester'" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" style="display: none;">
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-table text-blue-600"></i>
                        Matriks Nilai Kelas: {{ $selectedKelas->nama_kelas }} ({{ count($legerData) }} Siswa)
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Wali Kelas: <b>{{ $selectedKelas->waliKelasGuru->name ?? ($selectedKelas->wali_kelas ?: '-') }}</b> • Tahun: {{ $tahunAjaran }} ({{ ucfirst($semester) }})</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-[11px] text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                        <tr>
                            <th class="px-2.5 py-3 text-center border-r border-slate-200 w-10">Rank</th>
                            <th class="px-3 py-3 border-r border-slate-200 min-w-[150px]">Nama Siswa</th>
                            <th class="px-2.5 py-3 text-center border-r border-slate-200">NISN</th>
                            
                            @foreach($mapels as $m)
                                <th class="px-2 py-2 text-center border-r border-slate-200 min-w-[65px]" title="{{ $m->nama }}">
                                    <span class="block truncate w-14 mx-auto">{{ $m->kode ?: ($m->kode_mapel ?: substr($m->nama,0,6)) }}</span>
                                </th>
                            @endforeach

                            <th class="px-2.5 py-3 text-center border-r border-slate-200 bg-blue-50/50 text-blue-900 font-extrabold">Total</th>
                            <th class="px-2.5 py-3 text-center border-r border-slate-200 bg-blue-50/50 text-blue-900 font-extrabold">Rata2</th>
                            <th class="px-3 py-3 border-r border-slate-200 min-w-[130px]">Ekstrakurikuler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($legerData as $row)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-2.5 py-2 text-center font-bold text-blue-700 border-r border-slate-100">{{ $row['rank'] }}</td>
                                <td class="px-3 py-2 font-bold text-slate-800 border-r border-slate-100 whitespace-nowrap">{{ $row['siswa']->nama }}</td>
                                <td class="px-2.5 py-2 text-center text-slate-500 border-r border-slate-100">{{ $row['siswa']->nisn }}</td>

                                @foreach($mapels as $m)
                                    @php $n = $row['nilai_mapels'][$m->id] ?? null; @endphp
                                    <td class="px-2 py-2 text-center border-r border-slate-100">
                                        {{ $n !== null ? number_format($n, 0) : '-' }}
                                    </td>
                                @endforeach

                                <td class="px-2.5 py-2 text-center border-r border-slate-100 font-bold text-slate-800 bg-blue-50/20">{{ number_format($row['total_nilai'], 0) }}</td>
                                <td class="px-2.5 py-2 text-center border-r border-slate-100 font-extrabold text-blue-700 bg-blue-50/40">{{ number_format($row['rata_rata'], 1) }}</td>
                                <td class="px-3 py-2 border-r border-slate-100 text-[10px]">
                                    @if(count($row['eskul_list']) > 0)
                                        @foreach($row['eskul_list'] as $es)
                                            <span class="inline-block bg-emerald-50 text-emerald-800 px-1.5 py-0.5 rounded mr-1 mb-0.5">{{ $es['nama'] }} ({{ $es['predikat'] ?: ($es['nilai'] ? number_format($es['nilai'],0) : '-') }})</span>
                                        @endforeach
                                    @else
                                        <span class="text-slate-300 italic">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 6 + $mapels->count() }}" class="px-4 py-8 text-center text-slate-400">
                                    Tidak ada data siswa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-3xl mx-auto mb-3">
                <i class="bi bi-filter-circle"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800">Silakan Pilih Rombel / Kelas</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">Pilih kelas di formulir atas untuk menampilkan matriks rekapitulasi nilai dan keterlibatan eskul.</p>
        </div>
    @endif
</div>
@endsection
