@extends('layouts.app')

@section('title', 'Pengaturan Jadwal Per Hari')

@section('content')
<div class="space-y-5">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-3xl border border-slate-200 shadow-xs">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-200 mb-2">
                <i class="bi-calendar3-week-fill text-indigo-600"></i>
                <span>Pengaturan Jadwal Manual</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Atur Jadwal Per Hari</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pilih kelas & hari, kemudian filter guru untuk melihat mapel yang diampu.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ request()->routeIs('admin.*') ? route('admin.jadwal.matrix') : route('akademik.jadwal.matrix') }}"
               class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition inline-flex items-center gap-2 border border-slate-200">
                <i class="bi-grid-3x3-gap-fill"></i> Lihat Matriks
            </a>
            <a href="{{ request()->routeIs('admin.*') ? route('admin.jadwal.index') : route('akademik.jadwal.index') }}"
               class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition inline-flex items-center gap-2 border border-slate-200">
                <i class="bi-table"></i> Tabel Jadwal
            </a>
        </div>
    </div>

    {{-- FILTER KELAS + HARI + GURU --}}
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-4">

        {{-- Baris 1: Kelas & Hari --}}
        <div class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Kelas</label>
                <select id="select-kelas" name="kelas" onchange="navigasiFilter()"
                        class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    @foreach($daftarKelas as $k)
                        <option value="{{ $k }}" {{ $kelasDipilih === $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Hari</label>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($daftarHari as $h)
                        <button type="button" onclick="pilihHari('{{ $h }}')"
                                class="px-3 py-1.5 rounded-xl text-xs font-black transition border
                                    {{ $hariDipilih === $h
                                        ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-600/20'
                                        : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                            {{ $h }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Baris 2: Filter Guru --}}
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">
                <i class="bi-person-badge-fill text-indigo-500 mr-1"></i>
                Filter Guru Pengajar
                <span class="ml-1 text-slate-400 font-normal">(klik guru untuk lihat mapel yang diampu)</span>
            </label>
            <div class="flex flex-wrap gap-2 items-center">
                <button type="button" onclick="filterGuru(null)"
                        class="px-3 py-2 rounded-xl text-xs font-bold transition border
                            {{ !$guruDipilih ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-indigo-50 hover:border-indigo-300' }}">
                    <i class="bi-people-fill mr-1"></i>Semua Guru
                </button>
                @foreach($guruList as $guru)
                    <button type="button" onclick="filterGuru({{ $guru->id }})"
                            class="px-3 py-2 rounded-xl text-xs font-bold transition border
                                {{ (string)$guruDipilih === (string)$guru->id
                                    ? 'bg-indigo-600 text-white border-indigo-600'
                                    : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-indigo-50 hover:border-indigo-300' }}">
                        <i class="bi-person-fill mr-1"></i>{{ $guru->name }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-sm font-semibold">
            <i class="bi-check-circle-fill text-emerald-500 text-base"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-sm font-semibold">
            <i class="bi-exclamation-circle-fill text-rose-500 text-base"></i> {{ session('error') }}
        </div>
    @endif

    {{-- INFO GURU AKTIF --}}
    @if($guruDipilih)
        @php $guruAktifObj = $guruList->firstWhere('id', $guruDipilih); @endphp
        @if($guruAktifObj)
        <div class="flex items-center gap-3 px-4 py-3 bg-indigo-50 border border-indigo-200 rounded-2xl">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center shrink-0">
                <i class="bi-person-fill text-white"></i>
            </div>
            <div>
                <div class="font-black text-indigo-800 text-sm">{{ $guruAktifObj->name }}</div>
                <div class="text-xs text-indigo-600">Mengampu {{ $kurikulumsDitampilkan->count() }} mapel di kelas {{ $kelasDipilih }}</div>
            </div>
            <div class="ml-auto flex flex-wrap gap-1.5">
                @foreach($kurikulumsDitampilkan as $kur)
                    @php
                        $sdh = $jamTerjadwal[$kur->mata_pelajaran_id] ?? 0;
                        $sl  = $sdh >= $kur->alokasi_jam;
                    @endphp
                    <span class="px-2 py-1 text-[11px] font-bold rounded-xl border
                        {{ $sl ? 'bg-emerald-100 text-emerald-700 border-emerald-300' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                        {{ $kur->mataPelajaran->kode ?? '-' }}
                        <span class="text-[10px]">{{ $sdh }}/{{ $kur->alokasi_jam }}jp</span>
                        @if($sl)<i class="bi-check-circle-fill ml-0.5"></i>@endif
                    </span>
                @endforeach
            </div>
        </div>
        @endif
    @endif

    {{-- DAFTAR JAM --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 bg-slate-50">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center shadow-sm">
                <i class="bi-clock-fill text-white text-sm"></i>
            </div>
            <div>
                <div class="text-sm font-black text-slate-800">{{ $hariDipilih }} &mdash; Kelas {{ $kelasDipilih }}</div>
                <div class="text-xs text-slate-500">{{ count($periods) }} slot jam &middot; TA {{ $tahunAjaran }} / Smt {{ $semester }}</div>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <span class="px-2 py-0.5 text-xs rounded-lg bg-emerald-100 text-emerald-700 font-bold">{{ $existingJadwals->count() }} terisi</span>
                <span class="px-2 py-0.5 text-xs rounded-lg bg-slate-100 text-slate-500 font-bold">{{ count($periods) - $existingJadwals->count() }} kosong</span>
            </div>
        </div>

        <div class="divide-y divide-slate-100">
            @foreach($periods as $jamKe => $period)
                @php
                    $jadwal  = $existingJadwals->get($jamKe);
                    $isTerisi= $jadwal !== null;
                    $covered = false;
                    foreach($existingJadwals as $jx) {
                        if ($jx->jam_ke_mulai < $jamKe && $jx->jam_ke_selesai >= $jamKe) { $covered = true; break; }
                    }
                    $summaryAlokasi = [];
                    foreach($kurikulumsDitampilkan as $k) {
                        $sdh = $jamTerjadwal[$k->mata_pelajaran_id] ?? 0;
                        $summaryAlokasi[$k->id] = [
                            'sudah'   => $sdh,
                            'total'   => $k->alokasi_jam,
                            'sisa'    => max(0, $k->alokasi_jam - $sdh),
                            'selesai' => $sdh >= $k->alokasi_jam,
                        ];
                    }
                @endphp

                <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-4
                            {{ $isTerisi ? 'bg-emerald-50/40' : ($covered ? 'bg-amber-50/30' : '') }}">

                    {{-- Nomor & Waktu --}}
                    <div class="flex items-center gap-3 sm:w-48 shrink-0">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-sm shrink-0
                                    {{ $isTerisi ? 'bg-emerald-600 text-white' : ($covered ? 'bg-amber-400 text-slate-900' : 'bg-slate-200 text-slate-600') }}">
                            {{ $jamKe }}
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-700">Jam ke-{{ $jamKe }}</div>
                            <div class="text-[11px] text-slate-500">{{ $period['label'] }}</div>
                        </div>
                    </div>

                    @if($covered && !$isTerisi)
                        <div class="flex-1 px-4 py-2.5 rounded-2xl bg-amber-100 border border-amber-200 text-amber-700 text-xs font-semibold flex items-center gap-2">
                            <i class="bi-arrow-bar-up text-amber-500"></i> Masuk dalam sesi jam sebelumnya
                        </div>

                    @elseif($isTerisi)
                        <div class="flex-1 flex items-center gap-3 flex-wrap">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-3 py-1 rounded-xl bg-indigo-600 text-white text-xs font-black shadow-sm">{{ $jadwal->mataPelajaran->kode ?? '-' }}</span>
                                    <span class="text-sm font-bold text-slate-800 truncate">{{ $jadwal->mataPelajaran->nama ?? 'Mapel' }}</span>
                                    @if($jadwal->guru)
                                        <span class="text-xs text-slate-500 flex items-center gap-1"><i class="bi-person-fill"></i> {{ $jadwal->guru->name }}</span>
                                    @endif
                                    <span class="px-2 py-0.5 text-[10px] rounded-lg {{ $jadwal->is_locked ? 'bg-amber-100 text-amber-700 border border-amber-300' : 'bg-slate-100 text-slate-500' }} font-bold">
                                        {{ $jadwal->is_locked ? '🔒 Dikunci' : 'Draft' }}
                                    </span>
                                    <span class="text-xs text-slate-400">Jam {{ $jadwal->jam_ke_mulai }}-{{ $jadwal->jam_ke_selesai }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button"
                                        onclick="bukaModalEdit({{ $jadwal->id }}, {{ $jadwal->mata_pelajaran_id }}, '{{ $jadwal->jam_ke_mulai }}')"
                                        class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5">
                                    <i class="bi-pencil-fill"></i> Ganti
                                </button>
                                <form action="{{ request()->routeIs('admin.*') ? route('admin.jadwal.atur.hapus', $jadwal->id) : route('akademik.jadwal.atur.hapus', $jadwal->id) }}"
                                      method="POST" onsubmit="return confirm('Hapus jadwal ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5">
                                        <i class="bi-trash-fill"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>

                    @else
                        <div class="flex-1">
                            <form action="{{ request()->routeIs('admin.*') ? route('admin.jadwal.atur.simpan') : route('akademik.jadwal.atur.simpan') }}"
                                  method="POST" class="flex items-center gap-2 flex-wrap">
                                @csrf
                                <input type="hidden" name="hari"   value="{{ $hariDipilih }}">
                                <input type="hidden" name="kelas"  value="{{ $kelasDipilih }}">
                                <input type="hidden" name="jam_ke" value="{{ $jamKe }}">

                                <div class="flex-1 min-w-[240px]">
                                    <select name="kurikulum_id" required
                                            class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                        <option value="">
                                            @if($guruDipilih && $kurikulumsDitampilkan->isEmpty())
                                                &mdash; Guru ini tidak mengampu di kelas ini &mdash;
                                            @else
                                                &mdash; Pilih Mapel{{ $guruDipilih ? ' Guru Ini' : '' }} &mdash;
                                            @endif
                                        </option>
                                        @foreach($kurikulumsDitampilkan as $kur)
                                            @php
                                                $inf = $summaryAlokasi[$kur->id] ?? null;
                                                $sl  = $inf['selesai'] ?? false;
                                                $si  = $inf['sisa']    ?? 0;
                                                $sd  = $inf['sudah']   ?? 0;
                                                $tt  = $inf['total']   ?? $kur->alokasi_jam;
                                                $gNm = $kur->guruUser->name ?? null;
                                            @endphp
                                            <option value="{{ $kur->id }}" {{ $sl ? 'disabled' : '' }}>
                                                @if($sl) {{ chr(10004) }} Sudah terjadwal penuh
                                                @elseif($sd > 0) ~ Sebagian terjadwal
                                                @else + Belum terjadwal
                                                @endif
                                                &mdash; {{ $kur->mataPelajaran->nama ?? '-' }}
                                                ({{ $kur->mataPelajaran->kode ?? '' }})
                                                @if($gNm && !$guruDipilih) &mdash; {{ $gNm }} @endif
                                                @if($sl) &mdash; ({{ $sd }}/{{ $tt }} jp PENUH)
                                                @else &mdash; sisa {{ $si }}/{{ $tt }} jp
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 cursor-pointer whitespace-nowrap">
                                    <input type="checkbox" name="is_locked" value="1" class="rounded border-slate-300"> Kunci
                                </label>

                                <button type="submit"
                                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition shadow-sm inline-flex items-center gap-1.5">
                                    <i class="bi-plus-lg"></i> Simpan
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- RINGKASAN ALOKASI SEMINGGU --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <div class="text-xs font-black text-slate-700 flex items-center gap-2">
                <i class="bi-journal-text text-indigo-500"></i>
                Ringkasan Alokasi Mapel &mdash; Kelas {{ $kelasDipilih }} (Semua Hari)
            </div>
            @if($guruDipilih)
                <span class="text-xs text-indigo-600 font-bold">
                    <i class="bi-funnel-fill mr-1"></i>{{ $guruList->firstWhere('id', $guruDipilih)?->name ?? '-' }}
                </span>
            @endif
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                @foreach($kurikulumsDitampilkan as $kur)
                    @php
                        $sdh = $jamTerjadwal[$kur->mata_pelajaran_id] ?? 0;
                        $pct = $kur->alokasi_jam > 0 ? min(100, round($sdh / $kur->alokasi_jam * 100)) : 0;
                        $sl  = $sdh >= $kur->alokasi_jam;
                        $lb  = $sdh > $kur->alokasi_jam;
                        $gNm = $kur->guruUser->name ?? null;
                    @endphp
                    <div class="flex flex-col gap-1.5 p-3 rounded-2xl border
                                {{ $lb ? 'border-rose-300 bg-rose-50' : ($sl ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200 bg-slate-50') }}">
                        <div class="flex items-center justify-between gap-2">
                            <div class="text-xs font-bold text-slate-700 truncate flex-1">{{ $kur->mataPelajaran->nama ?? '-' }}</div>
                            <span class="text-[10px] font-black px-2 py-0.5 rounded-lg
                                {{ $lb ? 'bg-rose-200 text-rose-700' : ($sl ? 'bg-emerald-200 text-emerald-700' : 'bg-amber-100 text-amber-700') }}">
                                {{ $sdh }}/{{ $kur->alokasi_jam }} jp
                            </span>
                        </div>
                        @if($gNm)
                            <div class="text-[10px] text-slate-500 flex items-center gap-1">
                                <i class="bi-person-fill text-indigo-400"></i>{{ $gNm }}
                            </div>
                        @endif
                        <div class="flex items-center gap-2">
                            <div class="text-[10px] text-slate-400">{{ $kur->mataPelajaran->kode ?? '' }}</div>
                            <div class="flex-1 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $lb ? 'bg-rose-500' : ($sl ? 'bg-emerald-500' : 'bg-amber-400') }}"
                                     style="width:{{ $pct }}%"></div>
                            </div>
                            <div class="text-[10px] font-bold text-slate-500">{{ $pct }}%</div>
                        </div>
                        @if($sl)
                            <div class="text-[10px] font-bold text-emerald-600 flex items-center gap-1">
                                <i class="bi-check-circle-fill"></i> Sudah terjadwal penuh
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div id="modal-edit" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center">
                <i class="bi-pencil-fill text-white"></i>
            </div>
            <div>
                <div class="text-base font-black text-slate-800">Ganti Mapel</div>
                <div class="text-xs text-slate-500">Jam ke-<span id="modal-jam-label">?</span></div>
            </div>
            <button onclick="tutupModal()" class="ml-auto text-slate-400 hover:text-slate-700 p-1">
                <i class="bi-x-lg text-lg"></i>
            </button>
        </div>

        {{-- Filter Guru di Modal --}}
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Filter Guru (opsional)</label>
            <select id="modal-filter-guru" onchange="filterMapelModal()"
                    class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">-- Semua Guru --</option>
                @foreach($guruList as $guru)
                    <option value="{{ $guru->id }}">{{ $guru->name }}</option>
                @endforeach
            </select>
        </div>

        <form id="form-edit-jadwal"
              action="{{ request()->routeIs('admin.*') ? route('admin.jadwal.atur.simpan') : route('akademik.jadwal.atur.simpan') }}"
              method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="hari"       value="{{ $hariDipilih }}">
            <input type="hidden" name="kelas"      value="{{ $kelasDipilih }}">
            <input type="hidden" name="jam_ke"     id="modal-jam-ke"    value="">
            <input type="hidden" name="jadwal_id"  id="modal-jadwal-id" value="">

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Pilih Mapel Baru</label>
                <select name="kurikulum_id" id="modal-kurikulum" required
                        class="w-full px-3 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Pilih Mapel --</option>
                    @foreach($kurikulums as $kur)
                        @php
                            $sd  = $jamTerjadwal[$kur->mata_pelajaran_id] ?? 0;
                            $sl  = $sd >= $kur->alokasi_jam;
                            $gId = $kur->guru_user_id ?: ($kur->mataPelajaran->guru_user_id ?? null);
                            $gNm = $kur->guruUser->name ?? null;
                        @endphp
                        <option value="{{ $kur->id }}"
                                data-mapel-id="{{ $kur->mata_pelajaran_id }}"
                                data-guru-id="{{ $gId }}">
                            @if($sl) [PENUH] @endif
                            {{ $kur->mataPelajaran->nama ?? '-' }}
                            ({{ $kur->mataPelajaran->kode ?? '' }})
                            @if($gNm) &mdash; {{ $gNm }} @endif
                            &mdash; {{ $sd }}/{{ $kur->alokasi_jam }} jp
                        </option>
                    @endforeach
                </select>
            </div>

            <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                <input type="checkbox" name="is_locked" value="1" id="modal-is-locked" class="rounded">
                Kunci jadwal ini (tidak akan diubah saat acak otomatis)
            </label>

            <div class="flex gap-2 pt-1">
                <button type="button" onclick="tutupModal()"
                        class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-sm transition">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm transition shadow-md shadow-indigo-600/20">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const baseUrl  = '{{ request()->routeIs("admin.*") ? route("admin.jadwal.atur") : route("akademik.jadwal.atur") }}';
let hariAktif  = '{{ $hariDipilih }}';
let kelasAktif = '{{ $kelasDipilih }}';
let guruAktif  = '{{ $guruDipilih ?? "" }}';

function navigasiFilter(newGuru) {
    kelasAktif = document.getElementById('select-kelas').value;
    if (newGuru !== undefined) guruAktif = (newGuru === null ? '' : newGuru);
    let url = baseUrl + '?hari=' + hariAktif + '&kelas=' + encodeURIComponent(kelasAktif);
    if (guruAktif) url += '&guru_id=' + guruAktif;
    window.location.href = url;
}

function pilihHari(hari) { hariAktif = hari; navigasiFilter(); }
function filterGuru(id)   { navigasiFilter(id); }

function bukaModalEdit(jadwalId, mapelId, jamKe) {
    document.getElementById('modal-jadwal-id').value      = jadwalId;
    document.getElementById('modal-jam-ke').value         = jamKe;
    document.getElementById('modal-jam-label').textContent= jamKe;
    const sel = document.getElementById('modal-kurikulum');
    for (const opt of sel.options) {
        if (opt.dataset.mapelId == mapelId) { sel.value = opt.value; break; }
    }
    document.getElementById('modal-edit').classList.remove('hidden');
    document.getElementById('modal-edit').classList.add('flex');
}

function tutupModal() {
    document.getElementById('modal-edit').classList.add('hidden');
    document.getElementById('modal-edit').classList.remove('flex');
}

function filterMapelModal() {
    const guruId = document.getElementById('modal-filter-guru').value;
    document.querySelectorAll('#modal-kurikulum option[data-guru-id]').forEach(opt => {
        opt.hidden = guruId ? (opt.dataset.guruId != guruId) : false;
    });
    document.getElementById('modal-kurikulum').value = '';
}

document.getElementById('modal-edit').addEventListener('click', function(e) {
    if (e.target === this) tutupModal();
});
</script>
@endsection