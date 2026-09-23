<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CapaianPembelajaran extends Model
{
    protected $table = 'capaian_pembelajarans';

    protected $fillable = [
        'mata_pelajaran_id',
        'guru_user_id',
        'fase',     // E (Kelas X), F (Kelas XI & XII)
        'tingkat',  // X, XI, XII
        'elemen',
        'deskripsi',
        'tahun_ajaran',
        'semester',
    ];

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    public function tujuanPembelajaran(): HasMany
    {
        return $this->hasMany(TujuanPembelajaran::class, 'capaian_pembelajaran_id')->orderBy('urutan');
    }
}
