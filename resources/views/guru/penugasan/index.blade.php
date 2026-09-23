@extends('layouts.app')

@section('title', 'Informasi Penugasan Guru')

@section('content')
<div class="space-y-6" x-data="{ 
    tab: '{{ $tab }}',
    modalProgramKerja: false,
    selectedTugas: ''
}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-person-workspace text-amber-600"></i>
                <span>Informasi Penugasan Guru</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Distribusi tugas mengajar KBM hasil plotting kurikulum & jadwal admin sekolah, serta tugas tambahan yang diemban (Tahun Ajaran {{ $tahunAjaran }}).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 rounded-xl bg-amber-50 text-amber-900 border border-amber-200 text-xs font-bold flex items-center gap-1.5">
                <i class="bi-calendar-range"></i>
                <span>T.A. {{ $tahunAjaran }}</span>
            </span>
        </div>
    </div>

    <!-- Papan Monitoring Kehadiran Guru di Kelas (Real-Time) -->
    @include('partials.papan_monitoring_kelas')

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 gap-2 text-xs font-bold">
        <button type="button" 
                @click="tab = 'mengajar'" 
                :class="tab === 'mengajar' ? 'border-b-2 border-amber-600 text-amber-700 bg-amber-50/50' : 'text-slate-500 hover:text-slate-700'"
                class="px-5 py-3 rounded-t-xl transition flex items-center gap-2">
            <i class="bi-book-half text-base"></i>
            <span>1. Penugasan Mengajar KBM (Plotting Admin)</span>
            <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-black">{{ $penugasanMengajar->count() }} Mapel-Kelas</span>
        </button>

        <button type="button" 
                @click="tab = 'tambahan'" 
                :class="tab === 'tambahan' ? 'border-b-2 border-amber-600 text-amber-700 bg-amber-50/50' : 'text-slate-500 hover:text-slate-700'"
                class="px-5 py-3 rounded-t-xl transition flex items-center gap-2">
            <i class="bi-award text-base"></i>
            <span>2. Penugasan Tambahan & Program Kerja</span>
            <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-black">{{ count($listTugasTambahan) }} Tugas</span>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: PENUGASAN MENGAJAR KBM (PLOTTING ADMIN) -->
    <!-- ========================================================================= -->
    <div x-show="tab === 'mengajar'" class="space-y-4">
        <div class="p-4 bg-blue-50/70 border border-blue-200 rounded-2xl flex items-start gap-3 text-blue-900 text-xs">
            <i class="bi-info-circle-fill text-blue-600 text-base flex-shrink-0 mt-0.5"></i>
            <div>
                <strong>Petunjuk Perangkat Pembelajaran:</strong>
                <p class="text-blue-800 mt-0.5 leading-relaxed">
                    Di bawah ini adalah daftar mata pelajaran dan kelas yang telah diplot secara resmi oleh bagian Kurikulum/Admin. 
                    Klik tombol <strong>"Buat Perangkat Pembelajaran"</strong> pada masing-masing mata pelajaran untuk mengunggah tautan Google Drive berkas perangkat ajar Anda.
                </p>
            </div>
        </div>

        @if($penugasanMengajar->isEmpty())
        <div class="bg-white rounded-3xl p-10 text-center border border-slate-200 shadow-xs">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                <i class="bi-book"></i>
            </div>
            <h3 class="font-bold text-slate-800 text-base">Belum Ada Plotting Mengajar</h3>
            <p class="text-slate-500 text-xs mt-1 max-w-md mx-auto">
                Admin kurikulum belum memplot jadwal mata pelajaran untuk akun Anda pada tahun ajaran aktif ini.
            </p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($penugasanMengajar as $item)
            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-xs hover:shadow-md transition space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <!-- Kelas & Kode -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-3 py-1 rounded-xl bg-slate-900 text-white font-black text-xs">
                            Kelas {{ $item['kelas'] }}
                        </span>
                        @if($item['mapel_kode'])
                        <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600 font-mono text-[10px] font-bold">
                            {{ $item['mapel_kode'] }}
                        </span>
                        @endif
                    </div>

                    <!-- Judul Mapel -->
                    <div>
                        <h3 class="font-black text-slate-900 text-base leading-snug">
                            {{ $item['mapel_nama'] }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1">
                            <i class="bi-geo-alt text-rose-500"></i> Ruang: {{ $item['ruang'] }}
                        </p>
                    </div>

                    <!-- Metrik Jam Terjadwal -->
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 grid grid-cols-2 gap-2 text-center text-xs">
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase block">Alokasi Jam</span>
                            <span class="font-black text-slate-800 text-sm">{{ $item['alokasi_jam'] }} JP</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase block">Hari KBM</span>
                            <span class="font-bold text-blue-700 text-xs truncate block" title="{{ $item['hari_mengajar'] }}">{{ $item['hari_mengajar'] }}</span>
                        </div>
                    </div>

                    <!-- Status Perangkat Pembelajaran -->
                    <div class="pt-1">
                        @if($item['perangkat'])
                        <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs">
                            <span class="text-emerald-800 font-bold flex items-center gap-1.5">
                                <i class="bi-check-circle-fill text-emerald-600"></i> Perangkat Ajar Ada
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $item['perangkat']->status_badge }}">
                                {{ $item['perangkat']->status_label }}
                            </span>
                        </div>
                        @else
                        <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-xl flex items-center gap-1.5 text-xs text-amber-800">
                            <i class="bi-exclamation-triangle-fill text-amber-600"></i>
                            <span>Perangkat ajar belum diunggah</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Action Button: Buat Perangkat Pembelajaran -->
                <div class="pt-3 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('guru.perangkat-ajar.create', ['mata_pelajaran_id' => $item['mata_pelajaran_id'], 'kelas' => $item['kelas']]) }}" 
                       class="flex-1 py-2.5 px-3 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition shadow-sm shadow-amber-600/20 text-center">
                        <i class="bi-cloud-arrow-up-fill text-sm"></i>
                        <span>{{ $item['perangkat'] ? 'Update Perangkat' : 'Buat Perangkat Pembelajaran' }}</span>
                    </a>
                    <a href="{{ route('guru.rencana-pembelajaran.rpp.create', ['jadwal_id' => null]) }}" 
                       title="Buat Modul Ajar (RPP)"
                       class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                        <i class="bi-journal-plus"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: PENUGASAN TAMBAHAN & PROGRAM KERJA -->
    <!-- ========================================================================= -->
    <div x-show="tab === 'tambahan'" class="space-y-6">
        <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl flex items-start gap-3 text-emerald-900 text-xs">
            <i class="bi-patch-check-fill text-emerald-600 text-base flex-shrink-0 mt-0.5"></i>
            <div>
                <strong>Penugasan Tambahan di Sekolah:</strong>
                <p class="text-emerald-800 mt-0.5 leading-relaxed">
                    Bagi guru yang mengemban tugas manajerial/struktural sekolah (Kepala Sekolah, Waka, Kaprog, Wali Kelas, BK, Bendahara BOS, dll.), 
                    Anda dapat menyusun <strong>Rencana Tugas & Program Kerja</strong> serta mencatat <strong>Realisasi Kerja Tugas Tambahan</strong>.
                </p>
            </div>
        </div>

        @if(empty($listTugasTambahan))
        <div class="bg-white rounded-3xl p-10 text-center border border-slate-200 shadow-xs">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                <i class="bi-person-badge"></i>
            </div>
            <h3 class="font-bold text-slate-800 text-base">Tidak Ada Tugas Tambahan</h3>
            <p class="text-slate-500 text-xs mt-1 max-w-md mx-auto">
                Anda terdaftar murni sebagai Guru Pengajar dan tidak memiliki penugasan tambahan struktural saat ini.
            </p>
        </div>
        @else
        <!-- Daftar Kartu Tugas Tambahan -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($listTugasTambahan as $tugas)
            @php
                $programsForThisTugas = $programKerjaList->where('tugas_tambahan', $tugas);
            @endphp
            <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-xs hover:shadow-md transition space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-black text-[11px] uppercase tracking-wider border border-indigo-200">
                            Tugas Tambahan
                        </span>
                        <span class="text-xs text-slate-400 font-semibold">{{ $programsForThisTugas->count() }} Rencana</span>
                    </div>

                    <div>
                        <h3 class="font-black text-slate-900 text-lg leading-snug">
                            {{ $tugas }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Penetapan resmi manajemen SMK Plus Al-Hilal Arjawinangun
                        </p>
                    </div>

                    <!-- Ringkasan Program Kerja Yang Sudah Disusun -->
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-600 block">Rencana Program Kerja:</span>
                        @if($programsForThisTugas->isEmpty())
                        <p class="text-xs text-slate-400 italic">Belum ada program kerja yang dibuat.</p>
                        @else
                        <ul class="space-y-1.5">
                            @foreach($programsForThisTugas->take(3) as $pk)
                            <li class="p-2 bg-slate-50 rounded-xl border border-slate-100 text-xs flex items-center justify-between gap-2">
                                <span class="font-medium text-slate-800 truncate" title="{{ $pk->nama_program }}">
                                    {{ $pk->nama_program }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase {{ $pk->status_badge }}">
                                    {{ $pk->status_label }}
                                </span>
                            </li>
                            @endforeach
                        </ul>
                        @if($programsForThisTugas->count() > 3)
                        <p class="text-[11px] text-blue-600 font-bold">+{{ $programsForThisTugas->count() - 3 }} program kerja lainnya</p>
                        @endif
                        @endif
                    </div>
                </div>

                <!-- Aksi: Buat Rencana Tugas & Program Kerja & Input Kegiatan Kalender -->
                @php
                    $bidangParam = 'waka_kurikulum';
                    $tLower = strtolower($tugas);
                    if (str_contains($tLower, 'hubin') || str_contains($tLower, 'humas') || str_contains($tLower, 'industri')) $bidangParam = 'waka_hubin';
                    elseif (str_contains($tLower, 'osis')) $bidangParam = 'pembina_osis';
                    elseif (str_contains($tLower, 'kesiswaan')) $bidangParam = 'waka_kesiswaan';
                    elseif (str_contains($tLower, 'sarpras')) $bidangParam = 'waka_sarpras';
                @endphp
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <a href="{{ route('guru.kegiatan.create', ['bidang' => $bidangParam]) }}" 
                       class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition shadow-sm shadow-emerald-600/20">
                        <i class="bi-calendar-plus-fill"></i>
                        <span>Input Kegiatan (Sinkron Kalender)</span>
                    </a>
                    <button type="button" 
                            @click="modalProgramKerja = true; selectedTugas = '{{ addslashes($tugas) }}'"
                            class="w-full py-2 px-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition border border-indigo-200">
                        <i class="bi-plus-circle-fill"></i>
                        <span>Buat Rencana & Program Kerja</span>
                    </button>
                    <a href="{{ route('guru.realisasi-tugas-tambahan.create', ['tugas' => $tugas]) }}" 
                       class="w-full py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition">
                        <i class="bi-pencil-square"></i>
                        <span>Catat Realisasi Kerja</span>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Tabel Lengkap Seluruh Program Kerja -->
        @if($programKerjaList->isNotEmpty())
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-black text-slate-900 text-base">Matriks Seluruh Rencana Tugas & Program Kerja</h3>
                    <p class="text-xs text-slate-500">Daftar agenda kegiatan terencana untuk penugasan tambahan Anda</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 border-b font-bold uppercase text-[10px] text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Tugas Tambahan</th>
                            <th class="px-4 py-3">Nama Program / Rencana</th>
                            <th class="px-4 py-3">Target Waktu</th>
                            <th class="px-4 py-3">Indikator Capaian</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y font-medium text-slate-700">
                        @foreach($programKerjaList as $pk)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-bold text-slate-900">
                                {{ $pk->tugas_tambahan }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $pk->nama_program }}</div>
                                @if($pk->tujuan)
                                <div class="text-[11px] text-slate-500 line-clamp-1">{{ $pk->tujuan }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $pk->target_waktu ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 max-w-xs truncate">
                                {{ $pk->indikator_keberhasilan ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase {{ $pk->status_badge }}">
                                    {{ $pk->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('guru.penugasan.program-kerja.destroy', $pk) }}" onsubmit="return confirm('Hapus rencana program kerja ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                        <i class="bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL POPUP: BUAT RENCANA TUGAS & PROGRAM KERJA -->
    <!-- ========================================================================= -->
    <div x-show="modalProgramKerja" 
         x-transition.opacity.duration.300ms
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        <div @click.away="modalProgramKerja = false" 
             class="bg-white w-full max-w-lg rounded-3xl p-6 sm:p-7 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-indigo-100 text-indigo-700 rounded-xl">
                        <i class="bi-calendar2-plus-fill text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Buat Rencana Tugas & Program Kerja</h3>
                        <p class="text-xs text-slate-500" x-text="'Penugasan: ' + selectedTugas"></p>
                    </div>
                </div>
                <button type="button" @click="modalProgramKerja = false" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100">
                    <i class="bi-x-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('guru.penugasan.program-kerja.store') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="tugas_tambahan" :value="selectedTugas">
                <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">
                <input type="hidden" name="semester" value="Ganjil">

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Program / Rencana Tugas <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_program" required placeholder="Contoh: Rapat Koordinasi Kurikulum / Supervisi Kelas / Kunjungan Industri" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tujuan & Sasaran Kegiatan</label>
                    <textarea name="tujuan" rows="2" placeholder="Uraikan tujuan yang ingin dicapai dari rencana program kerja ini..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Target Waktu Pelaksanaan</label>
                        <input type="text" name="target_waktu" placeholder="Contoh: September 2026 / Pekan ke-3" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Estimasi Anggaran (Opsional)</label>
                        <input type="number" name="anggaran" min="0" placeholder="Rp 0" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Indikator Keberhasilan / Output</label>
                    <textarea name="indikator_keberhasilan" rows="2" placeholder="Contoh: Terlaksananya evaluasi KBM bagi seluruh guru dan tersusunnya laporan rekapitulasi..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="modalProgramKerja = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-600/20 flex items-center gap-1.5">
                        <i class="bi-check-lg"></i>
                        <span>Simpan Rencana Program Kerja</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
