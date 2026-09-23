@extends('layouts.app')

@section('title', 'Direktori Data Dewan Guru')

@section('content')
<div class="space-y-6" x-data="{
    editModal: false,
    form: {
        id: '',
        name: '',
        username: '',
        email: '',
        nip: '',
        nuptk: '',
        no_hp: '',
        jenis_kelamin: 'L',
        jabatan_utama: 'Guru',
        tugas_tambahan: [],
        bidang_studi: '',
        status_kepegawaian: 'GTY',
        is_active: true,
        password: ''
    },
    openEdit(g) {
        this.form = {
            id: g.id,
            name: g.name || '',
            username: g.username || '',
            email: g.email || '',
            nip: g.nip || '',
            nuptk: g.nuptk || '',
            no_hp: g.no_hp || '',
            jenis_kelamin: g.jenis_kelamin || 'L',
            jabatan_utama: g.jabatan_utama || 'Guru',
            tugas_tambahan: Array.isArray(g.tugas_tambahan) ? [...g.tugas_tambahan] : (g.tugas_tambahan ? [g.tugas_tambahan] : []),
            bidang_studi: g.bidang_studi || '',
            status_kepegawaian: g.status_kepegawaian || 'GTY',
            is_active: Boolean(g.is_active),
            password: ''
        };
        this.editModal = true;
    },
    toggleTugas(val) {
        const idx = this.form.tugas_tambahan.indexOf(val);
        if (idx > -1) {
            this.form.tugas_tambahan.splice(idx, 1);
        } else {
            this.form.tugas_tambahan.push(val);
        }
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold border border-blue-200 mb-2">
                <i class="bi-person-lines-fill text-blue-600"></i>
                <span>Database Dewan Guru &bull; Terhubung Langsung ke HilalEdu</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                Direktori Data Dewan Guru
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Data {{ $stats['total'] }} dewan guru resmi. Super Admin dapat memperbarui data guru secara langsung dan otomatis tersinkron ke database pusat HilalEdu.
            </p>
        </div>

        <!-- Quick Stats -->
        <div class="flex items-center gap-2 flex-wrap">
            <div class="px-3.5 py-2 rounded-2xl bg-slate-100 text-center">
                <div class="text-lg font-black text-slate-900">{{ $stats['total'] }}</div>
                <div class="text-[10px] font-bold text-slate-500 uppercase">Total Guru</div>
            </div>
            <div class="px-3.5 py-2 rounded-2xl bg-amber-50 text-amber-900 border border-amber-200 text-center">
                <div class="text-lg font-black text-amber-600">{{ $stats['manajemen_waka'] }}</div>
                <div class="text-[10px] font-bold text-amber-800 uppercase">Pimpinan / Waka</div>
            </div>
            <div class="px-3.5 py-2 rounded-2xl bg-purple-50 text-purple-900 border border-purple-200 text-center">
                <div class="text-lg font-black text-purple-600">{{ $stats['kaprog'] }}</div>
                <div class="text-[10px] font-bold text-purple-800 uppercase">Kaprog</div>
            </div>
            <div class="px-3.5 py-2 rounded-2xl bg-emerald-50 text-emerald-900 border border-emerald-200 text-center">
                <div class="text-lg font-black text-emerald-600">{{ $stats['wali_kelas'] }}</div>
                <div class="text-[10px] font-bold text-emerald-800 uppercase">Wali Kelas</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.gurus.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div class="sm:col-span-2">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama guru, NIP, username, atau email..."
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
            </div>
            <div class="flex items-center gap-2">
                <select name="tugas" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                    <option value="">Semua Tugas Tambahan</option>
                    <option value="Wakasek" {{ $tugasFilter === 'Wakasek' ? 'selected' : '' }}>Wakasek / Pimpinan</option>
                    <option value="Kaprog" {{ $tugasFilter === 'Kaprog' ? 'selected' : '' }}>Ketua Program (Kaprog)</option>
                    <option value="Wali Kelas" {{ $tugasFilter === 'Wali Kelas' ? 'selected' : '' }}>Wali Kelas</option>
                    <option value="Bendahara" {{ $tugasFilter === 'Bendahara' ? 'selected' : '' }}>Bendahara BOS</option>
                    <option value="Konseling" {{ $tugasFilter === 'Konseling' ? 'selected' : '' }}>Guru BK</option>
                </select>
                <button type="submit" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition flex-shrink-0">
                    <i class="bi-search"></i>
                    <span class="hidden sm:inline">Cari</span>
                </button>
                @if($search || $tugasFilter)
                <a href="{{ route('admin.gurus.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition flex-shrink-0" title="Reset Filter">
                    <i class="bi-arrow-counterclockwise"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Data Guru -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-black uppercase text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 text-center w-16">Kode</th>
                        <th class="px-5 py-3.5">Nama & Identitas</th>
                        <th class="px-5 py-3.5">Username & Akun</th>
                        <th class="px-5 py-3.5">Tugas Tambahan & Jabatan</th>
                        <th class="px-5 py-3.5">Kontak</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($gurus as $g)
                    @php
                        $kode = $g->kode_guru;
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition">
                        <!-- Kode Jadwal -->
                        <td class="px-5 py-4 text-center">
                            @if($kode)
                            <span class="w-7 h-7 rounded-xl bg-slate-900 text-white font-black text-xs inline-flex items-center justify-center shadow-xs">
                                {{ $kode }}
                            </span>
                            @elseif($g->id == 514)
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 font-bold text-[10px]" title="Bendahara BOS (Non-KBM)">Non-KBM</span>
                            @else
                            <span class="text-slate-300">-</span>
                            @endif
                        </td>

                        <!-- Nama & NIP -->
                        <td class="px-5 py-4">
                            <div class="font-extrabold text-slate-900 text-sm leading-tight">
                                {{ $g->name }}
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5 font-mono">
                                NIP: {{ $g->nip ?: 'Non-NIP' }}
                                @if(!empty($g->nuptk))
                                &bull; NUPTK: {{ $g->nuptk }}
                                @endif
                            </div>
                        </td>

                        <!-- Username & Email -->
                        <td class="px-5 py-4">
                            <div class="font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-lg inline-block text-[11px]">
                                {{ $g->username }}
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5 truncate max-w-[180px]">
                                {{ $g->email }}
                            </div>
                        </td>

                        <!-- Tugas Tambahan -->
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-1 max-w-sm">
                                @if(!empty($g->tugas_tambahan) && is_array($g->tugas_tambahan))
                                    @foreach($g->tugas_tambahan as $tugas)
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        {{ $tugas }}
                                    </span>
                                    @endforeach
                                @else
                                    <span class="text-slate-400 text-[11px] italic">Guru Pengajar</span>
                                @endif
                            </div>
                        </td>

                        <!-- Kontak -->
                        <td class="px-5 py-4 whitespace-nowrap">
                            @if($g->no_hp)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $g->no_hp) }}" target="_blank"
                               class="text-emerald-700 hover:text-emerald-800 font-bold inline-flex items-center gap-1.5">
                                <i class="bi-whatsapp text-emerald-600"></i>
                                <span>{{ $g->no_hp }}</span>
                            </a>
                            @else
                            <span class="text-slate-300">-</span>
                            @endif
                        </td>

                        <!-- Status -->
                        <td class="px-5 py-4 text-center whitespace-nowrap">
                            @if($g->is_active)
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                Aktif
                            </span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-300">
                                Nonaktif
                            </span>
                            @endif
                        </td>

                        <!-- Aksi Edit -->
                        <td class="px-5 py-4 text-center whitespace-nowrap">
                            <button type="button" @click="openEdit({{ json_encode($g) }})" 
                                    class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-xl text-xs inline-flex items-center gap-1.5 transition border border-blue-200 shadow-2xs">
                                <i class="bi-pencil-square"></i>
                                <span>Edit</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                            <i class="bi-search text-3xl mb-2 inline-block text-slate-300"></i>
                            <p class="text-xs font-semibold">Tidak ada data guru yang sesuai kriteria pencarian.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($gurus->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $gurus->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL EDIT DATA GURU (TERHUBUNG KE HILALEDU) -->
    <div x-show="editModal" style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs overflow-y-auto">
        <div @click.away="editModal = false" class="bg-white rounded-3xl p-6 sm:p-8 max-w-2xl w-full border border-slate-200 shadow-2xl space-y-5 my-8">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg font-black text-slate-900">Edit Data Dewan Guru</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                            Sinkron HilalEdu
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Perubahan akan langsung terupdate ke database lokal dan database utama HilalEdu.
                    </p>
                </div>
                <button type="button" @click="editModal = false" class="p-2 rounded-xl hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
                    <i class="bi-x-lg text-sm"></i>
                </button>
            </div>

            <form :action="'{{ url('/admin/gurus') }}/' + form.id" method="POST" class="space-y-4 text-xs">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Lengkap -->
                    <div class="sm:col-span-2">
                        <label class="font-bold text-slate-700 block mb-1">Nama Lengkap & Gelar <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="form.name" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- NIP & NUPTK -->
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">NIP</label>
                        <input type="text" name="nip" x-model="form.nip" placeholder="Contoh: 19850101..."
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">NUPTK</label>
                        <input type="text" name="nuptk" x-model="form.nuptk" placeholder="Nomor Unik Pendidik"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono">
                    </div>

                    <!-- Username & Email -->
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Username Login <span class="text-rose-500">*</span></label>
                        <input type="text" name="username" x-model="form.username" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono font-bold">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" x-model="form.email" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>

                    <!-- Kontak No HP & Jenis Kelamin -->
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Nomor WhatsApp / HP</label>
                        <input type="text" name="no_hp" x-model="form.no_hp" placeholder="08..."
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" x-model="form.jenis_kelamin" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>

                    <!-- Jabatan Utama & Status Kepegawaian -->
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Jabatan Utama</label>
                        <input type="text" name="jabatan_utama" x-model="form.jabatan_utama" placeholder="Guru / Guru Produktif"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Status Kepegawaian</label>
                        <select name="status_kepegawaian" x-model="form.status_kepegawaian" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                            <option value="GTY">GTY (Guru Tetap Yayasan)</option>
                            <option value="GTT">GTT (Guru Tidak Tetap)</option>
                            <option value="PNS">PNS DPK</option>
                            <option value="PPPK">PPPK</option>
                            <option value="Honorer">Honorer</option>
                        </select>
                    </div>

                    <!-- Bidang Studi -->
                    <div class="sm:col-span-2">
                        <label class="font-bold text-slate-700 block mb-1">Bidang Studi / Mata Pelajaran Utama</label>
                        <input type="text" name="bidang_studi" x-model="form.bidang_studi" placeholder="Contoh: Teknik Komputer & Jaringan / Bahasa Indonesia"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>

                    <!-- Multi Tugas Tambahan -->
                    <div class="sm:col-span-2">
                        <label class="font-bold text-slate-700 block mb-1">Tugas Tambahan Sekolah (Pilih yang Sesuai)</label>
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl max-h-36 overflow-y-auto space-y-1.5">
                            @foreach($daftarTugasTambahan as $tt)
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-white p-1 rounded-lg transition">
                                <input type="checkbox" name="tugas_tambahan[]" value="{{ $tt }}"
                                       :checked="form.tugas_tambahan.includes('{{ $tt }}')"
                                       @change="toggleTugas('{{ $tt }}')"
                                       class="rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-xs font-semibold text-slate-800">{{ $tt }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Password Baru (Opsional) -->
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Password Baru (Opsional)</label>
                        <input type="password" name="password" x-model="form.password" placeholder="Kosongkan jika tidak diubah"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono">
                    </div>

                    <!-- Status Keaktifan Akun -->
                    <div class="flex items-center gap-2 pt-6">
                        <input type="checkbox" name="is_active" id="is_active_check" value="1" x-model="form.is_active"
                               class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                        <label for="is_active_check" class="font-bold text-slate-800 cursor-pointer">
                            Akun Aktif (Dapat Login HilalEdu)
                        </label>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="editModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition flex items-center gap-2">
                        <i class="bi-check2-circle text-base"></i>
                        <span>Simpan & Sinkronkan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
