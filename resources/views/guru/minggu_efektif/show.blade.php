@extends('layouts.app')

@section('title', 'Detail Minggu Efektif')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div>
            <div class="text-xs text-slate-500 mb-1">
                <a href="{{ route('guru.minggu-efektif.index') }}" class="text-blue-600 hover:underline">&larr; Kembali ke Daftar</a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Rincian Minggu Efektif: {{ $mingguEfektif->mataPelajaran->nama ?? 'Mata Pelajaran' }}
            </h1>
            <p class="text-xs text-slate-500">
                Kelas {{ $mingguEfektif->kelas }} &bull; {{ $mingguEfektif->tahun_ajaran }} (Semester {{ ucfirst($mingguEfektif->semester) }})
                @php
                    $jadwalNotes = '';
                    if (!empty($mingguEfektif->catatan) && str_contains($mingguEfektif->catatan, 'jadwal mengajar:')) {
                        $parts = explode('jadwal mengajar:', $mingguEfektif->catatan);
                        $jadwalNotes = trim(rtrim($parts[1] ?? '', '.'));
                    }
                @endphp
                @if($jadwalNotes)
                &bull; <span class="font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-md border border-teal-200">Jadwal: {{ $jadwalNotes }}</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.minggu-efektif.print', $mingguEfektif) }}" target="_blank" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition">
                <i class="bi-printer text-base"></i>
                <span>Cetak Lembar Resmi</span>
            </a>
        </div>
    </div>

    <!-- Overview Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
        
        <!-- Summary Numbers -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                <div class="text-[11px] font-bold text-slate-500 uppercase">Total Minggu</div>
                <div class="text-2xl font-black text-slate-800 mt-1">{{ $mingguEfektif->total_minggu }}</div>
                <div class="text-[10px] text-slate-400">Minggu Kalender</div>
            </div>

            <div class="p-4 bg-rose-50 rounded-2xl border border-rose-200 text-center">
                <div class="text-[11px] font-bold text-rose-700 uppercase">Tidak Efektif</div>
                <div class="text-2xl font-black text-rose-600 mt-1">{{ $mingguEfektif->total_tidak_efektif }}</div>
                <div class="text-[10px] text-rose-500">Libur / Asesmen</div>
            </div>

            <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 text-center">
                <div class="text-[11px] font-bold text-emerald-800 uppercase">Minggu Efektif</div>
                <div class="text-2xl font-black text-emerald-700 mt-1">{{ $mingguEfektif->total_efektif }}</div>
                <div class="text-[10px] text-emerald-600">Minggu KBM Aktif</div>
            </div>

            <div class="p-4 bg-blue-50 rounded-2xl border border-blue-200 text-center">
                <div class="text-[11px] font-bold text-blue-800 uppercase">Total Jam Efektif</div>
                <div class="text-2xl font-black text-blue-700 mt-1">{{ $mingguEfektif->total_jam_efektif }}</div>
                <div class="text-[10px] text-blue-600">JP ({{ $mingguEfektif->jam_per_minggu }} JP/Minggu)</div>
            </div>
        </div>

        <!-- Table Breakdown -->
        <div class="space-y-3">
            <h3 class="font-extrabold text-sm text-slate-900 border-b pb-2">Rincian Alokasi Per Bulan</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 border-b border-slate-200 font-bold uppercase text-[10px] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Nama Bulan</th>
                            <th class="px-4 py-3 text-center">Jumlah Minggu</th>
                            <th class="px-4 py-3 text-center text-rose-600">Tidak Efektif</th>
                            <th class="px-4 py-3 text-center text-emerald-600">Minggu Efektif</th>
                            <th class="px-4 py-3 text-center text-blue-700 font-extrabold">Jam Efektif</th>
                            <th class="px-4 py-3">Keterangan Agenda</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($mingguEfektif->rincian_bulanan ?? [] as $item)
                        <tr>
                            <td class="px-4 py-3 font-bold text-slate-800">{{ $item['bulan'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $item['total_minggu'] }}</td>
                            <td class="px-4 py-3 text-center font-bold text-rose-600">{{ $item['tidak_efektif'] }}</td>
                            <td class="px-4 py-3 text-center font-bold text-emerald-600 bg-emerald-50/40">{{ $item['efektif'] }}</td>
                            <td class="px-4 py-3 text-center font-black text-blue-700 bg-blue-50/40">{{ $item['jam_efektif'] ?? ($item['efektif'] * $mingguEfektif->jam_per_minggu) }} JP</td>
                            <td class="px-4 py-3 text-slate-600">{{ $item['keterangan'] ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Distribution -->
        <div class="space-y-3 pt-2">
            <h3 class="font-extrabold text-sm text-slate-900 border-b pb-2">Distribusi Alokasi Jam Pembelajaran</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-slate-500 text-[11px]">Tatap Muka & Praktik</div>
                    <div class="text-base font-bold text-slate-900 mt-0.5">{{ $mingguEfektif->distribusi_jam['tatap_muka'] ?? '-' }} JP</div>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-slate-500 text-[11px]">Asesmen Formatif</div>
                    <div class="text-base font-bold text-slate-900 mt-0.5">{{ $mingguEfektif->distribusi_jam['asesmen_formatif'] ?? '-' }} JP</div>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-slate-500 text-[11px]">Asesmen Sumatif</div>
                    <div class="text-base font-bold text-slate-900 mt-0.5">{{ $mingguEfektif->distribusi_jam['asesmen_sumatif'] ?? '-' }} JP</div>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-slate-500 text-[11px]">Cadangan Jam</div>
                    <div class="text-base font-bold text-slate-900 mt-0.5">{{ $mingguEfektif->distribusi_jam['cadangan'] ?? '-' }} JP</div>
                </div>
            </div>
        </div>

        @if($mingguEfektif->catatan)
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs">
            <span class="font-bold text-slate-700 block mb-1">Catatan Tambahan:</span>
            <p class="text-slate-600">{{ $mingguEfektif->catatan }}</p>
        </div>
        @endif

    </div>
</div>
@endsection
