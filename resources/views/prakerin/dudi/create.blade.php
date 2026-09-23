@extends('layouts.app')

@section('title', 'Tambah Mitra DU/DI')
@section('page-title', 'Tambah Mitra DU/DI')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-header bg-transparent border-secondary p-4 pb-0">
                    <h5 class="fw-bold text-white mb-1"><i class="bi bi-plus-circle text-success me-2"></i>Tambah Mitra DU/DI Baru</h5>
                    <p class="text-white-50 small">Masukkan informasi perusahaan atau instansi mitra kerja prakerin.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('prakerin.dudi.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="nama" class="form-label text-white">Nama DU/DI <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="nama" class="form-control bg-dark text-white border-secondary @error('nama') is-invalid @enderror" value="{{ old('nama') }}" placeholder="Contoh: PT Telkom Indonesia, Bengkel Al Hilal Motor, dll." required autofocus>
                            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bidang_usaha" class="form-label text-white">Bidang Usaha / Sektor</label>
                            <input type="text" name="bidang_usaha" id="bidang_usaha" class="form-control bg-dark text-white border-secondary" value="{{ old('bidang_usaha') }}" placeholder="Contoh: IT & Software, Otomotif, Perbankan, Grafika">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="no_telp" class="form-label text-white">No. Telepon / WhatsApp</label>
                                <input type="text" name="no_telp" id="no_telp" class="form-control bg-dark text-white border-secondary" value="{{ old('no_telp') }}" placeholder="08xxxxxxxxxx">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label text-white">Alamat Email Perusahaan</label>
                                <input type="email" name="email" id="email" class="form-control bg-dark text-white border-secondary @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="hrd@perusahaan.com">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="alamat" class="form-label text-white">Alamat Kantor / Bengkel / Workshop</label>
                            <textarea name="alamat" id="alamat" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Alamat lengkap lokasi DU/DI...">{{ old('alamat') }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success px-4"><i class="bi bi-save me-1"></i> Simpan Mitra DU/DI</button>
                            <a href="{{ route('prakerin.dudi.index') }}" class="btn btn-secondary px-4"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
