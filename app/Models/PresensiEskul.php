<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiEskul extends Model
{
    use HasFactory;

    protected $table = 'presensi_eskuls';

    protected $fillable = [
        'ekstrakurikuler_id',
        'laporan_kegiatan_id',
        'rencana_kegiatan_id',
        'tanggal',
        'siswa_id',
        'status',
        'keterangan',
        'metode_absen',
        'diinput_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function ekstrakurikuler(): BelongsTo
    {
        return $this->belongsTo(Ekstrakurikuler::class, 'ekstrakurikuler_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function laporanKegiatan(): BelongsTo
    {
        return $this->belongsTo(LaporanKegiatanEskul::class, 'laporan_kegiatan_id');
    }

    public function rencanaKegiatan(): BelongsTo
    {
        return $this->belongsTo(RencanaKegiatanEskul::class, 'rencana_kegiatan_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diinput_oleh');
    }
}
