@extends('layouts.app')

@section('title', 'Input Laporan Realisasi KBM & Presensi Siswa')

@section('content')
@php
    $rencanaPayload = $rencanaList->map(function($r) {
        $tp = $r->tujuanPembelajaran;
        return [
            'id' => $r->id,
            'pertemuan_ke' => $r->pertemuan_ke,
            'tanggal_rencana' => $r->tanggal_rencana,
            'materi_pokok' => $r->materi_pokok,
            'tp_kode' => $tp ? $tp->kode_tp : null,
            'tp_deskripsi' => $tp ? $tp->deskripsi : null,
            'tp_kktp' => $tp ? $tp->kktp : null,
            'aktivitas_pendahuluan' => $r->aktivitas_pendahuluan,
            'aktivitas_inti' => $r->aktivitas_inti,
            'aktivitas_penutup' => $r->aktivitas_penutup,
            'media_sumber' => $r->media_sumber,
            'bentuk_asesmen' => $r->bentuk_asesmen,
            'catatan' => $r->catatan,
        ];
    })->values();

    $initialRencanaId = $rencana ? $rencana->id : ($rencanaList->isNotEmpty() ? $rencanaList->first()->id : '');
@endphp

<div class="max-w-5xl mx-auto space-y-6" 
     x-data="{
        statusPelaksanaan: 'sesuai_jadwal',
        kesesuaianRencana: 'sesuai',
        rencanaList: {{ Js::from($rencanaPayload) }},
        selectedRencanaId: '{{ $initialRencanaId }}',
        currentRencana: null,
        catatanKegiatan: '',
        keteranganKesesuaian: '',

        init() {
            this.updateCurrentRencana();
        },

        updateCurrentRencana() {
            if (!this.selectedRencanaId) {
                this.currentRencana = null;
                return;
            }
            this.currentRencana = this.rencanaList.find(r => r.id == this.selectedRencanaId) || null;
            if (this.currentRencana) {
                // Prefill ringkasan aktivitas sesuai rencana jika masih kosong atau ganti rencana
                let aktivitas = [];
                if (this.currentRencana.aktivitas_pendahuluan) aktivitas.push('• Pendahuluan: ' + this.currentRencana.aktivitas_pendahuluan);
                if (this.currentRencana.aktivitas_inti) aktivitas.push('• Kegiatan Inti: ' + this.currentRencana.aktivitas_inti);
                if (this.currentRencana.aktivitas_penutup) aktivitas.push('• Penutup: ' + this.currentRencana.aktivitas_penutup);

                if (aktivitas.length > 0) {
                    this.catatanKegiatan = aktivitas.join('\n');
                } else {
                    this.catatanKegiatan = 'Pembelajaran materi ' + this.currentRencana.materi_pokok + ' terlaksana sesuai rencana modul ajar.';
                }

                if (this.currentRencana.tp_kktp) {
                    this.keteranganKesesuaian = 'Ketercapaian TP (' + (this.currentRencana.tp_kode || '') + '): ' + this.currentRencana.tp_kktp;
                } else {
                    this.keteranganKesesuaian = 'Materi ' + this.currentRencana.materi_pokok + ' tersampaikan tuntas sesuai alokasi.';
                }
            }
        },

        setAll(status) {
            document.querySelectorAll('input[type=radio][value=' + status + ']').forEach(el => el.checked = true);
            this.updateCounts();
        },
        hadirCount: {{ $students->count() }},
        tidakHadirCount: 0,
        updateCounts() {
            let hadir = 0;
            let tidak = 0;
            document.querySelectorAll('.presensi-radio:checked').forEach(el => {
                if (el.value === 'hadir' || el.value === 'terlambat') hadir++;
                else tidak++;
            });
            this.hadirCount = hadir;
            this.tidakHadirCount = tidak;
        }
     }">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="text-xs text-slate-500 mb-1">
                <a href="{{ route('guru.laporan-kbm.index') }}" class="text-blue-600 hover:underline">&larr; Kembali ke Daftar Laporan KBM</a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-clipboard-check text-emerald-600"></i>
                <span>Input Realisasi KBM & Presensi Siswa</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Laporan jurnal pengajaran disinkronkan langsung dengan Rencana Pembelajaran (Modul Ajar / RPP)</p>
        </div>
    </div>

    <!-- Pilihan Jadwal Mengajar -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs">
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
            Pilih Slot Jadwal KBM Yang Dilaksanakan:
        </label>
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <select onchange="window.location.href='{{ route('guru.laporan-kbm.create') }}?jadwal_id=' + this.value" 
                    class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-600">
                <option value="">-- Pilih Jadwal Mengajar Hari Ini / Jadwal Lain --</option>
                @foreach($jadwals as $j)
                <option value="{{ $j->id }}" {{ $selectedJadwal && $selectedJadwal->id == $j->id ? 'selected' : '' }}>
                    {{ $j->hari }} ({{ substr($j->jam_mulai,0,5) }}-{{ substr($j->jam_selesai,0,5) }}) &bull; Kelas {{ $j->kelas }} &bull; {{ $j->mataPelajaran->nama }}
                </option>
                @endforeach
            </select>
        </div>
    </div>

    @if(!$selectedJadwal)
    <div class="bg-amber-50 border border-amber-200 rounded-3xl p-8 text-center text-amber-900 text-xs">
        <i class="bi-info-circle text-3xl text-amber-600 mb-2 inline-block"></i>
        <h4 class="font-bold text-sm">Pilih Jadwal Mengajar Terlebih Dahulu</h4>
        <p class="mt-1 text-amber-800">Silakan pilih slot jadwal mengajar pada dropdown di atas untuk memuat data rencana ajar dan presensi siswa secara otomatis.</p>
    </div>
    @else

    <form method="POST" action="{{ route('guru.laporan-kbm.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <input type="hidden" name="jadwal_pelajaran_id" value="{{ $selectedJadwal->id }}">
        <input type="hidden" name="rencana_pembelajaran_id" :value="selectedRencanaId">

        <!-- Card 1: Konfirmasi Waktu Realisasi & Kesesuaian Jadwal -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
            <h3 class="font-extrabold text-sm text-slate-900 border-b pb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 text-xs flex items-center justify-center font-black">1</span>
                    <span>Waktu Realisasi & Status Pelaksanaan</span>
                </div>
                <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 font-bold rounded-lg text-xs">
                    Kelas {{ $selectedJadwal->kelas }} &bull; {{ $selectedJadwal->mataPelajaran->nama }}
                </span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                <!-- Tanggal Realisasi -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Realisasi Pelaksanaan
                    </label>
                    <input type="date" name="tanggal_realisasi" value="{{ date('Y-m-d') }}" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-900 focus:ring-2 focus:ring-emerald-600">
                    <span class="text-[10px] text-slate-400 mt-1 block">Default hari ini, sesuaikan jika mengisi kelas pengganti.</span>
                </div>

                <!-- Status Pelaksanaan Jadwal -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Keterangan Kesesuaian Jadwal
                    </label>
                    <select name="status_pelaksanaan" x-model="statusPelaksanaan" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-emerald-600">
                        <option value="sesuai_jadwal">✅ Dilaksanakan Sesuai Jadwal</option>
                        <option value="ganti_hari">🔄 Mengganti Hari / Jam Pengganti</option>
                        <option value="jam_tambahan">➕ Jam Tambahan / Pengayaan</option>
                        <option value="lainnya">✏️ Keterangan Lainnya</option>
                    </select>
                </div>

                <!-- Keterangan jika ganti hari -->
                <div x-show="statusPelaksanaan !== 'sesuai_jadwal'">
                    <label class="block font-bold text-rose-700 uppercase tracking-wider mb-1.5">
                        Keterangan Ganti Hari / Alasan
                    </label>
                    <input type="text" name="keterangan_pelaksanaan" placeholder="Contoh: Mengganti jam tanggal 02/09 karena dinas luar..." class="w-full p-3 bg-rose-50 border border-rose-300 rounded-xl text-xs font-semibold text-rose-900">
                </div>
            </div>
        </div>

        <!-- Card 2: Sinkronisasi Rencana Pembelajaran (Modul Ajar / RPP) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 text-xs flex items-center justify-center font-black">2</span>
                    <span class="font-extrabold text-sm text-slate-900">Sinkronisasi Rencana Pembelajaran (Modul Ajar)</span>
                </div>
                <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-bold text-[11px] border border-blue-200">
                    Otomatis Menyesuaikan Yang Direncanakan
                </span>
            </div>

            <!-- Pemilihan RPP Terdaftar untuk Jadwal Ini -->
            @if($rencanaList->isEmpty())
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-900 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <strong>Belum Ada Rencana Pembelajaran (RPP) yang Dibuat untuk Jadwal Ini.</strong>
                    <p class="text-[11px] text-amber-700 mt-0.5">Anda tetap dapat mengisi realisasi secara manual, atau buat Modul Ajar terlebih dahulu agar tersinkronisasi otomatis.</p>
                </div>
                <a href="{{ route('guru.rencana-pembelajaran.rpp.create', ['jadwal_id' => $selectedJadwal->id]) }}" target="_blank" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl whitespace-nowrap shadow-xs">
                    + Buat RPP Jadwal Ini
                </a>
            </div>
            @else
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider text-xs mb-1.5">
                    Pilih Modul Ajar / RPP yang Direalisasikan Pada Pertemuan Ini:
                </label>
                <select x-model="selectedRencanaId" @change="updateCurrentRencana()" 
                        class="w-full p-3 bg-blue-50/50 border-2 border-blue-300 rounded-2xl font-bold text-xs text-slate-900 focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Tanpa Rencana (Input Realisasi Mandiri) --</option>
                    <template x-for="r in rencanaList" :key="r.id">
                        <option :value="r.id" x-text="'Pertemuan Ke-' + r.pertemuan_ke + ': ' + r.materi_pokok + (r.tp_kode ? ' [' + r.tp_kode + ']' : '')"></option>
                    </template>
                </select>
            </div>

            <!-- Panel Rincian Rencana yang Terpilih -->
            <template x-if="currentRencana">
                <div class="p-4 bg-gradient-to-br from-slate-50 to-blue-50/40 rounded-2xl border border-blue-200 space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md bg-blue-600 text-white font-black text-[10px] uppercase">
                                RPP Terhubung
                            </span>
                            <span class="font-extrabold text-sm text-slate-900" x-text="'Pertemuan Ke-' + currentRencana.pertemuan_ke + ' • ' + currentRencana.materi_pokok"></span>
                        </div>
                        <span class="text-[11px] text-slate-500" x-text="'Tanggal Rencana: ' + currentRencana.tanggal_rencana"></span>
                    </div>

                    <!-- Poin TP & KKTP Target -->
                    <template x-if="currentRencana.tp_kode">
                        <div class="p-3 bg-white rounded-xl border border-slate-200 text-xs space-y-1">
                            <div class="font-bold text-blue-800 flex items-center gap-1.5">
                                <i class="bi-bullseye"></i>
                                <span>Tujuan Pembelajaran (<span x-text="currentRencana.tp_kode"></span>):</span>
                            </div>
                            <p class="text-slate-700 text-[11px]" x-text="currentRencana.tp_deskripsi"></p>
                            <template x-if="currentRencana.tp_kktp">
                                <div class="text-[10.5px] text-emerald-700 font-semibold pt-1 border-t border-slate-100">
                                    <strong>Target KKTP:</strong> <span x-text="currentRencana.tp_kktp"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    <div class="flex items-center gap-3 text-[11px] text-slate-600 flex-wrap">
                        <template x-if="currentRencana.bentuk_asesmen">
                            <span><strong>Bentuk Asesmen:</strong> <span x-text="currentRencana.bentuk_asesmen"></span></span>
                        </template>
                        <template x-if="currentRencana.media_sumber">
                            <span>&bull; <strong>Media/Sumber:</strong> <span x-text="currentRencana.media_sumber"></span></span>
                        </template>
                    </div>
                </div>
            </template>
            @endif

            <!-- Form Realisasi Menyesuaikan Rencana -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tingkat Kesesuaian Realisasi <span class="text-rose-500">*</span></label>
                    <select name="kesesuaian_rencana" x-model="kesesuaianRencana" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-emerald-600">
                        <option value="sesuai">🌟 Sesuai Penuh Dengan Rencana Modul Ajar</option>
                        <option value="sebagian">⚠️ Sebagian Tercapai (Materi Tertunda/Perlu Lanjutan)</option>
                        <option value="materi_pengganti">🔀 Diberikan Materi Pengganti / Penyesuaian</option>
                        <option value="tidak_sesuai">❌ Tidak Sesuai Rencana</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Evaluasi / Ketercapaian Target TP</label>
                    <input type="text" name="keterangan_kesesuaian" x-model="keteranganKesesuaian" placeholder="Misal: Seluruh siswa tuntas praktikum jobsheet 1..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-emerald-600">
                </div>
            </div>

            <div class="text-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block font-bold text-slate-700 uppercase tracking-wider">Ringkasan Aktivitas KBM & Jurnal Mengajar</label>
                    <span class="text-[10px] text-slate-400">Otomatis terisi dari aktivitas RPP, dapat Anda edit atau tambahkan catatan</span>
                </div>
                <textarea name="catatan_kegiatan" x-model="catatanKegiatan" rows="4" placeholder="Tuliskan jalannya pembelajaran, aktivitas praktikum, keaktifan siswa..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium leading-relaxed focus:ring-2 focus:ring-emerald-600"></textarea>
            </div>
        </div>

        <!-- Card 3: Ceklis Kehadiran Siswa (Diambil dari Data Siswa SMK Plus Al-Hilal) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b pb-4">
                <div>
                    <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 text-xs flex items-center justify-center font-black">3</span>
                        <span>Ceklis Presensi Siswa Kelas {{ $selectedJadwal->kelas }} ({{ $students->count() }} Siswa)</span>
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Data siswa ditarik otomatis dari database siswa aktif SMK Plus Al-Hilal</p>
                </div>

                <!-- Live Counter Badges & Quick Action -->
                <div class="flex items-center gap-2 text-xs">
                    <button type="button" @click="setAll('hadir')" class="px-3 py-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-900 font-bold rounded-xl transition">
                        ✓ Semua Hadir
                    </button>
                    <div class="px-3 py-1.5 bg-emerald-50 text-emerald-800 rounded-xl font-bold border border-emerald-200">
                        Hadir: <span x-text="hadirCount" class="font-black text-sm"></span>
                    </div>
                    <div class="px-3 py-1.5 bg-rose-50 text-rose-800 rounded-xl font-bold border border-rose-200">
                        Tidak Hadir: <span x-text="tidakHadirCount" class="font-black text-sm"></span>
                    </div>
                </div>
            </div>

            @if($students->isEmpty())
            <div class="p-6 text-center text-slate-400 text-xs">
                Tidak ada data siswa yang terdaftar di kelas {{ $selectedJadwal->kelas }}.
            </div>
            @else
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 sticky top-0 border-b border-slate-200 font-bold uppercase text-[10px] text-slate-500 z-10">
                        <tr>
                            <th class="px-4 py-3 text-center" width="5%">No</th>
                            <th class="px-4 py-3" width="15%">NIS</th>
                            <th class="px-4 py-3" width="35%">Nama Siswa</th>
                            <th class="px-4 py-3 text-center" width="25%">Status Presensi</th>
                            <th class="px-4 py-3" width="20%">Catatan / Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($students as $idx => $s)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2.5 text-center text-slate-400 font-bold">{{ $idx + 1 }}</td>
                            <td class="px-4 py-2.5 font-mono text-slate-600">{{ $s->nis }}</td>
                            <td class="px-4 py-2.5 font-bold text-slate-900">{{ $s->nama_lengkap }}</td>
                            <td class="px-4 py-2.5 text-center">
                                <div class="inline-flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl">
                                    <label class="px-2 py-1 rounded-lg cursor-pointer text-[11px] font-bold transition has-[:checked]:bg-emerald-600 has-[:checked]:text-white">
                                        <input type="radio" name="presensi[{{ $s->user_id }}]" value="hadir" checked @change="updateCounts()" class="sr-only presensi-radio">
                                        <span>H</span>
                                    </label>
                                    <label class="px-2 py-1 rounded-lg cursor-pointer text-[11px] font-bold transition has-[:checked]:bg-blue-600 has-[:checked]:text-white">
                                        <input type="radio" name="presensi[{{ $s->user_id }}]" value="izin" @change="updateCounts()" class="sr-only presensi-radio">
                                        <span>I</span>
                                    </label>
                                    <label class="px-2 py-1 rounded-lg cursor-pointer text-[11px] font-bold transition has-[:checked]:bg-purple-600 has-[:checked]:text-white">
                                        <input type="radio" name="presensi[{{ $s->user_id }}]" value="sakit" @change="updateCounts()" class="sr-only presensi-radio">
                                        <span>S</span>
                                    </label>
                                    <label class="px-2 py-1 rounded-lg cursor-pointer text-[11px] font-bold transition has-[:checked]:bg-rose-600 has-[:checked]:text-white">
                                        <input type="radio" name="presensi[{{ $s->user_id }}]" value="alpa" @change="updateCounts()" class="sr-only presensi-radio">
                                        <span>A</span>
                                    </label>
                                    <label class="px-2 py-1 rounded-lg cursor-pointer text-[11px] font-bold transition has-[:checked]:bg-amber-600 has-[:checked]:text-white">
                                        <input type="radio" name="presensi[{{ $s->user_id }}]" value="terlambat" @change="updateCounts()" class="sr-only presensi-radio">
                                        <span>T</span>
                                    </label>
                                </div>
                            </td>
                            <td class="px-4 py-2.5">
                                <input type="text" name="keterangan_siswa[{{ $s->user_id }}]" placeholder="Keterangan jika I/S/A..." class="w-full p-1.5 bg-slate-50 border rounded-lg text-xs">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- Card 4: Upload Foto Dokumentasi (Opsional) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
            <h3 class="font-extrabold text-sm text-slate-900 border-b pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 text-xs flex items-center justify-center font-black">4</span>
                <span>Dokumentasi KBM (Opsional)</span>
            </h3>
            <div class="text-xs">
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Foto Suasana Kelas / Hasil Karya Praktikum</label>
                <input type="file" name="foto" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-800 hover:file:bg-emerald-100">
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('guru.laporan-kbm.index') }}" class="px-5 py-3 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-2">
                <i class="bi-check-circle-fill text-base"></i>
                <span>Simpan Laporan KBM & Presensi Siswa</span>
            </button>
        </div>

    </form>
    @endif

</div>
@endsection
