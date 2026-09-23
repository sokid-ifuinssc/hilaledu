@extends('layouts.app')

@section('title', 'Tambah Periode Prakerin')
@section('page-title', 'Tambah Periode Prakerin')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-header bg-transparent border-secondary p-4 pb-0">
                    <h5 class="fw-bold text-white mb-1"><i class="bi bi-calendar-plus text-success me-2"></i>Buat Periode / Gelombang Baru</h5>
                    <p class="text-white-50 small">Atur jadwal mulai dan selesai untuk gelombang pelaksanaan prakerin.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('prakerin.periode.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="tahun_ajaran_id" class="form-label text-white">Tahun Ajaran <span class="text-danger">*</span></label>
                            <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select bg-dark text-white border-secondary @error('tahun_ajaran_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                @foreach($tahunAjaran as $ta)
                                    <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id') == $ta->id ? 'selected' : '' }}>{{ $ta->nama }}</option>
                                @endforeach
                            </select>
                            @error('tahun_ajaran_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nama" class="form-label text-white">Nama Periode / Gelombang <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="nama" class="form-control bg-dark text-white border-secondary @error('nama') is-invalid @enderror" value="{{ old('nama') }}" placeholder="Contoh: Gelombang 1 TP 2026/2027 (Juli - Oktober)" required>
                            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_mulai" class="form-label text-white">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control bg-dark text-white border-secondary @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai') }}" required>
                                @error('tanggal_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_selesai" class="form-label text-white">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control bg-dark text-white border-secondary @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai') }}" required>
                                @error('tanggal_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="aktif" id="aktif" value="1" {{ old('aktif') ? 'checked' : '' }}>
                                <label class="form-check-label text-white" for="aktif">Set Sebagai Periode Aktif Saat Ini</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success px-4"><i class="bi bi-save me-1"></i> Simpan Periode</button>
                            <a href="{{ route('prakerin.periode.index') }}" class="btn btn-secondary px-4"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
