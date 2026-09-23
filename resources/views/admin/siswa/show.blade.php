@extends('layouts.app')
@section('title', 'Detail Siswa')
@section('subtitle', $siswa->nama_lengkap)

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start gap-6">
            <div class="w-20 h-20 rounded-full bg-indigo-100 flex items-center justify-center text-2xl font-bold text-indigo-600">{{ substr($siswa->nama_lengkap, 0, 1) }}</div>
            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <h3 class="text-xl font-bold text-gray-800">{{ $siswa->nama_lengkap }}</h3>
                    <span class="badge {{ $siswa->poin >= 80 ? 'bg-green-100 text-green-700' : ($siswa->poin >= 60 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">Poin: {{ $siswa->poin }}</span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                    <div><p class="text-xs text-gray-500">NIS</p><p class="text-sm font-medium">{{ $siswa->nis }}</p></div>
                    <div><p class="text-xs text-gray-500">NISN</p><p class="text-sm font-medium">{{ $siswa->nisn ?? '-' }}</p></div>
                    <div><p class="text-xs text-gray-500">Kelas</p><p class="text-sm font-medium">{{ $siswa->kelas?->nama_kelas ?? $siswa->kelas?->nama ?? '-' }}</p></div>
                    <div><p class="text-xs text-gray-500">Jurusan</p><p class="text-sm font-medium">{{ $siswa->kelas?->jurusan?->nama ?? '-' }}</p></div>
                    <div><p class="text-xs text-gray-500">Jenis Kelamin</p><p class="text-sm font-medium">{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</p></div>
                    <div><p class="text-xs text-gray-500">Status</p><span class="badge {{ $siswa->status == 'aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ ucfirst($siswa->status) }}</span></div>
                    <div class="col-span-2"><p class="text-xs text-gray-500">Alamat Tempat Tinggal</p><p class="text-sm font-medium">{{ $siswa->alamat_display }}</p></div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4 bg-emerald-50/60 p-4 rounded-xl border border-emerald-100">
                    <div>
                        <p class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">👨‍👩‍👧‍👦 Orang Tua / Wali Siswa</p>
                        <p class="text-base font-bold text-gray-900 mt-0.5">{{ $siswa->nama_ortu_display }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">📞 Kontak WhatsApp / Telepon Ortu</p>
                        @if($siswa->no_hp_ortu_display !== '-')
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', str_starts_with($siswa->no_hp_ortu_display, '0') ? '62' . substr($siswa->no_hp_ortu_display, 1) : $siswa->no_hp_ortu_display) }}" target="_blank" class="inline-flex items-center text-sm font-bold text-emerald-700 hover:text-emerald-900 bg-white px-3 py-1.5 rounded-lg border border-emerald-200 shadow-sm hover:shadow transition-all gap-2 mt-0.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                            Hubungi {{ $siswa->no_hp_ortu_display }}
                        </a>
                        @else
                        <p class="text-sm font-medium text-gray-500 mt-0.5">-</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat Pelanggaran --}}
    <div class="table-container">
        <div class="px-5 py-4 border-b border-gray-200"><h3 class="font-semibold text-gray-800">Riwayat Pelanggaran ({{ $siswa->pelanggarans->count() }})</h3></div>
        <table>
            <thead><tr><th>Tanggal</th><th>Pelanggaran</th><th>Kategori</th><th>Poin</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($siswa->pelanggarans as $p)
                <tr>
                    <td>{{ $p->tanggal_pelanggaran->format('d/m/Y') }}</td>
                    <td class="font-medium">{{ $p->jenisPelanggaran->nama }}</td>
                    <td><span class="badge" style="background-color: {{ $p->jenisPelanggaran->kategori->warna }}20; color: {{ $p->jenisPelanggaran->kategori->warna }}">{{ $p->jenisPelanggaran->kategori->nama }}</span></td>
                    <td><span class="text-red-600 font-semibold">-{{ $p->poin }}</span></td>
                    <td><span class="badge {{ $p->status_badge }}">{{ $p->status_label }}</span></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-8 text-gray-500">Tidak ada pelanggaran</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex gap-3">
        <a href="{{ route('superadmin.siswa.index') }}" class="btn-secondary">← Kembali ke Daftar Siswa</a>
    </div>
</div>
@endsection
