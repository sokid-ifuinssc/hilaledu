<?php

namespace App\Services;

use App\Models\AbsensiGuru;
use App\Models\CutiGuru;
use App\Models\JadwalPelajaran;
use App\Models\KalenderAkademikEvent;
use App\Models\LaporanKbm;
use App\Models\PengaturanSekolah;
use App\Models\PresensiHarianGuru;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Rekapitulasi kehadiran PEGAWAI (guru + tendik) dan kehadiran MENGAJAR guru.
 *
 * Semua angka dihitung dalam satuan HARI, dengan klasifikasi per hari:
 *   hadir | izin | sakit | dinas_luar | tanpa_keterangan
 *
 * Aturan:
 *  - Hari kerja pegawai = Senin..Jumat/Sabtu (setting `hari_kerja_per_minggu`, default 6)
 *    dikurangi hari libur pada Kalender Akademik. Hari yang belum terlewati tidak dihitung.
 *  - Hari kerja mengajar guru = hari kerja (di atas) yang jatuh pada hari guru punya jadwal.
 *  - Hari tanpa catatan presensi (dan tanpa cuti disetujui) = tanpa keterangan.
 *  - Jumlah tidak hadir = tanpa keterangan + sakit + izin + dinas luar.
 */
class RekapKehadiranService
{
    public const HARI = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

    public const STATUS_LABEL = [
        'hadir'            => 'Hadir',
        'izin'             => 'Ijin',
        'sakit'            => 'Sakit',
        'dinas_luar'       => 'Dinas Luar',
        'tanpa_keterangan' => 'Tanpa Keterangan',
        'belum_berjalan'   => 'Belum Terlaksana',
    ];

    public const STATUS_BADGE = [
        'hadir'            => 'bg-emerald-100 text-emerald-800 border-emerald-300',
        'izin'             => 'bg-blue-100 text-blue-800 border-blue-300',
        'sakit'            => 'bg-purple-100 text-purple-800 border-purple-300',
        'dinas_luar'       => 'bg-indigo-100 text-indigo-800 border-indigo-300',
        'tanpa_keterangan' => 'bg-rose-100 text-rose-800 border-rose-300',
        'belum_berjalan'   => 'bg-slate-100 text-slate-600 border-slate-200',
    ];

    // =====================================================================
    // PERIODE
    // =====================================================================

    /**
     * Tentukan rentang tanggal dari filter: bulan | semester | tahun (ajaran).
     */
    public function resolvePeriode(?string $mode, ?string $bulan, ?string $tahunAjaran, ?string $semester): array
    {
        $mode = in_array($mode, ['bulan', 'semester', 'tahun'], true) ? $mode : 'bulan';

        $defaultTa = (string) PengaturanSekolah::get('tahun_pelajaran', '2026/2027');
        $ta = preg_match('/^\d{4}\/\d{4}$/', (string) $tahunAjaran) ? $tahunAjaran : $defaultTa;
        $bulan = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $bulan) ? $bulan : date('Y-m');
        $semester = in_array((string) $semester, ['1', '2'], true)
            ? (string) $semester
            : (Carbon::now()->month >= 7 ? '1' : '2');

        if ($mode === 'bulan') {
            $start = Carbon::createFromFormat('Y-m-d', $bulan . '-01')->startOfDay();
            $end = $start->copy()->endOfMonth()->startOfDay();
            // Tahun ajaran diturunkan dari bulan yang dipilih (Juli..Juni)
            $startYear = $start->month >= 7 ? $start->year : $start->year - 1;
            $ta = $startYear . '/' . ($startYear + 1);
            $label = 'Bulan ' . $start->copy()->locale('id')->isoFormat('MMMM Y');
        } else {
            $range = app(RekapPresensiService::class)->getAcademicYearRange($ta);
            if ($mode === 'semester') {
                if ($semester === '1') {
                    $start = Carbon::parse("{$range['startYear']}-07-01")->startOfDay();
                    $end = Carbon::parse("{$range['startYear']}-12-31")->startOfDay();
                } else {
                    $start = Carbon::parse("{$range['endYear']}-01-01")->startOfDay();
                    $end = Carbon::parse("{$range['endYear']}-06-30")->startOfDay();
                }
                $label = 'Semester ' . ($semester === '1' ? 'Ganjil' : 'Genap') . " T.A. {$ta}";
            } else {
                $start = Carbon::parse($range['start'])->startOfDay();
                $end = Carbon::parse($range['end'])->startOfDay();
                $label = "Tahun Ajaran {$ta}";
            }
        }

