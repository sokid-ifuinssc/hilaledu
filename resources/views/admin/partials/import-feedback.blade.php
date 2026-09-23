@if(session('import_summary'))
@php
    $summary = session('import_summary');
    $created = $summary['created'] ?? 0;
    $updated = $summary['updated'] ?? 0;
    $skipped = $summary['skipped'] ?? 0;
    $replaced = $summary['replaced'] ?? 0;
    $matched = $summary['matched'] ?? [];
    $errorsList = $summary['errors'] ?? [];
    $totalProcessed = $created + $updated + $skipped + $replaced + count($errorsList);
@endphp

<div class="card mb-4 border-0 shadow-sm" style="background: var(--bg-card, #16221c); border: 1px solid var(--border-color, rgba(255,255,255,0.1)) !important; border-radius: 16px; overflow: hidden;">
    <div class="card-header py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: rgba(255,255,255,0.03); border-bottom: 1px solid var(--border-color, rgba(255,255,255,0.08));">
        <div class="d-flex align-items-center gap-2">
            <div style="width: 34px; height: 34px; border-radius: 10px; background: rgba(46,204,113,0.15); display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-file-earmark-spreadsheet-fill text-success fs-5"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold text-light">Hasil Laporan Import Excel</h6>
                <small class="text-muted">Ringkasan pemrosesan data, validasi kolom wajib, dan penanganan data duplikat</small>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="this.closest('.card').remove()" title="Tutup laporan">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="card-body p-4">
        {{-- Stat Badges --}}
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-auto">
                <div class="p-2 px-3 rounded-3 d-flex align-items-center gap-2" style="background: rgba(46,204,113,0.1); border: 1px solid rgba(46,204,113,0.25);">
                    <i class="bi bi-person-plus-fill text-success"></i>
                    <div>
                        <div class="fw-bold text-success small">{{ $created }} Data Baru</div>
                        <div style="font-size: 0.72rem; color: var(--text-muted);">Berhasil Dibuat</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-auto">
                <div class="p-2 px-3 rounded-3 d-flex align-items-center gap-2" style="background: rgba(52,152,219,0.1); border: 1px solid rgba(52,152,219,0.25);">
                    <i class="bi bi-arrow-repeat text-info"></i>
                    <div>
                        <div class="fw-bold text-info small">{{ $updated }} Data Diperbarui</div>
                        <div style="font-size: 0.72rem; color: var(--text-muted);">Ditindih / Ditimpa</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-auto">
                <div class="p-2 px-3 rounded-3 d-flex align-items-center gap-2" style="background: rgba(243,156,18,0.1); border: 1px solid rgba(243,156,18,0.25);">
                    <i class="bi bi-skip-forward-fill text-warning"></i>
                    <div>
                        <div class="fw-bold text-warning small">{{ $skipped }} Dilewati</div>
                        <div style="font-size: 0.72rem; color: var(--text-muted);">Jangan Timpa</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-auto">
                <div class="p-2 px-3 rounded-3 d-flex align-items-center gap-2" style="background: rgba(155,89,182,0.1); border: 1px solid rgba(155,89,182,0.25);">
                    <i class="bi bi-arrow-clockwise" style="color:#bb86fc;"></i>
                    <div>
                        <div class="fw-bold small" style="color:#bb86fc;">{{ $replaced }} Diganti</div>
                        <div style="font-size: 0.72rem; color: var(--text-muted);">Hapus & Buat Baru</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-auto">
                <div class="p-2 px-3 rounded-3 d-flex align-items-center gap-2" style="background: rgba(231,76,60,0.1); border: 1px solid rgba(231,76,60,0.25);">
                    <i class="bi bi-x-circle-fill text-danger"></i>
                    <div>
                        <div class="fw-bold text-danger small">{{ count($errorsList) }} Baris Ditolak</div>
                        <div style="font-size: 0.72rem; color: var(--text-muted);">Kesalahan Validasi</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 1: Error Report (Jika Ada Kesalahan Validasi / Kolom Kosong) --}}
        @if(count($errorsList) > 0)
        <div class="mb-4 p-3 rounded-3" style="background: rgba(231,76,60,0.06); border: 1px solid rgba(231,76,60,0.2);">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-exclamation-octagon-fill text-danger fs-5"></i>
                <span class="fw-bold text-danger">Detail Kesalahan Data / Kolom (Wajib Diperbaiki):</span>
            </div>
            <p class="small text-muted mb-3" style="line-height: 1.5;">
                Berikut adalah baris-baris pada file Excel yang tidak dapat diterima. Periksa kolom yang bersangkutan dan isi data yang wajib sebelum mengunggah kembali.
            </p>

            <div class="table-responsive">
                <table class="table table-sm table-bordered text-light mb-0" style="border-color: rgba(255,255,255,0.1); font-size: 0.82rem;">
                    <thead>
                        <tr style="background: rgba(231,76,60,0.15);">
                            <th style="width: 120px;" class="text-center">Baris Excel</th>
                            <th style="width: 180px;">Kolom Bermasalah</th>
                            <th>Penyebab & Penjelasan Kesalahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($errorsList as $err)
                        <tr>
                            <td class="text-center align-middle">
                                <span class="badge bg-danger">Baris ke-{{ $err['row'] }}</span>
                            </td>
                            <td class="align-middle">
                                <span class="badge bg-warning text-dark font-monospace">{{ $err['column'] }}</span>
                            </td>
                            <td class="align-middle">
                                <div class="text-light fw-medium">{{ $err['message'] }}</div>
                                @if(!empty($err['data']))
                                    <div class="small text-muted mt-1" style="font-size:0.75rem;">
                                        <em>Cuplikan data baris:</em>
                                        @php
                                            $preview = array_filter($err['data'], fn($v) => !is_null($v) && $v !== '');
                                            $previewStr = collect($preview)->map(fn($v, $k) => "$k: $v")->take(3)->implode(' | ');
                                        @endphp
                                        <code>{{ $previewStr ?: '(Semua kolom kosong)' }}</code>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Section 2: Kesamaan Data / Data Duplikat yang Ditemukan --}}
        @if(count($matched) > 0)
        <div class="p-3 rounded-3" style="background: rgba(52,152,219,0.06); border: 1px solid rgba(52,152,219,0.2);">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-people-fill text-info fs-5"></i>
                    <span class="fw-bold text-info">Pemberitahuan Kesamaan Data (Duplikat / Data Sudah Terdaftar):</span>
                </div>
                <button class="btn btn-sm btn-outline-info py-0 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#matchedListCollapse" aria-expanded="false" style="font-size: 0.75rem;">
                    <i class="bi bi-chevron-down me-1"></i> Tampilkan / Sembunyikan ({{ count($matched) }} Data)
                </button>
            </div>
            <p class="small text-muted mb-2">
                Sistem menemukan data yang sudah ada sebelumnya di database dan telah menerapkan aksi sesuai preferensi yang Anda pilih saat upload:
            </p>

            <div class="collapse show" id="matchedListCollapse">
                <div class="table-responsive mt-2">
                    <table class="table table-sm table-bordered text-light mb-0" style="border-color: rgba(255,255,255,0.1); font-size: 0.8rem;">
                        <thead>
                            <tr style="background: rgba(52,152,219,0.15);">
                                <th style="width: 100px;" class="text-center">Baris Excel</th>
                                <th>Nama Lengkap</th>
                                <th style="width: 180px;">Nomor Pengenal (NIS/NUPTK)</th>
                                <th style="width: 200px;">Tindakan Diterapkan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($matched as $m)
                            <tr>
                                <td class="text-center align-middle">
                                    <span class="badge bg-secondary">Baris {{ $m['row'] }}</span>
                                </td>
                                <td class="align-middle fw-medium">{{ $m['name'] }}</td>
                                <td class="align-middle font-monospace">{{ $m['identifier'] }}</td>
                                <td class="align-middle">
                                    @php $act = $m['action'] ?? ''; @endphp
                                    @if($act === 'update' || str_contains($act, 'Diperbarui') || str_contains($act, 'Timpa'))
                                        <span class="badge bg-info text-dark"><i class="bi bi-arrow-repeat me-1"></i>Ditimpa / Diperbarui</span>
                                    @elseif($act === 'skip' || str_contains($act, 'Dilewati') || str_contains($act, 'Lewati'))
                                        <span class="badge bg-warning text-dark"><i class="bi bi-skip-forward me-1"></i>Dilewati (Data Lama Utuh)</span>
                                    @elseif($act === 'replace' || str_contains($act, 'Diganti') || str_contains($act, 'Dihapus'))
                                        <span class="badge text-white" style="background:#8e44ad;"><i class="bi bi-trash-fill me-1"></i>Dihapus & Diganti Baru</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $act ?: '-' }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endif
