@extends('layouts.app')

@section('title', 'Jadwal Pelajaran Kelas ' . $kelasNama)

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'harian' }">

    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-900 via-teal-950 to-slate-950 p-6 sm:p-8 text-white shadow-xl border border-emerald-800/40">
        <div class="absolute inset-0 bg-cover bg-center opacity-15 mix-blend-luminosity pointer-events-none" style="background-image: url('{{ asset('images/gedung-sekolah-lapangan.jpg') }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-950/95 via-teal-950/85 to-slate-950/90 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold backdrop-blur-sm border border-emerald-500/30">
                    <i class="bi bi-calendar3-week-fill"></i>
                    <span>Jadwal Pelajaran Resmi Sekolah</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Jadwal Pelajaran Kelas <span class="text-amber-400 font-black">{{ $kelasNama }}</span> 📅
                </h1>
                <p class="text-slate-300 text-sm max-w-2xl">
                    Tahun Ajaran <strong class="text-white">{{ $tahunAjaran }}</strong> &bull; Semester <strong class="text-white">{{ ucfirst($semester) }}</strong>
                    @if($waliKelas)
                    &bull; Wali Kelas: <strong class="text-emerald-300">{{ $waliKelas->name }}</strong>
                    @endif
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                <a href="{{ route('siswa.jadwal.print') }}" target="_blank"
                   class="px-5 py-3 bg-white/10 hover:bg-white/20 text-white font-extrabold text-xs rounded-2xl border border-white/20 shadow-md transition flex items-center gap-2">
                    <i class="bi bi-printer-fill text-base text-amber-300"></i>
                    <span>Cetak Jadwal</span>
                </a>
                <a href="{{ route('siswa.dashboard') }}" 
                   class="px-5 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs rounded-2xl shadow-lg shadow-emerald-500/30 transition flex items-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 bg-white rounded-3xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Beban KBM</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $totalJp }}</div>
            <div class="text-[10px] text-emerald-700 font-semibold">Jam Pelajaran / Minggu</div>
        </div>
        <div class="p-4 bg-white rounded-3xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Mapel</div>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $ringkasanMapel->count() }}</div>
            <div class="text-[10px] text-blue-700 font-semibold">Mata Pelajaran</div>
        </div>
        <div class="p-4 bg-white rounded-3xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hari Efektif KBM</div>
            <div class="text-2xl font-black text-purple-600 mt-1">6</div>
            <div class="text-[10px] text-purple-700 font-semibold">Senin s/d Sabtu</div>
        </div>
        <div class="p-4 bg-white rounded-3xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rombongan Belajar</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $kelasNama }}</div>
            <div class="text-[10px] text-amber-700 font-semibold">{{ $kelasModel?->jurusan?->nama ?? 'SMK Plus Al-Hilal' }}</div>
        </div>
    </div>

    <!-- View Toggle Tab -->
    <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-3">
        <div class="flex items-center gap-2">
            <button type="button" @click="activeTab = 'harian'"
                    :class="activeTab === 'harian' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
                <i class="bi bi-grid-fill"></i>
                <span>Tampilan Hari (Kartu)</span>
            </button>
            <button type="button" @click="activeTab = 'tabel'"
                    :class="activeTab === 'tabel' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
                <i class="bi bi-table"></i>
                <span>Tampilan Matriks Mingguan</span>
            </button>
        </div>
        <div class="text-xs text-slate-400 font-medium hidden sm:block">
            * Jam istirahat & upacara tertera otomatis pada urutan jam pelajaran
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
                'Sabtu'  => 'from-rose-500 to-pink-600',
            ];
            $todayHari = \App\Models\JadwalPelajaran::getHariIndonesia();
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($hariOrder as $hari)
            @php
                $sesiHari = $jadwalLengkap->get($hari, collect());
                $isToday = ($hari === $todayHari);
            @endphp
            <div class="bg-white rounded-3xl border {{ $isToday ? 'border-2 border-emerald-500 ring-4 ring-emerald-500/10' : 'border-slate-200' }} shadow-xs overflow-hidden flex flex-col justify-between">
                <div>
                    <!-- Card Day Header -->
                    <div class="bg-gradient-to-r {{ $hariColors[$hari] }} px-5 py-3.5 text-white flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center font-black text-sm">
                                <i class="bi bi-calendar-day"></i>
                            </span>
                            <div>
                                <h3 class="font-extrabold text-base leading-tight">{{ $hari }}</h3>
                                <p class="text-[11px] text-white/80">{{ $sesiHari->count() }} Sesi Pelajaran</p>
                            </div>
                        </div>
                        @if($isToday)
                        <span class="px-3 py-1 bg-white text-emerald-800 text-[10px] font-black rounded-full uppercase tracking-wider shadow-sm">
                            Hari Ini
                        </span>
                        @endif
                    </div>

                    <!-- List Jam Pelajaran -->
                    <div class="p-4 divide-y divide-slate-100 space-y-3">
                        @if($hari === 'Senin')
                        <div class="pt-2 pb-1 flex items-center gap-3 text-xs bg-amber-50/70 p-3 rounded-2xl border border-amber-200/80">
                            <span class="w-12 text-center font-mono font-bold text-amber-800 shrink-0">Jam 1</span>
                            <div class="flex-1">
                                <div class="font-bold text-slate-800">Upacara Bendera / Apel Pagi</div>
                                <div class="text-[11px] text-slate-500">07.00 - 07.45 &bull; Lapangan Utama</div>
                            </div>
                            <span class="px-2 py-0.5 bg-amber-200/80 text-amber-900 text-[10px] font-bold rounded-md">Rutin</span>
                        </div>
                        @endif

                        @forelse($sesiHari as $j)
                        <div class="pt-3 pb-1 flex items-start gap-3 text-xs">
                            <div class="w-14 shrink-0 text-center">
                                <div class="px-2 py-1 rounded-xl bg-slate-100 font-mono font-black text-slate-800 text-[11px]">
                                    Jam {{ $j->jam_ke_mulai }}{{ $j->jam_ke_selesai > $j->jam_ke_mulai ? '-' . $j->jam_ke_selesai : '' }}
                                </div>
                                <div class="text-[10px] text-slate-400 mt-1 font-mono">
                                    {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="font-extrabold text-slate-900 text-sm leading-tight">
                                    {{ $j->mataPelajaran->nama ?? 'Mata Pelajaran' }}
                                </div>
                                <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-600 flex-wrap">
                                    <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold">
                                        <i class="bi bi-person-fill"></i>
                                        {{ $j->guru?->name ?? 'Guru Belum Ditugaskan' }}
                                    </span>
                                    @if($j->ruang)
                                    <span class="text-slate-300">&bull;</span>
                                    <span class="inline-flex items-center gap-1 text-slate-500 font-mono">
                                        <i class="bi bi-geo-alt"></i>
                                        {{ $j->ruang }}
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-black shrink-0">
                                {{ ($j->jam_ke_selesai - $j->jam_ke_mulai) + 1 }} JP
                            </span>
                        </div>
                        @empty
                        <div class="py-6 text-center text-slate-400 text-xs italic">
                            Tidak ada jadwal KBM pada hari ini.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Tab 2: Tampilan Matriks Mingguan -->
    <div x-show="activeTab === 'tabel'" style="display: none;" class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle text-xs">
                <thead class="bg-slate-100 font-black text-slate-700 uppercase">
                    <tr>
                        <th style="width: 80px;">Jam Ke</th>
                        <th style="width: 110px;">Waktu</th>
                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h)
                        <th>{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @for($jam = 1; $jam <= 8; $jam++)
                    <tr>
                        <td class="font-black bg-slate-50 text-slate-900">Jam {{ $jam }}</td>
                        <td class="font-mono text-[11px] text-slate-500 bg-slate-50">
                            @if($jam == 1) 07.00 - 07.45
                            @elseif($jam == 2) 07.45 - 08.30
                            @elseif($jam == 3) 08.30 - 09.15
                            @elseif($jam == 4) 09.15 - 10.00
                            @elseif($jam == 5) 10.30 - 11.15
                            @elseif($jam == 6) 11.15 - 12.00
                            @elseif($jam == 7) 12.30 - 13.15
                            @elseif($jam == 8) 13.15 - 14.00
                            @endif
                        </td>
                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h)
                        @php
                            if ($h === 'Jumat' && $jam > 6) {
                                echo '<td class="bg-slate-100 text-slate-400 text-[10px] italic">Pulang (10.30)</td>';
                                continue;
                            }
                            if ($h === 'Senin' && $jam === 1) {
                                echo '<td class="bg-amber-50 text-amber-900 font-bold text-[11px]"><i class="bi bi-flag-fill text-amber-600 mr-1"></i> Upacara Bendera</td>';
                                continue;
                            }

                            $sesi = $jadwalLengkap->get($h, collect())->first(function($j) use ($jam) {
                                return $jam >= $j->jam_ke_mulai && $jam <= $j->jam_ke_selesai;
                            });
                        @endphp
                        @if($sesi)
                        <td class="p-2.5 bg-emerald-50/60 border border-emerald-200/80 text-left">
                            <div class="font-extrabold text-slate-900 text-xs leading-tight">
                                {{ $sesi->mataPelajaran->nama ?? '-' }}
                            </div>
                            <div class="text-[10px] text-emerald-800 font-semibold mt-1">
                                {{ $sesi->guru?->name ?? 'Guru Belum Ditugaskan' }}
                            </div>
                            @if($sesi->ruang)
                            <div class="text-[9px] text-slate-400 font-mono">{{ $sesi->ruang }}</div>
                            @endif
                        </td>
                        @else
                        <td class="bg-slate-50/50 text-slate-300">-</td>
                        @endif
                        @endforeach
                    </tr>

                    {{-- Baris Istirahat --}}
                    @if($jam == 4)
                    <tr class="bg-amber-100/60 font-black text-amber-900 text-[11px]">
                        <td colspan="2" class="py-1">ISTIRAHAT 1</td>
                        <td colspan="6" class="py-1 tracking-wider">ISTIRAHAT PERTAMA (10.00 - 10.30 WIB / Jumat: 09.00 - 09.30)</td>
                    </tr>
                    @endif
                    @if($jam == 6)
                    <tr class="bg-amber-100/60 font-black text-amber-900 text-[11px]">
                        <td colspan="2" class="py-1">ISTIRAHAT 2</td>
                        <td colspan="6" class="py-1 tracking-wider">ISTIRAHAT KEDUA & SHOLAT DZUHUR (12.00 - 12.30 WIB)</td>
                    </tr>
                    @endif
                    @endfor
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ringkasan Guru Pengampu Kelas -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
        <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
            <i class="bi bi-person-workspace text-emerald-600"></i>
            <span>Daftar Guru Pengajar di Kelas {{ $kelasNama }}</span>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($ringkasanMapel as $mapelId => $jadwalsMapel)
            @php
                $mapelFirst = $jadwalsMapel->first();
                $totalJpMapel = $jadwalsMapel->sum(fn($j) => max(1, ($j->jam_ke_selesai - $j->jam_ke_mulai + 1)));
            @endphp
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h4 class="font-extrabold text-xs text-slate-900 truncate">
                        {{ $mapelFirst->mataPelajaran->nama ?? '-' }}
                    </h4>
                    <p class="text-[11px] text-emerald-700 font-semibold truncate mt-0.5">
                        {{ $mapelFirst->guru?->name ?? 'Belum Ditugaskan' }}
                    </p>
                    <p class="text-[10px] text-slate-400 mt-0.5">
                        Hari: {{ $jadwalsMapel->pluck('hari')->unique()->implode(', ') }}
                    </p>
                </div>
                <span class="px-2.5 py-1 bg-white border border-slate-200 text-slate-700 text-xs font-black rounded-xl shrink-0">
                    {{ $totalJpMapel }} JP
                </span>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