        return [
            'mode'         => $mode,
            'bulan'        => $bulan,
            'tahun_ajaran' => $ta,
            'semester'     => $semester,
            'start'        => $start,
            'end'          => $end,
            'label'        => $label,
        ];
    }

    /**
     * Data penandatangan & titimangsa untuk lembar cetak.
     */
    public function signatureSettings(): array
    {
        return [
            'nama_kepala_sekolah' => PengaturanSekolah::get('nama_kepala_sekolah', 'Mukhammad Mansyur, S.Pt'),
            'nip_kepala_sekolah'  => PengaturanSekolah::get('nip_kepala_sekolah', '6942767668130350'),
            'nama_waka_kurikulum' => PengaturanSekolah::get('nama_waka_kurikulum', 'Sokid, S.T, M.Kom'),
            'nip_waka_kurikulum'  => PengaturanSekolah::get('nip_waka_kurikulum', '198501012010011005'),
            'titimangsa'          => PengaturanSekolah::get('titimangsa', 'Arjawinangun, ' . date('d F Y')),
        ];
    }

    // =====================================================================
    // DAFTAR HADIR PEGAWAI (GURU + TENDIK)
    // =====================================================================

    public function rekapPegawai(array $periode, ?Collection $users = null, bool $withDetail = false): array
    {
        $start = $periode['start'];
        $end = $periode['end'];

        $users = $users ?? User::whereIn('role', ['guru', 'tendik'])
            ->where('is_active', true)
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $libur = $this->liburMap($start, $end);
        $hariKerja = $this->hariKerjaDates($start, $end, $libur);
        $totalHariKerjaSebulan = count($hariKerja);
        $ids = $users->pluck('id')->all();
        $today = Carbon::today();

        $presensi = $this->presensiMap($ids, $start, $end);
        $cuti = $this->cutiMap($ids, $start, $end);

        // Sumber KBM & Piket sebagai bukti kehadiran tambahan bagi guru
        $absensi = [];
        AbsensiGuru::whereIn('guru_user_id', $ids)
            ->whereDate('tanggal', '>=', $start->toDateString())
            ->whereDate('tanggal', '<=', $end->toDateString())
            ->get(['guru_user_id', 'tanggal', 'status'])
            ->each(function ($a) use (&$absensi) {
                $absensi[$a->guru_user_id][Carbon::parse($a->tanggal)->toDateString()][] = strtolower((string) $a->status);
            });

        $kbm = [];
        LaporanKbm::whereIn('guru_user_id', $ids)
            ->whereDate('tanggal_realisasi', '>=', $start->toDateString())
            ->whereDate('tanggal_realisasi', '<=', $end->toDateString())
            ->get(['guru_user_id', 'tanggal_realisasi'])
            ->each(function ($k) use (&$kbm) {
                $kbm[$k->guru_user_id][Carbon::parse($k->tanggal_realisasi)->toDateString()] = true;
            });

        $rows = collect();
        foreach ($users->values() as $i => $u) {
            $c = array_fill_keys(array_keys(self::STATUS_LABEL), 0);
            $detail = [];
            $hariBerjalan = 0;

            foreach ($hariKerja as $tgl) {
                $tglCarbon = Carbon::parse($tgl)->startOfDay();
                $p = $presensi[$u->id][$tgl] ?? null;
                $hasRecord = ($p !== null)
                    || isset($cuti[$u->id][$tgl])
                    || !empty($absensi[$u->id][$tgl])
                    || isset($kbm[$u->id][$tgl]);

                // Hari dihitung berjalan jika:
                // 1. Tanggal sudah lewat (< today)
                // 2. Hari ini (== today) DAN sudah ada catatan absensi/KBM ATAU jam sudah >= 15:00
                if ($tglCarbon->lt($today)) {
                    $isPassed = true;
                } elseif ($tglCarbon->eq($today)) {
                    $isPassed = $hasRecord || (now()->hour >= 15);
                } else {
                    $isPassed = false;
                }

                if ($isPassed) {
                    $hariBerjalan++;
                    $status = $this->classifyPresensi(
                        $p,
                        isset($cuti[$u->id][$tgl]),
                        $absensi[$u->id][$tgl] ?? [],
                        isset($kbm[$u->id][$tgl])
                    );
                    $c[$status]++;
                } else {
                    $p = null;
                    $status = 'belum_berjalan';
                    $c['belum_berjalan']++;
                }

                if ($withDetail) {
                    $detail[] = [
                        'tanggal'     => $tgl,
                        'hari'        => $this->namaHari(Carbon::parse($tgl)),
                        'status'      => $status,
                        'label'       => self::STATUS_LABEL[$status],
                        'badge'       => self::STATUS_BADGE[$status],
                        'jam_masuk'   => $p && $p->jam_masuk ? substr($p->jam_masuk, 0, 5) : null,
                        'jam_pulang'  => $p && $p->jam_pulang ? substr($p->jam_pulang, 0, 5) : null,
                        'terlambat'   => $p && strtolower((string) $p->status_masuk) === 'terlambat' ? (int) $p->terlambat_masuk_menit : 0,
                        'catatan'     => $p->catatan ?? null,
                    ];
                }
            }

            $tidakHadir = $c['tanpa_keterangan'] + $c['sakit'] + $c['izin'] + $c['dinas_luar'];
            $basisPersen = ($end->lt($today) || $hariBerjalan === 0) ? $totalHariKerjaSebulan : $hariBerjalan;
            $persenHadir = $basisPersen > 0 ? round($c['hadir'] / $basisPersen * 100, 1) : 0;
            $persenTidak = $basisPersen > 0 ? round(100 - $persenHadir, 1) : 0;

            $rows->push([
                'no'               => $i + 1,
                'id'               => $u->id,
                'nama'             => $u->name,
                'nip'              => $u->nip ?: ($u->nuptk ?? null) ?: null,
                'jenis'            => $u->role === 'tendik' ? 'Tendik' : 'Guru',
                'hari_kerja'       => $totalHariKerjaSebulan, // Total hari kerja aktif 1 bulan tersebut
                'hari_berjalan'    => $hariBerjalan,
                'belum_berjalan'   => $c['belum_berjalan'],
                'hadir'            => $c['hadir'],
                'tidak_hadir'      => $tidakHadir,
                'tanpa_keterangan' => $c['tanpa_keterangan'],
                'sakit'            => $c['sakit'],
                'izin'             => $c['izin'],
                'dinas_luar'       => $c['dinas_luar'],
                'persen_hadir'     => $persenHadir,
                'persen_tidak'     => $persenTidak,
                'detail'           => $detail,
            ]);
        }

        return [
            'rows'       => $rows,
            'total'      => $this->buildTotal($rows),
            'hari_kerja' => $totalHariKerjaSebulan,
            'libur'      => $libur,
        ];
    }

    // =====================================================================
    // REKAP KEHADIRAN MENGAJAR GURU
    // =====================================================================

    public function rekapMengajar(array $periode, ?Collection $gurus = null, bool $withDetail = false): array
    {
        $start = $periode['start'];
        $end = $periode['end'];
        $ta = $periode['tahun_ajaran'] ?? null;

        $gurus = $gurus ?? User::where('role', 'guru')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $libur = $this->liburMap($start, $end);
        $ids = $gurus->pluck('id')->all();
        $today = Carbon::today();

        // Jadwal mengajar (prioritaskan jadwal tahun ajaran terpilih bila tersedia)
        $jadwalAll = JadwalPelajaran::whereIn('guru_user_id', $ids)->get();
        if ($ta && $jadwalAll->contains(fn ($j) => $this->sameTa($j->tahun_ajaran, $ta))) {
            $jadwalAll = $jadwalAll->filter(fn ($j) => empty($j->tahun_ajaran) || $this->sameTa($j->tahun_ajaran, $ta));
        }
        $jadwalByGuru = $jadwalAll->groupBy('guru_user_id');

        // Tanggal kalender periode (bukan libur) - untuk perhitungan hari kerja mengajar efektif dari minggu efektif satu bulan tersebut
        $tanggalKalenderPeriode = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if (isset($libur[$d->toDateString()])) {
                continue;
            }
            $tanggalKalenderPeriode[$d->toDateString()] = $this->namaHari($d);
        }

        // Data sumber kehadiran
        $absensi = [];
        AbsensiGuru::whereIn('guru_user_id', $ids)
            ->whereDate('tanggal', '>=', $start->toDateString())
            ->whereDate('tanggal', '<=', $end->toDateString())
            ->get(['guru_user_id', 'tanggal', 'status'])
            ->each(function ($a) use (&$absensi) {
                $absensi[$a->guru_user_id][Carbon::parse($a->tanggal)->toDateString()][] = strtolower((string) $a->status);
            });

        $kbm = [];
        LaporanKbm::whereIn('guru_user_id', $ids)
            ->whereDate('tanggal_realisasi', '>=', $start->toDateString())
            ->whereDate('tanggal_realisasi', '<=', $end->toDateString())
            ->get(['guru_user_id', 'tanggal_realisasi'])
            ->each(function ($k) use (&$kbm) {
                $kbm[$k->guru_user_id][Carbon::parse($k->tanggal_realisasi)->toDateString()] = true;
            });

        $presensi = $this->presensiMap($ids, $start, $end);
        $cuti = $this->cutiMap($ids, $start, $end);

        $rows = collect();
        $no = 0;
        foreach ($gurus->values() as $g) {
            $jadwals = $jadwalByGuru->get($g->id, collect());
            if ($jadwals->isEmpty()) {
                continue; // guru tanpa jadwal mengajar tidak masuk rekap mengajar
            }

            $hariMengajar = $jadwals->map(fn ($j) => strtolower(trim((string) $j->hari)))->unique()->all();
            $jamPerMinggu = (int) $jadwals->sum(fn ($j) => max(1, (int) $j->jam_ke_selesai - (int) $j->jam_ke_mulai + 1));
            $sesiPerHari = $jadwals->groupBy(fn ($j) => strtolower(trim((string) $j->hari)))
                ->map(fn ($grp) => $grp->count());

            $c = array_fill_keys(array_keys(self::STATUS_LABEL), 0);
            $detail = [];
            $hariKerjaMengajar = 0; // Total hari kerja mengajar dari minggu efektif satu bulan/periode tersebut
            $hariBerjalan = 0;      // Hari kerja yang sudah terlewati / ada aktivitas

            foreach ($tanggalKalenderPeriode as $tgl => $hari) {
                $key = strtolower($hari);
                if (!in_array($key, $hariMengajar, true)) {
                    continue;
                }
                $hariKerjaMengajar++;

                $tglCarbon = Carbon::parse($tgl)->startOfDay();
                $hasRecord = !empty($absensi[$g->id][$tgl])
                    || isset($kbm[$g->id][$tgl])
                    || ($presensi[$g->id][$tgl] ?? null) !== null
                    || isset($cuti[$g->id][$tgl]);

                // Hari dihitung berjalan jika:
                // 1. Tanggal sudah lewat (< today)
                // 2. Hari ini (== today) DAN sudah ada catatan absensi/KBM/presensi ATAU jam sudah >= 15:00
                if ($tglCarbon->lt($today)) {
                    $isPassed = true;
                } elseif ($tglCarbon->eq($today)) {
                    $isPassed = $hasRecord || (now()->hour >= 15);
                } else {
                    $isPassed = false;
                }

                if ($isPassed) {
                    $hariBerjalan++;
                    $status = $this->classifyMengajar(
                        $absensi[$g->id][$tgl] ?? [],
                        isset($kbm[$g->id][$tgl]),
                        $presensi[$g->id][$tgl] ?? null,
                        isset($cuti[$g->id][$tgl])
                    );
                    $c[$status]++;
                } else {
                    $status = 'belum_berjalan';
                    $c['belum_berjalan']++;
                }

                if ($withDetail) {
                    $pHarian = $presensi[$g->id][$tgl] ?? null;
                    $detail[] = [
                        'tanggal'    => $tgl,
                        'hari'       => $hari,
                        'status'     => $status,
                        'label'      => self::STATUS_LABEL[$status],
                        'badge'      => self::STATUS_BADGE[$status],
                        'sesi'       => (int) ($sesiPerHari[$key] ?? 0),
                        'jam_masuk'  => $pHarian && $pHarian->jam_masuk ? substr($pHarian->jam_masuk, 0, 5) : null,
                        'jam_pulang' => $pHarian && $pHarian->jam_pulang ? substr($pHarian->jam_pulang, 0, 5) : null,
                        'terlambat'  => $pHarian && strtolower((string) $pHarian->status_masuk) === 'terlambat' ? (int) $pHarian->terlambat_masuk_menit : 0,
                        'catatan'    => $pHarian->catatan ?? null,
                    ];
                }
            }

            $tidakHadir = $c['tanpa_keterangan'] + $c['sakit'] + $c['izin'] + $c['dinas_luar'];
            $basisPersen = ($end->lt($today) || $hariBerjalan === 0) ? $hariKerjaMengajar : $hariBerjalan;
            $persenHadir = $basisPersen > 0 ? round($c['hadir'] / $basisPersen * 100, 1) : 0;
            $persenTidak = $basisPersen > 0 ? round(100 - $persenHadir, 1) : 0;

            $row = [
                'no'               => ++$no,
                'id'               => $g->id,
                'nama'             => $g->name,
                'nip'              => $g->nip ?: ($g->nuptk ?? null) ?: null,
                'jenis'            => 'Guru',
                'hari_kerja'       => $hariKerjaMengajar,
                'hari_berjalan'    => $hariBerjalan,
                'belum_berjalan'   => $c['belum_berjalan'],
                'jam_per_minggu'   => $jamPerMinggu,
                'hadir'            => $c['hadir'],
                'tidak_hadir'      => $tidakHadir,
                'tanpa_keterangan' => $c['tanpa_keterangan'],
                'sakit'            => $c['sakit'],
                'izin'             => $c['izin'],
                'dinas_luar'       => $c['dinas_luar'],
                'persen_hadir'     => $persenHadir,
                'persen_tidak'     => $persenTidak,
                'detail'           => $detail,
            ];
            $rows->push($row);
        }

        $total = $this->buildTotal($rows);
        $total['jam_per_minggu'] = (int) $rows->sum('jam_per_minggu');

        return [
            'rows'  => $rows,
            'total' => $total,
            'libur' => $libur,
        ];
    }

    // =====================================================================
    // HELPER INTERNAL
    // =====================================================================

    protected function buildRow(int $no, User $u, int $hariKerja, array $c, array $detail = []): array
    {
        $tidakHadir = $c['tanpa_keterangan'] + $c['sakit'] + $c['izin'] + $c['dinas_luar'];
        $persenHadir = $hariKerja > 0 ? round($c['hadir'] / $hariKerja * 100, 1) : 0;
        $persenTidak = $hariKerja > 0 ? round(100 - $persenHadir, 1) : 0;

        return [
            'no'               => $no,
            'id'               => $u->id,
            'nama'             => $u->name,
            'nip'              => $u->nip ?: ($u->nuptk ?? null) ?: null,
            'jenis'            => $u->role === 'tendik' ? 'Tendik' : 'Guru',
            'hari_kerja'       => $hariKerja,
            'hadir'            => $c['hadir'],
            'tidak_hadir'      => $tidakHadir,
            'tanpa_keterangan' => $c['tanpa_keterangan'],
            'sakit'            => $c['sakit'],
            'izin'             => $c['izin'],
            'dinas_luar'       => $c['dinas_luar'],
            'persen_hadir'     => $persenHadir,
            'persen_tidak'     => $persenTidak,
            'detail'           => $detail,
        ];
    }

    protected function buildTotal(Collection $rows): array
    {
        $hariKerja = (int) $rows->sum('hari_kerja');
        $hadir = (int) $rows->sum('hadir');
        $persenHadir = $hariKerja > 0 ? round($hadir / $hariKerja * 100, 1) : 0;

        return [
            'hari_kerja'       => $hariKerja,
            'hadir'            => $hadir,
            'tidak_hadir'      => (int) $rows->sum('tidak_hadir'),
            'tanpa_keterangan' => (int) $rows->sum('tanpa_keterangan'),
            'sakit'            => (int) $rows->sum('sakit'),
            'izin'             => (int) $rows->sum('izin'),
            'dinas_luar'       => (int) $rows->sum('dinas_luar'),
            'persen_hadir'     => $persenHadir,
            'persen_tidak'     => $hariKerja > 0 ? round(100 - $persenHadir, 1) : 0,
        ];
    }

    /**
     * Klasifikasi satu hari kerja pegawai dari presensi harian (+ cuti disetujui).
     */
    protected function classifyPresensi(?PresensiHarianGuru $p, bool $cuti, array $absensiKbm = [], bool $adaKbm = false): string
    {
        if ($p) {
            $s = strtolower(trim((string) $p->status_masuk));
            if (in_array($s, ['hadir', 'terlambat', 'hadir_sesuai_jam'], true)) {
                return 'hadir';
            }
            if ($s === 'izin') {
                return 'izin';
            }
            if ($s === 'sakit') {
                return 'sakit';
            }
            if ($s === 'tugas_luar') {
                return 'dinas_luar';
            }
            if ($s === 'alpa') {
                return 'tanpa_keterangan';
            }
            // Status kosong/tidak dikenal: pakai bukti jam masuk/pulang
            if (!empty($p->jam_masuk) || !empty($p->jam_pulang)) {
                return 'hadir';
            }
        }

        if ($adaKbm || array_intersect($absensiKbm, ['hadir', 'terlambat'])) {
            return 'hadir';
        }
        if (in_array('tugas_luar', $absensiKbm, true)) {
            return 'dinas_luar';
        }
        if (in_array('sakit', $absensiKbm, true)) {
            return 'sakit';
        }
        if (in_array('izin', $absensiKbm, true)) {
            return 'izin';
        }

        return $cuti ? 'izin' : 'tanpa_keterangan';
    }

    /**
     * Klasifikasi satu hari mengajar guru.
     * Prioritas: hadir (absensi KBM / jurnal KBM) > dinas luar > sakit > izin > presensi harian > cuti.
     */
    protected function classifyMengajar(array $statuses, bool $adaJurnalKbm, ?PresensiHarianGuru $p, bool $cuti): string
    {
        if ($adaJurnalKbm || array_intersect($statuses, ['hadir', 'terlambat'])) {
            return 'hadir';
        }
        foreach (['tugas_luar' => 'dinas_luar', 'sakit' => 'sakit', 'izin' => 'izin'] as $raw => $mapped) {
            if (in_array($raw, $statuses, true)) {
                return $mapped;
            }
        }

        return $this->classifyPresensi($p, $cuti);
    }

    protected function namaHari(Carbon $d): string
    {
        return self::HARI[$d->dayOfWeekIso];
    }

    protected function sameTa(?string $a, ?string $b): bool
    {
        $norm = fn ($v) => preg_replace('/\s+/', '', str_replace(['-', '\\'], '/', (string) $v));

        return $norm($a) !== '' && $norm($a) === $norm($b);
    }

    /**
     * Hari terakhir yang ikut dihitung: hari ini bila sudah lewat jam 15.00, selain itu kemarin
     * (agar pegawai yang belum sempat presensi hari ini tidak langsung dihitung tanpa keterangan).
     */
    protected function capDate(Carbon $end): Carbon
    {
        $today = Carbon::today();
        $limit = now()->hour >= 15 ? $today : $today->copy()->subDay();

        return $end->lt($limit) ? $end->copy() : $limit;
    }

    /**
     * Daftar tanggal (Y-m-d) hari kerja aktif pegawai pada periode (1 bulan / periode penuh).
     */
    protected function hariKerjaDates(Carbon $start, Carbon $end, array $libur): array
    {
        $perMinggu = (int) PengaturanSekolah::get('hari_kerja_per_minggu', 6);
        $perMinggu = max(5, min(6, $perMinggu));

        $dates = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if ($d->dayOfWeekIso > $perMinggu) {
                continue;
            }
            if (isset($libur[$d->toDateString()])) {
                continue;
            }
            $dates[] = $d->toDateString();
        }

        return $dates;
    }

    /**
     * Peta hari libur dari Kalender Akademik: [Y-m-d => judul kegiatan].
     */
    protected function liburMap(Carbon $start, Carbon $end): array
    {
        try {
            $events = KalenderAkademikEvent::query()
                ->where(function ($q) {
                    $q->where('is_libur', true)
                        ->orWhereIn('kategori', ['libur_nasional', 'libur_sekolah', 'libur_semester']);
                })
                ->whereDate('tanggal_mulai', '<=', $end->toDateString())
                ->whereDate('tanggal_selesai', '>=', $start->toDateString())
                ->get();
        } catch (\Throwable $e) {
            return [];
        }

        $map = [];
        foreach ($events as $ev) {
            $d = Carbon::parse($ev->tanggal_mulai)->startOfDay();
            $last = Carbon::parse($ev->tanggal_selesai)->startOfDay();
            while ($d->lte($last)) {
                if ($d->gte($start) && $d->lte($end)) {
                    $map[$d->toDateString()] = $ev->judul_kegiatan;
                }
                $d->addDay();
            }
        }

        return $map;
    }

    /**
     * Presensi harian: [user_id][Y-m-d] => PresensiHarianGuru
     */
    protected function presensiMap(array $ids, Carbon $start, Carbon $end): array
    {
        $map = [];
        PresensiHarianGuru::whereIn('guru_user_id', $ids)
            ->whereDate('tanggal', '>=', $start->toDateString())
            ->whereDate('tanggal', '<=', $end->toDateString())
            ->get()
            ->each(function ($p) use (&$map) {
                $map[$p->guru_user_id][Carbon::parse($p->tanggal)->toDateString()] = $p;
            });

        return $map;
    }

    /**
     * Cuti yang sudah disetujui: [user_id][Y-m-d] => true
     */
    protected function cutiMap(array $ids, Carbon $start, Carbon $end): array
    {
        $map = [];
        try {
            $cutis = CutiGuru::whereIn('guru_user_id', $ids)
                ->whereIn('status', ['disetujui_waka', 'disetujui_kepsek'])
                ->whereDate('tanggal_mulai', '<=', $end->toDateString())
                ->whereDate('tanggal_selesai', '>=', $start->toDateString())
                ->get();
        } catch (\Throwable $e) {
            return [];
        }

        foreach ($cutis as $c) {
            $d = Carbon::parse($c->tanggal_mulai)->startOfDay();
            $last = Carbon::parse($c->tanggal_selesai)->startOfDay();
            while ($d->lte($last)) {
                $map[$c->guru_user_id][$d->toDateString()] = true;
                $d->addDay();
            }
        }

        return $map;
    }
}
