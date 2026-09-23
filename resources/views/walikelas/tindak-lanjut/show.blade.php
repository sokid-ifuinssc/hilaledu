@extends('layouts.app')
@section('title', 'Detail Tindak Lanjut')
@section('content')
<div class="max-w-3xl space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-semibold">Detail Tindak Lanjut</h3><span class="badge text-sm {{ $tindakLanjut->status_badge }}">{{ $tindakLanjut->status_label }}</span></div>
        <div class="grid grid-cols-2 gap-4">
            <div><p class="text-xs text-gray-500">Tanggal</p><p class="text-sm font-medium">{{ $tindakLanjut->tanggal_tindak_lanjut->format('d M Y') }}</p></div>
            <div><p class="text-xs text-gray-500">Wali Kelas</p><p class="text-sm font-medium">{{ $tindakLanjut->waliKelas->nama_lengkap ?? '-' }}</p></div>
            <div class="col-span-2"><p class="text-xs text-gray-500">Tindakan</p><p class="text-sm">{{ $tindakLanjut->tindakan_yang_dilakukan }}</p></div>
            <div class="col-span-2"><p class="text-xs text-gray-500">Hasil</p><p class="text-sm">{{ $tindakLanjut->hasil ?? '-' }}</p></div>
            @if($tindakLanjut->catatan_bk)<div class="col-span-2 p-3 bg-blue-50 rounded-lg"><p class="text-xs text-blue-600 font-semibold">Catatan BK:</p><p class="text-sm text-blue-800">{{ $tindakLanjut->catatan_bk }}</p></div>@endif
        </div>
    </div>
    <div class="flex gap-3">
        @if($tindakLanjut->status != 'selesai')
        <form method="POST" action="{{ route('walikelas.tindak-lanjut.updateStatus', $tindakLanjut) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="selesai"><button class="btn-success">Tandai Selesai</button></form>
        @endif
        <a href="{{ route('walikelas.tindak-lanjut.index') }}" class="btn-secondary">← Kembali</a>
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">🔍</span> Detail Tindak Lanjut</h3>
    <p>Informasi lengkap tindak lanjut yang telah dilakukan. Anda dapat mengubah status menjadi "Selesai" jika proses telah tuntas.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Review <strong>detail tindak lanjut</strong> yang telah dicatat</li>
        <li>Klik <strong>"Selesaikan"</strong> jika proses tindak lanjut sudah tuntas</li>
        <li>Status pelanggaran akan berubah menjadi <strong>"Selesai"</strong></li>
    </ol>
</div>
@endsection

