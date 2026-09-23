<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RencanaPembelajaran extends Model
{
    protected $table = 'rencana_pembelajarans';

    protected $fillable = [
        'jadwal_pelajaran_id',
        'guru_user_id',
        'tujuan_pembelajaran_id',
        'tanggal_rencana',
        'pertemuan_ke',
        'materi_pokok',
        'aktivitas_pendahuluan',
        'aktivitas_inti',
        'aktivitas_penutup',
        'media_sumber',
        'bentuk_asesmen',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'pertemuan_ke' => 'integer',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalPelajaran::class, 'jadwal_pelajaran_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    public function tujuanPembelajaran(): BelongsTo
    {
        return $this->belongsTo(TujuanPembelajaran::class, 'tujuan_pembelajaran_id');
    }

    public function laporanKbm(): HasOne
    {
        return $this->hasOne(LaporanKbm::class, 'rencana_pembelajaran_id');
    }
}
