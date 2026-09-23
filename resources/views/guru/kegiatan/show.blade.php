@extends('layouts.app')

@section('title', 'Detail Kegiatan Sekolah & Presensi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-xs text-slate-500 mb-1">
                <a href="{{ route('guru.kegiatan.index') }}" class="text-blue-600 hover:underline">&larr; Kembali ke Agenda Kegiatan</a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                {{ $kegiatan->judul }}
            </h1>
            <p class="text-xs text-slate-500 mt-1 flex items-center gap-2 flex-wrap">
                <span>Bidang: <strong class="text-slate-800">{{ $kegiatan->bidang_label }}</strong></span>
                <span>&bull;</span>
                <span>Diinput oleh: <strong class="text-slate-800">{{ $kegiatan->author->name ?? 'Pimpinan Sekolah' }}</strong></span>
                @if($kegiatan->editor)
                <span>&bull;</span>
                <span class="text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200 text-[11px] font-semibold">
                    <i class="bi-pencil"></i> Diedit oleh: <strong>{{ $kegiatan->editor->name }}</strong> ({{ $kegiatan->updated_at->format('d M Y H:i') }})
                </span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider {{ $kegiatan->bidang_color }}">
                {{ $kegiatan->bidang_label }}
            </span>

            @if($kegiatan->canBeEditedBy(auth()->user()))
            <a href="{{ route('guru.kegiatan.edit', $kegiatan) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-md shadow-blue-600/20 transition">
                <i class="bi-pencil-square text-sm"></i>
                <span>Edit Agenda</span>
            </a>
            @endif

            @if($kegiatan->canBeEditedBy(auth()->user()))
            <form action="{{ route('guru.kegiatan.destroy', $kegiatan) }}" method="POST" onsubmit="return confirm('Hapus agenda kegiatan sekolah ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-xl hover:bg-slate-100 transition" title="Hapus Agenda">
                    <i class="bi-trash3 text-sm"></i>
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Content Card Detail Agenda -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6 text-xs">
        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Tanggal Agenda</div>
                <div class="font-bold text-slate-800 mt-1 text-xs sm:text-sm">{{ \Carbon\Carbon::parse($kegiatan->tanggal_kegiatan)->isoFormat('dddd, D MMMM Y') }}</div>
            </div>
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Waktu Pelaksanaan</div>
                <div class="font-bold text-slate-800 mt-1 text-xs sm:text-sm">{{ substr($kegiatan->waktu_mulai,0,5) ?: '08:00' }} - {{ substr($kegiatan->waktu_selesai,0,5) ?: 'Selesai' }} WIB</div>
            </div>
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Lokasi / Tempat</div>
                <div class="font-bold text-slate-800 mt-1 text-xs sm:text-sm flex items-center gap-1">
                    <i class="bi-geo-alt-fill text-rose-500"></i> {{ $kegiatan->tempat }}
                </div>
            </div>
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Sasaran Peserta</div>
                <div class="font-bold text-slate-800 mt-1 text-xs sm:text-sm">{{ $kegiatan->sasaran }}</div>
            </div>
        </div>

        <div class="space-y-2">
            <h3 class="font-bold text-slate-900 text-sm border-b pb-2">Deskripsi & Uraian Lengkap</h3>
            <div class="text-slate-700 bg-slate-50 p-5 rounded-2xl border border-slate-100 whitespace-pre-line leading-relaxed text-xs">
                {{ $kegiatan->deskripsi }}
            </div>
        </div>

        @if($kegiatan->lampiran)
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-2 text-blue-900 font-semibold">
                <i class="bi-paperclip text-lg text-blue-600"></i>
                <span>Lampiran Dokumen Surat Edaran / Berkas Acara</span>
            </div>
            <a href="{{ asset('storage/' . $kegiatan->lampiran) }}" target="_blank" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-xs transition">
                Unduh / Lihat Berkas
            </a>
        </div>
        @endif

    </div>

    <!-- Panel Form Presensi Kehadiran Guru -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="p-2.5 bg-emerald-100 text-emerald-700 rounded-2xl">
                    <i class="bi-fingerprint text-xl"></i>
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Presensi Kehadiran Kegiatan Anda</h2>
                    <p class="text-xs text-slate-500">Konfirmasi kehadiran pada agenda yang diinput oleh pimpinan sekolah</p>
                </div>
            </div>
            @if($presensiSaya)
            <span class="px-3 py-1.5 rounded-xl text-xs font-bold border flex items-center gap-1.5 {{ $presensiSaya->badge_class }}">
                <i class="bi-check-circle-fill text-sm"></i>
                <span>Status: {{ $presensiSaya->status_label }}</span>
                <span class="text-[10px] font-normal opacity-80">({{ \Carbon\Carbon::parse($presensiSaya->waktu_presensi)->format('d M H:i') }})</span>
            </span>
            @endif
        </div>

        <form method="POST" action="{{ route('guru.kegiatan.presensi', $kegiatan) }}" enctype="multipart/form-data" class="space-y-4 pt-2">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Status Kehadiran Anda
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                    <label class="p-3.5 border-2 border-slate-200 rounded-2xl cursor-pointer hover:border-emerald-500 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/80 transition flex items-center gap-2.5 font-bold">
                        <input type="radio" name="status" value="hadir" {{ (!$presensiSaya || $presensiSaya->status === 'hadir') ? 'checked' : '' }} class="text-emerald-600">
                        <span class="text-emerald-900">✅ Hadir Kegiatan</span>
                    </label>
                    <label class="p-3.5 border-2 border-slate-200 rounded-2xl cursor-pointer hover:border-blue-500 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/80 transition flex items-center gap-2.5 font-bold">
                        <input type="radio" name="status" value="izin" {{ ($presensiSaya && $presensiSaya->status === 'izin') ? 'checked' : '' }} class="text-blue-600">
                        <span class="text-blue-900">📝 Izin Resmi</span>
                    </label>
                    <label class="p-3.5 border-2 border-slate-200 rounded-2xl cursor-pointer hover:border-purple-500 has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50/80 transition flex items-center gap-2.5 font-bold">
                        <input type="radio" name="status" value="sakit" {{ ($presensiSaya && $presensiSaya->status === 'sakit') ? 'checked' : '' }} class="text-purple-600">
                        <span class="text-purple-900">🏥 Sakit</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Komentar / Catatan / Tanggapan Agenda (Opsional)
                </label>
                <textarea name="catatan" rows="3" placeholder="Tuliskan catatan, tanggapan agenda, notulensi rapat, atau alasan izin/sakit..." class="w-full p-3.5 bg-slate-50 border border-slate-300 rounded-2xl text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">{{ old('catatan', $presensiSaya?->catatan) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Bukti Foto Kehadiran / Surat Keterangan (Opsional)
                </label>
                @if($presensiSaya?->foto_bukti)
                <div class="mb-2 flex items-center gap-2 text-xs text-blue-600 font-semibold">
                    <i class="bi-file-earmark-check"></i>
                    <a href="{{ asset('storage/' . $presensiSaya->foto_bukti) }}" target="_blank" class="hover:underline">Lihat Lampiran Tersimpan</a>
                </div>
                @endif
                <input type="file" name="foto_bukti" class="w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                <p class="text-[11px] text-slate-400 mt-1">Format: JPG, PNG, atau PDF. Maksimal 3MB.</p>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 font-bold text-white rounded-2xl text-xs transition shadow-md shadow-emerald-600/20 active:scale-[0.98] flex items-center gap-2">
                    <i class="bi-fingerprint text-base"></i>
                    <span>{{ $presensiSaya ? 'Perbarui Presensi Kehadiran' : 'Simpan Presensi Kehadiran' }}</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Rekap Kehadiran Dewan Guru Lainnya Pada Kegiatan Ini -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="p-2 bg-indigo-100 text-indigo-700 rounded-xl">
                    <i class="bi-people-fill text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Daftar Kehadiran Dewan Guru ({{ $kegiatan->presensis->count() }} Terdata)</h3>
                    <p class="text-xs text-slate-500">Guru yang telah melakukan presensi pada agenda kegiatan ini</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 font-bold">
                    {{ $kegiatan->presensis->where('status', 'hadir')->count() }} Hadir
                </span>
                <span class="px-2.5 py-1 rounded-lg bg-blue-100 text-blue-800 font-bold">
                    {{ $kegiatan->presensis->where('status', 'izin')->count() }} Izin
                </span>
                <span class="px-2.5 py-1 rounded-lg bg-purple-100 text-purple-800 font-bold">
                    {{ $kegiatan->presensis->where('status', 'sakit')->count() }} Sakit
                </span>
            </div>
        </div>

        @if($kegiatan->presensis->isEmpty())
        <div class="py-8 text-center bg-slate-50 rounded-2xl border border-slate-100">
            <i class="bi-person-x text-2xl text-slate-400"></i>
            <p class="text-xs text-slate-500 mt-1">Belum ada dewan guru yang melakukan presensi pada kegiatan ini.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px] bg-slate-50/70">
                        <th class="py-3 px-4 rounded-l-xl">No</th>
                        <th class="py-3 px-4">Nama Guru</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Waktu Presensi</th>
                        <th class="py-3 px-4 rounded-r-xl">Komentar / Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($kegiatan->presensis as $idx => $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 font-semibold text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-3 px-4">
                            <span class="font-extrabold text-slate-900">{{ $p->guru?->name ?? 'Guru' }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $p->badge_class }}">
                                {{ $p->status_label }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-medium">
                            {{ \Carbon\Carbon::parse($p->waktu_presensi)->format('d M Y H:i') }} WIB
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $p->catatan ?: '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection
