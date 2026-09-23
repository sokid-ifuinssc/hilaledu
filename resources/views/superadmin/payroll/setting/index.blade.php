@extends('layouts.app')

@section('title', 'Pengaturan Gaji Pegawai - HilalPay')
@section('page-title', 'HilalPay: Pengaturan Gaji Pegawai')

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
@endsection

@section('content')
<div class="container-fluid py-3">

    {{-- Breadcrumb & Actions --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('superadmin.payroll.dashboard') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 10px;">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke HilalPay
            </a>
            <h5 class="text-slate-900 fw-bold mb-0 ms-2">Pengaturan Gaji Guru & Tendik</h5>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('superadmin.payroll.setting.sync-tugas-tambahan') }}" method="POST" class="d-inline" onsubmit="return confirm('Otomatis perbarui tunjangan jabatan seluruh guru dan tendik berdasarkan tugas tambahan (Wali Kelas, Kaprog, Kepsek, Wakasek, Admin Modul)?');">
                @csrf
                <button type="submit" class="btn btn-warning fw-semibold shadow-sm text-dark" style="border-radius: 10px;">
                    <i class="bi bi-arrow-repeat me-1"></i> Auto-Sync Tunjangan Tugas Tambahan
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 bg-success bg-opacity-10 text-success fw-medium mb-4" role="alert" style="border-radius: 14px;">
            <i class="bi bi-check-circle-fill me-2 text-success"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="card bg-white border border-slate-200 shadow-sm p-3 mb-3" style="border-radius: 14px;">
        <form method="GET" action="{{ route('superadmin.payroll.setting.index') }}" class="row g-2">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control search-input" placeholder="Cari nama, NIP, atau username..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select search-input" onchange="this.form.submit()">
                    <option value="">Semua Peran (Guru & Tendik)</option>
                    <option value="guru" {{ request('role') === 'guru' ? 'selected' : '' }}>Khusus Guru ({{ $totalGuru }})</option>
                    <option value="tendik" {{ request('role') === 'tendik' ? 'selected' : '' }}>Khusus Tendik ({{ $totalTendik }})</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100 fw-medium" style="border-radius: 10px;">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-2 text-end">
                @if(request()->hasAny(['search', 'role']))
                    <a href="{{ route('superadmin.payroll.setting.index') }}" class="btn btn-outline-secondary w-100" style="border-radius: 10px;">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Setting Pegawai --}}
    <div class="section-card">
        <div class="table-responsive">
            <table class="table table-custom mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Pegawai</th>
                        <th>Peran & Jabatan</th>
                        <th>Gaji Pokok</th>
                        <th>Honor Jam (Guru)</th>
                        <th>Tunj. Jabatan</th>
                        <th>Tunj. Kehadiran</th>
                        <th>Total Potongan</th>
                        <th>Est. Take Home Pay</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pegawais as $pegawai)
                        @php
                            $setting = $pegawai->payrollSetting;
                            $gajiPokok = $setting ? (float)$setting->gaji_pokok : 0;
                            $honorPerJam = $setting ? (float)$setting->honor_per_jam : 0;
                            $jamDefault = $setting ? (int)$setting->jam_mengajar_default : 0;
                            $tunjJabatan = $setting ? (float)$setting->tunjangan_jabatan : 0;
                            $tunjKehadiran = $setting ? (float)$setting->tunjangan_kehadiran : 0;
                            $tunjLain = $setting ? (float)$setting->tunjangan_lain : 0;
                            $totalPotongan = $setting ? ((float)$setting->potongan_bpjs + (float)$setting->potongan_koperasi + (float)$setting->potongan_lain) : 0;
                            $totalPenerimaan = $gajiPokok + ($honorPerJam * $jamDefault) + $tunjJabatan + $tunjKehadiran + $tunjLain;
                            $thp = max(0, $totalPenerimaan - $totalPotongan);
                            $daftarJabatan = $pegawai->daftar_jabatan;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold text-slate-900">{{ $pegawai->name }}</div>
                                <small class="text-slate-500">
                                    NIP: {{ $pegawai->nip ?? '-' }} &bull; {{ $pegawai->username }}
                                </small>
                            </td>
                            <td>
                                @if($pegawai->role === 'guru')
                                    <span class="badge bg-emerald-100 text-emerald-800 border border-emerald-300">Guru</span>
                                @else
                                    <span class="badge bg-amber-100 text-amber-800 border border-amber-300">Tendik</span>
                                @endif
                                <div class="small text-slate-600 mt-1" style="max-width: 220px; line-height: 1.35;">
                                    @if(count($daftarJabatan) > 0)
                                        {{ implode(', ', $daftarJabatan) }}
                                    @else
                                        {{ $pegawai->jabatan_utama ?? 'Staf' }}
                                    @endif
                                </div>
                            </td>
                            <td class="fw-semibold text-slate-800">Rp {{ number_format($gajiPokok, 0, ',', '.') }}</td>
                            <td>
                                @if($pegawai->role === 'guru')
                                    <div class="text-slate-800 small fw-semibold">Rp {{ number_format($honorPerJam, 0, ',', '.') }}/jam</div>
                                    <small class="text-slate-500">Est: {{ $jamDefault }} jam/bln</small>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="text-emerald-700 fw-semibold">Rp {{ number_format($tunjJabatan, 0, ',', '.') }}</td>
                            <td class="text-emerald-700 fw-semibold">Rp {{ number_format($tunjKehadiran, 0, ',', '.') }}</td>
                            <td class="text-rose-600 fw-semibold">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</td>
                            <td>
                                <span class="fw-bold text-emerald-800 fs-6">Rp {{ number_format($thp, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('superadmin.payroll.setting.edit', $pegawai) }}" class="btn btn-sm btn-outline-success fw-medium" style="border-radius: 8px;">
                                    <i class="bi bi-pencil me-1"></i> Sesuaikan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-slate-500">
                                <i class="bi bi-person-x fs-2 d-block mb-2 text-slate-400"></i>
                                Tidak ada data guru atau tendik ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top border-slate-200 d-flex justify-content-end bg-slate-50">
            {{ $pegawais->links() }}
        </div>
    </div>

</div>
@endsection
