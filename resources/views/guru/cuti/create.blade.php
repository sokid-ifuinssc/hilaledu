@extends('layouts.app')

@section('title', 'Ajukan Permohonan Cuti')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div>
        <div class="text-xs text-slate-500 mb-1">
            <a href="{{ route('guru.cuti.index') }}" class="text-blue-600 hover:underline">&larr; Kembali ke Daftar Cuti</a>
        </div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
            <i class="bi-person-dash text-blue-600"></i>
            <span>Form Pengajuan Cuti & Penugasan Inval</span>
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">Tentukan rentang tanggal cuti dan pilih guru pengganti untuk menjaga kelancaran KBM</p>
    </div>

    <form method="POST" action="{{ route('guru.cuti.store') }}" enctype="multipart/form-data" class="space-y-6 text-xs">
        @csrf

        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-5">
            
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jenis Cuti</label>
                <select name="jenis_cuti" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-semibold text-slate-800 focus:ring-2 focus:ring-blue-600">
                    <option value="Cuti Tahunan">Cuti Tahunan</option>
                    <option value="Cuti Sakit">Cuti Sakit (Disertai Surat Dokter)</option>
                    <option value="Cuti Bersalin / Melahirkan">Cuti Bersalin / Melahirkan</option>
                    <option value="Cuti Alasan Penting">Cuti Karena Alasan Penting / Keperluan Keluarga</option>
                    <option value="Cuti Ibadah Haji / Umroh">Cuti Ibadah Keagamaan (Haji / Umroh)</option>
                    <option value="Cuti Studi / Pelatihan">Cuti Studi Lanjut / Pelatihan Kedinasan</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Mulai Cuti</label>
                    <input type="date" name="tanggal_mulai" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Selesai Cuti</label>
                    <input type="date" name="tanggal_selesai" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-blue-600">
                </div>
            </div>

            <!-- Guru Pengganti (Inval) -->
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Guru Pengganti / Inval (Opsional)</label>
                <select name="guru_pengganti_id" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-600">
                    <option value="">-- Tanpa Guru Pengganti (Jadwal Ditandai Cuti) --</option>
                    @foreach($guruList as $g)
                    <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->jabatan_utama ?: 'Guru Pengajar' }})</option>
                    @endforeach
                </select>
                <span class="text-[11px] text-slate-400 mt-1 block">Guru pengganti yang dipilih akan otomatis menerima mandat jadwal KBM selama masa cuti Anda.</span>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alasan Lengkap Pengajuan Cuti</label>
                <textarea name="alasan" rows="3" placeholder="Jelaskan keperluan cuti secara jelas..." required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium"></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Lampiran Surat Pendukung / Surat Keterangan (PDF/Gambar)</label>
                <input type="file" name="lampiran" class="w-full text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('guru.cuti.index') }}" class="px-5 py-3 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-600/30 transition flex items-center gap-2">
                <i class="bi-send text-base"></i>
                <span>Kirim Pengajuan Cuti</span>
            </button>
        </div>

    </form>

</div>
@endsection
