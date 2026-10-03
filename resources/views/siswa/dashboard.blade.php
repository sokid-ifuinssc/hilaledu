@extends('layouts.app')

@section('title', 'Portal Siswa - Jadwal')

@section('content')
<div class="space-y-6" x-data="{ viewMode: 'hari_ini' }">

    <!-- Hero Banner Siswa -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-900 via-teal-950 to-slate-950 p-6 sm:p-8 text-white shadow-xl border border-emerald-800/40">
        <!-- Background Foto Kampus dengan Soft Overlay -->
        <div class="absolute inset-0 bg-cover bg-center opacity-15 mix-blend-luminosity pointer-events-none" style="background-image: url('{{ asset('images/gedung-sekolah-lapangan.jpg') }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-950/95 via-teal-950/85 to-slate-950/90 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center gap-5">
                <!-- Foto Siswa Aksen Mini Badge -->
                <div class="hidden sm:block w-16 h-16 rounded-2xl overflow-hidden ring-2 ring-emerald-400/40 shadow-md shrink-0 bg-emerald-800">
                    <img src="{{ asset('images/siswi-buku.jpg') }}" alt="Siswa Al-Hilal" class="w-full h-full object-cover object-top">
                </div>
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold backdrop-blur-sm border border-emerald-500/30">
                        <i class="bi-mortarboard"></i>
                        <span>Portal Akademik Siswa SMK Plus Al-Hilal</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Halo, {{ $user->name }}! 🎓
                    </h1>
                    <p class="text-slate-300 text-sm max-w-xl">
                        Kelas: <strong class="text-amber-400 text-base font-black">{{ $kelasNama }}</strong> &bull; NIS: {{ $siswaData?->nis ?: $user->username }}
                    </p>
                </div>
            </div>

            <!-- Quick Action -->
            <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0">
                @php
                    $isHadir = \App\Models\PresensiHarianSiswa::where('siswa_id', auth()->id())->whereDate('tanggal', \Carbon\Carbon::today())->exists();
                @endphp
                @if(!$isHadir)
                <form action="{{ route('presensi-harian.absen-hadir') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-5 py-3.5 bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-xs rounded-2xl shadow-lg shadow-emerald-500/30 transition flex items-center gap-2" onclick="return confirm('Apakah Anda yakin absen HADIR hari ini?')">
                        <i class="bi-hand-index-thumb-fill text-base"></i>
                        <span>Klik Absen Hadir</span>
                    </button>
                </form>
                @else
                <div class="px-5 py-3.5 bg-emerald-100/10 border border-emerald-500/30 text-emerald-300 font-extrabold text-xs rounded-2xl flex items-center gap-2">
                    <i class="bi-check-circle-fill text-base"></i>
                    <span>Anda Sudah Absen</span>
                </div>
                @endif
                
                @if(auth()->user()->kelas && auth()->user()->kelas->ketua_kelas_id == auth()->id())
                <a href="{{ route('presensi-harian.index') }}" class="px-5 py-3.5 bg-blue-500 hover:bg-blue-600 text-white font-extrabold text-xs rounded-2xl shadow-lg shadow-blue-500/30 transition flex items-center gap-2">
                    <i class="bi-people-fill text-base"></i>
                    <span>Absen Kelas</span>
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Presensi Siswa -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 bg-white rounded-3xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hadir di KBM</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $totalHadir }}</div>
            <div class="text-[10px] text-emerald-700 font-semibold">Sesi Pelajaran</div>
        </div>

        <div class="p-4 bg-white rounded-3xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Izin</div>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $totalIzin }}</div>
            <div class="text-[10px] text-blue-700 font-semibold">Sesi Izin</div>
        </div>

        <div class="p-4 bg-white rounded-3xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sakit</div>
            <div class="text-2xl font-black text-purple-600 mt-1">{{ $totalSakit }}</div>
            <div class="text-[10px] text-purple-700 font-semibold">Sesi Sakit</div>
        </div>

        <div class="p-4 bg-white rounded-3xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alpa (Tanpa Keterangan)</div>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ $totalAlpa }}</div>
            <div class="text-[10px] text-rose-700 font-semibold">Sesi Alpa</div>
        </div>
    </div>

    <!-- Section Jadwal: Toggle Hari Ini vs Seminggu Penuh -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-5">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b pb-4">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi-calendar-week text-emerald-600"></i>
                    <span>Jadwal Pelajaran Kelas {{ $kelasNama }}</span>
                </h2>
                <p class="text-xs text-slate-500">Cek guru pengajar, jam pelajaran, dan status kehadiran guru di kelas</p>
            </div>

            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-bold">
                <button type="button" 
                        @click="viewMode = 'hari_ini'"
                        :class="viewMode === 'hari_ini' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900'"
                        class="px-4 py-2 rounded-lg transition">
                    Jadwal Hari Ini ({{ $todayName }})
                </button>
                <button type="button" 
                        @click="viewMode = 'lengkap'"
                        :class="viewMode === 'lengkap' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900'"
                        class="px-4 py-2 rounded-lg transition">
                    Jadwal Lengkap Seminggu
                </button>
            </div>
        </div>

        <!-- Mode 1: Jadwal Hari Ini & Keterangan Kehadiran Siswa -->
        <div x-show="viewMode === 'hari_ini'" class="space-y-4">

            <!-- Banner Ringkasan Kehadiran Hari Ini -->
            <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-emerald-50 via-teal-50 to-slate-50 border border-emerald-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-2xs">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-lg shadow-sm shadow-emerald-600/30 flex-shrink-0">
                        <i class="bi-calendar-check-fill"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-slate-900 text-sm sm:text-base flex items-center gap-2">
                            <span>Status Kehadiran Anda Hari Ini</span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 border border-emerald-200">
                                {{ $todayName }}, {{ \Carbon\Carbon::parse($todayDate)->translatedFormat('d F Y') }}
                            </span>
                        </h4>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Status presensi dicatat langsung oleh masing-masing guru pengampu pada setiap jam mata pelajaran.
                        </p>
                    </div>
                </div>

                <!-- Rekap Badges Hari Ini -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    @if($rekapHariIni['hadir'] > 0)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <i class="bi-check-circle-fill text-emerald-600"></i> {{ $rekapHariIni['hadir'] }} Hadir
                    </span>
                    @endif
                    @if($rekapHariIni['terlambat'] > 0)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        <i class="bi-clock-history text-amber-600"></i> {{ $rekapHariIni['terlambat'] }} Terlambat
                    </span>
                    @endif
                    @if($rekapHariIni['izin'] > 0)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                        <i class="bi-info-circle-fill text-blue-600"></i> {{ $rekapHariIni['izin'] }} Izin
                    </span>
                    @endif
                    @if($rekapHariIni['sakit'] > 0)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-purple-100 text-purple-800 border border-purple-300">
                        <i class="bi-bandaid-fill text-purple-600"></i> {{ $rekapHariIni['sakit'] }} Sakit
                    </span>
                    @endif
                    @if($rekapHariIni['alpa'] > 0)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                        <i class="bi-x-circle-fill text-rose-600"></i> {{ $rekapHariIni['alpa'] }} Alpa
                    </span>
                    @endif
                    @if($rekapHariIni['belum'] > 0)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                        <i class="bi-hourglass-split"></i> {{ $rekapHariIni['belum'] }} Belum Presensi
                    </span>
                    @endif
                </div>
            </div>

            @if($jadwalHariIni->isEmpty())
            <div class="py-12 text-center text-slate-400 text-xs bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                <i class="bi-calendar2-check text-4xl mb-2 text-slate-300 inline-block"></i>
                <p class="font-bold text-slate-700 text-sm">Tidak Ada Jadwal Pelajaran Hari Ini ({{ $todayName }})</p>
                <p class="text-slate-500 mt-0.5">Silakan klik tombol "Jadwal Lengkap Seminggu" di atas untuk memeriksa jadwal hari lainnya.</p>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($jadwalHariIni as $j)
                @php
                    $cuti = $j->getCutiAktif();
                    $guruAktif = $j->getGuruPengajar();
                    $isRunning = $j->isRunningNow();
                    $presensi = $j->presensi_siswa_hari_ini;
                    $laporan = $j->laporan_hari_ini;
                    $now = now()->format('H:i:s');
                    $isPassed = ($now > $j->jam_selesai);
                    $isUpcoming = ($now < $j->jam_mulai);
                @endphp
                <div class="p-5 rounded-2xl border transition duration-200 flex flex-col justify-between space-y-3 {{ $isRunning ? 'border-2 border-emerald-500 bg-white ring-4 ring-emerald-500/10 shadow-sm' : ($presensi ? 'border-slate-200 bg-white shadow-2xs' : 'border-slate-200 bg-slate-50/70') }}">
                    
                    <div class="space-y-2.5">
                        <!-- Top Bar: Jam, Ruang & Status Sesi -->
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200">
                                    <i class="bi-clock mr-1"></i>{{ substr($j->jam_mulai,0,5) }} - {{ substr($j->jam_selesai,0,5) }}
                                </span>
                                @if($isRunning)
                                <span class="px-2 py-0.5 rounded-md bg-emerald-500 text-white font-extrabold text-[10px] tracking-wide uppercase flex items-center gap-1 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span> KBM Berlangsung
                                </span>
                                @endif
                            </div>
                            <span class="text-slate-500 text-[11px] font-medium flex items-center gap-1">
                                <i class="bi-geo-alt-fill text-slate-400"></i> {{ $j->ruang ?: 'Kelas' }}
                            </span>
                        </div>

                        <!-- Mata Pelajaran -->
                        <div>
                            <h4 class="font-extrabold text-slate-900 text-base leading-snug">
                                {{ $j->mataPelajaran->nama ?? 'Mata Pelajaran' }}
                            </h4>
                            <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1.5">
                                <i class="bi-person-badge text-slate-400"></i>
                                @if($cuti)
                                <span>Guru Inval: <strong>{{ $guruAktif->name ?? '-' }}</strong> <span class="text-amber-600 font-semibold">(Menggantikan)</span></span>
                                @else
                                <span>Guru: <strong>{{ $j->guru?->name ?? 'Guru Pengampu' }}</strong></span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Keterangan Kehadiran Siswa di Hari Itu -->
                    <div class="pt-3 border-t border-slate-200/70 space-y-3">
                        @php
                            $tugas = $j->tugasKbms->first();
                        @endphp
                        @if($tugas)
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs shadow-inner">
                            <div class="flex items-center gap-2 font-black text-amber-900 mb-1.5 uppercase tracking-wider text-[10px]">
                                <i class="bi-journal-text text-amber-600"></i>
                                <span>Tugas / Instruksi Belajar:</span>
                            </div>
                            <p class="text-slate-800 italic">"{!! nl2br(e($tugas->deskripsi_tugas)) !!}"</p>
                            @if($tugas->file_lampiran)
                            <div class="mt-2 pt-2 border-t border-amber-200/50">
                                <a href="{{ asset('storage/' . $tugas->file_lampiran) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-700 bg-indigo-100 hover:bg-indigo-200 px-2.5 py-1 rounded-lg transition">
                                    <i class="bi-paperclip"></i> Lihat Lampiran File
                                </a>
                            </div>
                            @endif
                        </div>
                        @endif
                        @if($presensi)
                            @if($presensi->status === 'hadir')
                            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs">
                                <span class="text-emerald-900 font-semibold">Kehadiran Anda:</span>
                                <span class="inline-flex items-center gap-1.5 font-black text-emerald-700 bg-emerald-100/80 px-2.5 py-1 rounded-lg border border-emerald-300">
                                    <i class="bi-check-circle-fill text-emerald-600"></i> HADIR
                                </span>
                            </div>
                            @elseif($presensi->status === 'terlambat')
                            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-between text-xs">
                                <span class="text-amber-900 font-semibold">Kehadiran Anda:</span>
                                <span class="inline-flex items-center gap-1.5 font-black text-amber-800 bg-amber-100/80 px-2.5 py-1 rounded-lg border border-amber-300">
                                    <i class="bi-clock-history text-amber-600"></i> TERLAMBAT
                                </span>
                            </div>
                            @elseif($presensi->status === 'izin')
                            <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-between text-xs">
                                <div>
                                    <div class="text-blue-900 font-semibold">Kehadiran Anda:</div>
                                    @if($presensi->keterangan)
                                    <div class="text-[11px] text-blue-700 mt-0.5 italic">"{{ $presensi->keterangan }}"</div>
                                    @endif
                                </div>
                                <span class="inline-flex items-center gap-1.5 font-black text-blue-800 bg-blue-100/80 px-2.5 py-1 rounded-lg border border-blue-300 flex-shrink-0">
                                    <i class="bi-info-circle-fill text-blue-600"></i> IZIN
                                </span>
                            </div>
                            @elseif($presensi->status === 'sakit')
                            <div class="p-3 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-between text-xs">
                                <div>
                                    <div class="text-purple-900 font-semibold">Kehadiran Anda:</div>
                                    @if($presensi->keterangan)
                                    <div class="text-[11px] text-purple-700 mt-0.5 italic">"{{ $presensi->keterangan }}"</div>
                                    @endif
                                </div>
                                <span class="inline-flex items-center gap-1.5 font-black text-purple-800 bg-purple-100/80 px-2.5 py-1 rounded-lg border border-purple-300 flex-shrink-0">
                                    <i class="bi-bandaid-fill text-purple-600"></i> SAKIT
                                </span>
                            </div>
                            @elseif($presensi->status === 'alpa')
                            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-between text-xs">
                                <span class="text-rose-900 font-semibold">Kehadiran Anda:</span>
                                <span class="inline-flex items-center gap-1.5 font-black text-rose-700 bg-rose-100/80 px-2.5 py-1 rounded-lg border border-rose-300">
                                    <i class="bi-x-circle-fill text-rose-600"></i> ALPA
                                </span>
                            </div>
                            @endif
                        @else
                            @if($isRunning)
                            <div class="p-3 rounded-xl bg-cyan-50 border border-cyan-200 flex items-center justify-between text-xs">
                                <span class="text-cyan-900 font-semibold flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-cyan-500 animate-ping"></span>
                                    Kehadiran Anda:
                                </span>
                                <span class="text-cyan-700 font-bold text-[11px]">Menunggu Presensi Guru</span>
                            </div>
                            @elseif($isUpcoming)
                            <div class="p-3 rounded-xl bg-slate-100/80 border border-slate-200 flex items-center justify-between text-xs text-slate-600">
                                <span class="font-medium">Kehadiran Anda:</span>
                                <span class="text-slate-500 font-semibold text-[11px] flex items-center gap-1">
                                    <i class="bi-hourglass"></i> Belum Dimulai
                                </span>
                            </div>
                            @else
                            <div class="p-3 rounded-xl bg-slate-100/80 border border-slate-200 flex items-center justify-between text-xs text-slate-600">
                                <span class="font-medium">Kehadiran Anda:</span>
                                <span class="text-slate-400 font-medium text-[11px] flex items-center gap-1">
                                    <i class="bi-dash-circle"></i> Belum Diinput Guru
                                </span>
                            </div>
                            @endif
                        @endif
                    </div>

                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Mode 2: Jadwal Lengkap Seminggu -->
        <div x-show="viewMode === 'lengkap'" class="space-y-6" style="display: none;">
            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
            <div class="space-y-2">
                <h4 class="font-black text-xs uppercase tracking-wider text-slate-700 border-b pb-1 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Hari {{ $hari }}</span>
                </h4>
                @if(isset($jadwalLengkap[$hari]) && $jadwalLengkap[$hari]->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                    @foreach($jadwalLengkap[$hari] as $jh)
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="flex justify-between font-mono text-[11px] text-blue-700 font-bold mb-1">
                            <span>{{ substr($jh->jam_mulai,0,5) }} - {{ substr($jh->jam_selesai,0,5) }}</span>
                            <span class="text-slate-400 font-sans">{{ $jh->ruang }}</span>
                        </div>
                        <div class="font-bold text-slate-900">{{ $jh->mataPelajaran->nama ?? 'Mata Pelajaran' }}</div>
                        <div class="text-[11px] text-slate-500 mt-1">Guru: {{ $jh->guru?->name ?? '-' }}</div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-[11px] text-slate-400 italic">Tidak ada jadwal KBM di hari ini.</p>
                @endif
            </div>
            @endforeach
        </div>

    </div>

</div>
@endsection
