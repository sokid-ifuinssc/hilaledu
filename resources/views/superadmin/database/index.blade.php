@extends('layouts.app')

@section('title', 'Backup & Restore Database')
@section('page-title', 'Backup & Restore Database')

@section('dashboard-styles')
.page-header {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 14px; margin-bottom: 24px;
}
.page-header h4 { font-size: 1.25rem; font-weight: 700; margin: 0; }

.stats-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 28px;
}
.stat-card {
    background: var(--bg-card); border: 1px solid var(--border-color);
    border-radius: 16px; padding: 18px 22px;
    display: flex; align-items: center; gap: 16px;
    transition: all 0.3s ease;
}
.stat-card:hover {
    background: var(--bg-card-hover);
    border-color: rgba(255, 255, 255, 0.12);
}
.stat-icon {
    width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;
}
.stat-val { font-size: 1.4rem; font-weight: 800; line-height: 1.2; }
.stat-lbl { font-size: 0.78rem; color: var(--text-muted); margin-top: 2px; }

.action-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px; margin-bottom: 30px;
}
.action-card {
    background: var(--bg-card); border: 1px solid var(--border-color);
    border-radius: 20px; padding: 26px;
    display: flex; flex-direction: column; justify-content: space-between;
    position: relative; overflow: hidden;
}
.action-card-header {
    display: flex; align-items: center; gap: 14px; margin-bottom: 16px;
}
.action-card-icon {
    width: 52px; height: 52px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;
}
.action-card-title { font-size: 1.15rem; font-weight: 700; margin: 0; }
.action-card-desc { font-size: 0.83rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 18px; }

.btn-cta-export {
    display: inline-flex; align-items: center; justify-content: center; gap: 10px;
    background: linear-gradient(135deg, #16a085, #27ae60);
    border: none; color: white; padding: 14px 24px; border-radius: 14px;
    font-size: 0.92rem; font-weight: 600; text-decoration: none; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.3s ease; box-shadow: 0 6px 20px rgba(39,174,96,0.3);
}
.btn-cta-export:hover {
    transform: translateY(-2px); box-shadow: 0 10px 28px rgba(39,174,96,0.45); color: white;
}

.btn-cta-import {
    display: inline-flex; align-items: center; justify-content: center; gap: 10px;
    background: linear-gradient(135deg, #2980b9, #3498db);
    border: none; color: white; padding: 14px 24px; border-radius: 14px;
    font-size: 0.92rem; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.3s ease; box-shadow: 0 6px 20px rgba(52,152,219,0.3);
}
.btn-cta-import:hover {
    transform: translateY(-2px); box-shadow: 0 10px 28px rgba(52,152,219,0.45); color: white;
}

.custom-file-upload {
    border: 2px dashed rgba(255,255,255,0.18); border-radius: 14px;
    padding: 20px; text-align: center; cursor: pointer;
    background: rgba(255,255,255,0.02); transition: all 0.25s ease;
    margin-bottom: 16px;
}
.custom-file-upload:hover {
    border-color: #5dade2; background: rgba(52,152,219,0.06);
}

.table-card {
    background: var(--bg-card); border: 1px solid var(--border-color);
    border-radius: 20px; overflow: hidden; margin-bottom: 30px;
}
.table-card-header {
    padding: 20px 24px; border-bottom: 1px solid var(--border-color);
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
}
.data-table { width: 100%; border-collapse: collapse; }
.data-table th {
    padding: 14px 20px; text-align: left; font-size: 0.75rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-muted);
    border-bottom: 1px solid var(--border-color); background: rgba(255,255,255,0.02);
}
.data-table td {
    padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04);
    font-size: 0.86rem; vertical-align: middle;
}
.data-table tr:hover td { background: rgba(255,255,255,0.02); }
.data-table tr:last-child td { border-bottom: none; }

