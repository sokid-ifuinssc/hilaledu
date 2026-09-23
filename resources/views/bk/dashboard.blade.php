@extends('layouts.app')
@section('title', 'Dashboard BK')

@section('content')
<div class="space-y-6">

    {{-- Hero Banner BK --}}
    <x-dashboard-hero 
        icon="bi-shield-check" 
        badge="Monitoring Pelanggaran & Poin"
        title="Dashboard Bimbingan Konseling"
        description="Pantau perkembangan perilaku siswa, rekam pelanggaran, dan berikan poin penghargaan dengan cepat dan mudah." />

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-exclamation-triangle-fill text-red-600 text-lg"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Pelanggaran</div>
                <div class="text-2xl font-black text-slate-800 mt-0.5">{{ $stats['total_pelanggaran'] }}</div>
                <div class="text-[10px] text-slate-500">Tahun Ini</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-calendar-month-fill text-amber-600 text-lg"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Bulan Ini</div>
                <div class="text-2xl font-black text-amber-600 mt-0.5">{{ $stats['pelanggaran_bulan_ini'] }}</div>
                <div class="text-[10px] text-slate-500">Pelanggaran</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-hourglass-split text-orange-600 text-lg"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Dalam Proses</div>
                <div class="text-2xl font-black text-orange-600 mt-0.5">{{ $stats['dalam_proses'] }}</div>
                <div class="text-[10px] text-slate-500">Sedang Ditangani</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-rose-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-person-x-fill text-rose-600 text-lg"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Siswa Bermasalah</div>
                <div class="text-2xl font-black text-rose-600 mt-0.5">{{ $stats['siswa_bermasalah'] }}</div>
                <div class="text-[10px] text-slate-500">Poin &lt; 70</div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('bk.pelanggaran.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition shadow-sm shadow-indigo-200">
            <i class="bi-plus-circle-fill"></i>
            Input Pelanggaran Baru
        </a>
        <a href="{{ route('bk.laporan.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold rounded-xl border border-slate-200 transition">
            <i class="bi-bar-chart-line-fill"></i>
            Lihat Laporan
        </a>
    </div>

    {{-- Dua Kolom: Status Pipeline & Siswa Kritis --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Status Pipeline --}}
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="bi-kanban-fill text-indigo-600"></i>
                <h3 class="font-extrabold text-sm text-slate-900">Status Progres Penanganan</h3>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-100 text-center">
                    <p class="text-2xl font-black text-amber-700">{{ $stats['menunggu_approval'] }}</p>
                    <p class="text-xs text-amber-600 font-semibold mt-1">Menunggu Approval</p>
                </div>
                <div class="p-4 rounded-2xl bg-purple-50 border border-purple-100 text-center">
                    <p class="text-2xl font-black text-purple-700">{{ $stats['menunggu_laporan'] }}</p>
                    <p class="text-xs text-purple-600 font-semibold mt-1">Menunggu Laporan</p>
                </div>
                <div class="p-4 rounded-2xl bg-blue-50 border border-blue-100 text-center">
                    <p class="text-2xl font-black text-blue-700">{{ $stats['dalam_proses'] }}</p>
                    <p class="text-xs text-blue-600 font-semibold mt-1">Dalam Proses</p>
                </div>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-center">
                    <p class="text-2xl font-black text-emerald-700">{{ $stats['selesai'] }}</p>
                    <p class="text-xs text-emerald-600 font-semibold mt-1">Selesai</p>
                </div>
            </div>
        </div>

        {{-- Siswa Poin Terendah --}}
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="bi-person-exclamation text-rose-600"></i>
                <h3 class="font-extrabold text-sm text-slate-900">Siswa Poin Terendah</h3>
            </div>
            <div class="space-y-2.5">
                @forelse($siswaKritis->take(5) as $s)
                <div class="flex items-center justify-between p-3 rounded-2xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-black shrink-0
                            {{ $s->poin >= 70 ? 'bg-emerald-100 text-emerald-700' : ($s->poin >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                            {{ $s->poin }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ $s->nama_lengkap }}</p>
                            <p class="text-xs text-slate-500">{{ $s->kelas?->nama ?? '-' }}</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold
                        {{ $s->poin >= 70 ? 'bg-emerald-100 text-emerald-700' : ($s->poin >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                        {{ $s->status_poin }}
                    </span>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-6">
                    <i class="bi-check-circle-fill text-emerald-400 block text-2xl mb-2"></i>
                    Semua siswa dalam kondisi baik
                </p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Chart Tren Pelanggaran --}}
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
            <i class="bi-graph-up-arrow text-indigo-600"></i>
            <h3 class="font-extrabold text-sm text-slate-900">Tren Pelanggaran (6 Bulan Terakhir)</h3>
        </div>
        <div style="height: 280px;">
            <canvas id="chartPelanggaran"></canvas>
        </div>
    </div>

    {{-- Tabel Pelanggaran Terbaru --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="bi-clock-history text-rose-500"></i>
                <h3 class="font-extrabold text-sm text-slate-900">Pelanggaran Terbaru</h3>
            </div>
            <a href="{{ route('bk.pelanggaran.index') }}" class="text-xs text-indigo-600 font-semibold hover:underline flex items-center gap-1">
                Lihat Semua <i class="bi-arrow-right"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Pelanggaran</th>
                        <th>Poin</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pelanggaranTerbaru as $p)
                    <tr>
                        <td class="text-sm text-slate-600">{{ $p->tanggal_pelanggaran->format('d/m/Y') }}</td>
                        <td class="text-sm font-semibold text-slate-800">{{ $p->siswa->nama_lengkap }}</td>
                        <td class="text-sm text-slate-600">{{ $p->siswa?->kelas?->nama ?? '-' }}</td>
                        <td class="text-sm text-slate-600">{{ $p->jenisPelanggaran->nama }}</td>
                        <td><span class="text-rose-600 font-black text-sm">-{{ $p->poin }}</span></td>
                        <td><span class="badge {{ $p->status_badge }}">{{ $p->status_label }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-slate-400">
                            <i class="bi-check-circle-fill text-2xl text-emerald-400 block mb-2"></i>
                            Belum ada pelanggaran
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('chartPelanggaran');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(collect($chartData)->pluck('bulan')) !!},
                datasets: [{
                    label: 'Jumlah Pelanggaran',
                    data: {!! json_encode(collect($chartData)->pluck('jumlah')) !!},
                    backgroundColor: 'rgba(79, 70, 229, 0.85)',
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
</script>
@endpush
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">📊</span> Dashboard Guru BK</h3>
    <p>Dashboard ini menampilkan ringkasan pelanggaran siswa pada tahun ajaran aktif: total pelanggaran, pelanggaran bulan ini, status progres, dan jumlah siswa bermasalah.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Membaca Dashboard</h3>
    <ul>
        <li><strong>Total Pelanggaran</strong> — Jumlah seluruh pelanggaran yang tercatat di semester aktif</li>
        <li><strong>Bulan Ini</strong> — Pelanggaran yang terjadi pada bulan berjalan</li>
        <li><strong>Dalam Proses</strong> — Pelanggaran yang sedang dalam proses penanganan</li>
        <li><strong>Siswa Bermasalah</strong> — Siswa dengan poin &lt; 70 yang perlu perhatian khusus</li>
    </ul>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Guru BK dapat memantau kondisi pelanggaran secara real-time dan memantau progres penanganan melalui menu Progres Data Pelanggaran.</p>
</div>
@endsection
