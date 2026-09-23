<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengelolaAkademik extends Model
{
    protected $table = 'pengelola_akademiks';

    protected $fillable = [
        'user_id',
        'can_manage_jadwal',
        'can_manage_mapel',
        'can_view_laporan_kehadiran',
        'can_view_laporan_kbm',
        'ditunjuk_oleh_user_id',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'can_manage_jadwal'          => 'boolean',
            'can_manage_mapel'           => 'boolean',
            'can_view_laporan_kehadiran' => 'boolean',
            'can_view_laporan_kbm'       => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ditunjukOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditunjuk_oleh_user_id');
    }
}
