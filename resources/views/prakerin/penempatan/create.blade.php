@extends('layouts.app')

@section('title', 'Tambah Penempatan Prakerin')
@section('page-title', 'Tambah Penempatan Prakerin')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-header bg-transparent border-secondary p-4 pb-0">
                    <h5 class="fw-bold text-white mb-1"><i class="bi bi-person-plus text-success me-2"></i>Plotting Penempatan Siswa Prakerin</h5>
                    <p class="text-white-50 small">Hubungkan siswa dengan mitra industri, pembimbing sekolah, dan instruktur DU/DI.</p>
                </div>
                <div class="card-body p-4">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('prakerin.penempatan.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="periode_prakerin_id" class="form-label text-white">Periode Prakerin <span class="text-danger">*</span></label>
                                <select name="periode_prakerin_id" id="periode_prakerin_id" class="form-select bg-dark text-white border-secondary @error('periode_prakerin_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Periode --</option>
                                    @foreach($periodeList as $p)
                                        <option value="{{ $p->id }}" {{ old('periode_prakerin_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->nama }} ({{ $p->tahunAjaran->nama ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('periode_prakerin_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="siswa_id" class="form-label text-white">Siswa yang Ditempatkan <span class="text-danger">*</span></label>
                                <select name="siswa_id" id="siswa_id" class="form-select bg-dark text-white border-secondary @error('siswa_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Siswa --</option>
                                    @foreach($siswaList as $s)
                                        <option value="{{ $s->id }}" {{ old('siswa_id') == $s->id ? 'selected' : '' }}>
                                            {{ $s->nama_lengkap }} ({{ $s->kelas?->nama_lengkap ?? 'Tanpa Kelas' }}) - NIS: {{ $s->nis }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('siswa_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="dudi_id" class="form-label text-white">Mitra Industri DU/DI <span class="text-danger">*</span></label>
                                <select name="dudi_id" id="dudi_id" class="form-select bg-dark text-white border-secondary @error('dudi_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih DU/DI --</option>
                                    @foreach($dudiList as $d)
                                        <option value="{{ $d->id }}" {{ old('dudi_id') == $d->id ? 'selected' : '' }}>
                                            {{ $d->nama }} ({{ $d->bidang_usaha ?? 'Umum' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('dudi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="pembimbing_dudi_id" class="form-label text-white">Instruktur / Pembimbing DU/DI <span class="text-danger">*</span></label>
                                <select name="pembimbing_dudi_id" id="pembimbing_dudi_id" class="form-select bg-dark text-white border-secondary @error('pembimbing_dudi_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Pembimbing DU/DI --</option>
                                    @foreach($pembimbingList as $pb)
                                        <option value="{{ $pb->id }}" data-dudi="{{ $pb->dudi_id }}" {{ old('pembimbing_dudi_id') == $pb->id ? 'selected' : '' }}>
                                            {{ $pb->nama }} ({{ $pb->dudi->nama ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('pembimbing_dudi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="guru_id" class="form-label text-white">Guru Pembimbing Internal (Sekolah) <span class="text-danger">*</span></label>
                                <select name="guru_id" id="guru_id" class="form-select bg-dark text-white border-secondary @error('guru_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Guru Pembimbing --</option>
                                    @foreach($guruList as $g)
                                        <option value="{{ $g->id }}" {{ old('guru_id') == $g->id ? 'selected' : '' }}>
                                            {{ $g->nama_lengkap }} (NIP: {{ $g->nip ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('guru_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label text-white">Status Penempatan <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select bg-dark text-white border-secondary @error('status') is-invalid @enderror" required>
                                    <option value="belum_mulai" {{ old('status') == 'belum_mulai' ? 'selected' : '' }}>Belum Mulai</option>
                                    <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="selesai" {{ old('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_mulai" class="form-label text-white">Tanggal Mulai Prakerin <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control bg-dark text-white border-secondary @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai') }}" required>
                                @error('tanggal_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tanggal_selesai" class="form-label text-white">Tanggal Selesai Prakerin <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control bg-dark text-white border-secondary @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai') }}" required>
                                @error('tanggal_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="keterangan" class="form-label text-white">Catatan / Keterangan Khusus</label>
                            <textarea name="keterangan" id="keterangan" class="form-control bg-dark text-white border-secondary" rows="2" placeholder="Catatan penempatan siswa...">{{ old('keterangan') }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success px-4"><i class="bi bi-save me-1"></i> Simpan Penempatan</button>
                            <a href="{{ route('prakerin.penempatan.index') }}" class="btn btn-secondary px-4"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
