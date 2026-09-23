<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TugasKbm extends Model
{
    protected $table = 'tugas_kbms';

    protected $fillable = [
        'jadwal_pelajaran_id',
        'tanggal',
        'guru_user_id',
        'dibuat_oleh_user_id',
        'deskripsi_tugas',
        'file_lampiran',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalPelajaran::class, 'jadwal_pelajaran_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh_user_id');
    }
}
