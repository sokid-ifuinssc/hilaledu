<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JadwalPelajaran;
use App\Models\MataPelajaran;
use App\Models\Kurikulum;
use App\Models\User;
use App\Models\PengaturanSekolah;
use Illuminate\Support\Facades\DB;

class JadwalMatrixController extends Controller
{
    /**
     * 9 Rombel Kelas standar SMK Plus Al-Hilal sesuai kolom lembar jadwal
     */
    public static function getDaftarKelas(): array
    {
        return [
            0 => ['nama' => 'X AKL',   'label' => 'X AKL',   'jurusan' => 'AKL',  'badge' => 'bg-red-500 text-white'],
            1 => ['nama' => 'X TKJT',  'label' => 'X TKJT',  'jurusan' => 'TKJT', 'badge' => 'bg-emerald-500 text-white'],
            2 => ['nama' => 'X TO',    'label' => 'X TO',    'jurusan' => 'TO',   'badge' => 'bg-yellow-400 text-slate-900'],
            3 => ['nama' => 'XI AKL',  'label' => 'XI AKL',  'jurusan' => 'AKL',  'badge' => 'bg-red-500 text-white'],
            4 => ['nama' => 'XI TKJT', 'label' => 'XI TKJT', 'jurusan' => 'TKJT', 'badge' => 'bg-emerald-500 text-white'],
            5 => ['nama' => 'XI TO',   'label' => 'XI TO',   'jurusan' => 'TO',   'badge' => 'bg-yellow-400 text-slate-900'],
            6 => ['nama' => 'XII AKL', 'label' => 'XII AKL', 'jurusan' => 'AKL',  'badge' => 'bg-red-500 text-white'],
            7 => ['nama' => 'XII TKJT','label' => 'XII TKJT','jurusan' => 'TKJT', 'badge' => 'bg-emerald-500 text-white'],
            8 => ['nama' => 'XII TO',  'label' => 'XII TO',  'jurusan' => 'TO',   'badge' => 'bg-yellow-400 text-slate-900'],
        ];
    }

    /**
     * Definisi Slot Jam Pelajaran Sesuai Permintaan Pengguna:
     * - Senin, Selasa, Rabu, Kamis, Sabtu: 8 jam @ 45 menit.
     *   Istirahat 1: 30 menit setelah jam ke-4 (10.00 - 10.30)
     *   Istirahat 2: 30 menit setelah jam ke-6 (12.00 - 12.30)
     *   Pulang: 14.00
     * - Jumat: 6 jam @ 30 menit.
     *   Istirahat: 30 menit setelah jam ke-4 (09.00 - 09.30)
     *   Pulang: 10.30 (setelah jam ke-6)
     */
    public static function getPeriodsConfig(): array
    {
        return [
            'reguler' => [
                1 => ['mulai' => '07:00:00', 'selesai' => '07:45:00', 'label' => '07.00 - 07.45'],
                2 => ['mulai' => '07:45:00', 'selesai' => '08:30:00', 'label' => '07.45 - 08.30'],
                3 => ['mulai' => '08:30:00', 'selesai' => '09:15:00', 'label' => '08.30 - 09.15'],
                4 => ['mulai' => '09:15:00', 'selesai' => '10:00:00', 'label' => '09.15 - 10.00'],
                // Istirahat 1: 10.00 - 10.30 (30 menit)
                5 => ['mulai' => '10:30:00', 'selesai' => '11:15:00', 'label' => '10.30 - 11.15'],
                6 => ['mulai' => '11:15:00', 'selesai' => '12:00:00', 'label' => '11.15 - 12.00'],
                // Istirahat 2: 12.00 - 12.30 (30 menit)
                7 => ['mulai' => '12:30:00', 'selesai' => '13:15:00', 'label' => '12.30 - 13.15'],
                8 => ['mulai' => '13:15:00', 'selesai' => '14:00:00', 'label' => '13.15 - 14.00'],
            ],
            'jumat' => [
                1 => ['mulai' => '07:00:00', 'selesai' => '07:30:00', 'label' => '07.00 - 07.30'],
                2 => ['mulai' => '07:30:00', 'selesai' => '08:00:00', 'label' => '07.30 - 08.00'],
                3 => ['mulai' => '08:00:00', 'selesai' => '08:30:00', 'label' => '08.00 - 08.30'],
                4 => ['mulai' => '08:30:00', 'selesai' => '09:00:00', 'label' => '08.30 - 09.00'],
                // Istirahat: 09.00 - 09.30 (30 menit)
                5 => ['mulai' => '09:30:00', 'selesai' => '10:00:00', 'label' => '09.30 - 10.00'],
                6 => ['mulai' => '10:00:00', 'selesai' => '10:30:00', 'label' => '10.00 - 10.30'],
            ]
        ];
    }

