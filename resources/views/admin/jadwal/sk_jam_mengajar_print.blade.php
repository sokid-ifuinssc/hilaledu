<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lampiran SK Jam Mengajar Guru - {{ $tahun }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page { size: 215mm 330mm; margin: 10mm; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; margin: 0; padding: 0; }
            .sheet { box-shadow: none !important; margin: 0 !important; min-height: auto !important; }
        }
        .sheet { page-break-after: always; }
        .sheet:last-of-type { page-break-after: auto; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-900 antialiased p-4">

    <div class="no-print fixed bottom-6 left-1/2 -translate-x-1/2 bg-white/90 backdrop-blur-md px-6 py-4 rounded-2xl shadow-2xl border border-slate-200 flex items-center gap-4 z-50">
        <button onclick="window.print()" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-sm transition shadow-lg shadow-rose-600/20">🖨️ Cetak Dokumen</button>
        <button onclick="window.close()" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-sm transition">Tutup</button>
    </div>

    @php
        $setting = \App\Models\PengaturanSekolah::getSetting();
        $namaKepsek = !empty($setting->kepala_sekolah) && is_string($setting->kepala_sekolah) ? $setting->kepala_sekolah : 'Muhammad Mansyur, S.Pt';
        $nipKepsek = !empty($setting->nip_kepala_sekolah) && is_string($setting->nip_kepala_sekolah) && $setting->nip_kepala_sekolah !== '-' ? $setting->nip_kepala_sekolah : '6942767668130350';
    @endphp

    @forelse($gurus as $guru)
        @php
            $kurikulums = $guru->kurikulums;
            $totalJam = $kurikulums->sum('alokasi_jam');
        @endphp
        <div class="sheet bg-white w-full max-w-[210mm] mx-auto p-8 shadow-sm mb-6">
            <div class="text-center border-b-4 border-double border-slate-800 pb-4 mb-6">
                <h1 class="text-xl font-bold uppercase tracking-wider">YAYASAN AL HILAL CIREBON</h1>
                <h2 class="text-2xl font-black uppercase tracking-widest text-slate-900 mt-1">{{ $setting->nama_sekolah ?? 'SMK PLUS AL-HILAL' }}</h2>
                <p class="text-sm mt-2 text-slate-600">Alamat: {{ $setting->alamat ?? '-' }}</p>
            </div>

            <div class="text-center mb-6">
                <h3 class="text-lg font-bold uppercase underline">Lampiran SK Pembagian Tugas Jam Mengajar Guru</h3>
                <p class="text-sm font-bold mt-1">Tahun Pelajaran: {{ $tahun }} | Semester: {{ ucfirst($semester) }}</p>
            </div>

            <table class="text-sm mb-4">
                <tr><td class="pr-4 py-0.5">Nama Guru</td><td class="pr-2">:</td><td class="font-bold">{{ $guru->name }}</td></tr>
                @if(!empty($guru->nip))
                <tr><td class="pr-4 py-0.5">NUPTK</td><td class="pr-2">:</td><td>{{ $guru->nip }}</td></tr>
                @endif
            </table>

            <table class="w-full border-collapse text-[12px]">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="border border-slate-800 px-2 py-2 w-10 text-center">No</th>
                        <th class="border border-slate-800 px-2 py-2">Mata Pelajaran</th>
                        <th class="border border-slate-800 px-2 py-2 w-28 text-center">Kelas</th>
                        <th class="border border-slate-800 px-2 py-2 w-28 text-center">Jumlah Jam</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kurikulums as $i => $k)
                        <tr>
                            <td class="border border-slate-800 px-2 py-1.5 text-center">{{ $i + 1 }}</td>
                            <td class="border border-slate-800 px-2 py-1.5">{{ $k->mataPelajaran->nama ?? 'Mapel Terhapus' }}</td>
                            <td class="border border-slate-800 px-2 py-1.5 text-center">{{ $k->kelas }}</td>
                            <td class="border border-slate-800 px-2 py-1.5 text-center">{{ $k->alokasi_jam }} JP</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="border border-slate-800 px-2 py-3 text-center italic text-slate-500">- Tidak Mengajar -</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 font-bold">
                        <td colspan="3" class="border border-slate-800 px-2 py-2 text-right">Total Jam Mengajar</td>
                        <td class="border border-slate-800 px-2 py-2 text-center">{{ $totalJam }} JP</td>
                    </tr>
                </tfoot>
            </table>

            <div class="mt-12 flex justify-end">
                <div class="text-center min-w-[200px]">
                    <p class="text-sm">Mengetahui,</p>
                    <p class="text-sm font-bold">Kepala Sekolah</p>
                    <div class="h-24"></div>
                    <p class="text-sm font-bold underline">{{ $namaKepsek }}</p>
                    <p class="text-xs">NUPTK: {{ $nipKepsek }}</p>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white max-w-[210mm] mx-auto p-8 text-center text-slate-500">Belum ada data guru.</div>
    @endforelse
</body>
</html>
