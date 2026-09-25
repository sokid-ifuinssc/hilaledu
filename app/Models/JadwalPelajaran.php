<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalPelajaran extends Model
{
    protected $table = 'jadwal_pelajarans';

    protected $fillable = [
        'hari',
        'jam_ke_mulai',
        'jam_ke_selesai',
        'jam_mulai',
        'jam_selesai',
        'kelas',
        'mata_pelajaran_id',
        'guru_user_id',
        'ruang',
        'tahun_ajaran',
        'semester',
        'is_locked',
    ];

    /**
     * Konfigurasi Slot Waktu Resmi:
     * - Senin s.d. Kamis & Sabtu: 8 jam @ 45 menit (Istirahat 1: 10.00-10.30, Istirahat 2: 12.00-12.30)
     * - Jumat: 6 jam @ 30 menit (Istirahat: 09.00-09.30)
     */
    public static function getPeriodsConfig(): array
    {
        return [
            'reguler' => [
                1 => ['mulai' => '07:00:00', 'selesai' => '07:45:00', 'label' => '07.00 - 07.45'],
                2 => ['mulai' => '07:45:00', 'selesai' => '08:30:00', 'label' => '07.45 - 08.30'],
                3 => ['mulai' => '08:30:00', 'selesai' => '09:15:00', 'label' => '08.30 - 09.15'],
                4 => ['mulai' => '09:15:00', 'selesai' => '10:00:00', 'label' => '09.15 - 10.00'],
                // Istirahat 1: 10.00 - 10.30
                5 => ['mulai' => '10:30:00', 'selesai' => '11:15:00', 'label' => '10.30 - 11.15'],
                6 => ['mulai' => '11:15:00', 'selesai' => '12:00:00', 'label' => '11.15 - 12.00'],
                // Istirahat 2: 12.00 - 12.30
                7 => ['mulai' => '12:30:00', 'selesai' => '13:15:00', 'label' => '12.30 - 13.15'],
                8 => ['mulai' => '13:15:00', 'selesai' => '14:00:00', 'label' => '13.15 - 14.00'],
            ],
            'jumat' => [
                1 => ['mulai' => '07:00:00', 'selesai' => '07:30:00', 'label' => '07.00 - 07.30'],
                2 => ['mulai' => '07:30:00', 'selesai' => '08:00:00', 'label' => '07.30 - 08.00'],
                3 => ['mulai' => '08:00:00', 'selesai' => '08:30:00', 'label' => '08.00 - 08.30'],
                4 => ['mulai' => '08:30:00', 'selesai' => '09:00:00', 'label' => '08.30 - 09.00'],
                // Istirahat: 09.00 - 09.30
                5 => ['mulai' => '09:30:00', 'selesai' => '10:00:00', 'label' => '09.30 - 10.00'],
                6 => ['mulai' => '10:00:00', 'selesai' => '10:30:00', 'label' => '10.00 - 10.30'],
            ],
        ];
    }

    /**
     * Hitung jam_mulai dan jam_selesai otomatis berdasarkan hari, jam_ke_mulai, dan jam_ke_selesai
     */
    public static function calculateTimesFromJamKe(string $hari, int $mulai, int $selesai): array
    {
        $periods = static::getPeriodsConfig();
        $isJumat = ($hari === 'Jumat');
        $schedule = $isJumat ? $periods['jumat'] : $periods['reguler'];

        $maxPeriod = $isJumat ? 6 : 8;
        $mulai = max(1, min($mulai, $maxPeriod));
        $selesai = max($mulai, min($selesai, $maxPeriod));

        $jamMulai = $schedule[$mulai]['mulai'] ?? '07:00:00';
        $jamSelesai = $schedule[$selesai]['selesai'] ?? ($isJumat ? '10:30:00' : '14:00:00');

        return [
            'jam_ke_mulai'   => $mulai,
            'jam_ke_selesai' => $selesai,
            'jam_mulai'      => $jamMulai,
            'jam_selesai'    => $jamSelesai,
        ];
    }

    /**
     * Pengecekan bentrok jadwal guru dan bentrok jadwal kelas
     */
    public static function checkConflict(string $hari, string $jamMulai, string $jamSelesai, string $kelas, ?int $guruUserId = null, $ignoreId = null): array
    {
        // 1. Cek bentrok guru di kelas lain (hanya jika guru sudah ditentukan)
        if (!empty($guruUserId)) {
            $guruConflict = static::with(['mataPelajaran'])
                ->where('hari', $hari)
                ->where('guru_user_id', $guruUserId)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->where(function ($q) use ($jamMulai, $jamSelesai) {
                    $q->where('jam_mulai', '<', $jamSelesai)
                      ->where('jam_selesai', '>', $jamMulai);
                })
                ->first();

            if ($guruConflict) {
                $guruName = $guruConflict->guru->name ?? 'Guru';
                return [
                    'conflict' => true,
                    'type'     => 'guru',
                    'message'  => "Bentrok Guru! {$guruName} sudah memiliki jadwal mengajar di kelas {$guruConflict->kelas} ({$guruConflict->jam_ke_label}) pada hari {$hari}.",
                ];
            }
        }

        // 2. Cek bentrok kelas di mapel lain
        $kelasConflict = static::with(['mataPelajaran', 'guru'])
            ->where('hari', $hari)
            ->where('kelas', $kelas)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($q) use ($jamMulai, $jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai)
                  ->where('jam_selesai', '>', $jamMulai);
            })
            ->first();

        if ($kelasConflict) {
            $mapelName = $kelasConflict->mataPelajaran->nama ?? 'Mata Pelajaran Lain';
            $guruName = $kelasConflict->guru->name ?? 'Guru Lain';
            return [
                'conflict' => true,
                'type'     => 'kelas',
                'message'  => "Bentrok Kelas! Kelas {$kelas} sudah terisi mapel {$mapelName} bersama {$guruName} ({$kelasConflict->jam_ke_label}) pada hari {$hari}.",
            ];
        }

        return ['conflict' => false];
    }

    /**
     * Label representasi Jam Ke (misal: "Jam ke-1 s.d. 3" atau "Jam ke-2")
     */
    public function getJamKeLabelAttribute(): string
    {
        if (!empty($this->jam_ke_mulai) && !empty($this->jam_ke_selesai)) {
            return ($this->jam_ke_mulai == $this->jam_ke_selesai)
                ? "Jam ke-{$this->jam_ke_mulai}"
                : "Jam ke-{$this->jam_ke_mulai} s.d. {$this->jam_ke_selesai}";
        }

        // Estimasi dari jam_mulai & jam_selesai jika kolom jam_ke belum terisi
        $isJumat = ($this->hari === 'Jumat');
        $periods = static::getPeriodsConfig()[$isJumat ? 'jumat' : 'reguler'];
        $matched = [];
        foreach ($periods as $jk => $p) {
            if ($p['mulai'] < $this->jam_selesai && $p['selesai'] > $this->jam_mulai) {
                $matched[] = $jk;
            }
        }

        if (!empty($matched)) {
            $first = min($matched);
            $last = max($matched);
            return ($first == $last) ? "Jam ke-{$first}" : "Jam ke-{$first} s.d. {$last}";
        }

        return substr($this->jam_mulai, 0, 5) . ' - ' . substr($this->jam_selesai, 0, 5);
    }

    /**
     * Waktu berformat ramah (misal: "07.00 - 09.15")
     */
    public function getWaktuFormattedAttribute(): string
    {
        return substr($this->jam_mulai, 0, 5) . ' - ' . substr($this->jam_selesai, 0, 5);
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    public function rencanaPembelajaran(): HasMany
    {
        return $this->hasMany(RencanaPembelajaran::class, 'jadwal_pelajaran_id');
    }

    public function laporanKbm(): HasMany
    {
        return $this->hasMany(LaporanKbm::class, 'jadwal_pelajaran_id');
    }

    public function absensiGuru(): HasMany
    {
        return $this->hasMany(AbsensiGuru::class, 'jadwal_pelajaran_id');
    }

    /**
     * Dapatkan hari ini dalam bahasa Indonesia
     */
    public static function getHariIndonesia(?Carbon $date = null): string
    {
        $date = $date ?? now();
        $map = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
        return $map[$date->dayOfWeekIso] ?? 'Senin';
    }

    /**
     * Cek apakah jadwal ini jatuh pada hari ini
     */
    public function isToday(): bool
    {
        return strtolower($this->hari) === strtolower(self::getHariIndonesia());
    }

    /**
     * Cek apakah guru saat ini sedang cuti pada tanggal tertentu
     */
    public function getCutiAktif(?string $tanggal = null): ?CutiGuru
    {
        $tgl = $tanggal ?? date('Y-m-d');
        return CutiGuru::where('guru_user_id', $this->guru_user_id)
            ->whereIn('status', ['disetujui_waka', 'disetujui_kepsek'])
            ->where('tanggal_mulai', '<=', $tgl)
            ->where('tanggal_selesai', '>=', $tgl)
            ->first();
    }

    /**
     * Dapatkan guru yang bertugas (guru asli atau guru pengganti inval jika sedang cuti)
     */
    public function getGuruPengajar(?string $tanggal = null): User
    {
        $cuti = $this->getCutiAktif($tanggal);
        if ($cuti && $cuti->guruPengganti) {
            return $cuti->guruPengganti;
        }
        return $this->guru ?? new User(['name' => 'Belum Ditentukan']);
    }

    /**
     * Cek apakah jadwal sedang berlangsung sekarang
     */
    public function isRunningNow(): bool
    {
        if (!$this->isToday()) return false;
        $now = now()->format('H:i:s');
        return ($now >= $this->jam_mulai && $now <= $this->jam_selesai);
    }

    /**
     * Status presensi guru untuk jadwal hari ini
     */
    public function getPresensiHariIniAttribute(): ?AbsensiGuru
    {
        $today = date('Y-m-d');
        return $this->absensiGuru()->where('tanggal', $today)->first();
    }

    /**
     * Status laporan KBM guru untuk jadwal hari ini
     */
    public function getLaporanKbmHariIniAttribute(): ?LaporanKbm
    {
        $today = date('Y-m-d');
        return $this->laporanKbm()->where('tanggal_realisasi', $today)->first();
    }

    public function tugasKbms(): HasMany
    {
        return $this->hasMany(TugasKbm::class, 'jadwal_pelajaran_id');
    }

    /**
     * Tugas KBM untuk jadwal hari ini
     */
    public function getTugasHariIniAttribute(): ?TugasKbm
    {
        $today = date('Y-m-d');
        return $this->tugasKbms()->where('tanggal', $today)->first();
    }
}
