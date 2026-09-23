<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Industri extends Model
{
    protected $fillable = [
        'nama',
        'alamat',
        'kontak',
    ];

    /**
     * Relasi ke Jurnal Prakerin
     */
    public function jurnalPrakerins()
    {
        return $this->hasMany(JurnalPrakerin::class, 'industri_id');
    }
}
