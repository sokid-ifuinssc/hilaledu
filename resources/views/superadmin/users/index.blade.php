@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page-title', 'Manajemen Pengguna')

@section('dashboard-styles')
.page-header {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px; margin-bottom: 24px;
}
.page-header h4 { font-size: 1.2rem; font-weight: 700; margin: 0; }
.btn-primary-green {
    display: inline-flex; align-items: center; gap: 8px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border: none; color: white; padding: 10px 20px;
    border-radius: 12px; font-size: 0.88rem; font-weight: 600;
    cursor: pointer; text-decoration: none; font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
}
.btn-primary-green:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(26,86,50,0.4); color: white; }

.filter-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 16px; padding: 18px 20px; margin-bottom: 20px;
}
.form-control-dark, .form-select-dark {
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--border-color);
    color: var(--text-light);
    border-radius: 10px; padding: 9px 14px;
    font-family: 'Poppins', sans-serif; font-size: 0.85rem;
    width: 100%;
}
.form-control-dark:focus, .form-select-dark:focus {
    outline: none; border-color: var(--primary-light);
    background: rgba(255,255,255,0.07); color: var(--text-light);
    box-shadow: 0 0 0 3px rgba(45,138,78,0.15);
}
.form-select-dark option { background: #1a2e24; color: var(--text-light); }
.form-control-dark::placeholder { color: var(--text-muted); }

.btn-filter {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 18px; border-radius: 10px;
    font-size: 0.85rem; font-weight: 500; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s ease;
}
.btn-filter-submit {
    background: var(--primary); border: 1px solid var(--primary-light); color: white;
}
.btn-filter-submit:hover { background: var(--primary-light); color: white; }
.btn-filter-reset {
    background: transparent; border: 1px solid var(--border-color); color: var(--text-muted);
}
.btn-filter-reset:hover { border-color: rgba(255,255,255,0.15); color: var(--text-light); }

.table-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 18px; overflow: hidden;
}
.table-responsive { overflow-x: auto; }
table.users-table { width: 100%; border-collapse: collapse; }
table.users-table th {
    padding: 14px 18px; text-align: left;
    font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.8px; color: var(--text-muted);
    border-bottom: 1px solid var(--border-color);
    white-space: nowrap;
}
table.users-table td {
    padding: 14px 18px; font-size: 0.87rem;
    border-bottom: 1px solid rgba(255,255,255,0.03);
    vertical-align: middle;
}
table.users-table tr:last-child td { border-bottom: none; }
table.users-table tbody tr:hover { background: rgba(255,255,255,0.02); }

.user-cell { display: flex; align-items: center; gap: 12px; }
.avatar-circle {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem; font-weight: 700; color: white; flex-shrink: 0;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
}
.user-name { font-weight: 600; font-size: 0.88rem; }
.user-email { font-size: 0.75rem; color: var(--text-muted); }
.user-username { font-size: 0.75rem; color: var(--text-muted); margin-top: 1px; }

