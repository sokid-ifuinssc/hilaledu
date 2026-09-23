@extends('layouts.app')

@section('title', 'Profil & Biodata Siswa')
@section('page-title', 'Profil Saya')

@section('dashboard-styles')
.profile-hero {
    background: linear-gradient(135deg, rgba(41, 128, 185, 0.45), rgba(21, 67, 96, 0.6));
    border: 1px solid rgba(52, 152, 219, 0.3);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.profile-hero::before {
    content: '';
    position: absolute;
    top: -40%;
    right: -10%;
    width: 250px;
    height: 250px;
    background: radial-gradient(circle, rgba(52, 152, 219, 0.15) 0%, transparent 70%);
    border-radius: 50%;
}
.form-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 32px;
    margin-bottom: 24px;
}
.form-section-title {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #5dade2;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 20px;
    margin-top: 30px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.form-section-title:first-child { margin-top: 0; }
.field-label {
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--text-muted);
    margin-bottom: 7px;
    display: block;
}
.field-required { color: var(--accent-gold); }
.form-input {
    width: 100%;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--border-color);
    color: var(--text-light);
    border-radius: 12px;
    padding: 11px 16px;
    font-family: 'Poppins', sans-serif;
    font-size: 0.88rem;
    transition: all 0.25s ease;
}
.form-input:focus {
    outline: none;
    border-color: #5dade2;
    background: rgba(255, 255, 255, 0.08);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
}
.form-input:disabled, .form-input[readonly] {
    opacity: 0.6;
    cursor: not-allowed;
    background: rgba(0, 0, 0, 0.2);
}
select.form-input option { background: #1a2e24; color: #fff; }
.btn-save {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #2980b9, #3498db);
    border: none;
    color: white;
    padding: 12px 28px;
    border-radius: 12px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
}
.btn-save:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(41, 128, 185, 0.4);
    color: white;
}
.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    border-radius: 12px;
    border: 1px solid var(--border-color);
    background: transparent;
    color: var(--text-muted);
    font-size: 0.85rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
}
.back-btn:hover { background: var(--bg-card); color: var(--text-light); }
.edu-card {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 14px;
    padding: 16px;
    margin-bottom: 14px;
}
.edu-badge {
    background: rgba(52, 152, 219, 0.15);
    color: #5dade2;
    border: 1px solid rgba(52, 152, 219, 0.3);
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    text-transform: uppercase;
}
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('siswa.dashboard') }}" class="back-btn"><i class="bi bi-arrow-left"></i> Dashboard</a>
        <div>
            <h4 style="margin:0;font-size:1.2rem;font-weight:800;">Profil & Biodata Siswa</h4>
            <p style="margin:0;font-size:0.8rem;color:var(--text-muted);">Kelola data pribadi, alamat, kontak orang tua, dan riwayat pendidikan Anda</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert" style="background:rgba(46,204,113,0.15);border:1px solid rgba(46,204,113,0.3);color:#58d68d;border-radius:14px;">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="background:rgba(231,76,60,0.15);border:1px solid rgba(231,76,60,0.3);color:#f1948a;border-radius:14px;">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Terdapat kesalahan pada isian form:</div>
        <ul class="mb-0 ps-3 small">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Profile Hero Card --}}
