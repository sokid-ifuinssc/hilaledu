<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiGuru extends Model
{
    protected $table = 'absensi_gurus';

    protected $fillable = [
        'jadwal_pelajaran_id',
        'guru_user_id',
        'tanggal',
        'jam_absen',
        'status', // hadir, terlambat, izin, sakit, tugas_luar
        'terlambat_menit',
        'catatan',
        'lampiran_bukti',
    ];

    protected function casts(): array
    {
        return [
            'terlambat_menit' => 'integer',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalPelajaran::class, 'jadwal_pelajaran_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    /**
     * Tentukan status kehadiran dengan aturan toleransi 10 menit.
     * Jika memilih hadir dan waktu absen melebihi jam_mulai + 10 menit, status otomatis 'terlambat'.
     */
    public static function determineStatus(JadwalPelajaran $jadwal, string $inputStatus, ?Carbon $now = null): array
    {
        $now = $now ?? now();
        $jamMulai = Carbon::parse($now->format('Y-m-d') . ' ' . $jadwal->jam_mulai);
        $batasToleransi = $jamMulai->copy()->addMinutes(10);

        if ($inputStatus === 'hadir') {
            if ($now->greaterThan($batasToleransi)) {
                $terlambatMenit = intval($now->diffInMinutes($jamMulai));
                return [
                    'status' => 'terlambat',
                    'terlambat_menit' => $terlambatMenit,
                    'catatan' => "Hadir terlambat {$terlambatMenit} menit dari jadwal (" . $jamMulai->format('H:i') . "). Batas toleransi 10 menit telah terlampaui.",
                ];
            } else {
                return [
                    'status' => 'hadir',
                    'terlambat_menit' => 0,
                    'catatan' => 'Hadir tepat waktu sesuai jadwal.',
                ];
            }
        }

        // Izin, sakit, atau tugas luar tidak diubah menjadi terlambat
        return [
            'status' => $inputStatus,
            'terlambat_menit' => 0,
            'catatan' => null,
        ];
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'hadir'      => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'terlambat'  => 'bg-amber-100 text-amber-800 border-amber-300',
            'izin'       => 'bg-blue-100 text-blue-800 border-blue-300',
            'sakit'      => 'bg-purple-100 text-purple-800 border-purple-300',
            'tugas_luar' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            default      => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'hadir'      => 'Hadir Tepat Waktu',
            'terlambat'  => "Hadir Terlambat ({$this->terlambat_menit} mnt)",
            'izin'       => 'Izin',
            'sakit'      => 'Sakit',
            'tugas_luar' => 'Tugas Luar',
            default      => ucfirst($this->status),
        };
    }
}
