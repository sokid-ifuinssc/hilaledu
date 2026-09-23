@extends('layouts.app')

@section('title', 'Suara Siswa & Saran KBM')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-chat-square-quote text-indigo-600"></i>
                <span>Suara Siswa & Kotak Evaluasi KBM</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Masukan anonim dari siswa, koordinasi wali kelas, dan arahan pembinaan dari Kepala Sekolah, Waka, serta Kaprog</p>
        </div>
    </div>

    <!-- Anonymity Notice Banner -->
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-xs text-emerald-950">
        <i class="bi-shield-check text-2xl text-emerald-600 flex-shrink-0"></i>
        <div>
            <strong>Jaminan Kerahasiaan Identitas Siswa:</strong>
            <p class="text-emerald-800 text-[11px] mt-0.5">Sesuai kebijakan sekolah, seluruh identitas siswa pengisi masukan dirahasiakan (anonim). Hanya informasi kelas dan isi keluhan yang disajikan untuk menjaga objektivitas evaluasi KBM.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 text-xs font-semibold">
        <a href="{{ route('guru.keluhan.index') }}" class="px-4 py-2 rounded-xl transition flex-shrink-0 {{ empty($statusFilter) ? 'bg-slate-900 text-white' : 'bg-white border text-slate-600 hover:bg-slate-100' }}">
            Semua Status
        </a>
        <a href="{{ route('guru.keluhan.index', ['status' => 'baru']) }}" class="px-4 py-2 rounded-xl transition flex-shrink-0 {{ $statusFilter === 'baru' ? 'bg-amber-600 text-white' : 'bg-white border text-slate-600 hover:bg-slate-100' }}">
            Keluhan Baru
        </a>
        <a href="{{ route('guru.keluhan.index', ['status' => 'diproses']) }}" class="px-4 py-2 rounded-xl transition flex-shrink-0 {{ $statusFilter === 'diproses' ? 'bg-blue-600 text-white' : 'bg-white border text-slate-600 hover:bg-slate-100' }}">
            Sedang Ditindaklanjuti
        </a>
        <a href="{{ route('guru.keluhan.index', ['status' => 'selesai']) }}" class="px-4 py-2 rounded-xl transition flex-shrink-0 {{ $statusFilter === 'selesai' ? 'bg-emerald-600 text-white' : 'bg-white border text-slate-600 hover:bg-slate-100' }}">
            Selesai
        </a>
    </div>

    <!-- Complaints Cards / Table -->
    <div class="space-y-4">
        @forelse($keluhans as $k)
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-xs hover:shadow-md transition flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div class="space-y-2 flex-1">
                <div class="flex items-center gap-2 flex-wrap text-xs">
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase {{ $k->status_badge }}">
                        {{ $k->status_label }}
                    </span>
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->canManageAcademic())
                        <span class="px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-800 font-extrabold border border-emerald-300 text-xs inline-flex items-center gap-1.5" title="Identitas Terbuka untuk Pimpinan/Admin">
                            <i class="bi-person-check-fill text-emerald-600"></i>
                            <span>{{ $k->siswa->name ?? 'Siswa #'.$k->siswa_user_id }} (NIS: {{ $k->siswa->username ?? '-' }}) &bull; Kelas: {{ $k->kelas }}</span>
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 font-bold border border-indigo-200 text-xs inline-flex items-center gap-1.5" title="Kerahasiaan Pengirim Dijamin Sistem">
                            <i class="bi-shield-lock-fill text-indigo-500"></i>
                            <span>{{ $k->pengirim_anonim }}</span>
                        </span>
                    @endif
                    <span class="text-slate-400">&bull;</span>
                    <span class="text-slate-500 font-semibold">
                        <i class="bi-calendar-event"></i> Tanggal KBM: {{ \Carbon\Carbon::parse($k->tanggal_kbm)->format('d M Y') }}
                    </span>
                    <span class="text-slate-400">&bull;</span>
                    <span class="text-amber-700 font-semibold bg-amber-50 px-2 py-0.5 rounded">
                        Kategori: {{ $k->kategori_masalah }}
                    </span>
                </div>

                <div class="text-xs font-bold text-slate-700">
                    Guru Yang Dituju: <span class="text-blue-700 font-extrabold">{{ $k->targetGuru->name ?? '-' }}</span>
                    @if($k->mataPelajaran) &bull; Mapel: {{ $k->mataPelajaran->nama ?? '-' }} @endif
                </div>

                <p class="text-xs text-slate-800 leading-relaxed font-medium bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                    "{{ $k->isi_keluhan }}"
                </p>

                @if($k->harapan_siswa)
                <div class="text-[11px] text-slate-600">
                    <span class="font-bold text-emerald-700">Harapan Siswa:</span> {{ $k->harapan_siswa }}
                </div>
                @endif

                @if($k->saranPerbaikan->isNotEmpty())
                <div class="pt-2 flex items-center gap-2 text-[11px] text-purple-700 font-bold">
                    <i class="bi-chat-left-dots-fill"></i>
                    <span>Terdapat {{ $k->saranPerbaikan->count() }} arahan pembinaan dari pimpinan / Kaprog / Walikelas</span>
                </div>
                @endif
            </div>

            <div class="flex-shrink-0 self-start md:self-center">
                <a href="{{ route('guru.keluhan.show', $k) }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs transition inline-flex items-center gap-1.5 shadow-sm shadow-blue-600/20">
                    <span>Lihat & Tindak Lanjuti</span>
                    <i class="bi-chevron-right text-[10px]"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-3xl p-12 text-center text-slate-400 border border-slate-200">
            <i class="bi-chat-square-check text-4xl mb-3 inline-block text-emerald-400"></i>
            <p class="text-sm font-semibold text-slate-600">Tidak ada masukan atau keluhan KBM pada kategori ini.</p>
        </div>
        @endforelse
    </div>

    <div class="pt-2">
        {{ $keluhans->links() }}
    </div>

</div>
@endsection
