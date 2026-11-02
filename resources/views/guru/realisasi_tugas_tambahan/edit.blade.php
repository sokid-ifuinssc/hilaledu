@extends('layouts.app')

@section('title', 'Edit Atribut Kesesuaian Realisasi Tugas Tambahan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" 
     x-data="{
        statusKesesuaian: '{{ $realisasi->status_kesesuaian ?? 'sesuai' }}',
        persentase: {{ $realisasi->persentase_kesesuaian ?? 100 }}
     }">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('guru.realisasi-tugas-tambahan.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1 mb-1">
                <i class="bi-arrow-left"></i> Kembali ke Matriks Realisasi
            </a>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-pencil-square text-indigo-600"></i>
                <span>Edit Atribut Kesesuaian Realisasi</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Perbarui evaluasi kesesuaian dan capaian pelaksanaan kegiatan terhadap rencana program kerja.
            </p>
        </div>
    </div>

    <!-- 1. Card Rujukan Rencana Program Kerja -->
    <div class="bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-900 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden">
        <div class="flex items-center justify-between gap-3 pb-4 border-b border-indigo-800/60">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-lg bg-indigo-500/30 text-indigo-200 font-black text-[11px] uppercase tracking-wider border border-indigo-400/30">
                    Rujukan Rencana Terpilih
                </span>
                <span class="text-xs text-indigo-300 font-medium">{{ $programKerja ? $programKerja->tugas_tambahan : $realisasi->tugas_tambahan }}</span>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-indigo-800/80 text-indigo-200">
                Mode Edit Realisasi
            </span>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="md:col-span-2 space-y-2">
                <span class="text-[10px] uppercase font-bold text-indigo-300 tracking-wider">Nama Program Kerja:</span>
                <h2 class="text-xl font-black text-white leading-snug">
                    {{ $programKerja ? $programKerja->nama_program : $realisasi->judul_kegiatan }}
                </h2>
                
                @if($programKerja && $programKerja->indikator_keberhasilan)
                <div class="pt-2 text-xs text-indigo-200/90 leading-relaxed bg-indigo-950/40 p-3 rounded-2xl border border-indigo-800/40">
                    <span class="text-[10px] font-bold uppercase text-indigo-300 block mb-0.5">Indikator Keberhasilan Terencana:</span>
                    <span>{{ $programKerja->indikator_keberhasilan }}</span>
                </div>
                @endif
            </div>

            <div class="space-y-3 bg-indigo-950/50 p-4 rounded-2xl border border-indigo-800/40 text-xs">
                <div>
                    <span class="text-[10px] font-bold text-indigo-300 uppercase block">Target Waktu Rencana:</span>
                    <strong class="text-indigo-100">{{ $programKerja->target_waktu ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-indigo-300 uppercase block">Estimasi Anggaran Rencana:</span>
                    <strong class="text-emerald-300">Rp {{ number_format($programKerja->anggaran ?? 0, 0, ',', '.') }}</strong>
                </div>
                @if($programKerja && $programKerja->tujuan)
                <div>
                    <span class="text-[10px] font-bold text-indigo-300 uppercase block">Sasaran / Tujuan:</span>
                    <span class="text-indigo-200 text-[11px] line-clamp-2">{{ $programKerja->tujuan }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 2. Form Edit Atribut Kesesuaian -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('guru.realisasi-tugas-tambahan.update', $realisasi) }}" enctype="multipart/form-data" class="space-y-6 text-xs">
            @csrf
            @method('PUT')

            <!-- Bagian A: Status & Persentase Kesesuaian -->
            <div class="space-y-3">
                <label class="block font-black text-slate-800 text-sm">
                    1. Evaluasi Status Kesesuaian dengan Rencana <span class="text-rose-500">*</span>
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                    <!-- Sesuai Rencana -->
                    <label class="relative flex flex-col p-4 bg-slate-50 border-2 rounded-2xl cursor-pointer hover:bg-emerald-50/40 transition select-none"
                           :class="statusKesesuaian === 'sesuai' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-950 shadow-xs' : 'border-slate-200 text-slate-700'">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-black text-xs text-emerald-800 flex items-center gap-1.5">
                                <i class="bi-check-circle-fill text-emerald-600 text-sm"></i>
                                Sesuai Rencana
                            </span>
                            <input type="radio" name="status_kesesuaian" value="sesuai" x-model="statusKesesuaian" @change="persentase = 100" class="text-emerald-600 focus:ring-emerald-500">
                        </div>
                        <p class="text-[11px] text-slate-500 leading-snug">
                            Terlaksana 100% tepat waktu, sasaran, dan indikator keberhasilan tercapai penuh.
                        </p>
                    </label>

                    <!-- Sebagian Sesuai -->
                    <label class="relative flex flex-col p-4 bg-slate-50 border-2 rounded-2xl cursor-pointer hover:bg-amber-50/40 transition select-none"
                           :class="statusKesesuaian === 'sebagian_sesuai' ? 'border-amber-500 bg-amber-50/50 text-amber-950 shadow-xs' : 'border-slate-200 text-slate-700'">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-black text-xs text-amber-800 flex items-center gap-1.5">
                                <i class="bi-slash-circle-fill text-amber-500 text-sm"></i>
                                Sebagian Sesuai
                            </span>
                            <input type="radio" name="status_kesesuaian" value="sebagian_sesuai" x-model="statusKesesuaian" @change="if(persentase >= 100) persentase = 75" class="text-amber-600 focus:ring-amber-500">
                        </div>
                        <p class="text-[11px] text-slate-500 leading-snug">
                            Terlaksana dengan penyesuaian waktu / indikator ketercapaian sebagian terpenuhi.
                        </p>
                    </label>

                    <!-- Tidak Sesuai -->
                    <label class="relative flex flex-col p-4 bg-slate-50 border-2 rounded-2xl cursor-pointer hover:bg-rose-50/40 transition select-none"
                           :class="statusKesesuaian === 'tidak_sesuai' ? 'border-rose-500 bg-rose-50/50 text-rose-950 shadow-xs' : 'border-slate-200 text-slate-700'">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-black text-xs text-rose-800 flex items-center gap-1.5">
                                <i class="bi-x-circle-fill text-rose-600 text-sm"></i>
                                Tidak Sesuai
                            </span>
                            <input type="radio" name="status_kesesuaian" value="tidak_sesuai" x-model="statusKesesuaian" @change="if(persentase > 50) persentase = 30" class="text-rose-600 focus:ring-rose-500">
                        </div>
                        <p class="text-[11px] text-slate-500 leading-snug">
                            Belum mencapai indikator rencana atau terdapat kendala mendasar yang mengalihkan target.
                        </p>
                    </label>
                </div>
            </div>

            <!-- Bagian B: Persentase, Tanggal, dan Anggaran Riil -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-slate-100">
                <!-- Persentase Kesesuaian -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-bold text-slate-700">Tingkat Kesesuaian (%) <span class="text-rose-500">*</span></label>
                        <span class="font-black text-indigo-600" x-text="persentase + '%'"></span>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="range" min="0" max="100" step="5" x-model="persentase" name="persentase_kesesuaian" class="w-full accent-indigo-600">
                        <input type="number" min="0" max="100" x-model="persentase" class="w-16 px-2 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-center font-bold text-xs">
                    </div>
                </div>

                <!-- Tanggal Pelaksanaan -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Pelaksanaan Realisasi <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_pelaksanaan" value="{{ $realisasi->tanggal_pelaksanaan ? $realisasi->tanggal_pelaksanaan->format('Y-m-d') : date('Y-m-d') }}" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                </div>

                <!-- Realisasi Anggaran -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Realisasi Anggaran Riil (Rp)</label>
                    <input type="number" name="realisasi_anggaran" min="0" value="{{ $realisasi->realisasi_anggaran ?? 0 }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>

            <!-- Bagian C: Keterangan Kesesuaian & Uraian Pelaksanaan -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <!-- Keterangan Kesesuaian -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Keterangan & Analisis Kesesuaian Terhadap Rencana
                    </label>
                    <textarea name="keterangan_kesesuaian" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">{{ old('keterangan_kesesuaian', $realisasi->keterangan_kesesuaian) }}</textarea>
                </div>

                <!-- Uraian Pelaksanaan Kegiatan -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Uraian Pelaksanaan Realisasi Kegiatan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="uraian_kegiatan" rows="3" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">{{ old('uraian_kegiatan', $realisasi->uraian_kegiatan) }}</textarea>
                </div>

                <!-- Hasil Capaian terhadap Indikator -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Hasil Capaian Terhadap Indikator Keberhasilan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="hasil_capaian" rows="2" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">{{ old('hasil_capaian', $realisasi->hasil_capaian) }}</textarea>
                </div>

                <!-- Kendala & Tindak Lanjut -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Kendala & Rencana Solusi / Tindak Lanjut
                    </label>
                    <textarea name="kendala_solusi" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">{{ old('kendala_solusi', $realisasi->kendala_solusi) }}</textarea>
                </div>
            </div>

            <!-- Bagian D: Bukti Dokumentasi & Update Status Rencana -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-slate-100">
                <!-- Foto Dokumentasi -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ganti Foto Dokumentasi (JPG/PNG)</label>
                    <input type="file" name="foto" accept="image/*" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    @if($realisasi->foto_dokumentasi)
                    <div class="mt-2 text-[11px] text-blue-600 font-bold">
                        <a href="{{ asset('storage/' . $realisasi->foto_dokumentasi) }}" target="_blank" class="hover:underline flex items-center gap-1">
                            <i class="bi-image"></i> Lihat Foto Saat Ini
                        </a>
                    </div>
                    @endif
                </div>

                <!-- Dokumen Pendukung -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ganti Dokumen Pendukung</label>
                    <input type="file" name="dokumen" accept=".pdf,.doc,.docx,.xls,.xlsx" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    @if($realisasi->dokumen_pendukung)
                    <div class="mt-2 text-[11px] text-emerald-600 font-bold">
                        <a href="{{ asset('storage/' . $realisasi->dokumen_pendukung) }}" target="_blank" class="hover:underline flex items-center gap-1">
                            <i class="bi-file-earmark-pdf"></i> Lihat Dokumen Saat Ini
                        </a>
                    </div>
                    @endif
                </div>

                <!-- Update Status Program Kerja -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status Rencana Program</label>
                    <select name="status_program" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                        <option value="tercapai" {{ ($programKerja->status ?? '') === 'tercapai' ? 'selected' : '' }}>Tercapai (Selesai & Sesuai)</option>
                        <option value="sedang_berjalan" {{ ($programKerja->status ?? '') === 'sedang_berjalan' ? 'selected' : '' }}>Sedang Berjalan</option>
                        <option value="tertunda" {{ ($programKerja->status ?? '') === 'tertunda' ? 'selected' : '' }}>Tertunda</option>
                    </select>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-5 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('guru.realisasi-tugas-tambahan.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl transition shadow-md shadow-amber-600/20 flex items-center gap-2">
                    <i class="bi-check2-circle"></i>
                    <span>Simpan Perubahan Atribut Kesesuaian</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
