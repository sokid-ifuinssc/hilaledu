@extends('layouts.app')

@section('title', 'Edit Mitra DU/DI')
@section('page-title', 'Edit Mitra DU/DI')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-header bg-transparent border-secondary p-4 pb-0">
                    <h5 class="fw-bold text-white mb-1"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Data Mitra DU/DI</h5>
                    <p class="text-white-50 small">Perbarui data profil industri mitra prakerin.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('prakerin.dudi.update', $dudi->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="nama" class="form-label text-white">Nama DU/DI <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="nama" class="form-control bg-dark text-white border-secondary @error('nama') is-invalid @enderror" value="{{ old('nama', $dudi->nama) }}" required>
                            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bidang_usaha" class="form-label text-white">Bidang Usaha / Sektor</label>
                            <input type="text" name="bidang_usaha" id="bidang_usaha" class="form-control bg-dark text-white border-secondary" value="{{ old('bidang_usaha', $dudi->bidang_usaha) }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="no_telp" class="form-label text-white">No. Telepon / WhatsApp</label>
                                <input type="text" name="no_telp" id="no_telp" class="form-control bg-dark text-white border-secondary" value="{{ old('no_telp', $dudi->no_telp) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label text-white">Alamat Email Perusahaan</label>
                                <input type="email" name="email" id="email" class="form-control bg-dark text-white border-secondary @error('email') is-invalid @enderror" value="{{ old('email', $dudi->email) }}">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="alamat" class="form-label text-white">Alamat Kantor / Workshop</label>
                            <textarea name="alamat" id="alamat" class="form-control bg-dark text-white border-secondary" rows="3">{{ old('alamat', $dudi->alamat) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="status" id="status" value="1" {{ old('status', $dudi->status) ? 'checked' : '' }}>
                                <label class="form-check-label text-white" for="status">Status Kemitraan Aktif</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Perbarui Mitra DU/DI</button>
                            <a href="{{ route('prakerin.dudi.index') }}" class="btn btn-secondary px-4"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
