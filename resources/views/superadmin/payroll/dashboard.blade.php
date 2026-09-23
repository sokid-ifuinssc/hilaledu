@extends('layouts.app')

@section('title', 'HilalPay - Pusat Penggajian Guru & Tendik')
@section('page-title', 'HilalPay: Penggajian & Bisyarah')

@section('dashboard-styles')
.payroll-hero {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.95) 0%, rgba(6, 78, 59, 0.98) 100%);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 26px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(6, 78, 59, 0.15);
}
.payroll-hero::after {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
    border-radius: 50%;
}
.stat-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 18px;
    padding: 22px;
    transition: all 0.25s ease;
    height: 100%;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.stat-card:hover {
    transform: translateY(-2px);
    border-color: #10b981;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.06);
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}
.section-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 18px;
    overflow: hidden;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}
.section-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
}
.table-custom th {
    background: #f8fafc;
    color: #475569;
    font-weight: 600;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--border-color, #e2e8f0);
    padding: 14px 16px;
}
.table-custom td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    color: #1e293b;
    font-size: 0.88rem;
    vertical-align: middle;
}
.badge-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}
.badge-draft { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
.badge-finalized { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
.badge-paid { background: #d1fae5; color: #047857; border: 1px solid #a7f3d0; }
@endsection

@section('content')
<div class="container-fluid py-3">

    {{-- Alert Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 bg-success bg-opacity-10 text-success fw-medium mb-4" role="alert" style="border-radius: 14px;">
            <i class="bi bi-check-circle-fill me-2 text-success"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger bg-opacity-10 text-danger fw-medium mb-4" role="alert" style="border-radius: 14px;">
            <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Hero Banner --}}
    <div class="payroll-hero">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-white bg-opacity-25 text-white border border-white border-opacity-30" style="font-size:0.75rem;">
                        <i class="bi bi-shield-check me-1"></i> SISTEM PENGGAJIAN TERPADU
                    </span>
                    <span class="text-white text-opacity-75" style="font-size: 0.82rem;">SMK Plus Al Hilal</span>
                </div>
                <h2 class="text-white fw-bold mb-2">HilalPay & Bisyarah Digital</h2>
                <p class="text-white text-opacity-90 mb-4" style="max-width: 600px; font-size: 0.92rem; line-height: 1.5;">
                    Platform pengelolaan gaji pokok, honor jam mengajar, tunjangan tugas tambahan, potongan simpanan/BPJS, serta penerbitan slip gaji digital resmi untuk Guru & Tenaga Kependidikan.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('superadmin.payroll.periode.create') }}" class="btn btn-light fw-bold px-4 py-2 text-emerald-900 shadow-sm" style="border-radius: 12px;">
                        <i class="bi bi-plus-circle me-1 text-success"></i> Buat Periode Penggajian
                    </a>
                    <a href="{{ route('superadmin.payroll.setting.index') }}" class="btn btn-outline-light fw-semibold px-3 py-2" style="border-radius: 12px; border-color: rgba(255,255,255,0.4);">
                        <i class="bi bi-sliders me-1"></i> Atur Gaji Pegawai
                    </a>
                    <a href="{{ route('superadmin.payroll.komponen.index') }}" class="btn btn-outline-light fw-semibold px-3 py-2" style="border-radius: 12px; border-color: rgba(255,255,255,0.4);">
                        <i class="bi bi-tags-fill me-1"></i> Master Komponen
                    </a>
                    <a href="{{ route('payroll.laporan.index') }}" class="btn btn-outline-light fw-semibold px-3 py-2" style="border-radius: 12px; border-color: rgba(255,255,255,0.4);">
                        <i class="bi bi-file-earmark-text me-1"></i> Laporan Penggajian
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center text-lg-end d-none d-lg-block">
                <div class="d-inline-flex flex-column align-items-center p-4" style="background: rgba(0,0,0,0.2); border-radius: 20px; border: 1px solid rgba(255,255,255,0.15);">
                    <i class="bi bi-wallet2 text-white mb-2" style="font-size: 3.2rem;"></i>
                    <span class="text-white text-opacity-75" style="font-size: 0.8rem;">Status Periode Terakhir:</span>
                    <span class="text-white fw-bold fs-6 mt-1">{{ $periodeTerakhir?->nama_periode ?? 'Belum Ada Periode' }}</span>
                    @if($periodeTerakhir)
                        <span class="badge-status badge-{{ $periodeTerakhir->status }} mt-2">
                            {{ ucfirst($periodeTerakhir->status) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: #ecfdf5; color: #059669;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="text-slate-900 fw-bold fs-4">{{ $totalPegawai }}</div>
                        <div class="text-slate-600 fw-medium" style="font-size: 0.82rem;">Pendidik & Tendik</div>
                        <div class="text-slate-500 mt-1" style="font-size: 0.74rem;">{{ $totalGuru }} Guru &bull; {{ $totalTendik }} Tendik</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: #eff6ff; color: #2563eb;">
                        <i class="bi bi-calendar3"></i>
                    </div>
                    <div>
                        <div class="text-slate-900 fw-bold fs-4">{{ $totalPeriode }}</div>
                        <div class="text-slate-600 fw-medium" style="font-size: 0.82rem;">Total Periode Gaji</div>
                        <div class="text-slate-500 mt-1" style="font-size: 0.74rem;">Data penggajian terarsip</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div>
                        <div class="text-slate-900 fw-bold fs-5">Rp {{ number_format($totalUangDibayar, 0, ',', '.') }}</div>
                        <div class="text-slate-600 fw-medium" style="font-size: 0.82rem;">Total Dana Tersalurkan</div>
                        <div class="text-slate-500 mt-1" style="font-size: 0.74rem;">Status Paid (Lunas)</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background: #fef2f2; color: #dc2626;">
                        <i class="bi bi-exclamation-circle-fill"></i>
                    </div>
                    <div>
                        <div class="text-slate-900 fw-bold fs-4">{{ $pegawaiTanpaSetting }}</div>
                        <div class="text-slate-600 fw-medium" style="font-size: 0.82rem;">Belum Diset Gaji</div>
                        <div class="mt-1" style="font-size: 0.74rem;">
                            @if($pegawaiTanpaSetting > 0)
                                <a href="{{ route('superadmin.payroll.setting.index') }}" class="text-amber-700 fw-semibold text-decoration-none">Atur sekarang &rarr;</a>
                            @else
                                <span class="text-emerald-700 fw-semibold">Semua terkonfigurasi</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Periode Penggajian Terbaru --}}
    <div class="section-card">
        <div class="section-header">
            <div>
                <h6 class="text-slate-900 fw-bold mb-1">
                    <i class="bi bi-journal-text me-2 text-success"></i>Riwayat Periode Penggajian
                </h6>
                <small class="text-slate-500">Daftar pemrosesan gaji bulanan dan slip gaji pegawai</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('superadmin.payroll.periode.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
                    Lihat Semua Periode &rarr;
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-custom mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Pegawai</th>
                        <th>Total Penerimaan</th>
                        <th>Total Potongan</th>
                        <th>Total Dibayarkan (THP)</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPeriodes as $periode)
                        <tr>
                            <td>
                                <div class="fw-bold text-slate-900">{{ $periode->nama_periode }}</div>
                                <small class="text-slate-500">
                                    {{ $periode->tanggal_pembayaran ? 'Bayar: ' . $periode->tanggal_pembayaran->format('d/m/Y') : 'Belum dibayar' }}
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-slate-100 text-slate-700 border border-slate-300">
                                    <i class="bi bi-person me-1"></i> {{ $periode->payrolls_count }} orang
                                </span>
                            </td>
                            <td class="text-emerald-700 fw-semibold">
                                Rp {{ number_format($periode->total_penerimaan, 0, ',', '.') }}
                            </td>
                            <td class="text-rose-600 fw-semibold">
                                Rp {{ number_format($periode->total_potongan, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="fw-bold text-emerald-800">
                                    Rp {{ number_format($periode->total_dibayarkan, 0, ',', '.') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-status badge-{{ $periode->status }}">
                                    @if($periode->status === 'draft')
                                        <i class="bi bi-pencil-square me-1"></i> Draft
                                    @elseif($periode->status === 'finalized')
                                        <i class="bi bi-check-all me-1"></i> Finalized
                                    @else
                                        <i class="bi bi-cash-coin me-1"></i> Paid (Lunas)
                                    @endif
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('superadmin.payroll.periode.show', $periode) }}" class="btn btn-sm btn-outline-success fw-medium" style="border-radius: 8px;">
                                    <i class="bi bi-eye me-1"></i> Buka Periode
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-slate-500">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-slate-400"></i>
                                Belum ada periode penggajian yang dibuat.<br>
                                <a href="{{ route('superadmin.payroll.periode.create') }}" class="btn btn-sm btn-success mt-3 shadow-sm" style="border-radius: 8px;">
                                    <i class="bi bi-plus me-1"></i> Buat Periode Pertama
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
