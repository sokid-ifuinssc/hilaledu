@extends('layouts.app')

@section('title', 'Modul Terpadu HilalEdu')
@section('page-title', 'Modul Terpadu HilalEdu')

@section('dashboard-styles')
.page-header {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 14px; margin-bottom: 24px;
}
.page-header h4 { font-size: 1.25rem; font-weight: 700; margin: 0; }

.stats-mini {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 28px;
}
.stat-mini-card {
    background: var(--bg-card); border: 1px solid var(--border-color);
    border-radius: 16px; padding: 18px 22px;
    display: flex; align-items: center; gap: 16px;
    transition: all 0.3s ease;
}
.stat-mini-card:hover {
    background: var(--bg-card-hover);
    border-color: rgba(255, 255, 255, 0.12);
}
.stat-mini-icon {
    width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;
}
.stat-mini-value { font-size: 1.6rem; font-weight: 800; line-height: 1.2; }
.stat-mini-label { font-size: 0.8rem; color: var(--text-muted); }

.apps-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px; }

.app-card {
    background: var(--bg-card); border: 1px solid var(--border-color);
    border-radius: 20px; overflow: hidden;
    transition: all 0.35s ease; position: relative;
    display: flex; flex-direction: column;
}
.app-card:hover {
    transform: translateY(-4px);
    border-color: rgba(255,255,255,0.14);
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
}
.app-card-stripe { height: 4px; width: 100%; }
.app-card-header {
    padding: 22px 22px 14px;
    display: flex; align-items: flex-start; gap: 16px;
}
.app-icon-wrap {
    width: 58px; height: 58px; border-radius: 16px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.7rem;
}
.app-card-title { font-size: 1.05rem; font-weight: 700; margin-bottom: 4px; line-height: 1.3; }
.app-card-desc { font-size: 0.82rem; color: var(--text-muted); line-height: 1.5; }

.app-card-body { padding: 0 22px 18px; flex: 1; display: flex; flex-direction: column; gap: 14px; }

.app-info-row {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    padding: 10px 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    font-size: 0.82rem;
}
.app-info-item {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
}
.app-info-label { color: var(--text-muted); display: flex; align-items: center; gap: 6px; }
.app-info-val { font-weight: 500; text-align: right; word-break: break-all; }

.app-features {
    display: flex; flex-wrap: wrap; gap: 6px;
}
.feature-chip {
    font-size: 0.72rem; padding: 3px 10px; border-radius: 20px;
    background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);
    color: var(--text-muted); white-space: nowrap;
}

.app-card-footer {
    padding: 14px 22px; border-top: 1px solid var(--border-color);
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(0, 0, 0, 0.15);
    gap: 10px; flex-wrap: wrap;
}

