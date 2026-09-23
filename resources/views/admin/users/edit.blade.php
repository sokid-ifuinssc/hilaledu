@extends('layouts.app')
@section('title', 'Edit Akun Pengguna')
@section('subtitle', $user->nama_lengkap)

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" class="form-input" required>
                    @error('nama_lengkap')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Username</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" required>
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Password Baru <span class="text-gray-400 font-normal">(kosongkan jika tidak ingin mengubah)</span></label>
                    <input type="password" name="password" class="form-input" placeholder="Masukkan password baru...">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Role</label>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @foreach($user->roles as $role)
                        <span class="badge bg-indigo-100 text-indigo-700 px-3 py-1">{{ str_replace('_', ' ', ucfirst($role->name)) }}</span>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Role tidak dapat diubah dari halaman ini.</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-sm text-gray-700">Akun Aktif</label>
                </div>
            </div>

            <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Kembali</a>
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
