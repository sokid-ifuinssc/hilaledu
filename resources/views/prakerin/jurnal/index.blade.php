@extends('layouts.app')

@section('title', 'Jurnal Kegiatan Prakerin - HilalEdu')

@section('content')
<div class="content-area space-y-6">
    <!-- Header Banner -->
    <x-dashboard-hero 
        icon="bi-journal-check" 
        badge="Aktivitas Harian PKL"
        title="Jurnal Harian Siswa Prakerin"
        description="Pantau dan verifikasi setiap laporan pekerjaan dan kompetensi yang dipelajari siswa di industri mitra." />

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 12px;">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 12px;">
            <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Card Table -->
    <div class="card shadow-sm border-0" style="border-radius: 16px; background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px);">
        <div class="card-header bg-transparent border-0 d-flex flex-wrap justify-content-between align-items-center gap-3 p-4">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 text-white font-weight-bold">
                    <i class="bi bi-journal-text text-warning me-2"></i>Catatan Kegiatan Siswa
                </h5>
                <span class="badge bg-secondary rounded-pill">{{ $jurnals->total() }} Laporan</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-warning rounded-pill px-3 font-weight-bold" data-bs-toggle="modal" data-bs-target="#modalTambahJurnal">
                    <i class="bi bi-plus-lg me-1"></i> Catat Jurnal Harian
                </button>
            </div>
        </div>

        <div class="card-body p-4 pt-0">
            <!-- Filter -->
            <form method="GET" action="{{ route('prakerin.jurnal.index') }}" class="mb-4">
                <div class="row g-2">
                    @if(auth()->user()->role !== 'siswa')
                    <div class="col-md-3">
                        <select name="siswa_id" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Semua Siswa --</option>
                            @foreach($siswas as $s)
                                <option value="{{ $s->id }}" {{ request('siswa_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-3">
                        <select name="industri_id" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Semua Industri Mitra --</option>
                            @foreach($industris as $ind)
                                <option value="{{ $ind->id }}" {{ request('industri_id') == $ind->id ? 'selected' : '' }}>{{ $ind->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Status --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="tanggal" class="form-control bg-dark text-white border-secondary" value="{{ request('tanggal') }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-warning w-100"><i class="bi bi-funnel"></i></button>
                        @if(request()->anyFilled(['siswa_id', 'industri_id', 'status', 'tanggal']))
                            <a href="{{ route('prakerin.jurnal.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-dark align-middle border-secondary" style="border-radius: 12px; overflow: hidden;">
                    <thead class="bg-black text-secondary uppercase text-xs">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Tanggal</th>
                            <th>Nama Siswa</th>
                            <th>Industri Mitra</th>
                            <th>Uraian Kegiatan / Pekerjaan</th>
                            <th class="text-center">Status</th>
                            <th width="160" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jurnals as $index => $item)
                        <tr>
                            <td class="text-center font-monospace">{{ $jurnals->firstItem() + $index }}</td>
                            <td>
                                <span class="badge bg-dark border border-secondary text-light px-2 py-1">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $item->tanggal ? $item->tanggal->format('d/m/Y') : '-' }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-white">{{ $item->siswa->name ?? '-' }}</div>
                                <small class="text-muted">{{ $item->siswa->email ?? '' }}</small>
                            </td>
                            <td>
                                <span class="text-warning font-weight-bold">
                                    <i class="bi bi-building me-1"></i>{{ $item->industri->nama ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <div class="text-slate-300" style="max-width: 320px; white-space: normal;">
                                    {{ $item->kegiatan }}
                                </div>
                            </td>
                            <td class="text-center">
                                @if($item->status === 'approved')
                                    <span class="badge bg-success bg-opacity-25 text-success border border-success rounded-pill px-3 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i> Disetujui
                                    </span>
                                @elseif($item->status === 'rejected')
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger rounded-pill px-3 py-1">
                                        <i class="bi bi-x-circle-fill me-1"></i> Ditolak
                                    </span>
                                @else
                                    <span class="badge bg-warning bg-opacity-25 text-warning border border-warning rounded-pill px-3 py-1">
                                        <i class="bi bi-clock-history me-1"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->role === 'admin' || auth()->user()->role === 'guru')
                                        @if($item->status !== 'approved')
                                        <form action="{{ route('prakerin.jurnal.approve', $item) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Setujui Jurnal">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @if($item->status !== 'rejected')
                                        <form action="{{ route('prakerin.jurnal.reject', $item) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning" title="Tolak Jurnal">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                        @endif
                                    @endif

                                    <form action="{{ route('prakerin.jurnal.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus jurnal kegiatan ini?')" class="d-inline">
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
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x text-secondary fs-1 d-block mb-2"></i>
                                Belum ada catatan jurnal prakerin yang sesuai filter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $jurnals->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Jurnal -->
<div class="modal fade" id="modalTambahJurnal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-slate-900 text-white border border-secondary" style="background: #1e293b;">
            <form action="{{ route('prakerin.jurnal.store') }}" method="POST">
                @csrf
                <div class="modal-header border-secondary">
                    <h5 class="modal-title font-weight-bold"><i class="bi bi-journal-plus text-warning me-2"></i>Catat Jurnal Kegiatan Prakerin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body space-y-3">
                    @if(auth()->user()->role !== 'siswa')
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Nama Siswa <span class="text-danger">*</span></label>
                        <select name="siswa_id" class="form-select bg-dark text-white border-secondary" required>
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($siswas as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label text-secondary small">Mitra Industri Tempat PKL <span class="text-danger">*</span></label>
                        <select name="industri_id" class="form-select bg-dark text-white border-secondary" required>
                            <option value="">-- Pilih Industri --</option>
                            @foreach($industris as $ind)
                                <option value="{{ $ind->id }}">{{ $ind->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small">Tanggal Kegiatan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control bg-dark text-white border-secondary" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small">Uraian Tugas / Aktivitas Harian <span class="text-danger">*</span></label>
                        <textarea name="kegiatan" rows="4" class="form-control bg-dark text-white border-secondary" placeholder="Tuliskan pekerjaan teknis atau pembelajaran yang dilakukan hari ini..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 font-weight-bold">Simpan Jurnal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
