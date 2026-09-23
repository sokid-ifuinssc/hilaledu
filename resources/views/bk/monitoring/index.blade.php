@extends('layouts.app')
@section('title', 'Monitoring Tindak Lanjut')

@section('content')
<div class="space-y-5">

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-11 h-11 bg-red-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="bi-exclamation-circle-fill text-red-600 text-lg"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Belum Diproses</div>
                <div class="text-2xl font-black text-red-600 mt-0.5">{{ $stats['belum_diproses'] }}</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-11 h-11 bg-amber-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="bi-hourglass-split text-amber-600 text-lg"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Sedang Diproses</div>
                <div class="text-2xl font-black text-amber-600 mt-0.5">{{ $stats['sedang_diproses'] }}</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-center gap-4 hover:shadow-sm transition">
            <div class="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                <i class="bi-check-circle-fill text-emerald-600 text-lg"></i>
            </div>
            <div>
                <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Selesai</div>
                <div class="text-2xl font-black text-emerald-600 mt-0.5">{{ $stats['selesai'] }}</div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="flex items-center gap-3">
        <form method="GET" class="flex items-center gap-3">
            <select name="status" class="form-select w-48 text-sm" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="belum_diproses" {{ request('status') == 'belum_diproses' ? 'selected' : '' }}>Belum Diproses</option>
                <option value="sedang_diproses" {{ request('status') == 'sedang_diproses' ? 'selected' : '' }}>Sedang Diproses</option>
                <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
            <i class="bi-eye-fill text-indigo-600"></i>
            <h3 class="font-extrabold text-sm text-slate-900">Daftar Tindak Lanjut Wali Kelas</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Tindakan</th>
                        <th>Wali Kelas</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tindakLanjuts as $tl)
                    @php
                        $pelanggaran = $tl->rekomendasi?->pelanggaran ?? $tl->rekomendasiKaprog?->pelanggaran;
                        $siswa = $pelanggaran?->siswa;
                    @endphp
                    <tr>
                        <td class="text-sm text-slate-600">{{ $tl->tanggal_tindak_lanjut->format('d/m/Y') }}</td>
                        <td class="text-sm font-semibold text-slate-800">{{ $siswa?->nama_lengkap ?? '-' }}</td>
                        <td class="text-sm text-slate-600">{{ $siswa?->kelas?->nama ?? '-' }}</td>
                        <td class="text-sm text-slate-600 max-w-xs truncate">{{ $tl->tindakan_yang_dilakukan }}</td>
                        <td class="text-sm text-slate-600">{{ $tl->waliKelas?->nama_lengkap ?? '-' }}</td>
                        <td><span class="badge {{ $tl->status_badge }}">{{ $tl->status_label }}</span></td>
                        <td>
                            <div class="flex items-center gap-2">
                                @if($pelanggaran)
                                <a href="{{ route('bk.pelanggaran.show', $pelanggaran) }}"
                                   class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">Detail</a>
                                @endif
                                <div x-data="{ showCatatan: false }">
                                    <button @click="showCatatan = !showCatatan"
                                            class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 hover:underline">
                                        Catatan BK
                                    </button>
                                    <div x-show="showCatatan" x-transition class="mt-2">
                                        <form method="POST" action="{{ route('bk.monitoring.catatan', $tl) }}">
                                            @csrf
                                            <textarea name="catatan_bk" class="form-control text-xs" rows="2"
                                                placeholder="Tambah catatan...">{{ $tl->catatan_bk }}</textarea>
                                            <button type="submit"
                                                    class="mt-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition">
                                                Simpan Catatan
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-400">
                            <i class="bi-inbox text-2xl block mb-2"></i>
                            Belum ada tindak lanjut
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tindakLanjuts->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">
            {{ $tindakLanjuts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@section('helpContent')
<div class="help-section">
    <h3><span class="help-icon" style="background: #eef2ff; color: #4f46e5;">👁️</span> Monitoring Tindak Lanjut</h3>
    <p>Pantau seluruh proses tindak lanjut yang dilakukan oleh Wali Kelas. Anda dapat melihat progres, menambahkan catatan, dan memverifikasi penyelesaian.</p>
</div>
<div class="help-section">
    <h3><span class="help-icon" style="background: #f0fdf4; color: #16a34a;">📝</span> Cara Penggunaan</h3>
    <ol>
        <li>Lihat daftar semua <strong>tindak lanjut</strong> beserta statusnya</li>
        <li>Filter berdasarkan <strong>status</strong> (Dalam Proses/Selesai)</li>
        <li>Klik untuk melihat <strong>detail</strong> tindak lanjut</li>
        <li>Tambahkan <strong>catatan BK</strong> jika diperlukan untuk tindak lanjut yang sedang berjalan</li>
    </ol>
</div>
<div class="help-result">
    <h4>✅ Hasil yang Diharapkan</h4>
    <p>Guru BK memiliki visibilitas penuh atas semua proses tindak lanjut, memastikan tidak ada pelanggaran yang terlewat dari penanganan.</p>
</div>
@endsection
