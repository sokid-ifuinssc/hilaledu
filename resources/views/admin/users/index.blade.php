@extends('layouts.app')
@section('title', 'Akun Pengguna')
@section('subtitle', 'Informasi login semua pengguna')

@section('content')
<div class="space-y-5">
    {{-- Info Card --}}
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-5 text-white">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-lg">Informasi Akun Pengguna</h3>
                <p class="text-indigo-100 text-sm mt-1">Halaman ini menampilkan seluruh akun pengguna beserta informasi login. Password default untuk semua akun baru adalah <strong class="text-white">"password"</strong>. Gunakan tombol Reset Password untuk mengembalikan password ke default.</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" placeholder="Cari nama / email..." value="{{ request('search') }}" class="form-input w-64">
            <select name="role" class="form-select w-48" onchange="this.form.submit()">
                <option value="">Semua Role</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="guru_bk" {{ request('role') == 'guru_bk' ? 'selected' : '' }}>Guru BK</option>
                <option value="kaprog" {{ request('role') == 'kaprog' ? 'selected' : '' }}>Kaprog</option>
                <option value="wali_kelas" {{ request('role') == 'wali_kelas' ? 'selected' : '' }}>Wali Kelas</option>
                <option value="kepala_sekolah" {{ request('role') == 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                <option value="siswa" {{ request('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
            </select>
            <button type="submit" class="btn-primary">Cari</button>
        </form>

        <a href="{{ route('admin.users.export', request()->query()) }}" class="btn-success">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export Kredensial
        </a>
    </div>

    {{-- Table --}}
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Password</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td class="text-gray-500">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                    <td class="font-medium text-gray-900">{{ $u->nama_lengkap }}</td>
                    <td class="font-mono text-xs text-gray-600">{{ $u->name }}</td>
                    <td>
                        <span class="text-sm text-gray-700">{{ $u->email }}</span>
                    </td>
                    <td>
                        @foreach($u->roles as $role)
                        <span class="badge
                            @switch($role->name)
                                @case('admin') bg-purple-100 text-purple-700 @break
                                @case('guru_bk') bg-blue-100 text-blue-700 @break
                                @case('kaprog') bg-teal-100 text-teal-700 @break
                                @case('wali_kelas') bg-amber-100 text-amber-700 @break
                                @case('kepala_sekolah') bg-rose-100 text-rose-700 @break
                                @case('siswa') bg-green-100 text-green-700 @break
                                @default bg-gray-100 text-gray-700
                            @endswitch
                        ">{{ str_replace('_', ' ', ucfirst($role->name)) }}</span>
                        @endforeach
                    </td>
                    <td>
                        <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">password</span>
                        <span class="text-[10px] text-gray-400 block mt-0.5">(default)</span>
                    </td>
                    <td>
                        @if($u->is_active)
                            <span class="badge bg-green-100 text-green-700">Aktif</span>
                        @else
                            <span class="badge bg-red-100 text-red-700">Nonaktif</span>
                        @endif
                    </td>
                    <td class="flex items-center gap-3">
                        <a href="{{ route('admin.users.edit', $u) }}" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </a>
                        <form method="POST" action="{{ route('admin.users.resetPassword', $u) }}" onsubmit="return confirm('Reset password {{ $u->nama_lengkap }} ke default (password)?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 text-amber-600 hover:text-amber-800 text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Reset
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-8 text-gray-500">Tidak ada data pengguna</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $users->links() }}</div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">🔑</span> Halaman Akun Pengguna</h3>
    <p>Kelola akun login seluruh pengguna sistem. Setiap pengguna memiliki email, password, dan role yang menentukan hak akses mereka.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Lihat daftar semua pengguna beserta <strong>role, email,</strong> dan <strong>status akun</strong></li>
        <li>Klik <strong>"Edit"</strong> untuk mengubah data akun pengguna</li>
        <li>Klik <strong>"Reset Password"</strong> untuk mereset password pengguna ke default</li>
        <li>Gunakan <strong>"Export"</strong> untuk mengunduh daftar akun pengguna</li>
        <li>Gunakan <strong>pencarian</strong> untuk menemukan pengguna berdasarkan nama atau email</li>
    </ol>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #faf5ff; color: #7c3aed;">👤</span> Daftar Role</h3>
    <ul>
        <li><strong>Admin</strong> — Akses penuh untuk mengelola data master</li>
        <li><strong>Guru BK</strong> — Input pelanggaran, buat rekomendasi, monitoring, laporan</li>
        <li><strong>Kaprog</strong> — Lihat pelanggaran jurusan, buat rekomendasi</li>
        <li><strong>Wali Kelas</strong> — Lihat pelanggaran kelas, tindak lanjut</li>
        <li><strong>Kepala Sekolah</strong> — Dashboard ringkasan, monitoring keseluruhan</li>
        <li><strong>Siswa</strong> — Lihat riwayat pelanggaran sendiri</li>
    </ul>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Semua pengguna memiliki akun aktif dengan role yang sesuai, sehingga dapat mengakses fitur yang dibutuhkan.</p>
</div>
@endsection
