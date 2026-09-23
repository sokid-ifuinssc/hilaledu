@extends('layouts.app')
@php $isWaka = auth()->user()->isWakaKesiswaan(); @endphp
@section('title', $isWaka ? 'Dashboard Waka Kesiswaan' : 'Dashboard Kepala Sekolah')

@section('content')
<div class="space-y-5">

    {{-- Hero Banner --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-violet-900 via-purple-950 to-slate-950 p-6 sm:p-8 text-white shadow-lg border border-violet-800/40">
        <div class="absolute inset-0 bg-gradient-to-r from-violet-950/95 via-purple-950/85 to-slate-950/90 pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-400 text-slate-950 flex items-center justify-center text-2xl shadow-lg shrink-0">
                    <i class="bi-bar-chart-line-fill"></i>
                </div>
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-500/20 text-violet-300 text-xs font-semibold border border-violet-500/30">
                        <i class="bi-shield-fill-check"></i>
                        <span>{{ $isWaka ? 'Waka Kesiswaan' : 'Kepala Sekolah' }} — Monitoring Siswa</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h1>
                    <p class="text-slate-300 text-sm">
                        Pantau perilaku siswa dan penanganan pelanggaran seluruh jurusan.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-mono font-bold text-purple-300 bg-white/10 px-3 py-2 rounded-xl border border-white/10 shrink-0">
                <i class="bi-clock-fill"></i>
                <span>{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 text-center hover:shadow-sm transition">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Siswa</div>
            <div class="text-2xl font-black text-indigo-700 mt-1">{{ $stats['total_siswa'] }}</div>
            <div class="text-[10px] text-slate-500 font-medium">Siswa Aktif</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 text-center hover:shadow-sm transition">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Pelanggaran</div>
            <div class="text-2xl font-black text-red-600 mt-1">{{ $stats['total_pelanggaran'] }}</div>
            <div class="text-[10px] text-slate-500 font-medium">Tahun Ini</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 text-center hover:shadow-sm transition">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Selesai</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['pelanggaran_selesai'] }}</div>
            <div class="text-[10px] text-slate-500 font-medium">Sudah Ditangani</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 text-center hover:shadow-sm transition">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Dalam Proses</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $stats['pelanggaran_proses'] }}</div>
            <div class="text-[10px] text-slate-500 font-medium">Sedang Ditangani</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 text-center hover:shadow-sm transition">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Jurusan</div>
            <div class="text-2xl font-black text-purple-600 mt-1">{{ $stats['total_jurusan'] }}</div>
            <div class="text-[10px] text-slate-500 font-medium">Program Keahlian</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 text-center hover:shadow-sm transition">
            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Kelas</div>
            <div class="text-2xl font-black text-teal-600 mt-1">{{ $stats['total_kelas'] }}</div>
            <div class="text-[10px] text-slate-500 font-medium">Rombel Aktif</div>
        </div>
    </div>

    {{-- Dua Kolom: Statistik & Tren --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Statistik per Jurusan --}}
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="bi-diagram-3-fill text-purple-600"></i>
                <h3 class="font-extrabold text-sm text-slate-900">Statistik Per Jurusan</h3>
            </div>
            <div class="space-y-3">
                @foreach($jurusanStats as $j)
                <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ $j->nama }}</p>
                        <p class="text-xs text-slate-500">{{ $j->siswas_count }} siswa aktif</p>
                    </div>
                    <div class="text-right">
                        <span class="text-base font-black text-red-600">{{ $j->total_pelanggaran }}</span>
                        <p class="text-[10px] text-slate-400 font-medium">pelanggaran</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Tren 6 Bulan --}}
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <i class="bi-bar-chart-fill text-indigo-600"></i>
                <h3 class="font-extrabold text-sm text-slate-900">Tren Pelanggaran 6 Bulan Terakhir</h3>
            </div>
            <div class="space-y-2.5">
                @php $maxJumlah = collect($chartData)->max('jumlah') ?: 1; @endphp
                @foreach($chartData as $data)
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-500 w-20 shrink-0">{{ $data['bulan'] }}</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-6 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-full rounded-full flex items-center justify-end pr-2 transition-all duration-500"
                             style="width: {{ ($data['jumlah'] / $maxJumlah) * 100 }}%; min-width: {{ $data['jumlah'] > 0 ? '2rem' : '0' }}">
                            @if($data['jumlah'] > 0)<span class="text-[10px] text-white font-black">{{ $data['jumlah'] }}</span>@endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Tabel Pelanggaran Terbaru --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
            <i class="bi-exclamation-triangle-fill text-rose-500"></i>
            <h3 class="font-extrabold text-sm text-slate-900">Pelanggaran Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>Pelanggaran</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pelanggaranTerbaru as $p)
                    <tr>
                        <td class="text-sm text-slate-600">{{ $p->tanggal_pelanggaran?->format('d/m/Y') }}</td>
                        <td class="text-sm font-semibold text-slate-800">{{ $p->siswa->nama_lengkap }}</td>
                        <td class="text-sm text-slate-600">{{ $p->siswa?->kelas?->nama ?? '-' }}</td>
                        <td class="text-sm text-slate-600">{{ $p->siswa?->kelas?->jurusan?->nama ?? '-' }}</td>
                        <td class="text-sm text-slate-600">{{ $p->jenisPelanggaran->nama ?? '-' }}</td>
                        <td><span class="badge {{ $p->status_badge }}">{{ $p->status_label }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-slate-400">
                            <i class="bi-check-circle-fill text-2xl text-emerald-400 block mb-2"></i>
                            Belum ada data pelanggaran
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #faf5ff; color: #7c3aed;">📊</span> Dashboard {{ $isWaka ? 'Waka Kesiswaan' : 'Kepala Sekolah' }}</h3>
    <p>Dashboard ini menampilkan ringkasan keseluruhan pelanggaran siswa dari semua jurusan dan kelas, termasuk statistik per jurusan dan tren 6 bulan terakhir.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Membaca Dashboard</h3>
    <ul>
        <li><strong>Total Siswa</strong> — Jumlah seluruh siswa aktif di sekolah</li>
        <li><strong>Total Pelanggaran</strong> — Seluruh pelanggaran di tahun ajaran aktif</li>
        <li><strong>Selesai</strong> — Pelanggaran yang sudah selesai ditangani</li>
        <li><strong>Dalam Proses</strong> — Pelanggaran yang masih dalam proses penanganan</li>
        <li><strong>Statistik Per Jurusan</strong> — Perbandingan pelanggaran antar jurusan</li>
        <li><strong>Tren 6 Bulan</strong> — Grafik batang pelanggaran 6 bulan terakhir</li>
    </ul>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Kepala Sekolah mendapatkan gambaran menyeluruh tentang kondisi perilaku siswa dan efektivitas penanganan pelanggaran.</p>
</div>
@endsection
