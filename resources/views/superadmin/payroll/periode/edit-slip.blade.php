@extends('layouts.app')

@section('title', 'Sesuaikan Slip Gaji: ' . $payroll->user->name . ' - HilalPay')
@section('page-title', 'HilalPay: Sesuaikan Slip Gaji')

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
.item-row {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}
.item-row:hover {
    background: #f1f5f9;
}
@endsection

@section('content')
<div class="container-fluid py-3">

    {{-- Breadcrumb & Back --}}
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('superadmin.payroll.periode.show', $periode) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 10px;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Periode
        </a>
        <h5 class="text-slate-900 fw-bold mb-0 ms-2">Sesuaikan Rincian Slip: {{ $payroll->user->name }} ({{ $payroll->nomor_slip }})</h5>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="section-card">
                <h6 class="text-slate-900 fw-bold mb-3">Ringkasan Pegawai</h6>
                <div class="mb-2">
                    <small class="text-slate-500 d-block">Nama Lengkap</small>
                    <span class="text-slate-900 fw-bold fs-6">{{ $payroll->user->name }}</span>
                </div>
                <div class="mb-2">
                    <small class="text-slate-500 d-block">Peran / NIP</small>
                    <span class="text-slate-700">{{ ucfirst($payroll->user->role) }} &bull; NIP: {{ $payroll->user->nip ?? '-' }}</span>
                </div>
                <div class="mb-3">
                    <small class="text-slate-500 d-block">Tugas Tambahan & Jabatan</small>
                    <span class="text-slate-700 small">
                        @php $jabatanList = $payroll->user->daftar_jabatan; @endphp
                        {{ count($jabatanList) > 0 ? implode(', ', $jabatanList) : '-' }}
                    </span>
                </div>

                <hr class="border-slate-200 my-3">

                <div class="p-3 bg-emerald-50 rounded-3 border border-emerald-200">
                    <small class="text-emerald-800 fw-medium d-block">Total Gaji Bersih (THP):</small>
                    <div class="fw-bold text-emerald-700 fs-4 mt-1">
                        Rp {{ number_format($payroll->gaji_bersih, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="section-card">
                <form action="{{ route('superadmin.payroll.periode.slip.update', [$periode, $payroll]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-slate-700 fw-medium small">Metode Pembayaran</label>
                            <select name="metode_pembayaran" class="form-select" required>
                                <option value="transfer" {{ $payroll->metode_pembayaran === 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                                <option value="tunai" {{ $payroll->metode_pembayaran === 'tunai' ? 'selected' : '' }}>Tunai / Cash</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-700 fw-medium small">Status Slip</label>
                            <select name="status" class="form-select" required>
                                <option value="draft" {{ $payroll->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="approved" {{ $payroll->status === 'approved' ? 'selected' : '' }}>Approved (Disetujui)</option>
                                <option value="paid" {{ $payroll->status === 'paid' ? 'selected' : '' }}>Paid (Telah Dibayar)</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="text-slate-900 fw-bold mb-3">
                        <i class="bi bi-list-check text-success me-2"></i>Daftar Item Komponen Pada Slip Ini
                    </h6>

                    <div id="itemsContainer">
                        @foreach($payroll->items as $index => $item)
                            <div class="item-row p-3 mb-2 rounded-3">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-4">
                                        <label class="form-label text-slate-600 small mb-1">Nama Komponen</label>
                                        <input type="text" name="items[{{ $index }}][nama]" class="form-control form-control-sm" value="{{ $item->nama_komponen }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label text-slate-600 small mb-1">Jenis</label>
                                        <select name="items[{{ $index }}][jenis]" class="form-select form-select-sm" required>
                                            <option value="penerimaan" {{ $item->jenis === 'penerimaan' ? 'selected' : '' }}>Penerimaan (+)</option>
                                            <option value="potongan" {{ $item->jenis === 'potongan' ? 'selected' : '' }}>Potongan (-)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-slate-600 small mb-1">Nominal (Rp)</label>
                                        <input type="number" name="items[{{ $index }}][nominal]" class="form-control form-control-sm" value="{{ (int)$item->nominal }}" min="0" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label text-slate-600 small mb-1">Keterangan</label>
                                        <input type="text" name="items[{{ $index }}][ket]" class="form-control form-control-sm" value="{{ $item->keterangan }}" placeholder="Ket">
                                    </div>
                                    <div class="col-md-1 text-end pt-3">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.item-row').remove();" title="Hapus Baris">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-2 mb-4">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addItemRow();" style="border-radius: 8px;">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Item Tambahan (Bonus / Kasbon / Potongan Insidentil)
                        </button>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-slate-700 fw-medium small">Catatan Khusus Slip</label>
                        <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan opsional untuk pegawai">{{ $payroll->catatan }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top border-slate-200">
                        <a href="{{ route('superadmin.payroll.periode.show', $periode) }}" class="btn btn-secondary px-4" style="border-radius: 10px;">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-success fw-semibold px-4 shadow-sm" style="border-radius: 10px; background: #10b981; border: none;">
                            <i class="bi bi-check-lg me-1"></i> Simpan Penyesuaian Slip
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
let itemIndex = {{ count($payroll->items) + 1 }};
function addItemRow() {
    const container = document.getElementById('itemsContainer');
    const div = document.createElement('div');
    div.className = 'item-row p-3 mb-2 rounded-3';
    div.innerHTML = `
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <label class="form-label text-slate-600 small mb-1">Nama Komponen</label>
                <input type="text" name="items[${itemIndex}][nama]" class="form-control form-control-sm" placeholder="Contoh: Bonus Kegiatan, Kasbon" required>
            </div>
            <div class="col-md-2">
                <label class="form-label text-slate-600 small mb-1">Jenis</label>
                <select name="items[${itemIndex}][jenis]" class="form-select form-select-sm" required>
                    <option value="penerimaan">Penerimaan (+)</option>
                    <option value="potongan">Potongan (-)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-slate-600 small mb-1">Nominal (Rp)</label>
                <input type="number" name="items[${itemIndex}][nominal]" class="form-control form-control-sm" value="0" min="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label text-slate-600 small mb-1">Keterangan</label>
                <input type="text" name="items[${itemIndex}][ket]" class="form-control form-control-sm" placeholder="Opsional">
            </div>
            <div class="col-md-1 text-end pt-3">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.item-row').remove();">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(div);
    itemIndex++;
}
</script>
@endsection
