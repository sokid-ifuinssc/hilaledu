@extends('layouts.app')

@section('title', 'Master Pengaturan Sekolah & Kategori')

@section('content')
<div class="space-y-6" x-data="{
    categories: {{ json_encode($kategoriList) }},
    newCategory: '',
    addCategory() {
        if (this.newCategory.trim() !== '' && !this.categories.includes(this.newCategory.trim())) {
            this.categories.push(this.newCategory.trim());
            this.newCategory = '';
        }
    },
    removeCategory(index) {
        this.categories.splice(index, 1);
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-gear-wide-connected text-amber-500"></i>
                <span>Master Pengaturan Sekolah & Kategori</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Kelola profil lembaga, alamat resmi, data pimpinan penandatangan dokumen, serta opsi kategori suara siswa.
            </p>
        </div>
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                <i class="bi-shield-check text-emerald-600"></i> Hak Akses: Superadmin & Pengelola Akademik
            </span>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl flex items-center gap-3 text-xs font-semibold shadow-xs">
        <i class="bi-check-circle-fill text-lg text-emerald-600 flex-shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.pengaturan.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- CARD 1: IDENTITAS & ALAMAT SEKOLAH -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="p-2.5 bg-amber-50 text-amber-600 rounded-2xl border border-amber-200">
                    <i class="bi-building text-xl"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Identitas & Alamat Resmi Sekolah</h2>
                    <p class="text-xs text-slate-500">Data ini dicantumkan pada kop surat, formulir jadwal, dan laporan resmi KBM.</p>
                </div>
            </div>

            <!-- Preview Logo Master & Upload -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col sm:flex-row items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-white p-1 border border-slate-300 shadow-xs flex items-center justify-center flex-shrink-0">
                    <img src="{{ asset($settings['logo_sekolah'] ?? 'images/logo.png') }}" class="w-full h-full object-contain" alt="Logo Master" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                </div>
                <div class="flex-1 space-y-1">
                    <label class="block text-xs font-bold text-slate-800">Logo Resmi Sekolah (Kop Dokumen Cetak)</label>
                    <p class="text-[11px] text-slate-500">Logo ini otomatis tercetak pada kop jadwal pelajaran, kurikulum, dan laporan resmi.</p>
                    <input type="file" name="logo_file" accept="image/*" class="text-xs text-slate-600 mt-1 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200 cursor-pointer">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Sekolah / Lembaga <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_sekolah" value="{{ old('nama_sekolah', $settings['nama_sekolah'] ?? 'SMK Plus Al-Hilal') }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Pokok Sekolah Nasional (NPSN)</label>
                    <input type="text" name="npsn" value="{{ old('npsn', $settings['npsn'] ?? '69758451') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Lengkap Sekolah <span class="text-rose-500">*</span></label>
                    <textarea name="alamat_sekolah" rows="2" required
                              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">{{ old('alamat_sekolah', $settings['alamat_sekolah'] ?? 'Jl. H. Manshur No 7 Lap. Bima Rembes, Ds. Tegalgubug, Kec. Arjawinangun, Kab. Cirebon') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Telepon Sekolah</label>
                    <input type="text" name="telepon_sekolah" value="{{ old('telepon_sekolah', $settings['telepon_sekolah'] ?? '(0231) 357123') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Resmi</label>
                    <input type="email" name="email_sekolah" value="{{ old('email_sekolah', $settings['email_sekolah'] ?? 'smkpal2021@gmail.com') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Website Resmi</label>
                    <input type="text" name="website_sekolah" value="{{ old('website_sekolah', $settings['website_sekolah'] ?? 'smkplusalhilal.sch.id') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Titimangsa Cetak Dokumen <span class="text-rose-500">*</span></label>
                    <input type="text" name="titimangsa" value="{{ old('titimangsa', $settings['titimangsa'] ?? 'Arjawinangun, 1 September 2026') }}" required
                           placeholder="Contoh: Arjawinangun, 1 September 2026"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                </div>
            </div>
        </div>

        <!-- CARD 2: PIMPINAN PENANDATANGAN & AKADEMIK -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="p-2.5 bg-blue-50 text-blue-600 rounded-2xl border border-blue-200">
                    <i class="bi-pen text-xl"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Pimpinan Penandatangan & Periode KBM</h2>
                    <p class="text-xs text-slate-500">Nama pimpinan yang tertera pada lembar jadwal cetak dan dokumen resmi kurikulum.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Kepala Sekolah <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_kepala_sekolah" value="{{ old('nama_kepala_sekolah', $settings['nama_kepala_sekolah'] ?? 'Mukhammad Mansyur, S.Pt') }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">NIP Kepala Sekolah</label>
                    <input type="text" name="nip_kepala_sekolah" value="{{ old('nip_kepala_sekolah', $settings['nip_kepala_sekolah'] ?? '6942767668130350') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Waka Kurikulum & Akademik <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_waka_kurikulum" value="{{ old('nama_waka_kurikulum', $settings['nama_waka_kurikulum'] ?? 'Sokid, S.T, M.Kom') }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">NIP Waka Kurikulum</label>
                    <input type="text" name="nip_waka_kurikulum" value="{{ old('nip_waka_kurikulum', $settings['nip_waka_kurikulum'] ?? '') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                </div>

                <!-- Panel Master Tahun Akademik HilalEdu -->
                <div class="md:col-span-2 p-4 bg-gradient-to-r from-emerald-50 to-blue-50 border border-emerald-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg flex-shrink-0 shadow-xs">
                            <i class="bi-cloud-check-fill"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-xs text-slate-900">Status Database Utama HilalEdu</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    {{ ($hilalAcademic['is_connected'] ?? false) ? 'AKTIF & TERHUBUNG' : 'OFFLINE' }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-600 mt-0.5">
                                Tahun akademik berjalan di HilalEdu: <strong>{{ $hilalAcademic['tahun_ajaran'] ?? '2026/2027' }}</strong> (Semester {{ ucfirst($hilalAcademic['semester'] ?? 'Ganjil') }}).
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" form="form-sync-hilaledu" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 whitespace-nowrap">
                            <i class="bi-arrow-repeat"></i>
                            <span>Sinkronkan Tahun Ajaran</span>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Tahun Pelajaran Berjalan (Aktif) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="tahun_pelajaran" 
                           value="{{ old('tahun_pelajaran', $settings['tahun_pelajaran'] ?? \App\Models\PengaturanSekolah::getActiveTahunAjaran()) }}" required
                           placeholder="Contoh: 2026/2027"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                    <span class="text-[10px] text-slate-500 mt-1 block">Tersambung langsung ke seluruh modul HilalEdu, Kalender Pendidikan, dan dokumen cetak KBM.</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Semester Aktif <span class="text-rose-500">*</span>
                    </label>
                    @php
                        $curSmt = strtolower($settings['semester'] ?? \App\Models\PengaturanSekolah::getActiveSemester());
                    @endphp
                    <select name="semester" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                        <option value="Ganjil" {{ $curSmt === 'ganjil' ? 'selected' : '' }}>Semester Ganjil (Juli - Desember)</option>
                        <option value="Genap" {{ $curSmt === 'genap' ? 'selected' : '' }}>Semester Genap (Januari - Juni)</option>
                    </select>
                    <span class="text-[10px] text-slate-500 mt-1 block">Digunakan pada jadwal mengajar, modul minggu efektif, dan absensi guru.</span>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Program Keahlian (Tercantum pada Header Jadwal)</label>
                    <input type="text" name="program_keahlian" value="{{ old('program_keahlian', $settings['program_keahlian'] ?? 'Teknik Jaringan Komputer dan Telekomunikasi, Teknik Otomotif, Akuntansi dan Keuangan Lembaga') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Konsentrasi Keahlian (Tercantum pada Header Jadwal)</label>
                    <input type="text" name="konsentrasi_keahlian" value="{{ old('konsentrasi_keahlian', $settings['konsentrasi_keahlian'] ?? 'Teknik Komputer dan Jaringan, Teknik Kendaraan Ringan, Layanan Perbankan') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                </div>
            </div>
        </div>

        <!-- CARD 3: MASTER PENUGASAN GURU & KODE MENGAJAR RESMI (1 - 30) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-2xl border border-emerald-200">
                        <i class="bi-person-badge text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">Master Penugasan Guru & Kode Mengajar (Kode 1 - 30)</h2>
                        <p class="text-xs text-slate-500">Sesuaikan kode pengenal, nama guru resmi, mapel yang ditugaskan, dan jabatan struktural untuk dokumen jadwal KBM.</p>
                    </div>
                </div>
            </div>

            <div class="p-3.5 bg-blue-50/70 border border-blue-200 text-blue-900 rounded-2xl text-xs flex items-start gap-2.5">
                <i class="bi-info-circle-fill text-blue-600 text-sm mt-0.5 flex-shrink-0"></i>
                <div class="leading-relaxed">
                    Data pada tabel ini disinkronkan langsung dengan <strong>Legenda 30 Guru Resmi</strong> pada lembar cetak Matriks Jadwal Pelajaran (Gambar Lembar Resmi). Anda dapat mengubah nama, mapel penugasan, dan jabatan di sini kapan saja.
                </div>
            </div>

            <!-- Tabel Form Penugasan 30 Guru -->
            <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-slate-50 text-slate-700 font-extrabold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-3 w-14 text-center">KODE</th>
                            <th class="py-3 px-3 w-3/12">NAMA GURU (LEMBAR CETAK)</th>
                            <th class="py-3 px-3 w-3/12">TAUTAN AKUN DATABASE (USER)</th>
                            <th class="py-3 px-3 w-3/12">MAPEL PENUGASAN</th>
                            <th class="py-3 px-3 w-3/12">JABATAN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                        @foreach($guruList as $i => $g)
                        @php 
                            $badgeColor = match((int)$i) {
                                1 => 'bg-red-600 text-white font-black',
                                2 => 'bg-yellow-400 text-slate-900 font-black',
                                20 => 'bg-emerald-600 text-white font-black',
                                default => 'bg-slate-100 text-slate-800 font-bold border border-slate-300'
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-2.5 px-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs {{ $badgeColor }}">
                                    {{ $i }}
                                </span>
                                <input type="hidden" name="penugasan_guru[{{ $i }}][kode]" value="{{ $i }}">
                            </td>
                            <td class="py-2.5 px-3">
                                <input type="text" name="penugasan_guru[{{ $i }}][nama]" 
                                       value="{{ old("penugasan_guru.{$i}.nama", $g['nama'] ?? '') }}" required
                                       placeholder="Nama Guru"
                                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                            </td>
                            <td class="py-2.5 px-3">
                                <select name="penugasan_guru[{{ $i }}][user_id]" 
                                        class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                                    <option value="">-- Belum Ditautkan Akun --</option>
                                    @foreach($allGurus ?? [] as $u)
                                    <option value="{{ $u->id }}" {{ ($g['user_id'] ?? null) == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }} (ID: {{ $u->id }})
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-2.5 px-3">
                                <input type="text" name="penugasan_guru[{{ $i }}][mapel]" 
                                       value="{{ old("penugasan_guru.{$i}.mapel", $g['mapel'] ?? '') }}"
                                       placeholder="Contoh: CB, PAI / Produktif TKJ"
                                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                            </td>
                            <td class="py-2.5 px-3">
                                <input type="text" name="penugasan_guru[{{ $i }}][jabatan]" 
                                       value="{{ old("penugasan_guru.{$i}.jabatan", $g['jabatan'] ?? '') }}"
                                       placeholder="Contoh: KEPALA SMK / W KURIKULUM"
                                       class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CARD 4: MANAJEMEN KATEGORI SUARA SISWA (KELUHAN KBM) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="p-2.5 bg-purple-50 text-purple-600 rounded-2xl border border-purple-200">
                    <i class="bi-tags text-xl"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Manajemen Kategori Masukan Siswa (Suara Siswa)</h2>
                    <p class="text-xs text-slate-500">Kategori topik yang dapat dipilih oleh siswa saat menyampaikan evaluasi pembelajaran secara online.</p>
                </div>
            </div>

            <!-- List Tags Kategori -->
            <div class="space-y-3">
                <div class="flex flex-wrap gap-2 min-h-[44px] p-3 bg-slate-50 border border-slate-200 rounded-2xl items-center">
                    <template x-for="(cat, index) in categories" :key="index">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-100 text-purple-900 text-xs font-bold border border-purple-200">
                            <span x-text="cat"></span>
                            <input type="hidden" name="kategori_keluhan[]" :value="cat">
                            <button type="button" @click="removeCategory(index)" class="text-purple-500 hover:text-rose-600 transition ml-1">
                                <i class="bi-x-circle-fill text-xs"></i>
                            </button>
                        </span>
                    </template>
                    <span x-show="categories.length === 0" class="text-xs text-slate-400 italic">Belum ada kategori. Tambahkan di bawah.</span>
                </div>

                <!-- Input Tambah Kategori -->
                <div class="flex items-center gap-2 max-w-md">
                    <input type="text" x-model="newCategory" @keydown.enter.prevent="addCategory()"
                           placeholder="Ketik nama kategori baru..."
                           class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                    <button type="button" @click="addCategory()" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5">
                        <i class="bi-plus-lg"></i>
                        <span>Tambah Kategori</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tombol Simpan -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <button type="submit" class="px-6 py-3 bg-slate-900 hover:bg-black text-white font-extrabold rounded-2xl text-xs transition inline-flex items-center gap-2 shadow-lg shadow-slate-900/20">
                <i class="bi-floppy2-fill"></i>
                <span>Simpan Seluruh Pengaturan Master</span>
            </button>
        </div>
    </form>

    <!-- Hidden Form for Sync from HilalEdu -->
    <form id="form-sync-hilaledu" method="POST" action="{{ route('admin.pengaturan.sync-hilaledu') }}" class="hidden" onsubmit="return confirm('Sinkronkan Tahun Pelajaran & Semester langsung dari database HilalEdu?');">
        @csrf
    </form>

</div>
@endsection
