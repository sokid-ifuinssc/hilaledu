<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelajaran Resmi - {{ $settings['nama_sekolah'] ?? 'SMK Plus Al-Hilal' }}</title>
    <!-- Tailwind CSS CDN for Print Layout -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4 landscape;
            margin: 6mm;
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
            }
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-900 antialiased p-4">

    <!-- FLOATING ACTION TOOLBAR (NO-PRINT) -->
    <div class="no-print max-w-6xl mx-auto mb-4 bg-white p-4 rounded-2xl shadow-lg border border-slate-300">
        <div class="flex items-center justify-between gap-3 mb-2">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.jadwal.matrix', request()->query()) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold rounded-xl text-xs transition">
                    &larr; Kembali ke Tampilan Layar
                </a>
                <span class="text-xs font-semibold text-slate-600">Dokumen Cetak Format Resmi Landscape A4</span>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-md transition flex items-center gap-2 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                        <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/>
                    </svg>
                    <span>Cetak Sekarang (Print / PDF)</span>
                </button>
            </div>
        </div>

        <!-- QUICK FILTER BUTTONS -->
        <div class="pt-2 border-t border-slate-200 flex flex-wrap items-center gap-1.5 text-xs">
            <span class="text-slate-500 font-bold mr-1">Pilihan Matriks yang Dicetak:</span>
            <a href="{{ route('admin.jadwal.matrix.print', ['jenjang' => 'X']) }}"
               class="px-2.5 py-1 rounded-lg font-black transition {{ ($jenjang ?? 'all') === 'X' && ($jurusan ?? 'all') === 'all' ? 'bg-emerald-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                Kelas X (3 Jurusan)
            </a>
            <span class="text-slate-300">|</span>
            <a href="{{ route('admin.jadwal.matrix.print', ['jurusan' => 'AKL']) }}"
               class="px-2.5 py-1 rounded-lg font-bold transition {{ ($jurusan ?? 'all') === 'AKL' ? 'bg-red-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                Jurusan AKL (PRB)
            </a>
            <a href="{{ route('admin.jadwal.matrix.print', ['jurusan' => 'TKJT']) }}"
               class="px-2.5 py-1 rounded-lg font-bold transition {{ ($jurusan ?? 'all') === 'TKJT' ? 'bg-emerald-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                Jurusan TKJT (TKJ)
            </a>
            <a href="{{ route('admin.jadwal.matrix.print', ['jurusan' => 'TO']) }}"
               class="px-2.5 py-1 rounded-lg font-bold transition {{ ($jurusan ?? 'all') === 'TO' ? 'bg-yellow-500 text-slate-900 font-black' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                Jurusan TO (TKR)
            </a>
            <span class="text-slate-300">|</span>
            <a href="{{ route('admin.jadwal.matrix.print') }}"
               class="px-2.5 py-1 rounded-lg font-bold transition {{ ($jenjang ?? 'all') === 'all' && ($jurusan ?? 'all') === 'all' ? 'bg-slate-800 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                Semua 9 Rombel
            </a>
        </div>
    </div>

    <!-- MAIN PRINT CONTAINER -->
    <div class="bg-white p-2 mx-auto max-w-[1300px] text-[10px] leading-tight text-black">

        <!-- KOP DOKUMEN (DENGAN LOGO MASTER SEKOLAH) -->
        <div class="flex items-center justify-between gap-4 mb-3 border-b border-black pb-2">
            <div class="w-14 h-14 flex-shrink-0 flex items-center justify-center">
                <img src="{{ asset($settings['logo_sekolah'] ?? 'images/logo.png') }}" class="w-14 h-14 object-contain" alt="Logo Sekolah" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
            </div>
            <div class="text-center flex-1 space-y-0.5">
                <h1 class="text-lg font-black tracking-wider uppercase text-black">JADWAL PELAJARAN</h1>
                <h2 class="text-sm font-black uppercase text-black">TAHUN PELAJARAN {{ $settings['tahun_pelajaran'] ?? '2025 – 2026' }}</h2>
                <p class="text-[9.5px] font-bold text-black">
                    Program Keahlian : {{ $settings['program_keahlian'] ?? 'Teknik Jaringan Komputer dan Telekomunikasi, Teknik Otomotif, Akuntansi dan Keuangan Lembaga' }}
                </p>
                <p class="text-[9.5px] font-bold text-black">
                    Konsentrasi Keahlian : {{ $settings['konsentrasi_keahlian'] ?? 'Teknik Komputer dan Jaringan, Teknik Kendaraan Ringan, Layanan Perbankan' }}
                </p>
            </div>
            <div class="w-14 h-14 flex-shrink-0 flex items-center justify-center">
                <img src="{{ asset($settings['logo_sekolah'] ?? 'images/logo.png') }}" class="w-14 h-14 object-contain" alt="Logo Sekolah" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
            </div>
        </div>

        <!-- GRID 6 HARI (2 BARIS x 3 KOLOM) -->
        <div class="space-y-3">
            <!-- BARIS 1 -->
            <div class="grid grid-cols-3 gap-2">
                @foreach(['Senin', 'Selasa', 'Rabu'] as $hari)
                @include('admin.jadwal.partials.day_matrix_card', [
                    'hari' => $hari, 
                    'dataHari' => $matrixData['matrix'][$hari] ?? [],
                    'kelasList' => $matrixData['kelasList']
                ])
                @endforeach
            </div>

            <!-- BARIS 2 -->
            <div class="grid grid-cols-3 gap-2">
                @foreach(['Kamis', 'Jumat', 'Sabtu'] as $hari)
                @include('admin.jadwal.partials.day_matrix_card', [
                    'hari' => $hari, 
                    'dataHari' => $matrixData['matrix'][$hari] ?? [],
                    'kelasList' => $matrixData['kelasList']
                ])
                @endforeach
            </div>
        </div>

        <!-- LEGENDA GURU RESMI & KODE MENGAJAR (DINAMIS DIBAGI 3 KOLOM) -->
        <div class="mt-3 border-t border-black pt-2">
            <div class="grid grid-cols-3 gap-1.5 text-[8.5px]">

                @php
                    $totalGuru = count($guruList);
                    $perColumn = ceil($totalGuru / 3);
                    $guruChunks = array_chunk($guruList, $perColumn > 0 ? $perColumn : 1, true);
                @endphp

                @foreach($guruChunks as $chunkIndex => $chunk)
                <table class="w-full border-collapse border border-black self-start">
                    <thead class="bg-slate-100 font-black text-center text-black">
                        <tr>
                            <th class="border border-black px-1.5 py-0.5 text-center w-[44%]">NAMA</th>
                            <th class="border border-black px-0.5 py-0.5 text-center w-[12%]">KODE</th>
                            <th class="border border-black px-1 py-0.5 text-center w-[22%]">MAPEL</th>
                            <th class="border border-black px-1 py-0.5 text-center w-[22%]">JABATAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chunk as $kode => $g)
                        @php 
                            $badgeClass = isset($g['color']) ? $g['color'] . ' font-black' : 'bg-white text-black font-bold';
                        @endphp
                        <tr>
                            <td class="border border-black px-1.5 py-0.5 font-bold truncate max-w-[95px]">{{ $g['nama'] ?? '-' }}</td>
                            <td class="border border-black px-0.5 py-0.5 text-center {{ $badgeClass }}">{{ $kode }}</td>
                            <td class="border border-black px-1 py-0.5 text-center font-semibold">{{ $g['mapel'] ?? '-' }}</td>
                            <td class="border border-black px-1 py-0.5 font-bold text-center text-[7.5px] uppercase truncate max-w-[65px]">{{ $g['jabatan'] ?? '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endforeach

            </div>
        </div>

        <!-- BAGIAN PALING BAWAH: TTD KEPSEK, CATATAN JUMAT, & TTD WAKA KURIKULUM (SESUAI DATA MASTER) -->
        <div class="mt-3 pt-2 border-t border-black grid grid-cols-12 gap-3 items-start text-[9.5px]">

            <!-- TTD KEPALA SEKOLAH (KIRI) -->
            <div class="col-span-5 text-center space-y-0.5">
                <p class="font-semibold">Mengetahui,</p>
                <p class="font-black leading-tight">Kepala {{ $settings['nama_sekolah'] ?? 'SMK Plus Al-Hilal Arjawinangun' }}</p>
                <div class="h-14 flex items-center justify-center">
                    <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">[ Tanda Tangan & Cap ]</span>
                </div>
                <p class="font-black underline underline-offset-2 text-[10px] uppercase">{{ $settings['nama_kepala_sekolah'] ?? 'Mukhammad Mansyur, S.Pt' }}</p>
                @if(!empty($settings['nip_kepala_sekolah']))
                <p class="text-[8.5px] font-bold">NIP. {{ $settings['nip_kepala_sekolah'] }}</p>
                @endif
            </div>

            <!-- CATATAN KHUSUS HARI JUMAT (TENGAH) -->
            <div class="col-span-2 flex flex-col items-center justify-center pt-1">
                <div class="border border-black p-1.5 text-[8px] leading-tight text-center w-full">
                    <div class="font-black mb-0.5">*) Untuk Hari Jumat:</div>
                    <p>Semua jam dikurangi 15 menit, jam istirahat setelah jam ke-4 (30 menit), dan setelah jam ke-6 pulang.</p>
                </div>
            </div>

            <!-- TITIMANGSA & TTD WAKA KURIKULUM (KANAN) -->
            <div class="col-span-5 text-center space-y-0.5">
                <div class="text-[9px] font-bold mb-0.5">
                    <div class="text-[8px] font-bold uppercase tracking-wider">BERLAKU MULAI 1 SEPTEMBER 2026</div>
                    <div class="font-black">{{ $settings['titimangsa'] ?? 'Arjawinangun, 1 September 2026' }}</div>
                </div>
                <p class="font-black leading-tight">Waka Kurikulum,</p>
                <div class="h-12 flex items-center justify-center">
                    <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">[ Tanda Tangan ]</span>
                </div>
                <p class="font-black underline underline-offset-2 text-[10px] uppercase">{{ $settings['nama_waka_kurikulum'] ?? 'Sokid, S.T, M.Kom' }}</p>
                @if(!empty($settings['nip_waka_kurikulum']))
                <p class="text-[8.5px] font-bold">NIP. {{ $settings['nip_waka_kurikulum'] }}</p>
                @else
                <p class="text-[8.5px] font-bold">NIP. -</p>
                @endif
            </div>

        </div>

    </div>

</body>
</html>
