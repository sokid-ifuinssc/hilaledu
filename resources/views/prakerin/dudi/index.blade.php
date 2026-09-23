@extends('layouts.app')

@section('title', 'Data DU/DI Mitra')
@section('page-title', 'Data Mitra DU/DI Prakerin')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-white mb-1">
                <i class="bi bi-buildings text-warning me-2"></i>Data Mitra DU/DI (Dunia Usaha & Industri)
            </h4>
            <p class="text-white-50 small mb-0">Kelola master mitra industri tempat pelaksanaan Praktik Kerja Lapangan siswa SMK Plus Al Hilal.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('prakerin.dudi.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> Tambah Mitra DU/DI
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
        <div class="card-body p-4">
            <form action="{{ route('prakerin.dudi.index') }}" method="GET" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-white-50"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control bg-dark text-white border-secondary" placeholder="Cari nama DU/DI, bidang usaha, atau alamat..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                        @if(request('search'))
                            <a href="{{ route('prakerin.dudi.index') }}" class="btn btn-outline-light"><i class="bi bi-x-circle"></i></a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-dark align-middle mb-0" style="border-radius: 12px; overflow: hidden;">
                    <thead class="table-dark text-secondary small text-uppercase">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama DU/DI</th>
                            <th>Bidang Usaha</th>
                            <th>Alamat</th>
                            <th>Kontak</th>
                            <th class="text-center">Pembimbing</th>
                            <th class="text-center">Penempatan</th>
                            <th class="text-center">Status</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dudi as $index => $item)
                            <tr>
                                <td>{{ $dudi->firstItem() + $index }}</td>
                                <td class="fw-semibold text-white">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-25 rounded-circle p-2 me-2 text-primary">
                                            <i class="bi bi-building"></i>
                                        </div>
                                        <div>
                                            {{ $item->nama }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                        {{ $item->bidang_usaha ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-white-50 small">{{ Str::limit($item->alamat, 45) ?? '-' }}</td>
                                <td>
                                    <div class="small text-white-50">
                                        @if($item->no_telp) <div><i class="bi bi-telephone text-warning me-1"></i>{{ $item->no_telp }}</div> @endif
                                        @if($item->email) <div><i class="bi bi-envelope text-info me-1"></i>{{ $item->email }}</div> @endif
                                        @if(!$item->no_telp && !$item->email) - @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $item->pembimbing_dudi_count }} Orang</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $item->penempatans_count }} Siswa</span>
                                </td>
                                <td class="text-center">
                                    @if($item->status)
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25">
                                            <i class="bi bi-check-circle me-1"></i> Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25">
                                            <i class="bi bi-x-circle me-1"></i> Non-Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('prakerin.dudi.edit', $item->id) }}" class="btn btn-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('prakerin.dudi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data mitra DU/DI ini?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-white-50">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                    Belum ada data mitra DU/DI. Silahkan tambahkan data baru.
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
