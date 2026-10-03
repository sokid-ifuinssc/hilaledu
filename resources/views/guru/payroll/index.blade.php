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
    {{-- Rincian Komponen Terdaftar --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <small class="text-slate-500 d-block">Penugasan Mengajar</small>
                <div class="text-slate-900 fw-bold fs-5 mt-1">{{ auth()->user()->total_jam_mengajar ?: ($setting?->jam_mengajar_default ?? 0) }} Jam</div>
                <small class="text-emerald-600"><i class="bi bi-check2-circle"></i> Otomatis dari Database</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <small class="text-slate-500 d-block">Honor Mengajar</small>
                <div class="text-emerald-700 fw-bold fs-5 mt-1">Rp {{ number_format($setting?->honor_per_jam ?? 0, 0, ',', '.') }}/jam</div>
                <small class="text-slate-500">Honor jam tatap muka</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <small class="text-slate-500 d-block">Uang Transport / Hari</small>
                <div class="text-emerald-700 fw-bold fs-5 mt-1">Rp {{ number_format($setting?->transport_per_hari ?? 20000, 0, ',', '.') }}</div>
                <small class="text-slate-500">Per hari hadir / laporan KBM</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <small class="text-slate-500 d-block">Tunj. Tugas Tambahan</small>
                <div class="text-primary fw-bold fs-5 mt-1">Rp {{ number_format($setting?->tunjangan_jabatan ?? 0, 0, ',', '.') }}</div>
                <small class="text-slate-500">
                    @php $jabatanList = auth()->user()->daftar_jabatan; @endphp
                    {{ count($jabatanList) > 0 ? implode(', ', array_slice($jabatanList, 0, 2)) . (count($jabatanList) > 2 ? '...' : '') : '-' }}
                </small>
            </div>
        </div>
    </div>

    {{-- Riwayat Transaksi Gaji (Sesuai Mockup) --}}
    <div class="section-card">
        <div class="p-3 px-4 border-bottom border-slate-200 bg-white">
            <h5 class="text-slate-900 fw-bold mb-0">Riwayat Transaksi</h5>
        </div>

        {{-- Toolbar Controls: Tampilkan Data & Cari --}}
        <div class="p-3 bg-white border-bottom border-slate-100">
            <form method="GET" action="{{ route('guru.payroll.index') }}" id="formFilterPayroll">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-slate-600">Tampilkan</span>
                        <select name="per_page" class="form-select form-select-sm" style="width: auto; border-radius: 8px;" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="small text-slate-600">data</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <label for="inputCari" class="small text-slate-600 mb-0">Cari:</label>
                        <input type="text" 
                               name="search" 
                               id="inputCari" 
                               class="form-control form-control-sm" 
                               value="{{ request('search') }}" 
                               placeholder="Cari bulan, tahun..." 
                               style="width: 220px; border-radius: 8px;">
                        @if(request('search'))
                            <a href="{{ route('guru.payroll.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-custom mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="color: #2563eb; width: 22%;">
                            Bulan <i class="bi bi-arrow-down-up small ms-1"></i>
                        </th>
                        <th style="color: #2563eb; width: 18%;">
                            Tahun <i class="bi bi-arrow-down-up small ms-1"></i>
                        </th>
                        <th style="color: #2563eb; width: 25%;">
                            Status <i class="bi bi-arrow-down-up small ms-1"></i>
                        </th>
                        <th style="color: #2563eb; width: 15%;">
                            Validasi <i class="bi bi-arrow-down-up small ms-1"></i>
                        </th>
                        <th class="text-end" style="width: 20%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $p)
                        <tr>
                            <td class="fw-semibold text-slate-900">
                                {{ \App\Models\Payroll\PayrollPeriode::getNamaBulan($p->periode->bulan) }}
                            </td>
                            <td class="text-slate-700">
                                {{ $p->periode->tahun }}
                            </td>
                            <td>
                                <span class="text-slate-800 fw-medium">{{ $p->catatan ?: 'Tanpa Perubahan' }}</span>
                                @if($p->status === 'paid')
                                    <span class="badge bg-emerald-100 text-emerald-800 border border-emerald-300 ms-1 px-2 py-0.5" style="font-size: 0.70rem;">
                                        Lunas
                                    </span>
                                @elseif($p->status === 'approved')
                                    <span class="badge bg-blue-100 text-blue-800 border border-blue-300 ms-1 px-2 py-0.5" style="font-size: 0.70rem;">
                                        Disetujui
                                    </span>
                                @else
                                    <span class="badge bg-amber-100 text-amber-800 border border-amber-300 ms-1 px-2 py-0.5" style="font-size: 0.70rem;">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($p->status === 'paid')
                                    <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-300 px-2 py-1" title="Divalidasi oleh Bendahara Sekolah dan Disahkan oleh Kepala Sekolah">
                                        <i class="bi bi-patch-check-fill text-emerald-600 me-1"></i> Terverifikasi
                                    </span>
                                @elseif($p->status === 'approved')
                                    <span class="badge bg-blue-50 text-blue-700 border border-blue-300 px-2 py-1" title="Divalidasi oleh Bendahara Sekolah">
                                        <i class="bi bi-check-circle-fill text-blue-600 me-1"></i> Terverifikasi
                                    </span>
                                @else
                                    <span class="badge bg-slate-100 text-slate-500 border border-slate-300 px-2 py-1">
                                        <i class="bi bi-hourglass-split me-1"></i> Menunggu Verifikasi
                                    </span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <button type="button" 
                                        class="btn btn-sm btn-primary px-3 fw-semibold shadow-sm" 
                                        style="border-radius: 6px; background-color: #0d6efd; border-color: #0d6efd;" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalRincian{{ $p->id }}" 
                                        title="Lihat Rincian Gaji">
                                    Lihat
                                </button>
                                <a href="{{ route('guru.payroll.print', $p) }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-outline-success fw-semibold ms-1" 
                                   style="border-radius: 6px;" 
                                   title="Cetak Slip Gaji {{ \App\Models\Payroll\PayrollPeriode::getNamaBulan($p->periode->bulan) }} {{ $p->periode->tahun }}">
                                    <i class="bi bi-printer me-1"></i> Cetak
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Rincian Gaji -->
                        <div class="modal fade" id="modalRincian{{ $p->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content border-0 rounded-4 shadow">
                                    <div class="modal-header border-bottom bg-slate-50 rounded-top-4 p-3 px-4">
                                        <div>
                                            <h5 class="modal-title fw-bold text-slate-900 mb-0">
                                                Rincian Gaji & Bisyarah: {{ \App\Models\Payroll\PayrollPeriode::getNamaBulan($p->periode->bulan) }} {{ $p->periode->tahun }}
                                            </h5>
                                            <small class="text-slate-500 font-monospace">{{ $p->nomor_slip }} &bull; NUPTK: {{ auth()->user()->nip ?? '-' }}</small>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        {{-- Ringkasan Kehadiran & Jam Mengajar --}}
                                        <div class="row g-2 mb-4">
                                            <div class="col-md-6">
                                                <div class="p-3 bg-blue-50 rounded-3 border border-blue-200">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="small fw-semibold text-blue-900"><i class="bi bi-clock-history me-1"></i> Beban Mengajar:</span>
                                                        <span class="badge bg-primary fs-6">{{ $p->jumlah_jam_mengajar }} Jam</span>
                                                    </div>
                                                    <small class="text-blue-700 d-block mt-1">Rp {{ number_format($p->user->payrollSetting?->honor_per_jam ?? 35000, 0, ',', '.') }}/jam tatap muka</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-3 bg-emerald-50 rounded-3 border border-emerald-200">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="small fw-semibold text-emerald-900"><i class="bi bi-calendar-check me-1"></i> Kehadiran Riil Mengajar:</span>
                                                        <span class="badge bg-success fs-6">{{ $p->jumlah_kehadiran }} Hari</span>
                                                    </div>
                                                    <small class="text-emerald-700 d-block mt-1">Dihitung dinamis dari presensi harian & KBM per hari</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-4">
                                            <!-- Penerimaan -->
                                            <div class="col-md-6">
                                                <h6 class="fw-bold text-emerald-800 mb-3 border-bottom pb-2 d-flex align-items-center">
                                                    <i class="bi bi-plus-circle text-success me-2"></i> Rincian Penerimaan
                                                </h6>
                                                
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-slate-600">Honor Jam Mengajar ({{ $p->jumlah_jam_mengajar }} jam)</span>
                                                    <span class="fw-semibold text-slate-900">Rp {{ number_format($p->total_honor_jam, 0, ',', '.') }}</span>
                                                </div>

                                                @foreach($p->items->where('jenis', 'penerimaan')->where('nama_komponen', '!=', 'Honor Jam Mengajar') as $item)
                                                <div class="d-flex justify-content-between mb-2">
                                                    <div>
                                                        <span class="text-slate-600 d-block">{{ $item->nama_komponen }}</span>
                                                        @if($item->keterangan)
                                                            <small class="text-slate-400" style="font-size: 0.72rem;">{{ $item->keterangan }}</small>
                                                        @endif
                                                    </div>
                                                    <span class="fw-semibold text-slate-900">Rp {{ number_format($item->nominal, 0, ',', '.') }}</span>
                                                </div>
                                                @endforeach
                                            </div>

                                            <!-- Potongan -->
                                            <div class="col-md-6">
                                                <h6 class="fw-bold text-rose-800 mb-3 border-bottom pb-2 d-flex align-items-center">
                                                    <i class="bi bi-dash-circle text-danger me-2"></i> Rincian Potongan
                                                </h6>
                                                @forelse($p->items->where('jenis', 'potongan') as $item)
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-slate-600">{{ $item->nama_komponen }}</span>
                                                    <span class="fw-semibold text-rose-600">- Rp {{ number_format($item->nominal, 0, ',', '.') }}</span>
                                                </div>
                                                @empty
                                                <div class="text-slate-400 py-2 fst-italic small">
                                                    Tidak ada potongan pada periode ini.
                                                </div>
                                                @endforelse
                                            </div>
                                        </div>

                                        {{-- Total THP --}}
                                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center p-3 bg-light rounded-3">
                                            <div>
                                                <span class="fw-bold text-slate-800 d-block">Take Home Pay (Gaji Bersih)</span>
                                                <small class="text-slate-500">Penerimaan bersih yang disalurkan</small>
                                            </div>
                                            <span class="fw-bold fs-3 text-emerald-700">Rp {{ number_format($p->gaji_bersih, 0, ',', '.') }}</span>
                                        </div>

                                        {{-- Informasi Validasi & Pengesahan --}}
                                        <div class="mt-3 p-3 bg-slate-50 rounded-3 border border-slate-200 small">
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <span class="text-muted d-block">Status Validasi Keuangan:</span>
                                                    <strong class="text-slate-800">
                                                        @if($p->status === 'paid' || $p->status === 'approved')
                                                            <i class="bi bi-check-circle-fill text-success me-1"></i> Terverifikasi oleh Bendahara Sekolah (Elin Tamaya, S.E)
                                                        @else
                                                            <i class="bi bi-hourglass-split text-warning me-1"></i> Menunggu Verifikasi Bendahara
                                                        @endif
                                                    </strong>
                                                </div>
                                                <div class="col-md-6">
                                                    <span class="text-muted d-block">Pengesahan Pimpinan:</span>
                                                    <strong class="text-slate-800">
                                                        @if($p->status === 'paid')
                                                            <i class="bi bi-check-circle-fill text-success me-1"></i> Disahkan oleh Kepala Sekolah
                                                        @else
                                                            <i class="bi bi-dash-circle text-muted me-1"></i> Menunggu Pengesahan
                                                        @endif
                                                    </strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top bg-slate-50 rounded-bottom-4 d-flex justify-content-between">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        <a href="{{ route('guru.payroll.print', $p) }}" target="_blank" class="btn btn-success fw-bold px-4 shadow-sm">
                                            <i class="bi bi-printer-fill me-1"></i> Cetak Slip Gaji Sendiri
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-slate-500">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-slate-400"></i>
                                Belum ada riwayat transaksi gaji yang diterbitkan untuk akun Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Informasi & Pagination --}}
        <div class="p-3 border-top border-slate-200 d-flex flex-wrap align-items-center justify-content-between gap-2 bg-slate-50">
            <span class="small text-slate-500">
                Menampilkan {{ $payrolls->firstItem() ?? 0 }} s/d {{ $payrolls->lastItem() ?? 0 }} dari {{ $payrolls->total() }} data
            </span>
            <div>
                {{ $payrolls->links() }}
            </div>
        </div>
    </div>

</div>
@endsection
