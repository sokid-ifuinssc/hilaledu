<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanKbmPresensi extends Model
{
    protected $table = 'laporan_kbm_presensis';

    protected $fillable = [
        'laporan_kbm_id',
        'siswa_user_id',
        'status', // hadir, sakit, izin, alpa, terlambat
        'keterangan',
    ];

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanKbm::class, 'laporan_kbm_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_user_id');
    }

    /**
     * Dapatkan nama siswa dengan aman, baik tersimpan ID dari db_hilaledu.siswas maupun db_akademik.users
     */
    public function getNamaSiswaAttribute(): string
    {
        if ($this->relationLoaded('siswa') && $this->siswa) {
            return $this->siswa->nama_lengkap ?? $this->siswa->nama ?? 'Siswa';
        }

        try {
            $siswa = Siswa::find($this->siswa_user_id);
            if ($siswa) {
                return $siswa->nama_lengkap ?? $siswa->nama ?? 'Siswa';
            }
        } catch (\Throwable $e) {}

        try {
            $user = User::find($this->siswa_user_id);
            if ($user) {
                return $user->name;
            }
        } catch (\Throwable $e) {}

        try {
            $siswaByUid = Siswa::where('user_id', $this->siswa_user_id)->first();
            if ($siswaByUid) {
                return $siswaByUid->nama_lengkap ?? $siswaByUid->nama ?? 'Siswa';
            }
        } catch (\Throwable $e) {}

        return "Siswa #{$this->siswa_user_id}";
    }
}
