@extends('layouts.app')

@section('title', 'Buat Modul Ajar Harian (RPP) - Minggu Efektif')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div>
        <div class="text-xs text-slate-500 mb-1">
            <a href="{{ route('guru.rencana-pembelajaran.index', ['mapel_id' => $mapelId, 'tingkat' => $tingkat, 'tab' => 'rpp']) }}" class="text-blue-600 hover:underline inline-flex items-center gap-1 font-semibold">
                <i class="bi-arrow-left"></i> Kembali ke Rencana Ajar
            </a>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <i class="bi-calendar-check text-blue-600"></i>
                    <span>Susun Modul Ajar Harian (RPP)</span>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Tanggal pelaksanaan tergenerate otomatis dari <strong>Minggu Efektif</strong> mengajar Anda &mdash; guru tidak perlu input tanggal manual lagi.
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 self-start sm:self-auto">
                <i class="bi-lightning-charge-fill text-amber-500"></i> Terintegrasi Minggu Efektif
            </span>
        </div>
    </div>

    <form method="POST" action="{{ route('guru.rencana-pembelajaran.rpp.store') }}" class="space-y-6" id="formRpp">
        @csrf
        <input type="hidden" name="mapel_id" value="{{ $mapelId }}">
        <input type="hidden" name="tingkat" value="{{ $tingkat }}">

        <!-- Card 1: Slot Jadwal Mengajar & Tanggal Efektif Tergenerate -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-sm text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-black">1</span>
                    <span>Pilih Mapel / Jadwal &amp; Tanggal Minggu Efektif</span>
                </h3>
                <span id="badgeTotalPertemuan" class="text-[11px] font-semibold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
                    Memuat tanggal efektif...
                </span>
            </div>

            <!-- Banner Panduan Otomatis -->
            <div class="p-3.5 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 text-xs text-blue-900 flex items-start gap-2.5">
                <i class="bi-info-circle-fill text-blue-600 text-sm mt-0.5 shrink-0"></i>
                <div class="space-y-0.5">
                    <p class="font-bold">Tanggal terpetakan otomatis dari Minggu Efektif &amp; Kalender Akademik!</p>
                    <p class="text-blue-700/90 text-[11px]">
                        Hari libur nasional, libur sekolah, dan jeda semester otomatis dilewati. Anda cukup memilih jadwal mengajar dan tanggal efektif yang tersedia, nomor pertemuan akan otomatis sinkron.
                    </p>
                </div>
            </div>

            <!-- Pilihan Semester -->
            <div class="flex flex-wrap items-center gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-xs">
                <span class="font-bold text-slate-700 flex items-center gap-1 mr-1">
                    <i class="bi-calendar3 text-blue-600"></i> Semester RPP:
                </span>
                <a href="{{ request()->fullUrlWithQuery(['semester' => 'ganjil']) }}" 
                   class="px-3 py-1 rounded-lg font-bold transition {{ ($semester ?? 'ganjil') === 'ganjil' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' }}">
                    Semester Ganjil (Jul - Des)
                </a>
                <a href="{{ request()->fullUrlWithQuery(['semester' => 'genap']) }}" 
                   class="px-3 py-1 rounded-lg font-bold transition {{ ($semester ?? '') === 'genap' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' }}">
                    Semester Genap (Jan - Jun)
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <!-- Dropdown Jadwal Mengajar Guru -->
                <div>
                    <label for="jadwal_pelajaran_id" class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                        <i class="bi-journal-bookmark text-blue-600"></i>
                        <span>Mata Pelajaran &amp; Jadwal Mengajar</span>
                    </label>
                    <select name="jadwal_pelajaran_id" id="jadwal_pelajaran_id" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-semibold text-slate-800 focus:ring-2 focus:ring-blue-600 transition">
                        @foreach($jadwals as $j)
                        <option value="{{ $j->id }}" {{ $selectedJadwalId == $j->id ? 'selected' : '' }}>
                            {{ $j->mataPelajaran->nama ?? 'Mapel' }} &bull; Kelas {{ $j->kelas }} &bull; {{ $j->hari }} ({{ substr($j->jam_mulai,0,5) }}-{{ substr($j->jam_selesai,0,5) }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Dropdown Tanggal Tergenerate dari Minggu Efektif -->
                <div>
                    <label for="tanggal_rencana" class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                        <i class="bi-calendar-event text-emerald-600"></i>
                        <span>Tanggal Pelaksanaan (Minggu Efektif)</span>
                    </label>
                    <select name="tanggal_rencana" id="tanggal_rencana" required class="w-full p-3 bg-emerald-50/50 border border-emerald-300 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-emerald-600 transition">
                        <option value="">-- Memuat Tanggal Efektif --</option>
                    </select>
                    <p id="helperTanggal" class="text-[10px] text-slate-500 mt-1 flex items-center gap-1">
                        <i class="bi-check-circle text-emerald-600"></i>
                        <span>Pilih tanggal pertemuan yang hendak Anda susun RPP-nya.</span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <!-- Pertemuan Ke- (Auto sync) -->
                <div>
                    <label for="pertemuan_ke" class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i class="bi-hash text-blue-600"></i>
                            <span>Pertemuan Ke-</span>
                        </span>
                        <span class="text-[10px] text-emerald-600 font-semibold normal-case">Otomatis dari kalender</span>
                    </label>
                    <input type="number" name="pertemuan_ke" id="pertemuan_ke" value="1" min="1" required class="w-full p-3 bg-slate-100 border border-slate-300 rounded-xl font-black text-blue-700 text-sm focus:ring-2 focus:ring-blue-600">
                </div>

                <!-- Tautkan Tujuan Pembelajaran -->
                <div>
                    <label for="tujuan_pembelajaran_id" class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                        <i class="bi-bullseye text-indigo-600"></i>
                        <span>Tautkan Tujuan Pembelajaran (TP)</span>
                    </label>
                    <select name="tujuan_pembelajaran_id" id="tujuan_pembelajaran_id" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 transition">
                        <option value="">-- Pilih Tujuan Pembelajaran (Opsional) --</option>
                        @foreach($tps as $tp)
                        <option value="{{ $tp->id }}">
                            {{ $tp->kode_tp }} &bull; {{ \Illuminate\Support\Str::limit($tp->deskripsi, 65) }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Card 2: Materi & Skenario KBM -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
            <h3 class="font-extrabold text-sm text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-black">2</span>
                <span>Rincian Materi &amp; Skenario KBM</span>
            </h3>

            <div class="text-xs">
                <label for="materi_pokok" class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                    <i class="bi-card-heading text-blue-600"></i>
                    <span>Materi Pokok / Topik Pembahasan Hari Itu</span>
                </label>
                <input type="text" name="materi_pokok" id="materi_pokok" placeholder="Contoh: Konfigurasi Subnetting dan VLAN pada Switch Managed..." required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-bold text-slate-900 focus:ring-2 focus:ring-blue-600">
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">A. Kegiatan Pendahuluan (10-15 Menit)</label>
                    <textarea name="aktivitas_pendahuluan" rows="2" placeholder="Salam, doa bersama, presensi kehadiran, apersepsi keterkaitan materi sebelumnya, dan penyampaian tujuan pembelajaran..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl">Guru membuka pembelajaran dengan salam dan doa, melakukan apersepsi, memotivasi peserta didik, serta menyampaikan tujuan pembelajaran dan asesmen yang akan dicapai.</textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">B. Kegiatan Inti (Eksplorasi, Praktik &amp; Kolaborasi)</label>
                    <textarea name="aktivitas_inti" rows="4" placeholder="Skenario pembelajaran aktif, diskusi kelompok, demonstrasi guru, unjuk kerja praktik siswa, presentasi hasil..." required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl">1. Peserta didik mengamati demonstrasi materi dan panduan jobsheet/modul ajar.
2. Peserta didik dibagi menjadi kelompok kecil untuk mempraktikkan materi dan lembar kerja.
3. Guru memfasilitasi, membimbing, dan melakukan asesmen formatif berkeliling.
4. Setiap perwakilan kelompok mempresentasikan hasil temuan praktikum di depan kelas.</textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">C. Kegiatan Penutup (10 Menit)</label>
                    <textarea name="aktivitas_penutup" rows="2" placeholder="Refleksi pembelajaran bersama siswa, kesimpulan, tindak lanjut penugasan, dan doa penutup..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl">Guru bersama peserta didik menyimpulkan intisari materi, melakukan refleksi pembelajaran hari ini, memberikan tindak lanjut materi selanjutnya, dan menutup dengan doa bersama.</textarea>
                </div>
            </div>
        </div>

        <!-- Card 3: Asesmen, Media & Sumber Belajar -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
            <h3 class="font-extrabold text-sm text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-black">3</span>
                <span>Bentuk Asesmen &amp; Sumber Belajar</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Bentuk Asesmen / Penilaian</label>
                    <input type="text" name="bentuk_asesmen" value="Formatif (Observasi Kinerja &amp; Lembar Kerja Siswa)" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Media &amp; Sumber Belajar</label>
                    <input type="text" name="media_sumber" placeholder="Slide PPT, Jobsheet LKPD, Video Tutorial, Perangkat Lab..." value="Modul Digital SMK Plus Al-Hilal, LKPD Siswa, Lab Komputer" class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl font-medium">
                </div>
            </div>

            <div class="text-xs">
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Tambahan (Opsional)</label>
                <textarea name="catatan" rows="2" placeholder="Catatan khusus kesiapan sarana prasarana, diferensiasi belajar siswa, remedial, dll..." class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl"></textarea>
            </div>
        </div>

        <!-- Tombol Submit -->
        <div class="flex items-center justify-end gap-3 pb-8">
            <a href="{{ route('guru.rencana-pembelajaran.index', ['mapel_id' => $mapelId, 'tingkat' => $tingkat, 'tab' => 'rpp']) }}" class="px-5 py-3 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-blue-600/30 transition flex items-center gap-2">
                <i class="bi-check2-circle text-base"></i>
                <span>Simpan Modul Ajar Harian (RPP)</span>
            </button>
        </div>

    </form>

</div>

<script>
    // Peta tanggal efektif mengajar hasil komputasi Minggu Efektif
    const effectiveDatesMap = @json($effectiveDatesMap ?? []);

    function populateTanggalOptions(jadwalId) {
        const selectTanggal = document.getElementById('tanggal_rencana');
        const badgeTotal = document.getElementById('badgeTotalPertemuan');
        const inputPertemuan = document.getElementById('pertemuan_ke');
        const helperText = document.getElementById('helperTanggal');

        if (!selectTanggal) return;

        selectTanggal.innerHTML = '';
        const dates = effectiveDatesMap[jadwalId] || [];

        if (dates.length === 0) {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = '-- Belum ada kalender akademik aktif untuk jadwal ini --';
            selectTanggal.appendChild(opt);
            badgeTotal.textContent = '0 Pertemuan Efektif';
            return;
        }

        badgeTotal.textContent = `${dates.length} Pertemuan Efektif KBM`;

        let firstUnfilledIndex = -1;

        dates.forEach((item, index) => {
            const opt = document.createElement('option');
            opt.value = item.tanggal;
            opt.setAttribute('data-pertemuan', item.pertemuan_ke);
            opt.setAttribute('data-materi', item.materi_pokok || '');
            opt.setAttribute('data-sudah-rpp', item.sudah_ada_rpp ? '1' : '0');

            if (item.sudah_ada_rpp) {
                opt.textContent = `Pertemuan ${item.pertemuan_ke} • ${item.tanggal_format} ✓ (Sudah ada RPP: ${item.materi_pokok || 'Tersusun'})`;
                opt.className = 'text-slate-400 bg-slate-50';
            } else {
                opt.textContent = `Pertemuan ${item.pertemuan_ke} • ${item.tanggal_format} (Tersedia)`;
                opt.className = 'font-bold text-slate-900';
                if (firstUnfilledIndex === -1) {
                    firstUnfilledIndex = index;
                }
            }

            selectTanggal.appendChild(opt);
        });

        // Pilih pertemuan pertama yang belum dibuatkan RPP-nya secara otomatis
        const targetIndex = firstUnfilledIndex !== -1 ? firstUnfilledIndex : 0;
        selectTanggal.selectedIndex = targetIndex;

        // Sinkronkan pertemuan ke
        syncPertemuanKe();
    }

    function syncPertemuanKe() {
        const selectTanggal = document.getElementById('tanggal_rencana');
        const inputPertemuan = document.getElementById('pertemuan_ke');
        const inputMateri = document.getElementById('materi_pokok');

        if (!selectTanggal || !inputPertemuan) return;

        const selectedOption = selectTanggal.options[selectTanggal.selectedIndex];
        if (selectedOption) {
            const pertemuan = selectedOption.getAttribute('data-pertemuan');
            if (pertemuan) {
                inputPertemuan.value = pertemuan;
            }

            const sudahRpp = selectedOption.getAttribute('data-sudah-rpp') === '1';
            const existingMateri = selectedOption.getAttribute('data-materi');
            if (sudahRpp && existingMateri && (!inputMateri.value || inputMateri.value.trim() === '')) {
                inputMateri.value = existingMateri;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const selectJadwal = document.getElementById('jadwal_pelajaran_id');
        const selectTanggal = document.getElementById('tanggal_rencana');

        if (selectJadwal) {
            populateTanggalOptions(selectJadwal.value);

            selectJadwal.addEventListener('change', function () {
                populateTanggalOptions(this.value);
            });
        }

        if (selectTanggal) {
            selectTanggal.addEventListener('change', function () {
                syncPertemuanKe();
            });
        }
    });
</script>
@endsection

