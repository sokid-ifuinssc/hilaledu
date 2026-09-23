<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class KalenderAkademik extends Model
{
    protected $table = 'kalender_akademiks';

    protected $fillable = [
        'tahun_ajaran',
        'nama_kalender',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_mulai_smt1',
        'tanggal_selesai_smt1',
        'tanggal_mulai_smt2',
        'tanggal_selesai_smt2',
        'is_aktif',
        'deskripsi',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai'        => 'date:Y-m-d',
            'tanggal_selesai'      => 'date:Y-m-d',
            'tanggal_mulai_smt1'   => 'date:Y-m-d',
            'tanggal_selesai_smt1' => 'date:Y-m-d',
            'tanggal_mulai_smt2'   => 'date:Y-m-d',
            'tanggal_selesai_smt2' => 'date:Y-m-d',
            'is_aktif'             => 'boolean',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(KalenderAkademikEvent::class, 'kalender_akademik_id')
            ->orderBy('tanggal_mulai');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Dapatkan kalender akademik aktif atau kalender default
     */
    public static function getActiveCalendar(?string $tahunAjaran = null): ?self
    {
        $query = self::query();
        if ($tahunAjaran) {
            $query->where('tahun_ajaran', $tahunAjaran);
        } else {
            $query->where('is_aktif', true);
        }

        $cal = $query->latest('id')->first();
        if (!$cal && !$tahunAjaran) {
            $cal = self::latest('id')->first();
        }

        return $cal;
    }

    /**
     * Dapatkan daftar bulan untuk semester yang dipilih
     * Semester 1: Juli - Desember (Tahun Awal)
     * Semester 2: Januari - Juli (Tahun Akhir)
     */
    public function getMonthsForSemester(string|int $semester): array
    {
        $start = Carbon::parse($this->tanggal_mulai);
        $end = Carbon::parse($this->tanggal_selesai);
        $startYear = $start->year;
        $endYear = $end->year;

        $months = [];

        if ((string)$semester === '1') {
            for ($m = 7; $m <= 12; $m++) {
                $dt = Carbon::createFromDate($startYear, $m, 1);
                $months[] = [
                    'year'       => $startYear,
                    'month'      => $m,
                    'month_name' => strtoupper($dt->isoFormat('MMMM')),
                    'carbon'     => $dt,
                ];
            }
        } else {
            // Semester 2: Januari s/d Juli tahun berikutnya
            for ($m = 1; $m <= 7; $m++) {
                $dt = Carbon::createFromDate($endYear, $m, 1);
                $months[] = [
                    'year'       => $endYear,
                    'month'      => $m,
                    'month_name' => strtoupper($dt->isoFormat('MMMM')),
                    'carbon'     => $dt,
                ];
            }
        }

        return $months;
    }

    /**
     * Bangun matriks kalender bulanan lengkap (Minggu s/d Sabtu) beserta penandaan status warna
     */
    public function generateMonthlyMatrix(string|int $semester): array
    {
        $months = $this->getMonthsForSemester($semester);
        $allEvents = $this->events()->where('semester', (string)$semester)->get();

        $matrix = [];

        foreach ($months as $mInfo) {
            $y = $mInfo['year'];
            $m = $mInfo['month'];
            $firstDayOfMonth = Carbon::createFromDate($y, $m, 1);
            $daysInMonth = $firstDayOfMonth->daysInMonth;

            // Di Indonesia/standar kalender Jabar: Kolom 0: Minggu, 1: Senin, 2: Selasa, 3: Rabu, 4: Kamis, 5: Jumat, 6: Sabtu
            $startDayOfWeek = $firstDayOfMonth->dayOfWeek; // 0 = Sunday, 1 = Monday, ...

            $weeks = [];
            $currentWeek = [];

            // Padding hari sebelum tanggal 1
            for ($i = 0; $i < $startDayOfWeek; $i++) {
                $currentWeek[] = [
                    'day'         => null,
                    'date_string' => null,
                    'is_current'  => false,
                    'is_sunday'   => ($i === 0),
                    'events'      => [],
                    'bg_class'    => ($i === 0) ? 'bg-slate-50 text-slate-300' : 'bg-white',
                    'cell_style'  => '',
                ];
            }

            // Tanggal 1 sampai akhir bulan
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $currentDate = Carbon::createFromDate($y, $m, $d);
                $dateStr = $currentDate->format('Y-m-d');
                $isSunday = ($currentDate->dayOfWeek === 0);

                // Cari event pada tanggal ini
                $matchingEvents = $allEvents->filter(function ($ev) use ($dateStr) {
                    $startStr = Carbon::parse($ev->tanggal_mulai)->format('Y-m-d');
                    $endStr = Carbon::parse($ev->tanggal_selesai)->format('Y-m-d');
                    return $dateStr >= $startStr && $dateStr <= $endStr;
                });

                // Tentukan warna cell
                $bgClass = 'bg-white text-slate-800 font-semibold';
                $cellStyle = '';
                $primaryEvent = $matchingEvents->first();

                if ($primaryEvent) {
                    $bgClass = $primaryEvent->cell_color;
                } elseif ($isSunday) {
                    $bgClass = 'bg-rose-600 text-white font-black'; // Hari Minggu Merah
                }

                $currentWeek[] = [
                    'day'           => $d,
                    'date_string'   => $dateStr,
                    'is_current'    => true,
                    'is_sunday'     => $isSunday,
                    'events'        => $matchingEvents->values()->all(),
                    'primary_event' => $primaryEvent,
                    'bg_class'      => $bgClass,
                    'cell_style'    => $cellStyle,
                ];

                if (count($currentWeek) === 7) {
                    $weeks[] = $currentWeek;
                    $currentWeek = [];
                }
            }

            // Padding sisa hari setelah akhir bulan
            if (count($currentWeek) > 0) {
                while (count($currentWeek) < 7) {
                    $currentWeek[] = [
                        'day'         => null,
                        'date_string' => null,
                        'is_current'  => false,
                        'is_sunday'   => false,
                        'events'      => [],
                        'bg_class'    => 'bg-slate-50 text-slate-300',
                        'cell_style'  => '',
                    ];
                }
                $weeks[] = $currentWeek;
            }

            $matrix[] = [
                'year'        => $y,
                'month'       => $m,
                'month_name'  => $mInfo['month_name'],
                'weeks'       => $weeks,
            ];
        }

        return $matrix;
    }

    /**
     * Hitung total minggu efektif untuk Semester 1 (Ganjil) dan Semester 2 (Genap)
     */
    public function calculateEffectiveWeeks(): array
    {
        $ganjil = $this->calculateEfektifPerBulan('ganjil');
        $genap  = $this->calculateEfektifPerBulan('genap');

        $totalGanjil = array_sum(array_column($ganjil, 'efektif'));
        $totalGenap  = array_sum(array_column($genap, 'efektif'));

        return [
            'semester1' => [
                'total_minggu_efektif' => $totalGanjil ?: 19,
                'months' => $ganjil,
            ],
            'semester2' => [
                'total_minggu_efektif' => $totalGenap ?: 17,
                'months' => $genap,
            ],
        ];
    }

    /**
     * Hitung otomatis jumlah minggu total, minggu tidak efektif, dan minggu efektif per bulan
     * untuk modul Minggu Efektif Guru.
     */
    public function calculateEfektifPerBulan(string $semester = 'ganjil'): array
    {
        $semNum = ($semester === 'ganjil' || $semester === '1') ? '1' : '2';
        $months = $this->getMonthsForSemester($semNum);

        // Untuk KBM efektif semester 2 (genap), standar kurikulum adalah 6 bulan (Januari - Juni)
        if ($semNum === '2') {
            $months = array_values(array_filter($months, fn($m) => $m['month'] <= 6));
        }

        $events = $this->events()->where('semester', $semNum)->get();
        $result = [];

        foreach ($months as $mInfo) {
            $y = $mInfo['year'];
            $m = $mInfo['month'];
            $monthTitle = ucfirst(mb_strtolower($mInfo['month_name'], 'UTF-8'));

            $firstDay = Carbon::createFromDate($y, $m, 1);
            $daysInMonth = $firstDay->daysInMonth;

            // Hitung minggu dalam bulan
            $totalWeeks = (int) ceil(($daysInMonth + $firstDay->dayOfWeek) / 7);

            // Hitung hari libur / tidak efektif di bulan ini (Senin - Sabtu)
            $nonEffectiveDays = 0;
            $holidayNotes = [];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $cur = Carbon::createFromDate($y, $m, $d);
                if ($cur->dayOfWeek === 0) continue; // Skip Minggu

                $dateStr = $cur->format('Y-m-d');
                $ev = $events->first(function ($item) use ($dateStr) {
                    $s = Carbon::parse($item->tanggal_mulai)->format('Y-m-d');
                    $e = Carbon::parse($item->tanggal_selesai)->format('Y-m-d');
                    return $dateStr >= $s && $dateStr <= $e && ($item->is_libur || in_array($item->kategori, ['libur_nasional', 'libur_sekolah', 'libur_semester', 'ujian_asesmen']));
                });

                if ($ev) {
                    $nonEffectiveDays++;
                    if (!in_array($ev->judul_kegiatan, $holidayNotes)) {
                        $holidayNotes[] = $ev->judul_kegiatan;
                    }
                }
            }

            // Perkiraan minggu tidak efektif (jika hari libur/kegiatan non-KBM >= 3 hari dalam seminggu)
            $tidakEfektifWeeks = min($totalWeeks, (int) round($nonEffectiveDays / 5));
            $efektifWeeks = max(0, $totalWeeks - $tidakEfektifWeeks);

            $keterangan = !empty($holidayNotes) ? implode(', ', array_slice($holidayNotes, 0, 2)) : 'KBM Efektif Penuh';

            $result[] = [
                'bulan'         => $monthTitle,
                'nama'          => $monthTitle,
                'total_minggu'  => $totalWeeks,
                'total'         => $totalWeeks,
                'tidak_efektif' => $tidakEfektifWeeks,
                'tidak'         => $tidakEfektifWeeks,
                'efektif'       => $efektifWeeks,
                'keterangan'    => $keterangan,
                'ket'           => $keterangan,
            ];
        }

        return $result;
    }

    /**
     * Hitung otomatis rincian minggu & jam efektif mengajar guru
     * berdasarkan hari mengajar spesifik (Senin s/d Sabtu) sesuai Kalender Akademik.
     */
    public function calculateEfektifByHari(string $hari, string $semester = 'ganjil', int $jpPerPertemuan = 4): array
    {
        $dayMap = [
            'minggu' => 0,
            'senin'  => 1,
            'selasa' => 2,
            'rabu'   => 3,
            'kamis'  => 4,
            'jumat'  => 5,
            'sabtu'  => 6,
        ];

        $targetDayOfWeek = $dayMap[strtolower(trim($hari))] ?? 1;

        $semNum = ($semester === 'ganjil' || $semester === '1') ? '1' : '2';
        $months = $this->getMonthsForSemester($semNum);

        if ($semNum === '2') {
            $months = array_values(array_filter($months, fn($m) => $m['month'] <= 6));
        }

        $events = $this->events()->where('semester', $semNum)->get();
        $rincian = [];
        $sumTotal = 0;
        $sumTidak = 0;

        foreach ($months as $mInfo) {
            $y = $mInfo['year'];
            $m = $mInfo['month'];
            $monthTitle = ucfirst(mb_strtolower($mInfo['month_name'], 'UTF-8'));

            $firstDay = Carbon::createFromDate($y, $m, 1);
            $daysInMonth = $firstDay->daysInMonth;

            $totalHariIni = 0;
            $tidakEfektifHariIni = 0;
            $holidayNotes = [];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $cur = Carbon::createFromDate($y, $m, $d);
                if ($cur->dayOfWeek !== $targetDayOfWeek) {
                    continue;
                }

                $totalHariIni++;
                $dateStr = $cur->format('Y-m-d');

                // Cek apakah tanggal ini bertepatan dengan libur atau agenda non-KBM
                $ev = $events->first(function ($item) use ($dateStr) {
                    $s = Carbon::parse($item->tanggal_mulai)->format('Y-m-d');
                    $e = Carbon::parse($item->tanggal_selesai)->format('Y-m-d');
                    return $dateStr >= $s && $dateStr <= $e && ($item->is_libur || in_array($item->kategori, ['libur_nasional', 'libur_sekolah', 'libur_semester', 'ujian_asesmen']));
                });

                if ($ev) {
                    $tidakEfektifHariIni++;
                    $holidayNotes[] = "Tgl {$d} ({$ev->judul_kegiatan})";
                }
            }

            $efektifHariIni = max(0, $totalHariIni - $tidakEfektifHariIni);
            $sumTotal += $totalHariIni;
            $sumTidak += $tidakEfektifHariIni;

            $ket = !empty($holidayNotes) ? implode(', ', $holidayNotes) : 'KBM Efektif Penuh';

            $rincian[] = [
                'bulan'         => $monthTitle,
                'total_minggu'  => $totalHariIni,
                'tidak_efektif' => $tidakEfektifHariIni,
                'efektif'       => $efektifHariIni,
                'jam_efektif'   => $efektifHariIni * $jpPerPertemuan,
                'keterangan'    => $ket,
            ];
        }

        $sumEfektif = max(0, $sumTotal - $sumTidak);
        $totalJam = $sumEfektif * $jpPerPertemuan;

        return [
            'hari'                => ucfirst(strtolower($hari)),
            'semester'            => $semester,
            'tahun_ajaran'        => $this->tahun_ajaran,
            'jp_per_pertemuan'    => $jpPerPertemuan,
            'total_minggu'        => $sumTotal,
            'total_tidak_efektif' => $sumTidak,
            'total_efektif'       => $sumEfektif,
            'total_jam_efektif'   => $totalJam,
            'rincian'             => $rincian,
        ];
    }
}
