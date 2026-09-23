@extends('layouts.app')
@section('title', isset($tahunAjaran) ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran')
@section('content')
<div class="max-w-lg"><div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
<form method="POST" action="{{ isset($tahunAjaran) ? route('admin.tahun-ajaran.update', $tahunAjaran) : route('admin.tahun-ajaran.store') }}">@csrf @if(isset($tahunAjaran)) @method('PUT') @endif
<div class="space-y-4">
    <div><label class="form-label">Nama *</label><input type="text" name="nama" value="{{ old('nama', $tahunAjaran->nama ?? '') }}" class="form-input" placeholder="2025/2026" required>@error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror</div>
    <div><label class="form-label">Semester *</label><select name="semester" class="form-select" required><option value="ganjil" {{ old('semester', $tahunAjaran->semester ?? '') == 'ganjil' ? 'selected' : '' }}>Ganjil</option><option value="genap" {{ old('semester', $tahunAjaran->semester ?? '') == 'genap' ? 'selected' : '' }}>Genap</option></select></div>
    <div><label class="form-label">Tanggal Mulai *</label><input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', isset($tahunAjaran) ? ($tahunAjaran->tanggal_mulai?->format('Y-m-d') ?? '') : '') }}" class="form-input" required></div>
    <div><label class="form-label">Tanggal Selesai *</label><input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', isset($tahunAjaran) ? ($tahunAjaran->tanggal_selesai?->format('Y-m-d') ?? '') : '') }}" class="form-input" required></div>
    <div class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $tahunAjaran->is_active ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600"><label for="is_active" class="text-sm text-gray-700">Jadikan Aktif</label></div>
</div>
<div class="flex gap-3 mt-6"><button type="submit" class="btn-primary">Simpan</button><a href="{{ route('admin.tahun-ajaran.index') }}" class="btn-secondary">Batal</a></div>
</form></div></div>
@endsection