    /**
     * 30 Guru Resmi SMK Plus Al-Hilal (Daftar Standar Resmi Sekolah)
     */
    public static function getDefaultGuruResmi(): array
    {
        return [
            1  => ['nama' => 'Mukhammad Mansyur, S.Pt', 'user_id' => 516, 'mapel' => '', 'jabatan' => 'KEPALA SMK'],
            2  => ['nama' => 'Dra. Roisah',              'user_id' => 525, 'mapel' => 'CB, PAI', 'jabatan' => ''],
            3  => ['nama' => 'Khayatun Nufus, S.Pd',     'user_id' => 521, 'mapel' => 'PAI, SBY', 'jabatan' => 'KA PESANTREN'],
            4  => ['nama' => 'NUR AFIFAH, S.Pd',         'user_id' => 522, 'mapel' => 'PAI', 'jabatan' => ''],
            5  => ['nama' => 'Saefullah, S.Pd.I',        'user_id' => 526, 'mapel' => 'PKN', 'jabatan' => 'W HUBIN'],
            6  => ['nama' => 'SITI KOMALA SARI, S.Pd',   'user_id' => 527, 'mapel' => 'PKN', 'jabatan' => ''],
            7  => ['nama' => 'M. NAJJINI, S.Pd',         'user_id' => 517, 'mapel' => 'Sejarah', 'jabatan' => ''],
            8  => ['nama' => 'Dewi Priyatin, S.Pd',      'user_id' => 520, 'mapel' => 'B. Inggris', 'jabatan' => ''],
            9  => ['nama' => 'ALI MUSTOPA, S.Pd',        'user_id' => 523, 'mapel' => 'B. Inggris / MTK', 'jabatan' => ''],
            10 => ['nama' => 'M Sabiqul Huda',           'user_id' => 540, 'mapel' => 'Penjaskes', 'jabatan' => ''],
            11 => ['nama' => 'Ani Mawaddatul Mukhlishoh, S.Pd', 'user_id' => 519, 'mapel' => 'Matematika', 'jabatan' => ''],
            12 => ['nama' => 'Aty Riswanty',            'user_id' => 530, 'mapel' => 'Matematika', 'jabatan' => ''],
            13 => ['nama' => 'MALIKHA, S.Pd',            'user_id' => 534, 'mapel' => 'B. Indo', 'jabatan' => ''],
            14 => ['nama' => 'Fitriyanti S.Pd',          'user_id' => 538, 'mapel' => 'B. Indo', 'jabatan' => ''],
            15 => ['nama' => 'Yuliyanti, S.Pd.I',        'user_id' => 528, 'mapel' => 'IPA', 'jabatan' => 'KA PERPUS'],
            16 => ['nama' => 'Siti Saro, S.Pd',          'user_id' => 537, 'mapel' => 'Seni Budaya', 'jabatan' => ''],
            17 => ['nama' => 'Jefri Handa, A.Md',        'user_id' => 533, 'mapel' => 'Produktif TKJ', 'jabatan' => 'KA BKK'],
            18 => ['nama' => 'VIAUNILLAHI TASYA',        'user_id' => 539, 'mapel' => 'Produktif TKJ', 'jabatan' => ''],
            19 => ['nama' => 'Moh. Roghib, S.Kom',       'user_id' => 518, 'mapel' => 'Produktif TKJ', 'jabatan' => 'KAPROG TKJ'],
            20 => ['nama' => 'Sokid, ST, M.Kom',         'user_id' => 56,  'mapel' => 'Produktif TKJ', 'jabatan' => 'W KURIKULUM'],
            21 => ['nama' => 'Rizki Dwi Safitri, S.Pd',  'user_id' => 524, 'mapel' => 'Produktif PRB', 'jabatan' => 'KAPROG PRB'],
            22 => ['nama' => 'Melinda, SE',              'user_id' => 541, 'mapel' => 'Produktif PRB', 'jabatan' => ''],
            23 => ['nama' => 'Silvi Dwi Manitik S.pd',   'user_id' => 532, 'mapel' => 'Produktif PRB', 'jabatan' => 'KA PKK'],
            24 => ['nama' => 'Ismail Fahmi, ST',         'user_id' => 515, 'mapel' => 'Produktif TKR', 'jabatan' => 'W SARPRAS'],
            25 => ['nama' => 'NIDZOMUDDIN, Amd',         'user_id' => 535, 'mapel' => 'Produktif TKR', 'jabatan' => 'KAPROG TKR'],
            26 => ['nama' => 'Abdul Fatah, ST',          'user_id' => 531, 'mapel' => 'Produktif TKJ', 'jabatan' => 'W KESISWAAN'],
            27 => ['nama' => 'KH. Nadziri Syirodz',      'user_id' => 542, 'mapel' => 'Pesantren', 'jabatan' => ''],
            28 => ['nama' => 'M.Kholid S.Ag',            'user_id' => 536, 'mapel' => 'Pesantren', 'jabatan' => ''],
            29 => ['nama' => 'Ust. Masrudin',            'user_id' => 543, 'mapel' => 'Pesantren', 'jabatan' => ''],
            30 => ['nama' => 'Yunani Elok, S.Pd',        'user_id' => 529, 'mapel' => 'BK', 'jabatan' => 'KA BK'],
            31 => ['nama' => 'Elin Tamaya, SE',          'user_id' => 514, 'mapel' => 'Produktif PRB', 'jabatan' => 'Guru'],
        ];
    }

