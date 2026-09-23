@extends('layouts.app')
@section('title', 'Kategori Pelanggaran')
@section('content')
<div class="space-y-5">
    <div class="flex flex-wrap justify-between items-center gap-3">
        <h3 class="font-semibold text-gray-800">Data Kategori Pelanggaran</h3>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.kategori-pelanggaran.template') }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Template
            </a>
            <a href="{{ route('admin.kategori-pelanggaran.export') }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>Export
            </a>
            <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>Import
            </button>
            <a href="{{ route('admin.kategori-pelanggaran.create') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>Tambah
            </a>
        </div>
    </div>

    <div class="table-container"><table><thead><tr><th>Nama</th><th>Bobot Poin</th><th>Warna</th><th>Jenis Pelanggaran</th><th>Aksi</th></tr></thead><tbody>
    @forelse($kategoris as $k)
    <tr><td class="font-medium">{{ $k->nama }}</td><td>{{ $k->bobot_poin }}</td><td><div class="flex items-center gap-2"><span class="w-4 h-4 rounded-full" style="background-color: {{ $k->warna }}"></span>{{ $k->warna }}</div></td><td>{{ $k->jenis_pelanggarans_count }} jenis</td><td class="flex gap-2"><a href="{{ route('admin.kategori-pelanggaran.edit', $k) }}" class="text-amber-600 hover:underline text-sm">Edit</a><form method="POST" action="{{ route('admin.kategori-pelanggaran.destroy', $k) }}" onsubmit="return confirm('Yakin?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline text-sm">Hapus</button></form></td></tr>
    @empty<tr><td colspan="5" class="text-center py-8 text-gray-500">Belum ada data</td></tr>@endforelse
    </tbody></table></div><div>{{ $kategoris->links() }}</div>
</div>

{{-- Import Modal --}}
<div id="importModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Import Kategori Pelanggaran</h3>
        <form method="POST" action="{{ route('admin.kategori-pelanggaran.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="form-label">File CSV *</label>
                    <input type="file" name="file" accept=".csv,.txt" required class="form-input">
                    <p class="text-xs text-gray-400 mt-1">Format: CSV. Maks 2MB. <a href="{{ route('admin.kategori-pelanggaran.template') }}" class="text-indigo-600 hover:underline">Download template</a></p>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary">Import</button>
                <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="btn-secondary">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #fef3c7; color: #d97706;">🏷️</span> Halaman Kategori Pelanggaran</h3>
    <p>Kelola kategori/kelompok besar pelanggaran. Setiap kategori memiliki nama, bobot poin default, dan warna label untuk identifikasi visual.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Klik <strong>"Tambah"</strong> untuk membuat kategori baru</li>
        <li>Isi <strong>nama kategori</strong> (contoh: Ringan, Sedang, Berat)</li>
        <li>Tentukan <strong>bobot poin</strong> default untuk kategori ini</li>
        <li>Pilih <strong>warna</strong> label yang akan tampil di tabel dan badge</li>
    </ol>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">📦</span> Import & Export</h3>
    <ol>
        <li>Klik <strong>"Template"</strong> untuk download format CSV</li>
        <li>Isi data sesuai template, lalu klik <strong>"Import"</strong></li>
        <li>Klik <strong>"Export"</strong> untuk unduh semua data dalam format CSV</li>
    </ol>
</div>
<div class="help-tip">
    <span class="tip-icon"><svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
    <p>Kolom <strong>"Jenis Pelanggaran"</strong> menunjukkan berapa banyak jenis pelanggaran yang terdaftar di bawah kategori ini.</p>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Kategori membantu mengelompokkan pelanggaran berdasarkan tingkat keparahan, memudahkan filtering dan pelaporan.</p>
</div>
@endsection
