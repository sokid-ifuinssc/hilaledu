@extends('layouts.app')
@section('title', 'Rekap Tagihan Kelas - Wali Kelas')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Rekap Tagihan Kelas {{ $kelas->nama }} 💸</h1>
        <p class="text-sm text-slate-500 mt-1">Pantau pembayaran tagihan dan tunggakan anak wali Anda.</p>
    </div>

    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                    <tr>
                        <th scope="col" class="px-4 py-3">Nama Siswa</th>
                        <th scope="col" class="px-4 py-3 text-right">Total Tagihan</th>
                        <th scope="col" class="px-4 py-3 text-right">Telah Dibayar</th>
                        <th scope="col" class="px-4 py-3 text-right">Sisa Tunggakan</th>
                        <th scope="col" class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswas as $siswa)
                    @php
                        $totalTagihan = $siswa->tagihans->sum('nominal');
                        $totalTerbayar = $siswa->tagihans->sum('terbayar');
                        $sisa = $totalTagihan - $totalTerbayar;
                    @endphp
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">
                            {{ $siswa->name }}
                        </td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-emerald-600">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-rose-600 font-bold">Rp {{ number_format($sisa, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($totalTagihan == 0)
                                <span class="bg-slate-100 text-slate-600 text-xs px-2 py-1 rounded">Tidak ada</span>
                            @elseif($sisa <= 0)
                                <span class="bg-emerald-100 text-emerald-700 text-xs px-2 py-1 rounded font-bold">LUNAS</span>
                            @else
                                <span class="bg-amber-100 text-amber-700 text-xs px-2 py-1 rounded font-bold">TUNGGAKAN</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">Tidak ada data tagihan siswa di kelas ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
