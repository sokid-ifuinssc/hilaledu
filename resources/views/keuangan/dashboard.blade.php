@extends('layouts.app')
@section('title', 'Dashboard Keuangan')

@section('content')
<div class="space-y-5">

    <x-dashboard-hero
        icon="bi-cash-stack"
        badge="Administrasi Keuangan &amp; SPP"
        title="Pusat Keuangan Sekolah"
        description="Pantau arus kas sekolah, kelola tagihan SPP bulanan siswa, dan rekapitulasi data pembayaran secara mudah dan transparan." />

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border border-emerald-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-wallet2 text-emerald-600 text-2xl"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Pemasukan</div>
                <div class="text-xl font-black text-emerald-600 mt-0.5">Rp {{ number_format($totalPemasukan ?? 0, 0, ',', '.') }}</div>
                <div class="text-xs text-slate-500 font-medium mt-0.5">Arus Kas Terverifikasi</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-sky-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-sky-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-receipt-cutoff text-sky-600 text-2xl"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Jumlah Tagihan</div>
                <div class="text-2xl font-black text-slate-800 mt-0.5">{{ $totalTagihan ?? 0 }}</div>
                <div class="text-xs text-slate-500 font-medium mt-0.5">Catatan Tagihan Siswa</div>
            </div>
        </div>
    </div>

    {{-- Menu Pintasan --}}
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs">
        <h3 class="font-extrabold text-sm text-slate-900 mb-4 flex items-center gap-2">
            <i class="bi-lightning-charge-fill text-amber-500"></i>
            Aksi Cepat Keuangan
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="{{ route('keuangan.tagihan.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50 transition group">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg shrink-0 group-hover:bg-emerald-200 transition">
                    <i class="bi-receipt"></i>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-800">Daftar Tagihan Siswa</div>
                    <div class="text-xs text-slate-500">Buat tagihan baru, cek status lunas &amp; belum lunas</div>
                </div>
            </a>
            <a href="{{ route('keuangan.pembayaran.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-sky-400 hover:bg-sky-50 transition group">
                <div class="w-10 h-10 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center text-lg shrink-0 group-hover:bg-sky-200 transition">
                    <i class="bi-credit-card-2-front"></i>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-800">Riwayat Pembayaran</div>
                    <div class="text-xs text-slate-500">Rekap transaksi masuk &amp; cetak bukti kwitansi</div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
