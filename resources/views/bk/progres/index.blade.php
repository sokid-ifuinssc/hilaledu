@extends('layouts.app')
@section('title', 'Progres Data Pelanggaran')
@section('subtitle', 'Monitoring progres tindakan pelanggaran')

@section('content')
<div class="space-y-6">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200">
            <p class="text-xs text-yellow-600 font-medium">Menunggu Approval</p>
            <p class="text-2xl font-bold text-yellow-800">{{ $stats['menunggu_approval'] }}</p>
        </div>
        <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
            <p class="text-xs text-blue-600 font-medium">Menunggu Cetak Surat</p>
            <p class="text-2xl font-bold text-blue-800">{{ $stats['menunggu_cetak_surat'] }}</p>
        </div>
        <div class="bg-purple-50 rounded-xl p-4 border border-purple-200">
            <p class="text-xs text-purple-600 font-medium">Menunggu Laporan</p>
            <p class="text-2xl font-bold text-purple-800">{{ $stats['menunggu_laporan'] }}</p>
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
                <label class="text-xs text-gray-500">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama/NIS siswa..." class="form-input text-sm">
            </div>
            <div>
                <label class="text-xs text-gray-500">Status</label>
                <select name="status" class="form-select text-sm">
                    <option value="">Semua Status</option>
                    <option value="menunggu_approval" {{ request('status') == 'menunggu_approval' ? 'selected' : '' }}>Menunggu Approval</option>
                    <option value="menunggu_cetak_surat" {{ request('status') == 'menunggu_cetak_surat' ? 'selected' : '' }}>Menunggu Cetak Surat</option>
                    <option value="menunggu_laporan" {{ request('status') == 'menunggu_laporan' ? 'selected' : '' }}>Menunggu Laporan</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500">Jenis Tindakan</label>
                <select name="jenis_tindakan" class="form-select text-sm">
                    <option value="">Semua</option>
                    @foreach(\App\Models\ProgresPelanggaran::jenisTindakanOptions() as $val => $label)
                    <option value="{{ $val }}" {{ request('jenis_tindakan') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500">Kelas</label>
                <select name="kelas_id" class="form-select text-sm">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary text-sm">Filter</button>
            <a href="{{ route('bk.progres.index') }}" class="btn-secondary text-sm">Reset</a>
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
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500">Status Progres</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500">Tanggal</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500">Aksi</th>
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
                        <td class="py-3 px-4">
                            <span class="badge text-xs {{ $progres->jenis_tindakan_badge }}">{{ $progres->jenis_tindakan_label }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="badge text-xs {{ $progres->status_badge }}">{{ $progres->status_label }}</span>
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-500">{{ $progres->created_at->format('d/m/Y') }}</td>
                        <td class="py-3 px-4">
                            <a href="{{ route('bk.progres.show', $p) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Detail →</a>
                        </td>
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
