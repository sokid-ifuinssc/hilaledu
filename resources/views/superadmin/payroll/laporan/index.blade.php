@extends('layouts.app')

@section('title', 'Laporan Penggajian')
@section('page-title', 'Laporan & Rekap Penggajian')

@section('content')
<div class="container-fluid py-4">
    
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white p-4 border-bottom d-flex align-items-center justify-content-between">
            <div>
                <h5 class="fw-bold text-slate-800 mb-1"><i class="bi bi-funnel me-2 text-emerald-600"></i>Filter Laporan</h5>
                <p class="text-slate-500 mb-0 small">Filter data penggajian untuk dicetak.</p>
            </div>
            <a href="{{ route('payroll.laporan.print', request()->all()) }}" target="_blank" class="btn btn-emerald rounded-3 px-4">
                <i class="bi bi-printer me-2"></i> Cetak Laporan
            </a>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('payroll.laporan.index') }}" method="GET" class="row g-3">
                
                <div class="col-md-3">
                    <label class="form-label text-slate-700 fw-semibold text-sm">Bulan</label>
                    <select name="bulan" class="form-select bg-slate-50 border-slate-200">
                        <option value="">-- Semua Bulan --</option>
                        @foreach($bulans as $num => $name)
                            <option value="{{ $num }}" {{ request('bulan') == $num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label text-slate-700 fw-semibold text-sm">Tahun</label>
                    <select name="tahun" class="form-select bg-slate-50 border-slate-200">
                        <option value="">-- Semua --</option>
                        @foreach($tahuns as $thn)
                            <option value="{{ $thn }}" {{ request('tahun') == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-slate-700 fw-semibold text-sm">Kegiatan (Nama Periode)</label>
                    <input type="text" name="kegiatan" class="form-control bg-slate-50 border-slate-200" 
                           value="{{ request('kegiatan') }}" placeholder="Cari: PTS, ANBK, dll...">
                </div>

                <div class="col-md-4">
                    <label class="form-label text-slate-700 fw-semibold text-sm">Nama Guru</label>
                    <select name="guru_id" class="form-select bg-slate-50 border-slate-200">
                        <option value="">-- Semua Guru --</option>
                        @foreach($gurus as $g)
                            <option value="{{ $g->id }}" {{ request('guru_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 text-end mt-4">
                    <a href="{{ route('payroll.laporan.index') }}" class="btn btn-light border me-2">Reset</a>
                    <button type="submit" class="btn btn-primary px-4">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-slate-50 text-slate-600 text-uppercase text-xs" style="letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4 py-3">Slip & Periode</th>
                            <th class="py-3">Guru</th>
                            <th class="py-3">Penerimaan</th>
                            <th class="py-3">Potongan</th>
                            <th class="py-3">Total Bisyarah (THP)</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 pe-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $p)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-slate-800">{{ $p->nomor_slip }}</div>
                                    <div class="small text-emerald-600">{{ $p->periode->nama_periode }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-slate-800">{{ $p->user->name }}</div>
                                </td>
                                <td>
                                    <span class="text-emerald-700 fw-medium">Rp {{ number_format($p->gaji_pokok + $p->total_honor_jam + $p->total_tunjangan, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    <span class="text-rose-600 fw-medium">Rp {{ number_format($p->total_potongan, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-emerald-800">Rp {{ number_format($p->gaji_bersih, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    @if($p->status === 'paid')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3">Dibayar</span>
                                    @elseif($p->status === 'approved')
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3">Disetujui</span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3">Draft</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('payroll.periode.slip', [$p->periode_id, $p->id]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-slate-500">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-slate-300"></i>
                                    Tidak ada data gaji yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payrolls->hasPages())
        <div class="card-footer bg-white p-3 border-top d-flex justify-content-end">
            {{ $payrolls->links() }}
        </div>
        @endif
    </div>

</div>

<style>
.btn-emerald {
    background-color: #059669;
    color: white;
    border: none;
}
.btn-emerald:hover {
    background-color: #047857;
    color: white;
}
</style>
@endsection
