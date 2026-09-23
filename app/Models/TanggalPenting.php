<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TanggalPenting extends Model
{
    use HasFactory;


    protected $fillable = [
        'tahun_ajaran_id',
        'nama',
        'jenis',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    // Relationships
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    // Helpers
    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis) {
            'mulai_sekolah' => 'Mulai Sekolah',
            'uts' => 'UTS',
            'uas' => 'UAS',
            'libur' => 'Libur',
            'lainnya' => 'Lainnya',
            default => $this->jenis,
        };
    }
}
