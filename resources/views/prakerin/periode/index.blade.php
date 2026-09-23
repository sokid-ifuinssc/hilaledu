@extends('layouts.app')

@section('title', 'Periode Prakerin')
@section('page-title', 'Periode Pelaksanaan Prakerin')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-white mb-1">
                <i class="bi bi-calendar-range text-warning me-2"></i>Periode & Gelombang Prakerin
            </h4>
            <p class="text-white-50 small mb-0">Tentukan rentang tanggal pelaksanaan PKL / Prakerin per tahun ajaran.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('prakerin.periode.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> Tambah Periode Prakerin
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
            <div class="table-responsive">
                <table class="table table-hover table-dark align-middle mb-0" style="border-radius: 12px; overflow: hidden;">
                    <thead class="table-dark text-secondary small text-uppercase">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Gelombang / Periode</th>
                            <th>Tahun Ajaran</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Durasi</th>
                            <th class="text-center">Jumlah Siswa</th>
                            <th class="text-center">Status</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periode as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold text-white">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-info bg-opacity-25 rounded-circle p-2 me-2 text-info">
                                            <i class="bi bi-calendar2-check"></i>
                                        </div>
                                        <div>
                                            {{ $item->nama }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $item->tahunAjaran->nama ?? '-' }}</span>
                                </td>
                                <td class="text-white-50">{{ $item->tanggal_mulai ? \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') : '-' }}</td>
                                <td class="text-white-50">{{ $item->tanggal_selesai ? \Carbon\Carbon::parse($item->tanggal_selesai)->format('d M Y') : '-' }}</td>
                                <td>
                                    @if($item->tanggal_mulai && $item->tanggal_selesai)
                                        <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25">
                                            {{ \Carbon\Carbon::parse($item->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($item->tanggal_selesai)) }} Hari
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $item->penempatans_count }} Siswa</span>
                                </td>
                                <td class="text-center">
                                    @if($item->aktif)
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25">
                                            <i class="bi bi-check-circle me-1"></i> Sedang Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Non-Aktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('prakerin.penempatan.index', ['periode_id' => $item->id]) }}" class="btn btn-info" title="Lihat Penempatan">
                                            <i class="bi bi-people"></i>
                                        </a>
                                        <a href="{{ route('prakerin.periode.edit', $item->id) }}" class="btn btn-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('prakerin.periode.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus periode ini? Data penempatan terkait juga akan terhapus.')" class="d-inline">
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
                                    <i class="bi bi-calendar-x fs-2 d-block mb-2 text-secondary"></i>
                                    Belum ada periode prakerin. Silahkan tambahkan periode baru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