.badge-auto {
    background: rgba(243,156,18,0.15); color: #f39c12; border: 1px solid rgba(243,156,18,0.3);
    font-size: 0.72rem; padding: 4px 10px; border-radius: 8px; font-weight: 600;
}
.badge-manual {
    background: rgba(46,204,113,0.15); color: #58d68d; border: 1px solid rgba(46,204,113,0.3);
    font-size: 0.72rem; padding: 4px 10px; border-radius: 8px; font-weight: 600;
}

.btn-act {
    display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px;
    border-radius: 8px; font-size: 0.78rem; font-weight: 500; text-decoration: none;
    cursor: pointer; border: none; font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.btn-act-download {
    background: rgba(46,204,113,0.12); color: #58d68d; border: 1px solid rgba(46,204,113,0.25);
}
.btn-act-download:hover { background: rgba(46,204,113,0.25); color: #58d68d; }
.btn-act-restore {
    background: rgba(52,152,219,0.12); color: #5dade2; border: 1px solid rgba(52,152,219,0.25);
}
.btn-act-restore:hover { background: rgba(52,152,219,0.25); color: #5dade2; }
.btn-act-delete {
    background: rgba(231,76,60,0.12); color: #f1948a; border: 1px solid rgba(231,76,60,0.25);
}
.btn-act-delete:hover { background: rgba(231,76,60,0.25); color: #f1948a; }
@endsection

@section('content')
<div class="page-header">
    <div>
        <h4><i class="bi bi-database-fill-gear me-2" style="color:#5dade2;"></i>Backup & Restore Database</h4>
        <p style="font-size:0.82rem;color:var(--text-muted);margin:4px 0 0;">
            Kelola salinan cadangan (backup) database dan pemulihan (restore) sistem HilalEdu
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('superadmin.database.export') }}" class="btn-cta-export" style="padding:10px 18px;font-size:0.85rem;">
            <i class="bi bi-download"></i> Ekspor Database Terbaru
        </a>
    </div>
</div>

{{-- Feedback Notifikasi --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4"
         style="background: rgba(46, 204, 113, 0.15); border: 1px solid rgba(46, 204, 113, 0.35); color: #58d68d; border-radius: 14px; padding: 14px 18px;">
        <i class="bi bi-check-circle-fill" style="font-size: 1.15rem;"></i>
        <div style="font-size:0.88rem;">{{ session('success') }}</div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4"
         style="background: rgba(231, 76, 60, 0.15); border: 1px solid rgba(231, 76, 60, 0.35); color: #f1948a; border-radius: 14px; padding: 14px 18px;">
        <i class="bi bi-exclamation-octagon-fill" style="font-size: 1.15rem;"></i>
        <div style="font-size:0.88rem;">{{ session('error') }}</div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (isset($errors) && $errors->any())
    <div class="alert alert-danger mb-4 rounded-3" style="background:rgba(231,76,60,0.15);border:1px solid rgba(231,76,60,0.4);color:#f1948a;font-size:0.85rem;padding:14px 18px;">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Terdapat kesalahan pada proses impor:</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Ringkasan Status Database --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(52,152,219,0.15);color:#5dade2;">
            <i class="bi bi-database"></i>
        </div>
        <div>
            <div class="stat-val">{{ $dbInfo['database'] }}</div>
            <div class="stat-lbl">Database Aktif</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(46,204,113,0.15);color:#58d68d;">
            <i class="bi bi-hdd-network-fill"></i>
        </div>
        <div>
            <div class="stat-val" style="font-size:1.15rem;">{{ $dbInfo['host'] }}:{{ $dbInfo['port'] }}</div>
            <div class="stat-lbl">MySQL Server ({{ Str::limit($dbInfo['mysql_version'], 14) }})</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(243,156,18,0.15);color:#f39c12;">
            <i class="bi bi-table"></i>
        </div>
        <div>
            <div class="stat-val">{{ $dbInfo['tables_count'] }} Tabel</div>
            <div class="stat-lbl">{{ number_format($dbInfo['total_rows']) }} Total Baris Data</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(155,89,182,0.15);color:#bb8fce;">
            <i class="bi bi-pie-chart-fill"></i>
        </div>
        <div>
            <div class="stat-val">{{ $dbInfo['total_size_mb'] }} MB</div>
            <div class="stat-lbl">Ukuran Database</div>
        </div>
    </div>
</div>

{{-- Dua Aksi Utama: Ekspor & Impor --}}
<div class="action-grid">

    {{-- KARTU 1: EKSPOR DATABASE --}}
    <div class="action-card">
        <div>
            <div class="action-card-header">
                <div class="action-card-icon" style="background:rgba(46,204,113,0.15);color:#58d68d;">
                    <i class="bi bi-cloud-arrow-down-fill"></i>
                </div>
                <div>
                    <h5 class="action-card-title">Ekspor Database Terbaru</h5>
                    <span style="font-size:0.75rem;color:var(--text-muted);">Download salinan lengkap (.SQL)</span>
                </div>
            </div>
            <p class="action-card-desc">
                Download seluruh struktur tabel dan isi data database <strong>{{ $dbInfo['database'] }}</strong> ke dalam format file <code>.sql</code> standar. File cadangan ini juga otomatis diarsipkan di server.
            </p>

            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border-color);border-radius:12px;padding:14px 16px;margin-bottom:20px;font-size:0.8rem;">
                <div class="fw-bold mb-2 text-light"><i class="bi bi-check2-circle text-success me-1"></i> Data yang tercakup dalam ekspor:</div>
                <ul class="mb-0 ps-3 text-muted" style="line-height:1.7;">
                    <li>Seluruh {{ $dbInfo['tables_count'] }} tabel sistem & sub-aplikasi</li>
                    <li>Akun pengguna, guru, siswa, rombel & kurikulum</li>
                    <li>Riwayat pendidikan, tugas tambahan & kredensial akun</li>
                    <li>Perintah SQL siap pakai untuk restore kapan saja</li>
                </ul>
            </div>
        </div>

        <div>
            <a href="{{ route('superadmin.database.export') }}" class="btn-cta-export w-100">
                <i class="bi bi-download"></i> Unduh File SQL Database Sekarang
            </a>
            <div class="text-center mt-2" style="font-size:0.74rem;color:var(--text-muted);">
                Waktu pemrosesan tergantung ukuran data (rata-rata 1-3 detik)
            </div>
        </div>
    </div>

    {{-- KARTU 2: IMPOR / RESTORE DATABASE --}}
    <div class="action-card">
        <form id="form-import-db" method="POST" action="{{ route('superadmin.database.import') }}" enctype="multipart/form-data">
            @csrf
            <div>
                <div class="action-card-header">
                    <div class="action-card-icon" style="background:rgba(52,152,219,0.15);color:#5dade2;">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                    </div>
                    <div>
                        <h5 class="action-card-title">Impor / Pulihkan Database</h5>
                        <span style="font-size:0.75rem;color:var(--text-muted);">Unggah file backup (.SQL)</span>
                    </div>
                </div>
                <p class="action-card-desc">
                    Pulihkan database dari file cadangan <code>.sql</code>. Sebelum data baru diimpor, sistem akan <strong>otomatis membuat cadangan darurat (safety backup)</strong> terlebih dahulu.
                </p>

                <div class="custom-file-upload" onclick="document.getElementById('input-sql-file').click();">
                    <input type="file" name="sql_file" id="input-sql-file" accept=".sql" style="display:none;" onchange="onSqlFileSelected(this)">
                    <i class="bi bi-filetype-sql" style="font-size:2.2rem;color:#5dade2;display:block;margin-bottom:8px;"></i>
                    <div id="file-upload-label" style="font-size:0.86rem;font-weight:600;color:var(--text-light);">
                        Pilih file .SQL dari komputer Anda
                    </div>
                    <div style="font-size:0.74rem;color:var(--text-muted);margin-top:4px;">
                        Maksimal ukuran file: 100 MB
                    </div>
                </div>

                <div class="form-check mb-3" style="background:rgba(231,76,60,0.08);border:1px solid rgba(231,76,60,0.25);border-radius:12px;padding:12px 14px 12px 36px;">
                    <input class="form-check-input" type="checkbox" name="confirm_import" id="check-confirm" value="1">
                    <label class="form-check-label text-warning" for="check-confirm" style="font-size:0.8rem;line-height:1.4;">
                        Saya mengonfirmasi untuk memulihkan database dari file SQL yang dipilih.
                    </label>
                </div>
            </div>

            <div>
                <button type="button" id="btn-submit-import" class="btn-cta-import w-100" onclick="triggerImportModal()">
                    <i class="bi bi-arrow-repeat"></i> Mulai Pulihkan Database
                </button>
            </div>
        </form>
    </div>

</div>

{{-- Arsip / Riwayat File Backup --}}
<div class="table-card">
    <div class="table-card-header">
        <div>
            <h5 style="margin:0;font-size:1.05rem;font-weight:700;">
                <i class="bi bi-archive-fill me-2" style="color:var(--accent-gold);"></i>Riwayat Arsip Backup di Server
            </h5>
            <p style="margin:4px 0 0;font-size:0.78rem;color:var(--text-muted);">
                Daftar file cadangan database yang tersimpan di direktori storage server
            </p>
        </div>
        <span style="font-size:0.8rem;color:var(--text-muted);">
            Total: <strong>{{ count($backups) }}</strong> file tersimpan
        </span>
    </div>

    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th>Nama File Backup</th>
                    <th>Tipe Cadangan</th>
                    <th>Ukuran File</th>
                    <th>Waktu Dibuat</th>
                    <th style="text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($backups as $index => $b)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-code-fill" style="font-size:1.2rem;color:#5dade2;"></i>
                                <span class="fw-semibold text-light">{{ $b['filename'] }}</span>
                            </div>
                        </td>
                        <td>
                            @if($b['is_safety'])
                                <span class="badge-auto"><i class="bi bi-shield-check me-1"></i> Safety Backup (Auto)</span>
                            @else
                                <span class="badge-manual"><i class="bi bi-cloud-download me-1"></i> Ekspor Manual</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-light fw-medium">{{ $b['size_human'] }}</span>
                        </td>
                        <td>
                            <div style="font-size:0.85rem;color:var(--text-light);">{{ $b['created_at']->format('d M Y, H:i') }} WIB</div>
                            <div style="font-size:0.73rem;color:var(--text-muted);">{{ $b['created_at']->diffForHumans() }}</div>
                        </td>
                        <td style="text-align:right;">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('superadmin.database.download', $b['filename']) }}" class="btn-act btn-act-download" title="Unduh File SQL">
                                    <i class="bi bi-download"></i> Unduh
                                </a>

                                <button type="button" class="btn-act btn-act-restore"
                                        onclick="confirmRestoreArchive('{{ $b['filename'] }}', '{{ route('superadmin.database.restore', $b['filename']) }}')"
                                        title="Pulihkan database dari arsip ini">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                </button>

                                <button type="button" class="btn-act btn-act-delete"
                                        onclick="confirmDeleteArchive('{{ $b['filename'] }}', '{{ route('superadmin.database.delete', $b['filename']) }}')"
                                        title="Hapus file backup">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4" style="color:var(--text-muted);font-size:0.88rem;">
                            <i class="bi bi-inbox d-block mb-2" style="font-size:2rem;opacity:0.4;"></i>
                            Belum ada file backup yang tersimpan di server. Klik tombol <strong>Ekspor Database Terbaru</strong> untuk membuat cadangan pertama.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Rincian Tabel Database Saat Ini (Collapsible) --}}
