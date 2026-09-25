@extends('layouts.app')
@section('title', 'Edit Data Pelanggaran')
@section('content')
<div class="max-w-lg"><div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
<form method="POST" action="{{ route(request()->routeIs('admin.*') ? 'admin.jenis-pelanggaran.update' : 'bk.jenis-pelanggaran.update', $jenisPelanggaran) }}">@csrf @method('PUT')
<div class="space-y-4">
    <div><label class="form-label">Nama *</label><input type="text" name="nama" value="{{ old('nama', $jenisPelanggaran->nama) }}" class="form-input" required></div>
    <div><label class="form-label">Poin *</label><input type="number" name="poin" value="{{ old('poin', $jenisPelanggaran->poin) }}" class="form-input" min="1" required></div>
    <div><label class="form-label">Deskripsi</label><textarea name="deskripsi" rows="2" class="form-textarea">{{ old('deskripsi', $jenisPelanggaran->deskripsi) }}</textarea></div>
</div>
<div class="flex gap-3 mt-6"><button type="submit" class="btn-primary">Simpan</button><a href="{{ route(request()->routeIs('admin.*') ? 'admin.jenis-pelanggaran.index' : 'bk.jenis-pelanggaran.index') }}" class="btn-secondary">Batal</a></div>
</form></div></div>
@endsection
