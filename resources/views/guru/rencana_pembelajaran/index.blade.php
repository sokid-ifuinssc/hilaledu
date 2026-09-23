@extends('layouts.app')

@section('title', 'Rencana Pembelajaran Kurikulum Merdeka')

@section('content')
<div class="space-y-6" x-data="{ tab: '{{ $tab }}' }">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-journal-check text-blue-600"></i>
                <span>Perangkat Rencana Pembelajaran</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Alur Kurikulum Merdeka: 1. CP &rarr; 2. TP &rarr; 3. ATP &rarr; 4. Modul Ajar Harian (RPP)</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.rencana-pembelajaran.rpp.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-blue-600/20 transition">
                <i class="bi-plus-circle text-sm"></i>
                <span>Buat Modul Ajar Harian (RPP)</span>
            </a>
        </div>
    </div>

    <!-- Informasi Kalender Akademik Sekolah (Acuan Perencanaan KBM) -->
    @if(isset($activeKalender) && $activeKalender)
    @php
        $efektifData = $activeKalender->calculateEffectiveWeeks();
        $smt1Efektif = $efektifData['semester1']['total_minggu_efektif'] ?? 19;
        $smt2Efektif = $efektifData['semester2']['total_minggu_efektif'] ?? 17;
        $upcomingEvents = $activeKalender->events->where('tanggal_mulai', '>=', date('Y-m-d'))->take(3);
    @endphp
    <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white rounded-3xl p-5 sm:p-6 shadow-md relative overflow-hidden">
        <div class="absolute right-0 top-0 -mt-10 -mr-10 w-72 h-72 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="space-y-2 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-[10px] uppercase tracking-wider">
                        Acuan Kalender Akademik Sekolah
                    </span>
                    <span class="text-xs text-slate-300 font-semibold">&bull;</span>
                    <span class="text-xs text-blue-200 font-bold">Tahun Ajaran {{ $activeKalender->tahun_ajaran }}</span>
                </div>
                <h3 class="font-extrabold text-lg text-white">
                    {{ $activeKalender->nama_kalender ?: 'Kalender Pendidikan SMK Plus Al-Hilal' }}
                </h3>
                <p class="text-xs text-slate-300 leading-relaxed max-w-2xl">
                    Jadwal efektif belajar KBM menjadi dasar alokasi CP, TP, ATP, dan Modul Ajar Harian. Semester Ganjil: <strong>{{ $smt1Efektif }} Minggu Efektif</strong> &bull; Semester Genap: <strong>{{ $smt2Efektif }} Minggu Efektif</strong>.
                </p>

                <!-- Event / Libur Terdekat -->
                @if($upcomingEvents->isNotEmpty())
                <div class="flex items-center gap-2 pt-1 flex-wrap text-xs">
                    <span class="text-amber-300 font-bold flex items-center gap-1">
                        <i class="bi-calendar-event"></i> Agenda/Libur Terdekat:
                    </span>
                    @foreach($upcomingEvents as $ue)
                    <span class="px-2.5 py-1 rounded-xl bg-white/10 text-white font-medium text-[11px] border border-white/10 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                        <strong>{{ \Carbon\Carbon::parse($ue->tanggal_mulai)->format('d M') }}:</strong> {{ $ue->judul_kegiatan ?? $ue->judul }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="{{ route('guru.minggu-efektif.index') }}" class="px-4 py-2.5 bg-white/15 hover:bg-white/25 text-white font-bold rounded-xl text-xs flex items-center gap-2 border border-white/20 transition">
                    <i class="bi-calendar2-week"></i>
                    <span>Analisis Minggu Efektif</span>
                </a>
                <a href="{{ route('guru.kalender.index') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition">
                    <i class="bi-calendar3"></i>
                    <span>Buka Kalender Lengkap</span>
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Navigation Tabs (Strict Order: 1. CP -> 2. TP -> 3. ATP -> 4. RPP) -->
    <div class="flex border-b border-slate-200 gap-2 text-xs font-bold overflow-x-auto pb-px">
        <button type="button" 
                @click="tab = 'cp'" 
                :class="tab === 'cp' ? 'border-b-2 border-blue-600 text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700'"
                class="px-5 py-3 rounded-t-xl transition flex items-center gap-2 whitespace-nowrap">
            <i class="bi-award text-base"></i>
            <span>1. Capaian Pembelajaran (CP)</span>
            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-black">{{ $cpList->count() }}</span>
        </button>

        <button type="button" 
                @click="tab = 'tp'" 
                :class="tab === 'tp' ? 'border-b-2 border-blue-600 text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700'"
                class="px-5 py-3 rounded-t-xl transition flex items-center gap-2 whitespace-nowrap">
            <i class="bi-bullseye text-base"></i>
            <span>2. Tujuan Pembelajaran (TP)</span>
            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-black">{{ $tpList->count() }}</span>
        </button>

        <button type="button" 
                @click="tab = 'atp'" 
                :class="tab === 'atp' ? 'border-b-2 border-blue-600 text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700'"
                class="px-5 py-3 rounded-t-xl transition flex items-center gap-2 whitespace-nowrap">
            <i class="bi-bezier2 text-base"></i>
            <span>3. Alur Tujuan Pembelajaran (ATP)</span>
            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-black">{{ $atpList->count() }}</span>
        </button>

        <button type="button" 
                @click="tab = 'rpp'" 
                :class="tab === 'rpp' ? 'border-b-2 border-blue-600 text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700'"
                class="px-5 py-3 rounded-t-xl transition flex items-center gap-2 whitespace-nowrap">
            <i class="bi-file-earmark-text text-base"></i>
            <span>4. Rencana Pembelajaran (Modul Ajar / RPP Harian)</span>
            <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-black">{{ $rencanaList->total() }}</span>
        </button>
    </div>

    <!-- ========================================================= -->
    <!-- TAB 1: CAPAIAN PEMBELAJARAN (CP) -->
    <!-- ========================================================= -->
    <div x-show="tab === 'cp'" class="space-y-6">
        <!-- Form Tambah CP -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
                    <i class="bi-plus-circle text-blue-600"></i>
                    <span>1. Input Capaian Pembelajaran (CP) Baru</span>
                </h3>
                <span class="text-xs text-slate-400">Kurikulum Merdeka SMK</span>
            </div>

            <form method="POST" action="{{ route('guru.rencana-pembelajaran.cp.store') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <select name="mata_pelajaran_id" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-500">
                            @foreach($mapels as $mapel)
                            <option value="{{ $mapel->id }}">{{ $mapel->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Fase Kurikulum <span class="text-rose-500">*</span></label>
                        <select name="fase" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-blue-500">
                            <option value="E">Fase E (Kelas X)</option>
                            <option value="F">Fase F (Kelas XI & XII)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Tingkat Kelas <span class="text-rose-500">*</span></label>
                        <select name="tingkat" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-blue-500">
                            <option value="X">Kelas X</option>
                            <option value="XI">Kelas XI</option>
                            <option value="XII">Kelas XII</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Elemen / Dimensi <span class="text-rose-500">*</span></label>
                        <input type="text" name="elemen" placeholder="Contoh: Jaringan Komputer..." required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Deskripsi Capaian Pembelajaran <span class="text-rose-500">*</span></label>
                    <textarea name="deskripsi" rows="3" placeholder="Pada akhir fase ini peserta didik mampu..." required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <input type="hidden" name="tahun_ajaran" value="{{ $activeKalender->tahun_ajaran ?? '2026/2027' }}">
                    <div class="flex items-center gap-2">
                        <label class="font-bold text-slate-700">Semester:</label>
                        <select name="semester" class="p-2 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                            <option value="ganjil">Semester Ganjil</option>
                            <option value="genap">Semester Genap</option>
                        </select>
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                        <i class="bi-plus-circle"></i>
                        <span>Simpan Capaian Pembelajaran</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Daftar CP Cards -->
        <div class="space-y-4">
            @forelse($cpList as $cp)
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-3 hover:border-blue-300 transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-lg text-xs font-black">
                            Fase {{ $cp->fase }} &bull; Kelas {{ $cp->tingkat }}
                        </span>
                        <h4 class="font-extrabold text-slate-900 text-sm">{{ $cp->mataPelajaran->nama ?? 'Mata Pelajaran' }}</h4>
                        <span class="text-slate-400">&bull;</span>
                        <span class="text-xs font-semibold text-slate-600">Elemen: {{ $cp->elemen }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 uppercase">{{ $cp->semester }}</span>
                    </div>
                    <form action="{{ route('guru.rencana-pembelajaran.cp.destroy', $cp) }}" method="POST" onsubmit="return confirm('Hapus CP ini? Semua TP terkait juga perlu diperhatikan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-bold flex items-center gap-1 p-1">
                            <i class="bi-trash"></i> <span>Hapus</span>
                        </button>
                    </form>
                </div>
                <p class="text-xs text-slate-700 leading-relaxed">{{ $cp->deskripsi }}</p>
                <div class="text-[11px] text-slate-500 border-t border-slate-100 pt-2 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="bi-check2-square text-emerald-600 font-bold"></i>
                        <span>Tujuan Pembelajaran (TP) Diturunkan: <strong class="text-slate-800">{{ $cp->tujuanPembelajaran->count() }} TP</strong></span>
                    </div>
                    <button type="button" @click="tab = 'tp'" class="text-blue-600 hover:text-blue-800 font-bold">
                        + Tambah TP dari CP ini &rarr;
                    </button>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-3xl p-10 text-center text-slate-400 border border-slate-200">
                <i class="bi-journal-x text-3xl mb-2 inline-block"></i>
                <p class="text-xs font-semibold">Belum ada Capaian Pembelajaran yang diinput. Silakan input CP pertama di atas.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- TAB 2: TUJUAN PEMBELAJARAN (TP) -->
    <!-- ========================================================= -->
    <div x-show="tab === 'tp'" class="space-y-6" style="display: none;">
        <!-- Form Tambah TP -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
                    <i class="bi-plus-circle text-blue-600"></i>
                    <span>2. Input Tujuan Pembelajaran (TP) Baru</span>
                </h3>
                <span class="text-xs text-slate-400">Diturunkan dari CP</span>
            </div>

            @if($cpList->isEmpty())
            <div class="p-5 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-800 space-y-2">
                <div class="font-bold flex items-center gap-2">
                    <i class="bi-exclamation-triangle-fill text-amber-600"></i>
                    <span>Capaian Pembelajaran (CP) Belum Tersedia</span>
                </div>
                <p>Silakan input <strong>Capaian Pembelajaran (CP)</strong> terlebih dahulu pada Tab 1 sebelum menambahkan Tujuan Pembelajaran.</p>
                <button type="button" @click="tab = 'cp'" class="px-4 py-2 bg-amber-600 text-white font-bold rounded-xl text-xs">
                    Buka Tab 1: Input CP
                </button>
            </div>
            @else
            <form method="POST" action="{{ route('guru.rencana-pembelajaran.tp.store') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Capaian Pembelajaran (CP) Induk <span class="text-rose-500">*</span></label>
                        <select name="capaian_pembelajaran_id" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-500">
                            @foreach($cpList as $cp)
                            <option value="{{ $cp->id }}">{{ $cp->mataPelajaran->nama }} - Fase {{ $cp->fase }} Kelas {{ $cp->tingkat }} ({{ $cp->elemen }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Kode TP <span class="text-rose-500">*</span></label>
                        <input type="text" name="kode_tp" placeholder="Contoh: TP 10.1" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Alokasi JP <span class="text-rose-500">*</span></label>
                        <input type="number" name="alokasi_jp" value="4" min="1" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Deskripsi Tujuan Pembelajaran <span class="text-rose-500">*</span></label>
                        <textarea name="deskripsi" rows="2" placeholder="Peserta didik mampu memahami prinsip..." required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Kriteria Ketercapaian (KKTP)</label>
                        <textarea name="kktp" rows="2" placeholder="Indikator ketercapaian kompetensi siswa..." class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                    <div class="w-full sm:w-1/2">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Lingkup Materi / Topik</label>
                        <input type="text" name="lingkup_materi" placeholder="Topik materi pokok..." class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                        <select name="semester" class="p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                            <option value="ganjil">Semester Ganjil</option>
                            <option value="genap">Semester Genap</option>
                        </select>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                            <i class="bi-plus-circle"></i>
                            <span>Simpan Tujuan Pembelajaran</span>
                        </button>
                    </div>
                </div>
            </form>
            @endif
        </div>

        <!-- Tabel Daftar TP -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 font-extrabold text-sm text-slate-900 flex items-center justify-between">
                <span>Daftar Tujuan Pembelajaran (TP) Terdaftar</span>
                <span class="text-xs text-slate-500 font-semibold">{{ $tpList->count() }} TP Aktif</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 border-b border-slate-200 font-bold uppercase text-[10px] text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Kode</th>
                            <th class="px-4 py-3">Mata Pelajaran & Fase</th>
                            <th class="px-6 py-3">Deskripsi TP</th>
                            <th class="px-6 py-3">Kriteria Ketercapaian (KKTP)</th>
                            <th class="px-4 py-3 text-center">Alokasi</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($tpList as $tp)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-black text-blue-700">{{ $tp->kode_tp }}</td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-slate-800">{{ $tp->capaianPembelajaran->mataPelajaran->nama ?? 'Mata Pelajaran' }}</div>
                                <span class="text-[10px] text-slate-400">Fase {{ $tp->capaianPembelajaran->fase ?? '-' }} &bull; {{ $tp->capaianPembelajaran->elemen ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-800">{{ $tp->deskripsi }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $tp->kktp ?: '-' }}</td>
                            <td class="px-4 py-4 text-center font-bold text-slate-700">{{ $tp->alokasi_jp }} JP</td>
                            <td class="px-4 py-4 text-right">
                                <form action="{{ route('guru.rencana-pembelajaran.tp.destroy', $tp) }}" method="POST" onsubmit="return confirm('Hapus TP ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 p-1" title="Hapus TP">
                                        <i class="bi-trash text-base"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">Belum ada TP yang diinput.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- TAB 3: ALUR TUJUAN PEMBELAJARAN (ATP) -->
    <!-- ========================================================= -->
    <div x-show="tab === 'atp'" class="space-y-6" style="display: none;">
        
        <!-- Panel Generator & Form Manual ATP -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Manual Tambah ATP -->
            <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
                        <i class="bi-bezier2 text-blue-600"></i>
                        <span>3. Input Alur Tujuan Pembelajaran (ATP) Manual</span>
                    </h3>
                    <span class="text-xs text-slate-400">Rangkaian Alur KBM</span>
                </div>

                <form method="POST" action="{{ route('guru.rencana-pembelajaran.atp.store') }}" class="space-y-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                            <select name="mata_pelajaran_id" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-500">
                                @foreach($mapels as $mapel)
                                <option value="{{ $mapel->id }}">{{ $mapel->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">TP Terkait (Opsional)</label>
                            <select name="tujuan_pembelajaran_id" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih TP Terkait --</option>
                                @foreach($tpList as $tp)
                                <option value="{{ $tp->id }}">{{ $tp->kode_tp }}: {{ Str::limit($tp->deskripsi, 40) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Urutan Alur Ke- <span class="text-rose-500">*</span></label>
                            <input type="number" name="alur_ke" value="{{ ($atpList->max('alur_ke') ?? 0) + 1 }}" min="1" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Fase <span class="text-rose-500">*</span></label>
                            <select name="fase" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                                <option value="E">Fase E</option>
                                <option value="F">Fase F</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Tingkat <span class="text-rose-500">*</span></label>
                            <select name="tingkat" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                                <option value="X">Kelas X</option>
                                <option value="XI">Kelas XI</option>
                                <option value="XII">Kelas XII</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Semester <span class="text-rose-500">*</span></label>
                            <select name="semester" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                                <option value="ganjil">Semester Ganjil</option>
                                <option value="genap">Semester Genap</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Kode ATP <span class="text-rose-500">*</span></label>
                            <input type="text" name="kode_atp" placeholder="Contoh: ATP.10.1" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Materi Pokok / Lingkup Pembelajaran <span class="text-rose-500">*</span></label>
                            <input type="text" name="materi_pokok" placeholder="Materi yang dipelajari pada tahapan alur ini..." required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Alokasi JP <span class="text-rose-500">*</span></label>
                            <input type="number" name="alokasi_jp" value="4" min="1" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Profil Pelajar Pancasila</label>
                            <input type="text" name="profil_pelajar_pancasila" value="Bernalar Kritis, Mandiri, Kreatif" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Keterangan / Aktivitas Pembelajaran</label>
                            <input type="text" name="keterangan" placeholder="Catatan pendekatan alur..." class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                            <i class="bi-plus-circle"></i>
                            <span>Simpan Alur Tujuan Pembelajaran</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Generator: Otomatis Buat ATP dari TP yang sudah ada -->
            <div class="bg-gradient-to-br from-indigo-900 to-slate-900 text-white rounded-3xl p-6 border border-indigo-800 shadow-md flex flex-col justify-between space-y-4">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-black text-[10px] uppercase tracking-wider border border-emerald-500/30">
                        Fitur Praktis
                    </span>
                    <h3 class="font-extrabold text-base text-white mt-2">
                        Petakan Alur (ATP) dari TP Otomatis
                    </h3>
                    <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                        Pilih Capaian Pembelajaran (CP) untuk memetakan seluruh Tujuan Pembelajaran (TP) yang sudah diinput menjadi rangkaian Alur Tujuan Pembelajaran secara kronologis.
                    </p>
                </div>

                @if($cpList->isNotEmpty())
                <form method="POST" action="{{ route('guru.rencana-pembelajaran.atp.generate') }}" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-indigo-200 uppercase tracking-wider mb-1">Pilih CP yang Hendak Dipetakan:</label>
                        <select name="capaian_pembelajaran_id" required class="w-full p-2.5 bg-white/10 border border-white/20 rounded-xl text-white font-medium focus:ring-2 focus:ring-emerald-400">
                            @foreach($cpList as $cp)
                            <option value="{{ $cp->id }}" class="text-slate-900">{{ $cp->mataPelajaran->nama }} - Fase {{ $cp->fase }} ({{ $cp->tujuanPembelajaran->count() }} TP)</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" onclick="return confirm('Petakan semua TP dalam CP ini menjadi alur ATP?');" class="w-full py-2.5 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black rounded-xl text-xs flex items-center justify-center gap-2 shadow-lg transition">
                        <i class="bi-magic"></i>
                        <span>Petakan TP ke ATP Sekarang</span>
                    </button>
                </form>
                @else
                <div class="p-3 bg-white/10 rounded-xl text-xs text-slate-300">
                    Input CP & TP terlebih dahulu untuk mengaktifkan pemetaan otomatis.
                </div>
                @endif
            </div>
        </div>

        <!-- Tabel Daftar ATP -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 font-extrabold text-sm text-slate-900 flex items-center justify-between">
                <span>Daftar Alur Tujuan Pembelajaran (ATP)</span>
                <span class="text-xs text-slate-500 font-semibold">{{ $atpList->count() }} Alur Disusun</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 border-b border-slate-200 font-bold uppercase text-[10px] text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-center w-16">Alur Ke</th>
                            <th class="px-4 py-3">Kode ATP</th>
                            <th class="px-4 py-3">Mata Pelajaran & Fase</th>
                            <th class="px-6 py-3">Materi Pokok & Keterangan</th>
                            <th class="px-4 py-3">Profil Pelajar Pancasila</th>
                            <th class="px-4 py-3 text-center">Alokasi</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($atpList as $atp)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-4 text-center font-black text-slate-700 bg-slate-50/50">
                                {{ $atp->alur_ke }}
                            </td>
                            <td class="px-4 py-4 font-black text-blue-700">
                                {{ $atp->kode_atp }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-slate-800">{{ $atp->mataPelajaran->nama ?? 'Mata Pelajaran' }}</div>
                                <span class="text-[10px] text-slate-400">Fase {{ $atp->fase }} &bull; Kelas {{ $atp->tingkat }} &bull; Smt {{ ucfirst($atp->semester) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $atp->materi_pokok }}</div>
                                @if($atp->keterangan)
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $atp->keterangan }}</div>
                                @endif
                                @if($atp->tujuanPembelajaran)
                                <div class="text-[10px] text-blue-600 font-semibold mt-1">Ref TP: {{ $atp->tujuanPembelajaran->kode_tp }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 font-semibold text-[10.5px] border border-purple-200">
                                    {{ $atp->profil_pelajar_pancasila ?: '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-slate-700">{{ $atp->alokasi_jp }} JP</td>
                            <td class="px-4 py-4 text-right">
                                <form action="{{ route('guru.rencana-pembelajaran.atp.destroy', $atp) }}" method="POST" onsubmit="return confirm('Hapus Alur Tujuan Pembelajaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 p-1" title="Hapus ATP">
                                        <i class="bi-trash text-base"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                Belum ada Alur Tujuan Pembelajaran (ATP) yang disusun. Silakan susun secara manual atau petakan otomatis dari TP di atas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- TAB 4: RENCANA PEMBELAJARAN (MODUL AJAR / RPP HARIAN) -->
    <!-- ========================================================= -->
    <div x-show="tab === 'rpp'" class="space-y-4" style="display: none;">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-black text-sm text-slate-900">4. Modul Ajar Harian (RPP) Sesuai Jadwal KBM</h3>
                <p class="text-xs text-slate-500">Perencanaan tatap muka per pertemuan yang terhubung dengan jadwal mengajar dan laporan realisasi KBM</p>
            </div>
            <a href="{{ route('guru.rencana-pembelajaran.rpp.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition">
                <i class="bi-plus-circle"></i>
                <span>+ Buat RPP Harian</span>
            </a>
        </div>

        @if($rencanaList->isEmpty())
        <div class="bg-white rounded-3xl p-10 text-center border border-slate-200">
            <i class="bi-journal-plus text-4xl text-slate-300 inline-block mb-3"></i>
            <h3 class="font-bold text-slate-800 text-base">Belum Ada Modul Ajar Harian</h3>
            <p class="text-slate-500 text-xs mt-1 max-w-md mx-auto">
                Silakan buat rencana pembelajaran harian yang terhubung langsung dengan jadwal mengajar Anda setelah menyelesaikan input CP, TP, dan ATP.
            </p>
            <a href="{{ route('guru.rencana-pembelajaran.rpp.create') }}" class="mt-4 inline-block px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-md">
                + Buat Rencana Ajar Baru
            </a>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($rencanaList as $r)
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                        <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold border border-blue-200">
                            Pertemuan Ke-{{ $r->pertemuan_ke }}
                        </span>
                        <span>{{ \Carbon\Carbon::parse($r->tanggal_rencana)->format('d M Y') }}</span>
                    </div>

                    <h3 class="font-extrabold text-base text-slate-900 leading-snug">
                        {{ $r->materi_pokok }}
                    </h3>

                    <div class="mt-2 text-xs text-slate-600">
                        <strong>{{ $r->jadwal->mataPelajaran->nama ?? 'Mata Pelajaran' }}</strong> &bull; Kelas {{ $r->jadwal->kelas ?? '-' }}
                    </div>

                    @if($r->tujuanPembelajaran)
                    <div class="mt-3 p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-[11px] text-slate-700 line-clamp-2">
                        <strong class="text-blue-700">{{ $r->tujuanPembelajaran->kode_tp }}:</strong> {{ $r->tujuanPembelajaran->deskripsi }}
                    </div>
                    @endif

                    <div class="mt-3 text-[11px] text-slate-500">
                        <span class="font-semibold text-slate-700">Asesmen:</span> {{ $r->bentuk_asesmen }}
                    </div>
                </div>

                <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('guru.rencana-pembelajaran.rpp.show', $r) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">
                        Lihat Modul Ajar &rarr;
                    </a>
                    <a href="{{ route('guru.laporan-kbm.create', ['rencana_id' => $r->id, 'jadwal_id' => $r->jadwal_pelajaran_id]) }}" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs rounded-xl border border-emerald-300">
                        Input Realisasi KBM
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        <div class="pt-4">
            {{ $rencanaList->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
