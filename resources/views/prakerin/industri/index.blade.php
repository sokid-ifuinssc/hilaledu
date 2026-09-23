@extends('layouts.app')

@section('title', 'Industri Mitra Prakerin - HilalEdu')

@section('content')
<div class="content-area space-y-6">
    <!-- Header Banner -->
    <x-dashboard-hero 
        icon="bi-building" 
        badge="Mitra Kerja Sama Industri"
        title="Daftar Industri Mitra Prakerin"
        description="Kelola seluruh Dunia Usaha & Dunia Industri (DUDI) tempat pelaksanaan Praktik Kerja Lapangan siswa SMK Plus Al Hilal." />

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 12px;">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Card Data Table -->
    <div class="card shadow-sm border-0" style="border-radius: 16px; background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px);">
        <div class="card-header bg-transparent border-0 d-flex flex-wrap justify-content-between align-items-center gap-3 p-4">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 text-white font-weight-bold">
                    <i class="bi bi-buildings text-warning me-2"></i>Daftar Perusahaan / Mitra DUDI
                </h5>
                <span class="badge bg-secondary rounded-pill">{{ $industris->total() }} Perusahaan</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-warning rounded-pill px-3 font-weight-bold" data-bs-toggle="modal" data-bs-target="#modalTambahIndustri">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Mitra Industri
                </button>
            </div>
        </div>

        <div class="card-body p-4 pt-0">
            <!-- Filter & Search -->
            <form method="GET" action="{{ route('prakerin.industri.index') }}" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control bg-dark text-white border-secondary" placeholder="Cari nama perusahaan, alamat, atau kontak..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-warning w-100">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                    </div>
                    @if(request('search'))
                    <div class="col-md-2">
                        <a href="{{ route('prakerin.industri.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-dark align-middle border-secondary" style="border-radius: 12px; overflow: hidden;">
                    <thead class="bg-black text-secondary uppercase text-xs">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Nama Perusahaan / Instansi</th>
                            <th>Alamat Lokasi</th>
                            <th>Kontak / PIC</th>
                            <th class="text-center">Aktivitas Jurnal</th>
                            <th width="140" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($industris as $index => $item)
                        <tr>
                            <td class="text-center font-monospace">{{ $industris->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-bold text-white fs-6">{{ $item->nama }}</div>
                                <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>Mitra sejak {{ $item->created_at->format('d M Y') }}</small>
                            </td>
                            <td>
                                <span class="text-slate-300">{{ $item->alamat ?: '-' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-dark border border-secondary text-info px-2 py-1">
                                    <i class="bi bi-telephone-fill me-1"></i>{{ $item->kontak ?: '-' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark font-weight-bold px-3 py-1 rounded-pill">
                                    {{ $item->jurnal_prakerins_count }} Jurnal
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $item->id }}" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form action="{{ route('prakerin.industri.destroy', $item) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data industri ini?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit Industri -->
                        <div class="modal fade" id="modalEdit{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content bg-slate-900 text-white border border-secondary" style="background: #1e293b;">
                                    <form action="{{ route('prakerin.industri.update', $item) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header border-secondary">
                                            <h5 class="modal-title font-weight-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Data Industri</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body space-y-3">
                                            <div class="mb-3">
                                                <label class="form-label text-secondary small">Nama Perusahaan / Instansi <span class="text-danger">*</span></label>
                                                <input type="text" name="nama" class="form-control bg-dark text-white border-secondary" value="{{ $item->nama }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-secondary small">Kontak / No. Telepon / PIC</label>
                                                <input type="text" name="kontak" class="form-control bg-dark text-white border-secondary" value="{{ $item->kontak }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-secondary small">Alamat Lengkap Perusahaan</label>
                                                <textarea name="alamat" rows="3" class="form-control bg-dark text-white border-secondary">{{ $item->alamat }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-secondary">
                                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-warning rounded-pill px-4">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-buildings text-secondary fs-1 d-block mb-2"></i>
                                Belum ada data industri mitra yang terdaftar.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $industris->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Industri -->
<div class="modal fade" id="modalTambahIndustri" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-slate-900 text-white border border-secondary" style="background: #1e293b;">
            <form action="{{ route('prakerin.industri.store') }}" method="POST">
                @csrf
                <div class="modal-header border-secondary">
                    <h5 class="modal-title font-weight-bold"><i class="bi bi-building-add text-warning me-2"></i>Tambah Mitra Industri Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body space-y-3">
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Nama Perusahaan / Instansi <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control bg-dark text-white border-secondary" placeholder="Contoh: PT. Astra Honda Motor" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Kontak / No. HP / PIC</label>
                        <input type="text" name="kontak" class="form-control bg-dark text-white border-secondary" placeholder="Contoh: 08123456789 (Bpk. Ahmad)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Alamat Lengkap Perusahaan</label>
                        <textarea name="alamat" rows="3" class="form-control bg-dark text-white border-secondary" placeholder="Jl. Raya Industri No. 12..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 font-weight-bold">Simpan Mitra</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
