<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model PembimbingDudi
 * Instruktur / Pembimbing lapangan dari pihak industri.
 */
class PembimbingDudi extends Model
{
    use HasFactory;

    protected $table = 'pembimbing_dudi';

    protected $fillable = [
        'dudi_id',
        'nama',
        'jabatan',
        'no_hp',
        'email',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Relasi ke Mitra DU/DI
     */
    public function dudi(): BelongsTo
    {
        return $this->belongsTo(Dudi::class, 'dudi_id');
    }

    /**
     * Relasi ke penugasan Penempatan siswa
     */
    public function penempatans(): HasMany
    {
        return $this->hasMany(Penempatan::class, 'pembimbing_dudi_id');
    }
}
