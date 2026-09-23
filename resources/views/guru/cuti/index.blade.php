@extends('layouts.app')

@section('title', 'Pengajuan Cuti & Guru Pengganti')

@section('content')
<div class="space-y-6" x-data="{ approvalModal: false, selectedCuti: null }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-person-dash text-amber-600"></i>
                <span>Pengajuan Cuti & Penugasan Guru Pengganti (Inval)</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Selama masa cuti yang disetujui, jadwal KBM otomatis berstatus cuti dan dialihkan ke guru pengganti yang ditugaskan</p>
        </div>
        <div>
            <a href="{{ route('guru.cuti.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-blue-600/20 transition">
                <i class="bi-plus-circle text-sm"></i>
                <span>Ajukan Permohonan Cuti</span>
            </a>
        </div>
    </div>

    @if($isApprover)
    <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center gap-3 text-xs text-amber-900">
        <i class="bi-shield-lock-fill text-amber-600 text-lg"></i>
        <span>Hak Akses Verifikasi Cuti: Anda memiliki wewenang menyetujui atau menolak permohonan cuti dewan guru SMK Plus Al-Hilal.</span>
    </div>
    @endif

    <!-- Table List -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Guru Pemohon</th>
                        <th class="px-4 py-4">Jenis Cuti</th>
                        <th class="px-4 py-4">Rentang Tanggal</th>
                        <th class="px-4 py-4">Guru Pengganti (Inval)</th>
                        <th class="px-4 py-4">Alasan Cuti</th>
                        <th class="px-4 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($cutiList as $c)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-6 py-4 font-bold text-slate-900">
                            {{ $c->guru?->name ?? 'Guru' }}
                            <div class="text-[11px] text-slate-400 font-normal">NIP: {{ $c->guru->nip ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-800 font-bold rounded-lg text-xs">
                                {{ $c->jenis_cuti }}
                            </span>
                        </td>
                        <td class="px-4 py-4 font-mono text-slate-700">
                            {{ \Carbon\Carbon::parse($c->tanggal_mulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($c->tanggal_selesai)->format('d/m/Y') }}
                            <div class="text-[10px] text-slate-400 font-sans">
                                ({{ \Carbon\Carbon::parse($c->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($c->tanggal_selesai)) + 1 }} Hari)
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            @if($c->guruPengganti)
                            <div class="font-bold text-blue-700">{{ $c->guruPengganti->name ?? '-' }}</div>
                            <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded">Inval Aktif</span>
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-slate-600 max-w-xs truncate">
                            {{ $c->alasan }}
                            @if($c->catatan_approval)
                            <div class="text-[10px] text-purple-700 mt-0.5 font-semibold">
                                Note: {{ $c->catatan_approval }}
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $c->status_badge }}">
                                {{ $c->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-1">
                            @if($isApprover && $c->status === 'diajukan')
                            <button type="button" 
                                    @click="approvalModal = true; selectedCuti = { id: {{ $c->id }}, nama: '{{ addslashes($c->guru?->name ?? 'Guru') }}', jenis: '{{ $c->jenis_cuti }}', tanggal: '{{ $c->tanggal_mulai }} s/d {{ $c->tanggal_selesai }}' }"
                                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition inline-flex items-center gap-1 shadow-xs">
                                <i class="bi-check-circle"></i>
                                <span>Proses</span>
                            </button>
                            @endif

                            @if($c->lampiran_surat)
                            <a href="{{ asset('storage/' . $c->lampiran_surat) }}" target="_blank" class="p-2 text-blue-600 hover:text-blue-800 rounded-lg hover:bg-blue-50 transition inline-block" title="Lihat Lampiran">
                                <i class="bi-paperclip text-base"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <i class="bi-calendar-check text-3xl mb-2 inline-block"></i>
                            <p>Tidak ada data pengajuan cuti yang tercatat.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $cutiList->links() }}
        </div>
    </div>

    <!-- MODAL APPROVAL CUTI -->
    <div x-show="approvalModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative"
             @click.outside="approvalModal = false">
            
            <button @click="approvalModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <div class="mb-5">
                <div class="inline-flex p-3 rounded-2xl bg-amber-100 text-amber-700 text-xl mb-2">
                    <i class="bi-person-check-fill"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900">Persetujuan Permohonan Cuti</h3>
                <p class="text-xs text-slate-500" x-text="selectedCuti ? selectedCuti.nama + ' (' + selectedCuti.jenis + ')' : ''"></p>
                <p class="text-[11px] font-mono text-blue-700 font-bold mt-0.5" x-text="selectedCuti ? selectedCuti.tanggal : ''"></p>
            </div>

            <form :action="'{{ url('/guru/cuti') }}/' + (selectedCuti ? selectedCuti.id : '') + '/approve'" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keputusan Pimpinan</label>
                    <select name="status" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                        <option value="disetujui_kepsek">✅ Setujui Cuti (Kepala Sekolah)</option>
                        <option value="disetujui_waka">✅ Setujui Cuti (Waka Kurikulum)</option>
                        <option value="ditolak">❌ Tolak Permohonan Cuti</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Persetujuan / Arahan Pengganti</label>
                    <textarea name="catatan_approval" rows="3" placeholder="Tuliskan arahan jadwal tugas guru pengganti inval..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">Cuti disetujui. Jadwal KBM dialihkan kepada guru pengganti yang telah ditugaskan.</textarea>
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button type="button" @click="approvalModal = false" class="w-1/3 py-2.5 bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="w-2/3 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow-md shadow-emerald-600/30">
                        Proses Keputusan
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
