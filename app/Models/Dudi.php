<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Dudi (Dunia Usaha / Dunia Industri)
 * Mitra tempat siswa melaksanakan Praktik Kerja Lapangan / Prakerin.
 */
class Dudi extends Model
{
    use HasFactory;

    protected $table = 'dudi';

    protected $fillable = [
        'nama',
        'alamat',
        'no_telp',
        'email',
        'bidang_usaha',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Relasi ke Pembimbing Lapangan DU/DI
     */
    public function pembimbingDudi(): HasMany
    {
        return $this->hasMany(PembimbingDudi::class, 'dudi_id');
    }

    /**
     * Relasi ke riwayat Penempatan Siswa
     */
    public function penempatans(): HasMany
    {
        return $this->hasMany(Penempatan::class, 'dudi_id');
    }
}
