<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struktur Kurikulum & Pembagian Tugas Mengajar - {{ $settings['nama_sekolah'] ?? 'SMK Plus Al-Hilal' }}</title>
    <!-- Tailwind CSS CDN for Print Layout -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                padding: 0 !important;
            }
            .page-break {
                page-break-after: always;
                break-after: page;
            }
            .page-container {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-900 antialiased p-4 print:p-0">

    <!-- FLOATING ACTION TOOLBAR (NO-PRINT) -->
    <div class="no-print max-w-4xl mx-auto mb-6 bg-white p-4 rounded-2xl shadow-lg border border-slate-300">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.kurikulum.index', request()->query()) }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition inline-flex items-center gap-1.5">
                    &larr; Kembali ke Kelola Kurikulum
                </a>
                <span class="text-xs font-semibold text-slate-500">Format Cetak Resmi A4 (Kurikulum SMK)</span>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-md transition flex items-center gap-2 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                        <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/>
                    </svg>
                    <span>Cetak Lembar Dokumen (Print / PDF)</span>
                </button>
            </div>
        </div>

        <!-- QUICK FILTER BUTTONS -->
        <div class="pt-3 border-t border-slate-200 flex flex-wrap items-center gap-1.5 text-xs">
            <span class="text-slate-500 font-bold mr-1">Pilihan Cetak Jenjang:</span>
            <a href="{{ route('admin.kurikulum.print', ['jenjang' => 'X']) }}"
               class="px-3 py-1 rounded-lg font-black transition {{ $jenjang === 'X' && $kelas === 'all' && $jurusan === 'all' ? 'bg-emerald-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                Kelas X (3 Jurusan)
            </a>
            <a href="{{ route('admin.kurikulum.print', ['jenjang' => 'XI']) }}"
               class="px-3 py-1 rounded-lg font-black transition {{ $jenjang === 'XI' && $kelas === 'all' && $jurusan === 'all' ? 'bg-blue-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                Kelas XI (3 Jurusan)
            </a>
            <a href="{{ route('admin.kurikulum.print', ['jenjang' => 'XII']) }}"
               class="px-3 py-1 rounded-lg font-black transition {{ $jenjang === 'XII' && $kelas === 'all' && $jurusan === 'all' ? 'bg-purple-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                Kelas XII (3 Jurusan)
            </a>
            <span class="text-slate-300">|</span>
            <a href="{{ route('admin.kurikulum.print') }}"
               class="px-3 py-1 rounded-lg font-bold transition {{ $jenjang === 'all' && $kelas === 'all' && $jurusan === 'all' ? 'bg-slate-800 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                Semua Rombel (9 Kelas)
            </a>
        </div>
    </div>

    @forelse($kurikulumsByKelas as $rombelNama => $itemsKelas)
    @php
        $katUmum = 'A. KELOMPOK MATA PELAJARAN UMUM';
        $katKejuruan = 'B. KELOMPOK MATA PELAJARAN KEJURUAN';

        $isKelasX = str_starts_with($rombelNama, 'X ');
        $isKelasXI = str_starts_with($rombelNama, 'XI ');
        $isKelasXII = str_starts_with($rombelNama, 'XII ');

        $itemsUmum = $itemsKelas->filter(fn($i) => str_starts_with($i->kategori, 'A.') || $i->kategori === $katUmum);
        $itemsKejuruan = $itemsKelas->filter(fn($i) => str_starts_with($i->kategori, 'B.') || $i->kategori === $katKejuruan);

        $totalJpUmum = $itemsUmum->sum('alokasi_jam');
        $totalJpKejuruan = $itemsKejuruan->sum('alokasi_jam');
        $totalJpKelas = $itemsKelas->sum('alokasi_jam');

        // Deteksi Info Jurusan & Fase
        $jurusanNama = match(true) {
            str_contains($rombelNama, 'AKL') || str_contains($rombelNama, 'PRB') => 'Akuntansi dan Keuangan Lembaga (AKL)',
            str_contains($rombelNama, 'TKJT') || str_contains($rombelNama, 'TKJ') => 'Teknik Jaringan Komputer dan Telekomunikasi (TKJT)',
            str_contains($rombelNama, 'TO') || str_contains($rombelNama, 'TKR') => 'Teknik Otomotif (TO)',
            default => 'Kejuruan SMK',
        };

        $fase = $isKelasX ? 'Fase E (Kelas X)' : ($isKelasXI ? 'Fase F (Kelas XI)' : 'Fase F (Kelas XII)');
    @endphp

    <!-- MAIN PRINT CONTAINER PER ROMBEL KELAS -->
    <div class="page-container bg-white p-6 sm:p-10 mx-auto max-w-[900px] text-xs leading-relaxed text-black border border-slate-200 print:border-none shadow-sm print:shadow-none mb-8 print:mb-0">

        <!-- KOP DOKUMEN RESMI SEKOLAH (SESUAI DATA MASTER) -->
        <div class="border-b-2 border-black pb-2.5 mb-2.5 flex items-center gap-4">
            <div class="w-16 h-16 flex-shrink-0 flex items-center justify-center">
                <img src="{{ asset($settings['logo_sekolah'] ?? 'images/logo.png') }}" class="w-16 h-16 object-contain" alt="Logo Sekolah" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
            </div>
            <div class="text-center flex-1">
                <h2 class="text-base font-bold uppercase tracking-wider text-black">YAYASAN AL-HILAL TEGALGUBUG</h2>
                <h1 class="text-xl font-black uppercase tracking-wide text-black">{{ $settings['nama_sekolah'] ?? 'SMK PLUS AL-HILAL ARJAWINANGUN' }}</h1>
                <p class="text-[10px] font-semibold text-black">
                    NPSN: {{ $settings['npsn'] ?? '69758451' }} | Program Keahlian: {{ $settings['program_keahlian'] ?? 'Teknik Jaringan Komputer, Teknik Otomotif, Akuntansi' }}
                </p>
                <p class="text-[9.5px] text-black italic">
                    {{ $settings['alamat_sekolah'] ?? 'Jl. H. Manshur No 7 Lap. Bima Rembes, Ds. Tegalgubug, Kec. Arjawinangun, Kab. Cirebon' }}
                </p>
                <p class="text-[9px] text-black">
                    Telp: {{ $settings['telepon_sekolah'] ?? '(0231) 357123' }} | Email: {{ $settings['email_sekolah'] ?? 'smkpal2021@gmail.com' }} | Web: {{ $settings['website_sekolah'] ?? 'smkplusalhilal.sch.id' }}
                </p>
            </div>
        </div>

        <!-- GARIS GANDA KOP -->
        <div class="border-b border-black -mt-1.5 mb-3"></div>

        <!-- JUDUL DOKUMEN -->
        <div class="text-center space-y-0.5 mb-3">
            <h2 class="text-sm font-black tracking-wider uppercase underline">
                STRUKTUR KURIKULUM & PEMBAGIAN TUGAS MENGAJAR (BEBAN KBM)
            </h2>
            <p class="text-xs font-bold uppercase text-black">
                TAHUN PELAJARAN {{ $settings['tahun_pelajaran'] ?? \App\Models\PengaturanSekolah::getActiveTahunAjaran() }} &mdash; SEMESTER {{ strtoupper($settings['semester'] ?? \App\Models\PengaturanSekolah::getActiveSemester()) }}
            </p>
            
            <!-- IDENTITAS KELAS & JURUSAN -->
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-100 border border-black rounded text-[11px] font-black mt-1">
                <span>ROMBEL KELAS: <u>{{ $rombelNama }}</u></span>
                <span>&bull;</span>
                <span>PROGRAM KEAHLIAN: <u>{{ $jurusanNama }}</u></span>
                <span>&bull;</span>
                <span>FASE: <u>{{ $fase }}</u></span>
            </div>
        </div>

        <!-- TABEL STRUKTUR ALOKASI KURIKULUM RESMI (A. UMUM & B. KEJURUAN) -->
        <table class="w-full border-collapse border border-black text-[10.5px] mb-3">
            <thead class="bg-slate-100 font-black text-center text-black">
                <tr>
                    <th class="border border-black px-2 py-1.5 w-[6%]">NO</th>
                    <th class="border border-black px-2 py-1.5 w-[10%]">KODE</th>
                    <th class="border border-black px-2 py-1.5 text-left w-[44%]">MATA PELAJARAN</th>
                    <th class="border border-black px-2 py-1.5 text-left w-[28%]">GURU PENGAMPU</th>
                    <th class="border border-black px-2 py-1.5 text-center w-[12%]">BEBAN (JP)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black font-medium">

                <!-- ========================================================= -->
                <!-- A. KELOMPOK MATA PELAJARAN UMUM                           -->
                <!-- ========================================================= -->
                <tr class="bg-slate-200 font-black text-black">
                    <td colspan="5" class="border border-black px-3 py-1.5 text-left uppercase tracking-wide text-[11px]">
                        A. KELOMPOK MATA PELAJARAN UMUM:
                    </td>
                </tr>

                @php $noA = 1; @endphp
                @forelse($itemsUmum as $it)
                <tr>
                    <td class="border border-black px-2 py-1 text-center font-bold">{{ $noA++ }}</td>
                    <td class="border border-black px-2 py-1 text-center font-mono font-bold">
                        {{ $it->mataPelajaran->kode ?? '-' }}
                    </td>
                    <td class="border border-black px-2 py-1">
                        <span class="font-bold text-black">{{ $it->mataPelajaran->nama ?? '-' }}</span>
                        @if($it->keterangan)
                        <span class="text-[9.5px] block italic text-slate-600">Ket: {{ $it->keterangan }}</span>
                        @endif
                    </td>
                    <td class="border border-black px-2 py-1">
                        @if($it->guru)
                        <span class="font-bold text-black">{{ $it->guru?->name }}</span>
                        @if($it->guru->nip)
                        <span class="text-[9px] block font-mono text-slate-600">NIP: {{ $it->guru->nip }}</span>
                        @endif
                        @else
                        <span class="italic text-slate-500 font-normal">Belum Ditetapkan</span>
                        @endif
                    </td>
                    <td class="border border-black px-2 py-1 text-center font-bold">
                        {{ $it->alokasi_jam }} JP
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="border border-black px-4 py-2 text-center italic text-slate-500">
                        Belum ada data mata pelajaran umum teralokasi.
                    </td>
                </tr>
                @endforelse
                <tr class="bg-slate-50 font-bold text-black">
                    <td colspan="4" class="border border-black px-3 py-1 text-right italic">
                        Jumlah Kelompok Mata Pelajaran Umum (A) :
                    </td>
                    <td class="border border-black px-2 py-1 text-center font-black">
                        {{ $totalJpUmum }} JP
                    </td>
                </tr>

                <!-- ========================================================= -->
                <!-- B. KELOMPOK MATA PELAJARAN KEJURUAN                       -->
                <!-- ========================================================= -->
                <tr class="bg-slate-200 font-black text-black">
                    <td colspan="5" class="border border-black px-3 py-1.5 text-left uppercase tracking-wide text-[11px]">
                        B. KELOMPOK MATA PELAJARAN KEJURUAN:
                    </td>
                </tr>

                @if($isKelasX)
                    {{-- STRUKTUR RESMI KELAS X (Fase E: Dasar-dasar Program Keahlian) --}}
                    @php
                        $subGroupedX = $itemsKejuruan->groupBy(function($item) {
                            $sub = $item->sub_kategori ?? '';
                            if (str_contains($sub, 'Konsentrasi')) return 'konsentrasi';
                            if (str_contains($sub, 'Pilihan')) return 'pilihan';
                            if (str_contains($sub, 'Dasar-dasar')) return 'dasar';
                            return 'top_level';
                        });
                        $noB = 1;
                    @endphp

                    {{-- Top Level Kejuruan (Matematika, Bahasa Inggris, Informatika, IPAS dll) --}}
                    @forelse($subGroupedX['top_level'] ?? [] as $it)
                    <tr>
                        <td class="border border-black px-2 py-1 text-center font-bold">{{ $noB++ }}.</td>
                        <td class="border border-black px-2 py-1 text-center font-mono font-bold">{{ $it->mataPelajaran->kode ?? '-' }}</td>
                        <td class="border border-black px-2 py-1">
                            <span class="font-bold text-black">{{ $it->mataPelajaran->nama ?? '-' }}</span>
                            @if($it->keterangan)
                            <span class="text-[9.5px] block italic text-slate-600">Ket: {{ $it->keterangan }}</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1">
                            @if($it->guru)
                            <span class="font-bold text-black">{{ $it->guru?->name }}</span>
                            @else
                            <span class="italic text-slate-500 font-normal">Belum Ditetapkan</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1 text-center font-bold">{{ $it->alokasi_jam }} JP</td>
                    </tr>
                    @empty
                    @endforelse

                    {{-- Dasar-dasar Program Keahlian --}}
                    @if(isset($subGroupedX['dasar']) && count($subGroupedX['dasar']) > 0)
                    <tr class="bg-slate-100 font-bold text-slate-900">
                        <td class="border border-black px-2 py-1 text-center font-bold">{{ $noB++ }}.</td>
                        <td colspan="4" class="border border-black px-2 py-1 text-left font-bold">
                            Dasar-dasar Program Keahlian
                        </td>
                    </tr>
                    @foreach($subGroupedX['dasar'] as $it)
                    <tr>
                        <td class="border border-black px-2 py-1 text-center font-mono text-slate-400"></td>
                        <td class="border border-black px-2 py-1 text-center font-mono font-bold">{{ $it->mataPelajaran->kode ?? '-' }}</td>
                        <td class="border border-black px-2 py-1 pl-6">
                            <span class="text-black">{{ $it->mataPelajaran->nama ?? '-' }}</span>
                            @if($it->keterangan)
                            <span class="text-[9.5px] block italic text-slate-600">Ket: {{ $it->keterangan }}</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1">
                            @if($it->guru)
                            <span class="font-bold text-black">{{ $it->guru?->name }}</span>
                            @else
                            <span class="italic text-slate-500 font-normal">Belum Ditetapkan</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1 text-center font-bold">{{ $it->alokasi_jam }} JP</td>
                    </tr>
                    @endforeach
                    @endif


                    <tr class="bg-slate-100 font-bold text-slate-900">
                        <td colspan="5" class="border border-black px-4 py-1 text-left italic">
                            2. Mata Pelajaran [Konsentrasi Keahlian]***
                        </td>
                    </tr>
                    @forelse($subGroupedX['konsentrasi'] ?? [] as $it)
                    <tr>
                        <td class="border border-black px-2 py-1 text-center font-mono text-slate-400">&bull;</td>
                        <td class="border border-black px-2 py-1 text-center font-mono font-bold">{{ $it->mataPelajaran->kode ?? '-' }}</td>
                        <td class="border border-black px-2 py-1 pl-6">
                            <span class="font-bold text-black">{{ $it->mataPelajaran->nama ?? '-' }}</span>
                            @if($it->keterangan)
                            <span class="text-[9.5px] block italic text-slate-600">Ket: {{ $it->keterangan }}</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1">
                            @if($it->guru)
                            <span class="font-bold text-black">{{ $it->guru?->name }}</span>
                            @else
                            <span class="italic text-slate-500 font-normal">Belum Ditetapkan</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1 text-center font-bold">{{ $it->alokasi_jam }} JP</td>
                    </tr>
                    @empty
                    <tr>
                        <td class="border border-black px-2 py-1 text-center font-mono text-slate-400">-</td>
                        <td class="border border-black px-2 py-1 text-center font-mono text-slate-400">-</td>
                        <td class="border border-black px-2 py-1 italic text-slate-500">
                            *) Dilaksanakan pada Fase F (Kelas XI & XII) sesuai konsentrasi keahlian
                        </td>
                        <td class="border border-black px-2 py-1 text-center italic text-slate-400">-</td>
                        <td class="border border-black px-2 py-1 text-center font-mono text-slate-400">-</td>
                    </tr>
                    @endforelse

                    <tr class="bg-slate-100 font-bold text-slate-900">
                        <td colspan="5" class="border border-black px-4 py-1 text-left italic">
                            3. Mata Pelajaran Pilihan****
                        </td>
                    </tr>
                    @forelse($subGroupedX['pilihan'] ?? [] as $it)
                    <tr>
                        <td class="border border-black px-2 py-1 text-center font-mono text-slate-400">&bull;</td>
                        <td class="border border-black px-2 py-1 text-center font-mono font-bold">{{ $it->mataPelajaran->kode ?? '-' }}</td>
                        <td class="border border-black px-2 py-1 pl-6">
                            <span class="font-bold text-black">{{ $it->mataPelajaran->nama ?? '-' }}</span>
                            @if($it->keterangan)
                            <span class="text-[9.5px] block italic text-slate-600">Ket: {{ $it->keterangan }}</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1">
                            @if($it->guru)
                            <span class="font-bold text-black">{{ $it->guru?->name }}</span>
                            @else
                            <span class="italic text-slate-500 font-normal">Belum Ditetapkan</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1 text-center font-bold">{{ $it->alokasi_jam }} JP</td>
                    </tr>
                    @empty
                    <tr>
                        <td class="border border-black px-2 py-1 text-center font-mono text-slate-400">-</td>
                        <td class="border border-black px-2 py-1 text-center font-mono text-slate-400">-</td>
                        <td class="border border-black px-2 py-1 italic text-slate-500">
                            **) Dilaksanakan pada Fase F (Kelas XI & XII) sesuai minat & bakat murid
                        </td>
                        <td class="border border-black px-2 py-1 text-center italic text-slate-400">-</td>
                        <td class="border border-black px-2 py-1 text-center font-mono text-slate-400">-</td>
                    </tr>
                    @endforelse

                @else
                    {{-- STRUKTUR RESMI KELAS XI & XII (Fase F: Konsentrasi, PKK, PKL, Pilihan) --}}
                    @php
                        // Kelompokkan Sub-Kategori Sesuai Dokumen Resmi Sekolah
                        $subGrouped = $itemsKejuruan->groupBy(function($item) {
                            $sub = $item->sub_kategori ?? '';
                            if (str_contains($sub, 'Konsentrasi')) return 'konsentrasi';
                            if (str_contains($sub, 'Kewirausahaan') || str_contains($sub, 'PKK')) return 'pkk';
                            if (str_contains($sub, 'Lapangan') || str_contains($sub, 'PKL')) return 'pkl';
                            if (str_contains($sub, 'Pilihan')) return 'pilihan';
                            return 'dasar_umum';
                        });

                        $noB = $isKelasXII ? 6 : 1;
                    @endphp

                    {{-- 1. Umum Kejuruan (Matematika, Bahasa Inggris, Techno Preneur) --}}
                    @foreach($subGrouped['dasar_umum'] ?? [] as $it)
                    <tr>
                        <td class="border border-black px-2 py-1 text-center font-bold">{{ $noB++ }}</td>
                        <td class="border border-black px-2 py-1 text-center font-mono font-bold">{{ $it->mataPelajaran->kode ?? '-' }}</td>
                        <td class="border border-black px-2 py-1">
                            <span class="font-bold text-black">{{ $it->mataPelajaran->nama ?? '-' }}</span>
                            @if($it->keterangan)
                            <span class="text-[9.5px] block italic text-slate-600">Ket: {{ $it->keterangan }}</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1">
                            @if($it->guru)
                            <span class="font-bold text-black">{{ $it->guru?->name }}</span>
                            @else
                            <span class="italic text-slate-500 font-normal">Belum Ditetapkan</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1 text-center font-bold">{{ $it->alokasi_jam }} JP</td>
                    </tr>
                    @endforeach

                    {{-- 2. Mata Pelajaran [Konsentrasi Keahlian]*** --}}
                    @if(isset($subGrouped['konsentrasi']) && $subGrouped['konsentrasi']->isNotEmpty())
                    <tr class="bg-slate-100 font-bold text-slate-900">
                        <td colspan="5" class="border border-black px-4 py-1 text-left italic">
                            {{ $noB++ }}. Mata Pelajaran [Konsentrasi Keahlian]***
                        </td>
                    </tr>
                    @foreach($subGrouped['konsentrasi'] as $it)
                    <tr>
                        <td class="border border-black px-2 py-1 text-center font-mono text-slate-400">&bull;</td>
                        <td class="border border-black px-2 py-1 text-center font-mono font-bold">{{ $it->mataPelajaran->kode ?? '-' }}</td>
                        <td class="border border-black px-2 py-1 pl-6">
                            <span class="font-bold text-black">{{ $it->mataPelajaran->nama ?? '-' }}</span>
                            @if($it->keterangan)
                            <span class="text-[9.5px] block italic text-slate-600">Ket: {{ $it->keterangan }}</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1">
                            @if($it->guru)
                            <span class="font-bold text-black">{{ $it->guru?->name }}</span>
                            @else
                            <span class="italic text-slate-500 font-normal">Belum Ditetapkan</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1 text-center font-bold">{{ $it->alokasi_jam }} JP</td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- 3. Projek Kreatif dan Kewirausahaan --}}
                    @if(isset($subGrouped['pkk']) && $subGrouped['pkk']->isNotEmpty())
                    <tr class="bg-slate-100 font-bold text-slate-900">
                        <td colspan="5" class="border border-black px-4 py-1 text-left italic">
                            {{ $noB++ }}. Projek Kreatif dan Kewirausahaan
                        </td>
                    </tr>
                    @foreach($subGrouped['pkk'] as $it)
                    <tr>
                        <td class="border border-black px-2 py-1 text-center font-mono text-slate-400">&bull;</td>
                        <td class="border border-black px-2 py-1 text-center font-mono font-bold">{{ $it->mataPelajaran->kode ?? '-' }}</td>
                        <td class="border border-black px-2 py-1 pl-6">
                            <span class="font-bold text-black">{{ $it->mataPelajaran->nama ?? '-' }}</span>
                            @if($it->keterangan)
                            <span class="text-[9.5px] block italic text-slate-600">Ket: {{ $it->keterangan }}</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1">
                            @if($it->guru)
                            <span class="font-bold text-black">{{ $it->guru?->name }}</span>
                            @else
                            <span class="italic text-slate-500 font-normal">Belum Ditetapkan</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1 text-center font-bold">{{ $it->alokasi_jam }} JP</td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- 4. Praktik Kerja Lapangan (Khusus Kelas XII) --}}
                    @if(isset($subGrouped['pkl']) && $subGrouped['pkl']->isNotEmpty())
                    <tr class="bg-slate-100 font-bold text-slate-900">
                        <td colspan="5" class="border border-black px-4 py-1 text-left italic">
                            {{ $noB++ }}. Praktik Kerja Lapangan****
                        </td>
                    </tr>
                    @foreach($subGrouped['pkl'] as $it)
                    <tr class="bg-emerald-50/40">
                        <td class="border border-black px-2 py-1 text-center font-mono text-slate-400">&bull;</td>
                        <td class="border border-black px-2 py-1 text-center font-mono font-bold">{{ $it->mataPelajaran->kode ?? '-' }}</td>
                        <td class="border border-black px-2 py-1 pl-6">
                            <span class="font-bold text-black">{{ $it->mataPelajaran->nama ?? '-' }}</span>
                            @if($it->keterangan)
                            <span class="text-[9.5px] block italic text-slate-600">Ket: {{ $it->keterangan }}</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1">
                            @if($it->guru)
                            <span class="font-bold text-black">{{ $it->guru?->name }}</span>
                            @else
                            <span class="italic text-slate-500 font-normal">Belum Ditetapkan</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1 text-center font-bold">{{ $it->alokasi_jam }} JP</td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- 5. Mata Pelajaran Pilihan**** --}}
                    @if(isset($subGrouped['pilihan']) && $subGrouped['pilihan']->isNotEmpty())
                    <tr class="bg-slate-100 font-bold text-slate-900">
                        <td colspan="5" class="border border-black px-4 py-1 text-left italic">
                            {{ $noB++ }}. Mata Pelajaran Pilihan{{ $isKelasXII ? '*****' : '****' }}
                        </td>
                    </tr>
                    @foreach($subGrouped['pilihan'] as $it)
                    <tr>
                        <td class="border border-black px-2 py-1 text-center font-mono text-slate-400">&bull;</td>
                        <td class="border border-black px-2 py-1 text-center font-mono font-bold">{{ $it->mataPelajaran->kode ?? '-' }}</td>
                        <td class="border border-black px-2 py-1 pl-6">
                            <span class="font-bold text-black">{{ $it->mataPelajaran->nama ?? '-' }}</span>
                            @if($it->keterangan)
                            <span class="text-[9.5px] block italic text-slate-600">Ket: {{ $it->keterangan }}</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1">
                            @if($it->guru)
                            <span class="font-bold text-black">{{ $it->guru?->name }}</span>
                            @else
                            <span class="italic text-slate-500 font-normal">Belum Ditetapkan / Team</span>
                            @endif
                        </td>
                        <td class="border border-black px-2 py-1 text-center font-bold">{{ $it->alokasi_jam }} JP</td>
                    </tr>
                    @endforeach
                    @endif

                @endif

                <tr class="bg-slate-50 font-bold text-black">
                    <td colspan="4" class="border border-black px-3 py-1 text-right italic">
                        Jumlah Kelompok Mata Pelajaran Kejuruan (B) :
                    </td>
                    <td class="border border-black px-2 py-1 text-center font-black">
                        {{ $totalJpKejuruan }} JP
                    </td>
                </tr>

            </tbody>
            <tfoot class="bg-slate-200 font-black text-black">
                <tr>
                    <td colspan="4" class="border border-black px-3 py-1.5 text-right uppercase tracking-wider text-xs">
                        JUMLAH TOTAL ALOKASI JAM PELAJARAN (A + B) / MINGGU :
                    </td>
                    <td class="border border-black px-2 py-1.5 text-center text-sm font-black bg-emerald-300 text-slate-950">
                        {{ $totalJpKelas }} JP
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- CATATAN KAKI DOKUMEN (SESUAI DOKUMEN RESMI SEKOLAH) -->
        <div class="mb-4 p-2 bg-slate-50 border border-black text-[9.5px] space-y-0.5">
            <p><strong>Keterangan Kurikulum:</strong></p>
            <p>&bull; Total Mata Pelajaran Terdistribusi: <strong>{{ $itemsKelas->count() }} Mapel</strong> &bull; Total Guru Pengampu: <strong>{{ $itemsKelas->whereNotNull('guru_user_id')->pluck('guru_user_id')->unique()->count() }} Guru</strong> &bull; Alokasi Beban: <strong>{{ $totalJpKelas }} JP / Minggu</strong></p>
            <p>&bull; <strong>***)</strong> Mata Pelajaran Konsentrasi Keahlian diselenggarakan pada Fase F (Kelas XI dan XII) untuk memperdalam kompetensi keahlian.</p>
            <p>&bull; <strong>****)</strong> Mata Pelajaran Pilihan diselenggarakan pada Fase F (Kelas XI dan XII) memberikan keleluasaan peserta didik mendalami minat bakat.</p>
            @if($isKelasXII)
            <p>&bull; <strong>*****)</strong> Praktik Kerja Lapangan (PKL) dilaksanakan terintegrasi dengan pembelajaran berbasis proyek industri (Project Manajemen).</p>
            @endif
        </div>

        <!-- TANDA TANGAN DOKUMEN (SESUAI DATA MASTER PENGATURAN) -->
        <div class="grid grid-cols-2 gap-8 text-[11px] text-black mt-5 pt-1">
            <!-- KIRI: KEPALA SEKOLAH -->
            <div class="text-left space-y-1">
                <p>Mengetahui,</p>
                <p class="font-bold">Kepala SMK Plus Al-Hilal,</p>
                <div class="h-16"></div>
                <p class="font-black underline uppercase">{{ $settings['nama_kepala_sekolah'] ?? 'Mukhammad Mansyur, S.Pt' }}</p>
                <p class="text-[10px] font-mono">NIP: {{ $settings['nip_kepala_sekolah'] ?? '-' }}</p>
            </div>

            <!-- KANAN: TITIMANGSA & WAKA KURIKULUM -->
            <div class="text-left space-y-1 pl-10">
                <p>{{ $settings['titimangsa'] ?? 'Arjawinangun, 1 September 2026' }}</p>
                <p class="font-bold">Wakasek Bidang Kurikulum,</p>
                <div class="h-16"></div>
                <p class="font-black underline uppercase">{{ $settings['nama_waka_kurikulum'] ?? 'Sokid, S.T, M.Kom' }}</p>
                <p class="text-[10px] font-mono">NIP: {{ $settings['nip_waka_kurikulum'] ?? '-' }}</p>
            </div>
        </div>

    </div>

    <!-- PAGE BREAK ANTAR KELAS KETIKA DICETAK BANYAK KELAS -->
    @if(!$loop->last)
    <div class="page-break"></div>
    @endif

    @empty
    <div class="bg-white p-10 max-w-xl mx-auto text-center rounded-2xl shadow border border-slate-200 text-slate-500">
        <p class="font-bold">Tidak ada data kurikulum yang cocok dengan filter yang dipilih.</p>
    </div>
    @endforelse

</body>
</html>
