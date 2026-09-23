@extends('layouts.app')
@section('title', 'Data Guru')
@section('subtitle', 'Daftar pendidik dan penugasan peran HilalEdu')

@section('content')
<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Guru</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalGuru }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Guru Aktif</p>
                <p class="text-2xl font-bold text-emerald-600">{{ $guruAktif }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Status Database</p>
                <p class="text-sm font-bold text-blue-700">Terhubung db_hilaledu</p>
            </div>
        </div>
    </div>

    {{-- Action Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" placeholder="Cari nama / NUPTK..." value="{{ request('search') }}" class="form-input w-72">
            <select name="status" class="form-select w-36" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            <button type="submit" class="btn-primary">Cari</button>
        </form>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('superadmin.credentials.print', ['role' => 'guru']) }}" target="_blank" class="inline-flex items-center px-3.5 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm">
                <i class="bi bi-printer mr-1.5"></i>
                Cetak Akun
            </a>
            <a href="{{ route('superadmin.guru.create') }}" class="btn-primary inline-flex items-center">
                <i class="bi bi-person-plus-fill mr-1.5"></i>
                Tambah Guru
            </a>
            <a href="{{ route('superadmin.guru.import') }}" class="inline-flex items-center px-3.5 py-2 border border-amber-200 text-sm font-medium rounded-lg text-amber-700 bg-amber-50 hover:bg-amber-100 transition-colors shadow-sm">
                <i class="bi bi-file-earmark-excel mr-1.5"></i>
                Import Excel
            </a>
            <a href="{{ route('superadmin.guru.export', request()->query()) }}" class="btn-success">
                <i class="bi bi-download mr-1.5"></i>
                Export CSV
            </a>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 font-semibold uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3.5">Guru & NUPTK</th>
                        <th class="px-6 py-3.5">Kontak</th>
                        <th class="px-6 py-3.5">Penugasan Sistem BK</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($gurus as $guru)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($guru->nama_lengkap ?: $guru->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $guru->nama_lengkap ?: $guru->name }}</div>
                                    <div class="text-xs text-gray-500 font-mono">NUPTK/NIP: {{ $guru->nip ?: '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            <div class="text-xs font-medium text-gray-800">{{ $guru->no_hp ?: '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $guru->email ?: ($guru->user->email ?? '-') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @if($guru->tugas_tambahan && is_array($guru->tugas_tambahan) && count($guru->tugas_tambahan) > 0)
                                    @foreach($guru->tugas_tambahan as $tugas)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            {{ $tugas }}
                                        </span>
                                    @endforeach
                                @elseif($guru->jabatan_utama)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ $guru->jabatan_utama }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">Guru Pengajar</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($guru->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Nonaktif
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('superadmin.guru.edit', $guru->id) }}" class="p-1.5 text-gray-500 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors" title="Edit Data Guru">
                                    <i class="bi bi-pencil-square text-sm"></i>
                                </a>
                                <form action="{{ route('superadmin.guru.toggle-active', $guru->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-1.5 {{ $guru->is_active ? 'text-amber-500 hover:bg-amber-50' : 'text-emerald-500 hover:bg-emerald-50' }} rounded-lg transition" title="{{ $guru->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="bi {{ $guru->is_active ? 'bi-pause-circle' : 'bi-play-circle' }} text-sm"></i>
                                    </button>
                                </form>
                                <form action="{{ route('superadmin.guru.destroy', $guru->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus guru {{ $guru->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Hapus Guru">
                                        <i class="bi bi-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            Belum ada data guru. Klik tombol <strong>Sinkronkan Guru</strong> untuk memuat data guru HilalEdu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($gurus->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $gurus->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
