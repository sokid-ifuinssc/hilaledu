@extends('layouts.app')

@section('title', 'Beranda Guru')

@section('content')
@php
    // Sapaan dinamis berdasarkan hari
    $hariAngka = \Carbon\Carbon::now()->dayOfWeek; // 0=Minggu, 1=Senin, ..., 6=Sabtu
    $sapaanData = [
        0 => [
            'icon'    => '☀️',
            'judul'   => 'Selamat Hari Minggu!',
            'pesan'   => 'Nikmati waktu istirahat dan persiapkan energi untuk pekan baru yang lebih semangat.',
            'warna'   => 'from-orange-500 to-amber-500',
            'bg'      => 'bg-amber-50 border-amber-200 text-amber-900',
        ],
        1 => [
            'icon'    => '🌟',
            'judul'   => 'Semangat Awal Pekan!',
            'pesan'   => 'Selamat datang di hari Senin! Mari wujudkan semangat dan inspirasi terbaik untuk para siswa hari ini.',
            'warna'   => 'from-indigo-500 to-blue-600',
            'bg'      => 'bg-indigo-50 border-indigo-200 text-indigo-900',
        ],
        2 => [
            'icon'    => '💪',
            'judul'   => 'Tetap Semangat!',
            'pesan'   => 'Kiprah Bapak/Ibu sangat berarti bagi kemajuan siswa. Terus berikan dedikasi terbaik!',
            'warna'   => 'from-emerald-500 to-teal-600',
            'bg'      => 'bg-emerald-50 border-emerald-200 text-emerald-900',
        ],
        3 => [
            'icon'    => '🎯',
            'judul'   => 'Pertengahan Pekan Penuh Energi!',
            'pesan'   => 'Rabu yang produktif! Terus berikan yang terbaik, siswa membutuhkan bimbingan Bapak/Ibu.',
            'warna'   => 'from-violet-500 to-purple-600',
            'bg'      => 'bg-violet-50 border-violet-200 text-violet-900',
        ],
        4 => [
            'icon'    => '📚',
            'judul'   => 'Hari yang Produktif!',
            'pesan'   => 'Satu langkah lagi menuju akhir pekan. Teruskan semangat mengajar yang luar biasa!',
            'warna'   => 'from-sky-500 to-cyan-600',
            'bg'      => 'bg-sky-50 border-sky-200 text-sky-900',
        ],
        5 => [
            'icon'    => '🤲',
            'judul'   => 'Selamat Hari Jumat yang Berkah!',
            'pesan'   => 'Semoga hari Jumat ini dipenuhi keberkahan dan kebaikan untuk kita semua. Tetap semangat!',
            'warna'   => 'from-green-500 to-emerald-600',
            'bg'      => 'bg-green-50 border-green-200 text-green-900',
        ],
        6 => [
            'icon'    => '🎉',
            'judul'   => 'Terima Kasih atas Dedikasi Bapak/Ibu!',
            'pesan'   => 'Hari terakhir sebelum libur pekan. Terima kasih telah memberikan yang terbaik! Selamat beristirahat & selamat weekend! 🏖️',
            'warna'   => 'from-rose-500 to-pink-600',
            'bg'      => 'bg-rose-50 border-rose-200 text-rose-900',
        ],
    ];
    $sapaan = $sapaanData[$hariAngka] ?? $sapaanData[1];
