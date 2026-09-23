<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiGuru extends Model
{
    protected $table = 'cuti_gurus';

    protected $fillable = [
        'guru_user_id',
        'guru_pengganti_id',
        'jenis_cuti',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'lampiran_surat',
        'status', // diajukan, disetujui_waka, disetujui_kepsek, ditolak
        'catatan_approval',
        'approved_by_user_id',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    public function guruPengganti(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_pengganti_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'disetujui_kepsek', 'disetujui_waka' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'ditolak'                            => 'bg-rose-100 text-rose-800 border-rose-300',
            default                              => 'bg-amber-100 text-amber-800 border-amber-300',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'disetujui_kepsek' => 'Disetujui Kepala Sekolah',
            'disetujui_waka'   => 'Disetujui Waka Kurikulum',
            'ditolak'          => 'Ditolak',
            default            => 'Menunggu Persetujuan',
        };
    }
}
