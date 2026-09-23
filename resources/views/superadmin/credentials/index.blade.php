@extends('layouts.app')

@section('title', 'Kredensial & Reset Password Pengguna')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Breadcrumb & Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Kredensial Pengguna</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-gray-800 mb-0">Informasi Kredensial & Reset Password</h1>
            <p class="text-muted small mb-0">Kelola akun, lihat password tersimpan, dan reset kredensial ke database HilalEdu serta aplikasi lain.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('superadmin.credentials.print', request()->query()) }}" target="_blank" class="btn btn-outline-primary d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zM1 7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm3 5a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4z"/>
                </svg>
                Cetak Kartu Akun
            </a>
            <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus" viewBox="0 0 16 16">
                    <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H1s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                    <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
                </svg>
                Tambah Pengguna
            </a>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check-circle-fill text-success flex-shrink-0" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
            </svg>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-exclamation-triangle-fill text-warning flex-shrink-0" viewBox="0 0 16 16">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
            </svg>
            <div>{{ session('warning') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold">Total Pengguna</span>
                            <h4 class="fw-bold mb-0 text-primary">{{ $counts['total'] }}</h4>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                                <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold">Guru Pengampu</span>
                            <h4 class="fw-bold mb-0 text-success">{{ $counts['guru'] }}</h4>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-2 rounded-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-mortarboard-fill" viewBox="0 0 16 16">
                                <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917z"/>
                                <path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold">Tenaga Kependidikan</span>
                            <h4 class="fw-bold mb-0 text-warning">{{ $counts['tendik'] }}</h4>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-briefcase-fill" viewBox="0 0 16 16">
                                <path d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v1.384l7.614 2.03a1.5 1.5 0 0 0 .772 0L16 5.884V4.5A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5"/>
                                <path d="M0 12.5A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5V6.85L8.129 8.947a.5.5 0 0 1-.258 0L0 6.85z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold">Siswa Terdaftar</span>
                            <h4 class="fw-bold mb-0 text-info">{{ $counts['siswa'] }}</h4>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-2 rounded-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-backpack-fill" viewBox="0 0 16 16">
                                <path d="M5 13v-3h4v3zM6 2v1h4V2z"/>
                                <path d="M4.5 1A1.5 1.5 0 0 0 3 2.5V3h-.5A1.5 1.5 0 0 0 1 4.5v7A1.5 1.5 0 0 0 2.5 13H3v1.5A1.5 1.5 0 0 0 4.5 16h7a1.5 1.5 0 0 0 1.5-1.5V13h.5a1.5 1.5 0 0 0 1.5-1.5v-7A1.5 1.5 0 0 0 13.5 3H13v-.5A1.5 1.5 0 0 0 11.5 1z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Filter & Data Card --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row g-3 align-items-center justify-content-between">
                {{-- Role Filter Pills --}}
                <div class="col-md-7">
                    <div class="nav nav-pills gap-1">
                        <a href="{{ route('superadmin.credentials.index', array_merge(request()->except('role', 'page'))) }}" 
                           class="nav-link py-1 px-3 rounded-pill {{ !request('role') ? 'active' : 'bg-light text-dark' }}">
                            Semua Akun ({{ $counts['total'] }})
                        </a>
                        <a href="{{ route('superadmin.credentials.index', array_merge(request()->except('page'), ['role' => 'guru'])) }}" 
                           class="nav-link py-1 px-3 rounded-pill {{ request('role') === 'guru' ? 'active' : 'bg-light text-dark' }}">
                            Guru ({{ $counts['guru'] }})
                        </a>
                        <a href="{{ route('superadmin.credentials.index', array_merge(request()->except('page'), ['role' => 'tendik'])) }}" 
                           class="nav-link py-1 px-3 rounded-pill {{ request('role') === 'tendik' ? 'active' : 'bg-light text-dark' }}">
                            Tendik ({{ $counts['tendik'] }})
                        </a>
                        <a href="{{ route('superadmin.credentials.index', array_merge(request()->except('page'), ['role' => 'siswa'])) }}" 
                           class="nav-link py-1 px-3 rounded-pill {{ request('role') === 'siswa' ? 'active' : 'bg-light text-dark' }}">
                            Siswa ({{ $counts['siswa'] }})
                        </a>
                    </div>
                </div>

                {{-- Search Box & Filters --}}
                <div class="col-md-5">
                    <form method="GET" action="{{ route('superadmin.credentials.index') }}" class="d-flex gap-2">
                        @if(request('role'))
                            <input type="hidden" name="role" value="{{ request('role') }}">
                        @endif
                        
                        @if(request('role') === 'siswa' && isset($kelasList))
                            <select name="kelas_id" class="form-select border-start-0 text-sm" style="max-width: 150px;" onchange="this.form.submit()">
                                <option value="">Semua Kelas</option>
                                @foreach($kelasList as $k)
                                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        @endif

                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search text-muted" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Cari nama, username, NIP/NISN, email...">
                            @if(request('search'))
                                <a href="{{ route('superadmin.credentials.index', request()->except('search', 'page')) }}" class="btn btn-light border">✕</a>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-primary px-3">Cari</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4" style="width: 50px;">#</th>
                        <th>Pengguna & Identitas</th>
                        <th>Peran</th>
                        <th>Username & Email</th>
                        <th>Password Terbaca</th>
                        <th>Status</th>
                        <th class="text-end pe-4" style="min-width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $u)
                        <tr>
                            <td class="ps-4 text-muted">{{ $users->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" 
                                         style="width: 38px; height: 38px; font-size: 14px; background-color: {{ match($u->role) { 'guru' => '#198754', 'tendik' => '#ffc107', 'siswa' => '#0dcaf0', default => '#0d6efd' } }};">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $u->name }}</div>
                                        <div class="small text-muted">
                                            @if($u->nip)
                                                <span>{{ $u->role === 'siswa' ? 'NISN: ' : 'NIP: ' }}{{ $u->nip }}</span>
                                            @else
                                                <span class="fst-italic text-secondary">Tanpa NIP/NISN</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($u->role === 'guru')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">Guru</span>
                                @elseif($u->role === 'tendik')
                                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning px-2 py-1">Tendik</span>
                                @elseif($u->role === 'siswa')
                                    <span class="badge bg-info bg-opacity-10 text-dark border border-info px-2 py-1">Siswa</span>
                                @else
                                    <span class="badge bg-primary px-2 py-1">{{ ucfirst($u->role) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-dark font-monospace">{{ $u->username }}</div>
                                <div class="small text-muted">{{ $u->email }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="font-monospace bg-light px-2 py-1 rounded border text-secondary" style="min-width: 110px;">
                                        <span class="password-masked" id="pwd-masked-{{ $u->id }}">••••••••</span>
                                        <span class="password-plain d-none text-dark fw-bold" id="pwd-plain-{{ $u->id }}">{{ $u->password_plain ?? 'password123' }}</span>
                                    </div>
                                    {{-- Eye toggle --}}
                                    <button type="button" class="btn btn-sm btn-outline-secondary p-1" onclick="togglePasswordVisibility({{ $u->id }})" title="Lihat / Sembunyikan Password">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-eye" id="eye-icon-{{ $u->id }}" viewBox="0 0 16 16">
                                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                        </svg>
                                    </button>
                                    {{-- Copy button --}}
                                    <button type="button" class="btn btn-sm btn-outline-secondary p-1" onclick="copyToClipboard('{{ $u->password_plain ?? 'password123' }}', this)" title="Salin Password">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-clipboard" viewBox="0 0 16 16">
                                            <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/>
                                            <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td>
                                @if($u->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    {{-- Tombol Reset Password --}}
                                    <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1"
                                            onclick="openResetModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->username }}', '{{ $u->role }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16">
                                            <path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1.337 1.337A3.5 3.5 0 0 1 3.5 11.5M3 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                                        </svg>
                                        Reset Password
                                    </button>

                                    {{-- Tombol Dropdown Opsi Lanjutan --}}
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <button class="dropdown-item d-flex align-items-center gap-2" 
                                                    onclick="openSyncModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->username }}')">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-arrow-repeat text-info" viewBox="0 0 16 16">
                                                    <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/>
                                                    <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/>
                                                </svg>
                                                Sync ke DB Aplikasi Lain
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('superadmin.users.edit', $u) }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil text-secondary" viewBox="0 0 16 16">
                                                    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                                                </svg>
                                                Edit Profil Lengkap
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-person-x text-secondary opacity-50 mb-2" viewBox="0 0 16 16">
                                    <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m.256 7a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.05q.082-.444.275-.844l-.275-.006H8c-4.418 0-8 2.239-8 5v1h8.057q-.033-.48-.057-1z"/>
                                    <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m-.646-4.854.646.647.646-.647a.5.5 0 0 1 .708.708l-.647.646.647.646a.5.5 0 0 1-.708.708l-.646-.647-.646.647a.5.5 0 0 1-.708-.708l.647-.646-.647-.646a.5.5 0 0 1 .708-.708"/>
                                </svg>
                                <p class="mb-0">Tidak ada data pengguna ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                    Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }} akun
                </div>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

{{-- MODAL 1: RESET PASSWORD --}}
<div class="modal fade" id="modalResetPassword" tabindex="-1" aria-labelledby="modalResetPasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formResetPassword" method="POST" action="">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="modalResetPasswordLabel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-key-fill me-2" viewBox="0 0 16 16">
                            <path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1.337 1.337A3.5 3.5 0 0 1 3.5 11.5M3 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                        </svg>
                        Reset Password Pengguna
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- User Preview --}}
                    <div class="alert alert-light border d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px;">
                            <span id="resetModalAvatar">U</span>
                        </div>
                        <div>
                            <div class="fw-bold" id="resetModalName">Nama Pengguna</div>
                            <div class="small text-muted font-monospace" id="resetModalUsername">@username</div>
                            <span class="badge bg-secondary mt-1" id="resetModalRole">Siswa</span>
                        </div>
                    </div>

                    {{-- Options --}}
                    <label class="form-label fw-bold mb-2">Pilih Cara Reset Password:</label>
                    <div class="d-flex flex-column gap-2 mb-3">
                        <div class="form-check p-3 border rounded">
                            <input class="form-check-input" type="radio" name="reset_type" id="type_default" value="default_role" checked onchange="handleResetTypeChange()">
                            <label class="form-check-label fw-semibold" for="type_default">
                                Reset ke Password Standar Peran
                                <div class="text-muted small fw-normal">Contoh: Guru#2026, Tendik#2026, atau Siswa#2026</div>
                            </label>
                        </div>

                        <div class="form-check p-3 border rounded">
                            <input class="form-check-input" type="radio" name="reset_type" id="type_random" value="random" onchange="handleResetTypeChange()">
                            <label class="form-check-label fw-semibold" for="type_random">
                                Buat Password Acak Baru Otomatis
                                <div class="text-muted small fw-normal">Sistem akan men-generate 8 karakter acak yang unik dan aman.</div>
                            </label>
                        </div>

                        <div class="form-check p-3 border rounded">
                            <input class="form-check-input" type="radio" name="reset_type" id="type_custom" value="custom" onchange="handleResetTypeChange()">
                            <label class="form-check-label fw-semibold" for="type_custom">
                                Masukkan Password Baru Manual
                            </label>
                            <div id="customPasswordContainer" class="mt-2 d-none">
                                <input type="text" name="new_password" id="customNewPassword" class="form-control" placeholder="Minimal 6 karakter..." minlength="6">
                            </div>
                        </div>
                    </div>

                    {{-- Cross-App Database Sync Option --}}
                    <div class="bg-light p-3 rounded border">
                        <label class="form-label fw-bold mb-1 d-flex align-items-center gap-1 text-dark">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-database-fill-gear text-primary" viewBox="0 0 16 16">
                                <path d="M8 1c-1.573 0-3.022.289-4.096.777C2.823 2.27 2 2.973 2 3.8v7.4c0 .827.823 1.53 1.904 2.023C4.978 13.711 6.427 14 8 14s3.022-.289 4.096-.777C13.177 12.73 14 12.027 14 11.2V3.8c0-.827-.823-1.53-1.904-2.023C11.022 1.289 9.573 1 8 1"/>
                                <path d="m14.504 1.838-.008.004-.008-.004A7 7 0 0 0 8 .894 7 7 0 0 0 1.512 1.838l-.008.004-.008-.004A4.8 4.8 0 0 0 0 3.8v7.4c0 1.91 3.582 3.46 8 3.46s8-1.55 8-3.46V3.8c0-1.077-.55-2.062-1.496-2.662"/>
                            </svg>
                            Sinkronkan & Reset ke Database Aplikasi Lain
                        </label>
                        <p class="small text-muted mb-2">Centang aplikasi terhubung agar password akun ini otomatis ikut di-reset di database aplikasi tersebut:</p>
                        
                        <div class="d-flex flex-column gap-2">
                            @foreach($availableDbs as $db)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sync_apps[]" value="{{ $db['key'] }}" id="sync_app_{{ $db['key'] }}" {{ $db['is_available'] ? 'checked' : 'disabled' }}>
                                    <label class="form-check-label small" for="sync_app_{{ $db['key'] }}">
                                        <strong>{{ $db['name'] }}</strong> <span class="badge bg-secondary font-monospace">{{ $db['database'] }}</span>
                                        @if(!$db['is_available'])
                                            <span class="text-danger small">(Koneksi DB belum aktif)</span>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan & Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 2: SYNC KE APLIKASI LAIN --}}
