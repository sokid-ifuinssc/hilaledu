@extends('layouts.app')

@section('page-title', 'Update Data Alumni')

@section('content')
<div class="card max-w-2xl mx-auto">
    <div class="card-header border-b border-gray-200 pb-4 mb-4">
        <h5 class="text-lg font-bold text-gray-900 m-0">Update Data Alumni: {{ $alumni->user?->name ?? $alumni->user?->nama_lengkap ?? '-' }}</h5>
    </div>

    <form action="{{ route('tracer.alumni.update', $alumni->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="card-body">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Lulus</label>
                    <input type="number" name="tahun_lulus" value="{{ old('tahun_lulus', $alumni->tahun_lulus) }}" class="form-input w-full" required>
                    @error('tahun_lulus')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Saat Ini</label>
                    <select name="status_saat_ini" class="form-select w-full">
                        <option value="">-- Pilih Status --</option>
                        <option value="Bekerja" {{ old('status_saat_ini', $alumni->status_saat_ini) == 'Bekerja' ? 'selected' : '' }}>Bekerja</option>
                        <option value="Kuliah" {{ old('status_saat_ini', $alumni->status_saat_ini) == 'Kuliah' ? 'selected' : '' }}>Kuliah</option>
                        <option value="Wirausaha" {{ old('status_saat_ini', $alumni->status_saat_ini) == 'Wirausaha' ? 'selected' : '' }}>Wirausaha</option>
                        <option value="Mencari Kerja" {{ old('status_saat_ini', $alumni->status_saat_ini) == 'Mencari Kerja' ? 'selected' : '' }}>Mencari Kerja</option>
                        <option value="Lainnya" {{ old('status_saat_ini', $alumni->status_saat_ini) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('status_saat_ini')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Instansi / Kampus / Tempat Kerja</label>
                    <input type="text" name="nama_instansi" value="{{ old('nama_instansi', $alumni->nama_instansi) }}" class="form-input w-full">
                    <p class="text-xs text-gray-500 mt-1">Isi jika alumni sedang bekerja, kuliah, atau wirausaha.</p>
                    @error('nama_instansi')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card-footer bg-gray-50 flex justify-end gap-3 mt-6 p-4 rounded-b-lg border-t border-gray-200">
            <a href="{{ route('tracer.alumni.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary bg-indigo-600 hover:bg-indigo-700">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
