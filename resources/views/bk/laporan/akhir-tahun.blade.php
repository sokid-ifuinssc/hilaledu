@extends('layouts.app')
@section('title', 'Laporan Akhir Tahun')
@section('subtitle', $tahunAjaran ? 'Tahun Ajaran ' . $tahunAjaran->nama : 'Tidak ada tahun ajaran aktif')

@section('content')
<div class="space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tahun Ajaran</label>
                <select name="tahun_ajaran_id" class="form-select w-56">
                    @foreach($tahunAjarans as $ta)
                    <option value="{{ $ta->id }}" {{ ($tahunAjaran && $tahunAjaran->id == $ta->id) ? 'selected' : '' }}>{{ $ta->nama }} {{ $ta->is_active ? '(Aktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Filter Kelas</label>
                <select name="kelas_id" class="form-select w-48">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary">Tampilkan</button>
        </form>
        <a href="{{ route('bk.laporan.cetak', array_merge(['jenis' => 'akhir_tahun'], request()->only('tahun_ajaran_id', 'kelas_id'))) }}" target="_blank" class="btn-secondary">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>Cetak Laporan
        </a>
    </div>

    @if(!$tahunAjaran)
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-700">
        ⚠️ Belum ada tahun ajaran. Silahkan hubungi admin.
    </div>
    @else
    <div class="stat-card bg-indigo-50 border-indigo-200">
        <p class="text-sm text-indigo-600 font-medium">Tahun Ajaran</p>
        <p class="text-lg font-bold text-indigo-800">{{ $tahunAjaran->nama }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ $tahunAjaran->tanggal_mulai?->format('d M Y') }} - {{ $tahunAjaran->tanggal_selesai?->format('d M Y') }}</p>
    </div>
    @endif

    {{-- Statistik Utama --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat-card"><p class="text-sm text-gray-500">Total Pelanggaran</p><p class="text-2xl font-bold text-red-600">{{ $pelanggarans->count() }}</p></div>
        <div class="stat-card"><p class="text-sm text-gray-500">Total Poin Dikurangi</p><p class="text-2xl font-bold text-orange-600">{{ $pelanggarans->sum('poin') }}</p></div>
        <div class="stat-card"><p class="text-sm text-gray-500">Siswa Terlibat</p><p class="text-2xl font-bold text-indigo-600">{{ $pelanggarans->pluck('siswa_id')->unique()->count() }}</p></div>
        <div class="stat-card"><p class="text-sm text-gray-500">Selesai Ditangani</p><p class="text-2xl font-bold text-green-600">{{ $pelanggarans->where('status', 'selesai')->count() }}</p></div>
    </div>

    {{-- Statistik per Kategori --}}
    @if($statistikKategori->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h4 class="text-sm font-semibold text-gray-800 mb-3">Rekap per Kategori Pelanggaran</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($statistikKategori as $kategori => $stat)
            <div class="rounded-lg p-3 border" style="background-color: {{ $stat['warna'] }}10; border-color: {{ $stat['warna'] }}30;">
                <p class="text-xs font-medium" style="color: {{ $stat['warna'] }}">{{ $kategori }}</p>
                <p class="text-lg font-bold text-gray-800">{{ $stat['jumlah'] }} kasus</p>
                <p class="text-xs text-gray-500">{{ $stat['poin'] }} poin</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Rekap per Bulan --}}
    @if($rekapPerBulan->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h4 class="text-sm font-semibold text-gray-800 mb-3">Tren Pelanggaran per Bulan</h4>
        <div class="table-container">
            <table>
                <thead><tr><th>Bulan</th><th>Jumlah Pelanggaran</th><th>Total Poin</th></tr></thead>
                <tbody>
                @foreach($rekapPerBulan as $stat)
                <tr>
                    <td class="font-medium">{{ $stat['label'] }}</td>
                    <td><span class="text-red-600 font-semibold">{{ $stat['jumlah'] }}</span></td>
                    <td><span class="text-orange-600 font-semibold">{{ $stat['poin'] }}</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Rekap per Kelas --}}
    @if($rekapPerKelas->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h4 class="text-sm font-semibold text-gray-800 mb-3">Rekap per Kelas</h4>
        <div class="table-container">
            <table>
                <thead><tr><th>Kelas</th><th>Jumlah Pelanggaran</th><th>Total Poin</th><th>Siswa Terlibat</th><th>Selesai</th></tr></thead>
                <tbody>
                @foreach($rekapPerKelas as $kelas => $stat)
                <tr>
                    <td class="font-medium">{{ $kelas }}</td>
                    <td><span class="text-red-600 font-semibold">{{ $stat['jumlah'] }}</span></td>
                    <td><span class="text-orange-600 font-semibold">{{ $stat['poin'] }}</span></td>
                    <td>{{ $stat['siswa'] }}</td>
                    <td><span class="text-green-600 font-semibold">{{ $stat['selesai'] }}</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Detail Pelanggaran --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h4 class="text-sm font-semibold text-gray-800 mb-3">Detail Pelanggaran ({{ $pelanggarans->count() }} data)</h4>
        <div class="table-container">
            <table>
                <thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Pelanggaran</th><th>Poin</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($pelanggarans as $p)
                    <tr>
                        <td>{{ $p->tanggal_pelanggaran->format('d/m/Y') }}</td>
                        <td class="font-medium">{{ $p->siswa->nama_lengkap }}</td>
                        <td>{{ $p->siswa?->kelas?->nama ?? '-' }}</td>
                        <td>{{ $p->jenisPelanggaran->nama }}</td>
                        <td><span class="text-red-600 font-semibold">-{{ $p->poin }}</span></td>
                        <td><span class="badge {{ $p->status_badge }}">{{ $p->status_label }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-8 text-gray-500">Tidak ada data pelanggaran</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #fce7f3; color: #db2777;">📋</span> Laporan Akhir Tahun</h3>
    <p>Rekapitulasi seluruh pelanggaran dalam satu tahun ajaran. Laporan ini ditujukan untuk pimpinan sekolah.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Pilih <strong>tahun ajaran</strong> yang diinginkan</li>
        <li>Gunakan <strong>filter kelas</strong> jika ingin melihat kelas tertentu</li>
        <li>Lihat <strong>tren per bulan</strong>, <strong>rekap per kelas</strong>, dan <strong>kategori</strong></li>
        <li>Klik <strong>"Cetak Laporan"</strong> untuk dicetak dan dilaporkan ke pimpinan</li>
    </ol>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Laporan akhir tahun memberikan evaluasi menyeluruh yang bisa menjadi bahan pertimbangan kebijakan sekolah.</p>
</div>
@endsection
