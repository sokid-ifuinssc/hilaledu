<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisPelanggaran extends Model
{
    use HasFactory;


    protected $fillable = [
        'kategori_pelanggaran_id',
        'kode',
        'nama',
        'poin',
        'deskripsi',
    ];

    // Relationships
    public function kategori()
    {
        return $this->belongsTo(KategoriPelanggaran::class, 'kategori_pelanggaran_id');
    }

    public function pelanggarans()
    {
        return $this->hasMany(Pelanggaran::class);
    }
}
