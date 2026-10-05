{{-- Rincian kehadiran per hari untuk satu pegawai. Params: $detail (satu row dari service), $mengajar (bool), $closeUrl (nullable) --}}
@php $mengajar = $mengajar ?? false; $closeUrl = $closeUrl ?? null; @endphp
<div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-3">
        <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
            <i class="bi bi-calendar2-week text-emerald-600"></i>
            <span>Rincian Per Hari{{ $mengajar ? ' Mengajar' : '' }} &mdash; {{ $detail['nama'] }}</span>
        </h2>
        @if($closeUrl)
        <a href="{{ $closeUrl }}" class="text-xs font-bold text-rose-600 hover:underline">Tutup Rincian</a>
        @endif
    </div>
    <div class="overflow-x-auto max-h-[28rem] overflow-y-auto">
        <table class="w-full text-left text-xs text-slate-600">
            <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-bold sticky top-0">
                <tr>
                    <th class="px-4 py-2.5">Tanggal</th>
                    <th class="px-4 py-2.5">Hari</th>
                    @if($mengajar)
                    <th class="px-4 py-2.5 text-center">Sesi Terjadwal</th>
                    @else
                    <th class="px-4 py-2.5 text-center">Masuk</th>
                    <th class="px-4 py-2.5 text-center">Pulang</th>
                    @endif
                    <th class="px-4 py-2.5 text-center">Status</th>
                    @unless($mengajar)
                    <th class="px-4 py-2.5">Catatan</th>
                    @endunless
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($detail['detail'] as $d)
                <tr>
                    <td class="px-4 py-2 font-mono">{{ \Carbon\Carbon::parse($d['tanggal'])->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">{{ $d['hari'] }}</td>
                    @if($mengajar)
                    <td class="px-4 py-2 text-center">{{ $d['sesi'] }}</td>
                    @else
                    <td class="px-4 py-2 text-center font-mono">{{ $d['jam_masuk'] ?? '-' }}@if($d['terlambat']) <span class="text-amber-600">(+{{ $d['terlambat'] }}m)</span>@endif</td>
                    <td class="px-4 py-2 text-center font-mono">{{ $d['jam_pulang'] ?? '-' }}</td>
                    @endif
                    <td class="px-4 py-2 text-center">
                        <span class="px-2 py-0.5 rounded-lg border font-bold text-[11px] {{ $d['badge'] }}">{{ $d['label'] }}</span>
                    </td>
                    @unless($mengajar)
                    <td class="px-4 py-2 italic text-slate-500">{{ $d['catatan'] ?: '-' }}</td>
                    @endunless
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Tidak ada hari kerja pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
