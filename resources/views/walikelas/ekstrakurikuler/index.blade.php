@extends('layouts.app')

@section('title', 'Rekap Ekstrakurikuler Siswa Bimbingan')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <span>Wali Kelas</span>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <span class="text-slate-800 font-medium">Ekstrakurikuler Siswa</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-md shadow-emerald-200">
                    <i class="bi bi-award-fill text-lg"></i>
                </span>
                Rekap Eskul Siswa: {{ $kelas->nama_kelas ?? 'Kelas Bimbingan' }}
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Pantau daftar eskul yang diikuti siswa kelas binaan Anda, tingkat kehadiran eskul, dan nilai dari pembina eskul.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.ekstrakurikuler.rekap-kelas.print', $kelas->id) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                <i class="bi bi-printer-fill"></i>
                Cetak Rekap Eskul
            </a>
            <a href="{{ route('walikelas.leger', ['kelas_id' => $kelas->id]) }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 transition shadow-sm">
                <i class="bi bi-journal-bookmark"></i>
                Leger Nilai
            </a>
        </div>
    </div>

    @if(isset($kelasList) && $kelasList->isNotEmpty())
        <!-- Selector Kelas untuk Super Admin / Pimpinan -->
        <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-4 bg-gradient-to-r from-emerald-50/40 via-white to-teal-50/40">
            <form method="GET" action="{{ route('walikelas.eskul') }}" class="flex flex-col sm:flex-row items-end gap-3">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-emerald-900 mb-1 flex items-center gap-1.5">
                        <i class="bi bi-shield-check text-emerald-600"></i>
                        Akses Super Admin: Pilih Rombel / Kelas untuk Melihat Rekap Eskul
                    </label>
                    <select name="kelas_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs rounded-xl border border-emerald-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-semibold bg-white">
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ $kelas->id == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }} ({{ $k->jurusan ?: 'Umum' }}) - Wali Kelas: {{ $k->waliKelas->name ?? ($k->wali_kelas ?: 'Belum diset') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 text-xs font-bold bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition">
                    Tampilkan
                </button>
            </form>
        </div>
    @endif

    <!-- Statistik Mini -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Total Siswa Bimbingan</p>
            <h4 class="text-xl font-bold text-slate-800 mt-1">{{ $rekapData->count() }} <span class="text-xs font-normal text-slate-400">siswa</span></h4>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Mengikuti Ekstrakurikuler</p>
            <h4 class="text-xl font-bold text-emerald-600 mt-1">{{ $rekapData->where('jumlah_eskul', '>', 0)->count() }} <span class="text-xs font-normal text-slate-400">siswa</span></h4>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Belum Ikut Eskul</p>
            <h4 class="text-xl font-bold text-rose-600 mt-1">{{ $rekapData->where('jumlah_eskul', 0)->count() }} <span class="text-xs font-normal text-slate-400">siswa</span></h4>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Tahun Ajaran</p>
            <h4 class="text-base font-bold text-slate-800 mt-1">{{ $tahunAjaran }}</h4>
            <span class="text-[11px] text-slate-400">{{ ucfirst($semester) }}</span>
        </div>
    </div>

    <!-- Tabel Rekap Detail Siswa -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="bi bi-people-fill text-emerald-600"></i>
                Daftar Keikutsertaan Ekstrakurikuler Siswa
            </h3>
            <span class="text-xs text-slate-500">
                Nilai dan persentase diinput langsung oleh masing-masing Pembina Eskul
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 w-12 text-center">No</th>
                        <th class="px-4 py-3">Nama Siswa</th>
                        <th class="px-4 py-3">NISN</th>
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
                                {{ $row['siswa']->nisn ?: ($row['siswa']->nis ?: '-') }}
                            </td>
                            
                            @if(count($row['eskul_details']) > 0)
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
                                        Belum Mengikuti Ekstrakurikuler
                                    </span>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                Tidak ada data siswa di kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
