@extends('layouts.app')
@section('title', 'Detail Tenaga Kependidikan')
@section('subtitle', $tendik->nama_lengkap)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.tendik.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Tendik
        </a>
    </div>

    {{-- Profile Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 flex flex-col sm:flex-row items-center sm:items-start gap-6 border-b border-gray-100 bg-gradient-to-r from-purple-50/50 via-white to-white">
            <div class="w-20 h-20 rounded-2xl bg-purple-600 text-white flex items-center justify-center font-bold text-2xl shadow-md shrink-0">
                {{ strtoupper(substr($tendik->nama_lengkap, 0, 2)) }}
            </div>
            <div class="flex-1 text-center sm:text-left">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                    <h2 class="text-xl font-bold text-gray-900">{{ $tendik->nama_lengkap }}</h2>
                    @if($tendik->is_active)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Aktif</span>
                    @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Nonaktif</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-1">Tenaga Kependidikan &bull; Synchronized from db_hilaledu</p>

                <div class="flex flex-wrap gap-1.5 mt-3 justify-center sm:justify-start">
                    @if($tendik->user && $tendik->user->roles->count() > 0)
                        @foreach($tendik->user->roles as $role)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $role->name == 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-700' }}">
                            {{ ucwords(str_replace('_', ' ', $role->name)) }}
                        </span>
                        @endforeach
                    @else
                        <span class="text-xs text-gray-400 italic">Staf Tendik</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Identitas Pegawai</h4>
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <dt class="text-gray-500">NIP</dt>
                        <dd class="font-medium text-gray-800 font-mono">{{ $tendik->nip ?: '-' }}</dd>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <dt class="text-gray-500">Bagian / Unit</dt>
                        <dd class="font-medium text-gray-800">{{ $tendik->bagian ?: 'Tata Usaha' }}</dd>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <dt class="text-gray-500">Jabatan</dt>
                        <dd class="font-medium text-gray-800">{{ $tendik->jabatan ?: $tendik->status_kepegawaian }}</dd>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <dt class="text-gray-500">Jenis Kelamin</dt>
                        <dd class="font-medium text-gray-800">{{ $tendik->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
                    </div>
                </dl>
            </div>

            <div>
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Kontak & Alamat</h4>
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <dt class="text-gray-500">Email Login</dt>
                        <dd class="font-medium text-gray-800">{{ $tendik->email ?: ($tendik->user->email ?? '-') }}</dd>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <dt class="text-gray-500">No. HP / WhatsApp</dt>
                        <dd class="font-medium text-gray-800">{{ $tendik->no_hp ?: '-' }}</dd>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <dt class="text-gray-500">Alamat</dt>
                        <dd class="font-medium text-gray-800 text-right">{{ $tendik->alamat ?: '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
