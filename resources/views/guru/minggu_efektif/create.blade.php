@extends('layouts.app')

@section('title', 'Input Minggu Efektif')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" 
     x-data="{
        tahunAjaran: '{{ $activeKalender->tahun_ajaran ?? '2026/2027' }}',
        semester: 'ganjil',
        jamPerMinggu: 4,
        isLoading: false,
        syncStatus: '',
        bulans: {
            ganjil: [
                { nama: 'Juli', total: 5, tidak: 3, ket: 'MPLS & Libur Tahun Ajaran' },
                { nama: 'Agustus', total: 4, tidak: 1, ket: 'HUT RI' },
                { nama: 'September', total: 4, tidak: 1, ket: 'Asesmen Tengah Semester (ASTS)' },
                { nama: 'Oktober', total: 5, tidak: 0, ket: 'KBM Efektif' },
                { nama: 'November', total: 4, tidak: 0, ket: 'KBM Efektif' },
                { nama: 'Desember', total: 4, tidak: 3, ket: 'Asesmen Akhir Semester & Raport' },
            ],
            genap: [
                { nama: 'Januari', total: 5, tidak: 1, ket: 'Awal Semester Genap' },
                { nama: 'Februari', total: 4, tidak: 0, ket: 'KBM Efektif' },
                { nama: 'Maret', total: 4, tidak: 1, ket: 'Asesmen Tengah Semester (ASTS)' },
                { nama: 'April', total: 5, tidak: 2, ket: 'Libur Idul Fitri' },
                { nama: 'Mei', total: 4, tidak: 1, ket: 'Ujian Kenaikan Kelas / Asesmen Akhir' },
                { nama: 'Juni', total: 4, tidak: 3, ket: 'Pembagian Raport & Libur Semester' },
            ]
        },
        getTotalMinggu() {
            return (this.bulans[this.semester] || []).reduce((sum, b) => sum + parseInt(b.total || 0), 0);
        },
        getTotalTidakEfektif() {
            return (this.bulans[this.semester] || []).reduce((sum, b) => sum + parseInt(b.tidak || 0), 0);
        },
        getTotalEfektif() {
            return Math.max(0, this.getTotalMinggu() - this.getTotalTidakEfektif());
        },
        getTotalJam() {
            return this.getTotalEfektif() * parseInt(this.jamPerMinggu || 0);
        },
        async loadFromKalender() {
            this.isLoading = true;
            this.syncStatus = 'Menghubungi kalender akademik...';
            try {
                const url = `{{ route('kalender.efektif-api') }}?tahun_ajaran=${encodeURIComponent(this.tahunAjaran)}&semester=${this.semester}`;
                const res = await fetch(url);
                const data = await res.json();
                if (data && data.status === 'success' && data.bulans && data.bulans.length > 0) {
                    this.bulans[this.semester] = data.bulans.map(b => ({
                        nama: b.nama || b.bulan,
                        total: parseInt(b.total || b.total_minggu || 4),
                        tidak: parseInt(b.tidak || b.tidak_efektif || 0),
                        ket: b.ket || b.keterangan || '-'
                    }));
                    this.syncStatus = `Data Kalender ${data.tahun_ajaran} (${data.semester.toUpperCase()}) Berhasil Dimuat`;
                } else {
                    this.syncStatus = 'Kalender TA ini belum disetel.';
                }
            } catch (e) {
                console.error(e);
                this.syncStatus = 'Koneksi ke kalender gagal.';
            } finally {
                this.isLoading = false;
                setTimeout(() => { this.syncStatus = ''; }, 4000);
            }
        },
        init() {
            this.loadFromKalender();
        }
     }">

    <!-- Breadcrumb & Title -->
    <div class="flex items-center justify-between">
        <div>
            <div class="text-xs text-slate-500 mb-1">
                <a href="{{ route('guru.minggu-efektif.index') }}" class="text-blue-600 hover:underline">&larr; Kembali ke Daftar</a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-calendar-plus text-amber-500"></i>
                <span>Form Input Minggu Efektif Mengajar</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Isi rincian jumlah minggu per bulan dan hitung otomatis jam tatap muka KBM</p>
        </div>
    </div>

    <form method="POST" action="{{ route('guru.minggu-efektif.store') }}" class="space-y-6">
        @csrf

        <!-- Card 1: Identitas Mapel & Semester -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-5">
            <h3 class="font-extrabold text-sm text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-black">1</span>
                <span>Informasi Mata Pelajaran & Kelas</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Mata Pelajaran</label>
                    <select name="mata_pelajaran_id" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-600">
                        @foreach($mapels as $mapel)
                        <option value="{{ $mapel->id }}">{{ $mapel->nama }} ({{ $mapel->kode }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kelas</label>
                    <select name="kelas" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-600">
                        @foreach($kelasList as $k)
                        <option value="{{ $k->nama_kelas ?? $k->nama }}">{{ $k->nama_kelas ?? $k->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tahun Ajaran</label>
                    <select name="tahun_ajaran" x-model="tahunAjaran" @change="loadFromKalender()" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-600">
                        @if(isset($kalenders) && $kalenders->isNotEmpty())
                            @foreach($kalenders as $k)
                            <option value="{{ $k->tahun_ajaran }}">{{ $k->tahun_ajaran }}</option>
                            @endforeach
                        @else
                            <option value="2026/2027">2026/2027 (Aktif)</option>
                            <option value="2027/2028">2027/2028</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Semester</label>
                    <select name="semester" x-model="semester" @change="loadFromKalender()" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-600">
                        <option value="ganjil">Semester Ganjil (Jul - Des)</option>
                        <option value="genap">Semester Genap (Jan - Jun)</option>
                    </select>
                </div>
            </div>

            <div class="sm:w-1/3 text-xs">
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alokasi Jam Pelajaran (JP) / Minggu</label>
                <input type="number" name="jam_per_minggu" x-model="jamPerMinggu" min="1" max="20" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-blue-700 focus:ring-2 focus:ring-blue-600">
            </div>
        </div>

        <!-- Card 2: Rincian Alokasi Per Bulan -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-5">
            <h3 class="font-extrabold text-sm text-slate-900 border-b border-slate-100 pb-3 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-black">2</span>
                    <span>Rincian Minggu Per Bulan (<span x-text="semester === 'ganjil' ? 'Juli - Desember' : 'Januari - Juni'" class="text-blue-700"></span>)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span x-show="syncStatus" x-text="syncStatus" class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200 transition"></span>
                    <button type="button" @click="loadFromKalender()" :disabled="isLoading"
                            class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 font-bold rounded-xl text-xs flex items-center gap-1.5 transition shadow-2xs">
                        <i class="bi-arrow-repeat text-amber-600" :class="{ 'animate-spin': isLoading }"></i>
                        <span x-text="isLoading ? 'Menghitung...' : 'Sinkronkan Kalender'"></span>
                    </button>
                    <a href="{{ route('guru.kalender.index') }}" target="_blank"
                       class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs flex items-center gap-1.5 transition border border-slate-200" title="Buka Kalender Akademik di tab baru">
                        <i class="bi-box-arrow-up-right text-[10px]"></i>
                        <span>Lihat Kalender</span>
                    </a>
                </div>
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase text-[10px]">
                        <tr>
                            <th class="px-4 py-3 text-left">Nama Bulan</th>
                            <th class="px-4 py-3 text-center">Jumlah Minggu</th>
                            <th class="px-4 py-3 text-center text-rose-600">Minggu Tidak Efektif</th>
                            <th class="px-4 py-3 text-center text-emerald-600">Minggu Efektif</th>
                            <th class="px-4 py-3 text-left">Keterangan Kegiatan Non-Efektif</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-for="(item, idx) in bulans[semester]" :key="item.nama">
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-4 py-3 font-bold text-slate-800" x-text="item.nama"></td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" :name="'bulans[' + item.nama + '][total_minggu]'" x-model="item.total" min="1" max="6" class="w-16 p-1.5 text-center bg-slate-50 border rounded-lg font-bold">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" :name="'bulans[' + item.nama + '][tidak_efektif]'" x-model="item.tidak" min="0" max="6" class="w-16 p-1.5 text-center bg-rose-50 border border-rose-200 text-rose-700 rounded-lg font-bold">
                                </td>
                                <td class="px-4 py-3 text-center font-extrabold text-emerald-700 bg-emerald-50/50" x-text="Math.max(0, item.total - item.tidak)"></td>
                                <td class="px-4 py-3">
                                    <input type="text" :name="'bulans[' + item.nama + '][keterangan]'" x-model="item.ket" placeholder="Agenda non-efektif..." class="w-full p-1.5 bg-slate-50 border rounded-lg text-xs">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="bg-slate-100 font-extrabold text-xs">
                        <tr>
                            <td class="px-4 py-3 text-left text-slate-800">TOTAL SEMESTER</td>
                            <td class="px-4 py-3 text-center font-black text-slate-900" x-text="getTotalMinggu()"></td>
                            <td class="px-4 py-3 text-center font-black text-rose-600" x-text="getTotalTidakEfektif()"></td>
                            <td class="px-4 py-3 text-center font-black text-emerald-600 bg-emerald-100" x-text="getTotalEfektif()"></td>
                            <td class="px-4 py-3 font-black text-blue-800">
                                Total Jam Efektif: <span x-text="getTotalJam()"></span> Jam Pelajaran (JP)
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Card 3: Distribusi Alokasi Waktu KBM -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-5">
            <h3 class="font-extrabold text-sm text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-black">3</span>
                <span>Distribusi Jam Pembelajaran & Catatan</span>
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                    <div class="text-slate-500 font-semibold mb-1">Tatap Muka Teori & Praktik</div>
                    <input type="number" name="tatap_muka" :value="Math.round(getTotalJam() * 0.8)" class="w-20 p-2 text-center bg-white border rounded-xl font-bold text-sm mx-auto">
                    <span class="text-[11px] text-slate-400 block mt-1">JP (Est. 80%)</span>
                </div>

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                    <div class="text-slate-500 font-semibold mb-1">Asesmen Formatif</div>
                    <input type="number" name="asesmen_formatif" :value="Math.round(getTotalJam() * 0.1)" class="w-20 p-2 text-center bg-white border rounded-xl font-bold text-sm mx-auto">
                    <span class="text-[11px] text-slate-400 block mt-1">JP (Est. 10%)</span>
                </div>

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                    <div class="text-slate-500 font-semibold mb-1">Asesmen Sumatif</div>
                    <input type="number" name="asesmen_sumatif" :value="Math.round(getTotalJam() * 0.05)" class="w-20 p-2 text-center bg-white border rounded-xl font-bold text-sm mx-auto">
                    <span class="text-[11px] text-slate-400 block mt-1">JP (Est. 5%)</span>
                </div>

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                    <div class="text-slate-500 font-semibold mb-1">Cadangan Jam</div>
                    <input type="number" name="cadangan" :value="Math.round(getTotalJam() * 0.05)" class="w-20 p-2 text-center bg-white border rounded-xl font-bold text-sm mx-auto">
                    <span class="text-[11px] text-slate-400 block mt-1">JP (Est. 5%)</span>
                </div>
            </div>

            <div class="text-xs">
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Khusus Pembelajaran (Opsional)</label>
                <textarea name="catatan" rows="3" placeholder="Tambahkan catatan agenda kurikulum atau target capaian..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium focus:ring-2 focus:ring-blue-600"></textarea>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('guru.minggu-efektif.index') }}" class="px-5 py-3 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-blue-600/30 transition flex items-center gap-2">
                <i class="bi-check2-circle text-base"></i>
                <span>Simpan Rincian Minggu Efektif</span>
            </button>
        </div>

    </form>
</div>
@endsection