@endphp
<div class="space-y-4" x-data="{ 
    absenModal: false, 
    selectedJadwal: null,
    tugasModal: false,
    selectedTugasJadwal: { id: null, mapel: '', kelas: '', deskripsi: '' },
    kegiatanModal: false,
    selectedKegiatan: { id: null, judul: '', bidang: '', author: '', tanggal: '', jam: '', tempat: '', status: 'hadir', catatan: '' },
    modalJeda: false,
    jedaStatus: '{{ $presensiHarianHariIni ? $presensiHarianHariIni->status_pelaksanaan_jeda : "tugas_mandiri" }}'
}">

    <!-- 1. Header Guru + Sapaan Harian Terpadu -->
    <div class="rounded-2xl sm:rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-950 p-4 sm:p-6 text-white shadow-sm border border-slate-800/80 relative overflow-hidden">
        <!-- Background Foto Gedung Sekolah Aksen Halus -->
        <div class="absolute inset-0 bg-cover bg-center opacity-15 mix-blend-luminosity pointer-events-none" style="background-image: url('{{ asset('images/gedung-sekolah-biru.jpg') }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/95 via-indigo-950/85 to-blue-950/90 pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
            <div class="flex items-start sm:items-center gap-3 sm:gap-3.5 min-w-0">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-xl sm:text-2xl shrink-0 shadow-inner mt-0.5 sm:mt-0">
                    {{ $sapaan['icon'] }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap text-[10px] sm:text-xs">
                        <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/30 border border-indigo-400/30 font-bold text-indigo-200 uppercase tracking-wider">
                            {{ \App\Models\JadwalPelajaran::getHariIndonesia() }}, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}
                        </span>
                        <span class="text-slate-400 text-[10px] sm:text-[11px] truncate">&bull; SMK Plus Al-Hilal Arjawinangun</span>
                    </div>
                    <h1 class="text-base sm:text-xl font-black tracking-tight mt-1 leading-snug break-words">
                        {{ $sapaan['judul'] }}, {{ auth()->user()->name }}!
                    </h1>
                    <p class="text-indigo-200/90 text-xs sm:text-sm font-normal mt-1 max-w-2xl leading-relaxed">
                        {{ $sapaan['pesan'] }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0 pt-2 sm:pt-0 border-t border-white/10 sm:border-t-0 justify-end">
                <a href="{{ route('guru.profile.edit') }}" class="w-full sm:w-auto px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-slate-200 hover:text-white text-xs font-bold transition border border-white/10 flex items-center justify-center gap-1.5">
                    <i class="bi-person-gear"></i> Profil Saya
                </a>
            </div>
        </div>
    </div>

    <!-- 1.5 Panel Eksekutif Kepala Sekolah (Jika Login sebagai Kepsek) -->
    @if(auth()->user()->isKepalaSekolah())
    <div class="p-4 bg-amber-500/10 border border-amber-300 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center text-lg font-black shrink-0">
                <i class="bi-patch-check-fill"></i>
            </div>
            <div>
                <span class="font-black text-slate-900">Panel Supervisi Kepala Sekolah</span>
                <p class="text-slate-600 text-[11px]">Pengawasan presensi dan kehadiran dewan guru</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.rekap-presensi.index') }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition flex items-center gap-1">
                <i class="bi-bar-chart-line-fill"></i> Rekap Presensi Guru
            </a>
        </div>
    </div>
    @endif

    <!-- 2. Active Cuti Alert (Jika guru sedang cuti) -->
    @if($cutiAktif)
    <div class="p-4 bg-amber-500/10 border-2 border-amber-500/30 rounded-2xl flex items-start gap-3 text-amber-900 shadow-sm text-xs">
        <i class="bi-calendar-x text-xl text-amber-600 flex-shrink-0 mt-0.5"></i>
        <div>
            <h4 class="font-bold text-sm">Status Cuti Aktif</h4>
            <p class="text-amber-800 mt-0.5">
                Anda sedang dalam masa <strong>{{ $cutiAktif->jenis_cuti }}</strong> periode {{ \Carbon\Carbon::parse($cutiAktif->tanggal_mulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($cutiAktif->tanggal_selesai)->format('d M Y') }}. 
                @if($cutiAktif->guruPengganti)
                    Jadwal mengajar sementara dialihkan kepada <strong>{{ $cutiAktif->guruPengganti->name }}</strong>.
                @endif
            </p>
        </div>
    </div>
    @endif

    <!-- 3. Presensi Harian (Masuk & Pulang) - Tampilan Simpel & Bersih -->
    <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-slate-200 shadow-xs space-y-3 sm:space-y-4 max-w-full overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 gap-2">
            <h2 class="text-sm sm:text-base font-black text-slate-900 flex items-center gap-2">
                <i class="bi-fingerprint text-indigo-600 text-lg"></i>
                <span>Presensi Harian</span>
            </h2>
            <div class="flex items-center gap-1.5 text-[11px] sm:text-xs font-mono font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-xl border border-indigo-100 shrink-0">
                <i class="bi-clock-fill text-indigo-600"></i>
                <span>{{ $nowTime }} WIB</span>
            </div>
        </div>

        <!-- Pemberitahuan Mekanisme Absen Masuk & Pulang -->
        <div class="p-3 sm:p-3.5 rounded-xl sm:rounded-2xl bg-indigo-50/70 border border-indigo-200/80 text-xs text-indigo-950 flex items-start gap-2.5 sm:gap-3">
            <i class="bi-info-circle-fill text-indigo-600 text-base mt-0.5 shrink-0"></i>
            <div class="space-y-1 leading-relaxed flex-1 min-w-0">
                <span class="font-bold text-slate-900 block text-xs">Mekanisme Presensi Harian:</span>
                <p class="text-[11px] text-slate-600 leading-normal">
                    &bull; <strong>Presensi Masuk:</strong> Batas waktu kehadiran tepat waktu adalah pukul <strong>07.30 WIB</strong> (presensi setelahnya otomatis dicatat terlambat).<br>
                    &bull; <strong>Presensi Pulang:</strong> Kepulangan resmi dibuka mulai pukul <strong>14.10 WIB</strong> (presensi sebelum jam tersebut dicatat sebagai <em>Pulang Cepat</em>).
                </p>
            </div>
        </div>

        <!-- Grid 2 Kolom: Presensi Masuk & Presensi Pulang -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            
            <!-- KOLOM PRESENSI MASUK -->
            <div class="p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-slate-50 border {{ $presensiHarianHariIni && $presensiHarianHariIni->jam_masuk ? 'border-emerald-300 bg-emerald-50/30' : 'border-slate-200' }} flex flex-col justify-between gap-3 min-w-0">
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-black uppercase text-slate-700 flex items-center gap-1.5">
                            <i class="bi-door-open-fill text-emerald-600"></i> Presensi Masuk
                        </span>
                        @if($presensiHarianHariIni && $presensiHarianHariIni->jam_masuk)
                            <span class="px-2 py-0.5 rounded-lg text-[11px] font-bold {{ $presensiHarianHariIni->badge_masuk_class }} shrink-0">
                                {{ $presensiHarianHariIni->status_masuk_label }}
                            </span>
                        @else
                            <span class="text-[11px] text-slate-400 font-medium whitespace-nowrap shrink-0">Batas: 07.30 WIB</span>
                        @endif
                    </div>

                    @if($presensiHarianHariIni && $presensiHarianHariIni->jam_masuk)
                    <div class="text-xs text-slate-600">
                        Waktu Masuk: <strong class="font-mono font-black text-slate-900">{{ substr($presensiHarianHariIni->jam_masuk, 0, 5) }} WIB</strong>
                        @if($presensiHarianHariIni->terlambat_masuk_menit > 0)
                            <span class="text-rose-600 font-bold ml-1">(Terlambat {{ $presensiHarianHariIni->terlambat_masuk_menit }}m)</span>
                        @endif
                    </div>
                    @else
                    <p class="text-xs text-slate-500">
                        Presensi pagi hari (07.00 - 07.30 WIB).
                    </p>
                    @endif
                </div>

                <div>
                    @if(!$presensiHarianHariIni || empty($presensiHarianHariIni->jam_masuk))
                    <form method="POST" action="{{ route('guru.presensi-harian.masuk') }}">
                        @csrf
                        <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition shadow-sm">
                            <i class="bi-fingerprint text-sm"></i>
                            <span>Klik Presensi Masuk</span>
                        </button>
                    </form>
                    @else
                    <div class="w-full py-2 px-3 bg-emerald-100/70 border border-emerald-300 text-emerald-900 font-bold rounded-xl text-xs flex items-center justify-center gap-1.5">
                        <i class="bi-check-circle-fill text-emerald-600"></i>
                        <span>Sudah Presensi Masuk</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- KOLOM PRESENSI PULANG -->
            <div class="p-3.5 sm:p-4 rounded-xl sm:rounded-2xl bg-slate-50 border {{ $presensiHarianHariIni && $presensiHarianHariIni->jam_pulang ? 'border-blue-300 bg-blue-50/30' : 'border-slate-200' }} flex flex-col justify-between gap-3 min-w-0">
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-black uppercase text-slate-700 flex items-center gap-1.5">
                            <i class="bi-house-door-fill text-blue-600"></i> Presensi Pulang
                        </span>
                        @if($presensiHarianHariIni && $presensiHarianHariIni->jam_pulang)
                            <span class="px-2 py-0.5 rounded-lg text-[11px] font-bold {{ $presensiHarianHariIni->badge_pulang_class }} shrink-0">
                                {{ $presensiHarianHariIni->status_pulang_label }}
                            </span>
                        @else
                            <span class="text-[11px] text-slate-400 font-medium whitespace-nowrap shrink-0">Mulai: 14.10 WIB</span>
                        @endif
                    </div>

                    @if($presensiHarianHariIni && $presensiHarianHariIni->jam_pulang)
                    <div class="text-xs text-slate-600">
                        Waktu Pulang: <strong class="font-mono font-black text-slate-900">{{ substr($presensiHarianHariIni->jam_pulang, 0, 5) }} WIB</strong>
                        @if($presensiHarianHariIni->status_pulang === 'pulang_cepat')
                            <span class="text-amber-700 font-bold ml-1">(Pulang Cepat)</span>
                        @endif
                    </div>
                    @else
                    <p class="text-xs text-slate-500">
                        Presensi kepulangan resmi dimulai pukul 14.10 WIB.
                    </p>
                    @endif
                </div>

                <div>
                    @if(!$presensiHarianHariIni || empty($presensiHarianHariIni->jam_pulang))
                    <form method="POST" action="{{ route('guru.presensi-harian.pulang') }}">
                        @csrf
                        @php $isBefore14 = now()->format('H:i:s') < '14:00:00'; @endphp
                        <button type="submit" 
                                onclick="{{ $isBefore14 ? "return confirm('Saat ini belum pukul 14.00 WIB. Presensi pulang sekarang akan dicatat sebagai Pulang Cepat. Lanjutkan?');" : "" }}"
                                class="w-full py-2.5 px-4 {{ $isBefore14 ? 'bg-amber-600 hover:bg-amber-700 active:bg-amber-800' : 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800' }} text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition shadow-sm">
                            <i class="bi-box-arrow-right text-sm"></i>
                            <span>{{ $isBefore14 ? 'Presensi Pulang (Pulang Cepat)' : 'Klik Presensi Pulang' }}</span>
                        </button>
                    </form>
                    @else
                    <div class="w-full py-2 px-3 bg-blue-100/70 border border-blue-300 text-blue-900 font-bold rounded-xl text-xs flex items-center justify-center gap-1.5">
                        <i class="bi-check-circle-fill text-blue-600"></i>
                        <span>Sudah Presensi Pulang</span>
                    </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Jeda Jam KBM (Jika Jam Mengajar Pertama Jam ke-3 ke atas) -->
        @if($firstJadwalHariIni && $firstJadwalHariIni->jam_ke_mulai >= 3 && $presensiHarianHariIni && $presensiHarianHariIni->jam_masuk)
        <div class="p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div>
                <span class="font-bold text-indigo-950">Laporan Jeda KBM:</span>
                <span class="text-slate-700 ml-1">Jadwal mengajar pertama jam ke-{{ $firstJadwalHariIni->jam_ke_mulai }} ({{ substr($firstJadwalHariIni->jam_mulai,0,5) }} WIB)</span>
                @if($presensiHarianHariIni->status_pelaksanaan_jeda === 'izin_keluar')
                    <span class="text-amber-800 font-semibold block sm:inline sm:ml-2">&bull; Izin Keluar: "{{ $presensiHarianHariIni->izin_keluar_alasan }}"</span>
                @endif
            </div>
            <button type="button" @click="modalJeda = true" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shrink-0">
                Ubah Laporan Jeda
            </button>
        </div>
        @endif
    </div>

    <!-- 4. Jadwal Mengajar Hari Ini (Absen Mapel - Simpel: Jam ke berapa, Mapel apa, Kelas mana) -->
    <div id="jadwal-hari-ini" class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                <i class="bi-calendar-check text-blue-600 text-lg"></i>
                <span>Jadwal Mengajar Hari Ini</span>
            </h2>
            <span class="text-xs font-semibold text-slate-500">
                Hari: <strong class="text-slate-800">{{ $todayName }}</strong>
            </span>
        </div>

        @if($jadwalHariIni->isEmpty() && $jadwalInval->isEmpty())
        <div class="bg-white rounded-3xl p-8 text-center border border-slate-200 shadow-xs">
            <i class="bi-calendar-x text-3xl text-slate-300"></i>
            <h3 class="font-bold text-slate-700 text-sm mt-2">Tidak Ada Jadwal Mengajar Hari Ini</h3>
            <p class="text-slate-400 text-xs">Anda tidak memiliki jadwal mengajar pada hari {{ $todayName }}.</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($jadwalHariIni as $j)
            @php
                $isRunning = $j->isRunningNow();
                $presensi = $j->absensiHariIni;
                $tugas = $j->tugasHariIni;
            @endphp
            <div class="bg-white rounded-3xl p-5 border {{ $isRunning ? 'border-2 border-emerald-500 ring-4 ring-emerald-500/10' : 'border-slate-200' }} shadow-xs flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <!-- Jam Ke-Berapa & Waktu -->
                    <div class="flex items-center justify-between text-xs">
                        <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold">
                            Jam ke-{{ $j->jam_ke_mulai }}{{ $j->jam_ke_selesai > $j->jam_ke_mulai ? ' - ' . $j->jam_ke_selesai : '' }} ({{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }})
                        </span>
                        @if($isRunning)
                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase tracking-wider animate-pulse">
                            Sedang Berlangsung
                        </span>
                        @endif
                    </div>

                    <!-- Mata Pelajaran & Kelas -->
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 leading-snug">
                            {{ $j->mataPelajaran->nama ?? 'Mata Pelajaran' }}
                        </h3>
                        <div class="mt-1">
                            <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-800 text-xs font-bold">
                                Kelas {{ $j->kelas }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi: Absen & Tugas -->
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    @if(!$presensi)
                    <button type="button" 
                            @click="absenModal = true; selectedJadwal = { id: {{ $j->id }}, mapel: '{{ addslashes($j->mataPelajaran->nama ?? 'Mata Pelajaran') }}', kelas: '{{ $j->kelas }}', jam: '{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}' }"
                            class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition shadow-sm">
                        <i class="bi-fingerprint text-sm"></i>
                        <span>Klik Absen Kehadiran</span>
                    </button>
                    <button type="button" 
                            @click="tugasModal = true; selectedTugasJadwal = { id: {{ $j->id }}, mapel: '{{ addslashes($j->mataPelajaran->nama ?? 'Mata Pelajaran') }}', kelas: '{{ $j->kelas }}', deskripsi: '{{ addslashes($tugas->deskripsi_tugas ?? '') }}' }"
                            class="w-full py-2 px-3 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition border border-amber-200">
                        <i class="bi-journal-text text-sm"></i>
                        <span>{{ $tugas ? 'Edit Tugas Siswa' : 'Tinggalkan Tugas' }}</span>
                    </button>
                    @else
                    <div class="w-full py-2 px-3 bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold rounded-xl text-xs flex items-center justify-center gap-1.5">
                        <i class="bi-check-circle-fill text-emerald-600"></i>
                        <span>Sudah Presensi ({{ \Carbon\Carbon::parse($presensi->waktu_masuk ?? $presensi->created_at)->format('H:i') }})</span>
                    </div>
                    @if($presensi->status !== 'hadir')
                    <button type="button" 
                            @click="tugasModal = true; selectedTugasJadwal = { id: {{ $j->id }}, mapel: '{{ addslashes($j->mataPelajaran->nama ?? 'Mata Pelajaran') }}', kelas: '{{ $j->kelas }}', deskripsi: '{{ addslashes($tugas->deskripsi_tugas ?? '') }}' }"
                            class="w-full py-2 px-3 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition border border-amber-200">
                        <i class="bi-journal-text text-sm"></i>
                        <span>{{ $tugas ? 'Edit Tugas Siswa' : 'Input Tugas Siswa' }}</span>
                    </button>
                    @endif
                    @endif
                </div>
            </div>
            @endforeach

            <!-- Inval Mengajar (Jika menggantikan guru cuti) -->
            @foreach($jadwalInval as $j)
            @php $presensiInval = $j->absensiHariIni; @endphp
            <div class="bg-amber-50/40 rounded-3xl p-5 border-2 border-amber-300 shadow-xs flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="px-2 py-0.5 rounded-lg bg-amber-200 text-amber-900 font-bold text-[10px] uppercase">
                            Tugas Inval (Guru Pengganti)
                        </span>
                        <span class="font-semibold text-slate-500 text-xs">
                            Jam ke-{{ $j->jam_ke_mulai }} ({{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }})
                        </span>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">{{ $j->mataPelajaran->nama ?? 'Mata Pelajaran' }}</h3>
                        <p class="text-xs text-slate-600 mt-0.5">Kelas {{ $j->kelas }} &bull; Menggantikan: <strong>{{ $j->guru?->name ?? 'Guru Pengampu' }}</strong></p>
                    </div>
                </div>

                <div class="pt-3 border-t border-amber-200">
                    @if(!$presensiInval)
                    <button type="button" 
                            @click="absenModal = true; selectedJadwal = { id: {{ $j->id }}, mapel: '{{ addslashes($j->mataPelajaran->nama ?? 'Mata Pelajaran') }} (Inval)', kelas: '{{ $j->kelas }}', jam: '{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}' }"
                            class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition shadow-sm">
                        <i class="bi-fingerprint text-sm"></i>
                        <span>Klik Absen Kehadiran (Inval)</span>
                    </button>
                    @else
                    <div class="w-full py-2 px-3 bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold rounded-xl text-xs flex items-center justify-center gap-1.5">
                        <i class="bi-check-circle-fill text-emerald-600"></i>
                        <span>Sudah Absen Inval</span>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- 5. Informasi Kegiatan Sekolah -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="bi-calendar-event text-amber-600 text-lg"></i>
                    <span>Informasi Kegiatan Sekolah</span>
                </h2>
                <a href="{{ route('guru.kegiatan.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition">
                    Semua Kegiatan &rarr;
                </a>
            </div>

            @if($kegiatanTerbaru->isEmpty())
            <div class="py-8 text-center text-slate-400">
                <i class="bi-calendar-x text-3xl text-slate-300 mb-1 block"></i>
                <p class="text-xs">Tidak ada agenda kegiatan sekolah terdekat.</p>
            </div>
            @else
            <div class="space-y-3">
                @foreach($kegiatanTerbaru as $k)
                @php
                    $presensiKegiatan = $k->presensi_saya;
                    $tglKegiatan = \Carbon\Carbon::parse($k->tanggal_kegiatan)->startOfDay();
                    $tglHariIni = \Carbon\Carbon::now()->startOfDay();
                    $diffHari = (int) $tglHariIni->diffInDays($tglKegiatan, false);
                @endphp
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="space-y-1 flex-1">
                        <div class="font-extrabold text-slate-900 text-sm">
                            {{ $k->judul }}
                        </div>
                        <div class="text-slate-500 text-[11px] flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-slate-700">
                                <i class="bi-calendar3 text-indigo-600 mr-1"></i>{{ \Carbon\Carbon::parse($k->tanggal_kegiatan)->isoFormat('D MMMM Y') }}
                            </span>
                            @if($k->waktu_mulai)
                            <span>&bull; {{ substr($k->waktu_mulai, 0, 5) }} - {{ substr($k->waktu_selesai, 0, 5) }} WIB</span>
                            @endif
                            @if($k->tempat)
                            <span>&bull; <i class="bi-geo-alt text-rose-500"></i> {{ $k->tempat }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Tombol Presensi Kehadiran Kegiatan: Hanya Bisa Diklik Saat Hari H -->
                    <div class="shrink-0 flex items-center gap-2">
                        @if($presensiKegiatan)
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-xl text-xs font-bold border {{ $presensiKegiatan->badge_class }}">
                                    {{ $presensiKegiatan->status_label }}
                                </span>
                                <button type="button"
                                        @click="kegiatanModal = true; selectedKegiatan = { 
                                            id: {{ $k->id }}, 
                                            judul: '{{ addslashes($k->judul) }}', 
                                            bidang: '{{ addslashes($k->bidang_label) }}', 
                                            author: '{{ addslashes($k->author->name ?? 'Pimpinan') }}', 
                                            tanggal: '{{ \Carbon\Carbon::parse($k->tanggal_kegiatan)->isoFormat('dddd, D MMMM Y') }}', 
                                            jam: '{{ substr($k->waktu_mulai, 0, 5) }} - {{ substr($k->waktu_selesai, 0, 5) }} WIB', 
                                            tempat: '{{ addslashes($k->tempat) }}', 
                                            status: '{{ $presensiKegiatan->status }}', 
                                            catatan: '{{ addslashes($presensiKegiatan->catatan ?? '') }}' 
                                        }"
                                        class="text-blue-600 hover:text-blue-800 font-bold underline text-[11px]">
                                    Ubah
                                </button>
                            </div>
                        @else
                            @if($diffHari === 0)
                                <!-- HARI H PELAKSANAAN: BISA DIKLIK -->
                                <button type="button" 
                                        @click="kegiatanModal = true; selectedKegiatan = { 
                                            id: {{ $k->id }}, 
                                            judul: '{{ addslashes($k->judul) }}', 
                                            bidang: '{{ addslashes($k->bidang_label) }}', 
                                            author: '{{ addslashes($k->author->name ?? 'Pimpinan') }}', 
                                            tanggal: '{{ \Carbon\Carbon::parse($k->tanggal_kegiatan)->isoFormat('dddd, D MMMM Y') }}', 
                                            jam: '{{ substr($k->waktu_mulai, 0, 5) }} - {{ substr($k->waktu_selesai, 0, 5) }} WIB', 
                                            tempat: '{{ addslashes($k->tempat) }}', 
                                            status: 'hadir', 
                                            catatan: '' 
                                        }"
                                        class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow-sm">
                                    <i class="bi-fingerprint"></i>
                                    <span>Klik Hadir (Hari H)</span>
                                </button>
                            @elseif($diffHari > 0)
                                <!-- SEBELUM PELAKSANAAN: TIDAK BISA DIKLIK -->
                                <button type="button" 
                                        disabled
                                        title="Presensi kehadiran hanya dapat diisi pada hari H pelaksanaan kegiatan."
                                        class="px-3 py-1.5 bg-slate-100 text-slate-400 font-semibold rounded-xl text-xs cursor-not-allowed border border-slate-200">
                                    <i class="bi-lock-fill mr-1"></i> Hadir saat Hari H (H-{{ $diffHari }})
                                </button>
                            @else
                                <!-- SUDAH LEWAT -->
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-400 rounded-lg text-[11px] font-semibold">
                                    Selesai
                                </span>
                            @endif
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif


    <!-- MODAL POPUP: Presensi Guru Mapel -->
    <div x-show="absenModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative"
             @click.outside="absenModal = false">
            
            <button @click="absenModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <div class="text-center mb-5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto mb-2 text-xl shadow-inner">
                    <i class="bi-fingerprint"></i>
                </div>
                <h3 class="text-base font-black text-slate-900">Presensi Mengajar</h3>
                <p class="text-xs text-slate-600 mt-0.5 font-bold" x-text="selectedJadwal ? selectedJadwal.mapel + ' - Kelas ' + selectedJadwal.kelas : ''"></p>
                <p class="text-[11px] font-mono text-blue-700 font-bold" x-text="selectedJadwal ? 'Jadwal: ' + selectedJadwal.jam : ''"></p>
            </div>

            <form method="POST" action="{{ route('guru.absensi.store') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="jadwal_pelajaran_id" :value="selectedJadwal ? selectedJadwal.id : ''">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        Status Kehadiran
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="p-2.5 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-emerald-500 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50 transition flex items-center gap-2 font-semibold">
                            <input type="radio" name="status" value="hadir" checked class="text-emerald-600">
                            <span>✅ Hadir</span>
                        </label>
                        <label class="p-2.5 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-500 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 transition flex items-center gap-2 font-semibold">
                            <input type="radio" name="status" value="izin" class="text-blue-600">
                            <span>📝 Izin</span>
                        </label>
                        <label class="p-2.5 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-purple-500 has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50 transition flex items-center gap-2 font-semibold">
                            <input type="radio" name="status" value="sakit" class="text-purple-600">
                            <span>🏥 Sakit</span>
                        </label>
                        <label class="p-2.5 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-indigo-500 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50 transition flex items-center gap-2 font-semibold">
                            <input type="radio" name="status" value="tugas_luar" class="text-indigo-600">
                            <span>🚗 Tugas Luar</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan (Opsional)</label>
                    <textarea name="catatan" rows="2" placeholder="Catatan izin/sakit/tugas luar..." class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Lampiran Bukti (Opsional)</label>
                    <input type="file" name="lampiran" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-slate-100 file:text-slate-700">
                </div>

                <div class="pt-2 flex items-center gap-2">
                    <button type="button" @click="absenModal = false" class="w-1/3 py-2 bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="w-2/3 py-2 bg-emerald-600 hover:bg-emerald-700 font-bold text-white rounded-xl transition shadow-sm">
                        Simpan Presensi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL POPUP: Presensi Kegiatan Sekolah -->
    <div x-show="kegiatanModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative"
             @click.outside="kegiatanModal = false">
            
            <button @click="kegiatanModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <div class="text-center mb-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-2 text-xl shadow-inner">
                    <i class="bi-calendar-check-fill"></i>
                </div>
                <h3 class="text-base font-black text-slate-900">Presensi Kegiatan Sekolah</h3>
                <p class="text-xs text-slate-600 mt-0.5 font-bold" x-text="selectedKegiatan ? selectedKegiatan.judul : ''"></p>
                <p class="text-[11px] text-slate-400" x-text="selectedKegiatan ? selectedKegiatan.tanggal + ' • ' + selectedKegiatan.jam : ''"></p>
            </div>

            <form method="POST" :action="'{{ url('guru/kegiatan') }}/' + (selectedKegiatan ? selectedKegiatan.id : '') + '/presensi'" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Pilih Status Kehadiran</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="p-2.5 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-emerald-500 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/80 transition flex flex-col items-center text-center font-bold">
                            <input type="radio" name="status" value="hadir" x-model="selectedKegiatan.status" class="text-emerald-600">
                            <span class="text-emerald-800 mt-1">✅ Hadir</span>
                        </label>
                        <label class="p-2.5 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-500 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/80 transition flex flex-col items-center text-center font-bold">
                            <input type="radio" name="status" value="izin" x-model="selectedKegiatan.status" class="text-blue-600">
                            <span class="text-blue-800 mt-1">📝 Izin</span>
                        </label>
                        <label class="p-2.5 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-purple-500 has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50/80 transition flex flex-col items-center text-center font-bold">
                            <input type="radio" name="status" value="sakit" x-model="selectedKegiatan.status" class="text-purple-600">
                            <span class="text-purple-800 mt-1">🏥 Sakit</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan (Opsional)</label>
                    <textarea name="catatan" rows="2" x-model="selectedKegiatan.catatan" placeholder="Keterangan hadir/alasan izin..." class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Foto Bukti / Lampiran (Opsional)</label>
                    <input type="file" name="foto_bukti" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-slate-100 file:text-slate-700">
                </div>

                <div class="pt-2 flex items-center gap-2">
                    <button type="button" @click="kegiatanModal = false" class="w-1/3 py-2 bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="w-2/3 py-2 bg-emerald-600 hover:bg-emerald-700 font-bold text-white rounded-xl transition shadow-sm">
                        Simpan Presensi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL POPUP: Input Tugas Siswa -->
    <div x-show="tugasModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative"
             @click.outside="tugasModal = false">
            <button @click="tugasModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <div class="text-center mb-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-2 text-xl shadow-inner">
                    <i class="bi-journal-text"></i>
                </div>
                <h3 class="text-base font-black text-slate-900">Input Tugas Siswa</h3>
                <p class="text-xs text-slate-500" x-text="selectedTugasJadwal?.mapel + ' - Kelas ' + selectedTugasJadwal?.kelas"></p>
            </div>

            <form method="POST" action="{{ route('guru.tugas-absen.store') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="jadwal_pelajaran_id" :value="selectedTugasJadwal?.id">
                <input type="hidden" name="tanggal" value="{{ $todayDate }}">

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Deskripsi / Instruksi Tugas:
                    </label>
                    <textarea name="deskripsi_tugas" rows="3" x-model="selectedTugasJadwal.deskripsi" class="w-full p-2.5 bg-white border border-slate-300 rounded-xl text-xs" placeholder="Contoh: Kerjakan modul halaman 15 nomor 1-5..."></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Lampiran File (Opsional):</label>
                    <input type="file" name="file_lampiran" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-slate-100 file:text-slate-700">
                </div>

                <div class="pt-2 flex items-center gap-2">
                    <button type="button" @click="tugasModal = false" class="w-1/3 py-2 bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="w-2/3 py-2 bg-indigo-600 hover:bg-indigo-700 font-bold text-white rounded-xl transition shadow-sm">
                        Simpan Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL POPUP: Ubah Laporan Jeda -->
    <div x-show="modalJeda"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative"
             @click.outside="modalJeda = false">
            <button @click="modalJeda = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <div class="text-center mb-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center mx-auto mb-2 text-xl shadow-inner">
                    <i class="bi-clock-history"></i>
                </div>
                <h3 class="text-base font-black text-slate-900">Laporan Jam Jeda KBM</h3>
                <p class="text-xs text-slate-500 mt-0.5">Aktivitas sebelum jam mengajar Anda dimulai</p>
            </div>

            <form method="POST" action="{{ route('guru.presensi-harian.jeda') }}" class="space-y-4 text-xs">
                @csrf
                <div class="space-y-2">
                    <label class="p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-indigo-500 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/70 font-bold flex items-center gap-2.5 transition">
                        <input type="radio" name="status" value="tugas_mandiri" x-model="jedaStatus" class="text-indigo-600">
                        <div>
                            <div class="text-slate-900">🏢 Tugas Mandiri di Sekolah</div>
                            <div class="text-[11px] text-slate-500 font-normal">Perangkat ajar atau standby di ruang guru.</div>
                        </div>
                    </label>

                    <label class="p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-amber-500 has-[:checked]:border-amber-600 has-[:checked]:bg-amber-50/70 font-bold flex items-center gap-2.5 transition">
                        <input type="radio" name="status" value="izin_keluar" x-model="jedaStatus" class="text-amber-600">
                        <div>
                            <div class="text-slate-900">🚪 Izin Keluar Sementara</div>
                            <div class="text-[11px] text-amber-800 font-normal">Keperluan singkat sebelum jam KBM dimulai.</div>
                        </div>
                    </label>
                </div>

                <div x-show="jedaStatus === 'izin_keluar'" class="space-y-3 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                    <div>
                        <label class="block font-bold text-amber-900 mb-1">Alasan Izin:</label>
                        <textarea name="alasan" rows="2" placeholder="Contoh: Mengambil berkas dinas..." class="w-full p-2 bg-white border border-amber-300 rounded-xl text-xs"></textarea>
                    </div>
                    <div>
                        <label class="block font-bold text-amber-900 mb-1">Perkiraan Jam Kembali:</label>
                        <input type="time" name="jam_kembali" value="{{ $firstJadwalHariIni ? substr($firstJadwalHariIni->jam_mulai,0,5) : '08:30' }}" class="w-full p-2 bg-white border border-amber-300 rounded-xl text-xs font-mono font-bold">
                    </div>
                </div>

                <div class="pt-2 flex items-center gap-2">
                    <button type="button" @click="modalJeda = false" class="w-1/3 py-2 bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="w-2/3 py-2 bg-indigo-600 hover:bg-indigo-700 font-bold text-white rounded-xl transition shadow-sm">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
