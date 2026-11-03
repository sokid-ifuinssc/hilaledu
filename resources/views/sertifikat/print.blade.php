<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat - {{ $dokumen->nama_dokumen }} - {{ $siswa->name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @php
        $settingSekolah = \App\Models\PengaturanSekolah::getSetting();
        $namaSekolah = $settingSekolah->nama_sekolah ?: 'SMK PLUS AL HILAL';
        $logoUrl = $settingSekolah->logo_url;
        $cMeta = is_array($dokumen->custom_meta) ? $dokumen->custom_meta : [];
        $headerInstansi = $cMeta['header_instansi'] ?? $namaSekolah;
        $subHeader = !empty($cMeta['sub_header'])
            ? $cMeta['sub_header']
            : ($dokumen->kategori === 'kepesantrenan'
                ? 'PONDOK PESANTREN & LEMBAGA KEPESANTRENAN SISWA'
                : 'UNIT PRODUKSI & LEMBAGA KEWIRAUSAHAAN SISWA');
        $judulSertifikat = $cMeta['judul_sertifikat'] ?? 'SERTIFIKAT PENGHARGAAN';
        $kataPengantar = $cMeta['kata_pengantar'] ?? 'Diberikan dengan bangga kepada:';
        $kalimatPenutup = $cMeta['kalimat_penutup'] ?? '';
        $labelPredikat = $cMeta['label_predikat'] ?? 'DENGAN PREDIKAT NILAI';
        $showLogo = ($cMeta['show_logo'] ?? true) !== false;
        $templateBgRel = $cMeta['template_bg_rel_path'] ?? null;
        $templateBgUrl = ($templateBgRel && \Illuminate\Support\Facades\Storage::disk('public')->exists($templateBgRel))
            ? asset('storage/' . $templateBgRel)
            : null;
        $templateMode = $cMeta['template_mode'] ?? 'full';
        $namaYPercent = (float) ($cMeta['nama_y_percent'] ?? 52);
        $textColorHex = $cMeta['text_color_hex'] ?? '#0f172a';
        $hasTwoSigners = !empty($dokumen->pejabat_2_jabatan) || !empty($dokumen->pejabat_2_nama);
        $tempatTgl = trim(($dokumen->tempat_terbit ?: 'Cirebon') . ', ' . ($dokumen->tanggal_terbit ?: $dokumen->created_at->translatedFormat('d F Y')));
        $primaryColor = $dokumen->kategori === 'kepesantrenan' ? '#065f46' : '#1e3a8a';
    @endphp
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
        .btn-print { background: #10b981; color: #fff; }
        .btn-print:hover { background: #059669; }
        .btn-pdf { background: #3b82f6; color: #fff; }
        .btn-pdf:hover { background: #2563eb; }

        .cert-sheet {
            width: 297mm;
            height: 210mm;
            background: #fffdf5;
            position: relative;
            padding: {{ $templateBgUrl ? '0' : '10mm' }};
            box-shadow: 0 20px 50px rgba(0,0,0,0.45);
            overflow: hidden;
            @if($templateBgUrl)
            background-image: url('{{ $templateBgUrl }}');
            background-size: 100% 100%;
            background-position: center;
            @endif
        }
        .cert-border-outer {
            width: 100%;
            height: 100%;
            border: {{ $templateBgUrl ? 'none' : "5px solid {$primaryColor}" }};
            padding: {{ $templateBgUrl ? '0' : '5px' }};
            position: relative;
        }
        .cert-border-inner {
            width: 100%;
            height: 100%;
            border: {{ $templateBgUrl ? 'none' : '2px solid #ca8a04' }};
            padding: 10mm 16mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: center;
            position: relative;
            @if(!$templateBgUrl)
            background: radial-gradient(circle at center, rgba(254, 249, 195, 0.20) 0%, rgba(255, 253, 245, 0.96) 75%);
            @endif
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
            gap: 3px;
        }
        .school-logo {
            width: 64px;
            height: 64px;
            object-fit: contain;
            margin-bottom: 4px;
        }
        .school-name {
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 2px;
            color: {{ $primaryColor }};
            text-transform: uppercase;
        }
        .unit-subtitle {
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2.5px;
            color: #b45309;
            text-transform: uppercase;
        }
        .gold-divider {
            width: 340px;
            height: 1.5px;
            background: #ca8a04;
            margin: 5px auto;
            position: relative;
        }
        .cert-main-title {
            font-size: 34px;
            font-weight: 700;
            letter-spacing: 5px;
            color: {{ $primaryColor }};
            margin-top: 4px;
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
            margin-top: 4px;
        }
        .student-name {
            font-size: 30px;
            font-weight: 700;
            color: {{ $textColorHex }};
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
            margin-top: 3px;
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
            font-size: 19px;
            font-weight: 800;
            color: {{ $primaryColor }};
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 3px 0 8px;
        }
        .predicate-badge {
            display: inline-block;
            background: {{ $primaryColor }};
            color: #ffffff;
            border: 2px solid #eab308;
            padding: 6px 28px;
            border-radius: 6px;
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .signatures-row {
            display: flex;
            justify-content: {{ $hasTwoSigners ? 'space-between' : 'center' }};
            align-items: flex-end;
            margin-top: 10px;
            padding: 0 36px;
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
            margin-bottom: 54px;
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
            body { background: #fff; padding: 0; }
            .toolbar { display: none !important; }
            .cert-sheet { box-shadow: none; }
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

    <div class="cert-sheet">
        @if($templateBgUrl && $templateMode === 'overlay_nama')
            <!-- Mode Desain Sertifikat Diupload (Overlay Nama Siswa dari Database) -->
            <div style="position:absolute; left:0; right:0; top:{{ $namaYPercent }}%; transform:translateY(-50%); text-align:center; padding:0 15mm;">
                @if(!empty($cMeta['show_nomor_overlay']) && $dokumen->nomor_dokumen)
                    <div class="cert-number" style="margin-bottom:10px; color:{{ $textColorHex }};">Nomor: {{ $dokumen->nomor_dokumen }}</div>
                @endif
                <div class="student-name" style="border-bottom:none;">{{ $siswa->name }}</div>
                @if(!empty($cMeta['show_nisn_overlay']))
                    <div class="student-meta" style="color:{{ $textColorHex }};">
                        NISN: {{ $siswa->nisn ?: $siswa->username }}
                        @if($siswa->kelas)
                            &nbsp;|&nbsp; Kelas: {{ $siswa->kelas->nama_kelas ?? $siswa->kelas->nama }}
                        @endif
                    </div>
                @endif
                @if(!empty($cMeta['show_kegiatan_overlay']))
                    @if($dokumen->deskripsi_kegiatan)
                        <div class="activity-desc" style="color:{{ $textColorHex }};">{{ $dokumen->deskripsi_kegiatan }}</div>
                    @endif
                    <div class="activity-title">"{{ $dokumen->nama_kegiatan ?: $dokumen->nama_dokumen }}"</div>
                @endif
                @if(!empty($cMeta['show_predikat_overlay']) && $dokumen->predikat)
                    <div style="margin-top:10px;">
                        <span class="predicate-badge">{{ $labelPredikat }} : {{ strtoupper($dokumen->predikat) }}</span>
                    </div>
                @endif
            </div>
        @else
            <div class="cert-border-outer">
                <div class="cert-border-inner">
                    @if(!$templateBgUrl)
                    <div class="corner-ornament corner-tl"></div>
                    <div class="corner-ornament corner-tr"></div>
                    <div class="corner-ornament corner-bl"></div>
                    <div class="corner-ornament corner-br"></div>
                    @endif

                    <div class="header-block">
                        @if($showLogo && $logoUrl)
                            <img src="{{ $logoUrl }}" alt="Logo Sekolah" class="school-logo">
                        @endif
                        <div class="school-name">{{ $headerInstansi }}</div>
                        <div class="unit-subtitle">{{ $subHeader }}</div>
                        <div class="gold-divider"></div>
                        <div class="cert-main-title">{{ $judulSertifikat }}</div>
                        @if($dokumen->nomor_dokumen)
                            <div class="cert-number">Nomor : {{ $dokumen->nomor_dokumen }}</div>
                        @endif
                    </div>

                    <div>
                        <div class="given-to">{{ $kataPengantar }}</div>
                        <div class="student-name">{{ $siswa->name }}</div>
                        <div class="student-meta">
                            NISN/ID: {{ $siswa->nisn ?: $siswa->username }}
                            @if($siswa->kelas)
                                &nbsp;&bull;&nbsp; Kelas: {{ $siswa->kelas->nama_kelas ?? $siswa->kelas->nama }}
                            @endif
                        </div>

                        <div class="activity-desc">
                            {{ $dokumen->deskripsi_kegiatan ?: ($dokumen->kategori === 'kepesantrenan'
                                ? 'Telah mengikuti dan menyelesaikan dengan baik program pembinaan serta evaluasi kompetensi kepesantrenan pada kegiatan:'
                                : 'Telah mengikuti dan menyelesaikan dengan baik program praktik serta evaluasi kompetensi kewirausahaan pada kegiatan:') }}
                        </div>
                        <div class="activity-title">"{{ $dokumen->nama_kegiatan ?: $dokumen->nama_dokumen }}"</div>

                        @if($kalimatPenutup)
                            <div style="font-style:italic; font-size:13px; color:#475569; margin-bottom:6px;">{{ $kalimatPenutup }}</div>
                        @endif

                        @if($dokumen->predikat && $dokumen->predikat !== '-')
                            <div>
                                <span class="predicate-badge">{{ $labelPredikat }} : {{ strtoupper($dokumen->predikat) }}</span>
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
        @endif
    </div>

</body>
</html>
