@extends('layouts.app')

@section('title', 'Matriks Jadwal Pelajaran Resmi')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200 mb-2">
                <i class="bi-file-earmark-spreadsheet-fill text-amber-600"></i>
                <span>Format Resmi Lembar Jadwal Sekolah (100% Terhubung Database)</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                Matriks Jadwal Pelajaran (Semua Jurusan & Rombel)
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Visualisasi jadwal KBM lengkap 6 hari (Senin - Sabtu), 9 rombel kelas, dengan slot kosong (-) untuk jam yang belum terinput di database.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            @if(auth()->user() && auth()->user()->canManageAcademic())
            <a href="{{ route('admin.jadwal.index') }}" 
               class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition inline-flex items-center gap-2 border border-slate-200">
                <i class="bi-table"></i>
                <span>Kelola Jadwal (Tabel)</span>
            </a>

            <a href="{{ route('admin.kurikulum.index') }}" 
               class="px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold rounded-xl text-xs transition inline-flex items-center gap-2 border border-emerald-200">
                <i class="bi-journal-bookmark-fill text-emerald-600"></i>
                <span>Data Kurikulum</span>
            </a>
            @endif

            <a href="{{ route('admin.jadwal.matrix.print', request()->query()) }}" target="_blank"
               class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs transition inline-flex items-center gap-2 shadow-lg shadow-rose-600/20" title="Cetak Matriks sesuai filter aktif">
                <i class="bi-printer-fill"></i>
                <span>Cetak Dokumen Resmi (Landscape)</span>
            </a>
        </div>
    </div>

    <!-- FILTER BAR MATRIKS (JENJANG & JURUSAN) -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="font-extrabold text-slate-800 flex items-center gap-1.5 mr-2">
                <i class="bi-funnel-fill text-amber-500"></i>
                <span>Filter Tampilan:</span>
            </span>

            <!-- Filter Cepat Jenjang X (3 Jurusan) -->
            <a href="{{ route('admin.jadwal.matrix', ['jenjang' => 'X']) }}"
               class="px-3 py-1.5 rounded-xl font-black transition {{ ($jenjang ?? 'all') === 'X' && ($jurusan ?? 'all') === 'all' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                <i class="bi-stars text-amber-300 mr-1"></i> Kelas X (3 Jurusan)
            </a>

            <span class="text-slate-300">|</span>

            <!-- Filter Jurusan -->
            <span class="text-slate-500 font-semibold">Jurusan:</span>
            <a href="{{ route('admin.jadwal.matrix', array_merge(request()->query(), ['jurusan' => 'all'])) }}"
               class="px-2.5 py-1 rounded-lg font-bold transition {{ ($jurusan ?? 'all') === 'all' ? 'bg-slate-800 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-600' }}">
                Semua
            </a>
            <a href="{{ route('admin.jadwal.matrix', array_merge(request()->query(), ['jurusan' => 'AKL'])) }}"
               class="px-2.5 py-1 rounded-lg font-bold transition {{ ($jurusan ?? 'all') === 'AKL' ? 'bg-red-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-600' }}">
                AKL (PRB)
            </a>
            <a href="{{ route('admin.jadwal.matrix', array_merge(request()->query(), ['jurusan' => 'TKJT'])) }}"
               class="px-2.5 py-1 rounded-lg font-bold transition {{ ($jurusan ?? 'all') === 'TKJT' ? 'bg-emerald-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-600' }}">
                TKJT (TKJ)
            </a>
            <a href="{{ route('admin.jadwal.matrix', array_merge(request()->query(), ['jurusan' => 'TO'])) }}"
               class="px-2.5 py-1 rounded-lg font-bold transition {{ ($jurusan ?? 'all') === 'TO' ? 'bg-yellow-500 text-slate-950 font-black' : 'bg-slate-100 hover:bg-slate-200 text-slate-600' }}">
                TO (TKR)
            </a>

            <span class="text-slate-300">|</span>

            <!-- Filter Jenjang -->
            <span class="text-slate-500 font-semibold">Jenjang:</span>
            <a href="{{ route('admin.jadwal.matrix', array_merge(request()->query(), ['jenjang' => 'all'])) }}"
               class="px-2.5 py-1 rounded-lg font-bold transition {{ ($jenjang ?? 'all') === 'all' ? 'bg-slate-800 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-600' }}">
                Semua Jenjang
            </a>
            <a href="{{ route('admin.jadwal.matrix', array_merge(request()->query(), ['jenjang' => 'XI'])) }}"
               class="px-2.5 py-1 rounded-lg font-bold transition {{ ($jenjang ?? 'all') === 'XI' ? 'bg-blue-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-600' }}">
                Kelas XI
            </a>
            <a href="{{ route('admin.jadwal.matrix', array_merge(request()->query(), ['jenjang' => 'XII'])) }}"
               class="px-2.5 py-1 rounded-lg font-bold transition {{ ($jenjang ?? 'all') === 'XII' ? 'bg-purple-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-600' }}">
                Kelas XII
            </a>
        </div>

        @if(($jurusan ?? 'all') !== 'all' || ($jenjang ?? 'all') !== 'all' || ($kelas ?? 'all') !== 'all')
        <a href="{{ route('admin.jadwal.matrix') }}" class="text-xs text-rose-600 hover:text-rose-800 font-bold flex items-center gap-1 transition">
            <i class="bi-x-circle"></i> Reset Filter
        </a>
        @endif
    </div>

    <!-- 1. BANNER PERINGATAN BENTROK JADWAL (JIKA ADA) -->
    @if(!empty($matrixData['conflicts']) && $matrixData['conflicts']['total'] > 0)
    <div class="p-5 bg-rose-50 border-2 border-rose-400 rounded-3xl shadow-sm text-xs text-rose-900 space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center font-black text-base shadow-xs animate-bounce">
                    ⚠️
                </span>
                <div>
                    <h3 class="font-black text-sm text-rose-950 uppercase tracking-wide">
                        Peringatan Bentrok Jadwal Mengajar Terdeteksi! ({{ $matrixData['conflicts']['total'] }} Kasus Bentrok)
                    </h3>
                    <p class="text-[11.5px] text-rose-700 font-medium mt-0.5">
                        Ditemukan jadwal mengajar yang saling bertabrakan pada hari dan jam yang sama. Periksa slot berwarna merah di bawah ini:
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 bg-rose-600 text-white text-[11px] font-black rounded-full uppercase">
                Perlu Disesuaikan
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-rose-200">
            @if(!empty($matrixData['conflicts']['guru']))
            <div class="p-3 bg-white/80 rounded-2xl border border-rose-200 space-y-1.5">
                <strong class="text-rose-900 flex items-center gap-1.5 font-black text-xs">
                    <i class="bi-person-x-fill text-rose-600"></i> Bentrok Guru (Mengajar di >1 Kelas Bersamaan):
                </strong>
                <ul class="list-disc list-inside space-y-1 text-slate-700 font-medium max-h-36 overflow-y-auto">
                    @foreach($matrixData['conflicts']['guru'] as $bg)
                    <li>
                        <strong>{{ $bg['guru_nama'] }}</strong>: Hari <strong>{{ $bg['hari'] }}</strong>, Jam ke-<strong>{{ $bg['jam_ke'] }}</strong> ({{ $bg['waktu'] }}) di kelas: <span class="text-rose-700 font-bold">{{ implode(', ', $bg['classes']) }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($matrixData['conflicts']['kelas']))
            <div class="p-3 bg-white/80 rounded-2xl border border-rose-200 space-y-1.5">
                <strong class="text-rose-900 flex items-center gap-1.5 font-black text-xs">
                    <i class="bi-door-open-fill text-rose-600"></i> Bentrok Kelas (Lebih dari 1 Mapel pada Jam yang Sama):
                </strong>
                <ul class="list-disc list-inside space-y-1 text-slate-700 font-medium max-h-36 overflow-y-auto">
                    @foreach($matrixData['conflicts']['kelas'] as $bk)
                    <li>
                        Kelas <strong>{{ $bk['kelas'] }}</strong>: Hari <strong>{{ $bk['hari'] }}</strong> Jam ke-<strong>{{ $bk['jam_ke'] }}</strong> &rarr; <span class="text-rose-700 font-bold">{{ implode(' vs ', $bk['jadwals']) }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- 2. BANNER PERINGATAN KELEBIHAN ALOKASI JAM KBM (JIKA ADA) -->
    @if((!empty($matrixData['overAllocations']) && count($matrixData['overAllocations']) > 0) || (!empty($matrixData['classOverLimits']) && count($matrixData['classOverLimits']) > 0))
    <div class="p-4 bg-amber-50 border-2 border-amber-300 rounded-3xl shadow-sm text-xs text-amber-900 space-y-2.5">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center font-black text-base shadow-xs">
                ⚠️
            </span>
            <div>
                <h3 class="font-black text-sm text-amber-950 uppercase tracking-wide">
                    Peringatan Kelebihan Jam Mengajar (Melebihi Kurikulum)
                </h3>
                <p class="text-[11px] text-amber-700 font-medium">
                    Terdapat mata pelajaran atau kelas yang jumlah jam terjadwalnya melebihi alokasi kurikulum yang telah ditetapkan:
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 border-t border-amber-200">
            @foreach($matrixData['overAllocations'] as $ov)
            <div class="p-2.5 bg-white/90 rounded-xl border border-amber-200 flex items-center justify-between">
                <div>
                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-900 font-bold text-[10px]">{{ $ov['kelas'] }}</span>
                    <strong class="text-slate-900 text-xs ml-1">{{ $ov['mapel_nama'] }}</strong>
                    <span class="text-[10px] text-slate-500 block">Guru: {{ $ov['guru_nama'] }}</span>
                </div>
                <div class="text-right">
                    <span class="px-2 py-1 rounded-lg bg-amber-500 text-white font-black text-xs">
                        +{{ $ov['kelebihan_jp'] }} JP
                    </span>
                    <span class="text-[10px] text-slate-500 block mt-0.5">{{ $ov['terjadwal_jp'] }}/{{ $ov['alokasi_jam'] }} JP</span>
                </div>
            </div>
            @endforeach

            @foreach($matrixData['classOverLimits'] ?? [] as $co)
            <div class="p-2.5 bg-rose-50 rounded-xl border border-rose-200 flex items-center justify-between">
                <div>
                    <span class="px-2 py-0.5 rounded bg-rose-200 text-rose-900 font-bold text-[10px]">{{ $co['kelas'] }}</span>
                    <strong class="text-rose-950 text-xs ml-1">Total Jam Kelas Melebihi Batas Mingguan</strong>
                </div>
                <div class="text-right font-black text-rose-700 text-xs">
                    {{ $co['total_jp'] }} JP (Maks: 46 JP)
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- REKAP STATUS SLOT JADWAL TERISI VS BELUM TERINPUT -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Slot Matriks</p>
                <h4 class="text-2xl font-black text-slate-900 mt-0.5">{{ $matrixData['totalSlots'] ?? 414 }} Slot</h4>
                <p class="text-[11px] text-slate-500">6 Hari &times; 9 Rombel Kelas</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                <i class="bi-grid-3x3"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Slot Terisi (Terinput)</p>
                <h4 class="text-2xl font-black text-emerald-700 mt-0.5">{{ $matrixData['filledSlots'] ?? 0 }} Slot</h4>
                <p class="text-[11px] text-slate-500">Sudah terjadwal guru & mapel</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="bi-check-circle-fill"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Slot Belum Terinput</p>
                <h4 class="text-2xl font-black text-amber-700 mt-0.5">{{ $matrixData['emptySlots'] ?? 0 }} Slot</h4>
                <p class="text-[11px] text-amber-600 font-semibold">Tampil tanda (-) &bull; Klik untuk input</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                <i class="bi-dash-circle-fill"></i>
            </div>
        </div>
    </div>

    <!-- PETUNJUK EDIT CEPAT & INTERAKSI MATRIKS -->
    <div class="px-5 py-3 bg-blue-50 border border-blue-200 rounded-2xl flex items-center justify-between gap-3 text-xs text-blue-900 shadow-xs">
        <div class="flex items-center gap-2.5">
            <span class="w-7 h-7 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-xs flex-shrink-0">
                <i class="bi-pencil-square"></i>
            </span>
            <div>
                <strong class="font-black text-blue-950">Kemudahan Koreksi Jadwal:</strong>
                <span class="text-blue-800 ml-1">Jika ada kesalahan input, Anda cukup <strong>klik langsung pada kotak jadwal yang ingin diubah</strong> untuk membuka formulir edit manual. Klik pada tanda <strong>(-)</strong> untuk input jadwal baru.</span>
            </div>
        </div>
        <a href="{{ route('admin.jadwal.index') }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs flex-shrink-0 transition inline-flex items-center gap-1.5 shadow-xs">
            <i class="bi-list-ul"></i>
            <span>Daftar Tabel</span>
        </a>
    </div>

    <!-- SHEET PREVIEW CONTAINER (SCROLLABLE ON SMALL SCREENS) -->
    <div class="bg-white rounded-3xl p-4 sm:p-8 border border-slate-200 shadow-sm overflow-x-auto">
        <div class="min-w-[1100px] bg-white text-slate-900 text-[11px] leading-tight font-sans">

            <!-- KOP JUDUL RESMI (DENGAN LOGO MASTER SEKOLAH) -->
            <div class="flex items-center justify-between gap-4 mb-8 pt-2 border-b border-slate-200 pb-4">
                <div class="w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <img src="{{ asset($settings['logo_sekolah'] ?? 'images/logo.png') }}" class="w-14 h-14 object-contain" alt="Logo Sekolah" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                </div>
                <div class="text-center flex-1 space-y-1">
                    <h2 class="text-2xl sm:text-3xl font-black tracking-wider uppercase text-slate-950">JADWAL PELAJARAN</h2>
                    <h3 class="text-base sm:text-lg font-black uppercase text-slate-900">TAHUN PELAJARAN {{ $settings['tahun_pelajaran'] ?? '2025 – 2026' }}</h3>
                    <p class="text-xs sm:text-sm font-semibold text-slate-800">
                        Program Keahlian : {{ $settings['program_keahlian'] ?? 'Teknik Jaringan Komputer dan Telekomunikasi, Teknik Otomotif, Akuntansi dan Keuangan Lembaga' }}
                    </p>
                    <p class="text-xs sm:text-sm font-semibold text-slate-800">
                        Konsentrasi Keahlian : {{ $settings['konsentrasi_keahlian'] ?? 'Teknik Komputer dan Jaringan, Teknik Kendaraan Ringan, Layanan Perbankan' }}
                    </p>
                </div>
                <div class="w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <img src="{{ asset($settings['logo_sekolah'] ?? 'images/logo.png') }}" class="w-14 h-14 object-contain" alt="Logo Sekolah" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                </div>
            </div>

            <!-- GRID 6 HARI (2 BARIS x 3 KOLOM) -->
            <div class="space-y-6">

                <!-- BARIS 1: SENIN, SELASA, RABU -->
                <div class="grid grid-cols-3 gap-3">
                    @foreach(['Senin', 'Selasa', 'Rabu'] as $hari)
                    @include('admin.jadwal.partials.day_matrix_card', [
                        'hari' => $hari, 
                        'dataHari' => $matrixData['matrix'][$hari] ?? [],
                        'kelasList' => $matrixData['kelasList']
                    ])
                    @endforeach
                </div>

                <!-- BARIS 2: KAMIS, JUMAT, SABTU -->
                <div class="grid grid-cols-3 gap-3">
                    @foreach(['Kamis', 'Jumat', 'Sabtu'] as $hari)
                    @include('admin.jadwal.partials.day_matrix_card', [
                        'hari' => $hari, 
                        'dataHari' => $matrixData['matrix'][$hari] ?? [],
                        'kelasList' => $matrixData['kelasList']
                    ])
                    @endforeach
                </div>

            </div>

            <!-- LEGENDA GURU RESMI & KODE MENGAJAR (DINAMIS) -->
            <div class="mt-8 border-t-2 border-slate-900 pt-6">
                <div class="grid grid-cols-3 gap-3">
                    @php
                        $totalGuru = count($guruList);
                        $perColumn = ceil($totalGuru / 3);
                        $guruChunks = array_chunk($guruList, $perColumn > 0 ? $perColumn : 1, true);
                    @endphp

                    @foreach($guruChunks as $chunkIndex => $chunk)
                    <table class="w-full border-collapse border border-slate-900 text-[10.5px] self-start">
                        <thead class="bg-white font-black text-center text-slate-950">
                            <tr>
                                <th class="border border-slate-900 px-2 py-1 text-center w-[44%]">NAMA</th>
                                <th class="border border-slate-900 px-1 py-1 text-center w-[12%]">KODE</th>
                                <th class="border border-slate-900 px-1.5 py-1 text-center w-[22%]">MAPEL</th>
                                <th class="border border-slate-900 px-1.5 py-1 text-center w-[22%]">JABATAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($chunk as $kode => $g)
                            @php 
                                $badgeClass = isset($g['color']) ? $g['color'] . ' font-black' : 'bg-white text-slate-900 font-bold';
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="border border-slate-900 px-2 py-1 font-bold text-slate-900 truncate max-w-[130px]" title="{{ $g['nama'] ?? '-' }}">{{ $g['nama'] ?? '-' }}</td>
                                <td class="border border-slate-900 px-1 py-1 text-center {{ $badgeClass }}">{{ $kode }}</td>
                                <td class="border border-slate-900 px-1.5 py-1 text-center font-semibold text-slate-800 truncate max-w-[80px]" title="{{ $g['mapel'] ?? '-' }}">{{ $g['mapel'] ?? '-' }}</td>
                                <td class="border border-slate-900 px-1.5 py-1 font-bold text-slate-900 text-[9.5px] uppercase truncate max-w-[80px]" title="{{ $g['jabatan'] ?? '' }}">{{ $g['jabatan'] ?? '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endforeach
                </div>
            </div>

            <!-- BAGIAN PALING BAWAH: TANDA TANGAN KEPALA SEKOLAH & WAKA KURIKULUM (SESUAI DATA MASTER) -->
            <div class="mt-8 pt-6 border-t-2 border-slate-900/20 grid grid-cols-12 gap-6 items-start">

                <!-- TTD KEPALA SEKOLAH (KIRI) -->
                <div class="col-span-5 text-center text-xs space-y-1">
                    <p class="font-semibold text-slate-700">Mengetahui,</p>
                    <p class="font-black text-slate-900 leading-tight">Kepala {{ $settings['nama_sekolah'] ?? 'SMK Plus Al-Hilal Arjawinangun' }}</p>
                    <div class="h-20 flex items-center justify-center">
                        <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">[ Tanda Tangan & Cap ]</span>
                    </div>
                    <p class="font-black text-slate-900 underline underline-offset-4 text-sm uppercase">{{ $settings['nama_kepala_sekolah'] ?? 'Mukhammad Mansyur, S.Pt' }}</p>
                    @if(!empty($settings['nip_kepala_sekolah']))
                    <p class="text-[11px] font-bold text-slate-700">NIP. {{ $settings['nip_kepala_sekolah'] }}</p>
                    @endif
                </div>

                <!-- CATATAN KHUSUS HARI JUMAT (TENGAH) -->
                <div class="col-span-2 flex flex-col items-center justify-center pt-2">
                    <div class="border border-slate-800 bg-amber-50/70 rounded-xl p-3 text-[10px] leading-snug text-slate-800 text-center w-full shadow-xs">
                        <div class="font-black text-amber-900 mb-1 flex items-center justify-center gap-1">
                            <i class="bi-info-circle-fill text-amber-600"></i>
                            <span>*) Catatan Hari Jumat:</span>
                        </div>
                        <p class="text-[9px] font-semibold text-slate-700">Semua jam dikurangi 15 menit, jam istirahat setelah jam ke-4 (30 menit), dan setelah jam ke-6 pulang.</p>
                    </div>
                </div>

                <!-- TITIMANGSA & TTD WAKA KURIKULUM (KANAN) -->
                <div class="col-span-5 text-center text-xs space-y-1">
                    <div class="text-xs font-bold text-slate-800 mb-1">
                        <div class="text-slate-500 text-[10px] uppercase font-bold tracking-wider">BERLAKU MULAI 1 SEPTEMBER 2026</div>
                        <div class="text-slate-900 font-extrabold">{{ $settings['titimangsa'] ?? 'Arjawinangun, 1 September 2026' }}</div>
                    </div>
                    <p class="font-black text-slate-900 leading-tight">Waka Kurikulum,</p>
                    <div class="h-16 flex items-center justify-center">
                        <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">[ Tanda Tangan ]</span>
                    </div>
                    <p class="font-black text-slate-900 underline underline-offset-4 text-sm uppercase">{{ $settings['nama_waka_kurikulum'] ?? 'Sokid, S.T, M.Kom' }}</p>
                    @if(!empty($settings['nip_waka_kurikulum']))
                    <p class="text-[11px] font-bold text-slate-700">NIP. {{ $settings['nip_waka_kurikulum'] }}</p>
                    @else
                    <p class="text-[11px] font-bold text-slate-700">NIP. -</p>
                    @endif
                </div>

            </div>

        </div>
    </div>

</div>
@endsection
