@extends('layouts.app')

@section('title', 'Tambah Pembimbing DU/DI')
@section('page-title', 'Tambah Pembimbing DU/DI')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-header bg-transparent border-secondary p-4 pb-0">
                    <h5 class="fw-bold text-white mb-1"><i class="bi bi-person-plus text-success me-2"></i>Tambah Pembimbing DU/DI Baru</h5>
                    <p class="text-white-50 small">Daftarkan instruktur pembimbing dari pihak perusahaan/industri.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('prakerin.pembimbing-dudi.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="dudi_id" class="form-label text-white">Mitra Perusahaan / DU/DI <span class="text-danger">*</span></label>
                            <select name="dudi_id" id="dudi_id" class="form-select bg-dark text-white border-secondary @error('dudi_id') is-invalid @enderror" required>
                                <option value="">-- Pilih DU/DI --</option>
                                @foreach($dudi as $item)
                                    <option value="{{ $item->id }}" {{ old('dudi_id') == $item->id ? 'selected' : '' }}>{{ $item->nama }}</option>
                                @endforeach
                            </select>
                            @error('dudi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nama" class="form-label text-white">Nama Lengkap Pembimbing <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="nama" class="form-control bg-dark text-white border-secondary @error('nama') is-invalid @enderror" value="{{ old('nama') }}" placeholder="Contoh: Budi Santoso, S.T." required>
                            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="jabatan" class="form-label text-white">Jabatan / Bagian di Perusahaan</label>
                            <input type="text" name="jabatan" id="jabatan" class="form-control bg-dark text-white border-secondary" value="{{ old('jabatan') }}" placeholder="Contoh: Kepala Bengkel, Senior Developer, HR Supervisor">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="no_hp" class="form-label text-white">No. HP / WhatsApp</label>
                                <input type="text" name="no_hp" id="no_hp" class="form-control bg-dark text-white border-secondary" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label text-white">Email</label>
                                <input type="email" name="email" id="email" class="form-control bg-dark text-white border-secondary @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="budi@perusahaan.com">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-success px-4"><i class="bi bi-save me-1"></i> Simpan Pembimbing</button>
                            <a href="{{ route('prakerin.pembimbing-dudi.index') }}" class="btn btn-secondary px-4"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
