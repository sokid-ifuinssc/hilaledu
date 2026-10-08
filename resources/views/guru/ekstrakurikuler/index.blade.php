@extends('layouts.app')

@section('title', 'Ekstrakurikuler Binaan Saya')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white shadow-md shadow-indigo-200">
                    <i class="bi bi-award-fill text-lg"></i>
                </span>
                Ekstrakurikuler Binaan Saya
            </h1>
            <p class="text-sm text-slate-500 mt-1">Kelola anggota, tunjuk ketua eskul, input rencana & laporan kegiatan, absensi, serta penilaian eskul binaan Anda.</p>
        </div>
    </div>

    <!-- Alert Informasi Tugas Pembina -->
    <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-100 rounded-2xl p-4 flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0 text-sm shadow-sm">
            <i class="bi bi-info-circle-fill"></i>
        </div>
        <div class="text-xs text-indigo-900 leading-relaxed">
            <span class="font-bold">Panduan Pembina Eskul:</span> Sebagai pembina eskul, Anda dapat menunjuk salah satu siswa menjadi <b>Ketua Eskul</b> yang memiliki akses untuk mengabsen rekannya. Anda juga dapat menginput <b>Rencana Kegiatan</b>, membuat <b>Laporan Keterlaksanaan</b> (tanggal otomatis terisi dari rencana), mengabsen langsung siswa, serta menginput <b>Nilai Akhir Eskul</b> yang akan menyambung ke rapor & mapel project.
        </div>
    </div>

    <!-- Grid Card Eskul -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($eskuls as $eskul)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col overflow-hidden group">
                <div class="p-5 flex-1 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl font-bold group-hover:scale-105 transition">
                            <i class="bi bi-stars"></i>
                        </div>
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $eskul->is_aktif ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $eskul->is_aktif ? 'Aktif' : 'Non-aktif' }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-slate-800 group-hover:text-indigo-600 transition">
                            {{ $eskul->nama }}
                        </h3>
                        <p class="text-xs text-slate-500 line-clamp-2 mt-1">
                            {{ $eskul->deskripsi ?: 'Tidak ada deskripsi eskul.' }}
                        </p>
                    </div>

                    <!-- Info Jadwal & Tempat -->
                    <div class="space-y-1.5 pt-2 border-t border-slate-100 text-xs text-slate-600">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-calendar-event text-slate-400 w-4"></i>
                            <span>{{ $eskul->hari ?: 'Belum ditentukan' }}, {{ substr($eskul->jam_mulai,0,5) }} - {{ substr($eskul->jam_selesai,0,5) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-geo-alt text-slate-400 w-4"></i>
                            <span>{{ $eskul->tempat ?: 'Lokasi belum diset' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-person-badge text-amber-500 w-4"></i>
                            <span>Ketua: <b>{{ $eskul->ketuaSiswa->nama ?? 'Belum ditunjuk' }}</b></span>
                        </div>
                    </div>

                    <!-- Statistik Mini -->
                    <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-100 text-center">
                        <div class="p-2 rounded-xl bg-slate-50">
                            <span class="block text-base font-bold text-slate-800">{{ $eskul->anggotas_count }}</span>
                            <span class="text-[10px] text-slate-400">Anggota</span>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-50">
                            <span class="block text-base font-bold text-indigo-600">{{ $eskul->rencanas_count }}</span>
                            <span class="text-[10px] text-slate-400">Rencana</span>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-50">
                            <span class="block text-base font-bold text-emerald-600">{{ $eskul->laporans_count }}</span>
                            <span class="text-[10px] text-slate-400">Laporan</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-100">
                    <a href="{{ route('guru.ekstrakurikuler.show', $eskul->id) }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                        <span>Buka Kelola Eskul</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-3xl mx-auto mb-3">
                    <i class="bi bi-award"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">Anda Belum Ditugaskan Sebagai Pembina Eskul</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">Penugasan pembina eskul diatur oleh Administrator atau Waka Kesiswaan melalui menu Master Ekstrakurikuler.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
