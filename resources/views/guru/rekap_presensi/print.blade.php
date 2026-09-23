<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Presensi Kehadiran Guru - {{ $rekap['guru']->name }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.4; color: #000; padding: 20px; margin: 0; }
        .kop-surat { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 15px; position: relative; }
        .kop-logo { position: absolute; left: 10px; top: 0; width: 70px; height: 70px; object-fit: contain; }
        .kop-header { margin-left: 80px; margin-right: 80px; }
        .kop-header h2 { margin: 0; font-size: 14pt; font-weight: bold; }
        .kop-header h1 { margin: 2px 0; font-size: 16pt; font-weight: bold; }
        .kop-header p { margin: 1px 0; font-size: 9pt; }
        .title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; margin-bottom: 15px; }
        table.identitas { width: 100%; margin-bottom: 12px; font-size: 10.5pt; }
        table.identitas td { padding: 2px 0; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 9.5pt; }
        table.data th, table.data td { border: 1px solid #000; padding: 5px 6px; }
        table.data th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .summary-box { border: 1px solid #000; padding: 8px 12px; margin-bottom: 15px; font-size: 10pt; background-color: #fafafa; }
        .ttd-box { width: 100%; margin-top: 30px; font-size: 10.5pt; }
        .ttd-box td { vertical-align: top; text-align: center; width: 50%; }
        .signature-space { height: 65px; }
        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0b7b4b; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- Kop Surat -->
    <div class="kop-surat">
        <img src="{{ asset('images/logo.png') }}" class="kop-logo" alt="Logo">
        <div class="kop-header">
            <h2>YAYASAN AL-HILAL ARJAWINANGUN</h2>
            <h1>SMK PLUS AL-HILAL ARJAWINANGUN</h1>
            <p>NSS: 322021708001 &bull; NPSN: 20268571 &bull; Terakreditasi "A"</p>
            <p>Jl. Kebon Melati No. 01 Ds. Jungjang Kec. Arjawinangun Kab. Cirebon 45162 &bull; Telp. (0231) 357890</p>
        </div>
    </div>

    <div class="title">REKAPITULASI PRESENSI KEHADIRAN KBM & KEGIATAN SEKOLAH</div>

    <table class="identitas">
        <tr>
            <td width="20%">Nama Guru</td>
            <td width="2%">:</td>
            <td width="38%"><strong>{{ $rekap['guru']->name }}</strong></td>
            <td width="20%">Periode</td>
            <td width="2%">:</td>
            <td width="18%">{{ $bulan ? \Carbon\Carbon::parse($bulan . '-01')->isoFormat('MMMM Y') : "T.A. {$tahunAjaran}" }}</td>
        </tr>
        <tr>
            <td>NIP / Kode Guru</td>
            <td>:</td>
            <td>{{ $rekap['guru']->nip ?: ($rekap['guru']->kode_guru ? 'Kode ' . $rekap['guru']->kode_guru : '-') }}</td>
            <td>Predikat Kehadiran</td>
            <td>:</td>
            <td><strong>{{ $rekap['predikat'] }} ({{ $rekap['persenGabungan'] }}%)</strong></td>
        </tr>
        <tr>
            <td>Jabatan / Mapel</td>
            <td>:</td>
            <td>{{ $rekap['guru']->jabatan_utama ?: ($rekap['guru']->tugas_tambahan_label ?: 'Guru Pengajar') }}</td>
            <td>Total Kehadiran</td>
            <td>:</td>
            <td><strong>{{ $rekap['totalHadirGabungan'] }} / {{ $rekap['totalSesiGabungan'] }} Sesi</strong></td>
        </tr>
    </table>

    <div class="summary-box">
        <strong>RINGKASAN AKUMULASI KEHADIRAN:</strong><br>
        1. <strong>Kehadiran Mengajar KBM:</strong> Hadir Tepat Waktu: {{ $rekap['kbmHadirTepat'] }} &bull; Terlambat: {{ $rekap['kbmTerlambat'] }} &bull; Izin: {{ $rekap['kbmIzin'] }} &bull; Sakit: {{ $rekap['kbmSakit'] }} &bull; Tugas Luar: {{ $rekap['kbmTugasLuar'] }} &bull; <strong>Persentase KBM: {{ $rekap['persenKbm'] }}%</strong><br>
        2. <strong>Kehadiran Kegiatan Sekolah:</strong> Hadir: {{ $rekap['kegiatanHadir'] }} dari {{ $rekap['totalKegiatan'] }} Agenda &bull; Izin: {{ $rekap['kegiatanIzin'] }} &bull; Sakit: {{ $rekap['kegiatanSakit'] }} &bull; <strong>Persentase Kegiatan: {{ $rekap['persenKegiatan'] }}%</strong>
    </div>

    <!-- 1. Tabel Rincian KBM -->
    <div style="font-weight: bold; margin-bottom: 6px; font-size: 10.5pt;">A. RINCIAN PRESENSI MENGAJAR KBM:</div>
    <table class="data">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="15%">Tanggal</th>
                <th width="12%">Jam Masuk</th>
                <th width="35%">Mata Pelajaran & Kelas</th>
                <th width="18%">Status</th>
                <th width="15%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekap['listKbm'] as $idx => $kbm)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($kbm->tanggal)->format('d/m/Y') }}</td>
                <td class="text-center">{{ $kbm->jam_absen }}</td>
                <td>{{ $kbm->jadwal->mataPelajaran->nama ?? 'Mapel' }} (Kelas {{ $kbm->jadwal->kelas ?? '-' }})</td>
                <td class="text-center"><strong>{{ strtoupper($kbm->status_label) }}</strong></td>
                <td>{{ $kbm->catatan ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada catatan log presensi KBM pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 2. Tabel Rincian Kegiatan Sekolah -->
    <div style="font-weight: bold; margin-bottom: 6px; font-size: 10.5pt; margin-top: 15px;">B. RINCIAN KEHADIRAN KEGIATAN RESMI SEKOLAH:</div>
    <table class="data">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="15%">Tanggal</th>
                <th width="20%">Bidang Penyelenggara</th>
                <th width="35%">Nama Agenda Kegiatan</th>
                <th width="15%">Status Hadir</th>
                <th width="10%">Tempat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekap['listKegiatan'] as $idx => $keg)
            @php
                $pk = $keg->presensis->first();
            @endphp
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($keg->tanggal_kegiatan)->format('d/m/Y') }}</td>
                <td>{{ $keg->bidang_label }}</td>
                <td>{{ $keg->judul }}</td>
                <td class="text-center"><strong>{{ $pk ? strtoupper($pk->status_label) : 'BELUM HADIR' }}</strong></td>
                <td class="text-center">{{ $keg->tempat }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada agenda kegiatan sekolah pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd-box">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala SMK Plus Al-Hilal<br>
                <div class="signature-space"></div>
                <strong><u>{{ $settings['nama_kepala_sekolah'] ?? '...' }}</u></strong><br>
                NIP. {{ $settings['nip_kepala_sekolah'] ?? '-' }}
            </td>
            <td>
                {{ $settings['titimangsa'] ?? '...' }}<br>
                Guru Bersangkutan,<br>
                <div class="signature-space"></div>
                <strong><u>{{ $rekap['guru']->name }}</u></strong><br>
                NIP. {{ $rekap['guru']->nip ?: '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
