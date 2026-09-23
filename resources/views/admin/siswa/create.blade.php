@extends('layouts.app')
@section('title', 'Tambah Siswa Baru')
@section('page-title', 'Tambah Siswa Baru')

@section('content')
@php
    $listKelas = $kelas ?? [];
@endphp

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('superadmin.siswa.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-emerald-600 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Siswa
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <i class="bi bi-person-plus-fill text-lg"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-800">Tambah Data Siswa Baru</h3>
                <p class="text-xs text-slate-500">Isi seluruh informasi identitas dan akun login siswa.</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            <div class="font-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terdapat kesalahan input:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('superadmin.siswa.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Nama Lengkap --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs" required>
                    @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Username --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Username Login *</label>
                    <input type="text" name="username" value="{{ old('username') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs" required placeholder="Huruf, angka, titik, underscore">
                    @error('username')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs" required>
                    @error('email')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- NIS / NISN --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">NIS / NISN</label>
                    <input type="text" name="nip" value="{{ old('nip') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs" placeholder="Nomor Induk Siswa">
                    @error('nip')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Rombel / Kelas --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Rombel / Kelas</label>
                    <select name="kelas_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs">
                        <option value="">-- Belum Ditentukan / Bebas Rombel --</option>
                        @foreach($listKelas as $k)
                            <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_lengkap ?? $k->nama ?? $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                    @error('kelas_id')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Jenis Kelamin --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs">
                        <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                {{-- No HP --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">No. HP / WhatsApp Siswa</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs">
                </div>

                {{-- Nama Ayah --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Ayah</label>
                    <input type="text" name="nama_ayah" value="{{ old('nama_ayah') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs">
                </div>

                {{-- Nama Ibu --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Ibu</label>
                    <input type="text" name="nama_ibu" value="{{ old('nama_ibu') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs">
                </div>

                {{-- No HP Ortu --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">No. HP / WhatsApp Orang Tua</label>
                    <input type="text" name="no_hp_ortu" value="{{ old('no_hp_ortu') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs">
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Password Login *</label>
                    <input type="password" name="password" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs" placeholder="Minimal 6 karakter" required>
                    @error('password')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Ulangi Password *</label>
                    <input type="password" name="password_confirmation" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs">
                </div>

                {{-- Status Akun Aktif --}}
                <div class="md:col-span-2 pt-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500">
                        <span class="text-sm font-semibold text-slate-700">Akun Aktif (Bisa Login ke HilalEdu Portal)</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-6 border-t border-slate-100">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl transition shadow-sm inline-flex items-center gap-2">
                    <i class="bi bi-plus-circle-fill"></i>
                    Simpan Data Siswa
                </button>
                <a href="{{ route('superadmin.siswa.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
