@php
    $isJumat = ($hari === 'Jumat');
    $headerColor = match($hari) {
        'Senin'  => 'bg-blue-700 text-white',
        'Selasa' => 'bg-cyan-600 text-white',
        'Rabu'   => 'bg-blue-600 text-white',
        'Kamis'  => 'bg-blue-700 text-white',
        'Jumat'  => 'bg-emerald-700 text-white',
        'Sabtu'  => 'bg-indigo-700 text-white',
        default  => 'bg-slate-800 text-white',
    };
@endphp

<div class="border border-slate-900 bg-white shadow-xs">
    <!-- NAMA HARI -->
    <div class="{{ $headerColor }} py-1 text-center font-black text-xs tracking-wider uppercase border-b border-slate-900">
        {{ $hari }}{{ $isJumat ? ' *' : '' }}
    </div>

    <!-- TABEL JAM & KELAS -->
    <table class="w-full border-collapse text-[9px] font-sans">
        <thead>
            <!-- BARIS 1: BADGE JURUSAN (AKL, TKJT, TO) -->
            <tr class="bg-slate-100 text-center font-bold">
                <th colspan="2" class="border border-slate-900 py-0.5 text-[8px] bg-slate-200">WAKTU</th>
                @foreach($kelasList as $k)
                <th class="border border-slate-900 py-0.5 {{ $k['badge'] }} text-[7.5px] uppercase tracking-wider">
                    {{ $k['jurusan'] }}
                </th>
                @endforeach
            </tr>
            <!-- BARIS 2: NAMA KELAS DARI DATABASE (X AKL, X TKJT, X TO, dst) -->
            <tr class="text-center font-black text-[8px]">
                <th class="border border-slate-900 py-0.5 px-0.5 w-[20px] bg-cyan-400 text-slate-900">JAM</th>
                <th class="border border-slate-900 py-0.5 px-1 w-[62px] bg-slate-100">WAKTU</th>
                @foreach($kelasList as $k)
                <th class="border border-slate-900 py-0.5 px-0.5 {{ $k['badge'] }} truncate">
                    {{ $k['label'] }}
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <!-- PEMBIASAAN AL-QUR'AN (06.50 - 07.00) -->
            <tr class="text-center font-semibold bg-emerald-50">
                <td colspan="2" class="border border-slate-900 py-0.5 font-mono text-[8px]">06.50 - 07.00</td>
                <td colspan="{{ count($kelasList) }}" class="border border-slate-900 py-0.5 bg-emerald-400 text-slate-900 font-bold text-[8.5px]">
                    10 menit pembiasaan membaca Al-Qur'an
                </td>
            </tr>

            @if(!$isJumat)
                <!-- HARI REGULER (SENIN, SELASA, RABU, KAMIS, SABTU): 8 JAM @ 45 MENIT -->
                <!-- JAM 1 -->
                @include('admin.jadwal.partials.matrix_slot_row', ['jamKe' => 1, 'waktu' => '07.00 - 07.45', 'slots' => $dataHari[1]['slots'] ?? [], 'kelasList' => $kelasList, 'hari' => $hari])
                <!-- JAM 2 -->
                @include('admin.jadwal.partials.matrix_slot_row', ['jamKe' => 2, 'waktu' => '07.45 - 08.30', 'slots' => $dataHari[2]['slots'] ?? [], 'kelasList' => $kelasList, 'hari' => $hari])
                <!-- JAM 3 -->
                @include('admin.jadwal.partials.matrix_slot_row', ['jamKe' => 3, 'waktu' => '08.30 - 09.15', 'slots' => $dataHari[3]['slots'] ?? [], 'kelasList' => $kelasList, 'hari' => $hari])
                <!-- JAM 4 -->
                @include('admin.jadwal.partials.matrix_slot_row', ['jamKe' => 4, 'waktu' => '09.15 - 10.00', 'slots' => $dataHari[4]['slots'] ?? [], 'kelasList' => $kelasList, 'hari' => $hari])

                <!-- ISTIRAHAT 1 (30 MENIT): 10.00 - 10.30 -->
                <tr class="text-center font-bold bg-yellow-200 text-slate-900">
                    <td colspan="2" class="border border-slate-900 py-0.5 font-mono text-[8px]">10.00 - 10.30</td>
                    <td colspan="{{ count($kelasList) }}" class="border border-slate-900 py-0.5 tracking-wider uppercase font-black text-[8px] bg-yellow-200">
                        ISTIRAHAT DAN DUHA (30 MENIT)
                    </td>
                </tr>

                <!-- JAM 5 -->
                @include('admin.jadwal.partials.matrix_slot_row', ['jamKe' => 5, 'waktu' => '10.30 - 11.15', 'slots' => $dataHari[5]['slots'] ?? [], 'kelasList' => $kelasList, 'hari' => $hari])
                <!-- JAM 6 -->
                @include('admin.jadwal.partials.matrix_slot_row', ['jamKe' => 6, 'waktu' => '11.15 - 12.00', 'slots' => $dataHari[6]['slots'] ?? [], 'kelasList' => $kelasList, 'hari' => $hari])

                <!-- ISTIRAHAT 2 (30 MENIT): 12.00 - 12.30 -->
                <tr class="text-center font-bold bg-emerald-200 text-slate-900">
                    <td colspan="2" class="border border-slate-900 py-0.5 font-mono text-[8px]">12.00 - 12.30</td>
                    <td colspan="{{ count($kelasList) }}" class="border border-slate-900 py-0.5 tracking-wider uppercase font-black text-[8px] bg-emerald-200">
                        ISTIRAHAT SHALAT DUHUR BERJAMAAH (30 MENIT)
                    </td>
                </tr>

                <!-- JAM 7 -->
                @include('admin.jadwal.partials.matrix_slot_row', ['jamKe' => 7, 'waktu' => '12.30 - 13.15', 'slots' => $dataHari[7]['slots'] ?? [], 'kelasList' => $kelasList, 'hari' => $hari])
                <!-- JAM 8 -->
                @include('admin.jadwal.partials.matrix_slot_row', ['jamKe' => 8, 'waktu' => '13.15 - 14.00', 'slots' => $dataHari[8]['slots'] ?? [], 'kelasList' => $kelasList, 'hari' => $hari])

            @else
                <!-- KHUSUS JUMAT: 6 JAM @ 30 MENIT -->
                <!-- JAM 1 -->
                @include('admin.jadwal.partials.matrix_slot_row', ['jamKe' => 1, 'waktu' => '07.00 - 07.30', 'slots' => $dataHari[1]['slots'] ?? [], 'kelasList' => $kelasList, 'hari' => $hari])
                <!-- JAM 2 -->
                @include('admin.jadwal.partials.matrix_slot_row', ['jamKe' => 2, 'waktu' => '07.30 - 08.00', 'slots' => $dataHari[2]['slots'] ?? [], 'kelasList' => $kelasList, 'hari' => $hari])
                <!-- JAM 3 -->
                @include('admin.jadwal.partials.matrix_slot_row', ['jamKe' => 3, 'waktu' => '08.00 - 08.30', 'slots' => $dataHari[3]['slots'] ?? [], 'kelasList' => $kelasList, 'hari' => $hari])
                <!-- JAM 4 -->
                @include('admin.jadwal.partials.matrix_slot_row', ['jamKe' => 4, 'waktu' => '08.30 - 09.00', 'slots' => $dataHari[4]['slots'] ?? [], 'kelasList' => $kelasList, 'hari' => $hari])

                <!-- ISTIRAHAT JUMAT (30 MENIT): 09.00 - 09.30 -->
                <tr class="text-center font-bold bg-yellow-200 text-slate-900">
                    <td colspan="2" class="border border-slate-900 py-0.5 font-mono text-[8px]">09.00 - 09.30</td>
                    <td colspan="{{ count($kelasList) }}" class="border border-slate-900 py-0.5 tracking-wider uppercase font-black text-[8px] bg-yellow-200">
                        ISTIRAHAT DAN DUHA (30 MENIT)
                    </td>
                </tr>

                <!-- JAM 5 -->
                @include('admin.jadwal.partials.matrix_slot_row', ['jamKe' => 5, 'waktu' => '09.30 - 10.00', 'slots' => $dataHari[5]['slots'] ?? [], 'kelasList' => $kelasList, 'hari' => $hari])
                <!-- JAM 6 -->
                @include('admin.jadwal.partials.matrix_slot_row', ['jamKe' => 6, 'waktu' => '10.00 - 10.30', 'slots' => $dataHari[6]['slots'] ?? [], 'kelasList' => $kelasList, 'hari' => $hari])
            @endif

            <!-- PULANG -->
            <tr class="text-center font-black bg-slate-800 text-white tracking-widest text-[8.5px]">
                <td colspan="2" class="border border-slate-900 py-0.5 font-mono text-[8px] bg-slate-900 text-amber-300">
                    {{ $isJumat ? '10.30' : '14.00' }}
                </td>
                <td colspan="{{ count($kelasList) }}" class="border border-slate-900 py-0.5">
                    {{ $isJumat ? "PULANG (PERSIAPAN SHALAT JUM'AT)" : 'PULANG' }}
                </td>
            </tr>
        </tbody>
    </table>
</div>
