@extends('layouts.app')

@section('title', 'Detail Realisasi Kerja Tugas Tambahan')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('guru.realisasi-tugas-tambahan.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1 mb-1">
                <i class="bi-arrow-left"></i> Kembali ke Riwayat
            </a>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Detail Laporan Realisasi Tugas</h1>
            <p class="text-xs text-slate-500 mt-0.5">Penugasan: {{ $realisasi->tugas_tambahan }} &bull; Oleh: {{ $realisasi->guru?->name }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.realisasi-tugas-tambahan.print', ['tugas' => $realisasi->tugas_tambahan]) }}" target="_blank" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                <i class="bi-printer-fill"></i>
                <span>Cetak Lembar Laporan</span>
            </a>
        </div>
    </div>

    <!-- Content Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6 text-xs">
        <!-- Status & Meta -->
        <div class="flex flex-wrap items-center justify-between gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-200">
            <div>
                <span class="text-[10px] text-slate-400 font-bold uppercase block">Tanggal Pelaksanaan</span>
                <strong class="text-sm font-black text-slate-900">{{ \Carbon\Carbon::parse($realisasi->tanggal_pelaksanaan)->isoFormat('dddd, D MMMM Y') }}</strong>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 font-bold uppercase block">Status Validasi</span>
                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $realisasi->status_validasi_badge }}">
                    {{ $realisasi->status_validasi_label }}
                </span>
            </div>
        </div>

        <!-- Judul -->
        <div>
            <h3 class="font-extrabold text-base text-slate-900">{{ $realisasi->judul_kegiatan }}</h3>
            @if($realisasi->programKerja)
            <p class="text-xs text-blue-600 font-semibold mt-1">
                Terkait Program: {{ $realisasi->programKerja->nama_program }}
            </p>
            @endif
        </div>

        <!-- Uraian -->
        <div class="space-y-1">
            <h4 class="font-bold text-slate-700 text-xs uppercase tracking-wider text-[11px]">Uraian Pelaksanaan Kegiatan:</h4>
            <p class="text-slate-800 leading-relaxed bg-slate-50 p-4 rounded-2xl border border-slate-100 whitespace-pre-line">{{ $realisasi->uraian_kegiatan }}</p>
        </div>

        <!-- Hasil -->
        <div class="space-y-1">
            <h4 class="font-bold text-slate-700 text-xs uppercase tracking-wider text-[11px]">Hasil & Capaian:</h4>
            <p class="text-slate-800 leading-relaxed bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100 whitespace-pre-line">{{ $realisasi->hasil_capaian }}</p>
        </div>

        @if($realisasi->kendala_solusi)
        <div class="space-y-1">
            <h4 class="font-bold text-slate-700 text-xs uppercase tracking-wider text-[11px]">Kendala & Rencana Solusi:</h4>
            <p class="text-amber-900 leading-relaxed bg-amber-50/50 p-4 rounded-2xl border border-amber-100 whitespace-pre-line">{{ $realisasi->kendala_solusi }}</p>
        </div>
        @endif

        <!-- Foto Dokumentasi -->
        @if($realisasi->foto_dokumentasi)
        <div class="space-y-2 pt-2 border-t border-slate-100">
            <h4 class="font-bold text-slate-700 text-xs uppercase tracking-wider text-[11px]">Foto Bukti Dokumentasi:</h4>
            <div class="rounded-2xl overflow-hidden border border-slate-200 max-w-md">
                <img src="{{ asset('storage/' . $realisasi->foto_dokumentasi) }}" alt="Dokumentasi" class="w-full object-cover">
            </div>
        </div>
        @endif

        @if($realisasi->dokumen_pendukung)
        <div class="pt-2 border-t border-slate-100">
            <a href="{{ asset('storage/' . $realisasi->dokumen_pendukung) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl font-bold transition">
                <i class="bi-file-earmark-arrow-down-fill text-blue-600 text-base"></i>
                <span>Unduh Dokumen / Berkas Lampiran</span>
            </a>
        </div>
        @endif
    </div>

</div>
@endsection
