<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerangkatAjar extends Model
{
    protected $table = 'perangkat_ajars';

    protected $fillable = [
        'guru_user_id',
        'mata_pelajaran_id',
        'kelas',
        'tahun_ajaran',
        'semester',
        'judul',
        'link_gdrive',
        'kelengkapan_berkas',
        'status', // menunggu_review, disetujui, perlu_perbaikan
        'catatan_waka',
        'verified_by_user_id',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'kelengkapan_berkas' => 'array',
            'verified_at'        => 'datetime',
        ];
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'disetujui'        => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'perlu_perbaikan'  => 'bg-rose-100 text-rose-800 border-rose-300',
            default            => 'bg-amber-100 text-amber-800 border-amber-300',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'disetujui'        => 'Disetujui / Valid',
            'perlu_perbaikan'  => 'Perlu Perbaikan',
            default            => 'Menunggu Review',
        };
    }
}
