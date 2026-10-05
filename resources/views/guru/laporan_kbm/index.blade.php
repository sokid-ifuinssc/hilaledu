@extends('layouts.app')

@section('title', 'Laporan Realisasi KBM & Presensi Mengajar')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-clipboard-data text-emerald-600"></i>
                <span>Laporan Realisasi KBM & Presensi Mengajar</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Jadwal KBM dan tanggal mengajar tergenerate otomatis. Guru dapat langsung memantau sesi yang sudah maupun belum dilaporkan.
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('guru.laporan-kbm.rekap.print', ['bulan' => $bulan, 'guru_id' => $guruIdFilter, 'kelas' => $kelas, 'mapel_id' => $mapelId, 'status' => $statusFilter]) }}" target="_blank" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-blue-600/20 transition">
                <i class="bi-printer text-sm"></i>
                <span>Cetak Rekap KBM</span>
            </a>
            <a href="{{ route('guru.laporan-kbm.create') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-slate-800/20 transition">
                <i class="bi-plus-circle text-sm"></i>
                <span>Input Laporan Manual / Pengganti</span>
            </a>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-px text-xs font-bold">
        <a href="{{ route('guru.laporan-kbm.index') }}" 
           class="px-4 py-2.5 border-b-2 border-emerald-600 text-emerald-700 flex items-center gap-2 bg-emerald-50/50 rounded-t-xl">
            <i class="bi-journal-check"></i>
            <span>Jurnal Realisasi KBM & Presensi Siswa</span>
        </a>
        <a href="{{ route('guru.absensi.index') }}" 
           class="px-4 py-2.5 text-slate-500 hover:text-slate-800 flex items-center gap-2 transition rounded-t-xl hover:bg-slate-50">
            <i class="bi-fingerprint"></i>
            <span>Rekap Kehadiran Saya</span>
        </a>
    </div>

    <!-- Stat Scorecard -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-black flex-shrink-0">
                <i class="bi-calendar-event"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Sesi KBM</div>
                <div class="text-xl font-black text-slate-800">{{ $stat['total_sesi'] }} <span class="text-xs font-semibold text-slate-400">Sesi</span></div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-black flex-shrink-0">
                <i class="bi-check-circle"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sudah Dilaporkan</div>
                <div class="text-xl font-black text-emerald-600">{{ $stat['sudah_lapor'] }} <span class="text-xs font-semibold text-slate-400">Sesi</span></div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-black flex-shrink-0">
                <i class="bi-exclamation-triangle"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Belum Dilaporkan</div>
                <div class="text-xl font-black text-rose-600">{{ $stat['belum_lapor'] }} <span class="text-xs font-semibold text-slate-400">Sesi</span></div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-black flex-shrink-0">
                <i class="bi-pie-chart"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kepatuhan Laporan</div>
                <div class="text-xl font-black text-indigo-600">{{ $stat['persen_lapor'] }}%</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs text-xs">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
            <!-- Bulan -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Periode Bulan</label>
                <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
            </div>

            <!-- Filter Guru (Executive) -->
            @if($isExecutive)
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Filter Guru</label>
                <select name="guru_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="all" {{ $guruIdFilter === 'all' ? 'selected' : '' }}>-- Semua Guru (Monitoring) --</option>
                    @foreach($gurus as $g)
                    <option value="{{ $g->id }}" {{ $guruIdFilter == $g->id ? 'selected' : '' }}>
                        {{ $g->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Filter Kelas -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Filter Kelas</label>
                <select name="kelas" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->nama_kelas ?? $k->nama }}" {{ $kelas === ($k->nama_kelas ?? $k->nama) ? 'selected' : '' }}>
                        {{ $k->nama_kelas ?? $k->nama }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Pelaporan -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Status Pelaporan</label>
                <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="belum_lapor" {{ $statusFilter === 'belum_lapor' ? 'selected' : '' }}>Belum Dilaporkan (Perlu Isi)</option>
                    <option value="sudah_lapor" {{ $statusFilter === 'sudah_lapor' ? 'selected' : '' }}>Sudah Dilaporkan</option>
                </select>
            </div>

            <!-- Tombol Reset / Submit -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition">
                    Terapkan
                </button>
                @if($kelas || $mapelId || ($isExecutive && $guruIdFilter !== 'all') || $statusFilter !== 'all')
                <a href="{{ route('guru.laporan-kbm.index', ['bulan' => $bulan]) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-rose-600 font-bold rounded-xl text-xs transition">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Slot KBM -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between text-xs">
            <div class="font-bold text-slate-700">
                Jadwal KBM Bulan: <span class="text-blue-600 font-black">{{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->isoFormat('MMMM Y') }}</span>
                @if($selectedGuru)
                    &bull; Guru: <span class="text-slate-900 font-bold">{{ $selectedGuru->name }}</span>
                @elseif($isExecutive && $guruIdFilter === 'all')
                    &bull; Menampilkan: <span class="text-indigo-600 font-bold">Seluruh Guru & Mata Pelajaran</span>
                @endif
            </div>
            <div class="text-slate-400">
                Menampilkan <strong>{{ count($slots) }}</strong> slot pembelajaran
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal & Hari</th>
                        <th class="px-4 py-3.5">Jam & Waktu</th>
                        <th class="px-4 py-3.5">Kelas & Ruang</th>
                        <th class="px-4 py-3.5">Mata Pelajaran</th>
                        @if($isExecutive && $guruIdFilter === 'all')
                        <th class="px-4 py-3.5">Guru Pengampu</th>
                        @endif
                        <th class="px-4 py-3.5">Status Pelaporan & Keterangan</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($slots as $slot)
                    @php
                        $tglCarbon = $slot['carbon'];
                        $isToday = $tglCarbon->isToday();
                        $j = $slot['jadwal'];
                        $lap = $slot['laporan'];
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition {{ $isToday ? 'bg-amber-50/30' : '' }}">
                        <!-- Tanggal & Hari -->
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                                <span>{{ $tglCarbon->isoFormat('DD MMM Y') }}</span>
                                @if($isToday)
                                    <span class="px-1.5 py-0.5 rounded bg-amber-400 text-slate-950 font-black text-[9px] uppercase tracking-wider">Hari Ini</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-500 font-medium">
                                {{ $slot['hari'] }}
                                @if($slot['is_libur'])
                                    <span class="text-rose-500 text-[10px] font-bold">({{ $slot['libur_ket'] }})</span>
                                @endif
                            </div>
                        </td>

                        <!-- Jam & Waktu -->
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-800">
                                Jam Ke-{{ $j->jam_ke_mulai }}{{ $j->jam_ke_selesai > $j->jam_ke_mulai ? ' - ' . $j->jam_ke_selesai : '' }}
                            </div>
                            <div class="text-[11px] text-slate-400">
                                {{ substr($j->jam_mulai,0,5) }} - {{ substr($j->jam_selesai,0,5) }} WIB
                            </div>
                        </td>

                        <!-- Kelas & Ruang -->
                        <td class="px-4 py-3.5">
                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-200 inline-block">
                                {{ $j->kelas }}
                            </span>
                            @if($j->ruang)
                            <div class="text-[10px] text-slate-400 mt-1 font-medium">
                                {{ $j->ruang }}
                            </div>
                            @endif
                        </td>

                        <!-- Mata Pelajaran -->
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">{{ $j->mataPelajaran->nama ?? 'Mata Pelajaran' }}</div>
                            <div class="text-[10px] text-slate-400">Kode: {{ $j->mataPelajaran->kode ?? '-' }}</div>
                        </td>

                        <!-- Guru Pengampu (Jika Mode Monitoring Seluruh Guru) -->
                        @if($isExecutive && $guruIdFilter === 'all')
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-800">{{ $j->guru->name ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400">NIP/NUPTK: {{ $j->guru->nip ?? '-' }}</div>
                        </td>
                        @endif

                        <!-- Status Pelaporan KBM -->
                        <td class="px-4 py-3.5">
                            @if($slot['status'] === 'sudah_lapor')
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px] flex items-center gap-1 border border-emerald-300">
                                        <i class="bi-check-circle-fill"></i> Sudah Dilaporkan
                                    </span>
                                    <span class="text-[11px] text-slate-500 font-medium">
                                        {{ ucfirst(str_replace('_', ' ', $lap->kesesuaian_rencana)) }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-700 line-clamp-1">
                                    <strong>Materi:</strong> {{ $lap->rencana ? "Pertemuan Ke-{$lap->rencana->pertemuan_ke}: {$lap->rencana->materi_pokok}" : ($lap->catatan_kegiatan ?: 'KBM Terlaksana') }}
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5">
                                    Siswa Hadir: <strong class="text-emerald-700">{{ $lap->jumlah_siswa_hadir }}</strong> &bull; Tidak Hadir: <strong class="text-rose-600">{{ $lap->jumlah_siswa_tidak_hadir }}</strong>
                                </div>
                            @elseif($slot['status'] === 'belum_lapor')
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2.5 py-0.5 bg-rose-100 text-rose-800 font-black rounded-full text-[10px] flex items-center gap-1 border border-rose-300">
                                        <i class="bi-exclamation-circle-fill"></i> Belum Dilaporkan
                                    </span>
                                </div>
                                <p class="text-[10px] text-rose-600 mt-1 font-medium">
                                    Silakan isi laporan jurnal & presensi siswa untuk sesi ini.
                                </p>
                            @else
                                <span class="px-2.5 py-0.5 bg-slate-100 text-slate-500 font-bold rounded-full text-[10px] flex items-center gap-1 border border-slate-200 inline-flex">
                                    <i class="bi-clock"></i> Jadwal Mendatang
                                </span>
                            @endif
                        </td>

                        <!-- Aksi -->
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            @if($slot['status'] === 'sudah_lapor')
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('guru.laporan-kbm.show', $lap->id) }}" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition" title="Lihat Detail Jurnal">
                                        <i class="bi-eye text-sm"></i>
                                    </a>
                                    <a href="{{ route('guru.laporan-kbm.print', $lap->id) }}" target="_blank" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold transition" title="Cetak Jurnal Harian">
                                        <i class="bi-printer text-sm"></i>
                                    </a>
                                    @if(auth()->user()->id === $lap->guru_user_id || auth()->user()->isSuperAdmin())
                                    <form action="{{ route('guru.laporan-kbm.destroy', $lap->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus laporan KBM ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs transition" title="Hapus Laporan">
                                            <i class="bi-trash text-sm"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            @else
                                <!-- Tombol Cepat: Langsung Isi Laporan dengan Tanggal & Jadwal Terkunci Otomatis -->
                                <a href="{{ route('guru.laporan-kbm.create', ['jadwal_id' => $j->id, 'tanggal' => $slot['tanggal']]) }}" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-xs transition">
                                    <i class="bi-pencil-square"></i>
                                    <span>Isi Laporan</span>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ ($isExecutive && $guruIdFilter === 'all') ? 7 : 6 }}" class="px-6 py-12 text-center text-slate-400">
                            <i class="bi-calendar-x text-3xl mb-2 inline-block"></i>
                            <p class="font-bold text-slate-600">Tidak ada slot jadwal KBM pada periode ini.</p>
                            <p class="text-xs text-slate-400 mt-0.5">Pastikan jadwal pelajaran guru sudah diploting di menu kurikulum/jadwal.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
