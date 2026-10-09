<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RencanaKegiatanEskul extends Model
{
    use HasFactory;

    protected $table = 'rencana_kegiatan_eskuls';

    protected $fillable = [
        'ekstrakurikuler_id',
        'pertemuan_ke',
        'tipe_jadwal',
        'minggu_ke',
        'tanggal_rencana',
        'nama_kegiatan',
        'deskripsi_rencana',
        'target_pencapaian',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_rencana' => 'date',
            'pertemuan_ke'    => 'integer',
            'minggu_ke'       => 'integer',
        ];
    }

    public function ekstrakurikuler(): BelongsTo
    {
        return $this->belongsTo(Ekstrakurikuler::class, 'ekstrakurikuler_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function laporan(): HasOne
    {
        return $this->hasOne(LaporanKegiatanEskul::class, 'rencana_kegiatan_id');
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(PresensiEskul::class, 'rencana_kegiatan_id');
    }

    public function getIsTerlaksanaAttribute(): bool
    {
        return $this->laporan()->exists();
    }
}
