@extends('layouts.app')
@section('title', 'Persetujuan Rekomendasi')
@section('subtitle', 'Setujui atau tolak rekomendasi dari BK dan Kaprog')

@section('content')
<div class="space-y-5">
    @php $prefix = auth()->user()->isWakaKesiswaan() ? 'waka' : 'kepsek'; @endphp
    <div class="flex gap-2">
        <a href="{{ route("{$prefix}.approval.index", ['tab' => 'menunggu']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $tab == 'menunggu' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border' }}">
            Menunggu
        </a>
        <a href="{{ route("{$prefix}.approval.index", ['tab' => 'disetujui']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $tab == 'disetujui' ? 'bg-green-600 text-white' : 'bg-white text-gray-600 border' }}">
            Disetujui
        </a>
        <a href="{{ route("{$prefix}.approval.index", ['tab' => 'ditolak']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $tab == 'ditolak' ? 'bg-red-600 text-white' : 'bg-white text-gray-600 border' }}">
            Ditolak
        </a>
    </div>

    {{-- Rekomendasi BK --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h4 class="text-sm font-semibold text-gray-800 mb-3">Rekomendasi Guru BK ({{ $rekomendasiBK->count() }})</h4>
        <div class="table-container"><table><thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Jenis</th><th>Guru BK</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        @forelse($rekomendasiBK as $r)
        <tr>
            <td>{{ $r->tanggal_rekomendasi?->format('d/m/Y') }}</td>
            <td class="font-medium">{{ $r->pelanggaran->siswa->nama_lengkap }}</td>
            <td>{{ $r->pelanggaran->siswa?->kelas?->nama ?? '-' }}</td>
            <td>{{ $r->jenis_rekomendasi_label }}</td>
            <td>{{ $r->guruBK->nama_lengkap }}</td>
            <td><span class="badge {{ $r->status_approval_badge }}">{{ $r->status_approval_label }}</span></td>
            <td><a href="{{ route("{$prefix}.approval.show-bk", $r) }}" class="text-indigo-600 hover:underline text-sm">Detail</a></td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-6 text-gray-500">Tidak ada data</td></tr>
        @endforelse
        </tbody></table></div>
    </div>

    {{-- Rekomendasi Kaprog --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h4 class="text-sm font-semibold text-gray-800 mb-3">Rekomendasi Kaprog ({{ $rekomendasiKaprog->count() }})</h4>
        <div class="table-container"><table><thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Jenis</th><th>Kaprog</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        @forelse($rekomendasiKaprog as $r)
        <tr>
            <td>{{ $r->tanggal_rekomendasi?->format('d/m/Y') }}</td>
            <td class="font-medium">{{ $r->pelanggaran->siswa->nama_lengkap }}</td>
            <td>{{ $r->pelanggaran->siswa?->kelas?->nama ?? '-' }}</td>
            <td>{{ $r->jenis_rekomendasi_label }}</td>
            <td>{{ $r->kaprog->nama_lengkap }}</td>
            <td><span class="badge {{ $r->status_approval_badge }}">{{ $r->status_approval_label }}</span></td>
            <td><a href="{{ route("{$prefix}.approval.show-kaprog", $r) }}" class="text-indigo-600 hover:underline text-sm">Detail</a></td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-6 text-gray-500">Tidak ada data</td></tr>
        @endforelse
        </tbody></table></div>
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #fef3c7; color: #d97706;">✅</span> Persetujuan Rekomendasi</h3>
    <p>Halaman ini menampilkan rekomendasi dari Guru BK dan Kaprog yang perlu disetujui. Setelah disetujui, kasus akan dikembalikan ke BK untuk ditutup.</p>
</div>
@endsection
