<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Akun Pengguna - HilalEdu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fa;
            color: #212529;
        }
        .card-credential {
            border: 2px dashed #0d6efd;
            border-radius: 8px;
            padding: 15px;
            background: #ffffff;
            margin-bottom: 20px;
            break-inside: avoid;
            page-break-inside: avoid;
            position: relative;
        }
        .brand-badge {
            background-color: #0d6efd;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .role-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
        }
        .cred-box {
            background: #f1f5f9;
            border-radius: 6px;
            padding: 8px 12px;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 13px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
            }
            .card-credential {
                border-color: #94a3b8 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="p-3">

    {{-- Header Action --}}
    <div class="no-print d-flex justify-content-between align-items-center mb-4 p-3 bg-white rounded shadow-sm">
        <div>
            <h4 class="mb-0 fw-bold">Kartu Kredensial Akun Pengguna HilalEdu</h4>
            <div class="text-muted small">Total: {{ $users->count() }} kartu akun siap dicetak</div>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary fw-bold px-4">
                Cetak Dokumen (Ctrl + P)
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary">
                Tutup
            </button>
        </div>
    </div>

    {{-- Cards Grid --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
        @foreach($users as $u)
            <div class="col">
                <div class="card-credential shadow-sm h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-1">
                            <span class="brand-badge">HILALEDU</span>
                            <span class="small fw-semibold text-muted">Portal Sekolah</span>
                        </div>
                        <span class="role-badge {{ match($u->role) { 'guru' => 'bg-success text-white', 'tendik' => 'bg-warning text-dark', 'siswa' => 'bg-info text-dark', default => 'bg-primary text-white' } }}">
                            @if($u->role === 'siswa' && $u->kelas)
                                SISWA - {{ $u->kelas->nama_lengkap ?? $u->kelas->nama_kelas ?? $u->kelas->nama }}
                            @else
                                {{ strtoupper($u->role) }}
                            @endif
                        </span>
                    </div>

                    <div class="mb-2">
                        <div class="fw-bold fs-6 text-dark text-truncate">{{ $u->name }}</div>
                        <div class="small text-muted">
                            @if($u->nip)
                                {{ $u->role === 'siswa' ? 'NISN: ' : 'NIP: ' }}<strong>{{ $u->nip }}</strong>
                            @else
                                ID Pengguna: #{{ $u->id }}
                            @endif
                        </div>
                    </div>

                    <div class="cred-box mb-2">
                        <div class="d-flex justify-content-between py-1 border-bottom border-secondary border-opacity-10">
                            <span class="text-muted">Username:</span>
                            <span class="fw-bold text-dark">{{ $u->username }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Password:</span>
                            <span class="fw-bold text-primary">{{ $u->password_plain ?? 'password123' }}</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center text-muted small" style="font-size: 10px;">
                        <span>URL: {{ url('/login') }}</span>
                        <span>Harap simpan dengan baik</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
