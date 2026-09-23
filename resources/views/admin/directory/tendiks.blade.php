@extends('layouts.app')

@section('title', 'Direktori Tenaga Kependidikan (Tendik)')

@section('content')
<div class="space-y-6" x-data="{
    editModal: false,
    form: {
        id: '',
        nama_lengkap: '',
        nuptk: '',
        nip: '',
        email: '',
        no_hp: '',
        jenis_kelamin: 'L',
        jabatan: '',
        tugas: '',
        status_kepegawaian: 'Pegawai Tetap',
        is_active: true,
        password: ''
    },
    openEdit(t) {
        this.form = {
            id: t.id,
            nama_lengkap: t.nama_lengkap || '',
            nuptk: t.nuptk || '',
            nip: t.nip || '',
            email: t.email || '',
            no_hp: t.no_hp || '',
            jenis_kelamin: t.jenis_kelamin || 'L',
            jabatan: t.jabatan || '',
            tugas: t.tugas || '',
            status_kepegawaian: t.status_kepegawaian || 'Pegawai Tetap',
            is_active: Boolean(t.is_active ?? true),
            password: ''
        };
        this.editModal = true;
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-50 text-purple-700 text-xs font-bold border border-purple-200 mb-2">
                <i class="bi-people-fill text-purple-600"></i>
                <span>Database Tenaga Kependidikan &bull; Terhubung Langsung ke HilalEdu</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                Direktori Tenaga Kependidikan (Tendik)
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Data staf tata usaha dan staf kependidikan resmi. Super Admin dapat memperbarui data tendik dan penugasan yang otomatis tersinkron ke database HilalEdu.
            </p>
        </div>

        @if(auth()->user()->isSuperAdmin())
        <div>
            <a href="{{ route('admin.tendik.index') }}" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-xs transition inline-flex items-center gap-2 shadow-sm shadow-purple-600/20">
                <i class="bi-person-gear"></i>
                <span>Kelola Penunjukan Hak Akses Tendik</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Data Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($tendiks as $t)
        @php
            $isPengelola = in_array($t->user_id, $pengelolaUserIds);
        @endphp
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs hover:shadow-md transition flex flex-col justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-800 font-black text-lg flex items-center justify-center shrink-0">
                    {{ strtoupper(substr($t->nama_lengkap, 0, 2)) }}
                </div>
                <div class="space-y-1 flex-1">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <h3 class="font-extrabold text-slate-900 text-base">{{ $t->nama_lengkap }}</h3>
                        <div class="flex items-center gap-1.5">
                            @if($isPengelola)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300">
                                Pengelola Akademik
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                Staf Tata Usaha
                            </span>
                            @endif

                            <button type="button" @click="openEdit({{ json_encode($t) }})" 
                                    class="px-2.5 py-1 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold rounded-lg text-xs inline-flex items-center gap-1 transition border border-purple-200 shadow-2xs">
                                <i class="bi-pencil-square"></i>
                                <span>Edit</span>
                            </button>
                        </div>
                    </div>
                    <p class="text-xs font-semibold text-slate-500 font-mono">
                        NUPTK: {{ $t->nuptk ?: ($t->nip ?: 'Non-NUPTK') }}
                    </p>
                    <p class="text-xs text-slate-700 font-medium pt-1">
                        <strong>Jabatan:</strong> {{ $t->jabatan ?: 'Staf Administrasi' }}
                    </p>
                    @if($t->email)
                    <p class="text-[11px] text-slate-500">
                        <i class="bi-envelope"></i> {{ $t->email }}
                    </p>
                    @endif
                    @if($t->no_hp)
                    <p class="text-[11px] text-emerald-700 font-bold">
                        <i class="bi-whatsapp"></i> {{ $t->no_hp }}
                    </p>
                    @endif
                </div>
            </div>

            <div class="border-t border-slate-100 pt-3 flex items-center justify-between text-xs text-slate-500">
                <span>Status Kepegawaian: <strong class="text-slate-800">{{ $t->status_kepegawaian ?: 'Pegawai Tetap' }}</strong></span>
                @if($t->is_active ?? true)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                @else
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Nonaktif</span>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-2 bg-white rounded-3xl p-12 text-center text-slate-400 border border-slate-200">
            <i class="bi-people text-4xl mb-2 inline-block text-slate-300"></i>
            <p class="text-sm font-semibold">Tidak ada data tenaga kependidikan.</p>
        </div>
        @endforelse
    </div>

    <!-- MODAL EDIT DATA TENDIK (TERHUBUNG KE HILALEDU) -->
    <div x-show="editModal" style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs overflow-y-auto">
        <div @click.away="editModal = false" class="bg-white rounded-3xl p-6 sm:p-8 max-w-xl w-full border border-slate-200 shadow-2xl space-y-5 my-8">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg font-black text-slate-900">Edit Data Tenaga Kependidikan</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-800 border border-purple-300">
                            Sinkron HilalEdu
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Perubahan langsung tersimpan di database hilaledu.tendiks.
                    </p>
                </div>
                <button type="button" @click="editModal = false" class="p-2 rounded-xl hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
                    <i class="bi-x-lg text-sm"></i>
                </button>
            </div>

            <form :action="'{{ url('/admin/tendiks') }}/' + form.id" method="POST" class="space-y-4 text-xs">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Lengkap -->
                    <div class="sm:col-span-2">
                        <label class="font-bold text-slate-700 block mb-1">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_lengkap" x-model="form.nama_lengkap" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold focus:ring-2 focus:ring-purple-500">
                    </div>

                    <!-- NUPTK & NIP -->
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">NUPTK</label>
                        <input type="text" name="nuptk" x-model="form.nuptk" placeholder="Nomor Unik Pendidik"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">NIP (Jika Ada)</label>
                        <input type="text" name="nip" x-model="form.nip" placeholder="Nomor Induk Pegawai"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono">
                    </div>

                    <!-- Email & Kontak No HP -->
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Email</label>
                        <input type="email" name="email" x-model="form.email"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Nomor WhatsApp / HP</label>
                        <input type="text" name="no_hp" x-model="form.no_hp" placeholder="08..."
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>

                    <!-- Jabatan & Jenis Kelamin -->
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Jabatan Utama</label>
                        <input type="text" name="jabatan" x-model="form.jabatan" placeholder="Kepala Tata Usaha / Staf TU / Operator"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" x-model="form.jenis_kelamin" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>

                    <!-- Bagian / Tugas Tambahan -->
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Tugas / Unit Bagian</label>
                        <input type="text" name="tugas" x-model="form.tugas" placeholder="Administrasi Kepegawaian / Keuangan"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>

                    <!-- Status Kepegawaian -->
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Status Kepegawaian</label>
                        <select name="status_kepegawaian" x-model="form.status_kepegawaian" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                            <option value="Pegawai Tetap">Pegawai Tetap Yayasan</option>
                            <option value="Pegawai Tidak Tetap">Pegawai Tidak Tetap</option>
                            <option value="PNS">PNS DPK</option>
                            <option value="Honorer">Honorer</option>
                        </select>
                    </div>

                    <!-- Password Baru (Opsional) -->
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Password Akun Baru (Opsional)</label>
                        <input type="password" name="password" x-model="form.password" placeholder="Kosongkan jika tidak diubah"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono">
                    </div>

                    <!-- Status Keaktifan Akun -->
                    <div class="flex items-center gap-2 pt-6">
                        <input type="checkbox" name="is_active" id="tendik_active_check" value="1" x-model="form.is_active"
                               class="rounded text-purple-600 focus:ring-purple-500 w-4 h-4">
                        <label for="tendik_active_check" class="font-bold text-slate-800 cursor-pointer">
                            Status Pegawai Aktif
                        </label>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="editModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-md transition flex items-center gap-2">
                        <i class="bi-check2-circle text-base"></i>
                        <span>Simpan Perubahan Tendik</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
