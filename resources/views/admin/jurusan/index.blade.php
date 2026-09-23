@extends('layouts.app')
@section('title', 'Program Keahlian')
@section('content')
<div class="space-y-5">
    <div class="flex justify-between"><h3 class="font-semibold text-gray-800">Data Program Keahlian</h3><a href="{{ route('admin.jurusan.create') }}" class="btn-primary"><svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>Tambah</a></div>
    <div class="table-container"><table><thead><tr><th>Kode</th><th>Nama</th><th>Kaprog</th><th>Aksi</th></tr></thead><tbody>
    @forelse($jurusans as $j)
    <tr><td class="font-mono font-semibold">{{ $j->kode }}</td><td>{{ $j->nama }}</td><td>{{ $j->kaprog->nama_lengkap ?? '-' }}</td><td class="flex gap-2"><a href="{{ route('admin.jurusan.edit', $j) }}" class="text-amber-600 hover:underline text-sm">Edit</a><form method="POST" action="{{ route('admin.jurusan.destroy', $j) }}" onsubmit="return confirm('Yakin?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline text-sm">Hapus</button></form></td></tr>
    @empty<tr><td colspan="4" class="text-center py-8 text-gray-500">Belum ada data</td></tr>@endforelse
    </tbody></table></div><div>{{ $jurusans->links() }}</div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #faf5ff; color: #7c3aed;">🏫</span> Halaman Program Keahlian</h3>
    <p>Kelola data jurusan/program keahlian SMK. Setiap jurusan memiliki kode unik, nama, dan bisa di-assign ke Kaprog (Ketua Program).</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Klik <strong>"Tambah"</strong> untuk menambah jurusan baru</li>
        <li>Isi <strong>kode</strong> (contoh: RPL, TKJ) dan <strong>nama lengkap</strong> jurusan</li>
        <li>Pilih <strong>Kaprog</strong> yang bertanggung jawab atas jurusan ini</li>
        <li>Data jurusan digunakan saat membuat kelas dan mengelompokkan siswa</li>
    </ol>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Setiap jurusan memiliki data yang lengkap dan terhubung ke Kaprog yang bertanggung jawab. Kaprog dapat melihat pelanggaran siswa di jurusannya.</p>
</div>
@endsection
