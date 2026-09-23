@extends('layouts.app')
@section('title', 'Tambah Tenaga Kependidikan')
@section('page-title', 'Tambah Tendik')

@section('dashboard-styles')
.back-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; border: 1px solid var(--border-color); background: transparent; color: var(--text-muted); font-size: 0.82rem; font-weight: 500; text-decoration: none; transition: all 0.2s ease; font-family: 'Poppins', sans-serif; }
.back-btn:hover { background: var(--bg-card); color: var(--text-light); }
.form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 32px; max-width: 760px; }
.form-section-title { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); padding-bottom: 10px; border-bottom: 1px solid var(--border-color); margin-bottom: 18px; margin-top: 26px; }
.form-section-title:first-child { margin-top: 0; }
.field-label { font-size: 0.82rem; font-weight: 500; color: var(--text-muted); margin-bottom: 7px; display: block; }
.field-required { color: var(--accent-gold); }
.form-input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: var(--text-light); border-radius: 12px; padding: 11px 16px; font-family: 'Poppins', sans-serif; font-size: 0.88rem; transition: all 0.25s ease; }
.form-input:focus { outline: none; border-color: #e67e22; background: rgba(255,255,255,0.07); box-shadow: 0 0 0 3px rgba(230,126,34,0.15); }
.form-input.is-invalid { border-color: rgba(231,76,60,0.6); }
.field-error { font-size: 0.78rem; color: #f1948a; margin-top: 6px; }
select.form-input option { background: #1e1e1e; }
.toggle-switch { display: flex; align-items: center; gap: 12px; cursor: pointer; padding: 12px 16px; border-radius: 12px; border: 1px solid var(--border-color); transition: all 0.2s; }
.toggle-switch input[type=checkbox] { display: none; }
.toggle-track { width: 44px; height: 24px; border-radius: 12px; background: rgba(255,255,255,0.1); position: relative; flex-shrink: 0; transition: background 0.3s ease; }
.toggle-track::after { content: ''; position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; border-radius: 50%; background: white; transition: transform 0.3s ease; }
.toggle-switch input:checked + .toggle-track { background: #e67e22; }
.toggle-switch input:checked + .toggle-track::after { transform: translateX(20px); }
.btn-save { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #d35400, #e67e22); border: none; color: white; padding: 12px 28px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif; transition: all 0.3s ease; }
.btn-save:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(211,84,0,0.4); }
.password-wrapper { position: relative; }
.password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1rem; }
@endsection

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('superadmin.tendik.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i> Kembali</a>
    <div>
        <h4 style="margin:0;font-size:1.1rem;font-weight:700;">Tambah Tenaga Kependidikan Baru</h4>
        <p style="margin:0;font-size:0.78rem;color:var(--text-muted);">Daftarkan staf atau tenaga kependidikan ke sistem HilalEdu</p>
    </div>
</div>

<form method="POST" action="{{ route('superadmin.tendik.store') }}">
    @csrf
    <div class="form-card">
        <div class="form-section-title">Informasi Dasar</div>
        <div class="row g-3">
            <div class="col-md-12">
                <label class="field-label">Nama Lengkap & Gelar <span class="field-required">*</span></label>
                <input type="text" name="name" class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ old('name') }}" placeholder="Contoh: Siti Nurhaliza, S.Kom">
                @error('name')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="field-label">Username <span class="field-required">*</span></label>
                <input type="text" name="username" class="form-input {{ $errors->has('username') ? 'is-invalid' : '' }}" value="{{ old('username') }}" placeholder="Contoh: tendik_siti">
                @error('username')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="field-label">Email <span class="field-required">*</span></label>
                <input type="email" name="email" class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email') }}" placeholder="Contoh: siti@hilaledu.sch.id">
                @error('email')<div class="field-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-section-title">Data Kepegawaian</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="field-label">NUPTK</label>
                <input type="text" name="nip" class="form-input {{ $errors->has('nip') ? 'is-invalid' : '' }}" value="{{ old('nip') }}" placeholder="Contoh: 1234567890123456 (NUPTK)">
                @error('nip')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="field-label">No. HP / WhatsApp</label>
                <input type="text" name="no_hp" class="form-input" value="{{ old('no_hp') }}" placeholder="08123456789">
            </div>
            <div class="col-md-4">
                <label class="field-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-input">
                    <option value="">-- Pilih --</option>
                    <option value="L" {{ old('jenis_kelamin')==='L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ old('jenis_kelamin')==='P' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="field-label">Tahun Masuk</label>
                <input type="text" name="tahun_masuk" class="form-input" value="{{ old('tahun_masuk') }}" placeholder="Contoh: 2019" maxlength="10">
            </div>
            <div class="col-md-4">
                <label class="field-label">Lulusan Tahun</label>
                <input type="text" name="lulusan_tahun" class="form-input" value="{{ old('lulusan_tahun') }}" placeholder="Contoh: 2016" maxlength="10">
            </div>
        </div>

        <div class="form-section-title">Alamat & Domisili</div>
        <div class="row g-3">
            <div class="col-md-12">
                <label class="field-label">Alamat Lengkap (Jalan / RT / RW)</label>
                <textarea name="alamat" class="form-input" rows="2" placeholder="Jl. Raya Sukajadi No. 12, RT 01 / RW 02">{{ old('alamat') }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="field-label">Desa / Kelurahan</label>
                <input type="text" name="desa" class="form-input" value="{{ old('desa') }}" placeholder="Desa / Kelurahan">
            </div>
            <div class="col-md-3">
                <label class="field-label">Kecamatan</label>
                <input type="text" name="kecamatan" class="form-input" value="{{ old('kecamatan') }}" placeholder="Kecamatan">
            </div>
            <div class="col-md-3">
                <label class="field-label">Kabupaten / Kota</label>
                <input type="text" name="kabupaten" class="form-input" value="{{ old('kabupaten') }}" placeholder="Kabupaten / Kota">
            </div>
            <div class="col-md-3">
                <label class="field-label">Provinsi</label>
                <input type="text" name="provinsi" class="form-input" value="{{ old('provinsi') }}" placeholder="Provinsi">
            </div>
        </div>

        <div class="form-section-title">Riwayat Pendidikan (SD, SMP, SMA, S1, S2)</div>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="field-label">Nama Sekolah SD / MI</label>
                <input type="text" name="pendidikan_sd" class="form-input" value="{{ old('pendidikan_sd') }}" placeholder="SDN 1 ...">
            </div>
            <div class="col-md-4">
                <label class="field-label">Tahun Lulus SD</label>
                <input type="text" name="tahun_lulus_sd" class="form-input" value="{{ old('tahun_lulus_sd') }}" placeholder="2004" maxlength="10">
            </div>

            <div class="col-md-8">
                <label class="field-label">Nama Sekolah SMP / MTs</label>
                <input type="text" name="pendidikan_smp" class="form-input" value="{{ old('pendidikan_smp') }}" placeholder="SMPN 1 ...">
            </div>
            <div class="col-md-4">
                <label class="field-label">Tahun Lulus SMP</label>
                <input type="text" name="tahun_lulus_smp" class="form-input" value="{{ old('tahun_lulus_smp') }}" placeholder="2007" maxlength="10">
            </div>

            <div class="col-md-8">
                <label class="field-label">Nama Sekolah SMA / SMK / MA</label>
                <input type="text" name="pendidikan_sma" class="form-input" value="{{ old('pendidikan_sma') }}" placeholder="SMKN 1 ...">
            </div>
            <div class="col-md-4">
                <label class="field-label">Tahun Lulus SMA</label>
                <input type="text" name="tahun_lulus_sma" class="form-input" value="{{ old('tahun_lulus_sma') }}" placeholder="2010" maxlength="10">
            </div>

            <div class="col-md-8">
                <label class="field-label">Nama Kampus & Prodi S1</label>
                <input type="text" name="pendidikan_s1" class="form-input" value="{{ old('pendidikan_s1') }}" placeholder="Universitas ... - Sistem Informasi">
            </div>
            <div class="col-md-4">
                <label class="field-label">Tahun Lulus S1</label>
                <input type="text" name="tahun_lulus_s1" class="form-input" value="{{ old('tahun_lulus_s1') }}" placeholder="2015" maxlength="10">
            </div>

            <div class="col-md-8">
                <label class="field-label">Nama Kampus & Prodi S2 (Jika Ada)</label>
                <input type="text" name="pendidikan_s2" class="form-input" value="{{ old('pendidikan_s2') }}" placeholder="Universitas ...">
            </div>
            <div class="col-md-4">
                <label class="field-label">Tahun Lulus S2</label>
                <input type="text" name="tahun_lulus_s2" class="form-input" value="{{ old('tahun_lulus_s2') }}" placeholder="2019" maxlength="10">
            </div>
        </div>

        <div class="form-section-title">Password Akun</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="field-label">Password <span class="field-required">*</span></label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="pw1" class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Min. 6 karakter">
                    <button type="button" class="password-toggle" onclick="togglePass('pw1','ic1')"><i class="bi bi-eye-fill" id="ic1"></i></button>
                </div>
                @error('password')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="field-label">Konfirmasi Password <span class="field-required">*</span></label>
                <div class="password-wrapper">
                    <input type="password" name="password_confirmation" id="pw2" class="form-input" placeholder="Ulangi password">
                    <button type="button" class="password-toggle" onclick="togglePass('pw2','ic2')"><i class="bi bi-eye-fill" id="ic2"></i></button>
                </div>
            </div>
        </div>

        <div class="form-section-title">Status</div>
        <label class="toggle-switch">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
            <div class="toggle-track"></div>
            <div style="font-size:0.88rem;font-weight:500;">Akun Aktif</div>
        </label>

        <div class="d-flex gap-3 mt-4 pt-4" style="border-top:1px solid var(--border-color);">
            <button type="submit" class="btn-save"><i class="bi bi-check-circle-fill"></i> Simpan Tendik</button>
            <a href="{{ route('superadmin.tendik.index') }}" class="back-btn">Batal</a>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
function togglePass(id, iconId) {
    const f = document.getElementById(id), i = document.getElementById(iconId);
    f.type = f.type === 'password' ? 'text' : 'password';
    i.className = f.type === 'password' ? 'bi bi-eye-fill' : 'bi bi-eye-slash-fill';
}
</script>
@endsection
