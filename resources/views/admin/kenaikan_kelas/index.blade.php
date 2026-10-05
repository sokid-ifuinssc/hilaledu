@extends('layouts.app')

@section('page-title', 'Kenaikan Kelas & Kelulusan Alumni')

@section('content')
<div class="space-y-6">

    {{-- ============================================================ --}}
    {{-- 1. HERO HEADER & TAHUN AJARAN AKTIF                          --}}
    {{-- ============================================================ --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-800 via-teal-800 to-slate-900 p-6 md:p-8 text-white shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 backdrop-blur-md mb-3">
                    <i class="bi bi-arrow-repeat animate-spin-slow"></i>
                    Siklus Akademik Berkelanjutan
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white mb-2">
                    Kenaikan Kelas, Kelulusan & Tahun Ajaran
                </h1>
                <p class="text-emerald-100/90 text-sm max-w-2xl leading-relaxed">
                    Kelola promosi kenaikan rombel siswa (Tingkat X & XI), otomatisasi kelulusan siswa kelas XII menjadi Alumni Tracer Study, serta pergantian Tahun Ajaran baru yang berkesinambungan.
                </p>
            </div>

            <div class="bg-white/10 border border-white/20 backdrop-blur-md rounded-xl p-4 text-center md:text-right min-w-[220px]">
                <div class="text-xs uppercase tracking-wider text-emerald-200 font-semibold mb-1">Tahun Ajaran Aktif</div>
                <div class="text-2xl font-black text-white flex items-center justify-center md:justify-end gap-2">
                    <i class="bi bi-calendar-check text-emerald-400"></i>
                    {{ $tahunAjaranAktif->nama ?? '2026/2027' }}
                </div>
                <div class="text-xs font-medium text-emerald-200/80 mt-1">
                    Semester <span class="uppercase font-bold text-white">{{ $semesterAktif }}</span> &bull; 
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] bg-emerald-400 text-emerald-950 font-bold">Aktif</span>
                </div>
            </div>
        </div>

        {{-- Background Geometric Decoration --}}
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-64 h-64 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    {{-- ============================================================ --}}
    {{-- 2. KARTU STATISTIK KELAS & ALUMNI                            --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Card 1: Tingkat X -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tingkat X</span>
                <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                    <i class="bi bi-1-circle-fill"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-800">{{ $countSiswaX }} <span class="text-xs font-normal text-slate-500">Siswa</span></div>
            <div class="text-xs text-blue-600 font-medium mt-1 flex items-center gap-1">
                <i class="bi bi-arrow-up-right"></i> Siap promosi ke Tingkat XI
            </div>
        </div>

        <!-- Card 2: Tingkat XI -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tingkat XI</span>
                <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                    <i class="bi bi-2-circle-fill"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-800">{{ $countSiswaXI }} <span class="text-xs font-normal text-slate-500">Siswa</span></div>
            <div class="text-xs text-indigo-600 font-medium mt-1 flex items-center gap-1">
                <i class="bi bi-arrow-up-right"></i> Siap promosi ke Tingkat XII
            </div>
        </div>

        <!-- Card 3: Tingkat XII -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tingkat XII</span>
                <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="bi bi-3-circle-fill"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-800">{{ $countSiswaXII }} <span class="text-xs font-normal text-slate-500">Siswa</span></div>
            <div class="text-xs text-amber-600 font-medium mt-1 flex items-center gap-1">
                <i class="bi bi-mortarboard-fill"></i> Calon Lulusan / Alumni
            </div>
        </div>

        <!-- Card 4: Total Alumni -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Alumni</span>
                <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="bi bi-award-fill"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-800">{{ $countAlumni }} <span class="text-xs font-normal text-slate-500">Alumni</span></div>
            <div class="text-xs text-emerald-600 font-medium mt-1 flex items-center gap-1">
                <i class="bi bi-check2-all"></i> Terdaftar di Tracer Study
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 3. TAB NAVIGASI WIZARD                                       --}}
    {{-- ============================================================ --}}
    @php
        $activeTab = request()->get('tab', 'promosi');
    @endphp

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/70 p-2 sm:p-3 flex flex-wrap gap-2">
            <button type="button" 
                    onclick="switchTab('promosi')"
                    id="btn-tab-promosi"
                    class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm transition-all {{ $activeTab === 'promosi' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-200/70' }}">
                <i class="bi bi-box-arrow-in-up-right"></i>
                <span>1. Kenaikan Kelas (X & XI)</span>
            </button>

            <button type="button" 
                    onclick="switchTab('kelulusan')"
                    id="btn-tab-kelulusan"
                    class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm transition-all {{ $activeTab === 'kelulusan' ? 'bg-amber-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-200/70' }}">
                <i class="bi bi-mortarboard"></i>
                <span>2. Kelulusan Siswa & Alumni (XII)</span>
            </button>

            <button type="button" 
                    onclick="switchTab('tahun_ajaran')"
                    id="btn-tab-tahun_ajaran"
                    class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm transition-all {{ $activeTab === 'tahun_ajaran' ? 'bg-teal-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-200/70' }}">
                <i class="bi bi-calendar2-range"></i>
                <span>3. Tahun Ajaran Berkelanjutan</span>
            </button>

            <button type="button" 
                    onclick="switchTab('alumni')"
                    id="btn-tab-alumni"
                    class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm transition-all {{ $activeTab === 'alumni' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-200/70' }}">
                <i class="bi bi-people-fill"></i>
                <span>4. Direktori Alumni Terdaftar</span>
            </button>
        </div>

        <div class="p-5 md:p-8">

            {{-- ======================================================== --}}
            {{-- TAB 1: KENAIKAN KELAS (TINGKAT X KE XI, XI KE XII)       --}}
            {{-- ======================================================== --}}
            <div id="tab-content-promosi" class="tab-pane {{ $activeTab === 'promosi' ? '' : 'hidden' }}">
                <div class="max-w-4xl mx-auto mb-8 bg-blue-50/70 border border-blue-200/80 rounded-2xl p-5 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 text-xl shadow-sm">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <div class="text-sm text-slate-700 leading-relaxed">
                        <h4 class="font-bold text-blue-900 mb-1">Skema Kenaikan Kelas (Promosi Rombel)</h4>
                        Pilih rombel kelas asal (Tingkat X atau XI). Sistem secara cerdas akan menyarankan rombel kelas tujuan tingkat berikutnya sesuai jurusan. Anda dapat menetapkan siswa yang <strong>Naik Kelas</strong> atau <strong>Tinggal Kelas</strong>.
                    </div>
                </div>

                <!-- Form Filter Rombel Asal -->
                <form action="{{ route('superadmin.kenaikan-kelas.index') }}" method="GET" class="max-w-4xl mx-auto mb-8 bg-slate-50 border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <input type="hidden" name="tab" value="promosi">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-8">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                <i class="bi bi-collection text-emerald-600 mr-1"></i> Pilih Kelas Asal yang Akan Dinaikkan:
                            </label>
                            <select name="kelas_asal_id" class="form-select w-full font-medium" onchange="this.form.submit()">
                                <option value="">-- Pilih Kelas Asal (Tingkat X / XI) --</option>
                                <optgroup label="Tingkat X (Ke Tingkat XI)">
                                    @foreach($kelasX as $kx)
                                        <option value="{{ $kx->id }}" {{ $selectedKelasAsalId == $kx->id ? 'selected' : '' }}>
                                            {{ $kx->nama_lengkap }} ({{ $kx->siswas()->count() }} Siswa)
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Tingkat XI (Ke Tingkat XII)">
                                    @foreach($kelasXI as $kxi)
                                        <option value="{{ $kxi->id }}" {{ $selectedKelasAsalId == $kxi->id ? 'selected' : '' }}>
                                            {{ $kxi->nama_lengkap }} ({{ $kxi->siswas()->count() }} Siswa)
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <div class="md:col-span-4 flex gap-2">
                            <button type="submit" class="btn-primary w-full justify-center">
                                <i class="bi bi-search mr-1"></i> Tampilkan Siswa
                            </button>
                            @if($selectedKelasAsalId)
                                <a href="{{ route('superadmin.kenaikan-kelas.index', ['tab' => 'promosi']) }}" class="btn-secondary">Reset</a>
                            @endif
                        </div>
                    </div>
                </form>

                @if($kelasAsal)
                    <form action="{{ route('superadmin.kenaikan-kelas.naik') }}" method="POST" id="form-proses-naik" onsubmit="return confirmProsesKenaikan()">
                        @csrf
                        <input type="hidden" name="kelas_asal_id" value="{{ $kelasAsal->id }}">

                        <!-- Box Pemilihan Kelas Tujuan -->
                        <div class="max-w-4xl mx-auto mb-6 p-5 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xl font-bold">
                                    {{ $kelasAsal->tingkat }}
                                </span>
                                <div>
                                    <div class="text-xs text-slate-500 font-semibold uppercase">Kelas Asal Terpilih</div>
                                    <div class="text-lg font-extrabold text-slate-800">{{ $kelasAsal->nama_lengkap }}</div>
                                    <div class="text-xs text-emerald-700 font-medium">Jumlah: {{ $siswaKelasAsal->count() }} Siswa Aktif</div>
                                </div>
                            </div>

                            <div class="text-2xl text-emerald-600 hidden md:block">
                                <i class="bi bi-arrow-right-circle-fill"></i>
                            </div>

                            <div class="w-full md:w-80">
                                <label class="block text-xs font-bold uppercase tracking-wider text-emerald-900 mb-1.5">
                                    <i class="bi bi-check2-circle text-emerald-600 mr-1"></i> Kelas Tujuan Kenaikan:
                                </label>
                                <select name="kelas_tujuan_id" id="kelas_tujuan_id" class="form-select w-full font-bold border-emerald-300 focus:border-emerald-500 bg-white" required>
                                    <option value="">-- Pilih Kelas Tujuan --</option>
                                    @foreach($kelasTujuanOptions as $opt)
                                        <option value="{{ $opt->id }}" {{ ($rekomendasiKelas && $rekomendasiKelas->id == $opt->id) ? 'selected' : '' }}>
                                            {{ $opt->nama_lengkap }} {{ ($rekomendasiKelas && $rekomendasiKelas->id == $opt->id) ? '⭐ (Disarankan)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Tabel Siswa Rombel Asal -->
                        <div class="max-w-4xl mx-auto">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                                <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                                    <i class="bi bi-people text-emerald-600"></i>
                                    Daftar Siswa Kelas {{ $kelasAsal->nama_lengkap }}
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">{{ $siswaKelasAsal->count() }} orang</span>
                                </h3>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="setSemuaStatus('naik')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 hover:bg-emerald-200 transition">
                                        <i class="bi bi-check-all"></i> Semua Naik Kelas
                                    </button>
                                    <button type="button" onclick="setSemuaStatus('tinggal')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-rose-100 text-rose-800 hover:bg-rose-200 transition">
                                        <i class="bi bi-x-circle"></i> Semua Tinggal Kelas
                                    </button>
                                </div>
                            </div>

                            <div class="table-container shadow-sm mb-6">
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="w-12 text-center">No</th>
                                            <th>NIS / Username</th>
                                            <th>Nama Lengkap Siswa</th>
                                            <th class="text-center">L/P</th>
                                            <th class="text-center w-56">Status Keputusan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($siswaKelasAsal as $idx => $s)
                                            <tr class="hover:bg-slate-50/80">
                                                <td class="text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                                                <td class="font-mono text-xs text-slate-600">{{ $s->nip ?? $s->username ?? '-' }}</td>
                                                <td>
                                                    <div class="font-bold text-slate-800">{{ $s->name }}</div>
                                                    <div class="text-[11px] text-slate-400">{{ $s->email }}</div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold {{ $s->jenis_kelamin === 'P' ? 'bg-pink-100 text-pink-700' : 'bg-blue-100 text-blue-700' }}">
                                                        {{ $s->jenis_kelamin ?? 'L' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200">
                                                        <label class="cursor-pointer">
                                                            <input type="radio" name="siswa[{{ $s->id }}]" value="naik" checked class="sr-only peer status-radio-naik">
                                                            <span class="px-3 py-1 text-xs font-bold rounded-lg block peer-checked:bg-emerald-600 peer-checked:text-white text-slate-600 transition">
                                                                <i class="bi bi-arrow-up-right"></i> Naik
                                                            </span>
                                                        </label>
                                                        <label class="cursor-pointer">
                                                            <input type="radio" name="siswa[{{ $s->id }}]" value="tinggal" class="sr-only peer status-radio-tinggal">
                                                            <span class="px-3 py-1 text-xs font-bold rounded-lg block peer-checked:bg-rose-600 peer-checked:text-white text-slate-600 transition">
                                                                <i class="bi bi-arrow-down-left"></i> Tinggal
                                                            </span>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-8 text-slate-400">
                                                    <i class="bi bi-person-x text-3xl block mb-2 text-slate-300"></i>
                                                    Tidak ada siswa aktif terdaftar pada rombel {{ $kelasAsal->nama_lengkap }}.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($siswaKelasAsal->isNotEmpty())
                                <div class="flex justify-end gap-3 p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                                    <button type="submit" class="btn-success px-6 py-3 text-base shadow-lg">
                                        <i class="bi bi-check-circle-fill text-lg"></i>
                                        Proses Kenaikan Kelas Siswa
                                    </button>
                                </div>
                            @endif
                        </div>
                    </form>
                @else
                    <div class="max-w-md mx-auto text-center py-12">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl mx-auto mb-4 border border-emerald-200">
                            <i class="bi bi-box-arrow-in-up-right"></i>
                        </div>
                        <h4 class="font-bold text-slate-700 text-lg mb-1">Pilih Rombel Terlebih Dahulu</h4>
                        <p class="text-sm text-slate-500">Silakan pilih kelas asal pada dropdown di atas untuk memulai skema kenaikan kelas siswa.</p>
                    </div>
                @endif
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 2: KELULUSAN SISWA KELAS XII -> OTOMATIS JADI ALUMNI --}}
            {{-- ======================================================== --}}
            <div id="tab-content-kelulusan" class="tab-pane {{ $activeTab === 'kelulusan' ? '' : 'hidden' }}">
                <div class="max-w-4xl mx-auto mb-8 bg-amber-50/80 border border-amber-200/90 rounded-2xl p-5 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center shrink-0 text-xl shadow-sm">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <div class="text-sm text-slate-700 leading-relaxed">
                        <h4 class="font-bold text-amber-900 mb-1">Otomatisasi Kelulusan Menjadi Alumni (Tracer Study)</h4>
                        Ketika siswa kelas XII diproses kelulusannya, sistem secara otomatis:
                        <ul class="list-disc list-inside mt-1.5 space-y-1 text-slate-600">
                            <li>Mengubah status profil siswa menjadi <strong>Lulus</strong> dan melepas rombel kelas aktifnya.</li>
                            <li><strong>Langsung mencatatkan siswa ke direktori Alumni</strong> dengan Tahun Kelulusan yang ditentukan.</li>
                            <li>Akun siswa tetap aktif sehingga alumni dapat login untuk mengisi kuesioner <strong>Tracer Study</strong>.</li>
                        </ul>
                    </div>
                </div>

                <!-- Filter & Form Kelulusan -->
                <form action="{{ route('superadmin.kenaikan-kelas.lulus') }}" method="POST" id="form-proses-lulus" onsubmit="return confirmProsesKelulusan()">
                    @csrf
                    <div class="max-w-4xl mx-auto bg-slate-50 border border-slate-200 rounded-2xl p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <div class="md:col-span-4">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                    <i class="bi bi-funnel text-amber-600 mr-1"></i> Filter Rombel Kelas XII:
                                </label>
                                <select onchange="window.location.href='{{ route('superadmin.kenaikan-kelas.index', ['tab' => 'kelulusan']) }}&kelas_xii_id=' + this.value" class="form-select w-full font-medium">
                                    <option value="all" {{ $selectedKelasXiiId === 'all' ? 'selected' : '' }}>-- Semua Kelas XII ({{ $countSiswaXII }} Siswa) --</option>
                                    @foreach($kelasXII as $kxii)
                                        <option value="{{ $kxii->id }}" {{ $selectedKelasXiiId == $kxii->id ? 'selected' : '' }}>
                                            {{ $kxii->nama_lengkap }} ({{ $kxii->siswas()->count() }} Siswa)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-xs font-bold uppercase tracking-wider text-amber-900 mb-2">
                                    <i class="bi bi-calendar-event text-amber-600 mr-1"></i> Tahun Kelulusan: <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" name="tahun_lulus" id="tahun_lulus" value="{{ $defaultTahunLulus }}" min="2020" max="2099" class="form-input w-full font-extrabold text-amber-900" required>
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                    Status Awal Tracer:
                                </label>
                                <select name="status_tracer" class="form-select w-full text-sm">
                                    <option value="Belum Mengisi">Belum Mengisi (Default)</option>
                                    <option value="Bekerja">Bekerja</option>
                                    <option value="Melanjutkan Pendidikan (Kuliah)">Kuliah</option>
                                    <option value="Wirausaha">Wirausaha</option>
                                    <option value="Mencari Kerja">Mencari Kerja</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Siswa Kelas XII -->
                    <div class="max-w-4xl mx-auto">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                                <i class="bi bi-mortarboard text-amber-600"></i>
                                Siswa Kelas XII Siap Diluluskan
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">{{ $siswaKelasXII->count() }} calon lulusan</span>
                            </h3>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="toggleSelectAllXii(true)" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-100 text-amber-800 hover:bg-amber-200 transition">
                                    <i class="bi bi-check-square"></i> Pilih Semua
                                </button>
                                <button type="button" onclick="toggleSelectAllXii(false)" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-200 text-slate-700 hover:bg-slate-300 transition">
                                    <i class="bi bi-square"></i> Batalkan Semua
                                </button>
                            </div>
                        </div>

                        <div class="table-container shadow-sm mb-6">
                            <table>
                                <thead>
                                    <tr>
                                        <th class="w-12 text-center">
                                            <input type="checkbox" id="checkAllXii" checked onclick="toggleSelectAllXii(this.checked)" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                        </th>
                                        <th>NIS / Username</th>
                                        <th>Nama Lengkap Siswa</th>
                                        <th>Kelas Rombel</th>
                                        <th class="text-center">Status Kelulusan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($siswaKelasXII as $s)
                                        <tr class="hover:bg-amber-50/40">
                                            <td class="text-center">
                                                <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" checked class="check-siswa-xii rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                            </td>
                                            <td class="font-mono text-xs text-slate-600">{{ $s->nip ?? $s->username ?? '-' }}</td>
                                            <td>
                                                <div class="font-bold text-slate-800">{{ $s->name }}</div>
                                                <div class="text-[11px] text-slate-400">{{ $s->email }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-slate-100 text-slate-700 border border-slate-200 font-semibold">
                                                    {{ $s->kelas?->nama_lengkap ?? 'Kelas XII' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                                    <i class="bi bi-award"></i> Lulus & Jadi Alumni
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-8 text-slate-400">
                                                <i class="bi bi-emoji-smile text-3xl block mb-2 text-slate-300"></i>
                                                Semua siswa kelas XII sudah diluluskan atau belum ada siswa di tingkat XII.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($siswaKelasXII->isNotEmpty())
                            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 p-5 bg-amber-50/70 border border-amber-200 rounded-2xl">
                                <div class="text-xs text-amber-800">
                                    <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                                    Pastikan tahun kelulusan sudah benar sebelum memproses. Data akan langsung terhubung ke modul Tracer Alumni.
                                </div>
                                <button type="submit" class="btn-primary bg-amber-600 hover:bg-amber-700 text-white border-none px-6 py-3 text-base shadow-lg shrink-0">
                                    <i class="bi bi-mortarboard-fill text-lg"></i>
                                    Proses Kelulusan & Arsipkan ke Alumni
                                </button>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 3: PERGANTIAN TAHUN AJARAN BERKELANJUTAN             --}}
            {{-- ======================================================== --}}
            <div id="tab-content-tahun_ajaran" class="tab-pane {{ $activeTab === 'tahun_ajaran' ? '' : 'hidden' }}">
                <div class="max-w-4xl mx-auto mb-8 bg-teal-50/80 border border-teal-200/90 rounded-2xl p-5 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center shrink-0 text-xl shadow-sm">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div class="text-sm text-slate-700 leading-relaxed">
                        <h4 class="font-bold text-teal-900 mb-1">Skema Transisi Tahun Ajaran Berkelanjutan</h4>
                        Pergantian Tahun Ajaran dilakukan setelah proses promosi kenaikan kelas dan kelulusan selesai. Sistem akan menutup Tahun Ajaran aktif lama dan mengaktifkan Tahun Ajaran baru secara berkelanjutan dimulai dari <strong>Semester Ganjil</strong>.
                    </div>
                </div>

                <!-- Visual Alur Berkelanjutan -->
                <div class="max-w-4xl mx-auto mb-8 p-6 bg-slate-900 text-white rounded-2xl shadow-md">
                    <div class="text-xs font-bold uppercase tracking-wider text-emerald-400 mb-4 flex items-center gap-2">
                        <i class="bi bi-signpost-split"></i> Alur Siklus Tahunan Sekolah
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
                        <div class="bg-white/10 p-4 rounded-xl border border-white/10">
                            <div class="text-xs text-slate-400">Tahap 1</div>
                            <div class="font-bold text-emerald-300 text-sm mt-1">Kelulusan Kelas XII</div>
                            <div class="text-[11px] text-slate-300 mt-1">Otomatis masuk database Alumni</div>
                        </div>
                        <div class="bg-white/10 p-4 rounded-xl border border-white/10">
                            <div class="text-xs text-slate-400">Tahap 2</div>
                            <div class="font-bold text-blue-300 text-sm mt-1">Kenaikan Rombel</div>
                            <div class="text-[11px] text-slate-300 mt-1">X naik ke XI, XI naik ke XII</div>
                        </div>
                        <div class="bg-white/10 p-4 rounded-xl border border-white/10">
                            <div class="text-xs text-slate-400">Tahap 3</div>
                            <div class="font-bold text-teal-300 text-sm mt-1">Tutup TA Lama</div>
                            <div class="text-[11px] text-slate-300 mt-1">{{ $tahunAjaranAktif->nama ?? '2026/2027' }} dinonaktifkan</div>
                        </div>
                        <div class="bg-emerald-600/30 p-4 rounded-xl border border-emerald-400/50">
                            <div class="text-xs text-emerald-200">Tahap 4</div>
                            <div class="font-bold text-white text-sm mt-1">Aktifkan TA Baru</div>
                            <div class="text-[11px] text-emerald-200 mt-1">{{ $nextTahunNama }} (Semester Ganjil)</div>
                        </div>
                    </div>
                </div>

                <!-- Form Ganti Tahun Ajaran Baru -->
                <form action="{{ route('superadmin.kenaikan-kelas.tahun-ajaran-baru') }}" method="POST" class="max-w-4xl mx-auto bg-slate-50 border border-slate-200 rounded-2xl p-6 md:p-8" onsubmit="return confirmGantiTahunAjaran()">
                    @csrf
                    <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">
                        <i class="bi bi-calendar-plus text-teal-600"></i>
                        Buka & Aktifkan Tahun Ajaran Baru
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                Nama Tahun Ajaran Baru: <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nama" value="{{ $nextTahunNama }}" required class="form-input w-full font-black text-lg text-teal-900 border-teal-300 focus:border-teal-500">
                            <p class="text-xs text-slate-500 mt-1">Sistem otomatis menghitung kelanjutan tahun: {{ $nextTahunNama }}.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                Semester Awal: <span class="text-rose-500">*</span>
                            </label>
                            <select name="semester" class="form-select w-full font-bold text-teal-900" required>
                                <option value="ganjil" selected>Semester Ganjil (Awal Tahun Ajaran)</option>
                                <option value="genap">Semester Genap</option>
                            </select>
                            <p class="text-xs text-slate-500 mt-1">Tahun ajaran baru selalu dimulai dari semester Ganjil.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                Tahun Mulai: <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="tahun_mulai" value="{{ $nextTahunMulai }}" required min="2020" max="2099" class="form-input w-full font-bold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                Tahun Selesai: <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="tahun_selesai" value="{{ $nextTahunSelesai }}" required min="2020" max="2099" class="form-input w-full font-bold">
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-xl border border-slate-200 mb-6 flex items-start gap-3">
                        <input type="checkbox" name="duplikasi_kelas" id="duplikasi_kelas" value="1" checked class="rounded border-slate-300 text-teal-600 focus:ring-teal-500 mt-1">
                        <label for="duplikasi_kelas" class="text-xs text-slate-700 cursor-pointer">
                            <span class="font-bold text-slate-800 block">Kaitkan Rombel Kelas Aktif ke Tahun Ajaran Baru</span>
                            Menghubungkan data rombel kelas SMK Plus Al Hilal yang saat ini aktif ke Tahun Ajaran baru secara otomatis sehingga guru dan siswa dapat langsung memulai aktivitas pembelajaran.
                        </label>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="submit" class="btn-success bg-teal-600 hover:bg-teal-700 px-6 py-3 text-base shadow-lg">
                            <i class="bi bi-check2-all text-lg"></i>
                            Tutup TA Lama & Aktifkan Tahun Ajaran Baru Berkelanjutan
                        </button>
                    </div>
                </form>
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 4: DIREKTORI ALUMNI TERDAFTAR (TRACER STUDY)         --}}
            {{-- ======================================================== --}}
            <div id="tab-content-alumni" class="tab-pane {{ $activeTab === 'alumni' ? '' : 'hidden' }}">
                <div class="max-w-4xl mx-auto mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 class="font-black text-slate-800 text-lg flex items-center gap-2">
                            <i class="bi bi-people-fill text-indigo-600"></i>
                            Direktori Alumni Hasil Kelulusan
                        </h3>
                        <p class="text-xs text-slate-500">Daftar siswa yang telah diluluskan dan otomatis terintegrasi ke sistem Tracer Study.</p>
                    </div>
                    <a href="{{ route('tracer.alumni.index') }}" class="btn-primary bg-indigo-600 hover:bg-indigo-700 text-white text-xs">
                        <i class="bi bi-box-arrow-up-right mr-1"></i> Buka Modul Tracer Study Lengkap
                    </a>
                </div>

                <!-- Filter Alumni -->
                <div class="max-w-4xl mx-auto bg-slate-50 p-4 rounded-xl border border-slate-200 mb-6 flex flex-wrap items-center gap-3">
                    <form action="{{ route('superadmin.kenaikan-kelas.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full">
                        <input type="hidden" name="tab" value="alumni">
                        <span class="text-xs font-bold text-slate-600 uppercase">Filter Angkatan Lulus:</span>
                        <select name="filter_tahun_alumni" class="form-select text-sm py-1.5" onchange="this.form.submit()">
                            <option value="">-- Semua Tahun Lulus --</option>
                            @foreach($alumniTahunList as $thn)
                                <option value="{{ $thn }}" {{ $selectedTahunAlumni == $thn ? 'selected' : '' }}>
                                    Lulusan Tahun {{ $thn }}
                                </option>
                            @endforeach
                        </select>
                        @if($selectedTahunAlumni)
                            <a href="{{ route('superadmin.kenaikan-kelas.index', ['tab' => 'alumni']) }}" class="btn-secondary text-xs py-1.5">Reset</a>
                        @endif
                    </form>
                </div>

                <!-- Tabel Alumni -->
                <div class="max-w-4xl mx-auto table-container shadow-sm mb-6">
                    <table>
                        <thead>
                            <tr>
                                <th class="w-12 text-center">No</th>
                                <th>Alumni & Akun</th>
                                <th class="text-center">Tahun Lulus</th>
                                <th>Status Saat Ini</th>
                                <th>Instansi / Kampus / Tempat Kerja</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAlumni as $idx => $alm)
                                <tr class="hover:bg-indigo-50/30">
                                    <td class="text-center font-bold text-slate-400">
                                        {{ $recentAlumni->firstItem() + $idx }}
                                    </td>
                                    <td>
                                        <div class="font-bold text-slate-800">{{ $alm->user?->name ?? 'User #' . $alm->user_id }}</div>
                                        <div class="text-xs text-slate-500 font-mono">NIS/Username: {{ $alm->user?->nip ?? $alm->user?->username ?? '-' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-indigo-100 text-indigo-800">
                                            {{ $alm->tahun_lulus }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $alm->status_saat_ini && $alm->status_saat_ini !== 'Belum Mengisi' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $alm->status_saat_ini ?? 'Belum Mengisi' }}
                                        </span>
                                    </td>
                                    <td class="text-sm text-slate-600">
                                        {{ $alm->nama_instansi ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10 text-slate-400">
                                        <i class="bi bi-mortarboard text-4xl block mb-2 text-slate-300"></i>
                                        Belum ada alumni yang tercatat pada filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="max-w-4xl mx-auto">
                    {{ $recentAlumni->links() }}
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Tab Switching Function
    function switchTab(tabId) {
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-emerald-600', 'bg-amber-600', 'bg-teal-600', 'bg-indigo-600', 'text-white', 'shadow-md');
            btn.classList.add('text-slate-600');
        });

        const targetPane = document.getElementById('tab-content-' + tabId);
        const targetBtn  = document.getElementById('btn-tab-' + tabId);

        if (targetPane) targetPane.classList.remove('hidden');
        if (targetBtn) {
            targetBtn.classList.remove('text-slate-600');
            if (tabId === 'promosi') targetBtn.classList.add('bg-emerald-600', 'text-white', 'shadow-md');
            if (tabId === 'kelulusan') targetBtn.classList.add('bg-amber-600', 'text-white', 'shadow-md');
            if (tabId === 'tahun_ajaran') targetBtn.classList.add('bg-teal-600', 'text-white', 'shadow-md');
            if (tabId === 'alumni') targetBtn.classList.add('bg-indigo-600', 'text-white', 'shadow-md');
        }

        // Update URL query param without reload
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);
    }

    // Toggle Semua Status Kenaikan (Naik / Tinggal)
    function setSemuaStatus(status) {
        if (status === 'naik') {
            document.querySelectorAll('.status-radio-naik').forEach(el => el.checked = true);
        } else {
            document.querySelectorAll('.status-radio-tinggal').forEach(el => el.checked = true);
        }
    }

    // Toggle Checkbox Kelulusan Siswa XII
    function toggleSelectAllXii(checked) {
        document.querySelectorAll('.check-siswa-xii').forEach(cb => cb.checked = checked);
        const mainCb = document.getElementById('checkAllXii');
        if (mainCb) mainCb.checked = checked;
    }

    // Konfirmasi Kenaikan Kelas
    function confirmProsesKenaikan() {
        const targetSelect = document.getElementById('kelas_tujuan_id');
        if (!targetSelect.value) {
            alert('Pilih kelas tujuan kenaikan kelas terlebih dahulu!');
            targetSelect.focus();
            return false;
        }
        const targetText = targetSelect.options[targetSelect.selectedIndex].text;
        return confirm('Konfirmasi Kenaikan Kelas:\nApakah Anda yakin ingin memproses kenaikan kelas untuk siswa terpilih ke ' + targetText + '?');
    }

    // Konfirmasi Kelulusan Kelas XII
    function confirmProsesKelulusan() {
        const checkedCount = document.querySelectorAll('.check-siswa-xii:checked').length;
        if (checkedCount === 0) {
            alert('Pilih minimal satu siswa yang akan diluluskan!');
            return false;
        }
        const tahunLulus = document.getElementById('tahun_lulus').value;
        return confirm('Konfirmasi Kelulusan Siswa:\nSebanyak ' + checkedCount + ' siswa kelas XII akan diluluskan dan otomatis terdaftar sebagai Alumni Angkatan ' + tahunLulus + '.\n\nLanjutkan proses?');
    }

    // Konfirmasi Pergantian Tahun Ajaran Baru
    function confirmGantiTahunAjaran() {
        return confirm('PERINGATAN PERGANTIAN TAHUN AJARAN:\n\nTahun Ajaran lama akan ditutup dan Tahun Ajaran baru akan diaktifkan secara berkelanjutan.\n\nApakah seluruh proses kenaikan kelas dan kelulusan telah selesai dilakukan? Lanjutkan?');
    }
</script>
@endsection
