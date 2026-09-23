@extends('layouts.app')
@section('title', 'Rekomendasi Saya')
@section('content')
<div class="table-container">
    <div class="px-5 py-4 border-b border-gray-200"><h3 class="font-semibold text-gray-800">Daftar Rekomendasi</h3></div>
    <table><thead><tr><th>Tanggal</th><th>Siswa</th><th>Pelanggaran</th><th>Jenis</th><th>Status</th></tr></thead><tbody>
    @forelse($rekomendasis as $r)
    <tr><td>{{ $r->tanggal_rekomendasi->format('d/m/Y') }}</td><td class="font-medium">{{ $r->pelanggaran->siswa->nama_lengkap ?? '-' }}</td><td>{{ $r->pelanggaran->jenisPelanggaran->nama ?? '-' }}</td><td>{{ $r->jenis_rekomendasi_label }}</td><td><span class="badge {{ $r->status == 'selesai' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">{{ $r->status_label }}</span></td></tr>
    @empty<tr><td colspan="5" class="text-center py-8 text-gray-500">Belum ada rekomendasi</td></tr>@endforelse
    </tbody></table>
</div><div class="mt-4">{{ $rekomendasis->links() }}</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #faf5ff; color: #7c3aed;">📋</span> Rekomendasi Kaprog</h3>
    <p>Daftar rekomendasi tindak lanjut yang telah Anda buat untuk pelanggaran siswa di jurusan Anda.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Klik <strong>"Buat Rekomendasi"</strong> untuk rekomendasi baru</li>
        <li>Lihat <strong>status</strong> rekomendasi (Menunggu/Ditindaklanjuti/Selesai)</li>
        <li>Klik <strong>"Detail"</strong> untuk informasi lengkap</li>
    </ol>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Rekomendasi dari Kaprog melengkapi rekomendasi BK dan membantu Wali Kelas dalam tindak lanjut.</p>
</div>
@endsection