    /**
     * Dapatkan Daftar Guru Resmi & Penugasan Mapel (Menyesuaikan Data Master & Jadwal KBM)
     */
    public static function getDaftarGuruResmi(): array
    {
        // 1. Ambil data guru aktif dari database (Otomatis)
        $gurus = \App\Models\User::where('is_active', true)
            ->where(function ($q) {
                $q->where('role', 'guru')
                  ->orWhere('tugas_tambahan', 'like', '%Kepala Sekolah%');
            })
            ->orderBy('id')
            ->get();

        $base = [];
        $kode = 1;

        // Palette warna khusus untuk masing-masing guru (Background + Text)
        $colorPalette = [
            'bg-red-500 text-white',
            'bg-blue-600 text-white',
            'bg-emerald-500 text-white',
            'bg-amber-400 text-slate-900',
            'bg-purple-500 text-white',
            'bg-pink-500 text-white',
            'bg-teal-500 text-white',
            'bg-orange-500 text-white',
            'bg-cyan-500 text-slate-900',
            'bg-lime-500 text-slate-900',
            'bg-fuchsia-500 text-white',
            'bg-rose-500 text-white',
            'bg-sky-500 text-white',
            'bg-violet-500 text-white',
            'bg-yellow-300 text-slate-900',
            'bg-green-600 text-white',
            'bg-stone-500 text-white',
            'bg-indigo-500 text-white',
        ];

        foreach ($gurus as $g) {
            // Singkatan Jabatan Khusus sesuai permintaan
            $jabatanSingkat = [];

            if ($g->isKepalaSekolah()) {
                $jabatanSingkat[] = 'Kepsek';
            }
            if ($g->isWakaKurikulum() || $g->isWakaKesiswaan() || $g->isWakaSarpras() || $g->isWakaHubin()) {
                $jabatanSingkat[] = 'Wakasek';
            }
            if ($g->isKaprog()) {
                $jabatanSingkat[] = 'Kaprog';
            }
            if ($g->isWaliKelas()) {
                $jabatanSingkat[] = 'Walas';
            }
            if ($g->isPembinaOsis()) {
                $jabatanSingkat[] = 'Osis';
            }
            // Tambahan Bendahara
            $isBendahara = collect($g->tugas_tambahan)->contains(fn($t) => stripos($t, 'bendahara') !== false);
            if ($isBendahara) {
                $jabatanSingkat[] = 'Bendahara';
            }

            $jabatanStr = implode(', ', array_unique($jabatanSingkat));
            $color = $colorPalette[($kode - 1) % count($colorPalette)];

            $base[$kode] = [
                'nama'    => $g->name,
                'user_id' => $g->id,
                'mapel'   => '-',
                'jabatan' => $jabatanStr,
                'color'   => $color,
            ];
            $kode++;
        }



        // 2. Ambil penugasan mapel aktual yang sedang dijadwalkan di jadwal_pelajarans
        try {
            $assignedMapels = DB::table('jadwal_pelajarans')
                ->join('mata_pelajarans', 'jadwal_pelajarans.mata_pelajaran_id', '=', 'mata_pelajarans.id')
                ->select('jadwal_pelajarans.guru_user_id', 'mata_pelajarans.kode')
                ->distinct()
                ->get()
                ->groupBy('guru_user_id');

            // 3. Sinkronkan mapel aktual (menggunakan KODE mapel)
            foreach ($base as $k => &$info) {
                $uId = $info['user_id'] ?? null;
                if ($uId && isset($assignedMapels[$uId])) {
                    $mapelList = $assignedMapels[$uId]->pluck('kode')->filter()->toArray();
                    if (!empty($mapelList)) {
                        $info['mapel'] = implode(', ', $mapelList);
                    }
                }
            }
            unset($info);
        } catch (\Throwable $e) {
            // Abaikan jika query database gagal
        }

        return $base;
    }

