@extends('layouts.app')
@section('title', 'Tahun Ajaran')
@section('content')
<div class="space-y-5">
    <div class="flex justify-between"><h3 class="font-semibold text-gray-800">Data Tahun Ajaran</h3><a href="{{ route('admin.tahun-ajaran.create') }}" class="btn-primary"><svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>Tambah</a></div>
    <div class="table-container"><table><thead><tr><th>Nama</th><th>Semester</th><th>Mulai</th><th>Selesai</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
    @forelse($tahunAjarans as $t)
    <tr><td class="font-medium">{{ $t->nama }}</td><td>{{ ucfirst($t->semester) }}</td><td>{{ $t->tanggal_mulai?->format('d/m/Y') ?? ($t->tahun_mulai ?? '-') }}</td><td>{{ $t->tanggal_selesai?->format('d/m/Y') ?? ($t->tahun_selesai ?? '-') }}</td><td><span class="badge {{ $t->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $t->is_active ? 'Aktif' : 'Non-Aktif' }}</span></td><td class="flex gap-2"><a href="{{ route('admin.tahun-ajaran.edit', $t) }}" class="text-amber-600 hover:underline text-sm">Edit</a><form method="POST" action="{{ route('admin.tahun-ajaran.destroy', $t) }}" onsubmit="return confirm('Yakin?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline text-sm">Hapus</button></form></td></tr>
    @empty<tr><td colspan="6" class="text-center py-8 text-gray-500">Belum ada data</td></tr>@endforelse
    </tbody></table></div><div>{{ $tahunAjarans->links() }}</div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">📅</span> Halaman Tahun Ajaran</h3>
    <p>Kelola data tahun ajaran/periode akademik. Setiap tahun ajaran memiliki semester (Ganjil/Genap) dan rentang tanggal.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Klik tombol <strong>"Tambah"</strong> untuk menambah tahun ajaran baru</li>
        <li>Isi <strong>nama</strong> (contoh: 2024/2025), pilih <strong>semester</strong>, dan tentukan <strong>tanggal mulai/selesai</strong></li>
        <li>Centang <strong>"Aktif"</strong> untuk mengaktifkan tahun ajaran ini sebagai periode berjalan</li>
        <li>Klik <strong>"Edit"</strong> pada tabel untuk mengubah data</li>
        <li>Klik <strong>"Hapus"</strong> untuk menghapus tahun ajaran (pastikan tidak ada data terkait)</li>
    </ol>
</div>
<div class="help-warning">
    <span>⚠️</span>
    <p><strong>Penting:</strong> Hanya satu tahun ajaran yang boleh aktif pada satu waktu. Mengaktifkan tahun ajaran baru akan menonaktifkan yang sebelumnya.</p>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Tahun ajaran aktif digunakan sebagai acuan periode untuk seluruh data pelanggaran, laporan, dan monitoring.</p>
</div>
@endsection
