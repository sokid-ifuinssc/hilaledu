@extends('layouts.app')

@section('title', 'Data Kerjasama Mitra DU/DI')
@section('page-title', 'Menu Kerjasama Mitra DU/DI')

@section('content')
<div class="container-fluid px-0">
    {{-- Header Banner & Actions --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-handshake text-success fs-3"></i> Pendataan Kerjasama Mitra DU/DI
            </h4>
            <p class="text-muted small mb-0">
                Pencatatan MoU &amp; Perjanjian Kerjasama industri. Mitra yang didaftarkan otomatis terhubung ke sistem penempatan Prakerin.
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('prakerin.dudi.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 shadow-xs bg-white text-dark">
                <i class="bi bi-buildings text-primary"></i> Lihat Mitra Prakerin
            </a>
            <a href="{{ route('prakerin.kerjasama.create') }}" class="btn btn-success d-inline-flex align-items-center gap-2 shadow-sm text-white fw-semibold">
                <i class="bi bi-plus-circle-fill"></i> Tambah Kerjasama Baru
            </a>
        </div>
    </div>

    {{-- Alert Flash Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="background: #dcfce7; color: #15803d; border-radius: 12px;">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="background: #fee2e2; color: #b91c1c; border-radius: 12px;">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Metric Stat Cards (High Contrast & Clean) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border border-light-subtle bg-white shadow-xs h-100" style="border-radius: 16px;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 d-flex align-items-center justify-content-center text-primary shrink-0" style="background: #eff6ff; width: 52px; height: 52px;">
                        <i class="bi bi-file-earmark-text-fill fs-3"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold text-dark lh-1">{{ $totalKerjasama }}</div>
                        <div class="small text-muted text-uppercase fw-bold mt-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Kerjasama</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border border-light-subtle bg-white shadow-xs h-100" style="border-radius: 16px;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 d-flex align-items-center justify-content-center text-success shrink-0" style="background: #dcfce7; width: 52px; height: 52px;">
                        <i class="bi bi-check-circle-fill fs-3"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold text-success lh-1">{{ $totalAktif }}</div>
                        <div class="small text-muted text-uppercase fw-bold mt-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Kerjasama Aktif</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border border-light-subtle bg-white shadow-xs h-100" style="border-radius: 16px;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 d-flex align-items-center justify-content-center text-danger shrink-0" style="background: #fee2e2; width: 52px; height: 52px;">
                        <i class="bi bi-hourglass-bottom fs-3"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold text-danger lh-1">{{ $totalBerakhir }}</div>
                        <div class="small text-muted text-uppercase fw-bold mt-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Kadaluarsa / Berakhir</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border border-light-subtle bg-white shadow-xs h-100" style="border-radius: 16px;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 d-flex align-items-center justify-content-center text-warning shrink-0" style="background: #fef3c7; width: 52px; height: 52px;">
                        <i class="bi bi-buildings-fill fs-3 text-warning"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold text-dark lh-1">{{ $totalMitraDudi }}</div>
                        <div class="small text-muted text-uppercase fw-bold mt-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Mitra di Prakerin</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table Container (Clean Light Theme) --}}
    <div class="card border border-light-subtle bg-white shadow-xs" style="border-radius: 16px;">
        <div class="card-body p-4">
            {{-- Filter & Search Form --}}
            <form action="{{ route('prakerin.kerjasama.index') }}" method="GET" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-secondary-subtle text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control bg-white text-dark border-secondary-subtle" placeholder="Cari nama mitra, bidang usaha, bentuk kerjasama, atau alamat..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="bentuk" class="form-select bg-white text-dark border-secondary-subtle">
                            <option value="">-- Semua Bentuk Kerjasama --</option>
                            @foreach($presetBentuk as $pb)
                                <option value="{{ $pb }}" {{ request('bentuk') == $pb ? 'selected' : '' }}>{{ $pb }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select bg-white text-dark border-secondary-subtle">
                            <option value="">-- Status --</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="berakhir" {{ request('status') == 'berakhir' ? 'selected' : '' }}>Berakhir</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 fw-semibold"><i class="bi bi-funnel me-1"></i> Filter</button>
                        @if(request()->anyFilled(['search', 'bentuk', 'status', 'tahun']))
                            <a href="{{ route('prakerin.kerjasama.index') }}" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-x-circle"></i></a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="border-radius: 12px; overflow: hidden;">
                    <thead class="bg-light text-secondary small text-uppercase" style="border-bottom: 2px solid #e2e8f0; font-weight: 700;">
                        <tr>
                            <th width="4%" class="text-center text-dark">No</th>
                            <th width="22%" class="text-dark">Nama Mitra &amp; Bidang</th>
                            <th width="18%" class="text-dark">Alamat</th>
                            <th width="20%" class="text-dark">Bentuk Kerjasama</th>
                            <th width="12%" class="text-center text-dark">Periode</th>
                            <th width="12%" class="text-center text-dark">Berkas / Drive</th>
                            <th width="6%" class="text-center text-dark">Status</th>
                            <th width="6%" class="text-center text-dark">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kerjasamas as $index => $item)
                            <tr>
                                <td class="text-center text-muted fw-semibold">{{ $kerjasamas->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="rounded-circle p-2 mt-1 text-primary d-flex align-items-center justify-content-center shrink-0" style="background: #eff6ff; width: 36px; height: 36px;">
                                            <i class="bi bi-buildings fs-5"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('prakerin.kerjasama.show', $item->id) }}" class="fw-bold text-dark text-decoration-none hover-underline d-block" style="font-size: 0.95rem;">
                                                {{ $item->nama_mitra }}
                                            </a>
                                            <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                                @if($item->bidang_mitra)
                                                    <span class="badge bg-light text-primary border border-primary-subtle" style="font-size: 0.72rem; font-weight: 600;">
                                                        <i class="bi bi-tag me-1"></i>{{ $item->bidang_mitra }}
                                                    </span>
                                                @endif
                                                @if($item->nomor_mou)
                                                    <span class="text-muted small" style="font-size: 0.73rem;">
                                                        <i class="bi bi-file-earmark-text text-secondary me-1"></i>{{ $item->nomor_mou }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-dark small" style="line-height: 1.4;">
                                        @if($item->alamat)
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ Str::limit($item->alamat, 75) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                    @if($item->pic_nama || $item->pic_kontak)
                                        <div class="mt-1 text-muted small" style="font-size: 0.75rem;">
                                            <i class="bi bi-person-fill text-primary me-1"></i>PIC: <strong class="text-dark">{{ $item->pic_nama ?? 'Kontak' }}</strong>
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
                                                    $bgStyle = 'background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;';
                                                    $bLower = strtolower($b);
                                                    if (str_contains($bLower, 'prakerin') || str_contains($bLower, 'pkl')) {
                                                        $bgStyle = 'background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;';
                                                    } elseif (str_contains($bLower, 'kurikulum')) {
                                                        $bgStyle = 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;';
                                                    } elseif (str_contains($bLower, 'payroll') || str_contains($bLower, 'gaji')) {
                                                        $bgStyle = 'background: #fce7f3; color: #9d174d; border: 1px solid #fbcfe8;';
                                                    } elseif (str_contains($bLower, 'rekrutmen') || str_contains($bLower, 'salur')) {
                                                        $bgStyle = 'background: #e0f2fe; color: #075985; border: 1px solid #bae6fd;';
                                                    }
                                                @endphp
                                                <span class="badge" style="{{ $bgStyle }} font-size: 0.73rem; font-weight: 600;">
                                                    {{ $b }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem; font-weight: 600;">
                                            <i class="bi bi-check2-circle me-1"></i>Siap Tempat Prakerin
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold text-dark">
                                        {{ $item->tahun_mulai }} s/d {{ $item->tahun_berakhir }}
                                    </div>
                                    @php
                                        $currentYear = (int) date('Y');
                                        $endYear = (int) $item->tahun_berakhir;
                                        $sisaTahun = $endYear - $currentYear;
                                    @endphp
                                    <div class="small mt-1" style="font-size: 0.72rem;">
                                        @if($sisaTahun > 0)
                                            <span class="text-success fw-semibold"><i class="bi bi-clock me-1"></i>Sisa {{ $sisaTahun }} tahun</span>
                                        @elseif($sisaTahun == 0)
                                            <span class="text-warning fw-semibold"><i class="bi bi-exclamation-circle me-1"></i>Berakhir tahun ini</span>
                                        @else
                                            <span class="text-danger fw-semibold"><i class="bi bi-x-circle me-1"></i>Lewat {{ abs($sisaTahun) }} tahun</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column gap-1 align-items-center">
                                        @if($item->hasLocalFile())
                                            <a href="{{ route('prakerin.kerjasama.download', $item->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2 d-inline-flex align-items-center gap-1 w-100 justify-content-center" style="font-size: 0.75rem; font-weight: 600;" title="Unduh Berkas Dokumen MoU">
                                                <i class="bi bi-file-earmark-arrow-down-fill text-primary"></i> Unduh Berkas
                                            </a>
                                        @elseif($item->file_kerjasama)
                                            <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">
                                                <i class="bi bi-file-earmark-x me-1"></i>File Offline
                                            </span>
                                        @endif

                                        @if($item->link_drive)
                                            <a href="{{ $item->link_drive }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-warning text-dark py-1 px-2 d-inline-flex align-items-center gap-1 w-100 justify-content-center" style="font-size: 0.75rem; font-weight: 600;" title="Buka Dokumen di Google Drive">
                                                <i class="bi bi-google text-warning"></i> Google Drive
                                            </a>
                                        @endif

                                        @if(!$item->hasLocalFile() && !$item->link_drive)
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($item->isAktif())
                                        <span class="badge bg-success text-white" style="font-size: 0.75rem; font-weight: 600;">
                                            <i class="bi bi-check-circle me-1"></i>Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-danger text-white" style="font-size: 0.75rem; font-weight: 600;">
                                            <i class="bi bi-x-circle me-1"></i>Berakhir
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('prakerin.kerjasama.show', $item->id) }}" class="btn btn-outline-primary" title="Lihat Detail Kerjasama">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('prakerin.kerjasama.edit', $item->id) }}" class="btn btn-outline-warning" title="Edit Data Kerjasama">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('prakerin.kerjasama.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data kerjasama dengan mitra {{ addslashes($item->nama_mitra) }}?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus Kerjasama">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="rounded-circle p-3 mb-3 bg-light border d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                                            <i class="bi bi-handshake text-success fs-1"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1">Belum Ada Data Kerjasama Mitra DU/DI</h5>
                                        <p class="small text-muted mb-3" style="max-width: 440px;">
                                            Inputkan perjanjian kerjasama (MoU) dengan mitra industri untuk menyelaraskan kurikulum, pelaksanaan prakerin, payroll, dan lainnya.
                                        </p>
                                        <a href="{{ route('prakerin.kerjasama.create') }}" class="btn btn-success px-4 fw-semibold shadow-sm">
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
                    <div class="small text-muted">
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
