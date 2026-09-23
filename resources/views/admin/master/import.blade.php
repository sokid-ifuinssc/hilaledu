@extends('layouts.app')
@section('title', 'Import Master Data')
@section('page-title', 'Import Master Data')

@section('dashboard-styles')
.import-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 18px; padding: 32px; max-width: 640px; }
.import-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 6px; }
.import-desc { font-size: 0.82rem; color: var(--text-muted); margin-bottom: 24px; line-height: 1.6; }

.upload-zone { border: 2px dashed rgba(255,255,255,0.12); border-radius: 16px; padding: 40px 24px; text-align: center; transition: all 0.3s; cursor: pointer; position: relative; }
.upload-zone:hover, .upload-zone.dragover { border-color: var(--primary-light); background: rgba(39,174,96,0.08); }
.upload-zone i { font-size: 2.5rem; color: var(--primary-light); opacity: 0.6; margin-bottom: 10px; display: block; }
.upload-zone .label { font-size: 0.88rem; font-weight: 600; margin-bottom: 4px; }
.upload-zone .sublabel { font-size: 0.75rem; color: var(--text-muted); }
.upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.file-name { font-size: 0.82rem; color: var(--primary-light); margin-top: 10px; font-weight: 600; display: none; }

.btn-import { display: inline-flex; align-items: center; gap: 7px; padding: 12px 28px; border-radius: 12px; font-size: 0.88rem; font-weight: 600; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border: none; color: white; cursor: pointer; transition: all 0.25s ease; margin-top: 20px; }
.btn-import:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(39,174,96,0.4); }
.btn-import:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }

.btn-back { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; border: 1px solid var(--border-color); background: transparent; color: var(--text-muted); font-size: 0.82rem; font-weight: 500; text-decoration: none; transition: all 0.2s; margin-bottom: 20px; }
.btn-back:hover { background: rgba(255,255,255,0.05); color: var(--text-light); }

.btn-template { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; border: 1px solid rgba(46,204,113,0.3); background: rgba(46,204,113,0.08); color: #58d68d; font-size: 0.82rem; font-weight: 500; text-decoration: none; transition: all 0.2s; }
.btn-template:hover { background: rgba(46,204,113,0.15); color: #58d68d; }

.info-box { background: rgba(52,152,219,0.08); border: 1px solid rgba(52,152,219,0.2); border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; font-size: 0.8rem; color: #85c1e9; line-height: 1.7; }
.info-box i { margin-right: 6px; }
.info-box ul { margin: 6px 0 0 18px; padding: 0; }
.info-box li { margin-bottom: 2px; }

.error-box { background: rgba(231,76,60,0.08); border: 1px solid rgba(231,76,60,0.2); border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; font-size: 0.82rem; color: #f1948a; }
@endsection

@section('content')
<a href="{{ route('superadmin.master.jurusan') }}" class="btn-back"><i class="bi bi-arrow-left"></i> Kembali</a>

<div class="import-card">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
        <div>
            <div class="import-title"><i class="bi bi-file-earmark-spreadsheet me-2" style="color:#58d68d;"></i>Import Master Data</div>
            <div class="import-desc">Upload file Excel (.xlsx, .xls) berisi data Jurusan, Kelas, dan Tahun Ajaran.</div>
        </div>
        <a href="{{ route('superadmin.master.template') }}" class="btn-template">
            <i class="bi bi-download"></i> Download Template
        </a>
    </div>

    <div class="info-box">
        <i class="bi bi-info-circle-fill"></i> <strong>Informasi Import:</strong>
        <ul>
            <li>File harus berisi 3 sheet berurutan: <strong>Jurusan</strong>, <strong>Kelas</strong>, dan <strong>Tahun Ajaran</strong>.</li>
            <li>Anda tidak perlu mengisi data untuk semua sheet. Sheet yang kosong akan diabaikan.</li>
            <li>Pastikan nama header (baris pertama) sesuai dengan template.</li>
            <li>Data duplikat akan otomatis diperbarui berdasarkan identitas unik (misal: Kode Jurusan, Nama Kelas, Tahun Ajaran).</li>
        </ul>
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

    <form method="POST" action="{{ route('superadmin.master.import.process') }}" enctype="multipart/form-data" id="importForm">
        @csrf
        <div class="upload-zone" id="uploadZone">
            <input type="file" name="file" id="fileInput" accept=".xlsx,.xls">
            <i class="bi bi-cloud-arrow-up"></i>
            <div class="label">Klik atau drag & drop file Excel di sini</div>
            <div class="sublabel">Format: .xlsx, .xls — Maks 5MB</div>
            <div class="file-name" id="fileName"></div>
        </div>

        <button type="submit" class="btn-import" id="btnImport" disabled>
            <i class="bi bi-upload"></i> Import Master Data
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
