@extends('layouts.app')
@section('title', 'Tambah Kelas')
@section('content')
<div class="max-w-lg"><div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
<form method="POST" action="{{ route('admin.kelas.store') }}">@csrf
<div class="space-y-4">
    <div><label class="form-label">Nama Kelas *</label><input type="text" name="nama" value="{{ old('nama') }}" class="form-input" placeholder="X TKJT" required></div>
    <div><label class="form-label">Tingkat *</label><select name="tingkat" class="form-select" required><option value="">Pilih</option><option value="X" {{ old('tingkat') == 'X' ? 'selected' : '' }}>X</option><option value="XI" {{ old('tingkat') == 'XI' ? 'selected' : '' }}>XI</option><option value="XII" {{ old('tingkat') == 'XII' ? 'selected' : '' }}>XII</option></select></div>
    <div><label class="form-label">Jurusan *</label><select name="jurusan_id" class="form-select" required><option value="">Pilih</option>@foreach($jurusans as $j)<option value="{{ $j->id }}" {{ old('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->kode }} - {{ $j->nama }}</option>@endforeach</select></div>
    <div><label class="form-label">Tahun Ajaran *</label><select name="tahun_ajaran_id" class="form-select" required>@foreach($tahunAjarans as $t)<option value="{{ $t->id }}" {{ old('tahun_ajaran_id') == $t->id ? 'selected' : '' }}>{{ $t->nama }} ({{ ucfirst($t->semester) }}) {{ $t->is_active ? '✓' : '' }}</option>@endforeach</select></div>
    <div><label class="form-label">Wali Kelas</label><select name="wali_kelas_id" class="form-select"><option value="">Pilih</option>@foreach($waliKelas as $w)<option value="{{ $w->id }}" {{ old('wali_kelas_id') == $w->id ? 'selected' : '' }}>{{ $w->nama_lengkap }}</option>@endforeach</select></div>
</div>
<div class="flex gap-3 mt-6"><button type="submit" class="btn-primary">Simpan</button><a href="{{ route('admin.kelas.index') }}" class="btn-secondary">Batal</a></div>
</form></div></div>
@endsection
