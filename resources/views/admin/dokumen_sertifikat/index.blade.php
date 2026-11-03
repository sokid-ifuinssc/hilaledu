@extends('layouts.app')

@section('title', $config['title'] . ' - Dokumen & Sertifikat')
@section('page-title', 'Dokumen & Sertifikat: ' . $config['short'])

@section('content')
@php
    $firstSampleSiswa = $allFilteredSiswas->first();
    $sampleSiswaNama = $firstSampleSiswa ? $firstSampleSiswa->name : 'AHMAD FAUZI';
    $sampleSiswaNisn = $firstSampleSiswa ? ($firstSampleSiswa->nisn ?: $firstSampleSiswa->username) : '0081234567';
    $sampleSiswaKelas = $firstSampleSiswa && $firstSampleSiswa->kelas
        ? ($firstSampleSiswa->kelas->nama_kelas ?? ($firstSampleSiswa->kelas->nama ?? 'XII TKJ 1'))
        : 'XII TKJ 1';
    $initialPejabat1Id = $defaultKepsekId
        ? (string) $defaultKepsekId
        : (!empty($daftarPejabat) ? (string) $daftarPejabat[0]['id'] : 'manual');
    $initialPejabat1Jabatan = $defaultKepsekJabatan ?? 'Kepala SMK Plus Al Hilal';
    $initialPejabat1Nama = $defaultKepsekNama ?: (!empty($daftarPejabat) ? $daftarPejabat[0]['nama'] : '');
    $initialPejabat1Nip = $defaultKepsekNip ?: (!empty($daftarPejabat) ? $daftarPejabat[0]['nip'] : '');
