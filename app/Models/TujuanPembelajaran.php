<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TujuanPembelajaran extends Model
{
    protected $table = 'tujuan_pembelajarans';

    protected $fillable = [
        'capaian_pembelajaran_id',
        'kode_tp',
        'deskripsi',
        'kktp',
        'alokasi_jp',
        'lingkup_materi',
        'semester',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'alokasi_jp' => 'integer',
            'urutan'     => 'integer',
        ];
    }

    public function capaianPembelajaran(): BelongsTo
    {
        return $this->belongsTo(CapaianPembelajaran::class, 'capaian_pembelajaran_id');
    }

    public function rencanaPembelajaran(): HasMany
    {
        return $this->hasMany(RencanaPembelajaran::class, 'tujuan_pembelajaran_id');
    }

    public function alurTujuanPembelajaran(): HasMany
    {
        return $this->hasMany(AlurTujuanPembelajaran::class, 'tujuan_pembelajaran_id');
    }
}
