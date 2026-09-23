<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasTambahan extends Model
{
    use HasFactory;

    protected $table = 'master_tugas_tambahan';

    protected $fillable = [
        'nama',
        'kode',
        'kategori',
        'deskripsi',
        'nominal_gaji',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
        ];
    }
}
