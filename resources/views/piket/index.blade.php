@extends('layouts.app')

@section('title', 'Pusat Kendali Petugas Piket - Presensi Dewan Guru')

@section('content')
<div class="space-y-6" x-data="{
    modalAbsen: false,
    modalStatus: false,
    modalIzinKeluar: false,
    tugasModal: false,
    selectedGuru: { id: null, name: '' },
    selectedTugasJadwal: { id: null, mapel: '', kelas: '', deskripsi: '', guruName: '' },
    selectedPresensi: null,
    searchQuery: '',
    filterStatus: 'all'
}">

    <!-- 1. Hero Header Petugas Piket -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-900 p-6 sm:p-8 text-white shadow-xl">
        <div class="absolute right-0 top-0 -mt-10 -mr-10 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-36 bottom-0 -mb-10 w-64 h-64 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-xs font-bold border border-amber-500/30">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    <span>Pusat Kendali Petugas Piket Sekolah</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight flex items-center gap-2.5">
                    <span>Monitoring & Presensi Guru</span>
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm max-w-2xl leading-relaxed">
                    Wewenang khusus Petugas Piket (Tendik & Guru) untuk mengabsenkan kehadiran dewan guru yang terkendala teknis, mengelola izin/sakit, serta memantau izin keluar jeda KBM.
                </p>

                <!-- Petugas Piket Hari Ini Badge -->
                <div class="pt-2 flex items-center gap-2 flex-wrap text-xs">
                    <span class="text-slate-400 font-medium">Petugas Piket Hari Ini:</span>
                    @forelse($petugasHariIni as $p)
                    <span class="px-3 py-1 rounded-xl bg-white/10 text-white font-bold border border-white/15 flex items-center gap-1.5 shadow-2xs">
                        <i class="bi-person-badge-fill text-amber-400"></i>
                        <span>{{ $p->user->name }}</span>
                        <span class="text-[10px] uppercase font-mono px-1.5 py-0.2 bg-white/15 rounded text-amber-300">
                            {{ $p->user->role === 'tendik' ? 'Tendik' : 'Guru' }}
                        </span>
                    </span>
                    @empty
                    <span class="text-amber-300 italic text-xs">Belum ada jadwal piket tetap terdaftar untuk hari ini.</span>
                    @endforelse
                </div>
            </div>

            <!-- Jam Digital Real-Time & Tombol Cetak -->
            <div class="flex flex-col sm:items-end gap-3 flex-shrink-0">
                <div class="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10 text-right">
                    <div class="text-xs text-amber-300 font-bold uppercase tracking-wider">
                        {{ $hari }}, {{ \Carbon\Carbon::parse($tanggal)->isoFormat('D MMMM Y') }}
                    </div>
                    <div class="text-2xl sm:text-3xl font-mono font-black text-white mt-0.5" id="piket-clock">
                        {{ now()->format('H:i:s') }} WIB
                    </div>
                    <div class="text-[10px] text-slate-300 mt-0.5">Masuk: 07.00 - 07.30 &bull; Pulang: Mulai 14.10</div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('admin.jadwal.matrix') }}" 
                       class="px-4 py-2 bg-rose-600/80 hover:bg-rose-600 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition border border-rose-400/40 shadow-xs">
                        <i class="bi-file-earmark-spreadsheet-fill text-rose-200"></i>
                        <span>Matriks Jadwal KBM</span>
                    </a>
                    <a href="{{ route('piket.print', ['tanggal' => $tanggal]) }}" target="_blank"
                       class="px-4 py-2 bg-white/15 hover:bg-white/25 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition border border-white/20 shadow-xs">
                        <i class="bi-printer-fill text-amber-400"></i>
                        <span>Cetak Laporan Piket</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Kotak Notifikasi Izin Keluar (Guru Jam ke-3+ Yang Izin Keluar Saat Jeda KBM) -->
    @if($notifikasiIzinKeluar->isNotEmpty())
    <div class="bg-gradient-to-r from-amber-500/15 via-rose-500/10 to-amber-500/15 border-2 border-amber-400/80 rounded-3xl p-5 shadow-md">
        <div class="flex items-center justify-between gap-3 border-b border-amber-300/60 pb-3 mb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-2xl bg-amber-500 text-slate-950 flex items-center justify-center text-xl font-black shadow-sm">
                    <i class="bi-exclamation-octagon-fill"></i>
                </div>
                <div>
                    <h3 class="font-black text-slate-900 text-base flex items-center gap-2">
                        <span>Pemberitahuan Izin Keluar Sementara Guru (Jeda KBM)</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-black bg-rose-600 text-white">
                            {{ $notifikasiIzinKeluar->count() }} Guru
                        </span>
                    </h3>
                    <p class="text-xs text-slate-600">Guru yang jam mengajarnya jam ke-3 ke atas dan telah hadir masuk pagi, namun mengajukan izin keluar sementara.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($notifikasiIzinKeluar as $notif)
            <div class="bg-white rounded-2xl p-4 border border-amber-300 shadow-xs space-y-2.5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-black text-slate-900 text-sm truncate" title="{{ $notif->guru?->name }}">
                            {{ $notif->guru?->name }}
                        </span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase {{ $notif->izin_keluar_status === 'disetujui_piket' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800 animate-pulse' }}">
                            {{ $notif->izin_keluar_status === 'disetujui_piket' ? 'Diketahui Piket' : 'Menunggu Diketahui' }}
                        </span>
                    </div>

                    <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-950 mt-1 space-y-1">
                        <div class="font-bold flex items-center gap-1 text-[11px] text-amber-800">
                            <i class="bi-info-circle-fill text-amber-600"></i> Alasan Izin:
                        </div>
                        <p class="italic text-slate-800">"{{ $notif->izin_keluar_alasan ?: '-' }}"</p>
                        <div class="pt-1 flex items-center justify-between text-[11px] text-slate-600 border-t border-amber-200/60 font-semibold">
                            <span>Mulai: <strong>{{ substr($notif->izin_keluar_jam_mulai, 0, 5) }} WIB</strong></span>
                            <span>Kembali: <strong>{{ $notif->izin_keluar_jam_kembali ? substr($notif->izin_keluar_jam_kembali, 0, 5) . ' WIB' : 'Sebelum KBM' }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="pt-1 flex items-center gap-2">
                    <form method="POST" action="{{ route('piket.izin-keluar.tanggapi', $notif) }}" class="w-full flex items-center gap-2">
                        @csrf
                        @if($notif->izin_keluar_status !== 'disetujui_piket')
                        <button type="submit" name="status" value="disetujui_piket"
                                class="w-full py-1.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-1 shadow-xs">
                            <i class="bi-check-circle-fill"></i>
                            <span>Tandai Diketahui</span>
                        </button>
                        @else
                        <div class="w-full py-1 px-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-center rounded-xl text-xs font-bold">
                            ✅ Telah Diketahui Petugas Piket
                        </div>
                        @endif
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- 3. KPI Ringkasan Presensi Dewan Guru Hari Ini -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
        <!-- Total Guru -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-2xs">
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Guru</span>
            <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $rekap['total_guru'] }}</div>
            <span class="text-[10px] text-slate-500 font-medium">Aktif Sekolah</span>
        </div>

        <!-- Hadir Masuk -->
        <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-200 shadow-2xs">
            <span class="text-[10px] uppercase font-bold text-emerald-700 block">Hadir Tepat</span>
            <div class="text-2xl font-black text-emerald-700 mt-0.5">{{ $rekap['hadir_masuk'] }}</div>
            <span class="text-[10px] text-emerald-600 font-medium">&le; 07.30 WIB</span>
        </div>

        <!-- Terlambat -->
        <div class="bg-amber-50 rounded-2xl p-4 border border-amber-300 shadow-2xs">
            <span class="text-[10px] uppercase font-bold text-amber-800 block">Terlambat</span>
            <div class="text-2xl font-black text-amber-700 mt-0.5">{{ $rekap['terlambat'] }}</div>
            <span class="text-[10px] text-amber-700 font-medium">&gt; 07.30 WIB</span>
        </div>

        <!-- Izin -->
        <div class="bg-blue-50 rounded-2xl p-4 border border-blue-200 shadow-2xs">
            <span class="text-[10px] uppercase font-bold text-blue-700 block">Izin</span>
            <div class="text-2xl font-black text-blue-700 mt-0.5">{{ $rekap['izin'] }}</div>
            <span class="text-[10px] text-blue-600 font-medium">Resmi</span>
        </div>

        <!-- Sakit -->
        <div class="bg-purple-50 rounded-2xl p-4 border border-purple-200 shadow-2xs">
            <span class="text-[10px] uppercase font-bold text-purple-700 block">Sakit</span>
            <div class="text-2xl font-black text-purple-700 mt-0.5">{{ $rekap['sakit'] }}</div>
            <span class="text-[10px] text-purple-600 font-medium">Surat/Kabar</span>
        </div>

        <!-- Tugas Luar -->
        <div class="bg-indigo-50 rounded-2xl p-4 border border-indigo-200 shadow-2xs">
            <span class="text-[10px] uppercase font-bold text-indigo-700 block">Tugas Luar</span>
            <div class="text-2xl font-black text-indigo-700 mt-0.5">{{ $rekap['tugas_luar'] }}</div>
            <span class="text-[10px] text-indigo-600 font-medium">Dinas</span>
        </div>

        <!-- Pulang Tepat -->
        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 shadow-2xs">
            <span class="text-[10px] uppercase font-bold text-slate-600 block">Plg Tepat</span>
            <div class="text-2xl font-black text-slate-800 mt-0.5">{{ $rekap['pulang_tepat'] }}</div>
            <span class="text-[10px] text-slate-500 font-medium">&ge; 14.10 WIB</span>
        </div>

        <!-- Pulang Cepat -->
        <div class="bg-rose-50 rounded-2xl p-4 border border-rose-300 shadow-2xs">
            <span class="text-[10px] uppercase font-bold text-rose-800 block">Plg Cepat</span>
            <div class="text-2xl font-black text-rose-700 mt-0.5">{{ $rekap['pulang_cepat'] }}</div>
            <span class="text-[10px] text-rose-700 font-medium">&lt; 14.00 WIB</span>
        </div>
    </div>

    <!-- 4. Papan Monitoring & Tabel Presensi Seluruh Guru -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4">
        <!-- Bar Kontrol & Filter -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="bi-table text-indigo-600"></i>
                    <span>Tabel Presensi Kehadiran Seluruh Guru Hari Ini</span>
                </h2>
                <p class="text-xs text-slate-500">Gunakan tombol aksi di sebelah kanan nama guru untuk mengabsenkan atau merubah status jika guru lupa/kendala teknis.</p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <!-- Search Box -->
                <div class="relative">
                    <i class="bi-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" placeholder="Cari nama guru..."
                           class="pl-8 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-600 focus:outline-none w-48 sm:w-64">
                </div>

                <!-- Filter Tanggal -->
                <input type="date" value="{{ $tanggal }}" onchange="window.location.href='{{ route('piket.index') }}?tanggal=' + this.value"
                       class="py-1.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-600">
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 border-b font-bold uppercase text-[10px] text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-3 py-3 w-8 text-center">No</th>
                        <th class="px-4 py-3">Nama Guru & NIP</th>
                        <th class="px-4 py-3">Jadwal Mengajar Hari Ini</th>
                        <th class="px-3 py-3 text-center">Presensi Masuk</th>
                        <th class="px-3 py-3 text-center">Jeda KBM / Pelaksanaan</th>
                        <th class="px-3 py-3 text-center">Presensi Pulang</th>
                        <th class="px-4 py-3">Keterangan / Bukti</th>
                        <th class="px-3 py-3 text-center">Aksi Petugas Piket</th>
                    </tr>
                </thead>
                <tbody class="divide-y font-medium text-slate-700">
                    @foreach($items as $idx => $row)
                    @php
                        $guru = $row['guru'];
                        $p = $row['presensi'];
                        $jadwals = $row['jadwals'];
                        $firstJadwal = $row['first_jadwal'];
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition"
                        x-show="searchQuery === '' || '{{ strtolower(addslashes($guru->name)) }}'.includes(searchQuery.toLowerCase())">
                        <td class="px-3 py-3 text-center font-bold text-slate-400">
                            {{ $idx + 1 }}
                        </td>

                        <!-- Nama & NIP -->
                        <td class="px-4 py-3">
                            <div class="font-extrabold text-slate-900 text-sm">{{ $guru->name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">
                                {{ $guru->nip ?: 'NIP: -' }} &bull; 
                                <span class="capitalize">{{ $guru->jabatan_utama ?: 'Guru Pengajar' }}</span>
                            </div>
                        </td>

                        <!-- Jadwal Mengajar Hari Ini -->
                        <td class="px-4 py-3">
                            @if($jadwals->isEmpty())
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-500 font-semibold text-[10px]">
                                Tidak Ada Jadwal KBM
                            </span>
                            @else
                            <div class="space-y-1">
                                @foreach($jadwals as $j)
                                <div class="text-[11px] flex items-center gap-1 text-slate-800">
                                    <span class="px-1.5 py-0.2 bg-blue-50 text-blue-800 font-bold rounded font-mono text-[10px]">
                                        {{ $j->jam_ke_label }}
                                    </span>
                                    <span class="font-bold truncate max-w-[140px]" title="{{ $j->mataPelajaran->nama }}">{{ $j->mataPelajaran->nama }}</span>
                                    <span class="text-slate-400">&bull;</span>
                                    <span class="text-slate-600 font-semibold">Kls {{ $j->kelas }}</span>
                                    
                                    @php $tugas = $j->tugasHariIni; @endphp
                                    <button type="button" 
                                            @click="tugasModal = true; selectedTugasJadwal = { id: {{ $j->id }}, mapel: '{{ addslashes($j->mataPelajaran->nama ?? 'Mata Pelajaran') }}', kelas: '{{ $j->kelas }}', deskripsi: '{{ addslashes($tugas->deskripsi_tugas ?? '') }}', guruName: '{{ addslashes($guru->name) }}' }"
                                            class="ml-2 px-1.5 py-0.5 rounded {{ $tugas ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-amber-100 text-amber-700 hover:bg-amber-200' }} transition inline-flex items-center gap-1"
                                            title="{{ $tugas ? 'Edit Tugas Siswa' : 'Input Tugas Siswa (Jika Guru Absen)' }}">
                                        <i class="bi-journal-text"></i>
                                        <span class="text-[9px] font-bold">{{ $tugas ? 'Edit Tugas' : '+ Tugas' }}</span>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </td>

                        <!-- Presensi Masuk (07.00 - 07.30) -->
                        <td class="px-3 py-3 text-center">
                            @if($row['has_presensi_masuk'])
                                <span class="px-2.5 py-1 rounded-xl text-xs font-bold border inline-flex flex-col items-center {{ $row['badge_masuk_class'] }}">
                                    <span>{{ $row['status_masuk_label'] }}</span>
                                    <span class="font-mono text-[10px] opacity-80">{{ substr($p->jam_masuk, 0, 5) }} WIB</span>
                                </span>
                                @if($p->metode_masuk === 'piket')
                                <div class="text-[9px] text-amber-700 font-bold mt-0.5">Oleh Piket</div>
                                @endif
                            @else
                                <span class="px-2.5 py-1 rounded-xl text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                                    Belum Masuk
                                </span>
                            @endif
                        </td>

                        <!-- Status Jeda KBM (Jika jadwal jam 3+) -->
                        <td class="px-3 py-3 text-center">
                            @if($firstJadwal && $firstJadwal->jam_ke_mulai >= 3 && $row['has_presensi_masuk'])
                                @if($row['status_jeda'] === 'izin_keluar')
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-black bg-amber-100 text-amber-900 border border-amber-300 inline-block shadow-2xs">
                                        <i class="bi-box-arrow-right"></i> Izin Keluar
                                    </span>
                                    <div class="text-[10px] text-slate-600 font-semibold mt-0.5">
                                        {{ $p->izin_keluar_jam_kembali ? 's/d ' . substr($p->izin_keluar_jam_kembali, 0, 5) : '-' }}
                                    </div>
                                @else
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200 inline-block">
                                        Standby / Tugas
                                    </span>
                                @endif
                            @else
                                <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>

                        <!-- Presensi Pulang (Mulai 14.10, < 14.00 pulang cepat) -->
                        <td class="px-3 py-3 text-center">
                            @if($row['has_presensi_pulang'])
                                <span class="px-2.5 py-1 rounded-xl text-xs font-bold border inline-flex flex-col items-center {{ $row['badge_pulang_class'] }}">
                                    <span>{{ $row['status_pulang_label'] }}</span>
                                    <span class="font-mono text-[10px] opacity-80">{{ substr($p->jam_pulang, 0, 5) }} WIB</span>
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-lg text-xs font-medium text-slate-400 bg-slate-100">
                                    Belum Pulang
                                </span>
                            @endif
                        </td>

                        <!-- Keterangan / Bukti -->
                        <td class="px-4 py-3 max-w-xs">
                            @if($p && $p->catatan)
                            <p class="text-xs text-slate-700 line-clamp-2 italic">"{{ $p->catatan }}"</p>
                            @else
                            <span class="text-slate-400 text-xs">-</span>
                            @endif

                            @if($p && $p->lampiran_bukti)
                            <a href="{{ asset('storage/' . $p->lampiran_bukti) }}" target="_blank"
                               class="inline-flex items-center gap-1 text-[11px] text-blue-600 font-bold hover:underline mt-1">
                                <i class="bi-paperclip"></i> Lihat Bukti
                            </a>
                            @endif
                        </td>

                        <!-- Tombol Aksi Petugas Piket -->
                        <td class="px-3 py-3 text-center">
                            <div class="inline-flex items-center gap-1">
                                <!-- Tombol Absenkan Guru -->
                                <button type="button"
                                        @click="modalAbsen = true; selectedGuru = { id: {{ $guru->id }}, name: '{{ addslashes($guru->name) }}' }"
                                        title="Absenkan Guru Ini (Jika Lupa/Kendala Teknis)"
                                        class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition flex items-center gap-1 shadow-2xs">
                                    <i class="bi-fingerprint"></i>
                                    <span>Absenkan</span>
                                </button>

                                <!-- Tombol Ubah Status (Izin/Sakit/Hadir) -->
                                <button type="button"
                                        @click="modalStatus = true; selectedGuru = { id: {{ $guru->id }}, name: '{{ addslashes($guru->name) }}' }"
                                        title="Ubah Status Kehadiran (Izin, Sakit, Tugas Luar, Hadir Manual)"
                                        class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs transition flex items-center gap-1 shadow-2xs">
                                    <i class="bi-pencil-square"></i>
                                    <span>Ubah Status</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL 1: ABSENKAN GURU OLEH PETUGAS PIKET (JIKA LUPA / KENDALA TEKNIS) -->
    <div x-show="modalAbsen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative"
             @click.outside="modalAbsen = false">
            <button @click="modalAbsen = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <div class="text-center mb-5">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto mb-3 text-2xl shadow-inner">
                    <i class="bi-fingerprint"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900">Absenkan Guru Terkendala Teknis</h3>
                <p class="text-xs text-slate-600 mt-1 font-bold" x-text="selectedGuru.name"></p>
                <p class="text-[11px] text-slate-400">Petugas Piket membantu presensi resmi dewan guru</p>
            </div>

            <form method="POST" action="{{ route('piket.absen-guru') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="guru_user_id" :value="selectedGuru.id">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Pilih Jenis Presensi
                    </label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <label class="p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-emerald-500 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50 font-bold flex items-center gap-2">
                            <input type="radio" name="tipe" value="masuk" checked class="text-emerald-600">
                            <span>✅ Presensi Masuk</span>
                        </label>
                        <label class="p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-500 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 font-bold flex items-center gap-2">
                            <input type="radio" name="tipe" value="pulang" class="text-blue-600">
                            <span>🏠 Presensi Pulang</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Jam Presensi (WIB)
                    </label>
                    <input type="time" name="jam" value="{{ now()->format('H:i') }}" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono font-bold focus:ring-2 focus:ring-emerald-600">
                    <span class="text-[10px] text-slate-400 mt-1 block">Default jam saat ini. Masuk normal 07.00 - 07.30, pulang resmi mulai 14.10.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Alasan / Catatan Piket
                    </label>
                    <textarea name="catatan" rows="2" placeholder="Contoh: Guru lupa HP tertinggal di rumah / kendala jaringan internet..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-600 focus:outline-none"></textarea>
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button type="button" @click="modalAbsen = false" class="w-1/3 py-2.5 bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 rounded-xl text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="w-2/3 py-2.5 bg-emerald-600 hover:bg-emerald-700 font-bold text-white rounded-xl text-xs transition shadow-md shadow-emerald-600/30">
                        Simpan Presensi Guru
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: UBAH STATUS GURU (IZIN / SAKIT / TUGAS LUAR / DLL OLEH PIKET) -->
    <div x-show="modalStatus"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative"
             @click.outside="modalStatus = false">
            <button @click="modalStatus = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <div class="text-center mb-5">
                <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center mx-auto mb-3 text-2xl shadow-inner">
                    <i class="bi-pencil-square"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900">Ubah Status Kehadiran Guru</h3>
                <p class="text-xs text-slate-600 mt-1 font-bold" x-text="selectedGuru.name"></p>
                <p class="text-[11px] text-slate-400">Merubah status Izin, Sakit, Tugas Luar, atau Hadir Manual dewan guru</p>
            </div>

            <form method="POST" action="{{ route('piket.update-status') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="guru_user_id" :value="selectedGuru.id">
                <input type="hidden" name="tanggal" value="{{ $tanggal }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Pilih Status Kehadiran Baru
                    </label>
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <label class="p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-blue-500 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 font-bold flex flex-col items-center text-center gap-1">
                            <input type="radio" name="status" value="izin" checked class="text-blue-600">
                            <span class="text-blue-800">📝 Izin Resmi</span>
                        </label>
                        <label class="p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-purple-500 has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50 font-bold flex flex-col items-center text-center gap-1">
                            <input type="radio" name="status" value="sakit" class="text-purple-600">
                            <span class="text-purple-800">🏥 Sakit</span>
                        </label>
                        <label class="p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-indigo-500 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50 font-bold flex flex-col items-center text-center gap-1">
                            <input type="radio" name="status" value="tugas_luar" class="text-indigo-600">
                            <span class="text-indigo-800">🚗 Tugas Luar</span>
                        </label>
                        <label class="p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-emerald-500 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50 font-bold flex flex-col items-center text-center gap-1">
                            <input type="radio" name="status" value="hadir" class="text-emerald-600">
                            <span class="text-emerald-800">✅ Hadir Tepat</span>
                        </label>
                        <label class="p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-amber-500 has-[:checked]:border-amber-600 has-[:checked]:bg-amber-50 font-bold flex flex-col items-center text-center gap-1">
                            <input type="radio" name="status" value="terlambat" class="text-amber-600">
                            <span class="text-amber-800">⏰ Terlambat</span>
                        </label>
                        <label class="p-3 border-2 border-slate-200 rounded-xl cursor-pointer hover:border-rose-500 has-[:checked]:border-rose-600 has-[:checked]:bg-rose-50 font-bold flex flex-col items-center text-center gap-1">
                            <input type="radio" name="status" value="alpa" class="text-rose-600">
                            <span class="text-rose-800">❌ Alpa</span>
                        </label>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1.5">
                        *Catatan: Mengubah status menjadi Izin / Sakit / Tugas Luar otomatis mensinkronkan seluruh jadwal KBM guru hari ini dan papan monitoring kelas.
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Keterangan Alasan (Wajib Diisi)
                    </label>
                    <textarea name="catatan" rows="3" required placeholder="Tuliskan keterangan surat izin, diagnosa sakit, atau disposisi tugas luar dinas..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-600 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Upload Surat / Lampiran Bukti (Opsional)
                    </label>
                    <input type="file" name="lampiran_bukti" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button type="button" @click="modalStatus = false" class="w-1/3 py-2.5 bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 rounded-xl text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="w-2/3 py-2.5 bg-indigo-600 hover:bg-indigo-700 font-bold text-white rounded-xl text-xs transition shadow-md shadow-indigo-600/30">
                        Simpan Perubahan Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL POPUP: Input Tugas untuk Siswa (Oleh Piket) -->
    <div x-show="tugasModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative"
             @click.outside="tugasModal = false">
            <button @click="tugasModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 transition">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <div class="text-center mb-6">
                <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-3 text-2xl shadow-inner">
                    <i class="bi-journal-text"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900">Input Tugas Siswa (Piket)</h3>
                <p class="text-xs text-slate-500 mt-1 font-bold" x-text="selectedTugasJadwal.guruName"></p>
            </div>

            <div class="bg-amber-50 rounded-xl p-3 mb-5 border border-amber-200 text-center">
                <div class="text-xs font-black text-amber-900 uppercase tracking-wider" x-text="selectedTugasJadwal?.mapel"></div>
                <div class="text-xs text-amber-800 font-semibold mt-0.5">Kelas: <span x-text="selectedTugasJadwal?.kelas"></span></div>
            </div>

            <form method="POST" action="{{ route('guru.tugas-absen.store') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="jadwal_pelajaran_id" :value="selectedTugasJadwal?.id">
                <input type="hidden" name="tanggal" value="{{ $tanggal }}">

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Instruksi / Deskripsi Tugas:
                    </label>
                    <textarea name="deskripsi_tugas" rows="4" x-model="selectedTugasJadwal.deskripsi" class="w-full p-3 bg-white border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition" placeholder="Contoh: Kerjakan LKS Hal 20, atau rangkum materi bab 3 di buku catatan..."></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Lampiran File (Opsional):
                    </label>
                    <input type="file" name="file_lampiran" class="w-full p-2 bg-white border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500/20 outline-none transition">
                    <p class="text-[10px] text-slate-500 mt-1">Format bebas (PDF, Word, Image), maksimal 5MB.</p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center gap-3">
                    <button type="button" @click="tugasModal = false" class="w-1/3 py-2.5 bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 rounded-xl text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="w-2/3 py-2.5 bg-indigo-600 hover:bg-indigo-700 font-bold text-white rounded-xl text-xs flex items-center justify-center gap-2 transition shadow-md shadow-indigo-600/30">
                        <i class="bi-send"></i>
                        <span>Simpan Tugas</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Realtime Clock Script -->
<script>
(function() {
    function updateClock() {
        const el = document.getElementById('piket-clock');
        if (!el) return;
        const now = new Date();
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        const ss = String(now.getSeconds()).padStart(2, '0');
        el.innerText = `${hh}:${mm}:${ss} WIB`;
    }
    setInterval(updateClock, 1000);
})();
</script>
@endsection
