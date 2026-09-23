@extends('layouts.app')

@section('title', 'Detail Periode: ' . $periode->nama_periode . ' - HilalPay')
@section('page-title', 'HilalPay: Detail Periode ' . $periode->nama_periode)

@section('dashboard-styles')
.payroll-hero-periode {
    background: linear-gradient(135deg, #059669 0%, #064e3b 100%);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 24px;
    box-shadow: 0 10px 25px rgba(6, 78, 59, 0.15);
}
.stat-pill {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 14px;
    padding: 14px 18px;
}
.section-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 18px;
    overflow: hidden;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
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
.search-input {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #0f172a;
    border-radius: 10px;
    padding: 8px 14px;
}
.search-input:focus {
    background: #ffffff;
    border-color: #10b981;
    color: #0f172a;
    outline: none;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}
.modal-content {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #0f172a;
    border-radius: 18px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
@endsection

@section('content')
<div class="container-fluid py-3">

    {{-- Breadcrumb & Back --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('superadmin.payroll.periode.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 10px;">
                <i class="bi bi-arrow-left me-1"></i> Daftar Periode
            </a>
            <h5 class="text-slate-900 fw-bold mb-0 ms-2">Periode: {{ $periode->nama_periode }}</h5>
            <span class="badge-status badge-{{ $periode->status }} ms-2">
                {{ strtoupper($periode->status) }}
            </span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('superadmin.payroll.periode.rekap', $periode) }}" target="_blank" class="btn btn-outline-info btn-sm fw-semibold px-3" style="border-radius: 10px;">
                <i class="bi bi-printer-fill me-1"></i> Cetak Rekapitulasi Gaji
            </a>

            @if($periode->status !== 'paid')
                <form action="{{ route('superadmin.payroll.periode.generate', $periode) }}" method="POST" class="d-inline" onsubmit="return confirm('Jalankan hitung otomatis gaji seluruh guru dan tendik? Data slip yang ada pada periode ini akan diperbarui sesuai pengaturan terbaru.');">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm fw-semibold px-3 shadow-sm" style="border-radius: 10px; background: #10b981; border: none;">
                        <i class="bi bi-calculator me-1"></i> Hitung Gaji Otomatis
                    </button>
                </form>

                @if($periode->status === 'draft')
                    <form action="{{ route('superadmin.payroll.periode.finalize', $periode) }}" method="POST" class="d-inline" onsubmit="return confirm('Finalisasi periode ini? Status akan dikunci dan siap untuk pembayaran.');">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm fw-semibold px-3 shadow-sm" style="border-radius: 10px;">
                            <i class="bi bi-lock-fill me-1"></i> Finalisasi Periode
                        </button>
                    </form>
                @endif

                @if($periode->status === 'finalized')
                    <button type="button" class="btn btn-warning btn-sm fw-semibold px-3 text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#modalMarkPaid" style="border-radius: 10px;">
                        <i class="bi bi-check2-circle me-1"></i> Tandai Telah Dibayar
                    </button>
                @endif
            @else
                <span class="btn btn-sm btn-outline-success disabled px-3" style="border-radius: 10px;">
                    <i class="bi bi-check-all me-1"></i> Telah Dibayar ({{ $periode->tanggal_pembayaran?->format('d/m/Y') }})
                </span>
            @endif
        </div>
    </div>

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

    {{-- Banner Stat Periode --}}
    <div class="payroll-hero-periode">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="stat-pill">
                    <small class="text-white text-opacity-75 d-block">Total Penerimaan (Kotor)</small>
                    <span class="text-white fw-bold fs-5">Rp {{ number_format($periode->total_penerimaan, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-pill">
                    <small class="text-white text-opacity-75 d-block">Total Seluruh Potongan</small>
                    <span class="text-white fw-bold fs-5">Rp {{ number_format($periode->total_potongan, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-pill">
                    <small class="text-white text-opacity-75 d-block">Total Bersih Disalurkan (THP)</small>
                    <span class="text-white fw-bold fs-5">Rp {{ number_format($periode->total_dibayarkan, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-pill">
                    <small class="text-white text-opacity-75 d-block">Jumlah Penerima Gaji</small>
                    <span class="text-white fw-bold fs-5">{{ $payrolls->total() }} Pegawai</span>
                    <small class="text-white text-opacity-75 d-block" style="font-size:0.75rem;">{{ $countGuru }} Guru &bull; {{ $countTendik }} Tendik</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Search Bar --}}
    <div class="card bg-white border border-slate-200 shadow-sm p-3 mb-3" style="border-radius: 14px;">
        <form method="GET" action="{{ route('superadmin.payroll.periode.show', $periode) }}" class="row g-2">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control search-input" placeholder="Cari nama pegawai atau NIP..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select search-input" onchange="this.form.submit()">
                    <option value="">Semua Pegawai (Guru & Tendik)</option>
                    <option value="guru" {{ request('role') === 'guru' ? 'selected' : '' }}>Guru Saja</option>
                    <option value="tendik" {{ request('role') === 'tendik' ? 'selected' : '' }}>Tendik Saja</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100" style="border-radius: 10px;">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-2 text-end">
                @if(request()->hasAny(['search', 'role']))
                    <a href="{{ route('superadmin.payroll.periode.show', $periode) }}" class="btn btn-outline-secondary w-100" style="border-radius: 10px;">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Slip Gaji Periode --}}
    <div class="section-card">
        <div class="table-responsive">
            <table class="table table-custom mb-0 align-middle">
                <thead>
                    <tr>
                        <th>No. Slip</th>
                        <th>Pegawai</th>
                        <th>Peran & Tugas</th>
                        <th>Gaji Pokok</th>
                        <th>Tunjangan</th>
                        <th>Potongan</th>
                        <th>Gaji Bersih (THP)</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $p)
                        <tr>
                            <td>
                                <span class="badge bg-slate-100 text-slate-800 border border-slate-300 font-monospace">{{ $p->nomor_slip }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-slate-900">{{ $p->user->name }}</div>
                                <small class="text-slate-500">NIP: {{ $p->user->nip ?? '-' }}</small>
                            </td>
                            <td>
                                @if($p->user->role === 'guru')
                                    <span class="badge bg-emerald-100 text-emerald-800 border border-emerald-300">Guru</span>
                                @else
                                    <span class="badge bg-amber-100 text-amber-800 border border-amber-300">Tendik</span>
                                @endif
                                <div class="small text-slate-600 mt-1" style="max-width: 200px; line-height: 1.35;">
                                    @php $daftarJabatan = $p->user->daftar_jabatan; @endphp
                                    @if(count($daftarJabatan) > 0)
                                        {{ implode(', ', $daftarJabatan) }}
                                    @else
                                        {{ $p->user->jabatan_utama ?? '-' }}
                                    @endif
                                </div>
                            </td>
                            <td class="fw-semibold text-slate-800">Rp {{ number_format($p->gaji_pokok, 0, ',', '.') }}</td>
                            <td class="text-emerald-700 fw-medium">+ Rp {{ number_format($p->total_tunjangan + $p->total_honor_jam, 0, ',', '.') }}</td>
                            <td class="text-rose-600 fw-medium">- Rp {{ number_format($p->total_potongan, 0, ',', '.') }}</td>
                            <td>
                                <span class="fw-bold text-emerald-800 fs-6">Rp {{ number_format($p->gaji_bersih, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <span class="badge-status badge-{{ $p->status }}">
                                    {{ ucfirst($p->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('superadmin.payroll.periode.slip', [$periode, $p]) }}" target="_blank" class="btn btn-sm btn-outline-info me-1" style="border-radius: 8px;" title="Cetak Slip Gaji">
                                    <i class="bi bi-printer"></i>
                                </a>
                                @if($periode->status !== 'paid')
                                    <a href="{{ route('superadmin.payroll.periode.slip.edit', [$periode, $p]) }}" class="btn btn-sm btn-outline-warning" style="border-radius: 8px;" title="Sesuaikan Rincian Slip">
                                        <i class="bi bi-sliders"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-slate-500">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-slate-400"></i>
                                Belum ada slip gaji yang dihitung untuk periode ini.<br>
                                Klik tombol <strong>"Hitung Gaji Otomatis"</strong> di atas untuk memproses gaji seluruh pegawai aktif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top border-slate-200 d-flex justify-content-end bg-slate-50">
            {{ $payrolls->links() }}
        </div>
    </div>

</div>

{{-- Modal Tandai Telah Dibayar --}}
<div class="modal fade" id="modalMarkPaid" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('superadmin.payroll.periode.mark-paid', $periode) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom border-slate-200">
                    <h6 class="modal-title text-slate-900 fw-bold">Konfirmasi Pembayaran Gaji</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-slate-600 mb-3">
                        Tindakan ini akan menandai seluruh slip gaji pada periode <strong>{{ $periode->nama_periode }}</strong> berstatus <strong>PAID (Lunas)</strong> dan mencatat tanggal penyaluran dana.
                    </p>
                    <div class="mb-3">
                        <label class="form-label text-slate-700 fw-medium small">Tanggal Realisasi Pembayaran</label>
                        <input type="date" name="tanggal_pembayaran" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer border-top border-slate-200">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" style="background: #10b981; border: none;">Ya, Tandai Telah Dibayar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