@endphp
<div class="space-y-6" x-data="{
    modalUpload: false,
    modalEditJenis: false,
    modalPreview: false,
    modalAksesSiswa: false,
    modalBulkAkses: false,
    modalGenerateMassal: false,
    modalImportData: false,
    genModeNamaBaru: {{ $jenisSertifikats->isEmpty() ? 'true' : 'false' }},
    impModeNamaBaru: {{ $jenisSertifikats->isEmpty() ? 'true' : 'false' }},
    daftarPejabat: @js($daftarPejabat ?? []),
    masterJenisMap: @js($jenisSertifikats->pluck('nama_sertifikat', 'id')),
    showLivePreview: true,
    certForm: {
        jenis_sertifikat_id: '{{ $jenisSertifikats->first()->id ?? '' }}',
        nama_sertifikat_baru: '',
        format_nomor: '421.5/{NO}/SERT-AH/{{ date('Y') }}',
        tempat_terbit: 'Cirebon',
        tanggal_terbit: '{{ now()->translatedFormat('d F Y') }}',
        deskripsi_kegiatan: '',
        pejabat1_id: '{{ $initialPejabat1Id }}',
        pejabat_1_jabatan: @js($initialPejabat1Jabatan),
        pejabat_1_nama: @js($initialPejabat1Nama),
        pejabat_1_nip: @js($initialPejabat1Nip),
        pejabat2_id: 'manual',
        pejabat_2_jabatan: @js($kategori === 'kepesantrenan' ? 'Ketua Lembaga Kepesantrenan' : 'Ketua Lembaga Kewirausahaan'),
        pejabat_2_nama: '',
        pejabat_2_nip: '',
        sample_siswa_nama: @js($sampleSiswaNama),
        sample_siswa_nisn: @js($sampleSiswaNisn),
        sample_siswa_kelas: @js($sampleSiswaKelas),
        sample_predikat: 'Sangat Baik'
    },
    onSelectPejabat1(val) {
        this.certForm.pejabat1_id = val;
        if (val === 'manual') {
            return;
        }
        const found = this.daftarPejabat.find(p => String(p.id) === String(val));
        if (found) {
            this.certForm.pejabat_1_jabatan = found.jabatan;
            this.certForm.pejabat_1_nama = found.nama;
            this.certForm.pejabat_1_nip = found.nip || '';
        }
    },
    onSelectPejabat2(val) {
        this.certForm.pejabat2_id = val;
        if (val === 'none') {
            this.certForm.pejabat_2_jabatan = '';
            this.certForm.pejabat_2_nama = '';
            this.certForm.pejabat_2_nip = '';
            return;
        }
        if (val === 'manual') {
            if (!this.certForm.pejabat_2_jabatan) {
                this.certForm.pejabat_2_jabatan = '{{ $kategori === 'kepesantrenan' ? 'Ketua Lembaga Kepesantrenan' : 'Ketua Lembaga Kewirausahaan' }}';
            }
            return;
        }
        const found = this.daftarPejabat.find(p => String(p.id) === String(val));
        if (found) {
            this.certForm.pejabat_2_jabatan = found.jabatan;
            this.certForm.pejabat_2_nama = found.nama;
            this.certForm.pejabat_2_nip = found.nip || '';
        }
    },
    onNamaPejabatInput(slot, val) {
        const clean = (val || '').trim().toLowerCase();
        if (!clean) return;
        const found = this.daftarPejabat.find(p => p.nama.toLowerCase() === clean);
        if (found) {
            if (slot === 1) {
                this.certForm.pejabat1_id = String(found.id);
                this.certForm.pejabat_1_nip = found.nip || '';
                if (!this.certForm.pejabat_1_jabatan) {
                    this.certForm.pejabat_1_jabatan = found.jabatan;
                }
            } else {
                this.certForm.pejabat2_id = String(found.id);
                this.certForm.pejabat_2_nip = found.nip || '';
                if (!this.certForm.pejabat_2_jabatan) {
                    this.certForm.pejabat_2_jabatan = found.jabatan;
                }
            }
        }
    },
    onJabatanPejabatInput(slot, val) {
        const clean = (val || '').trim().toLowerCase();
        if (!clean) return;
        const matches = this.daftarPejabat.filter(p => p.jabatan.toLowerCase() === clean);
        if (matches.length === 1) {
            const found = matches[0];
            if (slot === 1 && (!this.certForm.pejabat_1_nama || this.certForm.pejabat1_id !== 'manual')) {
                this.certForm.pejabat1_id = String(found.id);
                this.certForm.pejabat_1_nama = found.nama;
                this.certForm.pejabat_1_nip = found.nip || '';
            } else if (slot === 2 && (!this.certForm.pejabat_2_nama || this.certForm.pejabat2_id !== 'manual')) {
                this.certForm.pejabat2_id = String(found.id);
                this.certForm.pejabat_2_nama = found.nama;
                this.certForm.pejabat_2_nip = found.nip || '';
            }
        }
    },
    getNamaKegiatanPreview(isImport = false) {
        const isBaru = isImport ? this.impModeNamaBaru : this.genModeNamaBaru;
        if (isBaru) {
            return (this.certForm.nama_sertifikat_baru || '').trim() || '{{ $config['placeholder'] }}';
        }
        const id = this.certForm.jenis_sertifikat_id;
        return (id && this.masterJenisMap[id]) ? this.masterJenisMap[id] : '{{ $config['placeholder'] }}';
    },
    getNomorPreview() {
        const fmt = (this.certForm.format_nomor || '').trim();
        if (!fmt) return '421.5/001/SERT-AH/{{ date('Y') }}';
        return fmt.includes('{NO}') ? fmt.replace('{NO}', '001') : fmt;
    },
    openDraftPdfPreview(isImport = false) {
        const params = new URLSearchParams({
            nama_kegiatan: this.getNamaKegiatanPreview(isImport),
            format_nomor: this.certForm.format_nomor || '',
            tempat_terbit: this.certForm.tempat_terbit || 'Cirebon',
            tanggal_terbit: this.certForm.tanggal_terbit || '',
            deskripsi_kegiatan: this.certForm.deskripsi_kegiatan || '',
            predikat: this.certForm.sample_predikat || 'Sangat Baik',
            nama_siswa: this.certForm.sample_siswa_nama || 'CONTOH NAMA SISWA',
            nisn: this.certForm.sample_siswa_nisn || '0081234567',
            kelas: this.certForm.sample_siswa_kelas || 'XII TKJ 1',
            pejabat_1_jabatan: this.certForm.pejabat_1_jabatan || '',
            pejabat_1_nama: this.certForm.pejabat_1_nama || '',
            pejabat_1_nip: this.certForm.pejabat_1_nip || '',
            pejabat_2_jabatan: this.certForm.pejabat_2_jabatan || '',
            pejabat_2_nama: this.certForm.pejabat_2_nama || '',
            pejabat_2_nip: this.certForm.pejabat_2_nip || ''
        });
        const url = '{{ route('dokumen-sertifikat.generate.preview', $kategori) }}?' + params.toString();
        this.openPreviewModal(url, 'Preview Hasil Sertifikat PDF — ' + this.getNamaKegiatanPreview(isImport));
    },
    setAllPredikat(val) {
        document.querySelectorAll('.select-predikat-siswa').forEach(el => el.value = val);
        if (val) this.certForm.sample_predikat = val;
    },
    previewUrl: '',
    previewTitle: '',
    selectedSiswaId: '',
    selectedSiswaName: '',
    selectedSiswaKelas: '',
    selectedSiswaNisn: '',
    aksesSiswa: {
        id: '',
        name: '',
        kelas: '',
        nisn: '',
        catatan: 'Administrasi sekolah belum diselesaikan. Silakan hubungi bagian Tata Usaha (TU) / Administrasi.'
    },
    bulkAkses: {
        action: 'batasi',
        kelas_id: '{{ $kelasId ?? '' }}',
        catatan: 'Administrasi sekolah belum diselesaikan. Silakan hubungi bagian Tata Usaha (TU) / Administrasi.'
    },
    selectedJenisId: '{{ $jenisFilter ?: ($jenisSertifikats->first()->id ?? '') }}',
    modeNamaBaru: false,
    namaSertifikatBaru: '',
    nomorDokumen: '',
    keteranganDokumen: '',
    editJenis: {
        id: '',
        nama_sertifikat: '',
        deskripsi: ''
    },
    openUploadModal(siswa, defaultJenisId = '') {
        this.selectedSiswaId = siswa.id;
        this.selectedSiswaName = siswa.name;
        this.selectedSiswaKelas = siswa.kelas;
        this.selectedSiswaNisn = siswa.nisn;
        if (defaultJenisId) {
            this.selectedJenisId = defaultJenisId;
        }
        this.modeNamaBaru = {{ $jenisSertifikats->isEmpty() && $config['is_multiple'] ? 'true' : 'false' }};
        this.namaSertifikatBaru = '';
        this.nomorDokumen = '';
        this.keteranganDokumen = '';
        this.modalUpload = true;
    },
    openAksesModal(siswa) {
        this.aksesSiswa = {
            id: siswa.id,
            name: siswa.name,
            kelas: siswa.kelas,
            nisn: siswa.nisn,
            catatan: siswa.catatan || 'Administrasi sekolah belum diselesaikan. Silakan hubungi bagian Tata Usaha (TU) / Administrasi.'
        };
        this.modalAksesSiswa = true;
    },
    openEditJenisModal(j) {
        this.editJenis = {
            id: j.id,
            nama_sertifikat: j.nama_sertifikat,
            deskripsi: j.deskripsi || ''
        };
        this.modalEditJenis = true;
    },
    openPreviewModal(url, title) {
        this.previewUrl = url;
        this.previewTitle = title;
        this.modalPreview = true;
    }
}">

    <!-- Sub-Menu Navigation Bar (Sesuai Hak Akses Unit Admin) -->
    <div class="bg-white p-2 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-2">
        <div class="flex flex-wrap items-center gap-1.5">
            @if(in_array('kepesantrenan', $allowedKategoris ?? [], true))
            <a href="{{ route('dokumen-sertifikat.index', ['kategori' => 'kepesantrenan', 'kelas_id' => $kelasId]) }}"
               class="px-4 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2 transition {{ $kategori === 'kepesantrenan' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20 font-black' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="bi-moon-stars-fill text-sm {{ $kategori === 'kepesantrenan' ? 'text-white' : 'text-emerald-600' }}"></i>
                <span>Sertifikat Kepesantrenan</span>
            </a>
            @endif
            @if(in_array('kewirausahaan', $allowedKategoris ?? [], true))
            <a href="{{ route('dokumen-sertifikat.index', ['kategori' => 'kewirausahaan', 'kelas_id' => $kelasId]) }}"
               class="px-4 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2 transition {{ $kategori === 'kewirausahaan' ? 'bg-amber-600 text-white shadow-sm shadow-amber-600/20 font-black' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="bi-shop-window text-sm {{ $kategori === 'kewirausahaan' ? 'text-white' : 'text-amber-600' }}"></i>
                <span>Sertifikat Kewirausahaan</span>
            </a>
            @endif
            @if(in_array('skl', $allowedKategoris ?? [], true))
            <a href="{{ route('dokumen-sertifikat.index', ['kategori' => 'skl', 'kelas_id' => $kelasId]) }}"
               class="px-4 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2 transition {{ $kategori === 'skl' ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20 font-black' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="bi-file-earmark-check-fill text-sm {{ $kategori === 'skl' ? 'text-white' : 'text-blue-600' }}"></i>
                <span>SKL (Surat Keterangan Lulus)</span>
            </a>
            @endif
            @if(in_array('ijazah', $allowedKategoris ?? [], true))
            <a href="{{ route('dokumen-sertifikat.index', ['kategori' => 'ijazah', 'kelas_id' => $kelasId]) }}"
               class="px-4 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2 transition {{ $kategori === 'ijazah' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20 font-black' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="bi-mortarboard-fill text-sm {{ $kategori === 'ijazah' ? 'text-white' : 'text-indigo-600' }}"></i>
                <span>Ijazah</span>
            </a>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($config['is_multiple'])
            <button type="button"
                    @click="modalGenerateMassal = true"
                    class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm shadow-emerald-600/20 transition cursor-pointer">
                <i class="bi-magic"></i>
                <span>Generate Sertifikat Otomatis</span>
            </button>
            <button type="button"
                    @click="modalImportData = true"
                    class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm shadow-indigo-600/20 transition cursor-pointer">
                <i class="bi-file-earmark-spreadsheet-fill"></i>
                <span>Upload 1 Dokumen Data &rarr; Generate</span>
            </button>
            @endif
            <button type="button"
                    @click="modalBulkAkses = true"
                    class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer">
                <i class="bi-shield-lock-fill text-rose-600"></i>
                <span>Pengaturan Akses Administrasi Siswa</span>
            </button>
        </div>
    </div>

    <!-- Header & Statistik -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 text-[10px] font-black uppercase tracking-wider">
                    {{ $config['is_multiple'] ? 'Multi-Sertifikat PDF Per Siswa (Bisa Upload Manual atau Generate Otomatis)' : '1 File PDF Per Siswa' }}
                </span>
                @if(!$config['is_multiple'])
                <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-black uppercase tracking-wider flex items-center gap-1">
                    <i class="bi-mortarboard-fill"></i>
                    <span>Khusus Menampilkan Siswa Kelas XII</span>
                </span>
                @endif
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2 mt-1">
                <i class="{{ $config['icon'] }} text-indigo-600"></i>
                <span>{{ $config['title'] }}</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ $config['desc'] }}
            </p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <div class="bg-white px-4 py-3 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[10px] font-bold uppercase text-slate-400 block">Total Siswa</span>
                <span class="text-lg font-black text-slate-900">{{ $totalSiswa }}</span>
            </div>
            <div class="bg-white px-4 py-3 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[10px] font-bold uppercase text-emerald-600 block">Sudah Ada PDF</span>
                <span class="text-lg font-black text-emerald-600">{{ $siswaSudahUpload }}</span>
            </div>
            <div class="bg-white px-4 py-3 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[10px] font-bold uppercase text-amber-600 block">Belum Ada PDF</span>
                <span class="text-lg font-black text-amber-600">{{ $siswaBelumUpload }}</span>
            </div>
            <div class="bg-white px-4 py-3 rounded-2xl border border-rose-200 bg-rose-50/40 shadow-xs">
                <span class="text-[10px] font-bold uppercase text-rose-700 block">Akses Dibatasi</span>
                <span class="text-lg font-black text-rose-700">{{ $siswaAksesDibatasi ?? 0 }}</span>
            </div>
            <div class="bg-white px-4 py-3 rounded-2xl border border-indigo-200 bg-indigo-50/40 shadow-xs">
                <span class="text-[10px] font-bold uppercase text-indigo-700 block">Total File PDF</span>
                <span class="text-lg font-black text-indigo-700">{{ $totalFilePdf }}</span>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAHAP 1 (KHUSUS KEPESANTRENAN & KEWIRAUSAHAAN): KELOLA NAMA SERTIFIKAT -->
    <!-- ========================================================================= -->
    @if($config['is_multiple'])
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs flex items-center justify-center font-black">1</span>
                    <span>Buat & Kelola Daftar Nama {{ $config['title'] }}</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Buat nama sertifikat terlebih dahulu (contoh: <em>{{ $kategori === 'kepesantrenan' ? 'Sertifikat Khatam Juz, Sertifikat Keahlian Tahlil' : 'Sertifikat Kompetensi Kewirausahaan, Sertifikat Produk Kreatif' }}</em>), lalu unggah file PDF ke masing-masing siswa. Setiap siswa dapat memiliki lebih dari satu sertifikat dengan nama berbeda.
                </p>
            </div>

            <!-- Form Tambah Nama Sertifikat Baru -->
            <form method="POST" action="{{ route('dokumen-sertifikat.jenis.store', $kategori) }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 min-w-[320px] lg:min-w-[460px]">
                @csrf
                <input type="text"
                       name="nama_sertifikat"
                       required
                       placeholder="{{ $config['placeholder'] }}"
                       class="flex-1 px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-600">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-sm transition whitespace-nowrap cursor-pointer">
                    <i class="bi-plus-circle-fill"></i>
                    <span>Tambah Nama Sertifikat</span>
                </button>
            </form>
        </div>

        <!-- Daftar Nama Sertifikat yang Sudah Dibuat -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    Daftar Nama Sertifikat Tersedia ({{ $jenisSertifikats->count() }} Jenis) — Klik untuk memfilter:
                </span>
                @if($jenisFilter)
                <a href="{{ route('dokumen-sertifikat.index', ['kategori' => $kategori, 'kelas_id' => $kelasId, 'search' => $search]) }}"
                   class="text-xs font-bold text-indigo-600 hover:underline">
                    Tampilkan Semua Jenis Sertifikat
                </a>
                @endif
            </div>

            @if($jenisSertifikats->isEmpty())
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-center gap-2">
                <i class="bi-info-circle-fill text-amber-600"></i>
                <span>Belum ada nama sertifikat. Silakan ketik nama sertifikat pada kolom di atas lalu klik <strong>Tambah Nama Sertifikat</strong>.</span>
            </div>
            @else
            <div class="flex flex-wrap items-center gap-2.5">
                @foreach($jenisSertifikats as $j)
                @php
                    $isActiveJenis = (string) $jenisFilter === (string) $j->id;
                @endphp
                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl border text-xs transition {{ $isActiveJenis ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-slate-50 hover:bg-white text-slate-800 border-slate-200' }}">
                    <a href="{{ route('dokumen-sertifikat.index', ['kategori' => $kategori, 'kelas_id' => $kelasId, 'search' => $search, 'jenis_id' => $isActiveJenis ? null : $j->id]) }}"
                       class="font-bold flex items-center gap-1.5 {{ $isActiveJenis ? 'text-white' : 'text-slate-800' }}">
                        <i class="bi-award-fill {{ $isActiveJenis ? 'text-amber-300' : 'text-indigo-600' }}"></i>
                        <span>{{ $j->nama_sertifikat }}</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $isActiveJenis ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-700' }}">
                            {{ $j->dokumen_siswas_count }} PDF
                        </span>
                    </a>
                    <div class="flex items-center gap-1 pl-1 border-l {{ $isActiveJenis ? 'border-white/20' : 'border-slate-200' }}">
                        <button type="button"
                                @click="openEditJenisModal({{ Js::from($j) }})"
                                class="p-1 rounded hover:bg-black/10 {{ $isActiveJenis ? 'text-white' : 'text-slate-400 hover:text-amber-600' }}"
                                title="Ubah Nama Sertifikat">
                            <i class="bi-pencil-square text-[11px]"></i>
                        </button>
                        <form method="POST" action="{{ route('dokumen-sertifikat.jenis.destroy', $j) }}" onsubmit="return confirm('Hapus nama sertifikat ini dari daftar master?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="p-1 rounded hover:bg-black/10 {{ $isActiveJenis ? 'text-white' : 'text-slate-400 hover:text-rose-600' }}"
                                    title="Hapus Nama Sertifikat">
                                <i class="bi-x-lg text-[11px]"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- FILTER KELAS, PENCARIAN SISWA, STATUS UPLOAD & STATUS AKSES ADMINISTRASI -->
    <!-- ========================================================================= -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('dokumen-sertifikat.index', $kategori) }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end text-xs">
            @if($jenisFilter)
            <input type="hidden" name="jenis_id" value="{{ $jenisFilter }}">
            @endif

            <!-- Filter Kelas -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">
                    <i class="bi-funnel-fill text-indigo-500"></i> Filter Kelas
                </label>
                <select name="kelas_id" onchange="this.form.submit()" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800">
                    <option value="">-- Semua Kelas ({{ $kelases->count() }} Kelas) --</option>
                    @foreach($kelases as $kls)
                    <option value="{{ $kls->id }}" {{ (string) $kelasId === (string) $kls->id ? 'selected' : '' }}>
                        Kelas {{ $kls->nama_kelas }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Nama Sertifikat (Jika Kepesantrenan / Kewirausahaan) -->
            @if($config['is_multiple'])
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">
                    <i class="bi-award text-indigo-500"></i> Filter Nama Sertifikat
                </label>
                <select name="jenis_id" onchange="this.form.submit()" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-slate-800">
                    <option value="">-- Semua Nama Sertifikat --</option>
                    @foreach($jenisSertifikats as $j)
                    <option value="{{ $j->id }}" {{ (string) $jenisFilter === (string) $j->id ? 'selected' : '' }}>
                        {{ $j->nama_sertifikat }} ({{ $j->dokumen_siswas_count }})
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Filter Status Upload -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Status Dokumen</label>
                <select name="status_upload" onchange="this.form.submit()" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-slate-800">
                    <option value="">Semua Siswa</option>
                    <option value="sudah" {{ $statusFilter === 'sudah' ? 'selected' : '' }}>Sudah Ada File PDF</option>
                    <option value="belum" {{ $statusFilter === 'belum' ? 'selected' : '' }}>Belum Ada File PDF</option>
                </select>
            </div>

            <!-- Filter Status Akses Administrasi Siswa -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">
                    <i class="bi-shield-lock text-rose-500"></i> Akses Siswa (Adm)
                </label>
                <select name="status_akses" onchange="this.form.submit()" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-slate-800">
                    <option value="">Semua Status Akses</option>
                    <option value="diizinkan" {{ ($aksesFilter ?? '') === 'diizinkan' ? 'selected' : '' }}>Akses Diizinkan (Terbuka)</option>
                    <option value="dibatasi" {{ ($aksesFilter ?? '') === 'dibatasi' ? 'selected' : '' }}>Akses Dibatasi (Belum Lunas Adm)</option>
                </select>
            </div>

            <!-- Pencarian Nama / NISN -->
            <div class="{{ $config['is_multiple'] ? 'lg:col-span-1' : 'lg:col-span-2' }}">
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Cari Nama / NISN Siswa</label>
                <div class="relative">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Ketik nama atau NISN siswa..."
                           class="w-full pl-3.5 pr-9 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                    <button type="submit" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600">
                        <i class="bi-search"></i>
                    </button>
                </div>
            </div>

            <!-- Tombol Cari & Reset -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl transition cursor-pointer">
                    Terapkan Filter
                </button>
                @if($kelasId || $search || $statusFilter || ($aksesFilter ?? '') || $jenisFilter)
                <a href="{{ route('dokumen-sertifikat.index', $kategori) }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- ========================================================================= -->
    <!-- TABEL DAFTAR SISWA & UNGGAHAN DOKUMEN PDF -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:px-6 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2 bg-slate-50/50">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs flex items-center justify-center font-black">
                    {{ $config['is_multiple'] ? '2' : '1' }}
                </span>
                <h3 class="font-black text-slate-900 text-sm">
                    Daftar Siswa & File PDF {{ $config['title'] }}
                </h3>
            </div>
            <span class="text-xs text-slate-500 font-medium">
                Menampilkan <strong class="text-slate-800">{{ $allFilteredSiswas->count() }}</strong> siswa
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 border-b font-bold uppercase text-[10px] text-slate-400">
                    <tr>
                        <th class="px-4 py-3.5 text-center" width="4%">No</th>
                        <th class="px-4 py-3.5" width="20%">Nama Siswa & NISN</th>
                        <th class="px-4 py-3.5 text-center" width="10%">Kelas</th>
                        <th class="px-4 py-3.5" width="34%">
                            {{ $config['is_multiple'] ? 'Daftar Sertifikat PDF yang Diupload (Bisa Lebih dari 1 File)' : 'File Dokumen PDF (' . $config['short'] . ' - 1 File Saja)' }}
                        </th>
                        <th class="px-4 py-3.5" width="19%">Akses Siswa (Administrasi)</th>
                        <th class="px-4 py-3.5 text-center" width="13%">Aksi Upload</th>
                    </tr>
                </thead>
                <tbody class="divide-y font-medium text-slate-700">
                    @forelse($allFilteredSiswas as $idx => $s)
                    @php
                        $docs = $dokumensBySiswa->get($s->id, collect());
                        $namaKelas = $s->kelas->nama_kelas ?? ($s->kelas->nama ?? 'Belum Masuk Kelas');
                        $nisnLabel = $s->nisn ?: $s->username;
                        $isDibatasi = (bool) ($s->akses_dokumen_dibatasi ?? false);
                        $siswaJs = [
                            'id'       => $s->id,
                            'name'     => $s->name,
                            'kelas'    => $namaKelas,
                            'nisn'     => $nisnLabel,
                            'dibatasi' => $isDibatasi,
                            'catatan'  => $s->catatan_pembatasan_dokumen ?? '',
                        ];
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition align-top {{ $isDibatasi ? 'bg-rose-50/20' : '' }}">
                        <td class="px-4 py-3.5 text-center font-bold text-slate-400">
                            {{ $idx + 1 }}
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-extrabold text-slate-900 text-xs">{{ $s->name }}</div>
                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">NISN/ID: {{ $nisnLabel }}</div>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-block px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-black text-[11px]">
                                {{ $namaKelas }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            @if($docs->isEmpty())
                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-400 text-[11px]">
                                <i class="bi-file-earmark-x"></i>
                                <span>Belum ada file PDF yang diupload</span>
                            </div>
                            @else
                            <div class="space-y-2">
                                @foreach($docs as $doc)
                                <div class="p-2.5 rounded-2xl bg-emerald-50/50 border border-emerald-200/80 flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                        <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0 text-base">
                                            <i class="bi-file-earmark-pdf-fill"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-black text-slate-900 text-xs truncate" title="{{ $doc->nama_dokumen }}">
                                                {{ $doc->nama_dokumen }}
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2 text-[10px] text-slate-500 mt-0.5">
                                                <span>{{ $doc->file_size_formatted }}</span>
                                                <span>·</span>
                                                <span>{{ $doc->created_at->format('d/m/Y H:i') }}</span>
                                                @if($doc->nomor_dokumen)
                                                <span>·</span>
                                                <span class="font-bold text-indigo-700">No: {{ $doc->nomor_dokumen }}</span>
                                                @endif
                                                @if($doc->predikat)
                                                <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-black">
                                                    Predikat: {{ $doc->predikat }}
                                                </span>
                                                @endif
                                            </div>
                                            @if($doc->pejabat_1_nama)
                                            <div class="text-[10px] text-slate-500 mt-0.5">
                                                TTD: {{ $doc->pejabat_1_jabatan }} ({{ $doc->pejabat_1_nama }})
                                                @if($doc->pejabat_2_nama)
                                                    &amp; {{ $doc->pejabat_2_jabatan }} ({{ $doc->pejabat_2_nama }})
                                                @endif
                                            </div>
                                            @elseif($doc->keterangan)
                                            <div class="text-[10px] text-slate-500 italic mt-0.5">{{ $doc->keterangan }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Tombol Lihat, Cetak HD, Unduh & Hapus File -->
                                    <div class="flex items-center gap-1 flex-shrink-0">
                                        @if($doc->is_generated)
                                        <a href="{{ route('dokumen-sertifikat.cetak', $doc) }}"
                                           target="_blank"
                                           class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-lg font-bold text-[11px] flex items-center gap-1 transition"
                                           title="Cetak Sertifikat Kualitas Tinggi (A4 Landscape)">
                                            <i class="bi-printer-fill"></i>
                                            <span>Cetak HD</span>
                                        </a>
                                        @endif
                                        <button type="button"
                                                @click="openPreviewModal('{{ route('dokumen-sertifikat.preview', $doc) }}', '{{ addslashes($doc->nama_dokumen . ' — ' . $s->name) }}')"
                                                class="px-2.5 py-1 bg-white hover:bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg font-bold text-[11px] flex items-center gap-1 transition cursor-pointer"
                                                title="Lihat / Preview PDF">
                                            <i class="bi-eye-fill"></i>
                                            <span>Lihat</span>
                                        </button>
                                        <a href="{{ route('dokumen-sertifikat.download', $doc) }}"
                                           class="px-2.5 py-1 bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg font-bold text-[11px] flex items-center gap-1 transition"
                                           title="Download PDF">
                                            <i class="bi-download"></i>
                                            <span>Unduh</span>
                                        </a>
                                        <form method="POST" action="{{ route('dokumen-sertifikat.destroy', $doc) }}" onsubmit="return confirm('Hapus file PDF {{ addslashes($doc->nama_dokumen) }} milik {{ addslashes($s->name) }}?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-1.5 text-rose-500 hover:bg-rose-100 rounded-lg transition cursor-pointer"
                                                    title="Hapus File PDF">
                                                <i class="bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            @if($isDibatasi)
                                <div class="space-y-1.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-100 text-rose-800 border border-rose-200 font-black text-[11px]">
                                        <i class="bi-lock-fill text-rose-600"></i>
                                        <span>Akses Dibatasi (Terkunci)</span>
                                    </span>
                                    @if($s->catatan_pembatasan_dokumen)
                                    <div class="text-[10px] text-rose-700 leading-snug bg-rose-50/80 p-1.5 rounded-lg border border-rose-100">
                                        <strong>Alasan:</strong> {{ $s->catatan_pembatasan_dokumen }}
                                    </div>
                                    @endif
                                    <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                        <form method="POST" action="{{ route('dokumen-sertifikat.akses.toggle', $s) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="akses_dokumen_dibatasi" value="0">
                                            <button type="submit"
                                                    class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-[10px] inline-flex items-center gap-1 transition cursor-pointer">
                                                <i class="bi-unlock-fill"></i>
                                                <span>Buka Akses</span>
                                            </button>
                                        </form>
                                        <button type="button"
                                                @click="openAksesModal({{ Js::from($siswaJs) }})"
                                                class="px-2 py-1 bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 rounded-lg font-bold text-[10px] inline-flex items-center gap-1 transition cursor-pointer">
                                            <i class="bi-pencil-square"></i>
                                            <span>Ubah Alasan</span>
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="space-y-1.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-[11px]">
                                        <i class="bi-unlock-fill text-emerald-600"></i>
                                        <span>Akses Diizinkan</span>
                                    </span>
                                    <div>
                                        <button type="button"
                                                @click="openAksesModal({{ Js::from($siswaJs) }})"
                                                class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg font-bold text-[10px] inline-flex items-center gap-1 transition cursor-pointer"
                                                title="Batasi siswa ini agar tidak dapat melihat sertifikat karena masalah administrasi">
                                            <i class="bi-lock-fill"></i>
                                            <span>Batasi Akses (Adm)</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            @if($config['is_multiple'])
                                <button type="button"
                                        @click="openUploadModal({{ Js::from($siswaJs) }})"
                                        class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-[11px] inline-flex items-center gap-1.5 shadow-sm shadow-indigo-600/20 transition cursor-pointer">
                                    <i class="bi-cloud-arrow-up-fill"></i>
                                    <span>+ Upload Sertifikat</span>
                                </button>
                            @else
                                @if($docs->isEmpty())
                                    <button type="button"
                                            @click="openUploadModal({{ Js::from($siswaJs) }})"
                                            class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-[11px] inline-flex items-center gap-1.5 shadow-sm shadow-indigo-600/20 transition cursor-pointer">
                                        <i class="bi-cloud-arrow-up-fill"></i>
                                        <span>Upload PDF {{ $config['short'] }}</span>
                                    </button>
                                @else
                                    <button type="button"
                                            @click="openUploadModal({{ Js::from($siswaJs) }})"
                                            class="px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 font-bold rounded-xl text-[11px] inline-flex items-center gap-1.5 transition cursor-pointer">
                                        <i class="bi-arrow-repeat"></i>
                                        <span>Ganti File PDF</span>
                                    </button>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                            <div class="max-w-sm mx-auto space-y-2">
                                <i class="bi-people text-3xl text-slate-300 block"></i>
                                <div class="font-bold text-slate-700 text-sm">Data Siswa Tidak Ditemukan</div>
                                <p class="text-xs text-slate-400">Coba ubah filter kelas atau kata kunci pencarian siswa di atas.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL POPUP: UPLOAD FILE PDF UNTUK SISWA -->
    <!-- ========================================================================= -->
    <div x-show="modalUpload"
         x-cloak
         x-transition.opacity.duration.200ms
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        <div @click.away="modalUpload = false"
             class="bg-white w-full max-w-lg rounded-3xl p-6 sm:p-7 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="p-2.5 bg-indigo-100 text-indigo-700 rounded-xl">
                        <i class="bi-file-earmark-pdf-fill text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Upload PDF {{ $config['title'] }}</h3>
                        <p class="text-xs text-slate-500" x-text="selectedSiswaName + ' (' + selectedSiswaKelas + ' · ' + selectedSiswaNisn + ')'"></p>
                    </div>
                </div>
                <button type="button" @click="modalUpload = false" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100">
                    <i class="bi-x-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('dokumen-sertifikat.store', $kategori) }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="siswa_id" :value="selectedSiswaId">

                @if($config['is_multiple'])
                <!-- Pilihan Nama Sertifikat (Kepesantrenan / Kewirausahaan) -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block font-bold text-slate-700">Nama Sertifikat <span class="text-rose-500">*</span></label>
                        @if($jenisSertifikats->isNotEmpty())
                        <button type="button"
                                @click="modeNamaBaru = !modeNamaBaru; if(!modeNamaBaru) namaSertifikatBaru = ''"
                                class="text-[11px] font-bold text-indigo-600 hover:underline cursor-pointer"
                                x-text="modeNamaBaru ? 'Pilih dari Daftar Nama Sertifikat' : '+ Buat Nama Sertifikat Baru'"></button>
                        @endif
                    </div>

                    <template x-if="!modeNamaBaru">
                        <select name="jenis_sertifikat_id" x-model="selectedJenisId" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-indigo-600">
                            <option value="">-- Pilih Nama Sertifikat --</option>
                            @foreach($jenisSertifikats as $j)
                            <option value="{{ $j->id }}">{{ $j->nama_sertifikat }}</option>
                            @endforeach
                        </select>
                    </template>

                    <template x-if="modeNamaBaru">
                        <div>
                            <input type="text"
                                   name="nama_sertifikat_baru"
                                   x-model="namaSertifikatBaru"
                                   required
                                   placeholder="{{ $config['placeholder'] }}"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                            <span class="text-[10px] text-slate-400 mt-1 block">Nama sertifikat baru ini juga akan otomatis tersimpan ke daftar master sertifikat.</span>
                        </div>
                    </template>
                </div>
                @else
                <!-- Informasi untuk SKL / Ijazah (1 File Saja) -->
                <div class="p-3 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 text-xs flex items-start gap-2">
                    <i class="bi-info-circle-fill text-blue-600 mt-0.5"></i>
                    <div>
                        <strong>Ketentuan {{ $config['short'] }}:</strong> Setiap siswa hanya memiliki <strong>1 file PDF {{ $config['short'] }}</strong>. Jika siswa ini sudah memiliki file sebelumnya, file lama akan otomatis diganti dengan file baru yang Anda unggah.
                    </div>
                </div>
                @endif

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih File Dokumen (Wajib PDF) <span class="text-rose-500">*</span></label>
                    <input type="file"
                           name="file_pdf"
                           accept="application/pdf,.pdf"
                           required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:text-white file:font-bold hover:file:bg-indigo-700">
                    <span class="text-[10px] text-slate-400 mt-1 block">Format: .PDF · Ukuran maksimal: 15 MB</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor Sertifikat / Dokumen (Opsional)</label>
                        <input type="text"
                               name="nomor_dokumen"
                               x-model="nomorDokumen"
                               placeholder="Contoh: 421.5/012/SMK-AH/2026"
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Keterangan / Catatan (Opsional)</label>
                        <input type="text"
                               name="keterangan"
                               x-model="keteranganDokumen"
                               placeholder="Contoh: Lulus Predikat Sangat Baik"
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="modalUpload = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-600/20 flex items-center gap-1.5 cursor-pointer">
                        <i class="bi-cloud-arrow-up-fill"></i>
                        <span>Unggah File PDF</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL POPUP: EDIT NAMA SERTIFIKAT MASTER -->
    <!-- ========================================================================= -->
    @if($config['is_multiple'])
    <div x-show="modalEditJenis"
         x-cloak
         x-transition.opacity.duration.200ms
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        <div @click.away="modalEditJenis = false"
             class="bg-white w-full max-w-md rounded-3xl p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-black text-slate-900 text-base">Ubah Nama Sertifikat</h3>
                <button type="button" @click="modalEditJenis = false" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg">
                    <i class="bi-x-lg"></i>
                </button>
            </div>

            <form method="POST" :action="'{{ url('dokumen-sertifikat/jenis') }}/' + editJenis.id" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Sertifikat <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_sertifikat" x-model="editJenis.nama_sertifikat" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="modalEditJenis = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL POPUP: PREVIEW PDF LANGSUNG DI LAYAR -->
    <!-- ========================================================================= -->
    <div x-show="modalPreview"
         x-cloak
         x-transition.opacity.duration.200ms
         class="fixed inset-0 z-[70] bg-black/75 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6"
         style="display: none;">
        <div @click.away="modalPreview = false; previewUrl = ''"
             class="bg-white w-full max-w-5xl h-[88vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden">
            <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 min-w-0">
                    <i class="bi-file-earmark-pdf-fill text-rose-400 text-lg"></i>
                    <span class="font-bold text-sm truncate" x-text="previewTitle"></span>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="previewUrl" target="_blank" class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold flex items-center gap-1.5 transition">
                        <i class="bi-box-arrow-up-right"></i>
                        <span>Tab Baru</span>
                    </a>
                    <button type="button" @click="modalPreview = false; previewUrl = ''" class="p-1.5 rounded-xl bg-white/10 hover:bg-rose-600 text-white transition cursor-pointer">
                        <i class="bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="flex-1 bg-slate-100">
                <template x-if="previewUrl">
                    <iframe :src="previewUrl" class="w-full h-full border-0"></iframe>
                </template>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL POPUP: BATASI / ATUR AKSES SISWA INDIVIDU (MASALAH ADMINISTRASI) -->
    <!-- ========================================================================= -->
    <div x-show="modalAksesSiswa"
         x-cloak
         x-transition.opacity.duration.200ms
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        <div @click.away="modalAksesSiswa = false"
             class="bg-white w-full max-w-md rounded-3xl p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="p-2.5 bg-rose-100 text-rose-600 rounded-xl">
                        <i class="bi-shield-lock-fill text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Pembatasan Akses Sertifikat Siswa</h3>
                        <p class="text-xs text-slate-500" x-text="aksesSiswa.name + ' (' + aksesSiswa.kelas + ')'"></p>
                    </div>
                </div>
                <button type="button" @click="modalAksesSiswa = false" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg">
                    <i class="bi-x-lg"></i>
                </button>
            </div>

            <form method="POST" :action="'{{ url('dokumen-sertifikat/akses-siswa') }}/' + aksesSiswa.id" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="akses_dokumen_dibatasi" value="1">

                <div class="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 leading-relaxed">
                    <strong><i class="bi-exclamation-triangle-fill text-rose-600"></i> Dampak Pembatasan:</strong><br>
                    Jika dibatasi, siswa ini <strong>tidak dapat melihat maupun mengunduh</strong> seluruh sertifikat & dokumen (Kepesantrenan, Kewirausahaan, SKL, dan Ijazah) pada halaman siswanya sampai akses dibuka kembali.
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Alasan / Catatan Administrasi untuk Siswa</label>
                    <textarea name="catatan_pembatasan_dokumen"
                              x-model="aksesSiswa.catatan"
                              rows="3"
                              placeholder="Contoh: Administrasi sekolah / SPP belum diselesaikan. Silakan hubungi bagian TU."
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-rose-500"></textarea>
                    <span class="text-[10px] text-slate-400 mt-1 block">Pesan ini akan ditampilkan kepada siswa saat membuka halaman Dokumen & Sertifikat.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="modalAksesSiswa = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl flex items-center gap-1.5 shadow-sm cursor-pointer">
                        <i class="bi-lock-fill"></i>
                        <span>Simpan & Batasi Akses Siswa</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL POPUP: PENGATURAN AKSES MASSAL (PER KELAS / SEMUA SISWA) -->
    <!-- ========================================================================= -->
    <div x-show="modalBulkAkses"
         x-cloak
         x-transition.opacity.duration.200ms
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        <div @click.away="modalBulkAkses = false"
             class="bg-white w-full max-w-lg rounded-3xl p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="p-2.5 bg-indigo-100 text-indigo-700 rounded-xl">
                        <i class="bi-shield-shaded text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Pengaturan Akses Sertifikat Massal</h3>
                        <p class="text-xs text-slate-500">Batasi atau buka akses halaman sertifikat siswa per kelas / keseluruhan</p>
                    </div>
                </div>
                <button type="button" @click="modalBulkAkses = false" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg">
                    <i class="bi-x-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('dokumen-sertifikat.akses.bulk') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Target Kelas</label>
                    <select name="kelas_id" x-model="bulkAkses.kelas_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800">
                        <option value="">-- Seluruh Siswa Semua Kelas --</option>
                        @foreach($kelases as $kls)
                        <option value="{{ $kls->id }}">Kelas {{ $kls->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tindakan Pengaturan Akses <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <label class="p-3 rounded-2xl border cursor-pointer flex items-start gap-2.5 transition"
                               :class="bulkAkses.action === 'batasi' ? 'bg-rose-50 border-rose-500 text-rose-900' : 'bg-white border-slate-200 text-slate-700'">
                            <input type="radio" name="action" value="batasi" x-model="bulkAkses.action" class="mt-0.5">
                            <div>
                                <div class="font-black flex items-center gap-1"><i class="bi-lock-fill text-rose-600"></i> Batasi Akses Siswa</div>
                                <div class="text-[10px] text-slate-500">Sembunyikan halaman sertifikat karena administrasi</div>
                            </div>
                        </label>
                        <label class="p-3 rounded-2xl border cursor-pointer flex items-start gap-2.5 transition"
                               :class="bulkAkses.action === 'buka' ? 'bg-emerald-50 border-emerald-500 text-emerald-900' : 'bg-white border-slate-200 text-slate-700'">
                            <input type="radio" name="action" value="buka" x-model="bulkAkses.action" class="mt-0.5">
                            <div>
                                <div class="font-black flex items-center gap-1"><i class="bi-unlock-fill text-emerald-600"></i> Buka Akses Siswa</div>
                                <div class="text-[10px] text-slate-500">Izinkan siswa melihat & mengunduh sertifikat</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div x-show="bulkAkses.action === 'batasi'">
                    <label class="block font-bold text-slate-700 mb-1">Catatan / Pesan Administrasi untuk Siswa</label>
                    <textarea name="catatan_pembatasan_dokumen"
                              x-model="bulkAkses.catatan"
                              rows="2"
                              class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="modalBulkAkses = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 text-white font-bold rounded-xl flex items-center gap-1.5 shadow-sm cursor-pointer transition"
                            :class="bulkAkses.action === 'batasi' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700'">
                        <i class="bi-check2-circle"></i>
                        <span x-text="bulkAkses.action === 'batasi' ? 'Terapkan Pembatasan Akses' : 'Buka Akses Target'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($config['is_multiple'])
    <!-- Datalist Global untuk Jabatan & Nama Pejabat dari Database -->
    <datalist id="listJabatanPejabat1">
        <option value="Kepala SMK Plus Al Hilal">
        <option value="Ketua Lembaga Kepesantrenan">
        <option value="Pengasuh Pondok Pesantren Al Hilal">
        <option value="Ketua Lembaga Kewirausahaan">
        <option value="Ketua Unit Produksi & Kewirausahaan">
        <option value="Ketua Program Keahlian">
        @foreach(collect($daftarPejabat ?? [])->pluck('jabatan')->unique()->filter() as $jbt)
        <option value="{{ $jbt }}">
        @endforeach
    </datalist>
    <datalist id="listJabatanPejabat2">
        <option value="Ketua Lembaga Kepesantrenan">
        <option value="Pengasuh Pondok Pesantren Al Hilal">
        <option value="Ketua Lembaga Kewirausahaan">
        <option value="Koordinator Kepesantrenan">
        <option value="Koordinator Kewirausahaan">
        <option value="Pembina Lembaga {{ $config['short'] }}">
        @foreach(collect($daftarPejabat ?? [])->pluck('jabatan')->unique()->filter() as $jbt)
        <option value="{{ $jbt }}">
        @endforeach
    </datalist>
    <datalist id="listNamaPejabatDb">
        @foreach($daftarPejabat ?? [] as $pjb)
        <option value="{{ $pjb['nama'] }}">{{ $pjb['jabatan'] }} {{ $pjb['nip'] ? '(' . $pjb['nip'] . ')' : '' }}</option>
        @endforeach
    </datalist>

    <!-- ========================================================================= -->
    <!-- MODAL POPUP 1: GENERATE SERTIFIKAT OTOMATIS (INPUT KEGIATAN, PREDIKAT & PEJABAT TTD) -->
    <!-- ========================================================================= -->
    <div x-show="modalGenerateMassal"
         x-cloak
         x-transition.opacity.duration.200ms
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5"
         style="display: none;">
        <div @click.away="if (!modalPreview) modalGenerateMassal = false"
             class="bg-white w-full max-w-5xl rounded-3xl p-5 sm:p-7 shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto">

            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="p-2.5 bg-emerald-100 text-emerald-700 rounded-xl">
                        <i class="bi-magic text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Generate Otomatis {{ $config['title'] }} untuk Masing-Masing Siswa</h3>
                        <p class="text-xs text-slate-500">Pilih Pejabat Penandatangan otomatis dari database atau input manual, cek Preview Sertifikat, lalu Generate PDF</p>
                    </div>
                </div>
                <button type="button" @click="modalGenerateMassal = false" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 cursor-pointer">
                    <i class="bi-x-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('dokumen-sertifikat.generate.massal', $kategori) }}" class="space-y-4 text-xs">
                @csrf

                <!-- Bagian A: Data Kegiatan & Sertifikat -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="font-black text-slate-800 uppercase text-[11px] tracking-wider flex items-center gap-1.5">
                        <i class="bi-award-fill text-indigo-600"></i>
                        <span>1. Informasi Kegiatan &amp; Nomor Sertifikat</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="font-bold text-slate-700">Nama Kegiatan / Sertifikat <span class="text-rose-500">*</span></label>
                                @if($jenisSertifikats->isNotEmpty())
                                <button type="button"
                                        @click="genModeNamaBaru = !genModeNamaBaru"
                                        class="text-[11px] font-bold text-indigo-600 hover:underline cursor-pointer"
                                        x-text="genModeNamaBaru ? 'Pilih dari Master Sertifikat' : '+ Ketik Nama Kegiatan Baru'"></button>
                                @endif
                            </div>

                            <template x-if="!genModeNamaBaru">
                                <select name="jenis_sertifikat_id"
                                        x-model="certForm.jenis_sertifikat_id"
                                        required
                                        class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl font-bold text-slate-800">
                                    <option value="">-- Pilih Nama Kegiatan / Sertifikat --</option>
                                    @foreach($jenisSertifikats as $j)
                                    <option value="{{ $j->id }}">{{ $j->nama_sertifikat }}</option>
                                    @endforeach
                                </select>
                            </template>
                            <template x-if="genModeNamaBaru">
                                <input type="text"
                                       name="nama_sertifikat_baru"
                                       x-model="certForm.nama_sertifikat_baru"
                                       required
                                       placeholder="{{ $config['placeholder'] }}"
                                       class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl font-medium">
                            </template>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Format Nomor Sertifikat (Gunakan {NO} untuk nomor urut otomatis)</label>
                            <input type="text"
                                   name="format_nomor"
                                   x-model="certForm.format_nomor"
                                   placeholder="Contoh: 421.5/{NO}/SERT-AH/{{ date('Y') }}"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl font-medium">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="md:col-span-1">
                            <label class="block font-bold text-slate-700 mb-1">Tempat Terbit</label>
                            <input type="text"
                                   name="tempat_terbit"
                                   x-model="certForm.tempat_terbit"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl font-medium">
                        </div>
                        <div class="md:col-span-1">
                            <label class="block font-bold text-slate-700 mb-1">Tanggal Terbit</label>
                            <input type="text"
                                   name="tanggal_terbit"
                                   x-model="certForm.tanggal_terbit"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl font-medium">
                        </div>
                        <div class="md:col-span-1">
                            <label class="block font-bold text-slate-700 mb-1">Narasi / Deskripsi Kegiatan (Opsional)</label>
                            <input type="text"
                                   name="deskripsi_kegiatan"
                                   x-model="certForm.deskripsi_kegiatan"
                                   placeholder="Kosongkan untuk menggunakan narasi resmi bawaan"
                                   class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl font-medium">
                        </div>
                    </div>
                </div>

                <!-- Bagian B: Pejabat Penandatangan (Otomatis dari Database / Input Manual) -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="font-black text-slate-800 uppercase text-[11px] tracking-wider flex items-center gap-1.5">
                            <i class="bi-pen-fill text-emerald-600"></i>
                            <span>2. Pejabat Penandatangan Sertifikat (Misal: Kepala Sekolah, Ketua Lembaga, dll)</span>
                        </div>
                        <span class="text-[11px] text-slate-500 font-medium">
                            <i class="bi-database-check text-indigo-600"></i> Pilih pejabat dari database untuk isi otomatis, atau ketik manual jika tidak ada di database
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Pejabat 1 (Wajib) -->
                        <div class="p-3.5 rounded-xl bg-white border border-slate-200 space-y-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-bold text-indigo-700 text-[11px] uppercase">Pejabat Penandatangan 1 (Utama / Kiri) *</div>
                                <button type="button"
                                        @click="certForm.pejabat1_id = 'manual'; certForm.pejabat_1_jabatan = ''; certForm.pejabat_1_nama = ''; certForm.pejabat_1_nip = ''"
                                        class="text-[10px] font-bold text-indigo-600 hover:underline cursor-pointer">
                                    Reset / Input Manual
                                </button>
                            </div>

                            <!-- Dropdown Pilih Otomatis dari Database -->
                            <div class="p-2.5 rounded-xl bg-indigo-50/70 border border-indigo-100">
                                <label class="block text-[10px] font-black text-indigo-900 uppercase mb-1">
                                    <i class="bi-person-check-fill mr-1"></i> Pilih Pejabat 1 dari Database (Otomatis Isi Jabatan, Nama &amp; NIP)
                                </label>
                                <select x-model="certForm.pejabat1_id"
                                        @change="onSelectPejabat1($event.target.value)"
                                        class="w-full px-3 py-1.5 bg-white border border-indigo-200 rounded-lg font-bold text-slate-800 text-xs">
                                    <option value="manual">-- Input Manual (Ketik Sendiri di Bawah) --</option>
                                    @foreach($daftarPejabat ?? [] as $pjb)
                                    <option value="{{ $pjb['id'] }}">{{ $pjb['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Jabatan Penandatangan 1 <span class="text-rose-500">*</span></label>
                                <input type="text"
                                       name="pejabat_1_jabatan"
                                       list="listJabatanPejabat1"
                                       x-model="certForm.pejabat_1_jabatan"
                                       @input="onJabatanPejabatInput(1, $event.target.value)"
                                       required
                                       placeholder="Contoh: Kepala SMK Plus Al Hilal / Ketua Lembaga"
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg font-medium">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Lengkap &amp; Gelar Pejabat 1 <span class="text-rose-500">*</span></label>
                                <input type="text"
                                       name="pejabat_1_nama"
                                       list="listNamaPejabatDb"
                                       x-model="certForm.pejabat_1_nama"
                                       @input="onNamaPejabatInput(1, $event.target.value)"
                                       required
                                       placeholder="Pilih dari daftar atau ketik manual nama pejabat & gelar..."
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg font-bold text-slate-900">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">NIP / NIY Pejabat 1 (Opsional)</label>
                                <input type="text"
                                       name="pejabat_1_nip"
                                       x-model="certForm.pejabat_1_nip"
                                       placeholder="Otomatis terisi jika ada di database, atau ketik manual"
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg font-medium">
                            </div>
                        </div>

                        <!-- Pejabat 2 (Opsional) -->
                        <div class="p-3.5 rounded-xl bg-white border border-slate-200 space-y-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-bold text-emerald-700 text-[11px] uppercase">Pejabat Penandatangan 2 (Kanan - Opsional)</div>
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            @click="onSelectPejabat2('none')"
                                            class="text-[10px] font-bold text-slate-500 hover:text-rose-600 hover:underline cursor-pointer">
                                        Kosongkan (1 TTD)
                                    </button>
                                </div>
                            </div>

                            <!-- Dropdown Pilih Otomatis dari Database -->
                            <div class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-100">
                                <label class="block text-[10px] font-black text-emerald-900 uppercase mb-1">
                                    <i class="bi-person-check-fill mr-1"></i> Pilih Pejabat 2 dari Database / Input Manual
                                </label>
                                <select x-model="certForm.pejabat2_id"
                                        @change="onSelectPejabat2($event.target.value)"
                                        class="w-full px-3 py-1.5 bg-white border border-emerald-200 rounded-lg font-bold text-slate-800 text-xs">
                                    <option value="manual">-- Input Manual (Ketik Sendiri di Bawah) --</option>
                                    <option value="none">-- Kosongkan (Hanya 1 Penandatangan) --</option>
                                    @foreach($daftarPejabat ?? [] as $pjb)
                                    <option value="{{ $pjb['id'] }}">{{ $pjb['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Jabatan Penandatangan 2 (Kosongkan jika hanya 1 TTD)</label>
                                <input type="text"
                                       name="pejabat_2_jabatan"
                                       list="listJabatanPejabat2"
                                       x-model="certForm.pejabat_2_jabatan"
                                       @input="onJabatanPejabatInput(2, $event.target.value)"
                                       placeholder="Contoh: Ketua Lembaga {{ $config['short'] }}"
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg font-medium">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Lengkap &amp; Gelar Pejabat 2</label>
                                <input type="text"
                                       name="pejabat_2_nama"
                                       list="listNamaPejabatDb"
                                       x-model="certForm.pejabat_2_nama"
                                       @input="onNamaPejabatInput(2, $event.target.value)"
                                       placeholder="Pilih dari daftar atau ketik manual nama pejabat ke-2..."
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg font-bold text-slate-900">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">NIP / NIY Pejabat 2 (Opsional)</label>
                                <input type="text"
                                       name="pejabat_2_nip"
                                       x-model="certForm.pejabat_2_nip"
                                       placeholder="Otomatis terisi jika ada di database, atau ketik manual"
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg font-medium">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bagian C: Pratinjau (Live Preview) Hasil Sertifikat Sebelum Generate -->
                <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-200 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="font-black text-slate-800 uppercase text-[11px] tracking-wider flex items-center gap-1.5">
                            <i class="bi-eye-fill text-amber-600"></i>
                            <span>3. Pratinjau (Preview) Hasil Desain Sertifikat Sebelum Di-Generate</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button"
                                    @click="openDraftPdfPreview(false)"
                                    class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px] flex items-center gap-1.5 shadow-xs cursor-pointer">
                                <i class="bi-file-earmark-pdf-fill"></i>
                                <span>Buka Preview PDF Asli (Ukuran Penuh)</span>
                            </button>
                            <button type="button"
                                    @click="showLivePreview = !showLivePreview"
                                    class="px-3 py-1.5 rounded-xl bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-[11px] cursor-pointer"
                                    x-text="showLivePreview ? 'Sembunyikan Pratinjau' : 'Tampilkan Pratinjau Visual'"></button>
                        </div>
                    </div>

                    <!-- Kanvas Live Preview A4 Landscape -->
                    <div x-show="showLivePreview" x-transition class="overflow-x-auto py-1">
                        <div class="mx-auto w-full max-w-[760px] bg-[#fffdf7] rounded-xl shadow-md p-3 border border-slate-300">
                            <div class="border-[4px] {{ $kategori === 'kepesantrenan' ? 'border-[#065f46]' : 'border-[#1e3a8a]' }} p-1">
                                <div class="border-2 border-[#ca8a04] px-6 py-5 text-center relative bg-gradient-to-b from-amber-50/20 via-[#fffdf7] to-amber-50/10 space-y-2.5" style="font-family: Georgia, 'Times New Roman', serif;">
                                    <!-- Header Lembaga -->
                                    <div class="space-y-0.5 font-sans">
                                        <div class="text-xs sm:text-sm font-black tracking-widest uppercase {{ $kategori === 'kepesantrenan' ? 'text-[#065f46]' : 'text-[#1e3a8a]' }}">
                                            {{ strtoupper($namaSekolahDefault ?? 'SMK PLUS AL HILAL') }}
                                        </div>
                                        <div class="text-[9px] sm:text-[10px] font-bold tracking-[2px] uppercase text-amber-700">
                                            {{ $kategori === 'kepesantrenan' ? 'PONDOK PESANTREN & LEMBAGA KEPESANTRENAN SISWA' : 'UNIT PRODUKSI & LEMBAGA KEWIRAUSAHAAN SISWA' }}
                                        </div>
                                    </div>

                                    <div class="w-48 h-[1px] bg-[#ca8a04] mx-auto"></div>

                                    <!-- Judul Sertifikat & Nomor -->
                                    <div>
                                        <div class="text-xl sm:text-2xl font-bold tracking-[6px] uppercase {{ $kategori === 'kepesantrenan' ? 'text-[#064e3b]' : 'text-[#1e3a8a]' }}">
                                            SERTIFIKAT
                                        </div>
                                        <div class="text-[10px] font-sans font-semibold text-slate-600 mt-0.5">
                                            Nomor: <span x-text="getNomorPreview()"></span>
                                        </div>
                                    </div>

                                    <!-- Nama Siswa Contoh -->
                                    <div class="space-y-1">
                                        <div class="italic text-[11px] text-slate-600">Diberikan dengan bangga kepada:</div>
                                        <div class="inline-block border-b-2 border-[#ca8a04] px-6 pb-0.5 text-base sm:text-lg font-bold uppercase tracking-wider text-slate-900"
                                             x-text="certForm.sample_siswa_nama || 'CONTOH NAMA SISWA'"></div>
                                        <div class="text-[10px] font-sans font-bold text-slate-600">
                                            NISN/ID: <span x-text="certForm.sample_siswa_nisn"></span>
                                            &nbsp;&bull;&nbsp;
                                            Kelas: <span x-text="certForm.sample_siswa_kelas"></span>
                                        </div>
                                    </div>

                                    <!-- Narasi & Nama Kegiatan -->
                                    <div class="max-w-xl mx-auto space-y-1">
                                        <div class="text-[11px] text-slate-700 leading-relaxed"
                                             x-text="certForm.deskripsi_kegiatan.trim() !== '' ? certForm.deskripsi_kegiatan : '{{ $kategori === 'kepesantrenan' ? 'Telah mengikuti dan menyelesaikan program pembinaan serta evaluasi kompetensi kepesantrenan pada kegiatan:' : 'Telah mengikuti dan menyelesaikan program praktik serta evaluasi kompetensi kewirausahaan pada kegiatan:' }}'"></div>
                                        <div class="text-sm sm:text-base font-sans font-black uppercase tracking-wide {{ $kategori === 'kepesantrenan' ? 'text-[#065f46]' : 'text-[#1e3a8a]' }}"
                                             x-text="'&ldquo;' + getNamaKegiatanPreview(false) + '&rdquo;'"></div>
                                    </div>

                                    <!-- Badge Predikat -->
                                    <div class="pt-0.5">
                                        <span class="inline-block px-5 py-1 rounded-full border-2 border-amber-400 text-white font-sans font-black text-[10px] tracking-widest uppercase {{ $kategori === 'kepesantrenan' ? 'bg-[#065f46]' : 'bg-[#1e3a8a]' }}"
                                              x-text="'PREDIKAT NILAI : ' + (certForm.sample_predikat || 'SANGAT BAIK').toUpperCase()"></span>
                                    </div>

                                    <!-- Blok Tanda Tangan Pejabat 1 & Pejabat 2 -->
                                    <div class="pt-4 px-4 sm:px-10 flex items-end font-sans text-slate-900"
                                         :class="(certForm.pejabat_2_jabatan.trim() !== '' || certForm.pejabat_2_nama.trim() !== '') ? 'justify-between' : 'justify-center'">
                                        <!-- Jika 2 Pejabat -->
                                        <template x-if="certForm.pejabat_2_jabatan.trim() !== '' || certForm.pejabat_2_nama.trim() !== ''">
                                            <div class="w-full flex items-end justify-between gap-4">
                                                <div class="text-center w-56">
                                                    <div class="text-[10px] text-slate-600">Mengetahui,</div>
                                                    <div class="text-[11px] font-bold text-slate-900 mb-9" x-text="certForm.pejabat_1_jabatan || 'Kepala Sekolah'"></div>
                                                    <div class="text-[11px] font-black underline text-slate-900" x-text="certForm.pejabat_1_nama || '....................................'"></div>
                                                    <div class="text-[10px] text-slate-600" x-show="certForm.pejabat_1_nip.trim() !== ''" x-text="certForm.pejabat_1_nip"></div>
                                                </div>
                                                <div class="text-center w-56">
                                                    <div class="text-[10px] text-slate-600" x-text="(certForm.tempat_terbit || 'Cirebon') + ', ' + (certForm.tanggal_terbit || '')"></div>
                                                    <div class="text-[11px] font-bold text-slate-900 mb-9" x-text="certForm.pejabat_2_jabatan || 'Ketua Lembaga'"></div>
                                                    <div class="text-[11px] font-black underline text-slate-900" x-text="certForm.pejabat_2_nama || '....................................'"></div>
                                                    <div class="text-[10px] text-slate-600" x-show="certForm.pejabat_2_nip.trim() !== ''" x-text="certForm.pejabat_2_nip"></div>
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Jika 1 Pejabat -->
                                        <template x-if="certForm.pejabat_2_jabatan.trim() === '' && certForm.pejabat_2_nama.trim() === ''">
                                            <div class="text-center w-64">
                                                <div class="text-[10px] text-slate-600" x-text="(certForm.tempat_terbit || 'Cirebon') + ', ' + (certForm.tanggal_terbit || '')"></div>
                                                <div class="text-[11px] font-bold text-slate-900 mb-9" x-text="certForm.pejabat_1_jabatan || 'Kepala SMK Plus Al Hilal'"></div>
                                                <div class="text-[11px] font-black underline text-slate-900" x-text="certForm.pejabat_1_nama || '....................................'"></div>
                                                <div class="text-[10px] text-slate-600" x-show="certForm.pejabat_1_nip.trim() !== ''" x-text="certForm.pejabat_1_nip"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bagian D: Daftar Siswa & Pilihan Predikat Nilai -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="font-black text-slate-800 uppercase text-[11px] tracking-wider flex items-center gap-1.5">
                            <i class="bi-people-fill text-indigo-600"></i>
                            <span>4. Pilih Predikat Nilai Masing-Masing Siswa ({{ $allFilteredSiswas->count() }} Siswa Ditampilkan)</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-[10px] font-bold text-slate-500">Set Semua Sekaligus:</span>
                            <button type="button" @click="setAllPredikat('Sangat Baik')" class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white font-bold text-[10px] cursor-pointer">
                                Sangat Baik
                            </button>
                            <button type="button" @click="setAllPredikat('Baik')" class="px-2.5 py-1 rounded-lg bg-blue-600 text-white font-bold text-[10px] cursor-pointer">
                                Baik
                            </button>
                            <button type="button" @click="setAllPredikat('Cukup')" class="px-2.5 py-1 rounded-lg bg-amber-600 text-white font-bold text-[10px] cursor-pointer">
                                Cukup
                            </button>
                            <button type="button" @click="setAllPredikat('')" class="px-2.5 py-1 rounded-lg bg-slate-200 text-slate-700 font-bold text-[10px] cursor-pointer">
                                Kosongkan
                            </button>
                        </div>
                    </div>

                    <div class="max-h-64 overflow-y-auto bg-white rounded-xl border border-slate-200">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-100 sticky top-0 text-[10px] font-bold uppercase text-slate-500">
                                <tr>
                                    <th class="px-3 py-2" width="6%">No</th>
                                    <th class="px-3 py-2" width="44%">Nama Siswa &amp; NISN</th>
                                    <th class="px-3 py-2" width="20%">Kelas</th>
                                    <th class="px-3 py-2" width="30%">Predikat Nilai Sertifikat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($allFilteredSiswas as $idx => $s)
                                <tr>
                                    <td class="px-3 py-2 text-slate-400 font-bold">{{ $idx + 1 }}</td>
                                    <td class="px-3 py-2">
                                        <span class="font-bold text-slate-900">{{ $s->name }}</span>
                                        <span class="text-[10px] text-slate-400 ml-1">({{ $s->nisn ?: $s->username }})</span>
                                    </td>
                                    <td class="px-3 py-2 font-bold text-slate-700">
                                        {{ $s->kelas->nama_kelas ?? ($s->kelas->nama ?? '-') }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <select name="predikat_siswa[{{ $s->id }}]"
                                                @change="if ($event.target.value) { certForm.sample_predikat = $event.target.value; certForm.sample_siswa_nama = @js($s->name); certForm.sample_siswa_nisn = @js($s->nisn ?: $s->username); certForm.sample_siswa_kelas = @js($s->kelas->nama_kelas ?? ($s->kelas->nama ?? '-')); }"
                                                class="select-predikat-siswa w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded-lg font-bold text-slate-800">
                                            <option value="Sangat Baik">Sangat Baik</option>
                                            <option value="Baik" selected>Baik</option>
                                            <option value="Cukup">Cukup</option>
                                            <option value="">-- Tidak Ikut / Lewati --</option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100">
                    <button type="button"
                            @click="openDraftPdfPreview(false)"
                            class="px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold rounded-xl flex items-center gap-1.5 cursor-pointer">
                        <i class="bi-eye-fill"></i>
                        <span>Preview Hasil Sertifikat (PDF)</span>
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="modalGenerateMassal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl flex items-center gap-1.5 shadow-md shadow-emerald-600/20 cursor-pointer">
                            <i class="bi-magic"></i>
                            <span>Generate Sertifikat PDF untuk Siswa Terpilih</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL POPUP 2: UPLOAD 1 DOKUMEN DATA (EXCEL/CSV) -> GENERATE SEMUA SERTIFIKAT -->
    <!-- ========================================================================= -->
    <div x-show="modalImportData"
         x-cloak
         x-transition.opacity.duration.200ms
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5"
         style="display: none;">
        <div @click.away="if (!modalPreview) modalImportData = false"
             class="bg-white w-full max-w-4xl rounded-3xl p-5 sm:p-7 shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto">

            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="p-2.5 bg-indigo-100 text-indigo-700 rounded-xl">
                        <i class="bi-file-earmark-spreadsheet-fill text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Upload 1 Dokumen Data &rarr; Generate Sertifikat Masing-Masing Siswa</h3>
                        <p class="text-xs text-slate-500">Unggah 1 file Excel/CSV berisi daftar nilai predikat siswa, pilih pejabat penandatangan, dan cek Preview terlebih dahulu</p>
                    </div>
                </div>
                <button type="button" @click="modalImportData = false" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 cursor-pointer">
                    <i class="bi-x-lg"></i>
                </button>
            </div>

            <!-- Langkah 1: Unduh Template Data -->
            <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="space-y-0.5">
                    <div class="font-black text-indigo-950">Langkah 1: Unduh Template Data Siswa</div>
                    <p class="text-indigo-800 text-[11px]">
                        Template sudah terisi otomatis daftar nama &amp; NISN siswa. Anda cukup mengisi kolom <strong>PREDIKAT_NILAI</strong> (<em>Sangat Baik / Baik / Cukup</em>).
                    </p>
                </div>
                <a href="{{ route('dokumen-sertifikat.generate.template', ['kategori' => $kategori, 'kelas_id' => $kelasId]) }}"
                   class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs inline-flex items-center gap-1.5 shadow-sm whitespace-nowrap">
                    <i class="bi-download"></i>
                    <span>Unduh Template Data (.CSV/Excel)</span>
                </a>
            </div>

            <form method="POST" action="{{ route('dokumen-sertifikat.generate.import', $kategori) }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="font-black text-slate-800 uppercase text-[11px] tracking-wider flex items-center gap-1.5">
                        <i class="bi-award-fill text-indigo-600"></i>
                        <span>1. Kegiatan, File Data &amp; Tanggal Terbit</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="font-bold text-slate-700">Nama Kegiatan / Sertifikat <span class="text-rose-500">*</span></label>
                                @if($jenisSertifikats->isNotEmpty())
                                <button type="button"
                                        @click="impModeNamaBaru = !impModeNamaBaru"
                                        class="text-[11px] font-bold text-indigo-600 hover:underline cursor-pointer"
                                        x-text="impModeNamaBaru ? 'Pilih Master' : '+ Nama Baru'"></button>
                                @endif
                            </div>
                            <template x-if="!impModeNamaBaru">
                                <select name="jenis_sertifikat_id"
                                        x-model="certForm.jenis_sertifikat_id"
                                        required
                                        class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl font-bold text-slate-800">
                                    <option value="">-- Pilih Nama Kegiatan / Sertifikat --</option>
                                    @foreach($jenisSertifikats as $j)
                                    <option value="{{ $j->id }}">{{ $j->nama_sertifikat }}</option>
                                    @endforeach
                                </select>
                            </template>
                            <template x-if="impModeNamaBaru">
                                <input type="text"
                                       name="nama_sertifikat_baru"
                                       x-model="certForm.nama_sertifikat_baru"
                                       required
                                       placeholder="{{ $config['placeholder'] }}"
                                       class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl font-medium">
                            </template>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Upload 1 Dokumen Data (.CSV / .XLSX) <span class="text-rose-500">*</span></label>
                            <input type="file"
                                   name="file_data"
                                   accept=".csv,.xlsx,.xls,.txt"
                                   required
                                   class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-xl font-medium file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:bg-indigo-600 file:text-white file:font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tempat Terbit</label>
                            <input type="text" name="tempat_terbit" x-model="certForm.tempat_terbit" class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl font-medium">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tanggal Terbit</label>
                            <input type="text" name="tanggal_terbit" x-model="certForm.tanggal_terbit" class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl font-medium">
                        </div>
                    </div>
                </div>

                <!-- Pejabat Penandatangan (Otomatis dari Database / Input Manual) -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="font-black text-slate-800 uppercase text-[11px] tracking-wider flex items-center gap-1.5">
                            <i class="bi-pen-fill text-emerald-600"></i>
                            <span>2. Pejabat Penandatangan Sertifikat</span>
                        </div>
                        <span class="text-[11px] text-slate-500 font-medium">
                            <i class="bi-database-check text-indigo-600"></i> Pilih otomatis dari database atau input manual
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Pejabat 1 -->
                        <div class="p-3.5 rounded-xl bg-white border border-slate-200 space-y-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-bold text-indigo-700 text-[11px] uppercase">Pejabat Penandatangan 1 (Utama / Kiri) *</div>
                                <button type="button"
                                        @click="certForm.pejabat1_id = 'manual'; certForm.pejabat_1_jabatan = ''; certForm.pejabat_1_nama = ''; certForm.pejabat_1_nip = ''"
                                        class="text-[10px] font-bold text-indigo-600 hover:underline cursor-pointer">
                                    Reset / Input Manual
                                </button>
                            </div>

                            <div class="p-2.5 rounded-xl bg-indigo-50/70 border border-indigo-100">
                                <label class="block text-[10px] font-black text-indigo-900 uppercase mb-1">
                                    Pilih Pejabat 1 dari Database
                                </label>
                                <select x-model="certForm.pejabat1_id"
                                        @change="onSelectPejabat1($event.target.value)"
                                        class="w-full px-3 py-1.5 bg-white border border-indigo-200 rounded-lg font-bold text-slate-800 text-xs">
                                    <option value="manual">-- Input Manual (Ketik Sendiri di Bawah) --</option>
                                    @foreach($daftarPejabat ?? [] as $pjb)
                                    <option value="{{ $pjb['id'] }}">{{ $pjb['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Jabatan Penandatangan 1 <span class="text-rose-500">*</span></label>
                                <input type="text"
                                       name="pejabat_1_jabatan"
                                       list="listJabatanPejabat1"
                                       x-model="certForm.pejabat_1_jabatan"
                                       @input="onJabatanPejabatInput(1, $event.target.value)"
                                       required
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg font-medium">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Lengkap &amp; Gelar Pejabat 1 <span class="text-rose-500">*</span></label>
                                <input type="text"
                                       name="pejabat_1_nama"
                                       list="listNamaPejabatDb"
                                       x-model="certForm.pejabat_1_nama"
                                       @input="onNamaPejabatInput(1, $event.target.value)"
                                       required
                                       placeholder="Nama Pejabat & Gelar"
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg font-bold text-slate-900">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">NIP / NIY Pejabat 1 (Opsional)</label>
                                <input type="text"
                                       name="pejabat_1_nip"
                                       x-model="certForm.pejabat_1_nip"
                                       placeholder="Opsional"
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg font-medium">
                            </div>
                        </div>

                        <!-- Pejabat 2 -->
                        <div class="p-3.5 rounded-xl bg-white border border-slate-200 space-y-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-bold text-emerald-700 text-[11px] uppercase">Pejabat Penandatangan 2 (Kanan - Opsional)</div>
                                <button type="button"
                                        @click="onSelectPejabat2('none')"
                                        class="text-[10px] font-bold text-slate-500 hover:text-rose-600 hover:underline cursor-pointer">
                                    Kosongkan (1 TTD)
                                </button>
                            </div>

                            <div class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-100">
                                <label class="block text-[10px] font-black text-emerald-900 uppercase mb-1">
                                    Pilih Pejabat 2 dari Database / Input Manual
                                </label>
                                <select x-model="certForm.pejabat2_id"
                                        @change="onSelectPejabat2($event.target.value)"
                                        class="w-full px-3 py-1.5 bg-white border border-emerald-200 rounded-lg font-bold text-slate-800 text-xs">
                                    <option value="manual">-- Input Manual (Ketik Sendiri di Bawah) --</option>
                                    <option value="none">-- Kosongkan (Hanya 1 Penandatangan) --</option>
                                    @foreach($daftarPejabat ?? [] as $pjb)
                                    <option value="{{ $pjb['id'] }}">{{ $pjb['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Jabatan Penandatangan 2 (Kosongkan jika 1 TTD)</label>
                                <input type="text"
                                       name="pejabat_2_jabatan"
                                       list="listJabatanPejabat2"
                                       x-model="certForm.pejabat_2_jabatan"
                                       @input="onJabatanPejabatInput(2, $event.target.value)"
                                       placeholder="Contoh: Ketua Lembaga {{ $config['short'] }}"
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg font-medium">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Lengkap &amp; Gelar Pejabat 2</label>
                                <input type="text"
                                       name="pejabat_2_nama"
                                       list="listNamaPejabatDb"
                                       x-model="certForm.pejabat_2_nama"
                                       @input="onNamaPejabatInput(2, $event.target.value)"
                                       placeholder="Kosongkan jika 1 penandatangan"
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg font-bold text-slate-900">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">NIP / NIY Pejabat 2 (Opsional)</label>
                                <input type="text"
                                       name="pejabat_2_nip"
                                       x-model="certForm.pejabat_2_nip"
                                       placeholder="Opsional"
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg font-medium">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100">
                    <button type="button"
                            @click="openDraftPdfPreview(true)"
                            class="px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold rounded-xl flex items-center gap-1.5 cursor-pointer">
                        <i class="bi-eye-fill"></i>
                        <span>Preview Hasil Sertifikat (PDF)</span>
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="modalImportData = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl flex items-center gap-1.5 shadow-md shadow-indigo-600/20 cursor-pointer">
                            <i class="bi-cloud-arrow-up-fill"></i>
                            <span>Proses Dokumen &amp; Generate Sertifikat</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection

