@extends('layouts.app')

@section('title', 'Detail Modul Ajar Harian')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="text-xs text-slate-500 mb-1">
                <a href="{{ route('guru.rencana-pembelajaran.index') }}" class="text-blue-600 hover:underline">&larr; Kembali ke Rencana Ajar</a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Modul Ajar Pertemuan Ke-{{ $rencana->pertemuan_ke }}: {{ $rencana->materi_pokok }}
            </h1>
            <p class="text-xs text-slate-500">{{ $rencana->jadwal->mataPelajaran->nama ?? 'Mata Pelajaran' }} &bull; Kelas {{ $rencana->jadwal->kelas ?? '-' }} &bull; Tanggal Rencana: {{ \Carbon\Carbon::parse($rencana->tanggal_rencana)->format('d M Y') }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('guru.laporan-kbm.create', ['rencana_id' => $rencana->id, 'jadwal_id' => $rencana->jadwal_pelajaran_id]) }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition">
                <i class="bi-pencil-square text-sm"></i>
                <span>Input Realisasi KBM</span>
            </a>
        </div>
    </div>

    <!-- Content Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6 text-xs">
        
        <!-- Info Badges -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3 bg-slate-50 rounded-xl border">
                <div class="text-[10px] text-slate-400 font-bold uppercase">Hari & Jam</div>
                <div class="font-bold text-slate-800 text-xs mt-0.5">{{ $rencana->jadwal->hari ?? '-' }}, {{ substr($rencana->jadwal->jam_mulai ?? '',0,5) }}-{{ substr($rencana->jadwal->jam_selesai ?? '',0,5) }}</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border">
                <div class="text-[10px] text-slate-400 font-bold uppercase">Ruangan</div>
                <div class="font-bold text-slate-800 text-xs mt-0.5">{{ $rencana->jadwal->ruang ?? 'Ruang Kelas' }}</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border">
                <div class="text-[10px] text-slate-400 font-bold uppercase">Asesmen</div>
                <div class="font-bold text-slate-800 text-xs mt-0.5">{{ $rencana->bentuk_asesmen }}</div>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border">
                <div class="text-[10px] text-slate-400 font-bold uppercase">Media</div>
                <div class="font-bold text-slate-800 text-xs mt-0.5">{{ $rencana->media_sumber ?: '-' }}</div>
            </div>
        </div>

        @if($rencana->tujuanPembelajaran)
        <!-- TP Section -->
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-2xl space-y-1">
            <span class="text-[10px] font-bold uppercase text-blue-700">Tujuan Pembelajaran (TP) Ditargetkan:</span>
            <div class="font-bold text-slate-900">{{ $rencana->tujuanPembelajaran->kode_tp }}: {{ $rencana->tujuanPembelajaran->deskripsi }}</div>
            @if($rencana->tujuanPembelajaran->kktp)
            <div class="text-slate-600 text-[11px] pt-1 border-t border-blue-200/50 mt-1">
                <strong>KKTP:</strong> {{ $rencana->tujuanPembelajaran->kktp }}
            </div>
            @endif
        </div>
        @endif

        <!-- Skenario KBM -->
        <div class="space-y-4">
            <h3 class="font-black text-sm text-slate-900 border-b pb-2">Skenario Langkah Pembelajaran</h3>

            <div class="space-y-2">
                <span class="font-bold text-slate-800">1. Kegiatan Pendahuluan:</span>
                <p class="text-slate-700 bg-slate-50 p-3.5 rounded-xl border border-slate-100 whitespace-pre-line">{{ $rencana->aktivitas_pendahuluan }}</p>
            </div>

            <div class="space-y-2">
                <span class="font-bold text-slate-800">2. Kegiatan Inti:</span>
                <p class="text-slate-700 bg-slate-50 p-3.5 rounded-xl border border-slate-100 whitespace-pre-line">{{ $rencana->aktivitas_inti }}</p>
            </div>

            <div class="space-y-2">
                <span class="font-bold text-slate-800">3. Kegiatan Penutup:</span>
                <p class="text-slate-700 bg-slate-50 p-3.5 rounded-xl border border-slate-100 whitespace-pre-line">{{ $rencana->aktivitas_penutup }}</p>
            </div>
        </div>

        @if($rencana->catatan)
        <div class="p-3.5 bg-slate-50 rounded-xl border text-slate-600">
            <strong>Catatan Tambahan:</strong> {{ $rencana->catatan }}
        </div>
        @endif

    </div>

</div>
@endsection
