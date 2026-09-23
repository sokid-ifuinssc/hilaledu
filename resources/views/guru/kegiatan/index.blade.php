@extends('layouts.app')

@section('title', 'Pemberitahuan Kegiatan Sekolah')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-megaphone text-amber-500"></i>
                <span>Agenda & Pemberitahuan Kegiatan Sekolah</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Pengumuman resmi dari Waka Kurikulum, Waka Sarpras, Waka Kesiswaan, Waka Hubin, dan Pembina OSIS</p>
        </div>
        @if($canCreate)
        <div>
            <a href="{{ route('guru.kegiatan.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-blue-600/20 transition">
                <i class="bi-plus-circle text-sm"></i>
                <span>Posting Agenda / Kegiatan Baru</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Filter by Bidang -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 text-xs font-semibold">
        <a href="{{ route('guru.kegiatan.index') }}" 
           class="px-4 py-2 rounded-xl transition flex-shrink-0 {{ empty($bidangFilter) ? 'bg-slate-900 text-white shadow-xs' : 'bg-white border text-slate-600 hover:bg-slate-100' }}">
            Semua Bidang
        </a>
        <a href="{{ route('guru.kegiatan.index', ['bidang' => 'waka_kurikulum']) }}" 
           class="px-4 py-2 rounded-xl transition flex-shrink-0 {{ $bidangFilter === 'waka_kurikulum' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white border text-slate-600 hover:bg-slate-100' }}">
            Waka Kurikulum
        </a>
        <a href="{{ route('guru.kegiatan.index', ['bidang' => 'waka_sarpras']) }}" 
           class="px-4 py-2 rounded-xl transition flex-shrink-0 {{ $bidangFilter === 'waka_sarpras' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white border text-slate-600 hover:bg-slate-100' }}">
            Waka Sarpras
        </a>
        <a href="{{ route('guru.kegiatan.index', ['bidang' => 'waka_kesiswaan']) }}" 
           class="px-4 py-2 rounded-xl transition flex-shrink-0 {{ $bidangFilter === 'waka_kesiswaan' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white border text-slate-600 hover:bg-slate-100' }}">
            Waka Kesiswaan
        </a>
        <a href="{{ route('guru.kegiatan.index', ['bidang' => 'waka_hubin']) }}" 
           class="px-4 py-2 rounded-xl transition flex-shrink-0 {{ $bidangFilter === 'waka_hubin' ? 'bg-cyan-600 text-white shadow-xs' : 'bg-white border text-slate-600 hover:bg-slate-100' }}">
            Waka Hubin (Humas/Industri)
        </a>
        <a href="{{ route('guru.kegiatan.index', ['bidang' => 'pembina_osis']) }}" 
           class="px-4 py-2 rounded-xl transition flex-shrink-0 {{ $bidangFilter === 'pembina_osis' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white border text-slate-600 hover:bg-slate-100' }}">
            Pembina OSIS
        </a>
    </div>

    <!-- Events List -->
    <div class="space-y-4">
        @forelse($kegiatans as $k)
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-xs hover:shadow-md transition flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase {{ $k->bidang_color }}">
                        {{ $k->bidang_label }}
                    </span>

                    @if($k->kalender_akademik_event_id)
                    <a href="{{ route('guru.kalender.index') }}" title="Lihat di Kalender Pendidikan Sekolah" 
                       class="px-2.5 py-1 rounded-lg text-[10px] font-black bg-emerald-50 text-emerald-800 border border-emerald-300 hover:bg-emerald-100 flex items-center gap-1 transition">
                        <i class="bi-calendar2-check-fill text-emerald-600"></i>
                        <span>Tersinkron Kalender</span>
                    </a>
                    @endif

                    <span class="text-xs text-slate-500 font-semibold flex items-center gap-1">
                        <i class="bi-calendar-event text-amber-600"></i>
                        {{ \Carbon\Carbon::parse($k->tanggal_kegiatan)->isoFormat('dddd, D MMMM Y') }}
                        @if($k->tanggal_selesai && $k->tanggal_selesai->gt($k->tanggal_kegiatan))
                        <span class="text-slate-400">s/d</span> {{ \Carbon\Carbon::parse($k->tanggal_selesai)->isoFormat('D MMMM Y') }}
                        @endif
                    </span>
                    @if($k->waktu_mulai)
                    <span class="text-xs text-slate-400">&bull; {{ substr($k->waktu_mulai,0,5) }} - {{ substr($k->waktu_selesai,0,5) }} WIB</span>
                    @endif
                    <span class="text-xs text-slate-400">&bull;</span>
                    <span class="text-xs text-slate-600 font-medium"><i class="bi-geo-alt"></i> {{ $k->tempat }}</span>
                </div>

                <h3 class="text-lg font-black text-slate-900 leading-snug">
                    {{ $k->judul }}
                </h3>

                <p class="text-xs text-slate-600 leading-relaxed line-clamp-2">
                    {{ $k->deskripsi }}
                </p>

                <div class="text-[11px] text-slate-400 flex items-center gap-3 pt-1 flex-wrap">
                    <span>Sasaran: <strong class="text-slate-700">{{ $k->sasaran }}</strong></span>
                    <span>&bull;</span>
                    <span>Diposting oleh: <strong class="text-slate-700">{{ $k->author->name ?? 'Admin' }}</strong></span>
                    @if($k->editor)
                    <span>&bull;</span>
                    <span class="text-amber-700 font-semibold bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                        <i class="bi-pencil"></i> Diedit oleh: <strong>{{ $k->editor->name }}</strong>
                    </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0 self-start md:self-center">
                <a href="{{ route('guru.kegiatan.show', $k) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-xs transition flex items-center gap-1.5">
                    <span>Detail & Presensi</span>
                    <i class="bi-chevron-right text-[10px]"></i>
                </a>

                @if($k->canBeEditedBy(auth()->user()))
                <a href="{{ route('guru.kegiatan.edit', $k) }}" class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-xl text-xs transition flex items-center gap-1.5 border border-blue-200" title="Edit Agenda Kegiatan">
                    <i class="bi-pencil-square text-sm"></i>
                    <span>Edit</span>
                </a>
                @endif

                @if($k->canBeEditedBy(auth()->user()))
                <form action="{{ route('guru.kegiatan.destroy', $k) }}" method="POST" onsubmit="return confirm('Hapus kegiatan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-xl hover:bg-slate-100 transition" title="Hapus Agenda">
                        <i class="bi-trash text-base"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-3xl p-12 text-center text-slate-400 border border-slate-200">
            <i class="bi-bell-slash text-4xl mb-3 inline-block"></i>
            <p class="text-sm font-semibold text-slate-600">Tidak ada pengumuman kegiatan sekolah pada kategori ini.</p>
        </div>
        @endforelse
    </div>

    <div class="pt-2">
        {{ $kegiatans->links() }}
    </div>

</div>
@endsection
