<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisSertifikat extends Model
{
    protected $table = 'jenis_sertifikats';

    protected $fillable = [
        'kategori',
        'nama_sertifikat',
        'deskripsi',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
        ];
    }

    public function dokumenSiswas(): HasMany
    {
        return $this->hasMany(DokumenSertifikatSiswa::class, 'jenis_sertifikat_id');
    }
}

