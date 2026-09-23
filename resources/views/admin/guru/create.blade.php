@extends('layouts.app')
@section('title', 'Tambah Guru')
@section('page-title', 'Tambah Guru')

@section('dashboard-styles')
.back-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; border: 1px solid var(--border-color); background: transparent; color: var(--text-muted); font-size: 0.82rem; font-weight: 500; text-decoration: none; transition: all 0.2s ease; font-family: 'Poppins', sans-serif; }
.back-btn:hover { background: var(--bg-card); color: var(--text-light); }
.form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 32px; max-width: 740px; }
.form-section-title { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); padding-bottom: 10px; border-bottom: 1px solid var(--border-color); margin-bottom: 18px; margin-top: 26px; }
.form-section-title:first-child { margin-top: 0; }
.field-label { font-size: 0.82rem; font-weight: 500; color: var(--text-muted); margin-bottom: 7px; display: block; }
.field-required { color: var(--accent-gold); }
.form-input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: var(--text-light); border-radius: 12px; padding: 11px 16px; font-family: 'Poppins', sans-serif; font-size: 0.88rem; transition: all 0.25s ease; }
.form-input:focus { outline: none; border-color: var(--primary-light); background: rgba(255,255,255,0.07); box-shadow: 0 0 0 3px rgba(45,138,78,0.15); }
.form-input.is-invalid { border-color: rgba(231,76,60,0.6); }
.field-error { font-size: 0.78rem; color: #f1948a; margin-top: 6px; }
select.form-input option { background: #1a2e24; }
.toggle-switch { display: flex; align-items: center; gap: 12px; cursor: pointer; padding: 12px 16px; border-radius: 12px; border: 1px solid var(--border-color); transition: all 0.2s; }
.toggle-switch:hover { background: rgba(255,255,255,0.03); }
.toggle-switch input[type=checkbox] { display: none; }
.toggle-track { width: 44px; height: 24px; border-radius: 12px; background: rgba(255,255,255,0.1); position: relative; flex-shrink: 0; transition: background 0.3s ease; }
.toggle-track::after { content: ''; position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; border-radius: 50%; background: white; transition: transform 0.3s ease; }
.toggle-switch input:checked + .toggle-track { background: var(--primary-light); }
.toggle-switch input:checked + .toggle-track::after { transform: translateX(20px); }
.btn-save { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border: none; color: white; padding: 12px 28px; border-radius: 12px; font-size: 0.9rem; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif; transition: all 0.3s ease; }
.btn-save:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(26,86,50,0.4); }
.password-wrapper { position: relative; }
.password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1rem; }
@endsection

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('superadmin.guru.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i> Kembali</a>
    <div>
        <h4 style="margin:0;font-size:1.1rem;font-weight:700;">Tambah Guru Baru</h4>
        <p style="margin:0;font-size:0.78rem;color:var(--text-muted);">Daftarkan tenaga pendidik baru ke sistem HilalEdu</p>
    </div>
</div>

<form method="POST" action="{{ route('superadmin.guru.store') }}">
    @csrf
    <div class="form-card">
        {{-- Alert Kesalahan / Validasi --}}
        @if ($errors->any())
            <div class="alert alert-danger mb-4 rounded-3" style="background:rgba(231,76,60,0.15);border:1px solid rgba(231,76,60,0.4);color:#f1948a;font-size:0.85rem;padding:14px 18px;">
                <div class="fw-bold mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size:1.1rem;"></i>
                    <span>Data guru gagal disimpan. Terdapat {{ $errors->count() }} kesalahan / kekurangan data:</span>
                </div>
                <ul class="mb-0 ps-3 mt-2" style="line-height:1.6;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Alert Kesalahan Sistem --}}
        @if (session('error'))
            <div class="alert alert-danger mb-4 rounded-3 d-flex align-items-center gap-2" style="background:rgba(231,76,60,0.15);border:1px solid rgba(231,76,60,0.4);color:#f1948a;font-size:0.85rem;padding:14px 18px;">
                <i class="bi bi-exclamation-octagon-fill" style="font-size:1.1rem;flex-shrink:0;"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <div class="form-section-title">Informasi Dasar</div>
        <div class="row g-3">
            <div class="col-md-12">
                <label class="field-label">Nama Lengkap <span class="field-required">*</span></label>
                <input type="text" name="name" class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ old('name') }}" placeholder="Ahmad Fauzi, S.Pd">
                @error('name')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="field-label">Username <span class="field-required">*</span></label>
                <input type="text" name="username" class="form-input {{ $errors->has('username') ? 'is-invalid' : '' }}" value="{{ old('username') }}" placeholder="ahmad.fauzi">
                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;">Boleh huruf, angka, titik (.), strip (-), dan garis bawah (_).</div>
                @error('username')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="field-label">Email <span class="field-required">*</span></label>
                <input type="email" name="email" class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email') }}" placeholder="guru@hilaledu.sch.id">
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
                <input type="text" name="no_hp" class="form-input {{ $errors->has('no_hp') ? 'is-invalid' : '' }}" value="{{ old('no_hp') }}" placeholder="08123456789">
                @error('no_hp')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="field-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-input {{ $errors->has('jenis_kelamin') ? 'is-invalid' : '' }}">
                    <option value="">-- Pilih --</option>
                    <option value="L" {{ old('jenis_kelamin')==='L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ old('jenis_kelamin')==='P' ? 'selected' : '' }}>Perempuan</option>
                </select>
                @error('jenis_kelamin')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="field-label">Tahun Masuk</label>
                <input type="text" name="tahun_masuk" class="form-input {{ $errors->has('tahun_masuk') ? 'is-invalid' : '' }}" value="{{ old('tahun_masuk') }}" placeholder="Contoh: 2018" maxlength="10">
                @error('tahun_masuk')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="field-label">Lulusan Tahun</label>
                <input type="text" name="lulusan_tahun" class="form-input {{ $errors->has('lulusan_tahun') ? 'is-invalid' : '' }}" value="{{ old('lulusan_tahun') }}" placeholder="Contoh: 2015" maxlength="10">
                @error('lulusan_tahun')<div class="field-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-section-title d-flex justify-content-between align-items-center">
            <span><i class="bi bi-award me-1" style="color:#f39c12;"></i> Tugas Tambahan Sekolah</span>
            <a href="{{ route('superadmin.master.tugas-tambahan') }}" target="_blank" style="font-size:0.75rem;color:#5dade2;text-decoration:none;">
                <i class="bi bi-gear-fill me-1"></i> Kelola Master Tugas Tambahan
            </a>
        </div>
        <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:14px;">
            Pilih tugas tambahan dari daftar master sekolah melalui dropdown. Jika tugas tambahan yang diinginkan belum ada di daftar, silakan tambahkan pada menu <a href="{{ route('superadmin.master.tugas-tambahan') }}" target="_blank" style="color:#5dade2;text-decoration:underline;">Kelola Master Tugas Tambahan</a>.
        </p>

        @php
            $selectedTugas = old('tugas_tambahan', []);
            if (!is_array($selectedTugas)) $selectedTugas = [];
            $daftarOpsi = $daftarTugasTambahan ?? [];

            $masterSelected = array_values(array_filter($selectedTugas, function($t) use ($daftarOpsi) {
                return in_array($t, $daftarOpsi);
            }));
            if (empty($masterSelected)) {
                $masterSelected = [''];
            }
        @endphp

        <div class="row g-3 mb-3">
            <div class="col-md-12">
                <label class="field-label">Jabatan Utama / Status Pengajar</label>
                <input type="text" name="jabatan_utama" class="form-input {{ $errors->has('jabatan_utama') ? 'is-invalid' : '' }}" value="{{ old('jabatan_utama', 'Guru Pengajar') }}" placeholder="Contoh: Guru Pengajar, Guru Produktif">
                @error('jabatan_utama')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            {{-- Dropdown Pilihan Tugas Tambahan Master --}}
            <div class="col-md-12">
                <label class="field-label" style="font-weight:600;color:var(--text-light);">
                    <i class="bi bi-chevron-down me-1" style="color:var(--primary-light);"></i> Pilih Tugas Tambahan Sekolah (Dropdown)
                </label>
                <div id="tugas-dropdown-container" class="d-flex flex-column gap-2 mb-2">
                    @foreach($masterSelected as $index => $val)
                        <div class="d-flex align-items-center gap-2 tugas-row">
                            <select name="tugas_tambahan[]" class="form-input mb-0 flex-grow-1">
                                <option value="">-- Pilih Tugas Tambahan --</option>
                                @foreach($daftarOpsi as $tugas)
                                    <option value="{{ $tugas }}" {{ $tugas === $val ? 'selected' : '' }}>{{ $tugas }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-sm btn-remove-tugas" style="background:rgba(231,76,60,0.12);color:#f1948a;border:1px solid rgba(231,76,60,0.25);border-radius:10px;padding:9px 14px;" title="Hapus">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
                @error('tugas_tambahan')<div class="field-error">{{ $message }}</div>@enderror
                @error('tugas_tambahan.*')<div class="field-error">{{ $message }}</div>@enderror
                <button type="button" id="btn-add-tugas-row" class="btn btn-sm" style="background:rgba(46,204,113,0.12);color:#58d68d;border:1px solid rgba(46,204,113,0.3);border-radius:10px;padding:8px 16px;font-size:0.82rem;font-weight:600;margin-top:6px;">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Dropdown Tugas Tambahan
                </button>
            </div>
        </div>

        <div class="form-section-title">Alamat & Domisili</div>
        <div class="row g-3">
            <div class="col-md-12">
                <label class="field-label">Alamat Lengkap (Jalan / RT / RW)</label>
                <textarea name="alamat" class="form-input {{ $errors->has('alamat') ? 'is-invalid' : '' }}" rows="2" placeholder="Jl. Raya No. 12, RT 01 / RW 02">{{ old('alamat') }}</textarea>
                @error('alamat')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="field-label">Desa / Kelurahan</label>
                <input type="text" name="desa" class="form-input {{ $errors->has('desa') ? 'is-invalid' : '' }}" value="{{ old('desa') }}" placeholder="Desa / Kelurahan">
                @error('desa')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="field-label">Kecamatan</label>
                <input type="text" name="kecamatan" class="form-input {{ $errors->has('kecamatan') ? 'is-invalid' : '' }}" value="{{ old('kecamatan') }}" placeholder="Kecamatan">
                @error('kecamatan')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="field-label">Kabupaten / Kota</label>
                <input type="text" name="kabupaten" class="form-input {{ $errors->has('kabupaten') ? 'is-invalid' : '' }}" value="{{ old('kabupaten') }}" placeholder="Kabupaten / Kota">
                @error('kabupaten')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="field-label">Provinsi</label>
                <input type="text" name="provinsi" class="form-input {{ $errors->has('provinsi') ? 'is-invalid' : '' }}" value="{{ old('provinsi') }}" placeholder="Provinsi">
                @error('provinsi')<div class="field-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-section-title">Riwayat Pendidikan (SD, SMP, SMA, S1, S2)</div>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="field-label">Nama Sekolah SD / MI</label>
                <input type="text" name="pendidikan_sd" class="form-input {{ $errors->has('pendidikan_sd') ? 'is-invalid' : '' }}" value="{{ old('pendidikan_sd') }}" placeholder="SDN 1 ...">
                @error('pendidikan_sd')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="field-label">Tahun Lulus SD</label>
                <input type="text" name="tahun_lulus_sd" class="form-input {{ $errors->has('tahun_lulus_sd') ? 'is-invalid' : '' }}" value="{{ old('tahun_lulus_sd') }}" placeholder="2004" maxlength="10">
                @error('tahun_lulus_sd')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-8">
                <label class="field-label">Nama Sekolah SMP / MTs</label>
                <input type="text" name="pendidikan_smp" class="form-input {{ $errors->has('pendidikan_smp') ? 'is-invalid' : '' }}" value="{{ old('pendidikan_smp') }}" placeholder="SMPN 1 ...">
                @error('pendidikan_smp')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="field-label">Tahun Lulus SMP</label>
                <input type="text" name="tahun_lulus_smp" class="form-input {{ $errors->has('tahun_lulus_smp') ? 'is-invalid' : '' }}" value="{{ old('tahun_lulus_smp') }}" placeholder="2007" maxlength="10">
                @error('tahun_lulus_smp')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-8">
                <label class="field-label">Nama Sekolah SMA / SMK / MA</label>
                <input type="text" name="pendidikan_sma" class="form-input {{ $errors->has('pendidikan_sma') ? 'is-invalid' : '' }}" value="{{ old('pendidikan_sma') }}" placeholder="SMAN 1 ...">
                @error('pendidikan_sma')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="field-label">Tahun Lulus SMA</label>
                <input type="text" name="tahun_lulus_sma" class="form-input {{ $errors->has('tahun_lulus_sma') ? 'is-invalid' : '' }}" value="{{ old('tahun_lulus_sma') }}" placeholder="2010" maxlength="10">
                @error('tahun_lulus_sma')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-8">
                <label class="field-label">Nama Kampus & Prodi S1</label>
                <input type="text" name="pendidikan_s1" class="form-input {{ $errors->has('pendidikan_s1') ? 'is-invalid' : '' }}" value="{{ old('pendidikan_s1') }}" placeholder="Universitas ... - Pend. ...">
                @error('pendidikan_s1')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="field-label">Tahun Lulus S1</label>
                <input type="text" name="tahun_lulus_s1" class="form-input {{ $errors->has('tahun_lulus_s1') ? 'is-invalid' : '' }}" value="{{ old('tahun_lulus_s1') }}" placeholder="2014" maxlength="10">
                @error('tahun_lulus_s1')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-8">
                <label class="field-label">Nama Kampus & Prodi S2 (Jika Ada)</label>
                <input type="text" name="pendidikan_s2" class="form-input {{ $errors->has('pendidikan_s2') ? 'is-invalid' : '' }}" value="{{ old('pendidikan_s2') }}" placeholder="Universitas ...">
                @error('pendidikan_s2')<div class="field-error">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="field-label">Tahun Lulus S2</label>
                <input type="text" name="tahun_lulus_s2" class="form-input {{ $errors->has('tahun_lulus_s2') ? 'is-invalid' : '' }}" value="{{ old('tahun_lulus_s2') }}" placeholder="2018" maxlength="10">
                @error('tahun_lulus_s2')<div class="field-error">{{ $message }}</div>@enderror
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
                    <input type="password" name="password_confirmation" id="pw2" class="form-input {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}" placeholder="Ulangi password">
                    <button type="button" class="password-toggle" onclick="togglePass('pw2','ic2')"><i class="bi bi-eye-fill" id="ic2"></i></button>
                </div>
                @error('password_confirmation')<div class="field-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-section-title">Status</div>
        <label class="toggle-switch">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
            <div class="toggle-track"></div>
            <div style="font-size:0.88rem;font-weight:500;">Akun Aktif</div>
        </label>

        <div class="d-flex gap-3 mt-4 pt-4" style="border-top:1px solid var(--border-color);">
            <button type="submit" class="btn-save"><i class="bi bi-check-circle-fill"></i> Simpan Guru</button>
            <a href="{{ route('superadmin.guru.index') }}" class="back-btn">Batal</a>
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

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('tugas-dropdown-container');
    const btnAdd = document.getElementById('btn-add-tugas-row');
    const daftarOpsi = @json($daftarTugasTambahan ?? []);

    if (btnAdd && container) {
        btnAdd.addEventListener('click', function() {
            let optionsHtml = '<option value="">-- Pilih Tugas Tambahan --</option>';
            daftarOpsi.forEach(function(opt) {
                optionsHtml += `<option value="${opt}">${opt}</option>`;
            });

            const row = document.createElement('div');
            row.className = 'd-flex align-items-center gap-2 tugas-row mt-1';
            row.innerHTML = `
                <select name="tugas_tambahan[]" class="form-input mb-0 flex-grow-1">
                    ${optionsHtml}
                </select>
                <button type="button" class="btn btn-sm btn-remove-tugas" style="background:rgba(231,76,60,0.12);color:#f1948a;border:1px solid rgba(231,76,60,0.25);border-radius:10px;padding:9px 14px;" title="Hapus">
                    <i class="bi bi-trash-fill"></i>
                </button>
            `;
            container.appendChild(row);
        });

        container.addEventListener('click', function(e) {
            const btnRemove = e.target.closest('.btn-remove-tugas');
            if (btnRemove) {
                const rows = container.querySelectorAll('.tugas-row');
                if (rows.length > 1) {
                    btnRemove.closest('.tugas-row').remove();
                } else {
                    const select = rows[0].querySelector('select');
                    if (select) select.value = '';
                }
            }
        });
    }
});
</script>
@endsection
