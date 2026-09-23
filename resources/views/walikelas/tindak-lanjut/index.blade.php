@extends('layouts.app')
@section('title', 'Tindak Lanjut')
@section('content')
<div class="space-y-5">
    <form method="GET" class="flex gap-3"><select name="status" class="form-select w-44" onchange="this.form.submit()"><option value="">Semua</option><option value="belum_diproses" {{ request('status') == 'belum_diproses' ? 'selected' : '' }}>Belum Diproses</option><option value="sedang_diproses" {{ request('status') == 'sedang_diproses' ? 'selected' : '' }}>Sedang Diproses</option><option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option></select></form>
    <div class="table-container"><table><thead><tr><th>Tanggal</th><th>Siswa</th><th>Tindakan</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
    @forelse($tindakLanjuts as $tl)
    @php $p = $tl->rekomendasi?->pelanggaran ?? $tl->rekomendasiKaprog?->pelanggaran; @endphp
    <tr><td>{{ $tl->tanggal_tindak_lanjut->format('d/m/Y') }}</td><td class="font-medium">{{ $p?->siswa->nama_lengkap ?? '-' }}</td><td class="max-w-xs truncate">{{ $tl->tindakan_yang_dilakukan }}</td><td><span class="badge {{ $tl->status_badge }}">{{ $tl->status_label }}</span></td><td class="flex gap-2"><a href="{{ route('walikelas.tindak-lanjut.show', $tl) }}" class="text-indigo-600 hover:underline text-sm">Detail</a>@if($tl->status != 'selesai')<form method="POST" action="{{ route('walikelas.tindak-lanjut.updateStatus', $tl) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="selesai"><button class="text-green-600 hover:underline text-sm">Selesai</button></form>@endif</td></tr>
    @empty<tr><td colspan="5" class="text-center py-8 text-gray-500">Belum ada tindak lanjut</td></tr>@endforelse
    </tbody></table></div><div>{{ $tindakLanjuts->links() }}</div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #dbeafe; color: #2563eb;">📋</span> Daftar Tindak Lanjut</h3>
    <p>Halaman ini menampilkan semua tindak lanjut yang telah Anda lakukan berdasarkan rekomendasi dari Guru BK dan Kaprog.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Lihat daftar tindak lanjut beserta <strong>status</strong> (Dalam Proses/Selesai)</li>
        <li>Klik <strong>"Buat Tindak Lanjut"</strong> untuk menindaklanjuti rekomendasi baru</li>
        <li>Klik <strong>"Detail"</strong> untuk melihat atau mengupdate status tindak lanjut</li>
    </ol>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Semua rekomendasi tertangani dengan baik. Status pelanggaran berubah menjadi "Selesai" setelah tindak lanjut diselesaikan.</p>
</div>
@endsection

