@extends('layouts.app')

@section('title', 'Penempatan Siswa Prakerin')
@section('page-title', 'Penempatan Siswa Prakerin')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-white mb-1">
                <i class="bi bi-person-workspace text-warning me-2"></i>Penempatan Siswa PKL / Prakerin
            </h4>
            <p class="text-white-50 small mb-0">Alokasi siswa ke mitra DU/DI, guru pembimbing internal sekolah, dan instruktur industri.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('prakerin.penempatan.create') }}" class="btn btn-success">
                <i class="bi bi-person-plus me-1"></i> Tambah Penempatan Siswa
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
        <div class="card-body p-4">
            <!-- Filter Bar -->
            <form action="{{ route('prakerin.penempatan.index') }}" method="GET" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label text-white-50 small mb-1">Periode Prakerin</label>
                        <select name="periode_id" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Semua Periode --</option>
                            @foreach($periodeList as $p)
                                <option value="{{ $p->id }}" {{ $periodeId == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama }} {{ $p->aktif ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50 small mb-1">Mitra DU/DI</label>
                        <select name="dudi_id" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Semua DU/DI --</option>
                            @foreach($dudiList as $d)
                                <option value="{{ $d->id }}" {{ request('dudi_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-white-50 small mb-1">Status</label>
                        <select name="status" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Semua Status --</option>
                            <option value="belum_mulai" {{ request('status') == 'belum_mulai' ? 'selected' : '' }}>Belum Mulai</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-white-50 small mb-1">Pencarian Siswa</label>
                        <input type="text" name="search" class="form-control bg-dark text-white border-secondary" placeholder="Nama / NIS..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter"></i> Filter</button>
                        @if(request('periode_id') || request('dudi_id') || request('status') || request('search'))
                            <a href="{{ route('prakerin.penempatan.index') }}" class="btn btn-outline-light"><i class="bi bi-arrow-counterclockwise"></i></a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-dark align-middle mb-0" style="border-radius: 12px; overflow: hidden;">
                    <thead class="table-dark text-secondary small text-uppercase">
                        <tr>
                            <th width="5%">No</th>
                            <th>Siswa</th>
                            <th>Kelas / Jurusan</th>
                            <th>Mitra DU/DI</th>
                            <th>Guru Pembimbing</th>
                            <th>Instruktur DU/DI</th>
                            <th>Rentang Waktu</th>
                            <th class="text-center">Status</th>
                            <th width="12%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penempatan as $index => $item)
                            <tr>
                                <td>{{ $penempatan->firstItem() + $index }}</td>
                                <td class="fw-semibold text-white">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success bg-opacity-25 rounded-circle p-2 me-2 text-success">
                                            <i class="bi bi-mortarboard"></i>
                                        </div>
                                        <div>
                                            {{ $item->siswa->nama_lengkap ?? '-' }}
                                            <div class="text-white-50 small">NIS: {{ $item->siswa->nis ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $item->siswa?->kelas?->nama_lengkap ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="fw-medium text-info">{{ $item->dudi->nama ?? '-' }}</div>
                                    <div class="text-white-50 small">{{ $item->dudi->bidang_usaha ?? '' }}</div>
                                </td>
                                <td class="text-white-50 small">
                                    <i class="bi bi-person text-warning me-1"></i>{{ $item->guru?->nama_lengkap ?? '-' }}
                                </td>
                                <td class="text-white-50 small">
                                    <i class="bi bi-person-badge text-info me-1"></i>{{ $item->pembimbingDudi->nama ?? '-' }}
                                </td>
                                <td class="small text-white-50">
                                    {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-' }} s.d.
                                    {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/Y') : '-' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $item->status_color }} bg-opacity-25 text-{{ $item->status_color }} border border-{{ $item->status_color }} border-opacity-25">
                                        {{ $item->status_label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('prakerin.penempatan.show', $item->id) }}" class="btn btn-info" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('prakerin.penempatan.edit', $item->id) }}" class="btn btn-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('prakerin.penempatan.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus penempatan siswa ini?')" class="d-inline">
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
                                    Belum ada penempatan siswa pada kriteria filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $penempatan->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
