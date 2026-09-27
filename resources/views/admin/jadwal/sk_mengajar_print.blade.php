<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lampiran SK Mengajar & Tugas Tambahan - {{ \App\Models\PengaturanSekolah::getActiveTahunAjaran() }}</title>
    <!-- Tailwind CSS CDN for Print Layout -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: 215mm 330mm; /* F4 Portrait Size */
            margin: 10mm;
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
                margin: 0;
                padding: 0;
            }
        }
        table {
            page-break-inside: auto;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-900 antialiased p-4">

    <!-- FLOATING ACTION TOOLBAR (NO-PRINT) -->
    <div class="no-print fixed bottom-6 left-1/2 -translate-x-1/2 bg-white/90 backdrop-blur-md px-6 py-4 rounded-2xl shadow-2xl border border-slate-200 flex items-center gap-4 z-50">
        <button onclick="window.print()" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-sm transition shadow-lg shadow-rose-600/20">
            🖨️ Cetak Dokumen
        </button>
        <button onclick="window.close()" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-sm transition">
            Tutup
        </button>
    </div>

    <div class="bg-white w-full max-w-[210mm] mx-auto min-h-screen p-8 shadow-sm">
        <!-- HEADER KOP SURAT (Simplified) -->
        <div class="text-center border-b-4 border-double border-slate-800 pb-4 mb-6">
            <h1 class="text-xl font-bold uppercase tracking-wider">YAYASAN AL HILAL CIREBON</h1>
            <h2 class="text-2xl font-black uppercase tracking-widest text-slate-900 mt-1">{{ \App\Models\PengaturanSekolah::getSetting()->nama_sekolah ?? 'SMK PLUS AL-HILAL' }}</h2>
            <p class="text-sm mt-2 text-slate-600">Alamat: {{ \App\Models\PengaturanSekolah::getSetting()->alamat ?? '-' }}</p>
        </div>

        <div class="text-center mb-8">
            <h3 class="text-lg font-bold uppercase underline">Lampiran SK Pembagian Tugas Mengajar & Tugas Tambahan</h3>
            <p class="text-sm font-bold mt-1">Tahun Pelajaran: {{ $tahun }} | Semester: {{ ucfirst($semester) }}</p>
        </div>

        <table class="w-full border-collapse text-[11px]">
            <thead>
                <tr class="bg-slate-100">
                    <th class="border border-slate-800 px-2 py-2 w-8 text-center">No</th>
                    <th class="border border-slate-800 px-2 py-2">Nama Guru</th>
                    <th class="border border-slate-800 px-2 py-2 w-1/4">Tugas Tambahan</th>
                    <th class="border border-slate-800 px-2 py-2 w-1/3">Mata Pelajaran (Kelas)</th>
                    <th class="border border-slate-800 px-2 py-2 w-16 text-center">Jml Jam</th>
                    <th class="border border-slate-800 px-2 py-2 w-16 text-center">Total Jam</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gurus as $index => $guru)
                    @php
                        $tugasTambahanStr = implode(', ', $guru->tugas_tambahan ?? []);
                        if (empty($tugasTambahanStr)) {
                            $tugasTambahanStr = '-';
                        }
                        
                        $totalJam = $guru->kurikulums->sum('alokasi_jam');
                    @endphp
                    <tr>
                        <td class="border border-slate-800 px-2 py-2 text-center align-top">{{ $index + 1 }}</td>
                        <td class="border border-slate-800 px-2 py-2 font-bold align-top whitespace-nowrap">{{ $guru->name }}</td>
                        <td class="border border-slate-800 px-2 py-2 align-top">{{ $tugasTambahanStr }}</td>
                        
                        <td class="border border-slate-800 px-0 py-0 align-top">
                            @if($guru->kurikulums->count() > 0)
                                <table class="w-full h-full border-none m-0">
                                    @foreach($guru->kurikulums as $k)
                                        <tr class="border-b border-slate-300 last:border-b-0">
                                            <td class="py-1 px-2">{{ $k->mataPelajaran->nama ?? 'Mapel Terhapus' }} ({{ $k->kelas }})</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @else
                                <div class="p-2 text-slate-400 italic">- Tidak Mengajar -</div>
                            @endif
                        </td>
                        
                        <td class="border border-slate-800 px-0 py-0 align-top">
                            @if($guru->kurikulums->count() > 0)
                                <table class="w-full h-full border-none m-0">
                                    @foreach($guru->kurikulums as $k)
                                        <tr class="border-b border-slate-300 last:border-b-0">
                                            <td class="py-1 px-2 text-center">{{ $k->alokasi_jam }} JP</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @else
                                <div class="p-2 text-center text-slate-400 italic">0 JP</div>
                            @endif
                        </td>

                        <td class="border border-slate-800 px-2 py-2 text-center font-bold align-top">{{ $totalJam }} JP</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- TTD Kepala Sekolah -->
        <div class="mt-12 flex justify-end">
            <div class="text-center">
                <p class="text-sm">Mengetahui,</p>
                <p class="text-sm font-bold">Kepala Sekolah</p>
                <div class="h-24"></div> <!-- Space for signature -->
                <p class="text-sm font-bold underline">{{ \App\Models\PengaturanSekolah::getSetting()->kepalaSekolah->name ?? '.....................................' }}</p>
                <p class="text-xs">NIP/NIY: {{ \App\Models\PengaturanSekolah::getSetting()->kepalaSekolah->nip ?? '-' }}</p>
            </div>
        </div>
    </div>
</body>
</html>
