@extends('layouts.app')

@section('title', 'Penunjukan Admin Unit (Guru & TU)')
@section('page-title', 'Penunjukan Admin Unit')

@section('content')
<div class="space-y-6 pb-12 w-full max-w-7xl mx-auto">

    <!-- 1. Hero Header -->
    <div class="bg-gradient-to-r from-slate-900 via-emerald-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-sm border border-emerald-900/40 relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-semibold">
                    <i class="bi bi-person-check-fill"></i>
                    <span>Delegasi Wewenang Sistem HilalEdu</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Penunjukan Admin Unit (Guru & TU)
                </h1>
                <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Tunjuk Dewan Guru dan Staf Tata Usaha (TU) sebagai administrator pengelola modul spesifik (Akademik, Prakerin, BK, Koperasi, Keuangan, dan Tracer Study) dengan wewenang penuh tanpa memerlukan akun baru.
                </p>
                <div class="flex items-center gap-4 pt-2 text-xs text-slate-300 font-medium">
                    <span class="flex items-center gap-1.5"><i class="bi bi-people-fill text-emerald-400"></i> Total Guru: <strong>{{ $totalGuru }}</strong></span>
                    <span>&bull;</span>
                    <span class="flex items-center gap-1.5"><i class="bi bi-person-badge-fill text-blue-400"></i> Total TU: <strong>{{ $totalTendik }}</strong></span>
                    <span>&bull;</span>
                    <span class="flex items-center gap-1.5"><i class="bi bi-shield-fill-check text-amber-400"></i> Admin Ditunjuk: <strong>{{ $totalAdminDitunjuk }}</strong></span>
                </div>
            </div>
            <button type="button" 
                    class="px-5 py-3 bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-2xl font-bold text-sm transition shadow-lg shadow-emerald-500/25 flex items-center gap-2 shrink-0"
                    data-bs-toggle="modal" 
                    data-bs-target="#modalTunjukAdmin"
                    onclick="setModalRole('')">
                <i class="bi bi-person-plus-fill text-base"></i>
                <span>+ Tunjuk Admin Baru</span>
            </button>
        </div>
        <!-- Subtle glow ornament -->
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- 2. Grid 6 Unit Administrasi & Pemegang Mandat Saat Ini -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-grid-fill text-emerald-600"></i>
                <span>Status Penugasan 6 Unit Layanan</span>
            </h2>
            <span class="text-xs text-slate-500">Klik "Tunjuk Pegawai" pada unit untuk menugaskan</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($roleDefinitions as $key => $def)
                @php
                    $assigned = $currentAdminsByRole[$key] ?? collect();
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0"
                                     style="background: {{ $def['bg_soft'] }}; color: {{ $def['color'] }}; border: 1px solid {{ $def['border'] }};">
                                    <i class="bi {{ $def['icon'] }}"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm text-slate-900 leading-snug">{{ $def['name'] }}</h3>
                                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">{{ $def['badge'] }}</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                  style="background: {{ $assigned->count() > 0 ? '#ecfdf5' : '#f8fafc' }}; color: {{ $assigned->count() > 0 ? '#047857' : '#94a3b8' }}; border: 1px solid {{ $assigned->count() > 0 ? '#a7f3d0' : '#e2e8f0' }};">
                                {{ $assigned->count() }} Pegawai
                            </span>
                        </div>

                        <p class="text-xs text-slate-500 mb-4 line-clamp-2 leading-relaxed">
                            {{ $def['desc'] }}
                        </p>

                        <!-- Daftar Admin yang Ditunjuk -->
                        <div class="space-y-2 mb-4">
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pemegang Wewenang:</div>
                            @forelse($assigned as $adm)
                                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-7 h-7 rounded-lg bg-slate-800 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                            {{ strtoupper(substr($adm->name, 0, 1)) }}
                                        </div>
                                        <div class="truncate">
                                            <div class="text-xs font-bold text-slate-800 truncate">{{ $adm->name }}</div>
                                            <div class="text-[10px] text-slate-500">
                                                {{ $adm->role === 'guru' ? 'Dewan Guru' : ($adm->role === 'tendik' ? 'Staf TU' : 'Admin') }}
                                            </div>
                                        </div>
                                    </div>
                                    <form action="{{ route('superadmin.penugasan-admin.destroy', $adm) }}" method="POST" class="shrink-0" onsubmit="return confirm('Cabut peran {{ $def['name'] }} dari {{ $adm->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 p-1 text-xs" title="Cabut Penugasan">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="py-3 px-3 bg-amber-50/70 border border-dashed border-amber-200 rounded-xl text-center">
                                    <span class="text-xs text-amber-700 font-medium italic">
                                        Belum ada admin yang ditunjuk
                                    </span>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Tombol Tunjuk Cepat ke Unit ini -->
                    <button type="button" 
                            class="w-full py-2 px-3 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition border"
                            style="color: {{ $def['color'] }}; background: {{ $def['bg_soft'] }}; border-color: {{ $def['border'] }};"
                            data-bs-toggle="modal" 
                            data-bs-target="#modalTunjukAdmin"
                            onclick="setModalRole('{{ $key }}')">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>Tunjuk Pegawai ke Unit Ini</span>
                    </button>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 3. Tabel Riwayat Semua Admin Unit yang Ditugaskan -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-base text-slate-900">Daftar Pegawai yang Ditugaskan sebagai Admin</h3>
                <p class="text-xs text-slate-500 mt-0.5">Kelola seluruh delegasi wewenang operasional unit</p>
            </div>

            <!-- Filter & Search -->
            <form action="{{ route('superadmin.penugasan-admin.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nama, NIP, email..." 
                       class="px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 w-full sm:w-48">

                <select name="role_filter" class="px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 text-slate-700">
                    <option value="">Semua Unit Admin</option>
                    @foreach($roleDefinitions as $rk => $rdef)
                        <option value="{{ $rk }}" {{ request('role_filter') === $rk ? 'selected' : '' }}>
                            {{ $rdef['badge'] }}
                        </option>
                    @endforeach
                </select>

                <select name="tipe_user" class="px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 text-slate-700">
                    <option value="">Semua Jabatan Utama</option>
                    <option value="guru" {{ request('tipe_user') === 'guru' ? 'selected' : '' }}>Dewan Guru</option>
                    <option value="tendik" {{ request('tipe_user') === 'tendik' ? 'selected' : '' }}>Tata Usaha (TU)</option>
                    <option value="admin" {{ request('tipe_user') === 'admin' ? 'selected' : '' }}>Admin Sistem</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-700 transition">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>

                @if(request()->anyFilled(['search', 'role_filter', 'tipe_user']))
                    <a href="{{ route('superadmin.penugasan-admin.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4">Pegawai / Staf</th>
                        <th class="py-3.5 px-4">Jabatan Utama</th>
                        <th class="py-3.5 px-4">Unit Admin Ditugaskan</th>
                        <th class="py-3.5 px-4">Kontak / NIP</th>
                        <th class="py-3.5 px-4">Status Akun</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($assignedUsers as $user)
                        @php
                            $roleMeta = $roleDefinitions[$user->admin_role] ?? null;
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-white text-sm shrink-0"
                                         style="background: {{ $user->role === 'guru' ? 'linear-gradient(135deg, #059669, #10b981)' : 'linear-gradient(135deg, #2563eb, #38bdf8)' }};">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ $user->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $user->email }} &bull; @<span>{{ $user->username }}</span></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($user->role === 'guru')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="bi bi-person-workspace"></i> Dewan Guru
                                    </span>
                                @elseif($user->role === 'tendik')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="bi bi-person-badge"></i> Staf TU / Tendik
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        <i class="bi bi-shield"></i> {{ ucfirst($user->role) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($roleMeta)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold"
                                          style="background: {{ $roleMeta['bg_soft'] }}; color: {{ $roleMeta['color'] }}; border: 1px solid {{ $roleMeta['border'] }};">
                                        <i class="bi {{ $roleMeta['icon'] }}"></i>
                                        <span>{{ $roleMeta['name'] }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">{{ $user->admin_role }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <div><i class="bi bi-person-vcard text-slate-400 me-1"></i> NIP: {{ $user->nip ?? '-' }}</div>
                                <div><i class="bi bi-telephone text-slate-400 me-1"></i> {{ $user->no_hp ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold text-xs">
                                        <i class="bi bi-check-circle-fill"></i> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-slate-400 font-semibold text-xs">
                                        <i class="bi bi-dash-circle-fill"></i> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Edit Penugasan -->
                                    <button type="button" 
                                            class="p-2 text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition"
                                            title="Ubah Unit Admin"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalTunjukAdmin"
                                            onclick="setEditModal('{{ $user->id }}', '{{ $user->admin_role }}')">
                                        <i class="bi bi-pencil-square text-sm"></i>
                                    </button>

                                    <!-- Cabut Penugasan -->
                                    <form action="{{ route('superadmin.penugasan-admin.destroy', $user) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin mencabut wewenang admin dari {{ $user->name }}?');"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                                title="Cabut Wewenang Admin">
                                            <i class="bi bi-trash3 text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i class="bi bi-shield-x text-3xl block text-slate-300 mb-2"></i>
                                <div class="font-bold text-slate-600 text-sm">Belum Ada Admin Unit yang Ditunjuk</div>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol "+ Tunjuk Admin Baru" untuk menugaskan Guru atau TU sebagai admin modul.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assignedUsers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $assignedUsers->links() }}
            </div>
        @endif
    </div>

</div>

<!-- MODAL FORM PENUNJUKAN ADMIN UNIT -->
<div class="modal fade" id="modalTunjukAdmin" tabindex="-1" aria-labelledby="modalTunjukAdminLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-3xl shadow-2xl overflow-hidden">
            <div class="modal-header bg-gradient-to-r from-slate-900 to-emerald-950 text-white px-6 py-5 border-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-lg">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-bold text-base text-white" id="modalTunjukAdminLabel">Tunjuk / Delegasikan Admin Unit</h5>
                        <p class="text-xs text-slate-300 mt-0.5">Pilih Guru atau TU dan tetapkan modul administrasi yang dikelola</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('superadmin.penugasan-admin.store') }}" method="POST">
                @csrf
                <div class="modal-body p-6 space-y-5">

                    <!-- 1. Pilih Pegawai (Guru / TU) -->
                    <div>
                        <label for="selectUserId" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Pilih Pegawai (Guru atau Staf TU) <span class="text-rose-500">*</span>
                        </label>
                        <select name="user_id" id="selectUserId" required class="w-full px-4 py-3 text-sm rounded-xl border border-slate-300 focus:outline-none focus:border-emerald-500 bg-white">
                            <option value="">-- Pilih Pegawai --</option>
                            
                            <optgroup label="📋 Tenaga Kependidikan / Tata Usaha (TU)">
                                @foreach($candidates->where('role', 'tendik') as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->name }} (NIP: {{ $c->nip ?? '-' }}) {{ $c->admin_role ? '[Saat Ini: Admin ' . ucfirst($c->admin_role) . ']' : '' }}
                                    </option>
                                @endforeach
                            </optgroup>

                            <optgroup label="👨‍🏫 Dewan Guru">
                                @foreach($candidates->where('role', 'guru') as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->name }} (NIP: {{ $c->nip ?? '-' }}) {{ $c->admin_role ? '[Saat Ini: Admin ' . ucfirst($c->admin_role) . ']' : '' }}
                                    </option>
                                @endforeach
                            </optgroup>

                            <optgroup label="⚙️ Admin Sistem">
                                @foreach($candidates->where('role', 'admin') as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->name }} ({{ $c->email }}) {{ $c->admin_role ? '[Saat Ini: Admin ' . ucfirst($c->admin_role) . ']' : '' }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Pegawai yang ditunjuk akan langsung mendapatkan akses panel admin pada menu modul tersebut.</p>
                    </div>

                    <!-- 2. Pilih Unit / Peran Admin -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Pilih Unit Layanan yang Ditugaskan <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="roleOptionsGrid">
                            @foreach($roleDefinitions as $rKey => $rMeta)
                                <label class="p-3.5 rounded-2xl border border-slate-200 hover:border-emerald-500 transition cursor-pointer flex items-start gap-3 bg-white has-[:checked]:bg-emerald-50/70 has-[:checked]:border-emerald-500 has-[:checked]:shadow-xs">
                                    <input type="radio" 
                                           name="admin_role" 
                                           value="{{ $rKey }}" 
                                           id="radioRole_{{ $rKey }}"
                                           required 
                                           class="mt-1 text-emerald-600 focus:ring-emerald-500">
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-2">
                                            <i class="bi {{ $rMeta['icon'] }}" style="color: {{ $rMeta['color'] }};"></i>
                                            <span class="text-xs font-bold text-slate-900">{{ $rMeta['name'] }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 leading-tight">
                                            {{ Str::limit($rMeta['desc'], 75) }}
                                        </p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 3. Catatan / Surat Keputusan (SK) Opsional -->
                    <div>
                        <label for="inputKeterangan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Keterangan / Nomor SK Penugasan <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <input type="text" 
                               name="keterangan" 
                               id="inputKeterangan" 
                               placeholder="Contoh: SK Kepala Sekolah No. 421.5/082/2026 tentang Penugasan Admin"
                               class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:border-emerald-500">
                    </div>

                </div>
                <div class="modal-footer bg-slate-50 px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                    <button type="button" class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-900" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition shadow-md shadow-emerald-600/20 flex items-center gap-2">
                        <i class="bi bi-check2-circle text-base"></i>
                        <span>Simpan & Berikan Wewenang Admin</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function setModalRole(roleKey) {
        if (!roleKey) return;
        const radio = document.getElementById('radioRole_' + roleKey);
        if (radio) {
            radio.checked = true;
        }
    }

    function setEditModal(userId, currentRole) {
        const selectUser = document.getElementById('selectUserId');
        if (selectUser) {
            selectUser.value = userId;
        }
        if (currentRole) {
            setModalRole(currentRole);
        }
    }
</script>
@endsection