<div class="modal fade" id="modalSyncOnly" tabindex="-1" aria-labelledby="modalSyncOnlyLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formSyncOnly" method="POST" action="">
                @csrf
                <div class="modal-header bg-info text-dark">
                    <h5 class="modal-title fw-bold" id="modalSyncOnlyLabel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-arrow-repeat me-2" viewBox="0 0 16 16">
                            <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/>
                            <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/>
                        </svg>
                        Sinkronisasi Akun ke Database Aplikasi Lain
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3 text-secondary">
                        Kredensial dan data akun <strong id="syncModalName" class="text-dark">User</strong> (<span id="syncModalUsername" class="font-monospace">@username</span>) akan diduplikasi/disinkronkan ke database aplikasi berikut:
                    </p>
                    <div class="d-flex flex-column gap-2 mb-3">
                        @foreach($availableDbs as $db)
                            <div class="form-check p-2 border rounded">
                                <input class="form-check-input ms-1 me-2" type="checkbox" name="sync_apps[]" value="{{ $db['key'] }}" id="sync_only_{{ $db['key'] }}" {{ $db['is_available'] ? 'checked' : 'disabled' }}>
                                <label class="form-check-label" for="sync_only_{{ $db['key'] }}">
                                    <strong>{{ $db['name'] }}</strong>
                                    <div class="text-muted small">Target Basis Data: <code>{{ $db['database'] }}</code></div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info fw-bold px-4">Mulai Sinkronisasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function togglePasswordVisibility(userId) {
        const masked = document.getElementById('pwd-masked-' + userId);
        const plain = document.getElementById('pwd-plain-' + userId);
        const icon = document.getElementById('eye-icon-' + userId);

        if (masked.classList.contains('d-none')) {
            masked.classList.remove('d-none');
            plain.classList.add('d-none');
            icon.innerHTML = '<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>';
        } else {
            masked.classList.add('d-none');
            plain.classList.remove('d-none');
            icon.innerHTML = '<path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7 7 0 0 0 2.79-.588M5.21 3.088A7 7 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474z"/><path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 4.803-12-12 .708-.708 12 12z"/>';
        }
    }

    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(function() {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-check text-success" viewBox="0 0 16 16"><path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/></svg>';
            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 1500);
        });
    }

    function handleResetTypeChange() {
        const customContainer = document.getElementById('customPasswordContainer');
        const customInput = document.getElementById('customNewPassword');
        if (document.getElementById('type_custom').checked) {
            customContainer.classList.remove('d-none');
            customInput.required = true;
            customInput.focus();
        } else {
            customContainer.classList.add('d-none');
            customInput.required = false;
        }
    }

    function openResetModal(userId, name, username, role) {
        const form = document.getElementById('formResetPassword');
        form.action = "{{ url('/superadmin/credentials') }}/" + userId + "/reset";

        document.getElementById('resetModalName').textContent = name;
        document.getElementById('resetModalUsername').textContent = '@' + username;
        document.getElementById('resetModalRole').textContent = role.toUpperCase();
        document.getElementById('resetModalAvatar').textContent = name.charAt(0).toUpperCase();

        // Reset radio
        document.getElementById('type_default').checked = true;
        handleResetTypeChange();

        const modal = new bootstrap.Modal(document.getElementById('modalResetPassword'));
        modal.show();
    }

    function openSyncModal(userId, name, username) {
        const form = document.getElementById('formSyncOnly');
        form.action = "{{ url('/superadmin/credentials') }}/" + userId + "/sync-external";

        document.getElementById('syncModalName').textContent = name;
        document.getElementById('syncModalUsername').textContent = '@' + username;

        const modal = new bootstrap.Modal(document.getElementById('modalSyncOnly'));
        modal.show();
    }
</script>
@endpush
@endsection
