@extends('layouts.app')
@section('title', 'Progres Data Pelanggaran')
@section('subtitle', 'Pelanggaran siswa jurusan yang membutuhkan aksi Anda')

@section('content')
<div class="space-y-6">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200">
            <p class="text-xs text-yellow-600 font-medium">Perlu Approve</p>
            <p class="text-2xl font-bold text-yellow-800">{{ $stats['perlu_approve'] }}</p>
        </div>
        <div class="bg-purple-50 rounded-xl p-4 border border-purple-200">
            <p class="text-xs text-purple-600 font-medium">Perlu Laporan</p>
            <p class="text-2xl font-bold text-purple-800">{{ $stats['perlu_laporan'] }}</p>
        </div>
        <div class="bg-green-50 rounded-xl p-4 border border-green-200">
            <p class="text-xs text-green-600 font-medium">Selesai</p>
            <p class="text-2xl font-bold text-green-800">{{ $stats['selesai'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs text-gray-500">Status</label>
                <select name="status" class="form-select text-sm">
                    <option value="">Semua</option>
                    <option value="menunggu_approval" {{ request('status') == 'menunggu_approval' ? 'selected' : '' }}>Menunggu Approval</option>
                    <option value="menunggu_cetak_surat" {{ request('status') == 'menunggu_cetak_surat' ? 'selected' : '' }}>Menunggu Cetak Surat</option>
                    <option value="menunggu_laporan" {{ request('status') == 'menunggu_laporan' ? 'selected' : '' }}>Menunggu Laporan</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <button type="submit" class="btn-primary text-sm">Filter</button>
            <a href="{{ route('kaprog.progres.index') }}" class="btn-secondary text-sm">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500">Siswa</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500">Kelas</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500">Pelanggaran</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500">Jenis Tindakan</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500">Aksi Anda</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500">Status</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                @forelse($progresList as $progres)
                    @php $p = $progres->pelanggaran; @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4">
                            <p class="font-medium text-gray-800">{{ $p->siswa->nama_lengkap ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ $p->siswa->nis ?? '-' }}</p>
                        </td>
                        <td class="py-3 px-4 text-gray-600">{{ $p->siswa?->kelas?->nama ?? '-' }}</td>
                        <td class="py-3 px-4">
                            <p class="text-gray-800">{{ $p->jenisPelanggaran->nama ?? '-' }}</p>
                            <p class="text-xs text-red-600 font-semibold">-{{ $p->poin }} poin</p>
                        </td>
                        <td class="py-3 px-4"><span class="badge text-xs {{ $progres->jenis_tindakan_badge }}">{{ $progres->jenis_tindakan_label }}</span></td>
                        <td class="py-3 px-4">
                            @if($progres->requiresApprovalFrom('kaprog') && $progres->approval_kaprog === 'belum')
                                <span class="badge text-xs bg-yellow-100 text-yellow-800">⏳ Perlu Approve</span>
                            @elseif($progres->requiresLaporanFrom('kaprog') && empty($progres->laporan_kaprog))
                                <span class="badge text-xs bg-purple-100 text-purple-800">📝 Perlu Laporan</span>
                            @else
                                <span class="badge text-xs bg-green-100 text-green-800">✓ Selesai</span>
                            @endif
                        </td>
                        <td class="py-3 px-4"><span class="badge text-xs {{ $progres->status_badge }}">{{ $progres->status_label }}</span></td>
                        <td class="py-3 px-4"><a href="{{ route('kaprog.progres.show', $p) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Detail →</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-8 text-gray-400">Tidak ada data progres</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $progresList->links() }}</div>
    </div>
</div>
@endsection
