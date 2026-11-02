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
                Evaluasi dan pencatatan kesesuaian pelaksanaan program kerja tugas tambahan terhadap rencana yang telah dibuat.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('guru.penugasan.index', ['tab' => 'tambahan']) }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs flex items-center gap-2 transition border border-slate-200 shadow-xs">
                <i class="bi-journal-plus text-sm text-indigo-600"></i>
                <span>Kelola Rencana Program</span>
            </a>
            <a href="{{ route('guru.realisasi-tugas-tambahan.print', ['tugas' => $tugasFilter]) }}" target="_blank" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-bold rounded-xl text-xs flex items-center gap-2 shadow-xs transition">
                <i class="bi-printer-fill text-sm text-slate-600"></i>
                <span>Cetak Laporan Kesesuaian (PDF)</span>
            </a>
        </div>
    </div>

    <!-- Ringkasan Statistik Metrik Ketercapaian -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Rencana</span>
            <div class="text-xl font-black text-slate-900 mt-0.5">{{ $totalProgram }}</div>
            <span class="text-[10px] text-slate-500">Program Kerja</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 block">Sesuai Rencana</span>
            <div class="text-xl font-black text-emerald-600 mt-0.5">{{ $totalSesuai }}</div>
            <span class="text-[10px] text-emerald-700 font-medium">100% Sesuai Rencana</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 block">Sebagian Sesuai</span>
            <div class="text-xl font-black text-amber-600 mt-0.5">{{ $totalSebagian }}</div>
            <span class="text-[10px] text-amber-700 font-medium">Ada Penyesuaian</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-500 block">Tidak Sesuai</span>
            <div class="text-xl font-black text-rose-500 mt-0.5">{{ $totalTidakSesuai }}</div>
            <span class="text-[10px] text-rose-600 font-medium">Belum Tercapai</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Belum Realisasi</span>
            <div class="text-xl font-black text-slate-700 mt-0.5">{{ $totalBelum }}</div>
            <span class="text-[10px] text-slate-500">Menunggu Pelaksanaan</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-indigo-200 bg-indigo-50/30 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 block">Rata-rata Kesesuaian</span>
            <div class="text-xl font-black text-indigo-700 mt-0.5">{{ $avgKesesuaian }}%</div>
            <span class="text-[10px] text-indigo-600 font-semibold">Tingkat Capaian Riil</span>
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
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Kesesuaian Realisasi</label>
                <select name="status_kesesuaian" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="">Semua Status Kesesuaian</option>
                    <option value="sesuai" {{ $statusKesesuaianFilter === 'sesuai' ? 'selected' : '' }}>🟢 Sesuai Rencana (100%)</option>
                    <option value="sebagian_sesuai" {{ $statusKesesuaianFilter === 'sebagian_sesuai' ? 'selected' : '' }}>🟡 Sebagian Sesuai</option>
                    <option value="tidak_sesuai" {{ $statusKesesuaianFilter === 'tidak_sesuai' ? 'selected' : '' }}>🔴 Tidak Sesuai</option>
                    <option value="belum_realisasi" {{ $statusKesesuaianFilter === 'belum_realisasi' ? 'selected' : '' }}>⚪ Belum Terealisasi</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Status Program</label>
                <select name="status_program" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="">Semua Status Program</option>
                    <option value="terencana" {{ $statusProgramFilter === 'terencana' ? 'selected' : '' }}>Terencana</option>
                    <option value="sedang_berjalan" {{ $statusProgramFilter === 'sedang_berjalan' ? 'selected' : '' }}>Sedang Berjalan</option>
                    <option value="tercapai" {{ $statusProgramFilter === 'tercapai' ? 'selected' : '' }}>Tercapai</option>
                    <option value="tertunda" {{ $statusProgramFilter === 'tertunda' ? 'selected' : '' }}>Tertunda</option>
                </select>
            </div>
            @if($tugasFilter || $statusKesesuaianFilter || $statusProgramFilter)
            <div class="pt-4">
                <a href="{{ route('guru.realisasi-tugas-tambahan.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs inline-block">
                    Reset Filter
                </a>
            </div>
            @endif
        </form>

        <div class="text-slate-500 font-medium text-xs">
            Menampilkan <strong class="text-slate-800">{{ $programKerjas->total() }}</strong> Rencana Program Kerja
        </div>
    </div>

    <!-- Tabel Matriks Rencana Program Kerja & Atribut Kesesuaian Realisasi -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 border-b font-bold uppercase text-[10px] text-slate-400">
                    <tr>
                        <th class="px-4 py-3.5 text-center" width="4%">No</th>
                        <th class="px-4 py-3.5" width="18%">Tugas Tambahan & Target</th>
                        <th class="px-4 py-3.5" width="28%">Rencana Program & Indikator</th>
                        <th class="px-4 py-3.5" width="28%">Kesesuaian & Hasil Realisasi</th>
                        <th class="px-4 py-3.5 text-center" width="10%">Status Program</th>
                        <th class="px-4 py-3.5 text-center" width="12%">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y font-medium text-slate-700">
                    @forelse($programKerjas as $idx => $pk)
                    @php
                        $r = $pk->realisasi; // Relasi realisasi terbaru
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition align-top">
                        <td class="px-4 py-3.5 text-center font-bold text-slate-400">
                            {{ $programKerjas->firstItem() + $idx }}
                        </td>
                        <td class="px-4 py-3.5 space-y-1.5">
                            <span class="inline-block px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-black text-[11px] border border-indigo-200">
                                {{ $pk->tugas_tambahan }}
                            </span>
                            @if($pk->target_waktu)
                            <div class="text-[11px] text-slate-600 flex items-center gap-1">
                                <i class="bi-calendar-event text-slate-400"></i>
                                <span>Target: <strong>{{ $pk->target_waktu }}</strong></span>
                            </div>
                            @endif
                            @if($pk->anggaran > 0)
                            <div class="text-[10px] text-slate-500">
                                Anggaran Rencana: <span class="font-bold text-slate-700">Rp {{ number_format($pk->anggaran, 0, ',', '.') }}</span>
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 space-y-1">
                            <div class="font-extrabold text-slate-900 text-xs leading-snug">
                                {{ $pk->nama_program }}
                            </div>
                            @if($pk->indikator_keberhasilan)
                            <div class="text-[11px] text-slate-600 bg-slate-50 p-2 rounded-xl border border-slate-100">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">Indikator Terencana:</span>
                                {{ $pk->indikator_keberhasilan }}
                            </div>
                            @endif
                            @if($pk->tujuan)
                            <div class="text-[10px] text-slate-400 line-clamp-1 italic">
                                Sasaran: {{ $pk->tujuan }}
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            @if($r)
                            <!-- Telah Mengisi Atribut Kesesuaian -->
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $r->status_kesesuaian_badge }}">
                                        {{ $r->status_kesesuaian_label }}
                                    </span>
                                    <span class="text-[11px] text-slate-500 font-semibold">
                                        <i class="bi-clock-history"></i> {{ \Carbon\Carbon::parse($r->tanggal_pelaksanaan)->isoFormat('D MMM Y') }}
                                    </span>
                                </div>
                                
                                @if($r->keterangan_kesesuaian)
                                <div class="text-[11px] text-slate-700 bg-emerald-50/40 p-2 rounded-xl border border-emerald-100">
                                    <span class="text-[10px] font-bold text-emerald-800 uppercase block">Keterangan Kesesuaian:</span>
                                    {{ $r->keterangan_kesesuaian }}
                                </div>
                                @endif

                                <div class="text-[11px] text-slate-600">
                                    <strong>Capaian:</strong> {{ \Illuminate\Support\Str::limit($r->hasil_capaian, 90) }}
                                </div>

                                @if($r->realisasi_anggaran > 0)
                                <div class="text-[10px] text-slate-500">
                                    Realisasi Anggaran: <strong class="text-slate-800">Rp {{ number_format($r->realisasi_anggaran, 0, ',', '.') }}</strong>
                                    @if($pk->anggaran > 0)
                                        @php $persenAnggaran = round(($r->realisasi_anggaran / $pk->anggaran) * 100); @endphp
                                        <span class="text-[10px] text-slate-400">({{ $persenAnggaran }}% rencana)</span>
                                    @endif
                                </div>
                                @endif

                                <div class="flex items-center gap-3 pt-0.5 text-[11px]">
                                    @if($r->foto_dokumentasi)
                                    <a href="{{ asset('storage/' . $r->foto_dokumentasi) }}" target="_blank" class="text-blue-600 hover:underline font-bold inline-flex items-center gap-1">
                                        <i class="bi-image"></i> Foto Bukti
                                    </a>
                                    @endif
                                    @if($r->dokumen_pendukung)
                                    <a href="{{ asset('storage/' . $r->dokumen_pendukung) }}" target="_blank" class="text-emerald-600 hover:underline font-bold inline-flex items-center gap-1">
                                        <i class="bi-file-earmark-pdf"></i> Dokumen
                                    </a>
                                    @endif
                                </div>
                            </div>
                            @else
                            <!-- Belum Mengisi Atribut Kesesuaian -->
                            <div class="py-2 px-3 bg-amber-50/70 border border-amber-200/60 rounded-xl space-y-1">
                                <div class="flex items-center gap-1.5 text-amber-800 font-bold text-[11px]">
                                    <i class="bi-exclamation-circle-fill text-amber-500"></i>
                                    <span>Belum Diisi Kesesuaiannya</span>
                                </div>
                                <p class="text-[10px] text-amber-700/80">
                                    Rencana program kerja ini belum dicatat atribut kesesuaian dan realisasinya.
                                </p>
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase {{ $pk->status_badge }}">
                                {{ $pk->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            @if($r)
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('guru.realisasi-tugas-tambahan.edit', $r) }}" class="p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition" title="Edit Atribut Kesesuaian">
                                    <i class="bi-pencil-square"></i>
                                </a>
                                <a href="{{ route('guru.realisasi-tugas-tambahan.show', $r) }}" class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition" title="Lihat Detail Realisasi">
                                    <i class="bi-eye"></i>
                                </a>
                                <form method="POST" action="{{ route('guru.realisasi-tugas-tambahan.destroy', $r) }}" onsubmit="return confirm('Hapus catatan realisasi kesesuaian ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Hapus Realisasi">
                                        <i class="bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                            @else
                            <a href="{{ route('guru.realisasi-tugas-tambahan.create', ['program_id' => $pk->id]) }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-[11px] shadow-sm shadow-indigo-600/20 transition">
                                <i class="bi-check2-circle text-xs"></i>
                                <span>Isi Kesesuaian</span>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                            <div class="max-w-md mx-auto space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 mx-auto flex items-center justify-center text-2xl">
                                    <i class="bi-calendar2-range"></i>
                                </div>
                                <h3 class="font-black text-slate-800 text-sm">Belum Ada Rencana Program Kerja Tugas Tambahan</h3>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Silakan buat terlebih dahulu Rencana Program Kerja pada tugas tambahan Anda di menu Informasi Penugasan Guru, kemudian Anda dapat mengisi atribut kesesuaian pelaksanaannya di sini.
                                </p>
                                <div class="pt-2">
                                    <a href="{{ route('guru.penugasan.index', ['tab' => 'tambahan']) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-md shadow-indigo-600/20 transition">
                                        <i class="bi-plus-circle-fill"></i>
                                        <span>Buat Rencana Program Kerja Sekarang</span>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($programKerjas->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $programKerjas->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
