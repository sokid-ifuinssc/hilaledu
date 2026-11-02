@extends('layouts.app')

@section('title', 'Isi Atribut Kesesuaian Realisasi Tugas Tambahan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" 
     x-data="formKesesuaian({
        programs: {{ Js::from($programKerjaList) }},
        selectedId: {{ $selectedProgram ? $selectedProgram->id : 'null' }}
     })">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('guru.realisasi-tugas-tambahan.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1 mb-1">
                <i class="bi-arrow-left"></i> Kembali ke Matriks Realisasi
            </a>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-clipboard2-check-fill text-indigo-600"></i>
                <span>Isi Atribut Kesesuaian Realisasi Kerja</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Evaluasi keterlaksanaan kegiatan tugas tambahan Anda terhadap rencana program kerja yang telah dibuat.
            </p>
        </div>
    </div>

    @if($programKerjaList->isEmpty())
    <!-- Peringatan Jika Belum Ada Program Kerja -->
    <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6 text-amber-900 space-y-3">
        <div class="flex items-start gap-3">
            <i class="bi-exclamation-triangle-fill text-2xl text-amber-500 mt-0.5"></i>
            <div class="space-y-1">
                <h3 class="font-black text-sm">Belum Ada Rencana Program Kerja yang Dibuat</h3>
                <p class="text-xs text-amber-800/90 leading-relaxed">
                    Pengisian realisasi kerja tugas tambahan mengacu langsung pada rencana program kerja yang telah disusun. Silakan buat rencana program kerja terlebih dahulu di menu <strong>Informasi Penugasan Guru</strong>.
                </p>
            </div>
        </div>
        <div class="pt-2 flex items-center gap-3">
            <a href="{{ route('guru.penugasan.index', ['tab' => 'tambahan']) }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition">
                <i class="bi-plus-circle-fill mr-1"></i> Buat Rencana Program Kerja Sekarang
            </a>
            <a href="{{ route('guru.realisasi-tugas-tambahan.index') }}" class="px-4 py-2 bg-white text-slate-700 font-bold rounded-xl text-xs border border-slate-300">
                Kembali
            </a>
        </div>
    </div>
    @else

    <!-- 1. Card Rujukan Rencana Program Kerja yang Dipilih -->
    <div class="bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-900 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-indigo-800/60">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-lg bg-indigo-500/30 text-indigo-200 font-black text-[11px] uppercase tracking-wider border border-indigo-400/30">
                    Rujukan Rencana Terpilih
                </span>
                <span class="text-xs text-indigo-300 font-medium" x-text="currentProgram ? currentProgram.tugas_tambahan : ''"></span>
            </div>

            <!-- Selector Pemilihan Program Kerja -->
            <div class="w-full sm:w-auto">
                <select x-model="selectedId" @change="onProgramChange()" class="w-full sm:w-80 px-3 py-1.5 bg-indigo-950/80 border border-indigo-700/60 rounded-xl text-xs font-semibold text-indigo-100 focus:ring-2 focus:ring-indigo-400">
                    <template x-for="p in programs" :key="p.id">
                        <option :value="p.id" :selected="p.id == selectedId" x-text="'[' + p.tugas_tambahan + '] ' + p.nama_program"></option>
                    </template>
                </select>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="md:col-span-2 space-y-2">
                <span class="text-[10px] uppercase font-bold text-indigo-300 tracking-wider">Nama Program Kerja Terencana:</span>
                <h2 class="text-xl font-black text-white leading-snug" x-text="currentProgram ? currentProgram.nama_program : '-'"></h2>
                
                <div class="pt-2 text-xs text-indigo-200/90 leading-relaxed bg-indigo-950/40 p-3 rounded-2xl border border-indigo-800/40" x-show="currentProgram && currentProgram.indikator_keberhasilan">
                    <span class="text-[10px] font-bold uppercase text-indigo-300 block mb-0.5">Indikator Keberhasilan Rencana:</span>
                    <span x-text="currentProgram ? currentProgram.indikator_keberhasilan : ''"></span>
                </div>
            </div>

            <div class="space-y-3 bg-indigo-950/50 p-4 rounded-2xl border border-indigo-800/40 text-xs">
                <div>
                    <span class="text-[10px] font-bold text-indigo-300 uppercase block">Target Waktu Rencana:</span>
                    <strong class="text-indigo-100" x-text="(currentProgram && currentProgram.target_waktu) ? currentProgram.target_waktu : 'Tidak ditentukan'"></strong>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-indigo-300 uppercase block">Estimasi Anggaran:</span>
                    <strong class="text-emerald-300" x-text="currentProgram ? formatRupiah(currentProgram.anggaran) : 'Rp 0'"></strong>
                </div>
                <div x-show="currentProgram && currentProgram.tujuan">
                    <span class="text-[10px] font-bold text-indigo-300 uppercase block">Sasaran / Tujuan:</span>
                    <span class="text-indigo-200 text-[11px] line-clamp-2" x-text="currentProgram ? currentProgram.tujuan : ''"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Form Pengisian Atribut Kesesuaian Saja -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('guru.realisasi-tugas-tambahan.store') }}" enctype="multipart/form-data" class="space-y-6 text-xs">
            @csrf

            <!-- Hidden Identitas Rencana -->
            <input type="hidden" name="program_kerja_id" :value="selectedId">
            <input type="hidden" name="tugas_tambahan" :value="currentProgram ? currentProgram.tugas_tambahan : ''">
            <input type="hidden" name="judul_kegiatan" :value="currentProgram ? currentProgram.nama_program : ''">

            <!-- Bagian A: Status & Persentase Kesesuaian -->
            <div class="space-y-3">
                <label class="block font-black text-slate-800 text-sm">
                    1. Evaluasi Status Kesesuaian dengan Rencana <span class="text-rose-500">*</span>
                </label>
                <p class="text-xs text-slate-500">Pilih tingkat kesesuaian antara pelaksanaan kegiatan di lapangan dengan rencana program kerja yang ditetapkan.</p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                    <!-- Sesuai Rencana -->
                    <label class="relative flex flex-col p-4 bg-slate-50 border-2 rounded-2xl cursor-pointer hover:bg-emerald-50/40 transition select-none"
                           :class="statusKesesuaian === 'sesuai' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-950 shadow-xs' : 'border-slate-200 text-slate-700'">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-black text-xs text-emerald-800 flex items-center gap-1.5">
                                <i class="bi-check-circle-fill text-emerald-600 text-sm"></i>
                                Sesuai Rencana
                            </span>
                            <input type="radio" name="status_kesesuaian" value="sesuai" x-model="statusKesesuaian" @change="persentase = 100" class="text-emerald-600 focus:ring-emerald-500">
                        </div>
                        <p class="text-[11px] text-slate-500 leading-snug">
                            Terlaksana 100% tepat waktu, sasaran, dan indikator keberhasilan tercapai penuh.
                        </p>
                    </label>

                    <!-- Sebagian Sesuai -->
                    <label class="relative flex flex-col p-4 bg-slate-50 border-2 rounded-2xl cursor-pointer hover:bg-amber-50/40 transition select-none"
                           :class="statusKesesuaian === 'sebagian_sesuai' ? 'border-amber-500 bg-amber-50/50 text-amber-950 shadow-xs' : 'border-slate-200 text-slate-700'">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-black text-xs text-amber-800 flex items-center gap-1.5">
                                <i class="bi-slash-circle-fill text-amber-500 text-sm"></i>
                                Sebagian Sesuai
                            </span>
                            <input type="radio" name="status_kesesuaian" value="sebagian_sesuai" x-model="statusKesesuaian" @change="if(persentase >= 100) persentase = 75" class="text-amber-600 focus:ring-amber-500">
                        </div>
                        <p class="text-[11px] text-slate-500 leading-snug">
                            Terlaksana dengan penyesuaian waktu / indikator ketercapaian sebagian terpenuhi.
                        </p>
                    </label>

                    <!-- Tidak Sesuai -->
                    <label class="relative flex flex-col p-4 bg-slate-50 border-2 rounded-2xl cursor-pointer hover:bg-rose-50/40 transition select-none"
                           :class="statusKesesuaian === 'tidak_sesuai' ? 'border-rose-500 bg-rose-50/50 text-rose-950 shadow-xs' : 'border-slate-200 text-slate-700'">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-black text-xs text-rose-800 flex items-center gap-1.5">
                                <i class="bi-x-circle-fill text-rose-600 text-sm"></i>
                                Tidak Sesuai
                            </span>
                            <input type="radio" name="status_kesesuaian" value="tidak_sesuai" x-model="statusKesesuaian" @change="if(persentase > 50) persentase = 30" class="text-rose-600 focus:ring-rose-500">
                        </div>
                        <p class="text-[11px] text-slate-500 leading-snug">
                            Belum mencapai indikator rencana atau terdapat kendala mendasar yang mengalihkan target.
                        </p>
                    </label>
                </div>
            </div>

            <!-- Bagian B: Persentase, Tanggal, dan Anggaran Riil -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-slate-100">
                <!-- Persentase Kesesuaian -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-bold text-slate-700">Tingkat Kesesuaian (%) <span class="text-rose-500">*</span></label>
                        <span class="font-black text-indigo-600" x-text="persentase + '%'"></span>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="range" min="0" max="100" step="5" x-model="persentase" name="persentase_kesesuaian" class="w-full accent-indigo-600">
                        <input type="number" min="0" max="100" x-model="persentase" class="w-16 px-2 py-1.5 bg-slate-50 border border-slate-300 rounded-lg text-center font-bold text-xs">
                    </div>
                </div>

                <!-- Tanggal Pelaksanaan -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Pelaksanaan Realisasi <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_pelaksanaan" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                </div>

                <!-- Realisasi Anggaran -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Realisasi Anggaran Riil (Rp)</label>
                    <input type="number" name="realisasi_anggaran" min="0" :placeholder="currentProgram ? currentProgram.anggaran : 0" :value="currentProgram ? currentProgram.anggaran : 0" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                    <span class="text-[10px] text-slate-400 mt-1 block">Bila ada pengeluaran dana kegiatan riil.</span>
                </div>
            </div>

            <!-- Bagian C: Keterangan Kesesuaian & Uraian Pelaksanaan -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <!-- Keterangan Analisis Kesesuaian -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Keterangan & Analisis Kesesuaian Terhadap Rencana
                    </label>
                    <textarea name="keterangan_kesesuaian" rows="2" placeholder="Jelaskan secara ringkas bagaimana pelaksanaan program ini sesuai dengan rencana yang telah disusun sebelumnya..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600"></textarea>
                </div>

                <!-- Uraian Pelaksanaan Kegiatan -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Uraian Pelaksanaan Realisasi Kegiatan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="uraian_kegiatan" rows="3" required placeholder="Jelaskan proses pelaksanaan kegiatan, sasaran peserta/siswa/rekan guru yang hadir, dan rincian agenda yang berjalan..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600"></textarea>
                </div>

                <!-- Hasil Capaian terhadap Indikator -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Hasil Capaian Terhadap Indikator Keberhasilan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="hasil_capaian" rows="2" required placeholder="Output konkret atau hasil yang diperoleh terhadap indikator capaian rencana program kerja..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600"></textarea>
                </div>

                <!-- Kendala & Tindak Lanjut -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Kendala & Rencana Solusi / Tindak Lanjut (Jika Terdapat Ketidaksesuaian)
                    </label>
                    <textarea name="kendala_solusi" rows="2" placeholder="Uraikan faktor kendala yang dihadapi serta rencana perbaikan ke depan..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600"></textarea>
                </div>
            </div>

            <!-- Bagian D: Bukti Dokumentasi & Update Status Rencana -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-slate-100">
                <!-- Foto Dokumentasi -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Foto Dokumentasi (JPG/PNG)</label>
                    <input type="file" name="foto" accept="image/*" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>

                <!-- Dokumen Pendukung -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Dokumen Pendukung / Notulensi</label>
                    <input type="file" name="dokumen" accept=".pdf,.doc,.docx,.xls,.xlsx" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>

                <!-- Update Status Program Kerja -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Perbarui Status Rencana Program</label>
                    <select name="status_program" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-indigo-600">
                        <option value="tercapai" :selected="statusKesesuaian === 'sesuai'">Tercapai (Selesai & Sesuai)</option>
                        <option value="sedang_berjalan" :selected="statusKesesuaian === 'sebagian_sesuai'">Sedang Berjalan</option>
                        <option value="tertunda" :selected="statusKesesuaian === 'tidak_sesuai'">Tertunda</option>
                    </select>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-5 flex items-center justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('guru.realisasi-tugas-tambahan.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-600/20 flex items-center gap-2">
                    <i class="bi-check-circle-fill"></i>
                    <span>Simpan Atribut Kesesuaian</span>
                </button>
            </div>
        </form>
    </div>
    @endif

</div>

<script>
function formKesesuaian(config) {
    return {
        programs: config.programs || [],
        selectedId: config.selectedId,
        currentProgram: null,
        statusKesesuaian: 'sesuai',
        persentase: 100,

        init() {
            if (this.selectedId) {
                this.currentProgram = this.programs.find(p => p.id == this.selectedId) || this.programs[0] || null;
            } else if (this.programs.length > 0) {
                this.selectedId = this.programs[0].id;
                this.currentProgram = this.programs[0];
            }
        },

        onProgramChange() {
            this.currentProgram = this.programs.find(p => p.id == this.selectedId) || null;
        },

        formatRupiah(number) {
            if (!number) return 'Rp 0';
            return 'Rp ' + Number(number).toLocaleString('id-ID');
        }
    };
}
</script>
@endsection
