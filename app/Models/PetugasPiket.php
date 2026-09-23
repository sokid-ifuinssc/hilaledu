<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PetugasPiket extends Model
{
    protected $table = 'petugas_pikets';

    protected $fillable = [
        'user_id',
        'hari',
        'tanggal',
        'tahun_ajaran',
        'semester',
        'lokasi_pos',
        'keterangan',
        'is_aktif',
        'ditugaskan_oleh_user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
            'tanggal'  => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ditugaskanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditugaskan_oleh_user_id');
    }

    /**
     * Scope untuk penugasan yang aktif hari ini (berdasarkan hari atau tanggal spesifik)
     */
    public function scopeActiveToday($query, ?string $hari = null, ?string $tanggal = null)
    {
        $hari = $hari ?: JadwalPelajaran::getHariIndonesia();
        $tanggal = $tanggal ?: date('Y-m-d');

        return $query->where('is_aktif', true)
            ->where(function ($q) use ($hari, $tanggal) {
                $q->where('hari', $hari)
                  ->orWhere('tanggal', $tanggal);
            });
    }

    /**
     * Helper mendapatkan daftar petugas piket hari ini
     */
    public static function getPiketHariIni(?string $hari = null, ?string $tanggal = null)
    {
        return static::with('user')
            ->activeToday($hari, $tanggal)
            ->get();
    }
}
