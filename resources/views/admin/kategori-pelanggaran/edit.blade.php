@extends('layouts.app')
@section('title', 'Edit Kategori Pelanggaran')
@section('content')
<div class="max-w-lg"><div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
<form method="POST" action="{{ route('admin.kategori-pelanggaran.update', $kategoriPelanggaran) }}">@csrf @method('PUT')
<div class="space-y-4">
    <div><label class="form-label">Nama *</label><input type="text" name="nama" value="{{ old('nama', $kategoriPelanggaran->nama) }}" class="form-input" required></div>
    <div><label class="form-label">Bobot Poin *</label><input type="number" name="bobot_poin" value="{{ old('bobot_poin', $kategoriPelanggaran->bobot_poin) }}" class="form-input" min="1" required></div>
    <div><label class="form-label">Warna</label><input type="color" name="warna" value="{{ old('warna', $kategoriPelanggaran->warna) }}" class="h-10 w-20 rounded cursor-pointer"></div>
    <div><label class="form-label">Deskripsi</label><textarea name="deskripsi" rows="2" class="form-textarea">{{ old('deskripsi', $kategoriPelanggaran->deskripsi) }}</textarea></div>
</div>
<div class="flex gap-3 mt-6"><button type="submit" class="btn-primary">Simpan</button><a href="{{ route('admin.kategori-pelanggaran.index') }}" class="btn-secondary">Batal</a></div>
</form></div></div>
@endsection
