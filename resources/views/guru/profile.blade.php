@extends('layouts.app')

@section('title', 'Ubah Profil Guru & Data Pribadi')

@section('content')
<div class="space-y-6">

    <!-- Top Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 p-6 sm:p-8 text-white shadow-xl">
        <div class="absolute right-0 top-0 -mt-10 -mr-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4 sm:gap-5">
                <!-- Avatar Preview -->
                <div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-amber-500/20 border-2 border-amber-400/50 p-1 flex-shrink-0 shadow-lg">
                    @if($user->foto || $user->avatar)
                    <img src="{{ asset($user->foto ?: $user->avatar) }}" 
                         alt="{{ $user->name }}" 
                         class="w-full h-full object-cover rounded-xl"
                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0D8ABC&color=fff';">
                    @else
                    <div class="w-full h-full rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 text-slate-950 font-black text-xl flex items-center justify-center">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    @endif
                    @if($user->kode_guru)
                    <span class="absolute -bottom-2 -right-2 w-7 h-7 rounded-xl bg-slate-900 text-amber-300 border border-amber-400/60 text-xs font-black flex items-center justify-center shadow-md" title="Kode Guru Resmi">
                        {{ $user->kode_guru }}
                    </span>
                    @endif
                </div>

                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-0.5 rounded-full bg-white/10 text-amber-300 text-xs font-semibold backdrop-blur-sm border border-white/10">
                        <i class="bi-shield-check text-emerald-400"></i>
                        <span>Profil Dewan Guru SMK Plus Al-Hilal</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        {{ $user->name }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-300 flex items-center gap-2 flex-wrap">
                        <span>NIP: <strong class="text-white">{{ $user->nip ?: 'Non-NIP' }}</strong></span>
                        <span>&bull;</span>
                        <span>NUPTK: <strong class="text-white">{{ $user->nuptk ?: ($guruHilal?->nuptk ?: '-') }}</strong></span>
                        <span>&bull;</span>
                        <span class="px-2 py-0.5 rounded-md bg-amber-400/20 text-amber-300 text-[11px] font-bold border border-amber-400/30">
                            ID Database: #{{ $user->id }} (Sistem)
                        </span>
                    </p>
                </div>
            </div>

            <div>
                <a href="{{ route('guru.dashboard') }}" class="px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition flex items-center gap-2 border border-white/20">
                    <i class="bi-arrow-left"></i>
                    <span>Kembali ke Beranda</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Sukses -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl flex items-center gap-3 text-xs font-semibold shadow-xs">
        <i class="bi-check-circle-fill text-xl text-emerald-600 flex-shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Alert Error Validasi -->
    @if(isset($errors) && $errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-900 rounded-2xl space-y-1 text-xs shadow-xs">
        <div class="flex items-center gap-2 font-bold text-rose-800">
            <i class="bi-exclamation-triangle-fill text-base text-rose-600"></i>
            <span>Terdapat beberapa isian yang perlu diperiksa kembali:</span>
        </div>
        <ul class="list-disc list-inside space-y-0.5 text-rose-700 pl-4">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('guru.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- CARD 1: DATA IDENTITAS & AKUN LOGIN -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-blue-50 text-blue-600 rounded-2xl border border-blue-200">
                        <i class="bi-person-badge text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900">Identitas Utama & Akun Pengguna</h2>
                        <p class="text-xs text-slate-500">Data identitas resmi guru yang tersinkronisasi 1:1 ke database HilalEdu.</p>
                    </div>
                </div>
                <span class="hidden sm:inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-xl border border-amber-200">
                    <i class="bi-shield-lock"></i> ID Sistem Terkunci
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                
                <!-- ID Database (Non-editable / Terkunci) -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1.5 flex items-center justify-between">
                        <span>ID Database Utama</span>
                        <span class="text-[10px] text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded border border-amber-200">Tidak Dapat Diubah</span>
                    </label>
                    <input type="text" value="#{{ $user->id }}" disabled
                           class="w-full px-4 py-2.5 bg-slate-100 border border-slate-300 rounded-xl text-xs font-mono font-bold text-slate-600 cursor-not-allowed shadow-inner">
                    <p class="text-[10px] text-slate-400 mt-1">ID database bersifat permanen dan menjadi acuan relasi cross-app.</p>
                </div>

                <!-- Nama Lengkap & Gelar -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->nama_lengkap ?: $user->name) }}" required
                           placeholder="Contoh: Sokid, ST, M.Kom"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>

                <!-- Username Login -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Username Login <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition font-mono">
                </div>

                <!-- Email Resmi -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Email Resmi <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>

                <!-- NIP -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Induk Pegawai (NIP)</label>
                    <input type="text" name="nip" value="{{ old('nip', $user->nip ?: $guruHilal?->nip) }}"
                           placeholder="Kosongkan jika Non-NIP"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition font-mono">
                </div>

                <!-- NUPTK -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">NUPTK</label>
                    <input type="text" name="nuptk" value="{{ old('nuptk', $user->nuptk ?: $guruHilal?->nuptk) }}"
                           placeholder="Nomor NUPTK resmi"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition font-mono">
                </div>

                <!-- Jenis Kelamin -->
                @php
                    $jkVal = old('jenis_kelamin', $user->jenis_kelamin ?: ($guruHilal?->jenis_kelamin ?: 'L'));
                @endphp
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        <option value="L" {{ $jkVal === 'L' ? 'selected' : '' }}>Laki-Laki (L)</option>
                        <option value="P" {{ $jkVal === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                    </select>
                </div>

                <!-- Tempat Lahir -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $user->tempat_lahir ?: $guruHilal?->tempat_lahir) }}"
                           placeholder="Kota / Kabupaten Kelahiran"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir ?: $guruHilal?->tanggal_lahir) }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>

                <!-- Upload Foto Profil -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Foto Profil Baru (Opsional)</label>
                    <input type="file" name="foto_file" accept="image/*"
                           class="w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG, WEBP. Maksimal 2 MB.</p>
                </div>

            </div>
        </div>

        <!-- CARD 2: KEPEGAWAIAN, MAPEL & PENUGASAN -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-2xl border border-emerald-200">
                    <i class="bi-briefcase text-xl"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Data Kepegawaian & Penugasan</h2>
                    <p class="text-xs text-slate-500">Jabatan struktural, status kepegawaian, mapel pengampu, dan tugas tambahan.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                
                <!-- Jabatan Utama -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jabatan Lembaga</label>
                    <input type="text" name="jabatan_utama" value="{{ old('jabatan_utama', $user->jabatan_utama ?: ($guruHilal?->jabatan ?: 'Guru Pengajar')) }}"
                           placeholder="Contoh: Kepala SMK, Guru Pengajar, Staf"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                </div>

                <!-- Bidang Studi / Mapel Diampu -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Bidang Studi / Mapel Diampu</label>
                    @if(!empty($bidangStudiJadwal))
                        <input type="text" value="{{ $bidangStudiJadwal }}"
                               class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 focus:outline-none" readonly>
                        <input type="hidden" name="bidang_studi" value="{{ $bidangStudiJadwal }}">
                        <p class="text-[10px] text-emerald-600 mt-1"><i class="bi-info-circle me-1"></i>Otomatis disinkronkan dari Penjadwalan.</p>
                    @else
                        <input type="text" name="bidang_studi" value="{{ old('bidang_studi', $user->bidang_studi ?: ($guruHilal?->bidang_studi ?: '')) }}"
                               placeholder="Contoh: Produktif TKJ, Matematika, PAI"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                    @endif
                </div>

                <!-- Status Kepegawaian -->
                @php
                    $statKep = old('status_kepegawaian', $user->status_kepegawaian ?: ($guruHilal?->status_kepegawaian ?: 'GTY'));
                @endphp
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Kepegawaian</label>
                    <select name="status_kepegawaian" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                        <option value="GTY" {{ $statKep === 'GTY' ? 'selected' : '' }}>Guru Tetap Yayasan (GTY)</option>
                        <option value="GTT" {{ $statKep === 'GTT' ? 'selected' : '' }}>Guru Tidak Tetap / Honor (GTT)</option>
                        <option value="PNS" {{ $statKep === 'PNS' ? 'selected' : '' }}>Pegawai Negeri Sipil (PNS)</option>
                        <option value="non-PNS" {{ $statKep === 'non-PNS' ? 'selected' : '' }}>Non-PNS</option>
                        <option value="Guru Pesantren" {{ $statKep === 'Guru Pesantren' ? 'selected' : '' }}>Guru Pesantren</option>
                    </select>
                </div>

                <!-- Tugas Tambahan (Read-Only) -->
                @php
                    $tugasAktif = is_array($user->tugas_tambahan) ? $user->tugas_tambahan : [];
                @endphp
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                        Tugas Tambahan Struktural / Khusus (Hanya Baca)
                    </label>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200">
                        @if(empty($tugasAktif))
                            <p class="text-xs text-slate-500 italic">Belum ada tugas tambahan yang diberikan.</p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach($tugasAktif as $t)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        {{ $t }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Tugas tambahan diatur oleh Admin atau Super Admin.</p>
                </div>

            </div>
        </div>

        <!-- CARD 3: KONTAK & ALAMAT LENGKAP -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="p-2.5 bg-purple-50 text-purple-600 rounded-2xl border border-purple-200">
                    <i class="bi-geo-alt text-xl"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Kontak & Alamat Domisili</h2>
                    <p class="text-xs text-slate-500">Nomor WhatsApp dan alamat lengkap guru untuk keperluan administrasi dan darurat.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                
                <!-- Nomor WhatsApp -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor WhatsApp / HP Aktif</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp ?: $guruHilal?->no_hp) }}"
                           placeholder="Contoh: 082216715951"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition font-mono">
                </div>

                <!-- Alamat Jalan / Blok -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Jalan / Blok / RT / RW</label>
                    <input type="text" name="alamat" value="{{ old('alamat', $user->alamat ?: $guruHilal?->alamat) }}"
                           placeholder="Contoh: Jl. H. Manshur No. 07 Blok 02"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition">
                </div>

                <!-- Desa / Kelurahan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Desa / Kelurahan</label>
                    <input type="text" name="desa" value="{{ old('desa', $user->desa ?: $userHilal?->desa) }}"
                           placeholder="Contoh: Tegalgubug"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition">
                </div>

                <!-- Kecamatan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kecamatan</label>
                    <input type="text" name="kecamatan" value="{{ old('kecamatan', $user->kecamatan ?: $userHilal?->kecamatan) }}"
                           placeholder="Contoh: Arjawinangun"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition">
                </div>

                <!-- Kabupaten / Kota -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kabupaten / Kota</label>
                    <input type="text" name="kabupaten" value="{{ old('kabupaten', $user->kabupaten ?: $userHilal?->kabupaten) }}"
                           placeholder="Contoh: Cirebon"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition">
                </div>

                <!-- Provinsi -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Provinsi</label>
                    <input type="text" name="provinsi" value="{{ old('provinsi', $user->provinsi ?: ($userHilal?->provinsi ?: 'Jawa Barat')) }}"
                           placeholder="Contoh: Jawa Barat"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition">
                </div>

            </div>
        </div>

        <!-- CARD 4: RIWAYAT PENDIDIKAN FORMAL -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="p-2.5 bg-amber-50 text-amber-600 rounded-2xl border border-amber-200">
                    <i class="bi-mortarboard text-xl"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Riwayat Pendidikan Formal</h2>
                    <p class="text-xs text-slate-500">Nama institusi pendidikan dan tahun kelulusan jenjang dasar hingga magister.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                
                <!-- SD -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                    <h4 class="font-bold text-xs text-slate-800 uppercase tracking-wider">1. Pendidikan Dasar (SD / MI)</h4>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Nama SD / MI</label>
                            <input type="text" name="pendidikan_sd" value="{{ old('pendidikan_sd', $user->pendidikan_sd ?: $userHilal?->pendidikan_sd) }}"
                                   placeholder="Contoh: SDN 1 Tegalgubug"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Tahun Lulus</label>
                            <input type="text" name="tahun_lulus_sd" value="{{ old('tahun_lulus_sd', $user->tahun_lulus_sd ?: $userHilal?->tahun_lulus_sd) }}"
                                   placeholder="Tahun"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold font-mono text-center">
                        </div>
                    </div>
                </div>

                <!-- SMP -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                    <h4 class="font-bold text-xs text-slate-800 uppercase tracking-wider">2. Pendidikan Menengah Pertama (SMP / MTs)</h4>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Nama SMP / MTs</label>
                            <input type="text" name="pendidikan_smp" value="{{ old('pendidikan_smp', $user->pendidikan_smp ?: $userHilal?->pendidikan_smp) }}"
                                   placeholder="Contoh: MTs Al-Hilal"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Tahun Lulus</label>
                            <input type="text" name="tahun_lulus_smp" value="{{ old('tahun_lulus_smp', $user->tahun_lulus_smp ?: $userHilal?->tahun_lulus_smp) }}"
                                   placeholder="Tahun"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold font-mono text-center">
                        </div>
                    </div>
                </div>

                <!-- SMA -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                    <h4 class="font-bold text-xs text-slate-800 uppercase tracking-wider">3. Pendidikan Menengah Atas (SMA / SMK / MA)</h4>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Nama SMA / SMK / MA</label>
                            <input type="text" name="pendidikan_sma" value="{{ old('pendidikan_sma', $user->pendidikan_sma ?: $userHilal?->pendidikan_sma) }}"
                                   placeholder="Contoh: SMA Negeri 1 Arjawinangun"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Tahun Lulus</label>
                            <input type="text" name="tahun_lulus_sma" value="{{ old('tahun_lulus_sma', $user->tahun_lulus_sma ?: $userHilal?->tahun_lulus_sma) }}"
                                   placeholder="Tahun"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold font-mono text-center">
                        </div>
                    </div>
                </div>

                <!-- S1 -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                    <h4 class="font-bold text-xs text-slate-800 uppercase tracking-wider">4. Sarjana / S1 (Strata 1)</h4>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Kampus & Program Studi</label>
                            <input type="text" name="pendidikan_s1" value="{{ old('pendidikan_s1', $user->pendidikan_s1 ?: $userHilal?->pendidikan_s1) }}"
                                   placeholder="Contoh: Universitas Swadaya Gunung Jati (S1 TI)"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Tahun Lulus</label>
                            <input type="text" name="tahun_lulus_s1" value="{{ old('tahun_lulus_s1', $user->tahun_lulus_s1 ?: $userHilal?->tahun_lulus_s1) }}"
                                   placeholder="Tahun"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold font-mono text-center">
                        </div>
                    </div>
                </div>

                <!-- S2 -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3 md:col-span-2">
                    <h4 class="font-bold text-xs text-slate-800 uppercase tracking-wider">5. Magister / S2 (Strata 2)</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div class="sm:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Kampus & Program Magister</label>
                            <input type="text" name="pendidikan_s2" value="{{ old('pendidikan_s2', $user->pendidikan_s2 ?: $userHilal?->pendidikan_s2) }}"
                                   placeholder="Contoh: Universitas Amikom Yogyakarta (Magister Teknik Informatika)"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">Tahun Lulus</label>
                            <input type="text" name="tahun_lulus_s2" value="{{ old('tahun_lulus_s2', $user->tahun_lulus_s2 ?: $userHilal?->tahun_lulus_s2) }}"
                                   placeholder="Tahun"
                                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold font-mono text-center">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- CARD 5: KEAMANAN & GANTI PASSWORD (OPSIONAL) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="p-2.5 bg-rose-50 text-rose-600 rounded-2xl border border-rose-200">
                    <i class="bi-key text-xl"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Keamanan Akun & Ganti Password</h2>
                    <p class="text-xs text-slate-500">Kosongkan bagian ini jika Anda tidak ingin mengubah password akun login Anda.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Password Baru</label>
                    <input type="password" name="password" placeholder="Minimal 6 karakter"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" placeholder="Ulangi password baru"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition">
                </div>
            </div>
        </div>

        <!-- Action Submit Button Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 bg-slate-900 text-white rounded-3xl shadow-xl">
            <div class="flex items-center gap-3">
                <i class="bi-cloud-arrow-up-fill text-2xl text-amber-400"></i>
                <div>
                    <div class="text-sm font-black">Simpan & Sinkronisasikan Data Guru</div>
                    <div class="text-xs text-slate-400">Perubahan otomatis terupdate pada database utama HilalEdu dan lembar jadwal KBM.</div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('guru.dashboard') }}" class="px-5 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 text-xs font-bold text-slate-300 hover:text-white transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs transition shadow-lg shadow-amber-500/30 flex items-center gap-2">
                    <i class="bi-check2-circle text-base"></i>
                    <span>Simpan Perubahan Profil</span>
                </button>
            </div>
        </div>

    </form>

</div>
@endsection
