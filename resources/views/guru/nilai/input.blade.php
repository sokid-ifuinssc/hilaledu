@extends('layouts.app')

@section('title', 'Input Nilai Akhir: ' . $mapel->nama . ' - Kelas ' . $kelas->nama_kelas)

@section('content')
<div class="space-y-6" x-data="{
    updatePredikat(rowId) {
        const val = parseFloat(document.getElementById('akhir_' + rowId).value);
        const predikatEl = document.getElementById('predikat_' + rowId);
        if (isNaN(val) || val === null || val === '') {
            predikatEl.innerText = '-';
            predikatEl.className = 'px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-500';
            return;
        }

        let p = 'D';
        let cls = 'px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-100 text-rose-700 border border-rose-200';
        if (val >= 88) {
            p = 'A';
            cls = 'px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200';
        } else if (val >= 76) {
            p = 'B';
            cls = 'px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200';
        } else if (val >= 65) {
            p = 'C';
            cls = 'px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200';
        }

        predikatEl.innerText = p;
        predikatEl.className = cls;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('guru.nilai.index') }}" class="hover:text-blue-600 transition-colors">Penilaian Mapel</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <span class="text-slate-800 font-medium">{{ $mapel->nama }}</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-200">
                    <i class="bi bi-pencil-square text-lg"></i>
                </span>
                Input Nilai Akhir: {{ $mapel->nama }}
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelas: <b>{{ $kelas->nama_kelas }}</b> ({{ $kelas->jurusan ?: 'Umum' }}) • Tahun Ajaran: <b>{{ $tahunAjaran }} ({{ ucfirst($semester) }})</b>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.nilai.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 transition shadow-sm">
                <i class="bi bi-arrow-left"></i>
                Kembali ke Daftar Mapel
            </a>
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isWaliKelas())
                <a href="{{ route('walikelas.leger', ['kelas_id' => $kelas->id]) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition">
                    <i class="bi bi-journal-bookmark"></i>
                    Lihat Leger Kelas
                </a>
            @endif
        </div>
    </div>

    @php
        $namaLower = strtolower($mapel->nama);
        $isEskulMapel = str_contains($namaLower, 'team work') 
            || str_contains($namaLower, 'project pancasila') 
            || str_contains($namaLower, 'work project')
            || str_starts_with(strtoupper($mapel->kode ?? ''), 'TWP');
    @endphp

    @if($isEskulMapel)
        <!-- Card Integrasi Khusus Eskul (HANYA UNTUK TEAM WORK PROJECT & PROJECT PANCASILA) -->
        <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white rounded-2xl p-5 shadow-lg shadow-emerald-900/10 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-2xl font-bold flex-shrink-0">
                    <i class="bi bi-stars"></i>
                </div>
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-400/20 text-emerald-100 text-[10px] font-bold uppercase tracking-wider mb-1">
                        <i class="bi bi-shield-check"></i> Project Terintegrasi Eskul
                    </div>
                    <h3 class="text-base font-bold">Sinkronisasi Nilai Akhir dari Ekstrakurikuler</h3>
                    <p class="text-xs text-emerald-100 max-w-2xl mt-0.5">
                        Mata pelajaran ini adalah <b>Team Work Project & Project Pancasila</b>. Klik tombol di samping untuk menarik akumulasi nilai & absensi kegiatan ekstrakurikuler siswa menjadi nilai akhir mapel project ini.
                    </p>
                </div>
            </div>
            <div>
                <form action="{{ route('guru.nilai.sync-eskul', [$mapel->id, $kelas->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menarik nilai eskul? Nilai akhir siswa pada mapel project ini akan disesuaikan dengan nilai dari pembina eskul.')">
                    @csrf
                    <input type="hidden" name="mata_pelajaran_id" value="{{ $mapel->id }}">
                    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                    <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">
                    <input type="hidden" name="semester" value="{{ $semester }}">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-white text-emerald-800 hover:bg-emerald-50 transition shadow-sm whitespace-nowrap">
                        <i class="bi bi-arrow-repeat text-sm"></i>
                        Tarik Nilai dari Eskul
                    </button>
                </form>
            </div>
        </div>
    @else
        <!-- Card Panduan Mapel Reguler -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/80 rounded-2xl p-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg font-bold shrink-0 shadow-sm shadow-blue-200">
                    <i class="bi bi-journal-check"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-800">Penilaian Mata Pelajaran Reguler: {{ $mapel->nama }}</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Silakan inputkan nilai akhir siswa (rentang 0 s.d 100) berdasarkan rekapitulasi nilai tugas, UTS/STS, dan UAS/SAS selama semester berjalan. Nilai yang Anda simpan akan langsung tercatat di <b>Leger Nilai Rombel</b>.
                    </p>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-2 text-xs font-semibold text-blue-700 bg-white px-3 py-1.5 rounded-xl border border-blue-200 shadow-2xs shrink-0">
                <i class="bi bi-check2-circle text-emerald-600"></i> Siap Diisi
            </div>
        </div>
    @endif

    <!-- Lembar Nilai Akhir Siswa -->
    <form action="{{ route('guru.nilai.store', [$mapel->id, $kelas->id]) }}" method="POST">
        @csrf
        <input type="hidden" name="mata_pelajaran_id" value="{{ $mapel->id }}">
        <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
        <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">
        <input type="hidden" name="semester" value="{{ $semester }}">

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <i class="bi bi-table text-blue-600"></i>
                    <h3 class="text-sm font-bold text-slate-800">Lembar Input Nilai Akhir ({{ $siswas->count() }} Siswa)</h3>
                    <span class="text-[11px] text-slate-400 font-normal">• Nilai diinput langsung dalam skala 0 s.d 100</span>
                </div>
                <div>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition">
                        <i class="bi bi-save"></i>
                        Simpan Semua Nilai Akhir
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="px-3 py-3 w-12 text-center">No</th>
                            <th class="px-4 py-3 w-64">Nama Siswa & NISN</th>
                            <th class="px-4 py-3 text-center w-36">Nilai Akhir (0-100)</th>
                            <th class="px-3 py-3 text-center w-24">Predikat</th>
                            <th class="px-4 py-3">Catatan Capaian / Deskripsi Kompetensi</th>
                            <th class="px-3 py-3 text-center w-28">Sumber Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($siswas as $idx => $s)
                            @php
                                $sId = $s->user_id ?: $s->id;
                                $val = $nilaiMap[$sId] ?? $nilaiMap[$s->id] ?? null;
                                $pred = $val ? $val->predikat : '-';
                                $badgeClass = 'bg-slate-100 text-slate-500';
                                if ($pred === 'A') $badgeClass = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                elseif ($pred === 'B') $badgeClass = 'bg-blue-100 text-blue-700 border border-blue-200';
                                elseif ($pred === 'C') $badgeClass = 'bg-amber-100 text-amber-700 border border-amber-200';
                                elseif ($pred === 'D') $badgeClass = 'bg-rose-100 text-rose-700 border border-rose-200';
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-3 py-3 text-center text-slate-500 font-medium">{{ $idx + 1 }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-bold text-slate-800 text-xs">{{ $s->nama }}</p>
                                    <span class="text-[11px] text-slate-400">NISN: {{ $s->nisn ?: '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" step="0.1" min="0" max="100" 
                                           id="akhir_{{ $sId }}"
                                           name="nilai[{{ $sId }}][nilai_akhir]" 
                                           value="{{ $val ? $val->nilai_akhir : '' }}" 
                                           placeholder="0 - 100" 
                                           @input="updatePredikat({{ $sId }})"
                                           tabindex="{{ $idx + 1 }}"
                                           class="w-28 text-center font-extrabold text-blue-700 text-sm bg-blue-50/40 px-3 py-1.5 rounded-xl border border-blue-200 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-inner">
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span id="predikat_{{ $sId }}" class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $badgeClass }}">
                                        {{ $pred }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="nilai[{{ $sId }}][catatan]" 
                                           value="{{ $val ? $val->catatan : '' }}" 
                                           placeholder="Capaian kompetensi / catatan siswa..." 
                                           class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                </td>
                                <td class="px-3 py-3 text-center whitespace-nowrap">
                                    @if($val && $val->is_sync_eskul)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200" title="Nilai diisi otomatis dari hasil ekstrakurikuler">
                                            <i class="bi bi-stars mr-0.5"></i>Eskul
                                        </span>
                                    @elseif($val && $val->nilai_akhir !== null)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            Manual
                                        </span>
                                    @else
                                        <span class="text-slate-300 text-[11px]">- Belum Diisi -</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                    Tidak ada data siswa terdaftar pada kelas ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($siswas->count() > 0)
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-xs text-slate-500">
                        <b>Panduan Predikat:</b> A (≥88 Sangat Baik), B (76-87 Baik), C (65-75 Cukup), D (&lt;65 Perlu Bimbingan).
                    </p>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-sm shadow-blue-200 transition">
                        <i class="bi bi-save"></i>
                        Simpan Semua Nilai Akhir
                    </button>
                </div>
            @endif
        </div>
    </form>
</div>
@endsection
