@extends('layouts.app')

@section('title', $config['title'] . ' Saya')
@section('page-title', $config['title'] . ' Saya')

@section('content')
@if($isAksesDibatasi ?? false)
<div class="max-w-2xl mx-auto py-8">
    <div class="bg-white rounded-3xl p-8 sm:p-10 text-center border border-rose-200 shadow-xs space-y-4">
        <div class="w-16 h-16 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto text-3xl">
            <i class="bi-shield-lock-fill"></i>
        </div>
        <div class="space-y-1">
            <span class="inline-block px-3 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200 text-[11px] font-black uppercase tracking-wider">
                Akses Dibatasi Sementara
            </span>
            <h2 class="text-xl font-black text-slate-900 pt-1">
                Halaman Dokumen & Sertifikat Belum Dapat Diakses
            </h2>
        </div>
        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed max-w-lg mx-auto">
            {{ $catatanPembatasan ?? 'Mohon maaf, halaman sertifikat dan dokumen Anda belum dapat ditampilkan karena masih terdapat administrasi sekolah yang belum diselesaikan.' }}
        </p>
        <div class="pt-3 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('siswa.tagihan.index') }}" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs inline-flex items-center gap-2 shadow-sm transition">
                <i class="bi-receipt"></i>
                <span>Cek Tagihan & Administrasi Saya</span>
            </a>
            <a href="{{ route('siswa.dashboard') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs inline-flex items-center gap-2 transition">
                <i class="bi-arrow-left"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>
