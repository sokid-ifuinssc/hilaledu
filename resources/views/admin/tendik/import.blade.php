@extends('layouts.app')
@section('title', 'Import Data Tenaga Kependidikan')
@section('page-title', 'Import Data Tendik')

@section('dashboard-styles')
.import-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 18px; padding: 32px; max-width: 680px; }
.import-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 6px; }
.import-desc { font-size: 0.82rem; color: var(--text-muted); margin-bottom: 24px; line-height: 1.6; }

.upload-zone { border: 2px dashed rgba(255,255,255,0.12); border-radius: 16px; padding: 40px 24px; text-align: center; transition: all 0.3s; cursor: pointer; position: relative; }
.upload-zone:hover, .upload-zone.dragover { border-color: #e67e22; background: rgba(211,84,0,0.08); }
.upload-zone i { font-size: 2.5rem; color: #e67e22; opacity: 0.6; margin-bottom: 10px; display: block; }
.upload-zone .label { font-size: 0.88rem; font-weight: 600; margin-bottom: 4px; }
.upload-zone .sublabel { font-size: 0.75rem; color: var(--text-muted); }
.upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.file-name { font-size: 0.82rem; color: #e67e22; margin-top: 10px; font-weight: 600; display: none; }

.btn-import { display: inline-flex; align-items: center; gap: 7px; padding: 12px 28px; border-radius: 12px; font-size: 0.88rem; font-weight: 600; background: linear-gradient(135deg, #d35400, #e67e22); border: none; color: white; cursor: pointer; transition: all 0.25s ease; margin-top: 20px; }
.btn-import:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(211,84,0,0.4); }
.btn-import:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }

.btn-back { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; border: 1px solid var(--border-color); background: transparent; color: var(--text-muted); font-size: 0.82rem; font-weight: 500; text-decoration: none; transition: all 0.2s; margin-bottom: 20px; }
.btn-back:hover { background: rgba(255,255,255,0.05); color: var(--text-light); }

.btn-template { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; border: 1px solid rgba(230,126,34,0.3); background: rgba(230,126,34,0.08); color: #f0b27a; font-size: 0.82rem; font-weight: 500; text-decoration: none; transition: all 0.2s; }
.btn-template:hover { background: rgba(230,126,34,0.15); color: #f0b27a; }

.info-box { background: rgba(52,152,219,0.08); border: 1px solid rgba(52,152,219,0.2); border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; font-size: 0.8rem; color: #85c1e9; line-height: 1.7; }
.error-box { background: rgba(231,76,60,0.08); border: 1px solid rgba(231,76,60,0.2); border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; font-size: 0.82rem; color: #f1948a; }
@endsection

@section('content')
<a href="{{ route('superadmin.tendik.index') }}" class="btn-back"><i class="bi bi-arrow-left"></i> Kembali</a>

<div class="import-card">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
        <div>
            <div class="import-title"><i class="bi bi-file-earmark-spreadsheet me-2" style="color:#f0b27a;"></i>Import Data Tendik</div>
            <div class="import-desc">Upload file Excel (.xlsx, .xls, .csv) berisi data staf dan tenaga kependidikan. Data duplikat akan otomatis diperbarui.</div>
        </div>
        <a href="{{ route('superadmin.tendik.template') }}" class="btn-template">
            <i class="bi bi-download"></i> Download Template
        </a>
    </div>

    <div class="info-box">
        <i class="bi bi-info-circle-fill"></i> <strong>Aturan Pengisian & Template Excel:</strong>
        <div class="mt-2 p-2 rounded" style="background:rgba(230,126,34,0.12);border:1px solid rgba(230,126,34,0.3);color:#f0b27a;font-size:0.8rem;">
            <i class="bi bi-check-circle-fill me-1"></i> <strong>PENTING:</strong> Hanya kolom <strong>NUPTK</strong> yang wajib diisi (tidak boleh kosong). Semua kolom lainnya bersifat fleksibel / opsional. Jika Username/Email kosong, sistem akan membuatnya secara otomatis.
        </div>
        <div class="row g-2 mt-2">
            <div class="col-md-6">
                <ul class="mb-0" style="padding-left:18px;">
                    <li><strong class="text-white">NUPTK</strong> <span class="badge bg-danger ms-1" style="font-size:0.65rem;">WAJIB</span></li>
                    <li><strong>Nama Lengkap</strong> (opsional)</li>
                    <li><strong>Username & Email</strong> (otomatis dibuat jika kosong)</li>
                    <li><strong>No HP</strong> (opsional)</li>
                    <li><strong>Jenis Kelamin</strong> — L / P</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="mb-0" style="padding-left:18px;">
                    <li><strong>Tahun Masuk & Lulusan Tahun</strong></li>
                    <li><strong>Alamat, Desa, Kecamatan, Kab, Prov</strong></li>
                    <li><strong>Pendidikan SD, SMP, SMA, S1, S2</strong> (+ Thn Lulus)</li>
                    <li><strong>Password</strong> (default: password123)</li>
                </ul>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="error-box">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Terjadi kesalahan:</strong>
        <ul style="margin:6px 0 0 18px;padding:0;">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('superadmin.tendik.import.process') }}" enctype="multipart/form-data" id="importForm">
        @csrf

        {{-- Opsi Penanganan Data Duplikat / Yang Sudah Ada --}}
        <div class="mb-4 p-3 rounded-3" style="background:rgba(255,255,255,0.03);border:1px solid var(--border-color);">
            <label class="fw-bold text-light small mb-2 d-flex align-items-center gap-1">
                <i class="bi bi-arrow-repeat text-warning"></i> Jika Data Tendik Sudah Ada di Database (Berdasarkan NUPTK/Username):
            </label>
            <div class="d-flex flex-column gap-2 mt-2" style="font-size:0.83rem;">
                <label class="d-flex align-items-center gap-2 text-light" style="cursor:pointer;">
                    <input type="radio" name="duplicate_action" value="update" checked class="form-check-input mt-0">
                    <span><strong>Timpa / Perbarui Data (Update)</strong> <span class="text-muted">— (Rekomendasi) Perbarui data tendik lama dengan data dari Excel.</span></span>
                </label>
                <label class="d-flex align-items-center gap-2 text-light" style="cursor:pointer;">
                    <input type="radio" name="duplicate_action" value="skip" class="form-check-input mt-0">
                    <span><strong>Jangan Timpa / Lewati (Skip)</strong> <span class="text-muted">— Biarkan data lama tetap ada, lewati baris yang sudah ada.</span></span>
                </label>
                <label class="d-flex align-items-center gap-2 text-light" style="cursor:pointer;">
                    <input type="radio" name="duplicate_action" value="replace" class="form-check-input mt-0">
                    <span><strong>Hapus yang Lama & Ganti Baru (Replace)</strong> <span class="text-muted">— Hapus data tendik lama dan buat baru dari Excel.</span></span>
                </label>
            </div>
        </div>
        <div class="upload-zone" id="uploadZone">
            <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv">
            <i class="bi bi-cloud-arrow-up"></i>
            <div class="label">Klik atau drag & drop file Excel di sini</div>
            <div class="sublabel">Format: .xlsx, .xls, .csv — Maks 5MB</div>
            <div class="file-name" id="fileName"></div>
        </div>

        <button type="submit" class="btn-import" id="btnImport" disabled>
            <i class="bi bi-upload"></i> Import Data Tendik
        </button>
    </form>
</div>

<script>
const fileInput = document.getElementById('fileInput');
const uploadZone = document.getElementById('uploadZone');
const fileName = document.getElementById('fileName');
const btnImport = document.getElementById('btnImport');

fileInput.addEventListener('change', function() {
    if (this.files.length > 0) {
        fileName.textContent = '📄 ' + this.files[0].name;
        fileName.style.display = 'block';
        btnImport.disabled = false;
    }
});

uploadZone.addEventListener('dragover', (e) => { e.preventDefault(); uploadZone.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        fileInput.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
