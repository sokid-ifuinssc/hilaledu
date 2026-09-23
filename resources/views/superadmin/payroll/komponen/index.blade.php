@extends('layouts.app')

@section('title', 'Master Komponen Gaji - HilalPay')
@section('page-title', 'HilalPay: Master Komponen Gaji')

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
.modal-content {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #0f172a;
    border-radius: 18px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
.modal-header {
    border-bottom: 1px solid #e2e8f0;
}
.modal-footer {
    border-top: 1px solid #e2e8f0;
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
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('superadmin.payroll.dashboard') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 10px;">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke HilalPay
            </a>
            <h5 class="text-slate-900 fw-bold mb-0 ms-2">Master Komponen Gaji</h5>
        </div>
        <button type="button" class="btn btn-success fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKomponen" style="border-radius: 10px; background: #10b981; border: none;">
            <i class="bi bi-plus-lg me-1"></i> Tambah Komponen Baru
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 bg-success bg-opacity-10 text-success fw-medium mb-4" role="alert" style="border-radius: 14px;">
            <i class="bi bi-check-circle-fill me-2 text-success"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Tabel Penerimaan --}}
    <div class="section-card mb-4">
        <div class="section-header">
            <div>
                <h6 class="text-slate-900 fw-bold mb-1">
                    <i class="bi bi-arrow-down-left-circle-fill me-2 text-success"></i>Komponen Penerimaan (Penambah Gaji)
                </h6>
                <small class="text-slate-500">Komponen yang menambah take home pay pegawai (Gaji Pokok, Honor Jam, Tunjangan Jabatan/Kehadiran)</small>
            </div>
            <span class="badge bg-emerald-100 text-emerald-800 border border-emerald-300">
                {{ $penerimaan->count() }} Komponen
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-custom mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width: 120px;">Kode</th>
                        <th>Nama Komponen</th>
                        <th>Tipe Perhitungan</th>
                        <th>Nominal Default</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th class="text-end" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($penerimaan as $item)
                        <tr>
                            <td><span class="badge bg-slate-100 text-slate-800 border border-slate-300 font-monospace">{{ $item->kode }}</span></td>
                            <td class="fw-semibold text-slate-900">{{ $item->nama }}</td>
                            <td>
                                <span class="badge bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ ucfirst(str_replace('_', ' ', $item->tipe)) }}
                                </span>
                            </td>
                            <td class="text-emerald-700 fw-bold">Rp {{ number_format($item->nominal_default, 0, ',', '.') }}</td>
                            <td>
                                <form action="{{ route('superadmin.payroll.komponen.toggle-active', $item) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm p-0 border-0 text-decoration-none">
                                        @if($item->is_aktif)
                                            <span class="badge bg-emerald-100 text-emerald-800 border border-emerald-300">Aktif</span>
                                        @else
                                            <span class="badge bg-rose-100 text-rose-800 border border-rose-300">Nonaktif</span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td class="text-slate-500 small">{{ $item->keterangan ?? '-' }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $item->id }}" style="border-radius: 8px;" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('superadmin.payroll.komponen.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus komponen ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 8px;" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        {{-- Modal Edit --}}
                        <div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('superadmin.payroll.komponen.update', $item) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h6 class="modal-title text-slate-900 fw-bold">Edit Komponen: {{ $item->nama }}</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label text-slate-700 fw-medium small">Nama Komponen</label>
                                                <input type="text" name="nama" class="form-control" value="{{ $item->nama }}" required>
                                            </div>
                                            <input type="hidden" name="jenis" value="{{ $item->jenis }}">
                                            <div class="mb-3">
                                                <label class="form-label text-slate-700 fw-medium small">Tipe Perhitungan</label>
                                                <select name="tipe" class="form-select" required>
                                                    <option value="tetap" {{ $item->tipe === 'tetap' ? 'selected' : '' }}>Tetap per Bulan</option>
                                                    <option value="per_jam" {{ $item->tipe === 'per_jam' ? 'selected' : '' }}>Per Jam Mengajar</option>
                                                    <option value="per_kehadiran" {{ $item->tipe === 'per_kehadiran' ? 'selected' : '' }}>Per Hari Kehadiran</option>
                                                    <option value="persentase" {{ $item->tipe === 'persentase' ? 'selected' : '' }}>Persentase</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-slate-700 fw-medium small">Nominal Default (Rp)</label>
                                                <input type="number" name="nominal_default" class="form-control" value="{{ (int)$item->nominal_default }}" required min="0">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-slate-700 fw-medium small">Keterangan</label>
                                                <input type="text" name="keterangan" class="form-control" value="{{ $item->keterangan }}">
                                            </div>
                                            <div class="form-check form-switch mt-3">
                                                <input class="form-check-input" type="checkbox" name="is_aktif" value="1" id="aktifEdit{{ $item->id }}" {{ $item->is_aktif ? 'checked' : '' }}>
                                                <label class="form-check-label text-slate-700 fw-medium small" for="aktifEdit{{ $item->id }}">Status Aktif</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success" style="background: #10b981; border: none;">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-slate-500">Belum ada komponen penerimaan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tabel Potongan --}}
    <div class="section-card">
        <div class="section-header">
            <div>
                <h6 class="text-slate-900 fw-bold mb-1">
                    <i class="bi bi-arrow-up-right-circle-fill me-2 text-danger"></i>Komponen Potongan (Pengurang Gaji)
                </h6>
                <small class="text-slate-500">Komponen potongan wajib atau sukarela (BPJS, Simpanan Koperasi, Kasbon, Infaq)</small>
            </div>
            <span class="badge bg-rose-100 text-rose-800 border border-rose-300">
                {{ $potongan->count() }} Komponen
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-custom mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width: 120px;">Kode</th>
                        <th>Nama Komponen</th>
                        <th>Tipe Perhitungan</th>
                        <th>Nominal Default</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th class="text-end" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($potongan as $item)
                        <tr>
                            <td><span class="badge bg-slate-100 text-slate-800 border border-slate-300 font-monospace">{{ $item->kode }}</span></td>
                            <td class="fw-semibold text-slate-900">{{ $item->nama }}</td>
                            <td>
                                <span class="badge bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ ucfirst(str_replace('_', ' ', $item->tipe)) }}
                                </span>
                            </td>
                            <td class="text-rose-600 fw-bold">Rp {{ number_format($item->nominal_default, 0, ',', '.') }}</td>
                            <td>
                                <form action="{{ route('superadmin.payroll.komponen.toggle-active', $item) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm p-0 border-0 text-decoration-none">
                                        @if($item->is_aktif)
                                            <span class="badge bg-emerald-100 text-emerald-800 border border-emerald-300">Aktif</span>
                                        @else
                                            <span class="badge bg-rose-100 text-rose-800 border border-rose-300">Nonaktif</span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td class="text-slate-500 small">{{ $item->keterangan ?? '-' }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $item->id }}" style="border-radius: 8px;" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('superadmin.payroll.komponen.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus komponen potongan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 8px;" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        {{-- Modal Edit Potongan --}}
                        <div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('superadmin.payroll.komponen.update', $item) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h6 class="modal-title text-slate-900 fw-bold">Edit Potongan: {{ $item->nama }}</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label text-slate-700 fw-medium small">Nama Komponen</label>
                                                <input type="text" name="nama" class="form-control" value="{{ $item->nama }}" required>
                                            </div>
                                            <input type="hidden" name="jenis" value="{{ $item->jenis }}">
                                            <div class="mb-3">
                                                <label class="form-label text-slate-700 fw-medium small">Tipe Perhitungan</label>
                                                <select name="tipe" class="form-select" required>
                                                    <option value="tetap" {{ $item->tipe === 'tetap' ? 'selected' : '' }}>Tetap per Bulan</option>
                                                    <option value="per_kehadiran" {{ $item->tipe === 'per_kehadiran' ? 'selected' : '' }}>Per Hari Kehadiran</option>
                                                    <option value="persentase" {{ $item->tipe === 'persentase' ? 'selected' : '' }}>Persentase</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-slate-700 fw-medium small">Nominal Default (Rp)</label>
                                                <input type="number" name="nominal_default" class="form-control" value="{{ (int)$item->nominal_default }}" required min="0">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-slate-700 fw-medium small">Keterangan</label>
                                                <input type="text" name="keterangan" class="form-control" value="{{ $item->keterangan }}">
                                            </div>
                                            <div class="form-check form-switch mt-3">
                                                <input class="form-check-input" type="checkbox" name="is_aktif" value="1" id="aktifEditPot{{ $item->id }}" {{ $item->is_aktif ? 'checked' : '' }}>
                                                <label class="form-check-label text-slate-700 fw-medium small" for="aktifEditPot{{ $item->id }}">Status Aktif</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success" style="background: #10b981; border: none;">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-slate-500">Belum ada komponen potongan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Modal Tambah Komponen Baru --}}
