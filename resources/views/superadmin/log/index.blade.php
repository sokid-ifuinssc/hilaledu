@extends('layouts.app')

@section('title', 'Log Akses & Keaktifan Pengguna')
@section('page-title', 'Log Akses, Pemantauan Lokasi & Keaktifan Akun')

@section('dashboard-styles')
/* Leaflet CSS via CDN */
@import url('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');

.log-page-container {
    padding-bottom: 40px;
}

.log-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
}

.log-title-box h3 {
    font-size: 1.4rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.log-title-box p {
    font-size: 0.86rem;
    color: #64748b;
    margin: 4px 0 0 0;
}

/* Stat Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.25s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.stat-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    border-color: #cbd5e1;
}

.stat-icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}

.stat-val {
    font-size: 1.45rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
}

.stat-lbl {
    font-size: 0.78rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 2px;
}

/* Modern Tabs */
.nav-tabs-modern {
    display: flex;
    gap: 8px;
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 24px;
    overflow-x: auto;
    padding-bottom: 2px;
}

.tab-link-modern {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    font-size: 0.92rem;
    font-weight: 600;
    color: #64748b;
    border-radius: 12px 12px 0 0;
    text-decoration: none;
    transition: all 0.2s ease;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    white-space: nowrap;
}

.tab-link-modern:hover {
    color: #0f172a;
    background: #f8fafc;
}

.tab-link-modern.active {
    color: #059669;
    border-bottom-color: #059669;
    background: #ffffff;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.02);
}

/* Map Card */
.map-card-wrapper {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    margin-bottom: 24px;
}

.map-card-header {
    padding: 18px 24px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    background: #ffffff;
}

.map-controls {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

#loginMap {
    height: 520px;
    width: 100%;
    z-index: 10;
}

/* Custom Marker Pins */
.custom-map-marker {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: white;
    font-weight: 700;
    font-size: 0.78rem;
    box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.8), 0 4px 12px rgba(0, 0, 0, 0.3);
    transition: transform 0.2s ease;
}

.custom-map-marker:hover {
    transform: scale(1.18);
}

