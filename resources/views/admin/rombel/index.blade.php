@extends('layouts.app')
@section('title', 'Pengaturan Rombel Kelas')
@section('page-title', 'Pengaturan Rombel Kelas')

@section('dashboard-styles')
.page-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; }
.btn-add { display: inline-flex; align-items: center; gap: 7px; padding: 10px 20px; border-radius: 12px; font-size: 0.88rem; font-weight: 600; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border: none; color: white; text-decoration: none; transition: all 0.25s ease; }
.btn-add:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(26,86,50,0.4); color: white; }
.stats-mini { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; }
.stat-mini { flex: 1; min-width: 200px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 16px 20px; display: flex; align-items: center; gap: 14px; }
.stat-mini-val { font-size: 1.4rem; font-weight: 800; }
.stat-mini-lbl { font-size: 0.75rem; color: var(--text-muted); }
.filter-bar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px; }
.filter-input { background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: var(--text-light); border-radius: 10px; padding: 9px 14px; font-family: 'Poppins', sans-serif; font-size: 0.85rem; }
.filter-input:focus { outline: none; border-color: var(--primary-light); }
.filter-input option { background: #1a2e24; }

.rombel-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
.rombel-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; padding: 24px; transition: all 0.3s ease; position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; }
.rombel-card:hover { transform: translateY(-3px); border-color: rgba(46,204,113,0.4); box-shadow: 0 12px 30px rgba(0,0,0,0.3); }
.rombel-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
.rombel-title { font-size: 1.2rem; font-weight: 800; color: var(--text-light); margin: 0; display: flex; align-items: center; gap: 8px; }
.rombel-badge-tingkat { background: rgba(46,204,113,0.15); color: #58d68d; border: 1px solid rgba(46,204,113,0.3); font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 8px; }
.rombel-jurusan { font-size: 0.78rem; color: var(--text-muted); margin-top: 4px; }
.rombel-wali { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 10px 14px; margin-bottom: 18px; display: flex; align-items: center; gap: 10px; font-size: 0.83rem; }
.rombel-wali-icon { width: 30px; height: 30px; border-radius: 8px; background: rgba(241,196,15,0.15); color: #f39c12; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0; }

.rombel-stat-box { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 0.82rem; }
.rombel-stat-num { font-weight: 700; color: #58d68d; font-size: 1rem; }
.rombel-progress-track { width: 100%; height: 8px; background: rgba(255,255,255,0.08); border-radius: 10px; overflow: hidden; margin-bottom: 20px; }
.rombel-progress-fill { height: 100%; background: linear-gradient(90deg, var(--primary), #2ecc71); border-radius: 10px; transition: width 0.5s ease; }

.btn-manage-rombel { width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 11px; border-radius: 12px; background: rgba(46,204,113,0.12); color: #58d68d; border: 1px solid rgba(46,204,113,0.3); font-weight: 600; font-size: 0.86rem; text-decoration: none; transition: all 0.25s ease; }
.btn-manage-rombel:hover { background: var(--primary-light); color: white; border-color: var(--primary-light); box-shadow: 0 6px 18px rgba(46,204,113,0.3); }
@endsection

@section('content')
<div class="page-header">
    <div>
        <h4 style="font-size:1.15rem;font-weight:700;margin:0;"><i class="bi bi-grid-3x3-gap-fill me-2" style="color:#f39c12;"></i>Pengaturan Rombongan Belajar (Rombel)</h4>
        <p style="font-size:0.8rem;color:var(--text-muted);margin:4px 0 0;">Pengelompokan siswa ke dalam rombel kelas dan pengelolaan anggota kelas</p>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="{{ route('superadmin.master.kelas') }}" class="btn-add" style="background:rgba(255,255,255,0.08); color:var(--text-light); border:1px solid var(--border-color);">
            <i class="bi bi-gear-fill"></i> Master Kelas
        </a>
        <a href="{{ route('superadmin.siswa.index') }}" class="btn-add" style="background:rgba(52,152,219,0.12); color:#5dade2; border:1px solid rgba(52,152,219,0.3);">
            <i class="bi bi-mortarboard-fill"></i> Data Siswa
        </a>
    </div>
</div>

<div class="stats-mini">
    <div class="stat-mini">
        <div style="width:42px;height:42px;border-radius:12px;background:rgba(52,152,219,0.15);display:flex;align-items:center;justify-content:center;"><i class="bi bi-people-fill" style="color:#5dade2;font-size:1.2rem;"></i></div>
        <div><div class="stat-mini-val">{{ $totalSiswa }}</div><div class="stat-mini-lbl">Total Seluruh Siswa</div></div>
    </div>
    <div class="stat-mini">
        <div style="width:42px;height:42px;border-radius:12px;background:rgba(46,204,113,0.15);display:flex;align-items:center;justify-content:center;"><i class="bi bi-check-circle-fill" style="color:#58d68d;font-size:1.2rem;"></i></div>
        <div><div class="stat-mini-val" style="color:#58d68d;">{{ $siswaPunyaKelas }}</div><div class="stat-mini-lbl">Sudah Punya Rombel</div></div>
    </div>
    <div class="stat-mini">
        <div style="width:42px;height:42px;border-radius:12px;background:rgba(241,196,15,0.15);display:flex;align-items:center;justify-content:center;"><i class="bi bi-exclamation-triangle-fill" style="color:#f39c12;font-size:1.2rem;"></i></div>
        <div><div class="stat-mini-val" style="color:#f39c12;">{{ $siswaTanpaKelas }}</div><div class="stat-mini-lbl">Belum Punya Rombel</div></div>
    </div>
</div>

@if(session('success'))
<div style="background:rgba(46,204,113,0.1);border:1px solid rgba(46,204,113,0.3);color:#58d68d;border-radius:14px;padding:14px 18px;margin-bottom:20px;font-size:0.88rem;display:flex;align-items:center;gap:10px;">
    <i class="bi bi-check-circle-fill" style="font-size:1.1rem;"></i>
    <div>{{ session('success') }}</div>
</div>
@endif

<form method="GET" class="filter-bar">
    <select name="tingkat" class="filter-input">
        <option value="">Semua Tingkat</option>
        <option value="10" {{ request('tingkat')=='10' ? 'selected' : '' }}>Tingkat 10 (X)</option>
        <option value="11" {{ request('tingkat')=='11' ? 'selected' : '' }}>Tingkat 11 (XI)</option>
        <option value="12" {{ request('tingkat')=='12' ? 'selected' : '' }}>Tingkat 12 (XII)</option>
    </select>
    <select name="jurusan_id" class="filter-input">
        <option value="">Semua Jurusan</option>
        @foreach($jurusanList as $j)
        <option value="{{ $j->id }}" {{ request('jurusan_id')==$j->id ? 'selected' : '' }}>{{ $j->nama_jurusan ?? $j->nama }} ({{ $j->singkatan }})</option>
        @endforeach
    </select>
    <input type="text" name="search" class="filter-input" placeholder="Cari nama kelas atau wali kelas..." value="{{ request('search') }}" style="flex:1;min-width:200px;">
    <button type="submit" class="btn-add" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);box-shadow:none;"><i class="bi bi-search"></i> Filter</button>
</form>

<div class="rombel-grid">
    @forelse($kelasList as $kelas)
    @php
        $kapasitasDefault = 36;
        $totalAnggota = $kelas->siswas_count;
        $persen = min(100, round(($totalAnggota / $kapasitasDefault) * 100));
    @endphp
    <div class="rombel-card">
        <div>
            <div class="rombel-header">
                <div>
                    <h5 class="rombel-title">
                        <i class="bi bi-door-open-fill" style="color:#58d68d;"></i>
                        {{ $kelas->nama_lengkap }}
                    </h5>
                    <div class="rombel-jurusan">
                        {{ $kelas->jurusan?->nama_jurusan ?? $kelas->jurusan?->nama ?? 'Umum' }}
                    </div>
                </div>
                <span class="rombel-badge-tingkat">Tingkat {{ $kelas->tingkat }}</span>
            </div>

            <div class="rombel-wali">
                <div class="rombel-wali-icon"><i class="bi bi-person-badge-fill"></i></div>
                <div>
                    <div style="font-size:0.7rem;color:var(--text-muted);">Wali Kelas</div>
                    <div style="font-weight:600;color:var(--text-light);">{{ $kelas->wali_kelas ?: ($kelas->waliKelasGuru?->name ?: 'Belum Ditentukan') }}</div>
                </div>
            </div>

            <div class="rombel-stat-box">
                <span style="color:var(--text-muted);">Anggota Rombel:</span>
                <span class="rombel-stat-num">{{ $totalAnggota }} <span style="font-size:0.75rem;color:var(--text-muted);font-weight:400;">Siswa</span></span>
            </div>
            <div class="rombel-progress-track">
                <div class="rombel-progress-fill" style="width: {{ max(5, $persen) }}%;"></div>
            </div>
        </div>

        <a href="{{ route('superadmin.rombel.show', $kelas) }}" class="btn-manage-rombel">
            <i class="bi bi-people-fill"></i> Kelola Anggota Rombel
        </a>
    </div>
    @empty
    <div style="grid-column: 1 / -1; background:var(--bg-card); border:1px solid var(--border-color); border-radius:20px; padding:60px 20px; text-align:center; color:var(--text-muted);">
        <i class="bi bi-grid-3x3-gap d-block mb-3" style="font-size:3rem; opacity:0.2;"></i>
        <h5>Belum Ada Data Rombel Kelas</h5>
        <p style="font-size:0.85rem;">Silakan tambahkan data kelas di menu Master Kelas terlebih dahulu.</p>
        <a href="{{ route('superadmin.master.kelas') }}" class="btn-add" style="display:inline-flex;margin-top:10px;"><i class="bi bi-plus-circle-fill"></i> Tambah Kelas Baru</a>
    </div>
    @endforelse
</div>
@endsection
