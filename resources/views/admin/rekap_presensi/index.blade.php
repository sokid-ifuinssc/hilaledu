@extends('layouts.app')

@section('title', 'Rekap Presensi Seluruh Dewan Guru')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-table text-emerald-600"></i>
                <span>Rekapitulasi Presensi Dewan Guru (KBM & Kegiatan)</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Panel pimpinan & pengelola untuk mengevaluasi tingkat kehadiran mengajar KBM serta keaktifan kegiatan sekolah seluruh dewan guru.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.rekap-presensi.print-all', ['bulan' => $bulan, 'tahun_ajaran' => $tahunAjaran]) }}" 
               target="_blank"
               class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-emerald-600/20 transition">
                <i class="bi-printer-fill text-sm"></i>
                <span>Cetak Rekapitulasi Keseluruhan (PDF)</span>
            </a>
        </div>
    </div>

    <!-- Tab Navigation: Laporan Pengelola -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-px text-xs font-bold">
        <a href="{{ route('admin.laporan.kbm.index') }}" 
           class="px-4 py-2.5 text-slate-500 hover:text-slate-800 flex items-center gap-2 transition rounded-t-xl hover:bg-slate-50">
            <i class="bi-file-earmark-check"></i>
            <span>Jurnal Realisasi KBM Guru</span>
        </a>
        <a href="{{ route('admin.laporan.kehadiran.index') }}" 
           class="px-4 py-2.5 text-slate-500 hover:text-slate-800 flex items-center gap-2 transition rounded-t-xl hover:bg-slate-50">
            <i class="bi-clock-history"></i>
            <span>Log Kehadiran Sesi Mengajar</span>
        </a>
        <a href="{{ route('admin.rekap-presensi.index') }}" 
           class="px-4 py-2.5 border-b-2 border-emerald-600 text-emerald-700 flex items-center gap-2 bg-emerald-50/50 rounded-t-xl">
            <i class="bi-trophy-fill"></i>
            <span>Rekap Presensi Komprehensif Dewan Guru</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4 text-xs">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Filter Per Guru (Opsional)</label>
                <select name="guru_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="">-- Seluruh Dewan Guru (Tabel Lengkap) --</option>
                    @foreach($gurus as $g)
                    <option value="{{ $g->id }}" {{ $guruId == $g->id ? 'selected' : '' }}>
                        {{ $g->name }} ({{ $g->nip ?: $g->username }})
                    </option>
                    @endforeach
                </select>
            </div>

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

            @if($guruId || ($periodeMode === 'tahun' && !empty($bulan)))
            <div class="pt-4">
                <a href="{{ route('admin.rekap-presensi.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">
                    Reset Filter
                </a>
            </div>
            @endif
        </form>

        <div class="text-slate-600 font-medium text-xs">
            Periode: <strong class="text-slate-900">{{ $bulan ? \Carbon\Carbon::parse($bulan . '-01')->isoFormat('MMMM Y') : "T.A. {$tahunAjaran}" }}</strong>
        </div>
    </div>

    <!-- School-wide Summary Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white rounded-3xl p-5 shadow-sm space-y-1">
            <span class="text-xs font-semibold text-emerald-100 block">Rata-Rata Kehadiran Sekolah</span>
            <div class="text-3xl font-black">{{ $rekapSemua['rataRataSekolah'] }}%</div>
            <div class="text-[11px] text-emerald-100 pt-1 border-t border-white/20">
                Akumulasi seluruh {{ $rekapSemua['totalGuru'] }} Tenaga Pendidik
            </div>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-bold text-slate-500 block">Total Dewan Guru</span>
            <div class="text-3xl font-black text-slate-800">{{ $rekapSemua['totalGuru'] }}</div>
            <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                Aktif mengampu KBM & penugasan
            </div>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-bold text-slate-500 block">Total Hadir Akumulasi</span>
            <div class="text-3xl font-black text-blue-700">{{ $rekapSemua['totalAkumulasiHadir'] }}</div>
            <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                Dari {{ $rekapSemua['totalAkumulasiSesi'] }} sesi KBM & kegiatan
            </div>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-xs space-y-1">
            <span class="text-xs font-bold text-slate-500 block">Agenda Sekolah Terlaksana</span>
            <div class="text-3xl font-black text-amber-700">{{ $rekapSemua['totalKegiatanPeriode'] }}</div>
            <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                Kegiatan resmi dalam periode ini
            </div>
        </div>
    </div>

    <!-- Jika Guru Spesifik Dipilih: Tampilkan Grafik & Rincian Guru Tersebut -->
    @if($guruDetail)
    <div class="p-6 bg-slate-900 text-white rounded-3xl space-y-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-700/60 pb-4">
            <div>
                <span class="px-2.5 py-0.5 rounded-md bg-amber-400 text-slate-950 font-black text-[10px] uppercase">Rincian Per Guru</span>
                <h3 class="text-xl font-black text-white mt-1">{{ $guruDetail['guru']->name }}</h3>
                <p class="text-xs text-slate-300">NIP: {{ $guruDetail['guru']->nip ?: '-' }} &bull; Jabatan: {{ $guruDetail['guru']->jabatan_utama ?: ($guruDetail['guru']->tugas_tambahan_label ?: 'Guru Pengajar') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.rekap-presensi.print-guru', $guruDetail['guru']->id) }}?bulan={{ $bulan }}&tahun_ajaran={{ $tahunAjaran }}" 
                   target="_blank" 
                   class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                    <i class="bi-printer-fill"></i>
                    <span>Cetak PDF Guru Ini</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-slate-900">
            <div class="p-4 bg-white rounded-2xl">
                <span class="text-xs font-bold text-slate-400 uppercase block">Persentase Total</span>
                <strong class="text-2xl font-black text-emerald-600">{{ $guruDetail['persenGabungan'] }}%</strong>
                <p class="text-[11px] text-slate-500 mt-0.5">Predikat: <strong>{{ $guruDetail['predikat'] }}</strong></p>
            </div>
            <div class="p-4 bg-white rounded-2xl">
                <span class="text-xs font-bold text-slate-400 uppercase block">Kehadiran Mengajar KBM</span>
                <strong class="text-2xl font-black text-blue-600">{{ $guruDetail['persenKbm'] }}%</strong>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $guruDetail['totalHadirKbm'] }} Hadir / {{ $guruDetail['totalSesiKbm'] }} Sesi</p>
            </div>
            <div class="p-4 bg-white rounded-2xl">
                <span class="text-xs font-bold text-slate-400 uppercase block">Kehadiran Kegiatan Sekolah</span>
                <strong class="text-2xl font-black text-amber-600">{{ $guruDetail['persenKegiatan'] }}%</strong>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $guruDetail['kegiatanHadir'] }} Hadir / {{ $guruDetail['totalKegiatan'] }} Agenda</p>
            </div>
        </div>

        <!-- Grafik Tren Guru Ini -->
        <div class="bg-slate-800/80 p-5 rounded-2xl border border-slate-700">
            <h4 class="font-bold text-white text-xs mb-3 flex items-center gap-2">
                <i class="bi-graph-up-arrow text-emerald-400"></i>
                <span>Tren Kehadiran 1 Tahun Ajaran - {{ $guruDetail['guru']->name }}</span>
            </h4>
            <div class="relative h-60">
                <canvas id="chartGuruDetail"></canvas>
            </div>
        </div>
    </div>
    @endif

    <!-- TABEL REKAPITULASI KESELURUHAN SELURUH DEWAN GURU -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden space-y-4 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h3 class="font-black text-slate-900 text-base">Tabel Rekapitulasi Kehadiran Seluruh Dewan Guru</h3>
                <p class="text-xs text-slate-500">Nomor, nama guru, jumlah kehadiran KBM, kegiatan sekolah, dan kalkulasi persentase resmi</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.rekap-presensi.print-all', ['bulan' => $bulan, 'tahun_ajaran' => $tahunAjaran]) }}" 
                   target="_blank" 
                   class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm shadow-emerald-600/20">
                    <i class="bi-printer-fill"></i>
                    <span>Cetak Lembar Resmi (PDF)</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 border-b font-bold uppercase text-[10px] text-slate-400">
                    <tr>
                        <th class="px-3 py-3 text-center">No</th>
                        <th class="px-3 py-3">Nama Guru</th>
                        <th class="px-3 py-3">NIP / Kode</th>
                        <th class="px-3 py-3 text-center">Hadir KBM</th>
                        <th class="px-3 py-3 text-center">Terlambat</th>
                        <th class="px-3 py-3 text-center">Izin/Sakit</th>
                        <th class="px-3 py-3 text-center">Hadir Kegiatan</th>
                        <th class="px-3 py-3 text-center">Total Hadir</th>
                        <th class="px-3 py-3 text-center">Total Sesi</th>
                        <th class="px-3 py-3 text-center">% Kehadiran</th>
                        <th class="px-3 py-3 text-center">Predikat</th>
                        <th class="px-3 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y font-medium text-slate-700">
                    @foreach($rekapSemua['rows'] as $row)
                    <tr class="hover:bg-slate-50/70 transition {{ $guruId == $row['guru_id'] ? 'bg-amber-50/60 font-bold' : '' }}">
                        <td class="px-3 py-3 text-center font-bold text-slate-400">{{ $row['no'] }}</td>
                        <td class="px-3 py-3">
                            <div class="font-extrabold text-slate-900">{{ $row['nama'] }}</div>
                            <div class="text-[10px] text-slate-400">{{ $row['jabatan'] }}</div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-slate-600 font-mono text-[11px]">
                            {{ $row['nip'] }}
                        </td>
                        <td class="px-3 py-3 text-center text-blue-700 font-bold">{{ $row['kbm_hadir'] }}</td>
                        <td class="px-3 py-3 text-center text-amber-700 font-bold">{{ $row['kbm_terlambat'] }}</td>
                        <td class="px-3 py-3 text-center text-rose-700 font-bold">{{ $row['kbm_izin_sakit'] }}</td>
                        <td class="px-3 py-3 text-center text-teal-700 font-bold">{{ $row['kegiatan_hadir'] }}</td>
                        <td class="px-3 py-3 text-center font-black text-slate-900">{{ $row['total_hadir'] }}</td>
                        <td class="px-3 py-3 text-center text-slate-500">{{ $row['total_sesi'] }}</td>
                        <td class="px-3 py-3 text-center font-black text-sm {{ $row['persentase'] >= 90 ? 'text-emerald-700' : ($row['persentase'] >= 75 ? 'text-blue-700' : 'text-rose-700') }}">
                            {{ $row['persentase'] }}%
                        </td>
                        <td class="px-3 py-3 text-center whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $row['badge_class'] }}">
                                {{ $row['predikat'] }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.rekap-presensi.index', ['guru_id' => $row['guru_id'], 'bulan' => $bulan, 'tahun_ajaran' => $tahunAjaran]) }}" 
                                   class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition" title="Lihat Grafik & Rincian">
                                    <i class="bi-bar-chart-fill"></i>
                                </a>
                                <a href="{{ route('admin.rekap-presensi.print-guru', $row['guru_id']) }}?bulan={{ $bulan }}&tahun_ajaran={{ $tahunAjaran }}" 
                                   target="_blank"
                                   class="p-1.5 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 rounded-lg transition" title="Cetak PDF Guru">
                                    <i class="bi-printer"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart Script -->
@if($guruDetail)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartGuruDetail');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($guruDetail['monthlyTrend']['labels']) !!},
                datasets: [
                    {
                        label: 'Hadir KBM (Sesi)',
                        data: {!! json_encode($guruDetail['monthlyTrend']['kbmHadir']) !!},
                        backgroundColor: '#38bdf8',
                        borderRadius: 6,
                    },
                    {
                        label: 'Hadir Kegiatan Sekolah',
                        data: {!! json_encode($guruDetail['monthlyTrend']['kegiatanHadir']) !!},
                        backgroundColor: '#34d399',
                        borderRadius: 6,
                    },
                    {
                        label: '% Kehadiran',
                        data: {!! json_encode($guruDetail['monthlyTrend']['persentase']) !!},
                        type: 'line',
                        borderColor: '#fbbf24',
                        backgroundColor: '#fbbf24',
                        borderWidth: 2,
                        tension: 0.3,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                        ticks: { color: '#cbd5e1' }
                    },
                    y1: {
                        beginAtZero: true,
                        max: 100,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { color: '#fbbf24' }
                    },
                    x: {
                        ticks: { color: '#cbd5e1' },
                        grid: { color: 'rgba(255, 255, 255, 0.05)' }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: '#f8fafc' }
                    }
                }
            }
        });
    }
});
</script>
@endif
@endsection
