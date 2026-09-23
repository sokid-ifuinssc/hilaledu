@extends('layouts.app')

@section('title', 'Rekap Presensi KBM & Kegiatan Sekolah')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-bar-chart-line-fill text-emerald-600"></i>
                <span>Rekapitulasi Presensi KBM & Kegiatan Sekolah</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Laporan komprehensif kehadiran mengajar guru dan partisipasi pada kegiatan resmi sekolah dengan kalkulasi persentase otomatis.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('guru.rekap-presensi.print', ['guru_id' => $targetUserId, 'bulan' => $bulan, 'tahun_ajaran' => $tahunAjaran]) }}" 
               target="_blank"
               class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-emerald-600/20 transition">
                <i class="bi-printer-fill text-sm"></i>
                <span>Cetak / Simpan PDF</span>
            </a>
        </div>
    </div>

    <!-- Tab Navigation: Laporan Kegiatan Pembelajaran -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-px text-xs font-bold">
        <a href="{{ route('guru.laporan-kbm.index') }}" 
           class="px-4 py-2.5 text-slate-500 hover:text-slate-800 flex items-center gap-2 transition rounded-t-xl hover:bg-slate-50">
            <i class="bi-journal-check"></i>
            <span>Jurnal Realisasi KBM & Presensi Siswa</span>
        </a>
        <a href="{{ route('guru.absensi.index') }}" 
           class="px-4 py-2.5 text-slate-500 hover:text-slate-800 flex items-center gap-2 transition rounded-t-xl hover:bg-slate-50">
            <i class="bi-fingerprint"></i>
            <span>Riwayat Presensi Mengajar Guru</span>
        </a>
        <a href="{{ route('guru.rekap-presensi.index') }}" 
           class="px-4 py-2.5 border-b-2 border-emerald-600 text-emerald-700 flex items-center gap-2 bg-emerald-50/50 rounded-t-xl">
            <i class="bi-pie-chart-fill"></i>
            <span>Rekap Presensi (KBM & Kegiatan)</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4 text-xs">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            @if($isExecutive && $gurus->isNotEmpty())
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Pilih Guru</label>
                <select name="guru_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    @foreach($gurus as $g)
                    <option value="{{ $g->id }}" {{ $targetUserId == $g->id ? 'selected' : '' }}>
                        {{ $g->name }} ({{ $g->nip ?: $g->username }})
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Mode Periode</label>
                <select name="mode" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="bulan" {{ $periodeMode === 'bulan' ? 'selected' : '' }}>Filter Bulanan</option>
                    <option value="tahun" {{ $periodeMode === 'tahun' ? 'selected' : '' }}>1 Tahun Ajaran Penuh</option>
                </select>
            </div>

            @if($periodeMode === 'bulan')
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Bulan</label>
                <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
            </div>
            @endif

            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Tahun Ajaran</label>
                <select name="tahun_ajaran" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="2026/2027" {{ $tahunAjaran === '2026/2027' ? 'selected' : '' }}>2026/2027 (Aktif)</option>
                    <option value="2027/2028" {{ $tahunAjaran === '2027/2028' ? 'selected' : '' }}>2027/2028</option>
                </select>
            </div>
        </form>

        <div class="flex items-center gap-2">
            <span class="text-slate-500 font-medium text-xs">
                Periode Terpilih: <strong class="text-slate-900">{{ $bulan ? \Carbon\Carbon::parse($bulan . '-01')->isoFormat('MMMM Y') : "Tahun Ajaran {$tahunAjaran}" }}</strong>
            </span>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Persentase Kehadiran Gabungan -->
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white rounded-3xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-xs font-semibold text-emerald-100">
                <span>Persentase Kehadiran Total</span>
                <i class="bi-trophy-fill text-amber-300 text-base"></i>
            </div>
            <div class="text-3xl font-black">{{ $rekap['persenGabungan'] }}%</div>
            <div class="flex items-center justify-between text-[11px] pt-1 border-t border-white/20 text-emerald-100">
                <span>Predikat: <strong>{{ $rekap['predikat'] }}</strong></span>
                <span>{{ $rekap['totalHadirGabungan'] }}/{{ $rekap['totalSesiGabungan'] }} Sesi</span>
            </div>
        </div>

        <!-- Card 2: Kehadiran KBM -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                <span>Kehadiran Mengajar KBM</span>
                <i class="bi-book-half text-blue-600 text-base"></i>
            </div>
            <div class="text-3xl font-black text-blue-700">{{ $rekap['persenKbm'] }}%</div>
            <div class="flex items-center justify-between text-[11px] pt-1 border-t border-slate-100 text-slate-500">
                <span>Hadir: <strong>{{ $rekap['totalHadirKbm'] }}</strong> sesi</span>
                <span>Total: <strong>{{ $rekap['totalSesiKbm'] }}</strong> sesi</span>
            </div>
        </div>

        <!-- Card 3: Kehadiran Kegiatan Sekolah -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                <span>Partisipasi Kegiatan Sekolah</span>
                <i class="bi-calendar-check-fill text-amber-600 text-base"></i>
            </div>
            <div class="text-3xl font-black text-amber-700">{{ $rekap['persenKegiatan'] }}%</div>
            <div class="flex items-center justify-between text-[11px] pt-1 border-t border-slate-100 text-slate-500">
                <span>Hadir: <strong>{{ $rekap['kegiatanHadir'] }}</strong> agenda</span>
                <span>Total: <strong>{{ $rekap['totalKegiatan'] }}</strong> agenda</span>
            </div>
        </div>

        <!-- Card 4: Rincian Izin / Sakit / Terlambat -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                <span>Rincian Absensi</span>
                <i class="bi-clipboard-pulse text-rose-500 text-base"></i>
            </div>
            <div class="grid grid-cols-3 gap-1 pt-1 text-center">
                <div class="p-1 bg-amber-50 rounded-xl">
                    <span class="text-[10px] text-amber-700 font-bold block">Terlambat</span>
                    <strong class="text-sm font-black text-amber-900">{{ $rekap['kbmTerlambat'] }}</strong>
                </div>
                <div class="p-1 bg-blue-50 rounded-xl">
                    <span class="text-[10px] text-blue-700 font-bold block">Izin</span>
                    <strong class="text-sm font-black text-blue-900">{{ $rekap['kbmIzin'] + $rekap['kegiatanIzin'] }}</strong>
                </div>
                <div class="p-1 bg-purple-50 rounded-xl">
                    <span class="text-[10px] text-purple-700 font-bold block">Sakit</span>
                    <strong class="text-sm font-black text-purple-900">{{ $rekap['kbmSakit'] + $rekap['kegiatanSakit'] }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- VISUALISASI GRAFIK CHART.JS (1 TAHUN AJARAN & BULANAN) -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Grafik 1: Tren Kehadiran Selama 1 Tahun Ajaran (2 Kolom) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-black text-slate-900 text-base flex items-center gap-2">
                        <i class="bi-graph-up-arrow text-emerald-600"></i>
                        <span>Tren Kehadiran Selama 1 Tahun Ajaran (Juli - Juni)</span>
                    </h3>
                    <p class="text-xs text-slate-500">Grafik frekuensi kehadiran mengajar KBM dan kegiatan sekolah setiap bulan</p>
                </div>
            </div>
            <div class="relative h-64 sm:h-72">
                <canvas id="chartTrenTahunan"></canvas>
            </div>
        </div>

        <!-- Grafik 2: Komposisi Status Kehadiran (1 Kolom) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4">
            <div>
                <h3 class="font-black text-slate-900 text-base flex items-center gap-2">
                    <i class="bi-pie-chart text-blue-600"></i>
                    <span>Distribusi Status Kehadiran</span>
                </h3>
                <p class="text-xs text-slate-500">Komposisi status presensi periode ini</p>
            </div>
            <div class="relative h-64 sm:h-72 flex items-center justify-center">
                <canvas id="chartDistribusiStatus"></canvas>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TABEL RINCIAN KEHADIRAN KBM & KEGIATAN SEKOLAH -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Rincian Sesi KBM -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-black text-slate-900 text-base">Rincian Presensi Mengajar KBM</h3>
                    <p class="text-xs text-slate-500">Daftar sesi tatap muka di kelas pada periode ini</p>
                </div>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 font-bold rounded-xl text-xs">
                    {{ $rekap['listKbm']->count() }} Sesi
                </span>
            </div>

            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 border-b font-bold uppercase text-[10px] text-slate-400 sticky top-0">
                        <tr>
                            <th class="px-3 py-2.5">Tanggal</th>
                            <th class="px-3 py-2.5">Mapel & Kelas</th>
                            <th class="px-3 py-2.5 text-center">Status</th>
                            <th class="px-3 py-2.5">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y font-medium text-slate-700">
                        @forelse($rekap['listKbm'] as $kbm)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($kbm->tanggal)->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $kbm->jam_absen }}</div>
                            </td>
                            <td class="px-3 py-2.5">
                                <div class="font-bold text-slate-800">{{ $kbm->jadwal->mataPelajaran->nama ?? 'Mapel' }}</div>
                                <div class="text-[11px] text-slate-500">Kelas {{ $kbm->jadwal->kelas ?? '-' }}</div>
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ $kbm->badge_class }}">
                                    {{ $kbm->status_label }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-slate-500 max-w-xs truncate">
                                {{ $kbm->catatan ?: '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-3 py-8 text-center text-slate-400">
                                Tidak ada log presensi KBM pada periode ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rincian Partisipasi Kegiatan Sekolah -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-black text-slate-900 text-base">Rincian Kehadiran Kegiatan Sekolah</h3>
                    <p class="text-xs text-slate-500">Agenda resmi pimpinan yang terlaksana pada periode ini</p>
                </div>
                <span class="px-2.5 py-1 bg-amber-50 text-amber-700 font-bold rounded-xl text-xs">
                    {{ $rekap['listKegiatan']->count() }} Agenda
                </span>
            </div>

            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 border-b font-bold uppercase text-[10px] text-slate-400 sticky top-0">
                        <tr>
                            <th class="px-3 py-2.5">Tanggal</th>
                            <th class="px-3 py-2.5">Agenda Kegiatan</th>
                            <th class="px-3 py-2.5 text-center">Status</th>
                            <th class="px-3 py-2.5">Tempat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y font-medium text-slate-700">
                        @forelse($rekap['listKegiatan'] as $keg)
                        @php
                            $pk = $keg->presensis->first();
                        @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($keg->tanggal_kegiatan)->format('d/m/Y') }}</div>
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase {{ $keg->bidang_color }}">
                                    {{ $keg->bidang_label }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5">
                                <div class="font-bold text-slate-800">{{ $keg->judul }}</div>
                                <div class="text-[10px] text-slate-400">Sasaran: {{ $keg->sasaran }}</div>
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                @if($pk)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ $pk->badge_class }}">
                                    {{ $pk->status_label }}
                                </span>
                                @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                    Belum Hadir
                                </span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-slate-500 whitespace-nowrap">
                                {{ $keg->tempat }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-3 py-8 text-center text-slate-400">
                                Tidak ada agenda kegiatan sekolah pada periode ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Inisialisasi Grafik Chart.js -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Grafik Tren Tahunan
    const ctxTren = document.getElementById('chartTrenTahunan');
    if (ctxTren) {
        new Chart(ctxTren, {
            type: 'bar',
            data: {
                labels: {!! json_encode($rekap['monthlyTrend']['labels']) !!},
                datasets: [
                    {
                        label: 'Hadir KBM (Sesi)',
                        data: {!! json_encode($rekap['monthlyTrend']['kbmHadir']) !!},
                        backgroundColor: '#3b82f6',
                        borderRadius: 6,
                    },
                    {
                        label: 'Hadir Kegiatan Sekolah',
                        data: {!! json_encode($rekap['monthlyTrend']['kegiatanHadir']) !!},
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                    },
                    {
                        label: '% Kehadiran',
                        data: {!! json_encode($rekap['monthlyTrend']['persentase']) !!},
                        type: 'line',
                        borderColor: '#f59e0b',
                        backgroundColor: '#f59e0b',
                        borderWidth: 2,
                        tension: 0.3,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Jumlah Sesi Hadir' }
                    },
                    y1: {
                        beginAtZero: true,
                        max: 100,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Persentase (%)' }
                    }
                }
            }
        });
    }

    // 2. Grafik Distribusi Status
    const ctxDist = document.getElementById('chartDistribusiStatus');
    if (ctxDist) {
        new Chart(ctxDist, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($rekap['statusDistribution']['labels']) !!},
                datasets: [{
                    data: {!! json_encode($rekap['statusDistribution']['data']) !!},
                    backgroundColor: {!! json_encode($rekap['statusDistribution']['colors']) !!},
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 10 } }
                    }
                }
            }
        });
    }
});
</script>
@endsection
