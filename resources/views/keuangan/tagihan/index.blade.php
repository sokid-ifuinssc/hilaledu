@extends('layouts.app')
@section('title', 'Manajemen Tagihan Siswa - Keuangan')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto" 
     x-data="{ 
         tab: '{{ $tab }}',
         showRincianModal: false,
         activeSiswa: null,
         openRincian(siswa) {
             this.activeSiswa = siswa;
             this.showRincianModal = true;
         }
     }">
    
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Tagihan & Kewajiban Siswa 💸</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola daftar bayaran, SPP, Seragam, Kegiatan, dan tagihan seluruh siswa.</p>
        </div>
    </div>

    <!-- TABS -->
    <div class="mb-6 border-b border-slate-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-slate-500">
            <li class="mr-2">
                <a href="#" @click.prevent="tab = 'siswa'" 
                   :class="{'text-emerald-600 border-emerald-600 active font-bold': tab === 'siswa', 'border-transparent hover:text-slate-600 hover:border-slate-300': tab !== 'siswa'}"
                   class="inline-flex items-center justify-center p-4 border-b-2 rounded-t-lg group transition">
                    <i class="bi-people-fill me-2" :class="{'text-emerald-600': tab === 'siswa'}"></i>
                    Tagihan Per Siswa (Semua Siswa)
                </a>
            </li>
            <li class="mr-2">
                <a href="#" @click.prevent="tab = 'master'" 
                   :class="{'text-indigo-600 border-indigo-600 active font-bold': tab === 'master', 'border-transparent hover:text-slate-600 hover:border-slate-300': tab !== 'master'}"
                   class="inline-flex items-center justify-center p-4 border-b-2 rounded-t-lg group transition">
                    <i class="bi-database me-2" :class="{'text-indigo-600': tab === 'master'}"></i>
                    Master Tagihan
                </a>
            </li>
        </ul>
    </div>

    <!-- TAB 1: MASTER TAGIHAN -->
    <div x-show="tab === 'master'" style="{{ $tab !== 'master' ? 'display: none;' : '' }}" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
        
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-slate-800">Daftar Master Tagihan</h2>
                <button onclick="document.getElementById('modalAddMaster').classList.remove('hidden')" class="btn bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm font-medium">
                    <i class="bi-plus-lg me-1"></i> Buat Tagihan Baru
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-500">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                        <tr>
                            <th scope="col" class="px-4 py-3">Nama Tagihan</th>
                            <th scope="col" class="px-4 py-3">Jenis</th>
                            <th scope="col" class="px-4 py-3">Nominal</th>
                            <th scope="col" class="px-4 py-3">Target/Ket</th>
                            <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($masters as $master)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-900">
                                {{ $master->nama_tagihan }}
                                @if($master->is_rutin)
                                    <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2 py-0.5 rounded ml-2">Rutin</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 capitalize">{{ $master->jenis }}</td>
                            <td class="px-4 py-3 font-bold text-slate-800">Rp {{ number_format($master->nominal, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-xs">
                                Kelas: {{ $master->tingkat_kelas ?? 'Semua' }}<br>
                                Jurusan: {{ $master->jurusan ?? 'Semua' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" 
                                            data-id="{{ $master->id }}"
                                            data-nama="{{ $master->nama_tagihan }}"
                                            data-jenis="{{ $master->jenis }}"
                                            data-nominal="{{ (float)$master->nominal }}"
                                            data-tingkat="{{ $master->tingkat_kelas ?? '' }}"
                                            data-jurusan="{{ $master->jurusan ?? '' }}"
                                            data-rutin="{{ $master->is_rutin ? 1 : 0 }}"
                                            onclick="openEditMasterModal(this)"
                                            class="w-7 h-7 inline-flex items-center justify-center text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition border border-transparent hover:border-blue-200" 
                                            title="Edit Master Tagihan">
                                        <i class="bi-pencil-square text-sm"></i>
                                    </button>
                                    <form action="{{ route('keuangan.tagihan_master.destroy', $master->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus master tagihan ini? Data tagihan siswa mungkin terpengaruh.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-7 h-7 inline-flex items-center justify-center text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition border border-transparent hover:border-rose-200" title="Hapus Master Tagihan">
                                            <i class="bi-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada master tagihan yang dibuat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- TAB 2: TAGIHAN SISWA PER KELAS -->
    <div x-show="tab === 'siswa'" style="{{ $tab !== 'siswa' ? 'display: none;' : '' }}" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
        
        @php
            $grandTotalTagihan = 0;
            $grandTotalBayar = 0;
            $jumlahLunas = 0;
            $jumlahTunggakan = 0;
            $jumlahTanpaTagihan = 0;
            foreach ($siswas as $s) {
                $t = $s->tagihans->sum('nominal');
                $b = $s->tagihans->sum('terbayar');
                $grandTotalTagihan += $t;
                $grandTotalBayar += $b;
                if ($t > 0 && ($t - $b) <= 0) $jumlahLunas++;
                elseif ($t > 0) $jumlahTunggakan++;
                else $jumlahTanpaTagihan++;
            }
            $grandTotalSisa = $grandTotalTagihan - $grandTotalBayar;
        @endphp

        <!-- SUMMARY CARDS -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <p class="text-xs text-slate-500 font-medium mb-1">Total Tagihan ({{ $siswas->count() }} Siswa)</p>
                <p class="text-xl font-bold text-slate-800">Rp {{ number_format($grandTotalTagihan, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-emerald-200 p-4">
                <p class="text-xs text-emerald-600 font-medium mb-1">Total Terbayar</p>
                <p class="text-xl font-bold text-emerald-600">Rp {{ number_format($grandTotalBayar, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-rose-200 p-4">
                <p class="text-xs text-rose-600 font-medium mb-1">Total Tunggakan</p>
                <p class="text-xl font-bold text-rose-600">Rp {{ number_format($grandTotalSisa, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <p class="text-xs text-slate-500 font-medium mb-1">Status Kelulusan Biaya</p>
                <p class="text-sm font-bold flex flex-wrap gap-1 items-center">
                    <span class="text-emerald-600">{{ $jumlahLunas }} Lunas</span> · 
                    <span class="text-rose-600">{{ $jumlahTunggakan }} Tunggakan</span> · 
                    <span class="text-slate-400 font-normal text-xs">{{ $jumlahTanpaTagihan }} Bebas Tagihan</span>
                </p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <div class="flex flex-wrap justify-between items-center mb-4 gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Data Tagihan & Pembayaran Seluruh Siswa</h2>
                    <p class="text-xs text-slate-500">Menampilkan daftar siswa, klik <strong class="text-emerald-700">Lihat Rinci</strong> untuk melihat rincian setiap item tagihan dan status pelunasannya.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <form action="{{ route('keuangan.tagihan.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <input type="hidden" name="tab" value="siswa">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400">
                                <i class="bi-search text-xs"></i>
                            </span>
                            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama / NIS..." 
                                class="rounded-xl border-slate-300 text-xs pl-8 pr-3 py-2 w-40 md:w-52 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <select name="kelas_id" onchange="this.form.submit()" class="rounded-xl border-slate-300 text-xs py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">-- Semua Kelas ({{ $siswas->count() }} Siswa) --</option>
                            @foreach($kelasList as $k)
                                <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold shadow-xs">
                            Cari
                        </button>
                        @if($kelasId || !empty($search))
                            <a href="{{ route('keuangan.tagihan.index', ['tab' => 'siswa']) }}" class="px-2.5 py-2 text-rose-600 hover:text-rose-700 bg-rose-50 rounded-xl text-xs font-medium">
                                Reset
                            </a>
                        @endif
                    </form>
                    <div class="flex flex-wrap items-center gap-2">
                        <button onclick="document.getElementById('modalGenerateTagihan').classList.remove('hidden')" class="btn bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl px-3.5 py-2 text-xs font-semibold shadow-xs inline-flex items-center gap-1.5" title="Generate tagihan ke banyak siswa sekaligus">
                            <i class="bi-lightning-charge-fill"></i> Generate Tagihan
                        </button>
                        <form action="{{ route('keuangan.tagihan.generate_rutin') }}" method="POST" class="inline" onsubmit="return confirm('Terapkan semua master tagihan rutin ke seluruh siswa yang berhak? Siswa yang sudah memiliki tagihan akan dilewati otomatis (0 duplikat).')">
                            @csrf
                            <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-3.5 py-2 text-xs font-semibold shadow-xs inline-flex items-center gap-1.5" title="Terapkan semua tagihan rutin ke seluruh siswa">
                                <i class="bi-arrow-repeat"></i> Terapkan Rutin Semua Siswa
                            </button>
                        </form>
                        <form action="{{ route('keuangan.tagihan.clean_duplicates') }}" method="POST" class="inline" onsubmit="return confirm('Pindai dan bersihkan data tagihan yang kembar/duplikat yang belum memiliki riwayat pembayaran?')">
                            @csrf
                            <button type="submit" class="btn bg-amber-500 hover:bg-amber-600 text-white rounded-xl px-3 py-2 text-xs font-semibold shadow-xs inline-flex items-center gap-1.5" title="Bersihkan tagihan duplikat otomatis">
                                <i class="bi-shield-check"></i> Bersihkan Duplikat
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="siswaTable" class="w-full text-sm text-left text-slate-500">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-y border-slate-200">
                        <tr>
                            <th scope="col" class="px-3 py-3 w-8 text-center">No</th>
                            <th scope="col" class="px-4 py-3">Nama Siswa</th>
                            <th scope="col" class="px-3 py-3 text-center">Kelas</th>
                            <th scope="col" class="px-4 py-3 text-right">Total Tagihan</th>
                            <th scope="col" class="px-4 py-3 text-right">Telah Dibayar</th>
                            <th scope="col" class="px-4 py-3 text-right">Sisa Tunggakan</th>
                            <th scope="col" class="px-4 py-3 text-center">Status</th>
                            <th scope="col" class="px-3 py-3 text-center">Input Tagihan</th>
                            <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($siswas as $idx => $siswa)
                        @php
                            $totalTagihan = $siswa->tagihans->sum('nominal');
                            $totalTerbayar = $siswa->tagihans->sum('terbayar');
                            $sisa = $totalTagihan - $totalTerbayar;
                            $isLunas = $totalTagihan > 0 && $sisa <= 0;
                            $tidakAdaTagihan = $totalTagihan == 0;
                        @endphp
                        <tr class="transition-colors {{ $isLunas ? 'bg-emerald-50/25 hover:bg-emerald-50/50' : ($tidakAdaTagihan ? 'hover:bg-slate-50' : 'bg-rose-50/15 hover:bg-rose-50/30') }}">
                            <td class="px-3 py-3 text-slate-400 text-xs text-center">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $siswa->name }}</div>
                                <div class="text-[11px] text-slate-400">NIS/NISN: {{ $siswa->nisn ?: ($siswa->nis ?: '-') }}</div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-semibold">
                                    {{ $siswa->kelasModel->nama ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-800">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-emerald-600 font-semibold">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right {{ $sisa > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }}">
                                Rp {{ number_format($sisa, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($tidakAdaTagihan)
                                    <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 text-[11px] px-2.5 py-1 rounded-full font-semibold border border-slate-200">
                                        <i class="bi-dash-circle"></i> Belum Ada Tagihan
                                    </span>
                                @elseif($isLunas)
                                    <span class="inline-flex items-center gap-1.5 bg-emerald-600 text-white text-[11px] px-3 py-1 rounded-full font-bold shadow-xs">
                                        <i class="bi-check-circle-fill"></i> LUNAS
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 text-[11px] px-2.5 py-1 rounded-full font-bold border border-rose-200">
                                        <i class="bi-clock-history"></i> Tunggakan
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center" x-data="{ showInput: false, selectedNominal: '', isSpp: false }">
                                <button @click="showInput = !showInput" 
                                    class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-2.5 py-1 rounded-lg transition border border-slate-200 shadow-2xs" 
                                    x-text="showInput ? '✕ Tutup' : '+ Tagihan'"></button>
                                <div x-show="showInput" x-cloak class="mt-2 text-left min-w-[220px]">
                                    <form action="{{ route('keuangan.tagihan.store') }}" method="POST" class="p-2.5 bg-white border border-slate-200 rounded-xl shadow-lg space-y-2">
                                        @csrf
                                        <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Pilih Master Tagihan</label>
                                            <select name="tagihan_master_id" 
                                                @change="
                                                    let opt = $event.target.selectedOptions[0];
                                                    selectedNominal = opt.dataset.nominal || '';
                                                    isSpp = opt.dataset.jenis === 'spp';
                                                "
                                                class="text-xs rounded-lg border-slate-300 w-full focus:ring-emerald-500 focus:border-emerald-500" required>
                                                <option value="">-- Pilih Tagihan --</option>
                                                @foreach($masters as $m)
                                                    <option value="{{ $m->id }}" data-nominal="{{ (float)$m->nominal }}" data-jenis="{{ $m->jenis }}">
                                                        {{ $m->nama_tagihan }} (Rp {{ number_format($m->nominal, 0, ',', '.') }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div x-show="isSpp" x-cloak>
                                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Bulan SPP</label>
                                            <input type="month" name="bulan" value="{{ date('Y-m') }}" class="text-xs rounded-lg border-slate-300 w-full focus:ring-emerald-500 focus:border-emerald-500">
                                        </div>

                                        <div>
                                            <div class="flex justify-between items-center mb-0.5">
                                                <label class="block text-[10px] font-bold text-slate-500">Nominal (Rp)</label>
                                                <span class="text-[9px] text-emerald-600 font-bold">Otomatis Terisi</span>
                                            </div>
                                            <input type="number" name="nominal" x-model="selectedNominal" placeholder="Otomatis dari master" class="text-xs rounded-lg border-slate-300 w-full focus:ring-emerald-500 focus:border-emerald-500 font-semibold text-slate-800" required>
                                        </div>

                                        <button type="submit" class="w-full bg-emerald-600 text-white text-xs py-1.5 rounded-lg font-semibold hover:bg-emerald-700 shadow-2xs transition inline-flex items-center justify-center gap-1">
                                            <i class="bi-check-lg"></i> Simpan Tagihan
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" 
                                        @click="openRincian({
                                            name: '{{ addslashes($siswa->name) }}',
                                            nis: '{{ $siswa->nis ?? '-' }}',
                                            nisn: '{{ $siswa->nisn ?? '-' }}',
                                            kelas: '{{ addslashes($siswa->kelasModel->nama ?? '-') }}',
                                            totalTagihan: {{ (float)$totalTagihan }},
                                            totalTerbayar: {{ (float)$totalTerbayar }},
                                            sisaTunggakan: {{ (float)$sisa }},
                                            isLunas: {{ $isLunas ? 'true' : 'false' }},
                                            tidakAdaTagihan: {{ $tidakAdaTagihan ? 'true' : 'false' }},
                                            detailUrl: '{{ route('keuangan.tagihan.show', $siswa->id) }}',
                                            tagihans: [
                                                @foreach($siswa->tagihans as $t)
                                                {
                                                    id: {{ $t->id }},
                                                    nama: '{{ addslashes($t->nama_tagihan) }}',
                                                    jenis: '{{ addslashes($t->jenis) }}',
                                                    nominal: {{ (float)$t->nominal }},
                                                    terbayar: {{ (float)$t->terbayar }},
                                                    sisa: {{ (float)($t->nominal - $t->terbayar) }},
                                                    status: '{{ $t->status }}',
                                                    isLunas: {{ ($t->status === 'lunas' || ($t->nominal - $t->terbayar) <= 0) ? 'true' : 'false' }},
                                                    tanggalLunas: '{{ $t->pembayarans->sortByDesc('tanggal_bayar')->first()?->tanggal_bayar ? \Carbon\Carbon::parse($t->pembayarans->sortByDesc('tanggal_bayar')->first()->tanggal_bayar)->format('d M Y') : '' }}'
                                                },
                                                @endforeach
                                            ]
                                        })"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white rounded-lg text-xs font-semibold transition border border-emerald-200">
                                        <i class="bi-receipt"></i> Lihat Rinci
                                    </button>
                                    <a href="{{ route('keuangan.tagihan.show', $siswa->id) }}" class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold transition border border-indigo-200">
                                        <i class="bi-wallet2"></i> Bayar
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-slate-500">
                                @if(!empty($search))
                                    Tidak ditemukan siswa dengan kata kunci "{{ $search }}".
                                @elseif($kelasId)
                                    Tidak ada siswa di kelas ini.
                                @else
                                    Belum ada data siswa aktif.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($siswas->count() > 0)
                    <tfoot class="bg-slate-100 font-bold text-slate-800">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right uppercase text-xs">TOTAL KESELURUHAN ({{ $siswas->count() }} SISWA)</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($grandTotalTagihan, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-emerald-600">Rp {{ number_format($grandTotalBayar, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-rose-600">Rp {{ number_format($grandTotalSisa, 0, ',', '.') }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL RINCIAN TAGIHAN SISWA -->
    <div x-show="showRincianModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
        <div class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all my-8" @click.away="showRincianModal = false">
            
            <!-- Header Modal -->
            <div class="px-6 py-4 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-emerald-400 font-bold text-lg">
                        <i class="bi-file-earmark-spreadsheet"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base md:text-lg flex items-center gap-2">
                            <span x-text="activeSiswa ? activeSiswa.name : ''"></span>
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30" x-text="activeSiswa ? activeSiswa.kelas : ''"></span>
                        </h3>
                        <p class="text-xs text-slate-300">NIS/NISN: <span x-text="(activeSiswa && activeSiswa.nisn != '-') ? activeSiswa.nisn : (activeSiswa ? activeSiswa.nis : '-')"></span></p>
                    </div>
                </div>
                <button @click="showRincianModal = false" class="text-slate-400 hover:text-white text-xl p-1 rounded-lg hover:bg-white/10 transition">
                    <i class="bi-x-lg"></i>
                </button>
            </div>

            <div class="p-6 space-y-5 max-h-[72vh] overflow-y-auto">
                <!-- Status Pelunasan Banner -->
                <template x-if="activeSiswa && activeSiswa.isLunas">
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xl shrink-0 shadow-sm">
                            <i class="bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-emerald-800 text-sm">STATUS: LUNAS SEMPURNA ✓</h4>
                            <p class="text-xs text-emerald-700">Seluruh tagihan dan kewajiban biaya pendidikan siswa ini telah selesai dibayar lunas.</p>
                        </div>
                    </div>
                </template>

                <template x-if="activeSiswa && !activeSiswa.isLunas && !activeSiswa.tidakAdaTagihan">
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-rose-600 text-white flex items-center justify-center text-xl shrink-0 shadow-sm">
                            <i class="bi-exclamation-triangle-fill"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-rose-800 text-sm">STATUS: BELUM LUNAS / ADA TUNGGAKAN</h4>
                            <p class="text-xs text-rose-700">Masih terdapat sisa tunggakan sebesar <strong x-text="'Rp ' + Number(activeSiswa.sisaTunggakan).toLocaleString('id-ID')"></strong> yang harus diselesaikan.</p>
                        </div>
                    </div>
                </template>

                <template x-if="activeSiswa && activeSiswa.tidakAdaTagihan">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-400 text-white flex items-center justify-center text-xl shrink-0">
                            <i class="bi-info-circle"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-700 text-sm">BELUM ADA TAGIHAN</h4>
                            <p class="text-xs text-slate-500">Belum ada daftar tagihan yang dibebankan kepada siswa ini untuk tahun ajaran aktif.</p>
                        </div>
                    </div>
                </template>

                <!-- 3 Mini Stats -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-center">
                        <div class="text-[11px] text-slate-500 font-medium">Total Beban Tagihan</div>
                        <div class="text-base font-bold text-slate-800 mt-0.5" x-text="activeSiswa ? 'Rp ' + Number(activeSiswa.totalTagihan).toLocaleString('id-ID') : 'Rp 0'"></div>
                    </div>
                    <div class="bg-emerald-50/70 p-3 rounded-xl border border-emerald-200 text-center">
                        <div class="text-[11px] text-emerald-700 font-medium">Telah Dibayar</div>
                        <div class="text-base font-bold text-emerald-600 mt-0.5" x-text="activeSiswa ? 'Rp ' + Number(activeSiswa.totalTerbayar).toLocaleString('id-ID') : 'Rp 0'"></div>
                    </div>
                    <div class="bg-rose-50/70 p-3 rounded-xl border border-rose-200 text-center">
                        <div class="text-[11px] text-rose-700 font-medium">Sisa Tunggakan</div>
                        <div class="text-base font-bold text-rose-600 mt-0.5" x-text="activeSiswa ? 'Rp ' + Number(activeSiswa.sisaTunggakan).toLocaleString('id-ID') : 'Rp 0'"></div>
                    </div>
                </div>

                <!-- Tabel Rincian Semua Item Tagihan -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-bold text-slate-800 text-sm">Rincian Daftar Tagihan Siswa</h4>
                        <span class="text-xs text-slate-500" x-text="activeSiswa ? activeSiswa.tagihans.length + ' item tagihan' : ''"></span>
                    </div>

                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50 text-slate-600 text-xs font-semibold uppercase border-b border-slate-200">
                                <tr>
                                    <th class="p-3">Nama Tagihan</th>
                                    <th class="p-3 text-right">Nominal</th>
                                    <th class="p-3 text-right">Terbayar</th>
                                    <th class="p-3 text-right">Sisa</th>
                                    <th class="p-3 text-center">Keterangan Pelunasan</th>
                                    <th class="p-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-if="activeSiswa && activeSiswa.tagihans.length === 0">
                                    <tr>
                                        <td colspan="6" class="p-6 text-center text-slate-400 text-xs">
                                            Tidak ada rincian tagihan yang terdata untuk siswa ini.
                                        </td>
                                    </tr>
                                </template>
                                <template x-for="(t, index) in (activeSiswa ? activeSiswa.tagihans : [])" :key="t.id">
                                    <tr :class="t.isLunas ? 'bg-emerald-50/30 hover:bg-emerald-50/50' : 'hover:bg-slate-50'">
                                        <td class="p-3">
                                            <div class="font-bold text-slate-800" x-text="t.nama"></div>
                                            <div class="text-[11px] text-slate-400 capitalize" x-text="'Jenis: ' + t.jenis"></div>
                                        </td>
                                        <td class="p-3 text-right font-medium text-slate-700" x-text="'Rp ' + Number(t.nominal).toLocaleString('id-ID')"></td>
                                        <td class="p-3 text-right text-emerald-600 font-semibold" x-text="'Rp ' + Number(t.terbayar).toLocaleString('id-ID')"></td>
                                        <td class="p-3 text-right font-semibold" :class="t.sisa > 0 ? 'text-rose-600' : 'text-slate-400'" x-text="'Rp ' + Number(t.sisa).toLocaleString('id-ID')"></td>
                                        <td class="p-3 text-center">
                                            <template x-if="t.isLunas">
                                                <div class="inline-flex flex-col items-center">
                                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-600 text-white font-bold text-xs rounded-full shadow-xs">
                                                        <i class="bi-check-circle-fill"></i> LUNAS
                                                    </span>
                                                    <span x-show="t.tanggalLunas" class="text-[10px] text-emerald-700 font-medium mt-0.5" x-text="'Tgl: ' + t.tanggalLunas"></span>
                                                </div>
                                            </template>
                                            <template x-if="!t.isLunas">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-200 font-semibold text-xs rounded-full">
                                                    <i class="bi-clock"></i> Belum Lunas
                                                </span>
                                            </template>
                                        </td>
                                        <td class="p-3 text-center">
                                            <template x-if="t.terbayar == 0">
                                                <form :action="'{{ url('keuangan/tagihan') }}/' + t.id" method="POST" class="inline" onsubmit="return confirm('Hapus/batalkan tagihan ini dari siswa?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-7 h-7 inline-flex items-center justify-center text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition border border-rose-200" title="Hapus Tagihan (Batalkan Duplikat)">
                                                        <i class="bi-trash text-xs"></i>
                                                    </button>
                                                </form>
                                            </template>
                                            <template x-if="t.terbayar > 0">
                                                <span class="text-[10px] text-slate-400 italic">Terkunci</span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center">
                <button type="button" @click="showRincianModal = false" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-xl text-sm font-medium transition">
                    Tutup
                </button>
                <template x-if="activeSiswa">
                    <a :href="activeSiswa.detailUrl" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm transition inline-flex items-center gap-2">
                        <i class="bi-wallet2"></i> Buka Halaman Pembayaran / Bayar
                    </a>
                </template>
            </div>

        </div>
    </div>

</div>

<!-- MODAL ADD MASTER TAGIHAN -->
<div id="modalAddMaster" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm hidden">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
        <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 text-lg">Buat Master Tagihan</h3>
            <button onclick="document.getElementById('modalAddMaster').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i class="bi-x-lg"></i></button>
        </div>
        <form action="{{ route('keuangan.tagihan_master.store') }}" method="POST">
            @csrf
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Tagihan</label>
                    <input type="text" name="nama_tagihan" placeholder="Contoh: SPP Bulanan, Jas Almamater, Ujian Akhir" class="w-full text-sm rounded-lg border-slate-300" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Pembayaran</label>
                    <select name="jenis" class="w-full text-sm rounded-lg border-slate-300" required>
                        <option value="spp">SPP (Bulanan)</option>
                        <option value="sekali">Sekali Bayar (Pangkal/Lulus)</option>
                        <option value="tahunan">Tahunan (LDKS, Daftar Ulang)</option>
                        <option value="kondisional">Kondisional (Pindahan dll)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nominal (Rp)</label>
                    <input type="number" name="nominal" class="w-full text-sm rounded-lg border-slate-300" required>
                </div>
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tingkat Kelas (Opsional)</label>
                        <select name="tingkat_kelas" class="w-full text-sm rounded-lg border-slate-300">
                            <option value="">Semua Tingkat</option>
                            <option value="X">Kelas X</option>
                            <option value="XI">Kelas XI</option>
                            <option value="XII">Kelas XII</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jurusan (Opsional)</label>
                        <input type="text" name="jurusan" placeholder="Cth: TKR, TKJ" class="w-full text-sm rounded-lg border-slate-300">
                    </div>
                </div>
                <div>
                    <label class="flex items-center gap-2 text-sm text-slate-700 font-medium">
                        <input type="checkbox" name="is_rutin" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Jadikan Tagihan Rutin Otomatis
                    </label>
                </div>
            </div>
            <div class="p-5 border-t border-slate-200 bg-slate-50 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalAddMaster').classList.add('hidden')" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Simpan Tagihan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT MASTER TAGIHAN -->
<div id="modalEditMaster" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm hidden" onclick="if(event.target === this) closeEditMasterModal()">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
        <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                <i class="bi-pencil-square text-blue-600"></i>
                <span>Edit Master Tagihan</span>
            </h3>
            <button type="button" onclick="closeEditMasterModal()" class="text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg"></i>
            </button>
        </div>
        <form id="formEditMaster" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Tagihan</label>
                    <input type="text" id="edit_nama_tagihan" name="nama_tagihan" placeholder="Contoh: SPP Bulanan, Seragam, Ujian Akhir" class="w-full text-sm rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Pembayaran</label>
                    <select id="edit_jenis" name="jenis" class="w-full text-sm rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="spp">SPP (Bulanan)</option>
                        <option value="sekali">Sekali Bayar (Pangkal/Lulus)</option>
                        <option value="tahunan">Tahunan (LDKS, Daftar Ulang)</option>
                        <option value="kondisional">Kondisional (Pindahan dll)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nominal (Rp)</label>
                    <input type="number" id="edit_nominal" name="nominal" class="w-full text-sm rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tingkat Kelas (Opsional)</label>
                        <select id="edit_tingkat_kelas" name="tingkat_kelas" class="w-full text-sm rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Tingkat</option>
                            <option value="X">Kelas X</option>
                            <option value="XI">Kelas XI</option>
                            <option value="XII">Kelas XII</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jurusan (Opsional)</label>
                        <input type="text" id="edit_jurusan" name="jurusan" placeholder="Cth: TKJT, TO, AKL" class="w-full text-sm rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label class="flex items-center gap-2 text-sm text-slate-700 font-medium">
                        <input type="checkbox" id="edit_is_rutin" name="is_rutin" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        Jadikan Tagihan Rutin Otomatis
                    </label>
                </div>
            </div>
            <div class="p-5 border-t border-slate-200 bg-slate-50 flex justify-end gap-2">
                <button type="button" onclick="closeEditMasterModal()" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL GENERATE TAGIHAN -->
<div id="modalGenerateTagihan" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm hidden">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
        <div class="px-5 py-4 bg-emerald-50 border-b border-emerald-200 flex justify-between items-center">
            <h3 class="font-bold text-emerald-800 text-lg"><i class="bi-lightning-charge-fill me-2"></i>Generate Tagihan ke Siswa</h3>
            <button onclick="document.getElementById('modalGenerateTagihan').classList.add('hidden')" class="text-emerald-400 hover:text-emerald-600"><i class="bi-x-lg"></i></button>
        </div>
        <form action="{{ route('keuangan.tagihan.generate') }}" method="POST">
            @csrf
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Master Tagihan</label>
                    <select name="tagihan_master_id" class="w-full text-sm rounded-lg border-slate-300" required>
                        @foreach($masters as $m)
                            <option value="{{ $m->id }}">{{ $m->nama_tagihan }} - Rp {{ number_format($m->nominal,0,',','.') }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-data="{ target: 'semua' }">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Target Tagihan</label>
                    <select name="target" x-model="target" class="w-full text-sm rounded-lg border-slate-300 mb-3" required>
                        <option value="semua">Semua Siswa Aktif</option>
                        <option value="kelas_tertentu">Kelas Tertentu</option>
                        <option value="jurusan_tertentu">Jurusan Tertentu</option>
                    </select>
                    
                    <div x-show="target === 'kelas_tertentu'">
                        <select name="kelas_id" class="w-full text-sm rounded-lg border-slate-300">
                            @foreach($kelasList as $kls)
                                <option value="{{ $kls->id }}">{{ $kls->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="target === 'jurusan_tertentu'">
                        <input type="text" name="jurusan" placeholder="Masukkan Nama Jurusan" class="w-full text-sm rounded-lg border-slate-300">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan Bulan (Hanya untuk SPP)</label>
                    <input type="month" name="bulan" class="w-full text-sm rounded-lg border-slate-300">
                </div>
                
                <div class="bg-amber-50 text-amber-800 text-xs p-3 rounded border border-amber-200">
                    <i class="bi-info-circle-fill me-1"></i> Tagihan akan otomatis masuk ke data siswa yang dipilih. Jika tagihan serupa sudah ada untuk siswa tersebut, sistem akan mengabaikannya (mencegah duplikat).
                </div>
            </div>
            <div class="p-5 border-t border-slate-200 bg-slate-50 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalGenerateTagihan').classList.add('hidden')" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700"><i class="bi-check2-circle me-1"></i> Generate Sekarang</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditMasterModal(button) {
    const id = button.getAttribute('data-id');
    const nama = button.getAttribute('data-nama');
    const jenis = button.getAttribute('data-jenis');
    const nominal = button.getAttribute('data-nominal');
    const tingkat = button.getAttribute('data-tingkat');
    const jurusan = button.getAttribute('data-jurusan');
    const isRutin = button.getAttribute('data-rutin') === '1';

    const modal = document.getElementById('modalEditMaster');
    const form = document.getElementById('formEditMaster');
    form.action = "{{ url('keuangan/tagihan-master') }}/" + id;

    document.getElementById('edit_nama_tagihan').value = nama || '';
    document.getElementById('edit_jenis').value = jenis || 'spp';
    document.getElementById('edit_nominal').value = nominal || 0;
    document.getElementById('edit_tingkat_kelas').value = tingkat || '';
    document.getElementById('edit_jurusan').value = jurusan || '';
    document.getElementById('edit_is_rutin').checked = isRutin;

    modal.classList.remove('hidden');
}

function closeEditMasterModal() {
    const modal = document.getElementById('modalEditMaster');
    if (modal) {
        modal.classList.add('hidden');
    }
}
</script>

@endsection
