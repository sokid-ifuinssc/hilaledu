<tr class="text-center">
    <td class="border border-slate-900 py-0.5 bg-cyan-100 font-black text-[8px]">{{ $jamKe }}</td>
    <td class="border border-slate-900 py-0.5 font-mono text-[7.5px] px-0.5 bg-slate-50">{{ $waktu }}</td>

    @php 
        $numCols = !empty($slots) ? count($slots) : (isset($kelasList) ? count($kelasList) : 9); 
    @endphp
    @for($c = 0; $c < $numCols; $c++)
    @php
        $slot = $slots[$c] ?? null;
        $isTerisi = $slot['is_terisi'] ?? false;
        $kode = $slot['kode'] ?? '-';
        $mapel = $slot['mapel'] ?? '-';
        $bg = $slot['bg'] ?? 'bg-white text-slate-400';
        $isBentrok = ($slot['is_bentrok_guru'] ?? false) || ($slot['is_bentrok_kelas'] ?? false);
        $isOver = $slot['is_over_alokasi'] ?? false;
        $isGabungan = $slot['is_gabungan'] ?? false;
        $isLocked = $slot['is_locked'] ?? false;
        $slotKelas = $slot['kelas'] ?? ($kelasList[$c]['nama'] ?? ($kelasList[$c]['label'] ?? ''));
        $slotHari = $slot['hari'] ?? ($hari ?? '');

        if ($isBentrok) {
            $bg = 'bg-rose-600 text-white font-black animate-pulse';
        } elseif ($isOver) {
            $bg = 'bg-amber-600 text-white font-black';
        } elseif ($isGabungan) {
            $bg = 'bg-teal-700 text-white font-black';
        }
    @endphp
    @if($isTerisi)
    <td class="border border-slate-900 py-0.5 px-0.5 {{ $bg }} leading-tight cursor-pointer hover:opacity-90 hover:ring-1 hover:ring-blue-400 transition relative group p-0"
        title="Jam ke-{{ $jamKe }} | {{ $slot['guru_nama'] ?? '' }} (Kode: {{ $kode }}) - {{ $slot['mapel_nama'] ?? '' }}{{ $isGabungan ? ' [KULIAH GABUNGAN KEPESANTRENAN]' : '' }}{{ $isBentrok ? ' [BENTROK JADWAL!]' : '' }}{{ $isOver ? ' [LEBIH ALOKASI JAM!]' : '' }} &#10;👉 Klik untuk edit manual jadwal ini">
        <a href="{{ !empty($slot['jadwal_id']) ? route('admin.jadwal.index', ['edit' => $slot['jadwal_id']]) : '#' }}" class="block w-full h-full p-0.5">
            @if($isBentrok)
            <span class="absolute -top-0.5 -right-0.5 text-[7px] leading-none bg-yellow-300 text-slate-950 font-black px-0.5 rounded-bl">⚠️</span>
            @elseif($isOver)
            <span class="absolute -top-0.5 -right-0.5 text-[6.5px] leading-none bg-amber-200 text-amber-950 font-black px-0.5 rounded-bl" title="Melebihi alokasi kurikulum">+{{ $slot['over_alokasi_diff'] ?? 0 }}</span>
            @elseif($isGabungan)
            <span class="absolute -top-0.5 -right-0.5 text-[6.5px] leading-none bg-teal-300 text-teal-950 font-black px-0.5 rounded-bl" title="Kuliah Umum Gabungan">🕌</span>
            @endif
            @if($isLocked)
            <span class="absolute top-0.5 left-0.5 text-[7px] text-slate-700 bg-white/70 rounded px-0.5" title="Jadwal Dikunci"><i class="bi-lock-fill"></i></span>
            @endif
            <div class="font-black text-[9px] flex items-center justify-center gap-0.5">
                <span>{{ $kode }}</span>
            </div>
            <div class="font-extrabold text-[7.5px] tracking-tight uppercase truncate max-w-[45px] mx-auto">{{ $mapel }}</div>
        </a>
    </td>
    @else
    <td class="border border-slate-900 py-0.5 px-0.5 bg-slate-50/80 text-slate-400 hover:bg-amber-100 hover:text-amber-900 transition leading-tight">
        <a href="{{ route('admin.jadwal.index', ['hari' => $slotHari, 'kelas' => $slotKelas, 'jam_ke_mulai' => $jamKe, 'jam_ke_selesai' => $jamKe, 'tambah' => 1]) }}"
           class="block w-full h-full py-0.5 font-mono font-bold text-[9px]"
           title="Belum terinput. Klik untuk tambah jadwal Jam ke-{{ $jamKe }} {{ $slotKelas }}">
            -
        </a>
    </td>
    @endif
    @endfor
</tr>
