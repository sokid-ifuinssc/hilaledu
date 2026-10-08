@extends('layouts.app')

@section('title', 'Portal Ketua Eskul: Absensi Anggota')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('siswa.ekstrakurikuler.index') }}" class="hover:text-indigo-600 transition-colors">Eskul Saya</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <span class="text-slate-800 font-medium">Absensi Ketua Eskul</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-md shadow-amber-200">
                    <i class="bi bi-person-badge-fill text-lg"></i>
                </span>
                Absensi Anggota oleh Ketua: {{ $eskul->nama }}
            </h1>
            <p class="text-sm text-slate-500 mt-1">Anda ditugaskan oleh pembina (<b>{{ $eskul->pembina->name ?? 'Pembina' }}</b>) untuk mengabsen kehadiran rekan-rekan anggota eskul.</p>
        </div>
        <div>
            <a href="{{ route('siswa.ekstrakurikuler.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 transition shadow-sm">
                <i class="bi bi-arrow-left"></i>
                Kembali ke Eskul Saya
            </a>
        </div>
    </div>

    <!-- Form Absensi Ketua -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <form action="{{ route('siswa.ekstrakurikuler.ketua-simpan-absensi', $eskul->id) }}" method="POST">
            @csrf
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div class="w-full sm:w-64">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Kegiatan Pertemuan</label>
                    <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-bold text-slate-800">
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-500 block">Total Anggota Terdaftar:</span>
                    <span class="text-base font-bold text-slate-800">{{ $eskul->anggotas->count() }} Siswa</span>
                </div>
            </div>

            <!-- Tabel Anggota yang Diabsen -->
            <div class="overflow-x-auto mt-4">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3 w-12 text-center">No</th>
                            <th class="px-4 py-3">Nama Anggota Siswa</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3 text-center w-24">Hadir</th>
                            <th class="px-4 py-3 text-center w-24">Izin</th>
                            <th class="px-4 py-3 text-center w-24">Sakit</th>
                            <th class="px-4 py-3 text-center w-24">Alpa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($eskul->anggotas as $idx => $ang)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3 text-center text-slate-500 font-medium">{{ $idx + 1 }}</td>
                                <td class="px-4 py-3 font-bold text-slate-800">
                                    {{ $ang->siswa->nama ?? 'Siswa' }}
                                    @if($ang->siswa_id == $eskul->ketua_siswa_id)
                                        <span class="ml-1 text-[10px] px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 font-semibold">(Anda - Ketua)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $ang->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="presensi[{{ $ang->siswa_id }}]" value="hadir" checked class="text-emerald-600 focus:ring-emerald-500">
                                    </label>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="presensi[{{ $ang->siswa_id }}]" value="izin" class="text-blue-600 focus:ring-blue-500">
                                    </label>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="presensi[{{ $ang->siswa_id }}]" value="sakit" class="text-amber-600 focus:ring-amber-500">
                                    </label>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="presensi[{{ $ang->siswa_id }}]" value="alpa" class="text-rose-600 focus:ring-rose-500">
                                    </label>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                <p class="text-[11px] text-slate-400">
                    <i class="bi bi-info-circle mr-1"></i>Data kehadiran yang Anda input akan langsung tercatat sebagai presensi resmi eskul dengan metode "Ketua".
                </p>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white shadow-sm shadow-amber-200 transition">
                    <i class="bi bi-check2-circle text-sm"></i>
                    Simpan Absensi Pertemuan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
