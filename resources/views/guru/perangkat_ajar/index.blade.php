@extends('layouts.app')

@section('title', 'Link Perangkat Pembelajaran (Google Drive)')

@section('content')
<div class="space-y-6" x-data="{ reviewModal: false, selectedItem: null }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="bi-google-play text-blue-600"></i>
                <span>Link Perangkat Pembelajaran Lengkap</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Pengumpulan link Google Drive dokumen perangkat ajar lengkap yang sudah ditandatangani untuk verifikasi Waka Kurikulum</p>
        </div>
        <div>
            <a href="{{ route('guru.perangkat-ajar.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-blue-600/20 transition">
                <i class="bi-plus-circle text-sm"></i>
                <span>Input Link Google Drive Baru</span>
            </a>
        </div>
    </div>

    @if($isWaka)
    <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-2xl flex items-center justify-between gap-3 text-xs text-indigo-950">
        <div class="flex items-center gap-2.5 font-semibold">
            <i class="bi-patch-check-fill text-indigo-600 text-lg"></i>
            <span>Mode Review Waka Kurikulum Aktif: Anda dapat memeriksa link Google Drive dewan guru dan memberikan catatan/status persetujuan.</span>
        </div>
    </div>
    @endif

    <!-- Table List -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Guru & Mata Pelajaran</th>
                        <th class="px-4 py-4">Kelas & Semester</th>
                        <th class="px-6 py-4">Judul Perangkat Ajar</th>
                        <th class="px-4 py-4">Link Google Drive</th>
                        <th class="px-4 py-4 text-center">Status Verifikasi</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($perangkatList as $p)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $p->guru?->name ?? 'Guru' }}</div>
                            <div class="text-[11px] text-slate-500">{{ $p->mataPelajaran->nama ?? 'Mata Pelajaran' }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 font-bold rounded-lg border border-indigo-200 text-xs">
                                Kelas {{ $p->kelas }}
                            </span>
                            <div class="text-[10px] text-slate-400 mt-1 capitalize">{{ $p->semester }} &bull; {{ $p->tahun_ajaran }}</div>
                        </td>
                        <td class="px-6 py-4 text-slate-800 font-medium max-w-xs">
                            <div class="line-clamp-2">{{ $p->judul }}</div>
                            @if($p->catatan_waka)
                            <div class="text-[11px] text-slate-500 bg-slate-50 p-2 rounded-lg mt-1 border border-slate-100">
                                <strong>Catatan Waka:</strong> {{ $p->catatan_waka }}
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <a href="{{ $p->link_gdrive }}" target="_blank" class="px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold rounded-xl border border-blue-200 inline-flex items-center gap-1.5 transition text-xs">
                                <i class="bi-box-arrow-up-right"></i>
                                <span>Buka GDrive</span>
                            </a>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $p->status_badge }}">
                                {{ $p->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-1">
                            @if($isWaka)
                            <button type="button" 
                                    @click="reviewModal = true; selectedItem = { id: {{ $p->id }}, guru: '{{ addslashes($p->guru?->name ?? 'Guru') }}', mapel: '{{ addslashes($p->mataPelajaran->nama ?? 'Mapel') }}', status: '{{ $p->status }}', catatan: '{{ addslashes($p->catatan_waka ?? '') }}' }"
                                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition inline-flex items-center gap-1 shadow-xs">
                                <i class="bi-check-circle"></i>
                                <span>Review Waka</span>
                            </button>
                            @endif

                            @if($p->guru_user_id === auth()->id() || auth()->user()->isSuperAdmin())
                            <form action="{{ route('guru.perangkat-ajar.destroy', $p) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus berkas perangkat ajar ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 transition">
                                    <i class="bi-trash text-base"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <i class="bi-google text-3xl mb-2 inline-block"></i>
                            <p>Belum ada link perangkat pembelajaran yang diunggah.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $perangkatList->links() }}
        </div>
    </div>

    <!-- MODAL REVIEW WAKA KURIKULUM -->
    <div x-show="reviewModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative"
             @click.outside="reviewModal = false">
            
            <button @click="reviewModal = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <i class="bi-x-lg text-lg"></i>
            </button>

            <div class="mb-5">
                <div class="inline-flex p-3 rounded-2xl bg-indigo-100 text-indigo-700 text-xl mb-2">
                    <i class="bi-patch-check-fill"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900">Verifikasi Perangkat Pembelajaran</h3>
                <p class="text-xs text-slate-500" x-text="selectedItem ? selectedItem.guru + ' - ' + selectedItem.mapel : ''"></p>
            </div>

            <form :action="'{{ url('/guru/perangkat-ajar') }}/' + (selectedItem ? selectedItem.id : '') + '/verify'" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keputusan Status Verifikasi</label>
                    <select name="status" x-model="selectedItem.status" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold">
                        <option value="disetujui">✅ Disetujui / Valid (Lengkap & TTD)</option>
                        <option value="perlu_perbaikan">⚠️ Perlu Perbaikan / Revisi</option>
                        <option value="menunggu_review">⏳ Menunggu Review</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Review Waka Kurikulum</label>
                    <textarea name="catatan_waka" x-model="selectedItem.catatan" rows="3" placeholder="Tuliskan catatan kelengkapan dokumen atau koreksi modul..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium"></textarea>
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button type="button" @click="reviewModal = false" class="w-1/3 py-2.5 bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="w-2/3 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-md shadow-indigo-600/30">
                        Simpan Verifikasi
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
