@extends('layouts.app')

@section('title', 'Kalender Pendidikan & Akademik - ' . ($schoolInfo['nama'] ?? 'SMK Plus Al-Hilal'))

@section('content')
@php
    $canManage = auth()->user()->canManageAcademic();
    $kalenderIndexRoute = $canManage ? 'admin.kalender.index' : 'guru.kalender.index';
    $kalenderPrintRoute = $canManage ? 'admin.kalender.print' : 'guru.kalender.print';
@endphp
<div class="space-y-6" x-data="{
    eventModal: false,
    editEventModal: false,
    settingModal: false,
    createYearModal: false,
    importModal: false,
    importTab: 'file',
    activeSemester: '{{ $semester }}',
    eventForm: {
        id: '',
        judul_kegiatan: '',
        tanggal_mulai: '',
        tanggal_selesai: '',
        semester: '{{ $semester }}',
        kategori: 'kegiatan_sekolah',
        warna_bg: 'yellow',
        is_libur: false,
        keterangan: ''
    },
    openAdd(dateStr = '') {
        this.eventForm = {
            id: '',
            judul_kegiatan: '',
            tanggal_mulai: dateStr || new Date().toISOString().split('T')[0],
            tanggal_selesai: dateStr || new Date().toISOString().split('T')[0],
            semester: this.activeSemester,
            kategori: 'kegiatan_sekolah',
            warna_bg: 'yellow',
            is_libur: false,
            keterangan: ''
        };
        this.eventModal = true;
    },
    openEdit(item) {
        this.eventForm = { ...item };
        this.editEventModal = true;
    }
}">

    <!-- KOP RESMI SEKOLAH & HEADER KALENDER AKADEMIK -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
        
        <!-- Kop Surat Sekolah -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 pb-6 border-b border-slate-200">
            <div class="flex items-center gap-4">
                <img src="{{ asset($schoolInfo['logo']) }}" alt="Logo Sekolah" class="w-16 h-16 sm:w-20 sm:h-20 object-contain drop-shadow-xs" onerror="this.src='{{ asset('images/logo.png') }}'">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 text-[11px] font-bold border border-emerald-200 mb-1">
                        <i class="bi-patch-check-fill text-emerald-600"></i>
                        <span>NPSN: {{ $schoolInfo['npsn'] }} &bull; Terakreditasi Baik</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight uppercase">
                        {{ $schoolInfo['nama'] }} Arjawinangun
                    </h1>
                    <p class="text-xs text-slate-500 max-w-xl line-clamp-2">
                        {{ $schoolInfo['alamat'] }}
                    </p>
                </div>
            </div>

            <!-- Toolbar Tahun Ajaran & Cetak -->
            <div class="flex flex-wrap items-center gap-2 self-stretch md:self-auto justify-end">
                <form method="GET" action="{{ route($kalenderIndexRoute) }}" class="flex items-center gap-2">
                    <input type="hidden" name="semester" value="{{ $semester }}">
                    <select name="tahun_ajaran" onchange="this.form.submit()" class="px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-blue-500">
                        @foreach($allKalenders as $kItem)
                        <option value="{{ $kItem->tahun_ajaran }}" {{ $kalender->tahun_ajaran === $kItem->tahun_ajaran ? 'selected' : '' }}>
                            Tahun Ajaran {{ $kItem->tahun_ajaran }}
                        </option>
                        @endforeach
                    </select>
                </form>

                <a href="{{ route($kalenderPrintRoute, ['kalender' => $kalender->id, 'semester' => $semester]) }}" target="_blank"
                   class="px-4 py-2.5 bg-slate-900 hover:bg-black text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition">
                    <i class="bi-printer-fill text-sm"></i>
                    <span>Cetak Kalender</span>
                </a>

                @if($canManage)
                <button type="button" @click="createYearModal = true"
                        class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow-sm" title="Tambah Tahun Ajaran Baru (Multi-Year)">
                    <i class="bi-plus-circle-fill"></i>
                    <span>+ Tahun Ajaran</span>
                </button>
                <button type="button" @click="settingModal = true"
                        class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition border border-slate-200" title="Atur Rentang Tahun Akademik Ini">
                    <i class="bi-gear-fill text-base"></i>
                </button>
                @endif
            </div>
        </div>

        <!-- Banner Judul Kalender & Action Bar -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <span class="font-bold text-blue-700 uppercase tracking-wider">Kalender Pendidikan Resmi</span>
                    <span>&bull;</span>
                    <span>Provinsi Jawa Barat & Standar Kurikulum Merdeka</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900">
                    Kalender Pendidikan Tahun Ajaran {{ $kalender->tahun_ajaran }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Rentang Efektif: <strong>{{ \Carbon\Carbon::parse($kalender->tanggal_mulai)->isoFormat('D MMMM Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($kalender->tanggal_selesai)->isoFormat('D MMMM Y') }}</strong>
                </p>
            </div>

            <!-- Tab Semester & Tombol Aksi -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Toggle Semester 1 & Semester 2 -->
                <div class="flex items-center p-1 bg-slate-100 rounded-2xl border border-slate-200">
                    <a href="{{ route($kalenderIndexRoute, ['tahun_ajaran' => $kalender->tahun_ajaran, 'semester' => '1']) }}"
                       class="px-4 py-2 rounded-xl text-xs font-black transition {{ $semester === '1' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        SEMESTER 1 (Juli - Des)
                    </a>
                    <a href="{{ route($kalenderIndexRoute, ['tahun_ajaran' => $kalender->tahun_ajaran, 'semester' => '2']) }}"
                       class="px-4 py-2 rounded-xl text-xs font-black transition {{ $semester === '2' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        SEMESTER 2 (Jan - Jul)
                    </a>
                </div>

                @if(auth()->user()->canManageAcademic())
                <!-- Tombol Sinkronisasi Google Calendar Hari Libur -->
                <form method="POST" action="{{ route('admin.kalender.sync-holidays', $kalender) }}" class="inline">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('Tarik hari libur nasional & keagamaan resmi dari Google Calendar? Hari libur yang sudah ada akan diperbarui secara otomatis.');"
                            class="px-3.5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200 font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow-2xs">
                        <i class="bi-google text-rose-600"></i>
                        <span>Tarik Libur Nasional</span>
                    </button>
                </form>

                <!-- Tombol Muat Agenda Standar Disdik -->
                <form method="POST" action="{{ route('admin.kalender.seed-official', $kalender) }}" class="inline">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('Muat agenda pendidikan standar Jawa Barat (MPLS, Asesmen, Rapor, Libur Ramadhan)?');"
                            class="px-3.5 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow-2xs">
                        <i class="bi-magic text-emerald-600"></i>
                        <span>Muat Agenda Disdik</span>
                    </button>
                </form>

                <!-- Tombol Import Banyak Kegiatan (Excel / CSV / Paste) -->
                <button type="button" @click="importModal = true"
                        class="px-3.5 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-900 border border-indigo-200 font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow-2xs">
                    <i class="bi-file-earmark-arrow-up-fill text-indigo-600"></i>
                    <span>Import Banyak Kegiatan</span>
                </button>

                <!-- Tombol Tambah Agenda -->
                <button type="button" @click="openAdd()"
                        class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow-sm">
                    <i class="bi-plus-circle-fill text-sm"></i>
                    <span>Tambah Agenda</span>
                </button>
                @endif
            </div>
        </div>

        <!-- Panduan Warna Keterangan (Legend) -->
        <div class="pt-4 border-t border-slate-100 flex items-center gap-3 flex-wrap text-[11px] font-bold">
            <span class="text-slate-400 uppercase tracking-wider text-[10px]">Keterangan Warna:</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 border border-rose-200 text-rose-800">
                <span class="w-3 h-3 rounded-full bg-rose-600"></span> Libur Nasional / Minggu / Libur Semester
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-amber-900">
                <span class="w-3 h-3 rounded-full bg-amber-400"></span> Kegiatan Khusus / MPLS / Pesantren
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-900">
                <span class="w-3 h-3 rounded-full bg-blue-600"></span> Asesmen / Ujian / TKA / ASAJ
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-purple-50 border border-purple-200 text-purple-900">
                <span class="w-3 h-3 rounded-full bg-purple-600"></span> Uji Kompetensi Keahlian (UKK)
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-cyan-50 border border-cyan-200 text-cyan-900">
                <span class="w-3 h-3 rounded-full bg-cyan-600"></span> Pembagian Rapor
            </span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span> KBM Efektif
            </span>
        </div>

    </div>

    <!-- MATRIKS KALENDER BULANAN (FORMAT PERSIS CONTOH DISDIK JABAR) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between px-2">
            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight flex items-center gap-2">
                <i class="bi-calendar3 text-blue-600"></i>
                <span>Matriks Kalender Pendidikan — SEMESTER {{ $semester }}</span>
            </h3>
            <span class="text-xs text-slate-500 font-semibold">
                {{ count($matrix) }} Bulan &bull; {{ $canManage ? 'Klik tanggal untuk melihat atau menambah agenda' : 'Kalender resmi acuan KBM dan kegiatan sekolah' }}
            </span>
        </div>

        <!-- Grid 3 Kolom per Semester (Total 6-7 Bulan) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($matrix as $monthData)
            <div class="bg-white rounded-2xl border-2 border-slate-300 shadow-xs overflow-hidden flex flex-col justify-between">
                
                <!-- Header Bulan Biru Navy Khas Kalender Dinas -->
                <div class="bg-gradient-to-r from-blue-700 to-indigo-800 text-white text-center py-2 px-3 font-black text-sm tracking-wider uppercase shadow-xs">
                    {{ $monthData['month_name'] }} {{ $monthData['year'] }}
                </div>

                <!-- Tabel Hari & Tanggal -->
                <div class="p-2 flex-1">
                    <table class="w-full border-collapse text-center text-xs">
                        <thead>
                            <tr class="font-black text-[11px] border-b border-slate-300">
                                <th class="py-1.5 text-rose-600 bg-rose-50/80 border-r border-slate-200 w-[14.28%]">Minggu</th>
                                <th class="py-1.5 text-slate-700 bg-slate-100 border-r border-slate-200 w-[14.28%]">Senin</th>
                                <th class="py-1.5 text-slate-700 bg-slate-100 border-r border-slate-200 w-[14.28%]">Selasa</th>
                                <th class="py-1.5 text-slate-700 bg-slate-100 border-r border-slate-200 w-[14.28%]">Rabu</th>
                                <th class="py-1.5 text-slate-700 bg-slate-100 border-r border-slate-200 w-[14.28%]">Kamis</th>
                                <th class="py-1.5 text-slate-700 bg-slate-100 border-r border-slate-200 w-[14.28%]">Jumat</th>
                                <th class="py-1.5 text-slate-700 bg-slate-100 w-[14.28%]">Sabtu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 font-bold">
                            @foreach($monthData['weeks'] as $week)
                            <tr class="divide-x divide-slate-200">
                                @foreach($week as $cell)
                                @php
                                    $hasEvents = !empty($cell['events']);
                                    $tooltipTitle = '';
                                    if ($hasEvents) {
                                        $tooltipTitle = implode(' | ', array_column($cell['events'], 'judul_kegiatan'));
                                    } elseif ($cell['is_sunday']) {
                                        $tooltipTitle = 'Hari Minggu (Libur Mingguan)';
                                    }
                                @endphp
                                <td class="h-9 p-0.5 relative group">
                                    @if($cell['is_current'])
                                        @if($canManage)
                                        <button type="button" 
                                                @click="openAdd('{{ $cell['date_string'] }}')"
                                                title="{{ $tooltipTitle }}"
                                                class="w-full h-full rounded-md flex items-center justify-center transition font-bold text-xs {{ $cell['bg_class'] }} hover:ring-2 hover:ring-blue-500 hover:scale-105 shadow-2xs">
                                            {{ $cell['day'] }}
                                        </button>
                                        @else
                                        <div title="{{ $tooltipTitle }}"
                                             class="w-full h-full rounded-md flex items-center justify-center font-bold text-xs {{ $cell['bg_class'] }} shadow-2xs select-none">
                                            {{ $cell['day'] }}
                                        </div>
                                        @endif
                                    @else
                                    <span class="block w-full h-full text-slate-200 py-2 text-[10px]">&bull;</span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer mini status bulan -->
                <div class="px-3 py-1.5 bg-slate-50 border-t border-slate-200 text-[10px] text-slate-500 flex items-center justify-between">
                    <span>TA {{ $kalender->tahun_ajaran }}</span>
                    <span class="font-semibold text-slate-700">{{ count(array_filter($monthData['weeks'], fn($w) => !empty(array_filter($w, fn($c) => $c['is_current'])))) }} Minggu</span>
                </div>

            </div>
            @endforeach
        </div>
    </div>

    @if($semester === '2')
    <!-- TABEL PERKIRAAN JADWAL PENILAIAN AKHIR JENJANG (SESUAI GAMBAR RESMI USER) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center gap-2">
            <div class="p-2 bg-amber-100 text-amber-900 rounded-xl">
                <i class="bi-clipboard2-check text-lg"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-slate-900 uppercase tracking-tight">
                    Perkiraan Jadwal Penilaian Akhir Jenjang Tahun Ajaran {{ $kalender->tahun_ajaran }}
                </h3>
                <p class="text-xs text-slate-500">Acuan jadwal asesmen sumatif akhir jenjang dan uji kompetensi keahlian</p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-xs">
                <thead class="bg-emerald-100 text-emerald-950 font-black border-b border-slate-200 uppercase">
                    <tr>
                        <th class="py-3 px-4 w-3/12">Satuan Pendidikan</th>
                        <th class="py-3 px-4 w-5/12">Asesmen Sumatif Akhir Jenjang (ASAJ)</th>
                        <th class="py-3 px-4 w-4/12">Tes / Uji Kompetensi Keahlian (UKK)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-semibold text-slate-800">
                    <tr class="bg-blue-50/50 hover:bg-blue-100/50 transition">
                        <td class="py-3 px-4 font-black text-blue-900 flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-600"></span>
                            <span>SMK (Sekolah Menengah Kejuruan)</span>
                        </td>
                        <td class="py-3 px-4">22 Maret &ndash; 2 April 2027 (Asesmen Teori & Praktik)</td>
                        <td class="py-3 px-4 font-bold text-purple-900">29 Maret &ndash; 30 April 2027 (Pelaksanaan UKK bersama DUDI & LSP)</td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-bold text-slate-700">SMA / MA</td>
                        <td class="py-3 px-4">22 Maret &ndash; 2 April 2027</td>
                        <td class="py-3 px-4 text-slate-500">25 Oktober &ndash; 5 November 2026 (TKA)</td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-bold text-slate-700">SMP / MTs</td>
                        <td class="py-3 px-4">26 April &ndash; 7 Mei 2027</td>
                        <td class="py-3 px-4 text-slate-500">5 &ndash; 16 April 2027 (TKA)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- TABEL KETERANGAN TANGGAL & AGENDA KEGIATAN -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2">
                <div class="p-2 bg-emerald-100 text-emerald-900 rounded-xl">
                    <i class="bi-card-checklist text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-900 uppercase tracking-tight">
                        Tabel Keterangan Tanggal & Kegiatan (Semester {{ $semester }})
                    </h3>
                    <p class="text-xs text-slate-500">Rincian hari libur resmi, kegiatan sekolah, asesmen sumatif, dan penyerahan rapor</p>
                </div>
            </div>

            <div class="text-xs text-slate-500 font-semibold">
                Total Agenda: <strong class="text-slate-900 font-black">{{ $events->count() }}</strong> kegiatan tercatat
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-xs">
                <thead class="bg-emerald-100 text-emerald-950 font-black border-b border-slate-200 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4 w-3/12">Tanggal</th>
                        <th class="py-3 px-4 w-5/12">Kegiatan / Hari Libur</th>
                        <th class="py-3 px-4 w-2/12 text-center">Kategori & Status</th>
                        @if(auth()->user()->canManageAcademic())
                        <th class="py-3 px-4 w-2/12 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                    @forelse($events as $ev)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 whitespace-nowrap font-bold text-slate-900">
                            {{ $ev->formatted_tanggal }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-extrabold text-slate-900 text-sm leading-snug">
                                {{ $ev->judul_kegiatan }}
                            </div>
                            @if($ev->keterangan)
                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $ev->keterangan }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $ev->badge_class }}">
                                {{ $ev->kategori_label }}
                            </span>
                            @if($ev->is_libur)
                            <div class="text-[10px] text-rose-600 font-bold mt-1">
                                <i class="bi-x-circle-fill"></i> Tidak Efektif
                            </div>
                            @else
                            <div class="text-[10px] text-emerald-700 font-bold mt-1">
                                <i class="bi-check-circle-fill"></i> KBM / Kegiatan
                            </div>
                            @endif
                        </td>
                        @if(auth()->user()->canManageAcademic())
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" 
                                        @click="openEdit({
                                            id: {{ $ev->id }},
                                            judul_kegiatan: '{{ addslashes($ev->judul_kegiatan) }}',
                                            tanggal_mulai: '{{ Carbon\Carbon::parse($ev->tanggal_mulai)->format('Y-m-d') }}',
                                            tanggal_selesai: '{{ Carbon\Carbon::parse($ev->tanggal_selesai)->format('Y-m-d') }}',
                                            semester: '{{ $ev->semester }}',
                                            kategori: '{{ $ev->kategori }}',
                                            warna_bg: '{{ $ev->warna_bg }}',
                                            is_libur: {{ $ev->is_libur ? 1 : 0 }},
                                            keterangan: '{{ addslashes($ev->keterangan ?? '') }}'
                                        })"
                                        class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-xl transition cursor-pointer" title="Edit Agenda">
                                    <i class="bi-pencil-square text-base"></i>
                                </button>

                                <form action="{{ route('admin.kalender.event.destroy', $ev) }}" method="POST" onsubmit="return confirm('Hapus agenda {{ addslashes($ev->judul_kegiatan) }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition cursor-pointer" title="Hapus Agenda">
                                        <i class="bi-trash text-base"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center text-slate-400">
                            <i class="bi-calendar2-x text-3xl mb-2 inline-block"></i>
                            <p class="text-xs font-semibold">Belum ada agenda kegiatan atau hari libur yang dicatat untuk Semester {{ $semester }}.</p>
                            @if(auth()->user()->canManageAcademic())
                            <button type="button" @click="openAdd()" class="mt-3 px-4 py-2 bg-blue-600 text-white font-bold rounded-xl text-xs">
                                + Tambah Agenda Pertama
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- PANEL INTEGRASI REKOMENDASI MINGGU EFEKTIF -->
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30">
                    <i class="bi-cpu-fill"></i> Algoritma Perhitungan Efektif Otomatis
                </span>
                <h3 class="text-lg sm:text-xl font-extrabold mt-1">
                    Kalkulasi Minggu Efektif Semester {{ $semester }} dari Kalender Akademik
                </h3>
                <p class="text-xs text-slate-300 mt-1 max-w-2xl">
                    Data hari libur dan ujian di atas otomatis dibaca oleh modul Minggu Efektif Guru untuk menghitung jam tatap muka KBM.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('guru.minggu-efektif.index', ['tahun_ajaran' => $kalender->tahun_ajaran, 'semester' => ($semester === '1' ? 'ganjil' : 'genap')]) }}"
                   class="px-5 py-3 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black rounded-2xl text-xs flex items-center gap-2 shadow-lg transition">
                    <i class="bi-journal-check text-base"></i>
                    <span>Buka Modul Minggu Efektif</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach($efektifPerBulan as $bln)
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 border border-white/10 text-center space-y-1">
                <div class="text-[11px] font-black uppercase text-amber-300 tracking-wider">{{ $bln['bulan'] }}</div>
                <div class="text-xl font-black text-white">{{ $bln['efektif'] }} <span class="text-[10px] font-normal text-slate-300">ME</span></div>
                <div class="text-[10px] text-slate-300">Total: {{ $bln['total_minggu'] }} Mng ({{ $bln['tidak_efektif'] }} Tdk)</div>
                <div class="text-[9px] text-slate-400 truncate pt-1 border-t border-white/10" title="{{ $bln['keterangan'] }}">
                    {{ $bln['keterangan'] }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @if($canManage)
    <!-- MODAL TAMBAH AGENDA / HARI LIBUR -->
    <div x-show="eventModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="eventModal = false" class="bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in duration-200 border border-slate-200">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-700 to-indigo-800 text-white flex items-center justify-between">
                <h3 class="font-bold text-base flex items-center gap-2">
                    <i class="bi-calendar-plus-fill"></i> Tambah Agenda / Hari Libur
                </h3>
                <button type="button" @click="eventModal = false" class="text-white/70 hover:text-white text-xl cursor-pointer">
                    <i class="bi-x-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.kalender.event.store', $kalender) }}" method="POST" class="p-6 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Judul Kegiatan / Hari Libur <span class="text-rose-500">*</span></label>
                    <input type="text" name="judul_kegiatan" x-model="eventForm.judul_kegiatan" required placeholder="Contoh: Penilaian Akhir Semester (PAS) Gasal"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_mulai" x-model="eventForm.tanggal_mulai" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Selesai (Opsional)</label>
                        <input type="date" name="tanggal_selesai" x-model="eventForm.tanggal_selesai"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs font-bold">
                        <span class="text-[10px] text-slate-400">Kosongkan jika hanya 1 hari</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Semester <span class="text-rose-500">*</span></label>
                        <select name="semester" x-model="eventForm.semester" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold">
                            <option value="1">Semester 1 (Ganjil)</option>
                            <option value="2">Semester 2 (Genap)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Kategori Agenda <span class="text-rose-500">*</span></label>
                        <select name="kategori" x-model="eventForm.kategori" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                            <option value="libur_nasional">Libur Nasional / Keagamaan</option>
                            <option value="libur_sekolah">Libur Khusus Sekolah</option>
                            <option value="libur_semester">Libur Akhir Semester</option>
                            <option value="kegiatan_sekolah">Kegiatan Sekolah (MPLS / Pramuka)</option>
                            <option value="ujian_asesmen">Asesmen / Ujian / ASAJ / ASAT</option>
                            <option value="pembagian_rapor">Penetapan / Pembagian Rapor</option>
                            <option value="hari_efektif_khusus">KBM Efektif Khusus</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Warna Penanda Tanggal <span class="text-rose-500">*</span></label>
                        <select name="warna_bg" x-model="eventForm.warna_bg" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                            <option value="red">Merah (Hari Libur / Tanggal Merah)</option>
                            <option value="yellow">Kuning / Oranye (Kegiatan Khusus)</option>
                            <option value="blue">Biru (Asesmen / Ujian)</option>
                            <option value="purple">Ungu (Uji Kompetensi Keahlian)</option>
                            <option value="cyan">Cyan (Pembagian Rapor)</option>
                            <option value="green">Hijau (Hari Efektif Belajar)</option>
                        </select>
                    </div>
                    <div class="flex items-center pt-6">
                        <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                            <input type="checkbox" name="is_libur" value="1" x-model="eventForm.is_libur" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                            <span>Tandai sebagai Hari Libur / Tidak Efektif</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan / Keterangan Tambahan</label>
                    <textarea name="keterangan" x-model="eventForm.keterangan" rows="2" placeholder="Informasi tambahan untuk dewan guru dan siswa..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="eventModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-sm">Simpan Agenda</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT AGENDA -->
    <div x-show="editEventModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="editEventModal = false" class="bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in duration-200 border border-slate-200">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-600 to-amber-700 text-white flex items-center justify-between">
                <h3 class="font-bold text-base flex items-center gap-2">
                    <i class="bi-pencil-square"></i> Edit Agenda / Hari Libur
                </h3>
                <button type="button" @click="editEventModal = false" class="text-white/70 hover:text-white text-xl cursor-pointer">
                    <i class="bi-x-lg"></i>
                </button>
            </div>

            <form :action="'{{ url(request()->segment(1) . '/kalender/event') }}/' + eventForm.id" method="POST" class="p-6 space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Judul Kegiatan / Hari Libur <span class="text-rose-500">*</span></label>
                    <input type="text" name="judul_kegiatan" x-model="eventForm.judul_kegiatan" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500 font-semibold text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_mulai" x-model="eventForm.tanggal_mulai" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500 text-xs font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" x-model="eventForm.tanggal_selesai" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500 text-xs font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Semester <span class="text-rose-500">*</span></label>
                        <select name="semester" x-model="eventForm.semester" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500 font-bold">
                            <option value="1">Semester 1 (Ganjil)</option>
                            <option value="2">Semester 2 (Genap)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Kategori Agenda <span class="text-rose-500">*</span></label>
                        <select name="kategori" x-model="eventForm.kategori" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500 font-semibold">
                            <option value="libur_nasional">Libur Nasional / Keagamaan</option>
                            <option value="libur_sekolah">Libur Khusus Sekolah</option>
                            <option value="libur_semester">Libur Akhir Semester</option>
                            <option value="kegiatan_sekolah">Kegiatan Sekolah (MPLS / Pramuka)</option>
                            <option value="ujian_asesmen">Asesmen / Ujian / ASAJ / ASAT</option>
                            <option value="pembagian_rapor">Penetapan / Pembagian Rapor</option>
                            <option value="hari_efektif_khusus">KBM Efektif Khusus</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Warna Penanda Tanggal <span class="text-rose-500">*</span></label>
                        <select name="warna_bg" x-model="eventForm.warna_bg" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500 font-semibold">
                            <option value="red">Merah (Hari Libur / Tanggal Merah)</option>
                            <option value="yellow">Kuning / Oranye (Kegiatan Khusus)</option>
                            <option value="blue">Biru (Asesmen / Ujian)</option>
                            <option value="purple">Ungu (Uji Kompetensi Keahlian)</option>
                            <option value="cyan">Cyan (Pembagian Rapor)</option>
                            <option value="green">Hijau (Hari Efektif Belajar)</option>
                        </select>
                    </div>
                    <div class="flex items-center pt-6">
                        <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                            <input type="checkbox" name="is_libur" value="1" x-model="eventForm.is_libur" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                            <span>Tandai sebagai Hari Libur / Tidak Efektif</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan Tambahan</label>
                    <textarea name="keterangan" x-model="eventForm.keterangan" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="editEventModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL SETTING RENTANG TAHUN AKADEMIK -->
    <div x-show="settingModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="settingModal = false" class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden animate-in fade-in duration-200 border border-slate-200">
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                <h3 class="font-bold text-base flex items-center gap-2">
                    <i class="bi-calendar-range"></i> Atur Rentang Tahun Ajaran
                </h3>
                <button type="button" @click="settingModal = false" class="text-white/70 hover:text-white text-xl cursor-pointer">
                    <i class="bi-x-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.kalender.update', $kalender) }}" method="POST" class="p-6 space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Kalender</label>
                    <input type="text" name="nama_kalender" value="{{ $kalender->nama_kalender }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Mulai TA</label>
                        <input type="date" name="tanggal_mulai" value="{{ Carbon\Carbon::parse($kalender->tanggal_mulai)->format('Y-m-d') }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Berakhir TA</label>
                        <input type="date" name="tanggal_selesai" value="{{ Carbon\Carbon::parse($kalender->tanggal_selesai)->format('Y-m-d') }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Deskripsi Kalender</label>
                    <textarea name="deskripsi" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $kalender->deskripsi }}</textarea>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="settingModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-sm">Perbarui Kalender</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- MODAL TAMBAH TAHUN AJARAN BARU (BERKELANJUTAN / UNTUK SELAMANYA) -->
    @if($canManage)
    <div x-show="createYearModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="createYearModal = false" class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden animate-in fade-in duration-200 border border-slate-200">
            <div class="px-6 py-4 bg-emerald-700 text-white flex items-center justify-between">
                <h3 class="font-bold text-base flex items-center gap-2">
                    <i class="bi-plus-circle-fill"></i> Tambah Tahun Ajaran Baru
                </h3>
                <button type="button" @click="createYearModal = false" class="text-white/70 hover:text-white text-xl cursor-pointer">
                    <i class="bi-x-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.kalender.store') }}" method="POST" class="p-6 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Tahun Ajaran Baru</label>
                    <input type="text" name="tahun_ajaran" placeholder="Contoh: 2027/2028" required 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-900">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Format: YYYY/YYYY (misal: 2027/2028, 2028/2029)</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Kalender</label>
                    <input type="text" name="nama_kalender" placeholder="Kalender Pendidikan SMK Plus Al-Hilal ..." required 
                           value="Kalender Pendidikan & Akademik SMK Plus Al-Hilal"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-semibold">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Mulai TA</label>
                        <input type="date" name="tanggal_mulai" required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-800">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Default: 1 Juli</span>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Tanggal Berakhir TA</label>
                        <input type="date" name="tanggal_selesai" required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-slate-800">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Default: 30 Juni</span>
                    </div>
                </div>

                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl text-[11px] text-emerald-900 space-y-1">
                    <div class="font-bold flex items-center gap-1">
                        <i class="bi-magic text-emerald-600"></i> Otomatisasi Terpasang:
                    </div>
                    <ul class="list-disc list-inside text-slate-600 space-y-0.5 text-[10.5px]">
                        <li>Hari libur nasional & keagamaan resmi disinkronkan otomatis.</li>
                        <li>Agenda Disdik Jabar (MPLS, ASAS, UKK, ASAJ, Rapor) dimuat otomatis.</li>
                        <li>Semester 1 & 2 terbagi secara mandiri tanpa tercampur tahun lain.</li>
                    </ul>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Deskripsi Tambahan (Opsional)</label>
                    <textarea name="deskripsi" rows="2" placeholder="Catatan kalender akademik ini..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="createYearModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-sm flex items-center gap-1.5">
                        <i class="bi-check2-circle"></i>
                        <span>Buat Kalender Baru</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- ========================================================================= -->
    <!-- MODAL IMPORT BANYAK KEGIATAN KE KALENDER AKADEMIK -->
    <!-- ========================================================================= -->
    <div x-show="importModal" style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs overflow-y-auto">
        <div @click.away="importModal = false" class="bg-white rounded-3xl max-w-2xl w-full border border-slate-200 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in-95 duration-200">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-700 via-indigo-800 to-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center text-xl">
                        <i class="bi-file-earmark-arrow-up-fill text-indigo-300"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-base tracking-tight">Import Banyak Kegiatan ke Kalender</h3>
                        <p class="text-[11px] text-indigo-200">Tahun Ajaran {{ $kalender->tahun_ajaran }} &bull; Semester Ganjil & Genap</p>
                    </div>
                </div>
                <button type="button" @click="importModal = false" class="text-white/70 hover:text-white text-xl cursor-pointer">
                    <i class="bi-x-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.kalender.import-events', $kalender) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5 text-xs">
                @csrf

                <!-- Unduh Template Banner -->
                <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="space-y-0.5">
                        <div class="font-extrabold text-indigo-950 flex items-center gap-1.5">
                            <i class="bi-file-earmark-spreadsheet-fill text-indigo-600 text-base"></i>
                            <span>Template Format Standar Import</span>
                        </div>
                        <p class="text-[11px] text-indigo-800 leading-relaxed">
                            Unduh berkas format contoh CSV siap pakai yang dapat diedit langsung di Microsoft Excel atau Google Sheets.
                        </p>
                    </div>
                    <a href="{{ route('admin.kalender.template-csv') }}" 
                       class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs flex-shrink-0 flex items-center gap-1.5 shadow-sm transition">
                        <i class="bi-download"></i>
                        <span>Unduh Format CSV</span>
                    </a>
                </div>

                <!-- Tab Pemilihan Metode Input (Unggah File / Salin-Tempel Teks) -->
                <div class="flex items-center p-1 bg-slate-100 rounded-2xl border border-slate-200">
                    <button type="button" @click="importTab = 'file'" 
                            :class="importTab === 'file' ? 'bg-white text-slate-900 font-black shadow-xs' : 'text-slate-500 hover:text-slate-800 font-semibold'"
                            class="flex-1 py-2 rounded-xl text-xs transition flex items-center justify-center gap-2">
                        <i class="bi-cloud-arrow-up text-sm"></i>
                        <span>Unggah File (Excel / CSV)</span>
                    </button>
                    <button type="button" @click="importTab = 'paste'" 
                            :class="importTab === 'paste' ? 'bg-white text-slate-900 font-black shadow-xs' : 'text-slate-500 hover:text-slate-800 font-semibold'"
                            class="flex-1 py-2 rounded-xl text-xs transition flex items-center justify-center gap-2">
                        <i class="bi-clipboard-check text-sm"></i>
                        <span>Salin-Tempel Teks (Batch Paste)</span>
                    </button>
                </div>

                <!-- Mode 1: Unggah Berkas -->
                <div x-show="importTab === 'file'" class="space-y-3">
                    <label class="block font-bold text-slate-700 uppercase tracking-wider">
                        Pilih Berkas Spreadsheet (.csv / .xlsx) <span class="text-rose-500">*</span>
                    </label>
                    <div class="border-2 border-dashed border-slate-200 hover:border-indigo-400 bg-slate-50/50 rounded-2xl p-6 text-center transition">
                        <i class="bi-file-earmark-excel text-3xl text-indigo-500 mb-2 inline-block"></i>
                        <input type="file" name="file_import" accept=".csv,.xlsx,.xls,.txt" 
                               class="w-full text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                        <p class="text-[11px] text-slate-400 mt-2">Dukungan: Berkas .csv atau .xlsx (Maksimal 5MB)</p>
                    </div>
                </div>

                <!-- Mode 2: Salin-Tempel Teks Batch -->
                <div x-show="importTab === 'paste'" class="space-y-3" style="display: none;">
                    <label class="block font-bold text-slate-700 uppercase tracking-wider">
                        Tempelkan Baris Kegiatan dari Excel / Spreadsheet
                    </label>
                    <textarea name="batch_text" rows="7" 
                              placeholder="2026-07-13,2026-07-15,Masa Pengenalan Lingkungan Sekolah (MPLS),kegiatan_sekolah,1,0,Pengenalan siswa baru&#10;2026-08-17,2026-08-17,HUT Kemerdekaan RI ke-81,libur_nasional,1,1,Libur Nasional&#10;2026-10-14,2026-10-15,Kunjungan Industri Kelas XI,kegiatan_sekolah,1,0,Agenda Waka Hubin"
                              class="w-full px-3.5 py-3 rounded-2xl border border-slate-300 font-mono text-[11px] focus:outline-none focus:ring-2 focus:ring-indigo-500 leading-relaxed bg-slate-50"></textarea>
                    <p class="text-[10.5px] text-slate-500">
                        Anda dapat menyalin (copy) beberapa baris tabel langsung dari Excel/Google Sheets dan menempelkannya di sini. Pemisah koma, titik koma, atau tab akan dikenali otomatis.
                    </p>
                </div>

                <!-- Panduan Susunan Kolom Data -->
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl space-y-1 text-[11px] text-slate-600">
                    <div class="font-extrabold text-slate-800 flex items-center gap-1">
                        <i class="bi-info-circle-fill text-blue-600"></i>
                        <span>Urutan 7 Kolom Standar:</span>
                    </div>
                    <ol class="list-decimal list-inside space-y-0.5 text-[10.5px] text-slate-600 pl-1 font-mono">
                        <li><strong>tanggal_mulai</strong>: YYYY-MM-DD (misal: 2026-08-17 atau 17/08/2026)</li>
                        <li><strong>tanggal_selesai</strong>: YYYY-MM-DD (opsional, jika kosong sama dengan mulai)</li>
                        <li><strong>judul_kegiatan</strong>: Nama agenda / kegiatan</li>
                        <li><strong>kategori</strong>: kegiatan_sekolah / libur_nasional / libur_sekolah / libur_semester / ujian_asesmen / pembagian_rapor / hari_efektif_khusus</li>
                        <li><strong>semester</strong>: 1 atau 2 (otomatis terdeteksi dari tanggal jika dikosongkan)</li>
                        <li><strong>is_libur</strong>: 0 (efektif KBM) atau 1 (libur sekolah)</li>
                        <li><strong>keterangan</strong>: Catatan atau deskripsi kegiatan (opsional)</li>
                    </ol>
                </div>

                <!-- Footer Aksi Modal -->
                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" @click="importModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md shadow-indigo-600/20 flex items-center gap-2 transition">
                        <i class="bi-cloud-arrow-up-fill text-base"></i>
                        <span>Mulai Import Kegiatan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
