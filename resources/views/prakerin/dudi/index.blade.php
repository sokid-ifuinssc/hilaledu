@extends('layouts.app')

@section('title', 'Data DU/DI Mitra')
@section('page-title', 'Data Mitra DU/DI Prakerin')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-buildings text-warning fs-3"></i> Data Mitra DU/DI (Dunia Usaha &amp; Industri)
            </h4>
            <p class="text-muted small mb-0">Kelola master mitra industri tempat pelaksanaan Praktik Kerja Lapangan siswa SMK Plus Al Hilal.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('prakerin.kerjasama.index') }}" class="btn btn-outline-warning text-dark bg-white d-inline-flex align-items-center gap-1 shadow-xs border">
                <i class="bi bi-handshake text-warning"></i> Menu Kerjasama &amp; MoU
            </a>
            <a href="{{ route('prakerin.dudi.create') }}" class="btn btn-success d-inline-flex align-items-center gap-1 text-white fw-semibold shadow-sm">
                <i class="bi bi-plus-circle"></i> Tambah Mitra DU/DI
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="background: #dcfce7; color: #15803d; border-radius: 12px;">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border border-light-subtle bg-white shadow-xs" style="border-radius: 16px;">
        <div class="card-body p-4">
            <form action="{{ route('prakerin.dudi.index') }}" method="GET" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-secondary-subtle text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control bg-white text-dark border-secondary-subtle" placeholder="Cari nama DU/DI, bidang usaha, atau alamat..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 fw-semibold"><i class="bi bi-filter me-1"></i> Filter</button>
                        @if(request('search'))
                            <a href="{{ route('prakerin.dudi.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="border-radius: 12px; overflow: hidden;">
                    <thead class="bg-light text-secondary small text-uppercase" style="border-bottom: 2px solid #e2e8f0; font-weight: 700;">
                        <tr>
                            <th width="5%" class="text-dark">No</th>
                            <th class="text-dark">Nama DU/DI</th>
                            <th class="text-dark">Bidang Usaha</th>
                            <th class="text-dark">Alamat</th>
                            <th class="text-dark">Kontak</th>
                            <th class="text-center text-dark">Pembimbing</th>
                            <th class="text-center text-dark">Penempatan</th>
                            <th class="text-center text-dark">Status</th>
                            <th width="12%" class="text-center text-dark">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dudi as $index => $item)
                            <tr>
                                <td class="text-muted fw-semibold">{{ $dudi->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle p-2 me-2 text-primary d-flex align-items-center justify-content-center shrink-0" style="background: #eff6ff; width: 36px; height: 36px;">
                                            <i class="bi bi-building fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 0.95rem;">{{ $item->nama }}</div>
                                            @if($item->latestKerjasama)
                                                <div class="mt-1 d-flex align-items-center gap-1 flex-wrap">
                                                    <a href="{{ route('prakerin.kerjasama.show', $item->latestKerjasama->id) }}" class="badge text-decoration-none bg-success-subtle text-success border border-success-subtle" style="font-size: 0.72rem; font-weight: 600;">
                                                        <i class="bi bi-handshake me-1"></i>MoU: {{ $item->latestKerjasama->tahun_mulai }}-{{ $item->latestKerjasama->tahun_berakhir }}
                                                    </a>
                                                    @if($item->latestKerjasama->hasLocalFile())
                                                        <a href="{{ route('prakerin.kerjasama.download', $item->latestKerjasama->id) }}" class="text-primary small" title="Unduh Berkas MoU" style="font-size: 0.75rem;">
                                                            <i class="bi bi-file-earmark-pdf"></i>
                                                        </a>
                                                    @endif
                                                    @if($item->latestKerjasama->link_drive)
                                                        <a href="{{ $item->latestKerjasama->link_drive }}" target="_blank" rel="noopener noreferrer" class="text-warning small" title="Buka Google Drive" style="font-size: 0.75rem;">
                                                            <i class="bi bi-google"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="mt-1">
                                                    <a href="{{ route('prakerin.kerjasama.create') }}" class="text-muted text-decoration-none small" style="font-size: 0.72rem;">
                                                        <i class="bi bi-plus-circle text-primary me-1"></i>Input MoU Kerjasama
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border border-primary-subtle" style="font-weight: 600;">
                                        {{ $item->bidang_usaha ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-dark small">{{ Str::limit($item->alamat, 45) ?? '-' }}</td>
                                <td>
                                    <div class="small text-muted">
                                        @if($item->no_telp) <div class="text-dark"><i class="bi bi-telephone text-primary me-1"></i>{{ $item->no_telp }}</div> @endif
                                        @if($item->email) <div><i class="bi bi-envelope text-info me-1"></i>{{ $item->email }}</div> @endif
                                        @if(!$item->no_telp && !$item->email) - @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-dark border">{{ $item->pembimbing_dudi_count }} Orang</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary text-white">{{ $item->penempatans_count }} Siswa</span>
                                </td>
                                <td class="text-center">
                                    @if($item->status)
                                        <span class="badge bg-success text-white" style="font-weight: 600;">
                                            <i class="bi bi-check-circle me-1"></i> Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-danger text-white" style="font-weight: 600;">
                                            <i class="bi bi-x-circle me-1"></i> Non-Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('prakerin.dudi.edit', $item->id) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('prakerin.dudi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data mitra DU/DI ini?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="rounded-circle p-3 mb-3 bg-light border d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                                            <i class="bi bi-buildings text-warning fs-1"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1">Belum Ada Data Mitra DU/DI</h5>
                                        <p class="small text-muted mb-3" style="max-width: 440px;">
                                            Silahkan tambahkan data mitra baru atau inputkan kerjasama MoU industri.
                                        </p>
                                        <a href="{{ route('prakerin.dudi.create') }}" class="btn btn-success px-4 fw-semibold shadow-sm">
                                            <i class="bi bi-plus-circle me-1"></i> Tambah Mitra Sekarang
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $dudi->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
