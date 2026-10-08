<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanKegiatanEskul extends Model
{
    use HasFactory;

    protected $table = 'laporan_kegiatan_eskuls';

    protected $fillable = [
        'ekstrakurikuler_id',
        'rencana_kegiatan_id',
        'tanggal_kegiatan',
        'pertemuan_ke',
        'nama_kegiatan',
        'ringkasan_materi',
        'status_pelaksanaan',
        'catatan_kegiatan',
        'foto_dokumentasi',
        'jumlah_hadir',
        'jumlah_izin',
        'jumlah_sakit',
        'jumlah_alpa',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kegiatan' => 'date',
            'pertemuan_ke'    => 'integer',
            'jumlah_hadir'    => 'integer',
            'jumlah_izin'     => 'integer',
            'jumlah_sakit'    => 'integer',
            'jumlah_alpa'     => 'integer',
        ];
    }

    public function ekstrakurikuler(): BelongsTo
    {
        return $this->belongsTo(Ekstrakurikuler::class, 'ekstrakurikuler_id');
    }

    public function rencanaKegiatan(): BelongsTo
    {
        return $this->belongsTo(RencanaKegiatanEskul::class, 'rencana_kegiatan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(PresensiEskul::class, 'laporan_kegiatan_id');
    }

    public function getTotalSiswaAttribute(): int
    {
        return $this->jumlah_hadir + $this->jumlah_izin + $this->jumlah_sakit + $this->jumlah_alpa;
    }

    public function getPersentaseKehadiranAttribute(): float
    {
        $total = $this->total_siswa;
        return $total > 0 ? round(($this->jumlah_hadir / $total) * 100, 1) : 0;
    }
}
