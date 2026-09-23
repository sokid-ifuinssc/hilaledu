<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Penempatan Prakerin
 *
 * Algoritma Penempatan:
 * Menghubungkan secara relasional siswa yang diterjunkan prakerin dengan mitra DU/DI,
 * guru pembimbing internal sekolah, serta instruktur pembimbing eksternal DU/DI
 * pada satu periode kalender prakerin aktif.
 */
class Penempatan extends Model
{
    use HasFactory;

    protected $table = 'penempatan';

    protected $fillable = [
        'periode_prakerin_id',
        'siswa_id',
        'dudi_id',
        'guru_id',
        'pembimbing_dudi_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Relasi ke Periode Prakerin
     */
    public function periodePrakerin(): BelongsTo
    {
        return $this->belongsTo(PeriodePrakerin::class, 'periode_prakerin_id');
    }

    /**
     * Relasi ke data Siswa
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Relasi ke Industri Mitra DU/DI
     */
    public function dudi(): BelongsTo
    {
        return $this->belongsTo(Dudi::class, 'dudi_id');
    }

    /**
     * Relasi ke Guru Pembimbing Sekolah
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    /**
     * Relasi ke Pembimbing Lapangan DU/DI
     */
    public function pembimbingDudi(): BelongsTo
    {
        return $this->belongsTo(PembimbingDudi::class, 'pembimbing_dudi_id');
    }

    /**
     * Accessor label status
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'belum_mulai' => 'Belum Mulai',
            'aktif'       => 'Aktif Berjalan',
            'selesai'     => 'Selesai',
            default       => ucfirst($this->status),
        };
    }

    /**
     * Accessor warna badge bootstrap
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'belum_mulai' => 'warning',
            'aktif'       => 'success',
            'selesai'     => 'secondary',
            default       => 'info',
        };
    }
}
