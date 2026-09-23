@extends('layouts.app')
@section('title', 'Data Master Tahun Ajaran')
@section('page-title', 'Data Master - Tahun Ajaran')

@section('dashboard-styles')
.master-layout { display: grid; grid-template-columns: 1fr 320px; gap: 20px; }
.section-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 18px; overflow: hidden; }
.section-header { padding: 16px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; }
.section-header h6 { font-size: 0.95rem; font-weight: 700; margin: 0; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { padding: 12px 20px; text-align: left; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-muted); border-bottom: 1px solid var(--border-color); background: rgba(255,255,255,0.02); }
.data-table td { padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.03); font-size: 0.87rem; vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: rgba(255,255,255,0.02); }
.form-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 18px; padding: 24px; }
.field-label { font-size: 0.82rem; font-weight: 500; color: var(--text-muted); margin-bottom: 7px; display: block; }
.field-required { color: var(--accent-gold); }
.form-input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: var(--text-light); border-radius: 10px; padding: 10px 14px; font-family: 'Poppins', sans-serif; font-size: 0.85rem; margin-bottom: 14px; transition: all 0.25s ease; }
.form-input:focus { outline: none; border-color: var(--primary-light); background: rgba(255,255,255,0.07); box-shadow: 0 0 0 3px rgba(45,138,78,0.15); }
.form-input.is-invalid { border-color: rgba(231,76,60,0.6); }
.field-error { font-size: 0.76rem; color: #f1948a; margin-top: -10px; margin-bottom: 10px; }
.btn-save { display: inline-flex; align-items: center; gap: 7px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border: none; color: white; padding: 10px 22px; border-radius: 10px; font-size: 0.86rem; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif; width: 100%; justify-content: center; transition: all 0.25s; }
.btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(26,86,50,0.4); }
.action-btn { display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; border-radius: 7px; font-size: 0.75rem; font-weight: 500; cursor: pointer; border: none; font-family: 'Poppins', sans-serif; text-decoration: none; transition: all 0.2s; }
.btn-set { background: rgba(212,168,67,0.12); color: #d4a843; border: 1px solid rgba(212,168,67,0.3); }
.btn-set:hover { background: rgba(212,168,67,0.22); }
.btn-del { background: rgba(231,76,60,0.1); color: #f1948a; border: 1px solid rgba(231,76,60,0.2); }
.btn-del:hover { background: rgba(231,76,60,0.2); }
.badge-aktif { background: rgba(46,204,113,0.15); color: #58d68d; border: 1px solid rgba(46,204,113,0.3); font-size: 0.72rem; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
.nav-master { display: flex; gap: 6px; margin-bottom: 20px; flex-wrap: wrap; }
.nav-master a { padding: 8px 16px; border-radius: 10px; font-size: 0.82rem; font-weight: 500; text-decoration: none; border: 1px solid var(--border-color); color: var(--text-muted); transition: all 0.2s; }
.nav-master a.active, .nav-master a:hover { background: var(--bg-card); color: var(--text-light); border-color: var(--primary-light); }
@endsection

@section('content')
<div class="nav-master" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:20px;">
    <div style="display:flex; gap:6px; flex-wrap:wrap;">
        <a href="{{ route('superadmin.master.sekolah') }}"><i class="bi bi-building me-1"></i> Pengaturan Sekolah</a>
        <a href="{{ route('superadmin.master.tahun-ajaran') }}" class="active"><i class="bi bi-calendar-check me-1"></i> Tahun Ajaran</a>
        <a href="{{ route('superadmin.master.jurusan') }}"><i class="bi bi-diagram-3 me-1"></i> Jurusan</a>
        <a href="{{ route('superadmin.master.kelas') }}"><i class="bi bi-collection me-1"></i> Kelas</a>
        <a href="{{ route('superadmin.master.tugas-tambahan') }}"><i class="bi bi-award me-1"></i> Tugas Tambahan</a>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="{{ route('superadmin.master.export') }}" style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:8px; font-size:0.8rem; font-weight:600; text-decoration:none; background:rgba(52,152,219,0.1); color:#5dade2; border:1px solid rgba(52,152,219,0.3); transition:all 0.2s;">
            <i class="bi bi-download"></i> Export Data
        </a>
        <a href="{{ route('superadmin.master.import') }}" style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:8px; font-size:0.8rem; font-weight:600; text-decoration:none; background:rgba(46,204,113,0.1); color:#58d68d; border:1px solid rgba(46,204,113,0.3); transition:all 0.2s;">
            <i class="bi bi-upload"></i> Import Data
        </a>
    </div>
</div>

@if(session('success'))<div style="background:rgba(46,204,113,0.1);border:1px solid rgba(46,204,113,0.3);color:#58d68d;border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:0.85rem;"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>@endif
@if(session('error'))<div style="background:rgba(231,76,60,0.1);border:1px solid rgba(231,76,60,0.3);color:#f1948a;border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:0.85rem;"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>@endif

<div class="master-layout">
    <div class="section-card">
        <div class="section-header">
            <h6><i class="bi bi-calendar-check-fill me-2" style="color:var(--accent-gold);"></i>Data Tahun Ajaran</h6>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead><tr><th>Tahun Ajaran</th><th>Periode</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($tahunAjarans as $ta)
                    <tr>
                        <td style="font-weight:700;font-size:0.95rem;">{{ $ta->nama }}</td>
                        <td style="color:var(--text-muted);font-size:0.82rem;">{{ $ta->tahun_mulai }} / {{ $ta->tahun_selesai }}</td>
                        <td>
                            @if($ta->is_aktif)
                                <span class="badge-aktif"><i class="bi bi-check-circle-fill me-1"></i>Aktif</span>
                            @else
                                <span style="color:var(--text-muted);font-size:0.78rem;">Tidak Aktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @unless($ta->is_aktif)
                                <form method="POST" action="{{ route('superadmin.master.tahun-ajaran.set-aktif', $ta) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="action-btn btn-set"><i class="bi bi-check-circle"></i> Set Aktif</button>
                                </form>
                                @endunless
                                @unless($ta->is_aktif)
                                <form method="POST" action="{{ route('superadmin.master.tahun-ajaran.destroy', $ta) }}"
                                      onsubmit="return confirm('Hapus tahun ajaran {{ addslashes($ta->nama) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn btn-del"><i class="bi bi-trash-fill"></i></button>
                                </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;padding:36px;color:var(--text-muted);">Belum ada data tahun ajaran</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <form method="POST" action="{{ route('superadmin.master.tahun-ajaran.store') }}" class="form-card">
            @csrf
            <h6 style="font-weight:700;margin-bottom:20px;font-size:0.95rem;"><i class="bi bi-plus-circle-fill me-2" style="color:var(--accent-gold);"></i>Tambah Tahun Ajaran</h6>
            <label class="field-label">Nama <span class="field-required">*</span></label>
            <input type="text" name="nama" class="form-input {{ $errors->has('nama') ? 'is-invalid' : '' }}" value="{{ old('nama') }}" placeholder="2025/2026">
            @error('nama')<div class="field-error">{{ $message }}</div>@enderror
            <label class="field-label">Tahun Mulai <span class="field-required">*</span></label>
            <input type="number" name="tahun_mulai" class="form-input" value="{{ old('tahun_mulai', date('Y')) }}" min="2000" max="2100">
            <label class="field-label">Tahun Selesai <span class="field-required">*</span></label>
            <input type="number" name="tahun_selesai" class="form-input" value="{{ old('tahun_selesai', date('Y')+1) }}" min="2000" max="2100">
            <button type="submit" class="btn-save"><i class="bi bi-check-circle-fill"></i> Simpan</button>
        </form>
    </div>
</div>
@endsection
