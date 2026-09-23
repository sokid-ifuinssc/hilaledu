<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramKerjaTugasTambahan extends Model
{
    protected $table = 'program_kerja_tugas_tambahans';

    protected $fillable = [
        'guru_user_id',
        'tugas_tambahan',
        'tahun_ajaran',
        'semester',
        'nama_program',
        'tujuan',
        'target_waktu',
        'indikator_keberhasilan',
        'anggaran',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'anggaran' => 'decimal:2',
        ];
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    public function realisasis(): HasMany
    {
        return $this->hasMany(RealisasiTugasTambahan::class, 'program_kerja_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'tercapai'       => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'sedang_berjalan'=> 'bg-blue-100 text-blue-800 border-blue-300',
            'tertunda'       => 'bg-rose-100 text-rose-800 border-rose-300',
            default          => 'bg-amber-100 text-amber-800 border-amber-300',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'tercapai'       => 'Tercapai',
            'sedang_berjalan'=> 'Sedang Berjalan',
            'tertunda'       => 'Tertunda',
            default          => 'Terencana',
        };
    }
}
