<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiHarianSiswa extends Model
{
    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'tanggal',
        'status',
        'keterangan',
        'diinput_oleh',
    ];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function penginput()
    {
        return $this->belongsTo(User::class, 'diinput_oleh');
    }
}
