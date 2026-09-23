@extends('layouts.app')
@section('title', 'Data Master Kelas')
@section('page-title', 'Data Master - Kelas')

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
.field-label { font-size: 0.82rem; font-weight: 500; color: var(--text-muted); margin-bottom: 7px; display: block; }
.field-required { color: var(--accent-gold); }
.form-input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: var(--text-light); border-radius: 10px; padding: 10px 14px; font-family: 'Poppins', sans-serif; font-size: 0.85rem; margin-bottom: 14px; transition: all 0.25s ease; }
.form-input:focus { outline: none; border-color: var(--primary-light); background: rgba(255,255,255,0.07); box-shadow: 0 0 0 3px rgba(45,138,78,0.15); }
.form-input option { background: #1a2e24; }
.form-input.is-invalid { border-color: rgba(231,76,60,0.6); }
.field-error { font-size: 0.76rem; color: #f1948a; margin-top: -10px; margin-bottom: 10px; }
.btn-save { display: inline-flex; align-items: center; gap: 7px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border: none; color: white; padding: 10px 22px; border-radius: 10px; font-size: 0.86rem; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif; width: 100%; justify-content: center; transition: all 0.25s; }
.btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(26,86,50,0.4); }
.action-btn { display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 500; cursor: pointer; border: none; font-family: 'Poppins', sans-serif; text-decoration: none; transition: all 0.2s; }
.btn-edit { background: rgba(52, 152, 219, 0.12); color: #5dade2; border: 1px solid rgba(52, 152, 219, 0.25); }
.btn-edit:hover { background: rgba(52, 152, 219, 0.25); color: #7fb3d5; }
.btn-del { background: rgba(231,76,60,0.1); color: #f1948a; border: 1px solid rgba(231,76,60,0.2); }
.btn-del:hover { background: rgba(231,76,60,0.2); }
.filter-bar { display: flex; gap: 8px; padding: 12px 24px 0; flex-wrap: wrap; }
.filter-select { background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: var(--text-light); border-radius: 9px; padding: 7px 12px; font-family: 'Poppins', sans-serif; font-size: 0.82rem; }
.filter-select option { background: #1a2e24; }
.btn-filter { padding: 7px 16px; border-radius: 9px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08); color: var(--text-light); font-size: 0.82rem; cursor: pointer; font-family: 'Poppins', sans-serif; }
.tingkat-badge { background: rgba(155,89,182,0.15); color: #a569bd; border: 1px solid rgba(155,89,182,0.3); font-size: 0.75rem; padding: 3px 10px; border-radius: 6px; font-weight: 700; }
.nav-master { display: flex; gap: 6px; margin-bottom: 20px; flex-wrap: wrap; }
.nav-master a { padding: 8px 16px; border-radius: 10px; font-size: 0.82rem; font-weight: 500; text-decoration: none; border: 1px solid var(--border-color); color: var(--text-muted); transition: all 0.2s; }
.nav-master a.active, .nav-master a:hover { background: var(--bg-card); color: var(--text-light); border-color: var(--primary-light); }
.guru-info-box { background: rgba(46,204,113,0.08); border: 1px solid rgba(46,204,113,0.25); border-radius: 10px; padding: 10px 14px; font-size: 0.78rem; margin-top: -6px; margin-bottom: 14px; }
.modal-content { background-color: #14241b !important; border: 1px solid rgba(255,255,255,0.18) !important; border-radius: 20px; color: var(--text-light); box-shadow: 0 15px 50px rgba(0,0,0,0.9); }
.modal-backdrop.show { opacity: 0.75 !important; }
@endsection

@section('content')
<div class="nav-master" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:20px;">
    <div style="display:flex; gap:6px; flex-wrap:wrap;">
        <a href="{{ route('superadmin.master.sekolah') }}"><i class="bi bi-building me-1"></i> Pengaturan Sekolah</a>
        <a href="{{ route('superadmin.master.tahun-ajaran') }}"><i class="bi bi-calendar-check me-1"></i> Tahun Ajaran</a>
        <a href="{{ route('superadmin.master.jurusan') }}"><i class="bi bi-diagram-3 me-1"></i> Jurusan</a>
        <a href="{{ route('superadmin.master.kelas') }}" class="active"><i class="bi bi-collection me-1"></i> Kelas</a>
        <a href="{{ route('superadmin.master.tugas-tambahan') }}"><i class="bi bi-award me-1"></i> Tugas Tambahan</a>
        <a href="{{ route('superadmin.rombel.index') }}" style="background:rgba(241,196,15,0.12);color:#f39c12;border-color:rgba(241,196,15,0.3);"><i class="bi bi-grid-3x3-gap me-1"></i> Pengaturan Rombel</a>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="{{ route('superadmin.master.export') }}" style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:8px; font-size:0.8rem; font-weight:600; text-decoration:none; background:rgba(52,152,219,0.1); color:#5dade2; border:1px solid rgba(52,152,219,0.3); transition:all 0.2s;">
            <i class="bi bi-download"></i> Export Data
        </a>
        <a href="{{ route('superadmin.master.import') }}" style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:8px; font-size:0.8rem; font-weight:600; text-decoration:none; background:rgba(46,204,113,0.1); color:#58d68d; border:1px solid rgba(46,204,113,0.3); transition:all 0.2s;">
            <i class="bi bi-upload"></i> Import Data
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
            <h6><i class="bi bi-collection-fill me-2" style="color:#a569bd;"></i>Data Kelas</h6>
            <span style="font-size:0.8rem;color:var(--text-muted);">{{ $kelas->total() }} kelas terdaftar</span>
        </div>
        <form method="GET" class="filter-bar">
            <select name="jurusan_id" class="filter-select">
                <option value="">Semua Jurusan</option>
                @foreach($jurusans as $j)<option value="{{ $j->id }}" {{ request('jurusan_id')==$j->id ? 'selected' : '' }}>{{ $j->singkatan }}</option>@endforeach
            </select>
            <select name="tahun_ajaran_id" class="filter-select">
                <option value="">Semua Tahun</option>
                @foreach($tahunAjarans as $ta)<option value="{{ $ta->id }}" {{ request('tahun_ajaran_id')==$ta->id ? 'selected' : '' }}>{{ $ta->nama }}</option>@endforeach
            </select>
            <button type="submit" class="btn-filter"><i class="bi bi-funnel"></i> Filter</button>
            @if(request()->filled('jurusan_id') || request()->filled('tahun_ajaran_id'))
                <a href="{{ route('superadmin.master.kelas') }}" class="btn-filter" style="text-decoration:none; display:inline-flex; align-items:center;">Reset</a>
            @endif
        </form>
        <div style="overflow-x:auto;">
            <table class="data-table" style="margin-top:12px;">
                <thead><tr><th>Tingkat</th><th>Nama Kelas</th><th>Jurusan</th><th>Tahun Ajaran</th><th>Wali Kelas</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($kelas as $k)
                    <tr>
                        <td><span class="tingkat-badge">{{ $k->tingkat }}</span></td>
                        <td style="font-weight:600;">{{ $k->nama_kelas ?? $k->nama }}</td>
                        <td style="color:var(--text-muted);font-size:0.82rem;">{{ $k->jurusan?->singkatan ?? '-' }}</td>
                        <td style="color:var(--text-muted);font-size:0.82rem;">{{ $k->tahunAjaran?->nama ?? '-' }}</td>
                        <td style="font-size:0.82rem;">
                            @if($k->waliKelasGuru)
                                <span style="font-weight:600;color:var(--text-light);"><i class="bi bi-person-badge me-1" style="color:var(--primary-light);"></i>{{ $k->waliKelasGuru->name }}</span>
                                <span style="display:block;font-size:0.72rem;color:var(--accent-gold);"><i class="bi bi-arrow-repeat me-1"></i>Tersinkron di profil guru</span>
                            @elseif(!empty($k->wali_kelas))
                                <span style="color:var(--text-muted);">{{ $k->wali_kelas }}</span>
                            @else
                                <span style="color:var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <a href="{{ route('superadmin.rombel.show', $k) }}" class="action-btn" style="background:rgba(241,196,15,0.12);color:#f39c12;border:1px solid rgba(241,196,15,0.25);" title="Kelola Siswa Rombel">
                                    <i class="bi bi-people-fill"></i> Rombel
                                </a>
                                <button type="button" class="action-btn btn-edit" title="Edit Kelas"
                                        onclick="openEditKelasModal({{ $k->id }}, '{{ addslashes($k->nama_kelas ?? $k->nama) }}', '{{ $k->tingkat }}', {{ $k->jurusan_id }}, {{ $k->tahun_ajaran_id }}, '{{ $k->wali_kelas_id ?? '' }}', '{{ addslashes($k->wali_kelas ?? '') }}')">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <form method="POST" action="{{ route('superadmin.master.kelas.destroy', $k) }}"
                                      onsubmit="return confirm('Hapus kelas {{ addslashes($k->nama_kelas ?? $k->nama) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn btn-del" title="Hapus Kelas"><i class="bi bi-trash-fill"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:36px;color:var(--text-muted);">Belum ada data kelas</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($kelas->hasPages())
        <div style="padding:14px 20px;border-top:1px solid var(--border-color);">{{ $kelas->links() }}</div>
        @endif
    </div>

    {{-- Form Tambah Kelas --}}
    <div>
        <form method="POST" action="{{ route('superadmin.master.kelas.store') }}" class="form-card" id="formTambahKelas">
            @csrf
            <h6 style="font-weight:700;margin-bottom:20px;font-size:0.95rem;"><i class="bi bi-plus-circle-fill me-2" style="color:#a569bd;"></i>Tambah Kelas</h6>
            
            <label class="field-label">Jurusan <span class="field-required">*</span></label>
            <select name="jurusan_id" class="form-input {{ $errors->has('jurusan_id') ? 'is-invalid' : '' }}" required>
                <option value="">-- Pilih Jurusan --</option>
                @foreach($jurusans as $j)<option value="{{ $j->id }}" {{ old('jurusan_id')==$j->id ? 'selected' : '' }}>{{ $j->singkatan }} - {{ $j->nama }}</option>@endforeach
            </select>
            @error('jurusan_id')<div class="field-error">{{ $message }}</div>@enderror

            <label class="field-label">Tahun Ajaran <span class="field-required">*</span></label>
            <select name="tahun_ajaran_id" class="form-input" required>
                <option value="">-- Pilih Tahun --</option>
                @foreach($tahunAjarans as $ta)<option value="{{ $ta->id }}" {{ old('tahun_ajaran_id')==$ta->id || $ta->is_aktif ? 'selected' : '' }}>{{ $ta->nama }}</option>@endforeach
            </select>

            <label class="field-label">Tingkat <span class="field-required">*</span></label>
            <select name="tingkat" class="form-input" required>
                <option value="">-- Pilih Tingkat --</option>
                <option value="X" {{ old('tingkat')==='X' ? 'selected' : '' }}>X (Sepuluh)</option>
                <option value="XI" {{ old('tingkat')==='XI' ? 'selected' : '' }}>XI (Sebelas)</option>
                <option value="XII" {{ old('tingkat')==='XII' ? 'selected' : '' }}>XII (Dua Belas)</option>
            </select>

            <label class="field-label">Nama Kelas <span class="field-required">*</span></label>
            <input type="text" name="nama_kelas" id="inputNamaKelasTambah" class="form-input {{ $errors->has('nama_kelas') ? 'is-invalid' : '' }}" value="{{ old('nama_kelas') }}" placeholder="Contoh: X AKL" required>
            @error('nama_kelas')<div class="field-error">{{ $message }}</div>@enderror

            <label class="field-label">Wali Kelas (Pilih Guru)</label>
            <select name="wali_kelas_id" id="selectWaliKelasTambah" class="form-input mb-2" onchange="handleGuruSelection(this, 'inputWaliKelasTambah', 'infoBoxTambah', 'inputNamaKelasTambah')">
                <option value="">-- Pilih Guru Sebagai Wali Kelas --</option>
                @foreach($gurus ?? [] as $g)
                    <option value="{{ $g->id }}" 
                            data-name="{{ $g->name }}"
                            data-jabatan="{{ $g->jabatan_utama ?? 'Guru Pengajar' }}"
                            data-tugas="{{ implode(', ', $g->tugas_tambahan ?? []) }}"
                            {{ old('wali_kelas_id')==$g->id ? 'selected' : '' }}>
                        {{ $g->name }} (NUPTK: {{ $g->nip ?? '-' }})
                    </option>
                @endforeach
            </select>

            {{-- Live Guru Jabatan Box --}}
            <div id="infoBoxTambah" class="guru-info-box d-none">
                <div style="font-weight:600; color:var(--primary-lighter);"><i class="bi bi-person-check-fill me-1"></i> <span class="guru-name">-</span></div>
                <div style="color:var(--text-muted); font-size:0.75rem; margin-top:2px;">
                    Jabatan Utama: <strong class="guru-jabatan" style="color:var(--text-light);">-</strong>
                </div>
                <div style="color:var(--text-muted); font-size:0.75rem;">
                    Tugas saat ini: <span class="guru-tugas" style="color:var(--accent-gold);">-</span>
                </div>
                <div style="color:var(--accent-gold-light); font-size:0.74rem; margin-top:5px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:4px;">
                    <i class="bi bi-arrow-repeat me-1"></i> Otomatis disinkronkan ke profil: <strong>Wali Kelas <span class="kelas-target-name"></span></strong>
                </div>
            </div>

            <label class="field-label">Nama Wali Kelas Manual (Opsional)</label>
            <input type="text" name="wali_kelas" id="inputWaliKelasTambah" class="form-input" value="{{ old('wali_kelas') }}" placeholder="Terisi otomatis jika memilih guru">

            <button type="submit" class="btn-save"><i class="bi bi-check-circle-fill"></i> Simpan Kelas</button>
        </form>
    </div>
</div>

{{-- Modal Edit Kelas --}}
<div class="modal fade" id="editKelasModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--bg-sidebar); border:1px solid var(--border-color); border-radius:18px; color:var(--text-light);">
            <form method="POST" id="formEditKelas">
                @csrf
                @method('PUT')
                <div class="modal-header" style="border-bottom:1px solid var(--border-color); padding:18px 24px;">
                    <h5 class="modal-title" style="font-size:1.05rem; font-weight:700;"><i class="bi bi-pencil-square me-2" style="color:#5dade2;"></i>Edit Data Kelas</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding:22px 24px;">
                    <label class="field-label">Jurusan <span class="field-required">*</span></label>
                    <select name="jurusan_id" id="editJurusanId" class="form-input" required>
                        @foreach($jurusans as $j)<option value="{{ $j->id }}">{{ $j->singkatan }} - {{ $j->nama }}</option>@endforeach
                    </select>

                    <label class="field-label">Tahun Ajaran <span class="field-required">*</span></label>
                    <select name="tahun_ajaran_id" id="editTahunAjaranId" class="form-input" required>
                        @foreach($tahunAjarans as $ta)<option value="{{ $ta->id }}">{{ $ta->nama }}</option>@endforeach
                    </select>

                    <label class="field-label">Tingkat <span class="field-required">*</span></label>
                    <select name="tingkat" id="editTingkat" class="form-input" required>
                        <option value="X">X (Sepuluh)</option>
                        <option value="XI">XI (Sebelas)</option>
                        <option value="XII">XII (Dua Belas)</option>
                    </select>

                    <label class="field-label">Nama Kelas <span class="field-required">*</span></label>
                    <input type="text" name="nama_kelas" id="editNamaKelas" class="form-input" placeholder="Contoh: X AKL" required>

                    <label class="field-label">Wali Kelas (Pilih Guru)</label>
                    <select name="wali_kelas_id" id="editWaliKelasId" class="form-input mb-2" onchange="handleGuruSelection(this, 'editWaliKelasManual', 'infoBoxEdit', 'editNamaKelas')">
                        <option value="">-- Kosongkan / Tanpa Wali Kelas --</option>
                        @foreach($gurus ?? [] as $g)
                            <option value="{{ $g->id }}" 
                                    data-name="{{ $g->name }}"
                                    data-jabatan="{{ $g->jabatan_utama ?? 'Guru Pengajar' }}"
                                    data-tugas="{{ implode(', ', $g->tugas_tambahan ?? []) }}">
                                {{ $g->name }} (NUPTK: {{ $g->nip ?? '-' }})
                            </option>
                        @endforeach
                    </select>

                    {{-- Live Guru Jabatan Box for Edit --}}
                    <div id="infoBoxEdit" class="guru-info-box d-none">
                        <div style="font-weight:600; color:var(--primary-lighter);"><i class="bi bi-person-check-fill me-1"></i> <span class="guru-name">-</span></div>
                        <div style="color:var(--text-muted); font-size:0.75rem; margin-top:2px;">
                            Jabatan Utama: <strong class="guru-jabatan" style="color:var(--text-light);">-</strong>
                        </div>
                        <div style="color:var(--text-muted); font-size:0.75rem;">
                            Tugas saat ini: <span class="guru-tugas" style="color:var(--accent-gold);">-</span>
                        </div>
                        <div style="color:var(--accent-gold-light); font-size:0.74rem; margin-top:5px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:4px;">
                            <i class="bi bi-arrow-repeat me-1"></i> Jabatan guru akan disinkronkan menjadi: <strong>Wali Kelas <span class="kelas-target-name"></span></strong>
                        </div>
                    </div>

                    <label class="field-label">Nama Wali Kelas Manual (Opsional)</label>
                    <input type="text" name="wali_kelas" id="editWaliKelasManual" class="form-input" placeholder="Terisi otomatis jika memilih guru">
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border-color); padding:14px 24px;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="border-radius:10px; padding:8px 16px;">Batal</button>
                    <button type="submit" class="btn-save" style="width:auto; padding:8px 20px;"><i class="bi bi-check-circle-fill me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function handleGuruSelection(selectEl, manualInputId, infoBoxId, namaKelasInputId) {
        const manualInput = document.getElementById(manualInputId);
        const infoBox = document.getElementById(infoBoxId);
        const namaKelasInput = document.getElementById(namaKelasInputId);
        const namaKelas = namaKelasInput ? namaKelasInput.value.trim() : '';

        if (!selectEl.value) {
            if (infoBox) infoBox.classList.add('d-none');
            return;
        }

        const opt = selectEl.options[selectEl.selectedIndex];
        const name = opt.getAttribute('data-name') || '';
        const jabatan = opt.getAttribute('data-jabatan') || 'Guru Pengajar';
        const tugas = opt.getAttribute('data-tugas') || 'Belum ada tugas tambahan';

        if (manualInput) {
            manualInput.value = name;
        }

        if (infoBox) {
            infoBox.classList.remove('d-none');
            infoBox.querySelector('.guru-name').textContent = name;
            infoBox.querySelector('.guru-jabatan').textContent = jabatan;
            infoBox.querySelector('.guru-tugas').textContent = tugas || 'Tidak ada';
            infoBox.querySelector('.kelas-target-name').textContent = namaKelas || '(Nama Kelas)';
        }
    }

    function openEditKelasModal(id, namaKelas, tingkat, jurusanId, tahunAjaranId, waliKelasId, waliKelasManual) {
        const form = document.getElementById('formEditKelas');
        form.action = "{{ url('superadmin/master/kelas') }}/" + id;

        document.getElementById('editNamaKelas').value = namaKelas;
        document.getElementById('editTingkat').value = tingkat;
        document.getElementById('editJurusanId').value = jurusanId;
        document.getElementById('editTahunAjaranId').value = tahunAjaranId;
        document.getElementById('editWaliKelasManual').value = waliKelasManual || '';

        const selectWali = document.getElementById('editWaliKelasId');
        selectWali.value = waliKelasId || '';

        handleGuruSelection(selectWali, 'editWaliKelasManual', 'infoBoxEdit', 'editNamaKelas');

        const modal = new bootstrap.Modal(document.getElementById('editKelasModal'));
        modal.show();
    }
</script>
@endsection