<div class="profile-hero">
    <div class="d-flex align-items-center gap-4 flex-wrap">
        <div class="avatar-circle rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow" 
             style="width: 72px; height: 72px; font-size: 1.8rem; background: linear-gradient(135deg, #2980b9, #3498db);">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge" style="background:rgba(52,152,219,0.2);color:#5dade2;border:1px solid rgba(52,152,219,0.4);">
                    <i class="bi bi-person-fill me-1"></i> Peserta Didik
                </span>
                @if($user->nip)
                    <span class="badge" style="background:rgba(212,168,67,0.15);color:var(--accent-gold);border:1px solid rgba(212,168,67,0.3);">
                        NISN: {{ $user->nip }}
                    </span>
                @endif
            </div>
            <h3 style="margin:0;font-weight:800;font-size:1.4rem;">{{ $user->name }}</h3>
            <p style="margin:0;color:var(--text-muted);font-size:0.85rem;">{{ $user->email }} · @<span class="font-monospace">{{ $user->username }}</span></p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('siswa.profile.update') }}">
    @csrf
    @method('PUT')

    <div class="form-card">
        {{-- Section 1: Identitas Pribadi Siswa --}}
        <div class="form-section-title">
            <i class="bi bi-person-badge-fill"></i> Identitas Diri Siswa
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="field-label">Nama Lengkap <span class="field-required">*</span></label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="field-label">NISN (Nomor Induk Siswa Nasional)</label>
                <input type="text" name="nip" class="form-input" value="{{ old('nip', $user->nip) }}" placeholder="Contoh: 0071234567">
            </div>
            <div class="col-md-6">
                <label class="field-label">Username Akun</label>
                <input type="text" class="form-input" value="{{ $user->username }}" readonly disabled>
                <span class="small text-muted" style="font-size:0.75rem;">Username dibuat otomatis dari sistem.</span>
            </div>
            <div class="col-md-6">
                <label class="field-label">Alamat Email <span class="field-required">*</span></label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="col-md-6">
                <label class="field-label">Nomor HP / WhatsApp Siswa</label>
                <input type="text" name="no_hp" class="form-input" value="{{ old('no_hp', $user->no_hp) }}" placeholder="Contoh: 085712345678">
            </div>
            <div class="col-md-6">
                <label class="field-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-input">
                    <option value="">-- Pilih --</option>
                    <option value="L" {{ old('jenis_kelamin', $user->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ old('jenis_kelamin', $user->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
        </div>

        {{-- Section 2: Data Orang Tua / Wali --}}
        <div class="form-section-title">
            <i class="bi bi-people-fill"></i> Data Orang Tua / Wali
        </div>
        <div class="alert p-3 mb-3 d-flex align-items-center gap-2" style="background:rgba(212,168,67,0.1);border:1px solid rgba(212,168,67,0.25);color:var(--accent-gold);border-radius:12px;font-size:0.82rem;">
            <i class="bi bi-info-circle-fill fs-5"></i>
            <div>Informasi kontak orang tua ini sangat penting bagi <strong>Wali Kelas</strong> dan <strong>Guru BK</strong> di aplikasi sekolah untuk keperluan monitoring dan komunikasi pembinaan.</div>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="field-label">Nama Lengkap Ayah Kandung</label>
                <input type="text" name="nama_ayah" class="form-input" value="{{ old('nama_ayah', $user->nama_ayah) }}" placeholder="Nama Ayah">
            </div>
            <div class="col-md-6">
                <label class="field-label">Nama Lengkap Ibu Kandung</label>
                <input type="text" name="nama_ibu" class="form-input" value="{{ old('nama_ibu', $user->nama_ibu) }}" placeholder="Nama Ibu">
            </div>
            <div class="col-md-12">
                <label class="field-label">Nomor HP / WhatsApp Orang Tua atau Wali</label>
                <input type="text" name="no_hp_ortu" class="form-input" value="{{ old('no_hp_ortu', $user->no_hp_ortu) }}" placeholder="Contoh: 081234567890">
                <span class="small text-muted" style="font-size:0.75rem;">Pastikan nomor aktif dan dapat dihubungi melalui WhatsApp atau telepon langsung.</span>
            </div>
        </div>

        {{-- Section 3: Alamat Tempat Tinggal --}}
        <div class="form-section-title">
            <i class="bi bi-geo-alt-fill"></i> Alamat & Tempat Tinggal
        </div>
        <div class="row g-3">
            <div class="col-md-12">
                <label class="field-label">Alamat Lengkap (Jalan, RT/RW, No. Rumah)</label>
                <textarea name="alamat" class="form-input" rows="2" placeholder="Jl. Raya Cibadak No. 12, RT 02 / RW 04">{{ old('alamat', $user->alamat) }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="field-label">Desa / Kelurahan</label>
                <input type="text" name="desa" class="form-input" value="{{ old('desa', $user->desa) }}" placeholder="Desa / Kelurahan">
            </div>
            <div class="col-md-3">
                <label class="field-label">Kecamatan</label>
                <input type="text" name="kecamatan" class="form-input" value="{{ old('kecamatan', $user->kecamatan) }}" placeholder="Kecamatan">
            </div>
            <div class="col-md-3">
                <label class="field-label">Kabupaten / Kota</label>
                <input type="text" name="kabupaten" class="form-input" value="{{ old('kabupaten', $user->kabupaten) }}" placeholder="Kabupaten / Kota">
            </div>
            <div class="col-md-3">
                <label class="field-label">Provinsi</label>
                <input type="text" name="provinsi" class="form-input" value="{{ old('provinsi', $user->provinsi) }}" placeholder="Provinsi">
            </div>
        </div>

        {{-- Section 4: Riwayat Pendidikan --}}
        <div class="form-section-title">
            <i class="bi bi-mortarboard-fill"></i> Riwayat Pendidikan Sebelumnya
        </div>

        {{-- SD --}}
        <div class="edu-card">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="edu-badge">Asal Sekolah SD / MI</span>
            </div>
            <div class="row g-2">
                <div class="col-md-9">
                    <label class="field-label">Nama Sekolah SD</label>
                    <input type="text" name="pendidikan_sd" class="form-input" value="{{ old('pendidikan_sd', $user->pendidikan_sd) }}" placeholder="Contoh: SDN 1 Cibadak">
                </div>
                <div class="col-md-3">
                    <label class="field-label">Tahun Lulus SD</label>
                    <input type="text" name="tahun_lulus_sd" class="form-input" value="{{ old('tahun_lulus_sd', $user->tahun_lulus_sd) }}" placeholder="Contoh: 2021" maxlength="10">
                </div>
            </div>
        </div>

        {{-- SMP --}}
        <div class="edu-card">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="edu-badge">Asal Sekolah SMP / MTs</span>
            </div>
            <div class="row g-2">
                <div class="col-md-9">
                    <label class="field-label">Nama Sekolah SMP</label>
                    <input type="text" name="pendidikan_smp" class="form-input" value="{{ old('pendidikan_smp', $user->pendidikan_smp) }}" placeholder="Contoh: SMPN 1 Cibadak">
                </div>
                <div class="col-md-3">
                    <label class="field-label">Tahun Lulus SMP</label>
                    <input type="text" name="tahun_lulus_smp" class="form-input" value="{{ old('tahun_lulus_smp', $user->tahun_lulus_smp) }}" placeholder="Contoh: 2024" maxlength="10">
                </div>
            </div>
        </div>

        {{-- Section 5: Ganti Password --}}
        <div class="form-section-title">
            <i class="bi bi-shield-lock-fill"></i> Keamanan Akun (Ganti Password)
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="field-label">Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                <input type="password" name="password" class="form-input" placeholder="Minimal 6 karakter...">
            </div>
            <div class="col-md-6">
                <label class="field-label">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" class="form-input" placeholder="Ketik ulang password baru...">
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mt-4 pt-4" style="border-top:1px solid var(--border-color);">
            <div class="text-muted small">
                <i class="bi bi-info-circle me-1"></i> Data yang Anda perbarui akan langsung tersinkronisasi ke seluruh sistem sekolah.
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('siswa.dashboard') }}" class="back-btn">Batal</a>
                <button type="submit" class="btn-save">
                    <i class="bi bi-check-circle-fill"></i> Simpan Biodata
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
