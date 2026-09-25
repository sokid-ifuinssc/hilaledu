<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\JadwalPelajaran;
use App\Models\MataPelajaran;
use App\Models\Kurikulum;
use App\Models\LaporanKbm;
use App\Models\AbsensiGuru;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\TahunAjaran;
use App\Models\PengaturanSekolah;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $totalJadwal = JadwalPelajaran::count();
        $totalGuru = User::where('role', 'guru')->count();
        $totalSiswa = User::where('role', 'siswa')->count();
        $totalMapel = MataPelajaran::count();
        $totalLaporanKbm = LaporanKbm::count();
        $totalAbsensiGuru = AbsensiGuru::count();

        $jadwalTerbaru = JadwalPelajaran::with(['mataPelajaran', 'guru'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->take(10)
            ->get();

        $laporanKbmTerbaru = LaporanKbm::with(['guru', 'jadwal.mataPelajaran'])
            ->latest()
            ->take(6)
            ->get();

        // 1. Papan Monitoring Kelas Real-Time
        $monitoringPayload = app(\App\Services\MonitoringKelasService::class)->getMonitoringKelasData(
            $request->input('hari'),
            $request->input('jam'),
            $request->input('tanggal')
        );

        $monitoringKelas = $monitoringPayload['monitoringKelas'];
        $rekapMonitoring = $monitoringPayload['rekap'];
        $hariPantau      = $monitoringPayload['hariPantau'];
        $jamPantau       = $monitoringPayload['jamPantau'];
        $isSimulasi      = $monitoringPayload['isSimulasi'];
        $periodInfo      = $monitoringPayload['periodInfo'];

        // 2. Agenda Sekolah Terdekat (Rentang 1 Minggu Sebelum Hari H)
        $todayDate = date('Y-m-d');
        $hMinus1 = \Carbon\Carbon::parse($todayDate)->subDay()->toDateString();
        $hPlus7  = \Carbon\Carbon::parse($todayDate)->addDays(7)->toDateString();

        $kegiatanTerbaru = \App\Models\KegiatanSekolah::with(['author'])
            ->withCount(['presensis as total_kehadiran' => function ($q) {
                $q->where('status', 'hadir');
            }])
            ->where('is_active', true)
            ->whereBetween('tanggal_kegiatan', [$hMinus1, $hPlus7])
            ->orderBy('tanggal_kegiatan', 'asc')
            ->take(6)
            ->get();

        if ($kegiatanTerbaru->isEmpty()) {
            $kegiatanTerbaru = \App\Models\KegiatanSekolah::with(['author'])
                ->withCount(['presensis as total_kehadiran' => function ($q) {
                    $q->where('status', 'hadir');
                }])
                ->where('is_active', true)
                ->orderBy('tanggal_kegiatan', 'desc')
                ->take(3)
                ->get();
        }

        // 3. Ringkasan Presensi Guru Bulan Ini
        $currentMonth = date('Y-m');
        $absensiBulanIni = AbsensiGuru::where('tanggal', 'like', "{$currentMonth}%")->get();
        $totalHadirBulanIni = $absensiBulanIni->whereIn('status', ['hadir', 'terlambat', 'tugas_luar'])->count();
        $totalSesiBulanIni = $absensiBulanIni->count();
        $persenKehadiranBulanIni = $totalSesiBulanIni > 0 ? round(($totalHadirBulanIni / $totalSesiBulanIni) * 100, 1) : 100;

        return view('admin.dashboard', compact(
            'totalJadwal',
            'totalGuru',
            'totalSiswa',
            'totalMapel',
            'totalLaporanKbm',
            'totalAbsensiGuru',
            'jadwalTerbaru',
            'laporanKbmTerbaru',
            'monitoringKelas',
            'rekapMonitoring',
            'hariPantau',
            'jamPantau',
            'isSimulasi',
            'periodInfo',
            'kegiatanTerbaru',
            'persenKehadiranBulanIni',
            'totalHadirBulanIni',
            'totalSesiBulanIni'
        ));
    }

    // =========================================================
    // Manajemen Jadwal Pelajaran (Admin Mengatur Jadwal Guru)
    // =========================================================
    public function jadwalIndex(Request $request)
    {
        $hari = $request->query('hari');
        $kelas = $request->query('kelas');
        $guruId = $request->query('guru_id');

        $query = JadwalPelajaran::with(['mataPelajaran', 'guru'])
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('jam_mulai');

        if ($hari) $query->where('hari', $hari);
        if ($kelas) $query->where('kelas', $kelas);
        if ($guruId) $query->where('guru_user_id', $guruId);

        $jadwals = $query->paginate(20);
        $kelasList = Kelas::where('is_aktif', true)->orderBy('nama')->get();
        $guruList = User::where('role', 'guru')->where('is_active', true)->orderBy('name')->get();
        $mapels = MataPelajaran::where('is_aktif', true)->orderBy('nama')->get();

        // Ambil Data Kurikulum per Kelas untuk mempermudah auto-fill penugasan KBM
        $kurikulumByKelas = Kurikulum::with(['mataPelajaran', 'guru'])
            ->where('is_aktif', true)
            ->get()
            ->groupBy('kelas')
            ->map(function ($items) {
                return $items->map(function ($k) {
                    return [
                        'id'                => $k->id,
                        'mata_pelajaran_id' => $k->mata_pelajaran_id,
                        'mapel_nama'        => $k->mataPelajaran->nama ?? 'Mapel #' . $k->mata_pelajaran_id,
                        'mapel_kode'        => $k->mataPelajaran->kode ?? '',
                        'guru_user_id'      => $k->guru_user_id,
                        'guru_nama'         => $k->guru->name ?? 'Belum Ditentukan',
                        'alokasi_jam'       => $k->alokasi_jam,
                        'terjadwal_jp'      => $k->terjadwal_jp,
                        'sisa_jp'           => $k->sisa_jp,
                        'status_jadwal'     => $k->status_jadwal,
                    ];
                })->values();
            });

        // Analisis kelebihan alokasi jam per kelas & mapel
        $overAllocations = [];
        $allJadwalsCount = JadwalPelajaran::all();
        $scheduledJpByClassAndMapel = [];
        foreach ($allJadwalsCount as $j) {
            $jp = max(1, ($j->jam_ke_selesai - $j->jam_ke_mulai + 1));
            $c = trim($j->kelas);
            $m = $j->mata_pelajaran_id;
            $scheduledJpByClassAndMapel[$c][$m] = ($scheduledJpByClassAndMapel[$c][$m] ?? 0) + $jp;
        }

        $allKurikulums = Kurikulum::with(['mataPelajaran', 'guru'])->where('is_aktif', true)->get();
        $checkedPairs = [];
        foreach ($allKurikulums as $k) {
            $c = trim($k->kelas);
            $m = $k->mata_pelajaran_id;
            $pairKey = "{$c}_{$m}";
            if (isset($checkedPairs[$pairKey])) continue;
            $checkedPairs[$pairKey] = true;

            $terjadwal = $scheduledJpByClassAndMapel[$c][$m] ?? 0;
            $totalAlokasi = (int) $allKurikulums->where('kelas', $k->kelas)->where('mata_pelajaran_id', $k->mata_pelajaran_id)->sum('alokasi_jam');
            if ($totalAlokasi > 0 && $terjadwal > $totalAlokasi) {
                $overAllocations[] = [
                    'kelas'        => $k->kelas,
                    'mapel'        => $k->mataPelajaran->nama ?? 'Mapel',
                    'mapel_nama'   => $k->mataPelajaran->nama ?? 'Mapel',
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

        $editItem = null;
        if ($request->filled('edit')) {
            $editItem = JadwalPelajaran::with(['mataPelajaran', 'guru'])->find($request->input('edit'));
        }

        return view('admin.jadwal.index', compact('jadwals', 'kelasList', 'guruList', 'mapels', 'hari', 'kelas', 'guruId', 'kurikulumByKelas', 'overAllocations', 'editItem'));
    }
    /**
     * Auto Generate Jadwal Pelajaran (Draft) dari Kurikulum
     */
        public function autoGenerateJadwal(Request $request)
    {
        $tahunAjaran = \App\Models\PengaturanSekolah::getActiveTahunAjaran();
        $semester = \App\Models\PengaturanSekolah::getActiveSemester();

        DB::beginTransaction();
        try {
            // 1. Hapus jadwal yang tidak dikunci pada semester aktif
            \App\Models\JadwalPelajaran::where('tahun_ajaran', $tahunAjaran)
                ->where('semester', $semester)
                ->where('is_locked', false)
                ->delete();

            // 2. Ambil semua kurikulum (alokasi)
            $kurikulums = \App\Models\Kurikulum::with('mataPelajaran')
                ->where('is_aktif', true)
                ->get();

            // Kelompokkan per kelas
            $kurikulumsPerKelas = $kurikulums->groupBy('kelas');

            $created = 0;
            $hariTersedia = ['Senin' => 8, 'Selasa' => 8, 'Rabu' => 8, 'Kamis' => 8, 'Jumat' => 6];

            foreach ($kurikulumsPerKelas as $kelas => $kurs) {
                $tingkat = substr($kelas, 0, strpos($kelas, ' ')); // "X AKL" -> "X"
                if (!$tingkat) $tingkat = $kelas;

                // Inisialisasi slot (false = kosong, true = terisi)
                $slots = [];
                foreach ($hariTersedia as $hari => $maxJam) {
                    for ($j = 1; $j <= $maxJam; $j++) {
                        $slots[$hari][$j] = false;
                    }
                }

                // Tandai slot yang sudah ada (is_locked = true)
                $lockedJadwals = \App\Models\JadwalPelajaran::where('tahun_ajaran', $tahunAjaran)
                    ->where('semester', $semester)
                    ->where('kelas', $kelas)
                    ->where('is_locked', true)
                    ->get();

                foreach ($lockedJadwals as $lj) {
                    if (isset($slots[$lj->hari])) {
                        for ($j = $lj->jam_ke_mulai; $j <= $lj->jam_ke_selesai; $j++) {
                            $slots[$lj->hari][$j] = true;
                        }
                    }
                }

                // RULE 1: Kosongkan Senin Jam 1 (Upacara)
                $slots['Senin'][1] = true;

                // Hitung sisa jam untuk setiap kurikulum
                $sisaJam = [];
                foreach ($kurs as $kur) {
                    $sudahTerjadwal = $lockedJadwals->where('mata_pelajaran_id', $kur->mata_pelajaran_id)->sum(function($q) {
                        return ($q->jam_ke_selesai - $q->jam_ke_mulai) + 1;
                    });
                    $sisa = $kur->alokasi_jam - $sudahTerjadwal;
                    if ($sisa > 0) {
                        $sisaJam[$kur->id] = [
                            'kurikulum' => $kur,
                            'sisa' => $sisa
                        ];
                    }
                }

                // RULE 2: Mulok (Selasa 3-4 untuk X, Rabu 3-4 untuk XI, Kamis 3-4 untuk XII)
                $mulokHari = null;
                if ($tingkat === 'X') $mulokHari = 'Selasa';
                elseif ($tingkat === 'XI') $mulokHari = 'Rabu';
                elseif ($tingkat === 'XII') $mulokHari = 'Kamis';

                if ($mulokHari) {
                    // Cari mapel mulok (sub_kategori = 'Muatan Lokal' atau nama mengandung 'Muatan Lokal' atau 'Mulok')
                    foreach ($sisaJam as $id => &$data) {
                        $mapel = $data['kurikulum']->mataPelajaran;
                        if ($mapel && ($mapel->sub_kategori === 'Muatan Lokal' || stripos($mapel->nama, 'Mulok') !== false || stripos($mapel->nama, 'Muatan Lokal') !== false)) {
                            // Jadwalkan 2 jam di hari mulok
                            if (!$slots[$mulokHari][3] && !$slots[$mulokHari][4] && $data['sisa'] >= 2) {
                                $this->buatJadwal($data['kurikulum'], $mulokHari, 3, 4, true);
                                $slots[$mulokHari][3] = true;
                                $slots[$mulokHari][4] = true;
                                $data['sisa'] -= 2;
                                $created++;
                            }
                            break; // Hanya 1 mulok per kelas (asumsi)
                        }
                    }
                }

                // RULE 3: 3 Jam -> Prioritas Senin 2,3,4
                if (!$slots['Senin'][2] && !$slots['Senin'][3] && !$slots['Senin'][4]) {
                    foreach ($sisaJam as $id => &$data) {
                        if ($data['sisa'] == 3) {
                            $this->buatJadwal($data['kurikulum'], 'Senin', 2, 4, true);
                            $slots['Senin'][2] = true;
                            $slots['Senin'][3] = true;
                            $slots['Senin'][4] = true;
                            $data['sisa'] -= 3;
                            $created++;
                            break; // Hanya 1 mapel yang bisa menempati
                        }
                    }
                }

                // Sisa jam diacak
                $hariKeys = array_keys($hariTersedia);

                foreach ($sisaJam as $id => &$data) {
                    $kur = $data['kurikulum'];
                    $guruId = $kur->guru_user_id ?: ($kur->mataPelajaran->guru_user_id ?? null);

                    while ($data['sisa'] > 0) {
                        // Tentukan block size
                        $blockSize = 1;
                        if ($data['sisa'] >= 4) $blockSize = 2; // Pecah 4 jadi 2+2
                        elseif ($data['sisa'] >= 2) $blockSize = 2;
                        else $blockSize = 1;

                        // Coba cari slot kosong
                        $found = false;
                        shuffle($hariKeys); // Acak urutan hari

                        foreach ($hariKeys as $hari) {
                            $maxJam = $hariTersedia[$hari];
                            // Acak jam mulai (dari 1 sampai maxJam - blockSize + 1)
                            $possibleStarts = range(1, $maxJam - $blockSize + 1);
                            shuffle($possibleStarts);

                            foreach ($possibleStarts as $start) {
                                $end = $start + $blockSize - 1;
                                
                                // Cek apakah slot di kelas ini kosong
                                $isKosong = true;
                                for ($j = $start; $j <= $end; $j++) {
                                    if ($slots[$hari][$j]) {
                                        $isKosong = false;
                                        break;
                                    }
                                }

                                if ($isKosong) {
                                    // Cek bentrok guru (jika guru ditentukan)
                                    $bentrokGuru = false;
                                    if ($guruId) {
                                        // Cari jadwal guru di hari dan jam yang bersinggungan
                                        $times = \App\Models\JadwalPelajaran::calculateTimesFromJamKe($hari, $start, $end);
                                        $jamMulai = $times['jam_mulai'];
                                        $jamSelesai = $times['jam_selesai'];

                                        $bentrok = \App\Models\JadwalPelajaran::where('tahun_ajaran', $tahunAjaran)
                                            ->where('semester', $semester)
                                            ->where('hari', $hari)
                                            ->where('guru_user_id', $guruId)
                                            ->where(function ($q) use ($jamMulai, $jamSelesai) {
                                                $q->where('jam_mulai', '<', $jamSelesai)
                                                  ->where('jam_selesai', '>', $jamMulai);
                                            })->exists();
                                        
                                        if ($bentrok) $bentrokGuru = true;
                                    }

                                    if (!$bentrokGuru) {
                                        // Berhasil nemu slot!
                                        $this->buatJadwal($kur, $hari, $start, $end, false);
                                        for ($j = $start; $j <= $end; $j++) {
                                            $slots[$hari][$j] = true;
                                        }
                                        $data['sisa'] -= $blockSize;
                                        $created++;
                                        $found = true;
                                        break; // Keluar dari loop starts
                                    }
                                }
                            }
                            if ($found) break; // Keluar dari loop hari
                        }

                        if (!$found) {
                            // Gagal mencari slot (mungkin bentrok guru terus). Kurangi block size jadi 1 dan coba lagi, 
                            // atau paksa berhenti agar tidak infinite loop
                            if ($blockSize > 1) {
                                // Coba dipecah lebih kecil (terjadi otomatis di iterasi berikutnya jika kita kurangi sisa? Tidak, sisa belum berkurang)
                                // Kita ubah sisa sementara untuk memaksa block size 1? Tidak, block size dihitung ulang
                                // Break saja jika gagal total
                                break; 
                            } else {
                                break; // Gagal di 1 jam
                            }
                        }
                    }
                }
            }

            DB::commit();
            return back()->with('success', "Berhasil Generate Acak Jadwal. {$created} blok jadwal baru telah dibuat.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Gagal melakukan generate: " . $e->getMessage());
        }
    }

    private function buatJadwal($kurikulum, $hari, $jamMulai, $jamSelesai, $isLocked)
    {
        $times = \App\Models\JadwalPelajaran::calculateTimesFromJamKe($hari, $jamMulai, $jamSelesai);
        $guruId = $kurikulum->guru_user_id ?: ($kurikulum->mataPelajaran->guru_user_id ?? null);
        
        \App\Models\JadwalPelajaran::create([
            'hari' => $hari,
            'jam_ke_mulai' => $jamMulai,
            'jam_ke_selesai' => $jamSelesai,
            'jam_mulai' => $times['jam_mulai'],
            'jam_selesai' => $times['jam_selesai'],
            'kelas' => $kurikulum->kelas,
            'mata_pelajaran_id' => $kurikulum->mata_pelajaran_id,
            'guru_user_id' => $guruId,
            'tahun_ajaran' => \App\Models\PengaturanSekolah::getActiveTahunAjaran(),
            'semester' => \App\Models\PengaturanSekolah::getActiveSemester(),
            'is_locked' => $isLocked
        ]);
    }

    public function jadwalStore(Request $request)
    {
        $tahunAjaran = PengaturanSekolah::getActiveTahunAjaran();
        $semester = PengaturanSekolah::getActiveSemester();

        // =========================================================================
        // SKEMA 1: INPUT MULTI-SESI / PECAH JAM (Misal: 6 Jam dipecah Senin 2 jam & Selasa 4 jam)
        // =========================================================================
        if ($request->has('sesi') && is_array($request->input('sesi')) && count($request->input('sesi')) > 0) {
            $request->validate([
                'kelas'             => 'required|string',
                'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
                'guru_user_id'      => 'required|exists:users,id',
                'sesi'              => 'required|array|min:1',
                'sesi.*.hari'       => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
                'sesi.*.jam_ke_mulai' => 'required|integer|min:1|max:8',
                'sesi.*.jam_ke_selesai' => 'required|integer|min:1|max:8',
            ]);

            $kelas = $request->input('kelas');
            $mapelId = $request->input('mata_pelajaran_id');
            $guruUserId = (int) $request->input('guru_user_id');

            $processedSesi = [];
            $totalJp = 0;
            $conflictErrors = [];

            foreach ($request->input('sesi') as $idx => $s) {
                $hari = $s['hari'] ?? 'Senin';
                $isJumat = ($hari === 'Jumat');
                $maxP = $isJumat ? 6 : 8;

                $jkMulai = min((int)($s['jam_ke_mulai'] ?? 1), $maxP);
                $jkSelesai = min((int)($s['jam_ke_selesai'] ?? $jkMulai), $maxP);
                if ($jkSelesai < $jkMulai) {
                    $jkSelesai = $jkMulai;
                }

                $times = JadwalPelajaran::calculateTimesFromJamKe($hari, $jkMulai, $jkSelesai);
                $jpSesi = ($jkSelesai - $jkMulai + 1);
                $totalJp += $jpSesi;

                // Validasi bentrok jadwal guru dan kelas
                $check = JadwalPelajaran::checkConflict($hari, $times['jam_mulai'], $times['jam_selesai'], $kelas, $guruUserId);
                if ($check['conflict']) {
                    $conflictErrors[] = "Sesi " . ($idx + 1) . " ({$hari} Jam ke-{$jkMulai} s.d. {$jkSelesai}): " . $check['message'];
                }

                $processedSesi[] = array_merge($times, ['hari' => $hari, 'jp' => $jpSesi]);
            }

            if (!empty($conflictErrors)) {
                return back()->withInput()->with('error', implode(' | ', $conflictErrors));
            }

            DB::beginTransaction();
            try {
                $createdCount = 0;
                foreach ($processedSesi as $sesi) {
                    JadwalPelajaran::create([
                        'hari'              => $sesi['hari'],
                        'jam_ke_mulai'      => $sesi['jam_ke_mulai'],
                        'jam_ke_selesai'    => $sesi['jam_ke_selesai'],
                        'jam_mulai'         => $sesi['jam_mulai'],
                        'jam_selesai'       => $sesi['jam_selesai'],
                        'kelas'             => $kelas,
                        'mata_pelajaran_id' => $mapelId,
                        'guru_user_id'      => $guruUserId,
                        'ruang'             => null,
                        'tahun_ajaran'      => $tahunAjaran,
                        'semester'          => $semester,
                        'is_locked'         => $request->has('is_locked'),
                    ]);
                    $createdCount++;
                }
                DB::commit();

                $mapel = MataPelajaran::find($mapelId);
                $guru = User::find($guruUserId);

                // Cek atau buat Kurikulum jika belum ada
                $warningMsg = null;
                $kurikulum = Kurikulum::where('kelas', $kelas)
                    ->where('mata_pelajaran_id', $mapelId)
                    ->where('tahun_ajaran', $tahunAjaran)
                    ->where('semester', $semester)
                    ->first();
                
                if (!$kurikulum) {
                    $kelasModel = \App\Models\Kelas::where('nama', $kelas)->first();
                    $jenjang = 'X';
                    if (str_starts_with($kelas, 'XII')) $jenjang = 'XII';
                    elseif (str_starts_with($kelas, 'XI')) $jenjang = 'XI';

                    $jurusan = 'TKJT';
                    if (str_contains($kelas, 'TO')) $jurusan = 'TO';
                    elseif (str_contains($kelas, 'AKL')) $jurusan = 'AKL';

                    $jurusanModel = \App\Models\Jurusan::where('kode', $jurusan)->first();

                    $kurikulum = Kurikulum::create([
                        'tahun_ajaran'      => $tahunAjaran,
                        'semester'          => $semester,
                        'is_locked'         => $request->has('is_locked'),
                        'kelas'             => $kelas,
                        'jenjang'           => $jenjang,
                        'jurusan'           => $jurusan,
                        'jurusan_id'        => $jurusanModel?->id,
                        'kelas_id'          => $kelasModel?->id,
                        'mata_pelajaran_id' => $mapelId,
                        'guru_user_id'      => $guruUserId,
                        'alokasi_jam'       => max(2, $totalJp ?? 2),
                        'is_aktif'          => true,
                    ]);
                }

                $totalTerjadwalMapel = JadwalPelajaran::where('kelas', $kelas)
                    ->where('mata_pelajaran_id', $mapelId)
                    ->get()
                    ->sum(fn($j) => max(1, ($j->jam_ke_selesai - $j->jam_ke_mulai + 1)));

                $totalTerjadwalKelas = JadwalPelajaran::where('kelas', $kelas)
                    ->get()
                    ->sum(fn($j) => max(1, ($j->jam_ke_selesai - $j->jam_ke_mulai + 1)));

                if ($kurikulum && $totalTerjadwalMapel > $kurikulum->alokasi_jam) {
                    $selisih = $totalTerjadwalMapel - $kurikulum->alokasi_jam;
                    $warningMsg = "Peringatan: Total jam KBM terjadwal ({$totalTerjadwalMapel} JP) melebihi alokasi kurikulum ({$kurikulum->alokasi_jam} JP) sebanyak +{$selisih} JP untuk {$mapel->nama} di {$kelas}.";
                } elseif ($totalTerjadwalKelas > 46) {
                    $selisihKelas = $totalTerjadwalKelas - 46;
                    $warningMsg = "Peringatan: Total jam terjadwal kelas {$kelas} ({$totalTerjadwalKelas} JP) melampaui batas kapasitas mingguan (46 JP) sebanyak +{$selisihKelas} JP.";
                }

                $successMsg = "Sukses! {$createdCount} sesi jadwal (Total: {$totalJp} Jam Pelajaran) untuk {$guru->name} ({$mapel->nama}) di kelas {$kelas} berhasil disimpan!";
                $redirect = back()->with('success', $successMsg);
                if ($warningMsg) {
                    $redirect->with('warning', $warningMsg);
                }
                return $redirect;
            } catch (\Throwable $e) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Gagal menyimpan jadwal: ' . $e->getMessage());
            }
        }

        // =========================================================================
        // SKEMA 2: INPUT SATU SESI (STANDAR)
        // =========================================================================
        $hari = $request->input('hari');
        $isJumat = ($hari === 'Jumat');
        $maxPeriod = $isJumat ? 6 : 8;

        if ($request->filled('jam_ke_mulai') && $request->filled('jam_ke_selesai')) {
            $request->validate([
                'hari'              => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
                'jam_ke_mulai'      => "required|integer|min:1|max:{$maxPeriod}",
                'jam_ke_selesai'    => "required|integer|min:1|max:{$maxPeriod}|gte:jam_ke_mulai",
                'kelas'             => 'required|string',
                'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
                'guru_user_id'      => 'required|exists:users,id',
            ]);

            $times = JadwalPelajaran::calculateTimesFromJamKe(
                $hari,
                (int) $request->input('jam_ke_mulai'),
                (int) $request->input('jam_ke_selesai')
            );
            $jamMulai = $times['jam_mulai'];
            $jamSelesai = $times['jam_selesai'];
            $jamKeMulai = $times['jam_ke_mulai'];
            $jamKeSelesai = $times['jam_ke_selesai'];
        } else {
            $request->validate([
                'hari'              => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
                'jam_mulai'         => 'required',
                'jam_selesai'       => 'required|after:jam_mulai',
                'kelas'             => 'required|string',
                'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
                'guru_user_id'      => 'required|exists:users,id',
            ]);
            $jamMulai = $request->input('jam_mulai');
            $jamSelesai = $request->input('jam_selesai');
            $jamKeMulai = null;
            $jamKeSelesai = null;
        }

        // Cek bentrok jadwal
        $check = JadwalPelajaran::checkConflict(
            $hari, 
            $jamMulai, 
            $jamSelesai, 
            $request->input('kelas'), 
            (int) $request->input('guru_user_id')
        );
        if ($check['conflict']) {
            return back()->withInput()->with('error', $check['message']);
        }

        JadwalPelajaran::create([
            'hari'              => $hari,
            'jam_ke_mulai'      => $jamKeMulai,
            'jam_ke_selesai'    => $jamKeSelesai,
            'jam_mulai'         => $jamMulai,
            'jam_selesai'       => $jamSelesai,
            'kelas'             => $request->input('kelas'),
            'mata_pelajaran_id' => $request->input('mata_pelajaran_id'),
            'guru_user_id'      => $request->input('guru_user_id'),
            'ruang'             => null,
            'tahun_ajaran'      => $tahunAjaran,
            'semester'          => $semester,
                        'is_locked'         => $request->has('is_locked'),
        ]);

        $kelas = $request->input('kelas');
        $mapelId = $request->input('mata_pelajaran_id');
        $mapel = MataPelajaran::find($mapelId);

        // Cek atau buat Kurikulum jika belum ada
        $warningMsg = null;
        $kurikulum = Kurikulum::where('kelas', $kelas)
            ->where('mata_pelajaran_id', $mapelId)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->first();
        
        if (!$kurikulum) {
            $kelasModel = \App\Models\Kelas::where('nama', $kelas)->first();
            $jenjang = 'X';
            if (str_starts_with($kelas, 'XII')) $jenjang = 'XII';
            elseif (str_starts_with($kelas, 'XI')) $jenjang = 'XI';

            $jurusan = 'TKJT';
            if (str_contains($kelas, 'TO')) $jurusan = 'TO';
            elseif (str_contains($kelas, 'AKL')) $jurusan = 'AKL';

            $jurusanModel = \App\Models\Jurusan::where('kode', $jurusan)->first();

            $kurikulum = Kurikulum::create([
                'tahun_ajaran'      => $tahunAjaran,
                'semester'          => $semester,
                        'is_locked'         => $request->has('is_locked'),
                'kelas'             => $kelas,
                'jenjang'           => $jenjang,
                'jurusan'           => $jurusan,
                'jurusan_id'        => $jurusanModel?->id,
                'kelas_id'          => $kelasModel?->id,
                'mata_pelajaran_id' => $mapelId,
                'guru_user_id'      => $request->input('guru_user_id'),
                'alokasi_jam'       => 2,
                'is_aktif'          => true,
            ]);
        }

        $totalTerjadwalMapel = JadwalPelajaran::where('kelas', $kelas)
            ->where('mata_pelajaran_id', $mapelId)
            ->get()
            ->sum(fn($j) => max(1, ($j->jam_ke_selesai - $j->jam_ke_mulai + 1)));

        $totalTerjadwalKelas = JadwalPelajaran::where('kelas', $kelas)
            ->get()
            ->sum(fn($j) => max(1, ($j->jam_ke_selesai - $j->jam_ke_mulai + 1)));

        if ($kurikulum && $totalTerjadwalMapel > $kurikulum->alokasi_jam) {
            $selisih = $totalTerjadwalMapel - $kurikulum->alokasi_jam;
            $warningMsg = "Peringatan: Total jam KBM terjadwal ({$totalTerjadwalMapel} JP) melebihi alokasi kurikulum ({$kurikulum->alokasi_jam} JP) sebanyak +{$selisih} JP untuk {$mapel->nama} di {$kelas}.";
        } elseif ($totalTerjadwalKelas > 46) {
            $selisihKelas = $totalTerjadwalKelas - 46;
            $warningMsg = "Peringatan: Total jam terjadwal kelas {$kelas} ({$totalTerjadwalKelas} JP) melampaui batas kapasitas mingguan (46 JP) sebanyak +{$selisihKelas} JP.";
        }

        $redirect = back()->with('success', 'Jadwal pelajaran baru berhasil ditambahkan!');
        if ($warningMsg) {
            $redirect->with('warning', $warningMsg);
        }
        return $redirect;
    }

    public function jadwalUpdate(Request $request, JadwalPelajaran $jadwal)
    {
        $guruUserId = (int) $request->input('guru_user_id');
        $kelas = $request->input('kelas');
        $mapelId = (int) $request->input('mata_pelajaran_id');

        // =========================================================================
        // SKEMA 1: UPDATE DENGAN PECAH JAM (MULTI-SESI)
        // =========================================================================
        if ($request->has('sesi') && is_array($request->input('sesi')) && count($request->input('sesi')) > 0) {
            $request->validate([
                'kelas'             => 'required|string',
                'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
                'guru_user_id'      => 'required|exists:users,id',
                'sesi'              => 'required|array|min:1',
                'sesi.*.hari'       => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
                'sesi.*.jam_ke_mulai' => 'required|integer|min:1|max:8',
                'sesi.*.jam_ke_selesai' => 'required|integer|min:1|max:8',
                'ruang'             => 'nullable|string|max:50',
            ]);

            $processedSesi = [];
            $conflictErrors = [];

            foreach ($request->input('sesi') as $idx => $s) {
                $hari = $s['hari'] ?? 'Senin';
                $isJumat = ($hari === 'Jumat');
                $maxP = $isJumat ? 6 : 8;

                $jkMulai = min((int)($s['jam_ke_mulai'] ?? 1), $maxP);
                $jkSelesai = min((int)($s['jam_ke_selesai'] ?? $jkMulai), $maxP);
                if ($jkSelesai < $jkMulai) {
                    $jkSelesai = $jkMulai;
                }

                $times = JadwalPelajaran::calculateTimesFromJamKe($hari, $jkMulai, $jkSelesai);
                
                // Abaikan jadwal saat ini (agar bisa overwrite tanpa conflict dengan dirinya sendiri)
                $check = JadwalPelajaran::checkConflict($hari, $times['jam_mulai'], $times['jam_selesai'], $kelas, $guruUserId, $jadwal->id);
                if ($check['conflict']) {
                    $conflictErrors[] = "Sesi " . ($idx + 1) . " ({$hari} Jam ke-{$jkMulai} s.d. {$jkSelesai}): " . $check['message'];
                }

                $processedSesi[] = array_merge($times, ['hari' => $hari]);
            }

            if (!empty($conflictErrors)) {
                return back()->withInput()->with('error', implode(' | ', $conflictErrors));
            }

            DB::beginTransaction();
            try {
                // Sesi 1: Update existing record
                $sesi1 = $processedSesi[0];
                $jadwal->update([
                    'hari'              => $sesi1['hari'],
                    'jam_ke_mulai'      => $sesi1['jam_ke_mulai'],
                    'jam_ke_selesai'    => $sesi1['jam_ke_selesai'],
                    'jam_mulai'         => $sesi1['jam_mulai'],
                    'jam_selesai'       => $sesi1['jam_selesai'],
                    'kelas'             => $kelas,
                    'mata_pelajaran_id' => $mapelId,
                    'guru_user_id'      => $guruUserId,
                    'ruang'             => $request->input('ruang'),
                    'is_locked'         => $request->has('is_locked'),
                ]);

                // Sesi berikutnya: Create new records
                for ($i = 1; $i < count($processedSesi); $i++) {
                    $sesiN = $processedSesi[$i];
                    JadwalPelajaran::create([
                        'hari'              => $sesiN['hari'],
                        'jam_ke_mulai'      => $sesiN['jam_ke_mulai'],
                        'jam_ke_selesai'    => $sesiN['jam_ke_selesai'],
                        'jam_mulai'         => $sesiN['jam_mulai'],
                        'jam_selesai'       => $sesiN['jam_selesai'],
                        'kelas'             => $kelas,
                        'mata_pelajaran_id' => $mapelId,
                        'guru_user_id'      => $guruUserId,
                        'ruang'             => $request->input('ruang'),
                    'is_locked'         => $request->has('is_locked'),
                        'tahun_ajaran'      => $jadwal->tahun_ajaran,
                        'semester'          => $jadwal->semester,
                    ]);
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Gagal memecah jadwal: ' . $e->getMessage());
            }

        } else {
            // =========================================================================
            // SKEMA 2: UPDATE SATU SESI
            // =========================================================================
            $hari = $request->input('hari', $jadwal->hari);
            $isJumat = ($hari === 'Jumat');
            $maxPeriod = $isJumat ? 6 : 8;

            $request->validate([
                'hari'              => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
                'jam_ke_mulai'      => "required|integer|min:1|max:{$maxPeriod}",
                'jam_ke_selesai'    => "required|integer|min:1|max:{$maxPeriod}|gte:jam_ke_mulai",
                'kelas'             => 'required|string',
                'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
                'guru_user_id'      => 'required|exists:users,id',
                'ruang'             => 'nullable|string|max:50',
            ]);

            $times = JadwalPelajaran::calculateTimesFromJamKe(
                $hari,
                (int) $request->input('jam_ke_mulai'),
                (int) $request->input('jam_ke_selesai')
            );

            $check = JadwalPelajaran::checkConflict(
                $hari,
                $times['jam_mulai'],
                $times['jam_selesai'],
                $kelas,
                $guruUserId,
                $jadwal->id
            );

            if ($check['conflict']) {
                return back()->withInput()->with('error', $check['message']);
            }

            $jadwal->update([
                'hari'              => $hari,
                'jam_ke_mulai'      => $times['jam_ke_mulai'],
                'jam_ke_selesai'    => $times['jam_ke_selesai'],
                'jam_mulai'         => $times['jam_mulai'],
                'jam_selesai'       => $times['jam_selesai'],
                'kelas'             => $kelas,
                'mata_pelajaran_id' => $mapelId,
                'guru_user_id'      => $guruUserId,
                'ruang'             => $request->input('ruang'),
                    'is_locked'         => $request->has('is_locked'),
            ]);
        }

        $mapel = MataPelajaran::find($mapelId);
        $guru = User::find($guruUserId);

        // Cek apakah alokasi jam kurikulum atau batas jam kelas terlampaui
        $warningMsg = null;
        $kurikulum = Kurikulum::where('kelas', $kelas)
            ->where('mata_pelajaran_id', $mapelId)
            ->where('is_aktif', true)
            ->first();

        $totalTerjadwalMapel = JadwalPelajaran::where('kelas', $kelas)
            ->where('mata_pelajaran_id', $mapelId)
            ->get()
            ->sum(fn($j) => max(1, ($j->jam_ke_selesai - $j->jam_ke_mulai + 1)));

        $totalTerjadwalKelas = JadwalPelajaran::where('kelas', $kelas)
            ->get()
            ->sum(fn($j) => max(1, ($j->jam_ke_selesai - $j->jam_ke_mulai + 1)));

        if ($kurikulum && $totalTerjadwalMapel > $kurikulum->alokasi_jam) {
            $selisih = $totalTerjadwalMapel - $kurikulum->alokasi_jam;
            $warningMsg = "Peringatan: Total jam KBM terjadwal ({$totalTerjadwalMapel} JP) melebihi alokasi kurikulum ({$kurikulum->alokasi_jam} JP) sebanyak +{$selisih} JP untuk {$mapel->nama} di {$kelas}.";
        } elseif ($totalTerjadwalKelas > 46) {
            $selisihKelas = $totalTerjadwalKelas - 46;
            $warningMsg = "Peringatan: Total jam terjadwal kelas {$kelas} ({$totalTerjadwalKelas} JP) melampaui batas kapasitas mingguan (46 JP) sebanyak +{$selisihKelas} JP.";
        }

        $redirect = back()->with('success', "Jadwal pelajaran {$mapel->nama} ({$guru->name}) di kelas {$kelas} berhasil diperbarui!");
        if ($warningMsg) {
            $redirect->with('warning', $warningMsg);
        }
        return $redirect;
    }

    public function jadwalDestroy(JadwalPelajaran $jadwal)
    {
        $jadwal->delete();
        return back()->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }

    public function jadwalBulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'Silakan centang/pilih minimal satu jadwal yang ingin dihapus.');
        }

        $count = JadwalPelajaran::whereIn('id', $ids)->delete();

        return back()->with('success', "Berhasil menghapus {$count} jadwal pelajaran yang dipilih.");
    }

    public function nilaiIndex(Request $request)
    {
        return view('akademik.nilai.index');
    }

    // =========================================================
    // Manajemen Mata Pelajaran
    // =========================================================
    public function mapelIndex(Request $request)
    {
        $query = MataPelajaran::with('guru')->orderBy('tingkat')->orderBy('kelompok')->orderBy('nama');

        if ($request->filled('tingkat') && $request->tingkat !== 'all') {
            $query->where('tingkat', $request->tingkat);
        }
        if ($request->filled('kelompok') && $request->kelompok !== 'all') {
            $query->where('kelompok', $request->kelompok);
        }

        $mapels = $query->get();
        $guruList = User::whereIn('role', ['guru', 'admin', 'superadmin'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.mapel.index', compact('mapels', 'guruList'));
    }

    public function mapelStore(Request $request)
    {
        $request->validate([
            'kode'           => 'required|string|max:20|unique:mata_pelajarans,kode',
            'nama'           => 'required|string|max:150',
            'kelompok'       => 'required|in:umum,kejuruan,muatan_lokal',
            'tingkat'        => 'required|in:X,XI,XII,semua',
            'jam_per_minggu' => 'required|numeric|min:1',
            'guru_user_id'   => 'nullable|exists:users,id',
        ], [
            'kode.unique' => 'Kode Mapel sudah digunakan oleh mata pelajaran lain. Silakan gunakan kode yang berbeda.',
            'kode.required' => 'Kode Mapel wajib diisi.',
            'nama.required' => 'Nama Mapel wajib diisi.',
            'jam_per_minggu.required' => 'Jam per minggu wajib diisi.',
        ]);

        $createData = [
            'kode'           => strtoupper($request->kode),
            'nama'           => $request->nama,
            'kelompok'       => $request->kelompok,
            'tingkat'        => $request->tingkat,
            'jam_per_minggu' => $request->jam_per_minggu,
            'guru_user_id'   => $request->guru_user_id ?: null,
            'is_aktif'       => true,
        ];
        if ($request->filled('kategori')) {
            $createData['kategori'] = $request->kategori;
        }
        if ($request->filled('sub_kategori')) {
            $createData['sub_kategori'] = $request->sub_kategori;
        }

        MataPelajaran::create($createData);

        return back()->with('success', 'Mata Pelajaran berhasil ditambahkan beserta Guru Pengampu!');
    }

    public function mapelUpdate(Request $request, MataPelajaran $mapel)
    {
        $request->validate([
            'kode'           => 'required|string|max:20|unique:mata_pelajarans,kode,' . $mapel->id,
            'nama'           => 'required|string|max:150',
            'kelompok'       => 'required|in:umum,kejuruan,muatan_lokal',
            'tingkat'        => 'required|in:X,XI,XII,semua',
            'jam_per_minggu' => 'required|numeric|min:1',
            'guru_user_id'   => 'nullable|exists:users,id',
        ]);

        $updateData = [
            'kode'           => strtoupper($request->kode),
            'nama'           => $request->nama,
            'kelompok'       => $request->kelompok,
            'tingkat'        => $request->tingkat,
            'jam_per_minggu' => $request->jam_per_minggu,
            'guru_user_id'   => $request->guru_user_id ?: null,
        ];
        if ($request->filled('kategori')) {
            $updateData['kategori'] = $request->kategori;
        }
        if ($request->filled('sub_kategori')) {
            $updateData['sub_kategori'] = $request->sub_kategori;
        }

        $mapel->update($updateData);

        return back()->with('success', 'Mata Pelajaran & Guru Pengampu berhasil diperbarui!');
    }

    public function mapelDestroy(MataPelajaran $mapel)
    {
        // Sinkronisasi: hapus juga semua data kurikulum yang memakai mapel ini
        \App\Models\Kurikulum::where('mata_pelajaran_id', $mapel->id)->delete();
        
        $mapel->delete();
        return back()->with('success', 'Mata Pelajaran dan semua Kurikulum terkait berhasil dihapus.');
    }

    public function mapelBulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'Silakan centang/pilih minimal satu mata pelajaran yang ingin dihapus.');
        }

        // Sinkronisasi: hapus semua data kurikulum yang memakai mapel yang dihapus
        \App\Models\Kurikulum::whereIn('mata_pelajaran_id', $ids)->delete();
        
        $count = MataPelajaran::whereIn('id', $ids)->delete();

        return back()->with('success', "Berhasil menghapus {$count} mata pelajaran yang dipilih.");
    }

    // =========================================================
    // Sinkronisasi dan Validasi Data Pengguna (db_hilaledu)
    // =========================================================
    public function syncUsers()
    {
        $gurus   = DB::table('gurus')->get()->keyBy('user_id');
        $siswas  = DB::table('siswas')->get()->keyBy('user_id');
        $tendiks = DB::table('tendiks')->get()->keyBy('user_id');

        $users   = User::all();
        $synced  = 0;

        foreach ($users as $u) {
            $updates = [];

            // Sinkronkan atribut Guru jika user adalah guru
            if ($u->role === 'guru' && isset($gurus[$u->id])) {
                $g = $gurus[$u->id];
                if (empty($u->nip) && !empty($g->nip)) $updates['nip'] = $g->nip;
                if (empty($u->nuptk) && !empty($g->nuptk)) $updates['nuptk'] = $g->nuptk;
                if (empty($u->bidang_studi) && !empty($g->bidang_studi)) $updates['bidang_studi'] = $g->bidang_studi;
                if (empty($u->jabatan_utama) && !empty($g->jabatan)) $updates['jabatan_utama'] = $g->jabatan;
            }

            // Sinkronkan atribut Siswa jika user adalah siswa
            if ($u->role === 'siswa' && isset($siswas[$u->id])) {
                $s = $siswas[$u->id];
                if (empty($u->nis) && !empty($s->nis)) $updates['nis'] = $s->nis;
                if (empty($u->nisn) && !empty($s->nisn)) $updates['nisn'] = $s->nisn;
                if (empty($u->kelas_id) && !empty($s->kelas_id)) $updates['kelas_id'] = $s->kelas_id;
            }

            // Sinkronkan atribut Tendik jika user adalah tendik
            if ($u->role === 'tendik' && isset($tendiks[$u->id])) {
                $t = $tendiks[$u->id];
                if (empty($u->nip) && !empty($t->nip)) $updates['nip'] = $t->nip;
                if (empty($u->jabatan_utama) && !empty($t->jabatan)) $updates['jabatan_utama'] = $t->jabatan;
            }

            if (!empty($updates)) {
                $u->update($updates);
            }
            $synced++;
        }

        // Pastikan seluruh penugasan guru terdaftar di master penugasan guru
        $penugasan = \App\Models\PengaturanSekolah::get('master_penugasan_guru', []);
        if (!isset($penugasan[31]) && !isset($penugasan['31'])) {
            $penugasan['31'] = [
                'nama'    => 'Elin Tamaya, SE',
                'user_id' => 514,
                'mapel'   => 'Produktif PRB',
                'jabatan' => 'Guru',
            ];
            \App\Models\PengaturanSekolah::set('master_penugasan_guru', $penugasan, 'Master Penugasan Guru & Kode Mengajar', 'json');
        }

        return back()->with('success', "Data {$synced} akun pengguna dan relasi profil berhasil dirapikan dan disinkronkan!");
    }

    // =========================================================
    // Import, Export & Template Jadwal Pelajaran
    // =========================================================
    public function jadwalExport(Request $request, \App\Services\JadwalImportExportService $service)
    {
        $query = JadwalPelajaran::with(['mataPelajaran', 'guru'])
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('jam_mulai');

        if ($request->hari) $query->where('hari', $request->hari);
        if ($request->kelas) $query->where('kelas', $request->kelas);
        if ($request->guru_id) $query->where('guru_user_id', $request->guru_id);

        $jadwals = $query->get();
        $format = $request->query('format', 'xlsx');

        return $service->export($jadwals, $format);
    }

    public function jadwalImport(Request $request, \App\Services\JadwalImportExportService $service)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ]);

        $res = $service->import($request->file('file'));

        if (!empty($res['errors'])) {
            if ($res['success'] === 0) {
                return back()->with('error', "Import jadwal gagal. Format kolom atau data file tidak sesuai.")->with('import_errors', $res['errors']);
            }
            $msg = "Import sebagian berhasil: {$res['success']} jadwal berhasil disimpan/diperbarui, namun ditemukan " . count($res['errors']) . " catatan kesalahan.";
            return back()->with('warning', $msg)->with('import_errors', $res['errors']);
        }

        return back()->with('success', "Sukses! {$res['success']} jadwal pelajaran berhasil diimport.");
    }

    public function jadwalTemplate(Request $request, \App\Services\JadwalImportExportService $service)
    {
        $format = $request->query('format', 'xlsx');
        return $service->downloadTemplate($format);
    }

    // =========================================================
    // Import, Export & Template Mata Pelajaran
    // =========================================================
    public function mapelExport(Request $request, \App\Services\MapelImportExportService $service)
    {
        $mapels = MataPelajaran::orderBy('kelompok')->orderBy('nama')->get();
        $format = $request->query('format', 'xlsx');
        return $service->export($mapels, $format);
    }

    public function mapelImport(Request $request, \App\Services\MapelImportExportService $service)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
            'duplicate_action' => 'required|in:update,add_new,skip'
        ]);

        $res = $service->import($request->file('file'), $request->duplicate_action);

        if (!empty($res['errors'])) {
            if ($res['success'] === 0) {
                return back()->with('error', "Import mata pelajaran gagal. Format kolom atau data file tidak sesuai.")->with('import_errors', $res['errors']);
            }
            $msg = "Import sebagian berhasil: {$res['success']} mata pelajaran disimpan, namun ditemukan catatan/kesalahan baris data.";
            return back()->with('warning', $msg)->with('import_errors', $res['errors']);
        }

        return back()->with('success', "Sukses! {$res['success']} mata pelajaran berhasil diimport.");
    }

    public function mapelTemplate(Request $request, \App\Services\MapelImportExportService $service)
    {
        $format = $request->query('format', 'xlsx');
        return $service->downloadTemplate($format);
    }

    // =========================================================
    // Laporan & Rekap Kehadiran Guru (Cetak dari Inputan Guru)
    // =========================================================
    public function laporanKehadiranIndex(Request $request)
    {
        $bulan = $request->query('bulan', date('Y-m'));
        $guruId = $request->query('guru_id');
        $status = $request->query('status');

        $query = AbsensiGuru::with(['guru', 'jadwal.mataPelajaran'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_absen', 'desc');

        if ($bulan) {
            $query->where('tanggal', 'like', "{$bulan}%");
        }
        if ($guruId) {
            $query->where('guru_user_id', $guruId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $riwayat = $query->paginate(30)->withQueryString();

        // Query untuk statistik akumulasi
        $statQuery = AbsensiGuru::query();
        if ($bulan) $statQuery->where('tanggal', 'like', "{$bulan}%");
        if ($guruId) $statQuery->where('guru_user_id', $guruId);

        $allStats = $statQuery->get();
        $totalHadir = $allStats->where('status', 'hadir')->count();
        $totalTerlambat = $allStats->where('status', 'terlambat')->count();
        $totalIzin = $allStats->where('status', 'izin')->count();
        $totalSakit = $allStats->where('status', 'sakit')->count();
        $totalTugasLuar = $allStats->where('status', 'tugas_luar')->count();
        $totalSesi = $allStats->count();
        $persentase = $totalSesi > 0 ? round((($totalHadir + $totalTerlambat + $totalTugasLuar) / $totalSesi) * 100, 1) : 100;

        $gurus = User::where('role', 'guru')->orderBy('name')->get();
        $selectedGuru = $guruId ? User::find($guruId) : null;

        return view('admin.laporan.kehadiran', compact(
            'riwayat',
            'bulan',
            'guruId',
            'status',
            'totalHadir',
            'totalTerlambat',
            'totalIzin',
            'totalSakit',
            'totalTugasLuar',
            'totalSesi',
            'persentase',
            'gurus',
            'selectedGuru'
        ));
    }

    public function laporanKehadiranPrint(Request $request)
    {
        $bulan = $request->query('bulan', date('Y-m'));
        $guruId = $request->query('guru_id');
        $status = $request->query('status');

        $query = AbsensiGuru::with(['guru', 'jadwal.mataPelajaran'])
            ->orderBy('tanggal', 'asc')
            ->orderBy('jam_absen', 'asc');

        if ($bulan) $query->where('tanggal', 'like', "{$bulan}%");
        if ($guruId) $query->where('guru_user_id', $guruId);
        if ($status) $query->where('status', $status);

        $riwayat = $query->get();

        $totalHadir = $riwayat->where('status', 'hadir')->count();
        $totalTerlambat = $riwayat->where('status', 'terlambat')->count();
        $totalIzin = $riwayat->where('status', 'izin')->count();
        $totalSakit = $riwayat->where('status', 'sakit')->count();
        $totalTugasLuar = $riwayat->where('status', 'tugas_luar')->count();
        $totalSesi = $riwayat->count();
        $persentase = $totalSesi > 0 ? round((($totalHadir + $totalTerlambat + $totalTugasLuar) / $totalSesi) * 100, 1) : 100;

        $selectedGuru = $guruId ? User::find($guruId) : null;

        return view('admin.laporan.kehadiran_print', compact(
            'riwayat',
            'bulan',
            'guruId',
            'status',
            'totalHadir',
            'totalTerlambat',
            'totalIzin',
            'totalSakit',
            'totalTugasLuar',
            'totalSesi',
            'persentase',
            'selectedGuru'
        ));
    }

    // =========================================================
    // Laporan Realisasi KBM Guru
    // =========================================================
    public function laporanKbmIndex(Request $request)
    {
        $bulan = $request->query('bulan', date('Y-m'));
        $guruId = $request->query('guru_id');
        $kelas = $request->query('kelas');
        $kesesuaian = $request->query('kesesuaian');

        $query = LaporanKbm::with(['guru', 'jadwal.mataPelajaran', 'presensiSiswa'])
            ->orderBy('tanggal_realisasi', 'desc');

        if ($bulan) $query->where('tanggal_realisasi', 'like', "{$bulan}%");
        if ($guruId) $query->where('guru_user_id', $guruId);
        if ($kelas) $query->whereHas('jadwal', fn($q) => $q->where('kelas', $kelas));
        if ($kesesuaian) $query->where('kesesuaian_rencana', $kesesuaian);

        $laporans = $query->paginate(20)->withQueryString();
        $gurus = User::where('role', 'guru')->orderBy('name')->get();
        $kelasList = Kelas::where('is_aktif', true)->orderBy('nama')->get();

        return view('admin.laporan.kbm', compact('laporans', 'bulan', 'guruId', 'kelas', 'kesesuaian', 'gurus', 'kelasList'));
    }

    public function laporanKbmPrint(Request $request)
    {
        $bulan = $request->query('bulan', date('Y-m'));
        $guruId = $request->query('guru_id');
        $kelas = $request->query('kelas');
        $kesesuaian = $request->query('kesesuaian');

        $query = LaporanKbm::with(['guru', 'jadwal.mataPelajaran', 'presensiSiswa'])
            ->orderBy('tanggal_realisasi', 'asc');

        if ($bulan) $query->where('tanggal_realisasi', 'like', "{$bulan}%");
        if ($guruId) $query->where('guru_user_id', $guruId);
        if ($kelas) $query->whereHas('jadwal', fn($q) => $q->where('kelas', $kelas));
        if ($kesesuaian) $query->where('kesesuaian_rencana', $kesesuaian);

        $laporans = $query->get();
        $selectedGuru = $guruId ? User::find($guruId) : null;

        return view('admin.laporan.kbm_print', compact('laporans', 'bulan', 'guruId', 'kelas', 'kesesuaian', 'selectedGuru'));
    }

    // =========================================================
    // Penunjukan Tendik Akademik (Superadmin)
    // =========================================================
    public function tendikIndex()
    {
        $pengelolas = \App\Models\PengelolaAkademik::with('user', 'ditunjukOleh')->latest()->get();
        $tendiks = User::where('role', 'tendik')->orderBy('name')->get();
        $total = $tendiks->count();
        $aktif = $tendiks->where('is_active', true)->count();
        $nonaktif = $total - $aktif;
        return view('admin.tendik.index', compact('pengelolas', 'tendiks', 'total', 'aktif', 'nonaktif'));
    }

    public function tendikStore(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id|unique:pengelola_akademiks,user_id',
            'keterangan' => 'nullable|string|max:255',
        ]);

        \App\Models\PengelolaAkademik::create([
            'user_id'                    => $request->user_id,
            'can_manage_jadwal'          => $request->boolean('can_manage_jadwal', true),
            'can_manage_mapel'           => $request->boolean('can_manage_mapel', true),
            'can_view_laporan_kehadiran' => $request->boolean('can_view_laporan_kehadiran', true),
            'can_view_laporan_kbm'       => $request->boolean('can_view_laporan_kbm', true),
            'ditunjuk_oleh_user_id'      => Auth::id(),
            'keterangan'                 => $request->keterangan ?: 'Ditunjuk sebagai Pengelola Akademik',
        ]);

        return back()->with('success', 'Staf Tendik berhasil ditunjuk sebagai Pengelola Akademik!');
    }

    public function tendikDestroy(\App\Models\PengelolaAkademik $pengelola)
    {
        $pengelola->delete();
        return back()->with('success', 'Wewenang Pengelola Akademik berhasil dicabut.');
    }
}
