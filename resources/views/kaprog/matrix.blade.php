@extends('layouts.app')

@section('title', 'Matriks Jadwal Pelajaran Jurusan ' . $jurusanKode)

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-900 via-teal-950 to-slate-950 p-6 sm:p-8 text-white shadow-xl border border-emerald-800/40">
        <div class="absolute inset-0 bg-cover bg-center opacity-15 mix-blend-luminosity pointer-events-none" style="background-image: url('{{ asset('images/gedung-sekolah-lapangan.jpg') }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-950/95 via-teal-950/85 to-slate-950/90 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold backdrop-blur-sm border border-emerald-500/30">
                    <i class="bi bi-mortarboard-fill"></i>
                    <span>Tugas Kepala Program Keahlian (Kaprog)</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Matriks Jadwal Jurusan <span class="text-amber-400 font-black">{{ $jurusanKode }}</span> 📋
                </h1>
                <p class="text-slate-300 text-sm max-w-2xl">
                    {{ $jurusan?->nama ?? ('Program Keahlian ' . $jurusanKode) }}
                    &bull; Kaprog: <strong class="text-white">{{ $jurusan?->ketua_jurusan ?? auth()->user()->name }}</strong>
                    &bull; Meliputi Semua Kelas: 
                    @foreach($matrixData['kelasList'] as $k)
                    <span class="px-2 py-0.5 rounded bg-white/20 text-white font-bold text-xs">{{ $k['nama'] }}</span>
                    @endforeach
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                <a href="{{ route('kaprog.jadwal.matrix.print', ['jurusan' => $jurusanKode]) }}" target="_blank"
                   class="px-5 py-3 bg-white/10 hover:bg-white/20 text-white font-extrabold text-xs rounded-2xl border border-white/20 shadow-md transition flex items-center gap-2">
                    <i class="bi bi-printer-fill text-base text-amber-300"></i>
                    <span>Cetak Matriks Jurusan</span>
                </a>
                <a href="{{ route('kaprog.dashboard') }}" 
                   class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-2xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    <span>Dashboard Kaprog</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Pilihan Jurusan (Jika Superadmin / Mengampu Multi Jurusan) -->
    @if(auth()->user()->isSuperAdmin() || (isset($semuaJurusan) && $semuaJurusan->count() > 1))
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-500">Pilih Jurusan:</span>
            @foreach($semuaJurusan as $j)
                @php $jKode = $j->singkatan ?: $j->kode; @endphp
                <a href="{{ route('kaprog.jadwal.matrix', ['jurusan' => $jKode]) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-black transition {{ strtoupper($jurusanKode) === strtoupper($jKode) ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    {{ $jKode }} - {{ $j->nama }}
                </a>
            @endforeach
        </div>
        <span class="text-[11px] text-slate-400 font-medium">
            * Menampilkan 3 tingkatan kelas (X, XI, XII) untuk jurusan yang dipilih
        </span>
    </div>
    @endif

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 bg-white rounded-3xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rombel Jurusan</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ count($matrixData['kelasList']) }}</div>
            <div class="text-[10px] text-emerald-700 font-semibold">Tingkat X, XI, XII</div>
        </div>
        <div class="p-4 bg-white rounded-3xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Slot Terisi KBM</div>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $matrixData['filledSlots'] ?? 0 }}</div>
            <div class="text-[10px] text-blue-700 font-semibold">Total Jam Terjadwal</div>
        </div>
        <div class="p-4 bg-white rounded-3xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hari Efektif KBM</div>
            <div class="text-2xl font-black text-purple-600 mt-1">6</div>
            <div class="text-[10px] text-purple-700 font-semibold">Senin s/d Sabtu (Full)</div>
        </div>
        <div class="p-4 bg-white rounded-3xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Bentrok</div>
            <div class="text-2xl font-black {{ ($matrixData['conflicts']['total'] ?? 0) > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                {{ $matrixData['conflicts']['total'] ?? 0 }}
            </div>
            <div class="text-[10px] {{ ($matrixData['conflicts']['total'] ?? 0) > 0 ? 'text-rose-700' : 'text-emerald-700' }} font-semibold">
                {{ ($matrixData['conflicts']['total'] ?? 0) > 0 ? 'Perlu Disesuaikan' : 'Jadwal Bersih' }}
            </div>
        </div>
    </div>

    <!-- Banner Bentrok jika ada -->
    @if(!empty($matrixData['conflicts']) && $matrixData['conflicts']['total'] > 0)
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-3xl shadow-xs text-xs text-rose-900 space-y-2">
        <div class="flex items-center gap-2 font-black text-rose-800">
            <i class="bi bi-exclamation-triangle-fill text-rose-600 text-base"></i>
            <span>Pemberitahuan Bentrok Mengajar di Jurusan Ini ({{ $matrixData['conflicts']['total'] }} Kasus)</span>
        </div>
        <ul class="list-disc list-inside space-y-1 text-slate-700 font-medium">
            @foreach($matrixData['conflicts']['guru'] as $bg)
            <li>
                <strong>{{ $bg['guru_nama'] }}</strong>: Hari <strong>{{ $bg['hari'] }}</strong> Jam ke-<strong>{{ $bg['jam_ke'] }}</strong> ({{ $bg['waktu'] }}) di kelas: <span class="text-rose-700 font-bold">{{ implode(', ', $bg['classes']) }}</span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- SHEET PREVIEW CONTAINER -->
    <div class="bg-white rounded-3xl p-4 sm:p-8 border border-slate-200 shadow-sm overflow-x-auto">
        <div class="min-w-[1000px] bg-white text-slate-900 text-[11px] leading-tight font-sans">

            <!-- KOP JUDUL RESMI -->
            <div class="flex items-center justify-between gap-4 mb-6 pt-2 border-b border-slate-200 pb-4">
                <div class="w-16 h-16 flex-shrink-0 flex items-center justify-center">
                    <img src="{{ asset($settings['logo_sekolah'] ?? 'images/logo.png') }}" class="w-14 h-14 object-contain" alt="Logo Sekolah" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                </div>
                <div class="text-center flex-1 space-y-1">
                    <h2 class="text-xl sm:text-2xl font-black tracking-wider uppercase text-slate-950">MATRIKS JADWAL PELAJARAN JURUSAN {{ strtoupper($jurusanKode) }}</h2>
                    <h3 class="text-sm sm:text-base font-black uppercase text-slate-900">TAHUN PELAJARAN {{ $settings['tahun_pelajaran'] ?? '2025 – 2026' }}</h3>
                    <p class="text-xs font-semibold text-slate-800">
                        {{ $jurusan?->nama ?? ('Program Keahlian ' . $jurusanKode) }} &bull; SMK Plus Al-Hilal Arjawinangun
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

            <!-- LEGENDA GURU RESMI & KODE MENGAJAR -->
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
                                <th class="border border-slate-900 px-2 py-1 text-center w-[44%]">MATA PELAJARAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($chunk as $kode => $g)
                            <tr class="hover:bg-slate-50">
                                <td class="border border-slate-900 px-2 py-1 font-semibold text-slate-900">
                                    {{ $g['nama'] }}
                                </td>
                                <td class="border border-slate-900 px-1 py-1 text-center font-bold text-slate-900 bg-slate-100">
                                    {{ $kode }}
                                </td>
                                <td class="border border-slate-900 px-2 py-1 text-slate-700 font-medium">
                                    {{ $g['mapel'] ?: '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endforeach
                </div>
            </div>

            <!-- TANDA TANGAN KAPROG & KEPALA SEKOLAH -->
            <div class="mt-10 grid grid-cols-2 text-center text-xs font-semibold text-slate-800">
                <div>
                    Mengetahui,<br>
                    <strong>Kepala SMK Plus Al-Hilal</strong><br><br><br><br><br>
                    <strong><u>{{ $settings['kepala_sekolah'] ?? 'Mukhammad Mansyur, S.Pt' }}</u></strong>
                </div>
                <div>
                    Arjawinangun, {{ date('d F Y') }}<br>
                    <strong>Ketua Program Keahlian {{ $jurusanKode }}</strong><br><br><br><br><br>
                    <strong><u>{{ $jurusan?->ketua_jurusan ?? auth()->user()->name }}</u></strong>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
