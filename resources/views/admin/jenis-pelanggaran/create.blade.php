@extends('layouts.app')
@section('title', 'Tambah Data Pelanggaran - HilalEdu')

@section('content')
<div class="content-area space-y-6">
    <x-dashboard-hero 
        icon="bi-tags" 
        badge="Kedisiplinan"
        title="Tambah Data Pelanggaran"
        description="Tambahkan pelanggaran baru beserta poinnya." />

    <div class="card shadow-sm border-0" style="border-radius: 16px; background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px);">
        <div class="card-body p-5">
            <form method="POST" action="{{ route(request()->routeIs('admin.*') ? 'admin.jenis-pelanggaran.store' : 'bk.jenis-pelanggaran.store') }}" class="max-w-2xl">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="form-label text-white">Nama Pelanggaran *</label>
                        <input type="text" name="nama" value="{{ old('nama') }}" class="form-control bg-dark text-white border-secondary" placeholder="Contoh: Terlambat masuk sekolah" required>
                        @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label text-white">Poin *</label>
                        <input type="number" name="poin" value="{{ old('poin') }}" class="form-control bg-dark text-white border-secondary" placeholder="Contoh: 10" min="1" required>
                        <small class="text-muted">Besar poin yang akan dikurangkan kepada siswa saat melanggar.</small>
                        @error('poin')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="form-label text-white">Deskripsi <span class="text-muted">(Opsional)</span></label>
                        <textarea name="deskripsi" rows="3" class="form-control bg-dark text-white border-secondary" placeholder="Penjelasan detail mengenai pelanggaran ini...">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="d-flex gap-3 mt-5">
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                    <a href="{{ route(request()->routeIs('admin.*') ? 'admin.jenis-pelanggaran.index' : 'bk.jenis-pelanggaran.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
