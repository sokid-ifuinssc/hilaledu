@extends('layouts.app')

@section('title', 'Data Kerjasama Mitra DU/DI')
@section('page-title', 'Menu Kerjasama Mitra DU/DI')

@section('content')
<div class="container-fluid px-0">
    {{-- Header Banner & Actions --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold text-white mb-1">
                <i class="bi bi-handshake text-warning me-2"></i>Pendataan Kerjasama Mitra DU/DI
            </h4>
            <p class="text-white-50 small mb-0">
                Pencatatan MoU / Perjanjian Kerjasama dengan Dunia Usaha &amp; Industri. Mitra yang didaftarkan otomatis terhubung ke sistem penempatan Prakerin.
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('prakerin.dudi.index') }}" class="btn btn-outline-light d-inline-flex align-items-center gap-2">
                <i class="bi bi-buildings text-info"></i> Lihat Mitra Prakerin
            </a>
            <a href="{{ route('prakerin.kerjasama.create') }}" class="btn btn-success d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-plus-circle-fill"></i> Tambah Kerjasama Baru
            </a>
        </div>
    </div>

    {{-- Alert Flash Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="background: rgba(34, 197, 94, 0.2); backdrop-filter: blur(10px); color: #86efac; border-radius: 12px;">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="background: rgba(239, 68, 68, 0.2); backdrop-filter: blur(10px); color: #fca5a5; border-radius: 12px;">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>{{ session('error') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Metric Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 d-flex align-items-center justify-center text-primary" style="background: rgba(59, 130, 246, 0.18);">
                        <i class="bi bi-file-earmark-text-fill fs-3"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-black text-white lh-1">{{ $totalKerjasama }}</div>
                        <div class="small text-white-50 text-uppercase fw-semibold mt-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Kerjasama</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 d-flex align-items-center justify-center text-success" style="background: rgba(34, 197, 94, 0.18);">
                        <i class="bi bi-check-circle-fill fs-3"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-black text-white lh-1">{{ $totalAktif }}</div>
                        <div class="small text-white-50 text-uppercase fw-semibold mt-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Kerjasama Aktif</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 d-flex align-items-center justify-center text-danger" style="background: rgba(239, 68, 68, 0.18);">
                        <i class="bi bi-hourglass-bottom fs-3"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-black text-white lh-1">{{ $totalBerakhir }}</div>
                        <div class="small text-white-50 text-uppercase fw-semibold mt-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Kadaluarsa / Berakhir</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 d-flex align-items-center justify-center text-warning" style="background: rgba(245, 158, 11, 0.18);">
                        <i class="bi bi-buildings-fill fs-3"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-black text-white lh-1">{{ $totalMitraDudi }}</div>
                        <div class="small text-white-50 text-uppercase fw-semibold mt-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Mitra di Prakerin</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table Container --}}
    <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
        <div class="card-body p-4">
            {{-- Filter & Search Form --}}
            <form action="{{ route('prakerin.kerjasama.index') }}" method="GET" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-white-50"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control bg-dark text-white border-secondary" placeholder="Cari nama mitra, bidang, bentuk kerjasama, atau alamat..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="bentuk" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Semua Bentuk Kerjasama --</option>
                            @foreach($presetBentuk as $pb)
                                <option value="{{ $pb }}" {{ request('bentuk') == $pb ? 'selected' : '' }}>{{ $pb }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Status --</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="berakhir" {{ request('status') == 'berakhir' ? 'selected' : '' }}>Berakhir</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                        @if(request()->anyFilled(['search', 'bentuk', 'status', 'tahun']))
                            <a href="{{ route('prakerin.kerjasama.index') }}" class="btn btn-outline-light" title="Reset Filter"><i class="bi bi-x-circle"></i></a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover table-dark align-middle mb-0" style="border-radius: 12px; overflow: hidden;">
                    <thead class="table-dark text-secondary small text-uppercase" style="border-bottom: 2px solid rgba(255,255,255,0.1);">
                        <tr>
                            <th width="4%" class="text-center">No</th>
                            <th width="20%">Nama Mitra &amp; Bidang</th>
                            <th width="18%">Alamat</th>
                            <th width="20%">Bentuk Kerjasama</th>
                            <th width="12%" class="text-center">Periode Kerjasama</th>
                            <th width="12%" class="text-center">Berkas / Drive</th>
                            <th width="6%" class="text-center">Status</th>
                            <th width="8%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kerjasamas as $index => $item)
                            <tr>
                                <td class="text-center text-white-50 fw-semibold">{{ $kerjasamas->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="rounded-circle p-2 mt-1 text-primary d-flex align-items-center justify-content-center shrink-0" style="background: rgba(59, 130, 246, 0.15); width: 34px; height: 34px;">
                                            <i class="bi bi-buildings"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('prakerin.kerjasama.show', $item->id) }}" class="fw-bold text-white text-decoration-none hover-underline d-block">
                                                {{ $item->nama_mitra }}
                                            </a>
                                            <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                                @if($item->bidang_mitra)
                                                    <span class="badge" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3); font-size: 0.72rem;">
                                                        <i class="bi bi-tag me-1"></i>{{ $item->bidang_mitra }}
                                                    </span>
                                                @endif
                                                @if($item->nomor_mou)
                                                    <span class="text-white-50 small" style="font-size: 0.72rem;">
                                                        <i class="bi bi-file-earmark-text text-secondary me-1"></i>{{ $item->nomor_mou }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-white-50 small" style="line-height: 1.4;">
                                        @if($item->alamat)
                                            <i class="bi bi-geo-alt text-danger me-1"></i>{{ Str::limit($item->alamat, 75) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                    @if($item->pic_nama || $item->pic_kontak)
                                        <div class="mt-1 text-white-50 small" style="font-size: 0.75rem;">
                                            <i class="bi bi-person text-info me-1"></i>PIC: {{ $item->pic_nama ?? 'Kontak' }} 
                                            @if($item->pic_kontak)
                                                <span class="text-success ms-1">({{ $item->pic_kontak }})</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if(is_array($item->bentuk_kerjasama) && count($item->bentuk_kerjasama) > 0)
                                            @foreach($item->bentuk_kerjasama as $b)
                                                @php
                                                    $bgClass = 'rgba(99, 102, 241, 0.15)';
                                                    $color = '#a5b4fc';
                                                    $bLower = strtolower($b);
                                                    if (str_contains($bLower, 'prakerin') || str_contains($bLower, 'pkl')) {
                                                        $bgClass = 'rgba(16, 185, 129, 0.15)';
                                                        $color = '#6ee7b7';
                                                    } elseif (str_contains($bLower, 'kurikulum')) {
                                                        $bgClass = 'rgba(245, 158, 11, 0.15)';
                                                        $color = '#fcd34d';
                                                    } elseif (str_contains($bLower, 'payroll') || str_contains($bLower, 'gaji')) {
                                                        $bgClass = 'rgba(236, 72, 153, 0.15)';
                                                        $color = '#f472b6';
                                                    } elseif (str_contains($bLower, 'rekrutmen') || str_contains($bLower, 'salur')) {
                                                        $bgClass = 'rgba(14, 165, 233, 0.15)';
                                                        $color = '#7dd3fc';
                                                    }
                                                @endphp
                                                <span class="badge" style="background: {{ $bgClass }}; color: {{ $color }}; border: 1px solid rgba(255,255,255,0.08); font-size: 0.73rem; font-weight: 500;">
                                                    {{ $b }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </div>
                                    {{-- Terintegrasi Prakerin Tag --}}
                                    <div class="mt-2">
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #34d399; font-size: 0.68rem; border: 1px solid rgba(16, 185, 129, 0.25);">
                                            <i class="bi bi-check2-circle me-1"></i>Siap Jadi Lokasi Prakerin
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="fw-semibold text-white">
                                        {{ $item->tahun_mulai }} s/d {{ $item->tahun_berakhir }}
                                    </div>
                                    @php
                                        $currentYear = (int) date('Y');
                                        $endYear = (int) $item->tahun_berakhir;
                                        $sisaTahun = $endYear - $currentYear;
                                    @endphp
                                    <div class="small mt-1" style="font-size: 0.72rem;">
                                        @if($sisaTahun > 0)
                                            <span class="text-success"><i class="bi bi-clock me-1"></i>Sisa {{ $sisaTahun }} tahun</span>
                                        @elseif($sisaTahun == 0)
                                            <span class="text-warning"><i class="bi bi-exclamation-circle me-1"></i>Berakhir tahun ini</span>
                                        @else
                                            <span class="text-danger"><i class="bi bi-x-circle me-1"></i>Lewat {{ abs($sisaTahun) }} tahun</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column gap-1 align-items-center">
                                        {{-- File Dokumen Upload --}}
                                        @if($item->hasLocalFile())
                                            <a href="{{ route('prakerin.kerjasama.download', $item->id) }}" class="btn btn-sm btn-outline-info py-1 px-2 d-inline-flex align-items-center gap-1 w-100 justify-content-center" style="font-size: 0.74rem;" title="Unduh Berkas Dokumen MoU">
                                                <i class="bi bi-file-earmark-arrow-down-fill"></i> Berkas MoU
                                            </a>
                                        @elseif($item->file_kerjasama)
                                            <span class="badge bg-secondary text-white-50" style="font-size: 0.7rem;">
                                                <i class="bi bi-file-earmark-x me-1"></i>File Offline
                                            </span>
                                        @endif

                                        {{-- Link Google Drive --}}
                                        @if($item->link_drive)
                                            <a href="{{ $item->link_drive }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-warning py-1 px-2 d-inline-flex align-items-center gap-1 w-100 justify-content-center" style="font-size: 0.74rem;" title="Buka Dokumen di Google Drive">
                                                <i class="bi bi-google"></i> Google Drive
                                            </a>
                                        @endif

                                        @if(!$item->hasLocalFile() && !$item->link_drive)
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($item->isAktif())
                                        <span class="badge" style="background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.35); font-size: 0.74rem;">
                                            <i class="bi bi-check-circle me-1"></i>Aktif
                                        </span>
                                    @else
                                        <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35); font-size: 0.74rem;">
                                            <i class="bi bi-x-circle me-1"></i>Berakhir
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('prakerin.kerjasama.show', $item->id) }}" class="btn btn-info text-white" title="Lihat Detail Kerjasama">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('prakerin.kerjasama.edit', $item->id) }}" class="btn btn-warning" title="Edit Data Kerjasama">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('prakerin.kerjasama.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data kerjasama dengan mitra {{ addslashes($item->nama_mitra) }}?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Hapus Kerjasama">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-white-50">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="rounded-circle p-3 mb-2" style="background: rgba(255,255,255,0.05); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-inbox fs-2 text-secondary"></i>
                                        </div>
                                        <h6 class="fw-bold text-white mb-1">Belum Ada Data Kerjasama Mitra DU/DI</h6>
                                        <p class="small text-white-50 mb-3" style="max-width: 420px;">
                                            Inputkan perjanjian kerjasama (MoU) dengan mitra industri untuk menyelaraskan kurikulum, pelaksanaan prakerin, payroll, dan lainnya.
                                        </p>
                                        <a href="{{ route('prakerin.kerjasama.create') }}" class="btn btn-success btn-sm px-3">
                                            <i class="bi bi-plus-circle me-1"></i> Tambah Kerjasama Sekarang
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($kerjasamas->hasPages())
                <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="small text-white-50">
                        Menampilkan {{ $kerjasamas->firstItem() }} sampai {{ $kerjasamas->lastItem() }} dari total {{ $kerjasamas->total() }} kerjasama
                    </div>
                    <div>
                        {{ $kerjasamas->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