.badge-role { font-size: 0.72rem; padding: 4px 12px; border-radius: 20px; font-weight: 600; white-space: nowrap; }
.badge-superadmin { background: rgba(212,168,67,0.15); color: #d4a843; border: 1px solid rgba(212,168,67,0.3); }
.badge-admin      { background: rgba(52,152,219,0.15); color: #5dade2; border: 1px solid rgba(52,152,219,0.3); }
.badge-operator   { background: rgba(46,204,113,0.15); color: #58d68d; border: 1px solid rgba(46,204,113,0.3); }
.badge-guru       { background: rgba(155,89,182,0.15); color: #bb8fce; border: 1px solid rgba(155,89,182,0.3); }
.badge-tendik     { background: rgba(230,126,34,0.15); color: #f0b27a; border: 1px solid rgba(230,126,34,0.3); }
.badge-siswa      { background: rgba(231,76,60,0.15);  color: #f1948a; border: 1px solid rgba(231,76,60,0.3); }

.status-dot { display: inline-flex; align-items: center; gap: 6px; font-size: 0.82rem; }
.dot { width: 7px; height: 7px; border-radius: 50%; }
.dot-active   { background: #2ecc71; box-shadow: 0 0 6px rgba(46,204,113,0.5); }
.dot-inactive { background: #e74c3c; }

.action-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 8px;
    border: 1px solid var(--border-color); background: transparent;
    color: var(--text-muted); cursor: pointer; font-size: 0.9rem;
    transition: all 0.2s ease; text-decoration: none;
}
.action-btn:hover { background: var(--bg-card-hover); color: var(--text-light); }
.action-btn.btn-edit:hover  { border-color: rgba(52,152,219,0.4); color: #5dade2; }
.action-btn.btn-toggle-on:hover  { border-color: rgba(231,76,60,0.4); color: #f1948a; }
.action-btn.btn-toggle-off:hover { border-color: rgba(46,204,113,0.4); color: #58d68d; }
.action-btn.btn-delete:hover { border-color: rgba(231,76,60,0.5); color: #f1948a; background: rgba(231,76,60,0.08); }

.pagination-area {
    padding: 16px 20px;
    border-top: 1px solid var(--border-color);
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;
}
.pagination-info { font-size: 0.8rem; color: var(--text-muted); }
.pagination .page-link {
    background: var(--bg-card); border-color: var(--border-color);
    color: var(--text-muted); border-radius: 8px; font-size: 0.82rem;
    padding: 6px 12px; transition: all 0.2s ease;
}
.pagination .page-link:hover { background: var(--bg-card-hover); color: var(--text-light); }
.pagination .page-item.active .page-link { background: var(--primary); border-color: var(--primary); color: white; }
.pagination .page-item.disabled .page-link { opacity: 0.3; }
@endsection

@section('content')
<div class="page-header">
    <div>
        <h4><i class="bi bi-people-fill me-2" style="color:var(--accent-gold);"></i>Manajemen Pengguna</h4>
        <p style="font-size:0.82rem;color:var(--text-muted);margin:4px 0 0;">
            Total <strong style="color:var(--text-light);">{{ $totalUsers }}</strong> pengguna terdaftar,
            <strong style="color:#58d68d;">{{ $activeUsers }}</strong> aktif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('superadmin.credentials.print', request()->query()) }}" target="_blank" class="btn btn-outline-secondary d-flex align-items-center gap-2" style="border-radius: 12px; padding: 10px 20px; font-weight: 600;">
            <i class="bi bi-printer"></i> Cetak Kredensial
        </a>
        <a href="{{ route('superadmin.users.create') }}" class="btn-primary-green">
            <i class="bi bi-person-plus-fill"></i> Tambah Pengguna
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="filter-card">
    <form method="GET" action="{{ route('superadmin.users.index') }}" class="row g-2 align-items-end">
        <div class="col-md-5">
            <label style="font-size:0.78rem;color:var(--text-muted);margin-bottom:6px;display:block;">Cari Pengguna</label>
            <input type="text" name="search" class="form-control-dark"
                   placeholder="Nama, username, atau email..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <label style="font-size:0.78rem;color:var(--text-muted);margin-bottom:6px;display:block;">Filter Role</label>
            <select name="role" class="form-select-dark">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
                        {{ ucfirst($role) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:0.78rem;color:var(--text-muted);margin-bottom:6px;display:block;">Status</label>
            <select name="status" class="form-select-dark">
                <option value="">Semua Status</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn-filter btn-filter-submit flex-1" style="flex:1;">
                <i class="bi bi-search"></i> Filter
            </button>
            <a href="{{ route('superadmin.users.index') }}" class="btn-filter btn-filter-reset">
                <i class="bi bi-x"></i>
            </a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="table-card">
    <div class="table-responsive">
        <table class="users-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pengguna</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Terdaftar</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td style="color:var(--text-muted);font-size:0.8rem;">
                            {{ $users->firstItem() + $loop->index }}
                        </td>
                        <td>
                            <div class="user-cell">
                                <div class="avatar-circle"
                                     style="background: linear-gradient(135deg,
                                        {{ $user->role === 'superadmin' ? '#a07a1e, #d4a843' :
                                           ($user->role === 'admin' ? '#1a5889, #3498db' :
                                           ($user->role === 'guru' ? '#6c3483, #9b59b6' :
                                           ($user->role === 'siswa' ? '#922b21, #e74c3c' : '#1a5632, #2d8a4e'))) }});">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="user-name">{{ $user->name }}</div>
                                    <div class="user-email">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <code style="font-size:0.8rem;color:var(--primary-lighter);background:rgba(45,138,78,0.1);padding:2px 8px;border-radius:6px;">{{ $user->username }}</code>
                        </td>
                        <td><span class="badge-role badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
                        <td>
                            <div class="status-dot">
                                <div class="dot {{ $user->is_active ? 'dot-active' : 'dot-inactive' }}"></div>
                                <span style="color:{{ $user->is_active ? '#58d68d' : '#f1948a' }};">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </td>
                        <td style="font-size:0.8rem;color:var(--text-muted);">
                            {{ $user->created_at->diffForHumans() }}
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('superadmin.users.edit', $user) }}" class="action-btn btn-edit" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>

                                @if($user->id !== auth()->id())
                                    {{-- Toggle Active --}}
                                    <form method="POST" action="{{ route('superadmin.users.toggle-active', $user) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="action-btn {{ $user->is_active ? 'btn-toggle-on' : 'btn-toggle-off' }}"
                                                title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="bi bi-{{ $user->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                        </button>
                                    </form>

                                    {{-- Delete --}}
                                    <form method="POST" action="{{ route('superadmin.users.destroy', $user) }}" class="d-inline"
                                          onsubmit="return confirm('Hapus pengguna {{ addslashes($user->name) }}? Aksi ini tidak dapat dibatalkan.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-btn btn-delete" title="Hapus">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size:0.72rem;color:var(--text-muted);padding:4px 8px;">(Anda)</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:48px;color:var(--text-muted);">
                            <i class="bi bi-search" style="font-size:2rem;display:block;margin-bottom:12px;opacity:0.3;"></i>
                            Tidak ada pengguna yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="pagination-area">
            <div class="pagination-info">
                Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} pengguna
            </div>
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
