@extends('layouts.app')

@section('title', 'Monitoring Suara Siswa & Evaluasi KBM')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-200 mb-2">
                <i class="bi-eye-fill text-indigo-600"></i>
                <span>Visibilitas Khusus Pimpinan Sekolah</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                Monitoring Masukan & Suara Siswa
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Data identitas siswa (Nama & NIS) terbaca bagi Admin/Superadmin untuk penelusuran pembinaan, namun tetap anonim di mata guru pengampu.
            </p>
        </div>

        <!-- Quick Stats -->
        <div class="flex items-center gap-2 flex-wrap">
            <div class="px-3.5 py-2 rounded-2xl bg-slate-100 text-center">
                <div class="text-lg font-black text-slate-900">{{ $stats['total'] }}</div>
                <div class="text-[10px] font-bold text-slate-500 uppercase">Total</div>
            </div>
            <div class="px-3.5 py-2 rounded-2xl bg-amber-50 text-amber-900 border border-amber-200 text-center">
                <div class="text-lg font-black text-amber-600">{{ $stats['baru'] }}</div>
                <div class="text-[10px] font-bold text-amber-800 uppercase">Baru</div>
            </div>
            <div class="px-3.5 py-2 rounded-2xl bg-blue-50 text-blue-900 border border-blue-200 text-center">
                <div class="text-lg font-black text-blue-600">{{ $stats['diproses'] }}</div>
                <div class="text-[10px] font-bold text-blue-800 uppercase">Diproses</div>
            </div>
            <div class="px-3.5 py-2 rounded-2xl bg-emerald-50 text-emerald-900 border border-emerald-200 text-center">
                <div class="text-lg font-black text-emerald-600">{{ $stats['selesai'] }}</div>
                <div class="text-[10px] font-bold text-emerald-800 uppercase">Selesai</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.keluhan.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
            <!-- Search -->
            <div class="md:col-span-2">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama siswa, NIS, isi masukan, atau guru..."
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
            </div>

            <!-- Filter Status -->
            <div>
                <select name="status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                    <option value="">Semua Status</option>
                    <option value="baru" {{ $statusFilter === 'baru' ? 'selected' : '' }}>Baru</option>
                    <option value="diproses" {{ $statusFilter === 'diproses' ? 'selected' : '' }}>Diproses</option>
                    <option value="selesai" {{ $statusFilter === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>

            <!-- Filter Kelas -->
            <div>
                <select name="kelas" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $kls)
                    <option value="{{ $kls }}" {{ $kelasFilter === $kls ? 'selected' : '' }}>{{ $kls }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition text-center">
                    <i class="bi-filter"></i> Filter
                </button>
                @if($search || $statusFilter || $kelasFilter || $kategoriFilter)
                <a href="{{ route('admin.keluhan.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                    <i class="bi-x-lg"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] font-black uppercase text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Pengirim (Siswa)</th>
                        <th class="px-5 py-3.5">Guru & Mapel Dituju</th>
                        <th class="px-5 py-3.5">Kategori Masalah</th>
                        <th class="px-5 py-3.5">Isi Keluhan & Harapan</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($keluhans as $k)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-5 py-4 whitespace-nowrap text-slate-500 font-semibold">
                            <div>{{ \Carbon\Carbon::parse($k->tanggal_kbm)->format('d M Y') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $k->created_at->diffForHumans() }}</div>
                        </td>

                        <!-- Identitas Siswa Terbuka untuk Admin -->
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 font-extrabold flex items-center justify-center flex-shrink-0">
                                    <i class="bi-person-fill text-sm"></i>
                                </div>
                                <div>
                                    <div class="font-extrabold text-slate-900 leading-tight">
                                        {{ $k->siswa->name ?? 'Siswa ID: '.$k->siswa_user_id }}
                                    </div>
                                    <div class="text-[10px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                                        <span class="font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.2 rounded">NIS: {{ $k->siswa->username ?? '-' }}</span>
                                        <span>&bull;</span>
                                        <span class="font-bold text-slate-700">{{ $k->kelas }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-900">{{ $k->targetGuru->name ?? '-' }}</div>
                            <div class="text-[11px] text-blue-700 font-semibold">{{ $k->mataPelajaran->nama ?? '-' }}</div>
                        </td>

                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 font-bold border border-amber-200 text-[11px]">
                                {{ $k->kategori_masalah }}
                            </span>
                        </td>

                        <td class="px-5 py-4 max-w-xs">
                            <p class="text-slate-800 line-clamp-2 leading-relaxed font-medium">"{{ $k->isi_keluhan }}"</p>
                            @if($k->harapan_siswa)
                            <p class="text-[10px] text-emerald-700 font-semibold mt-1 truncate">
                                <strong>Harapan:</strong> {{ $k->harapan_siswa }}
                            </p>
                            @endif
                        </td>

                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase border {{ $k->status_badge }}">
                                {{ $k->status_label }}
                            </span>
                        </td>

                        <td class="px-5 py-4 text-center whitespace-nowrap">
                            <a href="{{ route('guru.keluhan.show', $k) }}" 
                               class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl transition inline-flex items-center gap-1">
                                <span>Detail</span>
                                <i class="bi-chevron-right text-[10px]"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                            <i class="bi-chat-square-check text-3xl mb-2 inline-block text-slate-300"></i>
                            <p class="text-xs font-semibold">Tidak ada masukan suara siswa yang sesuai kriteria.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($keluhans->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $keluhans->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
