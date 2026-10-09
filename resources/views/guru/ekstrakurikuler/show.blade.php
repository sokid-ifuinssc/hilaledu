@php
    $eskul = $eskul ?? $ekstrakurikuler ?? null;
@endphp
@extends('layouts.app')

@section('title', 'Kelola Eskul: ' . ($eskul->nama ?? '-'))

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: '{{ $tab ?? 'anggota' }}',
    modalRencana: false,
    modalLaporan: false,
    modalAnggota: false,
    rencanaTipeJadwal: 'minggu_efektif',
    selectedRencanaId: '',
    rencanasData: {{ Js::from($eskul->rencanas ?? $eskul->rencanaKegiatans ?? $rencanas ?? []) }},
    laporanTanggal: '{{ date('Y-m-d') }}',
    laporanPertemuan: 1,
    laporanNama: '',
    laporanMateri: '',
    laporanTipeJadwal: 'minggu_efektif',
    laporanMingguKe: 1,
    kelasFilter: '',
    searchSiswa: '',

    onSelectRencana(id) {
        if(!id) return;
        const found = this.rencanasData.find(r => r.id == id);
        if(found) {
            this.laporanTanggal = found.tanggal_rencana ? String(found.tanggal_rencana).substring(0, 10) : '';
            this.laporanPertemuan = found.pertemuan_ke;
            this.laporanNama = found.nama_kegiatan;
            this.laporanMateri = found.deskripsi_rencana || '';
            this.laporanTipeJadwal = found.tipe_jadwal || 'minggu_efektif';
            this.laporanMingguKe = found.minggu_ke || 1;
        }
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('guru.ekstrakurikuler.index') }}" class="hover:text-indigo-600 transition-colors">Eskul Binaan</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <span class="text-slate-800 font-medium">{{ $eskul->nama }}</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white shadow-md shadow-indigo-200">
                    <i class="bi bi-stars text-lg"></i>
                </span>
                {{ $eskul->nama }}
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Jadwal: <b>{{ $eskul->hari ?: '-' }}</b> ({{ substr($eskul->jam_mulai,0,5) }} - {{ substr($eskul->jam_selesai,0,5) }}) • Lokasi: <b>{{ $eskul->tempat ?: '-' }}</b>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.ekstrakurikuler.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 transition shadow-sm">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Info Banner Ketua Eskul -->
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/70 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                <i class="bi bi-person-badge"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Ketua Eskul yang Ditugaskan</span>
                <h4 class="text-sm font-bold text-slate-800">
                    {{ $eskul->ketuaSiswa->nama ?? 'Belum ada ketua eskul yang ditunjuk' }}
                    @if($eskul->ketuaSiswa && $eskul->ketuaSiswa->kelas)
                        <span class="text-xs font-normal text-slate-500">({{ $eskul->ketuaSiswa->kelas->nama_kelas }})</span>
                    @endif
                </h4>
                <p class="text-[11px] text-slate-600">Siswa yang ditunjuk sebagai ketua memiliki hak akses di akunnya untuk mengabsen kehadiran rekan eskul.</p>
            </div>
        </div>
        <div>
            <form action="{{ route('guru.ekstrakurikuler.set-ketua', $eskul->id) }}" method="POST" class="flex items-center gap-2">
                @csrf
                <select name="ketua_siswa_id" class="px-3 py-1.5 text-xs rounded-xl border border-amber-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 bg-white">
                    <option value="">-- Ganti / Pilih Ketua --</option>
                    @foreach($eskul->anggotas as $ang)
                        <option value="{{ $ang->siswa_id }}" {{ $eskul->ketua_siswa_id == $ang->siswa_id ? 'selected' : '' }}>
                            {{ $ang->siswa->nama ?? 'Siswa' }} ({{ $ang->siswa->kelas->nama_kelas ?? '-' }})
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-1.5 text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white rounded-xl shadow-sm transition">
                    Simpan Ketua
                </button>
            </form>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 space-x-2">
        <button @click="activeTab = 'anggota'" :class="activeTab === 'anggota' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'" class="py-3 px-4 text-xs border-b-2 transition flex items-center gap-2">
            <i class="bi bi-people-fill"></i>
            Anggota & Penilaian Nilai Eskul ({{ $eskul->anggotas->count() }})
        </button>
        <button @click="activeTab = 'rencana'" :class="activeTab === 'rencana' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'" class="py-3 px-4 text-xs border-b-2 transition flex items-center gap-2">
            <i class="bi bi-calendar-check"></i>
            Rencana Kegiatan ({{ $eskul->rencanas->count() }})
        </button>
        <button @click="activeTab = 'laporan'" :class="activeTab === 'laporan' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'" class="py-3 px-4 text-xs border-b-2 transition flex items-center gap-2">
            <i class="bi bi-journal-check"></i>
            Laporan Keterlaksanaan ({{ $eskul->laporans->count() }})
        </button>
        <button @click="activeTab = 'presensi'" :class="activeTab === 'presensi' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'" class="py-3 px-4 text-xs border-b-2 transition flex items-center gap-2">
            <i class="bi bi-check2-square"></i>
            Input Presensi Pertemuan
        </button>
    </div>

    <!-- TAB 1: ANGGOTA & PENILAIAN -->
    <div x-show="activeTab === 'anggota'" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Daftar Anggota & Input Nilai Ekstrakurikuler</h3>
                <p class="text-xs text-slate-500">Nilai yang diinput otomatis terhubung dan disinkronkan ke nilai mapel <b>Team Work Project dan Project Pancasila</b> di kelas masing-masing siswa.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500 bg-slate-100 px-3 py-1.5 rounded-xl font-medium">
                    Total: <b>{{ $eskul->anggotas->count() }}</b> Siswa
                </span>
                <button type="button" @click="modalAnggota = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                    <i class="bi bi-person-plus-fill"></i>
                    Pilih / Tambah Siswa (X, XI, XII)
                </button>
            </div>
        </div>

        <form action="{{ route('guru.ekstrakurikuler.nilai.store', $eskul->id) }}" method="POST">
            @csrf
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-3 w-12 text-center">No</th>
                                <th class="px-4 py-3">Nama Siswa</th>
                                <th class="px-4 py-3">Kelas</th>
                                <th class="px-4 py-3 text-center">Jabatan</th>
                                <th class="px-4 py-3 text-center">Kehadiran (H/Total)</th>
                                <th class="px-4 py-3 text-center w-24">Nilai Angka</th>
                                <th class="px-4 py-3 text-center w-24">Predikat</th>
                                <th class="px-4 py-3">Catatan / Deskripsi Perkembangan</th>
                                <th class="px-4 py-3 text-center w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse(($anggotas ?? $eskul->anggotas) as $index => $ang)
                                @php
                                    $stats = $ang->statistik_kehadiran;
                                    $persen = $ang->persentase_kehadiran;
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-4 py-3 text-center text-slate-500 font-medium">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-bold text-slate-800 whitespace-nowrap">
                                        {{ $ang->siswa->nama ?? 'Siswa' }}
                                        <span class="block text-[11px] text-slate-400 font-normal">NISN: {{ $ang->siswa->nisn ?: '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-medium">
                                            {{ $ang->siswa->kelas->nama_kelas ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ang->jabatan == 'ketua' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                                            {{ ucfirst($ang->jabatan) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $persen >= 75 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $persen }}%
                                        </span>
                                        <span class="block text-[10px] text-slate-400 mt-0.5">({{ $stats['hadir'] ?? 0 }}/{{ $stats['total'] ?? $stats['total_sesi'] ?? 0 }} ptm)</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="number" step="0.1" min="0" max="100" name="nilai_angka[{{ $ang->siswa_id }}]" value="{{ $ang->nilai_angka }}" placeholder="0-100" class="w-20 text-center font-bold px-2 py-1.5 text-xs rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <select name="nilai_huruf[{{ $ang->siswa_id }}]" class="w-20 text-center font-bold px-2 py-1.5 text-xs rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                            <option value="">-</option>
                                            <option value="A" {{ $ang->nilai_huruf == 'A' ? 'selected' : '' }}>A (Sangat Baik)</option>
                                            <option value="B" {{ $ang->nilai_huruf == 'B' ? 'selected' : '' }}>B (Baik)</option>
                                            <option value="C" {{ $ang->nilai_huruf == 'C' ? 'selected' : '' }}>C (Cukup)</option>
                                            <option value="D" {{ $ang->nilai_huruf == 'D' ? 'selected' : '' }}>D (Kurang)</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="catatan[{{ $ang->siswa_id }}]" value="{{ $ang->catatan_nilai }}" placeholder="Contoh: Sangat aktif dalam kegiatan..." class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" onclick="if(confirm('Keluarkan {{ addslashes($ang->siswa->nama ?? 'siswa ini') }} dari eskul?')) document.getElementById('formHapusAnggota_{{ $ang->id }}').submit();" class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition" title="Keluarkan siswa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-slate-400">
                                        Belum ada anggota siswa yang terdaftar dalam eskul ini. Klik "Pilih / Tambah Siswa" di atas untuk menambahkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($eskul->anggotas->count() > 0)
                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <span class="text-xs text-indigo-700 flex items-center gap-1.5">
                            <i class="bi bi-arrow-repeat"></i>
                            Nilai otomatis terhubung ke Mapel Team Work Project dan Project Pancasila.
                        </span>
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                            <i class="bi bi-save"></i>
                            Simpan Perubahan Nilai Eskul
                        </button>
                    </div>
                @endif
            </div>
        </form>

        <!-- Hidden Forms untuk Hapus Anggota -->
        @foreach(($anggotas ?? $eskul->anggotas) as $ang)
            <form id="formHapusAnggota_{{ $ang->id }}" action="{{ route('guru.ekstrakurikuler.anggota.destroy', [$eskul->id, $ang->id]) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    </div>

    <!-- TAB 2: RENCANA KEGIATAN -->
    <div x-show="activeTab === 'rencana'" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Rencana Program & Pertemuan Eskul</h3>
                <p class="text-xs text-slate-500">Input rencana kegiatan per pertemuan. Data rencana ini akan otomatis mengisi tanggal dan materi pada Laporan Kegiatan.</p>
            </div>
            <button @click="modalRencana = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                <i class="bi bi-plus-lg"></i>
                Tambah Rencana Kegiatan
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3 w-16 text-center">Pertemuan</th>
                            <th class="px-4 py-3">Jadwal Minggu Efektif</th>
                            <th class="px-4 py-3">Tanggal Rencana</th>
                            <th class="px-4 py-3">Nama Kegiatan</th>
                            <th class="px-4 py-3">Deskripsi Rencana Materi</th>
                            <th class="px-4 py-3">Target Pencapaian</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($eskul->rencanas as $ren)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3 text-center font-bold text-indigo-600">#{{ $ren->pertemuan_ke }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if(($ren->tipe_jadwal ?? '') === 'kegiatan_tambahan')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <i class="bi bi-star-fill text-[9px] text-amber-600"></i> Kegiatan Tambahan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                            <i class="bi bi-calendar-week text-[10px]"></i> Minggu Efektif #{{ $ren->minggu_ke ?: $ren->pertemuan_ke }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-800 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($ren->tanggal_rencana)->format('d M Y') }}
                                    <span class="block text-[11px] text-slate-400 font-normal">{{ \Carbon\Carbon::parse($ren->tanggal_rencana)->isoFormat('dddd') }}</span>
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-800">{{ $ren->nama_kegiatan }}</td>
                                <td class="px-4 py-3 text-slate-600 max-w-xs">{{ $ren->deskripsi_rencana ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 max-w-xs">{{ $ren->target_pencapaian ?: '-' }}</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @if($ren->status == 'terlaksana')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Terlaksana</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">Terencana</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                    Belum ada rencana kegiatan yang diinput untuk eskul ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: LAPORAN KEGIATAN -->
    <div x-show="activeTab === 'laporan'" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Laporan Keterlaksanaan Kegiatan Eskul</h3>
                <p class="text-xs text-slate-500">Laporan kegiatan eskul mengikuti jadwal minggu efektif sekolah atau kegiatan tambahan luar jadwal.</p>
            </div>
            <button @click="modalLaporan = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition">
                <i class="bi bi-journal-plus"></i>
                Input Laporan Kegiatan Baru
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3">Tanggal & Pertemuan</th>
                            <th class="px-4 py-3">Jadwal Minggu Efektif</th>
                            <th class="px-4 py-3">Nama Kegiatan & Ringkasan</th>
                            <th class="px-4 py-3">Kehadiran Peserta</th>
                            <th class="px-4 py-3">Dokumentasi</th>
                            <th class="px-4 py-3">Catatan / Kendala</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($eskul->laporans as $lap)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($lap->tanggal_kegiatan)->format('d M Y') }}</span>
                                    <span class="block text-[11px] text-indigo-600 font-semibold">Pertemuan #{{ $lap->pertemuan_ke }}</span>
                                    @if($lap->jam_mulai)
                                        <span class="text-[10px] text-slate-400">{{ substr($lap->jam_mulai,0,5) }} - {{ substr($lap->jam_selesai,0,5) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if(($lap->tipe_jadwal ?? '') === 'kegiatan_tambahan')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <i class="bi bi-star-fill text-[9px] text-amber-600"></i> Kegiatan Tambahan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                            <i class="bi bi-calendar-week text-[10px]"></i> Minggu Efektif #{{ $lap->minggu_ke ?: $lap->pertemuan_ke }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 max-w-sm">
                                    <h4 class="font-bold text-slate-800">{{ $lap->nama_kegiatan }}</h4>
                                    <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5">{{ $lap->ringkasan_materi ?: 'Tidak ada ringkasan materi.' }}</p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="font-bold text-emerald-700">{{ $lap->jumlah_hadir }} Hadir</span>
                                        <span class="text-slate-400">|</span>
                                        <span class="text-blue-600">{{ $lap->jumlah_izin }} Izin</span>
                                        <span class="text-amber-600">{{ $lap->jumlah_sakit }} Sakit</span>
                                        <span class="text-rose-600">{{ $lap->jumlah_alpa }} Alpa</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($lap->foto_kegiatan)
                                        <a href="{{ asset('storage/' . $lap->foto_kegiatan) }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-semibold hover:bg-indigo-100 transition">
                                            <i class="bi bi-image"></i>
                                            Lihat Foto
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic">Tanpa foto</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600 max-w-xs">
                                    {{ $lap->kendala_catatan ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                    Belum ada laporan kegiatan yang diinput.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 4: PRESENSI PERTEMUAN -->
    <div x-show="activeTab === 'presensi'" class="space-y-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200">
            <h3 class="text-sm font-bold text-slate-800 mb-1">Input Presensi Pertemuan Eskul oleh Pembina</h3>
            <p class="text-xs text-slate-500 mb-3">Pilih tanggal kegiatan, lalu tandai status kehadiran masing-masing anggota eskul.</p>
            
            <div class="p-3 bg-indigo-50/70 border border-indigo-200/80 rounded-xl mb-4 text-xs text-indigo-900 flex items-center gap-2.5">
                <i class="bi bi-link-45deg text-lg text-indigo-600"></i>
                <span><b>Otomatis Terintegrasi:</b> Setiap siswa yang diabsen di sini akan langsung tercatat sebagai kehadiran pada pertemuan mapel <b>Team Work Project dan Project Pancasila</b> di kelasnya masing-masing.</span>
            </div>
            
            <form action="{{ route('guru.ekstrakurikuler.presensi.store', $eskul->id) }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Presensi</label>
                        <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold">
                    </div>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-hidden mb-4">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-2.5 w-12 text-center">No</th>
                                <th class="px-4 py-2.5">Nama Siswa</th>
                                <th class="px-4 py-2.5">Kelas</th>
                                <th class="px-4 py-2.5 text-center">Hadir</th>
                                <th class="px-4 py-2.5 text-center">Izin</th>
                                <th class="px-4 py-2.5 text-center">Sakit</th>
                                <th class="px-4 py-2.5 text-center">Alpa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach(($anggotas ?? $eskul->anggotas) as $idx => $ang)
                                <tr>
                                    <td class="px-4 py-2 text-center text-slate-500">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-2 font-bold text-slate-800">{{ $ang->siswa->nama ?? 'Siswa' }}</td>
                                    <td class="px-4 py-2 text-slate-600">{{ $ang->siswa->kelas->nama_kelas ?? '-' }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <input type="radio" name="presensi[{{ $ang->siswa_id }}]" value="Hadir" checked class="text-emerald-600 focus:ring-emerald-500">
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <input type="radio" name="presensi[{{ $ang->siswa_id }}]" value="Izin" class="text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <input type="radio" name="presensi[{{ $ang->siswa_id }}]" value="Sakit" class="text-amber-600 focus:ring-amber-500">
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <input type="radio" name="presensi[{{ $ang->siswa_id }}]" value="Alpa" class="text-rose-600 focus:ring-rose-500">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition">
                        <i class="bi bi-check2-circle"></i>
                        Simpan Presensi Pertemuan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL TAMBAH RENCANA (MINGGU EFEKTIF / KEGIATAN TAMBAHAN) -->
    <div x-show="modalRencana" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalRencana = false"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-calendar-plus text-indigo-600"></i>
                        Tambah Rencana Kegiatan
                    </h3>
                    <button @click="modalRencana = false" class="text-slate-400 hover:text-slate-600">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <form action="{{ route('guru.ekstrakurikuler.rencana.store', $eskul->id) }}" method="POST" class="mt-4 space-y-4">
                    @csrf

                    <!-- Pilihan Minggu Efektif vs Kegiatan Tambahan -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Jadwal Minggu Efektif vs Tambahan</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-2 rounded-lg border cursor-pointer text-xs" :class="rencanaTipeJadwal === 'minggu_efektif' ? 'bg-indigo-50 border-indigo-300 text-indigo-900 font-bold' : 'bg-white border-slate-200 text-slate-600'">
                                <input type="radio" name="tipe_jadwal" value="minggu_efektif" x-model="rencanaTipeJadwal" class="text-indigo-600">
                                <span>Minggu Efektif</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-lg border cursor-pointer text-xs" :class="rencanaTipeJadwal === 'kegiatan_tambahan' ? 'bg-amber-50 border-amber-300 text-amber-900 font-bold' : 'bg-white border-slate-200 text-slate-600'">
                                <input type="radio" name="tipe_jadwal" value="kegiatan_tambahan" x-model="rencanaTipeJadwal" class="text-amber-600">
                                <span>Kegiatan Tambahan</span>
                            </label>
                        </div>
                        <div x-show="rencanaTipeJadwal === 'minggu_efektif'" class="mt-2.5">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Minggu Efektif Semester Ke-</label>
                            <input type="number" name="minggu_ke" min="1" max="30" value="{{ ($eskul->rencanas->where('tipe_jadwal', '!=', 'kegiatan_tambahan')->count() ?? 0) + 1 }}" placeholder="Contoh: 1, 2, 3..." class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200">
                        </div>
                        <div x-show="rencanaTipeJadwal === 'kegiatan_tambahan'" class="mt-2 text-[11px] text-amber-700">
                            <i class="bi bi-info-circle mr-1"></i>Kegiatan ekstra di luar jadwal minggu efektif resmi (misal: persiapan lomba, turnamen, diklat, sparring, TC).
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Pertemuan Ke-*</label>
                            <input type="number" name="pertemuan_ke" value="{{ ($eskul->rencanas->max('pertemuan_ke') ?? 0) + 1 }}" required min="1" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Rencana-*</label>
                            <input type="date" name="tanggal_rencana" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Kegiatan-*</label>
                        <input type="text" name="nama_kegiatan" placeholder="Contoh: Latihan Dasar Formasi Baris-Berbaris" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Deskripsi Rencana Materi</label>
                        <textarea name="deskripsi_rencana" rows="3" placeholder="Rincian materi kegiatan yang akan dibahas..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Target Pencapaian</label>
                        <input type="text" name="target_pencapaian" placeholder="Contoh: 100% siswa memahami gerakan hormat & langkah tegap" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modalRencana = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm">Simpan Rencana</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH LAPORAN (AUTODETECT TANGGAL DARI RENCANA & MINGGU EFEKTIF) -->
    <div x-show="modalLaporan" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalLaporan = false"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-2xl max-w-xl w-full p-6 shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-journal-plus text-emerald-600"></i>
                        Input Laporan Kegiatan Eskul
                    </h3>
                    <button @click="modalLaporan = false" class="text-slate-400 hover:text-slate-600">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form action="{{ route('guru.ekstrakurikuler.laporan.store', $eskul->id) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    
                    <!-- Pilih Dari Rencana (Autofill Otomatis Tanggal & Materi & Minggu Efektif) -->
                    <div class="p-3 bg-indigo-50/60 rounded-xl border border-indigo-100">
                        <label class="block text-xs font-bold text-indigo-900 mb-1">
                            Pilih Dari Rencana Kegiatan (Tanggal & Rincian Terisi Otomatis)
                        </label>
                        <select name="rencana_kegiatan_id" x-model="selectedRencanaId" @change="onSelectRencana($event.target.value)" class="w-full px-3 py-2 text-xs rounded-lg border border-indigo-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white">
                            <option value="">-- Input Bebas / Tanpa Mengaitkan Rencana --</option>
                            @foreach($eskul->rencanas as $ren)
                                <option value="{{ $ren->id }}">
                                    Pertemuan #{{ $ren->pertemuan_ke }} ({{ \Carbon\Carbon::parse($ren->tanggal_rencana)->format('d M Y') }}) [{{ ($ren->tipe_jadwal ?? '') === 'kegiatan_tambahan' ? 'Kegiatan Tambahan' : 'Minggu Efektif #' . ($ren->minggu_ke ?: $ren->pertemuan_ke) }}] - {{ $ren->nama_kegiatan }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-indigo-700 mt-1">
                            <i class="bi bi-magic mr-1"></i>Ketika rencana dipilih, tanggal, pertemuan, jadwal minggu efektif, dan materi otomatis terisi di bawah.
                        </p>
                    </div>

                    <!-- Pilihan Minggu Efektif vs Kegiatan Tambahan di Laporan -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Tipe Jadwal Kegiatan</label>
                            <select name="tipe_jadwal" x-model="laporanTipeJadwal" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white font-semibold">
                                <option value="minggu_efektif">Minggu Efektif (Mapel)</option>
                                <option value="kegiatan_tambahan">Kegiatan Tambahan (Luar)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Minggu Efektif Ke-</label>
                            <input type="number" name="minggu_ke" x-model="laporanMingguKe" :disabled="laporanTipeJadwal === 'kegiatan_tambahan'" min="1" max="30" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Pelaksanaan-*</label>
                            <input type="date" name="tanggal_kegiatan" x-model="laporanTanggal" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Pertemuan Ke-*</label>
                            <input type="number" name="pertemuan_ke" x-model="laporanPertemuan" required min="1" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Jam Mulai</label>
                            <input type="time" name="jam_mulai" value="{{ substr($eskul->jam_mulai,0,5) ?: '15:30' }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Jam Selesai</label>
                            <input type="time" name="jam_selesai" value="{{ substr($eskul->jam_selesai,0,5) ?: '17:00' }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Kegiatan-*</label>
                        <input type="text" name="nama_kegiatan" x-model="laporanNama" placeholder="Nama materi / kegiatan yang dilaksanakan" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Ringkasan Materi & Kegiatan Terlaksana-*</label>
                        <textarea name="ringkasan_materi" x-model="laporanMateri" rows="3" required placeholder="Rangkuman kegiatan eskul hari ini..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Foto Dokumentasi Kegiatan (Opsional)</label>
                        <input type="file" name="foto_dokumentasi" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan Kendala / Evaluasi</label>
                        <input type="text" name="catatan_kegiatan" placeholder="Contoh: Hujan gerimis, latihan dipindahkan ke aula" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modalLaporan = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-sm">Simpan Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PILIH / TAMBAH ANGGOTA (SEMUA SISWA X, XI, XII) -->
    <div x-show="modalAnggota" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalAnggota = false"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-2xl max-w-2xl w-full p-6 shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="bi bi-person-plus-fill text-indigo-600"></i>
                            Pilih & Tambah Siswa ke {{ $eskul->nama }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih siswa dari seluruh kelas X, XI, dan XII yang ada di sekolah.</p>
                    </div>
                    <button @click="modalAnggota = false" class="text-slate-400 hover:text-slate-600">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form action="{{ route('guru.ekstrakurikuler.anggota.store', $eskul->id) }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Filter Tingkat / Kelas</label>
                            <select x-model="kelasFilter" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white">
                                <option value="">Semua Tingkat (X, XI, XII)</option>
                                @foreach($kelases as $kls)
                                    <option value="{{ $kls->id }}">{{ $kls->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Nama / NISN</label>
                            <input type="text" x-model="searchSiswa" placeholder="Ketik nama siswa..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Jabatan Awal</label>
                            <select name="jabatan" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white">
                                <option value="Anggota">Anggota</option>
                                <option value="Wakil Ketua">Wakil Ketua</option>
                                <option value="Sekretaris">Sekretaris</option>
                                <option value="Bendahara">Bendahara</option>
                            </select>
                        </div>
                    </div>

                    <!-- Daftar Siswa Tersedia -->
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <div class="p-2.5 bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 flex justify-between items-center">
                            <span>Daftar Siswa Yang Tersedia</span>
                            <span class="text-[11px] text-slate-400">Centang kotak untuk memilih</span>
                        </div>
                        <div class="max-h-64 overflow-y-auto divide-y divide-slate-100 p-2 space-y-1">
                            @forelse($availableSiswas as $s)
                                <label x-show="(!kelasFilter || '{{ $s->kelas_id }}' == kelasFilter) && (!searchSiswa || '{{ strtolower($s->name) }}'.includes(searchSiswa.toLowerCase()) || '{{ $s->nisn }}'.includes(searchSiswa))" class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 cursor-pointer transition">
                                    <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <div class="flex-1">
                                        <span class="text-xs font-bold text-slate-800">{{ $s->name }}</span>
                                        <span class="text-[11px] text-slate-500 ml-2">({{ $s->kelas->nama_kelas ?? 'Kelas Tidak Diset' }})</span>
                                        @if($s->nisn)
                                            <span class="text-[10px] text-slate-400 ml-1">NISN: {{ $s->nisn }}</span>
                                        @endif
                                    </div>
                                </label>
                            @empty
                                <p class="text-xs text-slate-400 text-center py-4">Semua siswa aktif sudah terdaftar di eskul ini.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modalAnggota = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm">Tambahkan Siswa Terpilih</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
