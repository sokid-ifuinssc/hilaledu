@extends('layouts.app')

@section('title', 'Tambah Aplikasi Baru - HilalEdu')
@section('page-title', 'Tambah Aplikasi')

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
@endsection

@section('content')
<div class="mb-4 d-flex align-items-center justify-content-between">
    <div>
        <a href="{{ route('superadmin.apps.index') }}" class="btn btn-sm d-inline-flex align-items-center gap-2" style="background:rgba(255,255,255,0.06);border:1px solid var(--border-color);color:var(--text-muted);border-radius:10px;padding:6px 14px;text-decoration:none;">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Aplikasi
        </a>
    </div>
</div>

<div class="form-card">
    <div class="d-flex align-items-center gap-3 mb-4">
        <div style="width:48px;height:48px;border-radius:14px;background:rgba(45,138,78,0.2);color:var(--primary-lighter);display:flex;align-items:center;justify-content:center;font-size:1.5rem;">
            <i class="bi bi-grid-plus-fill"></i>
        </div>
        <div>
            <h4 class="fw-bold mb-1" style="font-size:1.3rem;">Tambah Aplikasi Baru</h4>
            <p class="text-muted small mb-0">Daftarkan sub-aplikasi baru ke dalam portal dan konfigurasi otentikasi terpusat.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-4" style="background:rgba(231,76,60,0.15);border:1px solid rgba(231,76,60,0.3);color:#f1948a;border-radius:12px;">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Mohon periksa kesalahan input:</div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('superadmin.apps.store') }}" method="POST">
        @csrf

        {{-- SEKSI 1: IDENTITAS DASAR --}}
        <div class="form-section-title">
            <i class="bi bi-info-circle"></i> 1. Identitas Aplikasi
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-7">
                <label class="form-label">Nama Aplikasi <span class="text-danger">*</span></label>
                <input type="text" name="name" id="appNameInput" value="{{ old('name') }}" required class="form-control-dark" placeholder="Contoh: Sistem Ujian CBT Online" onkeyup="autoSlug()">
                <div class="form-hint">Nama lengkap aplikasi yang akan ditampilkan di portal guru, tendik, dan siswa.</div>
            </div>

            <div class="col-md-5">
                <label class="form-label">Slug / Identifier Unik</label>
                <input type="text" name="slug" id="appSlugInput" value="{{ old('slug') }}" class="form-control-dark" placeholder="contoh: cbt-online">
                <div class="form-hint">Kode pengenal unik tanpa spasi (huruf kecil & tanda minus).</div>
            </div>

            <div class="col-12">
                <label class="form-label">Deskripsi Singkat</label>
                <textarea name="description" rows="2" class="form-control-dark" placeholder="Jelaskan fungsi utama aplikasi ini bagi sekolah...">{{ old('description') }}</textarea>
            </div>
        </div>

        {{-- SEKSI 2: INTEGRASI ALAMAT & DATABASE --}}
        <div class="form-section-title">
            <i class="bi bi-hdd-network"></i> 2. Alamat URL & Koneksi Database
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Alamat URL Aplikasi</label>
                <input type="url" name="url" value="{{ old('url') }}" class="form-control-dark" placeholder="http://localhost:8003 atau https://cbt.sekolah.sch.id">
                <div class="form-hint">Alamat URL yang dibuka saat pengguna mengakses aplikasi ini secara langsung.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nama Database MySQL Sub-Sistem</label>
                <input type="text" name="database_name" value="{{ old('database_name') }}" class="form-control-dark" placeholder="contoh: db_cbt_online">
                <div class="form-hint">Jika aplikasi memakai database sendiri, isi di sini agar akun pengguna otomatis disinkronkan ke tabel users database tersebut.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">URL Login Khusus (Opsional)</label>
                <input type="url" name="login_url" value="{{ old('login_url') }}" class="form-control-dark" placeholder="http://localhost:8003/login">
                <div class="form-hint">Opsional. Alamat spesifik halaman login aplikasi jika berbeda dari URL utama.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label">URL Endpoint Akses Langsung (Opsional)</label>
                <input type="url" name="sso_redirect_url" value="{{ old('sso_redirect_url') }}" class="form-control-dark" placeholder="http://localhost:8003/login">
                <div class="form-hint">Endpoint callback untuk menangani akses langsung satu-klik di HilalEdu.</div>
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
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>🟢 Aktif (Bisa Diakses)</option>
                    <option value="coming_soon" {{ old('status') === 'coming_soon' ? 'selected' : '' }}>🟡 Segera Hadir</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>🔴 Nonaktif</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Warna Aksen</label>
                <div class="d-flex align-items-center gap-2">
                    <input type="color" name="color" id="colorPicker" value="{{ old('color', '#3498db') }}" style="width:46px;height:42px;border:none;border-radius:10px;background:none;cursor:pointer;">
                    <input type="text" id="colorText" value="{{ old('color', '#3498db') }}" class="form-control-dark" readonly style="font-family:monospace;">
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Ikon (Bootstrap Icons)</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:rgba(255,255,255,0.05);border-color:var(--border-color);color:var(--text-light);" id="iconPreviewBox">
                        <i id="iconPreview" class="{{ old('icon', 'bi-app') }}"></i>
                    </span>
                    <input type="text" name="icon" id="iconInput" value="{{ old('icon', 'bi-app') }}" class="form-control-dark" placeholder="bi-app" onkeyup="updateIconPreview()">
                </div>
                <div class="d-flex gap-1 mt-2 flex-wrap">
                    <span class="icon-preset-btn" onclick="setIcon('bi-shield-check')" title="Monitoring / Keamanan"><i class="bi bi-shield-check"></i></span>
                    <span class="icon-preset-btn" onclick="setIcon('bi-briefcase-fill')" title="Prakerin / Vokasi"><i class="bi bi-briefcase-fill"></i></span>
                    <span class="icon-preset-btn" onclick="setIcon('bi-pc-display-horizontal')" title="CBT / Komputer"><i class="bi bi-pc-display-horizontal"></i></span>
                    <span class="icon-preset-btn" onclick="setIcon('bi-book-half')" title="Perpustakaan"><i class="bi bi-book-half"></i></span>
                    <span class="icon-preset-btn" onclick="setIcon('bi-mortarboard-fill')" title="Akademik"><i class="bi bi-mortarboard-fill"></i></span>
                    <span class="icon-preset-btn" onclick="setIcon('bi-card-checklist')" title="Absensi"><i class="bi bi-card-checklist"></i></span>
                    <span class="icon-preset-btn" onclick="setIcon('bi-shop')" title="Koperasi / Toko"><i class="bi bi-shop"></i></span>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label">Daftar Fitur Utama</label>
                <textarea name="features_raw" rows="3" class="form-control-dark" placeholder="Tuliskan fitur utama aplikasi ini (pisahkan dengan koma atau baris baru)...&#10;Contoh:&#10;Bank Soal Online&#10;Ujian Otomatis&#10;Penilaian Realtime">{{ old('features_raw') }}</textarea>
            </div>
        </div>

        {{-- SEKSI 4: OPSI AKSES TERPADU & SINKRONISASI --}}
        <div class="form-section-title">
            <i class="bi bi-shield-check"></i> 4. Opsi Akses Terpadu & Sinkronisasi Internal HilalEdu
        </div>

        <div class="p-3 mb-4 rounded-3" style="background:rgba(255,255,255,0.02);border:1px solid var(--border-color);">
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" name="auto_sync" id="autoSyncCheck" value="1" {{ old('auto_sync', '1') ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold text-light" for="autoSyncCheck">
                    Aktifkan Auto-Sync Pengguna ke Database Aplikasi Ini
                </label>
                <div class="text-muted small">Jika dicentang, setiap kali user dibuat, diimpor, diedit, atau direset passwordnya di HilalEdu, akun tersebut otomatis disalin ke database aplikasi ini.</div>
            </div>

            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" name="sso_enabled" id="ssoEnabledCheck" value="1" {{ old('sso_enabled', '1') ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold text-light" for="ssoEnabledCheck">
                    Izinkan Akses Terpadu HilalEdu
                </label>
                <div class="text-muted small">Memperbolehkan integrasi autentikasi internal pengguna HilalEdu.</div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3">
            <a href="{{ route('superadmin.apps.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-3" style="border-color:var(--border-color);color:var(--text-muted);">
                Batal
            </a>
            <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-bold" style="background:linear-gradient(135deg, var(--primary), var(--primary-light));border:none;">
                <i class="bi bi-check-lg me-1"></i> Simpan & Daftarkan Aplikasi
            </button>
        </div>
    </form>
</div>

<script>
    function autoSlug() {
        const name = document.getElementById('appNameInput').value;
        const slug = name.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
        document.getElementById('appSlugInput').value = slug;
    }

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
</script>
@endsection
