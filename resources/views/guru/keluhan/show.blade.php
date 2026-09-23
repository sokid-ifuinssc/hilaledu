@extends('layouts.app')

@section('title', 'Tindak Lanjut Suara Siswa & Saran KBM')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="text-xs text-slate-500 mb-1">
                <a href="{{ route('guru.keluhan.index') }}" class="text-blue-600 hover:underline">&larr; Kembali ke Daftar Keluhan & Suara Siswa</a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-chat-square-quote text-indigo-600"></i>
                <span>Detail Evaluasi KBM & Tindak Lanjut Pembinaan</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Identitas siswa pengirim dirahasiakan &bull; Terhubung ke Guru, Walikelas, Kaprog, Waka, BK, dan Kepala Sekolah</p>
        </div>
    </div>

    <!-- Complaint Detail Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-5 text-xs">
        
        <div class="flex items-center justify-between border-b pb-4 flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full font-black text-xs uppercase border {{ $keluhan->status_badge }}">
                    {{ $keluhan->status_label }}
                </span>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->canManageAcademic())
                <span class="px-3 py-1 bg-emerald-50 text-emerald-800 font-extrabold rounded-full border border-emerald-300 inline-flex items-center gap-1.5" title="Identitas Terbuka untuk Pimpinan/Admin">
                    <i class="bi-person-check-fill text-emerald-600"></i>
                    <span>{{ $keluhan->siswa->name ?? ('Siswa #'.$keluhan->siswa_user_id) }} (NIS: {{ $keluhan->siswa->username ?? '-' }}) &bull; Kelas: {{ $keluhan->kelas }}</span>
                </span>
                @else
                <span class="px-3 py-1 bg-indigo-50 text-indigo-800 font-extrabold rounded-full border border-indigo-200 inline-flex items-center gap-1.5">
                    <i class="bi-shield-lock-fill text-indigo-500"></i>
                    <span>{{ $keluhan->pengirim_anonim }}</span>
                </span>
                @endif
            </div>
            <div class="text-slate-500 font-medium">
                Tanggal KBM: <strong>{{ \Carbon\Carbon::parse($keluhan->tanggal_kbm)->format('d M Y') }}</strong>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="p-3 bg-slate-50 rounded-xl border">
                <span class="text-[10px] text-slate-400 font-bold uppercase block">Guru Yang Dituju</span>
                <span class="font-bold text-slate-900">{{ $keluhan->targetGuru->name ?? '-' }}</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border">
                <span class="text-[10px] text-slate-400 font-bold uppercase block">Mata Pelajaran</span>
                <span class="font-bold text-slate-900">{{ $keluhan->mataPelajaran ? $keluhan->mataPelajaran->nama : '-' }}</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border">
                <span class="text-[10px] text-slate-400 font-bold uppercase block">Kategori Masalah</span>
                <span class="font-bold text-amber-800">{{ $keluhan->kategori_masalah }}</span>
            </div>
        </div>

        <div class="space-y-2">
            <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Uraian Masukan / Keluhan Siswa:</span>
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-slate-800 text-xs leading-relaxed font-medium">
                "{{ $keluhan->isi_keluhan }}"
            </div>
        </div>

        @if($keluhan->harapan_siswa)
        <div class="p-3.5 bg-emerald-50 rounded-2xl border border-emerald-200 text-emerald-950">
            <strong class="text-emerald-800 font-bold">Harapan & Saran Siswa:</strong>
            <p class="mt-1">{{ $keluhan->harapan_siswa }}</p>
        </div>
        @endif

        @if($keluhan->catatan_tindak_lanjut)
        <div class="p-3.5 bg-blue-50 rounded-2xl border border-blue-200 text-blue-950">
            <strong class="text-blue-800 font-bold">Catatan Tindak Lanjut Guru:</strong>
            <p class="mt-1">{{ $keluhan->catatan_tindak_lanjut }}</p>
        </div>
        @endif

    </div>

    <!-- Form Tindak Lanjut oleh Guru yang Dituju -->
    @if($keluhan->target_guru_user_id === auth()->id() || auth()->user()->isSuperAdmin())
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4 text-xs">
        <h3 class="font-black text-sm text-slate-900 flex items-center gap-2 border-b pb-3">
            <i class="bi-pencil-square text-blue-600"></i>
            <span>Tindak Lanjut & Evaluasi Guru</span>
        </h3>

        <form method="POST" action="{{ route('guru.keluhan.update-status', $keluhan) }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Perbarui Status Penanganan</label>
                <select name="status" class="w-full sm:w-1/2 p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                    <option value="diproses" {{ $keluhan->status === 'diproses' ? 'selected' : '' }}>🔵 Sedang Ditindaklanjuti / Evaluasi</option>
                    <option value="selesai" {{ $keluhan->status === 'selesai' ? 'selected' : '' }}>🟢 Selesai Ditindaklanjuti & Diselesaikan</option>
                    <option value="baru" {{ $keluhan->status === 'baru' ? 'selected' : '' }}>🟡 Baru</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Tindak Lanjut & Komitmen Perbaikan Guru</label>
                <textarea name="catatan_tindak_lanjut" rows="3" placeholder="Tuliskan langkah perbaikan yang telah / akan Anda lakukan di kelas..." required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">{{ $keluhan->catatan_tindak_lanjut }}</textarea>
            </div>

            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-sm transition">
                Simpan Tanggapan Guru
            </button>
        </form>
    </div>
    @endif

    <!-- Daftar Saran Perbaikan dari Pimpinan / Kaprog / Walikelas -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4 text-xs">
        <h3 class="font-black text-sm text-slate-900 flex items-center justify-between border-b pb-3">
            <div class="flex items-center gap-2">
                <i class="bi-patch-question text-purple-600 text-lg"></i>
                <span>Arahan Pembinaan & Saran Perbaikan (Kepala Sekolah, Waka, Kaprog, Walikelas)</span>
            </div>
            <span class="px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 text-[10px] font-black">
                {{ $keluhan->saranPerbaikan->count() }} Arahan
            </span>
        </h3>

        @forelse($keluhan->saranPerbaikan as $saran)
        <div class="p-4 bg-purple-50/70 border border-purple-200 rounded-2xl space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-md bg-purple-200 text-purple-900 font-extrabold text-[10px] uppercase">
                        {{ $saran->role_author }}
                    </span>
                    <strong class="text-slate-900 text-xs">{{ $saran->author->name ?? 'Pengguna' }}</strong>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $saran->status_badge }}">
                    {{ $saran->status_label }}
                </span>
            </div>

            <div>
                <h4 class="font-extrabold text-xs text-purple-950">{{ $saran->judul_arahan }}</h4>
                <p class="text-slate-700 mt-1 leading-relaxed">{{ $saran->arahan_pembinaan }}</p>
                @if($saran->rekomendasi_tindakan)
                <p class="text-[11px] text-purple-800 mt-1 font-semibold">Rekomendasi Tindakan: {{ $saran->rekomendasi_tindakan }}</p>
                @endif
            </div>

            @if($saran->tanggapan_guru)
            <div class="p-3 bg-white rounded-xl border border-purple-200 text-slate-800">
                <strong class="text-blue-800 font-bold block mb-0.5">Tanggapan & Komitmen Guru:</strong>
                <p class="text-[11px]">{{ $saran->tanggapan_guru }}</p>
            </div>
            @elseif($keluhan->target_guru_user_id === auth()->id())
            <!-- Form Response by Teacher -->
            <form method="POST" action="{{ route('guru.keluhan.saran.tanggapi', $saran) }}" class="space-y-2 pt-2 border-t border-purple-200/60">
                @csrf
                @method('PATCH')
                <label class="block font-bold text-slate-700">Tanggapi Arahan Pembinaan Ini:</label>
                <textarea name="tanggapan_guru" rows="2" placeholder="Tuliskan tanggapan dan kesiapan komitmen Anda..." required class="w-full p-2.5 bg-white border border-purple-300 rounded-xl text-xs"></textarea>
                <button type="submit" class="px-4 py-1.5 bg-purple-700 hover:bg-purple-800 text-white font-bold rounded-xl text-xs shadow-xs">
                    Kirim Komitmen Tanggapan
                </button>
            </form>
            @endif
        </div>
        @empty
        <p class="text-slate-400 text-xs py-4 text-center">Belum ada arahan pembinaan yang diberikan untuk keluhan ini.</p>
        @endforelse

        <!-- Form Tambah Saran Perbaikan oleh Pimpinan / Kaprog / Walikelas -->
        @if($canGiveAdvice)
        <div class="mt-6 pt-5 border-t border-slate-200 space-y-3">
            <h4 class="font-extrabold text-xs text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                <i class="bi-plus-circle text-purple-600"></i>
                <span>Berikan Saran Perbaikan / Arahan Pembinaan Untuk Guru</span>
            </h4>

            <form method="POST" action="{{ route('guru.keluhan.saran.store', $keluhan) }}" class="space-y-3">
                @csrf

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Judul Arahan</label>
                    <input type="text" name="judul_arahan" placeholder="Contoh: Pembinaan Diferensiasi Ritme Pembelajaran..." required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Uraian Arahan Pembinaan</label>
                    <textarea name="arahan_pembinaan" rows="3" placeholder="Tuliskan arahan dan masukan konstruktif untuk guru yang bersangkutan..." required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Rekomendasi Tindakan Konkret (Opsional)</label>
                    <input type="text" name="rekomendasi_tindakan" placeholder="Contoh: Bagikan ringkasan materi di awal sesi..." class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl">
                </div>

                <button type="submit" class="px-5 py-2.5 bg-purple-700 hover:bg-purple-800 text-white font-bold rounded-xl shadow-md shadow-purple-700/20 transition">
                    + Kirimkan Arahan Pembinaan
                </button>
            </form>
        </div>
        @endif

    </div>

</div>
@endsection