.badge-status { font-size: 0.72rem; padding: 4px 12px; border-radius: 20px; font-weight: 600; }
.badge-active   { background: rgba(46,204,113,0.15); color: #58d68d; border: 1px solid rgba(46,204,113,0.3); }
.badge-inactive { background: rgba(231,76,60,0.15);  color: #f1948a; border: 1px solid rgba(231,76,60,0.3); }
.badge-coming   { background: rgba(212,168,67,0.12); color: #d4a843; border: 1px solid rgba(212,168,67,0.25); }

.badge-db-ok    { background: rgba(46,204,113,0.12); color: #58d68d; border: 1px solid rgba(46,204,113,0.25); border-radius: 8px; padding: 3px 8px; font-size: 0.74rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.badge-db-err   { background: rgba(231,76,60,0.12);  color: #f1948a; border: 1px solid rgba(231,76,60,0.25); border-radius: 8px; padding: 3px 8px; font-size: 0.74rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.badge-db-none  { background: rgba(255,255,255,0.05); color: var(--text-muted); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 3px 8px; font-size: 0.74rem; }

.btn-action-sm {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 12px; border-radius: 9px; font-size: 0.78rem; font-weight: 500;
    text-decoration: none; transition: all 0.2s ease; cursor: pointer;
    font-family: 'Poppins', sans-serif;
    border: 1px solid rgba(255,255,255,0.08);
    background: rgba(255,255,255,0.05);
    color: var(--text-light);
}
.btn-action-sm:hover { background: rgba(255,255,255,0.12); color: white; }

.btn-action-primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border: none; color: white;
}
.btn-action-primary:hover {
    box-shadow: 0 4px 14px rgba(26,86,50,0.4);
    color: white;
}

.code-snippet-box {
    background: #080d0b;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    padding: 14px 18px;
    font-family: 'Consolas', 'Courier New', monospace;
    font-size: 0.82rem;
    color: #a8d5ba;
    overflow-x: auto;
    position: relative;
}
.btn-copy-code {
    position: absolute; top: 10px; right: 10px;
    background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);
    color: #fff; border-radius: 6px; padding: 3px 8px; font-size: 0.72rem; cursor: pointer;
}
@endsection

@section('content')

{{-- Notifikasi / Feedback --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert" style="background:rgba(46,204,113,0.15);border:1px solid rgba(46,204,113,0.3);color:#58d68d;border-radius:14px;">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert" style="background:rgba(212,168,67,0.15);border:1px solid rgba(212,168,67,0.3);color:#e8c96a;border-radius:14px;">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div>{{ session('warning') }}</div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert" style="background:rgba(231,76,60,0.15);border:1px solid rgba(231,76,60,0.3);color:#f1948a;border-radius:14px;">
        <i class="bi bi-x-circle-fill fs-5"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Header Halaman & Action Buttons --}}
<div class="page-header">
    <div>
        <h4><i class="bi bi-grid-3x3-gap-fill me-2" style="color:var(--accent-gold);"></i>Modul Terpadu HilalEdu</h4>
        <p style="font-size:0.84rem;color:var(--text-muted);margin:4px 0 0;">
            Pusat kendali modul terpadu, integrasi database internal, dan pengelolaan akses pengguna HilalEdu.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <button type="button" class="btn-action-sm" data-bs-toggle="modal" data-bs-target="#integrationGuideModal" style="background:rgba(212,168,67,0.12);color:var(--accent-gold);border-color:rgba(212,168,67,0.3);">
            <i class="bi bi-book-half"></i>
            <span>Panduan Modul HilalEdu</span>
        </button>

        <form action="{{ route('superadmin.apps.sync-all-users') }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menyinkronkan seluruh {{ $totalUsers }} pengguna ke SEMUA database aplikasi yang aktif?');">
            @csrf
            <button type="submit" class="btn-action-sm" style="background:rgba(46,204,113,0.15);color:#58d68d;border-color:rgba(46,204,113,0.35);">
                <i class="bi bi-arrow-repeat"></i>
                <span>⚡ Sinkronkan Semua Akun</span>
            </button>
        </form>

        <a href="{{ route('superadmin.apps.create') }}" class="btn-action-sm btn-action-primary">
            <i class="bi bi-plus-lg"></i>
            <span>Tambah Aplikasi Baru</span>
        </a>
    </div>
</div>

{{-- Mini Stats --}}
<div class="stats-mini">
    <div class="stat-mini-card">
        <div class="stat-mini-icon" style="background:rgba(212,168,67,0.15);color:var(--accent-gold);">
            <i class="bi bi-grid-fill"></i>
        </div>
        <div>
            <div class="stat-mini-value">{{ $totalApps }}</div>
            <div class="stat-mini-label">Total Aplikasi Terdaftar</div>
        </div>
    </div>

    <div class="stat-mini-card">
        <div class="stat-mini-icon" style="background:rgba(46,204,113,0.15);color:#58d68d;">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <div>
            <div class="stat-mini-value" style="color:#58d68d;">{{ $activeApps }}</div>
            <div class="stat-mini-label">Aplikasi Berstatus Aktif</div>
        </div>
    </div>

    <div class="stat-mini-card">
        @php
            $connectedCount = 0;
            foreach($dbStatus as $st) {
                if ($st['is_connected']) $connectedCount++;
            }
        @endphp
        <div class="stat-mini-icon" style="background:rgba(52,152,219,0.15);color:#3498db;">
            <i class="bi bi-database-check"></i>
        </div>
        <div>
            <div class="stat-mini-value" style="color:#3498db;">{{ $connectedCount }}</div>
            <div class="stat-mini-label">Database Sub-Sistem Terhubung</div>
        </div>
    </div>

    <div class="stat-mini-card">
        <div class="stat-mini-icon" style="background:rgba(155,89,182,0.15);color:#9b59b6;">
            <i class="bi bi-people-fill"></i>
        </div>
        <div>
            <div class="stat-mini-value" style="color:#9b59b6;">{{ $totalUsers }}</div>
            <div class="stat-mini-label">Akun Pengguna di Master</div>
        </div>
    </div>
</div>

{{-- Grid Aplikasi --}}
@if($apps->isEmpty())
    <div class="text-center p-5 rounded-4" style="background:var(--bg-card);border:1px dashed var(--border-color);">
        <i class="bi bi-grid-3x3-gap" style="font-size:3rem;opacity:0.25;display:block;margin-bottom:12px;"></i>
        <h5>Belum Ada Modul Terdaftar</h5>
        <p style="color:var(--text-muted);font-size:0.88rem;">Daftarkan modul ekosistem pertama Anda untuk memulai pengelolaan terpadu HilalEdu.</p>
        <a href="{{ route('superadmin.apps.create') }}" class="btn-action-sm btn-action-primary mt-2">
            <i class="bi bi-plus-lg me-1"></i> Tambah Modul
        </a>
    </div>
@else
    <div class="apps-grid">
        @foreach($apps as $app)
            @php
                $dbInfo = $dbStatus[$app->id] ?? null;
            @endphp
            <div class="app-card">
                <div class="app-card-stripe" style="background: {{ $app->color }};"></div>

                <div class="app-card-header">
                    <div class="app-icon-wrap" style="background: {{ $app->color }}22; color: {{ $app->color }};">
                        <i class="{{ $app->icon }}"></i>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div class="app-card-title text-truncate">{{ $app->name }}</div>
                            <span class="badge-status badge-{{ $app->status }}">
                                {{ $app->statusLabel() }}
                            </span>
                        </div>
                        <div class="app-card-desc">{{ Str::limit($app->description, 80) }}</div>
                    </div>
                </div>

                <div class="app-card-body">
                    {{-- Detail Integrasi (URL, DB, SSO) --}}
                    <div class="app-info-row">
                        <div class="app-info-item">
                            <span class="app-info-label"><i class="bi bi-link-45deg"></i> Alamat URL:</span>
                            <span class="app-info-val">
                                @if($app->url)
                                    <a href="{{ $app->url }}" target="_blank" style="color:var(--primary-lighter);text-decoration:none;" title="Buka alamat langsung aplikasi">
                                        {{ Str::limit($app->url, 28) }} <i class="bi bi-box-arrow-up-right ms-1" style="font-size:0.75rem;"></i>
                                    </a>
                                @else
                                    <span class="text-muted fst-italic">Belum diatur</span>
                                @endif
                            </span>
                        </div>

                        <div class="app-info-item">
                            <span class="app-info-label"><i class="bi bi-database"></i> Database:</span>
                            <span class="app-info-val">
                                @if($app->hasDatabase())
                                    @if($app->slug === 'hilaledu')
                                        <span class="badge-db-ok"><i class="bi bi-check-circle-fill"></i> db_hilaledu (Master)</span>
                                    @elseif($dbInfo && $dbInfo['is_connected'])
                                        <span class="badge-db-ok" title="Database terhubung, {{ $dbInfo['user_count'] }} akun tersinkron">
                                            <i class="bi bi-check-circle-fill"></i> {{ $app->database_name }} ({{ $dbInfo['user_count'] }} user)
                                        </span>
                                    @else
                                        <span class="badge-db-err" title="{{ $dbInfo['error'] ?? 'Gagal koneksi' }}">
                                            <i class="bi bi-exclamation-triangle-fill"></i> {{ $app->database_name }} (Gagal)
                                        </span>
                                    @endif
                                @else
                                    <span class="badge-db-none">Tidak ada DB</span>
                                @endif
                            </span>
                        </div>

                        <div class="app-info-item">
                            <span class="app-info-label"><i class="bi bi-shield-lock"></i> Akses Terpadu:</span>
                            <span class="app-info-val">
                                @if($app->sso_enabled)
                                    <span style="color:#58d68d;font-size:0.75rem;"><i class="bi bi-check2-circle me-1"></i>Aktif</span>
                                @else
                                    <span class="text-muted" style="font-size:0.75rem;">Nonaktif</span>
                                @endif
                                @if($app->auto_sync)
                                    <span class="ms-2" style="color:var(--accent-gold);font-size:0.75rem;"><i class="bi bi-arrow-repeat me-1"></i>Auto-Sync</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Features Tags --}}
                    @if($app->features)
                        <div class="app-features">
                            @foreach(array_slice($app->features, 0, 4) as $feature)
                                <span class="feature-chip"><i class="bi bi-check2 me-1"></i>{{ $feature }}</span>
                            @endforeach
                            @if(count($app->features) > 4)
                                <span class="feature-chip">+{{ count($app->features) - 4 }}</span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Card Footer & Actions --}}
                <div class="app-card-footer">
                    <div class="d-flex align-items-center gap-2">
                        @if($app->slug !== 'hilaledu' && $app->hasDatabase())
                            {{-- Tombol Uji Koneksi --}}
                            <form action="{{ route('superadmin.apps.test-db', $app) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn-action-sm" title="Uji apakah database MySQL aplikasi ini dapat diakses">
                                    <i class="bi bi-plug"></i> Tes DB
                                </button>
                            </form>

                            {{-- Tombol Sinkron Pengguna --}}
                            <form action="{{ route('superadmin.apps.sync-users', $app) }}" method="POST" class="d-inline" onsubmit="return confirm('Sinkronkan seluruh pengguna ke database {{ $app->database_name }}?');">
                                @csrf
                                <button type="submit" class="btn-action-sm" title="Kirim seluruh akun pengguna ke database aplikasi ini">
                                    <i class="bi bi-arrow-repeat"></i> Sync
                                </button>
                            </form>
                        @endif

                        @if($app->isActive() && $app->url)
                            <a href="{{ route('sso.launch', $app->slug) }}" target="_blank" class="btn-action-sm" style="color:var(--accent-gold);border-color:rgba(212,168,67,0.3);" title="Buka modul HilalEdu">
                                <i class="bi bi-box-arrow-up-right"></i> Masuk
                            </a>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('superadmin.apps.edit', $app) }}" class="btn-action-sm" title="Ubah pengaturan modul">
                            <i class="bi bi-gear"></i> Setting
                        </a>
                        <a href="{{ route('superadmin.apps.show', $app) }}" class="btn-action-sm" title="Detail penugasan koordinator & kredensial">
                            <i class="bi bi-person-badge"></i> Detail
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- ============================================================ --}}
{{-- MODAL PANDUAN MODUL HILALEDU TERPADU                        --}}
{{-- ============================================================ --}}
<div class="modal fade" id="integrationGuideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content" style="background:#0e1713;border:1px solid var(--border-color);border-radius:20px;color:var(--text-light);">
            <div class="modal-header" style="border-bottom:1px solid var(--border-color);padding:20px 26px;">
                <div>
                    <h5 class="modal-title fw-bold mb-1"><i class="bi bi-book-half me-2 text-warning"></i>Panduan Modul HilalEdu</h5>
                    <p class="text-muted small mb-0">Pengelolaan modul Akademik, Monitoring BK, Prakerin, Keuangan, Koperasi, dan Tracer Study secara terpadu.</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" style="padding:26px;">
                {{-- Tabs Panduan --}}
                <ul class="nav nav-pills mb-4 gap-2" id="guideTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-db-sync" style="border-radius:10px;font-size:0.85rem;">
                            <i class="bi bi-database me-1"></i> 1. Database Terpadu (db_hilaledu)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-roles" style="border-radius:10px;font-size:0.85rem;">
                            <i class="bi bi-shield-check me-1"></i> 2. Hak Akses & Peran Pengguna
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-modules" style="border-radius:10px;font-size:0.85rem;">
                            <i class="bi bi-grid-3x3-gap me-1"></i> 3. Ekosistem Modul Terpadu
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    {{-- TAB 1: Database Terpadu --}}
                    <div class="tab-pane fade show active" id="tab-db-sync">
                        <div class="alert alert-info mb-3" style="background:rgba(52,152,219,0.12);border:1px solid rgba(52,152,219,0.3);color:#7fb3d5;border-radius:12px;font-size:0.85rem;">
                            <i class="bi bi-info-circle-fill me-2"></i><strong>Database Terpadu:</strong> HilalEdu menggunakan satu database utama <code style="color:#fff;">db_hilaledu</code> untuk seluruh modul sekolah.
                        </div>

                        <h6 class="fw-bold mb-2 text-white">Prinsip Pengelolaan Data:</h6>
                        <ol class="small text-muted" style="line-height:1.7;">
                            <li>Seluruh modul terintegrasi langsung ke database <code style="color:#58d68d;">db_hilaledu</code> tanpa dependensi database eksternal.</li>
                            <li>Tabel <code class="text-white">users</code> terpusat untuk otentikasi seluruh warga sekolah (Super Admin, Guru, Siswa, Tendik).</li>
                            <li>Data siswa, kelas, guru, dan tahun pelajaran saling terhubung secara otomatis di modul Akademik, BK, dan Prakerin.</li>
                        </ol>

                        <div class="code-snippet-box">
