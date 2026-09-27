@extends('layouts.app')
@section('title', 'Tagihan & Kewajiban Saya')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Tagihan & Kewajiban Saya 💸</h1>
        <p class="text-sm text-slate-500 mt-1">Rincian biaya pendidikan dan riwayat pembayaran.</p>
    </div>

    @php
        $totalTagihan = $tagihans->sum('nominal');
        $totalTerbayar = $tagihans->sum('terbayar');
        $totalTunggakan = $totalTagihan - $totalTerbayar;
    @endphp

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
                <h3 class="text-slate-500 text-sm font-medium">Sisa Tunggakan</h3>
            </div>
            <div class="text-2xl font-bold text-rose-600">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</div>
            @if($totalTunggakan > 0)
            <div class="mt-2 text-xs text-rose-500">Segera lunasi tunggakan Anda.</div>
            @endif
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
                    </tr>
                </thead>
                <tbody>
                    @forelse($tagihans as $tagihan)
                    @php $sisa = $tagihan->nominal - $tagihan->terbayar; @endphp
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $tagihan->nama_tagihan }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($tagihan->nominal, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-emerald-600">Rp {{ number_format($tagihan->terbayar, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-rose-600 font-bold">Rp {{ number_format($sisa, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($tagihan->status == 'lunas' || $sisa <= 0)
                                <span class="bg-emerald-100 text-emerald-700 text-xs px-2 py-1 rounded font-bold">LUNAS</span>
                            @else
                                <span class="bg-amber-100 text-amber-700 text-xs px-2 py-1 rounded font-bold">BELUM LUNAS</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada tagihan pendidikan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- RIWAYAT PEMBAYARAN -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Riwayat Pembayaran</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                    <tr>
                        <th scope="col" class="px-4 py-3">Tanggal / Waktu</th>
                        <th scope="col" class="px-4 py-3">Kode TRX</th>
                        <th scope="col" class="px-4 py-3">Tagihan</th>
                        <th scope="col" class="px-4 py-3 text-right">Nominal Bayar</th>
                        <th scope="col" class="px-4 py-3">Penerima (Kasir)</th>
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
                        <td class="px-4 py-3 text-xs">{{ $trx->penerima->name ?? 'Sistem' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada riwayat pembayaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
