@extends('layouts.app')

@section('title', 'Edit Agenda Kegiatan Sekolah')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div>
        <div class="text-xs text-slate-500 mb-1">
            <a href="{{ route('guru.kegiatan.show', $kegiatan) }}" class="text-blue-600 hover:underline">&larr; Kembali ke Detail Agenda</a>
        </div>
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="bi-pencil-square text-blue-600"></i>
                    <span>Edit Agenda Kegiatan Sekolah</span>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Dibuat oleh: <strong class="text-slate-700">{{ $kegiatan->author->name ?? 'Pimpinan' }}</strong> 
                    &bull; Editor saat ini: <strong class="text-blue-700">{{ auth()->user()->name }}</strong>
                </p>
            </div>
            <span class="px-3 py-1 rounded-xl text-xs font-black uppercase tracking-wider {{ $kegiatan->bidang_color }}">
                {{ $kegiatan->bidang_label }}
            </span>
        </div>
    </div>

    <!-- Edit Form -->
    <form method="POST" action="{{ route('guru.kegiatan.update', $kegiatan) }}" enctype="multipart/form-data" class="space-y-6 text-xs">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-5">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Bidang Penginput / Penanggung Jawab</label>
                    <select name="bidang" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-blue-600">
                        <option value="waka_kurikulum" {{ old('bidang', $kegiatan->bidang) === 'waka_kurikulum' ? 'selected' : '' }}>Waka Kurikulum & Akademik</option>
                        <option value="waka_sarpras" {{ old('bidang', $kegiatan->bidang) === 'waka_sarpras' ? 'selected' : '' }}>Waka Sarana & Prasarana</option>
                        <option value="waka_kesiswaan" {{ old('bidang', $kegiatan->bidang) === 'waka_kesiswaan' ? 'selected' : '' }}>Waka Kesiswaan & Karakter</option>
                        <option value="waka_hubin" {{ old('bidang', $kegiatan->bidang) === 'waka_hubin' ? 'selected' : '' }}>Waka Hubin (Humas & Industri)</option>
                        <option value="pembina_osis" {{ old('bidang', $kegiatan->bidang) === 'pembina_osis' ? 'selected' : '' }}>Pembina OSIS & Kesiswaan</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kategori Kegiatan</label>
                    <input type="text" name="kategori" placeholder="Contoh: Rapat Koordinasi, Supervisi, Upacara..." value="{{ old('kategori', $kegiatan->kategori) }}" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Judul Agenda / Kegiatan</label>
                <input type="text" name="judul" value="{{ old('judul', $kegiatan->judul) }}" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-900 focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Mulai Pelaksanaan</label>
                    <input type="date" name="tanggal_kegiatan" value="{{ old('tanggal_kegiatan', $kegiatan->tanggal_kegiatan ? $kegiatan->tanggal_kegiatan->format('Y-m-d') : '') }}" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Selesai (Rentang Waktu)</label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $kegiatan->tanggal_selesai ? $kegiatan->tanggal_selesai->format('Y-m-d') : ($kegiatan->tanggal_kegiatan ? $kegiatan->tanggal_kegiatan->format('Y-m-d') : '')) }}" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Waktu Mulai</label>
                    <input type="time" name="waktu_mulai" value="{{ old('waktu_mulai', substr($kegiatan->waktu_mulai, 0, 5)) }}" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Waktu Selesai</label>
                    <input type="time" name="waktu_selesai" value="{{ old('waktu_selesai', substr($kegiatan->waktu_selesai, 0, 5)) }}" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tempat / Lokasi</label>
                    <input type="text" name="tempat" value="{{ old('tempat', $kegiatan->tempat) }}" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Target Peserta / Sasaran</label>
                    <input type="text" name="sasaran" value="{{ old('sasaran', $kegiatan->sasaran) }}" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Uraian / Deskripsi Kegiatan</label>
                <textarea name="deskripsi" rows="6" required class="w-full p-3.5 bg-slate-50 border border-slate-300 rounded-2xl font-medium leading-relaxed">{{ old('deskripsi', $kegiatan->deskripsi) }}</textarea>
            </div>

            <!-- Checkbox Sinkronisasi Kalender Akademik -->
            <div class="p-4 bg-emerald-50/80 border border-emerald-300 rounded-2xl flex items-start gap-3">
                <input type="checkbox" name="sinkron_kalender" id="check_sinkron" value="1" {{ $kegiatan->kalender_akademik_event_id ? 'checked' : '' }}
                       class="mt-1 w-5 h-5 text-emerald-600 rounded focus:ring-emerald-500 cursor-pointer">
                <label for="check_sinkron" class="cursor-pointer space-y-0.5">
                    <span class="font-black text-emerald-950 text-xs flex items-center gap-1.5">
                        <i class="bi-calendar-check-fill text-emerald-600"></i>
                        <span>Sinkronkan & Tampilkan di Kalender Akademik Sekolah</span>
                        @if($kegiatan->kalender_akademik_event_id)
                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-200 text-emerald-900 font-extrabold">Sudah Terhubung</span>
                        @endif
                    </span>
                    <p class="text-[11px] text-emerald-800 leading-relaxed">
                        Saat aktif, perubahan tanggal, judul, dan detail kegiatan ini akan otomatis mengupdate agenda pada Kalender Akademik Resmi Sekolah.
                    </p>
                </label>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Lampiran Surat Edaran (PDF/Gambar)</label>
                @if($kegiatan->lampiran)
                <div class="mb-2 flex items-center gap-2 text-xs text-blue-600 font-semibold">
                    <i class="bi-file-earmark-check"></i>
                    <a href="{{ asset('storage/' . $kegiatan->lampiran) }}" target="_blank" class="hover:underline">Lihat Berkas Lampiran Saat Ini</a>
                    <span class="text-slate-400 text-[11px]">(Pilih file baru jika ingin mengganti)</span>
                </div>
                @endif
                <input type="file" name="lampiran" class="w-full text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
            </div>

        </div>

        <div class="flex items-center justify-between">
            <div>
                @if($kegiatan->editor)
                <span class="text-[11px] text-slate-400">
                    <i class="bi-clock-history"></i> Terakhir diedit oleh <strong>{{ $kegiatan->editor->name }}</strong> ({{ $kegiatan->updated_at->format('d M Y H:i') }})
                </span>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('guru.kegiatan.show', $kegiatan) }}" class="px-5 py-3 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-600/30 transition flex items-center gap-2">
                    <i class="bi-check-lg text-base"></i>
                    <span>Simpan Perubahan Agenda</span>
                </button>
            </div>
        </div>

    </form>

</div>
@endsection
