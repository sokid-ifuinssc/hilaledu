@extends('layouts.app')

@section('title', 'Detail Pembimbing DU/DI')
@section('page-title', 'Detail Pembimbing DU/DI')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-white mb-1">
                <i class="bi bi-person-badge text-warning me-2"></i>Detail Pembimbing: {{ $pembimbingDudi->nama }}
            </h4>
            <p class="text-white-50 small mb-0">{{ $pembimbingDudi->jabatan ?? 'Pembimbing Lapangan' }} - {{ $pembimbingDudi->dudi->nama ?? '-' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('prakerin.pembimbing-dudi.edit', $pembimbingDudi->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil-square me-1"></i> Edit Data
            </a>
            <a href="{{ route('prakerin.pembimbing-dudi.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-body p-4 text-center">
                    <div class="bg-warning bg-opacity-25 rounded-circle d-inline-flex p-4 text-warning mb-3">
                        <i class="bi bi-person-workspace fs-1"></i>
                    </div>
                    <h5 class="text-white fw-bold mb-1">{{ $pembimbingDudi->nama }}</h5>
                    <p class="text-white-50 small mb-3">{{ $pembimbingDudi->jabatan ?? '-' }}</p>
                    <hr class="border-secondary">
                    <div class="text-start small">
                        <div class="mb-2 text-white-50">Perusahaan: <strong class="text-white d-block">{{ $pembimbingDudi->dudi->nama ?? '-' }}</strong></div>
                        <div class="mb-2 text-white-50">Kontak WhatsApp: <strong class="text-white d-block">{{ $pembimbingDudi->no_hp ?? '-' }}</strong></div>
                        <div class="mb-2 text-white-50">Email: <strong class="text-white d-block">{{ $pembimbingDudi->email ?? '-' }}</strong></div>
                        <div class="mb-2 text-white-50">Status:
                            @if($pembimbingDudi->status)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Non-Aktif</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-header bg-transparent border-secondary p-4 pb-0">
                    <h5 class="fw-bold text-white mb-1"><i class="bi bi-people text-info me-2"></i>Daftar Siswa yang Dibimbing</h5>
                    <p class="text-white-50 small">Riwayat siswa yang ditempatkan di bawah bimbingan instruktur ini.</p>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover table-dark align-middle mb-0">
                            <thead class="table-dark text-secondary small text-uppercase">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas / Jurusan</th>
                                    <th>Periode</th>
                                    <th>Guru Pembimbing</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pembimbingDudi->penempatans as $idx => $p)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td class="fw-semibold text-white">{{ $p->siswa->nama_lengkap ?? '-' }}</td>
                                        <td><span class="badge bg-secondary">{{ $p->siswa?->kelas?->nama_lengkap ?? '-' }}</span></td>
                                        <td class="text-white-50 small">{{ $p->periodePrakerin->nama ?? '-' }}</td>
                                        <td class="text-white-50 small">{{ $p->guru?->nama_lengkap ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $p->status_color }}">{{ $p->status_label }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-white-50">
                                            Belum ada siswa yang ditugaskan ke pembimbing ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
