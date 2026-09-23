<?php

namespace App\Services;

use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\CutiGuru;
use Illuminate\Support\Collection;

class MonitoringKelasService
{
    /**
     * Helper alias untuk memantau status kelas hari ini secara realtime
     */
    public function getMonitoringHariIni(): array
    {
        return $this->getMonitoringKelasData();
    }

    /**
     * Hitung status kehadiran guru di seluruh kelas sesuai jam berlangsung realtime
     */
    public function getMonitoringKelasData(?string $hari = null, ?string $jam = null, ?string $tanggal = null): array
    {
        $todayName = JadwalPelajaran::getHariIndonesia();
        $todayDate = date('Y-m-d');
        $nowTime   = now()->format('H:i:s');

        // Jika hari ini adalah hari libur (Minggu), sediakan fallback cerdas ke 'Senin' jika tidak diset manual
        $defaultHari = ($todayName === 'Minggu') ? 'Senin' : $todayName;
        $hariPantau = $hari ?: $defaultHari;
        $jamPantau  = $jam ?: $nowTime;
        $tanggalPantau = $tanggal ?: $todayDate;

        $isSimulasi = (!empty($hari) && $hari !== $todayName) || (!empty($jam) && $jam !== $nowTime);

        // 1. Ambil seluruh nama kelas unik dari tabel Kelas dan Jadwal Pelajaran
        $kelasList = Kelas::select('nama_kelas', 'nama', 'tingkat')
            ->where('is_aktif', true)
            ->orWhereNull('is_aktif')
            ->get()
            ->map(function ($k) {
                return [
                    'nama'    => trim($k->nama_kelas ?: $k->nama),
                    'tingkat' => trim($k->tingkat ?? 'X'),
                ];
            })
            ->filter(fn($item) => !empty($item['nama']))
            ->unique('nama');

        $jadwalKelasNames = JadwalPelajaran::select('kelas')->distinct()->pluck('kelas');

        $existingNames = $kelasList->pluck('nama')->toArray();
        $additionalKelas = collect();
        foreach ($jadwalKelasNames as $jkn) {
            $trimmed = trim($jkn);
            if (!empty($trimmed) && !in_array($trimmed, $existingNames)) {
                $tingkat = 'X';
                if (str_starts_with($trimmed, 'XII')) $tingkat = 'XII';
                elseif (str_starts_with($trimmed, 'XI')) $tingkat = 'XI';
                $additionalKelas->push([
                    'nama'    => $trimmed,
                    'tingkat' => $tingkat,
                ]);
            }
        }

        $allKelas = $kelasList->concat($additionalKelas)->sortBy(function ($item) {
            $orderTingkat = ['X' => 1, 'XI' => 2, 'XII' => 3];
            $tVal = $orderTingkat[$item['tingkat']] ?? 9;
            return $tVal . '_' . $item['nama'];
        })->values();

        // 2. Ambil seluruh jadwal pada hari yang dipantau
        $allJadwals = JadwalPelajaran::with([
            'mataPelajaran',
            'guru',
            'absensiGuru' => function ($q) use ($tanggalPantau) {
                $q->where('tanggal', $tanggalPantau);
            }
        ])
        ->where('hari', $hariPantau)
        ->orderBy('jam_mulai')
        ->get();

        $jadwalByKelas = $allJadwals->groupBy('kelas');

        // 3. Ambil cuti yang sedang aktif pada hari ini
        $allCutiAktif = CutiGuru::with('guruPengganti')
            ->whereIn('status', ['disetujui_waka', 'disetujui_kepsek'])
            ->where('tanggal_mulai', '<=', $tanggalPantau)
            ->where('tanggal_selesai', '>=', $tanggalPantau)
            ->get()
            ->keyBy('guru_user_id');

        // 4. Bangun data per kelas
        $monitoringList = collect();

        foreach ($allKelas as $kls) {
            $namaKelas   = $kls['nama'];
            $jadwalKelas = $jadwalByKelas->get($namaKelas, collect());

            // Cari jadwal yang sedang berlangsung pada jam pantau
            $activeJadwal = $jadwalKelas->first(function ($j) use ($jamPantau) {
                return $jamPantau >= $j->jam_mulai && $jamPantau <= $j->jam_selesai;
            });

            // Jadwal berikutnya atau sebelumnya
            $nextJadwal = $jadwalKelas->first(function ($j) use ($jamPantau) {
                return $j->jam_mulai > $jamPantau;
            });
            $prevJadwal = $jadwalKelas->last(function ($j) use ($jamPantau) {
                return $j->jam_selesai < $jamPantau;
            });

            // Jika sedang di luar jam KBM atau hari libur, gunakan jadwal yang ada untuk kelas tersebut
            if (!$activeJadwal) {
                $activeJadwal = $jadwalKelas->first() ?: JadwalPelajaran::with([
                    'mataPelajaran',
                    'guru',
                    'absensiGuru' => function ($q) use ($tanggalPantau) {
                        $q->where('tanggal', $tanggalPantau);
                    }
                ])->where('kelas', $namaKelas)->first();
            }

            if ($activeJadwal) {
                $guru        = $activeJadwal->guru;
                $guruName    = $guru ? $guru->name : 'Guru Pengampu';
                $guruUserId  = $activeJadwal->guru_user_id;
                $mapelName   = $activeJadwal->mataPelajaran ? $activeJadwal->mataPelajaran->nama : 'Mata Pelajaran';
                $ruang       = $activeJadwal->ruang ?: 'Ruang Kelas';
                $jamMulai    = substr($activeJadwal->jam_mulai, 0, 5);
                $jamSelesai  = substr($activeJadwal->jam_selesai, 0, 5);

                // Cuti & Guru Pengganti (Inval)
                $cuti          = $guruUserId ? $allCutiAktif->get($guruUserId) : null;
                $guruPengganti = $cuti?->guruPengganti;

                // Status Presensi Hari Ini
                $absensi = $activeJadwal->absensiGuru->first();

                // Standar warna & status:
                // - Hijau: Ada gurunya (hadir / terlambat)
                // - Kuning: Guru pada jadwal tersebut izin
                // - Merah: Guru sakit / Belum Hadir
                // - Biru: Guru tugas luar
                $warna       = 'merah';
                $statusKey   = 'belum_hadir';
                $statusLabel = 'Belum Hadir';
                $badgeText   = 'Belum Hadir';
                $keterangan  = 'KBM sedang berlangsung, guru pengampu belum melakukan absensi.';

                if ($absensi) {
                    if ($absensi->status === 'hadir' || $absensi->status === 'terlambat') {
                        $warna       = 'hijau';
                        $statusKey   = 'hadir';
                        $statusLabel = 'Ada Gurunya';
                        $badgeText   = $absensi->status === 'terlambat' ? "Hadir Terlambat ({$absensi->terlambat_menit}m)" : 'Hadir di Kelas';
                        $keterangan  = 'Guru telah hadir di kelas dan melakukan presensi tepat waktu.';
                    } elseif ($absensi->status === 'izin') {
                        $warna       = 'kuning';
                        $statusKey   = 'izin';
                        $statusLabel = 'Guru Izin';
                        $badgeText   = 'Izin Resmi';
                        $keterangan  = $absensi->catatan ?: 'Guru berhalangan hadir dengan status Izin.';
                    } elseif ($absensi->status === 'sakit') {
                        $warna       = 'merah';
                        $statusKey   = 'sakit';
                        $statusLabel = 'Guru Sakit';
                        $badgeText   = 'Sakit';
                        $keterangan  = $absensi->catatan ?: 'Guru berhalangan hadir dikarenakan Sakit.';
                    } elseif ($absensi->status === 'tugas_luar') {
                        $warna       = 'biru';
                        $statusKey   = 'tugas_luar';
                        $statusLabel = 'Guru Tugas Luar';
                        $badgeText   = 'Tugas Luar';
                        $keterangan  = $absensi->catatan ?: 'Guru sedang bertugas dinas di luar sekolah.';
                    }
                } elseif ($cuti) {
                    if ($cuti->jenis_cuti === 'sakit') {
                        $warna       = 'merah';
                        $statusKey   = 'sakit';
                        $statusLabel = 'Guru Sakit (Cuti)';
                        $badgeText   = 'Sakit (Cuti)';
                        $keterangan  = 'Guru sedang dalam masa cuti sakit.' . ($guruPengganti ? ' Digantikan: ' . $guruPengganti->name : '');
                    } elseif ($cuti->jenis_cuti === 'tugas_luar') {
                        $warna       = 'biru';
                        $statusKey   = 'tugas_luar';
                        $statusLabel = 'Guru Tugas Luar';
                        $badgeText   = 'Tugas Luar';
                        $keterangan  = 'Guru sedang tugas luar resmi.' . ($guruPengganti ? ' Digantikan: ' . $guruPengganti->name : '');
                    } else {
                        $warna       = 'kuning';
                        $statusKey   = 'izin';
                        $statusLabel = 'Guru Izin (Cuti)';
                        $badgeText   = 'Izin Resmi';
                        $keterangan  = 'Guru sedang cuti izin resmi.' . ($guruPengganti ? ' Digantikan: ' . $guruPengganti->name : '');
                    }
                } else {
                    $warna       = 'merah';
                    $statusKey   = 'belum_hadir';
                    $statusLabel = 'Belum Ada Gurunya';
                    $badgeText   = 'Belum Hadir';
                    $keterangan  = 'KBM sedang berlangsung, guru pengampu belum melakukan presensi.';
                }

                $monitoringList->push([
                    'kelas'          => $namaKelas,
                    'tingkat'        => $kls['tingkat'],
                    'is_running'     => true,
                    'warna'          => $warna, // hijau, kuning, merah, biru
                    'status_key'     => $statusKey,
                    'status_label'   => $statusLabel,
                    'badge_text'     => $badgeText,
                    'guru_nama'      => $guruName,
                    'guru_pengganti' => $guruPengganti ? $guruPengganti->name : null,
                    'mapel'          => $mapelName,
                    'ruang'          => $ruang,
                    'jam_mulai'      => $jamMulai,
                    'jam_selesai'    => $jamSelesai,
                    'jam_display'    => "{$jamMulai} - {$jamSelesai}",
                    'keterangan'     => $keterangan,
                    'jadwal_id'      => $activeJadwal->id,
                ]);
            } else {
                $keterangan = 'Tidak ada jam KBM berlangsung saat ini.';
                if ($nextJadwal) {
                    $nm = $nextJadwal->mataPelajaran ? $nextJadwal->mataPelajaran->nama : 'Mapel';
                    $gr = $nextJadwal->guru ? $nextJadwal->guru->name : 'Guru';
                    $keterangan = "Berikutnya: " . substr($nextJadwal->jam_mulai, 0, 5) . " - {$nm} ({$gr})";
                } elseif ($prevJadwal) {
                    $keterangan = "Seluruh KBM hari {$hariPantau} di kelas ini telah selesai.";
                } elseif ($jadwalKelas->isEmpty()) {
                    $keterangan = "Tidak ada jadwal KBM pada hari {$hariPantau}.";
                }

                $monitoringList->push([
                    'kelas'          => $namaKelas,
                    'tingkat'        => $kls['tingkat'],
                    'is_running'     => false,
                    'warna'          => 'abu',
                    'status_key'     => 'kosong',
                    'status_label'   => 'Tidak Ada KBM',
                    'badge_text'     => 'Istirahat / Kosong',
                    'guru_nama'      => '-',
                    'guru_pengganti' => null,
                    'mapel'          => 'Tidak Ada Jam Pelajaran',
                    'ruang'          => '-',
                    'jam_mulai'      => null,
                    'jam_selesai'    => null,
                    'jam_display'    => 'Di Luar Jam KBM',
                    'keterangan'     => $keterangan,
                    'jadwal_id'      => null,
                ]);
            }
        }

        $rekap = [
            'total_kelas' => $monitoringList->count(),
            'ada_guru'    => $monitoringList->where('warna', 'hijau')->count(),
            'izin'        => $monitoringList->where('warna', 'kuning')->count(),
            'sakit'       => $monitoringList->where('warna', 'merah')->count(),
            'tugas_luar'  => $monitoringList->where('warna', 'biru')->count(),
            'kosong'      => $monitoringList->where('warna', 'abu')->count(),
        ];

        $periodInfo = self::getCurrentPeriodInfo($hariPantau, $jamPantau);

        return [
            'monitoringKelas' => $monitoringList,
            'rekap'           => $rekap,
            'hariPantau'      => $hariPantau,
            'jamPantau'       => $jamPantau,
            'tanggalPantau'   => $tanggalPantau,
            'periodInfo'      => $periodInfo,
            'isSimulasi'      => $isSimulasi,
        ];
    }

