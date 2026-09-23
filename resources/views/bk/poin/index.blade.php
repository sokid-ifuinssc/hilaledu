@extends('layouts.app')

@section('title', 'Akumulasi Poin Pelanggaran Siswa - HilalEdu')

@section('content')
<div class="content-area space-y-6">
    <!-- Header Banner -->
    <x-dashboard-hero 
        icon="bi-award" 
        badge="Kedisiplinan & Tata Tertib"
        title="Akumulasi Poin Kedisiplinan Siswa"
        description="Pantau skor kedisiplinan siswa SMK Plus Al Hilal. Siswa dengan poin di bawah ambang batas memerlukan bimbingan khusus dari BK dan Wali Kelas." />

    <!-- Card Data -->
    <div class="card shadow-sm border-0" style="border-radius: 16px; background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px);">
        <div class="card-header bg-transparent border-0 d-flex flex-wrap justify-content-between align-items-center gap-3 p-4">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 text-white font-weight-bold">
                    <i class="bi bi-shield-exclamation text-warning me-2"></i>Daftar Poin Siswa
                </h5>
                <span class="badge bg-secondary rounded-pill">{{ $siswas->total() }} Siswa</span>
            </div>
            <div>
                <a href="{{ route('bk.pelanggaran.create') }}" class="btn btn-danger rounded-pill px-3 font-weight-bold">
                    <i class="bi bi-plus-circle me-1"></i> Catat Kasus Pelanggaran
                </a>
            </div>
        </div>

        <div class="card-body p-4 pt-0">
            <!-- Filter -->
            <form method="GET" action="{{ route('bk.poin.index') }}" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control bg-dark text-white border-secondary" placeholder="Cari nama lengkap atau NIS siswa..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="kelas_id" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($kelasList as $k)
                                <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama }} ({{ $k->jurusan->singkatan ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-warning w-100 font-weight-bold"><i class="bi bi-funnel"></i> Filter</button>
                        @if(request()->anyFilled(['search', 'kelas_id']))
                            <a href="{{ route('bk.poin.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-dark align-middle border-secondary" style="border-radius: 12px; overflow: hidden;">
                    <thead class="bg-black text-secondary uppercase text-xs">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>NIS</th>
                            <th>Nama Lengkap Siswa</th>
                            <th>Kelas & Jurusan</th>
                            <th class="text-center">Sisa Poin</th>
                            <th class="text-center">Status Kedisiplinan</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswas as $index => $item)
                        <tr>
                            <td class="text-center font-monospace">{{ $siswas->firstItem() + $index }}</td>
                            <td><span class="font-monospace text-slate-300">{{ $item->nis ?: '-' }}</span></td>
                            <td>
                                <div class="fw-bold text-white fs-6">{{ $item->nama_lengkap }}</div>
                                <small class="text-muted">{{ $item->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-dark border border-secondary text-info px-2 py-1">
                                    {{ $item->kelas?->nama ?? '-' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php
                                    $poin = $item->poin ?? 100;
                                    $badgeClass = $poin >= 80 ? 'bg-success' : ($poin >= 50 ? 'bg-warning text-dark' : 'bg-danger');
                                @endphp
                                <span class="badge {{ $badgeClass }} px-3 py-2 rounded-pill fw-bold fs-6">
                                    {{ $poin }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if(($item->poin ?? 100) >= 80)
                                    <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Sangat Baik</span>
                                @elseif(($item->poin ?? 100) >= 50)
                                    <span class="text-warning"><i class="bi bi-exclamation-triangle-fill me-1"></i> Perlu Perhatian</span>
                                @else
                                    <span class="text-danger fw-bold"><i class="bi bi-x-octagon-fill me-1"></i> Panggilan Ortu</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('bk.pelanggaran.create', ['siswa_id' => $item->id]) }}" class="btn btn-sm btn-outline-danger" title="Catat Pelanggaran">
                                    <i class="bi bi-pencil-plus me-1"></i> Tindak
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Tidak ada data siswa yang ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $siswas->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
