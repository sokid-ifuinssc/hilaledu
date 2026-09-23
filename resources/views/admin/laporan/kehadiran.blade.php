@extends('layouts.app')

@section('title', 'Laporan & Rekap Kehadiran Guru - HilalEdu')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-emerald-800 via-teal-800 to-slate-900 rounded-3xl p-6 text-white shadow-xl">
        <div>
            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-emerald-200">
                <i class="bi-fingerprint mr-1"></i> Data Kehadiran Guru
            </span>
            <h1 class="text-2xl md:text-3xl font-black mt-2 tracking-tight">Laporan Rekapitulasi Kehadiran Guru</h1>
            <p class="text-slate-300 text-xs md:text-sm mt-1 max-w-2xl">
                Memuat data presensi mengajar riil yang diinput oleh guru sesuai jadwal KBM dan evaluasi toleransi waktu 10 menit.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.laporan.kehadiran.print', request()->query()) }}" target="_blank" class="px-4 py-2.5 bg-amber-400 hover:bg-amber-500 text-slate-950 font-bold rounded-2xl text-xs flex items-center gap-2 shadow-lg shadow-amber-400/20 transition">
                <i class="bi-printer-fill text-sm"></i>
                <span>Cetak Rekap Resmi</span>
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-xs flex items-center gap-2">
                <i class="bi-funnel-fill text-emerald-600"></i>
                <span>Filter Laporan Kehadiran</span>
            </h3>
            @if(request('bulan') || request('guru_id') || request('status'))
            <a href="{{ route('admin.laporan.kehadiran.index') }}" class="text-xs font-bold text-rose-600 hover:underline flex items-center gap-1">
                <i class="bi-arrow-counterclockwise"></i> Reset Filter
            </a>
            @endif
        </div>

        <form method="GET" action="{{ route('admin.laporan.kehadiran.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Periode Bulan</label>
                <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Pilih Guru Pengajar</label>
                <select name="guru_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
                    <option value="">-- Semua Guru ({{ $gurus->count() }} Orang) --</option>
                    @foreach($gurus as $g)
                    <option value="{{ $g->id }}" {{ $guruId == $g->id ? 'selected' : '' }}>
                        {{ $g->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Status Kehadiran</label>
                <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
                    <option value="">-- Semua Status --</option>
                    <option value="hadir" {{ $status === 'hadir' ? 'selected' : '' }}>Hadir Tepat Waktu</option>
                    <option value="terlambat" {{ $status === 'terlambat' ? 'selected' : '' }}>Hadir Terlambat</option>
                    <option value="izin" {{ $status === 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ $status === 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="tugas_luar" {{ $status === 'tugas_luar' ? 'selected' : '' }}>Tugas Luar</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="bi-search"></i>
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Statistik Ringkasan -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 text-center">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="text-[11px] font-semibold text-slate-500">Total Sesi Diabsen</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalSesi }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Sesi KBM Guru</div>
        </div>
        <div class="bg-emerald-50/80 p-4 rounded-2xl border border-emerald-200 shadow-xs">
            <div class="text-[11px] font-semibold text-emerald-800">Tepat Waktu</div>
            <div class="text-2xl font-black text-emerald-700 mt-1">{{ $totalHadir }}</div>
            <div class="text-[10px] text-emerald-600 mt-0.5">Toleransi ≤ 10 mnt</div>
        </div>
        <div class="bg-amber-50/80 p-4 rounded-2xl border border-amber-200 shadow-xs">
            <div class="text-[11px] font-semibold text-amber-800">Terlambat</div>
            <div class="text-2xl font-black text-amber-700 mt-1">{{ $totalTerlambat }}</div>
            <div class="text-[10px] text-amber-600 mt-0.5">&gt; 10 menit</div>
        </div>
        <div class="bg-blue-50/80 p-4 rounded-2xl border border-blue-200 shadow-xs">
            <div class="text-[11px] font-semibold text-blue-800">Izin</div>
            <div class="text-2xl font-black text-blue-700 mt-1">{{ $totalIzin }}</div>
            <div class="text-[10px] text-blue-600 mt-0.5">Ada surat izin</div>
        </div>
        <div class="bg-rose-50/80 p-4 rounded-2xl border border-rose-200 shadow-xs">
            <div class="text-[11px] font-semibold text-rose-800">Sakit</div>
            <div class="text-2xl font-black text-rose-700 mt-1">{{ $totalSakit }}</div>
            <div class="text-[10px] text-rose-600 mt-0.5">Keterangan dokter</div>
        </div>
        <div class="bg-purple-50/80 p-4 rounded-2xl border border-purple-200 shadow-xs">
            <div class="text-[11px] font-semibold text-purple-800">% Kehadiran</div>
            <div class="text-2xl font-black text-purple-700 mt-1">{{ $persentase }}%</div>
            <div class="text-[10px] text-purple-600 mt-0.5">Tingkat Disiplin</div>
        </div>
    </div>

    <!-- Active Filter Info -->
    @if($selectedGuru)
    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between text-xs text-emerald-900">
        <div class="flex items-center gap-2">
            <i class="bi-person-check-fill text-lg text-emerald-700"></i>
            <div>
                Menampilkan rekap presensi guru: <span class="font-bold">{{ $selectedGuru->name }}</span>
                @if($selectedGuru->nip) (NIP: {{ $selectedGuru->nip }}) @endif
                &bull; Periode: <span class="font-bold">{{ \Carbon\Carbon::parse($bulan . '-01')->isoFormat('MMMM Y') }}</span>
            </div>
        </div>
        <a href="{{ route('admin.laporan.kehadiran.print', ['bulan' => $bulan, 'guru_id' => $selectedGuru->id]) }}" target="_blank" class="px-3 py-1 bg-emerald-700 text-white rounded-lg font-bold hover:bg-emerald-800 transition">
            <i class="bi-printer mr-1"></i> Cetak Rekap Guru Ini
        </a>
    </div>
    @endif

    <!-- Tabel Data Rekap Kehadiran -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="bi-list-check text-emerald-600"></i>
                <span>Log Riwayat Presensi Guru ({{ $riwayat->total() }} Sesi Terdata)</span>
            </h2>
            <div class="text-xs text-slate-500">
                Halaman {{ $riwayat->currentPage() }} dari {{ $riwayat->lastPage() }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Tanggal & Hari</th>
                        <th class="px-5 py-3">Guru Pengajar</th>
                        <th class="px-5 py-3">Mata Pelajaran & Kelas</th>
                        <th class="px-5 py-3 text-center">Jadwal KBM</th>
                        <th class="px-5 py-3 text-center">Waktu Absen</th>
                        <th class="px-5 py-3 text-center">Status Kehadiran</th>
                        <th class="px-5 py-3">Catatan / Bukti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($riwayat as $absen)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900">
                                {{ \Carbon\Carbon::parse($absen->tanggal)->isoFormat('dddd, D MMMM Y') }}
                            </div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $absen->tanggal }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900">{{ $absen->guru?->name ?? 'Guru Tidak Ditemukan' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $absen->guru->nip ?? ('@' . ($absen->guru->username ?? '')) }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-slate-800">
                                {{ $absen->jadwal->mataPelajaran->nama ?? 'Mapel Terjadwal' }}
                            </div>
                            <div class="text-[11px] text-emerald-700 font-bold">
                                Kelas {{ $absen->jadwal->kelas ?? '-' }}
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-center font-mono text-slate-700 font-semibold">
                            {{ substr($absen->jadwal->jam_mulai ?? '00:00', 0, 5) }} - {{ substr($absen->jadwal->jam_selesai ?? '00:00', 0, 5) }}
                        </td>
                        <td class="px-5 py-3.5 text-center font-mono font-bold text-slate-800">
                            {{ substr($absen->jam_absen, 0, 5) }} WIB
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($absen->status == 'hadir')
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-lg font-bold text-[11px] inline-flex items-center gap-1">
                                    <i class="bi-check-circle-fill"></i> Tepat Waktu
                                </span>
                            @elseif($absen->status == 'terlambat')
                                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 border border-amber-300 rounded-lg font-bold text-[11px] inline-flex items-center gap-1" title="Terlambat {{ $absen->terlambat_menit }} menit">
                                    <i class="bi-exclamation-triangle-fill"></i> Terlambat ({{ $absen->terlambat_menit }}m)
                                </span>
                            @elseif($absen->status == 'izin')
                                <span class="px-2.5 py-1 bg-blue-100 text-blue-800 border border-blue-300 rounded-lg font-bold text-[11px] inline-flex items-center gap-1">
                                    <i class="bi-info-circle-fill"></i> Izin
                                </span>
                            @elseif($absen->status == 'sakit')
                                <span class="px-2.5 py-1 bg-rose-100 text-rose-800 border border-rose-300 rounded-lg font-bold text-[11px] inline-flex items-center gap-1">
                                    <i class="bi-bandaid-fill"></i> Sakit
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-purple-100 text-purple-800 border border-purple-300 rounded-lg font-bold text-[11px] inline-flex items-center gap-1">
                                    <i class="bi-briefcase-fill"></i> Tugas Luar
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="text-[11px] text-slate-700 italic">
                                {{ $absen->catatan ?: '-' }}
                            </div>
                            @if($absen->lampiran_bukti)
                            <a href="{{ asset('storage/' . $absen->lampiran_bukti) }}" target="_blank" class="text-[10px] font-bold text-blue-600 hover:underline mt-1 inline-flex items-center gap-1">
                                <i class="bi-paperclip"></i> Lihat Lampiran
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <i class="bi-calendar-x text-4xl mb-2 block text-slate-300"></i>
                            Belum ada rekaman presensi guru untuk periode dan filter yang dipilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($riwayat->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $riwayat->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
