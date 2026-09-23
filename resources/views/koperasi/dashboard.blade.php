@extends('layouts.app')
@section('title', 'Dashboard Koperasi')

@section('content')
<div class="space-y-5">

    <x-dashboard-hero
        icon="bi-shop"
        badge="Unit Usaha Sekolah"
        title="Koperasi Sekolah"
        description="Kelola data keanggotaan warga sekolah, pencatatan transaksi kasir, serta laporan keuangan harian koperasi secara rapi dan akurat." />

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border border-sky-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-sky-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-person-badge text-sky-600 text-2xl"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Anggota Terdaftar</div>
                <div class="text-2xl font-black text-slate-800 mt-0.5">{{ $totalAnggota ?? 0 }}</div>
                <div class="text-xs text-sky-600 font-medium mt-0.5">Guru, Tendik &amp; Siswa</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-amber-200 shadow-xs p-5 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center shrink-0">
                <i class="bi-cart3 text-amber-600 text-2xl"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Total Transaksi</div>
                <div class="text-2xl font-black text-amber-600 mt-0.5">{{ $totalTransaksi ?? 0 }}</div>
                <div class="text-xs text-slate-500 font-medium mt-0.5">Aktivitas Belanja</div>
            </div>
        </div>
    </div>

    {{-- Menu Pintasan --}}
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-xs">
        <h3 class="font-extrabold text-sm text-slate-900 mb-4 flex items-center gap-2">
            <i class="bi-lightning-charge-fill text-amber-500"></i>
            Menu Pintasan Koperasi
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="{{ route('koperasi.anggota.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-sky-400 hover:bg-sky-50 transition group">
                <div class="w-10 h-10 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center text-lg shrink-0 group-hover:bg-sky-200 transition">
                    <i class="bi-people"></i>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-800">Data Anggota Koperasi</div>
                    <div class="text-xs text-slate-500">Pendaftaran anggota baru dan status simpanan</div>
                </div>
            </a>
            <a href="{{ route('koperasi.transaksi.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-amber-400 hover:bg-amber-50 transition group">
                <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-lg shrink-0 group-hover:bg-amber-200 transition">
                    <i class="bi-cart-check"></i>
                </div>
                <div>
                    <div class="font-bold text-sm text-slate-800">Catatan Transaksi Penjualan</div>
                    <div class="text-xs text-slate-500">Rekap transaksi kasir dan struk pembelian</div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
