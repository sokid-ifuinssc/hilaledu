@extends('layouts.app')
@section('title', 'Buat Tindak Lanjut')
@section('content')
<div class="max-w-2xl space-y-5">
    @if($rekomendasi)
    <div class="bg-blue-50 rounded-xl border border-blue-200 p-5">
        <h3 class="text-sm font-semibold text-blue-800 mb-2">Rekomendasi dari Guru BK</h3>
        <p class="text-sm text-blue-700">{{ $rekomendasi->jenis_rekomendasi_label }}: {{ $rekomendasi->deskripsi_rekomendasi }}</p>
        <p class="text-xs text-blue-600 mt-2">Siswa: {{ $rekomendasi->pelanggaran->siswa->nama_lengkap ?? '-' }} | Pelanggaran: {{ $rekomendasi->pelanggaran->jenisPelanggaran->nama ?? '-' }}</p>
    </div>
    @endif
    @if($rekomendasiKaprog)
    <div class="bg-purple-50 rounded-xl border border-purple-200 p-5">
        <h3 class="text-sm font-semibold text-purple-800 mb-2">Rekomendasi dari Kaprog</h3>
        <p class="text-sm text-purple-700">{{ $rekomendasiKaprog->jenis_rekomendasi_label }}: {{ $rekomendasiKaprog->deskripsi_rekomendasi }}</p>
        <p class="text-xs text-purple-600 mt-2">Siswa: {{ $rekomendasiKaprog->pelanggaran->siswa->nama_lengkap ?? '-' }}</p>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('walikelas.tindak-lanjut.store') }}" enctype="multipart/form-data">@csrf
            @if($rekomendasi)<input type="hidden" name="rekomendasi_id" value="{{ $rekomendasi->id }}">@endif
            @if($rekomendasiKaprog)<input type="hidden" name="rekomendasi_kaprog_id" value="{{ $rekomendasiKaprog->id }}">@endif
            <div class="space-y-4">
                <div><label class="form-label">Tanggal Tindak Lanjut *</label><input type="date" name="tanggal_tindak_lanjut" value="{{ old('tanggal_tindak_lanjut', date('Y-m-d')) }}" class="form-input" required></div>
                <div><label class="form-label">Tindakan yang Dilakukan *</label><textarea name="tindakan_yang_dilakukan" rows="4" class="form-textarea" required placeholder="Jelaskan tindakan yang dilakukan...">{{ old('tindakan_yang_dilakukan') }}</textarea></div>
                <div><label class="form-label">Hasil</label><textarea name="hasil" rows="3" class="form-textarea" placeholder="Hasil dari tindakan...">{{ old('hasil') }}</textarea></div>
                <div><label class="form-label">Bukti (opsional)</label><input type="file" name="bukti_tindak_lanjut" class="form-input"></div>
            </div>
            <div class="flex gap-3 mt-6"><button type="submit" class="btn-primary">Simpan</button><a href="{{ route('walikelas.tindak-lanjut.index') }}" class="btn-secondary">Batal</a></div>
        </form>
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #dbeafe; color: #2563eb;">✏️</span> Buat Tindak Lanjut</h3>
    <p>Form untuk mencatat pelaksanaan tindak lanjut berdasarkan rekomendasi dari Guru BK atau Kaprog.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Langkah-langkah</h3>
    <ol>
        <li>Pilih <strong>rekomendasi</strong> yang akan ditindaklanjuti</li>
        <li>Isi <strong>tanggal pelaksanaan</strong> dan <strong>hasil/tindakan</strong> yang dilakukan</li>
        <li>Tambahkan <strong>catatan</strong> jika diperlukan</li>
        <li>Klik <strong>"Simpan"</strong> untuk menyimpan tindak lanjut</li>
    </ol>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Tindak lanjut tercatat. Status pelanggaran berubah menjadi "Ditindaklanjuti". Guru BK dapat memantau proses dari halaman Monitoring.</p>
</div>
@endsection