    /**
     * Hitung informasi jam pelajaran ke-berapa saat ini dan status KBM
     */
    public static function getCurrentPeriodInfo(?string $hari = null, ?string $time = null): array
    {
        $hari = $hari ?: JadwalPelajaran::getHariIndonesia();
        $time = $time ?: now()->format('H:i:s');

        if (strtolower($hari) === 'minggu') {
            return [
                'is_kbm'        => false,
                'status'        => 'libur',
                'jam_ke'        => null,
                'label_jam_ke'  => 'Libur Akhir Pekan',
                'rentang_waktu' => '-',
                'deskripsi'     => 'Hari Libur Sekolah (Minggu)',
                'badge_class'   => 'bg-slate-100 text-slate-700 border border-slate-300',
            ];
        }

        $isJumat = (strtolower($hari) === 'jumat');
        $periods = JadwalPelajaran::getPeriodsConfig()[$isJumat ? 'jumat' : 'reguler'];

        $firstPeriod = reset($periods);
        $lastPeriod  = end($periods);

        // Sebelum Jam KBM
        if ($time < $firstPeriod['mulai']) {
            return [
                'is_kbm'        => false,
                'status'        => 'sebelum_kbm',
                'jam_ke'        => null,
                'label_jam_ke'  => 'Persiapan KBM',
                'rentang_waktu' => 'Mulai ' . substr($firstPeriod['mulai'], 0, 5) . ' WIB',
                'deskripsi'     => 'Kegiatan Belajar Belum Dimulai (Mulai ' . substr($firstPeriod['mulai'], 0, 5) . ' WIB)',
                'badge_class'   => 'bg-blue-50 text-blue-700 border border-blue-200',
            ];
        }

        // Setelah Jam KBM Selesai
        if ($time > $lastPeriod['selesai']) {
            return [
                'is_kbm'        => false,
                'status'        => 'selesai_kbm',
                'jam_ke'        => null,
                'label_jam_ke'  => 'KBM Selesai',
                'rentang_waktu' => 'Selesai ' . substr($lastPeriod['selesai'], 0, 5) . ' WIB',
                'deskripsi'     => 'Seluruh KBM Hari ' . $hari . ' Telah Selesai',
                'badge_class'   => 'bg-slate-100 text-slate-700 border border-slate-300',
            ];
        }

        // Cek dalam Jam Pelajaran
        foreach ($periods as $jk => $p) {
            if ($time >= $p['mulai'] && $time <= $p['selesai']) {
                return [
                    'is_kbm'        => true,
                    'status'        => 'kbm',
                    'jam_ke'        => (int) $jk,
                    'label_jam_ke'  => "Jam ke-{$jk}",
                    'rentang_waktu' => substr($p['mulai'], 0, 5) . ' - ' . substr($p['selesai'], 0, 5) . ' WIB',
                    'deskripsi'     => "Jam Pelajaran ke-{$jk} (" . substr($p['mulai'], 0, 5) . " - " . substr($p['selesai'], 0, 5) . " WIB)",
                    'badge_class'   => 'bg-emerald-600 text-white shadow-xs',
                ];
            }
        }

        // Cek Waktu Istirahat
        if ($isJumat) {
            if ($time > '09:00:00' && $time < '09:30:00') {
                return [
                    'is_kbm'        => false,
                    'status'        => 'istirahat',
                    'jam_ke'        => null,
                    'label_jam_ke'  => 'Istirahat',
                    'rentang_waktu' => '09:00 - 09:30 WIB',
                    'deskripsi'     => 'Waktu Istirahat (09:00 - 09:30 WIB)',
                    'badge_class'   => 'bg-amber-500 text-white shadow-xs',
                ];
            }
        } else {
            if ($time > '10:00:00' && $time < '10:30:00') {
                return [
                    'is_kbm'        => false,
                    'status'        => 'istirahat',
                    'jam_ke'        => null,
                    'label_jam_ke'  => 'Istirahat 1',
                    'rentang_waktu' => '10:00 - 10:30 WIB',
                    'deskripsi'     => 'Waktu Istirahat Pertama (10:00 - 10:30 WIB)',
                    'badge_class'   => 'bg-amber-500 text-white shadow-xs',
                ];
            }
            if ($time > '12:00:00' && $time < '12:30:00') {
                return [
                    'is_kbm'        => false,
                    'status'        => 'istirahat',
                    'jam_ke'        => null,
                    'label_jam_ke'  => 'Istirahat 2 (Ishoma)',
                    'rentang_waktu' => '12:00 - 12:30 WIB',
                    'deskripsi'     => 'Waktu Istirahat Kedua / Sholat & Makan (12:00 - 12:30 WIB)',
                    'badge_class'   => 'bg-amber-500 text-white shadow-xs',
                ];
            }
        }

        return [
            'is_kbm'        => false,
            'status'        => 'jeda',
            'jam_ke'        => null,
            'label_jam_ke'  => 'Pergantian Jam',
            'rentang_waktu' => '-',
            'deskripsi'     => 'Pergantian Jam Pelajaran',
            'badge_class'   => 'bg-blue-100 text-blue-800 border border-blue-200',
        ];
    }
}
