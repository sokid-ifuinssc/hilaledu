@extends('layouts.app')

@section('title', 'Tenaga Kependidikan (Tendik) - HilalEdu')

@section('content')
<div class="space-y-6">

    <!-- Header Hero Banner -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-950 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10">
            <span class="px-3 py-1 bg-amber-400/20 text-amber-300 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider inline-flex items-center gap-1.5">
                <i class="bi bi-person-badge-fill"></i> Personalia & Staf
            </span>
            <h1 class="text-2xl md:text-3xl font-black mt-2 tracking-tight">Tenaga Kependidikan (Tendik)</h1>
            <p class="text-slate-300 text-xs md:text-sm mt-1 max-w-2xl leading-relaxed">
                Kelola data tenaga kependidikan (TU, staf administrasi, operator, laboran) dan delegasi hak akses pengelola akademik secara terpadu.
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap relative z-10">
            <a href="{{ route('superadmin.credentials.print', ['role' => 'tendik']) }}" target="_blank" class="px-3.5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-2xl text-xs flex items-center gap-2 border border-white/20 transition">
                <i class="bi bi-printer"></i>
                <span>Cetak Akun</span>
            </a>
            <a href="{{ route('superadmin.tendik.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl text-xs flex items-center gap-2 shadow-lg transition">
                <i class="bi bi-person-plus-fill"></i>
                <span>Tambah Tendik</span>
            </a>
            <a href="{{ route('superadmin.tendik.import') }}" class="px-3.5 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-2xl text-xs flex items-center gap-2 shadow-lg transition">
                <i class="bi bi-file-earmark-excel"></i>
                <span>Import Excel</span>
            </a>
            <a href="{{ route('superadmin.tendik.export') }}" class="px-3.5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-2xl text-xs flex items-center gap-2 border border-white/20 transition">
                <i class="bi bi-download"></i>
                <span>Export CSV</span>
            </a>
            <button type="button" onclick="document.getElementById('modalTunjukTendik').classList.remove('hidden')" class="px-4 py-2.5 bg-sky-500 hover:bg-sky-600 text-white font-bold rounded-2xl text-xs flex items-center gap-2 shadow-lg transition">
                <i class="bi bi-shield-check"></i>
                <span>Tunjuk Pengelola</span>
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-2xl flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-3">
            <i class="bi bi-check-circle-fill text-xl text-emerald-600"></i>
            <span class="font-bold text-xs">{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800"><i class="bi bi-x-lg"></i></button>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-2xl shadow-xs">
        <div class="flex items-center gap-2 font-bold text-xs mb-1">
            <i class="bi bi-exclamation-triangle-fill text-rose-600"></i> Terjadi kesalahan:
        </div>
        <ul class="list-disc list-inside text-[11px] space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Summary Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-3 hover:shadow-sm transition">
            <div class="w-11 h-11 bg-indigo-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="bi bi-people-fill text-indigo-600 text-xl"></i>
            </div>
            <div class="min-w-0">
                <div class="text-2xl font-black text-slate-800">{{ $total ?? 0 }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Total Tendik</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-3 hover:shadow-sm transition">
            <div class="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="bi bi-check-circle-fill text-emerald-600 text-xl"></i>
            </div>
            <div class="min-w-0">
                <div class="text-2xl font-black text-emerald-600">{{ $aktif ?? 0 }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Tendik Aktif</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-3 hover:shadow-sm transition">
            <div class="w-11 h-11 bg-rose-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="bi bi-slash-circle text-rose-600 text-xl"></i>
            </div>
            <div class="min-w-0">
                <div class="text-2xl font-black text-slate-800">{{ $nonaktif ?? 0 }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Nonaktif</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-3 hover:shadow-sm transition">
            <div class="w-11 h-11 bg-sky-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="bi bi-shield-lock-fill text-sky-600 text-xl"></i>
            </div>
            <div class="min-w-0">
                <div class="text-2xl font-black text-sky-700">{{ isset($pengelolas) ? $pengelolas->count() : 0 }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Pengelola Akademik</div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('superadmin.tendik.index') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div class="relative flex-1 sm:w-72">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" placeholder="Cari nama, username, NIP..." value="{{ request('search') }}" class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
            </div>
            <select name="status" class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition">
                Filter
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('superadmin.tendik.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800 text-xs font-medium">Reset</a>
            @endif
        </form>
    </div>

    <!-- TABEL 1: DAFTAR TENAGA KEPENDIDIKAN -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="bi bi-people-fill text-indigo-600"></i>
                <span>Daftar Seluruh Tenaga Kependidikan</span>
            </h2>
            <span class="text-xs text-slate-500">Menampilkan {{ isset($tendiks) ? $tendiks->count() : 0 }} data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Nama Staf Tendik</th>
                        <th class="px-6 py-3.5">Username / Email</th>
                        <th class="px-6 py-3.5">NIP / Kontak</th>
                        <th class="px-6 py-3.5">Jabatan / Bagian</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tendiks ?? [] as $t)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs shrink-0">
                                    {{ strtoupper(substr($t->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $t->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $t->role }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-mono text-indigo-700 font-bold">{{ '@' . $t->username }}</div>
                            <div class="text-[11px] text-slate-400">{{ $t->email ?: '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">{{ $t->nip ?: '-' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $t->no_hp ?: '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                {{ $t->jabatan_utama ?: 'Tendik' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($t->is_active)
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full font-bold text-[10px]">Aktif</span>
                            @else
                                <span class="px-2.5 py-1 bg-rose-100 text-rose-800 rounded-full font-bold text-[10px]">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('superadmin.tendik.edit', $t->id) }}" class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Edit Data">
                                    <i class="bi bi-pencil-square text-sm"></i>
                                </a>
                                <form action="{{ route('superadmin.tendik.toggle-active', $t->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-1.5 {{ $t->is_active ? 'text-amber-500 hover:bg-amber-50' : 'text-emerald-500 hover:bg-emerald-50' }} rounded-lg transition" title="{{ $t->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="bi {{ $t->is_active ? 'bi-pause-circle' : 'bi-play-circle' }} text-sm"></i>
                                    </button>
                                </form>
                                <form action="{{ route('superadmin.tendik.destroy', $t->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus staf {{ $t->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                        <i class="bi bi-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <i class="bi bi-people text-4xl mb-2 block text-slate-300"></i>
                            Belum ada data tenaga kependidikan terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($tendiks) && method_exists($tendiks, 'hasPages') && $tendiks->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $tendiks->links() }}
        </div>
        @endif
    </div>

    <!-- TABEL 2: HAK AKSES PENGELOLA AKADEMIK -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="bi bi-shield-lock-fill text-sky-600"></i>
                <span>Daftar Tendik Pengelola Akademik Aktif ({{ isset($pengelolas) ? $pengelolas->count() : 0 }})</span>
            </h2>
            <button type="button" onclick="document.getElementById('modalTunjukTendik').classList.remove('hidden')" class="px-3 py-1.5 bg-sky-50 text-sky-700 hover:bg-sky-100 rounded-xl font-bold transition text-xs flex items-center gap-1.5">
                <i class="bi bi-person-plus"></i> Tunjuk Tendik Baru
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Nama Staf Tendik</th>
                        <th class="px-6 py-3.5">Username / Email</th>
                        <th class="px-6 py-3.5 text-center">Hak Kelola Jadwal</th>
                        <th class="px-6 py-3.5 text-center">Hak Kelola Mapel</th>
                        <th class="px-6 py-3.5 text-center">Akses Laporan</th>
                        <th class="px-6 py-3.5">Keterangan / Ditunjuk Oleh</th>
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pengelolas ?? [] as $p)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $p->user->name ?? '-' }}</div>
                            <div class="text-[11px] text-slate-400">Tendik SMK Plus Al-Hilal</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-mono text-sky-700 font-bold">{{ '@' . ($p->user->username ?? '-') }}</div>
                            <div class="text-[11px] text-slate-400">{{ $p->user->email ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($p->can_manage_jadwal)
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg font-bold text-[10px]">Aktif</span>
                            @else
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-lg text-[10px]">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($p->can_manage_mapel)
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg font-bold text-[10px]">Aktif</span>
                            @else
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-lg text-[10px]">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-lg font-bold text-[10px]">Kehadiran & KBM</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">{{ $p->keterangan ?: 'Staf Pengelola Akademik' }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                Ditunjuk oleh: {{ $p->ditunjukOleh->name ?? 'Super Admin' }} &bull; {{ $p->created_at ? $p->created_at->format('d/m/Y') : '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if(Route::has('admin.tendik.destroy'))
                            <form action="{{ route('admin.tendik.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mencabut wewenang pengelola akademik staf {{ $p->user->name ?? '' }}?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-xl font-bold transition text-xs flex items-center gap-1 mx-auto" title="Cabut Wewenang">
                                    <i class="bi bi-trash"></i> Cabut
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                            <i class="bi bi-shield-slash text-3xl mb-2 block text-slate-300"></i>
                            Belum ada staf Tendik yang ditunjuk khusus sebagai Pengelola Akademik.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Tunjuk Tendik Baru -->
<div id="modalTunjukTendik" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex items-center justify-between">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i class="bi bi-person-plus"></i> Tunjuk Tendik Pengelola Akademik
            </h3>
            <button type="button" onclick="document.getElementById('modalTunjukTendik').classList.add('hidden')" class="text-white/70 hover:text-white text-xl">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form action="{{ Route::has('admin.tendik.store') ? route('admin.tendik.store') : '#' }}" method="POST" class="p-6 space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Pilih Staf Tendik <span class="text-rose-500">*</span></label>
                <select name="user_id" required class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-500 outline-none text-xs">
                    <option value="">-- Pilih Tendik SMK Plus Al-Hilal --</option>
                    @foreach($tendiks ?? [] as $t)
                    <option value="{{ $t->id }}">
                        {{ $t->name }} ({{ '@' . $t->username }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl space-y-2 border border-slate-200">
                <div class="font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1">Pemberian Izin Akses:</div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="can_manage_jadwal" value="1" checked class="rounded text-blue-600 focus:ring-blue-500">
                    <span class="font-medium text-slate-700">Dapat Menginput & Mengatur Jadwal Pelajaran</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="can_manage_mapel" value="1" checked class="rounded text-blue-600 focus:ring-blue-500">
                    <span class="font-medium text-slate-700">Dapat Menginput & Mengatur Mata Pelajaran</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="can_view_laporan_kehadiran" value="1" checked class="rounded text-blue-600 focus:ring-blue-500">
                    <span class="font-medium text-slate-700">Dapat Melihat & Mencetak Laporan Kehadiran Guru</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="can_view_laporan_kbm" value="1" checked class="rounded text-blue-600 focus:ring-blue-500">
                    <span class="font-medium text-slate-700">Dapat Melihat & Mencetak Laporan Realisasi KBM Guru</span>
                </label>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan / Keterangan Penugasan</label>
                <input type="text" name="keterangan" placeholder="Contoh: Petugas input jadwal semester ganjil" class="w-full p-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-500 outline-none text-xs">
            </div>

            <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalTunjukTendik').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i class="bi bi-check-circle-fill"></i> Simpan Penunjukan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
