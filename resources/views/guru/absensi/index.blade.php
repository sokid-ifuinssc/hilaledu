@extends('layouts.app')

@section('title', 'Rekap Kehadiran Saya')
@section('page-title', 'Rekap Kehadiran Saya')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-emerald-800 via-teal-800 to-slate-900 rounded-3xl p-6 text-white shadow-xl">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-emerald-200">
                    <i class="bi bi-calendar-check-fill mr-1"></i> Kehadiran Mengajar Guru
                </span>
                @if($user->id !== auth()->id())
                <span class="px-2.5 py-0.5 bg-amber-400 text-slate-900 font-bold text-xs rounded-full">
                    Melihat Data: {{ $user->name }}
                </span>
                @endif
            </div>
            <h1 class="text-2xl md:text-3xl font-black mt-2 tracking-tight">Rekap Kehadiran Saya</h1>
            <p class="text-slate-300 text-xs md:text-sm mt-1">
                Rekapitulasi jam mengajar diurutkan per hari lengkap dengan status kehadiran per jam mapel untuk evaluasi mandiri.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('guru.laporan-kbm.index') }}" 
               class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-bold rounded-xl text-xs flex items-center gap-2 border border-white/20 transition">
                <i class="bi bi-journal-text text-base"></i>
                <span>Jurnal KBM</span>
            </a>
            <a href="{{ route('guru.absensi.print', request()->query()) }}" target="_blank" 
               class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-lg shadow-emerald-900/30 transition">
                <i class="bi bi-printer-fill text-base"></i>
                <span>Cetak Rekap Saya</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar Periode Terpadu -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('guru.absensi.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end text-xs">
            @if($isExecutive && $gurus->isNotEmpty())
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">
                    <i class="bi bi-person-fill text-emerald-600 mr-1"></i> Pilih Guru (Pimpinan)
                </label>
                <select name="guru_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs focus:ring-2 focus:ring-emerald-500">
                    @foreach($gurus as $g)
                    <option value="{{ $g->id }}" {{ $user->id == $g->id ? 'selected' : '' }}>
                        {{ $g->name }} ({{ $g->nip ?: $g->username }})
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">
                    <i class="bi bi-funnel-fill text-emerald-600 mr-1"></i> Mode Rekap
                </label>
                <select name="mode" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-semibold text-xs focus:ring-2 focus:ring-emerald-500">
                    <option value="bulan" {{ $mode === 'bulan' ? 'selected' : '' }}>Per Bulan</option>
                    <option value="semester" {{ $mode === 'semester' ? 'selected' : '' }}>Per Semester</option>
                    <option value="tahun" {{ $mode === 'tahun' ? 'selected' : '' }}>1 Tahun Ajaran Penuh</option>
                </select>
            </div>

            @if($mode === 'bulan')
            <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">
                    <i class="bi bi-calendar-month text-emerald-600 mr-1"></i> Pilih Bulan
                </label>
                <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()" 
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-xs focus:ring-2 focus:ring-emerald-500">
            </div>
            @elseif($mode === 'semester')
            <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">
                    <i class="bi bi-calendar3 text-emerald-600 mr-1"></i> Pilih Semester
                </label>
                <select name="semester" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-xs focus:ring-2 focus:ring-emerald-500">
                    <option value="1" {{ $semester == '1' ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                    <option value="2" {{ $semester == '2' ? 'selected' : '' }}>Semester 2 (Genap)</option>
                </select>
            </div>
            @endif

            <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">
                    <i class="bi bi-mortarboard text-emerald-600 mr-1"></i> Tahun Ajaran
                </label>
                <input type="text" name="tahun_ajaran" value="{{ $tahunAjaran }}" placeholder="2026/2027" 
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-xs focus:ring-2 focus:ring-emerald-500"
                       onblur="this.form.submit()">
            </div>
        </form>
    </div>

    <!-- Ringkasan Statistik Evaluasi Diri Guru -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Card 1: Persentase Kehadiran -->
        <div class="p-4 bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl border border-emerald-200 shadow-xs text-center flex flex-col justify-between">
            <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Persentase Kehadiran</div>
            <div class="text-3xl font-black text-emerald-700 my-1">{{ $stat['persen_hadir'] }}%</div>
            <div class="w-full bg-emerald-200 rounded-full h-1.5 overflow-hidden">
                <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ min(100, $stat['persen_hadir']) }}%"></div>
            </div>
        </div>

        <!-- Card 2: Jam Per Minggu -->
        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-xs text-center flex flex-col justify-between">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Beban Mengajar</div>
            <div class="text-2xl font-black text-indigo-700 my-1">{{ $stat['jam_per_minggu'] }} <span class="text-xs font-semibold text-slate-500">Jam/Mg</span></div>
            <div class="text-[10px] text-slate-500 font-medium">Beban jadwal rutin</div>
        </div>

        <!-- Card 3: Hari Efektif Mengajar -->
        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-xs text-center flex flex-col justify-between">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hari Efektif KBM</div>
            <div class="text-2xl font-black text-slate-800 my-1">{{ $stat['total_hari_efektif'] }} <span class="text-xs font-semibold text-slate-500">Hari</span></div>
            <div class="text-[10px] text-slate-500 font-medium">{{ $stat['hari_berjalan'] }} hari berjalan</div>
        </div>

        <!-- Card 4: Sesi Hadir -->
        <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 shadow-xs text-center flex flex-col justify-between">
            <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Sesi Hadir</div>
            <div class="text-2xl font-black text-emerald-700 my-1">{{ $stat['sesi_hadir'] }}</div>
            <div class="text-[10px] text-emerald-600 font-medium">Dari {{ $stat['total_sesi'] }} total sesi</div>
        </div>

        <!-- Card 5: Sesi Tidak Hadir -->
        <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 shadow-xs text-center flex flex-col justify-between">
            <div class="text-[10px] font-bold text-rose-700 uppercase tracking-wider">Sesi Tidak Hadir</div>
            <div class="text-2xl font-black text-rose-700 my-1">{{ $stat['sesi_tidak_hadir'] }}</div>
            <div class="text-[10px] text-rose-600 font-medium">Alpa, Izin, Sakit, DL</div>
        </div>

        <!-- Card 6: Rincian Ketidakhadiran -->
        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 shadow-xs text-left flex flex-col justify-center text-[11px] space-y-0.5">
            <div class="flex justify-between items-center text-slate-600">
                <span>Alpa:</span>
                <strong class="{{ $stat['sesi_tanpa_ket'] > 0 ? 'text-rose-600' : 'text-slate-700' }}">{{ $stat['sesi_tanpa_ket'] }}</strong>
            </div>
            <div class="flex justify-between items-center text-slate-600">
                <span>Sakit:</span>
                <strong>{{ $stat['sesi_sakit'] }}</strong>
            </div>
            <div class="flex justify-between items-center text-slate-600">
                <span>Izin:</span>
                <strong>{{ $stat['sesi_izin'] }}</strong>
            </div>
            <div class="flex justify-between items-center text-slate-600">
                <span>Dinas Luar:</span>
                <strong>{{ $stat['sesi_dinas_luar'] }}</strong>
            </div>
        </div>
    </div>

    <!-- Informasi Otomasi Kehadiran Sesuai Aturan Sekolah -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-start gap-3 text-xs text-blue-900 shadow-xs">
        <i class="bi bi-info-circle-fill text-blue-600 text-base mt-0.5 flex-shrink-0"></i>
        <div class="leading-relaxed">
            <strong>Aturan Sinkronisasi Kehadiran Mengajar:</strong>
            Guru yang sudah melakukan presensi masuk di pagi hari otomatis dinyatakan <strong>Hadir</strong> pada seluruh sesi mata pelajaran di hari tersebut, 
            <em>kecuali</em> jika tercatat pulang cepat sebelum sesi mapel dimulai, atau ada catatan ketidakhadiran spesifik dari petugas piket / pengajuan cuti.
        </div>
    </div>

    <!-- Rincian Kehadiran Diurutkan Per Hari & Keterangan Per Jam Mapel -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                <i class="bi bi-calendar2-week text-emerald-600"></i>
                <span>Rincian Kehadiran Per Hari & Jam Mata Pelajaran</span>
            </h2>
            <span class="text-xs font-semibold text-slate-500">
                Total {{ count($hariList) }} Hari Terjadwal
            </span>
        </div>

        @forelse($hariList as $hari)
        <div class="bg-white rounded-3xl border {{ $hari['is_today'] ? 'border-emerald-400 ring-2 ring-emerald-500/20 shadow-md' : 'border-slate-200 shadow-xs' }} overflow-hidden transition">
            
            <!-- Header Kartu Hari -->
            <div class="p-4 bg-slate-50/80 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-bold flex items-center justify-center text-xs shadow-xs">
                            {{ substr($hari['hari'], 0, 3) }}
                        </span>
                        <div>
                            <div class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                <span>{{ $hari['hari'] }}, {{ $hari['tanggal_label'] }}</span>
                                @if($hari['is_today'])
                                <span class="px-2 py-0.5 bg-emerald-600 text-white font-bold text-[10px] rounded-full uppercase tracking-wider">
                                    Hari Ini
                                </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                <span>Terjadwal: <strong>{{ $hari['total_mapel'] }} Mapel</strong></span>
                                &bull;
                                <span class="text-emerald-700 font-semibold">{{ $hari['mapel_hadir'] }} Hadir</span>
                                @if($hari['mapel_tidak'] > 0)
                                &bull;
                                <span class="text-rose-600 font-semibold">{{ $hari['mapel_tidak'] }} Tidak Hadir</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Presensi Harian Pagi Sekolah -->
                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 text-xs">
                    <i class="bi bi-clock-history text-slate-400"></i>
                    @if($hari['presensi_harian'])
                        <div>
                            <span class="text-slate-500">Presensi Pagi:</span>
                            <strong class="font-mono text-emerald-700">{{ $hari['presensi_harian']['jam_masuk'] ?: '-' }}</strong>
                            @if($hari['presensi_harian']['terlambat'] > 0)
                            <span class="text-[10px] text-amber-600 font-bold">(+{{ $hari['presensi_harian']['terlambat'] }}m)</span>
                            @endif
                            <span class="text-slate-400 mx-1">&bull;</span>
                            <span class="text-slate-500">Pulang:</span>
                            <strong class="font-mono text-slate-700">{{ $hari['presensi_harian']['jam_pulang'] ?: '-' }}</strong>
                        </div>
                    @else
                        @if($hari['is_today'] && now()->hour < 15)
                            <span class="text-amber-600 font-medium">Belum melakukan presensi masuk pagi</span>
                        @elseif($hari['is_passed'])
                            <span class="text-rose-600 font-medium">Tanpa catatan presensi harian</span>
                        @else
                            <span class="text-slate-400">Hari kerja mendatang</span>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Tabel Keterangan Per Jam Mapel -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-white border-b border-slate-100 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                        <tr>
                            <th class="px-5 py-3 w-40">Jam & Sesi</th>
                            <th class="px-4 py-3">Mata Pelajaran & Kelas</th>
                            <th class="px-4 py-3 text-center w-36">Status Kehadiran</th>
                            <th class="px-5 py-3">Keterangan / Alasan Per Jam Mapel</th>
                            <th class="px-4 py-3 text-center w-28">Bukti / Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($hari['mapel_list'] as $m)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                    <i class="bi bi-clock text-slate-400"></i>
                                    <span>{{ $m['jam_waktu'] }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-semibold mt-0.5">
                                    {{ $m['jam_ke'] }}
                                </div>
                            </td>

                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900 text-sm">
                                    {{ $m['mapel'] }}
                                </div>
                                <div class="flex items-center gap-2 mt-1 text-[11px]">
                                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 font-bold rounded-md border border-indigo-200">
                                        Kelas {{ $m['kelas'] }}
                                    </span>
                                    @if($m['ruang'] && $m['ruang'] !== '-')
                                    <span class="text-slate-500 flex items-center gap-1">
                                        <i class="bi bi-door-open"></i> {{ $m['ruang'] }}
                                    </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-3.5 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-black border inline-block {{ $m['badge'] }}">
                                    {{ $m['label'] }}
                                </span>
                            </td>

                            <td class="px-5 py-3.5 text-slate-600 leading-relaxed">
                                <div class="flex items-start gap-1.5">
                                    @if($m['status'] === 'hadir')
                                        <i class="bi bi-check-circle-fill text-emerald-600 mt-0.5"></i>
                                    @elseif($m['status'] === 'tanpa_keterangan')
                                        <i class="bi bi-x-circle-fill text-rose-600 mt-0.5"></i>
                                    @elseif(in_array($m['status'], ['izin', 'sakit', 'dinas_luar']))
                                        <i class="bi bi-info-circle-fill text-blue-600 mt-0.5"></i>
                                    @else
                                        <i class="bi bi-hourglass-split text-slate-400 mt-0.5"></i>
                                    @endif
                                    <span>{{ $m['keterangan'] }}</span>
                                </div>
                            </td>

                            <td class="px-4 py-3.5 text-center">
                                @if($m['lampiran_bukti'])
                                <a href="{{ asset('storage/' . $m['lampiran_bukti']) }}" target="_blank" 
                                   class="px-2.5 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-bold transition inline-flex items-center gap-1">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Bukti</span>
                                </a>
                                @else
                                <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
        @empty
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center text-slate-400 shadow-xs">
            <i class="bi bi-calendar-x text-4xl mb-3 inline-block text-slate-300"></i>
            <h3 class="font-bold text-slate-700 text-sm">Tidak Ada Jadwal Mengajar</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                Tidak ditemukan jadwal mengajar aktif untuk guru pada periode kalender terpilih. Silakan periksa pengaturan jadwal atau filter periode Anda.
            </p>
        </div>
        @endforelse
    </div>

</div>
@endsection
