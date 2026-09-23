@extends('layouts.app')

@section('title', 'Catat Realisasi Kerja Tugas Tambahan')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('guru.realisasi-tugas-tambahan.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1 mb-1">
                <i class="bi-arrow-left"></i> Kembali ke Riwayat Realisasi
            </a>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-briefcase-fill text-indigo-600"></i>
                <span>Input Realisasi Kerja Tugas Tambahan</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Catat pelaksanaan kegiatan manajerial/struktural sekolah dan lampirkan bukti foto/dokumen</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('guru.realisasi-tugas-tambahan.store') }}" enctype="multipart/form-data" class="space-y-5 text-xs">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Tugas Tambahan -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tugas Tambahan Terkait <span class="text-rose-500">*</span></label>
                    <select name="tugas_tambahan" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                        @foreach($listTugas as $t)
                        <option value="{{ $t }}" {{ $selectedTugas === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tanggal Pelaksanaan -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Pelaksanaan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_pelaksanaan" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>

            <!-- Program Kerja Rujukan (Opsional) -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Terkait Program Kerja (Opsional)</label>
                <select name="program_kerja_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                    <option value="">-- Tidak Terkait / Kegiatan Insidental --</option>
                    @foreach($programKerjaList as $pk)
                    <option value="{{ $pk->id }}">[{{ $pk->tugas_tambahan }}] {{ $pk->nama_program }}</option>
                    @endforeach
                </select>
                <span class="text-[10px] text-slate-400 mt-1 block">Hubungkan dengan rencana program kerja yang telah Anda susun di menu Informasi Penugasan Guru.</span>
            </div>

            <!-- Judul Kegiatan -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Judul / Nama Kegiatan <span class="text-rose-500">*</span></label>
                <input type="text" name="judul_kegiatan" required placeholder="Contoh: Koordinasi Guru Mapel Produktif & Evaluasi Silabus Pembelajaran" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
            </div>

            <!-- Uraian Kegiatan -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Uraian Pelaksanaan Kegiatan <span class="text-rose-500">*</span></label>
                <textarea name="uraian_kegiatan" rows="3" required placeholder="Jelaskan proses pelaksanaan kegiatan, waktu, peserta yang hadir, dan topik pembahasan..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600"></textarea>
            </div>

            <!-- Hasil Capaian -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Hasil / Capaian Kegiatan <span class="text-rose-500">*</span></label>
                <textarea name="hasil_capaian" rows="2" required placeholder="Hasil konkret atau output yang diperoleh dari kegiatan ini..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600"></textarea>
            </div>

            <!-- Kendala & Solusi -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Kendala & Solusi / Tindak Lanjut (Opsional)</label>
                <textarea name="kendala_solusi" rows="2" placeholder="Catatan kendala yang dihadapi serta rencana perbaikan ke depan..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600"></textarea>
            </div>

            <!-- Upload Bukti -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Foto Bukti Dokumentasi (JPG/PNG max 4MB)</label>
                    <input type="file" name="foto" accept="image/*" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Dokumen Pendukung / Notulensi (PDF/DOC/XLS max 5MB)</label>
                    <input type="file" name="dokumen" accept=".pdf,.doc,.docx,.xls,.xlsx" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('guru.realisasi-tugas-tambahan.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-600/20 flex items-center gap-2">
                    <i class="bi-check-circle-fill"></i>
                    <span>Simpan Laporan Realisasi</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
