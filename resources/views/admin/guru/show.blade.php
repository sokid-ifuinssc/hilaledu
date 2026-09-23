@extends('layouts.app')
@section('title', 'Detail Guru')
@section('subtitle', $guru->nama_lengkap)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.guru.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Guru
        </a>
    </div>

    {{-- Profile Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 flex flex-col sm:flex-row items-center sm:items-start gap-6 border-b border-gray-100 bg-gradient-to-r from-indigo-50/50 via-white to-white">
            <div class="w-20 h-20 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-2xl shadow-md shrink-0">
                {{ strtoupper(substr($guru->nama_lengkap, 0, 2)) }}
            </div>
            <div class="flex-1 text-center sm:text-left">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                    <h2 class="text-xl font-bold text-gray-900">{{ $guru->nama_lengkap }}</h2>
                    @if($guru->is_active)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Aktif</span>
                    @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Nonaktif</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-1">Data Pendidik &bull; Synchronized from db_hilaledu</p>

                <div class="flex flex-wrap gap-1.5 mt-3 justify-center sm:justify-start">
                    @if($guru->user && $guru->user->roles->count() > 0)
                        @foreach($guru->user->roles as $role)
                            @if($role->name === 'wali_kelas')
                                @php $kelasWali = $guru->user->kelasAsWali->pluck('nama_kelas')->implode(', '); @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    Wali Kelas {{ $kelasWali ? "($kelasWali)" : '' }}
                                </span>
                            @elseif($role->name === 'kaprog')
                                @php $kaprogJur = $guru->user->jurusanAsKaprog->pluck('singkatan')->implode(', '); @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    Kaprog {{ $kaprogJur ? "($kaprogJur)" : '' }}
                                </span>
                            @elseif($role->name === 'waka_kesiswaan')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">
                                    Waka Kesiswaan
                                </span>
                            @elseif($role->name === 'waka_hubin')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                    Waka Hubin
                                </span>
                            @elseif($role->name === 'guru_bk')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                    Guru BK
                                </span>
                            @elseif($role->name === 'kepala_sekolah')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                    Kepala Sekolah
                                </span>
                            @elseif($role->name === 'admin')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                    Admin App
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                </span>
                            @endif
                        @endforeach
                    @else
                        <span class="text-xs text-gray-400 italic">Guru Pengajar</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Identitas Pegawai</h4>
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <dt class="text-gray-500">NUPTK</dt>
                        <dd class="font-medium text-gray-800 font-mono">{{ $guru->nuptk_display }}</dd>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <dt class="text-gray-500">Jenis Kelamin</dt>
                        <dd class="font-medium text-gray-800">{{ $guru->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
                    </div>
                </dl>
            </div>

            <div>
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Kontak & Alamat</h4>
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <dt class="text-gray-500">Email Login</dt>
                        <dd class="font-medium text-gray-800">{{ $guru->email ?: ($guru->user->email ?? '-') }}</dd>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <dt class="text-gray-500">No. HP / WhatsApp</dt>
                        <dd class="font-medium text-gray-800">{{ $guru->no_hp ?: '-' }}</dd>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <dt class="text-gray-500">Alamat</dt>
                        <dd class="font-medium text-gray-800 text-right">{{ $guru->alamat ?: '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