<div class="table-card">
    <div class="table-card-header" style="cursor:pointer;" onclick="toggleTableDetails()">
        <div>
            <h5 style="margin:0;font-size:1.05rem;font-weight:700;">
                <i class="bi bi-list-check me-2" style="color:#58d68d;"></i>Rincian Tabel Database Saat Ini ({{ $dbInfo['tables_count'] }} Tabel)
            </h5>
            <p style="margin:4px 0 0;font-size:0.78rem;color:var(--text-muted);">
                Klik untuk melihat rincian jumlah baris dan estimasi ukuran per tabel
            </p>
        </div>
        <i class="bi bi-chevron-down" id="icon-chevron-tables" style="font-size:1.1rem;transition:transform 0.3s ease;"></i>
    </div>

    <div id="wrapper-tables-list" style="display:none;overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th>Nama Tabel</th>
                    <th>Jumlah Baris</th>
                    <th>Ukuran (MB)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dbInfo['tables'] as $idx => $t)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="fw-semibold" style="color:var(--text-light);font-family:monospace;">
                            {{ $t['name'] }}
                        </td>
                        <td>
                            <span class="badge" style="background:rgba(255,255,255,0.06);color:var(--text-light);font-size:0.8rem;padding:4px 8px;">
                                {{ number_format($t['rows']) }} baris
                            </span>
                        </td>
                        <td style="color:var(--text-muted);">
                            {{ $t['size_mb'] > 0 ? $t['size_mb'] . ' MB' : '< 0.01 MB' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Konfirmasi Impor File Upload --}}
