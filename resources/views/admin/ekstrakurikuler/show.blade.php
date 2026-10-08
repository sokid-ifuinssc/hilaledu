@extends('layouts.app')
@section('title', 'Detail ' . $ekstrakurikuler->nama . ' - HilalEdu')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ $tab }}',
    tambahAnggotaModal: false,
    tambahRencanaModal: false,
    tambahLaporanModal: false,
    filterKelasAnggota: '',
    searchSiswa: '',
    selectedRencanaId: '',
    rencanasData: {{ json_encode($rencanas) }},
    onSelectRencana(id) {
        if (!id) return;
        const r = this.rencanasData.find(item => item.id == id);
        if (r) {
            document.getElementById('lap_tgl').value = r.tanggal_rencana.substring(0, 10);
            document.getElementById('lap_pertemuan').value = r.pertemuan_ke;
            document.getElementById('lap_nama').value = r.nama_kegiatan;
        }
    }
}">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.ekstrakurikuler.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 transition">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Ekstrakurikuler
        </a>
        <div class="text-xs text-slate-400">
            TA: <span class="font-bold text-slate-700">{{ $activeTa }} ({{ ucfirst($activeSem) }})</span>
        </div>
    </div>

    <!-- Header Banner Eskul -->
    <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-slate-900 rounded-3xl p-6 md:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-3xl font-black text-blue-200 shrink-0">
                <i class="bi bi-award"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-md bg-blue-500/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                        {{ $ekstrakurikuler->kode ?: 'ESK' }}
                    </span>
                    @if($ekstrakurikuler->is_aktif)
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/30 text-emerald-200 text-xs font-semibold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Aktif Berjalan
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl md:text-3xl font-black mt-2 tracking-tight">{{ $ekstrakurikuler->nama }}</h1>
                <p class="text-blue-100 text-xs md:text-sm mt-1 max-w-2xl">
                    {{ $ekstrakurikuler->deskripsi ?: 'Organisasi pembinaan minat, bakat, kedisiplinan dan kepemimpinan siswa.' }}
                </p>
                <div class="flex flex-wrap items-center gap-4 mt-3 text-xs text-blue-200 font-medium">
                    <span class="flex items-center gap-1.5"><i class="bi bi-calendar-event"></i> {{ $ekstrakurikuler->jadwal_lengkap }}</span>
                    <span class="flex items-center gap-1.5"><i class="bi bi-geo-alt"></i> {{ $ekstrakurikuler->tempat ?: 'Sekolah' }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 text-xs">
            <div class="border-b sm:border-b-0 sm:border-r border-white/10 pb-2 sm:pb-0 sm:pr-4">
                <div class="text-[10px] text-blue-200 uppercase font-semibold">Guru Pembina</div>
                <div class="font-bold text-white text-sm mt-0.5">
                    {{ $ekstrakurikuler->pembina ? $ekstrakurikuler->pembina->name : '- Belum Ditugaskan -' }}
                </div>
                <div class="text-[10px] text-blue-300">{{ $ekstrakurikuler->pembina?->nip ?: 'Dewan Guru' }}</div>
            </div>
            <div class="pt-2 sm:pt-0 sm:pl-2">
                <div class="text-[10px] text-blue-200 uppercase font-semibold">Ketua Eskul</div>
                <div class="font-bold text-white text-sm mt-0.5">
                    {{ $ekstrakurikuler->ketua ? $ekstrakurikuler->ketua->name : '(Belum Ditunjuk)' }}
                </div>
                <div class="text-[10px] text-blue-300">{{ $ekstrakurikuler->ketua?->kelas?->nama ?: 'Siswa' }}</div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 bg-white rounded-2xl p-2 shadow-sm flex flex-wrap items-center gap-1 text-xs font-bold">
        <button type="button" @click="activeTab = 'anggota'" :class="activeTab === 'anggota' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'" class="px-4 py-2.5 rounded-xl transition flex items-center gap-2">
            <i class="bi bi-people-fill"></i> Data Anggota ({{ $anggotas->count() }})
        </button>
        <button type="button" @click="activeTab = 'rencana'" :class="activeTab === 'rencana' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'" class="px-4 py-2.5 rounded-xl transition flex items-center gap-2">
            <i class="bi bi-journal-check"></i> Rencana Kegiatan ({{ $rencanas->count() }})
        </button>
        <button type="button" @click="activeTab = 'laporan'" :class="activeTab === 'laporan' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'" class="px-4 py-2.5 rounded-xl transition flex items-center gap-2">
            <i class="bi bi-file-earmark-ruled-fill"></i> Laporan Kegiatan ({{ $laporans->count() }})
        </button>
        <button type="button" @click="activeTab = 'presensi'" :class="activeTab === 'presensi' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'" class="px-4 py-2.5 rounded-xl transition flex items-center gap-2">
            <i class="bi bi-calendar-check-fill"></i> Presensi Pertemuan
        </button>
        <button type="button" @click="activeTab = 'nilai'" :class="activeTab === 'nilai' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'" class="px-4 py-2.5 rounded-xl transition flex items-center gap-2">
            <i class="bi bi-award-fill"></i> Penilaian Eskul
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: DATA ANGGOTA                                      -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'anggota'" class="space-y-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Daftar Siswa Anggota Ekstrakurikuler</h3>
                <p class="text-xs text-slate-500">Siswa yang terdaftar aktif dalam kelompok ekstrakurikuler ini.</p>
            </div>
            <button type="button" @click="tambahAnggotaModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-person-plus-fill"></i> Tambah Anggota Siswa
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Nama Siswa</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4">Jabatan</th>
                            <th class="py-3 px-4 text-center">Kehadiran</th>
                            <th class="py-3 px-4 text-center">Nilai</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($anggotas as $idx => $anggota)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800">{{ $anggota->siswa->name }}</div>
                                    <div class="text-[10px] text-slate-400">NISN: {{ $anggota->siswa->username }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 font-semibold text-slate-700">
                                        {{ $anggota->siswa->kelas?->nama ?: '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($anggota->jabatan === 'Ketua')
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-black flex items-center gap-1 w-max">
                                            <i class="bi bi-star-fill text-amber-500"></i> Ketua Eskul
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium">
                                            {{ $anggota->jabatan }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="font-bold {{ $anggota->persentase_kehadiran >= 75 ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $anggota->persentase_kehadiran }}%
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ $anggota->jumlah_hadir }} / {{ $anggota->total_pertemuan }} pertemuan
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($anggota->nilai_angka !== null || $anggota->nilai_huruf !== null)
                                        <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 font-bold">
                                            {{ $anggota->nilai_angka ? round($anggota->nilai_angka) : '' }} ({{ $anggota->predikat_nilai }})
                                        </span>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($anggota->jabatan !== 'Ketua')
                                            <form action="{{ route('admin.ekstrakurikuler.set-ketua', $ekstrakurikuler->id) }}" method="POST" onsubmit="return confirm('Tugaskan {{ $anggota->siswa->name }} sebagai Ketua Eskul?');">
                                                @csrf
                                                <input type="hidden" name="siswa_id" value="{{ $anggota->siswa_id }}">
                                                <button type="submit" class="px-2 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold text-[10px] transition" title="Jadikan Ketua">
                                                    <i class="bi bi-star"></i> Jadi Ketua
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.ekstrakurikuler.anggota.destroy', [$ekstrakurikuler->id, $anggota->id]) }}" method="POST" onsubmit="return confirm('Keluarkan siswa ini dari anggota eskul?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 transition" title="Hapus Anggota">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    Belum ada siswa yang ditambahkan ke dalam ekstrakurikuler ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: RENCANA KEGIATAN                                   -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'rencana'" class="space-y-4" style="display: none;">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Rencana Program Kegiatan Ekstrakurikuler</h3>
                <p class="text-xs text-slate-500">Silabus & matriks agenda pertemuan yang dirancang oleh Pembina.</p>
            </div>
            <button type="button" @click="tambahRencanaModal = true" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-plus-circle"></i> Buat Rencana Pertemuan
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($rencanas as $r)
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 font-black text-xs">
                                Pertemuan Ke-{{ $r->pertemuan_ke }}
                            </span>
                            @if($r->is_terlaksana)
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-bold text-[10px] flex items-center gap-1">
                                    <i class="bi bi-check-circle-fill"></i> Sudah Terlaksana
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 font-medium text-[10px]">
                                    Belum Terlaksana
                                </span>
                            @endif
                        </div>
                        <h4 class="text-base font-bold text-slate-800 mt-2">{{ $r->nama_kegiatan }}</h4>
                        <div class="text-xs text-slate-500 mt-1 flex items-center gap-2">
                            <i class="bi bi-calendar-event text-blue-500"></i>
                            Rencana: <span class="font-semibold text-slate-700">{{ \Carbon\Carbon::parse($r->tanggal_rencana)->isoFormat('dddd, D MMMM Y') }}</span>
                        </div>
                        @if($r->deskripsi_rencana)
                            <p class="text-xs text-slate-600 mt-3 p-3 rounded-xl bg-slate-50 border border-slate-100 leading-relaxed">
                                {{ $r->deskripsi_rencana }}
                            </p>
                        @endif
                        @if($r->target_pencapaian)
                            <div class="text-[11px] text-slate-500 mt-2">
                                <span class="font-bold text-slate-600">Target Pencapaian:</span> {{ $r->target_pencapaian }}
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-2 bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400 text-xs">
                    Belum ada rencana kegiatan yang diinputkan.
                </div>
            @endforelse
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: LAPORAN KEGIATAN (JURNAL KBM ESKUL)               -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'laporan'" class="space-y-4" style="display: none;">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Laporan Realisasi Kegiatan Ekstrakurikuler</h3>
                <p class="text-xs text-slate-500">Dokumentasi dan catatan jurnal hasil pelaksanaan latihan / kegiatan eskul.</p>
            </div>
            <button type="button" @click="tambahLaporanModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-journal-plus"></i> Input Laporan Kegiatan
            </button>
        </div>

        <div class="space-y-4">
            @forelse($laporans as $lap)
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div class="space-y-2 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-800 font-bold text-xs">
                                    Pertemuan Ke-{{ $lap->pertemuan_ke }}
                                </span>
                                <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                    <i class="bi bi-calendar3 text-blue-500"></i> {{ \Carbon\Carbon::parse($lap->tanggal_kegiatan)->isoFormat('dddd, D MMMM Y') }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $lap->status_pelaksanaan === 'sesuai_rencana' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ ucwords(str_replace('_', ' ', $lap->status_pelaksanaan)) }}
                                </span>
                            </div>
                            <h4 class="text-lg font-black text-slate-800">{{ $lap->nama_kegiatan }}</h4>
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700 leading-relaxed">
                                <div class="font-bold text-slate-800 mb-1">Realisasi Materi / Aktivitas:</div>
                                {{ $lap->ringkasan_materi }}
                            </div>
                            @if($lap->catatan_kegiatan)
                                <div class="text-xs text-slate-500 italic">
                                    Catatan: {{ $lap->catatan_kegiatan }}
                                </div>
                            @endif

                            <!-- Kehadiran -->
                            <div class="flex flex-wrap items-center gap-3 pt-2 text-xs">
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold">Hadir: {{ $lap->jumlah_hadir }}</span>
                                <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold">Izin: {{ $lap->jumlah_izin }}</span>
                                <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 font-bold">Sakit: {{ $lap->jumlah_sakit }}</span>
                                <span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-bold">Alpa: {{ $lap->jumlah_alpa }}</span>
                            </div>
                        </div>

                        @if($lap->foto_dokumentasi)
                            <div class="w-full md:w-48 shrink-0">
                                <img src="{{ asset('storage/' . $lap->foto_dokumentasi) }}" alt="Dokumentasi" class="w-full h-32 object-cover rounded-xl border border-slate-200 shadow-sm">
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400 text-xs">
                    Belum ada laporan kegiatan yang diinputkan.
                </div>
            @endforelse
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 4: PRESENSI PERTEMUAN                                 -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'presensi'" class="space-y-4" style="display: none;">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Presensi Anggota Eskul</h3>
                <p class="text-xs text-slate-500">Pilih tanggal pertemuan untuk melihat dan mengisi absensi anggota.</p>
            </div>
            <form action="{{ route('admin.ekstrakurikuler.show', $ekstrakurikuler->id) }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="presensi">
                <input type="date" name="tanggal" value="{{ $selectedTanggal }}" class="text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <button type="submit" class="px-3 py-2 bg-slate-800 text-white text-xs font-semibold rounded-xl hover:bg-slate-900 transition">
                    Buka Tanggal
                </button>
            </form>
        </div>

        <form action="{{ route('admin.ekstrakurikuler.show', $ekstrakurikuler->id) }}" method="POST">
            <!-- Form input presensi -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">
                        Tanggal: {{ \Carbon\Carbon::parse($selectedTanggal)->isoFormat('dddd, D MMMM Y') }}
                    </span>
                    <span class="text-xs text-slate-500">
                        Total Anggota: {{ $anggotas->count() }} Siswa
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-600 uppercase text-[10px] font-bold">
                            <tr>
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4">Nama Siswa</th>
                                <th class="py-3 px-4">Kelas</th>
                                <th class="py-3 px-4 text-center">Status Kehadiran</th>
                                <th class="py-3 px-4">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($anggotas as $idx => $a)
                                @php
                                    $p = $presensis[$a->siswa_id] ?? null;
                                    $st = $p ? $p->status : 'Hadir';
                                @endphp
                                <tr>
                                    <td class="py-3 px-4 text-center text-slate-400 font-bold">{{ $idx + 1 }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-800">{{ $a->siswa->name }}</td>
                                    <td class="py-3 px-4 text-slate-500">{{ $a->siswa->kelas?->nama ?: '-' }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold 
                                            {{ $st === 'Hadir' ? 'bg-emerald-100 text-emerald-800' : ($st === 'Izin' ? 'bg-blue-100 text-blue-800' : ($st === 'Sakit' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')) }}">
                                            {{ $st }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-500">
                                        {{ $p?->keterangan ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">Belum ada anggota di eskul ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 5: PENILAIAN ESKUL                                   -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'nilai'" class="space-y-4" style="display: none;">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Rekapitulasi Nilai Ekstrakurikuler Siswa</h3>
                <p class="text-xs text-slate-500">Nilai eskul yang diberikan oleh Guru Pembina untuk rapor dan portofolio siswa.</p>
            </div>
            <div class="text-xs font-semibold text-slate-500">
                Nilai Eskul Terhubung Otomatis ke Rapor & Mapel Project
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Nama Siswa</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4 text-center">Kehadiran</th>
                            <th class="py-3 px-4 text-center">Nilai Angka</th>
                            <th class="py-3 px-4 text-center">Predikat</th>
                            <th class="py-3 px-4">Catatan Capaian Pembina</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($anggotas as $idx => $a)
                            <tr>
                                <td class="py-3 px-4 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                                <td class="py-3 px-4 font-bold text-slate-800">{{ $a->siswa->name }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $a->siswa->kelas?->nama ?: '-' }}</td>
                                <td class="py-3 px-4 text-center font-semibold text-slate-700">{{ $a->persentase_kehadiran }}%</td>
                                <td class="py-3 px-4 text-center font-black text-slate-800">{{ $a->nilai_angka ? round($a->nilai_angka) : '-' }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2.5 py-0.5 rounded-md font-black {{ $a->predikat_nilai === 'A' ? 'bg-emerald-100 text-emerald-800' : ($a->predikat_nilai === 'B' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $a->predikat_nilai }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-600">{{ $a->catatan_nilai ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">Belum ada anggota di eskul ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH ANGGOTA SISWA -->
    <div x-show="tambahAnggotaModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-200" @click.outside="tambahAnggotaModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-person-plus text-blue-600"></i> Tambah Anggota Siswa ke {{ $ekstrakurikuler->nama }}
                </h3>
                <button @click="tambahAnggotaModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('admin.ekstrakurikuler.anggota.store', $ekstrakurikuler->id) }}" method="POST" class="mt-4 space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Filter Kelas</label>
                        <select x-model="filterKelasAnggota" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">Semua Kelas</option>
                            @foreach($kelases as $k)
                                <option value="{{ $k->nama }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jabatan Awal</label>
                        <select name="jabatan" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="Anggota" selected>Anggota</option>
                            <option value="Wakil Ketua">Wakil Ketua</option>
                            <option value="Sekretaris">Sekretaris</option>
                            <option value="Bendahara">Bendahara</option>
                            <option value="Ketua">Ketua</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Cari Nama Siswa</label>
                    <input type="text" x-model="searchSiswa" placeholder="Ketik nama atau NISN..." class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Siswa (Bisa Pilih Banyak)</label>
                    <div class="max-h-60 overflow-y-auto border border-slate-200 rounded-xl p-2 divide-y divide-slate-100 bg-slate-50">
                        @foreach($availableSiswas as $s)
                            <label class="flex items-center gap-3 py-2 px-2 hover:bg-white rounded-lg cursor-pointer transition"
                                   x-show="(!filterKelasAnggota || '{{ $s->kelas?->nama }}' === filterKelasAnggota) && (!searchSiswa || '{{ strtolower($s->name) }}'.includes(searchSiswa.toLowerCase()) || '{{ $s->username }}'.includes(searchSiswa))">
                                <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-slate-800">{{ $s->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $s->kelas?->nama ?: 'Tanpa Kelas' }} · NISN: {{ $s->username }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="tambahAnggotaModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition shadow-md">Tambahkan Siswa Terpilih</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL BUAT RENCANA KEGIATAN -->
    <div x-show="tambahRencanaModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200" @click.outside="tambahRencanaModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-journal-plus text-blue-600"></i> Buat Rencana Pertemuan Eskul
                </h3>
                <button @click="tambahRencanaModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('guru.ekstrakurikuler.rencana.store', $ekstrakurikuler->id) }}" method="POST" class="mt-4 space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Pertemuan Ke- <span class="text-rose-500">*</span></label>
                        <input type="number" name="pertemuan_ke" value="{{ $rencanas->count() + 1 }}" required class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Rencana <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_rencana" required class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Judul / Materi Kegiatan <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_kegiatan" required placeholder="Contoh: Latihan Dasar Baris-Berbaris (PBB)..." class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Deskripsi Rencana Kegiatan</label>
                    <textarea name="deskripsi_rencana" rows="3" placeholder="Rincian alur kegiatan latihan..." class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Target Pencapaian</label>
                    <input type="text" name="target_pencapaian" placeholder="Target kompetensi yang diharapkan..." class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="tambahRencanaModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition shadow-md">Simpan Rencana</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL INPUT LAPORAN KEGIATAN (AUTODETECT TANGGAL DARI RENCANA) -->
    <div x-show="tambahLaporanModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200" @click.outside="tambahLaporanModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-journal-check text-emerald-600"></i> Input Laporan Realisasi Eskul
                </h3>
                <button @click="tambahLaporanModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('guru.ekstrakurikuler.laporan.store', $ekstrakurikuler->id) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Rencana Kegiatan (Otomatis Isi Tanggal & Materi)</label>
                    <select name="rencana_kegiatan_id" x-model="selectedRencanaId" @change="onSelectRencana(selectedRencanaId)" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">-- Pilih dari Rencana Kegiatan --</option>
                        @foreach($rencanas as $r)
                            <option value="{{ $r->id }}">Pertemuan {{ $r->pertemuan_ke }}: {{ $r->nama_kegiatan }} ({{ \Carbon\Carbon::parse($r->tanggal_rencana)->format('d/m/Y') }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Pertemuan Ke- <span class="text-rose-500">*</span></label>
                        <input type="number" id="lap_pertemuan" name="pertemuan_ke" value="{{ $laporans->count() + 1 }}" required class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Kegiatan <span class="text-rose-500">*</span></label>
                        <input type="date" id="lap_tgl" name="tanggal_kegiatan" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold text-blue-700">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Judul / Topik Kegiatan <span class="text-rose-500">*</span></label>
                    <input type="text" id="lap_nama" name="nama_kegiatan" required placeholder="Judul kegiatan..." class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Ringkasan Realisasi Materi / Latihan <span class="text-rose-500">*</span></label>
                    <textarea name="ringkasan_materi" rows="3" required placeholder="Uraikan apa yang dilaksanakan pada latihan ini..." class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status Pelaksanaan</label>
                        <select name="status_pelaksanaan" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="sesuai_rencana" selected>Sesuai Rencana</option>
                            <option value="terlaksana_penuh">Terlaksana Penuh</option>
                            <option value="penyesuaian">Penyesuaian Materi</option>
                            <option value="ditunda">Ditunda / Ganti Hari</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Foto Dokumentasi</label>
                        <input type="file" name="foto_dokumentasi" accept="image/*" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-slate-500">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan / Evaluasi</label>
                    <input type="text" name="catatan_kegiatan" placeholder="Catatan jalannya kegiatan..." class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="tambahLaporanModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition shadow-md">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
