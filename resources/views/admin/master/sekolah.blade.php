@extends('layouts.app')
@section('title', 'Pengaturan Sekolah & Kepala Sekolah')
@section('page-title', 'Data Master - Pengaturan Sekolah & Kepala Sekolah')

@section('dashboard-styles')
.section-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 18px; overflow: hidden; padding: 24px; }
.field-label { font-size: 0.82rem; font-weight: 500; color: var(--text-muted); margin-bottom: 7px; display: block; }
.field-required { color: var(--accent-gold); }
.form-input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: var(--text-light); border-radius: 10px; padding: 10px 14px; font-family: 'Poppins', sans-serif; font-size: 0.85rem; margin-bottom: 14px; transition: all 0.25s ease; }
.form-input:focus { outline: none; border-color: var(--primary-light); background: rgba(255,255,255,0.07); box-shadow: 0 0 0 3px rgba(45,138,78,0.15); }
.form-input option { background: #1a2e24; color: #fff; }
.btn-save { display: inline-flex; align-items: center; justify-content: center; gap: 7px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border: none; color: white; padding: 12px 28px; border-radius: 10px; font-size: 0.88rem; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif; transition: all 0.25s; }
.btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(26,86,50,0.4); }
.nav-master { display: flex; gap: 6px; margin-bottom: 20px; flex-wrap: wrap; }
.nav-master a { padding: 8px 16px; border-radius: 10px; font-size: 0.82rem; font-weight: 500; text-decoration: none; border: 1px solid var(--border-color); color: var(--text-muted); transition: all 0.2s; }
.nav-master a.active, .nav-master a:hover { background: var(--bg-card); color: var(--text-light); border-color: var(--primary-light); }
@endsection

@section('content')
<div class="nav-master" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:20px;">
    <div style="display:flex; gap:6px; flex-wrap:wrap;">
        <a href="{{ route('superadmin.master.sekolah') }}" class="active"><i class="bi bi-building me-1"></i> Pengaturan Sekolah</a>
        <a href="{{ route('superadmin.master.tahun-ajaran') }}"><i class="bi bi-calendar-check me-1"></i> Tahun Ajaran</a>
        <a href="{{ route('superadmin.master.jurusan') }}"><i class="bi bi-diagram-3 me-1"></i> Jurusan</a>
        <a href="{{ route('superadmin.master.kelas') }}"><i class="bi bi-collection me-1"></i> Kelas</a>
        <a href="{{ route('superadmin.master.tugas-tambahan') }}"><i class="bi bi-award me-1"></i> Tugas Tambahan</a>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="{{ route('superadmin.guru.index') }}" style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:8px; font-size:0.8rem; font-weight:600; text-decoration:none; background:rgba(46,204,113,0.1); color:#58d68d; border:1px solid rgba(46,204,113,0.3); transition:all 0.2s;">
            <i class="bi bi-person-workspace"></i> Data Guru
        </a>
    </div>
</div>

@if(session('success'))
    <div style="background:rgba(46,204,113,0.1);border:1px solid rgba(46,204,113,0.3);color:#58d68d;border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:0.85rem;">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
@endif

<div class="section-card">
    <div style="border-bottom:1px solid var(--border-color);padding-bottom:16px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <div>
            <h5 style="font-size:1.05rem;font-weight:700;margin:0;"><i class="bi bi-building-fill me-2" style="color:#58d68d;"></i>Pengaturan Identitas Sekolah & Penugasan Kepala Sekolah</h5>
            <p style="font-size:0.8rem;color:var(--text-muted);margin:4px 0 0 0;">
                Penunjukan Kepala Sekolah di halaman ini akan otomatis mensinkronkan Tugas Tambahan "Kepala Sekolah" pada profil guru yang bersangkutan.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('superadmin.master.sekolah.update') }}">
        @csrf @method('PUT')
        
        <div class="row g-3">
            <div class="col-md-8">
                <label class="field-label">Nama Sekolah <span class="field-required">*</span></label>
                <input type="text" name="nama_sekolah" class="form-input" value="{{ old('nama_sekolah', $setting->nama_sekolah) }}" required placeholder="Contoh: SMK PLUS AL HILAL">
            </div>

            <div class="col-md-4">
                <label class="field-label">NPSN Sekolah</label>
                <input type="text" name="npsn" class="form-input" value="{{ old('npsn', $setting->npsn) }}" placeholder="Contoh: 12345678">
            </div>

            {{-- Penunjukan Kepala Sekolah --}}
            <div class="col-md-12">
                <div style="background:rgba(241,196,15,0.06);border:1px solid rgba(241,196,15,0.25);border-radius:14px;padding:18px;">
                    <label class="field-label" style="color:#f39c12;font-size:0.9rem;font-weight:700;margin-bottom:6px;display:flex;align-items:center;gap:8px;">
                        <i class="bi bi-person-badge-fill" style="font-size:1.1rem;"></i> Penunjukkan Kepala Sekolah (Sinkron Otomatis)
                    </label>
                    <p style="font-size:0.78rem;color:var(--text-muted);margin-bottom:12px;">
                        Pilih guru yang bertugas sebagai Kepala Sekolah. Tugas tambahan <strong>"Kepala Sekolah"</strong> akan otomatis ditambahkan ke profil guru ini.
                    </p>
                    <select name="kepala_sekolah_id" class="form-input mb-0">
                        <option value="">-- Belum Ditentukan / Kosong --</option>
                        @foreach($gurus as $guru)
                            <option value="{{ $guru->id }}" {{ old('kepala_sekolah_id', $setting->kepala_sekolah_id) == $guru->id ? 'selected' : '' }}>
                                {{ $guru->name }} ({{ $guru->nip ?? $guru->username }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <label class="field-label">Email Resmi Sekolah</label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $setting->email) }}" placeholder="admin@sekolah.sch.id">
            </div>

            <div class="col-md-4">
                <label class="field-label">No. Telepon / Fax</label>
                <input type="text" name="telepon" class="form-input" value="{{ old('telepon', $setting->telepon) }}" placeholder="(021) 1234567">
            </div>

            <div class="col-md-4">
                <label class="field-label">Website Resmi</label>
                <input type="text" name="website" class="form-input" value="{{ old('website', $setting->website) }}" placeholder="https://sekolah.sch.id">
            </div>

            <div class="col-md-12">
                <label class="field-label">Alamat Lengkap Sekolah</label>
                <textarea name="alamat" class="form-input" rows="3" placeholder="Jl. Raya Utama No. 100...">{{ old('alamat', $setting->alamat) }}</textarea>
            </div>
        </div>

        <div style="margin-top:20px;pt-3;border-top:1px solid var(--border-color);display:flex;justify-content:flex-end;">
            <button type="submit" class="btn-save"><i class="bi bi-check-circle-fill"></i> Simpan Pengaturan Sekolah</button>
        </div>
    </form>
</div>
@endsection
