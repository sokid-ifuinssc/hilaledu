@extends('layouts.app')
@section('title', 'Edit Siswa')
@section('subtitle', $siswa->name ?? $siswa->nama_lengkap ?? 'Siswa')

@section('content')
@php
    $listKelas = $kelasList ?? $kelas ?? [];
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
                <i class="bi bi-person-gear text-lg"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-800">Edit Data Identitas & Akun Siswa</h3>
                <p class="text-xs text-slate-500">Perbarui rombel kelas, NIS/NISN, data pribadi, serta pengaturan status siswa.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('superadmin.siswa.update', $siswa->id ?? $siswa) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Nama Lengkap --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ old('name', $siswa->name ?? $siswa->nama_lengkap) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs" required>
                    @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Username --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Username Login *</label>
                    <input type="text" name="username" value="{{ old('username', $siswa->username) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs" required>
                    @error('username')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $siswa->email) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs" required>
                    @error('email')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- NIS / NIP --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">NIS / NISN</label>
                    <input type="text" name="nip" value="{{ old('nip', $siswa->nip ?? $siswa->nisn ?? $siswa->nis) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs" placeholder="Nomor Induk Siswa">
                    @error('nip')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Rombel / Kelas --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Rombel / Kelas</label>
                    <select name="kelas_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs">
                        <option value="">-- Belum Ditentukan / Bebas Rombel --</option>
                        @foreach($listKelas as $k)
                            <option value="{{ $k->id }}" {{ (old('kelas_id', $siswa->kelas_id) == $k->id) ? 'selected' : '' }}>
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
                        <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                {{-- No HP --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">No. HP / WhatsApp Siswa</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp', $siswa->no_hp) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs">
                </div>

                {{-- Password Baru (Opsional) --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Password Baru (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs" placeholder="Minimal 6 karakter">
                    @error('password')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Ulangi Password Baru</label>
                    <input type="password" name="password_confirmation" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-xs">
                </div>

                {{-- Status Akun Aktif --}}
                <div class="md:col-span-2 pt-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $siswa->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500">
                        <span class="text-sm font-semibold text-slate-700">Akun Aktif (Bisa Login ke HilalEdu Portal)</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-6 border-t border-slate-100">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl transition shadow-sm inline-flex items-center gap-2">
                    <i class="bi bi-check2-circle"></i>
                    Simpan Perubahan
                </button>
                <a href="{{ route('superadmin.siswa.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
