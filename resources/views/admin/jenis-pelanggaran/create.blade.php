@extends('layouts.app')

@section('title', 'Tambah Data Pelanggaran - HilalEdu')
@section('page-title', 'Tambah Jenis Pelanggaran')

@section('content')
<div class="max-w-2xl space-y-5">
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <div class="mb-5 pb-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-800">Tambah Data Pelanggaran</h3>
            <p class="text-sm text-slate-500 mt-0.5">Tambahkan kategori nama pelanggaran baru beserta bobot pengurangan poinnya.</p>
        </div>

        <form method="POST" action="{{ route(request()->routeIs('admin.*') ? 'admin.jenis-pelanggaran.store' : 'bk.jenis-pelanggaran.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="form-label">Nama Pelanggaran *</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" class="form-input" placeholder="Contoh: Terlambat masuk sekolah" required>
                    @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Poin *</label>
                    <input type="number" name="poin" value="{{ old('poin') }}" class="form-input" placeholder="Contoh: 10" min="1" required>
                    <p class="text-xs text-slate-400 mt-1">Besar poin yang akan dikurangkan dari skor siswa saat melanggar.</p>
                    @error('poin')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Deskripsi <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <textarea name="deskripsi" rows="3" class="form-textarea" placeholder="Penjelasan detail mengenai pelanggaran ini...">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex gap-3 mt-6 pt-4 border-t border-slate-100">
                <button type="submit" class="btn-primary">Simpan</button>
                <a href="{{ route(request()->routeIs('admin.*') ? 'admin.jenis-pelanggaran.index' : 'bk.jenis-pelanggaran.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
