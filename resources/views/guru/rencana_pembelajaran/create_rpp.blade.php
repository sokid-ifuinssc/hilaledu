@extends('layouts.app')

@section('title', 'Buat Modul Ajar Harian (RPP)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div>
        <div class="text-xs text-slate-500 mb-1">
            <a href="{{ route('guru.rencana-pembelajaran.index') }}" class="text-blue-600 hover:underline">&larr; Kembali ke Rencana Ajar</a>
        </div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
            <i class="bi-file-earmark-plus text-blue-600"></i>
            <span>Susun Modul Ajar Harian (RPP) Sesuai Jadwal</span>
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">Hubungkan rencana KBM dengan slot jadwal mengajar dan Tujuan Pembelajaran Kurikulum Merdeka</p>
    </div>

    <form method="POST" action="{{ route('guru.rencana-pembelajaran.rpp.store') }}" class="space-y-6">
        @csrf

        <!-- Card 1: Slot Jadwal & Pertemuan -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
            <h3 class="font-extrabold text-sm text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-black">1</span>
                <span>Pilih Slot Jadwal Mengajar</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jadwal Mengajar Anda</label>
                    <select name="jadwal_pelajaran_id" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-semibold text-slate-800 focus:ring-2 focus:ring-blue-600">
                        @foreach($jadwals as $j)
                        <option value="{{ $j->id }}" {{ $selectedJadwalId == $j->id ? 'selected' : '' }}>
                            {{ $j->hari }} ({{ substr($j->jam_mulai,0,5) }}-{{ substr($j->jam_selesai,0,5) }}) &bull; Kelas {{ $j->kelas }} &bull; {{ $j->mataPelajaran->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Pelaksanaan</label>
                    <input type="date" name="tanggal_rencana" value="{{ date('Y-m-d') }}" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-600">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pertemuan Ke-</label>
                    <input type="number" name="pertemuan_ke" value="1" min="1" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-blue-700">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tautkan Tujuan Pembelajaran (TP)</label>
                    <select name="tujuan_pembelajaran_id" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                        <option value="">-- Pilih Tujuan Pembelajaran --</option>
                        @foreach($tps as $tp)
                        <option value="{{ $tp->id }}">
                            {{ $tp->kode_tp }} &bull; {{ \Illuminate\Support\Str::limit($tp->deskripsi, 60) }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Card 2: Materi & Langkah Pembelajaran -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
            <h3 class="font-extrabold text-sm text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-black">2</span>
                <span>Rincian Materi & Skenario KBM</span>
            </h3>

            <div class="text-xs">
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Materi Pokok / Topik Pembahasan Hari Itu</label>
                <input type="text" name="materi_pokok" placeholder="Contoh: Konfigurasi Subnetting dan VLAN pada Switch Managed..." required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-900 focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">A. Kegiatan Pendahuluan (10-15 Menit)</label>
                    <textarea name="aktivitas_pendahuluan" rows="2" placeholder="Salam, doa bersama, presensi kehadiran, apersepsi keterkaitan materi sebelumnya, dan penyampaian tujuan pembelajaran..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl">Guru membuka pembelajaran dengan salam dan doa, melakukan apersepsi, memotivasi peserta didik, serta menyampaikan tujuan pembelajaran dan asesmen yang akan dicapai.</textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">B. Kegiatan Inti (Eksplorasi, Praktik & Kolaborasi)</label>
                    <textarea name="aktivitas_inti" rows="4" placeholder="Skenario pembelajaran aktif, diskusi kelompok, demonstrasi guru, unjuk kerja praktik siswa, presentasi hasil..." required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl">1. Peserta didik mengamati demonstrasi materi dan panduan jobsheet.
2. Peserta didik dibagi menjadi kelompok kecil untuk mempraktikkan konfigurasi dan lembar kerja.
3. Guru memfasilitasi dan melakukan asesmen formatif berkeliling.
4. Setiap perwakilan mempresentasikan hasil temuan praktikum di depan kelas.</textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">C. Kegiatan Penutup (10 Menit)</label>
                    <textarea name="aktivitas_penutup" rows="2" placeholder="Refleksi pembelajaran bersama siswa, kesimpulan, tindak lanjut penugasan, dan doa penutup..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl">Guru bersama peserta didik menyimpulkan intisari materi, melakukan refleksi pembelajaran hari ini, memberikan tindak lanjut materi selanjutnya, dan menutup dengan doa bersama.</textarea>
                </div>
            </div>
        </div>

        <!-- Card 3: Asesmen & Media Sumber Belajar -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
            <h3 class="font-extrabold text-sm text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-black">3</span>
                <span>Asesmen, Media & Sumber Belajar</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Bentuk Asesmen / Penilaian</label>
                    <input type="text" name="bentuk_asesmen" value="Formatif (Observasi Kinerja & Lembar Kerja Jobsheet)" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Media & Sumber Belajar</label>
                    <input type="text" name="media_sumber" placeholder="Slide PPT, Jobsheet LKPD, Video Tutorial, Perangkat Lab..." value="Modul Digital SMK Plus Al-Hilal, LKPD Siswa, Lab Komputer" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                </div>
            </div>

            <div class="text-xs">
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Tambahan (Opsional)</label>
                <textarea name="catatan" rows="2" placeholder="Catatan khusus kesiapan sarana, diferensiasi siswa, dll..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl"></textarea>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('guru.rencana-pembelajaran.index') }}" class="px-5 py-3 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-blue-600/30 transition flex items-center gap-2">
                <i class="bi-check2-circle text-base"></i>
                <span>Simpan Modul Ajar Harian (RPP)</span>
            </button>
        </div>

    </form>

</div>
@endsection
