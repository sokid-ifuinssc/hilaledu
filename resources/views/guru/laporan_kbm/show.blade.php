@extends('layouts.app')

@section('title', 'Detail Laporan KBM')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="text-xs text-slate-500 mb-1">
                <a href="{{ route('guru.laporan-kbm.index') }}" class="text-blue-600 hover:underline">&larr; Kembali ke Daftar Laporan KBM</a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Laporan KBM: {{ $laporanKbm->jadwal->mataPelajaran->nama ?? 'Mata Pelajaran' }}
            </h1>
            <p class="text-xs text-slate-500">Kelas {{ $laporanKbm->jadwal->kelas ?? '-' }} &bull; Tanggal Realisasi: {{ \Carbon\Carbon::parse($laporanKbm->tanggal_realisasi)->isoFormat('dddd, D MMMM Y') }} &bull; Guru: {{ $laporanKbm->guru?->name ?? '-' }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('guru.laporan-kbm.print', $laporanKbm) }}" target="_blank" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-sm transition">
                <i class="bi-printer text-sm"></i>
                <span>Cetak Jurnal KBM</span>
            </a>
        </div>
    </div>

    <!-- Overview Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6 text-xs">
        
        <!-- Status Badges -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3.5 bg-slate-50 rounded-2xl border">
                <div class="text-[10px] text-slate-400 font-bold uppercase">Kesesuaian Rencana</div>
                <div class="font-extrabold text-slate-900 text-xs mt-1 capitalize flex items-center gap-1.5">
                    @if($laporanKbm->kesesuaian_rencana === 'sesuai')
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    @elseif($laporanKbm->kesesuaian_rencana === 'sebagian')
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    @endif
                    <span>{{ str_replace('_', ' ', $laporanKbm->kesesuaian_rencana) }}</span>
                </div>
            </div>

            <div class="p-3.5 bg-slate-50 rounded-2xl border">
                <div class="text-[10px] text-slate-400 font-bold uppercase">Status Jadwal</div>
                <div class="font-extrabold text-slate-900 text-xs mt-1 capitalize">{{ str_replace('_', ' ', $laporanKbm->status_pelaksanaan) }}</div>
            </div>

            <div class="p-3.5 bg-emerald-50 rounded-2xl border border-emerald-200">
                <div class="text-[10px] text-emerald-700 font-bold uppercase">Siswa Hadir</div>
                <div class="text-xl font-black text-emerald-700 mt-1">{{ $laporanKbm->jumlah_siswa_hadir }} Siswa</div>
            </div>

            <div class="p-3.5 bg-rose-50 rounded-2xl border border-rose-200">
                <div class="text-[10px] text-rose-700 font-bold uppercase">Tidak Hadir</div>
                <div class="text-xl font-black text-rose-600 mt-1">{{ $laporanKbm->jumlah_siswa_tidak_hadir }} Siswa</div>
            </div>
        </div>

        <!-- Sinkronisasi dengan Rencana Pembelajaran (Modul Ajar / RPP) -->
        @if($laporanKbm->rencana)
        @php
            $r = $laporanKbm->rencana;
            $tp = $r->tujuanPembelajaran;
        @endphp
        <div class="p-5 bg-gradient-to-br from-blue-50/70 to-indigo-50/40 rounded-3xl border border-blue-200 space-y-3">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-lg bg-blue-600 text-white font-black text-[10px] uppercase tracking-wider">
                        Modul Ajar (RPP) Terkait
                    </span>
                    <h4 class="font-extrabold text-sm text-slate-900">
                        Pertemuan Ke-{{ $r->pertemuan_ke }}: {{ $r->materi_pokok }}
                    </h4>
                </div>
                <a href="{{ route('guru.rencana-pembelajaran.rpp.show', $r) }}" class="text-xs font-bold text-blue-700 hover:underline">
                    Buka RPP Lengkap &rarr;
                </a>
            </div>

            @if($tp)
            <div class="p-3 bg-white/80 rounded-2xl border border-blue-100 text-xs space-y-1">
                <div class="font-bold text-blue-900">
                    Target Tujuan Pembelajaran: <span class="font-mono">{{ $tp->kode_tp }}</span>
                </div>
                <p class="text-slate-700 text-[11px]">{{ $tp->deskripsi }}</p>
                @if($tp->kktp)
                <div class="text-[10.5px] text-emerald-800 font-semibold pt-1 border-t border-slate-100">
                    <strong>Target Kriteria Ketercapaian (KKTP):</strong> {{ $tp->kktp }}
                </div>
                @endif
            </div>
            @endif

            @if($laporanKbm->keterangan_kesesuaian)
            <div class="text-xs text-slate-800 bg-white/60 p-3 rounded-2xl border border-blue-100">
                <strong>Catatan Evaluasi / Ketercapaian Materi:</strong> {{ $laporanKbm->keterangan_kesesuaian }}
            </div>
            @endif
        </div>
        @endif

        @if($laporanKbm->keterangan_pelaksanaan)
        <div class="p-4 bg-purple-50 border border-purple-200 rounded-2xl text-purple-900">
            <strong>Keterangan Pelaksanaan:</strong> {{ $laporanKbm->keterangan_pelaksanaan }}
        </div>
        @endif

        <!-- Jurnal / Aktivitas -->
        <div class="space-y-2">
            <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Jurnal Realisasi Aktivitas KBM:</h3>
            <p class="text-slate-700 bg-slate-50 p-4 rounded-2xl border border-slate-100 whitespace-pre-line leading-relaxed">{{ $laporanKbm->catatan_kegiatan }}</p>
        </div>

        @if($laporanKbm->foto_dokumentasi)
        <div class="space-y-2">
            <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Dokumentasi KBM:</h3>
            <img src="{{ asset('storage/' . $laporanKbm->foto_dokumentasi) }}" alt="Dokumentasi KBM" class="max-h-72 rounded-2xl object-cover border shadow-sm">
        </div>
        @endif

        <!-- Daftar Kehadiran Siswa -->
        <div class="space-y-3 pt-2">
            <h3 class="font-black text-sm text-slate-900 border-b pb-2">Rincian Presensi Siswa ({{ $laporanKbm->presensiSiswa->count() }} Siswa)</h3>
            <div class="overflow-x-auto max-h-80 overflow-y-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 sticky top-0 font-bold uppercase text-[10px] text-slate-500 border-b">
                        <tr>
                            <th class="px-4 py-2.5">No</th>
                            <th class="px-4 py-2.5">Nama Siswa</th>
                            <th class="px-4 py-2.5 text-center">Status</th>
                            <th class="px-4 py-2.5">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($laporanKbm->presensiSiswa as $idx => $p)
                        <tr>
                            <td class="px-4 py-2 text-slate-400 font-bold">{{ $idx + 1 }}</td>
                            <td class="px-4 py-2 font-bold text-slate-900">{{ $p->nama_siswa }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase
                                    {{ $p->status === 'hadir' ? 'bg-emerald-100 text-emerald-800' : ($p->status === 'sakit' ? 'bg-purple-100 text-purple-800' : ($p->status === 'izin' ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800')) }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-slate-500">{{ $p->keterangan ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