</div>
@else
<div class="space-y-6" x-data="{
    modalPreview: false,
    previewUrl: '',
    previewTitle: '',
    openPreview(url, title) {
        this.previewUrl = url;
        this.previewTitle = title;
        this.modalPreview = true;
    }
}">

    <!-- Sub-Menu Navigation Bar (4 Kategori Dokumen & Sertifikat Siswa) -->
    <div class="bg-white p-2 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-2">
        <div class="flex flex-wrap items-center gap-1.5">
            <a href="{{ route('siswa.dokumen-sertifikat.index', 'kepesantrenan') }}"
               class="px-4 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2 transition {{ $kategori === 'kepesantrenan' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20 font-black' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="bi-moon-stars-fill text-sm {{ $kategori === 'kepesantrenan' ? 'text-white' : 'text-emerald-600' }}"></i>
                <span>Sertifikat Kepesantrenan</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $kategori === 'kepesantrenan' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $countsByKategori['kepesantrenan'] ?? 0 }}
                </span>
            </a>
            <a href="{{ route('siswa.dokumen-sertifikat.index', 'kewirausahaan') }}"
               class="px-4 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2 transition {{ $kategori === 'kewirausahaan' ? 'bg-amber-600 text-white shadow-sm shadow-amber-600/20 font-black' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="bi-shop-window text-sm {{ $kategori === 'kewirausahaan' ? 'text-white' : 'text-amber-600' }}"></i>
                <span>Sertifikat Kewirausahaan</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $kategori === 'kewirausahaan' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $countsByKategori['kewirausahaan'] ?? 0 }}
                </span>
            </a>
            <a href="{{ route('siswa.dokumen-sertifikat.index', 'skl') }}"
               class="px-4 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2 transition {{ $kategori === 'skl' ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20 font-black' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="bi-file-earmark-check-fill text-sm {{ $kategori === 'skl' ? 'text-white' : 'text-blue-600' }}"></i>
                <span>SKL</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $kategori === 'skl' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $countsByKategori['skl'] ?? 0 }}
                </span>
            </a>
            <a href="{{ route('siswa.dokumen-sertifikat.index', 'ijazah') }}"
               class="px-4 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2 transition {{ $kategori === 'ijazah' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20 font-black' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="bi-mortarboard-fill text-sm {{ $kategori === 'ijazah' ? 'text-white' : 'text-indigo-600' }}"></i>
                <span>Ijazah</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $kategori === 'ijazah' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ $countsByKategori['ijazah'] ?? 0 }}
                </span>
            </a>
        </div>
    </div>

    <!-- Header Informasi Siswa -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl flex-shrink-0">
                <i class="{{ $config['icon'] }}"></i>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900">{{ $config['title'] }} Saya</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Arsip resmi dokumen dan sertifikat digital milik <strong>{{ $user->name }}</strong> ({{ $user->kelas->nama_kelas ?? 'Siswa' }} · NISN: {{ $user->nisn ?: $user->username }}).
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-2 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-black flex items-center gap-1.5">
                <i class="bi-file-earmark-pdf-fill text-rose-600"></i>
                <span>{{ $dokumens->count() }} Dokumen Tersedia</span>
            </span>
        </div>
    </div>

    <!-- Daftar Dokumen / Sertifikat Milik Siswa -->
    @if($dokumens->isEmpty())
    <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 shadow-xs">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-3xl">
            <i class="bi-file-earmark-pdf"></i>
        </div>
        <h3 class="font-black text-slate-800 text-base">Belum Ada {{ $config['title'] }}</h3>
        <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto leading-relaxed">
            Saat ini belum ada file PDF <strong>{{ $config['title'] }}</strong> yang diunggah untuk akun Anda oleh pihak sekolah. Silakan periksa kembali secara berkala atau hubungi bagian administrasi sekolah.
        </p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($dokumens as $doc)
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-xs hover:shadow-md transition flex flex-col justify-between space-y-4">
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 text-[10px] font-black uppercase tracking-wider">
                        {{ $config['short'] }}
                    </span>
                    <span class="text-[11px] text-slate-400 font-semibold flex items-center gap-1">
                        <i class="bi-calendar3"></i>
                        {{ $doc->created_at->format('d M Y') }}
                    </span>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center text-2xl flex-shrink-0">
                        <i class="bi-file-earmark-pdf-fill"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-black text-slate-900 text-base leading-snug">
                            {{ $doc->nama_dokumen }}
                        </h3>
                        @if($doc->nomor_dokumen)
                        <p class="text-xs text-indigo-700 font-bold mt-0.5">
                            No: {{ $doc->nomor_dokumen }}
                        </p>
                        @endif
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Format PDF · {{ $doc->file_size_formatted }}
                        </p>
                    </div>
                </div>

                @if($doc->keterangan)
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs text-slate-600">
                    <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Catatan / Keterangan:</span>
                    {{ $doc->keterangan }}
                </div>
                @endif
            </div>

            <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2.5">
                <button type="button"
                        @click="openPreview('{{ route('dokumen-sertifikat.preview', $doc) }}', '{{ addslashes($doc->nama_dokumen) }}')"
                        class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition cursor-pointer">
                    <i class="bi-eye-fill text-indigo-600"></i>
                    <span>Lihat PDF</span>
                </button>
                <a href="{{ route('dokumen-sertifikat.download', $doc) }}"
                   class="py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-sm shadow-emerald-600/20 transition">
                    <i class="bi-download"></i>
                    <span>Unduh PDF</span>
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Jika SKL atau Ijazah (1 File), tampilkan juga preview langsung di halaman agar praktis -->
    @if(!$config['is_multiple'] && $dokumens->first())
    @php $singleDoc = $dokumens->first(); @endphp
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="bi-file-earmark-pdf-fill text-rose-400"></i>
                <span class="text-xs font-bold">Pratinjau Langsung: {{ $singleDoc->nama_dokumen }}</span>
            </div>
            <a href="{{ route('dokumen-sertifikat.download', $singleDoc) }}" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-1.5 transition">
                <i class="bi-download"></i>
                <span>Download PDF</span>
            </a>
        </div>
        <div class="h-[72vh] bg-slate-100">
            <iframe src="{{ route('dokumen-sertifikat.preview', $singleDoc) }}" class="w-full h-full border-0"></iframe>
        </div>
    </div>
    @endif
    @endif

    <!-- Modal Preview PDF -->
    <div x-show="modalPreview"
         x-cloak
         x-transition.opacity.duration.200ms
         class="fixed inset-0 z-50 bg-black/75 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6"
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
                        <span>Buka Tab Baru</span>
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

</div>
@endif
@endsection

