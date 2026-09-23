@extends('layouts.app')
@section('title', 'Laporan Pelanggaran')
@section('subtitle', 'Pusat Pelaporan dan Rekapitulasi Data Pelanggaran Siswa')

@section('content')
<div class="space-y-8">
    
    {{-- Status Tahun Ajaran --}}
    @if($tahunAjaran)
    <div class="relative overflow-hidden bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 md:p-8 text-white shadow-xl shadow-indigo-200/50">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="font-medium text-indigo-100 mb-1">Tahun Ajaran Aktif</h3>
                <h2 class="text-3xl font-bold tracking-tight mb-2">{{ $tahunAjaran->nama }}</h2>
                <div class="flex items-center gap-2 text-indigo-100 text-sm bg-white/10 w-fit px-3 py-1.5 rounded-full backdrop-blur-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ $tahunAjaran->tanggal_mulai?->format('d M Y') }} &mdash; {{ $tahunAjaran->tanggal_selesai?->format('d M Y') }}</span>
                </div>
            </div>
            <div class="bg-white/20 p-4 rounded-xl backdrop-blur-md border border-white/30 text-center min-w-[140px]">
                <p class="text-xs uppercase tracking-wider font-semibold text-indigo-100 mb-1">Semester</p>
                <p class="text-2xl font-bold">{{ $tahunAjaran->semester }}</p>
            </div>
        </div>
    </div>
    @else
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-amber-800 flex items-start gap-4 shadow-sm">
        <div class="bg-amber-100 p-2 rounded-lg">
            <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
            <h3 class="font-bold text-lg mb-1">Perhatian: Belum ada Tahun Ajaran Aktif</h3>
            <p class="text-amber-700">Silakan hubungi administrator untuk mengaktifkan tahun ajaran agar data pelanggaran dapat dicatat dan direkap dengan benar.</p>
        </div>
    </div>
    @endif

    <div>
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Rekapitulasi Reguler
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Laporan Mingguan --}}
            <a href="{{ route('bk.laporan.mingguan') }}" class="group block bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-blue-100 hover:-translate-y-1 transition-all duration-300 overflow-hidden relative">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="p-6 relative z-10">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-200 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-800 text-lg mb-2 group-hover:text-blue-600 transition-colors">Mingguan</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Rekapitulasi data pelanggaran detail per minggu.</p>
                </div>
                <div class="h-1.5 w-full bg-blue-500 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-500"></div>
            </a>

            {{-- Laporan Bulanan --}}
            <a href="{{ route('bk.laporan.bulanan') }}" class="group block bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-emerald-100 hover:-translate-y-1 transition-all duration-300 overflow-hidden relative">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="p-6 relative z-10">
                    <div class="w-14 h-14 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-emerald-200 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-800 text-lg mb-2 group-hover:text-emerald-600 transition-colors">Bulanan</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Trend pelanggaran dan statistik rekap per bulan.</p>
                </div>
                <div class="h-1.5 w-full bg-emerald-500 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-500"></div>
            </a>

            {{-- Laporan Semester --}}
            <a href="{{ route('bk.laporan.semester') }}" class="group block bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-purple-100 hover:-translate-y-1 transition-all duration-300 overflow-hidden relative">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="p-6 relative z-10">
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-purple-200 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-800 text-lg mb-2 group-hover:text-purple-600 transition-colors">Semester</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Rekapitulasi utuh satu semester untuk rapor siswa.</p>
                </div>
                <div class="h-1.5 w-full bg-purple-500 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-500"></div>
            </a>

            {{-- Laporan Akhir Tahun --}}
            <a href="{{ route('bk.laporan.akhir-tahun') }}" class="group block bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-rose-100 hover:-translate-y-1 transition-all duration-300 overflow-hidden relative">
                <div class="absolute inset-0 bg-gradient-to-br from-rose-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="p-6 relative z-10">
                    <div class="w-14 h-14 bg-gradient-to-br from-rose-400 to-rose-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-rose-200 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-800 text-lg mb-2 group-hover:text-rose-600 transition-colors">Akhir Tahun</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Laporan komprehensif tahunan untuk pimpinan sekolah.</p>
                </div>
                <div class="h-1.5 w-full bg-rose-500 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-500"></div>
            </a>
        </div>
    </div>

    <div>
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2 mt-4">
            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Laporan Khusus
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
            {{-- Pra Ujian --}}
            <a href="{{ route('bk.laporan.pra-ujian') }}" class="group block bg-gradient-to-br from-amber-50 to-white rounded-2xl border border-amber-100 shadow-sm hover:shadow-xl hover:shadow-amber-100 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="p-6 md:p-8 flex items-start gap-6">
                    <div class="w-16 h-16 shrink-0 bg-gradient-to-br from-amber-400 to-amber-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-amber-200 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-xl mb-2 group-hover:text-amber-600 transition-colors">Laporan Pra Ujian (UTS/UAS)</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">Rekapitulasi khusus menjelang ujian untuk menentukan kelayakan siswa berdasarkan skor poin pelanggaran yang dimiliki.</p>
                        <div class="mt-4 flex items-center text-amber-600 text-sm font-semibold group-hover:translate-x-2 transition-transform duration-300">
                            Lihat Laporan <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">📊</span> Pusat Laporan</h3>
    <p>Halaman ini merupakan pusat laporan pelanggaran siswa. Pilih jenis laporan sesuai kebutuhan pelaporan ke pimpinan.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Jenis Laporan</h3>
    <ol>
        <li><strong>Mingguan</strong> — laporan pelanggaran per minggu</li>
        <li><strong>Bulanan</strong> — laporan pelanggaran per bulan</li>
        <li><strong>Semester</strong> — rekapitulasi lengkap per semester</li>
        <li><strong>Akhir Tahun</strong> — rekapitulasi seluruh tahun ajaran</li>
        <li><strong>Pra Ujian</strong> — rekapitulasi khusus kelayakan ujian</li>
    </ol>
</div>
@endsection