<div class="modal fade" id="modalConfirmImport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #142019; border: 1px solid rgba(231,76,60,0.4); border-radius: 20px; color: var(--text-light);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 20px 24px;">
                <h5 class="modal-title d-flex align-items-center gap-2" style="font-size: 1.1rem; font-weight: 700; color: #f1948a;">
                    <i class="bi bi-exclamation-triangle-fill"></i> Konfirmasi Pemulihan Database
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 24px; font-size: 0.88rem; line-height: 1.6;">
                <p>Anda akan melakukan pemulihan database dari file:</p>
                <div class="p-3 mb-3 rounded-3" style="background:rgba(255,255,255,0.04);border:1px solid var(--border-color);font-family:monospace;word-break:break-all;" id="modal-import-filename">
                    -
                </div>
                <div class="alert alert-warning mb-0" style="background:rgba(243,156,18,0.12);border:1px solid rgba(243,156,18,0.3);color:#f39c12;font-size:0.82rem;border-radius:12px;">
                    <i class="bi bi-shield-check me-1"></i> Sistem akan secara otomatis menyimpan cadangan cadangan darurat (safety backup) sebelum proses pemulihan dijalankan.
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--border-color); padding: 16px 24px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:10px;font-size:0.85rem;">Batal</button>
                <button type="button" class="btn btn-danger" onclick="submitImportForm()" style="border-radius:10px;font-size:0.85rem;background:#e74c3c;border:none;">
                    <i class="bi bi-check-circle-fill me-1"></i> Ya, Lanjutkan Pemulihan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Restore dari Arsip --}}
