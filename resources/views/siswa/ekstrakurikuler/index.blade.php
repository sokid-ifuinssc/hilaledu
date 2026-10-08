@extends('layouts.app')

@section('title', 'Ekstrakurikuler Saya')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white shadow-md shadow-indigo-200">
                    <i class="bi bi-award text-lg"></i>
                </span>
                Ekstrakurikuler Saya
            </h1>
            <p class="text-sm text-slate-500 mt-1">Pantau keikutsertaan eskul, riwayat absensi, absen mandiri, dan lihat nilai akhir dari pembina eskul.</p>
        </div>
    </div>

    <!-- Alert jika Siswa adalah Ketua Eskul -->
    @if($eskulDiketuai && $eskulDiketuai->count() > 0)
        @foreach($eskulDiketuai as $eskulKetua)
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-2xl p-4 sm:p-5 shadow-lg shadow-amber-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-2xl font-bold">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-100">Penugasan Khusus Pembina</span>
                        <h3 class="text-lg font-bold">Anda Ditugaskan Menjadi Ketua {{ $eskulKetua->nama }}</h3>
                        <p class="text-xs text-amber-100">Anda memiliki amanah untuk mengabsen kehadiran teman-teman anggota eskul pada setiap pertemuan.</p>
                    </div>
                </div>
                <a href="{{ route('siswa.ekstrakurikuler.ketua-absensi', $eskulKetua->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-white text-amber-800 hover:bg-amber-50 transition shadow-sm whitespace-nowrap">
                    <i class="bi bi-check2-all"></i>
                    Buka Portal Absensi Ketua
                </a>
            </div>
        @endforeach
    @endif

    <!-- Daftar Eskul yang Diikuti -->
    <div class="space-y-6">
        @forelse($keanggotaans as $ang)
            @php
                $eskul = $ang->ekstrakurikuler;
                $stats = $ang->statistik_kehadiran;
                $persen = $ang->persentase_kehadiran;
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gradient-to-r from-slate-50 to-white">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl font-bold">
                            <i class="bi bi-stars"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-lg font-bold text-slate-800">{{ $eskul->nama ?? '-' }}</h2>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $ang->jabatan == 'ketua' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($ang->jabatan) }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Pembina: <b>{{ $eskul->pembina->name ?? 'Belum ada pembina' }}</b> • Jadwal: <b>{{ $eskul->hari ?: '-' }}</b> ({{ substr($eskul->jam_mulai,0,5) }} - {{ substr($eskul->jam_selesai,0,5) }})
                            </p>
                        </div>
                    </div>

                    <!-- Presensi Mandiri Button -->
                    <div>
                        <form action="{{ route('siswa.ekstrakurikuler.presensi-mandiri', $eskul->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                                <i class="bi bi-geo-alt-fill"></i>
                                Absen Mandiri Hari Ini
                            </button>
                        </form>
                    </div>
                </div>

                <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Kartu Nilai Eskul -->
                    <div class="bg-gradient-to-br from-indigo-50/50 to-blue-50/50 rounded-xl p-4 border border-indigo-100/70">
                        <span class="text-xs font-bold text-indigo-900 uppercase tracking-wider block mb-2">Nilai Dari Pembina</span>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white flex flex-col items-center justify-center shadow-md shadow-indigo-200">
                                <span class="text-2xl font-extrabold leading-none">{{ $ang->nilai_huruf ?: '-' }}</span>
                                <span class="text-[10px] text-indigo-200 mt-0.5">Predikat</span>
                            </div>
                            <div>
                                <span class="text-2xl font-bold text-slate-800">
                                    {{ $ang->nilai_angka !== null ? number_format($ang->nilai_angka, 1) : 'Belum Dinilai' }}
                                </span>
                                <p class="text-[11px] text-slate-500 mt-1">
                                    {{ $ang->catatan_nilai ?: 'Belum ada catatan deskripsi dari pembina eskul.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Statistik Kehadiran -->
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/70 flex flex-col justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-1">Tingkat Kehadiran</span>
                            <div class="flex items-center gap-3">
                                <span class="text-2xl font-bold text-slate-800">{{ $persen }}%</span>
                                <span class="text-xs text-slate-500">({{ $stats['hadir'] ?? 0 }} dari {{ $stats['total'] ?? $stats['total_sesi'] ?? 0 }} pertemuan)</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center text-[11px] pt-3 border-t border-slate-200">
                            <div><span class="font-bold text-blue-600">{{ $stats['izin'] ?? 0 }}</span> Izin</div>
                            <div><span class="font-bold text-amber-600">{{ $stats['sakit'] ?? 0 }}</span> Sakit</div>
                            <div><span class="font-bold text-rose-600">{{ $stats['alpa'] ?? 0 }}</span> Alpa</div>
                        </div>
                    </div>

                    <!-- Tempat & Rencana Terdekat -->
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/70 flex flex-col justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-1">Lokasi Latihan</span>
                            <p class="text-xs font-medium text-slate-800 flex items-center gap-1.5">
                                <i class="bi bi-geo-alt text-rose-500"></i>
                                {{ $eskul->tempat ?: 'Tempat belum ditentukan' }}
                            </p>
                            <p class="text-[11px] text-slate-500 mt-2 line-clamp-2">
                                {{ $eskul->deskripsi ?: 'Tidak ada keterangan tambahan.' }}
                            </p>
                        </div>
                        <div class="pt-2 border-t border-slate-200 text-[11px] text-indigo-600 font-semibold">
                            Tahun Ajaran: {{ $ang->tahun_ajaran }} ({{ ucfirst($ang->semester) }})
                        </div>
                    </div>
                </div>

                <!-- Riwayat Absensi Siswa di Eskul Ini -->
                <div class="border-t border-slate-100 p-5">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Riwayat Absensi Pertemuan Terakhir</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                                <tr>
                                    <th class="px-3 py-2">Tanggal</th>
                                    <th class="px-3 py-2 text-center">Status</th>
                                    <th class="px-3 py-2">Metode Absen</th>
                                    <th class="px-3 py-2">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($riwayatPresensi->where('ekstrakurikuler_id', $eskul->id)->take(5) as $pres)
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-slate-800 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($pres->tanggal)->format('d M Y') }}
                                        </td>
                                        <td class="px-3 py-2 text-center whitespace-nowrap">
                                            @if($pres->status == 'hadir')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Hadir</span>
                                            @elseif($pres->status == 'izin')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">Izin</span>
                                            @elseif($pres->status == 'sakit')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">Sakit</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">Alpa</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-slate-500 capitalize">
                                            {{ $pres->metode_absen }}
                                        </td>
                                        <td class="px-3 py-2 text-slate-400">
                                            {{ $pres->keterangan ?: '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-4 text-center text-slate-400">
                                            Belum ada catatan presensi pertemuan eskul ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-3xl mx-auto mb-3">
                    <i class="bi bi-award"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">Anda Belum Terdaftar di Ekstrakurikuler</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">Silakan hubungi pembina ekstrakurikuler atau waka kesiswaan untuk pendaftaran keanggotaan eskul.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
