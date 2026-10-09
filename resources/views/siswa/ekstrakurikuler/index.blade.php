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
                Ekstrakurikuler Siswa
            </h1>
            <p class="text-sm text-slate-500 mt-1">Pilih dan ikuti ekstrakurikuler, pantau jadwal latihan, lakukan absensi mandiri, serta lihat nilai akhir yang terintegrasi ke mapel Team Work Project & Project Pancasila.</p>
        </div>
    </div>

    <!-- Alert Sukses / Gagal -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-3 shadow-sm">
            <i class="bi bi-check-circle-fill text-lg text-emerald-600 shrink-0"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-3 shadow-sm">
            <i class="bi bi-exclamation-triangle-fill text-lg text-rose-600 shrink-0"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- Banner Integrasi Mapel TWP & Project Pancasila -->
    <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-slate-50 border border-blue-200/80 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5 text-blue-900 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xl shrink-0 shadow-sm shadow-blue-200">
            <i class="bi bi-link-45deg"></i>
        </div>
        <div class="text-xs">
            <h3 class="font-bold text-sm text-blue-950 flex items-center gap-2">
                <span>Integrasi Otomatis Mapel Team Work Project & Project Pancasila</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-200/70 text-blue-800 font-bold uppercase tracking-wider">Kelas X, XI, XII</span>
            </h3>
            <p class="text-blue-800/90 mt-1 leading-relaxed">
                Setiap kehadiran (baik absensi oleh pembina, ketua eskul, maupun absen mandiri) dan nilai akhir ekstrakurikuler yang diberikan pembina <strong>secara otomatis disinkronkan ke pertemuan KBM dan nilai mata pelajaran Team Work Project & Project Pancasila</strong> di kelas Anda masing-masing.
            </p>
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

    <!-- Daftar Eskul yang Sedang Diikuti -->
    <div class="space-y-6">
        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <i class="bi bi-bookmark-check-fill text-indigo-600"></i>
                Ekstrakurikuler yang Anda Ikuti
            </h2>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700">
                {{ $keanggotaans->count() }} Eskul Aktif
            </span>
        </div>

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

                    <div class="flex items-center gap-2">
                        <!-- Presensi Mandiri Button -->
                        <form action="{{ route('siswa.ekstrakurikuler.presensi-mandiri', $eskul->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                                <i class="bi bi-geo-alt-fill"></i>
                                Absen Mandiri
                            </button>
                        </form>
                        <!-- Keluar Eskul Button -->
                        <form action="{{ route('siswa.ekstrakurikuler.leave', $eskul->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan keikutsertaan dari ekstrakurikuler {{ $eskul->nama }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition">
                                <i class="bi bi-box-arrow-right"></i>
                                Keluar
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
                                            @if(strtolower($pres->status) == 'hadir')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Hadir</span>
                                            @elseif(strtolower($pres->status) == 'izin')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">Izin</span>
                                            @elseif(strtolower($pres->status) == 'sakit')
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
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="bi bi-award"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">Anda Belum Terdaftar di Ekstrakurikuler</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">Silakan pilih dan daftarkan diri Anda pada salah satu ekstrakurikuler yang tersedia pada katalog di bawah ini.</p>
            </div>
        @endforelse
    </div>

    <!-- Section Eksplorasi & Pilih Ekstrakurikuler Sekolah -->
    <div class="mt-10 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-200 pb-3">
            <div>
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-compass text-indigo-600"></i>
                    Pilihan Ekstrakurikuler Sekolah
                </h2>
                <p class="text-xs text-slate-500">Tersedia untuk seluruh siswa Kelas X, XI, dan XII. Pilih eskul sesuai minat dan bakat Anda.</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 text-slate-600 self-start sm:self-auto">
                {{ $semuaEskuls->count() }} Pilihan Eskul Aktif
            </span>
        </div>

        @php
            $terdaftarIds = $keanggotaans->pluck('ekstrakurikuler_id')->toArray();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($semuaEskuls as $item)
                @php
                    $isEnrolled = in_array($item->id, $terdaftarIds);
                @endphp
                <div class="bg-white rounded-2xl border {{ $isEnrolled ? 'border-emerald-300 ring-2 ring-emerald-500/20' : 'border-slate-200' }} shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="w-11 h-11 rounded-xl {{ $isEnrolled ? 'bg-emerald-50 text-emerald-600' : 'bg-indigo-50 text-indigo-600' }} flex items-center justify-center text-xl font-bold">
                                <i class="bi bi-award"></i>
                            </div>
                            @if($isEnrolled)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                    <i class="bi bi-check-circle-fill"></i> Diikuti
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">
                                    <i class="bi bi-people"></i> {{ $item->total_anggota ?? 0 }} Siswa
                                </span>
                            @endif
                        </div>

                        <h3 class="text-base font-bold text-slate-800">{{ $item->nama }}</h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">
                            {{ $item->deskripsi ?: 'Tidak ada deskripsi ekstrakurikuler.' }}
                        </p>

                        <div class="space-y-1.5 mt-4 pt-3 border-t border-slate-100 text-xs">
                            <div class="flex items-center gap-2 text-slate-600">
                                <i class="bi bi-person-workspace text-slate-400 w-4"></i>
                                <span class="truncate">Pembina: <b>{{ $item->pembina->name ?? 'Belum ditentukan' }}</b></span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-600">
                                <i class="bi bi-calendar3 text-slate-400 w-4"></i>
                                <span>Jadwal: <b>{{ $item->hari ?: '-' }}</b> ({{ substr($item->jam_mulai,0,5) }} - {{ substr($item->jam_selesai,0,5) }})</span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-600">
                                <i class="bi bi-geo-alt text-slate-400 w-4"></i>
                                <span class="truncate">Tempat: <b>{{ $item->tempat ?: '-' }}</b></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100">
                        @if($isEnrolled)
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs text-emerald-700 font-semibold">
                                    <i class="bi bi-check-all"></i> Anggota Aktif
                                </span>
                                <form action="{{ route('siswa.ekstrakurikuler.leave', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan keikutsertaan dari {{ $item->nama }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-700 hover:underline">
                                        Batal Ikut
                                    </button>
                                </form>
                            </div>
                        @else
                            <form action="{{ route('siswa.ekstrakurikuler.join', $item->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                                    <i class="bi bi-plus-circle-fill"></i>
                                    Pilih & Ikuti Eskul Ini
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-400">
                    Belum ada ekstrakurikuler yang dibuka untuk pendaftaran.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
