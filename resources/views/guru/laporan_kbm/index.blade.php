@extends('layouts.app')

@section('title', 'Laporan Realisasi KBM & Presensi Siswa')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-clipboard-data text-emerald-600"></i>
                <span>Laporan Realisasi KBM & Presensi Mengajar</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Catatan jurnal mengajar guru, evaluasi kesesuaian rencana, dan rekapitulasi kehadiran KBM</p>
        </div>
        <div>
            <a href="{{ route('guru.laporan-kbm.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-emerald-600/20 transition">
                <i class="bi-plus-circle text-sm"></i>
                <span>Input Laporan KBM Baru</span>
            </a>
        </div>
    </div>

    <!-- Tab Navigation: Laporan Realisasi KBM & Presensi Mengajar -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-px text-xs font-bold">
        <a href="{{ route('guru.laporan-kbm.index') }}" 
           class="px-4 py-2.5 border-b-2 border-emerald-600 text-emerald-700 flex items-center gap-2 bg-emerald-50/50 rounded-t-xl">
            <i class="bi-journal-check"></i>
            <span>Jurnal Realisasi KBM & Presensi Siswa</span>
        </a>
        <a href="{{ route('guru.absensi.index') }}" 
           class="px-4 py-2.5 text-slate-500 hover:text-slate-800 flex items-center gap-2 transition rounded-t-xl hover:bg-slate-50">
            <i class="bi-fingerprint"></i>
            <span>Riwayat Presensi Mengajar Guru</span>
        </a>
        <a href="{{ route('guru.rekap-presensi.index') }}" 
           class="px-4 py-2.5 text-slate-500 hover:text-slate-800 flex items-center gap-2 transition rounded-t-xl hover:bg-slate-50">
            <i class="bi-pie-chart-fill"></i>
            <span>Rekap Presensi (KBM & Kegiatan)</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4 text-xs">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Tanggal KBM</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Filter Kelas</label>
                <select name="kelas" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->nama_kelas ?? $k->nama }}" {{ $kelas === ($k->nama_kelas ?? $k->nama) ? 'selected' : '' }}>
                        {{ $k->nama_kelas ?? $k->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            @if($tanggal || $kelas)
            <div class="pt-4">
                <a href="{{ route('guru.laporan-kbm.index') }}" class="text-xs font-bold text-rose-600 hover:underline">Reset Filter</a>
            </div>
            @endif
        </form>
        <div class="text-slate-500">
            Total Laporan: <strong>{{ $laporans->total() }}</strong>
        </div>
    </div>

    <!-- Table List -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Tanggal & Waktu</th>
                        <th class="px-4 py-4">Mata Pelajaran & Kelas</th>
                        <th class="px-4 py-4 text-center">Kesesuaian Rencana</th>
                        <th class="px-4 py-4 text-center">Pelaksanaan Jadwal</th>
                        <th class="px-4 py-4 text-center">Siswa Hadir</th>
                        <th class="px-4 py-4 text-center">Tidak Hadir</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($laporans as $l)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 text-sm">
                                {{ \Carbon\Carbon::parse($l->tanggal_realisasi)->format('d M Y') }}
                            </div>
                            <div class="text-[11px] text-slate-400">
                                {{ $l->jadwal->hari ?? '-' }}, {{ substr($l->jadwal->jam_mulai ?? '',0,5) }}-{{ substr($l->jadwal->jam_selesai ?? '',0,5) }}
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-bold text-slate-800">{{ $l->jadwal->mataPelajaran->nama ?? 'Mata Pelajaran' }}</div>
                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[11px] font-bold rounded-md border border-indigo-200">
                                Kelas {{ $l->jadwal->kelas ?? '-' }}
                            </span>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->canManageAcademic())
                            <div class="text-[10px] text-slate-400 mt-1">Guru: {{ $l->guru?->name ?? '-' }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($l->kesesuaian_rencana === 'sesuai')
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-lg text-xs border border-emerald-300">
                                    Sesuai Rencana
                                </span>
                            @elseif($l->kesesuaian_rencana === 'sebagian')
                                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 font-bold rounded-lg text-xs border border-amber-300">
                                    Sebagian
                                </span>
                            @elseif($l->kesesuaian_rencana === 'materi_pengganti')
                                <span class="px-2.5 py-1 bg-blue-100 text-blue-800 font-bold rounded-lg text-xs border border-blue-300">
                                    Materi Pengganti
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-rose-100 text-rose-800 font-bold rounded-lg text-xs border border-rose-300">
                                    Tidak Sesuai / Tunda
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($l->status_pelaksanaan === 'sesuai_jadwal')
                                <span class="text-xs text-slate-600 font-semibold">Sesuai Jadwal</span>
                            @else
                                <span class="px-2 py-0.5 bg-purple-50 text-purple-700 font-bold rounded-md border border-purple-200 text-[11px]">
                                    {{ ucwords(str_replace('_', ' ', $l->status_pelaksanaan)) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-black rounded-lg border border-emerald-200">
                                {{ $l->jumlah_siswa_hadir }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="px-2.5 py-1 {{ $l->jumlah_siswa_tidak_hadir > 0 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-slate-50 text-slate-400' }} font-black rounded-lg border">
                                {{ $l->jumlah_siswa_tidak_hadir }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-1">
                            <a href="{{ route('guru.laporan-kbm.show', $l) }}" class="p-2 text-slate-600 hover:text-blue-600 rounded-lg hover:bg-slate-100 transition inline-block" title="Lihat Detail">
                                <i class="bi-eye text-base"></i>
                            </a>
                            <a href="{{ route('guru.laporan-kbm.print', $l) }}" target="_blank" class="p-2 text-slate-600 hover:text-emerald-600 rounded-lg hover:bg-slate-100 transition inline-block" title="Cetak Jurnal">
                                <i class="bi-printer text-base"></i>
                            </a>
                            <form action="{{ route('guru.laporan-kbm.destroy', $l) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus laporan KBM ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 transition">
                                    <i class="bi-trash text-base"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <i class="bi-journal-x text-3xl mb-2 inline-block"></i>
                            <p>Belum ada laporan KBM yang tersimpan.</p>
                            <a href="{{ route('guru.laporan-kbm.create') }}" class="mt-3 inline-block font-bold text-emerald-600 hover:underline">
                                + Input Laporan KBM Sekarang
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $laporans->links() }}
        </div>
    </div>

</div>
@endsection
