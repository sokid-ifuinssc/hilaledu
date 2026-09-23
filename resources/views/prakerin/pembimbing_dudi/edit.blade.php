@extends('layouts.app')

@section('title', 'Edit Pembimbing DU/DI')
@section('page-title', 'Edit Pembimbing DU/DI')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-header bg-transparent border-secondary p-4 pb-0">
                    <h5 class="fw-bold text-white mb-1"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Pembimbing DU/DI</h5>
                    <p class="text-white-50 small">Perbarui data kontak dan status pembimbing lapangan.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('prakerin.pembimbing-dudi.update', $pembimbingDudi->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="dudi_id" class="form-label text-white">Mitra Perusahaan / DU/DI <span class="text-danger">*</span></label>
                            <select name="dudi_id" id="dudi_id" class="form-select bg-dark text-white border-secondary @error('dudi_id') is-invalid @enderror" required>
                                @foreach($dudi as $item)
                                    <option value="{{ $item->id }}" {{ old('dudi_id', $pembimbingDudi->dudi_id) == $item->id ? 'selected' : '' }}>{{ $item->nama }}</option>
                                @endforeach
                            </select>
                            @error('dudi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nama" class="form-label text-white">Nama Lengkap Pembimbing <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="nama" class="form-control bg-dark text-white border-secondary @error('nama') is-invalid @enderror" value="{{ old('nama', $pembimbingDudi->nama) }}" required>
                            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="jabatan" class="form-label text-white">Jabatan / Bagian</label>
                            <input type="text" name="jabatan" id="jabatan" class="form-control bg-dark text-white border-secondary" value="{{ old('jabatan', $pembimbingDudi->jabatan) }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="no_hp" class="form-label text-white">No. HP / WhatsApp</label>
                                <input type="text" name="no_hp" id="no_hp" class="form-control bg-dark text-white border-secondary" value="{{ old('no_hp', $pembimbingDudi->no_hp) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label text-white">Email</label>
                                <input type="email" name="email" id="email" class="form-control bg-dark text-white border-secondary @error('email') is-invalid @enderror" value="{{ old('email', $pembimbingDudi->email) }}">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="status" id="status" value="1" {{ old('status', $pembimbingDudi->status) ? 'checked' : '' }}>
                                <label class="form-check-label text-white" for="status">Status Aktif Membimbing</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Perbarui Pembimbing</button>
                            <a href="{{ route('prakerin.pembimbing-dudi.index') }}" class="btn btn-secondary px-4"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
