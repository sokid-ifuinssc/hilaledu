@extends('layouts.app')

@section('title', 'Rekap Kehadiran Saya')
@section('page-title', 'Rekap Kehadiran Saya')

@section('content')
<div class="space-y-5">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Rekap Kehadiran Saya</h1>
                @if($user->id !== auth()->id())
                <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 font-bold text-xs rounded-full">
                    Guru: {{ $user->name }}
                </span>
                @endif
            </div>
            <p class="text-slate-500 text-xs mt-0.5">
                Rincian jam mengajar dari tanggal awal sampai akhir periode beserta status per jam pelajaran.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.laporan-kbm.index') }}" 
               class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs flex items-center gap-1.5 transition">
                <i class="bi bi-journal-text text-sm"></i>
                <span>Jurnal KBM</span>
            </a>
            <a href="{{ route('guru.absensi.print', request()->query()) }}" target="_blank" 
               class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition">
                <i class="bi bi-printer text-sm"></i>
                <span>Cetak Rekap</span>
            </a>
        </div>
    </div>

    <!-- Filter Periode Ringkas -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('guru.absensi.index') }}" class="flex flex-wrap items-end gap-3 text-xs">
            @if($isExecutive && $gurus->isNotEmpty())
            <div class="w-full sm:w-auto min-w-[220px]">
                <label class="block text-[11px] font-bold text-slate-500 mb-1">
                    <i class="bi bi-person-fill text-emerald-600 mr-0.5"></i> Pilih Guru:
                </label>
                <select name="guru_id" onchange="this.form.submit()" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs focus:ring-2 focus:ring-emerald-500">
                    @foreach($gurus as $g)
                    <option value="{{ $g->id }}" {{ $user->id == $g->id ? 'selected' : '' }}>
                        {{ $g->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="w-36">
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Mode Filter:</label>
                <select name="mode" onchange="this.form.submit()" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs focus:ring-2 focus:ring-emerald-500">
                    <option value="bulan" {{ $mode === 'bulan' ? 'selected' : '' }}>Per Bulan</option>
                    <option value="semester" {{ $mode === 'semester' ? 'selected' : '' }}>Per Semester</option>
                    <option value="tahun" {{ $mode === 'tahun' ? 'selected' : '' }}>1 Tahun Penuh</option>
                </select>
            </div>

            @if($mode === 'bulan')
            <div class="w-40">
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Bulan:</label>
                <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()" 
                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-semibold text-xs focus:ring-2 focus:ring-emerald-500">
            </div>
            @elseif($mode === 'semester')
            <div class="w-44">
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Semester:</label>
                <select name="semester" onchange="this.form.submit()" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-semibold text-xs focus:ring-2 focus:ring-emerald-500">
                    <option value="1" {{ $semester == '1' ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                    <option value="2" {{ $semester == '2' ? 'selected' : '' }}>Semester 2 (Genap)</option>
                </select>
            </div>
            @endif

            <div class="w-32">
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Tahun Ajaran:</label>
                <input type="text" name="tahun_ajaran" value="{{ $tahunAjaran }}" placeholder="2026/2027" 
                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-semibold text-xs focus:ring-2 focus:ring-emerald-500"
                       onblur="this.form.submit()">
            </div>
        </form>
    </div>

    <!-- Ringkasan Statistik Evaluasi Diri (Simple & Mengena) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Kehadiran Mengajar -->
        <div class="p-4 bg-emerald-50/80 rounded-2xl border border-emerald-200 flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Kehadiran Mengajar</div>
                <div class="text-2xl font-black text-emerald-700 mt-0.5">{{ $stat['persen_hadir'] }}%</div>
                <div class="text-[11px] text-emerald-600 mt-0.5">{{ $stat['sesi_hadir'] }} dari {{ $stat['total_sesi'] }} total sesi</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg shadow-xs">
                <i class="bi bi-pie-chart-fill"></i>
            </div>
        </div>

        <!-- Beban Mengajar -->
        <div class="p-4 bg-white rounded-2xl border border-slate-200 flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Beban Mengajar</div>
                <div class="text-2xl font-black text-slate-800 mt-0.5">{{ $stat['jam_per_minggu'] }} <span class="text-xs font-semibold text-slate-500">Jam/Mg</span></div>
                <div class="text-[11px] text-slate-500 mt-0.5">{{ $stat['total_hari_efektif'] }} Hari KBM Efektif</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>

        <!-- Sesi Hadir -->
        <div class="p-4 bg-white rounded-2xl border border-slate-200 flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sesi Terlaksana</div>
                <div class="text-2xl font-black text-emerald-600 mt-0.5">{{ $stat['sesi_hadir'] }} <span class="text-xs font-normal text-slate-400">Sesi</span></div>
                <div class="text-[11px] text-slate-500 mt-0.5">{{ $stat['hari_berjalan'] }} hari KBM berjalan</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg">
                <i class="bi bi-check2-circle"></i>
            </div>
        </div>

        <!-- Tidak Hadir -->
        <div class="p-4 bg-white rounded-2xl border border-slate-200 flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tidak Hadir</div>
                <div class="text-2xl font-black {{ $stat['sesi_tidak_hadir'] > 0 ? 'text-rose-600' : 'text-slate-700' }} mt-0.5">
                    {{ $stat['sesi_tidak_hadir'] }} <span class="text-xs font-normal text-slate-400">Sesi</span>
                </div>
                <div class="text-[10px] text-slate-500 mt-0.5">
                    Alpa: <strong>{{ $stat['sesi_tanpa_ket'] }}</strong> &bull; Izin: <strong>{{ $stat['sesi_izin'] }}</strong> &bull; Sakit: <strong>{{ $stat['sesi_sakit'] }}</strong> &bull; DL: <strong>{{ $stat['sesi_dinas_luar'] }}</strong>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl {{ $stat['sesi_tidak_hadir'] > 0 ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center text-lg">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
        </div>
    </div>

    <!-- Catatan Hint Ringkas -->
    <div class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-600 text-xs flex items-center gap-2">
        <i class="bi bi-info-circle text-emerald-600 text-sm"></i>
        <span>
            <strong>Informasi:</strong> Kehadiran pagi otomatis tercatat hadir pada setiap jam mengajar hari tersebut, kecuali tercatat pulang cepat sebelum sesi dimulai.
        </span>
    </div>

    <!-- Tabel Rekap Kehadiran Terpadu (Urut dari Tanggal 1 s.d Akhir) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="bi bi-table text-emerald-600"></i>
                <span>Tabel Kehadiran Jam Mengajar (Urut Tanggal 1 - Akhir)</span>
            </h2>
            <span class="text-xs text-slate-500 font-medium">
                {{ count($hariList) }} Hari Mengajar
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-3 py-3 text-center w-12 border-r border-slate-200">No</th>
                        <th class="px-4 py-3 w-48 border-r border-slate-200">Hari & Tanggal</th>
                        <th class="px-4 py-3 w-40 border-r border-slate-200">Presensi Pagi</th>
                        <th class="px-3 py-3 w-36 border-r border-slate-200">Jam & Sesi</th>
                        <th class="px-4 py-3 border-r border-slate-200">Mata Pelajaran & Kelas</th>
                        <th class="px-4 py-3 text-center w-32 border-r border-slate-200">Status</th>
                        <th class="px-4 py-3">Keterangan</th>
                        <th class="px-3 py-3 text-center w-20">Bukti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium">
                    @php $no = 1; @endphp
                    @forelse($hariList as $hari)
                        @php
                            $rowSpan = max(1, count($hari['mapel_list']));
                            $isToday = $hari['is_today'];
                            $rowBg = $isToday ? 'bg-emerald-50/30' : 'hover:bg-slate-50/80';
                        @endphp

                        @if(empty($hari['mapel_list']))
                            {{-- Jika hari tersebut tidak memiliki sesi mengajar terdaftar --}}
                            <tr class="{{ $rowBg }} transition">
                                <td class="px-3 py-3 text-center font-bold text-slate-500 border-r border-slate-200">{{ $no++ }}</td>
                                <td class="px-4 py-3 border-r border-slate-200">
                                    <div class="font-bold text-slate-900">{{ $hari['hari'] }}, {{ $hari['tanggal_label'] }}</div>
                                    @if($isToday)
                                        <span class="inline-block mt-0.5 px-2 py-0.5 bg-emerald-600 text-white text-[10px] font-bold rounded-full">Hari Ini</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border-r border-slate-200 text-slate-500">
                                    @if($hari['presensi_harian'])
                                        <div><span class="text-slate-400">Msk:</span> <strong class="text-emerald-700 font-mono">{{ $hari['presensi_harian']['jam_masuk'] ?: '-' }}</strong></div>
                                        <div><span class="text-slate-400">Plg:</span> <strong class="text-slate-700 font-mono">{{ $hari['presensi_harian']['jam_pulang'] ?: '-' }}</strong></div>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td colspan="5" class="px-4 py-3 text-slate-400 italic text-center">
                                    Tidak ada jam mengajar
                                </td>
                            </tr>
                        @else
                            @foreach($hari['mapel_list'] as $idx => $m)
                            <tr class="{{ $rowBg }} transition {{ $idx > 0 ? 'border-t border-slate-100' : '' }}">
                                {{-- Kolom No, Tanggal, Presensi Pagi hanya di baris pertama (rowspan) --}}
                                @if($idx === 0)
                                <td rowspan="{{ $rowSpan }}" class="px-3 py-3 text-center font-bold text-slate-600 align-top border-r border-slate-200 bg-white/70">
                                    {{ $no++ }}
                                </td>
                                <td rowspan="{{ $rowSpan }}" class="px-4 py-3 align-top border-r border-slate-200 bg-white/70">
                                    <div class="font-bold text-slate-900 text-xs">
                                        {{ $hari['hari'] }}, {{ $hari['tanggal_label'] }}
                                    </div>
                                    @if($isToday)
                                        <span class="inline-block mt-1 px-2 py-0.5 bg-emerald-600 text-white text-[10px] font-bold rounded-full">
                                            Hari Ini
                                        </span>
                                    @endif
                                    <div class="text-[10px] text-slate-400 mt-1">
                                        {{ $hari['total_mapel'] }} Mapel ({{ $hari['mapel_hadir'] }} Hadir)
                                    </div>
                                </td>
                                <td rowspan="{{ $rowSpan }}" class="px-4 py-3 align-top border-r border-slate-200 bg-white/70 text-[11px]">
                                    @if($hari['presensi_harian'])
                                        <div class="space-y-0.5">
                                            <div class="flex items-center gap-1">
                                                <span class="text-slate-400 text-[10px] w-9">Masuk:</span>
                                                <strong class="font-mono text-emerald-700">{{ $hari['presensi_harian']['jam_masuk'] ?: '-' }}</strong>
                                                @if($hari['presensi_harian']['terlambat'] > 0)
                                                    <span class="text-[9px] text-amber-600 font-bold">(+{{ $hari['presensi_harian']['terlambat'] }}m)</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <span class="text-slate-400 text-[10px] w-9">Pulang:</span>
                                                <strong class="font-mono text-slate-700">{{ $hari['presensi_harian']['jam_pulang'] ?: '-' }}</strong>
                                            </div>
                                        </div>
                                    @else
                                        @if($hari['is_today'] && now()->hour < 15)
                                            <span class="text-amber-600 font-semibold text-[10px]">Belum Presensi</span>
                                        @elseif($hari['is_passed'])
                                            <span class="text-rose-600 font-semibold text-[10px]">Tidak Presensi</span>
                                        @else
                                            <span class="text-slate-400 text-[10px]">-</span>
                                        @endif
                                    @endif
                                </td>
                                @endif

                                {{-- Kolom Jam Sesi --}}
                                <td class="px-3 py-2.5 border-r border-slate-200">
                                    <div class="font-bold text-slate-800 text-xs">{{ $m['jam_waktu'] }}</div>
                                    <div class="text-[10px] text-slate-500 font-medium">{{ $m['jam_ke'] }}</div>
                                </td>

                                {{-- Kolom Mapel & Kelas --}}
                                <td class="px-4 py-2.5 border-r border-slate-200">
                                    <div class="font-bold text-slate-900">{{ $m['mapel'] }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        <span class="font-semibold text-indigo-700">Kelas {{ $m['kelas'] }}</span>
                                        @if($m['ruang'] && $m['ruang'] !== '-')
                                            <span class="text-slate-400 mx-1">&bull;</span>
                                            <span>R. {{ $m['ruang'] }}</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Kolom Status --}}
                                <td class="px-3 py-2.5 text-center border-r border-slate-200">
                                    @if($m['status'] === 'hadir')
                                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg text-[11px] font-bold inline-flex items-center gap-1">
                                            <i class="bi bi-check-circle-fill text-[10px]"></i> Hadir
                                        </span>
                                    @elseif($m['status'] === 'tanpa_keterangan')
                                        <span class="px-2.5 py-1 bg-rose-100 text-rose-800 rounded-lg text-[11px] font-bold inline-flex items-center gap-1">
                                            <i class="bi bi-x-circle-fill text-[10px]"></i> Tidak Hadir
                                        </span>
                                    @elseif($m['status'] === 'izin')
                                        <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-lg text-[11px] font-bold inline-flex items-center gap-1">
                                            <i class="bi bi-info-circle-fill text-[10px]"></i> Izin
                                        </span>
                                    @elseif($m['status'] === 'sakit')
                                        <span class="px-2.5 py-1 bg-purple-100 text-purple-800 rounded-lg text-[11px] font-bold inline-flex items-center gap-1">
                                            <i class="bi bi-heart-pulse-fill text-[10px]"></i> Sakit
                                        </span>
                                    @elseif($m['status'] === 'dinas_luar')
                                        <span class="px-2.5 py-1 bg-indigo-100 text-indigo-800 rounded-lg text-[11px] font-bold inline-flex items-center gap-1">
                                            <i class="bi bi-briefcase-fill text-[10px]"></i> Dinas Luar
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-[11px] font-medium inline-flex items-center gap-1">
                                            <i class="bi bi-hourglass text-[10px]"></i> Belum
                                        </span>
                                    @endif
                                </td>

                                {{-- Kolom Keterangan --}}
                                <td class="px-4 py-2.5 text-slate-600 text-xs">
                                    {{ $m['keterangan'] }}
                                </td>

                                {{-- Kolom Bukti --}}
                                <td class="px-3 py-2.5 text-center">
                                    @if(!empty($m['lampiran_bukti']))
                                        <a href="{{ asset('storage/' . $m['lampiran_bukti']) }}" target="_blank" 
                                           class="px-2 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded text-[10px] transition inline-flex items-center gap-1">
                                            <i class="bi bi-file-earmark-text"></i> Bukti
                                        </a>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            <i class="bi bi-calendar-x text-3xl mb-2 inline-block text-slate-300"></i>
                            <div class="font-bold text-slate-700 text-sm">Tidak Ada Jadwal Mengajar</div>
                            <div class="text-xs text-slate-500 mt-0.5">Tidak ditemukan jadwal mengajar aktif pada periode kalender terpilih.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
