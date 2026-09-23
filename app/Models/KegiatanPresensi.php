<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KegiatanPresensi extends Model
{
    protected $table = 'kegiatan_presensis';

    protected $fillable = [
        'kegiatan_sekolah_id',
        'guru_user_id',
        'status', // hadir, terlambat, izin, sakit
        'waktu_presensi',
        'catatan',
        'foto_bukti',
    ];

    protected function casts(): array
    {
        return [
            'waktu_presensi' => 'datetime',
        ];
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(KegiatanSekolah::class, 'kegiatan_sekolah_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'hadir'     => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin'      => 'Izin',
            'sakit'     => 'Sakit',
            default     => ucfirst($this->status),
        };
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'hadir'     => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'terlambat' => 'bg-amber-100 text-amber-800 border-amber-300',
            'izin'      => 'bg-blue-100 text-blue-800 border-blue-300',
            'sakit'     => 'bg-purple-100 text-purple-800 border-purple-300',
            default     => 'bg-slate-100 text-slate-700 border-slate-300',
        };
    }
}
