@extends('layouts.app')
@section('title', 'Data Master Ekstrakurikuler')
@section('page-title', 'Data Master - Ekstrakurikuler')

@php
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag();
@endphp

@section('dashboard-styles')
.master-layout { display: grid; grid-template-columns: 1fr 360px; gap: 20px; }
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
.field-label { font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 7px; display: block; }
.field-required { color: #e11d48; }
.form-input { width: 100%; background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; border-radius: 10px; padding: 10px 14px; font-family: 'Poppins', sans-serif; font-size: 0.85rem; margin-bottom: 14px; transition: all 0.25s ease; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: var(--primary); background: #ffffff; box-shadow: 0 0 0 3px rgba(5,150,105,0.15); color: #0f172a; }
.form-input option { background: #ffffff; color: #0f172a; }
.form-input.is-invalid { border-color: rgba(231,76,60,0.6); }
.field-error { font-size: 0.76rem; color: #e11d48; margin-top: -10px; margin-bottom: 10px; }
.btn-save { display: inline-flex; align-items: center; gap: 7px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border: none; color: white; padding: 10px 22px; border-radius: 10px; font-size: 0.86rem; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif; width: 100%; justify-content: center; transition: all 0.25s; }
.btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(5,150,105,0.3); }
.action-btn { display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 500; cursor: pointer; border: none; font-family: 'Poppins', sans-serif; text-decoration: none; transition: all 0.2s; }
.btn-edit { background: rgba(52, 152, 219, 0.12); color: #2563eb; border: 1px solid rgba(52, 152, 219, 0.25); }
.btn-edit:hover { background: rgba(52, 152, 219, 0.25); color: #1d4ed8; }
.btn-del { background: rgba(231,76,60,0.1); color: #e11d48; border: 1px solid rgba(231,76,60,0.2); }
.btn-del:hover { background: rgba(231,76,60,0.2); }
.filter-bar { display: flex; gap: 8px; padding: 12px 24px 0; flex-wrap: wrap; }
.filter-select { background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; border-radius: 9px; padding: 7px 12px; font-family: 'Poppins', sans-serif; font-size: 0.82rem; }
.filter-select option { background: #ffffff; color: #0f172a; }
.btn-filter { padding: 7px 16px; border-radius: 9px; background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; font-size: 0.82rem; cursor: pointer; font-family: 'Poppins', sans-serif; font-weight: 500; }
.nav-master { display: flex; gap: 6px; margin-bottom: 20px; flex-wrap: wrap; }
.nav-master a { padding: 8px 16px; border-radius: 10px; font-size: 0.82rem; font-weight: 500; text-decoration: none; border: 1px solid var(--border-color); color: var(--text-muted); transition: all 0.2s; }
.nav-master a.active, .nav-master a:hover { background: var(--bg-card); color: var(--text-light); border-color: var(--primary-light); }
.guru-info-box { background: rgba(46,204,113,0.08); border: 1px solid rgba(46,204,113,0.25); border-radius: 10px; padding: 10px 14px; font-size: 0.78rem; margin-top: -6px; margin-bottom: 14px; color: #059669; }
.badge-status { font-size: 0.72rem; padding: 3px 8px; border-radius: 6px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.badge-active { background: rgba(46,204,113,0.15); color: #059669; border: 1px solid rgba(46,204,113,0.3); }
.badge-inactive { background: rgba(149,165,166,0.15); color: #64748b; border: 1px solid rgba(149,165,166,0.3); }
.modal-content { background-color: #ffffff !important; border: 1px solid #e2e8f0 !important; border-radius: 20px; color: #0f172a !important; box-shadow: 0 25px 60px rgba(0,0,0,0.2) !important; }
.modal-header { border-bottom: 1px solid #e2e8f0; padding: 18px 24px; background: #ffffff; border-top-left-radius: 20px; border-top-right-radius: 20px; }
.modal-title { font-weight: 700; color: #0f172a !important; }
.modal-body { padding: 24px; background: #ffffff; }
.modal-footer { border-top: 1px solid #e2e8f0; padding: 16px 24px; background: #f8fafc; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px; }
.modal-backdrop.show { opacity: 0.5 !important; }
@endsection

@section('content')
{{-- Navigation Tabs Master Data --}}
<div class="nav-master" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:20px;">
    <div style="display:flex; gap:6px; flex-wrap:wrap;">
        <a href="{{ route('superadmin.master.sekolah') }}"><i class="bi bi-building me-1"></i> Pengaturan Sekolah</a>
        <a href="{{ route('superadmin.master.tahun-ajaran') }}"><i class="bi bi-calendar-check me-1"></i> Tahun Ajaran</a>
        <a href="{{ route('superadmin.master.jurusan') }}"><i class="bi bi-diagram-3 me-1"></i> Jurusan</a>
        <a href="{{ route('superadmin.master.kelas') }}"><i class="bi bi-collection me-1"></i> Kelas</a>
        <a href="{{ route('superadmin.master.tugas-tambahan') }}"><i class="bi bi-award me-1"></i> Tugas Tambahan</a>
        <a href="{{ route('admin.ekstrakurikuler.index') }}" class="active"><i class="bi bi-stars me-1"></i> Ekstrakurikuler</a>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="{{ route('admin.ekstrakurikuler.monitoring') }}" style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:8px; font-size:0.8rem; font-weight:600; text-decoration:none; background:rgba(52,152,219,0.1); color:#5dade2; border:1px solid rgba(52,152,219,0.3); transition:all 0.2s;">
            <i class="bi bi-calendar-range"></i> Monitoring Mingguan
        </a>
        <a href="{{ route('admin.ekstrakurikuler.rekap-kelas') }}" style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:8px; font-size:0.8rem; font-weight:600; text-decoration:none; background:rgba(46,204,113,0.1); color:#58d68d; border:1px solid rgba(46,204,113,0.3); transition:all 0.2s;">
            <i class="bi bi-card-checklist"></i> Rekap Per Kelas
        </a>
    </div>
</div>

@if(session('success'))
    <div style="background:rgba(46,204,113,0.1);border:1px solid rgba(46,204,113,0.3);color:#58d68d;border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:0.85rem;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
@endif

@if(isset($errors) && $errors->any())
    <div style="background:rgba(231,76,60,0.1);border:1px solid rgba(231,76,60,0.3);color:#f1948a;border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:0.85rem;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
    </div>
@endif

<div class="master-layout">
    {{-- SISI KIRI: TABEL DATA EKSTRAKURIKULER --}}
    <div class="section-card">
        <div class="section-header">
            <h6><i class="bi bi-stars me-2" style="color:var(--accent-gold);"></i>Data Ekstrakurikuler</h6>
            <span style="font-size:0.8rem;color:var(--text-muted);">{{ $eskuls->count() }} eskul terdaftar ({{ $totalEskulAktif }} aktif)</span>
        </div>

        {{-- Filter & Pencarian --}}
        <form method="GET" action="{{ route('admin.ekstrakurikuler.index') }}" class="filter-bar">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / kode / tempat..." class="filter-select" style="min-width:200px;">
            <select name="status" class="filter-select">
                <option value="">Semua Status</option>
                <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif Saja</option>
                <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            <button type="submit" class="btn-filter"><i class="bi bi-funnel"></i> Filter</button>
            @if(request()->filled('q') || request()->filled('status'))
                <a href="{{ route('admin.ekstrakurikuler.index') }}" class="btn-filter" style="text-decoration:none; display:inline-flex; align-items:center;">Reset</a>
            @endif
        </form>

        <div style="overflow-x:auto;">
            <table class="data-table" style="margin-top:12px;">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Ekstrakurikuler</th>
                        <th>Jadwal & Tempat</th>
                        <th>Guru Pembina</th>
                        <th>Anggota</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eskuls as $eskul)
                    <tr>
                        <td>
                            <span class="badge" style="background:rgba(52,152,219,0.15);color:#5dade2;border:1px solid rgba(52,152,219,0.3);font-size:0.75rem;padding:3px 8px;border-radius:6px;font-weight:700;">
                                {{ $eskul->kode ?: 'ESK' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.ekstrakurikuler.show', $eskul->id) }}" style="font-weight:600;color:var(--text-light);text-decoration:none;" class="hover-underline">
                                {{ $eskul->nama }}
                            </a>
                            @if($eskul->deskripsi)
                                <div style="font-size:0.75rem;color:var(--text-muted);max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $eskul->deskripsi }}
                                </div>
                            @endif
                        </td>
                        <td style="font-size:0.82rem;color:var(--text-muted);">
                            <div style="color:var(--text-light);font-weight:500;">
                                <i class="bi bi-clock me-1" style="color:#38bdf8;"></i>{{ $eskul->hari }}, {{ $eskul->jam_mulai ? substr($eskul->jam_mulai,0,5) : '' }} - {{ $eskul->jam_selesai ? substr($eskul->jam_selesai,0,5) : '' }}
                            </div>
                            @if($eskul->tempat)
                                <div style="font-size:0.75rem;"><i class="bi bi-geo-alt me-1"></i>{{ $eskul->tempat }}</div>
                            @endif
                        </td>
                        <td style="font-size:0.82rem;">
                            @if($eskul->pembina)
                                <span style="font-weight:600;color:var(--text-light);">
                                    <i class="bi bi-person-workspace me-1" style="color:var(--primary-light);"></i>{{ $eskul->pembina->name }}
                                </span>
                                <span style="display:block;font-size:0.72rem;color:var(--accent-gold);">
                                    <i class="bi bi-arrow-repeat me-1"></i>Tersinkron di tugas tambahan
                                </span>
                            @else
                                <span style="color:var(--text-muted);">- Belum Ditugaskan -</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge" style="background:rgba(46,204,113,0.15);color:#58d68d;border:1px solid rgba(46,204,113,0.3);font-size:0.75rem;padding:3px 8px;border-radius:6px;font-weight:600;">
                                <i class="bi bi-people-fill me-1"></i>{{ $eskul->total_anggota ?? 0 }} Siswa
                            </span>
                        </td>
                        <td>
                            @if($eskul->is_aktif)
                                <span class="badge-status badge-active">
                                    <span style="width:6px;height:6px;border-radius:50%;background:#58d68d;display:inline-block;"></span> Aktif
                                </span>
                            @else
                                <span class="badge-status badge-inactive">
                                    <span style="width:6px;height:6px;border-radius:50%;background:#bdc3c7;display:inline-block;"></span> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <a href="{{ route('admin.ekstrakurikuler.show', $eskul->id) }}" class="action-btn" style="background:rgba(241,196,15,0.12);color:#f39c12;border:1px solid rgba(241,196,15,0.25);" title="Kelola Anggota Siswa & Presensi">
                                    <i class="bi bi-people-fill"></i> Kelola
                                </a>
                                <button type="button" class="action-btn btn-edit" title="Edit Ekstrakurikuler"
                                        onclick="openEditEskulModal({{ $eskul->id }}, '{{ addslashes($eskul->nama) }}', '{{ addslashes($eskul->kode ?? '') }}', '{{ $eskul->hari }}', '{{ $eskul->jam_mulai ? substr($eskul->jam_mulai,0,5) : '' }}', '{{ $eskul->jam_selesai ? substr($eskul->jam_selesai,0,5) : '' }}', '{{ addslashes($eskul->tempat ?? '') }}', '{{ $eskul->pembina_guru_id ?? '' }}', '{{ addslashes($eskul->deskripsi ?? '') }}', {{ $eskul->is_aktif ? 'true' : 'false' }})">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <form method="POST" action="{{ route('admin.ekstrakurikuler.destroy', $eskul->id) }}"
                                      onsubmit="return confirm('Hapus ekstrakurikuler {{ addslashes($eskul->nama) }}? Seluruh keanggotaan dan riwayat kegiatan akan terhapus.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn btn-del" title="Hapus"><i class="bi bi-trash-fill"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:36px;color:var(--text-muted);">
                            <i class="bi bi-folder-x" style="font-size:2rem;display:block;margin-bottom:8px;opacity:0.5;"></i>
                            Belum ada data ekstrakurikuler. Gunakan form di sebelah kanan untuk menambahkan eskul baru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- SISI KANAN: FORM TAMBAH EKSTRAKURIKULER (KAYA MENGINPUT KELAS) --}}
    <div>
        <form method="POST" action="{{ route('admin.ekstrakurikuler.store') }}" class="form-card" id="formTambahEskul">
            @csrf
            <h6 style="font-weight:700;margin-bottom:20px;font-size:0.95rem;">
                <i class="bi bi-plus-circle-fill me-2" style="color:var(--accent-gold);"></i>Tambah Ekstrakurikuler
            </h6>

            <label class="field-label">Nama Ekstrakurikuler <span class="field-required">*</span></label>
            <input type="text" name="nama" class="form-input {{ (isset($errors) && $errors->has('nama')) ? 'is-invalid' : '' }}" required
                   placeholder="Contoh: Pramuka, Paskibra, PMR, Futsal, Tari, Musik, Rohis..." value="{{ old('nama') }}">
            @error('nama')<div class="field-error">{{ $message }}</div>@enderror

            <label class="field-label">Kode Eskul (Opsional)</label>
            <input type="text" name="kode" class="form-input uppercase" placeholder="Contoh: ESK-PRA" value="{{ old('kode') }}">

            <label class="field-label">Hari Latihan <span class="field-required">*</span></label>
            <select name="hari" class="form-input" required>
                <option value="Senin" {{ old('hari') == 'Senin' ? 'selected' : '' }}>Senin</option>
                <option value="Selasa" {{ old('hari') == 'Selasa' ? 'selected' : '' }}>Selasa</option>
                <option value="Rabu" {{ old('hari') == 'Rabu' ? 'selected' : '' }}>Rabu</option>
                <option value="Kamis" {{ old('hari') == 'Kamis' ? 'selected' : '' }}>Kamis</option>
                <option value="Jumat" {{ old('hari', 'Jumat') == 'Jumat' ? 'selected' : '' }}>Jumat</option>
                <option value="Sabtu" {{ old('hari') == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                <option value="Minggu" {{ old('hari') == 'Minggu' ? 'selected' : '' }}>Minggu</option>
            </select>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                <div>
                    <label class="field-label">Jam Mulai</label>
                    <input type="time" name="jam_mulai" class="form-input" value="{{ old('jam_mulai', '15:30') }}">
                </div>
                <div>
                    <label class="field-label">Jam Selesai</label>
                    <input type="time" name="jam_selesai" class="form-input" value="{{ old('jam_selesai', '17:00') }}">
                </div>
            </div>

            <label class="field-label">Tempat / Lokasi Latihan</label>
            <input type="text" name="tempat" class="form-input" placeholder="Contoh: Lapangan Utama / Aula / Lab Komputer" value="{{ old('tempat') }}">

            <label class="field-label">Guru Pembina</label>
            <select name="pembina_guru_id" class="form-input">
                <option value="">-- Pilih Guru Pembina --</option>
                @foreach($gurus as $guru)
                    <option value="{{ $guru->id }}" {{ old('pembina_guru_id') == $guru->id ? 'selected' : '' }}>
                        {{ $guru->name }} ({{ $guru->nip ?: 'Guru' }})
                    </option>
                @endforeach
            </select>
            <div class="guru-info-box">
                <i class="bi bi-arrow-repeat me-1"></i>Penugasan pembina eskul otomatis tersinkron ke SK & Tugas Tambahan Guru.
            </div>

            <label class="field-label">Deskripsi & Tujuan</label>
            <textarea name="deskripsi" class="form-input" rows="2" placeholder="Keterangan singkat kegiatan eskul...">{{ old('deskripsi') }}</textarea>

            <div style="display:flex; align-items:center; gap:8px; margin-bottom:16px;">
                <input type="checkbox" name="is_aktif" value="1" id="is_aktif_new" checked style="accent-color:var(--primary-light); width:16px; height:16px;">
                <label for="is_aktif_new" style="font-size:0.84rem; color:var(--text-light); cursor:pointer;">Status Aktif Berjalan</label>
            </div>

            <button type="submit" class="btn-save">
                <i class="bi bi-check-circle-fill me-1"></i> Simpan Ekstrakurikuler
            </button>
        </form>
    </div>
</div>

{{-- MODAL EDIT EKSTRAKURIKULER --}}
<div class="modal fade" id="editEskulModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" style="font-weight:700;">
                    <i class="bi bi-pencil-square me-2" style="color:#5dade2;"></i>Edit Data Ekstrakurikuler
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditEskul" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" style="padding:24px;">
                    <label class="field-label">Nama Ekstrakurikuler <span class="field-required">*</span></label>
                    <input type="text" name="nama" id="editNama" class="form-input" required>

                    <label class="field-label">Kode Eskul</label>
                    <input type="text" name="kode" id="editKode" class="form-input uppercase">

                    <label class="field-label">Hari Latihan <span class="field-required">*</span></label>
                    <select name="hari" id="editHari" class="form-input" required>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                        <option value="Minggu">Minggu</option>
                    </select>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                        <div>
                            <label class="field-label">Jam Mulai</label>
                            <input type="time" name="jam_mulai" id="editJamMulai" class="form-input">
                        </div>
                        <div>
                            <label class="field-label">Jam Selesai</label>
                            <input type="time" name="jam_selesai" id="editJamSelesai" class="form-input">
                        </div>
                    </div>

                    <label class="field-label">Tempat / Lokasi</label>
                    <input type="text" name="tempat" id="editTempat" class="form-input">

                    <label class="field-label">Guru Pembina</label>
                    <select name="pembina_guru_id" id="editPembinaGuruId" class="form-input">
                        <option value="">-- Pilih Guru Pembina --</option>
                        @foreach($gurus as $guru)
                            <option value="{{ $guru->id }}">{{ $guru->name }} ({{ $guru->nip ?: 'Guru' }})</option>
                        @endforeach
                    </select>

                    <label class="field-label">Deskripsi</label>
                    <textarea name="deskripsi" id="editDeskripsi" class="form-input" rows="2"></textarea>

                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                        <input type="checkbox" name="is_aktif" value="1" id="editIsAktif" style="accent-color:var(--primary); width:16px; height:16px;">
                        <label for="editIsAktif" style="font-size:0.84rem; color:#334155; font-weight:500; cursor:pointer;">Status Aktif Berjalan</label>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #e2e8f0; padding:14px 24px; background:#f8fafc;">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal" style="border-radius:10px; padding:8px 18px; border:1px solid #cbd5e1; color:#475569; font-weight:600;">Batal</button>
                    <button type="submit" class="btn-save" style="width:auto; padding:8px 20px;"><i class="bi bi-check-circle-fill me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openEditEskulModal(id, nama, kode, hari, jamMulai, jamSelesai, tempat, pembinaId, deskripsi, isAktif) {
        const form = document.getElementById('formEditEskul');
        form.action = "{{ url('admin/ekstrakurikuler') }}/" + id;

        document.getElementById('editNama').value = nama;
        document.getElementById('editKode').value = kode || '';
        document.getElementById('editHari').value = hari || 'Jumat';
        document.getElementById('editJamMulai').value = jamMulai || '';
        document.getElementById('editJamSelesai').value = jamSelesai || '';
        document.getElementById('editTempat').value = tempat || '';
        document.getElementById('editPembinaGuruId').value = pembinaId || '';
        document.getElementById('editDeskripsi').value = deskripsi || '';
        document.getElementById('editIsAktif').checked = isAktif ? true : false;

        const modal = new bootstrap.Modal(document.getElementById('editEskulModal'));
        modal.show();
    }
</script>
@endsection
