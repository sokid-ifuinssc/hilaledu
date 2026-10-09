@extends('layouts.app')

@section('title', 'Monitoring Mingguan Ekstrakurikuler')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.ekstrakurikuler.index') }}" class="hover:text-emerald-600 transition-colors">Ekstrakurikuler</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <span class="text-slate-800 font-medium">Monitoring Mingguan</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white shadow-md shadow-indigo-200">
                    <i class="bi bi-calendar2-range text-lg"></i>
                </span>
                Monitoring Kegiatan Mingguan Eskul
            </h1>
            <p class="text-sm text-slate-500 mt-1">Pantau keterlaksanaan kegiatan, absensi, dan realisasi ekstrakurikuler per rentang tanggal (Waka Kesiswaan, Pembina OSIS & Admin)</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.ekstrakurikuler.rekap-kelas') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition">
                <i class="bi bi-people-fill"></i>
                Rekap Per Kelas Siswa
            </a>
            <a href="{{ route('admin.ekstrakurikuler.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 transition shadow-sm">
                <i class="bi bi-arrow-left"></i>
                Daftar Eskul
            </a>
        </div>
    </div>

    <!-- Banner Waka Kesiswaan & Pembina OSIS -->
    <div class="bg-gradient-to-r from-indigo-50 via-blue-50 to-emerald-50 border border-indigo-200/80 rounded-2xl p-4 flex items-start gap-3.5 text-indigo-950 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xl shrink-0 shadow-sm shadow-indigo-200">
            <i class="bi bi-shield-check"></i>
        </div>
        <div class="text-xs">
            <h3 class="font-bold text-sm text-indigo-950 flex items-center gap-2">
                <span>Monitoring Eskul Terpadu — Waka Kesiswaan & Pembina OSIS</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">Terintegrasi KBM & Nilai</span>
            </h3>
            <p class="text-slate-600 mt-1 leading-relaxed">
                Rencana dan laporan kegiatan ekstrakurikuler terjadwal mengikuti <strong>Minggu Efektif</strong> kalender akademik (dengan opsi <em>Kegiatan Tambahan</em> jika ada latihan/event di luar minggu efektif). Seluruh catatan presensi siswa otomatis disinkronkan ke mata pelajaran <strong>Team Work Project dan Project Pancasila</strong> di rombel kelas siswa masing-masing (X, XI, XII).
            </p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.ekstrakurikuler.monitoring') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal (Mulai Pekan)</label>
                <input type="date" name="tanggal_mulai" value="{{ $startDate }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal (Akhir Pekan)</label>
                <input type="date" name="tanggal_selesai" value="{{ $endDate }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pilih Eskul</label>
                <select name="ekstrakurikuler_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    <option value="">-- Semua Ekstrakurikuler --</option>
                    @foreach($allEskul as $eskul)
                        <option value="{{ $eskul->id }}" {{ $selectedEskulId == $eskul->id ? 'selected' : '' }}>{{ $eskul->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                    <i class="bi bi-funnel-fill"></i> Terapkan Filter
                </button>
                <a href="{{ route('admin.ekstrakurikuler.monitoring') }}" class="px-3 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 border border-slate-200 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Ringkasan Statistik Periode -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-file-earmark-check"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Laporan Terlaksana</p>
                <h4 class="text-xl font-bold text-slate-800">{{ $laporans->count() }} <span class="text-xs font-normal text-slate-400">kegiatan</span></h4>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-person-check"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Siswa Hadir</p>
                <h4 class="text-xl font-bold text-slate-800">{{ $laporans->sum('jumlah_hadir') }}</h4>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Rencana Terjadwal</p>
                <h4 class="text-xl font-bold text-slate-800">{{ $rencanas->count() }} <span class="text-xs font-normal text-slate-400">pertemuan</span></h4>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-trophy"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Eskul Aktif</p>
                <h4 class="text-xl font-bold text-slate-800">{{ $laporans->pluck('ekstrakurikuler_id')->unique()->count() }} / {{ $allEskul->count() }}</h4>
            </div>
        </div>
    </div>

    <!-- Timeline / Tabel Laporan Kegiatan Terlaksana -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-journal-text text-emerald-600"></i>
                    Laporan Keterlaksanaan & Absensi Eskul
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Rentang: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                {{ $laporans->count() }} Laporan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3">Tanggal & Waktu</th>
                        <th class="px-4 py-3">Ekstrakurikuler & Pembina</th>
                        <th class="px-4 py-3">Pertemuan & Kegiatan</th>
                        <th class="px-4 py-3">Kehadiran Siswa</th>
                        <th class="px-4 py-3">Status & Bukti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($laporans as $lap)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($lap->tanggal_kegiatan)->format('d M Y') }}</span>
                                <span class="block text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($lap->tanggal_kegiatan)->isoFormat('dddd') }}</span>
                                @if($lap->jam_mulai)
                                    <span class="inline-block mt-0.5 text-[10px] text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">{{ substr($lap->jam_mulai,0,5) }} - {{ substr($lap->jam_selesai,0,5) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <a href="{{ route('admin.ekstrakurikuler.show', $lap->ekstrakurikuler_id) }}" class="font-bold text-indigo-600 hover:underline">
                                    {{ $lap->ekstrakurikuler->nama ?? '-' }}
                                </a>
                                <p class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                    <i class="bi bi-person text-slate-400"></i>
                                    {{ $lap->pembinaGuru->name ?? ($lap->ekstrakurikuler->pembina->name ?? 'Belum ada pembina') }}
                                </p>
                            </td>
                            <td class="px-4 py-3.5 max-w-xs">
                                <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-indigo-50 text-indigo-700">Pertemuan #{{ $lap->pertemuan_ke }}</span>
                                    @if($lap->tipe_jadwal === 'kegiatan_tambahan')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                            <i class="bi bi-plus-circle"></i> Tambahan Luar Minggu Efektif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-800">
                                            <i class="bi bi-calendar-check"></i> Minggu Efektif {{ $lap->minggu_ke ? '#'.$lap->minggu_ke : '' }}
                                        </span>
                                    @endif
                                </div>
                                <div class="font-semibold text-slate-800 mb-0.5">{{ $lap->nama_kegiatan }}</div>
                                <p class="text-[11px] text-slate-500 line-clamp-2">{{ $lap->ringkasan_materi ?: 'Tidak ada ringkasan materi.' }}</p>
                                @if($lap->kendala_catatan)
                                    <p class="text-[10px] text-amber-600 bg-amber-50/70 rounded p-1 mt-1 border border-amber-100/50">
                                        <i class="bi bi-exclamation-triangle mr-1"></i>{{ $lap->kendala_catatan }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-slate-800">{{ $lap->jumlah_hadir }} Hadir</span>
                                    @php
                                        $total = $lap->jumlah_hadir + $lap->jumlah_izin + $lap->jumlah_sakit + $lap->jumlah_alpa;
                                        $persen = $total > 0 ? round(($lap->jumlah_hadir / $total) * 100) : 0;
                                    @endphp
                                    <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold {{ $persen >= 75 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $persen }}%
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-[10px] text-slate-500">
                                    <span class="text-blue-600">Izin: {{ $lap->jumlah_izin }}</span>
                                    <span class="text-amber-600">Sakit: {{ $lap->jumlah_sakit }}</span>
                                    <span class="text-rose-600">Alpa: {{ $lap->jumlah_alpa }}</span>
                                </div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/50" title="Kehadiran otomatis tersinkron ke pertemuan mapel Team Work Project & Project Pancasila">
                                        <i class="bi bi-arrow-repeat"></i> Sync KBM Mapel TWP
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2 py-1 text-[10px] font-semibold rounded-full bg-emerald-100 text-emerald-700">
                                    {{ ucfirst($lap->status) }}
                                </span>
                                @if($lap->foto_kegiatan)
                                    <a href="{{ asset('storage/' . $lap->foto_kegiatan) }}" target="_blank" class="block mt-1 text-[11px] text-indigo-600 hover:underline">
                                        <i class="bi bi-image mr-1"></i>Foto Dokumentasi
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                <i class="bi bi-inbox text-3xl block mb-2 text-slate-300"></i>
                                Belum ada laporan kegiatan eskul pada rentang tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Daftar Rencana yang Belum Dilaporkan / Terjadwal di Pekan Ini -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-calendar-check text-indigo-600"></i>
                    Rencana Kegiatan Eskul Terjadwal Pada Periode Ini
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Rencana yang diinput oleh pembina untuk pelaksanaan pada rentang tanggal terpilih</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700">
                {{ $rencanas->count() }} Rencana
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3">Tanggal Rencana</th>
                        <th class="px-4 py-3">Ekstrakurikuler</th>
                        <th class="px-4 py-3">Pertemuan</th>
                        <th class="px-4 py-3">Nama & Deskripsi Rencana</th>
                        <th class="px-4 py-3">Target Pencapaian</th>
                        <th class="px-4 py-3">Status Laporan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rencanas as $ren)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3 whitespace-nowrap font-bold text-slate-800">
                                {{ \Carbon\Carbon::parse($ren->tanggal_rencana)->format('d M Y') }}
                                <span class="block text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($ren->tanggal_rencana)->isoFormat('dddd') }}</span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-800">
                                {{ $ren->ekstrakurikuler->nama ?? '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-bold text-indigo-600 block">Pertemuan #{{ $ren->pertemuan_ke }}</span>
                                @if($ren->tipe_jadwal === 'kegiatan_tambahan')
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 mt-0.5">
                                        <i class="bi bi-plus-circle"></i> Tambahan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-800 mt-0.5">
                                        <i class="bi bi-calendar-check"></i> ME {{ $ren->minggu_ke ? '#'.$ren->minggu_ke : '' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 max-w-sm">
                                <p class="font-semibold text-slate-800">{{ $ren->nama_kegiatan }}</p>
                                <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5">{{ $ren->deskripsi_rencana ?: '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-600 max-w-xs">
                                {{ $ren->target_pencapaian ?: '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($ren->status == 'terlaksana')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-700">Sudah Dilaporkan</span>
                                @elseif($ren->status == 'dibatalkan')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-700">Dibatalkan</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-700">Menunggu Pelaksanaan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-400">
                                Tidak ada jadwal rencana kegiatan pada rentang tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
