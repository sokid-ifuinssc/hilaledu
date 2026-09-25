<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak {{ $judul ?? 'Laporan' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5; color: #000; padding: 20px; }

        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 15px; margin-bottom: 20px; }
        .header img { width: 60px; height: 60px; vertical-align: middle; margin-right: 10px; }
        .header-text { display: inline-block; vertical-align: middle; text-align: center; }
        .header h1 { font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .header h2 { font-size: 14pt; font-weight: bold; }
        .header p { font-size: 10pt; }

        .title { text-align: center; margin: 20px 0; }
        .title h3 { font-size: 14pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; }
        .title p { font-size: 11pt; margin-top: 5px; }

        .info-box { background: #f5f5f5; border: 1px solid #ddd; padding: 10px 15px; margin: 15px 0; border-radius: 4px; }
        .info-box p { font-size: 11pt; margin: 2px 0; }
        .info-box strong { display: inline-block; min-width: 150px; }

        .stats { display: flex; gap: 15px; margin: 15px 0; flex-wrap: wrap; }
        .stat-item { flex: 1; min-width: 120px; border: 1px solid #ddd; padding: 10px; text-align: center; border-radius: 4px; }
        .stat-item .value { font-size: 18pt; font-weight: bold; }
        .stat-item .label { font-size: 9pt; color: #666; }

        table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 10pt; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background: #e5e5e5; font-weight: bold; font-size: 9pt; text-transform: uppercase; }
        td { vertical-align: top; }
        tr:nth-child(even) { background: #fafafa; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-red { color: #dc2626; }
        .text-green { color: #16a34a; }

        .footer { margin-top: 40px; display: flex; justify-content: space-between; }
        .footer .sign-block { text-align: center; width: 45%; }
        .footer .sign-block .line { margin-top: 60px; border-top: 1px solid #000; display: inline-block; min-width: 200px; }

        .section-title { font-size: 12pt; font-weight: bold; margin: 20px 0 10px 0; border-bottom: 1px solid #ccc; padding-bottom: 5px; }

        .no-print { margin: 20px 0; text-align: center; }
        .no-print button { background: #4f46e5; color: white; border: none; padding: 10px 30px; font-size: 14px; border-radius: 8px; cursor: pointer; }
        .no-print button:hover { background: #4338ca; }

        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            @page { margin: 1.5cm; size: A4; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak Laporan</button>
        <button onclick="window.close()" style="background:#6b7280;margin-left:10px;">✕ Tutup</button>
    </div>

    @php $settings = \App\Models\PengaturanSekolah::getAllSettings(); @endphp
    @include('components.kop-surat', ['settings' => $settings])

    {{-- Judul Laporan --}}
    <div class="title">
        <h3>{{ $judul ?? 'Laporan Pelanggaran' }}</h3>
        <p>Periode: {{ $periode ?? '-' }}</p>
    </div>

    {{-- Info --}}
    <div class="info-box">
        <p><strong>Tanggal Cetak:</strong> {{ now()->translatedFormat('l, d F Y H:i') }}</p>
        <p><strong>Dicetak oleh:</strong> {{ auth()->user()->nama_lengkap ?? '-' }}</p>
    </div>

    {{-- Statistik Ringkasan --}}
    <div class="stats">
        <div class="stat-item">
            <div class="value text-red">{{ $pelanggarans->count() }}</div>
            <div class="label">Total Pelanggaran</div>
        </div>
        <div class="stat-item">
            <div class="value">{{ $pelanggarans->sum('poin') }}</div>
            <div class="label">Total Poin</div>
        </div>
        <div class="stat-item">
            <div class="value">{{ $pelanggarans->pluck('siswa_id')->unique()->count() }}</div>
            <div class="label">Siswa Terlibat</div>
        </div>
        <div class="stat-item">
            <div class="value text-green">{{ $pelanggarans->where('status', 'selesai')->count() }}</div>
            <div class="label">Selesai Ditangani</div>
        </div>
    </div>

    

    {{-- Rekap per Kelas (jika ada) --}}
    @if(isset($rekapPerKelas) && $rekapPerKelas->isNotEmpty())
    <p class="section-title">Rekap per Kelas</p>
    <table>
        <thead><tr><th>Kelas</th><th class="text-center">Jumlah</th><th class="text-center">Total Poin</th><th class="text-center">Siswa</th><th class="text-center">Selesai</th></tr></thead>
        <tbody>
        @foreach($rekapPerKelas as $kelas => $stat)
        <tr>
            <td>{{ $kelas }}</td>
            <td class="text-center font-bold">{{ $stat['jumlah'] }}</td>
            <td class="text-center">{{ $stat['poin'] }}</td>
            <td class="text-center">{{ $stat['siswa'] }}</td>
            <td class="text-center">{{ $stat['selesai'] }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif

    {{-- Detail Pelanggaran --}}
    <p class="section-title">Detail Pelanggaran</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Pelanggaran</th>
                
                <th class="text-center">Poin</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pelanggarans as $i => $p)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $p->tanggal_pelanggaran->format('d/m/Y') }}</td>
                <td>{{ $p->siswa->nama_lengkap }}</td>
                <td>{{ $p->siswa?->kelas?->nama ?? '-' }}</td>
                <td>{{ $p->jenisPelanggaran->nama }}</td>
                
                <td class="text-center text-red font-bold">-{{ $p->poin }}</td>
                <td>{{ $p->status_label }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center">Tidak ada data pelanggaran</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Tanda Tangan --}}
    <div class="footer">
        <div class="sign-block">
            <p>Mengetahui,</p>
            <p>Kepala SMK Plus Al Hilal</p>
            <div class="line"></div>
            <p>NIP. ........................</p>
        </div>
        <div class="sign-block">
            <p>Sumedang, {{ now()->translatedFormat('d F Y') }}</p>
            <p>Guru BK</p>
            <div class="line"></div>
            <p>{{ auth()->user()->nama_lengkap ?? '........................' }}</p>
        </div>
    </div>
</body>
</html>
