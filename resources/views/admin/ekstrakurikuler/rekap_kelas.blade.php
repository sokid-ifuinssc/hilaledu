@extends('layouts.app')

@section('title', 'Rekap Ekstrakurikuler Siswa Per Kelas')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.ekstrakurikuler.index') }}" class="hover:text-emerald-600 transition-colors">Ekstrakurikuler</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <span class="text-slate-800 font-medium">Rekap Per Kelas</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-md shadow-emerald-200">
                    <i class="bi bi-people-fill text-lg"></i>
                </span>
                Rekap Keanggotaan & Nilai Eskul Per Kelas
            </h1>
            <p class="text-sm text-slate-500 mt-1">Laporan keikutsertaan eskul siswa, persentase kehadiran, dan nilai dari pembina eskul (Waka Kesiswaan & Wali Kelas)</p>
        </div>
        <div class="flex items-center gap-2">
            @if($selectedKelasId)
                <a href="{{ route('admin.ekstrakurikuler.rekap-kelas.print', $selectedKelasId) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm">
                    <i class="bi bi-printer-fill"></i>
                    Cetak Rekap Kelas (PDF)
                </a>
            @endif
            <a href="{{ route('admin.ekstrakurikuler.monitoring') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 transition shadow-sm">
                <i class="bi bi-calendar2-range"></i>
                Monitoring Mingguan
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.ekstrakurikuler.rekap-kelas') }}" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="flex-1 w-full">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pilih Rombel / Kelas Siswa</label>
                <select name="kelas_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-medium">
                    <option value="">-- Pilih Kelas untuk Menampilkan Rekap --</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ $selectedKelasId == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }} @if($k->jurusan) ({{ $k->jurusan }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                <i class="bi bi-search"></i> Tampilkan Rekap
            </button>
        </form>
    </div>

    @if($selectedKelas)
        <!-- Ringkasan Kelas -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-xs text-slate-500 font-medium">Kelas / Rombel</p>
                <h4 class="text-lg font-bold text-slate-800 mt-1">{{ $selectedKelas->nama_kelas }}</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $selectedKelas->tingkat ? 'Tingkat ' . $selectedKelas->tingkat : '' }} {{ $selectedKelas->jurusan }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-xs text-slate-500 font-medium">Total Siswa Terdaftar</p>
                <h4 class="text-lg font-bold text-slate-800 mt-1">{{ $rekapData->count() }} <span class="text-xs font-normal text-slate-400">siswa</span></h4>
                <p class="text-[11px] text-emerald-600 mt-0.5">{{ $rekapData->where('jumlah_eskul', '>', 0)->count() }} siswa ikut eskul</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-xs text-slate-500 font-medium">Belum Ikut Eskul</p>
                <h4 class="text-lg font-bold text-rose-600 mt-1">{{ $rekapData->where('jumlah_eskul', 0)->count() }} <span class="text-xs font-normal text-slate-400">siswa</span></h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Perlu perhatian kesiswaan</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-xs text-slate-500 font-medium">Wali Kelas</p>
                <h4 class="text-lg font-bold text-slate-800 mt-1 truncate">{{ $selectedKelas->waliKelas->name ?? '-' }}</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Guru Pembimbing</p>
            </div>
        </div>

        <!-- Tabel Rekap Detail Siswa -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-table text-emerald-600"></i>
                        Daftar Siswa Kelas {{ $selectedKelas->nama_kelas }} & Ekstrakurikuler
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Rincian eskul yang diikuti, persentase kehadiran, dan nilai dari pembina eskul</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700">
                    {{ $rekapData->count() }} Siswa
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3 w-12 text-center">No</th>
                            <th class="px-4 py-3">Nama Siswa</th>
                            <th class="px-4 py-3">NISN / NIS</th>
                            <th class="px-4 py-3">Ekstrakurikuler yang Diikuti</th>
                            <th class="px-4 py-3 text-center">Persentase Hadir</th>
                            <th class="px-4 py-3 text-center">Nilai Angka</th>
                            <th class="px-4 py-3 text-center">Predikat</th>
                            <th class="px-4 py-3">Catatan Pembina Eskul</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rekapData as $index => $row)
                            <tr class="hover:bg-slate-50/70 transition align-top">
                                <td class="px-4 py-3.5 text-center font-semibold text-slate-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <p class="font-bold text-slate-800">{{ $row['siswa']->nama }}</p>
                                    <span class="text-[11px] text-slate-400 capitalize">{{ $row['siswa']->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-slate-600">
                                    <span>{{ $row['siswa']->nisn ?: ($row['siswa']->nis ?: '-') }}</span>
                                </td>
                                
                                @if(count($row['eskul_details']) > 0)
                                    <!-- Menampilkan rincian eskul (bisa lebih dari satu eskul per siswa) -->
                                    <td class="px-4 py-3.5 space-y-2">
                                        @foreach($row['eskul_details'] as $det)
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                <span class="font-bold text-slate-800">{{ $det['eskul_nama'] }}</span>
                                                <span class="text-[10px] px-1.5 py-0.5 rounded font-medium {{ $det['jabatan'] == 'ketua' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                                                    {{ ucfirst($det['jabatan']) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3.5 text-center space-y-2">
                                        @foreach($row['eskul_details'] as $det)
                                            <div>
                                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $det['persentase_kehadiran'] >= 75 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                    {{ $det['persentase_kehadiran'] }}%
                                                </span>
                                                <span class="block text-[10px] text-slate-400 mt-0.5">({{ $det['hadir'] }}/{{ $det['total_pertemuan'] }} ptm)</span>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3.5 text-center space-y-2">
                                        @foreach($row['eskul_details'] as $det)
                                            <div class="font-bold text-slate-800">
                                                {{ $det['nilai_angka'] !== null ? number_format($det['nilai_angka'], 0) : '-' }}
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3.5 text-center space-y-2">
                                        @foreach($row['eskul_details'] as $det)
                                            <div>
                                                @if($det['nilai_huruf'])
                                                    <span class="px-2 py-0.5 rounded-md font-bold text-xs {{ in_array($det['nilai_huruf'], ['A', 'A+']) ? 'bg-indigo-100 text-indigo-700' : ($det['nilai_huruf'] == 'B' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
                                                        {{ $det['nilai_huruf'] }}
                                                    </span>
                                                @else
                                                    <span class="text-slate-400">-</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3.5 space-y-2 max-w-xs">
                                        @foreach($row['eskul_details'] as $det)
                                            <p class="text-[11px] text-slate-600 italic">
                                                {{ $det['catatan_nilai'] ?: 'Belum ada catatan pembina' }}
                                            </p>
                                        @endforeach
                                    </td>
                                @else
                                    <td colspan="5" class="px-4 py-3.5">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-600 border border-rose-200">
                                            <i class="bi bi-x-circle mr-1"></i>Belum Mengikuti Ekstrakurikuler
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                    Tidak ada data siswa pada kelas ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl mx-auto mb-3 shadow-sm">
                <i class="bi bi-arrow-up-circle"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800">Silakan Pilih Kelas Terlebih Dahulu</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">Pilih kelas/rombel pada form filter di atas untuk melihat daftar siswa, status keikutsertaan eskul, persentase absensi, dan nilai dari pembina eskul.</p>
        </div>
    @endif
</div>
@endsection
