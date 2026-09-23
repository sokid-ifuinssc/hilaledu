@extends('layouts.app')

@section('title', 'Direktori Data Siswa')

@section('content')
<div class="space-y-6" x-data="{
    editModal: false,
    form: {
        id: '',
        nama_lengkap: '',
        nis: '',
        nisn: '',
        kelas_id: '',
        jenis_kelamin: 'L',
        tempat_lahir: '',
        tanggal_lahir: '',
        alamat: '',
        no_hp: '',
        nama_wali: '',
        no_hp_wali: '',
        status: 'aktif'
    },
    openEdit(s) {
        this.form = {
            id: s.id,
            nama_lengkap: s.nama_lengkap || '',
            nis: s.nis || '',
            nisn: s.nisn || '',
            kelas_id: s.kelas_id || '',
            jenis_kelamin: s.jenis_kelamin || 'L',
            tempat_lahir: s.tempat_lahir || '',
            tanggal_lahir: s.tanggal_lahir ? s.tanggal_lahir.substring(0, 10) : '',
            alamat: s.alamat || '',
            no_hp: s.no_hp || '',
            nama_wali: s.nama_wali || '',
            no_hp_wali: s.no_hp_wali || '',
            status: s.status || 'aktif'
        };
        this.editModal = true;
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200 mb-2">
                <i class="bi-mortarboard-fill text-emerald-600"></i>
                <span>Database Siswa &bull; Terhubung Langsung ke HilalEdu</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                Direktori Data Siswa
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Data {{ $stats['total'] }} peserta didik aktif. Super Admin dapat memperbarui identitas, NIS/NISN, rombel kelas, dan kontak yang otomatis tersinkron ke database HilalEdu.
            </p>
        </div>

        <!-- Quick Stats -->
        <div class="flex items-center gap-2 flex-wrap">
            <div class="px-3.5 py-2 rounded-2xl bg-slate-100 text-center">
                <div class="text-lg font-black text-slate-900">{{ $stats['total'] }}</div>
                <div class="text-[10px] font-bold text-slate-500 uppercase">Total Siswa</div>
            </div>
            <div class="px-3.5 py-2 rounded-2xl bg-blue-50 text-blue-900 border border-blue-200 text-center">
                <div class="text-lg font-black text-blue-600">{{ $stats['laki'] }}</div>
                <div class="text-[10px] font-bold text-blue-800 uppercase">Laki-Laki</div>
            </div>
            <div class="px-3.5 py-2 rounded-2xl bg-rose-50 text-rose-900 border border-rose-200 text-center">
                <div class="text-lg font-black text-rose-600">{{ $stats['perempuan'] }}</div>
                <div class="text-[10px] font-bold text-rose-800 uppercase">Perempuan</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.siswas.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div class="sm:col-span-2">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama siswa, NIS, atau NISN..."
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
            </div>
            <div class="flex items-center gap-2">
                <select name="kelas_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                    <option value="">Semua Rombel Kelas</option>
                    @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ $kelasFilter == $k->id ? 'selected' : '' }}>
                        {{ $k->nama }} ({{ $k->wali_kelas ?: 'Wali -' }})
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition flex-shrink-0">
                    <i class="bi-search"></i>
                    <span class="hidden sm:inline">Cari</span>
                </button>
                @if($search || $kelasFilter)
                <a href="{{ route('admin.siswas.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition flex-shrink-0">
                    <i class="bi-x-lg"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] font-black uppercase text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Nama Lengkap & NIS</th>
                        <th class="px-5 py-3.5">Kelas / Rombel</th>
                        <th class="px-5 py-3.5">Wali Kelas</th>
                        <th class="px-5 py-3.5">Gender</th>
                        <th class="px-5 py-3.5">Nama Wali & Kontak</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($siswas as $s)
                    <tr class="hover:bg-slate-50/70 transition">
                        <!-- Nama & NIS -->
                        <td class="px-5 py-4">
                            <div class="font-extrabold text-slate-900 text-sm leading-tight">
                                {{ $s->nama_lengkap }}
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5 font-mono flex items-center gap-2">
                                <span class="bg-slate-100 px-1.5 py-0.2 rounded">NIS: {{ $s->nis }}</span>
                                @if($s->nisn)
                                <span>&bull; NISN: {{ $s->nisn }}</span>
                                @endif
                            </div>
                        </td>

                        <!-- Kelas -->
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 rounded-xl font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs inline-block">
                                {{ $s->kelas?->nama ?? 'Kelas #' . $s->kelas_id }}
                            </span>
                        </td>

                        <!-- Wali Kelas -->
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-800">
                                {{ $s->kelas->wali_kelas ?? '-' }}
                            </div>
                            <div class="text-[10px] text-slate-400">Tingkat {{ $s->kelas->tingkat ?? '-' }}</div>
                        </td>

                        <!-- Gender -->
                        <td class="px-5 py-4 whitespace-nowrap">
                            @if($s->jenis_kelamin === 'L')
                            <span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 font-bold border border-blue-200 text-[11px]">
                                Laki-laki
                            </span>
                            @else
                            <span class="px-2 py-0.5 rounded-lg bg-rose-50 text-rose-700 font-bold border border-rose-200 text-[11px]">
                                Perempuan
                            </span>
                            @endif
                        </td>

                        <!-- Orang Tua & Kontak -->
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-800">{{ $s->nama_wali ?: '-' }}</div>
                            @if($s->no_hp_wali || $s->no_hp)
                            <div class="text-[11px] text-slate-500 mt-0.5 font-mono">
                                {{ $s->no_hp_wali ?: $s->no_hp }}
                            </div>
                            @endif
                        </td>

                        <!-- Status -->
                        <td class="px-5 py-4 text-center whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 uppercase">
                                {{ $s->status ?: 'Aktif' }}
                            </span>
                        </td>

                        <!-- Aksi Edit -->
                        <td class="px-5 py-4 text-center whitespace-nowrap">
                            <button type="button" @click="openEdit({{ json_encode($s) }})" 
                                    class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold rounded-xl text-xs inline-flex items-center gap-1.5 transition border border-emerald-200 shadow-2xs">
                                <i class="bi-pencil-square"></i>
                                <span>Edit</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                            <i class="bi-search text-3xl mb-2 inline-block text-slate-300"></i>
                            <p class="text-xs font-semibold">Tidak ada data siswa yang sesuai kriteria pencarian.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($siswas->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $siswas->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL EDIT DATA SISWA (TERHUBUNG KE HILALEDU) -->
    <div x-show="editModal" style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs overflow-y-auto">
        <div @click.away="editModal = false" class="bg-white rounded-3xl p-6 sm:p-8 max-w-2xl w-full border border-slate-200 shadow-2xl space-y-5 my-8">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg font-black text-slate-900">Edit Data Peserta Didik</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                            Sinkron HilalEdu
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Perubahan langsung tersimpan di database hilaledu.siswas.
                    </p>
                </div>
                <button type="button" @click="editModal = false" class="p-2 rounded-xl hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
                    <i class="bi-x-lg text-sm"></i>
                </button>
            </div>

            <form :action="'{{ url('/admin/siswas') }}/' + form.id" method="POST" class="space-y-4 text-xs">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Lengkap -->
                    <div class="sm:col-span-2">
                        <label class="font-bold text-slate-700 block mb-1">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_lengkap" x-model="form.nama_lengkap" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- NIS & NISN -->
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">NIS <span class="text-rose-500">*</span></label>
                        <input type="text" name="nis" x-model="form.nis" required placeholder="Nomor Induk Siswa"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono font-bold">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">NISN</label>
                        <input type="text" name="nisn" x-model="form.nisn" placeholder="Nomor Induk Siswa Nasional"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono">
                    </div>

                    <!-- Kelas & Gender -->
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Rombel Kelas <span class="text-rose-500">*</span></label>
                        <select name="kelas_id" x-model="form.kelas_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelasList as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" x-model="form.jenis_kelamin" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>

                    <!-- Tempat & Tanggal Lahir -->
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" x-model="form.tempat_lahir" placeholder="Contoh: Cirebon"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" x-model="form.tanggal_lahir"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>

                    <!-- Kontak Siswa & Status -->
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Nomor HP Siswa</label>
                        <input type="text" name="no_hp" x-model="form.no_hp" placeholder="08..."
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Status Kesiswaan</label>
                        <select name="status" x-model="form.status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                            <option value="aktif">Aktif</option>
                            <option value="tidak_aktif">Tidak Aktif / Keluar</option>
                            <option value="lulus">Lulus</option>
                            <option value="pindah">Pindah Sekolah</option>
                        </select>
                    </div>

                    <!-- Orang Tua / Wali -->
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Nama Orang Tua / Wali</label>
                        <input type="text" name="nama_wali" x-model="form.nama_wali" placeholder="Nama ayah / ibu / wali"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Nomor HP Orang Tua / Wali</label>
                        <input type="text" name="no_hp_wali" x-model="form.no_hp_wali" placeholder="08..."
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                    </div>

                    <!-- Alamat -->
                    <div class="sm:col-span-2">
                        <label class="font-bold text-slate-700 block mb-1">Alamat Tempat Tinggal</label>
                        <textarea name="alamat" x-model="form.alamat" rows="2" placeholder="Alamat lengkap domisili siswa..."
                                  class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl"></textarea>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="editModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition flex items-center gap-2">
                        <i class="bi-check2-circle text-base"></i>
                        <span>Simpan Perubahan Siswa</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
