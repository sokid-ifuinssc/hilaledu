@extends('layouts.app')

@section('title', 'Laporan Realisasi KBM Guru - HilalEdu')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-blue-800 via-indigo-900 to-slate-900 rounded-3xl p-6 text-white shadow-xl">
        <div>
            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-blue-200">
                <i class="bi-file-earmark-check mr-1"></i> Realisasi Pembelajaran
            </span>
            <h1 class="text-2xl md:text-3xl font-black mt-2 tracking-tight">Laporan Realisasi KBM Guru</h1>
            <p class="text-slate-300 text-xs md:text-sm mt-1 max-w-2xl">
                Rekapitulasi pelaksanaan pembelajaran harian guru, kesesuaian materi Kurikulum Merdeka, serta rekapitulasi presensi kehadiran siswa di kelas.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.laporan.kbm.print', request()->query()) }}" target="_blank" class="px-4 py-2.5 bg-amber-400 hover:bg-amber-500 text-slate-950 font-bold rounded-2xl text-xs flex items-center gap-2 shadow-lg shadow-amber-400/20 transition">
                <i class="bi-printer-fill text-sm"></i>
                <span>Cetak Laporan KBM</span>
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-xs flex items-center gap-2">
                <i class="bi-funnel-fill text-blue-600"></i>
                <span>Filter Laporan Realisasi KBM</span>
            </h3>
            @if(request('bulan') || request('guru_id') || request('kelas') || request('kesesuaian'))
            <a href="{{ route('admin.laporan.kbm.index') }}" class="text-xs font-bold text-rose-600 hover:underline flex items-center gap-1">
                <i class="bi-arrow-counterclockwise"></i> Reset Filter
            </a>
            @endif
        </div>

        <form method="GET" action="{{ route('admin.laporan.kbm.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Periode Bulan</label>
                <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Guru Pengajar</label>
                <select name="guru_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">-- Semua Guru --</option>
                    @foreach($gurus as $g)
                    <option value="{{ $g->id }}" {{ $guruId == $g->id ? 'selected' : '' }}>
                        {{ $g->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Kelas</label>
                <select name="kelas" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->nama_kelas ?? $k->nama }}" {{ $kelas === ($k->nama_kelas ?? $k->nama) ? 'selected' : '' }}>
                        Kelas {{ $k->nama_kelas ?? $k->nama }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Kesesuaian Rencana</label>
                <select name="kesesuaian" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">-- Semua Kesesuaian --</option>
                    <option value="sesuai" {{ $kesesuaian === 'sesuai' ? 'selected' : '' }}>Sesuai RPP</option>
                    <option value="sebagian" {{ $kesesuaian === 'sebagian' ? 'selected' : '' }}>Sebagian</option>
                    <option value="tidak_sesuai" {{ $kesesuaian === 'tidak_sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                    <option value="materi_pengganti" {{ $kesesuaian === 'materi_pengganti' ? 'selected' : '' }}>Materi Pengganti</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="bi-search"></i>
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Data Laporan KBM -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="bi-journal-check text-blue-600"></i>
                <span>Data Realisasi KBM Masuk ({{ $laporans->total() }} Laporan)</span>
            </h2>
            <div class="text-xs text-slate-500">
                Halaman {{ $laporans->currentPage() }} dari {{ $laporans->lastPage() }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Tanggal Realisasi</th>
                        <th class="px-5 py-3">Guru & Mapel</th>
                        <th class="px-5 py-3">Kelas & Jadwal</th>
                        <th class="px-5 py-3">Kesesuaian & Pelaksanaan</th>
                        <th class="px-5 py-3 text-center">Presensi Siswa</th>
                        <th class="px-5 py-3">Catatan Pembelajaran</th>
                        <th class="px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($laporans as $lap)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900">
                                {{ \Carbon\Carbon::parse($lap->tanggal_realisasi)->isoFormat('dddd, D MMMM Y') }}
                            </div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $lap->tanggal_realisasi }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900">{{ $lap->guru?->name ?? '-' }}</div>
                            <div class="text-[11px] text-blue-700 font-medium">{{ $lap->jadwal->mataPelajaran->nama ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-800 rounded font-bold text-[11px]">
                                Kelas {{ $lap->jadwal->kelas ?? '-' }}
                            </span>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                {{ substr($lap->jadwal->jam_mulai ?? '00:00', 0, 5) }} - {{ substr($lap->jadwal->jam_selesai ?? '00:00', 0, 5) }}
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($lap->kesesuaian_rencana === 'sesuai')
                                <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-md font-bold text-[10px] inline-block mb-1">
                                    ✓ Sesuai RPP
                                </span>
                            @elseif($lap->kesesuaian_rencana === 'sebagian')
                                <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 border border-blue-300 rounded-md font-bold text-[10px] inline-block mb-1">
                                    ~ Sebagian Sesuai
                                </span>
                            @elseif($lap->kesesuaian_rencana === 'materi_pengganti')
                                <span class="px-2.5 py-0.5 bg-purple-100 text-purple-800 border border-purple-300 rounded-md font-bold text-[10px] inline-block mb-1">
                                    ⚙ Materi Pengganti
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 bg-rose-100 text-rose-800 border border-rose-300 rounded-md font-bold text-[10px] inline-block mb-1">
                                    ✕ Tidak Sesuai
                                </span>
                            @endif
                            <div class="text-[11px] text-slate-500">
                                {{ ucwords(str_replace('_', ' ', $lap->status_pelaksanaan)) }}
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="inline-flex items-center gap-1.5 font-bold text-[11px]">
                                <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded" title="Siswa Hadir">
                                    Hadir: {{ $lap->jumlah_siswa_hadir }}
                                </span>
                                <span class="text-rose-700 bg-rose-50 px-2 py-0.5 rounded" title="Siswa Tidak Hadir">
                                    Tdk: {{ $lap->jumlah_siswa_tidak_hadir }}
                                </span>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                Total: {{ $lap->jumlah_siswa_total }} siswa
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-[11px] text-slate-700 line-clamp-2 max-w-xs">
                                {{ $lap->catatan_kegiatan ?: ($lap->keterangan_kesesuaian ?: '-') }}
                            </p>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <a href="{{ route('guru.laporan-kbm.show', $lap->id) }}" target="_blank" class="p-2 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-xl transition inline-flex items-center gap-1 font-bold text-xs" title="Lihat Detail & Presensi Siswa">
                                <i class="bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <i class="bi-journal-x text-4xl mb-2 block text-slate-300"></i>
                            Belum ada laporan realisasi KBM untuk filter ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($laporans->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $laporans->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
