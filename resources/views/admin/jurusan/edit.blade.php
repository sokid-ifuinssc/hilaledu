@extends('layouts.app')
@section('title', 'Edit Program Keahlian')
@section('content')
<div class="max-w-lg"><div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
<form method="POST" action="{{ route('admin.jurusan.update', $jurusan) }}">@csrf @method('PUT')
<div class="space-y-4">
    <div><label class="form-label">Kode *</label><input type="text" name="kode" value="{{ old('kode', $jurusan->kode) }}" class="form-input" required></div>
    <div><label class="form-label">Nama *</label><input type="text" name="nama" value="{{ old('nama', $jurusan->nama) }}" class="form-input" required></div>
    <div><label class="form-label">Kaprog</label><select name="kaprog_id" class="form-select"><option value="">Pilih Kaprog</option>@foreach($kaprogs as $k)<option value="{{ $k->id }}" {{ old('kaprog_id', $jurusan->kaprog_id) == $k->id ? 'selected' : '' }}>{{ $k->nama_lengkap }}</option>@endforeach</select></div>
</div>
<div class="flex gap-3 mt-6"><button type="submit" class="btn-primary">Simpan</button><a href="{{ route('admin.jurusan.index') }}" class="btn-secondary">Batal</a></div>
</form></div></div>
@endsection
