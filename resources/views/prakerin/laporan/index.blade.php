@extends('layouts.app')

@section('title', 'Laporan Prakerin')
@section('page-title', 'Laporan & Rekapitulasi Kegiatan Prakerin')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-white mb-1">
                <i class="bi bi-file-earmark-bar-graph text-warning me-2"></i>Laporan & Rekapitulasi Kegiatan Prakerin
            </h4>
            <p class="text-white-50 small mb-0">Statistik penempatan, progres pelaksanaan, dan rekapitulasi data siswa prakerin SMK Plus Al Hilal.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('prakerin.laporan.print', request()->query()) }}" target="_blank" class="btn btn-outline-light">
                <i class="bi bi-printer me-1"></i> Cetak Laporan
            </a>
            <a href="{{ route('prakerin.laporan.export', request()->query()) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Data (CSV)
            </a>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center p-3" style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.2), rgba(10, 88, 202, 0.1)); border: 1px solid rgba(13, 110, 253, 0.3) !important; border-radius: 14px;">
                <div class="fs-2 fw-bold text-primary">{{ $totalSiswa }}</div>
                <div class="text-white-50 small"><i class="bi bi-people me-1"></i>Total Penempatan</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center p-3" style="background: linear-gradient(135deg, rgba(25, 135, 84, 0.2), rgba(20, 108, 67, 0.1)); border: 1px solid rgba(25, 135, 84, 0.3) !important; border-radius: 14px;">
                <div class="fs-2 fw-bold text-success">{{ $totalAktif }}</div>
                <div class="text-white-50 small"><i class="bi bi-play-circle me-1"></i>Sedang Aktif</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center p-3" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.2), rgba(224, 168, 0, 0.1)); border: 1px solid rgba(255, 193, 7, 0.3) !important; border-radius: 14px;">
                <div class="fs-2 fw-bold text-warning">{{ $totalBelum }}</div>
                <div class="text-white-50 small"><i class="bi bi-clock me-1"></i>Belum Mulai</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center p-3" style="background: linear-gradient(135deg, rgba(108, 117, 125, 0.2), rgba(73, 80, 87, 0.1)); border: 1px solid rgba(108, 117, 125, 0.3) !important; border-radius: 14px;">
                <div class="fs-2 fw-bold text-secondary">{{ $totalSelesai }}</div>
                <div class="text-white-50 small"><i class="bi bi-check-circle me-1"></i>Selesai</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center p-3" style="background: linear-gradient(135deg, rgba(13, 202, 240, 0.2), rgba(11, 172, 204, 0.1)); border: 1px solid rgba(13, 202, 240, 0.3) !important; border-radius: 14px;">
                <div class="fs-2 fw-bold text-info">{{ $totalDudi }}</div>
                <div class="text-white-50 small"><i class="bi bi-buildings me-1"></i>Mitra DU/DI</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm text-center p-3" style="background: linear-gradient(135deg, rgba(214, 51, 132, 0.2), rgba(180, 40, 110, 0.1)); border: 1px solid rgba(214, 51, 132, 0.3) !important; border-radius: 14px;">
                <div class="fs-2 fw-bold text-danger">{{ $totalPembimbing }}</div>
                <div class="text-white-50 small"><i class="bi bi-person-badge me-1"></i>Instruktur DU/DI</div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
        <div class="card-body p-4">
            <form action="{{ route('prakerin.laporan.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label text-white-50 small mb-1">Periode Prakerin</label>
                        <select name="periode_id" class="form-select bg-dark text-white border-secondary">
                            <option value="">Semua Periode</option>
                            @foreach($periodeList as $p)
                                <option value="{{ $p->id }}" {{ $periodeId == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50 small mb-1">Mitra DU/DI</label>
                        <select name="dudi_id" class="form-select bg-dark text-white border-secondary">
                            <option value="">Semua DU/DI</option>
                            @foreach($dudiList as $d)
                                <option value="{{ $d->id }}" {{ $dudiId == $d->id ? 'selected' : '' }}>{{ $d->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-white-50 small mb-1">Guru Pembimbing</label>
                        <select name="guru_id" class="form-select bg-dark text-white border-secondary">
                            <option value="">Semua Guru Pembimbing</option>
                            @foreach($guruList as $g)
                                <option value="{{ $g->id }}" {{ $guruId == $g->id ? 'selected' : '' }}>{{ $g->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-white-50 small mb-1">Status</label>
                        <select name="status" class="form-select bg-dark text-white border-secondary">
                            <option value="">Semua Status</option>
                            <option value="belum_mulai" {{ $status == 'belum_mulai' ? 'selected' : '' }}>Belum Mulai</option>
                            <option value="aktif" {{ $status == 'aktif' ? 'selected' : '' }}>Sedang Aktif</option>
                            <option value="selesai" {{ $status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Penempatan Table -->
    <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
        <div class="card-header bg-transparent border-secondary p-4 pb-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-white mb-1"><i class="bi bi-table text-info me-2"></i>Tabel Rekapitulasi Siswa Prakerin</h5>
                <p class="text-white-50 small mb-0">Total {{ $penempatan->total() }} record penempatan ditemukan.</p>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover table-dark align-middle mb-0" style="border-radius: 12px; overflow: hidden;">
                    <thead class="table-dark text-secondary small text-uppercase">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Siswa & NIS</th>
                            <th>Kelas & Jurusan</th>
                            <th>Mitra DU/DI</th>
                            <th>Guru Pembimbing</th>
                            <th>Instruktur DU/DI</th>
                            <th>Waktu Pelaksanaan</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penempatan as $index => $item)
                            <tr>
                                <td>{{ $penempatan->firstItem() + $index }}</td>
                                <td class="fw-semibold text-white">
                                    {{ $item->siswa->nama_lengkap ?? '-' }}
                                    <div class="text-white-50 small">NIS: {{ $item->siswa->nis ?? '-' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $item->siswa?->kelas?->nama_lengkap ?? '-' }}</span>
                                    <div class="text-white-50 small">{{ $item->siswa?->kelas?->jurusan?->nama ?? '' }}</div>
                                </td>
                                <td>
                                    <span class="text-info fw-medium">{{ $item->dudi->nama ?? '-' }}</span>
                                </td>
                                <td class="text-white-50 small">{{ $item->guru?->nama_lengkap ?? '-' }}</td>
                                <td class="text-white-50 small">{{ $item->pembimbingDudi->nama ?? '-' }}</td>
                                <td class="text-white-50 small">
                                    {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-' }} s.d.
                                    {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/Y') : '-' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $item->status_color }} bg-opacity-25 text-{{ $item->status_color }} border border-{{ $item->status_color }} border-opacity-25">
                                        {{ $item->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-white-50">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                    Tidak ada data penempatan prakerin.
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
