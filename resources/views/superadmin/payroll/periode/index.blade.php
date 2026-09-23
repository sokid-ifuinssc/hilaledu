@extends('layouts.app')

@section('title', 'Daftar Periode Penggajian - HilalPay')
@section('page-title', 'HilalPay: Periode Penggajian')

@section('dashboard-styles')
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

    {{-- Breadcrumb & Actions --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('superadmin.payroll.dashboard') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 10px;">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke HilalPay
            </a>
            <h5 class="text-slate-900 fw-bold mb-0 ms-2">Daftar Periode Penggajian</h5>
        </div>
        <a href="{{ route('superadmin.payroll.periode.create') }}" class="btn btn-success fw-semibold shadow-sm" style="border-radius: 10px; background: #10b981; border: none;">
            <i class="bi bi-plus-lg me-1"></i> Buat Periode Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 bg-success bg-opacity-10 text-success fw-medium mb-4" role="alert" style="border-radius: 14px;">
            <i class="bi bi-check-circle-fill me-2 text-success"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="section-card">
        <div class="table-responsive">
            <table class="table table-custom mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Periode Penggajian</th>
                        <th>Jumlah Pegawai</th>
                        <th>Total Penerimaan</th>
                        <th>Total Potongan</th>
                        <th>Total Bersih (THP)</th>
                        <th>Status</th>
                        <th>Tanggal Pembayaran</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periodes as $periode)
                        <tr>
                            <td>
                                <div class="fw-bold text-slate-900 fs-6">{{ $periode->nama_periode }}</div>
                                <small class="text-slate-500">Tahun {{ $periode->tahun }} - Bulan {{ $periode->bulan }}</small>
                            </td>
                            <td>
                                <span class="badge bg-slate-100 text-slate-700 border border-slate-300">
                                    {{ $periode->payrolls_count }} Pegawai
                                </span>
                            </td>
                            <td class="text-emerald-700 fw-semibold">Rp {{ number_format($periode->total_penerimaan, 0, ',', '.') }}</td>
                            <td class="text-rose-600 fw-semibold">Rp {{ number_format($periode->total_potongan, 0, ',', '.') }}</td>
                            <td>
                                <span class="text-emerald-800 fw-bold">Rp {{ number_format($periode->total_dibayarkan, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <span class="badge-status badge-{{ $periode->status }}">
                                    @if($periode->status === 'draft')
                                        <i class="bi bi-pencil me-1"></i> Draft
                                    @elseif($periode->status === 'finalized')
                                        <i class="bi bi-check-all me-1"></i> Finalized
                                    @else
                                        <i class="bi bi-cash-coin me-1"></i> Paid (Lunas)
                                    @endif
                                </span>
                            </td>
                            <td class="text-slate-600">
                                {{ $periode->tanggal_pembayaran ? $periode->tanggal_pembayaran->format('d M Y') : '-' }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('superadmin.payroll.periode.show', $periode) }}" class="btn btn-sm btn-outline-success me-1 fw-medium" style="border-radius: 8px;">
                                    <i class="bi bi-folder2-open me-1"></i> Buka
                                </a>
                                @if($periode->status === 'draft')
                                    <form action="{{ route('superadmin.payroll.periode.destroy', $periode) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus periode ini beserta seluruh slip di dalamnya?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 8px;" title="Hapus Periode">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-slate-500">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-slate-400"></i>
                                Belum ada periode penggajian.<br>
                                <a href="{{ route('superadmin.payroll.periode.create') }}" class="btn btn-sm btn-success mt-3 shadow-sm" style="border-radius: 8px;">
                                    <i class="bi bi-plus-lg me-1"></i> Buat Periode Sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top border-slate-200 d-flex justify-content-end bg-slate-50">
            {{ $periodes->links() }}
        </div>
    </div>

</div>
@endsection
