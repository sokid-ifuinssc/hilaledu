@extends('layouts.app')
@section('title', 'Detail Tagihan Siswa - Keuangan')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto" x-data="{ bayarModal: false, selectedTagihanId: null, selectedNominal: 0, tagihanNama: '' }">
    
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Rincian Tagihan: {{ $siswa->name }}</h1>
            <p class="text-sm text-slate-500 mt-1">Kelas: {{ $siswa->kelasModel->nama ?? '-' }} | NIS/NISN: {{ $siswa->nis ?? '-' }}</p>
        </div>
        <a href="{{ route('keuangan.tagihan.index', ['tab' => 'siswa']) }}" class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-600">
            <i class="bi-arrow-left me-2"></i> Kembali
        </a>
    </div>

    @php
        $totalTagihan = $tagihans->sum('nominal');
        $totalTerbayar = $tagihans->sum('terbayar');
        $totalTunggakan = $totalTagihan - $totalTerbayar;
    @endphp

    <!-- STATUS BANNER -->
    @if($totalTagihan > 0 && $totalTunggakan <= 0)
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex flex-wrap items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xl shadow-xs">
                    <i class="bi-check-circle-fill"></i>
                </div>
                <div>
                    <h3 class="font-bold text-emerald-800 text-base">STATUS: LUNAS SEMPURNA ✓</h3>
                    <p class="text-xs text-emerald-700">Semua tagihan dan kewajiban administrasi sekolah untuk siswa ini telah selesai dilunasi penuh.</p>
                </div>
            </div>
            <span class="px-3.5 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-full shadow-xs inline-flex items-center gap-1">
                <i class="bi-check2-all"></i> BEBAS TUNGGAKAN
            </span>
        </div>
    @elseif($totalTunggakan > 0)
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 flex flex-wrap items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-rose-600 text-white flex items-center justify-center text-xl shadow-xs">
                    <i class="bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <h3 class="font-bold text-rose-800 text-base">STATUS: BELUM LUNAS / TERDAPAT TUNGGAKAN</h3>
                    <p class="text-xs text-rose-700">Masih terdapat sisa tunggakan sebesar <strong class="text-rose-900">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</strong> yang belum diselesaikan.</p>
                </div>
            </div>
            <span class="px-3.5 py-1.5 bg-rose-600 text-white text-xs font-bold rounded-full shadow-xs inline-flex items-center gap-1">
                <i class="bi-clock"></i> ADA TUNGGAKAN
            </span>
        </div>
    @else
        <div class="mb-6 p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-slate-400 text-white flex items-center justify-center text-xl">
                <i class="bi-info-circle"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-700 text-base">BELUM ADA TAGIHAN</h3>
                <p class="text-xs text-slate-500">Siswa ini belum memiliki tagihan biaya yang ditetapkan untuk tahun ajaran aktif.</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Card 1 -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center space-x-3 mb-2">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-slate-100 text-slate-500">
                    <i class="bi-receipt text-xl"></i>
                </div>
                <h3 class="text-slate-500 text-sm font-medium">Total Tagihan</h3>
            </div>
            <div class="text-2xl font-bold text-slate-800">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</div>
        </div>
        <!-- Card 2 -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center space-x-3 mb-2">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-emerald-100 text-emerald-600">
                    <i class="bi-wallet2 text-xl"></i>
                </div>
                <h3 class="text-slate-500 text-sm font-medium">Telah Dibayar</h3>
            </div>
            <div class="text-2xl font-bold text-emerald-600">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</div>
        </div>
        <!-- Card 3 -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center space-x-3 mb-2">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-rose-100 text-rose-600">
                    <i class="bi-exclamation-circle text-xl"></i>
                </div>
                <h3 class="text-slate-500 text-sm font-medium">Total Tunggakan</h3>
            </div>
            <div class="text-2xl font-bold text-rose-600">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- DAFTAR TAGIHAN -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-8">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Rincian Tagihan</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                    <tr>
                        <th scope="col" class="px-4 py-3">Nama Tagihan</th>
                        <th scope="col" class="px-4 py-3 text-right">Nominal Tagihan</th>
                        <th scope="col" class="px-4 py-3 text-right">Telah Dibayar</th>
                        <th scope="col" class="px-4 py-3 text-right">Sisa</th>
                        <th scope="col" class="px-4 py-3 text-center">Status</th>
                        <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tagihans as $tagihan)
                    @php $sisa = $tagihan->nominal - $tagihan->terbayar; @endphp
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $tagihan->nama_tagihan }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($tagihan->nominal, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-emerald-600">Rp {{ number_format($tagihan->terbayar, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold {{ $sisa > 0 ? 'text-rose-600' : 'text-slate-400' }}">Rp {{ number_format($sisa, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($tagihan->status == 'lunas' || $sisa <= 0)
                                <div class="inline-flex flex-col items-center">
                                    <span class="inline-flex items-center gap-1 bg-emerald-600 text-white text-xs px-3 py-1 rounded-full font-bold shadow-xs">
                                        <i class="bi-check-circle-fill"></i> LUNAS
                                    </span>
                                    <span class="text-[10px] text-emerald-700 font-semibold mt-0.5">Lunas Penuh</span>
                                </div>
                            @else
                                <div class="inline-flex flex-col items-center">
                                    <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 border border-rose-200 text-xs px-2.5 py-1 rounded-full font-bold">
                                        <i class="bi-clock-history"></i> BELUM LUNAS
                                    </span>
                                    <span class="text-[10px] text-rose-600 font-medium mt-0.5">Sisa: Rp {{ number_format($sisa, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1.5 justify-end">
                                @if($sisa > 0)
                                <button @click="bayarModal = true; selectedTagihanId = {{ $tagihan->id }}; selectedNominal = {{ $sisa }}; tagihanNama = '{{ $tagihan->nama_tagihan }}';" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold shadow-xs transition inline-flex items-center gap-1">
                                    <i class="bi-cash me-1"></i> Bayar
                                </button>
                                @else
                                <button disabled class="bg-emerald-50 text-emerald-700 border border-emerald-200 cursor-not-allowed px-3 py-1 rounded-lg text-xs font-bold inline-flex items-center gap-1">
                                    <i class="bi-check2"></i> Lunas
                                </button>
                                @endif

                                @if($tagihan->terbayar == 0)
                                <form action="{{ route('keuangan.tagihan.destroy', $tagihan->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus/batalkan tagihan ini dari siswa?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-7 h-7 inline-flex items-center justify-center text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition border border-rose-200" title="Hapus Tagihan (Batalkan)">
                                        <i class="bi-trash text-xs"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada tagihan untuk siswa ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- RIWAYAT PEMBAYARAN SISWA -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-8">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Riwayat Pembayaran</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                    <tr>
                        <th scope="col" class="px-4 py-3">Tanggal / Waktu</th>
                        <th scope="col" class="px-4 py-3">Kode TRX</th>
                        <th scope="col" class="px-4 py-3">Tagihan</th>
                        <th scope="col" class="px-4 py-3 text-right">Nominal Bayar</th>
                        <th scope="col" class="px-4 py-3">Metode</th>
                        <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayarans as $trx)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <span class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($trx->tanggal_bayar)->format('d M Y') }}</span><br>
                            <span class="text-xs text-slate-400">{{ $trx->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $trx->kode_transaksi }}</td>
                        <td class="px-4 py-3 text-xs">{{ $trx->tagihan->nama_tagihan ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-600">Rp {{ number_format($trx->nominal_bayar, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 capitalize text-xs">
                            <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded">{{ $trx->metode_pembayaran }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form action="{{ route('keuangan.pembayaran.destroy', $trx->id) }}" method="POST" onsubmit="return confirm('Yakin membatalkan transaksi ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs px-2 py-1 border border-rose-200 rounded bg-rose-50 hover:bg-rose-100 transition">Batal TRX</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada riwayat pembayaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL PEMBAYARAN -->
    <div x-show="bayarModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden" @click.away="bayarModal = false">
            <div class="px-5 py-4 bg-indigo-50 border-b border-indigo-100 flex justify-between items-center">
                <h3 class="font-bold text-indigo-800 text-lg">Catat Pembayaran Baru</h3>
                <button @click="bayarModal = false" class="text-indigo-400 hover:text-indigo-600"><i class="bi-x-lg"></i></button>
            </div>
            <form action="{{ route('keuangan.pembayaran.store') }}" method="POST">
                @csrf
                <input type="hidden" name="tagihan_id" x-model="selectedTagihanId">
                <div class="p-5 space-y-4">
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                        <p class="text-xs text-slate-500 mb-1">Membayar tagihan:</p>
                        <p class="font-bold text-slate-800" x-text="tagihanNama"></p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pembayaran</label>
                        <input type="date" name="tanggal_bayar" value="{{ date('Y-m-d') }}" class="w-full text-sm rounded-lg border-slate-300" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Bayar (Maks. Rp <span x-text="selectedNominal"></span>)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500 font-medium">Rp</span>
                            <input type="number" name="nominal_bayar" x-model="selectedNominal" :max="selectedNominal" min="1" class="w-full text-sm rounded-lg border-slate-300 pl-10" required>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">*Sistem otomatis mengisi dengan nominal sisa tunggakan.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Metode Pembayaran</label>
                        <select name="metode_pembayaran" class="w-full text-sm rounded-lg border-slate-300" required>
                            <option value="tunai">Tunai / Cash</option>
                            <option value="transfer">Transfer Bank</option>
                        </select>
                    </div>
                </div>
                <div class="p-5 border-t border-slate-200 bg-slate-50 flex justify-end gap-2">
                    <button type="button" @click="bayarModal = false" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
