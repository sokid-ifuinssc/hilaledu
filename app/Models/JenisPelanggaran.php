<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisPelanggaran extends Model
{
    use HasFactory;


    protected $fillable = [
        'nama',
        'poin',
        'deskripsi',
    ];

    // Relationships
    public function pelanggarans()
    {
        return $this->hasMany(Pelanggaran::class);
    }
}
