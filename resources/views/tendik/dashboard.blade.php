@extends('layouts.app')

@section('title', 'Beranda Tendik')

@section('content')
<div class="space-y-6" x-data="{
    kegiatanModal: false,
    selectedKegiatan: { id: null, judul: '', tanggal: '', jam: '', tempat: '', status: 'hadir', catatan: '' }
}">

    <!-- 1. Header Ringkas Tendik -->
    <div class="rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-950 p-5 sm:p-6 text-white shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-slate-800/80 relative overflow-hidden">
        <!-- Background Foto Gedung Sekolah Aksen Halus -->
        <div class="absolute inset-0 bg-cover bg-center opacity-15 mix-blend-luminosity pointer-events-none" style="background-image: url('{{ asset('images/gedung-sekolah-biru.jpg') }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/95 via-indigo-950/85 to-blue-950/90 pointer-events-none"></div>

        <div class="space-y-1 relative z-10">
            <h1 class="text-xl sm:text-2xl font-black tracking-tight">
                Selamat Datang, {{ auth()->user()->name }} 👋
            </h1>
            <p class="text-slate-300 text-xs font-medium">
                {{ \App\Models\JadwalPelajaran::getHariIndonesia() }}, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }} &bull; SMK Plus Al-Hilal Arjawinangun
            </p>
        </div>
        <div class="flex items-center gap-2 relative z-10">
            @if($isPetugasPiketHariIni)
            <span class="px-3 py-1.5 rounded-xl bg-amber-500/20 border border-amber-400/30 text-amber-300 text-xs font-bold flex items-center gap-1.5">
                <i class="bi-person-badge-fill"></i> Petugas Piket Hari Ini
            </span>
            @endif
            <div class="flex items-center gap-1.5 text-xs font-mono font-bold text-indigo-300 bg-white/10 px-3 py-1.5 rounded-xl border border-white/10">
                <i class="bi-clock-fill"></i>
                <span>{{ $nowTime }} WIB</span>
            </div>
        </div>
    </div>

    <!-- 2. Presensi Harian (Masuk & Pulang) -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                <i class="bi-fingerprint text-indigo-600 text-lg"></i>
                <span>Presensi Harian</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- KOLOM PRESENSI MASUK -->
            <div class="p-4 rounded-2xl bg-slate-50 border {{ $presensiHarianHariIni && $presensiHarianHariIni->jam_masuk ? 'border-emerald-300 bg-emerald-50/30' : 'border-slate-200' }} flex flex-col justify-between space-y-3">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase text-slate-700 flex items-center gap-1.5">
                            <i class="bi-door-open-fill text-emerald-600"></i> Presensi Masuk
                        </span>
                        @if($presensiHarianHariIni && $presensiHarianHariIni->jam_masuk)
                            <span class="px-2 py-0.5 rounded-lg text-xs font-bold {{ $presensiHarianHariIni->badge_masuk_class }}">
                                {{ $presensiHarianHariIni->status_masuk_label }}
                            </span>
                        @else
                            <span class="text-[11px] text-slate-400 font-medium">Batas: 07.30 WIB</span>
                        @endif
                    </div>
                    @if($presensiHarianHariIni && $presensiHarianHariIni->jam_masuk)
                    <div class="text-xs text-slate-600">
                        Waktu Masuk: <strong class="font-mono font-black text-slate-900">{{ substr($presensiHarianHariIni->jam_masuk, 0, 5) }} WIB</strong>
                        @if($presensiHarianHariIni->terlambat_masuk_menit > 0)
                            <span class="text-rose-600 font-bold ml-1">(Terlambat {{ $presensiHarianHariIni->terlambat_masuk_menit }}m)</span>
                        @endif
                    </div>
                    @else
                    <p class="text-xs text-slate-500">Presensi pagi hari (07.00 - 07.30 WIB).</p>
                    @endif
                </div>
                <div>
                    @if(!$presensiHarianHariIni || empty($presensiHarianHariIni->jam_masuk))
                    <form method="POST" action="{{ route('guru.presensi-harian.masuk') }}">
                        @csrf
                        <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition shadow-sm">
                            <i class="bi-fingerprint"></i>
                            <span>Klik Presensi Masuk</span>
                        </button>
                    </form>
                    @else
                    <div class="w-full py-2 px-3 bg-emerald-100/70 border border-emerald-300 text-emerald-900 font-bold rounded-xl text-xs flex items-center justify-center gap-1.5">
                        <i class="bi-check-circle-fill text-emerald-600"></i>
                        <span>Sudah Presensi Masuk</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- KOLOM PRESENSI PULANG -->
            <div class="p-4 rounded-2xl bg-slate-50 border {{ $presensiHarianHariIni && $presensiHarianHariIni->jam_pulang ? 'border-blue-300 bg-blue-50/30' : 'border-slate-200' }} flex flex-col justify-between space-y-3">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase text-slate-700 flex items-center gap-1.5">
                            <i class="bi-house-door-fill text-blue-600"></i> Presensi Pulang
                        </span>
                        @if($presensiHarianHariIni && $presensiHarianHariIni->jam_pulang)
                            <span class="px-2 py-0.5 rounded-lg text-xs font-bold {{ $presensiHarianHariIni->badge_pulang_class }}">
                                {{ $presensiHarianHariIni->status_pulang_label }}
                            </span>
                        @else
                            <span class="text-[11px] text-slate-400 font-medium">Mulai: 14.10 WIB</span>
                        @endif
                    </div>
                    @if($presensiHarianHariIni && $presensiHarianHariIni->jam_pulang)
                    <div class="text-xs text-slate-600">
                        Waktu Pulang: <strong class="font-mono font-black text-slate-900">{{ substr($presensiHarianHariIni->jam_pulang, 0, 5) }} WIB</strong>
                        @if($presensiHarianHariIni->status_pulang === 'pulang_cepat')
                            <span class="text-amber-700 font-bold ml-1">(Pulang Cepat)</span>
                        @endif
                    </div>
                    @else
                    <p class="text-xs text-slate-500">Presensi kepulangan resmi dimulai pukul 14.10 WIB.</p>
                    @endif
                </div>
                <div>
                    @if(!$presensiHarianHariIni || empty($presensiHarianHariIni->jam_pulang))
                    <form method="POST" action="{{ route('guru.presensi-harian.pulang') }}">
                        @csrf
                        @php $isBefore14 = now()->format('H:i:s') < '14:00:00'; @endphp
                        <button type="submit"
                                onclick="{{ $isBefore14 ? 'return confirm(\'Saat ini belum pukul 14.00 WIB. Presensi pulang sekarang akan dicatat sebagai Pulang Cepat. Lanjutkan?\');' : '' }}"
                                class="w-full py-2.5 px-4 {{ $isBefore14 ? 'bg-amber-600 hover:bg-amber-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition shadow-sm">
                            <i class="bi-box-arrow-right"></i>
                            <span>{{ $isBefore14 ? 'Presensi Pulang (Pulang Cepat)' : 'Klik Presensi Pulang' }}</span>
                        </button>
                    </form>
                    @else
                    <div class="w-full py-2 px-3 bg-blue-100/70 border border-blue-300 text-blue-900 font-bold rounded-xl text-xs flex items-center justify-center gap-1.5">
                        <i class="bi-check-circle-fill text-blue-600"></i>
                        <span>Sudah Presensi Pulang</span>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- 3. Panel Piket (hanya tampil jika Petugas Piket / canAccessPiket) -->
    @if($piketData)
    <div class="bg-white rounded-3xl p-5 sm:p-6 border-2 border-amber-300 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-amber-100 pb-3">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                </span>
                <i class="bi-shield-check text-amber-600 text-lg"></i>
                <span>Monitoring Kehadiran Guru (Piket)</span>
            </h2>
            <a href="{{ route('piket.index') }}" class="text-xs font-bold text-amber-700 hover:text-amber-900 border border-amber-300 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-xl transition flex items-center gap-1.5">
                <i class="bi-box-arrow-up-right"></i> Panel Piket Penuh
            </a>
        </div>

        <!-- Ringkasan Status -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-center">
                <div class="text-2xl font-black text-emerald-700">{{ $piketData['rekap']['hadir_masuk'] ?? 0 }}</div>
                <div class="text-[11px] font-bold text-emerald-800 mt-0.5">Hadir</div>
            </div>
            <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-center">
                <div class="text-2xl font-black text-amber-700">{{ ($piketData['rekap']['izin'] ?? 0) + ($piketData['rekap']['sakit'] ?? 0) }}</div>
                <div class="text-[11px] font-bold text-amber-800 mt-0.5">Izin / Sakit</div>
            </div>
            <div class="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-center">
                <div class="text-2xl font-black text-rose-700">{{ $piketData['rekap']['belum_hadir'] ?? 0 }}</div>
                <div class="text-[11px] font-bold text-rose-800 mt-0.5">Belum Hadir</div>
            </div>
            <div class="p-3 rounded-2xl bg-blue-50 border border-blue-200 text-center">
                <div class="text-2xl font-black text-blue-700">{{ $piketData['rekap']['tugas_luar'] ?? 0 }}</div>
                <div class="text-[11px] font-bold text-blue-800 mt-0.5">Tugas Luar</div>
            </div>
        </div>

        <!-- Notifikasi Izin Keluar (jika ada) -->
        @if($piketData['notifikasiIzinKeluar']->count() > 0)
        <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 space-y-2">
            <p class="text-xs font-black text-amber-900 flex items-center gap-1.5">
                <i class="bi-exclamation-triangle-fill text-amber-500"></i>
                {{ $piketData['notifikasiIzinKeluar']->count() }} Guru Laporan Izin Keluar Jeda
            </p>
            @foreach($piketData['notifikasiIzinKeluar']->take(3) as $notif)
            <div class="flex items-center justify-between text-xs bg-white rounded-xl px-3 py-2 border border-amber-100">
                <span class="font-bold text-slate-800">{{ $notif->guru?->name ?? '-' }}</span>
                <span class="text-amber-700 font-semibold truncate mx-2">{{ $notif->izin_keluar_alasan ?? 'Izin keluar' }}</span>
                <a href="{{ route('piket.index') }}" class="text-amber-600 font-bold hover:underline shrink-0">Tanggapi &rarr;</a>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Daftar Guru Belum Hadir (5 teratas) -->
        @php
            $belumHadirList = collect($piketData['items'])->filter(function($g) {
                return !($g['has_presensi_masuk'] ?? false);
            })->take(5);
        @endphp
        @if($belumHadirList->count() > 0)
        <div class="space-y-2">
            <p class="text-xs font-black text-slate-700 uppercase tracking-wider">Guru Belum Hadir</p>
            @foreach($belumHadirList as $g)
            <div class="flex items-center justify-between p-3 rounded-2xl bg-rose-50 border border-rose-200 text-xs">
            <span class="font-bold text-slate-900">{{ $g['guru']->name ?? '-' }}</span>
                <a href="{{ route('piket.index') }}" class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold transition text-[11px]">
                    Absenkan di Panel Piket
                </a>
            </div>
            @endforeach
            @if(collect($piketData['items'])->where('has_presensi_masuk', false)->count() > 5)
            <a href="{{ route('piket.index') }}" class="block text-center text-xs text-blue-600 font-bold hover:underline py-1">
                Lihat semua di Panel Piket &rarr;
            </a>
            @endif
        </div>
        @endif
    </div>
    @endif

    <!-- 4. Informasi Kegiatan Sekolah -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                <i class="bi-calendar-event text-amber-600 text-lg"></i>
                <span>Informasi Kegiatan Sekolah</span>
            </h2>
        </div>

        @if($kegiatanTerbaru->isEmpty())
        <div class="py-8 text-center text-slate-400">
            <i class="bi-calendar-x text-3xl text-slate-300 mb-1 block"></i>
            <p class="text-xs">Tidak ada agenda kegiatan sekolah terdekat.</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($kegiatanTerbaru as $k)
            @php
                $presensiKegiatan = $k->presensis->first();
                $tglKegiatan = \Carbon\Carbon::parse($k->tanggal_kegiatan)->startOfDay();
                $tglHariIni = \Carbon\Carbon::now()->startOfDay();
                $diffHari = (int) $tglHariIni->diffInDays($tglKegiatan, false);
            @endphp
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="space-y-1 flex-1">
                    <div class="font-extrabold text-slate-900 text-sm">{{ $k->judul }}</div>
                    <div class="text-slate-500 text-[11px] flex items-center gap-2 flex-wrap">
                        <span class="font-bold text-slate-700">
                            <i class="bi-calendar3 text-indigo-600 mr-1"></i>{{ \Carbon\Carbon::parse($k->tanggal_kegiatan)->isoFormat('D MMMM Y') }}
                        </span>
                        @if($k->waktu_mulai)
                        <span>&bull; {{ substr($k->waktu_mulai, 0, 5) }} - {{ substr($k->waktu_selesai, 0, 5) }} WIB</span>
                        @endif
                        @if($k->tempat)
                        <span>&bull; <i class="bi-geo-alt text-rose-500"></i> {{ $k->tempat }}</span>
                        @endif
                    </div>
                </div>
                <div class="shrink-0">
                    @if($presensiKegiatan)
                        <span class="px-2.5 py-1 rounded-xl text-xs font-bold border {{ $presensiKegiatan->badge_class }}">
                            {{ $presensiKegiatan->status_label }}
                        </span>
                    @elseif($diffHari === 0)
                        <button type="button"
                                @click="kegiatanModal = true; selectedKegiatan = {
                                    id: {{ $k->id }},
                                    judul: '{{ addslashes($k->judul) }}',
                                    tanggal: '{{ \Carbon\Carbon::parse($k->tanggal_kegiatan)->isoFormat('dddd, D MMMM Y') }}',
                                    jam: '{{ substr($k->waktu_mulai, 0, 5) }} - {{ substr($k->waktu_selesai, 0, 5) }} WIB',
                                    tempat: '{{ addslashes($k->tempat) }}',
                                    status: 'hadir',
                                    catatan: ''
                                }"
                                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow-sm">
                            <i class="bi-fingerprint"></i>
                            <span>Klik Hadir (Hari H)</span>
                        </button>
                    @elseif($diffHari > 0)
                        <button type="button" disabled
                                title="Presensi hanya dapat diisi pada hari H pelaksanaan."
                                class="px-3 py-1.5 bg-slate-100 text-slate-400 font-semibold rounded-xl text-xs cursor-not-allowed border border-slate-200">
                            <i class="bi-lock-fill mr-1"></i> H-{{ $diffHari }}
                        </button>
                    @else
                        <span class="px-2.5 py-1 bg-slate-100 text-slate-400 rounded-lg text-[11px] font-semibold">Selesai</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- MODAL POPUP: Presensi Kegiatan Sekolah -->
    <div x-show="kegiatanModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative"
             @click.outside="kegiatanModal = false">
            <button @click="kegiatanModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg text-lg"></i>
            </button>
            <div class="text-center mb-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-2 text-xl shadow-inner">
                    <i class="bi-calendar-check-fill"></i>
                </div>
                <h3 class="text-base font-black text-slate-900">Presensi Kegiatan Sekolah</h3>
                <p class="text-xs text-slate-600 mt-0.5 font-bold" x-text="selectedKegiatan ? selectedKegiatan.judul : ''"></p>
                <p class="text-[11px] text-slate-400" x-text="selectedKegiatan ? selectedKegiatan.tanggal + ' • ' + selectedKegiatan.jam : ''"></p>
            </div>
            <form method="POST" :action="'{{ url('guru/kegiatan') }}/' + (selectedKegiatan ? selectedKegiatan.id : '') + '/presensi'" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1.5">Pilih Status Kehadiran</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="p-2.5 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-emerald-500 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/80 transition flex flex-col items-center text-center font-bold">
                            <input type="radio" name="status" value="hadir" x-model="selectedKegiatan.status" class="text-emerald-600">
                            <span class="text-emerald-800 mt-1">✅ Hadir</span>
                        </label>
                        <label class="p-2.5 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-500 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/80 transition flex flex-col items-center text-center font-bold">
                            <input type="radio" name="status" value="izin" x-model="selectedKegiatan.status" class="text-blue-600">
                            <span class="text-blue-800 mt-1">📝 Izin</span>
                        </label>
                        <label class="p-2.5 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-purple-500 has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50/80 transition flex flex-col items-center text-center font-bold">
                            <input type="radio" name="status" value="sakit" x-model="selectedKegiatan.status" class="text-purple-600">
                            <span class="text-purple-800 mt-1">🏥 Sakit</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan (Opsional)</label>
                    <textarea name="catatan" rows="2" x-model="selectedKegiatan.catatan" placeholder="Keterangan hadir/alasan izin..." class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs"></textarea>
                </div>
                <div class="pt-2 flex items-center gap-2">
                    <button type="button" @click="kegiatanModal = false" class="w-1/3 py-2 bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 rounded-xl transition">Batal</button>
                    <button type="submit" class="w-2/3 py-2 bg-emerald-600 hover:bg-emerald-700 font-bold text-white rounded-xl transition shadow-sm">Simpan Presensi</button>
                </div>
            </form>
        </div>
    </div>

    <x-ekosistem-apps />

</div>
@endsection