<div class="modal fade" id="modalTambahKomponen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('superadmin.payroll.komponen.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title text-slate-900 fw-bold">Tambah Komponen Gaji Baru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-slate-700 fw-medium small">Kode Unik Komponen</label>
                        <input type="text" name="kode" class="form-control" placeholder="Contoh: TJ_WALI, HJM02, P_INFAQ" required style="text-transform: uppercase;">
                        <small class="text-slate-500">Kode pembeda unik, huruf kapital & angka.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-slate-700 fw-medium small">Nama Komponen</label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Tunjangan Wali Kelas" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-slate-700 fw-medium small">Jenis Komponen</label>
                            <select name="jenis" class="form-select" required>
                                <option value="penerimaan">Penerimaan (+)</option>
                                <option value="potongan">Potongan (-)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-slate-700 fw-medium small">Tipe Perhitungan</label>
                            <select name="tipe" class="form-select" required>
                                <option value="tetap">Tetap per Bulan</option>
                                <option value="per_jam">Per Jam Mengajar</option>
                                <option value="per_kehadiran">Per Hari Kehadiran</option>
                                <option value="persentase">Persentase</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-slate-700 fw-medium small">Nominal Default (Rp)</label>
                        <input type="number" name="nominal_default" class="form-control" value="0" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-slate-700 fw-medium small">Keterangan / Deskripsi</label>
                        <input type="text" name="keterangan" class="form-control" placeholder="Opsional">
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_aktif" value="1" id="aktifTambah" checked>
                        <label class="form-check-label text-slate-700 fw-medium small" for="aktifTambah">Status Langsung Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" style="background: #10b981; border: none;">Simpan Komponen</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
