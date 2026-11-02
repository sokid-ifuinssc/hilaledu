@extends('layouts.app')

@section('title', 'Rencana Program Kerja Tugas Tambahan')
@section('page-title', 'Rencana Program Kerja Tambahan')

@section('content')
<div class="space-y-6" x-data="{
    modalCreate: {{ request('create') ? 'true' : 'false' }},
    modalEdit: false,
    selectedTugas: '{{ addslashes(request('tugas', $listTugasTambahan[0] ?? '')) }}',
    editData: {
        id: '',
        tugas_tambahan: '',
        nama_program: '',
        tahun_ajaran: '{{ $tahunAjaran }}',
        semester: '{{ $semesterAktif }}',
        tujuan: '',
        target_waktu: '',
        anggaran: 0,
        indikator_keberhasilan: '',
        status: 'terencana'
    },
    openEdit(pk) {
        this.editData = {
            id: pk.id,
            tugas_tambahan: pk.tugas_tambahan || '',
            nama_program: pk.nama_program || '',
            tahun_ajaran: pk.tahun_ajaran || '{{ $tahunAjaran }}',
            semester: pk.semester || '{{ $semesterAktif }}',
            tujuan: pk.tujuan || '',
            target_waktu: pk.target_waktu || '',
            anggaran: pk.anggaran || 0,
            indikator_keberhasilan: pk.indikator_keberhasilan || '',
            status: pk.status || 'terencana'
        };
        this.modalEdit = true;
    }
}">

    <!-- Sub-Navigation Banner (Rencana vs Laporan Realisasi) -->
    <div class="bg-white p-2 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-2">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('guru.program-kerja-tambahan.index') }}"
               class="px-4 py-2.5 rounded-xl text-xs font-black flex items-center gap-2 bg-indigo-600 text-white shadow-sm shadow-indigo-600/20 transition">
                <i class="bi-journal-plus text-sm"></i>
                <span>1. Rencana Program Kerja</span>
                <span class="px-2 py-0.5 rounded-full bg-white/20 text-white text-[10px]">{{ $totalProgram }}</span>
            </a>
            <a href="{{ route('guru.realisasi-tugas-tambahan.index') }}"
               class="px-4 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2 text-slate-600 hover:bg-slate-100 transition">
                <i class="bi-clipboard2-check-fill text-sm text-emerald-600"></i>
                <span>2. Laporan Realisasi Program Kerja</span>
            </a>
        </div>
        <div class="flex items-center gap-2 pr-2">
            <span class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold flex items-center gap-1.5">
                <i class="bi-calendar-range"></i>
                <span>T.A. {{ $tahunAjaran }} ({{ $semesterAktif }})</span>
            </span>
        </div>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-journal-bookmark-fill text-indigo-600"></i>
                <span>Rencana Program Kerja Tugas Tambahan</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Susun dan kelola rencana program kerja untuk jabatan/tugas tambahan yang Anda emban sebelum melaporkan realisasi pelaksanaannya.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button"
                    @click="selectedTugas = '{{ addslashes($tugasFilter ?: ($listTugasTambahan[0] ?? '')) }}'; modalCreate = true"
                    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-indigo-600/20 transition cursor-pointer">
                <i class="bi-plus-circle-fill text-sm"></i>
                <span>Buat Rencana Program Kerja</span>
            </button>
            <a href="{{ route('guru.realisasi-tugas-tambahan.index') }}"
               class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm shadow-emerald-600/20 transition">
                <i class="bi-clipboard2-check-fill text-sm"></i>
                <span>Buka Laporan Realisasi</span>
            </a>
        </div>
    </div>

    <!-- Statistik Ringkas -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Rencana</span>
            <div class="text-xl font-black text-slate-900 mt-0.5">{{ $totalProgram }}</div>
            <span class="text-[10px] text-slate-500">Program Kerja</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 block">Terencana</span>
            <div class="text-xl font-black text-amber-600 mt-0.5">{{ $totalTerencana }}</div>
            <span class="text-[10px] text-amber-700 font-medium">Menunggu Eksekusi</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">Sedang Berjalan</span>
            <div class="text-xl font-black text-blue-600 mt-0.5">{{ $totalBerjalan }}</div>
            <span class="text-[10px] text-blue-700 font-medium">Dalam Pelaksanaan</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 block">Tercapai</span>
            <div class="text-xl font-black text-emerald-600 mt-0.5">{{ $totalTercapai }}</div>
            <span class="text-[10px] text-emerald-700 font-medium">Selesai & Terealisasi</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-500 block">Tertunda</span>
            <div class="text-xl font-black text-rose-500 mt-0.5">{{ $totalTertunda }}</div>
            <span class="text-[10px] text-rose-600 font-medium">Perlu Evaluasi</span>
        </div>
    </div>

    <!-- Daftar Jabatan / Tugas Tambahan yang Diemban -->
    @if(!empty($listTugasTambahan))
    <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-xs space-y-3">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div>
                <h2 class="text-sm font-black text-slate-800 flex items-center gap-2">
                    <i class="bi-award-fill text-amber-500"></i>
                    <span>Penugasan Tambahan Anda ({{ count($listTugasTambahan) }} Tugas)</span>
                </h2>
                <p class="text-[11px] text-slate-500">Klik tombol pada salah satu kartu tugas tambahan di bawah untuk langsung menambahkan rencana program kerja.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
            @foreach($listTugasTambahan as $tugas)
            @php
                $countPerTugas = $allPrograms->where('tugas_tambahan', $tugas)->count();
                $countTercapai = $allPrograms->where('tugas_tambahan', $tugas)->where('status', 'tercapai')->count();
            @endphp
            <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60 hover:bg-white hover:shadow-sm transition flex flex-col justify-between gap-3">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 text-[10px] font-black uppercase">
                            Tugas Tambahan
                        </span>
                        <span class="text-[11px] font-bold text-slate-500">
                            {{ $countPerTugas }} Rencana ({{ $countTercapai }} Tercapai)
                        </span>
                    </div>
                    <h3 class="font-black text-slate-900 text-sm mt-2">{{ $tugas }}</h3>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-slate-200/70">
                    <button type="button"
                            @click="selectedTugas = '{{ addslashes($tugas) }}'; modalCreate = true"
                            class="flex-1 py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                        <i class="bi-plus-lg"></i>
                        <span>Tambah Rencana</span>
                    </button>
                    <a href="{{ route('guru.realisasi-tugas-tambahan.index', ['tugas' => $tugas]) }}"
                       class="py-2 px-3 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 font-bold rounded-xl text-xs flex items-center justify-center gap-1 transition"
                       title="Lihat Laporan Realisasi untuk {{ $tugas }}">
                        <i class="bi-clipboard2-check text-emerald-600"></i>
                        <span>Realisasi</span>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4 text-xs">
        <form method="GET" action="{{ route('guru.program-kerja-tambahan.index') }}" class="flex flex-wrap items-center gap-3">
            @if($gurus->isNotEmpty())
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Filter Guru / Pejabat</label>
                <select name="guru_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="">Semua Guru</option>
                    @foreach($gurus as $g)
                    <option value="{{ $g->id }}" {{ request('guru_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Filter Tugas Tambahan</label>
                <select name="tugas" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="">Semua Tugas Tambahan</option>
                    @foreach($listTugasTambahan as $t)
                    <option value="{{ $t }}" {{ $tugasFilter === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Semester</label>
                <select name="semester" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="">Semua Semester</option>
                    <option value="Ganjil" {{ $semesterFilter === 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                    <option value="Genap" {{ $semesterFilter === 'Genap' ? 'selected' : '' }}>Genap</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Status Program</label>
                <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="">Semua Status</option>
                    <option value="terencana" {{ $statusFilter === 'terencana' ? 'selected' : '' }}>Terencana</option>
                    <option value="sedang_berjalan" {{ $statusFilter === 'sedang_berjalan' ? 'selected' : '' }}>Sedang Berjalan</option>
                    <option value="tercapai" {{ $statusFilter === 'tercapai' ? 'selected' : '' }}>Tercapai</option>
                    <option value="tertunda" {{ $statusFilter === 'tertunda' ? 'selected' : '' }}>Tertunda</option>
                </select>
            </div>
            @if($tugasFilter || $semesterFilter || $statusFilter || request('guru_id'))
            <div class="pt-4">
                <a href="{{ route('guru.program-kerja-tambahan.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs inline-block">
                    Reset Filter
                </a>
            </div>
            @endif
        </form>

        <div class="text-slate-500 font-medium text-xs">
            Menampilkan <strong class="text-slate-800">{{ $programKerjaList->count() }}</strong> Rencana Program Kerja
        </div>
    </div>

    <!-- Tabel Daftar Rencana Program Kerja -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 border-b font-bold uppercase text-[10px] text-slate-400">
                    <tr>
                        <th class="px-4 py-3.5 text-center" width="4%">No</th>
                        <th class="px-4 py-3.5" width="18%">Tugas Tambahan</th>
                        <th class="px-4 py-3.5" width="26%">Nama Program & Tujuan</th>
                        <th class="px-4 py-3.5" width="15%">Target Waktu & Anggaran</th>
                        <th class="px-4 py-3.5" width="17%">Indikator Keberhasilan</th>
                        <th class="px-4 py-3.5 text-center" width="10%">Status</th>
                        <th class="px-4 py-3.5 text-center" width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y font-medium text-slate-700">
                    @forelse($programKerjaList as $idx => $pk)
                    <tr class="hover:bg-slate-50/70 transition align-top">
                        <td class="px-4 py-3.5 text-center font-bold text-slate-400">
                            {{ $idx + 1 }}
                        </td>
                        <td class="px-4 py-3.5 space-y-1">
                            <span class="inline-block px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-black text-[11px] border border-indigo-200">
                                {{ $pk->tugas_tambahan }}
                            </span>
                            <div class="text-[10px] text-slate-400 font-semibold">
                                T.A. {{ $pk->tahun_ajaran }} · Sem. {{ $pk->semester }}
                            </div>
                            @if((auth()->user()->isSuperAdmin() || auth()->user()->isKepalaSekolah()) && $pk->guru)
                            <div class="text-[11px] text-slate-600 font-bold">
                                <i class="bi-person-fill text-slate-400"></i> {{ $pk->guru->name }}
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 space-y-1">
                            <div class="font-extrabold text-slate-900 text-xs leading-snug">
                                {{ $pk->nama_program }}
                            </div>
                            @if($pk->tujuan)
                            <div class="text-[11px] text-slate-500 leading-relaxed">
                                {{ $pk->tujuan }}
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 space-y-1">
                            <div class="text-[11px] text-slate-700 flex items-center gap-1">
                                <i class="bi-calendar-event text-indigo-500"></i>
                                <span>{{ $pk->target_waktu ?: 'Belum ditentukan' }}</span>
                            </div>
                            @if($pk->anggaran > 0)
                            <div class="text-[11px] text-emerald-700 font-bold">
                                Rp {{ number_format($pk->anggaran, 0, ',', '.') }}
                            </div>
                            @else
                            <div class="text-[10px] text-slate-400">Non-Anggaran / Rp 0</div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            @if($pk->indikator_keberhasilan)
                            <div class="text-[11px] text-slate-600 bg-slate-50 p-2 rounded-xl border border-slate-100">
                                {{ $pk->indikator_keberhasilan }}
                            </div>
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center space-y-1.5">
                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-black uppercase {{ $pk->status_badge }}">
                                {{ $pk->status_label }}
                            </span>
                            @if($pk->realisasis_count > 0)
                            <div class="text-[10px] text-emerald-600 font-bold flex items-center justify-center gap-1">
                                <i class="bi-check-circle-fill"></i>
                                <span>Sudah Realisasi ({{ $pk->realisasis_count }})</span>
                            </div>
                            @else
                            <div class="text-[10px] text-amber-600 font-semibold">
                                Belum Dilaporkan
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('guru.realisasi-tugas-tambahan.create', ['program_id' => $pk->id]) }}"
                                   class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg font-bold text-[11px] transition inline-flex items-center gap-1"
                                   title="Laporkan Realisasi Pelaksanaan Program Kerja Ini">
                                    <i class="bi-clipboard2-check-fill"></i>
                                    <span>Realisasi</span>
                                </a>
                                <button type="button"
                                        @click="openEdit({{ Js::from($pk) }})"
                                        class="p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition cursor-pointer"
                                        title="Edit Rencana Program Kerja">
                                    <i class="bi-pencil-square"></i>
                                </button>
                                <form method="POST" action="{{ route('guru.penugasan.program-kerja.destroy', $pk) }}" onsubmit="return confirm('Hapus rencana program kerja ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Hapus Rencana">
                                        <i class="bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                            <div class="max-w-md mx-auto space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 mx-auto flex items-center justify-center text-2xl">
                                    <i class="bi-journal-plus"></i>
                                </div>
                                <h3 class="font-black text-slate-800 text-sm">Belum Ada Rencana Program Kerja</h3>
                                <p class="text-xs text-slate-500 leading-relaxed">
                                    Anda belum menambahkan rencana program kerja untuk penugasan tambahan pada periode ini. Klik tombol di bawah untuk mulai menyusun program kerja.
                                </p>
                                <div class="pt-1">
                                    <button type="button"
                                            @click="selectedTugas = '{{ addslashes($listTugasTambahan[0] ?? '') }}'; modalCreate = true"
                                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-md shadow-indigo-600/20 transition cursor-pointer">
                                        <i class="bi-plus-circle-fill"></i>
                                        <span>Buat Rencana Program Kerja Baru</span>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL POPUP: BUAT RENCANA PROGRAM KERJA BARU -->
    <!-- ========================================================================= -->
    <div x-show="modalCreate"
         x-cloak
         x-transition.opacity.duration.200ms
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        <div @click.away="modalCreate = false"
             class="bg-white w-full max-w-lg rounded-3xl p-6 sm:p-7 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-indigo-100 text-indigo-700 rounded-xl">
                        <i class="bi-calendar2-plus-fill text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Buat Rencana Tugas & Program Kerja</h3>
                        <p class="text-xs text-slate-500">Susun rencana program kerja tugas tambahan Anda</p>
                    </div>
                </div>
                <button type="button" @click="modalCreate = false" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100">
                    <i class="bi-x-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('guru.penugasan.program-kerja.store') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="redirect_to" value="rencana">

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jabatan / Tugas Tambahan <span class="text-rose-500">*</span></label>
                    @if(!empty($listTugasTambahan))
                    <select name="tugas_tambahan" x-model="selectedTugas" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                        <option value="">-- Pilih Tugas Tambahan --</option>
                        @foreach($listTugasTambahan as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                    @else
                    <input type="text" name="tugas_tambahan" x-model="selectedTugas" required placeholder="Contoh: Wali Kelas / Pembina Eskul / Waka" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <input type="text" name="tahun_ajaran" value="{{ $tahunAjaran }}" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Semester <span class="text-rose-500">*</span></label>
                        <select name="semester" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                            <option value="Ganjil" {{ $semesterAktif === 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                            <option value="Genap" {{ $semesterAktif === 'Genap' ? 'selected' : '' }}>Genap</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Program / Rencana Tugas <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_program" required placeholder="Contoh: Rapat Koordinasi Kurikulum / Pembinaan Rutin / Kunjungan Industri" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tujuan & Sasaran Kegiatan</label>
                    <textarea name="tujuan" rows="2" placeholder="Uraikan tujuan yang ingin dicapai dari rencana program kerja ini..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Target Waktu Pelaksanaan</label>
                        <input type="text" name="target_waktu" placeholder="Contoh: November 2026 / Pekan ke-2" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Estimasi Anggaran (Opsional)</label>
                        <input type="number" name="anggaran" min="0" placeholder="Rp 0" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Indikator Keberhasilan / Output</label>
                    <textarea name="indikator_keberhasilan" rows="2" placeholder="Contoh: Terlaksananya kegiatan secara tertib dan tersusunnya laporan evaluasi..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="modalCreate = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-600/20 flex items-center gap-1.5">
                        <i class="bi-check-lg"></i>
                        <span>Simpan Rencana Program Kerja</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL POPUP: EDIT RENCANA PROGRAM KERJA -->
    <!-- ========================================================================= -->
    <div x-show="modalEdit"
         x-cloak
         x-transition.opacity.duration.200ms
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        <div @click.away="modalEdit = false"
             class="bg-white w-full max-w-lg rounded-3xl p-6 sm:p-7 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-amber-100 text-amber-700 rounded-xl">
                        <i class="bi-pencil-square text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Edit Rencana Program Kerja</h3>
                        <p class="text-xs text-slate-500">Perbarui rincian rencana program kerja tugas tambahan</p>
                    </div>
                </div>
                <button type="button" @click="modalEdit = false" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100">
                    <i class="bi-x-lg"></i>
                </button>
            </div>

            <form method="POST" :action="'{{ url('guru/penugasan/program-kerja') }}/' + editData.id" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jabatan / Tugas Tambahan <span class="text-rose-500">*</span></label>
                    <input type="text" name="tugas_tambahan" x-model="editData.tugas_tambahan" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <input type="text" name="tahun_ajaran" x-model="editData.tahun_ajaran" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Semester <span class="text-rose-500">*</span></label>
                        <select name="semester" x-model="editData.semester" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Program / Rencana Tugas <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_program" x-model="editData.nama_program" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tujuan & Sasaran Kegiatan</label>
                    <textarea name="tujuan" rows="2" x-model="editData.tujuan" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Target Waktu Pelaksanaan</label>
                        <input type="text" name="target_waktu" x-model="editData.target_waktu" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Estimasi Anggaran (Opsional)</label>
                        <input type="number" name="anggaran" min="0" x-model="editData.anggaran" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">Indikator Keberhasilan / Output</label>
                        <textarea name="indikator_keberhasilan" rows="2" x-model="editData.indikator_keberhasilan" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">Status Ketercapaian Program</label>
                        <select name="status" x-model="editData.status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                            <option value="terencana">Terencana</option>
                            <option value="sedang_berjalan">Sedang Berjalan</option>
                            <option value="tercapai">Tercapai</option>
                            <option value="tertunda">Tertunda</option>
                        </select>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="modalEdit = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl transition shadow-md shadow-amber-600/20 flex items-center gap-1.5">
                        <i class="bi-check-lg"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

