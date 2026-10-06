@extends('layouts.app')

@section('title', 'Edit Kerjasama: ' . $kerjasama->nama_mitra)
@section('page-title', 'Edit Kerjasama Mitra DU/DI')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-header bg-transparent border-secondary p-4 pb-2">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold text-white mb-1">
                                <i class="bi bi-pencil-square text-warning me-2"></i>Edit Data Kerjasama (MoU)
                            </h5>
                            <p class="text-white-50 small mb-0">
                                Perbarui data kerjasama dengan mitra: <span class="text-white fw-bold">{{ $kerjasama->nama_mitra }}</span>
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('prakerin.kerjasama.show', $kerjasama->id) }}" class="btn btn-outline-info btn-sm">
                                <i class="bi bi-eye me-1"></i> Lihat Detail
                            </a>
                            <a href="{{ route('prakerin.kerjasama.index') }}" class="btn btn-outline-secondary btn-sm text-white-50">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 pt-3">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show border-0" role="alert" style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; border-radius: 12px;">
                            <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-2"></i>Periksa kembali isian formulir:</div>
                            <ul class="mb-0 ps-3 small">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('prakerin.kerjasama.update', $kerjasama->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Section 1: Profil Mitra DU/DI --}}
                        <div class="p-3 mb-4 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                            <h6 class="text-warning fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-buildings-fill"></i> 1. Identitas Mitra DU/DI
                            </h6>

                            <div class="row">
                                <div class="col-md-7 mb-3">
                                    <label for="nama_mitra" class="form-label text-white">Nama Mitra DU/DI <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_mitra" id="nama_mitra" class="form-control bg-dark text-white border-secondary @error('nama_mitra') is-invalid @enderror" value="{{ old('nama_mitra', $kerjasama->nama_mitra) }}" required>
                                    @error('nama_mitra') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label for="bidang_mitra" class="form-label text-white">Bidang Usaha / Industri</label>
                                    <input type="text" name="bidang_mitra" id="bidang_mitra" class="form-control bg-dark text-white border-secondary" value="{{ old('bidang_mitra', $kerjasama->bidang_mitra) }}" placeholder="Contoh: IT, Otomotif">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label text-white">Alamat Kantor / Workshop</label>
                                <textarea name="alamat" id="alamat" rows="2" class="form-control bg-dark text-white border-secondary">{{ old('alamat', $kerjasama->alamat) }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="no_telp" class="form-label text-white">No. Telepon / Hotline</label>
                                    <input type="text" name="no_telp" id="no_telp" class="form-control bg-dark text-white border-secondary" value="{{ old('no_telp', $kerjasama->no_telp) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label text-white">Email Instansi / HRD</label>
                                    <input type="email" name="email" id="email" class="form-control bg-dark text-white border-secondary @error('email') is-invalid @enderror" value="{{ old('email', $kerjasama->email) }}">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Bentuk Kerjasama --}}
                        <div class="p-3 mb-4 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                            <h6 class="text-info fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-briefcase-fill"></i> 2. Bentuk Kesepakatan Kerjasama <span class="text-danger">*</span>
                            </h6>

                            @php
                                $selectedBentuk = old('bentuk_kerjasama', $kerjasama->bentuk_kerjasama ?? []);
                                if (!is_array($selectedBentuk)) {
                                    $selectedBentuk = [];
                                }
                                $customSelected = array_diff($selectedBentuk, $presetBentuk);
                            @endphp

                            <div class="row g-2 mb-3">
                                @foreach($presetBentuk as $pb)
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="form-check p-2 px-3 rounded-2 border border-secondary" style="background: rgba(0,0,0,0.25);">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" name="bentuk_kerjasama[]" value="{{ $pb }}" id="bentuk_{{ Str::slug($pb) }}" {{ in_array($pb, $selectedBentuk) ? 'checked' : '' }}>
                                            <label class="form-check-label text-white small fw-medium" for="bentuk_{{ Str::slug($pb) }}">
                                                {{ $pb }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mb-2">
                                <label for="bentuk_kerjasama_custom" class="form-label text-white-50 small">
                                    Bentuk Kerjasama Tambahan (Pisahkan dengan tanda koma)
                                </label>
                                <input type="text" name="bentuk_kerjasama_custom" id="bentuk_kerjasama_custom" class="form-control bg-dark text-white border-secondary" value="{{ old('bentuk_kerjasama_custom', implode(', ', $customSelected)) }}">
                            </div>
                        </div>

                        {{-- Section 3: Masa Berlaku & Berkas --}}
                        <div class="p-3 mb-4 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                            <h6 class="text-success fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-check-fill"></i> 3. Masa Berlaku &amp; Berkas Perjanjian (MoU)
                            </h6>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="nomor_mou" class="form-label text-white">Nomor Surat MoU / PKS</label>
                                    <input type="text" name="nomor_mou" id="nomor_mou" class="form-control bg-dark text-white border-secondary" value="{{ old('nomor_mou', $kerjasama->nomor_mou) }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="tahun_mulai" class="form-label text-white">Tahun Mulai <span class="text-danger">*</span></label>
                                    <input type="number" name="tahun_mulai" id="tahun_mulai" class="form-control bg-dark text-white border-secondary @error('tahun_mulai') is-invalid @enderror" value="{{ old('tahun_mulai', $kerjasama->tahun_mulai) }}" required>
                                    @error('tahun_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="tahun_berakhir" class="form-label text-white">Tahun Berakhir <span class="text-danger">*</span></label>
                                    <input type="number" name="tahun_berakhir" id="tahun_berakhir" class="form-control bg-dark text-white border-secondary @error('tahun_berakhir') is-invalid @enderror" value="{{ old('tahun_berakhir', $kerjasama->tahun_berakhir) }}" required>
                                    @error('tahun_berakhir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="status" class="form-label text-white">Status</label>
                                    <select name="status" id="status" class="form-select bg-dark text-white border-secondary">
                                        <option value="aktif" {{ old('status', $kerjasama->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="berakhir" {{ old('status', $kerjasama->status) == 'berakhir' ? 'selected' : '' }}>Berakhir</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="file_kerjasama" class="form-label text-white">
                                        <i class="bi bi-cloud-arrow-up-fill text-info me-1"></i> Unggah / Ganti Berkas MoU
                                    </label>
                                    <input type="file" name="file_kerjasama" id="file_kerjasama" class="form-control bg-dark text-white border-secondary @error('file_kerjasama') is-invalid @enderror" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    @if($kerjasama->hasLocalFile())
                                        <div class="mt-2 p-2 rounded-2 d-flex align-items-center justify-content-between gap-2" style="background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3);">
                                            <div class="small text-white text-truncate">
                                                <i class="bi bi-file-earmark-check-fill text-info me-1"></i>
                                                {{ $kerjasama->file_nama_asli ?? basename($kerjasama->file_kerjasama) }}
                                            </div>
                                            <a href="{{ route('prakerin.kerjasama.download', $kerjasama->id) }}" class="btn btn-xs btn-info text-white py-0 px-2" style="font-size: 0.72rem;">
                                                Unduh
                                            </a>
                                        </div>
                                    @endif
                                    <div class="form-text text-white-50" style="font-size: 0.74rem;">
                                        Biarkan kosong jika tidak ingin mengubah file dokumen yang sudah ada.
                                    </div>
                                    @error('file_kerjasama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="link_drive" class="form-label text-white">
                                        <i class="bi bi-google text-warning me-1"></i> Link Google Drive
                                    </label>
                                    <input type="url" name="link_drive" id="link_drive" class="form-control bg-dark text-white border-secondary @error('link_drive') is-invalid @enderror" value="{{ old('link_drive', $kerjasama->link_drive) }}" placeholder="https://drive.google.com/...">
                                    @if($kerjasama->link_drive)
                                        <div class="mt-2 small">
                                            <a href="{{ $kerjasama->link_drive }}" target="_blank" rel="noopener noreferrer" class="text-warning text-decoration-none">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> Uji Tautan Google Drive Sekarang
                                            </a>
                                        </div>
                                    @endif
                                    @error('link_drive') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section 4: Narahubung PIC & Catatan --}}
                        <div class="p-3 mb-4 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                            <h6 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-person-lines-fill text-primary"></i> 4. Narahubung (PIC) &amp; Catatan
                            </h6>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="pic_nama" class="form-label text-white">Nama PIC</label>
                                    <input type="text" name="pic_nama" id="pic_nama" class="form-control bg-dark text-white border-secondary" value="{{ old('pic_nama', $kerjasama->pic_nama) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="pic_jabatan" class="form-label text-white">Jabatan PIC</label>
                                    <input type="text" name="pic_jabatan" id="pic_jabatan" class="form-control bg-dark text-white border-secondary" value="{{ old('pic_jabatan', $kerjasama->pic_jabatan) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="pic_kontak" class="form-label text-white">No. HP PIC</label>
                                    <input type="text" name="pic_kontak" id="pic_kontak" class="form-control bg-dark text-white border-secondary" value="{{ old('pic_kontak', $kerjasama->pic_kontak) }}">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label for="keterangan" class="form-label text-white">Catatan Kerjasama</label>
                                <textarea name="keterangan" id="keterangan" rows="2" class="form-control bg-dark text-white border-secondary">{{ old('keterangan', $kerjasama->keterangan) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                                <i class="bi bi-check2-circle"></i> Perbarui Data Kerjasama
                            </button>
                            <a href="{{ route('prakerin.kerjasama.index') }}" class="btn btn-secondary px-4 py-2">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
