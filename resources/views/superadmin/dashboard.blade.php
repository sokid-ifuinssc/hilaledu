@extends('layouts.app')

@section('title', 'Dashboard Super Admin')
@section('page-title', 'Dashboard Super Admin')

@section('dashboard-styles')
.stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px;
    transition: all 0.25s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    border-color: #cbd5e1;
}
.stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}
.stat-value {
    font-size: 1.85rem;
    font-weight: 800;
    line-height: 1.1;
    color: #0f172a;
}
.stat-label { font-size: 0.8rem; font-weight: 600; color: #475569; margin-top: 4px; }
.stat-sub   { font-size: 0.72rem; color: #94a3b8; margin-top: 2px; }

.section-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    overflow: hidden;
}
.section-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    background: #ffffff;
    display: flex; align-items: center; justify-content: space-between;
}
.section-header h6 { font-weight: 700; color: #0f172a; margin: 0; font-size: 0.95rem; }

.role-bar-item {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
}
.role-bar-item:last-child { border-bottom: none; }
.role-dot {
    width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
}
.role-bar-track {
    flex: 1; height: 7px;
    background: #f1f5f9;
    border-radius: 4px; overflow: hidden;
}
.role-bar-fill {
    height: 100%; border-radius: 4px;
    transition: width 1s ease;
}

.quick-action-btn {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    color: #1e293b;
    text-decoration: none;
    font-size: 0.84rem;
    font-weight: 600;
    transition: all 0.2s ease;
}
.quick-action-btn:hover {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #065f46;
    transform: translateX(3px);
}
.quick-action-btn i { font-size: 1.1rem; }

.user-row {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 20px;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s ease;
}
.user-row:last-child { border-bottom: none; }
.user-row:hover { background: #f8fafc; }
.user-avatar-sm {
    width: 36px; height: 36px; border-radius: 10px;
    background: linear-gradient(135deg, #059669, #10b981);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.82rem; font-weight: 700; color: white; flex-shrink: 0;
}
.badge-role {
    font-size: 0.7rem; padding: 3px 10px; border-radius: 20px;
    font-weight: 600; text-transform: capitalize;
}
.badge-superadmin { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.badge-operator   { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.badge-guru       { background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
.badge-tendik     { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
.badge-siswa      { background: #e0f2fe; color: #075985; border: 1px solid #bae6fd; }

.mini-stat-badge {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 6px 14px; border-radius: 10px;
    background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.15);
    font-size: 0.8rem; color: #cbd5e1;
}
.mini-stat-badge strong { color: #ffffff; }
@endsection

@section('content')
{{-- Welcome Card --}}
<div class="welcome-card mb-4">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h3>Selamat Datang, {{ auth()->user()->name }} 👋</h3>
            <p>Anda login sebagai <strong>Super Administrator</strong>. Memegang kendali penuh atas manajemen pengguna, data guru, data siswa, data master, dan integrasi aplikasi SMK Plus Al Hilal.</p>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <div class="role-badge" style="margin-top:0;">
                    <i class="bi bi-shield-lock-fill"></i>
                    Super Administrator
                </div>
                <div class="mini-stat-badge">
                    <i class="bi bi-calendar-check" style="color:var(--accent-gold);"></i>
                    Tahun Ajaran: <strong>{{ $stats['tahun_aktif'] }}</strong>
                </div>
                <div class="mini-stat-badge">
                    <i class="bi bi-diagram-3" style="color:#5dade2;"></i>
                    Jurusan: <strong>{{ $stats['total_jurusan'] }}</strong>
                </div>
                <div class="mini-stat-badge">
                    <i class="bi bi-collection" style="color:#bb8fce;"></i>
                    Kelas: <strong>{{ $stats['total_kelas'] }}</strong>
                </div>
            </div>
        </div>
        <div class="text-end d-none d-md-block">
            <div style="font-size:4rem;opacity:0.15;">🛡️</div>
        </div>
    </div>
</div>

{{-- Main Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <div class="stat-card h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(45,138,78,0.15);">
                    <i class="bi bi-people-fill" style="color:var(--primary-lighter);"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $stats['total_users'] }}</div>
                    <div class="stat-label">Total Pengguna</div>
                    <div class="stat-sub">{{ $stats['active_users'] }} aktif terdaftar</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="stat-card h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(155,89,182,0.15);">
                    <i class="bi bi-person-workspace" style="color:#bb8fce;"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $stats['total_guru'] }}</div>
                    <div class="stat-label">Total Guru</div>
                    <div class="stat-sub">Tenaga Pengajar</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="stat-card h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(230,126,34,0.15);">
                    <i class="bi bi-person-badge-fill" style="color:#f0b27a;"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $stats['total_tendik'] }}</div>
                    <div class="stat-label">Total Tendik</div>
                    <div class="stat-sub">Tenaga Kependidikan</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-xl">
        <div class="stat-card h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(52,152,219,0.15);">
                    <i class="bi bi-mortarboard-fill" style="color:#5dade2;"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $stats['total_siswa'] }}</div>
                    <div class="stat-label">Total Siswa</div>
                    <div class="stat-sub">Peserta Didik</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl">
        <div class="stat-card h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(212,168,67,0.15);">
                    <i class="bi bi-grid-3x3-gap-fill" style="color:var(--accent-gold);"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $stats['total_apps'] }}</div>
                    <div class="stat-label">Aplikasi Terintegrasi</div>
                    <div class="stat-sub">{{ $stats['active_apps'] }} aktif di portal</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Content Row --}}
