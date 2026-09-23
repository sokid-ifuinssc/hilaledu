@extends('layouts.app')

@section('title', 'Slip Gaji & Bisyarah Saya - HilalPay')
@section('page-title', 'HilalPay: Slip Gaji & Bisyarah Guru')

@section('dashboard-styles')
.payroll-hero-guru {
    background: linear-gradient(135deg, #059669 0%, #064e3b 100%);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 20px;
    padding: 28px;
    margin-bottom: 24px;
    box-shadow: 0 10px 25px rgba(6, 78, 59, 0.15);
}
.stat-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 18px;
    padding: 20px;
    height: 100%;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
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
.badge-approved { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
.badge-paid { background: #d1fae5; color: #047857; border: 1px solid #a7f3d0; }
@endsection

@section('content')
<div class="container-fluid py-3">

    {{-- Hero Banner Guru --}}
    <div class="payroll-hero-guru">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-white bg-opacity-25 text-white border border-white border-opacity-30 mb-2" style="font-size: 0.75rem;">
                    <i class="bi bi-wallet-fill me-1"></i> PORTAL BISYARAH GURU
                </span>
                <h3 class="text-white fw-bold mb-1">Assalamu'alaikum, {{ auth()->user()->name }}</h3>
                <p class="text-white text-opacity-90 mb-0" style="font-size: 0.92rem; line-height: 1.5;">
                    Berikut adalah catatan riwayat bisyarah dan slip gaji digital Anda sebagai Pendidik di SMK Plus Al Hilal.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="d-inline-block text-start p-3 rounded-4" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25);">
                    <small class="text-white text-opacity-75 d-block">Rekening Penerima:</small>
                    <div class="text-white fw-bold">{{ $setting?->rekening_bank ?? 'BSI' }}</div>
                    <div class="text-white fw-medium small">{{ $setting?->nomor_rekening ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Rincian Komponen Terdaftar --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <small class="text-slate-500 d-block">Gaji Pokok</small>
                <div class="text-slate-900 fw-bold fs-5 mt-1">Rp {{ number_format($setting?->gaji_pokok ?? 0, 0, ',', '.') }}</div>
                <small class="text-slate-500">Gaji pokok bulanan</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <small class="text-slate-500 d-block">Honor Mengajar</small>
                <div class="text-emerald-700 fw-bold fs-5 mt-1">Rp {{ number_format($setting?->honor_per_jam ?? 0, 0, ',', '.') }}/jam</div>
                <small class="text-slate-500">Estimasi: {{ $setting?->jam_mengajar_default ?? 0 }} jam/bln</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <small class="text-slate-500 d-block">Tunj. Tugas Tambahan</small>
                <div class="text-emerald-700 fw-bold fs-5 mt-1">Rp {{ number_format($setting?->tunjangan_jabatan ?? 0, 0, ',', '.') }}</div>
                <small class="text-slate-500">
                    @php $jabatanList = auth()->user()->daftar_jabatan; @endphp
                    {{ count($jabatanList) > 0 ? implode(', ', $jabatanList) : 'Walas, Kaprog, dll' }}
                </small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <small class="text-slate-500 d-block">Total Bisyarah Diterima</small>
                <div class="text-amber-700 fw-bold fs-5 mt-1">Rp {{ number_format($totalDiterima, 0, ',', '.') }}</div>
                <small class="text-slate-500">Akumulasi yang telah cair</small>
            </div>
        </div>
    </div>

    {{-- Riwayat Slip Gaji --}}
    <div class="section-card">
        <div class="p-3 px-4 border-bottom border-slate-200 bg-white d-flex align-items-center justify-content-between">
            <h6 class="text-slate-900 fw-bold mb-0">
                <i class="bi bi-file-earmark-text text-success me-2"></i>Riwayat Slip Gaji Bulanan
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-custom mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Nomor Slip</th>
                        <th>Periode</th>
                        <th>Gaji Pokok</th>
                        <th>Honor & Tunjangan</th>
                        <th>Potongan</th>
                        <th>Gaji Bersih (THP)</th>
                        <th>Status</th>
                        <th>Metode</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $p)
                        <tr>
                            <td><span class="badge bg-slate-100 text-slate-800 border border-slate-300 font-monospace">{{ $p->nomor_slip }}</span></td>
                            <td><div class="fw-bold text-slate-900">{{ $p->periode->nama_periode }}</div></td>
                            <td class="fw-semibold text-slate-800">Rp {{ number_format($p->gaji_pokok, 0, ',', '.') }}</td>
                            <td class="text-emerald-700 fw-medium">+ Rp {{ number_format($p->total_tunjangan + $p->total_honor_jam, 0, ',', '.') }}</td>
                            <td class="text-rose-600 fw-medium">- Rp {{ number_format($p->total_potongan, 0, ',', '.') }}</td>
                            <td>
                                <span class="fw-bold text-emerald-800 fs-6">Rp {{ number_format($p->gaji_bersih, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <span class="badge-status badge-{{ $p->status }}">
                                    @if($p->status === 'paid')
                                        <i class="bi bi-check-circle-fill me-1"></i> Cair / Lunas
                                    @elseif($p->status === 'approved')
                                        <i class="bi bi-check2 me-1"></i> Disetujui
                                    @else
                                        <i class="bi bi-clock me-1"></i> Proses
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-slate-100 text-slate-700 border border-slate-300">
                                    {{ strtoupper($p->metode_pembayaran) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-info fw-medium me-1" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalRincian{{ $p->id }}">
                                    <i class="bi bi-info-circle me-1"></i> Rincian
                                </button>
                                <a href="{{ route('guru.payroll.print', $p) }}" target="_blank" class="btn btn-sm btn-outline-success fw-medium" style="border-radius: 8px;">
                                    <i class="bi bi-printer me-1"></i> Cetak Slip
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Rincian Gaji -->
                        <div class="modal fade" id="modalRincian{{ $p->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content border-0 rounded-4 shadow-sm">
                                    <div class="modal-header border-bottom bg-slate-50 rounded-top-4">
                                        <h5 class="modal-title fw-bold text-slate-800">
                                            Rincian Bisyarah: {{ $p->periode->nama_periode }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-4">
                                            <!-- Penerimaan -->
                                            <div class="col-md-6">
                                                <h6 class="fw-bold text-emerald-700 mb-3 border-bottom pb-2">
                                                    <i class="bi bi-plus-circle me-1"></i> Penerimaan
                                                </h6>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-slate-600">Gaji Pokok</span>
                                                    <span class="fw-semibold">Rp {{ number_format($p->gaji_pokok, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-slate-600">Honor Mengajar ({{ $p->total_jam_mengajar }} jam)</span>
                                                    <span class="fw-semibold">Rp {{ number_format($p->total_honor_jam, 0, ',', '.') }}</span>
                                                </div>
                                                @foreach($p->items->where('jenis', 'penerimaan') as $item)
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-slate-600">{{ $item->nama_komponen }}</span>
                                                    <span class="fw-semibold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</span>
                                                </div>
                                                @endforeach
                                            </div>
                                            <!-- Potongan -->
                                            <div class="col-md-6">
                                                <h6 class="fw-bold text-rose-700 mb-3 border-bottom pb-2">
                                                    <i class="bi bi-dash-circle me-1"></i> Potongan
                                                </h6>
                                                @forelse($p->items->where('jenis', 'potongan') as $item)
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-slate-600">{{ $item->nama_komponen }}</span>
                                                    <span class="fw-semibold text-rose-600">Rp {{ number_format($item->nominal, 0, ',', '.') }}</span>
                                                </div>
                                                @empty
                                                <div class="text-slate-400 text-center py-2 fst-italic">
                                                    Tidak ada potongan
                                                </div>
                                                @endforelse
                                            </div>
                                        </div>
                                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-slate-800">Total Bisyarah (THP)</span>
                                            <span class="fw-bold fs-4 text-emerald-700">Rp {{ number_format($p->gaji_bersih, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top bg-slate-50 rounded-bottom-4">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        <a href="{{ route('guru.payroll.print', $p) }}" target="_blank" class="btn btn-success">
                                            <i class="bi bi-printer me-1"></i> Cetak Slip
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-slate-500">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-slate-400"></i>
                                Belum ada slip gaji yang diterbitkan untuk akun Anda.
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
@endsection
