@extends('layouts.app')

@section('title', 'Input Kegiatan Tugas Tambahan & Agenda Sekolah')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{
    bidang: '{{ $defaultBidang }}',
    judul: '',
    kategori: 'Kegiatan Sekolah',
    tanggal_kegiatan: '{{ date('Y-m-d') }}',
    tanggal_selesai: '{{ date('Y-m-d') }}',
    tempat: 'SMK Plus Al-Hilal Arjawinangun',
    sasaran: 'Seluruh Siswa & Dewan Guru',
    deskripsi: '',
    sinkron_kalender: true,

    setPill(b, j, k, s, t, d) {
        this.bidang = b;
        this.judul = j;
        this.kategori = k;
        this.sasaran = s;
        this.tempat = t;
        this.deskripsi = d;
    }
}">

    <!-- Header -->
    <div>
        <div class="text-xs text-slate-500 mb-1">
            <a href="{{ route('guru.kegiatan.index') }}" class="text-blue-600 hover:underline inline-flex items-center gap-1 font-semibold">
                <i class="bi-arrow-left"></i> Kembali ke Daftar Kegiatan
            </a>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="bi-calendar-plus-fill text-amber-500"></i>
                    <span>Input Kegiatan & Program Kerja Sekolah</span>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Penginputan agenda resmi tugas tambahan (Waka Hubin, Kesiswaan, OSIS, Kurikulum, Sarpras) yang <strong>otomatis tersinkron ke Kalender Akademik</strong>.
                </p>
            </div>
            <span class="px-3 py-1 rounded-xl bg-indigo-50 text-indigo-800 border border-indigo-200 text-xs font-bold self-start sm:self-auto flex items-center gap-1.5">
                <i class="bi-calendar-range"></i>
                <span>T.A. {{ $kalenderAktif->tahun_ajaran ?? '2026/2027' }}</span>
            </span>
        </div>
    </div>

    <!-- Tombol Inspirasi Contoh Kegiatan Cepat (Pills) -->
    <div class="p-4 bg-gradient-to-r from-amber-50 via-indigo-50 to-emerald-50 rounded-2xl border border-indigo-100 space-y-2">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                <i class="bi-lightning-charge-fill text-amber-500"></i>
                <span>Pilih Inspirasi Agenda Tugas Tambahan (Klik untuk Isi Cepat):</span>
            </span>
        </div>
        <div class="flex flex-wrap gap-2 text-xs">
            <button type="button" 
                    @click="setPill('waka_hubin', 'Kunjungan Industri (KI) Kelas XI ke DUDI Mitra', 'Kunjungan Industri', 'Siswa Kelas XI Semua Konsentrasi Keahlian', 'Kawasan Industri Cikarang / Bandung', 'Pelaksanaan Kunjungan Industri (KI) untuk pengenalan etos kerja dan teknologi industri riil kepada peserta didik kelas XI.')"
                    class="px-3 py-1.5 rounded-xl bg-white hover:bg-purple-50 text-purple-800 font-bold border border-purple-200 shadow-2xs transition flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                <span>Waka Hubin: Kunjungan Industri (KI)</span>
            </button>

            <button type="button" 
                    @click="setPill('waka_kesiswaan', 'Pekan Olahraga Antar Kelas & Seni (Classmeeting)', 'Pekan Olahraga & Seni', 'Seluruh Peserta Didik Kelas X, XI, XII', 'Lapangan Olahraga SMK Plus Al-Hilal', 'Pertandingan olahraga dan seni antar kelas pasca asesmen semester untuk menumbuhkan sportivitas dan kekompakan siswa.')"
                    class="px-3 py-1.5 rounded-xl bg-white hover:bg-emerald-50 text-emerald-800 font-bold border border-emerald-200 shadow-2xs transition flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                <span>Kesiswaan: Pekan Olahraga / Classmeeting</span>
            </button>

            <button type="button" 
                    @click="setPill('pembina_osis', 'Latihan Dasar Kepemimpinan Siswa (LDKS)', 'LDKS OSIS', 'Pengurus OSIS & MPK Terpilih', 'Bumi Perkemahan / Kampus Sekolah', 'Pelatihan kepemimpinan, manajemen organisasi, dan kedisiplinan bagi pengurus OSIS periode baru.')"
                    class="px-3 py-1.5 rounded-xl bg-white hover:bg-emerald-50 text-emerald-800 font-bold border border-emerald-200 shadow-2xs transition flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                <span>Pembina OSIS: LDKS Siswa</span>
            </button>

            <button type="button" 
                    @click="setPill('waka_kesiswaan', 'Bazaar Kewirausahaan & Gelar Karya Kreatif Siswa', 'Bazaar Kewirausahaan', 'Seluruh Warga Sekolah & Tamu Undangan', 'Halaman Utama SMK Plus Al-Hilal', 'Pameran produk kewirausahaan, kuliner, dan hasil inovasi karya siswa SMK Plus Al-Hilal.')"
                    class="px-3 py-1.5 rounded-xl bg-white hover:bg-cyan-50 text-cyan-900 font-bold border border-cyan-200 shadow-2xs transition flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-cyan-600"></span>
                <span>Kesiswaan/OSIS: Bazaar Kewirausahaan</span>
            </button>

            <button type="button" 
                    @click="setPill('waka_kurikulum', 'Workshop Peningkatan Mutu Modul Ajar & KSP', 'Workshop Kurikulum', 'Seluruh Dewan Guru Produktif & Umum', 'Laboratorium Multimedia', 'Penyelarasan perangkat ajar, kurikulum merdeka, dan penguatan literasi numerasi sekolah.')"
                    class="px-3 py-1.5 rounded-xl bg-white hover:bg-blue-50 text-blue-800 font-bold border border-blue-200 shadow-2xs transition flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <span>Kurikulum: Workshop Modul Ajar</span>
            </button>
        </div>
    </div>

    <!-- Form Input Kegiatan -->
    <form method="POST" action="{{ route('guru.kegiatan.store') }}" enctype="multipart/form-data" class="space-y-6 text-xs">
        @csrf

        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-5">
            
            <!-- Bidang & Kategori -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Bidang / Tugas Tambahan <span class="text-rose-500">*</span>
                    </label>
                    <select name="bidang" x-model="bidang" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-indigo-600">
                        <option value="waka_hubin">Waka Hubin (Humas & Hubungan Industri)</option>
                        <option value="waka_kesiswaan">Waka Kesiswaan & Karakter</option>
                        <option value="pembina_osis">Pembina OSIS & Kesiswaan</option>
                        <option value="waka_kurikulum">Waka Kurikulum & Akademik</option>
                        <option value="waka_sarpras">Waka Sarana & Prasarana</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kategori Kegiatan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="kategori" x-model="kategori" placeholder="Contoh: Kunjungan Industri, LDKS, Classmeeting..." required 
                           class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-semibold">
                </div>
            </div>

            <!-- Judul Kegiatan -->
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Judul Agenda / Kegiatan Sekolah <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="judul" x-model="judul" placeholder="Contoh: Kunjungan Industri PT Telkom & PT Len Industri..." required 
                       class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-900 text-sm focus:ring-2 focus:ring-indigo-600">
            </div>

            <!-- Rentang Tanggal Pelaksanaan (Mulai & Selesai) -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-600 block">
                    <i class="bi-calendar-date text-indigo-600"></i> Rentang Tanggal Pelaksanaan
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_kegiatan" x-model="tanggal_kegiatan" required 
                               class="w-full p-2.5 bg-white border border-slate-300 rounded-xl font-bold text-slate-800">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Selesai (Opsional)</label>
                        <input type="date" name="tanggal_selesai" x-model="tanggal_selesai" 
                               class="w-full p-2.5 bg-white border border-slate-300 rounded-xl font-bold text-slate-800">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Jika 1 hari saja, samakan dengan tanggal mulai.</span>
                    </div>
                </div>
            </div>

            <!-- Jam Pelaksanaan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Waktu Mulai</label>
                    <input type="time" name="waktu_mulai" value="08:00" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Waktu Selesai</label>
                    <input type="time" name="waktu_selesai" value="15:00" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                </div>
            </div>

            <!-- Tempat & Sasaran -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tempat / Lokasi <span class="text-rose-500">*</span></label>
                    <input type="text" name="tempat" x-model="tempat" placeholder="Contoh: Kawasan Industri / Aula Sekolah..." required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Target Peserta / Sasaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="sasaran" x-model="sasaran" placeholder="Contoh: Siswa Kelas XI / Pengurus OSIS..." required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                </div>
            </div>

            <!-- Uraian / Deskripsi -->
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Uraian / Deskripsi Kegiatan <span class="text-rose-500">*</span></label>
                <textarea name="deskripsi" x-model="deskripsi" rows="4" placeholder="Jelaskan tujuan, persiapan teknis, agenda pokok, dan rincian kegiatan..." required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium leading-relaxed"></textarea>
            </div>

            <!-- Checkbox Sinkronisasi Kalender Akademik -->
            <div class="p-4 bg-emerald-50/80 border border-emerald-300 rounded-2xl flex items-start gap-3">
                <input type="checkbox" name="sinkron_kalender" id="check_sinkron" value="1" x-model="sinkron_kalender"
                       class="mt-1 w-5 h-5 text-emerald-600 rounded focus:ring-emerald-500 cursor-pointer">
                <label for="check_sinkron" class="cursor-pointer space-y-0.5">
                    <span class="font-black text-emerald-950 text-xs flex items-center gap-1.5">
                        <i class="bi-calendar-check-fill text-emerald-600"></i>
                        <span>Sinkronkan & Tampilkan Langsung di Kalender Akademik Sekolah (T.A. {{ $kalenderAktif->tahun_ajaran ?? '2026/2027' }})</span>
                    </span>
                    <p class="text-[11px] text-emerald-800 leading-relaxed">
                        Saat dicentang, kegiatan ini akan otomatis tercatat pada Kalender Pendidikan sekolah dengan warna badge resmi dan dapat dilihat oleh seluruh guru, siswa, dan kepala sekolah.
                    </p>
                </label>
            </div>

            <!-- Berkas Lampiran -->
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Lampiran Surat / Petunjuk Teknis (Opsional - PDF/Gambar)</label>
                <input type="file" name="lampiran" class="w-full text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
            </div>

        </div>

        <!-- Tombol Submit -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('guru.kegiatan.index') }}" class="px-5 py-3 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition flex items-center gap-2 text-xs">
                <i class="bi-calendar-plus text-base"></i>
                <span>Posting Kegiatan & Sinkronkan ke Kalender</span>
            </button>
        </div>

    </form>

</div>
@endsection
