@extends('layouts.app')

@section('title', 'Realisasi Kerja Tugas Tambahan')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-briefcase-fill text-indigo-600"></i>
                <span>Realisasi Kerja Tugas Tambahan</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Jurnal pencatatan pelaksanaan kegiatan dan ketercapaian tugas tambahan struktural dewan guru / pimpinan sekolah.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('guru.realisasi-tugas-tambahan.create') }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-indigo-600/20 transition">
                <i class="bi-plus-circle-fill text-sm"></i>
                <span>Catat Realisasi Baru</span>
            </a>
            <a href="{{ route('guru.realisasi-tugas-tambahan.print', ['tugas' => $tugasFilter, 'bulan' => $bulanFilter]) }}" target="_blank" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-bold rounded-xl text-xs flex items-center gap-2 shadow-xs transition">
                <i class="bi-printer-fill text-sm text-slate-600"></i>
                <span>Cetak Laporan (PDF)</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4 text-xs">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Filter Tugas Tambahan</label>
                <select name="tugas" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="">Semua Tugas Tambahan</option>
                    @foreach($listTugas as $t)
                    <option value="{{ $t }}" {{ $tugasFilter === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Bulan Pelaksanaan</label>
                <input type="month" name="bulan" value="{{ $bulanFilter }}" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
            </div>
            @if($tugasFilter || $bulanFilter)
            <div class="pt-4">
                <a href="{{ route('guru.realisasi-tugas-tambahan.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs inline-block">
                    Reset Filter
                </a>
            </div>
            @endif
        </form>

        <div class="text-slate-400 font-medium text-xs">
            Total Laporan: <strong class="text-slate-800">{{ $realisasis->total() }}</strong> Kegiatan
        </div>
    </div>

    <!-- Table of Realisasi -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 border-b font-bold uppercase text-[10px] text-slate-400">
                    <tr>
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">Tugas Tambahan</th>
                        <th class="px-4 py-3.5">Kegiatan & Uraian</th>
                        <th class="px-4 py-3.5">Hasil / Capaian</th>
                        <th class="px-4 py-3.5 text-center">Bukti / Foto</th>
                        <th class="px-4 py-3.5 text-center">Validasi</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y font-medium text-slate-700">
                    @forelse($realisasis as $r)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-4 py-3.5 whitespace-nowrap font-bold text-slate-900">
                            {{ \Carbon\Carbon::parse($r->tanggal_pelaksanaan)->isoFormat('D MMM Y') }}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-black text-[11px] border border-indigo-200">
                                {{ $r->tugas_tambahan }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 max-w-sm">
                            <div class="font-extrabold text-slate-900 text-xs">{{ $r->judul_kegiatan }}</div>
                            <div class="text-[11px] text-slate-500 line-clamp-2 mt-0.5">{{ $r->uraian_kegiatan }}</div>
                            @if($r->programKerja)
                            <div class="text-[10px] text-blue-600 font-semibold mt-1">
                                &bull; Program: {{ $r->programKerja->nama_program }}
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 max-w-xs text-slate-600">
                            <div class="line-clamp-2">{{ $r->hasil_capaian }}</div>
                            @if($r->kendala_solusi)
                            <div class="text-[10px] text-amber-700 mt-0.5 line-clamp-1 italic">
                                Solusi: {{ $r->kendala_solusi }}
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            @if($r->foto_dokumentasi)
                            <a href="{{ asset('storage/' . $r->foto_dokumentasi) }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:underline font-bold text-[11px]">
                                <i class="bi-image"></i> Foto
                            </a>
                            @endif
                            @if($r->dokumen_pendukung)
                            <a href="{{ asset('storage/' . $r->dokumen_pendukung) }}" target="_blank" class="inline-flex items-center gap-1 text-emerald-600 hover:underline font-bold text-[11px] ml-2">
                                <i class="bi-file-earmark-pdf"></i> Dok
                            </a>
                            @endif
                            @if(!$r->foto_dokumentasi && !$r->dokumen_pendukung)
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase {{ $r->status_validasi_badge }}">
                                {{ $r->status_validasi_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('guru.realisasi-tugas-tambahan.show', $r) }}" class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition" title="Lihat Detail">
                                    <i class="bi-eye"></i>
                                </a>
                                <form method="POST" action="{{ route('guru.realisasi-tugas-tambahan.destroy', $r) }}" onsubmit="return confirm('Hapus laporan realisasi kegiatan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                        <i class="bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                            <i class="bi-clipboard-x text-3xl block mb-2"></i>
                            <span class="font-bold text-slate-700">Belum Ada Laporan Realisasi Tugas Tambahan</span>
                            <p class="text-xs mt-1">Klik tombol "Catat Realisasi Baru" di atas untuk menambahkan laporan kegiatan tugas tambahan Anda.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($realisasis->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $realisasis->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