<div class="row g-3 mb-4">
    {{-- User Distribution --}}
    <div class="col-lg-4">
        <div class="section-card h-100">
            <div class="section-header">
                <h6><i class="bi bi-pie-chart-fill me-2" style="color:var(--accent-gold);"></i>Distribusi Pengguna</h6>
            </div>
            <div class="p-4">
                @php
                    $roleColors = [
                        'superadmin' => ['#d4a843', 'var(--accent-gold)'],
                        'guru'       => ['#9b59b6', '#bb8fce'],
                        'tendik'     => ['#e67e22', '#f0b27a'],
                        'siswa'      => ['#3498db', '#5dade2'],
                    ];
                    $roleLabels = [
                        'superadmin' => 'Super Admin',
                        'guru'       => 'Guru',
                        'tendik'     => 'Tenaga Kependidikan',
                        'siswa'      => 'Siswa',
                    ];
                    $total = $stats['total_users'] > 0 ? $stats['total_users'] : 1;
                @endphp
                @foreach($roleLabels as $role => $label)
                    @php $count = $stats['users_by_role'][$role] ?? 0; @endphp
                    <div class="role-bar-item">
                        <div class="role-dot" style="background:{{ $roleColors[$role][1] }};"></div>
                        <div style="min-width:110px;font-size:0.82rem;color:var(--text-muted);">{{ $label }}</div>
                        <div class="role-bar-track">
                            <div class="role-bar-fill" style="width:{{ round($count/$total*100) }}%;background:{{ $roleColors[$role][0] }};"></div>
                        </div>
                        <div style="min-width:28px;text-align:right;font-size:0.85rem;font-weight:600;">{{ $count }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="col-lg-4">
        <div class="section-card h-100">
            <div class="section-header">
                <h6><i class="bi bi-lightning-fill me-2" style="color:var(--accent-gold);"></i>Aksi Cepat & Navigasi</h6>
            </div>
            <div class="p-3 d-flex flex-column gap-2">
                <a href="{{ route('akademik.dashboard') }}" class="quick-action-btn" style="border-color: rgba(52, 152, 219, 0.35);">
                    <i class="bi bi-book-fill" style="color:#3498db;"></i>
                    Buka Portal Akademik Terpadu
                </a>
                <a href="{{ route('bk.dashboard') }}" class="quick-action-btn" style="border-color: rgba(231, 76, 60, 0.35);">
                    <i class="bi bi-shield-check" style="color:#e74c3c;"></i>
                    Buka Monitoring & Layanan BK
                </a>
                <a href="{{ route('prakerin.dashboard') }}" class="quick-action-btn" style="border-color: rgba(230, 126, 34, 0.35);">
                    <i class="bi bi-briefcase-fill" style="color:#e67e22;"></i>
                    Buka Modul Prakerin (PKL)
                </a>
                <a href="{{ route('superadmin.credentials.index') }}" class="quick-action-btn" style="border-color: rgba(212,168,67,0.35);">
                    <i class="bi bi-key-fill" style="color:var(--accent-gold);"></i>
                    Kredensial & Reset Password
                </a>
                <a href="{{ route('superadmin.users.create') }}" class="quick-action-btn">
                    <i class="bi bi-person-plus-fill" style="color:var(--primary-lighter);"></i>
                    Tambah Pengguna Baru
                </a>
                <a href="{{ route('superadmin.guru.index') }}" class="quick-action-btn">
                    <i class="bi bi-person-workspace" style="color:#bb8fce;"></i>
                    Kelola Data Guru
                </a>
                <a href="{{ route('superadmin.siswa.index') }}" class="quick-action-btn">
                    <i class="bi bi-mortarboard-fill" style="color:#5dade2;"></i>
                    Kelola Data Siswa
                </a>
                <a href="{{ route('superadmin.master.jurusan') }}" class="quick-action-btn">
                    <i class="bi bi-diagram-3-fill" style="color:#f1948a;"></i>
                    Data Master Jurusan & Kelas
                </a>
            </div>
        </div>
    </div>

    {{-- Guru Terbaru --}}
    <div class="col-lg-4">
        <div class="section-card h-100">
            <div class="section-header">
                <h6><i class="bi bi-person-workspace me-2" style="color:var(--accent-gold);"></i>Guru Terbaru</h6>
                <a href="{{ route('superadmin.guru.index') }}" style="font-size:0.78rem;color:var(--primary-lighter);text-decoration:none;">Lihat semua →</a>
            </div>
            @forelse($recentGuru as $guru)
                <div class="user-row">
                    <div class="user-avatar-sm" style="background:linear-gradient(135deg, #8e44ad, #9b59b6);">{{ strtoupper(substr($guru->name, 0, 2)) }}</div>
                    <div class="flex-1 min-w-0" style="flex:1;min-width:0;">
                        <div style="font-size:0.85rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $guru->name }}</div>
                        <div style="font-size:0.72rem;color:var(--text-muted);">{{ $guru->nip ?? $guru->username }} · {{ $guru->created_at ? $guru->created_at->diffForHumans() : '-' }}</div>
                    </div>
                    <span class="badge-role badge-guru">Guru</span>
                </div>
            @empty
                <div class="p-4 text-center" style="color:var(--text-muted);font-size:0.85rem;">Belum ada data guru.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Integrated Apps Grid --}}
