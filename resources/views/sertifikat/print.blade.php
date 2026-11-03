<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat - {{ $dokumen->nama_dokumen }} - {{ $siswa->name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            background: #1e293b;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding: 24px 12px;
        }
        .toolbar {
            width: 297mm;
            max-width: 100%;
            background: #0f172a;
            color: #fff;
            padding: 12px 20px;
            border-radius: 14px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: system-ui, -apple-system, sans-serif;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        .toolbar-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: 0.2s;
        }
        .btn-print {
            background: #10b981;
            color: #fff;
        }
        .btn-print:hover {
            background: #059669;
        }
        .btn-pdf {
            background: #3b82f6;
            color: #fff;
        }
        .btn-pdf:hover {
            background: #2563eb;
        }
        .cert-sheet {
            width: 297mm;
            height: 210mm;
            background: #fffdf7;
            position: relative;
            padding: 12mm;
            box-shadow: 0 20px 50px rgba(0,0,0,0.45);
            overflow: hidden;
        }
        .cert-border-outer {
            width: 100%;
            height: 100%;
            border: 5px solid {{ $dokumen->kategori === 'kepesantrenan' ? '#065f46' : '#1e3a8a' }};
            padding: 5px;
            position: relative;
        }
        .cert-border-inner {
            width: 100%;
            height: 100%;
            border: 2px solid #ca8a04;
            padding: 10mm 16mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: center;
            position: relative;
            background: radial-gradient(circle at center, rgba(254, 249, 195, 0.22) 0%, rgba(255, 253, 247, 0.95) 75%);
        }
        .corner-ornament {
            position: absolute;
            width: 34px;
            height: 34px;
            border: 3px solid #ca8a04;
        }
        .corner-tl { top: 8px; left: 8px; border-right: none; border-bottom: none; }
        .corner-tr { top: 8px; right: 8px; border-left: none; border-bottom: none; }
        .corner-bl { bottom: 8px; left: 8px; border-right: none; border-top: none; }
        .corner-br { bottom: 8px; right: 8px; border-left: none; border-top: none; }

        .header-block {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
        .school-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-bottom: 4px;
        }
        .school-name {
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 17px;
            font-weight: 800;
            letter-spacing: 2px;
            color: {{ $dokumen->kategori === 'kepesantrenan' ? '#065f46' : '#1e3a8a' }};
            text-transform: uppercase;
        }
        .unit-subtitle {
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 3px;
            color: #b45309;
            text-transform: uppercase;
        }
        .cert-main-title {
            font-size: 40px;
            font-weight: 700;
            letter-spacing: 8px;
            color: {{ $dokumen->kategori === 'kepesantrenan' ? '#064e3b' : '#1e3a8a' }};
            margin-top: 6px;
            text-transform: uppercase;
        }
        .cert-number {
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 12px;
            color: #475569;
            font-weight: 600;
            margin-top: 2px;
        }
        .given-to {
            font-style: italic;
            font-size: 15px;
            color: #334155;
            margin-top: 8px;
        }
        .student-name {
            font-size: 30px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border-bottom: 2px solid #ca8a04;
            display: inline-block;
            padding: 2px 32px 5px;
            margin: 4px auto;
        }
        .student-meta {
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 12.5px;
            font-weight: 700;
            color: #475569;
            margin-top: 2px;
        }
        .activity-desc {
            font-size: 14.5px;
            color: #1e293b;
            max-width: 82%;
            margin: 8px auto 4px;
            line-height: 1.45;
        }
        .activity-title {
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: {{ $dokumen->kategori === 'kepesantrenan' ? '#065f46' : '#1e3a8a' }};
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 2px 0 8px;
        }
        .predicate-badge {
            display: inline-block;
            background: {{ $dokumen->kategori === 'kepesantrenan' ? '#065f46' : '#1e3a8a' }};
            color: #ffffff;
            border: 2px solid #eab308;
            padding: 6px 28px;
            border-radius: 999px;
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 13.5px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .signatures-row {
            display: flex;
            justify-content: {{ (!empty($dokumen->pejabat_2_jabatan) || !empty($dokumen->pejabat_2_nama)) ? 'space-between' : 'center' }};
            align-items: flex-end;
            margin-top: 12px;
            padding: 0 28px;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .sig-col {
            width: 260px;
            text-align: center;
        }
        .sig-date {
            font-size: 12px;
            color: #334155;
            margin-bottom: 3px;
        }
        .sig-role {
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 58px;
        }
        .sig-name {
            font-size: 13.5px;
            font-weight: 800;
            color: #0f172a;
            text-decoration: underline;
        }
        .sig-nip {
            font-size: 11px;
            color: #475569;
            margin-top: 2px;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .toolbar {
                display: none !important;
            }
            .cert-sheet {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <div>
            <strong>Pratinjau Cetak Sertifikat Resmi</strong> — {{ $siswa->name }} ({{ $dokumen->nama_dokumen }})
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('dokumen-sertifikat.download', $dokumen) }}" class="toolbar-btn btn-pdf">
                <i class="bi bi-file-earmark-pdf-fill"></i> Unduh File PDF
            </a>
            <button type="button" onclick="window.print()" class="toolbar-btn btn-print">
                <i class="bi bi-printer-fill"></i> Cetak / Simpan PDF (A4 Landscape)
            </button>
        </div>
    </div>

    @php
        $namaSekolah = \App\Models\PengaturanSekolah::first()->nama_sekolah ?? 'SMK PLUS AL HILAL';
        $hasTwoSigners = !empty($dokumen->pejabat_2_jabatan) || !empty($dokumen->pejabat_2_nama);
        $tempatTgl = trim(($dokumen->tempat_terbit ?: 'Cirebon') . ', ' . ($dokumen->tanggal_terbit ?: $dokumen->created_at->translatedFormat('d F Y')));
    @endphp

    <div class="cert-sheet">
        <div class="cert-border-outer">
            <div class="cert-border-inner">
                <div class="corner-ornament corner-tl"></div>
                <div class="corner-ornament corner-tr"></div>
                <div class="corner-ornament corner-bl"></div>
                <div class="corner-ornament corner-br"></div>

                <div class="header-block">
                    @if(file_exists(public_path('images/logo_smk.png')))
                        <img src="{{ asset('images/logo_smk.png') }}" alt="Logo" class="school-logo">
                    @endif
                    <div class="school-name">{{ $namaSekolah }}</div>
                    <div class="unit-subtitle">
                        {{ $dokumen->kategori === 'kepesantrenan' ? 'Pondok Pesantren & Lembaga Kepesantrenan Siswa' : 'Unit Produksi & Lembaga Kewirausahaan Siswa' }}
                    </div>
                    <div class="cert-main-title">SERTIFIKAT</div>
                    @if($dokumen->nomor_dokumen)
                        <div class="cert-number">Nomor: {{ $dokumen->nomor_dokumen }}</div>
                    @endif
                </div>

                <div>
                    <div class="given-to">Diberikan dengan bangga kepada:</div>
                    <div class="student-name">{{ $siswa->name }}</div>
                    <div class="student-meta">
                        NISN/ID: {{ $siswa->nisn ?: $siswa->username }}
                        @if($siswa->kelas)
                            &nbsp;&bull;&nbsp; Kelas: {{ $siswa->kelas->nama_kelas ?? $siswa->kelas->nama }}
                        @endif
                    </div>

                    <div class="activity-desc">
                        {{ $dokumen->deskripsi_kegiatan ?: ($dokumen->kategori === 'kepesantrenan'
                            ? 'Telah mengikuti dan menyelesaikan program pembinaan serta evaluasi kompetensi kepesantrenan pada kegiatan:'
                            : 'Telah mengikuti dan menyelesaikan program praktik serta evaluasi kompetensi kewirausahaan pada kegiatan:') }}
                    </div>
                    <div class="activity-title">"{{ $dokumen->nama_kegiatan ?: $dokumen->nama_dokumen }}"</div>

                    @if($dokumen->predikat)
                        <div>
                            <span class="predicate-badge">Predikat Nilai : {{ strtoupper($dokumen->predikat) }}</span>
                        </div>
                    @endif
                </div>

                <div class="signatures-row">
                    @if($hasTwoSigners)
                        <div class="sig-col">
                            <div class="sig-date">Mengetahui,</div>
                            <div class="sig-role">{{ $dokumen->pejabat_1_jabatan ?: 'Kepala Sekolah' }}</div>
                            <div class="sig-name">{{ $dokumen->pejabat_1_nama ?: '........................................' }}</div>
                            @if($dokumen->pejabat_1_nip)
                                <div class="sig-nip">{{ $dokumen->pejabat_1_nip }}</div>
                            @endif
                        </div>
                        <div class="sig-col">
                            <div class="sig-date">{{ $tempatTgl }}</div>
                            <div class="sig-role">{{ $dokumen->pejabat_2_jabatan ?: 'Ketua Lembaga' }}</div>
                            <div class="sig-name">{{ $dokumen->pejabat_2_nama ?: '........................................' }}</div>
                            @if($dokumen->pejabat_2_nip)
                                <div class="sig-nip">{{ $dokumen->pejabat_2_nip }}</div>
                            @endif
                        </div>
                    @else
                        <div class="sig-col">
                            <div class="sig-date">{{ $tempatTgl }}</div>
                            <div class="sig-role">{{ $dokumen->pejabat_1_jabatan ?: 'Kepala SMK Plus Al Hilal' }}</div>
                            <div class="sig-name">{{ $dokumen->pejabat_1_nama ?: '........................................' }}</div>
                            @if($dokumen->pejabat_1_nip)
                                <div class="sig-nip">{{ $dokumen->pejabat_1_nip }}</div>
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

</body>
</html>