.marker-success {
    background: linear-gradient(135deg, #10b981, #059669);
}

.marker-warning {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.marker-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

/* Feed & Sidebar Widgets */
.card-modern {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    overflow: hidden;
    margin-bottom: 24px;
}

.card-modern-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.card-modern-title {
    font-size: 0.98rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.feed-list {
    max-height: 480px;
    overflow-y: auto;
}

.feed-item {
    padding: 14px 20px;
    border-bottom: 1px solid #f8fafc;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    transition: background 0.15s ease;
}

.feed-item:last-child {
    border-bottom: none;
}

.feed-item:hover {
    background: #f8fafc;
}

.feed-avatar {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    color: #334155;
    flex-shrink: 0;
}

/* Table Design */
.table-modern {
    width: 100%;
    margin-bottom: 0;
}

.table-modern thead th {
    background: #f8fafc;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 18px;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}

.table-modern tbody td {
    padding: 14px 18px;
    vertical-align: middle;
    color: #1e293b;
    font-size: 0.88rem;
    border-bottom: 1px solid #f1f5f9;
}

.table-modern tbody tr:hover {
    background: #f8fafc;
}

/* Badges */
.badge-role {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: inline-block;
}

.badge-role-superadmin { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.badge-role-admin { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.badge-role-guru { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.badge-role-tendik { background: #fdf4ff; color: #9333ea; border: 1px solid #f5d0fe; }
.badge-role-siswa { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }

.badge-city-chip {
    font-size: 0.74rem;
    background: #f1f5f9;
    color: #334155;
    padding: 3px 8px;
    border-radius: 6px;
    margin: 2px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Buttons */
.btn-action-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    transition: all 0.2s ease;
    cursor: pointer;
}

.btn-action-icon:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #cbd5e1;
}

.btn-action-icon.danger:hover {
    background: #fee2e2;
    color: #ef4444;
    border-color: #fca5a5;
}

.btn-action-icon.warning:hover {
    background: #fef3c7;
    color: #d97706;
    border-color: #fcd34d;
}

.btn-action-icon.success:hover {
    background: #dcfce7;
    color: #16a34a;
    border-color: #86efac;
}

/* Modals */
.modal-content-modern {
    border-radius: 20px;
    border: none;
    box-shadow: 0 20px 40px rgba(0,0,0,0.12);
}

.modal-header-modern {
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
}
@endsection

@section('content')
<div class="container-fluid log-page-container">

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius: 14px; background: #ecfdf5; color: #065f46;" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
        <strong>Sukses!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" style="border-radius: 14px; background: #fef2f2; color: #991b1b;" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <strong>Perhatian!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Page Header --}}
    <div class="log-header">
        <div class="log-title-box">
            <h3>
                <i class="bi bi-shield-check text-emerald-600"></i>
                Log Akses, Peta Lokasi & Keaktifan Akun
            </h3>
            <p>Deteksi siapa saja yang login, visualisasi sebaran lokasi peta Indonesia/Dunia, serta audit & solusi masalah akun.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            {{-- Tombol Ekspor CSV --}}
            <a href="{{ route('superadmin.log-aktivitas.export') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 fw-semibold shadow-sm d-flex align-items-center gap-2 bg-white">
                <i class="bi bi-file-earmark-spreadsheet text-success fs-6"></i>
                <span>Ekspor CSV</span>
            </a>

            {{-- Tombol Generate Sample Data (jika perlu eksplorasi peta) --}}
            <form action="{{ route('superadmin.log-aktivitas.sample-data') }}" method="POST" class="d-inline" onsubmit="return confirm('Buat data simulasi log login dari kota-kota di Indonesia untuk mengisi peta & analitik?')">
                @csrf
                <button type="submit" class="btn btn-light btn-sm rounded-pill px-3 py-2 fw-semibold text-slate-700 border shadow-sm d-flex align-items-center gap-2" title="Isi riwayat simulasi realistis dari berbagai kota di Indonesia">
                    <i class="bi bi-magic text-purple-600"></i>
                    <span>Simulasi Data Peta</span>
                </button>
            </form>

            {{-- Tombol Bersihkan Log --}}
            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-2 fw-semibold shadow-sm d-flex align-items-center gap-2 bg-white" data-bs-toggle="modal" data-bs-target="#clearLogModal">
                <i class="bi bi-trash3"></i>
                <span>Bersihkan</span>
            </button>
        </div>
    </div>

    {{-- Summary Statistics Grid --}}
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-icon-wrap" style="background:#ecfdf5; color:#059669;">
                <i class="bi bi-box-arrow-in-right"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($totalLogins) }}</div>
                <div class="stat-lbl">Total Akses Login</div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon-wrap" style="background:#f0fdf4; color:#16a34a;">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($uniqueUsersCount) }} <span class="fs-6 fw-normal text-slate-400">/ {{ number_format($totalRegisteredUsers) }}</span></div>
                <div class="stat-lbl">User Pernah Login</div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon-wrap" style="background:#eff6ff; color:#2563eb;">
                <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($citiesCount) }}</div>
                <div class="stat-lbl">Kota / Lokasi Terdeteksi</div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon-wrap" style="background:#fef3c7; color:#d97706;">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($activeUsers7d) }}</div>
                <div class="stat-lbl">Aktif 7 Hari Terakhir</div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon-wrap" style="background:{{ $failedCount > 0 ? '#fef2f2' : '#f8fafc' }}; color:{{ $failedCount > 0 ? '#dc2626' : '#64748b' }};">
                <i class="bi bi-shield-exclamation"></i>
            </div>
            <div>
                <div class="stat-val">{{ number_format($failedCount) }}</div>
                <div class="stat-lbl">Percobaan Gagal</div>
            </div>
        </div>
    </div>

    {{-- Modern Navigation Tabs --}}
    <nav class="nav-tabs-modern">
        <a href="{{ route('superadmin.log-aktivitas.index', ['tab' => 'peta']) }}" class="tab-link-modern {{ $activeTab === 'peta' ? 'active' : '' }}">
            <i class="bi bi-map-fill"></i>
            <span>Peta Lokasi & Pemantauan Interaktif</span>
        </a>
        <a href="{{ route('superadmin.log-aktivitas.index', ['tab' => 'riwayat']) }}" class="tab-link-modern {{ $activeTab === 'riwayat' ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i>
            <span>Riwayat Log Login Lengkap ({{ number_format($totalLogins) }})</span>
        </a>
        <a href="{{ route('superadmin.log-aktivitas.index', ['tab' => 'audit']) }}" class="tab-link-modern {{ $activeTab === 'audit' ? 'active' : '' }}">
            <i class="bi bi-person-gear"></i>
            <span>Audit Keaktifan Akun & Pemecahan Masalah ({{ number_format($totalRegisteredUsers) }})</span>
        </a>
    </nav>

    {{-- TAB 1: PETA INTERAKTIF INDONESIA / DUNIA --}}
    @if($activeTab === 'peta')
    <div class="row">
        {{-- Peta Utama --}}
        <div class="col-lg-8">
            <div class="map-card-wrapper">
                <div class="map-card-header">
                    <div>
                        <h5 class="fw-bold m-0 text-slate-800 d-flex align-items-center gap-2">
                            <i class="bi bi-globe-asia-australia text-emerald-600"></i>
                            Peta Sebaran Lokasi Akses Pengguna
                        </h5>
                        <small class="text-slate-500">Mendeteksi dari mana saja pengguna masuk dan jumlah user per titik lokasi.</small>
                    </div>
                    <div class="map-controls">
                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 shadow-sm" onclick="focusIndonesia()">
                            <i class="bi bi-crosshair me-1 text-primary"></i> Peta Indonesia
                        </button>
                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 shadow-sm" onclick="focusWorld()">
                            <i class="bi bi-globe me-1 text-secondary"></i> Dunia
                        </button>
                        <select id="mapFilterStatus" class="form-select form-select-sm rounded-pill w-auto border shadow-sm" onchange="loadMapPoints()">
                            <option value="all">Semua Status</option>
                            <option value="success">Hanya Sukses</option>
                            <option value="failed">Ada Percobaan Gagal</option>
                        </select>
                        <select id="mapFilterPeriod" class="form-select form-select-sm rounded-pill w-auto border shadow-sm" onchange="loadMapPoints()">
                            <option value="all">Semua Waktu</option>
                            <option value="today">Hari Ini</option>
                            <option value="7days">7 Hari Terakhir</option>
                            <option value="30days">30 Hari Terakhir</option>
                        </select>
                    </div>
                </div>

                <div id="loginMap"></div>

                <div class="p-3 bg-slate-50 border-top d-flex align-items-center justify-content-between flex-wrap gap-2 text-xs text-slate-600">
                    <div class="d-flex align-items-center gap-3">
                        <span class="d-flex align-items-center gap-1.5"><span class="d-inline-block rounded-full bg-emerald-500 w-3 h-3"></span> Login Sukses</span>
                        <span class="d-flex align-items-center gap-1.5"><span class="d-inline-block rounded-full bg-amber-500 w-3 h-3"></span> Campuran</span>
                        <span class="d-flex align-items-center gap-1.5"><span class="d-inline-block rounded-full bg-rose-500 w-3 h-3"></span> Gagal / Terindikasi</span>
                    </div>
                    <div id="mapStatusText" class="fw-semibold text-slate-700">
                        Memuat data titik sebaran...
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar Panel: Top Lokasi & Live Feed --}}
        <div class="col-lg-4">
            {{-- Top Kota Akses --}}
            <div class="card-modern mb-4">
                <div class="card-modern-header">
                    <h6 class="card-modern-title">
                        <i class="bi bi-building text-primary"></i>
                        Kota / Wilayah Terbanyak
                    </h6>
                    <span class="badge bg-slate-100 text-slate-600 rounded-pill">{{ count($topCities) }} Lokasi</span>
                </div>
                <div class="p-3">
                    @forelse($topCities as $city)
                    @php
                        $pct = $totalLogins > 0 ? round(($city->total / $totalLogins) * 100) : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold text-slate-800 text-sm">
                                <i class="bi bi-geo-alt text-emerald-600 me-1"></i> {{ $city->city ?: 'Kota Tidak Terdeteksi' }}
                                <small class="text-slate-400">({{ $city->region }})</small>
                            </span>
                            <span class="fw-bold text-slate-700 text-xs">{{ $city->total }} akses ({{ $pct }}%)</span>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 4px; background: #f1f5f9;">
                            <div class="progress-bar bg-emerald-500" role="progressbar" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-slate-400 text-sm">
                        <i class="bi bi-geo fs-3 d-block mb-1"></i>
                        Belum ada data lokasi tercatat.
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Feed Akses Login Terkini --}}
            <div class="card-modern">
                <div class="card-modern-header">
                    <h6 class="card-modern-title">
                        <i class="bi bi-activity text-emerald-600"></i>
                        Aktivitas Login Terkini
                    </h6>
                    <span class="badge bg-emerald-50 text-emerald-700 rounded-pill">Real-time</span>
                </div>
                <div class="feed-list">
                    @forelse($recentFeed as $feed)
                    <div class="feed-item">
                        <div class="feed-avatar" style="{{ $feed->status === 'success' ? 'background:#ecfdf5; color:#059669;' : 'background:#fef2f2; color:#dc2626;' }}">
                            {{ strtoupper(substr($feed->user_name ?? $feed->login_input ?? 'U', 0, 1)) }}
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-slate-800 text-sm text-truncate" style="max-width: 140px;" title="{{ $feed->user_name ?? $feed->login_input }}">
                                    {{ $feed->user_name ?? $feed->login_input }}
                                </span>
                                <span class="text-xs text-slate-400">{{ $feed->login_at ? $feed->login_at->diffForHumans() : '-' }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="badge-role badge-role-{{ strtolower($feed->user_role ?? 'siswa') }}">
                                    {{ $feed->user_role ?? 'Tamu' }}
                                </span>
                                @if($feed->status === 'success')
                                    <span class="badge bg-emerald-100 text-emerald-700 rounded-pill text-xs">Sukses</span>
                                @else
                                    <span class="badge bg-rose-100 text-rose-700 rounded-pill text-xs">Gagal</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500 mt-1 d-flex align-items-center gap-1 text-truncate">
                                <i class="bi {{ $feed->device_icon }} text-slate-400"></i>
                                <span>{{ $feed->city ? $feed->city . ', ' . $feed->country : 'Lokasi Intranet' }}</span>
                                <span class="text-slate-300">•</span>
                                <span class="text-slate-400">{{ $feed->ip_address }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-slate-400 text-sm">
                        Belum ada aktivitas login.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 2: RIWAYAT LOG LENGKAP --}}
    @if($activeTab === 'riwayat')
    <div class="card-modern">
        <div class="card-modern-header">
            <div>
                <h5 class="fw-bold m-0 text-slate-800 d-flex align-items-center gap-2">
                    <i class="bi bi-list-columns-reverse text-emerald-600"></i>
                    Daftar Riwayat Login Pengguna
                </h5>
                <small class="text-slate-500">Mencatat data lengkap siapa, kapan, IP, perangkat, browser, dan lokasi login.</small>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="p-3 bg-slate-50 border-bottom">
            <form action="{{ route('superadmin.log-aktivitas.index') }}" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="riwayat">

                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-slate-400"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Cari nama, email, IP, kota...">
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="role" class="form-select form-select-sm">
                        <option value="">Semua Peran</option>
                        <option value="superadmin" {{ request('role') == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                        <option value="tendik" {{ request('role') == 'tendik' ? 'selected' : '' }}>Tendik</option>
                        <option value="siswa" {{ request('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Sukses</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Gagal</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="period" class="form-select form-select-sm">
                        <option value="">Semua Waktu</option>
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="7days" {{ request('period') == '7days' ? 'selected' : '' }}>7 Hari Terakhir</option>
                        <option value="30days" {{ request('period') == '30days' ? 'selected' : '' }}>30 Hari Terakhir</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                        <i class="bi bi-filter"></i> Terapkan
                    </button>
                    @if(request()->hasAny(['search', 'role', 'status', 'period']))
                    <a href="{{ route('superadmin.log-aktivitas.index', ['tab' => 'riwayat']) }}" class="btn btn-sm btn-light border rounded-pill px-3">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Log --}}
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th>Lokasi & IP</th>
                        <th>Perangkat & Browser</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>
                            <div class="fw-semibold text-slate-800">{{ $log->login_at ? $log->login_at->format('d/m/Y H:i') : '-' }}</div>
                            <small class="text-slate-400">{{ $log->login_at ? $log->login_at->diffForHumans() : '-' }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="feed-avatar" style="width:34px; height:34px; font-size:0.8rem;">
                                    {{ strtoupper(substr($log->user_name ?? $log->login_input ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-slate-800">{{ $log->user_name ?? 'Pengguna Tamu' }}</div>
                                    <small class="text-slate-500">{{ $log->login_input }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-role badge-role-{{ strtolower($log->user_role ?? 'siswa') }}">
                                {{ $log->user_role ?? 'Tamu' }}
                            </span>
                        </td>
                        <td>
                            @if($log->status === 'success')
                                <span class="badge bg-emerald-100 text-emerald-800 rounded-pill px-2.5 py-1">
                                    <i class="bi bi-check-circle me-1"></i> Sukses
                                </span>
                            @else
                                <span class="badge bg-rose-100 text-rose-800 rounded-pill px-2.5 py-1" title="{{ $log->failure_reason }}">
                                    <i class="bi bi-x-circle me-1"></i> Gagal
                                </span>
                                @if($log->failure_reason)
                                <div class="text-xs text-rose-600 mt-1" style="max-width: 160px;">{{ $log->failure_reason }}</div>
                                @endif
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-slate-800">
                                <i class="bi bi-geo-alt text-emerald-600 me-1"></i>
                                {{ $log->city ? $log->city . ', ' . $log->country : 'Lokal / Intranet' }}
                            </div>
                            <small class="text-slate-500 font-monospace">{{ $log->ip_address }}</small>
                            @if($log->isp)
                            <div class="text-xs text-slate-400 mt-0.5">{{ $log->isp }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="text-slate-800 fw-semibold d-flex align-items-center gap-1.5">
                                <i class="bi {{ $log->device_icon }} text-slate-500"></i>
                                <span>{{ $log->browser ?? 'Browser' }}</span>
                            </div>
                            <small class="text-slate-400">{{ $log->platform }} ({{ $log->device_type }})</small>
                        </td>
                        <td>
                            @if($log->user_id)
                            <button type="button" class="btn btn-action-icon" title="Lihat Riwayat Akun Ini" onclick="showUserAuditModal({{ $log->user_id }})">
                                <i class="bi bi-clock-history"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-slate-400">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            Tidak ada data log yang sesuai dengan filter pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-sm text-slate-500">
                Menampilkan {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} data
            </span>
            {{ $logs->links() }}
        </div>
        @endif
    </div>
    @endif

    {{-- TAB 3: AUDIT KEAKTIFAN AKUN & PEMECAHAN MASALAH --}}
    @if($activeTab === 'audit')
    <div class="card-modern">
        <div class="card-modern-header">
            <div>
                <h5 class="fw-bold m-0 text-slate-800 d-flex align-items-center gap-2">
                    <i class="bi bi-person-check text-emerald-600"></i>
                    Audit Keaktifan Akun Terdaftar & Pemecahan Masalah
                </h5>
                <small class="text-slate-500">Memantau berapa kali setiap akun mengakses, dari lokasi mana saja, status keaktifan, serta tombol aksi perbaikan masalah langsung.</small>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="p-3 bg-slate-50 border-bottom">
            <form action="{{ route('superadmin.log-aktivitas.index') }}" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="audit">

                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-slate-400"></i></span>
                        <input type="text" name="audit_search" value="{{ request('audit_search') }}" class="form-control border-start-0" placeholder="Cari nama pengguna, username, email, NIP...">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="audit_role" class="form-select form-select-sm">
                        <option value="">Semua Peran</option>
                        <option value="superadmin" {{ request('audit_role') == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="admin" {{ request('audit_role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="guru" {{ request('audit_role') == 'guru' ? 'selected' : '' }}>Guru</option>
                        <option value="tendik" {{ request('audit_role') == 'tendik' ? 'selected' : '' }}>Tendik</option>
                        <option value="siswa" {{ request('audit_role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                        <i class="bi bi-filter"></i> Saring Data
                    </button>
                    @if(request()->hasAny(['audit_search', 'audit_role']))
                    <a href="{{ route('superadmin.log-aktivitas.index', ['tab' => 'audit']) }}" class="btn btn-sm btn-light border rounded-pill px-3">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Pengguna & Audit --}}
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Akun Pengguna</th>
                        <th>Peran</th>
                        <th>Frekuensi Akses</th>
                        <th>Riwayat Lokasi Akses</th>
                        <th>Akses Terakhir</th>
                        <th>Status Keaktifan</th>
                        <th class="text-end">Aksi Masalah Akun</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="feed-avatar" style="width:38px; height:38px;">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-slate-800 d-flex align-items-center gap-1.5">
                                        <span>{{ $u->name }}</span>
                                        @if(!$u->is_active)
                                            <span class="badge bg-rose-100 text-rose-700 text-xs">Terkunci</span>
                                        @endif
                                    </div>
                                    <small class="text-slate-500">{{ $u->username }} • {{ $u->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-role badge-role-{{ strtolower($u->role) }}">
                                {{ $u->role }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <span class="badge bg-emerald-100 text-emerald-800 rounded-pill px-2 py-0.5" title="Login Berhasil">
                                    <i class="bi bi-check-lg me-1"></i> {{ $u->total_success }}x
                                </span>
                                @if($u->total_failed > 0)
                                <span class="badge bg-rose-100 text-rose-800 rounded-pill px-2 py-0.5" title="Login Gagal">
                                    <i class="bi bi-x-lg me-1"></i> {{ $u->total_failed }}x
                                </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if(!empty($u->cities_list))
                                @foreach(array_slice($u->cities_list, 0, 3) as $c)
                                    <span class="badge-city-chip"><i class="bi bi-geo-alt-fill text-emerald-600"></i> {{ $c }}</span>
                                @endforeach
                                @if(count($u->cities_list) > 3)
                                    <span class="badge bg-slate-200 text-slate-700 rounded-pill text-xs">+{{ count($u->cities_list) - 3 }}</span>
                                @endif
                            @else
                                <span class="text-slate-400 text-xs">Belum ada lokasi</span>
                            @endif
                        </td>
                        <td>
                            @if($u->last_login)
                                <div class="fw-semibold text-slate-800 text-sm">{{ $u->last_login->diffForHumans() }}</div>
                                <small class="text-slate-400">{{ $u->last_login->format('d M Y, H:i') }}</small>
                            @else
                                <span class="text-slate-400 text-xs italic">Belum pernah masuk</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $u->keaktifan_class }}-subtle text-{{ $u->keaktifan_class }} border border-{{ $u->keaktifan_class }}-subtle rounded-pill px-2.5 py-1">
                                {{ $u->keaktifan_badge }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex align-items-center gap-1">
                                {{-- Tombol Audit Riwayat --}}
                                <button type="button" class="btn btn-action-icon" title="Lihat Riwayat & Sesi" onclick="showUserAuditModal({{ $u->id }})">
                                    <i class="bi bi-eye"></i>
                                </button>

                                {{-- Tombol Toggle Status (Aktifkan / Kunci) --}}
                                <form action="{{ route('superadmin.log-aktivitas.user-action', $u->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="toggle_status">
                                    <button type="submit" class="btn btn-action-icon {{ $u->is_active ? 'warning' : 'success' }}" title="{{ $u->is_active ? 'Kunci / Nonaktifkan Akun' : 'Buka Kunci / Aktifkan Akun' }}">
                                        <i class="bi {{ $u->is_active ? 'bi-lock' : 'bi-unlock' }}"></i>
                                    </button>
                                </form>

                                {{-- Tombol Reset Password Cepat --}}
                                <button type="button" class="btn btn-action-icon" title="Reset Kata Sandi Akun" onclick="openResetPasswordModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->username }}')">
                                    <i class="bi bi-key"></i>
                                </button>

                                {{-- Tombol Putuskan Sesi (Force Logout) --}}
                                <form action="{{ route('superadmin.log-aktivitas.user-action', $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Putuskan seluruh sesi aktif untuk {{ addslashes($u->name) }}? User akan otomatis logout di semua perangkat.')">
                                    @csrf
                                    <input type="hidden" name="action" value="revoke_sessions">
                                    <button type="submit" class="btn btn-action-icon danger" title="Putuskan Sesi Aktif (Force Logout)">
                                        <i class="bi bi-box-arrow-right"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-slate-400">
                            Tidak ada akun yang ditemukan sesuai filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-sm text-slate-500">
                Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} akun
            </span>
            {{ $users->links() }}
        </div>
        @endif
    </div>
    @endif

</div>

{{-- MODAL: DETAIL RIWAYAT AUDIT USER --}}
<div class="modal fade" id="userAuditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-content-modern">
            <div class="modal-header modal-header-modern">
                <div>
                    <h5 class="modal-title fw-bold text-slate-800 d-flex align-items-center gap-2" id="auditModalUserName">
                        <i class="bi bi-person-badge text-emerald-600"></i>
                        Detail Akses Pengguna
                    </h5>
                    <small class="text-slate-500" id="auditModalUserSub">Memuat informasi...</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Quick User Info Card --}}
                <div class="p-3 rounded-4 bg-slate-50 border mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <div class="text-xs text-slate-400 text-uppercase fw-bold">Status Akun</div>
                        <div class="fw-bold mt-1" id="auditModalStatusBadge">-</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 text-uppercase fw-bold">Peran</div>
                        <div class="fw-bold mt-1" id="auditModalRoleBadge">-</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 text-uppercase fw-bold">Password Saat Ini (Plain)</div>
                        <div class="fw-mono mt-1 text-slate-700 fw-bold" id="auditModalPasswordPlain">-</div>
                    </div>
                </div>

                {{-- Sesi Aktif --}}
                <div class="mb-4">
                    <h6 class="fw-bold text-slate-800 text-sm mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-display text-primary"></i> Sesi Aktif Saat Ini
                    </h6>
                    <div id="auditModalSessionsList" class="p-2 rounded-3 border bg-white text-xs">
                        Memuat sesi...
                    </div>
                </div>

                {{-- Riwayat Login Terakhir --}}
                <h6 class="fw-bold text-slate-800 text-sm mb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-emerald-600"></i> 30 Percobaan Akses Terakhir
                </h6>
                <div class="table-responsive border rounded-3">
                    <table class="table table-sm table-hover mb-0 text-xs">
                        <thead class="bg-slate-50">
                            <tr>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Lokasi</th>
                                <th>IP Address</th>
                                <th>Perangkat</th>
                            </tr>
                        </thead>
                        <tbody id="auditModalLogsTable">
                            <tr><td colspan="5" class="text-center py-3 text-slate-400">Memuat riwayat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-slate-50 border-top p-3">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: RESET PASSWORD CEPAT --}}
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-modern">
            <form id="resetPasswordForm" method="POST" action="">
                @csrf
                <input type="hidden" name="action" value="reset_password">
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title fw-bold text-slate-800 d-flex align-items-center gap-2">
                        <i class="bi bi-key-fill text-amber-500"></i>
                        Reset Kata Sandi Pengguna
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-sm text-slate-600">
                        Atur kata sandi baru untuk akun <strong id="resetTargetName"></strong> (<span id="resetTargetUser"></span>).
                    </p>
                    <div class="mb-3">
                        <label class="form-label text-xs fw-bold text-slate-700 text-uppercase">Kata Sandi Baru</label>
                        <div class="input-group">
                            <input type="text" name="new_password" id="inputNewPassword" class="form-control font-monospace" placeholder="Kosongkan untuk buat acak otomatis" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="generateRandomPass()">
                                <i class="bi bi-shuffle"></i> Acak
                            </button>
                        </div>
                        <small class="text-slate-400">Sandi akan disimpan dan disinkronkan ke password_plain agar mudah diinformasikan ke pengguna.</small>
                    </div>
                </div>
                <div class="modal-footer bg-slate-50 border-top p-3">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">
                        <i class="bi bi-check-lg me-1"></i> Simpan Sandi Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: BERSIHKAN LOG --}}
<div class="modal fade" id="clearLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-modern">
            <form action="{{ route('superadmin.log-aktivitas.clear') }}" method="POST">
                @csrf
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title fw-bold text-rose-700 d-flex align-items-center gap-2">
                        <i class="bi bi-trash3-fill"></i>
                        Bersihkan Riwayat Log Login
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-xs fw-bold text-slate-700 text-uppercase">Pilih Metode Pembersihan</label>
                        <select name="type" class="form-select">
                            <option value="older_30">Hapus Log Lebih Lama dari 30 Hari (Direkomendasikan)</option>
                            <option value="all">Kosongkan Semua Log (Reset Total)</option>
                        </select>
                    </div>
                    <div class="alert alert-warning text-xs mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i> Data log yang sudah dihapus tidak dapat dipulihkan kembali. Pastikan Anda telah mengekspor CSV jika diperlukan untuk arsip.
                    </div>
                </div>
                <div class="modal-footer bg-slate-50 border-top p-3">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4">
                        <i class="bi bi-trash me-1"></i> Hapus Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- Leaflet.js CDN --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let loginMap = null;
let markersLayer = null;

document.addEventListener('DOMContentLoaded', function () {
    @if($activeTab === 'peta')
    initMap();
    @endif
});

function initMap() {
    const mapContainer = document.getElementById('loginMap');
    if (!mapContainer) return;

    // Inisialisasi peta terpusat di kepulauan Indonesia (Lat: -2.5, Lng: 118.0)
    loginMap = L.map('loginMap', {
        center: [-2.5489, 118.0149],
        zoom: 5,
        zoomControl: true,
        scrollWheelZoom: true
    });

    // Base Layer OpenStreetMap Resmi (100% Bebas API Key & Gratis Selamanya)
    const osmLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> kontributor',
        maxZoom: 19
    }).addTo(loginMap);

    // Alternatif Citra Satelit Esri (Bebas API Key)
    const satLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; Esri World Imagery',
        maxZoom: 18
    });

    // Kontrol pemilih tampilan peta (Jalan vs Satelit)
    L.control.layers({
        "🗺️ Peta Jalan": osmLayer,
        "🛰️ Citra Satelit": satLayer
    }, null, { position: 'topright' }).addTo(loginMap);

    setTimeout(() => {
        if (loginMap) loginMap.invalidateSize();
    }, 300);

    markersLayer = L.layerGroup().addTo(loginMap);

    loadMapPoints();
}

function focusIndonesia() {
    if (loginMap) {
        loginMap.setView([-2.5489, 118.0149], 5);
    }
}

function focusWorld() {
    if (loginMap) {
        loginMap.setView([15, 10], 2);
    }
}

function loadMapPoints() {
    if (!loginMap || !markersLayer) return;

    const status = document.getElementById('mapFilterStatus')?.value || 'all';
    const period = document.getElementById('mapFilterPeriod')?.value || 'all';
    const statusText = document.getElementById('mapStatusText');

    if (statusText) statusText.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengambil titik lokasi...';

    fetch(`{{ route('superadmin.log-aktivitas.geo-data') }}?status=${status}&period=${period}`)
        .then(res => res.json())
        .then(data => {
            markersLayer.clearLayers();

            if (!data.locations || data.locations.length === 0) {
                if (statusText) statusText.innerHTML = '<span class="text-slate-400">Belum ada titik koordinat yang sesuai filter.</span>';
                return;
            }

            if (statusText) {
                statusText.innerHTML = `Terdeteksi <strong>${data.total_locations}</strong> titik lokasi akses pengguna.`;
            }

            data.locations.forEach(loc => {
                const total = loc.total_logins;
                const uniqueUsers = loc.unique_users;
                const failed = loc.failed_count;

                // Tentukan warna marker
                let markerClass = 'marker-success';
                if (failed > 0 && loc.success_count === 0) {
                    markerClass = 'marker-danger';
                } else if (failed > 0) {
                    markerClass = 'marker-warning';
                }

                // Ukuran dinamis berdasarkan jumlah login
                const size = Math.min(Math.max(28 + Math.log(total) * 6, 32), 54);

                const icon = L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div class="custom-map-marker ${markerClass}" style="width:${size}px; height:${size}px;">${total}</div>`,
                    iconSize: [size, size],
                    iconAnchor: [size / 2, size / 2]
                });

                const marker = L.marker([loc.latitude, loc.longitude], { icon: icon });

                // Popup konten kaya
                let recentUsersHtml = '';
                if (loc.recent_users && loc.recent_users.length > 0) {
                    recentUsersHtml = '<div class="mt-2 pt-2 border-top"><div class="text-xs fw-bold text-slate-500 mb-1">User Terakhir Masuk:</div>';
                    loc.recent_users.forEach(u => {
                        const statusBadge = u.status === 'success' 
                            ? '<span class="badge bg-emerald-100 text-emerald-700 text-xs py-0">Sukses</span>'
                            : '<span class="badge bg-rose-100 text-rose-700 text-xs py-0">Gagal</span>';
                        recentUsersHtml += `<div class="d-flex justify-content-between align-items-center text-xs py-1">
                            <span class="fw-semibold text-slate-800 text-truncate" style="max-width:130px;">${u.user_name || 'Tamu'}</span>
                            ${statusBadge}
                        </div>`;
                    });
                    recentUsersHtml += '</div>';
                }

                const popupContent = `
                    <div style="min-width: 220px; font-family: 'Poppins', sans-serif;">
                        <div class="fw-bold fs-6 text-slate-900 d-flex align-items-center gap-1.5">
                            <i class="bi bi-geo-alt-fill text-emerald-600"></i> ${loc.city || 'Lokal'}
                        </div>
                        <div class="text-xs text-slate-500 mb-2">${loc.region ? loc.region + ', ' : ''}${loc.country || 'Indonesia'}</div>
                        
                        <div class="d-flex justify-content-between text-xs py-1 border-top">
                            <span class="text-slate-500">Total Akses:</span>
                            <span class="fw-bold text-slate-800">${loc.total_logins} kali</span>
                        </div>
                        <div class="d-flex justify-content-between text-xs py-1">
                            <span class="text-slate-500">Pengguna Unik:</span>
                            <span class="fw-bold text-slate-800">${loc.unique_users} user</span>
                        </div>
                        <div class="d-flex justify-content-between text-xs py-1">
                            <span class="text-slate-500">Terakhir Akses:</span>
                            <span class="fw-bold text-slate-800">${loc.latest_login_formatted}</span>
                        </div>
                        ${recentUsersHtml}
                    </div>
                `;

                marker.bindPopup(popupContent);
                markersLayer.addLayer(marker);
            });
        })
        .catch(err => {
            console.error(err);
            if (statusText) statusText.innerHTML = '<span class="text-rose-500">Gagal memuat titik peta.</span>';
        });
}

// Modal Audit Riwayat Pengguna
function showUserAuditModal(userId) {
    const modal = new bootstrap.Modal(document.getElementById('userAuditModal'));
    modal.show();

    document.getElementById('auditModalUserName').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memuat...';
    document.getElementById('auditModalUserSub').innerText = '';
    document.getElementById('auditModalStatusBadge').innerText = '-';
    document.getElementById('auditModalRoleBadge').innerText = '-';
    document.getElementById('auditModalPasswordPlain').innerText = '-';
    document.getElementById('auditModalSessionsList').innerText = 'Memuat sesi...';
    document.getElementById('auditModalLogsTable').innerHTML = '<tr><td colspan="5" class="text-center py-3 text-slate-400">Memuat riwayat...</td></tr>';

    fetch(`{{ url('superadmin/log-aktivitas/user-audit') }}/${userId}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const u = data.user;
                document.getElementById('auditModalUserName').innerHTML = `<i class="bi bi-person-badge text-emerald-600"></i> ${u.name}`;
                document.getElementById('auditModalUserSub').innerText = `${u.username} • ${u.email}`;
                
                document.getElementById('auditModalStatusBadge').innerHTML = u.is_active 
                    ? '<span class="badge bg-emerald-100 text-emerald-800 rounded-pill">Aktif Normal</span>'
                    : '<span class="badge bg-rose-100 text-rose-800 rounded-pill">Terkunci / Nonaktif</span>';

                document.getElementById('auditModalRoleBadge').innerHTML = `<span class="badge-role badge-role-${u.role.toLowerCase()}">${u.role}</span>`;
                document.getElementById('auditModalPasswordPlain').innerText = u.password_plain || '(Tersandi Hash)';

                // Sesi Aktif
                let sessionsHtml = '';
                if (data.sessions && data.sessions.length > 0) {
                    data.sessions.forEach(s => {
                        sessionsHtml += `<div class="d-flex justify-content-between align-items-center py-1.5 border-bottom">
                            <div>
                                <i class="bi bi-laptop me-1 text-emerald-600"></i> <strong>${s.ip_address}</strong>
                                <div class="text-slate-400" style="font-size:0.7rem;">${s.user_agent ? s.user_agent.substring(0, 70) + '...' : ''}</div>
                            </div>
                            <span class="badge bg-slate-100 text-slate-700">${s.last_active}</span>
                        </div>`;
                    });
                } else {
                    sessionsHtml = '<span class="text-slate-400">Tidak ada sesi aktif saat ini di database.</span>';
                }
                document.getElementById('auditModalSessionsList').innerHTML = sessionsHtml;

                // Log Table
                let logsHtml = '';
                if (data.logs && data.logs.length > 0) {
                    data.logs.forEach(l => {
                        const dateFormatted = l.login_at ? new Date(l.login_at).toLocaleString('id-ID') : '-';
                        const statusBadge = l.status === 'success'
                            ? '<span class="badge bg-emerald-100 text-emerald-700">Sukses</span>'
                            : `<span class="badge bg-rose-100 text-rose-700" title="${l.failure_reason || ''}">Gagal</span>`;
                        const loc = l.city ? `${l.city}, ${l.country}` : 'Lokal / Intranet';

                        logsHtml += `<tr>
                            <td>${dateFormatted}</td>
                            <td>${statusBadge}</td>
                            <td><i class="bi bi-geo-alt text-emerald-600"></i> ${loc}</td>
                            <td class="font-monospace">${l.ip_address}</td>
                            <td>${l.browser || '-'} (${l.platform || '-'})</td>
                        </tr>`;
                    });
                } else {
                    logsHtml = '<tr><td colspan="5" class="text-center py-3 text-slate-400">Belum ada catatan log untuk pengguna ini.</td></tr>';
                }
                document.getElementById('auditModalLogsTable').innerHTML = logsHtml;
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById('auditModalUserName').innerText = 'Gagal memuat';
        });
}

// Modal Reset Password Cepat
function openResetPasswordModal(userId, userName, userLogin) {
    document.getElementById('resetTargetName').innerText = userName;
    document.getElementById('resetTargetUser').innerText = userLogin;
    document.getElementById('resetPasswordForm').action = `{{ url('superadmin/log-aktivitas/user-action') }}/${userId}`;
    generateRandomPass();

    const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    modal.show();
}

function generateRandomPass() {
    const chars = 'abcdefghjkmnpqrstuvwxyz23456789ABCDEFGHJKMNPQRSTUVWXYZ';
    let res = '';
    for (let i = 0; i < 8; i++) {
        res += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('inputNewPassword').value = res;
}
</script>
@endsection
