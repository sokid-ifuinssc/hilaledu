<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TracerAlumni extends Model
{
    protected $fillable = [
        'user_id',
        'tahun_lulus',
        'status_saat_ini',
        'nama_instansi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
