@extends('layouts.app')
@section('title', 'Data Master Tugas Tambahan')
@section('page-title', 'Data Master - Tugas Tambahan Sekolah')

@section('dashboard-styles')
.master-layout { display: grid; grid-template-columns: 1fr 340px; gap: 20px; }
@media (max-width: 992px) {
    .master-layout { grid-template-columns: 1fr; }
}
.section-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 18px; overflow: hidden; }
.section-header { padding: 16px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
.section-header h6 { font-size: 0.95rem; font-weight: 700; margin: 0; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { padding: 12px 20px; text-align: left; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-muted); border-bottom: 1px solid var(--border-color); background: rgba(255,255,255,0.02); }
.data-table td { padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.03); font-size: 0.87rem; vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: rgba(255,255,255,0.02); }
.form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 18px; padding: 24px; }
.field-label { font-size: 0.82rem; font-weight: 500; color: var(--text-muted); margin-bottom: 7px; display: block; }
.field-required { color: var(--accent-gold); }
.form-input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: var(--text-light); border-radius: 10px; padding: 10px 14px; font-family: 'Poppins', sans-serif; font-size: 0.85rem; margin-bottom: 14px; transition: all 0.25s ease; }
.form-input:focus { outline: none; border-color: var(--primary-light); background: rgba(255,255,255,0.07); box-shadow: 0 0 0 3px rgba(45,138,78,0.15); }
.form-input.is-invalid { border-color: rgba(231,76,60,0.6); }
.field-error { font-size: 0.76rem; color: #f1948a; margin-top: -10px; margin-bottom: 10px; }
.btn-save { display: inline-flex; align-items: center; gap: 7px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border: none; color: white; padding: 10px 22px; border-radius: 10px; font-size: 0.86rem; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif; width: 100%; justify-content: center; transition: all 0.25s; }
.btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(26,86,50,0.4); }
.action-btn { display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 500; cursor: pointer; border: none; font-family: 'Poppins', sans-serif; text-decoration: none; transition: all 0.2s; }
.btn-edit { background: rgba(52, 152, 219, 0.12); color: #5dade2; border: 1px solid rgba(52, 152, 219, 0.25); }
.btn-edit:hover { background: rgba(52, 152, 219, 0.25); color: #7fb3d5; }
.btn-del { background: rgba(231,76,60,0.1); color: #f1948a; border: 1px solid rgba(231,76,60,0.2); }
.btn-del:hover { background: rgba(231,76,60,0.2); }
.nav-master { display: flex; gap: 6px; margin-bottom: 20px; flex-wrap: wrap; }
.nav-master a { padding: 8px 16px; border-radius: 10px; font-size: 0.82rem; font-weight: 500; text-decoration: none; border: 1px solid var(--border-color); color: var(--text-muted); transition: all 0.2s; }
.nav-master a.active, .nav-master a:hover { background: var(--bg-card); color: var(--text-light); border-color: var(--primary-light); }

/* Modal Styling */
.modal-content { background-color: #14241b !important; border: 1px solid rgba(255,255,255,0.18) !important; border-radius: 20px; color: var(--text-light); box-shadow: 0 15px 50px rgba(0,0,0,0.9); }
.modal-header { border-bottom: 1px solid var(--border-color); padding: 18px 24px; }
.modal-footer { border-top: 1px solid var(--border-color); padding: 16px 24px; }
.modal-backdrop.show { opacity: 0.75 !important; }
@endsection

@section('content')
<div class="nav-master" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:20px;">
    <div style="display:flex; gap:6px; flex-wrap:wrap;">
        <a href="{{ route('superadmin.master.sekolah') }}"><i class="bi bi-building me-1"></i> Pengaturan Sekolah</a>
        <a href="{{ route('superadmin.master.tahun-ajaran') }}"><i class="bi bi-calendar-check me-1"></i> Tahun Ajaran</a>
        <a href="{{ route('superadmin.master.jurusan') }}"><i class="bi bi-diagram-3 me-1"></i> Jurusan</a>
        <a href="{{ route('superadmin.master.kelas') }}"><i class="bi bi-collection me-1"></i> Kelas</a>
        <a href="{{ route('superadmin.master.tugas-tambahan') }}" class="active"><i class="bi bi-award me-1"></i> Tugas Tambahan</a>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="{{ route('superadmin.guru.index') }}" style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:8px; font-size:0.8rem; font-weight:600; text-decoration:none; background:rgba(46,204,113,0.1); color:#58d68d; border:1px solid rgba(46,204,113,0.3); transition:all 0.2s;">
            <i class="bi bi-person-workspace"></i> Data Guru
        </a>
    </div>
</div>

@if(session('success'))
    <div style="background:rgba(46,204,113,0.1);border:1px solid rgba(46,204,113,0.3);color:#58d68d;border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:0.85rem;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="background:rgba(231,76,60,0.1);border:1px solid rgba(231,76,60,0.3);color:#f1948a;border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:0.85rem;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
    </div>
@endif

<div class="master-layout">
    <div class="section-card">
        <div class="section-header">
            <h6><i class="bi bi-award-fill me-2" style="color:#f39c12;"></i>Master Tugas Tambahan</h6>
            <span style="font-size:0.8rem;color:var(--text-muted);">{{ count($tugasTambahanList) }} tugas terdaftar</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Nama Tugas Tambahan</th>
                        <th>Kategori</th>
                        <th>Nominal Honor/Gaji (Rp)</th>
                        <th style="width:120px;">Status</th>
                        <th style="width:120px;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tugasTambahanList as $index => $item)
                    <tr>
                        <td style="color:var(--text-muted);font-size:0.8rem;">{{ $index + 1 }}</td>
                        <td style="font-weight:600;color:var(--text-light);">
                            <i class="bi bi-award me-2" style="color:#f39c12;"></i> {{ $item->nama }}
                        </td>
                        <td>
                            @if($item->kategori == 'Tugas Tambahan Melekat')
                                <span class="badge bg-primary">Melekat</span>
                            @elseif($item->kategori == 'Tim Pokja')
                                <span class="badge bg-info">Tim Pokja</span>
                            @else
                                <span class="badge bg-secondary">{{ $item->kategori ?? 'Umum' }}</span>
                            @endif
                        </td>
                        <td>
                            Rp {{ number_format($item->nominal_gaji, 0, ',', '.') }}
                        </td>
                        <td>
                            @if($item->is_aktif)
                                <span style="color:#58d68d;font-size:0.75rem;font-weight:600;"><i class="bi bi-check-circle-fill me-1"></i>Aktif</span>
                            @else
                                <span style="color:#f1948a;font-size:0.75rem;font-weight:600;"><i class="bi bi-x-circle-fill me-1"></i>Nonaktif</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <div style="display:inline-flex; gap:6px; align-items:center;">
                                <button type="button" class="action-btn btn-edit" title="Edit Tugas Tambahan"
                                        onclick="openEditModal({{ $item->id }}, '{{ addslashes($item->nama) }}', '{{ $item->kategori }}', {{ $item->nominal_gaji }}, {{ $item->is_aktif ? 1 : 0 }})">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <form method="POST" action="{{ route('superadmin.master.tugas-tambahan.destroy', $item) }}"
                                      onsubmit="return confirm('Hapus tugas tambahan {{ addslashes($item->nama) }}?')" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn btn-del" title="Hapus"><i class="bi bi-trash-fill"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center;padding:48px;color:var(--text-muted);">
                            <i class="bi bi-award d-block mb-2" style="font-size:2.5rem;opacity:0.2;"></i>
                            Belum ada tugas tambahan terdaftar
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Form Tambah Master Tugas Tambahan (Simpel) -->
    <div>
        <div class="form-card">
            <h6 style="font-size:0.92rem;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                <i class="bi bi-plus-circle-fill" style="color:#58d68d;"></i> Tambah Tugas Tambahan
            </h6>
            <form method="POST" action="{{ route('superadmin.master.tugas-tambahan.store') }}">
                @csrf
                <label class="field-label">Nama Tugas Tambahan <span class="field-required">*</span></label>
                <input type="text" name="nama" class="form-input {{ $errors->has('nama') ? 'is-invalid' : '' }}" value="{{ old('nama') }}" placeholder="Contoh: Pembina OSIS, Kepala Laboratorium" required>
                @error('nama')<div class="field-error">{{ $message }}</div>@enderror

                <label class="field-label">Kategori <span class="field-required">*</span></label>
                <select name="kategori" class="form-input" required>
                    <option value="Tugas Tambahan Melekat" {{ old('kategori') == 'Tugas Tambahan Melekat' ? 'selected' : '' }}>Tugas Tambahan Melekat</option>
                    <option value="Tim Pokja" {{ old('kategori') == 'Tim Pokja' ? 'selected' : '' }}>Tim Pokja</option>
                    <option value="Umum" {{ old('kategori') == 'Umum' ? 'selected' : '' }}>Umum</option>
                </select>

                <label class="field-label">Nominal Honor / Gaji (Rp) <span class="field-required">*</span></label>
                <input type="number" name="nominal_gaji" class="form-input" value="{{ old('nominal_gaji', 0) }}" required min="0">

                <button type="submit" class="btn-save" style="margin-top:8px;"><i class="bi bi-check-circle-fill"></i> Simpan Tugas Tambahan</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Tugas Tambahan (Simpel) -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editForm" method="POST" action="">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2" style="color:#5dade2;"></i>Edit Tugas Tambahan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="field-label">Nama Tugas Tambahan <span class="field-required">*</span></label>
                    <input type="text" name="nama" id="edit_nama" class="form-input" required>

                    <label class="field-label">Kategori <span class="field-required">*</span></label>
                    <select name="kategori" id="edit_kategori" class="form-input" required>
                        <option value="Tugas Tambahan Melekat">Tugas Tambahan Melekat</option>
                        <option value="Tim Pokja">Tim Pokja</option>
                        <option value="Umum">Umum</option>
                    </select>

                    <label class="field-label">Nominal Honor / Gaji (Rp) <span class="field-required">*</span></label>
                    <input type="number" name="nominal_gaji" id="edit_nominal_gaji" class="form-input" required min="0">

                    <label class="d-flex align-items-center gap-2" style="cursor:pointer;margin-top:6px;">
                        <input type="checkbox" name="is_aktif" value="1" id="edit_is_aktif">
                        <span style="font-size:0.86rem;">Status Aktif (Tampil di Pilihan Guru)</span>
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-save" style="background:rgba(255,255,255,0.1);color:var(--text-light);" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-save"><i class="bi bi-check-circle-fill"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openEditModal(id, nama, kategori, nominal, isAktif) {
    document.getElementById('editForm').action = "{{ url('/superadmin/master/tugas-tambahan') }}/" + id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_kategori').value = kategori || 'Umum';
    document.getElementById('edit_nominal_gaji').value = nominal;
    document.getElementById('edit_is_aktif').checked = isAktif == 1;

    const modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}
</script>
@endsection