<pre style="margin:0;"><code>// Konfigurasi database tunggal HilalEdu (.env)
DB_CONNECTION=mysql
DB_DATABASE=db_hilaledu</code></pre>
                        </div>
                    </div>

                    {{-- TAB 2: Hak Akses & Peran --}}
                    <div class="tab-pane fade" id="tab-roles">
                        <div class="alert alert-success mb-3" style="background:rgba(46,204,113,0.12);border:1px solid rgba(46,204,113,0.3);color:#58d68d;border-radius:12px;font-size:0.85rem;">
                            <i class="bi bi-person-check-fill me-2"></i><strong>Otentikasi Tunggal:</strong> Setiap pengguna masuk menggunakan 1 akun terpadu sesuai perannya di sekolah.
                        </div>

                        <h6 class="fw-bold mb-2 text-white small">Struktur Hak Akses Pengguna:</h6>
                        <ul class="small text-muted mb-3" style="line-height:1.8;">
                            <li><strong class="text-white">Super Admin:</strong> Kendali penuh sistem, master data pokok, pengaturan sekolah, manajemen akun, dan backup data.</li>
                            <li><strong class="text-white">Admin Koordinator:</strong> Mengelola modul spesifik (Akademik, Monitoring BK, Prakerin, Keuangan, Koperasi, Tracer Study).</li>
                            <li><strong class="text-white">Guru & Wali Kelas:</strong> Presensi mengajar, penginputan jurnal KBM & nilai, pembimbing prakerin, serta monitoring pembinaan siswa.</li>
                            <li><strong class="text-white">Tenaga Kependidikan (Tendik):</strong> Pengelolaan administrasi sekolah, jadwal piket, dan data kepegawaian.</li>
                            <li><strong class="text-white">Siswa:</strong> Memantau jadwal belajar, presensi, jurnal penempatan prakerin, dan info tagihan sekolah.</li>
                        </ul>
                    </div>

                    {{-- TAB 3: Ekosistem Modul --}}
                    <div class="tab-pane fade" id="tab-modules">
                        <div class="alert alert-warning mb-3" style="background:rgba(212,168,67,0.12);border:1px solid rgba(212,168,67,0.3);color:var(--accent-gold);border-radius:12px;font-size:0.85rem;">
                            <i class="bi bi-lightning-charge-fill me-2"></i><strong>Navigasi Cepat:</strong> Seluruh modul dapat langsung diakses melalui sidebar atau tombol aksi cepat di dashboard.
                        </div>

                        <h6 class="fw-bold mb-2 text-white small">Modul yang Aktif & Terintegrasi:</h6>
                        <div class="d-flex flex-column gap-2 small">
                            <div class="p-2 rounded" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);">
                                <strong class="text-emerald-400">Akademik & Pembelajaran:</strong> Jadwal pelajaran, kurikulum merdeka, presensi guru & KBM, kalender pendidikan.
                            </div>
                            <div class="p-2 rounded" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);">
                                <strong class="text-red-400">Monitoring BK:</strong> Catatan pelanggaran, penanganan kasus siswa, bimbingan konseling, akumulasi poin.
                            </div>
                            <div class="p-2 rounded" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);">
                                <strong class="text-amber-400">Prakerin (PKL):</strong> Mitra DU/DI, penempatan siswa, jurnal kegiatan harian, sertifikat dan penilaian.
                            </div>
                            <div class="p-2 rounded" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);">
                                <strong class="text-sky-400">Layanan Pendukung:</strong> Keuangan SPP, Koperasi Sekolah, Tracer Study Alumni, dan Penggajian (HilalPay).
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="border-top:1px solid var(--border-color);padding:16px 26px;">
                <button type="button" class="btn-action-sm btn-action-primary" data-bs-dismiss="modal">Tutup Panduan</button>
            </div>
        </div>
    </div>
</div>

@endsection
