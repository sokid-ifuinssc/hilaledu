@extends('layouts.app')
@section('title', 'Edit Pelanggaran')
@section('subtitle', $pelanggaran->siswa->nama_lengkap)

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        {{-- Info Siswa --}}
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="grid grid-cols-2 gap-3">
                <div><p class="text-xs text-gray-500">Siswa</p><p class="text-sm font-medium">{{ $pelanggaran->siswa->nama_lengkap }}</p></div>
                <div><p class="text-xs text-gray-500">NIS</p><p class="text-sm font-medium">{{ $pelanggaran->siswa->nis }}</p></div>
                <div><p class="text-xs text-gray-500">Kelas</p><p class="text-sm font-medium">{{ $pelanggaran->siswa?->kelas?->nama ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">Jenis Pelanggaran</p><p class="text-sm font-medium">{{ $pelanggaran->jenisPelanggaran->nama }} ({{ $pelanggaran->poin }} poin)</p></div>
            </div>
        </div>

        <form method="POST" action="{{ route('bk.pelanggaran.update', $pelanggaran) }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="form-label">Tanggal Pelanggaran *</label>
                    <input type="date" name="tanggal_pelanggaran" value="{{ old('tanggal_pelanggaran', $pelanggaran->tanggal_pelanggaran->format('Y-m-d')) }}" class="form-input" required>
                    @error('tanggal_pelanggaran')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Deskripsi / Kronologi *</label>
                    <textarea name="deskripsi" rows="4" class="form-textarea" required placeholder="Jelaskan kronologi pelanggaran...">{{ old('deskripsi', $pelanggaran->deskripsi) }}</textarea>
                    @error('deskripsi')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <a href="{{ route('bk.pelanggaran.show', $pelanggaran) }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #fef2f2; color: #dc2626;">✏️</span> Edit Pelanggaran</h3>
    <p>Halaman ini untuk mengedit data pelanggaran yang sudah dicatat. Hanya deskripsi dan tanggal yang dapat diubah.</p>
</div>
<div class="help-warning">
    <span>⚠️</span>
    <p><strong>Perhatian:</strong> Jenis pelanggaran dan poin tidak dapat diubah setelah dicatat.</p>
</div>
@endsection
