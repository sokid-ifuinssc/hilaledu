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
        $query = JadwalPelajaran::with(['mataPelajaran', 'guru'])
            ->where('guru_user_id', $guruUserId);

        if ($tahunAjaran) {
            $query->where('tahun_ajaran', $tahunAjaran);
        }
        if ($semester) {
            $query->where('semester', $semester);
        }

        $allJadwal = $query->get();

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

        $semNum = ($semester === 'ganjil' || $semester === '1') ? '1' : '2';

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
}
