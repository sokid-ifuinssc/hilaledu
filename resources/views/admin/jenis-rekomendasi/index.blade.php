@extends('layouts.app')
@section('title', 'Jenis Rekomendasi')
@section('subtitle', 'Data master jenis rekomendasi')

@section('content')
<div class="space-y-5">
    <div class="flex justify-between items-center">
        <h3 class="font-semibold text-gray-800">Data Jenis Rekomendasi</h3>
        <a href="{{ route('admin.jenis-rekomendasi.create') }}" class="btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>Tambah
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Untuk</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jenisRekomendasis as $jr)
                <tr>
                    <td><code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $jr->kode }}</code></td>
                    <td class="font-medium">{{ $jr->nama }}</td>
                    <td>
                        <span class="badge {{ $jr->untuk == 'bk' ? 'bg-blue-100 text-blue-800' : ($jr->untuk == 'kaprog' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800') }}">
                            {{ $jr->untuk_label }}
                        </span>
                    </td>
                    <td class="text-sm text-gray-500">{{ Str::limit($jr->deskripsi, 50) }}</td>
                    <td>
                        @if($jr->is_active)
                            <span class="badge bg-green-100 text-green-800">Aktif</span>
                        @else
                            <span class="badge bg-red-100 text-red-800">Nonaktif</span>
                        @endif
                    </td>
                    <td class="flex gap-2">
                        <a href="{{ route('admin.jenis-rekomendasi.edit', $jr) }}" class="text-amber-600 hover:underline text-sm">Edit</a>
                        <form method="POST" action="{{ route('admin.jenis-rekomendasi.destroy', $jr) }}" onsubmit="return confirm('Yakin hapus jenis rekomendasi ini?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline text-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-8 text-gray-500">Belum ada data jenis rekomendasi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $jenisRekomendasis->links() }}</div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">📋</span> Jenis Rekomendasi</h3>
    <p>Kelola data master jenis rekomendasi yang bisa diberikan oleh Guru BK dan Kaprog.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Klik <strong>"Tambah"</strong> untuk membuat jenis rekomendasi baru</li>
        <li>Isi <strong>kode</strong> (huruf kecil dan underscore, contoh: peringatan_lisan)</li>
        <li>Isi <strong>nama</strong> yang akan ditampilkan</li>
        <li>Pilih <strong>untuk</strong> siapa (BK, Kaprog, atau Semua)</li>
    </ol>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Jenis rekomendasi yang dibuat akan muncul sebagai pilihan di form rekomendasi Guru BK dan Kaprog.</p>
</div>
@endsection
