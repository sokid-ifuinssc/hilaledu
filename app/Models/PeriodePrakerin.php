<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model PeriodePrakerin
 * Menentukan rentang gelombang pelaksanaan praktik kerja lapangan / industri.
 */
class PeriodePrakerin extends Model
{
    use HasFactory;

    protected $table = 'periode_prakerin';

    protected $fillable = [
        'tahun_ajaran_id',
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Relasi ke Tahun Ajaran sekolah
     */
    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    /**
     * Relasi ke data penempatan pada periode ini
     */
    public function penempatans(): HasMany
    {
        return $this->hasMany(Penempatan::class, 'periode_prakerin_id');
    }
}
