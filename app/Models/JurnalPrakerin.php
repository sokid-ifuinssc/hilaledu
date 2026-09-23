<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalPrakerin extends Model
{
    protected $fillable = [
        'siswa_id',
        'industri_id',
        'tanggal',
        'kegiatan',
        'status', // pending, approved, rejected
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    /**
     * Relasi ke Siswa (User)
     */
    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    /**
     * Relasi ke Industri Mitra
     */
    public function industri()
    {
        return $this->belongsTo(Industri::class, 'industri_id');
    }
}
