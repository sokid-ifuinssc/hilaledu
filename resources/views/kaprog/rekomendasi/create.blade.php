@extends('layouts.app')
@section('title', 'Buat Rekomendasi Kaprog')
@section('content')
<div class="max-w-2xl space-y-5">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Pelanggaran</h3>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div><span class="text-gray-500">Siswa:</span> <span class="font-medium">{{ $pelanggaran->siswa->nama_lengkap }}</span></div>
            <div><span class="text-gray-500">Kelas:</span> <span class="font-medium">{{ $pelanggaran->siswa?->kelas?->nama ?? '-' }}</span></div>
            <div><span class="text-gray-500">Pelanggaran:</span> <span class="font-medium">{{ $pelanggaran->jenisPelanggaran->nama }}</span></div>
            <div><span class="text-gray-500">Poin:</span> <span class="font-bold text-red-600">-{{ $pelanggaran->poin }}</span></div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('kaprog.rekomendasi.store') }}">@csrf
            <input type="hidden" name="pelanggaran_id" value="{{ $pelanggaran->id }}">
            <div class="space-y-4">
                <div><label class="form-label">Jenis Rekomendasi *</label>
                    <select name="jenis_rekomendasi" class="form-select" required>
                        <option value="">Pilih</option>
                        @foreach($jenisRekomendasis as $jr)
                        <option value="{{ $jr->kode }}" {{ old('jenis_rekomendasi') == $jr->kode ? 'selected' : '' }}>{{ $jr->nama }}</option>
                        @endforeach
                    </select>
                    @error('jenis_rekomendasi')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div><label class="form-label">Deskripsi *</label><textarea name="deskripsi_rekomendasi" rows="4" class="form-textarea" required placeholder="Jelaskan rekomendasi...">{{ old('deskripsi_rekomendasi') }}</textarea>
                    @error('deskripsi_rekomendasi')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div><label class="form-label">Batas Waktu</label><input type="date" name="batas_waktu" value="{{ old('batas_waktu') }}" class="form-input">
                    @error('batas_waktu')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex gap-3 mt-6"><button type="submit" class="btn-primary">Kirim Rekomendasi</button><a href="{{ route('kaprog.pelanggaran.show', $pelanggaran) }}" class="btn-secondary">Batal</a></div>
        </form>
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #faf5ff; color: #7c3aed;">✏️</span> Buat Rekomendasi Kaprog</h3>
    <p>Form untuk membuat rekomendasi tindak lanjut dari perspektif Ketua Program Keahlian.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Langkah-langkah</h3>
    <ol>
        <li>Pilih <strong>jenis rekomendasi</strong> yang sesuai</li>
        <li>Isi <strong>deskripsi rekomendasi</strong> yang diusulkan</li>
        <li>Klik <strong>"Kirim Rekomendasi"</strong> untuk mengirim</li>
    </ol>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Rekomendasi Kaprog terkirim dan menjadi acuan tambahan bagi Wali Kelas dalam menangani pelanggaran.</p>
</div>
@endsection
