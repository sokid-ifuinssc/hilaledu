@extends('layouts.app')

@section('title', 'Sampaikan Suara Siswa & Keluhan KBM (Anonim)')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div>
        <div class="text-xs text-slate-500 mb-1">
            <a href="{{ route('siswa.dashboard') }}" class="text-emerald-700 font-bold hover:underline">&larr; Kembali ke Dashboard Siswa</a>
        </div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
            <i class="bi-shield-shaded text-emerald-600"></i>
            <span>Kotak Suara Siswa & Evaluasi KBM (Anonim)</span>
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">Sampaikan masukan, kendala pembelajaran, atau saran perbaikan secara aman dan bertanggung jawab</p>
    </div>

    <!-- Anonymity Security Pledge Banner -->
    <div class="p-5 bg-gradient-to-r from-emerald-900 to-slate-900 text-white rounded-3xl shadow-md border border-emerald-500/30 flex items-start gap-4">
        <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center text-2xl flex-shrink-0 border border-emerald-500/30">
            <i class="bi-shield-lock-fill"></i>
        </div>
        <div class="space-y-1 text-xs">
            <h3 class="font-black text-sm text-emerald-300">Identitas Anda Dijamin 100% RAHASIA (Anonim)</h3>
            <p class="text-slate-300 leading-relaxed">
                Sistem tidak akan pernah menampilkan nama, NIS, maupun username Anda kepada guru yang dituju, wali kelas, para waka, guru BK, maupun kepala sekolah.
                Laporan yang mereka terima hanya berlabel: <strong class="text-amber-400">"Siswa Kelas {{ $kelasNama }} (Identitas Dirahasiakan)"</strong>.
            </p>
        </div>
    </div>

    <!-- Form Input Keluhan -->
    <form method="POST" action="{{ route('siswa.keluhan.store') }}" class="space-y-6 text-xs">
        @csrf
        <input type="hidden" name="kelas" value="{{ $kelasNama }}">

        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-5">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Guru Yang Dituju -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Guru Yang Dituju <span class="text-rose-500">*</span>
                    </label>
                    <select name="target_guru_user_id" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-emerald-600">
                        <option value="">-- Pilih Bapak/Ibu Guru --</option>
                        @foreach($gurus as $g)
                        <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->jabatan_utama ?: 'Guru' }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Mata Pelajaran -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Mata Pelajaran (Opsional)</label>
                    <select name="mata_pelajaran_id" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-emerald-600">
                        <option value="">-- Terkait Pembelajaran Mapel --</option>
                        @foreach($mapels as $m)
                        <option value="{{ $m->id }}">{{ $m->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Tanggal KBM / Kejadian -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal KBM / Kejadian Terkait</label>
                    <input type="date" name="tanggal_kbm" value="{{ date('Y-m-d') }}" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800">
                </div>

                <!-- Kategori Kendala -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kategori Masalah / Kendala <span class="text-rose-500">*</span></label>
                    <select name="kategori_masalah" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-emerald-600">
                        <option value="Penjelasan Materi">Penjelasan Materi Terlalu Cepat / Sulit Dipahami</option>
                        <option value="Metode Mengajar">Metode Pembelajaran / Variasi Belajar</option>
                        <option value="Kedisiplinan & Waktu">Kedisiplinan & Waktu Kehadiran KBM</option>
                        <option value="Beban Tugas">Penugasan / Beban Tugas Terlalu Berat</option>
                        <option value="Fasilitas / Lab">Fasilitas Lab / Alat Praktik Belum Memadai</option>
                        <option value="Sikap & Komunikasi">Sikap, Perilaku & Pola Komunikasi</option>
                        <option value="Lainnya">Kendala Lainnya</option>
                    </select>
                </div>
            </div>

            <!-- Isi Keluhan -->
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Uraikan Masukan / Keluhan Anda Secara Santun & Jelas <span class="text-rose-500">*</span>
                </label>
                <textarea name="isi_keluhan" rows="4" placeholder="Ceritakan kendala yang Anda alami di kelas dengan bahasa yang baik dan konstruktif..." required class="w-full p-3.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-600"></textarea>
                <span class="text-[11px] text-slate-400 mt-1 block">Tuliskan secara objektif demi perbaikan kualitas pembelajaran bersama.</span>
            </div>

            <!-- Harapan Siswa -->
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Harapan & Saran Solusi Dari Anda
                </label>
                <textarea name="harapan_siswa" rows="2" placeholder="Apa yang Anda harapkan dari bapak/ibu guru untuk pertemuan selanjutnya?" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium"></textarea>
            </div>

        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('siswa.dashboard') }}" class="px-5 py-3 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold rounded-xl shadow-lg shadow-emerald-700/30 transition flex items-center gap-2">
                <i class="bi-shield-check text-base"></i>
                <span>Kirim Masukan Secara Anonim</span>
            </button>
        </div>

    </form>

</div>
@endsection
