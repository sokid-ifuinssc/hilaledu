@extends('layouts.app')

@section('title', 'Detail Penempatan Siswa')
@section('page-title', 'Detail Penempatan Siswa')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-white mb-1">
                <i class="bi bi-person-workspace text-warning me-2"></i>Penempatan: {{ $penempatan->siswa->nama_lengkap ?? '-' }}
            </h4>
            <p class="text-white-50 small mb-0">{{ $penempatan->dudi->nama ?? '-' }} &bull; Periode: {{ $penempatan->periodePrakerin->nama ?? '-' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('prakerin.penempatan.edit', $penempatan->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil-square me-1"></i> Edit
            </a>
            <a href="{{ route('prakerin.penempatan.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Informasi Siswa -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-header bg-transparent border-secondary p-4 pb-0">
                    <h5 class="fw-bold text-white mb-1"><i class="bi bi-mortarboard text-success me-2"></i>Informasi Siswa</h5>
                </div>
                <div class="card-body p-4">
                    <table class="table table-dark table-borderless small mb-0">
                        <tr>
                            <td class="text-white-50" width="35%">Nama Lengkap</td>
                            <td class="text-white fw-bold">{{ $penempatan->siswa->nama_lengkap ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-white-50">NIS / NISN</td>
                            <td class="text-white">{{ $penempatan->siswa->nis ?? '-' }} / {{ $penempatan->siswa->nisn ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-white-50">Kelas</td>
                            <td class="text-white">{{ $penempatan->siswa?->kelas?->nama_lengkap ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-white-50">Jurusan / Kompetensi</td>
                            <td class="text-white">{{ $penempatan->siswa?->kelas?->jurusan?->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-white-50">Kontak / HP</td>
                            <td class="text-white">{{ $penempatan->siswa->no_hp ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Informasi DU/DI & Pembimbing -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-header bg-transparent border-secondary p-4 pb-0">
                    <h5 class="fw-bold text-white mb-1"><i class="bi bi-building text-info me-2"></i>DU/DI & Pembimbing</h5>
                </div>
                <div class="card-body p-4">
                    <table class="table table-dark table-borderless small mb-0">
                        <tr>
                            <td class="text-white-50" width="35%">Nama DU/DI</td>
                            <td class="text-info fw-bold">{{ $penempatan->dudi->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-white-50">Bidang Usaha</td>
                            <td class="text-white">{{ $penempatan->dudi->bidang_usaha ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-white-50">Alamat DU/DI</td>
                            <td class="text-white">{{ $penempatan->dudi->alamat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-white-50">Instruktur DU/DI</td>
                            <td class="text-white">{{ $penempatan->pembimbingDudi->nama ?? '-' }} ({{ $penempatan->pembimbingDudi->no_hp ?? '-' }})</td>
                        </tr>
                        <tr>
                            <td class="text-white-50">Guru Pembimbing</td>
                            <td class="text-white">{{ $penempatan->guru?->nama_lengkap ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Detail Jadwal & Catatan -->
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <span class="text-white-50 small d-block">Status Pelaksanaan:</span>
                            <span class="badge bg-{{ $penempatan->status_color }} fs-6 px-3 py-2 mt-1">{{ $penempatan->status_label }}</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-white-50 small d-block">Durasi Pelaksanaan:</span>
                            <span class="text-white fw-bold d-block mt-1">
                                {{ $penempatan->tanggal_mulai ? $penempatan->tanggal_mulai->format('d F Y') : '-' }} &mdash;
                                {{ $penempatan->tanggal_selesai ? $penempatan->tanggal_selesai->format('d F Y') : '-' }}
                            </span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-white-50 small d-block">Catatan:</span>
                            <span class="text-white-50 fst-italic mt-1 d-block">{{ $penempatan->keterangan ?? 'Tidak ada catatan khusus.' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
