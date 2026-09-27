@extends('layouts.app')
@section('title', 'Laporan Tagihan Siswa - Keuangan')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Laporan Keuangan Tagihan 📊</h1>
            <p class="text-sm text-slate-500 mt-1">Laporan rekapitulasi pembayaran dan tunggakan tagihan siswa.</p>
        </div>
        <div class="flex items-center space-x-4">
            <form method="GET" action="{{ route('keuangan.laporan.index') }}" class="flex items-center gap-2">
                <select name="bulan" class="rounded-lg border-slate-300 text-sm">
                    @for($i=1; $i<=12; $i++)
                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ $bulan == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                            {{ date('F', mktime(0,0,0,$i,1)) }}
                        </option>
                    @endfor
                </select>
                <select name="tahun" class="rounded-lg border-slate-300 text-sm">
                    @for($i=date('Y')-2; $i<=date('Y')+1; $i++)
                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
                <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-700">Filter</button>
            </form>
            <a href="{{ route('keuangan.laporan.print', ['bulan' => $bulan, 'tahun' => $tahun]) }}" target="_blank" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
                <i class="bi-printer me-1"></i> Cetak Laporan
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-500 mb-1">Total Tagihan Keseluruhan</h3>
            <div class="text-2xl font-bold text-slate-800">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-500 mb-1">Total Dana Masuk Keseluruhan</h3>
            <div class="text-2xl font-bold text-emerald-600">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <h3 class="text-sm font-semibold text-slate-500 mb-1">Total Sisa Tunggakan</h3>
            <div class="text-2xl font-bold text-rose-600">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-emerald-200 bg-emerald-50">
            <h3 class="text-sm font-semibold text-emerald-700 mb-1">Pembayaran Bulan Ini</h3>
            <div class="text-2xl font-bold text-emerald-800">Rp {{ number_format($pembayaranBulanIni, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Rincian Per Siswa</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                    <tr>
                        <th scope="col" class="px-4 py-3">Nama Siswa</th>
                        <th scope="col" class="px-4 py-3">Kelas</th>
                        <th scope="col" class="px-4 py-3 text-right">Total Tagihan</th>
                        <th scope="col" class="px-4 py-3 text-right">Telah Dibayar</th>
                        <th scope="col" class="px-4 py-3 text-right">Sisa Tunggakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswas as $siswa)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $siswa->name }}</td>
                        <td class="px-4 py-3">{{ $siswa->kelasModel->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($siswa->total_tagihan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-emerald-600">Rp {{ number_format($siswa->total_terbayar, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-rose-600 font-bold">Rp {{ number_format($siswa->sisa_tunggakan, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada data siswa.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
