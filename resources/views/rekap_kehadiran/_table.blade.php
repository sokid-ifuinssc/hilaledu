{{--
    Tabel rekap (layar). Params:
      $rekap       : ['rows','total'] dari service
      $mengajar    : bool  true = format rekap mengajar (ada kolom jam/minggu)
      $detailUrl   : null | Closure(int $id): string  -> tautan "Rincian"
      $logUrl      : null | Closure(int $id): string  -> tautan ke log presensi mengajar (opsional)
--}}
@php
    $mengajar = $mengajar ?? false;
    $detailUrl = $detailUrl ?? null;
    $logUrl = $logUrl ?? null;
    $rows = $rekap['rows'];
    $t = $rekap['total'];
    $badgePct = fn ($p) => $p >= 90 ? 'bg-emerald-100 text-emerald-800 border-emerald-300'
        : ($p >= 75 ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-rose-100 text-rose-800 border-rose-300');
    $colspan = $mengajar ? 12 : 11;
    if ($detailUrl) $colspan++;
@endphp
<div class="overflow-x-auto">
    <table class="w-full text-left text-xs text-slate-600">
        <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-100">
            <tr>
                <th class="px-3 py-3 text-center">No</th>
                <th class="px-3 py-3">Nama</th>
                <th class="px-3 py-3 text-center">{{ $mengajar ? 'Jumlah Hari Kerja Mengajar' : 'Jumlah Hari Kerja' }}</th>
                @if($mengajar)
                <th class="px-3 py-3 text-center">Jumlah Jam Mengajar / Minggu</th>
                @endif
                <th class="px-3 py-3 text-center">{{ $mengajar ? 'Jumlah Kehadiran Mengajar' : 'Jumlah Kehadiran' }}</th>
                <th class="px-3 py-3 text-center">Jumlah Tidak Hadir</th>
                <th class="px-3 py-3 text-center">Tanpa Keterangan</th>
                <th class="px-3 py-3 text-center">Sakit</th>
                <th class="px-3 py-3 text-center">Ijin</th>
                <th class="px-3 py-3 text-center">Dinas Luar</th>
                <th class="px-3 py-3 text-center">{{ $mengajar ? 'Persentase Kehadiran Mengajar' : 'Persentase Kehadiran' }}</th>
                <th class="px-3 py-3 text-center">{{ $mengajar ? 'Persentase Tidak Hadir Mengajar' : 'Persentase Tidak Hadir' }}</th>
                @if($detailUrl)
                <th class="px-3 py-3 text-center">Rincian</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($rows as $r)
            <tr class="hover:bg-slate-50/60 transition">
                <td class="px-3 py-3 text-center text-slate-500">{{ $r['no'] }}</td>
                <td class="px-3 py-3">
                    <div class="font-bold text-slate-900">{{ $r['nama'] }}</div>
                    <div class="text-[10px] text-slate-400">{{ $r['jenis'] }}@if($r['nip']) &bull; {{ $r['nip'] }}@endif</div>
                </td>
                <td class="px-3 py-3 text-center font-semibold">{{ $r['hari_kerja'] }}</td>
                @if($mengajar)
                <td class="px-3 py-3 text-center font-semibold">{{ $r['jam_per_minggu'] }}</td>
                @endif
                <td class="px-3 py-3 text-center font-bold text-emerald-700">{{ $r['hadir'] }}</td>
                <td class="px-3 py-3 text-center font-bold text-rose-700">{{ $r['tidak_hadir'] }}</td>
                <td class="px-3 py-3 text-center">{{ $r['tanpa_keterangan'] }}</td>
                <td class="px-3 py-3 text-center">{{ $r['sakit'] }}</td>
                <td class="px-3 py-3 text-center">{{ $r['izin'] }}</td>
                <td class="px-3 py-3 text-center">{{ $r['dinas_luar'] }}</td>
                <td class="px-3 py-3 text-center">
                    <span class="px-2 py-0.5 rounded-lg border font-bold {{ $badgePct($r['persen_hadir']) }}">{{ $r['persen_hadir'] }}%</span>
                </td>
                <td class="px-3 py-3 text-center">{{ $r['persen_tidak'] }}%</td>
                @if($detailUrl)
                <td class="px-3 py-3 text-center whitespace-nowrap">
                    <a href="{{ $detailUrl($r['id']) }}" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg font-bold text-slate-700" title="Rincian per hari">
                        <i class="bi bi-list-ul"></i>
                    </a>
                    @if($logUrl)
                    <a href="{{ $logUrl($r['id']) }}" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 rounded-lg font-bold text-emerald-700" title="Log presensi per sesi mengajar">
                        <i class="bi bi-clock-history"></i>
                    </a>
                    @endif
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ $colspan }}" class="px-6 py-12 text-center text-slate-400">
                    <i class="bi bi-calendar-x text-4xl mb-2 block text-slate-300"></i>
                    Belum ada data untuk periode yang dipilih.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($rows->count() > 1)
        <tfoot class="bg-slate-50 font-bold text-slate-800 text-xs border-t-2 border-slate-200">
            <tr>
                <td colspan="2" class="px-3 py-3 text-right">TOTAL / RATA-RATA</td>
                <td class="px-3 py-3 text-center">{{ $t['hari_kerja'] }}</td>
                @if($mengajar)
                <td class="px-3 py-3 text-center">{{ $t['jam_per_minggu'] }}</td>
                @endif
                <td class="px-3 py-3 text-center text-emerald-700">{{ $t['hadir'] }}</td>
                <td class="px-3 py-3 text-center text-rose-700">{{ $t['tidak_hadir'] }}</td>
                <td class="px-3 py-3 text-center">{{ $t['tanpa_keterangan'] }}</td>
                <td class="px-3 py-3 text-center">{{ $t['sakit'] }}</td>
                <td class="px-3 py-3 text-center">{{ $t['izin'] }}</td>
                <td class="px-3 py-3 text-center">{{ $t['dinas_luar'] }}</td>
                <td class="px-3 py-3 text-center">{{ $t['persen_hadir'] }}%</td>
                <td class="px-3 py-3 text-center">{{ $t['persen_tidak'] }}%</td>
                @if($detailUrl)<td></td>@endif
            </tr>
        </tfoot>
        @endif
    </table>
</div>
