@extends('layouts.app')

@section('title', 'Edit Pengaturan: ' . $application->name)
@section('page-title', 'Pengaturan Aplikasi')

@section('dashboard-styles')
.form-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 32px;
    max-width: 960px;
    margin: 0 auto;
}
.form-section-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--accent-gold);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 18px;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    display: flex;
    align-items: center;
    gap: 8px;
}
.form-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-light);
    margin-bottom: 6px;
}
.form-control-dark, .form-select-dark {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--border-color);
    color: var(--text-light);
    border-radius: 12px;
    padding: 10px 16px;
    font-family: 'Poppins', sans-serif;
    font-size: 0.88rem;
    width: 100%;
    transition: all 0.25s ease;
}
.form-control-dark:focus, .form-select-dark:focus {
    outline: none;
    border-color: var(--primary-lighter);
    box-shadow: 0 0 0 3px rgba(45, 138, 78, 0.25);
    background: rgba(255, 255, 255, 0.08);
    color: white;
}
.form-select-dark option {
    background: #111d17;
    color: var(--text-light);
}
.form-hint {
    font-size: 0.76rem;
    color: var(--text-muted);
    margin-top: 5px;
    line-height: 1.4;
}
.icon-preset-btn {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: var(--text-light);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all 0.2s ease;
}
.icon-preset-btn:hover {
    background: rgba(45, 138, 78, 0.3);
    border-color: var(--primary-lighter);
    color: #58d68d;
    transform: scale(1.08);
}
.api-key-box {
    background: #090e0c;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    padding: 12px 16px;
    font-family: 'Consolas', monospace;
    font-size: 0.85rem;
    color: #a8d5ba;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
@endsection

@section('content')
<div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <a href="{{ route('superadmin.apps.index') }}" class="btn btn-sm d-inline-flex align-items-center gap-2" style="background:rgba(255,255,255,0.06);border:1px solid var(--border-color);color:var(--text-muted);border-radius:10px;padding:6px 14px;text-decoration:none;">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Aplikasi
        </a>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('superadmin.apps.show', $application) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2" style="border-radius:10px;">
            <i class="bi bi-person-badge"></i> Penugasan Koordinator
        </a>
    </div>
</div>

<div class="form-card">
    <div class="d-flex align-items-center gap-3 mb-4">
        <div style="width:52px;height:52px;border-radius:16px;background:{{ $application->color }}22;color:{{ $application->color }};display:flex;align-items:center;justify-content:center;font-size:1.6rem;">
            <i class="{{ $application->icon }}"></i>
        </div>
        <div>
            <h4 class="fw-bold mb-1" style="font-size:1.3rem;">Pengaturan: {{ $application->name }}</h4>
            <p class="text-muted small mb-0">Kelola alamat URL, database MySQL, status, dan kredensial modul ini.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4" style="background:rgba(46,204,113,0.15);border:1px solid rgba(46,204,113,0.3);color:#58d68d;border-radius:12px;">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-4" style="background:rgba(231,76,60,0.15);border:1px solid rgba(231,76,60,0.3);color:#f1948a;border-radius:12px;">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Periksa kesalahan input:</div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- KREDENSIAL API KEY & AKSES --}}
    <div class="form-section-title">
        <i class="bi bi-key-fill"></i> Kredensial Akses & Kunci Modul
    </div>

    <div class="mb-4">
        <label class="form-label">API Secret Key (Untuk Autentikasi Aplikasi Klien)</label>
        <div class="api-key-box mb-2">
            <span id="apiKeyText">{{ $application->api_key ?? 'Belum ada API key' }}</span>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-light" onclick="copyApiKey()" style="font-size:0.75rem;padding:3px 10px;border-radius:8px;">
                    <i class="bi bi-clipboard me-1"></i> Salin Key
                </button>
            </div>
        </div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="form-hint">Gunakan API Key ini jika aplikasi Anda memerlukan otorisasi secret saat memanggil API HilalEdu.</div>
            <form action="{{ route('superadmin.apps.regenerate-api-key', $application) }}" method="POST" onsubmit="return confirm('Regenerate API Key akan mengubah kunci rahasia. Aplikasi yang menggunakan kunci lama harus diperbarui. Lanjutkan?');">
                @csrf
                <button type="submit" class="btn btn-sm text-warning p-0" style="background:none;border:none;font-size:0.78rem;text-decoration:underline;cursor:pointer;">
                    <i class="bi bi-arrow-clockwise me-1"></i> Regenerate API Key Baru
                </button>
            </form>
        </div>
    </div>

    <form action="{{ route('superadmin.apps.update', $application) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- SEKSI 1: IDENTITAS DASAR --}}
        <div class="form-section-title">
            <i class="bi bi-info-circle"></i> 1. Identitas Aplikasi
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-7">
                <label class="form-label">Nama Aplikasi <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $application->name) }}" required class="form-control-dark">
            </div>

            <div class="col-md-5">
                <label class="form-label">Slug / Identifier Unik <span class="text-danger">*</span></label>
                <input type="text" name="slug" value="{{ old('slug', $application->slug) }}" required class="form-control-dark" {{ $application->slug === 'hilaledu' ? 'readonly' : '' }}>
            </div>

            <div class="col-12">
                <label class="form-label">Deskripsi Singkat</label>
                <textarea name="description" rows="2" class="form-control-dark">{{ old('description', $application->description) }}</textarea>
            </div>
        </div>

        {{-- SEKSI 2: INTEGRASI ALAMAT & DATABASE --}}
        <div class="form-section-title">
            <i class="bi bi-hdd-network"></i> 2. Alamat URL & Koneksi Database
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Alamat URL Aplikasi</label>
                <input type="url" name="url" value="{{ old('url', $application->url) }}" class="form-control-dark" placeholder="http://localhost:8001">
                <div class="form-hint">Alamat URL yang dibuka saat pengguna mengakses aplikasi ini secara langsung.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nama Database MySQL Sub-Sistem</label>
                <input type="text" name="database_name" value="{{ old('database_name', $application->database_name) }}" class="form-control-dark" placeholder="contoh: db_monitoring_bk" {{ $application->slug === 'hilaledu' ? 'readonly' : '' }}>
                <div class="form-hint">Nama database MySQL lokal aplikasi tempat akun pengguna disinkronkan.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">URL Login Khusus (Opsional)</label>
                <input type="url" name="login_url" value="{{ old('login_url', $application->login_url) }}" class="form-control-dark" placeholder="http://localhost:8001/login">
            </div>

            <div class="col-md-6">
                <label class="form-label">URL Endpoint Akses Langsung (Opsional)</label>
                <input type="url" name="sso_redirect_url" value="{{ old('sso_redirect_url', $application->sso_redirect_url) }}" class="form-control-dark" placeholder="http://localhost:8001/login">
            </div>
        </div>

        {{-- SEKSI 3: TAMPILAN & STATUS --}}
        <div class="form-section-title">
            <i class="bi bi-palette"></i> 3. Desain, Ikon, & Status
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Status Aplikasi <span class="text-danger">*</span></label>
                <select name="status" class="form-select-dark" required>
                    <option value="active" {{ old('status', $application->status) === 'active' ? 'selected' : '' }}>🟢 Aktif (Bisa Diakses)</option>
                    <option value="coming_soon" {{ old('status', $application->status) === 'coming_soon' ? 'selected' : '' }}>🟡 Segera Hadir</option>
                    <option value="inactive" {{ old('status', $application->status) === 'inactive' ? 'selected' : '' }}>🔴 Nonaktif</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Warna Aksen</label>
                <div class="d-flex align-items-center gap-2">
                    <input type="color" name="color" id="colorPicker" value="{{ old('color', $application->color) }}" style="width:46px;height:42px;border:none;border-radius:10px;background:none;cursor:pointer;">
                    <input type="text" id="colorText" value="{{ old('color', $application->color) }}" class="form-control-dark" readonly style="font-family:monospace;">
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Ikon (Bootstrap Icons)</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:rgba(255,255,255,0.05);border-color:var(--border-color);color:var(--text-light);" id="iconPreviewBox">
                        <i id="iconPreview" class="{{ old('icon', $application->icon) }}"></i>
                    </span>
                    <input type="text" name="icon" id="iconInput" value="{{ old('icon', $application->icon) }}" class="form-control-dark" onkeyup="updateIconPreview()">
                </div>
                <div class="d-flex gap-1 mt-2 flex-wrap">
                    <span class="icon-preset-btn" onclick="setIcon('bi-shield-check')"><i class="bi bi-shield-check"></i></span>
                    <span class="icon-preset-btn" onclick="setIcon('bi-briefcase-fill')"><i class="bi bi-briefcase-fill"></i></span>
                    <span class="icon-preset-btn" onclick="setIcon('bi-pc-display-horizontal')"><i class="bi bi-pc-display-horizontal"></i></span>
                    <span class="icon-preset-btn" onclick="setIcon('bi-book-half')"><i class="bi bi-book-half"></i></span>
                    <span class="icon-preset-btn" onclick="setIcon('bi-mortarboard-fill')"><i class="bi bi-mortarboard-fill"></i></span>
                    <span class="icon-preset-btn" onclick="setIcon('bi-card-checklist')"><i class="bi bi-card-checklist"></i></span>
                    <span class="icon-preset-btn" onclick="setIcon('bi-shop')"><i class="bi bi-shop"></i></span>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label">Daftar Fitur Utama</label>
                @php
                    $featuresStr = is_array($application->features) ? implode("\n", $application->features) : '';
                @endphp
                <textarea name="features_raw" rows="3" class="form-control-dark" placeholder="Tuliskan fitur utama aplikasi (pisahkan baris baru)...">{{ old('features_raw', $featuresStr) }}</textarea>
            </div>
        </div>

        {{-- SEKSI 4: OPSI AKSES TERPADU & SINKRONISASI --}}
        <div class="form-section-title">
            <i class="bi bi-shield-check"></i> 4. Opsi Akses Terpadu & Sinkronisasi Internal HilalEdu
        </div>

        <div class="p-3 mb-4 rounded-3" style="background:rgba(255,255,255,0.02);border:1px solid var(--border-color);">
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" name="auto_sync" id="autoSyncCheck" value="1" {{ old('auto_sync', $application->auto_sync) ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold text-light" for="autoSyncCheck">
                    Aktifkan Auto-Sync Pengguna ke Database Aplikasi Ini
                </label>
                <div class="text-muted small">Jika dicentang, setiap kali user dibuat, diimpor, diedit, atau direset passwordnya di HilalEdu, akun tersebut otomatis disalin ke database aplikasi ini.</div>
            </div>

            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" name="sso_enabled" id="ssoEnabledCheck" value="1" {{ old('sso_enabled', $application->sso_enabled) ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold text-light" for="ssoEnabledCheck">
                    Izinkan Akses Terpadu HilalEdu
                </label>
                <div class="text-muted small">Memperbolehkan integrasi autentikasi internal pengguna HilalEdu.</div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                @if($application->slug !== 'hilaledu')
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="document.getElementById('deleteAppForm').submit();">
                        <i class="bi bi-trash me-1"></i> Hapus Aplikasi Ini
                    </button>
                @endif
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('superadmin.apps.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3" style="border-color:var(--border-color);color:var(--text-muted);">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-bold" style="background:linear-gradient(135deg, var(--primary), var(--primary-light));border:none;">
                    <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    @if($application->slug !== 'hilaledu')
        <form id="deleteAppForm" action="{{ route('superadmin.apps.destroy', $application) }}" method="POST" class="d-none" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aplikasi {{ $application->name }} dari portal?');">
            @csrf
            @method('DELETE')
        </form>
    @endif
</div>

<script>
    const colorPicker = document.getElementById('colorPicker');
    const colorText = document.getElementById('colorText');
    if (colorPicker && colorText) {
        colorPicker.addEventListener('input', function() {
            colorText.value = colorPicker.value;
        });
    }

    function setIcon(iconClass) {
        document.getElementById('iconInput').value = iconClass;
        updateIconPreview();
    }

    function updateIconPreview() {
        const icon = document.getElementById('iconInput').value.trim();
        document.getElementById('iconPreview').className = icon || 'bi-app';
    }

    function copyApiKey() {
        const key = document.getElementById('apiKeyText').innerText.trim();
        navigator.clipboard.writeText(key).then(() => {
            alert('API Key berhasil disalin ke clipboard!');
        });
    }
</script>
@endsection