    /**
     * Membangun Matriks Jadwal Dst. 100% DINAMIS Berdasarkan Tabel `jadwal_pelajarans` di Database
     * Jika jadwal belum terinput, maka slot kosong (is_terisi = false) sehingga langsung terlihat
     */
    public static function buildDynamicMatrix($jurusan = 'all', $jenjang = 'all', $kelas = 'all')
    {
        $allRawKelas = static::getDaftarKelas();
        $periodsConfig = static::getPeriodsConfig();
        $guruList = static::getDaftarGuruResmi();

        // 0. Filter Rombel Kelas Berdasarkan Parameter
        $kelasList = [];
        foreach ($allRawKelas as $kInfo) {
            if ($jurusan && $jurusan !== 'all') {
                if (strtoupper($kInfo['jurusan']) !== strtoupper($jurusan)) {
                    continue;
                }
            }
            if ($jenjang && $jenjang !== 'all') {
                if (!str_starts_with(strtoupper($kInfo['nama']), strtoupper($jenjang) . ' ')) {
                    continue;
                }
            }
            if ($kelas && $kelas !== 'all') {
                if (strtoupper(trim($kInfo['nama'])) !== strtoupper(trim($kelas))) {
                    continue;
                }
            }
            $kelasList[] = $kInfo;
        }
        if (empty($kelasList)) {
            $kelasList = $allRawKelas;
        }

        // Buat map balik dari user_id / nama guru ke Kode Guru (1 - 30)
        $kodeByUserId = [];
        $kodeByNama = [];
        foreach ($guruList as $kode => $info) {
            if (!empty($info['user_id'])) {
                $kodeByUserId[$info['user_id']] = $kode;
            }
            $kodeByNama[strtolower(trim($info['nama']))] = $kode;
        }

        // Ambil seluruh jadwal & kurikulum dari database
        $allJadwals = JadwalPelajaran::with(['mataPelajaran', 'guru'])->get();
        $allKurikulums = Kurikulum::with(['mataPelajaran', 'guru'])->where('is_aktif', true)->get();

        // 1. Hitung total jam terjadwal per kelas dan per mapel
        $scheduledJpByClassAndMapel = [];
        $scheduledJpByClass = [];
        foreach ($allJadwals as $j) {
            $jpCount = max(1, ($j->jam_ke_selesai - $j->jam_ke_mulai + 1));
            $cKey = trim($j->kelas);
            $mKey = $j->mata_pelajaran_id;
            $scheduledJpByClassAndMapel[$cKey][$mKey] = ($scheduledJpByClassAndMapel[$cKey][$mKey] ?? 0) + $jpCount;
            $scheduledJpByClass[$cKey] = ($scheduledJpByClass[$cKey] ?? 0) + $jpCount;
        }

        // 2. Analisis Kelebihan Alokasi Jam (Over-Allocation) Tanpa False Alarm Team-Teaching
        $overAllocations = [];
        $checkedPairs = [];
        foreach ($allKurikulums as $k) {
            $cKey = trim($k->kelas);
            $mKey = $k->mata_pelajaran_id;
            $pairKey = "{$cKey}_{$mKey}";
            if (isset($checkedPairs[$pairKey])) continue;
            $checkedPairs[$pairKey] = true;

            $terjadwal = $scheduledJpByClassAndMapel[$cKey][$mKey] ?? 0;
            $totalAlokasi = (int) $allKurikulums->where('kelas', $k->kelas)->where('mata_pelajaran_id', $k->mata_pelajaran_id)->sum('alokasi_jam');
            if ($totalAlokasi > 0 && $terjadwal > $totalAlokasi) {
                $overAllocations[] = [
                    'kelas'        => $k->kelas,
                    'mapel_id'     => $k->mata_pelajaran_id,
                    'mapel'        => $k->mataPelajaran->nama ?? 'Mapel #' . $k->mata_pelajaran_id,
                    'mapel_nama'   => $k->mataPelajaran->nama ?? 'Mapel #' . $k->mata_pelajaran_id,
                    'alokasi'      => $totalAlokasi,
                    'alokasi_jam'  => $totalAlokasi,
                    'terjadwal'    => $terjadwal,
                    'terjadwal_jp' => $terjadwal,
                    'kelebihan'    => $terjadwal - $totalAlokasi,
                    'kelebihan_jp' => $terjadwal - $totalAlokasi,
                    'guru_nama'    => $k->guru->name ?? 'Belum Ditentukan',
                ];
            }
        }

        // 3. Analisis Kelas Melebihi Batas Mingguan (46 JP)
        $classOverLimits = [];
        foreach ($scheduledJpByClass as $cName => $totJp) {
            if ($totJp > 46) {
                $classOverLimits[] = [
                    'kelas'        => $cName,
                    'total_jp'     => $totJp,
                    'max_jp'       => 46,
                    'kelebihan_jp' => $totJp - 46,
                ];
            }
        }

        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $matrix = [];
        $totalSlots = 0;
        $filledSlots = 0;
        $conflictsGuru = [];
        $conflictsKelas = [];

        foreach ($hariList as $hari) {
            $isJumat = ($hari === 'Jumat');
            $periods = $isJumat ? $periodsConfig['jumat'] : $periodsConfig['reguler'];
            $matrix[$hari] = [];

            foreach ($periods as $jamKe => $period) {
                $matrix[$hari][$jamKe] = [
                    'jam_ke'  => $jamKe,
                    'waktu'   => $period['label'],
                    'mulai'   => $period['mulai'],
                    'selesai' => $period['selesai'],
                    'slots'   => [],
                ];

                foreach ($kelasList as $cIdx => $kInfo) {
                    $totalSlots++;
                    $kelasNama = $kInfo['nama'];

                    // Cari semua jadwal yang cocok untuk slot ini
                    $matchedJadwals = [];
                    foreach ($allJadwals as $j) {
                        if ($j->hari !== $hari) continue;

                        $jKelasClean = trim(strtoupper($j->kelas));
                        $kNamaClean = trim(strtoupper($kelasNama));
                        if ($jKelasClean !== $kNamaClean && !str_contains($jKelasClean, $kNamaClean)) {
                            continue;
                        }

                        // Cek jam pelajaran
                        if (!empty($j->jam_ke_mulai) && !empty($j->jam_ke_selesai)) {
                            if ($jamKe >= $j->jam_ke_mulai && $jamKe <= $j->jam_ke_selesai) {
                                $matchedJadwals[] = $j;
                            }
                        } else {
                            $overlapStart = max($j->jam_mulai, $period['mulai']);
                            $overlapEnd = min($j->jam_selesai, $period['selesai']);
                            if ($overlapStart < $overlapEnd && (strtotime($overlapEnd) - strtotime($overlapStart)) >= 900) {
                                $matchedJadwals[] = $j;
                            }
                        }
                    }

                    if (!empty($matchedJadwals)) {
                        $filledSlots++;
                        $matchedJadwal = $matchedJadwals[0];
                        $isBentrokKelas = count($matchedJadwals) > 1;

                        if ($isBentrokKelas) {
                            $conflictsKelas[] = [
                                'hari'     => $hari,
                                'jam_ke'   => $jamKe,
                                'waktu'    => $period['label'],
                                'kelas'    => $kelasNama,
                                'jadwals'  => array_map(fn($item) => ($item->mataPelajaran->nama ?? 'Mapel') . ' (' . ($item->guru->name ?? 'Guru') . ')', $matchedJadwals),
                            ];
                        }

                        $guruId = $matchedJadwal->guru_user_id;
                        $guruName = $matchedJadwal->guru->name ?? 'Guru';
                        $kodeGuru = $kodeByUserId[$guruId] ?? ($kodeByNama[strtolower(trim($guruName))] ?? substr($guruName, 0, 3));
                        $mapelKode = $matchedJadwal->mataPelajaran->kode ?? 'MP';
                        $mapelNama = $matchedJadwal->mataPelajaran->nama ?? 'Mapel';

                        // Cek apakah mapel ini melebihi alokasi kurikulum
                        $cKey = trim($matchedJadwal->kelas);
                        $mKey = $matchedJadwal->mata_pelajaran_id;
                        $totJp = $scheduledJpByClassAndMapel[$cKey][$mKey] ?? 0;
                        $totAlokasi = (int) $allKurikulums->where('kelas', $cKey)->where('mata_pelajaran_id', $mKey)->sum('alokasi_jam');
                        $isOverAlokasi = ($totAlokasi > 0 && $totJp > $totAlokasi);
                        $overDiff = $isOverAlokasi ? ($totJp - $totAlokasi) : 0;

                        // Tentukan background badge berdasarkan warna unik guru di legenda
                        $bg = $guruList[$kodeGuru]['color'] ?? 'bg-gray-500 text-white';

                        $matrix[$hari][$jamKe]['slots'][$cIdx] = [
                            'is_terisi'         => true,
                            'jadwal_id'         => $matchedJadwal->id,
                            'kode'              => $kodeGuru,
                            'guru_id'           => $guruId,
                            'mapel'             => $mapelKode,
                            'mapel_nama'        => $mapelNama,
                            'guru_nama'         => $guruName,
                            'is_locked'         => $matchedJadwal->is_locked,
                            'bg'                => $bg,
                            'ruang'             => $matchedJadwal->ruang ?? '-',
                            'jam_mulai'         => $matchedJadwal->jam_mulai,
                            'jam_selesai'       => $matchedJadwal->jam_selesai,
                            'jam_ke'            => $jamKe,
                            'kelas'             => $kelasNama,
                            'hari'              => $hari,
                            'is_bentrok_guru'   => false,
                            'is_gabungan'       => false,
                            'is_bentrok_kelas'  => $isBentrokKelas,
                            'is_over_alokasi'   => $isOverAlokasi,
                            'over_alokasi_diff' => $overDiff,
                        ];
                    } else {
                        // KOSONG / BELUM TERINPUT
                        $matrix[$hari][$jamKe]['slots'][$cIdx] = [
                            'is_terisi'         => false,
                            'jadwal_id'         => null,
                            'kode'              => '-',
                            'guru_id'           => null,
                            'mapel'             => '-',
                            'mapel_nama'        => 'Belum Terinput',
                            'guru_nama'         => 'Belum Ditentukan',
                            'is_locked'         => false,
                            'bg'                => 'bg-slate-50 text-slate-400 hover:bg-amber-50 hover:text-amber-700',
                            'ruang'             => '-',
                            'jam_mulai'         => $period['mulai'],
                            'jam_selesai'       => $period['selesai'],
                            'jam_ke'            => $jamKe,
                            'kelas'             => $kelasNama,
                            'hari'              => $hari,
                            'is_bentrok_guru'   => false,
                            'is_gabungan'       => false,
                            'is_bentrok_kelas'  => false,
                            'is_over_alokasi'   => false,
                            'over_alokasi_diff' => 0,
                        ];
                    }
                }

                // 4. Deteksi Bentrok Guru vs Sesi Kuliah Gabungan Kepesantrenan
                $guruInSlot = [];
                foreach ($matrix[$hari][$jamKe]['slots'] as $cIdx => $slot) {
                    if ($slot['is_terisi'] && !empty($slot['guru_id'])) {
                        $guruInSlot[$slot['guru_id']][] = $cIdx;
                    }
                }

                foreach ($guruInSlot as $gId => $classIndices) {
                    if (count($classIndices) > 1) {
                        $firstSlot = $matrix[$hari][$jamKe]['slots'][$classIndices[0]];
                        // Cek apakah ini sesi kepesantrenan gabungan (KH. Nadziri / Kode 27 / Mapel AKHLAK)
                        $isGabunganPesantren = ($gId == 542 || in_array(strtoupper($firstSlot['mapel']), ['AKHLAK', 'PESANTREN']));

                        if ($isGabunganPesantren) {
                            foreach ($classIndices as $ci) {
                                $matrix[$hari][$jamKe]['slots'][$ci]['is_gabungan'] = true;
                            }
                        } else {
                            $classesInvolved = [];
                            foreach ($classIndices as $ci) {
                                $matrix[$hari][$jamKe]['slots'][$ci]['is_bentrok_guru'] = true;
                                $classesInvolved[] = $matrix[$hari][$jamKe]['slots'][$ci]['kelas'];
                            }
                            $conflictsGuru[] = [
                                'hari'      => $hari,
                                'jam_ke'    => $jamKe,
                                'waktu'     => $period['label'],
                                'guru_id'   => $gId,
                                'guru_nama' => $firstSlot['guru_nama'],
                                'classes'   => $classesInvolved,
                            ];
                        }
                    }
                }
            }
        }

        return [
            'matrix'           => $matrix,
            'kelasList'        => $kelasList,
            'periods'          => $periodsConfig,
            'totalSlots'       => $totalSlots,
            'filledSlots'      => $filledSlots,
            'emptySlots'       => $totalSlots - $filledSlots,
            'conflicts'        => [
                'guru'  => $conflictsGuru,
                'kelas' => $conflictsKelas,
                'total' => count($conflictsGuru) + count($conflictsKelas),
            ],
            'overAllocations'  => $overAllocations,
            'classOverLimits'  => $classOverLimits,
        ];
    }