<div class="modal fade" id="modalConfirmRestoreArchive" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #142019; border: 1px solid rgba(52,152,219,0.4); border-radius: 20px; color: var(--text-light);">
            <form id="form-restore-archive" method="POST" action="">
                @csrf
                <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 20px 24px;">
                    <h5 class="modal-title d-flex align-items-center gap-2" style="font-size: 1.1rem; font-weight: 700; color: #5dade2;">
                        <i class="bi bi-arrow-counterclockwise"></i> Pulihkan Dari Arsip Cadangan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding: 24px; font-size: 0.88rem; line-height: 1.6;">
                    <p>Apakah Anda yakin ingin memulihkan database dari arsip berikut?</p>
                    <div class="p-3 mb-3 rounded-3" style="background:rgba(255,255,255,0.04);border:1px solid var(--border-color);font-family:monospace;word-break:break-all;" id="modal-restore-archive-name">
                        -
                    </div>
                    <div class="alert alert-info mb-0" style="background:rgba(52,152,219,0.12);border:1px solid rgba(52,152,219,0.3);color:#5dade2;font-size:0.82rem;border-radius:12px;">
                        <i class="bi bi-info-circle me-1"></i> Seluruh data saat ini akan ditimpa dengan data dari file backup ini. Sistem akan otomatis membuat salinan darurat sebelum restore.
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border-color); padding: 16px 24px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:10px;font-size:0.85rem;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="border-radius:10px;font-size:0.85rem;background:#3498db;border:none;">
                        <i class="bi bi-arrow-repeat me-1"></i> Ya, Pulihkan Database
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Hapus Arsip --}}
<div class="modal fade" id="modalConfirmDeleteArchive" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #142019; border: 1px solid rgba(231,76,60,0.4); border-radius: 20px; color: var(--text-light);">
            <form id="form-delete-archive" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-header" style="border-bottom: 1px solid var(--border-color); padding: 20px 24px;">
                    <h5 class="modal-title d-flex align-items-center gap-2" style="font-size: 1.1rem; font-weight: 700; color: #f1948a;">
                        <i class="bi bi-trash3-fill"></i> Hapus File Arsip Backup
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding: 24px; font-size: 0.88rem; line-height: 1.6;">
                    <p>Apakah Anda yakin ingin menghapus file backup berikut secara permanen dari server?</p>
                    <div class="p-3 rounded-3" style="background:rgba(255,255,255,0.04);border:1px solid var(--border-color);font-family:monospace;word-break:break-all;" id="modal-delete-archive-name">
                        -
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--border-color); padding: 16px 24px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:10px;font-size:0.85rem;">Batal</button>
                    <button type="submit" class="btn btn-danger" style="border-radius:10px;font-size:0.85rem;background:#e74c3c;border:none;">
                        <i class="bi bi-trash-fill me-1"></i> Hapus Permanen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function onSqlFileSelected(input) {
    const label = document.getElementById('file-upload-label');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
        label.innerHTML = `<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> ${file.name}</span> (${sizeMb} MB)`;
    } else {
        label.innerText = 'Pilih file .SQL dari komputer Anda';
    }
}

