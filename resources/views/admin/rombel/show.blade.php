@extends('layouts.app')
@section('title', 'Kelola Rombel - ' . $kela->nama_lengkap)
@section('page-title', 'Kelola Rombel Kelas')

@section('dashboard-styles')
.back-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; border: 1px solid #e2e8f0; background: transparent; color: #64748b; font-size: 0.82rem; font-weight: 500; text-decoration: none; transition: all 0.2s ease; font-family: 'Poppins', sans-serif; }
.back-btn:hover { background: #f1f5f9; color: #1e293b; }

.rombel-detail-banner { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 24px 30px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
.rombel-banner-title { font-size: 1.35rem; font-weight: 800; color: #1e293b; margin: 0 0 6px 0; display: flex; align-items: center; gap: 10px; }
.rombel-banner-meta { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; font-size: 0.83rem; color: #64748b; }
.rombel-meta-item { display: flex; align-items: center; gap: 6px; }

.btn-act { display: inline-flex; align-items: center; gap: 7px; padding: 10px 18px; border-radius: 12px; font-size: 0.86rem; font-weight: 600; border: none; cursor: pointer; text-decoration: none; transition: all 0.25s ease; font-family: 'Poppins', sans-serif; }
.btn-assign { background: linear-gradient(135deg, #059669, #10b981); color: white; }
.btn-assign:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(5,150,105,0.3); color: white; }
.btn-pindah-bulk { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
.btn-pindah-bulk:hover { background: #fde68a; color: #b45309; }
.btn-del-bulk { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
.btn-del-bulk:hover { background: #fecaca; color: #b91c1c; }

.data-table { width: 100%; border-collapse: collapse; }
.data-table th { padding: 12px 16px; text-align: left; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #475569; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
.data-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.87rem; vertical-align: middle; color: #1e293b; }
.data-table tr:hover td { background: #f8fafc; }
.data-table tr:last-child td { border-bottom: none; }

.user-cell { display: flex; align-items: center; gap: 12px; }
.user-avatar { width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 0.82rem; font-weight: 700; color: white; background: linear-gradient(135deg, #059669, #10b981); }

.action-btn { display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; border-radius: 7px; font-size: 0.75rem; font-weight: 500; cursor: pointer; border: none; font-family: 'Poppins', sans-serif; text-decoration: none; transition: all 0.2s; }
.btn-edit { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.btn-edit:hover { background: #dbeafe; color: #1d4ed8; }
.btn-del { background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; }
.btn-del:hover { background: #ffe4e6; color: #be123c; }

/* Modal Custom Styling - Light Theme */
.modal-content { background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-radius: 20px; color: #1e293b; box-shadow: 0 25px 60px rgba(0,0,0,0.15); }
.modal-header { border-bottom: 1px solid #e2e8f0; padding: 20px 24px; background: #f8fafc; }
.modal-footer { border-top: 1px solid #e2e8f0; padding: 16px 24px; background: #f8fafc; }
.form-input { width: 100%; background: #ffffff; border: 1px solid #cbd5e1; color: #1e293b; border-radius: 12px; padding: 11px 16px; font-family: 'Poppins', sans-serif; font-size: 0.88rem; }
.form-input:focus { outline: none; border-color: #059669; background: #ffffff; box-shadow: 0 0 0 3px rgba(5,150,105,0.15); }
.form-input option { background: #ffffff; color: #1e293b; }
.student-select-list { max-height: 320px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 14px; padding: 10px; background: #f8fafc; }
.student-select-item { display: flex; align-items: center; gap: 14px; padding: 12px 14px; border-radius: 10px; transition: all 0.2s; cursor: pointer; border: 1px solid transparent; margin-bottom: 4px; }
.student-select-item:hover { background: #ecfdf5; border-color: rgba(5,150,105,0.3); }
.student-select-item input[type=checkbox] { width: 18px; height: 18px; accent-color: #059669; flex-shrink: 0; }
@endsection

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('superadmin.rombel.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i> Kembali ke Daftar Rombel</a>
</div>

<div class="rombel-detail-banner">
    <div>
        <h4 class="rombel-banner-title">
            <i class="bi bi-door-open-fill" style="color:#58d68d;"></i>
            Rombel {{ $kela->nama_lengkap }}
        </h4>
        <div class="rombel-banner-meta">
            <span class="rombel-meta-item"><i class="bi bi-bookmark-fill" style="color:#58d68d;"></i> Tingkat {{ $kela->tingkat }}</span>
            <span>•</span>
            <span class="rombel-meta-item"><i class="bi bi-diagram-3-fill" style="color:#5dade2;"></i> {{ $kela->jurusan?->nama_jurusan ?? $kela->jurusan?->nama ?? 'Umum' }}</span>
            <span>•</span>
            <span class="rombel-meta-item"><i class="bi bi-person-badge-fill" style="color:#f39c12;"></i> Wali Kelas: <strong>{{ $kela->wali_kelas ?: ($kela->waliKelasGuru?->name ?: 'Belum Ada') }}</strong></span>
            <span>•</span>
            <span class="rombel-meta-item" style="color:#58d68d;font-weight:700;"><i class="bi bi-people-fill"></i> {{ count($siswas) }} Siswa</span>
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn-act btn-assign" data-bs-toggle="modal" data-bs-target="#assignModal">
            <i class="bi bi-person-plus-fill"></i> Tambah Siswa ke Rombel
        </button>
        <button type="button" class="btn-act btn-pindah-bulk" onclick="openBulkPindahModal()">
            <i class="bi bi-arrow-left-right"></i> Pindahkan Terpilih (<span class="selected-count-badge">0</span>)
        </button>
        <button type="button" class="btn-act btn-del-bulk" onclick="openBulkRemoveModal()">
            <i class="bi bi-box-arrow-right"></i> Keluarkan Terpilih (<span class="selected-count-badge">0</span>)
        </button>
    </div>
</div>

@if(session('success'))
<div style="background:rgba(46,204,113,0.1);border:1px solid rgba(46,204,113,0.3);color:#58d68d;border-radius:14px;padding:14px 18px;margin-bottom:20px;font-size:0.88rem;display:flex;align-items:center;gap:10px;">
    <i class="bi bi-check-circle-fill" style="font-size:1.1rem;"></i>
    <div>{{ session('success') }}</div>
</div>
@endif

@if($errors->any())
<div style="background:rgba(231,76,60,0.15);border:1px solid rgba(231,76,60,0.4);color:#f1948a;border-radius:14px;padding:14px 18px;margin-bottom:20px;font-size:0.88rem;">
    <div style="font-weight:700;margin-bottom:4px;"><i class="bi bi-exclamation-triangle-fill me-1"></i> Perhatian:</div>
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- Master Container & Form Pindah Massal -->
<form id="formPindahBulk" method="POST" action="{{ route('superadmin.rombel.pindah') }}">
    @csrf
    <!-- Input Tersembunyi untuk Form Keluarkan Massal -->
</form>

<form id="formRemoveBulk" method="POST" action="{{ route('superadmin.rombel.remove-bulk', $kela) }}">
    @csrf
</form>

<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;background:#f8fafc;">
        <div style="font-weight:700;font-size:0.95rem;display:flex;align-items:center;gap:8px;color:#1e293b;">
            <i class="bi bi-people-fill" style="color:#059669;"></i> Anggota Rombel Saat Ini ({{ count($siswas) }} Siswa)
            <span id="bulkBadgeStatus" class="badge" style="background:#fef3c7;color:#d97706;border:1px solid #fde68a;display:none;font-size:0.75rem;padding:4px 10px;border-radius:8px;">
                <span class="selected-count-badge">0</span> siswa dipilih
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span style="font-size:0.78rem;color:#64748b;">
                Centang siswa untuk melakukan aksi massal (pindah / keluarkan)
            </span>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:40px;text-align:center;">
                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" title="Pilih Semua Siswa">
                    </th>
                    <th>Siswa</th>
                    <th>NIS/NISN</th>
                    <th>L/P</th>
                    <th>No. HP / WhatsApp Ortu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($siswas as $siswa)
                <tr>
                    <td style="text-align:center;">
                        <input type="checkbox" value="{{ $siswa->id }}" class="siswa-checkbox" onchange="updateSelectedCount()">
                    </td>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar">{{ strtoupper(substr($siswa->name,0,2)) }}</div>
                            <div>
                                <div style="font-weight:600;font-size:0.9rem;color:#1e293b;">{{ $siswa->name }}</div>
                                <div style="font-size:0.72rem;color:#64748b;">{{ $siswa->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="color:#64748b;">{{ $siswa->nip ?? $siswa->username }}</td>
                    <td>
                        @if($siswa->jenis_kelamin === 'L')
                            <span style="color:#2563eb;font-weight:600;">L</span>
                        @elseif($siswa->jenis_kelamin === 'P')
                            <span style="color:#e11d48;font-weight:600;">P</span>
                        @else
                            <span style="color:#94a3b8;">-</span>
                        @endif
                    </td>
                    <td style="color:#64748b;font-size:0.8rem;">
                        @if($siswa->no_hp_ortu)
                            <i class="bi bi-whatsapp text-success me-1"></i> {{ $siswa->no_hp_ortu }}
                        @elseif($siswa->no_hp)
                            <i class="bi bi-telephone me-1"></i> {{ $siswa->no_hp }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button type="button" class="action-btn btn-edit" onclick="pindahSatuSiswa('{{ $siswa->id }}')">
                                <i class="bi bi-arrow-left-right"></i> Pindah
                            </button>
                            <form method="POST" action="{{ route('superadmin.rombel.remove', [$kela, $siswa]) }}" onsubmit="return confirm('Keluarkan {{ addslashes($siswa->name) }} dari kelas {{ addslashes($kela->nama_lengkap) }}?')">
                                @csrf
                                <button type="submit" class="action-btn btn-del" title="Keluarkan dari kelas">
                                    <i class="bi bi-box-arrow-right"></i> Keluarkan
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:48px;color:#94a3b8;">
                        <i class="bi bi-door-open d-block mb-2" style="font-size:2.5rem;opacity:0.3;"></i>
                        Rombel kelas ini belum memiliki anggota siswa.<br>
                        Klik tombol <strong>Tambah Siswa ke Rombel</strong> di atas untuk memasukkan siswa.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Pindahkan Siswa Massal -->
<div class="modal fade" id="modalPindahBulk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right me-2" style="color:#d97706;"></i>Pindahkan Siswa ke Kelas Lain</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:0.85rem;color:#64748b;margin-bottom:16px;">
                    Pilih kelas tujuan untuk memindahkan <span id="countSelectedModal" style="font-weight:700;color:#1e293b;">0</span> siswa terpilih dari kelas {{ $kela->nama_lengkap }}.
                </p>
                <div class="mb-3">
                    <label class="field-label" style="font-size:0.82rem;font-weight:600;margin-bottom:6px;display:block;">Kelas Tujuan <span style="color:#f39c12;">*</span></label>
                    <select id="targetKelasSelect" class="form-input" required>
                        <option value="">-- Pilih Kelas Tujuan --</option>
                        @foreach($kelasLain as $kl)
                        <option value="{{ $kl->id }}">{{ $kl->nama_lengkap }} {{ $kl->wali_kelas ? '(Wali: '.$kl->wali_kelas.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-act" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn-act btn-assign" onclick="submitBulkPindah()"><i class="bi bi-check-circle-fill"></i> Proses Pemindahan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Assign Siswa (Tambah ke Rombel) -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('superadmin.rombel.assign', $kela) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2" style="color:#059669;"></i>Tambah Siswa ke Rombel {{ $kela->nama_lengkap }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p style="font-size:0.83rem;color:#64748b;margin-bottom:14px;">
                        Pilih siswa yang belum terdaftar di kelas manapun untuk dimasukkan ke rombel kelas ini.
                    </p>

                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <input type="text" id="searchUnassigned" class="form-input flex-grow-1" placeholder="Cari nama atau NISN siswa..." onkeyup="filterUnassignedStudents()">
                        @if(count($siswaBelumAdaKelas) > 0)
                        <label class="btn-act" style="background:#f1f5f9;border:1px solid #e2e8f0;color:#475569;font-size:0.8rem;white-space:nowrap;cursor:pointer;user-select:none;">
                            <input type="checkbox" id="selectAllUnassigned" onchange="toggleSelectAllUnassigned(this)" class="me-1"> Pilih Semua
                        </label>
                        @endif
                    </div>

                    <div class="student-select-list" id="unassignedList">
                        @forelse($siswaBelumAdaKelas as $s)
                        <label class="student-select-item" data-search="{{ strtolower($s->name . ' ' . $s->nip . ' ' . $s->username) }}">
                            <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="unassigned-checkbox">
                            <div class="user-avatar" style="width:30px;height:30px;font-size:0.75rem;">{{ strtoupper(substr($s->name,0,2)) }}</div>
                            <div style="flex:1;">
                                <div style="font-weight:600;font-size:0.86rem;color:#1e293b;">{{ $s->name }}</div>
                                <div style="font-size:0.72rem;color:#64748b;">NIS: {{ $s->nip ?? $s->username }}</div>
                            </div>
                        </label>
                        @empty
                        <div style="text-align:center;padding:24px;color:#94a3b8;font-size:0.85rem;">
                            Semua siswa terdaftar sudah memiliki rombel kelas!
                        </div>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-act" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-act btn-assign"><i class="bi bi-check-circle-fill"></i> Masukkan ke Rombel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function updateSelectedCount() {
    const checked = document.querySelectorAll('.siswa-checkbox:checked');
    const count = checked.length;
    
    document.querySelectorAll('.selected-count-badge').forEach(el => el.innerText = count);
    
    const badge = document.getElementById('bulkBadgeStatus');
    if (badge) {
        badge.style.display = count > 0 ? 'inline-block' : 'none';
    }

    const selectAllCb = document.getElementById('selectAll');
    const allCbs = document.querySelectorAll('.siswa-checkbox');
    if (selectAllCb && allCbs.length > 0) {
        selectAllCb.checked = (checked.length === allCbs.length);
    }
}

function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('.siswa-checkbox');
    checkboxes.forEach(cb => cb.checked = source.checked);
    updateSelectedCount();
}

function openBulkPindahModal() {
    const checked = document.querySelectorAll('.siswa-checkbox:checked');
    if (checked.length === 0) {
        alert('Pilih minimal satu siswa yang ingin dipindahkan dengan mencentang kotak di tabel.');
        return;
    }
    document.getElementById('countSelectedModal').innerText = checked.length;
    const modal = new bootstrap.Modal(document.getElementById('modalPindahBulk'));
    modal.show();
}

function submitBulkPindah() {
    const targetKelasId = document.getElementById('targetKelasSelect').value;
    if (!targetKelasId) {
        alert('Silakan pilih kelas tujuan terlebih dahulu.');
        return;
    }

    const form = document.getElementById('formPindahBulk');
    // Hapus input siswa_ids lama jika ada
    form.querySelectorAll('input[name="siswa_ids[]"]').forEach(el => el.remove());
    form.querySelectorAll('input[name="target_kelas_id"]').forEach(el => el.remove());

    // Masukkan target_kelas_id
    const targetInput = document.createElement('input');
    targetInput.type = 'hidden';
    targetInput.name = 'target_kelas_id';
    targetInput.value = targetKelasId;
    form.appendChild(targetInput);

    // Masukkan seluruh siswa_ids terpilih
    const checked = document.querySelectorAll('.siswa-checkbox:checked');
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'siswa_ids[]';
        input.value = cb.value;
        form.appendChild(input);
    });

    form.submit();
}

function openBulkRemoveModal() {
    const checked = document.querySelectorAll('.siswa-checkbox:checked');
    if (checked.length === 0) {
        alert('Pilih minimal satu siswa yang ingin dikeluarkan dari rombel kelas dengan mencentang kotak di tabel.');
        return;
    }

    if (!confirm('Apakah Anda yakin ingin mengeluarkan ' + checked.length + ' siswa terpilih dari kelas {{ addslashes($kela->nama_lengkap) }}?')) {
        return;
    }

    const form = document.getElementById('formRemoveBulk');
    form.querySelectorAll('input[name="siswa_ids[]"]').forEach(el => el.remove());

    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'siswa_ids[]';
        input.value = cb.value;
        form.appendChild(input);
    });

    form.submit();
}

function pindahSatuSiswa(siswaId) {
    const checkboxes = document.querySelectorAll('.siswa-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    
    const target = Array.from(checkboxes).find(cb => cb.value == siswaId);
    if (target) target.checked = true;
    updateSelectedCount();

    document.getElementById('countSelectedModal').innerText = 1;
    const modal = new bootstrap.Modal(document.getElementById('modalPindahBulk'));
    modal.show();
}

function filterUnassignedStudents() {
    const q = document.getElementById('searchUnassigned').value.toLowerCase();
    const items = document.querySelectorAll('#unassignedList .student-select-item');
    items.forEach(item => {
        const text = item.getAttribute('data-search') || '';
        item.style.display = text.includes(q) ? 'flex' : 'none';
    });
}

function toggleSelectAllUnassigned(source) {
    const items = document.querySelectorAll('#unassignedList .student-select-item');
    items.forEach(item => {
        if (item.style.display !== 'none') {
            const cb = item.querySelector('.unassigned-checkbox');
            if (cb) cb.checked = source.checked;
        }
    });
}
</script>
@endsection
