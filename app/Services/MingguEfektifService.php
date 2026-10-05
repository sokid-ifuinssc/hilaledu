<?php

namespace App\Services;

use App\Models\JadwalPelajaran;
use App\Models\KalenderAkademik;
use App\Models\MingguEfektif;
use App\Models\MataPelajaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MingguEfektifService
{
    protected array $dayMap = [
        'minggu' => 0,
        'senin'  => 1,
        'selasa' => 2,
        'rabu'   => 3,
        'kamis'  => 4,
        'jumat'  => 5,
        'sabtu'  => 6,
    ];

    /**
     * Ambil seluruh kelompok penugasan mengajar guru berdasarkan jadwal pelajaran aktif
     */
    public function getGuruAssignments(int $guruUserId, ?string $tahunAjaran = null, ?string $semester = null): Collection
    {
        $targetSemester = $semester ? \App\Models\PengaturanSekolah::normalizeSemester($semester) : null;

        $query = JadwalPelajaran::with(['mataPelajaran', 'guru'])
            ->where('guru_user_id', $guruUserId);

        if ($tahunAjaran) {
            $query->where('tahun_ajaran', $tahunAjaran);
        }
        if ($targetSemester) {
            $query->where(function ($q) use ($targetSemester) {
                $q->where('semester', $targetSemester)
                  ->orWhere('semester', $targetSemester === 'ganjil' ? '1' : '2')
                  ->orWhere('semester', ucfirst($targetSemester));
            });
        }

        $allJadwal = $query->get();

        // Fallback cerdas: Jika belum ada jadwal spesifik untuk semester yang diminta pada tahun ajaran ini,
        // gunakan jadwal guru pada tahun ajaran tersebut (karena penugasan mengajar guru berlaku per tahun ajaran).
        if ($allJadwal->isEmpty()) {
            $fallbackQuery = JadwalPelajaran::with(['mataPelajaran', 'guru'])
                ->where('guru_user_id', $guruUserId);
            if ($tahunAjaran) {
                $fallbackQuery->where('tahun_ajaran', $tahunAjaran);
            }
            $allJadwal = $fallbackQuery->get()->map(function ($j) use ($targetSemester) {
                $clone = clone $j;
                if ($targetSemester) {
                    $clone->semester = $targetSemester;
                }
                return $clone;
            });
        }

        // Kelompokkan berdasarkan: mapel + kelas + tahun_ajaran + semester
        $grouped = $allJadwal->groupBy(function ($j) {
            return $j->mata_pelajaran_id . '_' . $j->kelas . '_' . $j->tahun_ajaran . '_' . $j->semester;
        });

        $assignments = collect();

        foreach ($grouped as $items) {
            $first = $items->first();
            $mapel = $first->mataPelajaran;
            if (!$mapel) {
                continue;
            }

            // Hitung sesi hari mengajar & JP
            $hariGroup = $items->groupBy('hari');
            $sessions = [];
            $totalJpPerMinggu = 0;

            foreach ($hariGroup as $hariName => $hItems) {
                $jpForDay = 0;
                $slotLabels = [];

                foreach ($hItems as $item) {
                    $jp = ($item->jam_ke_selesai && $item->jam_ke_mulai)
                        ? ($item->jam_ke_selesai - $item->jam_ke_mulai + 1)
                        : 1;
                    $jpForDay += $jp;
                    $slotLabels[] = $item->jam_ke_label;
                }

                $dayNum = $this->dayMap[strtolower(trim($hariName))] ?? 1;
                $sessions[] = [
                    'hari'       => ucfirst(strtolower($hariName)),
                    'day_num'    => $dayNum,
                    'jp'         => $jpForDay,
                    'slot_label' => implode(', ', array_unique($slotLabels)),
                ];
                $totalJpPerMinggu += $jpForDay;
            }

            $hariLabels = array_map(fn($s) => "{$s['hari']} ({$s['jp']} JP)", $sessions);

            $assignments->push([
                'guru_user_id'        => $first->guru_user_id,
                'guru_name'           => $first->guru->name ?? 'Guru',
                'mata_pelajaran_id'   => $first->mata_pelajaran_id,
                'mata_pelajaran_nama' => $mapel->nama,
                'mata_pelajaran_kode' => $mapel->kode ?? '',
                'kelas'               => $first->kelas,
                'tahun_ajaran'        => $first->tahun_ajaran,
                'semester'            => $first->semester,
                'hari_sessions'       => $sessions,
                'hari_label'          => implode(' & ', $hariLabels),
                'total_jp_per_minggu' => max(1, $totalJpPerMinggu),
            ]);
        }

        return $assignments;
    }

    /**
     * Hitung otomatis rincian minggu & jam efektif mengajar berdasarkan kalender akademik & hari mengajar jadwal
     */
    public function calculateForAssignment(array $assignment): array
    {
        $tahun = $assignment['tahun_ajaran'];
        $semester = $assignment['semester'];
        $sessions = $assignment['hari_sessions'] ?? [];
        $totalJpPerMinggu = $assignment['total_jp_per_minggu'] ?? 4;

        // Ambil kalender akademik yang cocok
        $kalender = KalenderAkademik::where('tahun_ajaran', $tahun)->first()
            ?? KalenderAkademik::where('is_aktif', true)->latest()->first()
            ?? KalenderAkademik::first();

        $semClean = \App\Models\PengaturanSekolah::normalizeSemester($semester);
        $semNum = ($semClean === 'ganjil') ? '1' : '2';

        if (!$kalender) {
            return $this->getFallbackCalculation($assignment);
        }

        $months = $kalender->getMonthsForSemester($semNum);
        if ($semNum === '2') {
            $months = array_values(array_filter($months, fn($m) => $m['month'] <= 6));
        }

        $events = $kalender->events()->where('semester', $semNum)->get();

        $rincianBulanan = [];
        $sumTotalPertemuan = 0;
        $sumTidakPertemuan = 0;
        $sumJamEfektif = 0;

        foreach ($months as $mInfo) {
            $y = $mInfo['year'];
            $m = $mInfo['month'];
            $monthTitle = ucfirst(mb_strtolower($mInfo['month_name'], 'UTF-8'));
            $firstDay = Carbon::createFromDate($y, $m, 1);
            $daysInMonth = $firstDay->daysInMonth;

            $mTotal = 0;
            $mTidak = 0;
            $mJam = 0;
            $notes = [];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $cur = Carbon::createFromDate($y, $m, $d);

                foreach ($sessions as $session) {
                    if ($cur->dayOfWeek === $session['day_num']) {
                        $mTotal++;
                        $dateStr = $cur->format('Y-m-d');

                        // Cek apakah tanggal ini ada event libur / non-KBM
                        $ev = $events->first(function ($it) use ($dateStr) {
                            $s = Carbon::parse($it->tanggal_mulai)->format('Y-m-d');
                            $e = Carbon::parse($it->tanggal_selesai)->format('Y-m-d');
                            return $dateStr >= $s && $dateStr <= $e && ($it->is_libur || in_array($it->kategori, ['libur_nasional', 'libur_sekolah', 'libur_semester', 'ujian_asesmen']));
                        });

                        if ($ev) {
                            $mTidak++;
                            $notes[] = "Tgl {$d}: {$ev->judul_kegiatan} ({$session['hari']})";
                        } else {
                            $mJam += $session['jp'];
                        }
                    }
                }
            }

            $mEfektif = max(0, $mTotal - $mTidak);
            $sumTotalPertemuan += $mTotal;
            $sumTidakPertemuan += $mTidak;
            $sumJamEfektif += $mJam;

            // Konversi ekuivalen pekan: jika ada 2 hari jadwal/pekan, 1 pekan = 2 pertemuan
            $sessionCountPerWeek = max(1, count($sessions));
            $estMingguTotal = (int)ceil($mTotal / $sessionCountPerWeek);
            $estMingguTidak = (int)floor($mTidak / $sessionCountPerWeek);
            $estMingguEfektif = max(0, $estMingguTotal - $estMingguTidak);

            $ket = !empty($notes) ? implode('; ', $notes) : 'KBM Efektif Penuh';

            $rincianBulanan[] = [
                'bulan'           => $monthTitle,
                'total_minggu'    => $estMingguTotal,
                'tidak_efektif'   => $estMingguTidak,
                'efektif'         => $estMingguEfektif,
                'total_pertemuan' => $mTotal,
                'pertemuan_tidak' => $mTidak,
                'pertemuan_efektif' => $mEfektif,
                'jam_efektif'     => $mJam,
                'keterangan'      => $ket,
            ];
        }

        $sessionCountPerWeek = max(1, count($sessions));
        $totalMinggu = (int)ceil($sumTotalPertemuan / $sessionCountPerWeek);
        $totalTidak = (int)floor($sumTidakPertemuan / $sessionCountPerWeek);
        $totalEfektif = max(0, $totalMinggu - $totalTidak);

        // Distribusi Jam standar kurikulum
        $tatapMuka = (int)round($sumJamEfektif * 0.80);
        $formatif = (int)round($sumJamEfektif * 0.10);
        $sumatif = (int)round($sumJamEfektif * 0.05);
        $cadangan = max(0, $sumJamEfektif - ($tatapMuka + $formatif + $sumatif));

        $distribusiJam = [
            'tatap_muka'        => $tatapMuka,
            'asesmen_formatif'  => $formatif,
            'asesmen_sumatif'   => $sumatif,
            'cadangan'          => $cadangan,
        ];

        return [
            'guru_user_id'        => $assignment['guru_user_id'],
            'mata_pelajaran_id'   => $assignment['mata_pelajaran_id'],
            'mata_pelajaran_nama' => $assignment['mata_pelajaran_nama'],
            'kelas'               => $assignment['kelas'],
            'tahun_ajaran'        => $tahun,
            'semester'            => $semester,
            'hari_label'          => $assignment['hari_label'],
            'jam_per_minggu'      => $totalJpPerMinggu,
            'total_minggu'        => $totalMinggu,
            'total_tidak_efektif' => $totalTidak,
            'total_efektif'       => $totalEfektif,
            'total_pertemuan'     => $sumTotalPertemuan,
            'total_tidak_pertemuan' => $sumTidakPertemuan,
            'total_efektif_pertemuan' => max(0, $sumTotalPertemuan - $sumTidakPertemuan),
            'total_jam_efektif'   => $sumJamEfektif,
            'rincian_bulanan'     => $rincianBulanan,
            'distribusi_jam'      => $distribusiJam,
            'catatan'             => "Dihitung otomatis dari Kalender Akademik {$tahun} berdasarkan jadwal mengajar: {$assignment['hari_label']}.",
        ];
    }

    /**
     * Fallback calculation jika kalender belum terkonfigurasi
     */
    protected function getFallbackCalculation(array $assignment): array
    {
        $isGanjil = ($assignment['semester'] === 'ganjil');
        $months = $isGanjil
            ? ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
            : ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];

        $rincian = [];
        $totalMinggu = 26;
        $totalTidak = 8;
        $totalEfektif = 18;
        $jamPerMinggu = $assignment['total_jp_per_minggu'] ?? 4;
        $totalJam = $totalEfektif * $jamPerMinggu;

        foreach ($months as $idx => $m) {
            $tm = ($idx % 2 === 0) ? 5 : 4;
            $tne = ($idx === 0 || $idx === 5) ? 2 : 1;
            $rincian[] = [
                'bulan'         => $m,
                'total_minggu'  => $tm,
                'tidak_efektif' => $tne,
                'efektif'       => max(0, $tm - $tne),
                'jam_efektif'   => max(0, $tm - $tne) * $jamPerMinggu,
                'keterangan'    => 'Estimasi Kalender Standar',
            ];
        }

        return [
            'guru_user_id'        => $assignment['guru_user_id'],
            'mata_pelajaran_id'   => $assignment['mata_pelajaran_id'],
            'mata_pelajaran_nama' => $assignment['mata_pelajaran_nama'],
            'kelas'               => $assignment['kelas'],
            'tahun_ajaran'        => $assignment['tahun_ajaran'],
            'semester'            => $assignment['semester'],
            'hari_label'          => $assignment['hari_label'],
            'jam_per_minggu'      => $jamPerMinggu,
            'total_minggu'        => $totalMinggu,
            'total_tidak_efektif' => $totalTidak,
            'total_efektif'       => $totalEfektif,
            'total_jam_efektif'   => $totalJam,
            'rincian_bulanan'     => $rincian,
            'distribusi_jam'      => [
                'tatap_muka'       => (int)round($totalJam * 0.8),
                'asesmen_formatif' => (int)round($totalJam * 0.1),
                'asesmen_sumatif'  => (int)round($totalJam * 0.05),
                'cadangan'         => (int)round($totalJam * 0.05),
            ],
            'catatan'             => "Dihitung otomatis (mode estimasi).",
        ];
    }

    /**
     * Simpan hasil kalkulasi ke database (table minggu_efektifs)
     */
    public function syncToDatabase(array $calc): MingguEfektif
    {
        return MingguEfektif::updateOrCreate(
            [
                'guru_user_id'      => $calc['guru_user_id'],
                'mata_pelajaran_id' => $calc['mata_pelajaran_id'],
                'kelas'             => $calc['kelas'],
                'tahun_ajaran'      => $calc['tahun_ajaran'],
                'semester'          => $calc['semester'],
            ],
            [
                'total_minggu'        => $calc['total_minggu'],
                'total_tidak_efektif' => $calc['total_tidak_efektif'],
                'total_efektif'       => $calc['total_efektif'],
                'jam_per_minggu'      => $calc['jam_per_minggu'],
                'total_jam_efektif'   => $calc['total_jam_efektif'],
                'rincian_bulanan'     => $calc['rincian_bulanan'],
                'distribusi_jam'      => $calc['distribusi_jam'],
                'catatan'             => $calc['catatan'] ?? null,
            ]
        );
    }

    /**
     * Sinkronkan otomatis semua mapel yang diampu oleh seorang guru untuk tahun & semester tertentu
     */
    public function autoSyncAllForGuru(int $guruUserId, ?string $tahunAjaran = null, ?string $semester = null): Collection
    {
        $assignments = $this->getGuruAssignments($guruUserId, $tahunAjaran, $semester);
        $synced = collect();

        foreach ($assignments as $assignment) {
            $calculated = $this->calculateForAssignment($assignment);
            $record = $this->syncToDatabase($calculated);
            $synced->push($record);
        }

        return $synced;
    }

    /**
     * Ambil daftar tanggal efektif mengajar (KBM) untuk sebuah jadwal pelajaran
     * yang dipetakan dari Kalender Akademik dan Minggu Efektif (libur/non-KBM dilewati).
     *
     * @return array<int, array{
     *     tanggal: string,
     *     tanggal_format: string,
     *     pertemuan_ke: int,
     *     hari: string,
     *     label: string,
     *     sudah_ada_rpp: bool,
     *     rpp_id: int|null,
     *     materi_pokok: string|null
     * }>
     */
    public function getEffectiveDatesForJadwal(JadwalPelajaran $jadwal, ?string $tahunAjaran = null, ?string $semester = null): array
    {
        $tahun = $tahunAjaran ?: ($jadwal->tahun_ajaran ?: \App\Models\PengaturanSekolah::getActiveTahunAjaran());
        $semRaw = $semester ?: ($jadwal->semester ?: \App\Models\PengaturanSekolah::getActiveSemester());
        $semClean = \App\Models\PengaturanSekolah::normalizeSemester($semRaw);
        $semNum = ($semClean === 'ganjil') ? '1' : '2';

        $kalender = KalenderAkademik::where('tahun_ajaran', $tahun)->first()
            ?? KalenderAkademik::where('is_aktif', true)->latest()->first()
            ?? KalenderAkademik::first();

        $dayNum = $this->dayMap[strtolower(trim((string)$jadwal->hari))] ?? null;
        if ($dayNum === null) {
            return [];
        }

        // Ambil data RPP yang sudah pernah dibuat untuk jadwal ini
        $existingRpp = \App\Models\RencanaPembelajaran::where('jadwal_pelajaran_id', $jadwal->id)
            ->get()
            ->keyBy(fn ($r) => Carbon::parse($r->tanggal_rencana)->toDateString());

        if (!$kalender) {
            return $this->getFallbackDatesForJadwal($jadwal, $dayNum, $semNum, $tahun, $existingRpp);
        }

        $months = $kalender->getMonthsForSemester($semNum);
        if ($semNum === '2') {
            $months = array_values(array_filter($months, fn($m) => $m['month'] <= 6));
        }

        $events = $kalender->events()->where('semester', $semNum)->get();

        $result = [];
        $pertemuanKe = 1;

        foreach ($months as $mInfo) {
            $y = $mInfo['year'];
            $m = $mInfo['month'];
            $firstDay = Carbon::createFromDate($y, $m, 1);
            $daysInMonth = $firstDay->daysInMonth;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $cur = Carbon::createFromDate($y, $m, $d);
                if ($cur->dayOfWeek === $dayNum) {
                    $dateStr = $cur->format('Y-m-d');

                    // Cek apakah tanggal ini libur atau kegiatan non-KBM
                    $ev = $events->first(function ($it) use ($dateStr) {
                        $s = Carbon::parse($it->tanggal_mulai)->format('Y-m-d');
                        $e = Carbon::parse($it->tanggal_selesai)->format('Y-m-d');
                        return $dateStr >= $s && $dateStr <= $e && ($it->is_libur || in_array($it->kategori, ['libur_nasional', 'libur_sekolah', 'libur_semester', 'ujian_asesmen']));
                    });

                    if (!$ev) {
                        $rpp = $existingRpp->get($dateStr);
                        $hariLabel = ucfirst(strtolower($jadwal->hari));
                        $formattedDate = $cur->locale('id')->isoFormat('D MMMM Y');

                        $result[] = [
                            'tanggal'        => $dateStr,
                            'tanggal_format' => "{$hariLabel}, {$formattedDate}",
                            'pertemuan_ke'   => $pertemuanKe,
                            'hari'           => $hariLabel,
                            'label'          => "Pertemuan {$pertemuanKe} &bull; {$hariLabel}, {$formattedDate}",
                            'sudah_ada_rpp'  => (bool) $rpp,
                            'rpp_id'         => $rpp?->id,
                            'materi_pokok'   => $rpp?->materi_pokok,
                        ];
                        $pertemuanKe++;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Fallback tanggal pertemuan jika kalender akademik belum dikonfigurasi
     */
    protected function getFallbackDatesForJadwal(JadwalPelajaran $jadwal, int $dayNum, string $semNum, string $tahun, Collection $existingRpp): array
    {
        $years = explode('/', $tahun);
        $startYear = (int) ($years[0] ?? date('Y'));
        if ($semNum === '2' && isset($years[1])) {
            $year = (int) $years[1];
            $monthRange = [1, 2, 3, 4, 5, 6];
        } else {
            $year = $startYear;
            $monthRange = [7, 8, 9, 10, 11, 12];
        }

        $result = [];
        $pertemuanKe = 1;

        foreach ($monthRange as $m) {
            $firstDay = Carbon::createFromDate($year, $m, 1);
            $daysInMonth = $firstDay->daysInMonth;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $cur = Carbon::createFromDate($year, $m, $d);
                if ($cur->dayOfWeek === $dayNum) {
                    $dateStr = $cur->format('Y-m-d');
                    $rpp = $existingRpp->get($dateStr);
                    $hariLabel = ucfirst(strtolower($jadwal->hari));
                    $formattedDate = $cur->locale('id')->isoFormat('D MMMM Y');

                    $result[] = [
                        'tanggal'        => $dateStr,
                        'tanggal_format' => "{$hariLabel}, {$formattedDate}",
                        'pertemuan_ke'   => $pertemuanKe,
                        'hari'           => $hariLabel,
                        'label'          => "Pertemuan {$pertemuanKe} &bull; {$hariLabel}, {$formattedDate}",
                        'sudah_ada_rpp'  => (bool) $rpp,
                        'rpp_id'         => $rpp?->id,
                        'materi_pokok'   => $rpp?->materi_pokok,
                    ];
                    $pertemuanKe++;
                }
            }
        }

        return $result;
    }

    /**
     * Ambil seluruh tanggal efektif KBM seorang guru pada rentang tanggal tertentu (misal satu bulan)
     * berdasarkan hari mengajar jadwal dan kalender akademik (minggu efektif).
     *
     * @return array<string> Daftar string tanggal 'Y-m-d'
     */
    public function getEffectiveTeachingDatesForGuru(int $guruUserId, Carbon $start, Carbon $end, ?string $tahunAjaran = null): array
    {
        $jadwals = JadwalPelajaran::where('guru_user_id', $guruUserId)->get();
        if ($jadwals->isEmpty()) {
            return [];
        }

        $days = $jadwals->map(function ($j) {
            return $this->dayMap[strtolower(trim((string)$j->hari))] ?? null;
        })->filter()->unique()->all();

        if (empty($days)) {
            return [];
        }

        // Ambil event libur / non-KBM
        try {
            $events = \App\Models\KalenderAkademikEvent::query()
                ->where(function ($q) {
                    $q->where('is_libur', true)
                        ->orWhereIn('kategori', ['libur_nasional', 'libur_sekolah', 'libur_semester', 'ujian_asesmen']);
                })
                ->whereDate('tanggal_mulai', '<=', $end->toDateString())
                ->whereDate('tanggal_selesai', '>=', $start->toDateString())
                ->get();
        } catch (\Throwable $e) {
            $events = collect();
        }

        $dates = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if (in_array($d->dayOfWeek, $days, true)) {
                $dateStr = $d->toDateString();
                $isLibur = $events->contains(function ($it) use ($dateStr) {
                    $s = Carbon::parse($it->tanggal_mulai)->toDateString();
                    $e = Carbon::parse($it->tanggal_selesai)->toDateString();
                    return $dateStr >= $s && $dateStr <= $e;
                });

                if (!$isLibur) {
                    $dates[] = $dateStr;
                }
            }
        }

        return $dates;
    }
}

