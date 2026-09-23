@extends('layouts.app')
@section('title', 'Data Siswa')
@section('subtitle', 'Daftar seluruh siswa terpadu HilalEdu')

@section('content')
<div class="space-y-5">
    {{-- Action Bar --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" placeholder="Cari NIS / Nama..." value="{{ request('search') }}" class="form-input w-64">
            <select name="kelas_id" class="form-select w-48" onchange="this.form.submit()">
                <option value="">Semua Kelas</option>
                @foreach($kelasList as $k)
                <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_lengkap ?? $k->nama_kelas ?? $k->nama }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select w-40" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
            </select>
            <button type="submit" class="btn-primary">Cari</button>
        </form>

        <div class="flex items-center gap-2 flex-wrap">
            {{-- Cetak Akun --}}
            <a href="{{ route('superadmin.credentials.print', array_merge(request()->query(), ['role' => 'siswa'])) }}" target="_blank" class="inline-flex items-center px-3.5 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm">
                <i class="bi bi-printer mr-1.5"></i>
                Cetak Akun
            </a>

            {{-- Tambah Siswa --}}
            <a href="{{ route('superadmin.siswa.create') }}" class="btn-primary inline-flex items-center">
                <i class="bi bi-person-plus-fill mr-1.5"></i>
                Tambah Siswa
            </a>

            {{-- Import Form --}}
            <a href="{{ route('superadmin.siswa.import') }}" class="inline-flex items-center px-3.5 py-2 border border-amber-200 text-sm font-medium rounded-lg text-amber-700 bg-amber-50 hover:bg-amber-100 transition-colors shadow-sm">
                <i class="bi bi-file-earmark-excel mr-1.5"></i>
                Import Excel
            </a>

            {{-- Export --}}
            <a href="{{ route('superadmin.siswa.export', request()->query()) }}" class="btn-success">
                <i class="bi bi-download mr-1.5"></i>
                Export CSV
            </a>

            {{-- Migrasi --}}
            <button type="button" onclick="document.getElementById('migrasiModal').classList.remove('hidden')" class="btn-primary bg-indigo-600 hover:bg-indigo-700 ml-2">
                <i class="bi bi-arrow-left-right mr-1.5"></i>
                Migrasi
            </button>
        </div>
    </div>

    {{-- Form Bulk Action --}}
    <form id="migrasiForm" action="{{ route('superadmin.siswa.migrasi') }}" method="POST">
        @csrf
        <input type="hidden" name="action_type" id="action_type" value="naik_kelas">
        
        {{-- Modal Migrasi --}}
        <div id="migrasiModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('migrasiModal').classList.add('hidden')"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Migrasi Siswa Terpilih</h3>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500 mb-4">Pilih aksi untuk siswa-siswa yang dicentang:</p>
                                    
                                    <div class="mb-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="modal_action" value="naik_kelas" checked onchange="document.getElementById('action_type').value='naik_kelas'; document.getElementById('panel-naik').classList.remove('hidden'); document.getElementById('panel-lulus').classList.add('hidden');" class="form-radio text-indigo-600">
                                            <span class="ml-2 font-medium">Naik / Pindah Kelas</span>
                                        </label>
                                        <label class="inline-flex items-center ml-6">
                                            <input type="radio" name="modal_action" value="lulus" onchange="document.getElementById('action_type').value='lulus'; document.getElementById('panel-lulus').classList.remove('hidden'); document.getElementById('panel-naik').classList.add('hidden');" class="form-radio text-indigo-600">
                                            <span class="ml-2 font-medium">Lulus (Pindah ke Alumni)</span>
                                        </label>
                                    </div>
                                    
                                    <div id="panel-naik" class="mt-4 p-4 border rounded bg-gray-50">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kelas Tujuan</label>
                                        <select name="target_kelas" class="form-select w-full">
                                            <option value="">-- Pilih Kelas --</option>
                                            @foreach($kelasList as $k)
                                                <option value="{{ $k->id }}">{{ $k->nama_lengkap ?? $k->nama_kelas ?? $k->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div id="panel-lulus" class="mt-4 p-4 border rounded bg-gray-50 hidden">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Tahun Lulus</label>
                                        <input type="number" name="tahun_lulus" value="{{ date('Y') }}" class="form-input w-full" placeholder="Contoh: 2026">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Proses Migrasi
                        </button>
                        <button type="button" onclick="document.getElementById('migrasiModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

    {{-- Table --}}
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th class="w-12 text-center">
                        <input type="checkbox" onclick="document.querySelectorAll('.siswa-cb').forEach(cb => cb.checked = this.checked)" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </th>
                    <th>Siswa & NIS/NISN</th>
                    <th>JK</th>
                    <th>Kelas / Rombel</th>
                    <th>Poin BK</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($siswas as $s)
                <tr>
                    <td class="text-center">
                        <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="siswa-cb rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </td>
                    <td>
                        <div class="font-semibold text-gray-900">{{ $s->name ?? $s->nama_lengkap }}</div>
                        <div class="text-xs text-gray-500 font-mono">NIS: {{ $s->nip ?? $s->username ?? $s->nis }} @if($s->nisn) &bull; NISN: {{ $s->nisn }} @endif</div>
                    </td>
                    <td>{{ $s->gender ?? $s->jenis_kelamin ?? '-' }}</td>
                    <td><span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-gray-100 text-gray-800">{{ $s->kelas?->nama_kelas ?? $s->kelas?->nama ?? '-' }}</span></td>
                    <td>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            {{ $s->poin ?? 100 }} Poin
                        </span>
                    </td>
                    <td><span class="badge {{ ($s->is_active ?? true) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ ($s->is_active ?? true) ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('superadmin.siswa.edit', $s->id) }}" class="p-1.5 text-gray-500 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors inline-flex items-center gap-1 text-xs font-medium" title="Edit Data">
                                <i class="bi bi-pencil-square text-indigo-600"></i>
                                Edit
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-8 text-gray-500">Belum ada data siswa. Silahkan tambahkan data siswa baru.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </form>
    <div>{{ $siswas->links() }}</div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">👥</span> Halaman Data Siswa</h3>
    <p>Daftar data siswa terpadu HilalEdu. Siswa terhubung ke kelas dan memiliki poin awal 100.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Gunakan tombol <strong>"Tambah Siswa"</strong> atau <strong>"Import Excel"</strong> untuk menambahkan atau memperbarui data siswa</li>
        <li>Gunakan <strong>pencarian & filter kelas</strong> untuk mencari data siswa</li>
        <li>Gunakan <strong>"Export CSV"</strong> untuk mengunduh daftar siswa</li>
        <li>Klik <strong>"Edit"</strong> untuk memperbarui data siswa atau rombel</li>
    </ol>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Data siswa lengkap dan terintegrasi pada HilalEdu untuk pencatatan poin pelanggaran, presensi, dan komunikasi sekolah.</p>
</div>
@endsection
