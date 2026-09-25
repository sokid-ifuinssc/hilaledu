@extends('layouts.app')
@section('title', 'Data Pelanggaran')
@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <h3 class="font-semibold text-gray-800 text-lg">Data Pelanggaran</h3>
        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="btn-primary" style="background-color: #10b981; border-color: #10b981;">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg> Import
            </button>
            <a href="{{ route(request()->routeIs('admin.*') ? 'admin.jenis-pelanggaran.export' : 'bk.jenis-pelanggaran.export') }}" class="btn-primary" style="background-color: #f59e0b; border-color: #f59e0b;">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> Export
            </a>
            <a href="{{ route(request()->routeIs('admin.*') ? 'admin.jenis-pelanggaran.create' : 'bk.jenis-pelanggaran.create') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg> Tambah
            </a>
        </div>
    </div>

    <!-- Modal Import -->
    <div id="importModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('importModal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route(request()->routeIs('admin.*') ? 'admin.jenis-pelanggaran.import' : 'bk.jenis-pelanggaran.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Import Data Pelanggaran</h3>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500 mb-4">Pilih file Excel atau CSV untuk diimport. Pastikan format kolom sesuai dengan format export.</p>
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-input w-full" required>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Import Data
                        </button>
                        <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="table-container"><table><thead><tr><th>Nama Pelanggaran</th><th>Poin</th><th>Aksi</th></tr></thead><tbody>
    @forelse($jenisPelanggarans as $jp)
    <tr><td>{{ $jp->nama }}</td><td class="font-semibold text-red-600">{{ $jp->poin }}</td><td class="flex gap-2"><a href="{{ route(request()->routeIs('admin.*') ? 'admin.jenis-pelanggaran.edit' : 'bk.jenis-pelanggaran.edit', $jp) }}" class="text-amber-600 hover:underline text-sm">Edit</a><form method="POST" action="{{ route(request()->routeIs('admin.*') ? 'admin.jenis-pelanggaran.destroy' : 'bk.jenis-pelanggaran.destroy', $jp) }}" onsubmit="return confirm('Yakin ingin menghapus pelanggaran ini?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline text-sm">Hapus</button></form></td></tr>
    @empty<tr><td colspan="3" class="text-center py-8 text-gray-500">Belum ada data pelanggaran</td></tr>@endforelse
    </tbody></table></div><div>{{ $jenisPelanggarans->links() }}</div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #fef2f2; color: #dc2626;">📋</span> Halaman Data Pelanggaran</h3>
    <p>Kelola data nama-nama pelanggaran dan poinnya. Bobot poin ini akan mengurangi poin siswa ketika melakukan pelanggaran.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Klik <strong>"Tambah"</strong> untuk menambah data pelanggaran baru</li>
        <li>Isi <strong>nama pelanggaran</strong> (contoh: Terlambat masuk kelas)</li>
        <li>Tentukan <strong>poin</strong> yang akan dikurangi jika siswa melakukan pelanggaran ini</li>
    </ol>
</div>
<div class="help-warning">
    <span>⚠️</span>
    <p><strong>Perhatian:</strong> Poin yang ditetapkan di sini adalah poin yang akan langsung mengurangi skor siswa.</p>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Admin atau Guru dapat memilih nama pelanggaran saat mencatat pelanggaran siswa. Poin otomatis berkurang sesuai nilai yang ditentukan.</p>
</div>
@endsection
