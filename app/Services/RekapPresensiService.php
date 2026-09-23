<?php

namespace App\Services;

use App\Models\User;
use App\Models\AbsensiGuru;
use App\Models\KegiatanSekolah;
use App\Models\KegiatanPresensi;
use App\Models\PengaturanSekolah;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RekapPresensiService
{
    /**
     * Dapatkan rentang tanggal mulai dan selesai berdasarkan tahun ajaran
     * Contoh: '2026/2027' -> 2026-07-01 s/d 2027-06-30
     */
    public function getAcademicYearRange(string $tahunAjaran): array
    {
        $parts = explode('/', str_replace([' - ', ' '], ['/', ''], $tahunAjaran));
        $startYear = isset($parts[0]) && is_numeric($parts[0]) ? (int) $parts[0] : (int) date('Y');
        $endYear = isset($parts[1]) && is_numeric($parts[1]) ? (int) $parts[1] : $startYear + 1;

        return [
            'start' => "{$startYear}-07-01",
            'end'   => "{$endYear}-06-30",
            'startYear' => $startYear,
            'endYear'   => $endYear,
        ];
    }

    /**
     * Rekapitulasi Presensi Lengkap untuk Satu Guru Tertentu (KBM + Kegiatan Sekolah + Chart Data)
     */
    public function getRekapGuru(int $guruUserId, ?string $bulan = null, ?string $tahunAjaran = null): array
    {
        $guru = User::findOrFail($guruUserId);
        $activeTa = $tahunAjaran ?: PengaturanSekolah::get('tahun_pelajaran', '2026/2027');
        $taRange = $this->getAcademicYearRange($activeTa);

        // 1. Data Absensi KBM
        $kbmQuery = AbsensiGuru::with(['jadwal.mataPelajaran'])
            ->where('guru_user_id', $guruUserId)
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_absen', 'desc');

        if (!empty($bulan)) {
            $kbmQuery->where('tanggal', 'like', "{$bulan}%");
        } else {
            $kbmQuery->whereBetween('tanggal', [$taRange['start'], $taRange['end']]);
        }

        $listKbm = $kbmQuery->get();

        $kbmHadirTepat = $listKbm->where('status', 'hadir')->count();
        $kbmTerlambat  = $listKbm->where('status', 'terlambat')->count();
        $kbmIzin       = $listKbm->where('status', 'izin')->count();
        $kbmSakit      = $listKbm->where('status', 'sakit')->count();
        $kbmTugasLuar  = $listKbm->where('status', 'tugas_luar')->count();
        $totalSesiKbm  = $listKbm->count();
        $totalHadirKbm = $kbmHadirTepat + $kbmTerlambat + $kbmTugasLuar;
        $persenKbm     = $totalSesiKbm > 0 ? round(($totalHadirKbm / $totalSesiKbm) * 100, 1) : 100;

        // 2. Data Kegiatan Sekolah & Presensi Kegiatan
        $kegiatanQuery = KegiatanSekolah::with(['presensis' => function ($q) use ($guruUserId) {
                $q->where('guru_user_id', $guruUserId);
            }])
            ->where('is_active', true)
            ->orderBy('tanggal_kegiatan', 'desc');

        if (!empty($bulan)) {
            $kegiatanQuery->where('tanggal_kegiatan', 'like', "{$bulan}%");
        } else {
            $kegiatanQuery->whereBetween('tanggal_kegiatan', [$taRange['start'], $taRange['end']]);
        }

        $listKegiatan = $kegiatanQuery->get();
        $totalKegiatan = $listKegiatan->count();

        $kegiatanHadir = 0;
        $kegiatanIzin  = 0;
        $kegiatanSakit = 0;
        $kegiatanAlpha = 0;

        foreach ($listKegiatan as $keg) {
            $p = $keg->presensis->first();
            if ($p) {
                if ($p->status === 'hadir' || $p->status === 'terlambat') {
                    $kegiatanHadir++;
                } elseif ($p->status === 'izin') {
                    $kegiatanIzin++;
                } elseif ($p->status === 'sakit') {
                    $kegiatanSakit++;
                } else {
                    $kegiatanAlpha++;
                }
            } else {
                $kegiatanAlpha++;
            }
        }

        $persenKegiatan = $totalKegiatan > 0 ? round(($kegiatanHadir / $totalKegiatan) * 100, 1) : 100;

        // 3. Akumulasi Gabungan (KBM + Kegiatan)
        $totalSesiGabungan  = $totalSesiKbm + $totalKegiatan;
        $totalHadirGabungan = $totalHadirKbm + $kegiatanHadir;
        $persenGabungan     = $totalSesiGabungan > 0 ? round(($totalHadirGabungan / $totalSesiGabungan) * 100, 1) : 100;

        // Tentukan Predikat
        $predikat = 'Sangat Baik';
        $predikatColor = 'emerald';
        if ($persenGabungan < 75) {
            $predikat = 'Perlu Pembinaan';
            $predikatColor = 'rose';
        } elseif ($persenGabungan < 85) {
            $predikat = 'Cukup';
            $predikatColor = 'amber';
        } elseif ($persenGabungan < 95) {
            $predikat = 'Baik';
            $predikatColor = 'blue';
        }

        // 4. Data Visualisasi Grafik Chart.js
        // A. Tren Bulanan 1 Tahun Ajaran (Juli s.d. Juni)
        $monthlyTrend = $this->getMonthlyTrendForGuru($guruUserId, $taRange);

        // B. Distribusi Status Bulanan
        $statusDistribution = [
            'labels' => ['Hadir Tepat', 'Terlambat', 'Izin', 'Sakit', 'Tugas Luar', 'Kegiatan Sekolah Hadir'],
            'data'   => [$kbmHadirTepat, $kbmTerlambat, $kbmIzin, $kbmSakit, $kbmTugasLuar, $kegiatanHadir],
            'colors' => ['#10b981', '#f59e0b', '#3b82f6', '#8b5cf6', '#6366f1', '#06b6d4'],
        ];

        return [
            'guru'               => $guru,
            'bulan'              => $bulan,
            'tahunAjaran'        => $activeTa,
            // KBM
            'listKbm'            => $listKbm,
            'kbmHadirTepat'      => $kbmHadirTepat,
            'kbmTerlambat'       => $kbmTerlambat,
            'kbmIzin'            => $kbmIzin,
            'kbmSakit'           => $kbmSakit,
            'kbmTugasLuar'       => $kbmTugasLuar,
            'totalSesiKbm'       => $totalSesiKbm,
            'totalHadirKbm'      => $totalHadirKbm,
            'persenKbm'          => $persenKbm,
            // Kegiatan
            'listKegiatan'       => $listKegiatan,
            'totalKegiatan'      => $totalKegiatan,
            'kegiatanHadir'      => $kegiatanHadir,
            'kegiatanIzin'       => $kegiatanIzin,
            'kegiatanSakit'      => $kegiatanSakit,
            'kegiatanAlpha'      => $kegiatanAlpha,
            'persenKegiatan'     => $persenKegiatan,
            // Gabungan
            'totalSesiGabungan'  => $totalSesiGabungan,
            'totalHadirGabungan' => $totalHadirGabungan,
            'persenGabungan'     => $persenGabungan,
            'predikat'           => $predikat,
            'predikatColor'      => $predikatColor,
            // Chart Datasets
            'monthlyTrend'       => $monthlyTrend,
            'statusDistribution'=> $statusDistribution,
        ];
    }

    /**
     * Hitung tren kehadiran 12 bulan (Juli s.d. Juni) untuk grafik Chart.js
     */
    protected function getMonthlyTrendForGuru(int $guruUserId, array $taRange): array
    {
        $months = [
            ['m' => '07', 'y' => $taRange['startYear'], 'name' => 'Juli'],
            ['m' => '08', 'y' => $taRange['startYear'], 'name' => 'Agustus'],
            ['m' => '09', 'y' => $taRange['startYear'], 'name' => 'September'],
            ['m' => '10', 'y' => $taRange['startYear'], 'name' => 'Oktober'],
            ['m' => '11', 'y' => $taRange['startYear'], 'name' => 'November'],
            ['m' => '12', 'y' => $taRange['startYear'], 'name' => 'Desember'],
            ['m' => '01', 'y' => $taRange['endYear'],   'name' => 'Januari'],
            ['m' => '02', 'y' => $taRange['endYear'],   'name' => 'Februari'],
            ['m' => '03', 'y' => $taRange['endYear'],   'name' => 'Maret'],
            ['m' => '04', 'y' => $taRange['endYear'],   'name' => 'April'],
            ['m' => '05', 'y' => $taRange['endYear'],   'name' => 'Mei'],
            ['m' => '06', 'y' => $taRange['endYear'],   'name' => 'Juni'],
        ];

        $labels = [];
        $kbmHadirData = [];
        $kegiatanHadirData = [];
        $persenData = [];

        foreach ($months as $item) {
            $ym = "{$item['y']}-{$item['m']}";
            $labels[] = $item['name'];

            // Sesi KBM di bulan ini
            $kbmList = AbsensiGuru::where('guru_user_id', $guruUserId)
                ->where('tanggal', 'like', "{$ym}%")
                ->get();
            $kbmHadir = $kbmList->whereIn('status', ['hadir', 'terlambat', 'tugas_luar'])->count();
            $kbmTotal = $kbmList->count();

            // Kegiatan di bulan ini
            $kegiatanIds = KegiatanSekolah::where('tanggal_kegiatan', 'like', "{$ym}%")
                ->where('is_active', true)
                ->pluck('id');
            $kegiatanTotal = $kegiatanIds->count();
            $kegiatanHadir = KegiatanPresensi::where('guru_user_id', $guruUserId)
                ->whereIn('kegiatan_sekolah_id', $kegiatanIds)
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count();

            $totSesi  = $kbmTotal + $kegiatanTotal;
            $totHadir = $kbmHadir + $kegiatanHadir;
            $persen   = $totSesi > 0 ? round(($totHadir / $totSesi) * 100, 1) : 100;

            $kbmHadirData[] = $kbmHadir;
            $kegiatanHadirData[] = $kegiatanHadir;
            $persenData[] = $persen;
        }

        return [
            'labels'       => $labels,
            'kbmHadir'     => $kbmHadirData,
            'kegiatanHadir'=> $kegiatanHadirData,
            'persentase'   => $persenData,
        ];
    }

    /**
     * Rekapitulasi Presensi Seluruh Dewan Guru untuk Tampilan Admin & Kepala Sekolah
     */
    public function getRekapSemuaGuru(?string $bulan = null, ?string $tahunAjaran = null): array
    {
        $activeTa = $tahunAjaran ?: PengaturanSekolah::get('tahun_pelajaran', '2026/2027');
        $taRange = $this->getAcademicYearRange($activeTa);

        $gurus = User::where('role', 'guru')->orderBy('name')->get();

        // Ambil data absensi KBM pada periode
        $kbmQuery = AbsensiGuru::query();
        if (!empty($bulan)) {
            $kbmQuery->where('tanggal', 'like', "{$bulan}%");
        } else {
            $kbmQuery->whereBetween('tanggal', [$taRange['start'], $taRange['end']]);
        }
        $allKbm = $kbmQuery->get()->groupBy('guru_user_id');

        // Ambil data kegiatan pada periode
        $kegiatanQuery = KegiatanSekolah::where('is_active', true);
        if (!empty($bulan)) {
            $kegiatanQuery->where('tanggal_kegiatan', 'like', "{$bulan}%");
        } else {
            $kegiatanQuery->whereBetween('tanggal_kegiatan', [$taRange['start'], $taRange['end']]);
        }
        $allKegiatanIds = $kegiatanQuery->pluck('id');
        $totalKegiatanPeriode = $allKegiatanIds->count();

        $allKegiatanPresensi = KegiatanPresensi::whereIn('kegiatan_sekolah_id', $allKegiatanIds)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->get()
            ->groupBy('guru_user_id');

        $rows = collect();
        $totalAkumulasiSesi = 0;
        $totalAkumulasiHadir = 0;

        foreach ($gurus as $idx => $g) {
            $kbmGuru = $allKbm->get($g->id, collect());
            $kbmHadir = $kbmGuru->whereIn('status', ['hadir', 'terlambat', 'tugas_luar'])->count();
            $kbmTerlambat = $kbmGuru->where('status', 'terlambat')->count();
            $kbmIzinSakit = $kbmGuru->whereIn('status', ['izin', 'sakit'])->count();
            $kbmTotal = $kbmGuru->count();

            $kegiatanHadir = $allKegiatanPresensi->get($g->id, collect())->count();

            $totalSesi = $kbmTotal + $totalKegiatanPeriode;
            $totalHadir = $kbmHadir + $kegiatanHadir;
            $persen = $totalSesi > 0 ? round(($totalHadir / $totalSesi) * 100, 1) : 100;

            $predikat = 'Sangat Baik';
            $badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
            if ($persen < 75) {
                $predikat = 'Perlu Pembinaan';
                $badgeClass = 'bg-rose-100 text-rose-800 border-rose-300';
            } elseif ($persen < 85) {
                $predikat = 'Cukup';
                $badgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
            } elseif ($persen < 95) {
                $predikat = 'Baik';
                $badgeClass = 'bg-blue-100 text-blue-800 border-blue-300';
            }

            $totalAkumulasiSesi += $totalSesi;
            $totalAkumulasiHadir += $totalHadir;

            $rows->push([
                'no'              => $idx + 1,
                'guru'            => $g,
                'guru_id'         => $g->id,
                'nama'            => $g->name,
                'nip'             => $g->nip ?: ($g->nuptk ?: '-'),
                'kode_guru'       => $g->kode_guru,
                'jabatan'         => $g->jabatan_utama ?: ($g->tugas_tambahan_label ?: 'Guru Pengajar'),
                'kbm_hadir'       => $kbmHadir,
                'kbm_terlambat'   => $kbmTerlambat,
                'kbm_izin_sakit'  => $kbmIzinSakit,
                'kbm_total'       => $kbmTotal,
                'kegiatan_hadir'  => $kegiatanHadir,
                'kegiatan_total'  => $totalKegiatanPeriode,
                'total_hadir'     => $totalHadir,
                'total_sesi'      => $totalSesi,
                'persentase'      => $persen,
                'predikat'        => $predikat,
                'badge_class'     => $badgeClass,
            ]);
        }

        $rataRataSekolah = $totalAkumulasiSesi > 0 ? round(($totalAkumulasiHadir / $totalAkumulasiSesi) * 100, 1) : 100;

        // Data Chart Perbandingan Guru Teratas (Top 10 Kehadiran)
        $topGurus = $rows->sortByDesc('persentase')->take(10);
        $comparisonChart = [
            'labels' => $topGurus->pluck('nama')->map(fn($n) => strlen($n) > 15 ? substr($n, 0, 15) . '...' : $n)->values(),
            'data'   => $topGurus->pluck('persentase')->values(),
        ];

        return [
            'rows'                 => $rows,
            'bulan'                => $bulan,
            'tahunAjaran'          => $activeTa,
            'totalGuru'            => $gurus->count(),
            'totalKegiatanPeriode' => $totalKegiatanPeriode,
            'rataRataSekolah'      => $rataRataSekolah,
            'totalAkumulasiSesi'   => $totalAkumulasiSesi,
            'totalAkumulasiHadir'  => $totalAkumulasiHadir,
            'comparisonChart'      => $comparisonChart,
        ];
    }
}
