<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlurTujuanPembelajaran extends Model
{
    use HasFactory;

    protected $table = 'alur_tujuan_pembelajarans';

    protected $fillable = [
        'guru_user_id',
        'mata_pelajaran_id',
        'capaian_pembelajaran_id',
        'tujuan_pembelajaran_id',
        'fase',
        'tingkat',
        'semester',
        'alur_ke',
        'kode_atp',
        'materi_pokok',
        'alokasi_jp',
        'profil_pelajar_pancasila',
        'keterangan',
    ];

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function capaianPembelajaran()
    {
        return $this->belongsTo(CapaianPembelajaran::class, 'capaian_pembelajaran_id');
    }

    public function tujuanPembelajaran()
    {
        return $this->belongsTo(TujuanPembelajaran::class, 'tujuan_pembelajaran_id');
    }
}
