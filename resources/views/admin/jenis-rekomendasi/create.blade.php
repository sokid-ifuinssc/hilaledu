@extends('layouts.app')
@section('title', 'Tambah Jenis Rekomendasi')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.jenis-rekomendasi.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="form-label">Nama Jenis Rekomendasi *</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" class="form-input" required placeholder="Contoh: Peringatan Lisan">
                    @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Kode *</label>
                    <input type="text" name="kode" value="{{ old('kode') }}" class="form-input" required placeholder="Contoh: peringatan_lisan (huruf kecil, underscore)" pattern="[a-z_]+">
                    <p class="text-xs text-gray-400 mt-1">Gunakan huruf kecil dan underscore saja. Contoh: peringatan_lisan, panggilan_orang_tua</p>
                    @error('kode')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Untuk *</label>
                    <select name="untuk" class="form-select" required>
                        <option value="semua" {{ old('untuk') == 'semua' ? 'selected' : '' }}>Semua (BK & Kaprog)</option>
                        <option value="bk" {{ old('untuk') == 'bk' ? 'selected' : '' }}>Guru BK saja</option>
                        <option value="kaprog" {{ old('untuk') == 'kaprog' ? 'selected' : '' }}>Kaprog saja</option>
                    </select>
                    @error('untuk')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" class="form-textarea" placeholder="Deskripsi singkat tentang jenis rekomendasi ini...">{{ old('deskripsi') }}</textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-sm text-gray-700">Aktif</label>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary">Simpan</button>
                <a href="{{ route('admin.jenis-rekomendasi.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
