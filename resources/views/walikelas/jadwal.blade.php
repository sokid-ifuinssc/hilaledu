@extends('layouts.app')

@section('title', 'Jadwal Pelajaran Kelas Bimbingan ' . $namaKelas)

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'harian' }">

    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-900 via-indigo-950 to-slate-950 p-6 sm:p-8 text-white shadow-xl border border-indigo-800/40">
        <div class="absolute inset-0 bg-cover bg-center opacity-15 mix-blend-luminosity pointer-events-none" style="background-image: url('{{ asset('images/gedung-sekolah-lapangan.jpg') }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-blue-950/95 via-indigo-950/85 to-slate-950/90 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-xs font-semibold backdrop-blur-sm border border-blue-500/30">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Tugas Wali Kelas &bull; Pemantauan KBM Kelas</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Jadwal KBM Kelas Bimbingan <span class="text-amber-400 font-black">{{ $namaKelas }}</span> 📅
                </h1>
                <p class="text-slate-300 text-sm max-w-2xl">
                    Wali Kelas: <strong class="text-white">{{ $user->name }}</strong>
                    &bull; Tahun Ajaran <strong class="text-white">{{ $tahunAjaran }}</strong> 
                    &bull; Semester <strong class="text-white">{{ ucfirst($semester) }}</strong>
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                <a href="{{ route('walikelas.jadwal.print') }}" target="_blank"
                   class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs rounded-2xl shadow-md transition flex items-center gap-2">
                    <i class="bi bi-printer-fill text-sm"></i>
                    <span>Cetak Jadwal Kelas (Kertas F4 - 1 Halaman)</span>
                </a>
                <a href="{{ route('walikelas.jadwal.matrix.print', ['kelas' => $namaKelas]) }}" target="_blank"
                   class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-extrabold text-xs rounded-2xl border border-white/20 shadow-md transition flex items-center gap-2">
                    <i class="bi bi-grid-3x3 text-sm text-cyan-300"></i>
                    <span>Cetak Matriks Jadwal Resmi (Kertas F4)</span>
                </a>
                <a href="{{ route('walikelas.dashboard') }}" 
                   class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-2xl shadow-lg shadow-indigo-600/30 transition flex items-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    <span>Dashboard Wali Kelas</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 bg-white rounded-3xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Beban KBM</div>
            <div class="text-2xl font-black text-indigo-600 mt-1">{{ $totalJp }}</div>
            <div class="text-[10px] text-indigo-700 font-semibold">Jam Pelajaran / Minggu</div>
        </div>
        <div class="p-4 bg-white rounded-3xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Mata Pelajaran</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $ringkasanMapel->count() }}</div>
            <div class="text-[10px] text-emerald-700 font-semibold">Mapel Aktif Semester Ini</div>
        </div>
        <div class="p-4 bg-white rounded-3xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hari Efektif KBM</div>
            <div class="text-2xl font-black text-purple-600 mt-1">6</div>
            <div class="text-[10px] text-purple-700 font-semibold">Senin s/d Sabtu</div>
        </div>
        <div class="p-4 bg-white rounded-3xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kelas Bimbingan</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $namaKelas }}</div>
            <div class="text-[10px] text-amber-700 font-semibold">{{ $kelasSaya?->jurusan?->nama ?? 'SMK Plus Al-Hilal' }}</div>
        </div>
    </div>

    <!-- View Toggle Tab -->
    <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-3">
        <div class="flex items-center gap-2">
            <button type="button" @click="activeTab = 'harian'"
                    :class="activeTab === 'harian' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
                <i class="bi bi-grid-fill"></i>
                <span>Tampilan Hari (Kartu)</span>
            </button>
            <button type="button" @click="activeTab = 'tabel'"
                    :class="activeTab === 'tabel' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
                <i class="bi bi-table"></i>
                <span>Tampilan Matriks Mingguan</span>
            </button>
        </div>
        <div class="text-xs text-slate-400 font-medium hidden sm:block">
            * Jadwal ini memuat mata pelajaran, guru pengajar, jam pelajaran, dan ruang kelas khusus kelas {{ $namaKelas }}
        </div>
    </div>

    <!-- Tab 1: Tampilan Kartu Harian -->
    <div x-show="activeTab === 'harian'" class="space-y-6">
        @php
            $hariOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $hariColors = [
                'Senin'  => 'from-blue-600 to-indigo-700',
                'Selasa' => 'from-emerald-600 to-teal-700',
                'Rabu'   => 'from-amber-500 to-orange-600',
                'Kamis'  => 'from-purple-600 to-indigo-700',
                'Jumat'  => 'from-teal-600 to-cyan-700',
                'Sabtu'  => 'from-rose-600 to-pink-700',
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($hariOrder as $hari)
                @php
                    $jadwalsHari = $jadwalLengkap->get($hari, collect());
                    $totalJpHari = $jadwalsHari->sum(fn($j) => max(1, ($j->jam_ke_selesai - $j->jam_ke_mulai + 1)));
                    $bgGrad = $hariColors[$hari] ?? 'from-slate-700 to-slate-800';
                @endphp

                <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden flex flex-col hover:shadow-md transition">
                    <!-- Day Header -->
                    <div class="bg-gradient-to-r {{ $bgGrad }} p-4 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-xs flex items-center justify-center font-black text-lg shadow-inner">
                                {{ substr($hari, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="text-lg font-black tracking-tight leading-tight">{{ $hari }}</h3>
                                <p class="text-xs text-white/80 font-medium">
                                    {{ $jadwalsHari->count() }} Mata Pelajaran &bull; {{ $totalJpHari }} Jam
                                </p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-white/20 text-white text-[10px] font-extrabold uppercase tracking-wider backdrop-blur-xs">
                            {{ $hari === 'Jumat' ? '6 JP' : '8 JP' }}
                        </span>
                    </div>

                    <!-- Jadwal Items -->
                    <div class="p-4 space-y-3 flex-1">
                        @forelse($jadwalsHari as $jadwal)
                            @php
                                $jamRange = ($jadwal->jam_ke_mulai == $jadwal->jam_ke_selesai)
                                    ? "Jam ke-{$jadwal->jam_ke_mulai}"
                                    : "Jam ke-{$jadwal->jam_ke_mulai} s/d {$jadwal->jam_ke_selesai}";
                                $durasiJp = max(1, ($jadwal->jam_ke_selesai - $jadwal->jam_ke_mulai + 1));
                            @endphp

                            <div class="p-3.5 rounded-2xl border border-slate-100 bg-slate-50/70 hover:bg-indigo-50/50 hover:border-indigo-200 transition group">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-lg bg-indigo-100 text-indigo-700 text-[11px] font-black tracking-tight">
                                            {{ $jamRange }}
                                        </span>
                                        <span class="text-[11px] font-bold text-slate-500">
                                            ({{ $durasiJp }} JP)
                                        </span>
                                    </div>
                                    @if($jadwal->jam_mulai && $jadwal->jam_selesai)
                                    <span class="text-[10px] font-medium text-slate-400 bg-white px-2 py-0.5 rounded-md border border-slate-100">
                                        {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                    </span>
                                    @endif
                                </div>

                                <div class="mt-2.5">
                                    <h4 class="text-sm font-extrabold text-slate-800 group-hover:text-indigo-900 transition leading-snug">
                                        {{ $jadwal->mataPelajaran->nama ?? 'Mata Pelajaran' }}
                                    </h4>
                                    <div class="flex items-center gap-2 mt-1 text-xs text-slate-600">
                                        <i class="bi bi-person text-indigo-500"></i>
                                        <span class="font-medium truncate">{{ $jadwal->guru->name ?? 'Belum Ditentukan' }}</span>
                                    </div>
                                </div>

                                @if($jadwal->ruang)
                                <div class="mt-2 pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px] text-slate-400">
                                    <span><i class="bi bi-geo-alt-fill text-slate-400"></i> Ruang: <strong class="text-slate-600">{{ $jadwal->ruang }}</strong></span>
                                </div>
                                @endif
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400">
                                <i class="bi bi-calendar-x text-3xl opacity-40"></i>
                                <p class="text-xs font-semibold mt-2">Tidak ada jadwal KBM</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Tab 2: Tampilan Matriks Mingguan -->
    <div x-show="activeTab === 'tabel'" class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-black text-slate-800 text-sm flex items-center gap-2">
                <i class="bi bi-table text-indigo-600"></i>
                <span>Matriks Jadwal Pelajaran Mingguan &bull; {{ $namaKelas }}</span>
            </h3>
            <span class="text-xs text-slate-500">Jam 1 s/d Jam 8</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4 border-b border-r border-slate-200 w-28 text-center">Hari</th>
                        @for($i = 1; $i <= 8; $i++)
                            <th class="py-3 px-3 border-b border-r border-slate-200 text-center min-w-[130px]">
                                Jam ke-{{ $i }}
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($hariOrder as $hari)
                        @php
                            $jadwalsHari = $jadwalLengkap->get($hari, collect());
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 border-r border-slate-200 font-black text-slate-800 text-center bg-slate-50/50">
                                {{ $hari }}
                            </td>
                            @for($jam = 1; $jam <= 8; $jam++)
                                @php
                                    // Cari jadwal yang mengcover jam ini
                                    $item = $jadwalsHari->first(function($j) use ($jam) {
                                        return $jam >= $j->jam_ke_mulai && $jam <= $j->jam_ke_selesai;
                                    });

                                    // Khusus Jumat jam 7 dan 8 kosong (hanya 6 jam)
                                    $isJumatTutup = ($hari === 'Jumat' && $jam > 6);
                                @endphp

                                <td class="p-2 border-r border-slate-100 text-center align-top {{ $isJumatTutup ? 'bg-slate-100/60' : '' }}">
                                    @if($isJumatTutup)
                                        <span class="text-[10px] text-slate-400 italic">Libur / Pulang</span>
                                    @elseif($item)
                                        <div class="p-2 rounded-xl bg-indigo-50/80 border border-indigo-200/80 text-left shadow-xs">
                                            <div class="font-extrabold text-indigo-950 text-[11px] leading-tight line-clamp-2">
                                                {{ $item->mataPelajaran->nama ?? 'Mapel' }}
                                            </div>
                                            <div class="text-[10px] font-semibold text-slate-600 truncate mt-1 flex items-center gap-1">
                                                <i class="bi bi-person text-indigo-500"></i>
                                                {{ $item->guru->name ?? '-' }}
                                            </div>
                                            @if($item->ruang)
                                            <div class="text-[9px] text-indigo-600 font-bold mt-1">
                                                R: {{ $item->ruang }}
                                            </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-300 font-light">-</span>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ringkasan Guru Pengajar di Kelas Ini -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-black text-slate-800 text-sm flex items-center gap-2">
                    <i class="bi bi-people-fill text-indigo-600"></i>
                    <span>Daftar Guru Pengajar & Alokasi JP Kelas {{ $namaKelas }}</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Seluruh guru yang mengampu pembelajaran di kelas bimbingan Anda</p>
            </div>
            <span class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-800 text-xs font-bold">
                {{ $ringkasanMapel->count() }} Guru / Mapel
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Mata Pelajaran</th>
                        <th class="py-3 px-4">Guru Pengajar</th>
                        <th class="py-3 px-4 text-center">Hari KBM</th>
                        <th class="py-3 px-4 text-center">Total JP</th>
                        <th class="py-3 px-4 text-center">Ruang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($ringkasanMapel as $mapelId => $items)
                        @php
                            $first = $items->first();
                            $mapelNama = $first->mataPelajaran->nama ?? 'Mapel';
                            $guruNama = $first->guru->name ?? 'Belum Ditentukan';
                            $hariList = $items->pluck('hari')->unique()->implode(', ');
                            $totalJpMapel = $items->sum(fn($j) => max(1, ($j->jam_ke_selesai - $j->jam_ke_mulai + 1)));
                            $ruangs = $items->pluck('ruang')->filter()->unique()->implode(', ');
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4 text-center text-slate-400 font-bold">{{ $loop->iteration }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $mapelNama }}</td>
                            <td class="py-3 px-4 font-semibold text-indigo-900 flex items-center gap-2">
                                <span class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ substr($guruNama, 0, 1) }}
                                </span>
                                <span>{{ $guruNama }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-bold">
                                    {{ $hariList }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-black">
                                    {{ $totalJpMapel }} JP
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center text-slate-500 font-medium">
                                {{ $ruangs ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400 italic">
                                Belum ada mata pelajaran yang terjadwal di kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
