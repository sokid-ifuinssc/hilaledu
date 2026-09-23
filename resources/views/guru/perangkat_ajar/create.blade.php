@extends('layouts.app')

@section('title', 'Input Link Perangkat Pembelajaran')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div>
        <div class="text-xs text-slate-500 mb-1">
            <a href="{{ route('guru.perangkat-ajar.index') }}" class="text-blue-600 hover:underline">&larr; Kembali ke Daftar Perangkat Ajar</a>
        </div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
            <i class="bi-google text-blue-600"></i>
            <span>Kirim Link Google Drive Perangkat Pembelajaran</span>
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">Pastikan folder atau file Google Drive sudah diatur hak aksesnya (Minimal "Siapa saja yang memiliki link dapat melihat")</p>
    </div>

    <form method="POST" action="{{ route('guru.perangkat-ajar.store') }}" class="space-y-6 text-xs">
        @csrf

        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-5">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Mata Pelajaran</label>
                    <select name="mata_pelajaran_id" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-600">
                        @foreach($mapels as $m)
                        <option value="{{ $m->id }}">{{ $m->nama }} ({{ $m->kode }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kelas</label>
                    <select name="kelas" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-600">
                        @foreach($kelasList as $k)
                        <option value="{{ $k->nama_kelas ?? $k->nama }}">{{ $k->nama_kelas ?? $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tahun Ajaran</label>
                    <select name="tahun_ajaran" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800">
                        <option value="2026/2027" selected>2026/2027 (Aktif)</option>
                        <option value="2027/2028">2027/2028</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Semester</label>
                    <select name="semester" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                        <option value="ganjil">Semester Ganjil</option>
                        <option value="genap">Semester Genap</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Judul / Deskripsi Perangkat</label>
                <input type="text" name="judul" placeholder="Contoh: Perangkat Ajar Lengkap Informatika Fase E Kelas X (Sudah TTD)" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-semibold text-slate-800 focus:ring-2 focus:ring-blue-600">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Link Google Drive (Folder / Dokumen PDF Lengkap)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-blue-600">
                        <i class="bi-link-45deg text-lg"></i>
                    </span>
                    <input type="url" name="link_gdrive" placeholder="https://drive.google.com/drive/folders/..." required class="w-full pl-10 pr-4 py-3 bg-blue-50/40 border border-blue-300 rounded-xl font-mono text-xs font-semibold text-blue-900 focus:ring-2 focus:ring-blue-600">
                </div>
                <span class="text-[11px] text-slate-400 mt-1 block">Pastikan dokumen sudah ditandatangani secara digital atau basah sebelum dibagikan.</span>
            </div>

            <!-- Checklist Kelengkapan Dokumen -->
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-2">Checklist Berkas Yang Termasuk Di Dalam Link:</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                    @php
                        $dokumens = [
                            'Capaian Pembelajaran (CP)',
                            'Alur Tujuan Pembelajaran (ATP)',
                            'Modul Ajar Harian (RPP)',
                            'Program Tahunan (Prota)',
                            'Program Semester (Promes)',
                            'Kriteria Ketercapaian (KKTP)',
                            'Jadwal & Silabus Mapel',
                            'Instrumen Asesmen & Kisi-kisi',
                        ];
                    @endphp
                    @foreach($dokumens as $dok)
                    <label class="flex items-center gap-2 p-2.5 bg-slate-50 hover:bg-slate-100 rounded-xl border border-slate-200 cursor-pointer font-medium text-slate-800">
                        <input type="checkbox" name="kelengkapan[{{ $dok }}]" value="1" checked class="w-4 h-4 rounded text-blue-600">
                        <span>{{ $dok }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('guru.perangkat-ajar.index') }}" class="px-5 py-3 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-600/30 transition flex items-center gap-2">
                <i class="bi-cloud-arrow-up text-base"></i>
                <span>Kirim Link Untuk Diverifikasi Waka</span>
            </button>
        </div>
    </form>

</div>
@endsection
