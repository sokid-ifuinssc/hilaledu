@extends('layouts.app')
@section('title', 'Tambah Jenis Pelanggaran')
@section('content')
<div class="max-w-lg"><div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
<form method="POST" action="{{ route('admin.jenis-pelanggaran.store') }}">@csrf
<div class="space-y-4">
    <div><label class="form-label">Kategori *</label><select name="kategori_pelanggaran_id" class="form-select" required><option value="">Pilih</option>@foreach($kategoris as $k)<option value="{{ $k->id }}" {{ old('kategori_pelanggaran_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>@endforeach</select></div>
    <div><label class="form-label">Kode *</label><input type="text" name="kode" value="{{ old('kode') }}" class="form-input" placeholder="R01" required></div>
    <div><label class="form-label">Nama *</label><input type="text" name="nama" value="{{ old('nama') }}" class="form-input" required></div>
    <div><label class="form-label">Poin *</label><input type="number" name="poin" value="{{ old('poin') }}" class="form-input" min="1" required></div>
    <div><label class="form-label">Deskripsi</label><textarea name="deskripsi" rows="2" class="form-textarea">{{ old('deskripsi') }}</textarea></div>
</div>
<div class="flex gap-3 mt-6"><button type="submit" class="btn-primary">Simpan</button><a href="{{ route('admin.jenis-pelanggaran.index') }}" class="btn-secondary">Batal</a></div>
</form></div></div>
@endsection
