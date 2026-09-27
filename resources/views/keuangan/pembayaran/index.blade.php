@extends('layouts.app')
@section('title', 'Riwayat Pembayaran Siswa - Keuangan')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Riwayat Pembayaran 💳</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar semua transaksi pembayaran tagihan siswa.</p>
        </div>
    </div>

    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                    <tr>
                        <th scope="col" class="px-4 py-3">Waktu</th>
                        <th scope="col" class="px-4 py-3">Kode TRX</th>
                        <th scope="col" class="px-4 py-3">Siswa</th>
                        <th scope="col" class="px-4 py-3">Tagihan</th>
                        <th scope="col" class="px-4 py-3 text-right">Nominal</th>
                        <th scope="col" class="px-4 py-3">Metode</th>
                        <th scope="col" class="px-4 py-3">Penerima</th>
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
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $trx->tagihan->siswa->name ?? 'Terhapus' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $trx->tagihan->nama_tagihan ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-600">Rp {{ number_format($trx->nominal_bayar, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 capitalize text-xs">
                            @if($trx->metode_pembayaran == 'tunai')
                                <span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded">{{ $trx->metode_pembayaran }}</span>
                            @else
                                <span class="bg-amber-100 text-amber-700 px-2 py-1 rounded">{{ $trx->metode_pembayaran }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs">{{ $trx->penerima->name ?? 'Sistem' }}</td>
                        <td class="px-4 py-3 text-right">
                            <!-- Batal Transaksi -->
                            <form action="{{ route('keuangan.pembayaran.destroy', $trx->id) }}" method="POST" onsubmit="return confirm('Yakin membatalkan transaksi ini? Nominal akan dikembalikan menjadi tunggakan.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs px-2 py-1 border border-rose-200 rounded bg-rose-50 hover:bg-rose-100 transition">Batal</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada riwayat pembayaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $pembayarans->links() }}
        </div>
    </div>

</div>
@endsection
