@extends('layouts.app')

@section('title', 'Riwayat & Informasi Kehadiran Mengajar')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-fingerprint text-emerald-600"></i>
                <span>Laporan Realisasi KBM & Presensi Mengajar</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Rekapitulasi kehadiran mengajar sesuai jadwal & aturan toleransi 10 menit SMK Plus Al-Hilal</p>
        </div>
        <div>
            <a href="{{ route('guru.absensi.print', ['bulan' => $bulan]) }}" target="_blank" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition">
                <i class="bi-printer text-base"></i>
                <span>Cetak Rekap Bulanan</span>
            </a>
        </div>
    </div>

    <!-- Tab Navigation: Laporan Realisasi KBM & Presensi Mengajar -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-px text-xs font-bold">
        <a href="{{ route('guru.laporan-kbm.index') }}" 
           class="px-4 py-2.5 text-slate-500 hover:text-slate-800 flex items-center gap-2 transition rounded-t-xl hover:bg-slate-50">
            <i class="bi-journal-check"></i>
            <span>Jurnal Realisasi KBM & Presensi Siswa</span>
        </a>
        <a href="{{ route('guru.absensi.index') }}" 
           class="px-4 py-2.5 border-b-2 border-emerald-600 text-emerald-700 flex items-center gap-2 bg-emerald-50/50 rounded-t-xl">
            <i class="bi-fingerprint"></i>
            <span>Riwayat Presensi Mengajar Guru</span>
        </a>
        <a href="{{ route('guru.rekap-presensi.index') }}" 
           class="px-4 py-2.5 text-slate-500 hover:text-slate-800 flex items-center gap-2 transition rounded-t-xl hover:bg-slate-50">
            <i class="bi-pie-chart-fill"></i>
            <span>Rekap Presensi (KBM & Kegiatan)</span>
        </a>
    </div>

    <!-- Summary Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Persentase</div>
            <div class="text-2xl font-black text-blue-700 mt-0.5">{{ $persentase }}%</div>
            <div class="text-[10px] text-slate-500 font-semibold">{{ $totalHadir + $totalTerlambat + $totalTugasLuar }} / {{ $totalSesi }} Sesi</div>
        </div>

        <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Tepat Waktu</div>
            <div class="text-2xl font-black text-emerald-700 mt-0.5">{{ $totalHadir }}</div>
            <div class="text-[10px] text-emerald-600">&le; 10 Menit</div>
        </div>

        <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">Terlambat</div>
            <div class="text-2xl font-black text-amber-700 mt-0.5">{{ $totalTerlambat }}</div>
            <div class="text-[10px] text-amber-600">&gt; 10 Menit</div>
        </div>

        <div class="p-4 bg-blue-50 rounded-2xl border border-blue-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-blue-700 uppercase tracking-wider">Izin</div>
            <div class="text-2xl font-black text-blue-700 mt-0.5">{{ $totalIzin }}</div>
            <div class="text-[10px] text-blue-600">Surat Izin</div>
        </div>

        <div class="p-4 bg-purple-50 rounded-2xl border border-purple-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-purple-700 uppercase tracking-wider">Sakit</div>
            <div class="text-2xl font-black text-purple-700 mt-0.5">{{ $totalSakit }}</div>
            <div class="text-[10px] text-purple-600">Surat Dokter</div>
        </div>

        <div class="p-4 bg-indigo-50 rounded-2xl border border-indigo-200 shadow-xs text-center">
            <div class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider">Tugas Luar</div>
            <div class="text-2xl font-black text-indigo-700 mt-0.5">{{ $totalTugasLuar }}</div>
            <div class="text-[10px] text-indigo-600">Dinas Luar</div>
        </div>
    </div>

    <!-- Filter Mode Toggle (Bulanan & Harian) -->
    <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs"
         x-data="{ mode: '{{ $filterType }}' }">
        
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
                <button type="button" 
                        @click="mode = 'bulanan'; $refs.filterInput.value = 'bulanan'; $refs.form.submit()"
                        :class="mode === 'bulanan' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-500 font-medium'"
                        class="px-3 py-1.5 rounded-lg transition text-xs">
                    Filter Bulanan
                </button>
                <button type="button" 
                        @click="mode = 'harian'; $refs.filterInput.value = 'harian'; $refs.form.submit()"
                        :class="mode === 'harian' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-500 font-medium'"
                        class="px-3 py-1.5 rounded-lg transition text-xs">
                    Filter Harian
                </button>
            </div>
            <input type="hidden" name="filter_type" x-ref="filterInput" value="{{ $filterType }}">

            <!-- Monthly Selector -->
            <div x-show="mode === 'bulanan'">
                <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 text-xs">
            </div>

            <!-- Daily Selector -->
            <div x-show="mode === 'harian'">
                <input type="date" name="tanggal" value="{{ $tanggal }}" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 text-xs">
            </div>
        </form>

        <div class="text-xs text-slate-500">
            Aturan Sistem: <strong>Toleransi Hadir 10 Menit dari Jam Mulai Jadwal</strong>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Tanggal & Waktu Absen</th>
                        <th class="px-4 py-4">Mata Pelajaran & Kelas</th>
                        <th class="px-4 py-4">Jam Jadwal</th>
                        <th class="px-4 py-4 text-center">Status Kehadiran</th>
                        <th class="px-4 py-4">Keterangan / Alasan</th>
                        <th class="px-4 py-4 text-center">Lampiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($riwayat as $a)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 text-sm">
                                {{ \Carbon\Carbon::parse($a->tanggal)->format('d M Y') }}
                            </div>
                            <div class="text-[11px] text-slate-400">
                                Pukul {{ $a->jam_absen }} WIB
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-bold text-slate-800">{{ $a->jadwal->mataPelajaran->nama ?? 'Mata Pelajaran' }}</div>
                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[11px] font-bold rounded-md border border-indigo-200">
                                Kelas {{ $a->jadwal->kelas ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-4 font-mono text-slate-600">
                            {{ substr($a->jadwal->jam_mulai ?? '',0,5) }} - {{ substr($a->jadwal->jam_selesai ?? '',0,5) }}
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-black border {{ $a->badge_class }}">
                                {{ $a->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-slate-600">
                            {{ $a->catatan ?: '-' }}
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($a->lampiran_bukti)
                            <a href="{{ asset('storage/' . $a->lampiran_bukti) }}" target="_blank" class="px-2.5 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-bold transition inline-flex items-center gap-1">
                                <i class="bi-file-earmark-text"></i>
                                <span>Lihat</span>
                            </a>
                            @else
                            <span class="text-slate-300">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <i class="bi-fingerprint text-3xl mb-2 inline-block"></i>
                            <p>Belum ada riwayat kehadiran mengajar pada periode ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $riwayat->links() }}
        </div>
    </div>

</div>
@endsection