function triggerImportModal() {
    const fileInput = document.getElementById('input-sql-file');
    const checkConfirm = document.getElementById('check-confirm');

    if (!fileInput.files || !fileInput.files[0]) {
        alert('Silakan pilih file .SQL terlebih dahulu.');
        fileInput.click();
        return;
    }

    if (!checkConfirm.checked) {
        alert('Silakan centang kotak konfirmasi pemulihan database sebelum melanjutkan.');
        checkConfirm.focus();
        return;
    }

    document.getElementById('modal-import-filename').innerText = fileInput.files[0].name;
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmImport'));
    modal.show();
}

function submitImportForm() {
    const btn = document.getElementById('btn-submit-import');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Sedang memproses impor database...';
    document.getElementById('form-import-db').submit();
}

function confirmRestoreArchive(filename, actionUrl) {
    document.getElementById('modal-restore-archive-name').innerText = filename;
    document.getElementById('form-restore-archive').action = actionUrl;
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmRestoreArchive'));
    modal.show();
}

function confirmDeleteArchive(filename, actionUrl) {
    document.getElementById('modal-delete-archive-name').innerText = filename;
    document.getElementById('form-delete-archive').action = actionUrl;
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmDeleteArchive'));
    modal.show();
}

function toggleTableDetails() {
    const wrapper = document.getElementById('wrapper-tables-list');
    const icon = document.getElementById('icon-chevron-tables');
    if (wrapper.style.display === 'none') {
        wrapper.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        wrapper.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}
</script>
@endsection
