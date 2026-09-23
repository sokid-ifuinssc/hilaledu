@extends('layouts.app')
@section('title', 'Data Master Jurusan')
@section('page-title', 'Data Master - Jurusan')

@section('dashboard-styles')
.master-layout { display: grid; grid-template-columns: 1fr 360px; gap: 20px; }
@media (max-width: 992px) {
    .master-layout { grid-template-columns: 1fr; }
}
.section-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 18px; overflow: hidden; }
.section-header { padding: 16px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
.section-header h6 { font-size: 0.95rem; font-weight: 700; margin: 0; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { padding: 12px 20px; text-align: left; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-muted); border-bottom: 1px solid var(--border-color); background: rgba(255,255,255,0.02); }
.data-table td { padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.03); font-size: 0.87rem; vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: rgba(255,255,255,0.02); }
.form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 18px; padding: 24px; }
.field-label { font-size: 0.82rem; font-weight: 500; color: var(--text-muted); margin-bottom: 7px; display: block; }
.field-required { color: var(--accent-gold); }
.form-input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: var(--text-light); border-radius: 10px; padding: 10px 14px; font-family: 'Poppins', sans-serif; font-size: 0.85rem; transition: all 0.25s ease; margin-bottom: 14px; }
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
.jurusan-code { background: rgba(212,168,67,0.12); color: #d4a843; padding: 3px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; font-family: monospace; }
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
        <a href="{{ route('superadmin.master.jurusan') }}" class="active"><i class="bi bi-diagram-3 me-1"></i> Jurusan</a>
        <a href="{{ route('superadmin.master.kelas') }}"><i class="bi bi-collection me-1"></i> Kelas</a>
        <a href="{{ route('superadmin.master.tugas-tambahan') }}"><i class="bi bi-award me-1"></i> Tugas Tambahan</a>
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

@if(session('success'))<div style="background:rgba(46,204,113,0.1);border:1px solid rgba(46,204,113,0.3);color:#58d68d;border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:0.85rem;"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>@endif
@if(session('error'))<div style="background:rgba(231,76,60,0.1);border:1px solid rgba(231,76,60,0.3);color:#f1948a;border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:0.85rem;"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>@endif

<div class="master-layout">
    {{-- Tabel Jurusan --}}
    <div class="section-card">
        <div class="section-header">
            <h6><i class="bi bi-diagram-3-fill me-2" style="color:#f1948a;"></i>Data Jurusan</h6>
            <span style="font-size:0.8rem;color:var(--text-muted);">{{ $jurusans->count() }} jurusan terdaftar</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead><tr><th>Kode</th><th>Nama Jurusan</th><th>Ketua Jurusan / Kaprog</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($jurusans as $j)
                    <tr>
                        <td><span class="jurusan-code">{{ $j->kode }}</span></td>
                        <td>
                            <div style="font-weight:600;">{{ $j->nama }}</div>
                            <div style="font-size:0.75rem;color:var(--text-muted);">Singkatan: {{ $j->singkatan }}</div>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.82rem;">
                            @if($j->kaprogGuru)
                                <span style="font-weight:600;color:var(--text-light);"><i class="bi bi-person-badge me-1" style="color:#f1948a;"></i>{{ $j->kaprogGuru->name }}</span>
                                <span style="display:block;font-size:0.72rem;color:var(--accent-gold);"><i class="bi bi-arrow-repeat me-1"></i>Tersinkron di profil guru</span>
                            @elseif(!empty($j->ketua_jurusan))
                                {{ $j->ketua_jurusan }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <button type="button" class="action-btn btn-edit" title="Edit Jurusan"
                                        onclick="openEditJurusanModal({{ $j->id }}, '{{ addslashes($j->kode) }}', '{{ addslashes($j->nama) }}', '{{ addslashes($j->singkatan) }}', '{{ $j->kaprog_id ?? '' }}', '{{ addslashes($j->ketua_jurusan ?? '') }}')">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <form method="POST" action="{{ route('superadmin.master.jurusan.destroy', $j) }}"
                                      onsubmit="return confirm('Hapus jurusan {{ addslashes($j->nama) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn btn-del" title="Hapus"><i class="bi bi-trash-fill"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;padding:36px;color:var(--text-muted);">Belum ada data jurusan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Form Tambah Jurusan --}}
    <div>
        <form method="POST" action="{{ route('superadmin.master.jurusan.store') }}" class="form-card">
            @csrf
            <h6 style="font-weight:700;margin-bottom:20px;font-size:0.95rem;"><i class="bi bi-plus-circle-fill me-2" style="color:#f1948a;"></i>Tambah Jurusan</h6>
            
            <label class="field-label">Kode <span class="field-required">*</span></label>
            <input type="text" name="kode" class="form-input {{ $errors->has('kode') ? 'is-invalid' : '' }}" value="{{ old('kode') }}" placeholder="TKJ" style="text-transform:uppercase;" required>
            @error('kode')<div class="field-error">{{ $message }}</div>@enderror
            
            <label class="field-label">Nama Jurusan <span class="field-required">*</span></label>
            <input type="text" name="nama" class="form-input {{ $errors->has('nama') ? 'is-invalid' : '' }}" value="{{ old('nama') }}" placeholder="Teknik Komputer dan Jaringan" required>
            @error('nama')<div class="field-error">{{ $message }}</div>@enderror
            
            <label class="field-label">Singkatan <span class="field-required">*</span></label>
            <input type="text" name="singkatan" id="inputSingkatanTambah" class="form-input" value="{{ old('singkatan') }}" placeholder="TKJ" required>
            
            <label class="field-label">Ketua Jurusan / Kaprog (Pilih Guru)</label>
            <select name="kaprog_id" id="selectKaprogTambah" class="form-input mb-2" onchange="handleKaprogSelection(this, 'inputKetuaJurusanTambah', 'infoBoxKaprogTambah', 'inputSingkatanTambah')">
                <option value="">-- Pilih Guru Sebagai Kaprog --</option>
                @foreach($gurus ?? [] as $g)
                    <option value="{{ $g->id }}" 
                            data-name="{{ $g->name }}"
                            data-jabatan="{{ $g->jabatan_utama ?? 'Guru Pengajar' }}"
                            data-tugas="{{ implode(', ', $g->tugas_tambahan ?? []) }}"
                            {{ old('kaprog_id')==$g->id ? 'selected' : '' }}>
                        {{ $g->name }} (NUPTK: {{ $g->nip ?? '-' }})
                    </option>
                @endforeach
            </select>

            {{-- Info Box Kaprog Tambah --}}
            <div id="infoBoxKaprogTambah" class="guru-info-box d-none">
                <div style="font-weight:600; color:var(--primary-lighter);"><i class="bi bi-person-check-fill me-1"></i> <span class="guru-name">-</span></div>
                <div style="color:var(--text-muted); font-size:0.75rem; margin-top:2px;">
                    Jabatan Utama: <strong class="guru-jabatan" style="color:var(--text-light);">-</strong>
                </div>
                <div style="color:var(--text-muted); font-size:0.75rem;">
                    Tugas saat ini: <span class="guru-tugas" style="color:var(--accent-gold);">-</span>
                </div>
                <div style="color:var(--accent-gold-light); font-size:0.74rem; margin-top:5px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:4px;">
                    <i class="bi bi-arrow-repeat me-1"></i> Otomatis disinkronkan ke profil: <strong>Kaprog <span class="jurusan-target-name"></span></strong>
                </div>
            </div>

            <label class="field-label">Atau Nama Ketua Jurusan Manual</label>
            <input type="text" name="ketua_jurusan" id="inputKetuaJurusanTambah" class="form-input" value="{{ old('ketua_jurusan') }}" placeholder="Terisi otomatis jika memilih guru">
            
            <button type="submit" class="btn-save"><i class="bi bi-check-circle-fill"></i> Simpan Jurusan</button>
        </form>
    </div>
</div>

{{-- Modal Edit Jurusan --}}
<div class="modal fade" id="editJurusanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--bg-sidebar); border:1px solid var(--border-color); border-radius:18px; color:var(--text-light);">
            <form method="POST" id="formEditJurusan">
                @csrf
                @method('PUT')
                <div class="modal-header" style="border-bottom:1px solid var(--border-color); padding:18px 24px;">
                    <h5 class="modal-title" style="font-size:1.05rem; font-weight:700;"><i class="bi bi-pencil-square me-2" style="color:#5dade2;"></i>Edit Data Jurusan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding:22px 24px;">
                    <label class="field-label">Kode Jurusan <span class="field-required">*</span></label>
                    <input type="text" name="kode" id="editJurusanKode" class="form-input" style="text-transform:uppercase;" required>

                    <label class="field-label">Nama Jurusan <span class="field-required">*</span></label>
                    <input type="text" name="nama" id="editJurusanNama" class="form-input" required>

                    <label class="field-label">Singkatan <span class="field-required">*</span></label>
                    <input type="text" name="singkatan" id="editJurusanSingkatan" class="form-input" required>

                    <label class="field-label">Ketua Jurusan / Kaprog (Pilih Guru)</label>
                    <select name="kaprog_id" id="editJurusanKaprogId" class="form-input mb-2" onchange="handleKaprogSelection(this, 'editJurusanKetuaManual', 'infoBoxKaprogEdit', 'editJurusanSingkatan')">
                        <option value="">-- Kosongkan / Tanpa Kaprog --</option>
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
                    <div id="infoBoxKaprogEdit" class="guru-info-box d-none">
                        <div style="font-weight:600; color:var(--primary-lighter);"><i class="bi bi-person-check-fill me-1"></i> <span class="guru-name">-</span></div>
                        <div style="color:var(--text-muted); font-size:0.75rem; margin-top:2px;">
                            Jabatan Utama: <strong class="guru-jabatan" style="color:var(--text-light);">-</strong>
                        </div>
                        <div style="color:var(--text-muted); font-size:0.75rem;">
                            Tugas saat ini: <span class="guru-tugas" style="color:var(--accent-gold);">-</span>
                        </div>
                        <div style="color:var(--accent-gold-light); font-size:0.74rem; margin-top:5px; border-top:1px dashed rgba(255,255,255,0.1); padding-top:4px;">
                            <i class="bi bi-arrow-repeat me-1"></i> Jabatan guru akan disinkronkan menjadi: <strong>Kaprog <span class="jurusan-target-name"></span></strong>
                        </div>
                    </div>

                    <label class="field-label">Nama Ketua Jurusan Manual (Opsional)</label>
                    <input type="text" name="ketua_jurusan" id="editJurusanKetuaManual" class="form-input" placeholder="Terisi otomatis jika memilih guru">
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
    function handleKaprogSelection(selectEl, manualInputId, infoBoxId, singkatanInputId) {
        const manualInput = document.getElementById(manualInputId);
        const infoBox = document.getElementById(infoBoxId);
        const singkatanInput = document.getElementById(singkatanInputId);
        const singkatan = singkatanInput ? singkatanInput.value.trim() : '';

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
            infoBox.querySelector('.jurusan-target-name').textContent = singkatan || '(Jurusan)';
        }
    }

    function openEditJurusanModal(id, kode, nama, singkatan, kaprogId, ketuaManual) {
        const form = document.getElementById('formEditJurusan');
        form.action = "{{ url('superadmin/master/jurusan') }}/" + id;

        document.getElementById('editJurusanKode').value = kode;
        document.getElementById('editJurusanNama').value = nama;
        document.getElementById('editJurusanSingkatan').value = singkatan;
        document.getElementById('editJurusanKetuaManual').value = ketuaManual || '';

        const selectKaprog = document.getElementById('editJurusanKaprogId');
        selectKaprog.value = kaprogId || '';

        handleKaprogSelection(selectKaprog, 'editJurusanKetuaManual', 'infoBoxKaprogEdit', 'editJurusanSingkatan');

        const modal = new bootstrap.Modal(document.getElementById('editJurusanModal'));
        modal.show();
    }
</script>
@endsection
