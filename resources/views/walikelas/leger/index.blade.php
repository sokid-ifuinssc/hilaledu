@extends('layouts.app')

@section('title', 'Leger Nilai Kelas ' . ($kelas->nama_kelas ?? ''))

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <span>Wali Kelas</span>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <span class="text-slate-800 font-medium">Leger Nilai</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-indigo-200">
                    <i class="bi bi-journal-bookmark text-lg"></i>
                </span>
                Leger Nilai Siswa: {{ $kelas->nama_kelas ?? 'Kelas Bimbingan' }}
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Rekapitulasi nilai seluruh mata pelajaran, rata-rata, peringkat kelas, keikutsertaan ekstrakurikuler, dan absensi kehadiran.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('walikelas.leger.print', ['kelas_id' => $kelas->id]) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                <i class="bi bi-printer-fill"></i>
                Cetak Leger (Landscape)
            </a>
            <a href="{{ route('walikelas.eskul', ['kelas_id' => $kelas->id]) }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition">
                <i class="bi bi-award-fill"></i>
                Rekap Eskul Siswa
            </a>
        </div>
    </div>

    @if(isset($kelasList) && $kelasList->isNotEmpty())
        <!-- Selector Kelas untuk Super Admin / Pimpinan -->
        <div class="bg-white rounded-2xl border border-indigo-100 shadow-sm p-4 bg-gradient-to-r from-indigo-50/40 via-white to-purple-50/40">
            <form method="GET" action="{{ route('walikelas.leger') }}" class="flex flex-col sm:flex-row items-end gap-3">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-indigo-900 mb-1 flex items-center gap-1.5">
                        <i class="bi bi-shield-check text-indigo-600"></i>
                        Akses Super Admin: Pilih Rombel / Kelas untuk Melihat Leger Nilai
                    </label>
                    <select name="kelas_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs rounded-xl border border-indigo-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold bg-white">
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ $kelas->id == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }} ({{ $k->jurusan ?: 'Umum' }}) - Wali Kelas: {{ $k->waliKelas->name ?? ($k->wali_kelas ?: 'Belum diset') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 text-xs font-bold bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                    Tampilkan
                </button>
            </form>
        </div>
    @endif

    <!-- Info Banner Ringkasan Kelas -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Total Siswa Bimbingan</p>
            <h4 class="text-xl font-bold text-slate-800 mt-1">{{ count($legerData) }} <span class="text-xs font-normal text-slate-400">siswa</span></h4>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Jumlah Mata Pelajaran</p>
            <h4 class="text-xl font-bold text-slate-800 mt-1">{{ $mapels->count() }} <span class="text-xs font-normal text-slate-400">mapel</span></h4>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Tahun Ajaran / Semester</p>
            <h4 class="text-base font-bold text-slate-800 mt-1 truncate">{{ $tahunAjaran }}</h4>
            <span class="text-[11px] text-indigo-600 font-semibold">{{ ucfirst($semester) }}</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Wali Kelas Pengampu</p>
            <h4 class="text-base font-bold text-slate-800 mt-1 truncate">{{ $kelas->waliKelas->name ?? auth()->user()->name }}</h4>
        </div>
    </div>

    <!-- Matriks Leger Nilai -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="bi bi-grid-3x3-gap text-indigo-600"></i>
                Tabel Leger Nilai Hasil Belajar
            </h3>
            <span class="text-xs text-slate-500">
                Nilai akhir per mapel terhitung otomatis dari input guru mapel
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-[11px] text-left border-collapse">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-2.5 py-3 text-center border-r border-slate-200 sticky left-0 bg-slate-50 z-10 w-10">Rank</th>
                        <th class="px-3 py-3 border-r border-slate-200 sticky left-10 bg-slate-50 z-10 min-w-[160px]">Nama Siswa</th>
                        <th class="px-2.5 py-3 text-center border-r border-slate-200">NISN</th>
                        
                        <!-- Kolom Mapel -->
                        @foreach($mapels as $m)
                            <th class="px-2 py-2 text-center border-r border-slate-200 min-w-[70px]" title="{{ $m->nama }}">
                                <span class="block truncate w-16 mx-auto">{{ $m->kode_mapel ?: substr($m->nama,0,6) }}</span>
                            </th>
                        @endforeach

                        <th class="px-2.5 py-3 text-center border-r border-slate-200 bg-indigo-50/50 text-indigo-900 font-extrabold">Total</th>
                        <th class="px-2.5 py-3 text-center border-r border-slate-200 bg-indigo-50/50 text-indigo-900 font-extrabold">Rata2</th>
                        <th class="px-3 py-3 border-r border-slate-200 min-w-[140px]">Ekstrakurikuler</th>
                        <th class="px-2 py-3 text-center border-r border-slate-200 w-12 text-emerald-700">H</th>
                        <th class="px-2 py-3 text-center border-r border-slate-200 w-12 text-blue-700">I</th>
                        <th class="px-2 py-3 text-center border-r border-slate-200 w-12 text-amber-700">S</th>
                        <th class="px-2 py-3 text-center w-12 text-rose-700">A</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($legerData as $row)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-2.5 py-2.5 text-center font-bold text-indigo-700 border-r border-slate-100 sticky left-0 bg-white">
                                {{ $row['rank'] }}
                            </td>
                            <td class="px-3 py-2.5 font-bold text-slate-800 border-r border-slate-100 sticky left-10 bg-white whitespace-nowrap">
                                {{ $row['siswa']->nama }}
                            </td>
                            <td class="px-2.5 py-2.5 text-center text-slate-500 border-r border-slate-100 whitespace-nowrap">
                                {{ $row['siswa']->nisn ?: ($row['siswa']->nis ?: '-') }}
                            </td>

                            <!-- Nilai Tiap Mapel -->
                            @foreach($mapels as $m)
                                @php
                                    $n = $row['nilai_mapels'][$m->id] ?? null;
                                    $isP5 = str_contains(strtolower($m->nama), 'team work') || str_contains(strtolower($m->nama), 'pancasila');
                                @endphp
                                <td class="px-2 py-2 text-center border-r border-slate-100 font-medium {{ $isP5 ? 'bg-emerald-50/30 font-bold' : '' }}">
                                    @if($n !== null)
                                        <span class="{{ $n < 75 ? 'text-rose-600 font-bold' : 'text-slate-800' }}">
                                            {{ number_format($n, 0) }}
                                        </span>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            @endforeach

                            <!-- Total & Rata-rata -->
                            <td class="px-2.5 py-2 text-center border-r border-slate-100 font-bold text-slate-800 bg-indigo-50/30">
                                {{ number_format($row['total_nilai'], 0) }}
                            </td>
                            <td class="px-2.5 py-2 text-center border-r border-slate-100 font-extrabold text-indigo-700 bg-indigo-50/50">
                                {{ number_format($row['rata_rata'], 1) }}
                            </td>

                            <!-- Kolom Eskul -->
                            <td class="px-3 py-2 border-r border-slate-100">
                                @if(count($row['eskul_list']) > 0)
                                    <div class="space-y-0.5">
                                        @foreach($row['eskul_list'] as $es)
                                            <div class="text-[10px] text-slate-700 flex items-center justify-between gap-1">
                                                <span class="truncate max-w-[90px] font-medium">{{ $es['nama'] }}</span>
                                                <span class="font-bold text-indigo-600">{{ $es['predikat'] ?: ($es['nilai'] ? number_format($es['nilai'],0) : '-') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-300 text-[10px] italic">Tidak ada eskul</span>
                                @endif
                            </td>

                            <!-- Absensi Sekolah -->
                            <td class="px-2 py-2 text-center border-r border-slate-100 font-medium text-emerald-700">{{ $row['absensi']['H'] }}</td>
                            <td class="px-2 py-2 text-center border-r border-slate-100 font-medium text-blue-700">{{ $row['absensi']['I'] }}</td>
                            <td class="px-2 py-2 text-center border-r border-slate-100 font-medium text-amber-700">{{ $row['absensi']['S'] }}</td>
                            <td class="px-2 py-2 text-center font-medium text-rose-700">{{ $row['absensi']['A'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 7 + $mapels->count() }}" class="px-4 py-8 text-center text-slate-400">
                                Belum ada data siswa di kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
