@extends('layouts.app')

@section('title', 'Monitoring Presensi Harian')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Monitoring Presensi</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau kehadiran siswa per hari, bulan, atau semester.</p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('presensi-harian.index') }}" method="GET" class="flex flex-wrap items-end gap-4" id="filterForm">
            
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jenis Presensi</label>
                <select name="jenis_presensi" id="jenis_presensi" class="w-full text-sm border-slate-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="harian" {{ $jenisPresensi == 'harian' ? 'selected' : '' }}>Absensi Harian</option>
                    <option value="mapel" {{ $jenisPresensi == 'mapel' ? 'selected' : '' }}>Absensi Per Mapel</option>
                </select>
            </div>

            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Periode Waktu</label>
                <select name="periode_waktu" id="periode_waktu" class="w-full text-sm border-slate-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="hari" {{ $periodeWaktu == 'hari' ? 'selected' : '' }}>Per Hari</option>
                    <option value="bulan" {{ $periodeWaktu == 'bulan' ? 'selected' : '' }}>Per Bulan</option>
                    <option value="semester" {{ $periodeWaktu == 'semester' ? 'selected' : '' }}>Per Semester (TA Aktif)</option>
                </select>
            </div>

            <div class="flex-1 min-w-[150px]" id="filter_tanggal_container" style="display: {{ $periodeWaktu == 'hari' ? 'block' : 'none' }}">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pilih Tanggal</label>
                <input type="date" name="tanggal" value="{{ $filterTanggal }}" class="w-full text-sm border-slate-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div class="flex-1 min-w-[200px]" id="filter_bulan_container" style="display: {{ $periodeWaktu == 'bulan' ? 'block' : 'none' }}">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pilih Bulan</label>
                <div class="flex gap-2">
                    <select name="bulan" class="flex-1 text-sm border-slate-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">-- Semua Bulan --</option>
                        @for($i = 1; $i <= 12; $i++)
                        <option value="{{ sprintf('%02d', $i) }}" {{ $filterBulan == sprintf('%02d', $i) ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                        </option>
                        @endfor
                    </select>
                    <select name="tahun" class="w-24 text-sm border-slate-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500">
                        @for($i = date('Y') - 2; $i <= date('Y') + 1; $i++)
                        <option value="{{ $i }}" {{ $filterTahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pilih Kelas</label>
                <select name="kelas_id" class="w-full text-sm border-slate-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- Semua Kelas --</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ $filterKelas == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 min-w-[200px]" id="filter_mapel_container" style="display: {{ $jenisPresensi == 'mapel' ? 'block' : 'none' }}">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pilih Mapel</label>
                <select name="mapel_id" class="w-full text-sm border-slate-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- Semua Mapel --</option>
                    @foreach($mapelList as $m)
                    <option value="{{ $m->id }}" {{ $filterMapel == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-auto">
                <button type="submit" class="w-full md:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition shadow-sm">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Content -->
    @if($filterKelas && $students && $students->isNotEmpty())
        
        <!-- Role Specific Tools (Walikelas Setting Ketua Kelas) -->
        @if($myKelas && $myKelas->id == $filterKelas)
        <div class="bg-blue-50 border border-blue-200 p-5 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-blue-900 flex items-center gap-2">
                    <i class="bi-person-badge-fill"></i> Pengaturan Ketua Kelas
                </h3>
                <p class="text-xs text-blue-700 mt-1">Pilih siswa yang akan menjadi ketua kelas untuk mengelola absensi kelas ini.</p>
            </div>
            <form action="{{ route('presensi-harian.set-ketua-kelas') }}" method="POST" class="flex gap-2 w-full sm:w-auto">
                @csrf
                <input type="hidden" name="kelas_id" value="{{ $filterKelas }}">
                <select name="siswa_id" class="text-sm border-blue-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white min-w-[200px]">
                    <option value="">-- Kosongkan Ketua --</option>
                    @foreach($students as $s)
                    <option value="{{ $s->id }}" {{ $myKelas->ketua_kelas_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition">
                    Simpan
                </button>
            </form>
        </div>
        @endif

        @if($periodeWaktu == 'bulan' || $periodeWaktu == 'semester')
            <!-- Tampilan Rekap Bulanan / Semester -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="font-bold text-slate-800">
                        Rekap Presensi 
                        {{ $jenisPresensi == 'mapel' ? 'Mapel' : 'Harian' }} 
                        @if($periodeWaktu == 'bulan')
                            Bulan {{ $filterBulan ? \Carbon\Carbon::create()->month($filterBulan)->translatedFormat('F') : 'Semua' }} {{ $filterTahun }}
                        @else
                            Semester Aktif
                        @endif
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500">
                                <th class="p-3 font-semibold sticky left-0 bg-slate-50 z-10 border-r border-slate-200">Nama Siswa</th>
                                <th class="p-3 font-semibold text-center border-r border-slate-200">Hadir</th>
                                <th class="p-3 font-semibold text-center border-r border-slate-200">Sakit</th>
                                <th class="p-3 font-semibold text-center border-r border-slate-200">Izin</th>
                                <th class="p-3 font-semibold text-center border-r border-slate-200">Alpa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($students as $s)
                            <tr class="hover:bg-slate-50/50">
                                <td class="p-3 font-medium text-slate-700 sticky left-0 bg-white border-r border-slate-200">
                                    {{ $s->name }}
                                </td>
                                @php
                                    $rekap = isset($presensiData[$s->id]) ? $presensiData[$s->id] : ['Hadir'=>0, 'Sakit'=>0, 'Izin'=>0, 'Alpa'=>0];
                                @endphp
                                <td class="p-3 text-center border-r border-slate-200">
                                    <span class="inline-block px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-md font-semibold">{{ $rekap['Hadir'] ?? 0 }}</span>
                                </td>
                                <td class="p-3 text-center border-r border-slate-200">
                                    <span class="inline-block px-2 py-0.5 bg-purple-100 text-purple-700 rounded-md font-semibold">{{ $rekap['Sakit'] ?? 0 }}</span>
                                </td>
                                <td class="p-3 text-center border-r border-slate-200">
                                    <span class="inline-block px-2 py-0.5 bg-blue-100 text-blue-700 rounded-md font-semibold">{{ $rekap['Izin'] ?? 0 }}</span>
                                </td>
                                <td class="p-3 text-center border-r border-slate-200">
                                    <span class="inline-block px-2 py-0.5 bg-rose-100 text-rose-700 rounded-md font-semibold">{{ $rekap['Alpa'] ?? 0 }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <!-- Tampilan Harian & Input Absen (Jika berhak) -->
            @php
                $isKetua = (auth()->user()->role == 'siswa' && auth()->user()->kelas && auth()->user()->kelas->ketua_kelas_id == auth()->id());
                $isPiket = (auth()->user()->role == 'guru' && str_contains(strtolower(auth()->user()->tugas_tambahan), 'piket'));
                $canInput = ($canEdit && $jenisPresensi == 'harian' && $filterTanggal == date('Y-m-d'));
            @endphp

            @if($canInput)
            <form action="{{ route('presensi-harian.absen-kelas') }}" method="POST">
                @csrf
                <input type="hidden" name="kelas_id" value="{{ $filterKelas }}">
                <input type="hidden" name="tanggal" value="{{ $filterTanggal }}">
                
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-4">
                    <div class="p-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                        <h3 class="font-bold text-slate-800">Form Absensi Kelas ({{ $selectedKelas->nama ?? 'Kelas' }}) - {{ \Carbon\Carbon::parse($filterTanggal)->translatedFormat('d F Y') }}</h3>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm shadow-sm transition">
                            <i class="bi-save"></i> Simpan Absensi
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm whitespace-nowrap">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500">
                                    <th class="p-3 font-semibold w-12 text-center">No</th>
                                    <th class="p-3 font-semibold">Nama Siswa</th>
                                    <th class="p-3 font-semibold w-48 text-center">Kehadiran</th>
                                    <th class="p-3 font-semibold w-64">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($siswasForInput as $idx => $s)
                                    @php
                                        $existing = $existingPresensi[$s->id] ?? null;
                                    @endphp
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="p-3 text-center text-slate-500">{{ $idx + 1 }}</td>
                                        <td class="p-3 font-medium text-slate-700">{{ $s->name }}</td>
                                        <td class="p-3">
                                            <select name="absensi[{{ $s->id }}][status]" class="w-full text-sm border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                                                <option value="">- Belum Diabsen -</option>
                                                <option value="Hadir" {{ ($existing && $existing->status == 'Hadir') ? 'selected' : '' }}>Hadir</option>
                                                <option value="Sakit" {{ ($existing && $existing->status == 'Sakit') ? 'selected' : '' }}>Sakit</option>
                                                <option value="Izin" {{ ($existing && $existing->status == 'Izin') ? 'selected' : '' }}>Izin</option>
                                                <option value="Alpa" {{ ($existing && $existing->status == 'Alpa') ? 'selected' : '' }}>Alpa</option>
                                            </select>
                                        </td>
                                        <td class="p-3">
                                            <input type="text" name="absensi[{{ $s->id }}][keterangan]" value="{{ $existing ? $existing->keterangan : '' }}" placeholder="Opsional" class="w-full text-sm border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
            @endif

            <!-- Tabel Monitoring Harian -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="font-bold text-slate-800">
                        Monitoring Absensi {{ $jenisPresensi == 'mapel' ? 'Mapel' : 'Harian' }} - {{ \Carbon\Carbon::parse($filterTanggal)->translatedFormat('d F Y') }}
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500">
                                <th class="p-3 font-semibold">Nama Siswa</th>
                                <th class="p-3 font-semibold">Status Kehadiran</th>
                                @if($jenisPresensi == 'mapel')
                                <th class="p-3 font-semibold">Mata Pelajaran</th>
                                @endif
                                <th class="p-3 font-semibold">Keterangan</th>
                                <th class="p-3 font-semibold">Waktu / Diinput Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($students as $s)
                                @php
                                    // if Harian, it's a single object
                                    // if Mapel, it's a collection of objects (since a student can have multiple subjects a day)
                                    $dataHarian = isset($presensiData[$s->id]) ? $presensiData[$s->id] : null;
                                @endphp
                                
                                @if($jenisPresensi == 'mapel')
                                    @if($dataHarian && $dataHarian->count() > 0)
                                        @foreach($dataHarian as $idx => $abs)
                                        <tr class="hover:bg-slate-50/50">
                                            @if($idx == 0)
                                            <td class="p-3 font-medium text-slate-700" rowspan="{{ $dataHarian->count() }}">{{ $s->name }}</td>
                                            @endif
                                            <td class="p-3">
                                                @if(strtolower($abs->status) == 'hadir')
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-700">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Hadir
                                                    </span>
                                                @elseif(strtolower($abs->status) == 'sakit')
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-purple-100 text-purple-700">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> Sakit
                                                    </span>
                                                @elseif(strtolower($abs->status) == 'izin')
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-100 text-blue-700">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Izin
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-rose-100 text-rose-700">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Alpa
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="p-3 text-slate-600">
                                                {{ $abs->laporan->jadwal->mataPelajaran->nama ?? '-' }}
                                            </td>
                                            <td class="p-3 text-slate-600">{{ $abs->keterangan ?? '-' }}</td>
                                            <td class="p-3 text-xs text-slate-500">
                                                Mapel: {{ $abs->laporan->guru->name ?? '-' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="p-3 font-medium text-slate-700">{{ $s->name }}</td>
                                            <td class="p-3 text-slate-400 italic">Belum diabsen</td>
                                            <td class="p-3">-</td>
                                            <td class="p-3">-</td>
                                            <td class="p-3">-</td>
                                        </tr>
                                    @endif
                                @else
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="p-3 font-medium text-slate-700">{{ $s->name }}</td>
                                        <td class="p-3">
                                            @if($dataHarian)
                                                @if(strtolower($dataHarian->status) == 'hadir')
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-700">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Hadir
                                                    </span>
                                                @elseif(strtolower($dataHarian->status) == 'sakit')
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-purple-100 text-purple-700">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> Sakit
                                                    </span>
                                                @elseif(strtolower($dataHarian->status) == 'izin')
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-100 text-blue-700">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Izin
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-rose-100 text-rose-700">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Alpa
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-slate-400 italic">Belum diabsen</span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-slate-600">{{ $dataHarian->keterangan ?? '-' }}</td>
                                        <td class="p-3 text-xs text-slate-500">
                                            @if($dataHarian)
                                                {{ \Carbon\Carbon::parse($dataHarian->created_at)->format('H:i') }}<br>
                                                Oleh: {{ $dataHarian->penginput->name ?? 'Siswa (Mandiri)' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        
    @else
        @if(!$filterKelas)
            <div class="bg-blue-50 text-blue-700 p-6 rounded-2xl border border-blue-200 text-center shadow-sm">
                <i class="bi-info-circle text-2xl mb-2 block"></i>
                <h3 class="font-bold">Pilih Kelas</h3>
                <p class="text-sm mt-1">Silakan pilih kelas pada filter di atas untuk melihat data presensi.</p>
            </div>
        @else
            <div class="bg-yellow-50 text-yellow-700 p-6 rounded-2xl border border-yellow-200 text-center shadow-sm">
                <i class="bi-exclamation-triangle text-2xl mb-2 block"></i>
                <h3 class="font-bold">Data Tidak Ditemukan</h3>
                <p class="text-sm mt-1">Belum ada siswa di kelas ini atau data tidak tersedia untuk filter yang dipilih.</p>
            </div>
        @endif
    @endif

</div>

<script>
    document.getElementById('periode_waktu').addEventListener('change', function() {
        var val = this.value;
        document.getElementById('filter_tanggal_container').style.display = (val === 'hari') ? 'block' : 'none';
        document.getElementById('filter_bulan_container').style.display = (val === 'bulan') ? 'block' : 'none';
    });

    document.getElementById('jenis_presensi').addEventListener('change', function() {
        var val = this.value;
        document.getElementById('filter_mapel_container').style.display = (val === 'mapel') ? 'block' : 'none';
    });
</script>
@endsection