@extends('layouts.app')

@section('title', 'Aplikasi: ' . $application->name)
@section('page-title', 'Detail Aplikasi')

@section('dashboard-styles')
.back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 10px;
    border: 1px solid var(--border-color); background: transparent;
    color: var(--text-muted); font-size: 0.82rem; font-weight: 500;
    text-decoration: none; transition: all 0.2s ease;
    font-family: 'Poppins', sans-serif;
}
.back-btn:hover { background: var(--bg-card); color: var(--text-light); border-color: rgba(255,255,255,0.12); }

.app-hero {
    border-radius: 20px; overflow: hidden;
    border: 1px solid var(--border-color);
    margin-bottom: 24px; position: relative;
}
.app-hero-body {
    padding: 32px; display: flex; align-items: center; gap: 24px; flex-wrap: wrap;
    background: linear-gradient(135deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
}
.hero-icon-wrap {
    width: 72px; height: 72px; border-radius: 20px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 2rem;
}
.hero-app-name { font-size: 1.5rem; font-weight: 800; margin-bottom: 6px; }
.hero-app-desc { font-size: 0.88rem; color: var(--text-muted); max-width: 500px; line-height: 1.6; }
.hero-meta { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-top: 16px; }
.meta-item { display: flex; align-items: center; gap: 6px; font-size: 0.82rem; color: var(--text-muted); }

.badge-status { font-size: 0.78rem; padding: 5px 14px; border-radius: 20px; font-weight: 600; }
.badge-active   { background: rgba(46,204,113,0.15); color: #58d68d; border: 1px solid rgba(46,204,113,0.3); }
.badge-inactive { background: rgba(231,76,60,0.15);  color: #f1948a; border: 1px solid rgba(231,76,60,0.3); }
.badge-coming   { background: rgba(212,168,67,0.12); color: #d4a843; border: 1px solid rgba(212,168,67,0.25); }

.hero-actions { margin-left: auto; display: flex; gap: 10px; }
.btn-toggle-app {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 20px; border-radius: 12px; font-size: 0.85rem; font-weight: 600;
    border: none; cursor: pointer; font-family: 'Poppins', sans-serif; transition: all 0.25s ease;
}
.btn-toggle-activate { background: rgba(46,204,113,0.15); color: #58d68d; border: 1px solid rgba(46,204,113,0.3); }
.btn-toggle-activate:hover { background: rgba(46,204,113,0.25); }
.btn-toggle-deactivate { background: rgba(231,76,60,0.12); color: #f1948a; border: 1px solid rgba(231,76,60,0.25); }
.btn-toggle-deactivate:hover { background: rgba(231,76,60,0.22); }

.section-card {
    background: var(--bg-card); border: 1px solid var(--border-color);
    border-radius: 18px; overflow: hidden; margin-bottom: 20px;
}
.section-header {
    padding: 18px 24px; border-bottom: 1px solid var(--border-color);
    display: flex; align-items: center; justify-content: space-between;
}
.section-header h6 { font-size: 0.95rem; font-weight: 700; margin: 0; }

.feature-list { padding: 20px 24px; }
.feature-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.03);
    font-size: 0.88rem;
}
.feature-item:last-child { border-bottom: none; }
.feature-bullet {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}

.assign-form {
    padding: 20px 24px;
    display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;
}
.form-select-dark {
    background: rgba(255,255,255,0.05); border: 1px solid var(--border-color);
    color: var(--text-light); border-radius: 10px; padding: 10px 14px;
    font-family: 'Poppins', sans-serif; font-size: 0.85rem; flex: 1; min-width: 200px;
}
.form-select-dark:focus { outline: none; border-color: var(--primary-light); }
.form-select-dark option { background: #1a2e24; }
.btn-assign {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 20px; border-radius: 10px; font-size: 0.85rem; font-weight: 600;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border: none; color: white; cursor: pointer; font-family: 'Poppins', sans-serif;
    transition: all 0.25s ease; white-space: nowrap;
}
.btn-assign:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(26,86,50,0.4); }

.admin-list { }
.admin-row {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 24px; border-bottom: 1px solid rgba(255,255,255,0.03);
    transition: background 0.2s ease;
}
.admin-row:last-child { border-bottom: none; }
.admin-row:hover { background: rgba(255,255,255,0.02); }
.admin-avatar {
    width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.88rem; font-weight: 700; color: white;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
}
.admin-name { font-size: 0.9rem; font-weight: 600; }
.admin-meta { font-size: 0.75rem; color: var(--text-muted); }

.badge-role { font-size: 0.7rem; padding: 3px 10px; border-radius: 20px; font-weight: 600; white-space: nowrap; }
.badge-superadmin { background: rgba(212,168,67,0.15); color: #d4a843; border: 1px solid rgba(212,168,67,0.3); }
.badge-admin      { background: rgba(52,152,219,0.15); color: #5dade2; border: 1px solid rgba(52,152,219,0.3); }
.badge-operator   { background: rgba(46,204,113,0.15); color: #58d68d; border: 1px solid rgba(46,204,113,0.3); }

.btn-remove {
    display: inline-flex; align-items: center; gap: 5px; margin-left: auto;
    padding: 6px 14px; border-radius: 8px; font-size: 0.78rem; font-weight: 500;
    background: rgba(231,76,60,0.08); border: 1px solid rgba(231,76,60,0.2);
    color: #f1948a; cursor: pointer; font-family: 'Poppins', sans-serif;
    transition: all 0.2s ease;
}
.btn-remove:hover { background: rgba(231,76,60,0.18); border-color: rgba(231,76,60,0.4); }

.empty-admins {
    padding: 36px 24px; text-align: center;
    color: var(--text-muted); font-size: 0.88rem;
}
.empty-admins i { font-size: 2.5rem; display: block; margin-bottom: 10px; opacity: 0.2; }
@endsection

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('superadmin.apps.index') }}" class="back-btn">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <div style="font-size:0.8rem;color:var(--text-muted);">
        <a href="{{ route('superadmin.apps.index') }}" style="color:inherit;text-decoration:none;">Aplikasi</a>
        <i class="bi bi-chevron-right mx-1" style="font-size:0.7rem;"></i>
        {{ $application->name }}
    </div>
</div>

{{-- App Hero --}}
<div class="app-hero">
    <div style="height:5px;background:{{ $application->color }};"></div>
    <div class="app-hero-body">
        <div class="hero-icon-wrap" style="background:{{ $application->color }}22;">
            <i class="{{ $application->icon }}" style="color:{{ $application->color }};"></i>
        </div>
        <div class="flex-1">
            <div class="hero-app-name">{{ $application->name }}</div>
            <div class="hero-app-desc">{{ $application->description }}</div>
            <div class="hero-meta">
                <span class="badge-status badge-{{ $application->status }}">
                    <i class="bi bi-{{ $application->status === 'active' ? 'check-circle-fill' : 'clock-fill' }} me-1"></i>
                    {{ $application->statusLabel() }}
                </span>
                <span class="meta-item">
                    <i class="bi bi-people-fill"></i> {{ $application->coordinatorUsers->count() }} Pengelola Ditugaskan
                </span>
                    <i class="bi bi-code-slash"></i> Slug: <code style="color:var(--primary-lighter);">{{ $application->slug }}</code>
                </span>
                @if($application->url)
                    <span class="meta-item">
                        <i class="bi bi-link-45deg"></i>
                        <a href="{{ $application->url }}" target="_blank" style="color:var(--primary-lighter);">{{ $application->url }}</a>
                    </span>
                @endif
            </div>
        </div>
        <div class="hero-actions d-flex gap-2">
            <a href="{{ route('superadmin.apps.edit', $application) }}" class="btn-toggle-app btn-toggle-activate" style="text-decoration:none;">
                <i class="bi bi-gear-fill"></i> Pengaturan
            </a>
            <form method="POST" action="{{ route('superadmin.apps.toggle-status', $application) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn-toggle-app {{ $application->status === 'active' ? 'btn-toggle-deactivate' : 'btn-toggle-activate' }}">
                    <i class="bi bi-{{ $application->status === 'active' ? 'pause-circle-fill' : 'play-circle-fill' }}"></i>
                    {{ $application->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
            </form>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Info Integrasi & Fitur --}}
    <div class="col-lg-4">
        {{-- Card Status Database & Integrasi --}}
        <div class="section-card mb-3">
            <div class="section-header">
                <h6><i class="bi bi-hdd-network me-2" style="color:var(--accent-gold);"></i>Integrasi & Database</h6>
            </div>
            <div style="padding:18px 22px; display:flex; flex-direction:column; gap:12px; font-size:0.84rem;">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted"><i class="bi bi-database me-1"></i> Database:</span>
                    <span class="fw-semibold text-light">
                        @if($application->hasDatabase())
                            <code style="color:#58d68d;background:rgba(46,204,113,0.1);padding:2px 6px;border-radius:4px;">{{ $application->database_name }}</code>
                        @else
                            <span class="text-muted fst-italic">Belum diatur</span>
                        @endif
                    </span>
                </div>

                @if($application->hasDatabase())
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Koneksi DB:</span>
                        <span>
                            @if(isset($dbTest) && $dbTest['is_connected'])
                                <span class="badge bg-success bg-opacity-25 text-success border border-success"><i class="bi bi-check-circle-fill me-1"></i>Terhubung ({{ $dbTest['user_count'] }} user)</span>
                            @else
                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger"><i class="bi bi-x-circle-fill me-1"></i>Gagal Terhubung</span>
                            @endif
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Auto-Sync:</span>
                        <span class="fw-semibold" style="color:{{ $application->auto_sync ? '#58d68d' : '#888' }};">
                            <i class="bi bi-{{ $application->auto_sync ? 'check-circle-fill text-success' : 'dash-circle' }} me-1"></i>
                            {{ $application->auto_sync ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted"><i class="bi bi-shield-lock me-1"></i> Akses Terpadu:</span>
                    <span class="fw-semibold text-light">{{ $application->sso_enabled ? 'Aktif' : 'Nonaktif' }}</span>
                </div>

                @if($application->api_key)
                    <div class="mt-1 pt-2 border-top border-secondary border-opacity-25">
                        <div class="text-muted small mb-1">Kunci Akses API:</div>
                        <div class="p-2 rounded font-monospace small text-truncate" style="background:#090e0c;border:1px solid rgba(255,255,255,0.06);color:#a8d5ba;" title="{{ $application->api_key }}">
                            {{ $application->api_key }}
                        </div>
                    </div>
                @endif

                @if($application->hasDatabase() && $application->slug !== 'hilaledu')
                    <div class="d-flex gap-2 mt-2 pt-2 border-top border-secondary border-opacity-25">
                        <form action="{{ route('superadmin.apps.test-db', $application) }}" method="POST" class="flex-1" style="flex:1;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-light w-100" style="font-size:0.78rem;border-radius:8px;">
                                <i class="bi bi-plug me-1"></i> Tes DB
                            </button>
                        </form>
                        <form action="{{ route('superadmin.apps.sync-users', $application) }}" method="POST" class="flex-1" style="flex:1;" onsubmit="return confirm('Sinkronkan semua pengguna ke {{ $application->database_name }}?');">
                            @csrf
                            <button type="submit" class="btn btn-sm w-100" style="background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;font-size:0.78rem;border-radius:8px;border:none;">
                                <i class="bi bi-arrow-repeat me-1"></i> Sync User
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- Features --}}
        <div class="section-card">
            <div class="section-header">
                <h6><i class="bi bi-list-check me-2" style="color:var(--accent-gold);"></i>Fitur Aplikasi</h6>
            </div>
            <div class="feature-list">
                @if($application->features)
                    @foreach($application->features as $feature)
                        <div class="feature-item">
                            <div class="feature-bullet" style="background:{{ $application->color }};"></div>
                            {{ $feature }}
                        </div>
                    @endforeach
                @else
                    <p style="color:var(--text-muted);font-size:0.85rem;">Belum ada fitur terdaftar.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Admin & Coordinator Management (Guru & Tendik) --}}
    <div class="col-lg-8">
        {{-- Form Penugasan Guru / Tendik --}}
        @if(isset($availableUsers) && $availableUsers->isNotEmpty())
            <div class="section-card mb-3">
                <div class="section-header">
                    <h6><i class="bi bi-person-plus-fill me-2" style="color:var(--accent-gold);"></i>Tugaskan Guru / Tendik Sebagai Admin atau Koordinator</h6>
                </div>
                <form method="POST" action="{{ route('superadmin.apps.assign-coordinator', $application) }}" class="assign-form">
                    @csrf
                    <select name="user_id" class="form-select-dark" required>
                        <option value="">-- Pilih Guru atau Tendik --</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }} ({{ strtoupper($user->role) }}) — {{ $user->nip ? 'NIP: '.$user->nip : '@'.$user->username }}
                            </option>
                        @endforeach
                    </select>
                    <select name="coordinator_role" class="form-select-dark" required style="min-width:200px;">
                        @foreach($coordinatorRoles as $val => $label)
                            <option value="{{ $val }}" {{ $val === 'admin_app' ? 'selected' : '' }}>
                                {{ $val === 'admin_app' ? '⭐ ' : '' }}{{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-assign">
                        <i class="bi bi-person-check-fill"></i> Tugaskan Staf
                    </button>
                </form>
            </div>
        @else
            <div class="section-card mb-3 p-3 text-muted text-center" style="font-size:0.85rem;">
                <i class="bi bi-info-circle me-1"></i> Seluruh staf Guru dan Tendik yang aktif telah ditugaskan di aplikasi ini.
            </div>
        @endif

        {{-- Daftar Admin & Koordinator Terdaftar --}}
        <div class="section-card mb-3">
            <div class="section-header">
                <h6><i class="bi bi-shield-check me-2" style="color:var(--primary-lighter);"></i>Daftar Admin & Koordinator Aplikasi</h6>
                <span style="font-size:0.8rem;color:var(--text-muted);">{{ $coordinators->count() }} penugasan peran</span>
            </div>

            @if($coordinators->isEmpty())
                <div class="empty-admins">
                    <i class="bi bi-person-x"></i>
                    Belum ada Guru atau Tendik yang disetting sebagai Admin atau Koordinator di aplikasi ini.
                </div>
            @else
                <div class="admin-list">
                    @foreach($coordinators as $coord)
                        @php
                            $u = $coord->user;
                            $badge = $coord->badgeStyle();
                        @endphp
                        @if(!$u) @continue @endif
                        <div class="admin-row">
                            <div class="admin-avatar" style="background: {{ $badge['bg'] }};">
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                            </div>
                            <div class="flex-1" style="flex:1;min-width:0;">
                                <div class="admin-name d-flex align-items-center gap-2">
                                    {{ $u->name }}
                                    <span class="badge bg-secondary" style="font-size:0.65rem;">{{ strtoupper($u->role) }}</span>
                                </div>
                                <div class="admin-meta">
                                    {{ $u->nip ? 'NUPTK/NIP: '.$u->nip : '@'.$u->username }} ·
                                    Ditugaskan {{ \Carbon\Carbon::parse($coord->assigned_at ?? $coord->created_at)->diffForHumans() }}
                                </div>
                            </div>
                            
                            <span class="badge-role" style="background:{{ $badge['badge_bg'] }};color:{{ $badge['badge_color'] }};border:1px solid {{ $badge['badge_border'] }};">
                                {{ $coord->roleLabel() }}
                            </span>

                            <form method="POST"
                                  action="{{ route('superadmin.apps.remove-coordinator', [$application, $u]) }}"
                                  onsubmit="return confirm('Cabut peran {{ $coord->roleLabel() }} dari {{ addslashes($u->name) }}?')">
                                @csrf @method('DELETE')
                                <input type="hidden" name="coordinator_id" value="{{ $coord->id }}">
                                <button type="submit" class="btn-remove">
                                    <i class="bi bi-x-circle-fill"></i> Cabut Peran Ini
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
