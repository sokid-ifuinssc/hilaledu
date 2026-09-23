@extends('layouts.app')
@section('title', 'Jenis Pelanggaran')
@section('content')
<div class="space-y-5">
    <div class="flex justify-between"><h3 class="font-semibold text-gray-800">Data Jenis Pelanggaran</h3><a href="{{ route('admin.jenis-pelanggaran.create') }}" class="btn-primary"><svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>Tambah</a></div>
    <div class="table-container"><table><thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Poin</th><th>Aksi</th></tr></thead><tbody>
    @forelse($jenisPelanggarans as $jp)
    <tr><td class="font-mono font-semibold">{{ $jp->kode }}</td><td>{{ $jp->nama }}</td><td><span class="badge" style="background-color: {{ $jp->kategori->warna }}20; color: {{ $jp->kategori->warna }}">{{ $jp->kategori->nama }}</span></td><td class="font-semibold text-red-600">{{ $jp->poin }}</td><td class="flex gap-2"><a href="{{ route('admin.jenis-pelanggaran.edit', $jp) }}" class="text-amber-600 hover:underline text-sm">Edit</a><form method="POST" action="{{ route('admin.jenis-pelanggaran.destroy', $jp) }}" onsubmit="return confirm('Yakin?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline text-sm">Hapus</button></form></td></tr>
    @empty<tr><td colspan="5" class="text-center py-8 text-gray-500">Belum ada data</td></tr>@endforelse
    </tbody></table></div><div>{{ $jenisPelanggarans->links() }}</div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #fef2f2; color: #dc2626;">📋</span> Halaman Jenis Pelanggaran</h3>
    <p>Kelola detail jenis pelanggaran spesifik. Setiap jenis terhubung ke kategori dan memiliki bobot poin yang akan mengurangi poin siswa.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Klik <strong>"Tambah"</strong> untuk menambah jenis pelanggaran baru</li>
        <li>Isi <strong>kode</strong> (contoh: P001), <strong>nama</strong> pelanggaran (contoh: Terlambat masuk kelas)</li>
        <li>Pilih <strong>kategori</strong> pelanggaran (Ringan/Sedang/Berat)</li>
        <li>Tentukan <strong>poin</strong> yang akan dikurangi jika siswa melakukan pelanggaran ini</li>
    </ol>
</div>
<div class="help-warning">
    <span>⚠️</span>
    <p><strong>Perhatian:</strong> Poin yang ditetapkan di sini adalah poin yang akan langsung mengurangi skor siswa (awal 100). Siswa dengan poin &lt; 40 dianggap bermasalah.</p>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Guru BK dapat memilih jenis pelanggaran saat mencatat pelanggaran siswa. Poin otomatis berkurang sesuai bobot yang ditentukan.</p>
</div>
@endsection
