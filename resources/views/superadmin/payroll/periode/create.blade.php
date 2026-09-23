@extends('layouts.app')

@section('title', 'Buat Periode Penggajian - HilalPay')
@section('page-title', 'HilalPay: Buat Periode Baru')

@section('dashboard-styles')
.section-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 18px;
    overflow: hidden;
    padding: 30px;
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
        <a href="{{ route('superadmin.payroll.periode.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 10px;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Periode
        </a>
        <h5 class="text-slate-900 fw-bold mb-0 ms-2">Buat Periode Penggajian Baru</h5>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger bg-opacity-10 text-danger fw-medium mb-4" role="alert" style="border-radius: 14px;">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="section-card">
                <form action="{{ route('superadmin.payroll.periode.store') }}" method="POST">
                    @csrf

                    <h6 class="text-slate-900 fw-bold mb-3">
                        <i class="bi bi-calendar-event text-success me-2"></i>Informasi Periode Bulan & Tahun
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-slate-700 fw-medium small">Bulan Penggajian <span class="text-danger">*</span></label>
                            <select name="bulan" class="form-select" required>
                                @foreach($bulanList as $num => $nama)
                                    <option value="{{ $num }}" {{ old('bulan', $defaultBulan) == $num ? 'selected' : '' }}>
                                        {{ $nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-slate-700 fw-medium small">Tahun <span class="text-danger">*</span></label>
                            <input type="number" name="tahun" class="form-control" value="{{ old('tahun', $defaultTahun) }}" required min="2020" max="2099">
                        </div>

                        <div class="col-12">
                            <label class="form-label text-slate-700 fw-medium small">Nama Label Periode (Opsional)</label>
                            <input type="text" name="nama_periode" class="form-control" placeholder="Contoh: September 2026 (kosongkan untuk otomatis)" value="{{ old('nama_periode') }}">
                            <small class="text-slate-500">Jika dikosongkan, nama akan otomatis terisi sesuai bulan dan tahun yang dipilih.</small>
                        </div>
                    </div>

                    <h6 class="text-slate-900 fw-bold mb-3">
                        <i class="bi bi-clock-history text-info me-2"></i>Jadwal & Tanggal
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label text-slate-700 fw-medium small">Tanggal Mulai Perhitungan</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', date('Y-m-01')) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-slate-700 fw-medium small">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai', date('Y-m-t')) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-slate-700 fw-medium small">Rencana Pembayaran Gaji</label>
                            <input type="date" name="tanggal_pembayaran" class="form-control" value="{{ old('tanggal_pembayaran', date('Y-m-25')) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label text-slate-700 fw-medium small">Catatan Tambahan</label>
                            <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan internal bendahara / sekolah">{{ old('catatan') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top border-slate-200">
                        <a href="{{ route('superadmin.payroll.periode.index') }}" class="btn btn-secondary px-4" style="border-radius: 10px;">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-success fw-semibold px-4 shadow-sm" style="border-radius: 10px; background: #10b981; border: none;">
                            <i class="bi bi-arrow-right-circle me-1"></i> Buat Periode & Lanjutkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
