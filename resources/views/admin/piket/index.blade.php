@extends('layouts.app')

@section('title', 'Penugasan Petugas Piket Sekolah')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:underline">&larr; Kembali ke Panel Pengelola</a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-calendar2-check-fill text-indigo-600"></i>
                <span>Penugasan Petugas Piket Sekolah</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Jadwal penugasan Petugas Piket harian (Tendik & Dewan Guru) untuk mengelola ketertiban dan presensi dewan guru (T.A. {{ $tahunAjaran }} - Semester {{ $semester }}).
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('piket.index') }}" target="_blank"
               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow-sm shadow-emerald-600/20">
                <i class="bi-eye-fill"></i>
                <span>Buka Panel Petugas Piket</span>
            </a>
        </div>
    </div>

    <!-- Grid Form Tambah & Ringkasan -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Form Tambah Penugasan Piket (1 Kolom) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4 h-fit">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                <div class="p-2 bg-indigo-50 text-indigo-700 rounded-xl">
                    <i class="bi-person-plus-fill text-lg"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-sm text-slate-900">Tetapkan Petugas Piket</h3>
                    <p class="text-[11px] text-slate-500">Pilih personil Tendik atau Guru</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.piket.store') }}" class="space-y-4 text-xs">
                @csrf

                <!-- Pilih Personil (Tendik / Guru) -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Pilih Personil Petugas
                    </label>
                    <select name="user_id" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-indigo-600">
                        <option value="">-- Pilih Staf Tendik / Guru --</option>
                        <optgroup label="💼 Tenaga Kependidikan (Tendik)">
                            @foreach($tendiks as $t)
                            <option value="{{ $t->id }}">Tendik: {{ $t->name }} ({{ $t->nip ?: $t->username }})</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="👨‍🏫 Dewan Guru">
                            @foreach($gurus as $g)
                            <option value="{{ $g->id }}">Guru: {{ $g->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                <!-- Hari Piket -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Hari Bertugas
                    </label>
                    <select name="hari" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-indigo-600">
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                    </select>
                </div>

                <!-- Lokasi Pos Piket -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Lokasi / Pos Piket
                    </label>
                    <input type="text" name="lokasi_pos" value="Pos Piket Utama & Gerbang" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium text-slate-800">
                </div>

                <!-- Keterangan / Wewenang -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Keterangan Tugas
                    </label>
                    <textarea name="keterangan" rows="2" placeholder="Contoh: Petugas Piket Presensi Guru & Gerbang Masuk..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition shadow-md shadow-indigo-600/30 flex items-center justify-center gap-1.5">
                        <i class="bi-check-circle-fill"></i>
                        <span>Tetapkan Sebagai Petugas Piket</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Matriks Jadwal Piket Per Hari (2 Kolom) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-black text-slate-900 text-base flex items-center gap-2">
                        <i class="bi-calendar-week-fill text-indigo-600"></i>
                        <span>Matriks Jadwal Piket Mingguan</span>
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
                        {{ $pikets->count() }} Petugas Terjadwal
                    </span>
                </div>

                <!-- Card Hari Senin s.d. Sabtu -->
                @php
                    $hariOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                @endphp
                <div class="space-y-3">
                    @foreach($hariOrder as $h)
                    @php
                        $listInDay = $piketsByHari->get($h, collect());
                    @endphp
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-[120px]">
                            <span class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-black text-xs flex items-center justify-center shadow-xs">
                                {{ substr($h, 0, 3) }}
                            </span>
                            <div>
                                <h4 class="font-extrabold text-slate-900 text-sm leading-tight">{{ $h }}</h4>
                                <span class="text-[11px] text-slate-500 font-semibold">{{ $listInDay->count() }} Petugas</span>
                            </div>
                        </div>

                        <!-- Daftar Petugas di Hari Ini -->
                        <div class="flex-1 flex items-center gap-2 flex-wrap">
                            @forelse($listInDay as $pi)
                            <div class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center gap-2 text-xs">
                                <div class="w-2 h-2 rounded-full {{ $pi->user->role === 'tendik' ? 'bg-amber-500' : 'bg-emerald-500' }}"></div>
                                <div>
                                    <span class="font-black text-slate-900">{{ $pi->user->name }}</span>
                                    <span class="text-[10px] uppercase font-mono px-1 bg-slate-100 rounded text-slate-600 ml-1">
                                        {{ $pi->user->role === 'tendik' ? 'Tendik' : 'Guru' }}
                                    </span>
                                </div>
                                <form method="POST" action="{{ route('admin.piket.destroy', $pi) }}" onsubmit="return confirm('Hapus penugasan piket ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 ml-1 font-bold text-xs" title="Hapus Penugasan">
                                        <i class="bi-x-circle-fill"></i>
                                    </button>
                                </form>
                            </div>
                            @empty
                            <span class="text-slate-400 italic text-xs">Belum ada petugas piket terjadwal.</span>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
