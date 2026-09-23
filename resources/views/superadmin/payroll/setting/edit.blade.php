@extends('layouts.app')

@section('title', 'Sesuaikan Gaji: ' . $user->name . ' - HilalPay')
@section('page-title', 'HilalPay: Sesuaikan Gaji Pegawai')

@section('dashboard-styles')
.section-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 18px;
    overflow: hidden;
    padding: 28px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.form-control, .form-select {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #0f172a;
    border-radius: 10px;
}
.form-control:focus, .form-select:focus {
    background: #ffffff;
    border-color: #10b981;
    color: #0f172a;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}
@endsection

@section('content')
<div class="container-fluid py-3">

    {{-- Breadcrumb & Back --}}
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('superadmin.payroll.setting.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 10px;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pegawai
        </a>
        <h5 class="text-slate-900 fw-bold mb-0 ms-2">Pengaturan Gaji: {{ $user->name }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="section-card">
                <div class="text-center mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 72px; height: 72px; background: rgba(16, 185, 129, 0.15); color: #10b981; font-size: 2rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h5 class="text-slate-900 fw-bold mb-1">{{ $user->name }}</h5>
                    <p class="text-slate-500 small mb-2">NIP: {{ $user->nip ?? '-' }} &bull; {{ $user->username }}</p>
                    @if($user->role === 'guru')
                        <span class="badge bg-emerald-100 text-emerald-800 border border-emerald-300 px-3 py-1">Guru Pendidik</span>
                    @else
                        <span class="badge bg-amber-100 text-amber-800 border border-amber-300 px-3 py-1">Tenaga Kependidikan</span>
                    @endif
                </div>

                <hr class="border-slate-200 my-3">

                <div class="mb-3">
                    <label class="text-slate-600 fw-semibold small d-block mb-1">Tugas Tambahan & Jabatan Terdaftar:</label>
                    @php $jabatanList = $user->daftar_jabatan; @endphp
                    @if(count($jabatanList) > 0)
                        <ul class="list-unstyled mb-0 mt-1">
                            @foreach($jabatanList as $tugas)
                                <li class="text-slate-800 small mb-1">
                                    <i class="bi bi-check2-circle text-success me-1"></i> {{ $tugas }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <span class="text-slate-400 small">Tidak ada tugas tambahan khusus.</span>
                    @endif
                </div>

                <div class="p-3 mt-3 bg-emerald-50 rounded-3 border border-emerald-200">
                    <small class="text-emerald-800 fw-medium d-block">Estimasi Take Home Pay Saat Ini:</small>
                    <div class="fw-bold text-emerald-700 fs-4 mt-1">
                        Rp {{ number_format($setting->estimasiGajiBersihAttribute(), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="section-card">
                <form action="{{ route('superadmin.payroll.setting.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h6 class="text-slate-900 fw-bold mb-3">
                        <i class="bi bi-cash-coin text-success me-2"></i>Komponen Penerimaan Rutin
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-slate-700 fw-medium small">Gaji Pokok (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="gaji_pokok" class="form-control" value="{{ old('gaji_pokok', (int)$setting->gaji_pokok) }}" required min="0">
                        </div>

                        @if($user->role === 'guru')
                            <div class="col-md-3">
                                <label class="form-label text-slate-700 fw-medium small">Honor per Jam (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="honor_per_jam" class="form-control" value="{{ old('honor_per_jam', (int)$setting->honor_per_jam) }}" required min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-slate-700 fw-medium small">Jam Mengajar Default <span class="text-danger">*</span></label>
                                <input type="number" name="jam_mengajar_default" class="form-control" value="{{ old('jam_mengajar_default', $setting->jam_mengajar_default) }}" required min="0">
                                <small class="text-slate-500">Per bulan (contoh: 24)</small>
                            </div>
                        @else
                            <input type="hidden" name="honor_per_jam" value="0">
                            <input type="hidden" name="jam_mengajar_default" value="0">
                        @endif

                        <div class="col-md-4">
                            <label class="form-label text-slate-700 fw-medium small">Tunjangan Tugas Tambahan (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="tunjangan_jabatan" class="form-control" value="{{ old('tunjangan_jabatan', (int)$setting->tunjangan_jabatan) }}" required min="0">
                            <small class="text-slate-500">Kepsek / Kaprog / Walas / Admin</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-slate-700 fw-medium small">Tunjangan Kehadiran / Transport <span class="text-danger">*</span></label>
                            <input type="number" name="tunjangan_kehadiran" class="form-control" value="{{ old('tunjangan_kehadiran', (int)$setting->tunjangan_kehadiran) }}" required min="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-slate-700 fw-medium small">Tunjangan Lainnya (Rp)</label>
                            <input type="number" name="tunjangan_lain" class="form-control" value="{{ old('tunjangan_lain', (int)$setting->tunjangan_lain) }}" min="0">
                        </div>
                    </div>

                    <h6 class="text-slate-900 fw-bold mb-3">
                        <i class="bi bi-dash-circle text-danger me-2"></i>Komponen Potongan Rutin
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label text-slate-700 fw-medium small">Potongan BPJS (Rp)</label>
                            <input type="number" name="potongan_bpjs" class="form-control" value="{{ old('potongan_bpjs', (int)$setting->potongan_bpjs) }}" min="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-slate-700 fw-medium small">Simpanan Koperasi (Rp)</label>
                            <input type="number" name="potongan_koperasi" class="form-control" value="{{ old('potongan_koperasi', (int)$setting->potongan_koperasi) }}" min="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-slate-700 fw-medium small">Infaq / Kas Yayasan (Rp)</label>
                            <input type="number" name="potongan_lain" class="form-control" value="{{ old('potongan_lain', (int)$setting->potongan_lain) }}" min="0">
                        </div>
                    </div>

                    <h6 class="text-slate-900 fw-bold mb-3">
                        <i class="bi bi-bank text-primary me-2"></i>Informasi Rekening Bank Pegawai
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label text-slate-700 fw-medium small">Nama Bank</label>
                            <input type="text" name="rekening_bank" class="form-control" value="{{ old('rekening_bank', $setting->rekening_bank ?? 'BSI (Bank Syariah Indonesia)') }}" placeholder="Contoh: BSI, BRI, BCA">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-slate-700 fw-medium small">Nomor Rekening</label>
                            <input type="text" name="nomor_rekening" class="form-control" value="{{ old('nomor_rekening', $setting->nomor_rekening) }}" placeholder="Nomor rekening">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-slate-700 fw-medium small">Atas Nama Rekening</label>
                            <input type="text" name="atas_nama_rekening" class="form-control" value="{{ old('atas_nama_rekening', $setting->atas_nama_rekening ?? $user->name) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label text-slate-700 fw-medium small">Catatan Khusus</label>
                            <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan opsional untuk penggajian">{{ old('catatan', $setting->catatan) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top border-slate-200">
                        <a href="{{ route('superadmin.payroll.setting.index') }}" class="btn btn-secondary px-4" style="border-radius: 10px;">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-success fw-semibold px-4 shadow-sm" style="border-radius: 10px; background: #10b981; border: none;">
                            <i class="bi bi-check-lg me-1"></i> Simpan Pengaturan Gaji
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
