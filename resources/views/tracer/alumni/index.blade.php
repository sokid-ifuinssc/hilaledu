@extends('layouts.app')

@section('page-title', 'Data Alumni')

@section('content')
<div class="card">
    <div class="card-header border-b border-gray-200 pb-4 mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h5 class="text-lg font-bold text-gray-900 m-0">Data Alumni</h5>
    </div>

    {{-- Filters --}}
    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6 flex flex-col sm:flex-row gap-4">
        <form action="{{ route('tracer.alumni.index') }}" method="GET" class="flex-1 flex flex-col sm:flex-row gap-4 w-full">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIS/username..." class="form-input w-full">
            </div>
            <div class="w-full sm:w-48">
                <select name="tahun_lulus" class="form-select w-full" onchange="this.form.submit()">
                    <option value="">Semua Tahun Lulus</option>
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ request('tahun_lulus') == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary w-full sm:w-auto">
                <i class="bi bi-search mr-1.5"></i> Cari
            </button>
            @if(request()->anyFilled(['search', 'tahun_lulus']))
                <a href="{{ route('tracer.alumni.index') }}" class="btn-secondary w-full sm:w-auto text-center">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nama Lengkap & Kontak</th>
                    <th class="text-center">Tahun Lulus</th>
                    <th>Status Saat Ini</th>
                    <th>Nama Instansi / Kampus / Tempat Kerja</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alumnis as $a)
                <tr>
                    <td>
                        <div class="font-semibold text-gray-900">{{ $a->user?->name ?? $a->user?->nama_lengkap ?? '-' }}</div>
                        <div class="text-xs text-gray-500 font-mono">Username/NIS: {{ $a->user?->username ?? $a->user?->nis ?? '-' }}</div>
                    </td>
                    <td class="text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-indigo-100 text-indigo-800">
                            {{ $a->tahun_lulus }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $a->status_saat_ini ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $a->status_saat_ini ?? 'Belum Diisi' }}
                        </span>
                    </td>
                    <td>{{ $a->nama_instansi ?? '-' }}</td>
                    <td class="text-right">
                        <a href="{{ route('tracer.alumni.edit', $a->id) }}" class="p-1.5 text-gray-500 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors inline-flex items-center gap-1 text-xs font-medium">
                            <i class="bi bi-pencil-square text-indigo-600"></i>
                            Update Info
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-8 text-gray-500">
                        Belum ada data alumni. Data alumni akan muncul otomatis ketika siswa diluluskan dari menu Data Siswa.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $alumnis->links() }}
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">🎓</span> Halaman Data Alumni</h3>
    <p>Daftar data alumni HilalEdu. Data di halaman ini digenerate secara otomatis ketika siswa diluluskan melalui fitur migrasi di menu Siswa.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Gunakan <strong>filter tahun lulus</strong> untuk melihat alumni pada angkatan tertentu.</li>
        <li>Klik tombol <strong>Update Info</strong> untuk melengkapi data tracer study (kuliah, kerja, wirausaha, dll).</li>
    </ol>
</div>
@endsection
