@extends('layouts.app')

@section('title', 'Detail Kerjasama: ' . $kerjasama->nama_mitra)
@section('page-title', 'Detail Kerjasama Mitra DU/DI')

@section('content')
<div class="container-fluid px-0">
    {{-- Header Card --}}
    <div class="card border-0 shadow-sm mb-4" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 p-3 d-flex align-items-center justify-content-center text-primary" style="background: rgba(59, 130, 246, 0.2); width: 64px; height: 64px;">
                        <i class="bi bi-buildings-fill fs-2"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h3 class="fw-bold text-white mb-0">{{ $kerjasama->nama_mitra }}</h3>
                            @if($kerjasama->isAktif())
                                <span class="badge" style="background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.35);">
                                    <i class="bi bi-check-circle me-1"></i>Kerjasama Aktif
                                </span>
                            @else
                                <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35);">
                                    <i class="bi bi-x-circle me-1"></i>Kerjasama Berakhir
                                </span>
                            @endif
                        </div>
                        <div class="text-white-50 small mt-1 d-flex align-items-center gap-2 flex-wrap">
                            @if($kerjasama->bidang_mitra)
                                <span><i class="bi bi-tag text-info me-1"></i>{{ $kerjasama->bidang_mitra }}</span>
                                <span>•</span>
                            @endif
                            @if($kerjasama->nomor_mou)
                                <span><i class="bi bi-file-earmark-text text-warning me-1"></i>No. MoU: {{ $kerjasama->nomor_mou }}</span>
                                <span>•</span>
                            @endif
                            <span><i class="bi bi-calendar-range text-secondary me-1"></i>{{ $kerjasama->tahun_mulai }} s/d {{ $kerjasama->tahun_berakhir }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('prakerin.kerjasama.edit', $kerjasama->id) }}" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1">
                        <i class="bi bi-pencil-square"></i> Edit Kerjasama
                    </a>
                    @if($kerjasama->dudi)
                        <a href="{{ route('prakerin.penempatan.create', ['dudi_id' => $kerjasama->dudi_id]) }}" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1">
                            <i class="bi bi-person-plus-fill"></i> Plot Siswa ke Sini
                        </a>
                    @endif
                    <a href="{{ route('prakerin.kerjasama.index') }}" class="btn btn-outline-secondary btn-sm text-white-50">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Kolom Kiri: Detail Kesepakatan & Berkas --}}
        <div class="col-lg-7">
            {{-- Bentuk Kerjasama --}}
            <div class="card border-0 shadow-sm mb-4" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-header bg-transparent border-secondary p-4 pb-2">
                    <h5 class="fw-bold text-white mb-0">
                        <i class="bi bi-briefcase text-info me-2"></i>Bentuk Kesepakatan Kerjasama
                    </h5>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if(is_array($kerjasama->bentuk_kerjasama) && count($kerjasama->bentuk_kerjasama) > 0)
                            @foreach($kerjasama->bentuk_kerjasama as $b)
                                <span class="badge p-2 px-3" style="background: rgba(99, 102, 241, 0.2); color: #c7d2fe; border: 1px solid rgba(99, 102, 241, 0.35); font-size: 0.85rem; font-weight: 500;">
                                    <i class="bi bi-check2-circle text-info me-1"></i>{{ $b }}
                                </span>
                            @endforeach
                        @else
                            <span class="text-white-50">Belum ada bentuk kerjasama tertera.</span>
                        @endif
                    </div>

                    @if($kerjasama->keterangan)
                        <div class="p-3 rounded-3 mt-3" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.06);">
                            <div class="small text-white-50 fw-semibold text-uppercase mb-1" style="font-size: 0.72rem;">Catatan &amp; Ruang Lingkup:</div>
                            <div class="text-white small" style="white-space: pre-line;">{{ $kerjasama->keterangan }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Dokumen MoU & Google Drive --}}
            <div class="card border-0 shadow-sm mb-4" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-header bg-transparent border-secondary p-4 pb-2">
                    <h5 class="fw-bold text-white mb-0">
                        <i class="bi bi-file-earmark-pdf text-danger me-2"></i>Berkas MoU &amp; Akses Drive
                    </h5>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="row g-3">
                        {{-- File Dokumen Upload --}}
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 h-100 d-flex flex-column justify-content-between" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-file-earmark-arrow-down fs-4 text-info"></i>
                                        <div class="fw-bold text-white small">File Dokumen MoU / PKS</div>
                                    </div>
                                    @if($kerjasama->hasLocalFile())
                                        <p class="text-white-50 small mb-3 text-truncate" title="{{ $kerjasama->file_nama_asli }}">
                                            {{ $kerjasama->file_nama_asli ?? basename($kerjasama->file_kerjasama) }}
                                        </p>
                                    @else
                                        <p class="text-white-50 small mb-3">Tidak ada file lokal tersimpan.</p>
                                    @endif
                                </div>
                                <div>
                                    @if($kerjasama->hasLocalFile())
                                        <a href="{{ route('prakerin.kerjasama.download', $kerjasama->id) }}" class="btn btn-info text-white btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                                            <i class="bi bi-download"></i> Unduh Berkas MoU
                                        </a>
                                    @else
                                        <button class="btn btn-secondary btn-sm w-100" disabled>File Tidak Tersedia</button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Link Google Drive --}}
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 h-100 d-flex flex-column justify-content-between" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-google fs-4 text-warning"></i>
                                        <div class="fw-bold text-white small">Google Drive (Cadangan/Update)</div>
                                    </div>
                                    <p class="text-white-50 small mb-3">
                                        Akses berkas digital di cloud drive jika file tidak bisa dibuka atau telah diperbarui.
                                    </p>
                                </div>
                                <div>
                                    @if($kerjasama->link_drive)
                                        <a href="{{ $kerjasama->link_drive }}" target="_blank" rel="noopener noreferrer" class="btn btn-warning text-dark btn-sm w-100 d-flex align-items-center justify-content-center gap-2 fw-semibold">
                                            <i class="bi bi-box-arrow-up-right"></i> Buka Google Drive
                                        </a>
                                    @else
                                        <button class="btn btn-secondary btn-sm w-100" disabled>Belum Ada Link Drive</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Identitas Mitra & Penempatan Siswa --}}
        <div class="col-lg-5">
            {{-- Identitas Mitra --}}
            <div class="card border-0 shadow-sm mb-4" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-header bg-transparent border-secondary p-4 pb-2">
                    <h5 class="fw-bold text-white mb-0">
                        <i class="bi bi-info-circle text-primary me-2"></i>Informasi Mitra &amp; PIC
                    </h5>
                </div>
                <div class="card-body p-4 pt-3">
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                        <li class="d-flex gap-2">
                            <i class="bi bi-geo-alt-fill text-danger mt-1"></i>
                            <div>
                                <div class="text-white-50 small" style="font-size: 0.72rem;">ALAMAT PERUSAHAAN</div>
                                <div class="text-white small">{{ $kerjasama->alamat ?? '-' }}</div>
                            </div>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="bi bi-telephone-fill text-warning mt-1"></i>
                            <div>
                                <div class="text-white-50 small" style="font-size: 0.72rem;">TELEPON / HOTLINE</div>
                                <div class="text-white small">{{ $kerjasama->no_telp ?? '-' }}</div>
                            </div>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="bi bi-envelope-fill text-info mt-1"></i>
                            <div>
                                <div class="text-white-50 small" style="font-size: 0.72rem;">EMAIL</div>
                                <div class="text-white small">{{ $kerjasama->email ?? '-' }}</div>
                            </div>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="bi bi-person-badge-fill text-success mt-1"></i>
                            <div>
                                <div class="text-white-50 small" style="font-size: 0.72rem;">PIC / NARAHUBUNG</div>
                                <div class="text-white small font-semibold">{{ $kerjasama->pic_nama ?? '-' }}</div>
                                @if($kerjasama->pic_jabatan)
                                    <div class="text-white-50 small">{{ $kerjasama->pic_jabatan }}</div>
                                @endif
                                @if($kerjasama->pic_kontak)
                                    <div class="text-success small"><i class="bi bi-whatsapp me-1"></i>{{ $kerjasama->pic_kontak }}</div>
                                @endif
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Siswa Prakerin di Mitra Ini --}}
            <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-radius: 16px;">
                <div class="card-header bg-transparent border-secondary p-4 pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-white mb-0">
                        <i class="bi bi-person-workspace text-success me-2"></i>Siswa Prakerin di Sini
                    </h5>
                    @if($kerjasama->dudi)
                        <span class="badge bg-primary">{{ $kerjasama->dudi->penempatans->count() }} Siswa</span>
                    @endif
                </div>
                <div class="card-body p-4 pt-3">
                    @if($kerjasama->dudi && $kerjasama->dudi->penempatans->count() > 0)
                        <div class="list-group list-group-flush bg-transparent">
                            @foreach($kerjasama->dudi->penempatans->take(6) as $p)
                                <div class="list-group-item bg-transparent text-white px-0 py-2 border-secondary d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold small">{{ $p->siswa->nama_lengkap ?? 'Siswa' }}</div>
                                        <div class="text-white-50 small" style="font-size: 0.72rem;">
                                            {{ $p->siswa->kelas->nama_kelas ?? '-' }} • NIS: {{ $p->siswa->nis ?? '-' }}
                                        </div>
                                    </div>
                                    <div>
                                        @if($p->status === 'aktif')
                                            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25" style="font-size: 0.7rem;">Aktif</span>
                                        @elseif($p->status === 'selesai')
                                            <span class="badge bg-secondary" style="font-size: 0.7rem;">Selesai</span>
                                        @else
                                            <span class="badge bg-warning bg-opacity-25 text-warning" style="font-size: 0.7rem;">Belum Mulai</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($kerjasama->dudi->penempatans->count() > 6)
                            <div class="mt-2 text-center">
                                <a href="{{ route('prakerin.penempatan.index', ['dudi_id' => $kerjasama->dudi_id]) }}" class="text-info small text-decoration-none">
                                    Lihat semua {{ $kerjasama->dudi->penempatans->count() }} siswa penempatan <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3 text-white-50 small">
                            <i class="bi bi-person-x fs-3 d-block mb-1 text-secondary"></i>
                            Belum ada siswa yang ditempatkan di mitra ini.
                            @if($kerjasama->dudi)
                                <div class="mt-2">
                                    <a href="{{ route('prakerin.penempatan.create', ['dudi_id' => $kerjasama->dudi_id]) }}" class="btn btn-xs btn-outline-success py-1 px-2" style="font-size: 0.75rem;">
                                        <i class="bi bi-plus-circle me-1"></i> Tempatkan Siswa Sekarang
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
