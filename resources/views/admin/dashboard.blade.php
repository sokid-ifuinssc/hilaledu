@extends('layouts.app')

@section('title', 'Admin Akademik - Pusat Kendali')

@section('content')
<div class="space-y-6 w-full max-w-7xl mx-auto pb-10">

    <!-- 1. Header Banner Pusat Kendali -->
    <x-dashboard-hero 
        icon="bi-shield-check" 
        badge="Pusat Kendali Akademik"
        title="Layanan Akademik HilalEdu"
        description="Kelola jadwal pelajaran, kurikulum merdeka, master mata pelajaran, dan kehadiran mengajar guru secara praktis dalam satu sistem.">
    </x-dashboard-hero>

    <!-- 2. Stats Matrix (6 Kartu Statistik Rapi Terbuka) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 w-full">
        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-xs text-center transition hover:shadow-sm">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Jadwal Pelajaran</div>
            <div class="text-2xl font-black text-blue-700 mt-1">{{ $totalJadwal }}</div>
            <div class="text-[10px] text-slate-500 font-medium">Slot Aktif</div>
        </div>

        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-xs text-center transition hover:shadow-sm">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Dewan Guru</div>
            <div class="text-2xl font-black text-emerald-700 mt-1">{{ $totalGuru }}</div>
            <div class="text-[10px] text-slate-500 font-medium">Tenaga Pendidik</div>
        </div>

        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-xs text-center transition hover:shadow-sm">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Siswa Terdaftar</div>
            <div class="text-2xl font-black text-purple-700 mt-1">{{ $totalSiswa }}</div>
            <div class="text-[10px] text-slate-500 font-medium">9 Kelas Aktif</div>
        </div>

        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-xs text-center transition hover:shadow-sm">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Mata Pelajaran</div>
            <div class="text-2xl font-black text-amber-700 mt-1">{{ $totalMapel }}</div>
            <div class="text-[10px] text-slate-500 font-medium">Umum & Kejuruan</div>
        </div>

        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-xs text-center transition hover:shadow-sm">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Laporan KBM</div>
            <div class="text-2xl font-black text-indigo-700 mt-1">{{ $totalLaporanKbm }}</div>
            <div class="text-[10px] text-slate-500 font-medium">Realisasi Tersimpan</div>
        </div>

        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-xs text-center transition hover:shadow-sm">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Log Presensi Guru</div>
            <div class="text-2xl font-black text-teal-700 mt-1">{{ $totalAbsensiGuru }}</div>
            <div class="text-[10px] text-slate-500 font-medium">Presensi Mengajar</div>
        </div>
    </div>

    <!-- 3. Quick Shortcut & Ringkasan Presensi Dewan Guru (Laporan Eksekutif) -->
    <div class="p-5 bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white rounded-3xl shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border border-indigo-800/50 w-full">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-amber-400 text-slate-950 flex items-center justify-center text-2xl font-black shadow-md shadow-amber-400/20 shrink-0">
                <i class="bi-file-earmark-check-fill"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-400 text-slate-950">
                        Laporan Eksekutif
                    </span>
                    <span class="text-xs text-slate-300 font-semibold">
                        Tingkat Kehadiran KBM Guru Bulan Ini: <strong class="text-emerald-400">{{ $persenKehadiranBulanIni }}%</strong> ({{ $totalHadirBulanIni }}/{{ $totalSesiBulanIni }} Sesi)
                    </span>
                </div>
                <h3 class="font-extrabold text-base text-white mt-0.5 tracking-tight">
                    Rekapitulasi Presensi Dewan Guru (KBM & Kegiatan Sekolah)
                </h3>
                <p class="text-xs text-slate-300">
                    Ekspor rekapitulasi kehadiran seluruh guru, kalkulasi persentase, dan filter cetak PDF resmi.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <a href="{{ route('akademik.rekap-presensi.index') }}" class="px-4 py-2.5 bg-amber-400 hover:bg-amber-300 text-slate-950 rounded-xl text-xs font-black transition flex items-center gap-2 shadow-md">
                <i class="bi-table text-sm"></i>
                <span>Buka Rekap Presensi Guru</span>
            </a>
            <a href="{{ route('akademik.rekap-presensi.print-all') }}" target="_blank" class="px-4 py-2.5 bg-white/15 hover:bg-white/25 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 border border-white/20">
                <i class="bi-printer-fill text-sm"></i>
                <span>Cetak Rekap (PDF)</span>
            </a>
        </div>
    </div>

    <!-- 4. Papan Monitoring Kehadiran Kelas Real-Time (Full-Width, 9 Kolom Kompak, Jam Ke Real-Time) -->
    @include('partials.papan_monitoring_kelas')

    <!-- 5. Two Column Layout: Jadwal Pelajaran & Realisasi KBM Terbaru -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full">

        <!-- Jadwal Pelajaran Terbaru -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                    <i class="bi-calendar-range text-blue-600"></i>
                    <span>Jadwal Pelajaran Mengajar Terdaftar</span>
                </h3>
                <a href="{{ route('akademik.jadwal.index') }}" class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1">
                    <span>Kelola Jadwal</span>
                    <i class="bi-arrow-right"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 border-b font-bold uppercase text-[10px] text-slate-400">
                        <tr>
                            <th class="px-3 py-2">Hari & Jam</th>
                            <th class="px-3 py-2">Kelas</th>
                            <th class="px-3 py-2">Mata Pelajaran</th>
                            <th class="px-3 py-2">Guru</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y font-medium text-slate-700">
                        @forelse($jadwalTerbaru as $j)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-3 py-2.5 font-bold text-slate-900">
                                {{ $j->hari }} ({{ substr($j->jam_mulai,0,5) }}-{{ substr($j->jam_selesai,0,5) }})
                            </td>
                            <td class="px-3 py-2.5"><span class="px-2 py-0.5 bg-slate-100 font-bold rounded">{{ $j->kelas }}</span></td>
                            <td class="px-3 py-2.5">{{ $j->mataPelajaran->nama ?? '-' }}</td>
                            <td class="px-3 py-2.5 font-semibold text-blue-800">{{ $j->guru?->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-3 py-8 text-center text-slate-400 text-xs">
                                Belum ada slot jadwal pelajaran.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Laporan KBM Terbaru Sekolah -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                    <i class="bi-clipboard-check text-emerald-600"></i>
                    <span>Realisasi KBM Terbaru Masuk</span>
                </h3>
                <a href="{{ route('guru.laporan-kbm.index') }}" class="text-xs font-bold text-emerald-600 hover:underline flex items-center gap-1">
                    <span>Lihat Semua</span>
                    <i class="bi-arrow-right"></i>
                </a>
            </div>

            <div class="space-y-2.5 text-xs">
                @forelse($laporanKbmTerbaru as $l)
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between hover:bg-slate-100/70 transition">
                    <div>
                        <div class="font-bold text-slate-900">{{ $l->guru?->name ?? 'Guru' }} &bull; Kelas {{ $l->jadwal->kelas ?? '-' }}</div>
                        <div class="text-[11px] text-slate-500">{{ $l->jadwal->mataPelajaran->nama ?? '-' }} ({{ \Carbon\Carbon::parse($l->tanggal_realisasi)->format('d M Y') }})</div>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded font-bold text-[10px]">
                            {{ $l->jumlah_siswa_hadir }} Siswa Hadir
                        </span>
                    </div>
                </div>
                @empty
                <div class="py-10 text-center text-slate-400 space-y-1">
                    <i class="bi-inbox text-2xl text-slate-300 block"></i>
                    <p class="text-xs">Belum ada data realisasi laporan KBM terbaru.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
