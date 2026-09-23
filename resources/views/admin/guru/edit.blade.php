@extends('layouts.app')
@section('title', 'Atur Peran & Edit Guru')
@section('subtitle', $guru->nama_lengkap)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.guru.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Guru
        </a>
    </div>

    <form method="POST" action="{{ route('admin.guru.update', $guru->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Role Assignment Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-indigo-100 p-6">
            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-gray-100">
                <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-800">Penugasan Peran Sistem Monitoring BK</h3>
                    <p class="text-xs text-gray-500">Pilih hak akses guru dalam sistem (bisa memilih lebih dari satu)</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @php
                    $userRoleNames = $guru->user ? $guru->user->roles->pluck('name')->toArray() : [];
                @endphp
                @foreach($roles as $role)
                <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50/30 transition-all cursor-pointer">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                           {{ in_array($role->name, old('roles', $userRoleNames)) ? 'checked' : '' }}
                           class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <div>
                        <div class="text-sm font-semibold text-gray-800">{{ ucwords(str_replace('_', ' ', $role->name)) }}</div>
                        <div class="text-xs text-gray-500">
                            @if($role->name == 'guru_bk')
                                Dapat mencatat pelanggaran, membina siswa, dan memproses tahapan BK.
                            @elseif($role->name == 'wali_kelas')
                                Mengakses dan memantau siswa pada kelas yang diwalikan.
                            @elseif($role->name == 'kaprog')
                                Mengakses progres pelanggaran siswa di program keahlian terkait.
                            @elseif($role->name == 'kepala_sekolah')
                                Memantau rekap data dan memberikan persetujuan akhir.
                            @elseif($role->name == 'waka_kesiswaan')
                                Memantau ketertiban umum dan koordinasi penanganan pelanggaran.
                            @elseif($role->name == 'admin')
                                Akses penuh administrasi sistem.
                            @endif
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Personal Data Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h3 class="text-base font-bold text-gray-800 pb-3 border-b border-gray-100">Informasi Pendidik</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $guru->nama_lengkap) }}" required class="form-input w-full">
                    @error('nama_lengkap') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Jenis Kelamin *</label>
                    <select name="jenis_kelamin" required class="form-select w-full">
                        <option value="L" {{ old('jenis_kelamin', $guru->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin', $guru->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" value="{{ old('nip', $guru->nip) }}" class="form-input w-full">
                    @error('nip') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">NUPTK</label>
                    <input type="text" name="nuptk" value="{{ old('nuptk', $guru->nuptk) }}" class="form-input w-full">
                    @error('nuptk') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Bidang Studi / Mapel</label>
                    <input type="text" name="bidang_studi" value="{{ old('bidang_studi', $guru->bidang_studi) }}" placeholder="Contoh: Matematika, Produktif TKJ..." class="form-input w-full">
                </div>

                <div>
                    <label class="form-label">Jabatan Tambahan</label>
                    <input type="text" name="jabatan" value="{{ old('jabatan', $guru->jabatan) }}" placeholder="Contoh: Pembina OSIS, Kepala Lab..." class="form-input w-full">
                </div>

                <div>
                    <label class="form-label">Status Kepegawaian *</label>
                    <select name="status_kepegawaian" required class="form-select w-full">
                        <option value="non-PNS" {{ old('status_kepegawaian', $guru->status_kepegawaian) == 'non-PNS' ? 'selected' : '' }}>non-PNS</option>
                        <option value="PNS" {{ old('status_kepegawaian', $guru->status_kepegawaian) == 'PNS' ? 'selected' : '' }}>PNS</option>
                        <option value="honorer" {{ old('status_kepegawaian', $guru->status_kepegawaian) == 'honorer' ? 'selected' : '' }}>Honorer</option>
                        <option value="kontrak" {{ old('status_kepegawaian', $guru->status_kepegawaian) == 'kontrak' ? 'selected' : '' }}>Kontrak</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">No. WhatsApp / HP</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp', $guru->no_hp) }}" class="form-input w-full">
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $guru->email) }}" class="form-input w-full">
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" rows="2" class="form-input w-full">{{ old('alamat', $guru->alamat) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $guru->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="text-sm font-medium text-gray-700">Status Guru Aktif</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.guru.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