<div class="section-card mb-4">
    <div class="section-header">
        <h6><i class="bi bi-grid-3x3-gap-fill me-2" style="color:var(--accent-gold);"></i>Status Modul Ekosistem HilalEdu</h6>
        <a href="{{ route('superadmin.apps.index') }}" style="font-size:0.78rem;color:var(--primary-lighter);text-decoration:none;">Kelola Modul →</a>
    </div>
    <div class="p-4">
        <div class="row g-3">
            @foreach($apps as $app)
                <div class="col-md-6 col-lg-4">
                    <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:18px;height:100%;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,0.03);">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div style="width:46px;height:46px;border-radius:12px;background:{{ $app->color }}15;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
                                <i class="{{ $app->icon }}" style="color:{{ $app->color }};"></i>
                            </div>
                            <div style="overflow:hidden;">
                                <div style="font-weight:700;font-size:0.95rem;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $app->name }}</div>
                                <div style="font-size:0.75rem;color:#64748b;">{{ Str::limit($app->description, 60) }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2" style="border-top:1px solid #f1f5f9;">
                            <span class="badge-role badge-{{ $app->statusBadgeClass() }}">{{ $app->statusLabel() }}</span>
                            <span style="font-size:0.75rem;color:#64748b;"><i class="bi bi-people me-1"></i>{{ $app->coordinators_count }} Koordinator</span>
                            <a href="{{ route('superadmin.apps.show', $app) }}" style="font-size:0.78rem;color:#059669;font-weight:600;text-decoration:none;">Kelola <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
