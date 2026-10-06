@extends('layouts.app')

@section('title', 'Tambah Kerjasama Mitra DU/DI')
@section('page-title', 'Tambah Kerjasama Mitra DU/DI')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="card border border-light-subtle bg-white shadow-xs" style="border-radius: 16px;">
                <div class="card-header bg-white border-bottom p-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                <i class="bi bi-handshake text-success fs-4"></i> Input Pendataan Kerjasama (MoU) Mitra DU/DI
                            </h5>
                            <p class="text-muted small mb-0">
                                Catat kesepakatan kerjasama industri. Mitra ini akan otomatis terhubung ke daftar Mitra DU/DI Prakerin.
                            </p>
                        </div>
                        <a href="{{ route('prakerin.kerjasama.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>

                <div class="card-body p-4 pt-3">
                    {{-- Alert Error Validation --}}
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show border-0" role="alert" style="background: #fee2e2; color: #b91c1c; border-radius: 12px;">
                            <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-2"></i>Periksa kembali isian formulir:</div>
                            <ul class="mb-0 ps-3 small">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('prakerin.kerjasama.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Section 1: Profil Mitra DU/DI --}}
                        <div class="p-4 mb-4 rounded-3 border bg-light" style="border-color: #e2e8f0 !important;">
                            <h6 class="text-dark fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-buildings-fill text-primary"></i> 1. Identitas Mitra DU/DI
                            </h6>

                            @if($dudiList->count() > 0)
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-semibold">Pilih dari Mitra DU/DI yang sudah ada (Opsional untuk isi otomatis)</label>
                                    <select id="pilih_dudi_existing" class="form-select bg-white text-dark border-secondary-subtle">
                                        <option value="">-- Pilih Mitra Terdaftar atau Ketik Mitra Baru di Bawah --</option>
                                        @foreach($dudiList as $d)
                                            <option value="{{ $d->id }}" 
                                                    data-nama="{{ $d->nama }}" 
                                                    data-bidang="{{ $d->bidang_usaha }}" 
                                                    data-alamat="{{ $d->alamat }}"
                                                    data-telp="{{ $d->no_telp }}"
                                                    data-email="{{ $d->email }}"
                                                    {{ old('dudi_id') == $d->id ? 'selected' : '' }}>
                                                {{ $d->nama }} ({{ $d->bidang_usaha ?? 'Umum' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="dudi_id" id="dudi_id" value="{{ old('dudi_id') }}">
                                    <div class="form-text text-muted" style="font-size: 0.75rem;">
                                        Jika memilih mitra yang ada, form di bawah akan otomatis terisi. Jika mitra baru, biarkan kosong dan ketik nama mitra baru.
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-7 mb-3">
                                    <label for="nama_mitra" class="form-label text-dark fw-semibold">Nama Mitra DU/DI <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_mitra" id="nama_mitra" class="form-control bg-white text-dark border-secondary-subtle @error('nama_mitra') is-invalid @enderror" value="{{ old('nama_mitra') }}" placeholder="Contoh: PT Telkom Indonesia, Bengkel Al Hilal Motor, Bank BJB" required autofocus>
                                    @error('nama_mitra') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label for="bidang_mitra" class="form-label text-dark fw-semibold">Bidang Usaha / Industri</label>
                                    <input type="text" name="bidang_mitra" id="bidang_mitra" class="form-control bg-white text-dark border-secondary-subtle" value="{{ old('bidang_mitra') }}" placeholder="Contoh: IT & Telekomunikasi, Otomotif, Perbankan">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label text-dark fw-semibold">Alamat Kantor / Workshop / Instansi</label>
                                <textarea name="alamat" id="alamat" rows="2" class="form-control bg-white text-dark border-secondary-subtle" placeholder="Alamat lengkap lokasi mitra...">{{ old('alamat') }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="no_telp" class="form-label text-dark fw-semibold">No. Telepon / Hotline</label>
                                    <input type="text" name="no_telp" id="no_telp" class="form-control bg-white text-dark border-secondary-subtle" value="{{ old('no_telp') }}" placeholder="021-xxxxxxx atau 08xxxxxxx">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label text-dark fw-semibold">Email Instansi / HRD</label>
                                    <input type="email" name="email" id="email" class="form-control bg-white text-dark border-secondary-subtle @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="kerjasama@perusahaan.com">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Bentuk Kerjasama --}}
                        <div class="p-4 mb-4 rounded-3 border bg-light" style="border-color: #e2e8f0 !important;">
                            <h6 class="text-dark fw-bold mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-briefcase-fill text-info"></i> 2. Bentuk Kesepakatan Kerjasama <span class="text-danger">*</span>
                            </h6>
                            <p class="text-muted small mb-3">
                                Pilih kesepakatan kerjasama yang dibangun dengan mitra DU/DI (bisa memilih lebih dari satu):
                            </p>

                            @php
                                $selectedBentuk = old('bentuk_kerjasama', ['Pelaksanaan Prakerin / PKL']);
                                if (!is_array($selectedBentuk)) {
                                    $selectedBentuk = [];
                                }
                            @endphp

                            <div class="row g-2 mb-3">
                                @foreach($presetBentuk as $pb)
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="form-check p-2 px-3 rounded-2 border bg-white shadow-xs">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" name="bentuk_kerjasama[]" value="{{ $pb }}" id="bentuk_{{ Str::slug($pb) }}" {{ in_array($pb, $selectedBentuk) ? 'checked' : '' }}>
                                            <label class="form-check-label text-dark small fw-semibold" for="bentuk_{{ Str::slug($pb) }}">
                                                {{ $pb }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mb-2">
                                <label for="bentuk_kerjasama_custom" class="form-label text-dark fw-semibold small">
                                    Bentuk Kerjasama Lainnya (Pisahkan dengan tanda koma jika lebih dari satu)
                                </label>
                                <input type="text" name="bentuk_kerjasama_custom" id="bentuk_kerjasama_custom" class="form-control bg-white text-dark border-secondary-subtle" value="{{ old('bentuk_kerjasama_custom') }}" placeholder="Contoh: CSR Bantuan Komputer, Uji Kompetensi Mandiri, dll">
                            </div>
                        </div>

                        {{-- Section 3: Masa Berlaku & Dokumen Kerjasama --}}
                        <div class="p-4 mb-4 rounded-3 border bg-light" style="border-color: #e2e8f0 !important;">
                            <h6 class="text-dark fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-check-fill text-success"></i> 3. Masa Berlaku &amp; Berkas Perjanjian (MoU)
                            </h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nomor_mou" class="form-label text-dark fw-semibold">Nomor Surat MoU / PKS</label>
                                    <input type="text" name="nomor_mou" id="nomor_mou" class="form-control bg-white text-dark border-secondary-subtle" value="{{ old('nomor_mou') }}" placeholder="Contoh: 421.5/024/SMK-HILAL/MOU/2026">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="tahun_mulai" class="form-label text-dark fw-semibold">Tahun Mulai <span class="text-danger">*</span></label>
                                    <input type="number" name="tahun_mulai" id="tahun_mulai" class="form-control bg-white text-dark border-secondary-subtle @error('tahun_mulai') is-invalid @enderror" value="{{ old('tahun_mulai', date('Y')) }}" min="2000" max="2050" required>
                                    @error('tahun_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="tahun_berakhir" class="form-label text-dark fw-semibold">Tahun Berakhir <span class="text-danger">*</span></label>
                                    <input type="number" name="tahun_berakhir" id="tahun_berakhir" class="form-control bg-white text-dark border-secondary-subtle @error('tahun_berakhir') is-invalid @enderror" value="{{ old('tahun_berakhir', date('Y') + 3) }}" min="2000" max="2050" required>
                                    @error('tahun_berakhir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="file_kerjasama" class="form-label text-dark fw-semibold">
                                        <i class="bi bi-cloud-arrow-up-fill text-primary me-1"></i> Upload File Kerjasama (MoU / PKS)
                                    </label>
                                    <input type="file" name="file_kerjasama" id="file_kerjasama" class="form-control bg-white text-dark border-secondary-subtle @error('file_kerjasama') is-invalid @enderror" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    <div class="form-text text-muted" style="font-size: 0.75rem;">
                                        Format berkas: PDF, Word (DOC/DOCX), atau Gambar. Maks. 15 MB.
                                    </div>
                                    @error('file_kerjasama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="link_drive" class="form-label text-dark fw-semibold">
                                        <i class="bi bi-google text-warning me-1"></i> Link Google Drive
                                    </label>
                                    <input type="url" name="link_drive" id="link_drive" class="form-control bg-white text-dark border-secondary-subtle @error('link_drive') is-invalid @enderror" value="{{ old('link_drive') }}" placeholder="https://drive.google.com/drive/folders/...">
                                    <div class="form-text text-muted" style="font-size: 0.75rem;">
                                        Tautan Google Drive sebagai cadangan &amp; alternatif pembaruan jika file MoU tidak bisa dibuka.
                                    </div>
                                    @error('link_drive') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section 4: PIC / Narahubung & Keterangan --}}
                        <div class="p-4 mb-4 rounded-3 border bg-light" style="border-color: #e2e8f0 !important;">
                            <h6 class="text-dark fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-person-lines-fill text-primary"></i> 4. Narahubung (PIC) &amp; Catatan Tambahan
                            </h6>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="pic_nama" class="form-label text-dark fw-semibold">Nama PIC / Pejabat Mitra</label>
                                    <input type="text" name="pic_nama" id="pic_nama" class="form-control bg-white text-dark border-secondary-subtle" value="{{ old('pic_nama') }}" placeholder="Nama perwakilan industri">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="pic_jabatan" class="form-label text-dark fw-semibold">Jabatan PIC</label>
                                    <input type="text" name="pic_jabatan" id="pic_jabatan" class="form-control bg-white text-dark border-secondary-subtle" value="{{ old('pic_jabatan') }}" placeholder="Contoh: Manager HRD / Pimpinan">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="pic_kontak" class="form-label text-dark fw-semibold">No. HP / WhatsApp PIC</label>
                                    <input type="text" name="pic_kontak" id="pic_kontak" class="form-control bg-white text-dark border-secondary-subtle" value="{{ old('pic_kontak') }}" placeholder="08xxxxxxxxxx">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label for="keterangan" class="form-label text-dark fw-semibold">Catatan / Ringkasan Poin Kerjasama</label>
                                <textarea name="keterangan" id="keterangan" rows="2" class="form-control bg-white text-dark border-secondary-subtle" placeholder="Catatan tambahan mengenai ruang lingkup kerjasama...">{{ old('keterangan') }}</textarea>
                            </div>
                        </div>

                        {{-- Notice Integrasi Otomatis --}}
                        <div class="p-3 mb-4 rounded-3 d-flex align-items-center gap-3" style="background: #ecfdf5; border: 1px solid #a7f3d0;">
                            <div class="rounded-circle p-2 text-success d-flex align-items-center justify-content-center" style="background: #d1fae5; width: 42px; height: 42px;">
                                <i class="bi bi-link-45deg fs-4"></i>
                            </div>
                            <div class="small text-dark">
                                <div class="fw-bold text-success">Otomatis Terhubung ke Sistem Prakerin</div>
                                <div>Mitra yang diinputkan akan otomatis terdaftar pada menu <strong>Mitra DU/DI Prakerin</strong> dan langsung dapat dipilih sebagai lokasi penempatan PKL siswa.</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm">
                                <i class="bi bi-save-fill"></i> Simpan Kerjasama &amp; Mitra
                            </button>
                            <a href="{{ route('prakerin.kerjasama.index') }}" class="btn btn-outline-secondary px-4 py-2">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dudiSelect = document.getElementById('pilih_dudi_existing');
    if (dudiSelect) {
        dudiSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (opt.value) {
                document.getElementById('dudi_id').value = opt.value;
                document.getElementById('nama_mitra').value = opt.getAttribute('data-nama') || '';
                document.getElementById('bidang_mitra').value = opt.getAttribute('data-bidang') || '';
                document.getElementById('alamat').value = opt.getAttribute('data-alamat') || '';
                document.getElementById('no_telp').value = opt.getAttribute('data-telp') || '';
                document.getElementById('email').value = opt.getAttribute('data-email') || '';
            } else {
                document.getElementById('dudi_id').value = '';
            }
        });
    }
});
</script>
@endsection