    /**
     * Tampilan satu halaman visual Matriks Jadwal Resmi (100% Terhubung Database)
     */
    public function matrix(Request $request)
    {
        $jurusan = $request->query('jurusan', 'all');
        $jenjang = $request->query('jenjang', 'all');
        $kelas = $request->query('kelas', 'all');

        $settings = PengaturanSekolah::getAllSettings();
        $guruList = static::getDaftarGuruResmi();
        $matrixData = static::buildDynamicMatrix($jurusan, $jenjang, $kelas);

        return view('admin.jadwal.matrix', compact('settings', 'guruList', 'matrixData', 'jurusan', 'jenjang', 'kelas'));
    }

    /**
     * Tampilan cetak bersih dokumen jadwal resmi (Landscape A4)
     */
    public function printMatrix(Request $request)
    {
        $jurusan = $request->query('jurusan', 'all');
        $jenjang = $request->query('jenjang', 'all');
        $kelas = $request->query('kelas', 'all');

        $settings = PengaturanSekolah::getAllSettings();
        $guruList = static::getDaftarGuruResmi();
        $matrixData = static::buildDynamicMatrix($jurusan, $jenjang, $kelas);

        return view('admin.jadwal.matrix_print', compact('settings', 'guruList', 'matrixData', 'jurusan', 'jenjang', 'kelas'));
    }
}

