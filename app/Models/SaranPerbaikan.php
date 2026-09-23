<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaranPerbaikan extends Model
{
    protected $table = 'saran_perbaikans';

    protected $fillable = [
        'keluhan_kbm_id',
        'target_guru_user_id',
        'author_user_id',
        'role_author', // Kepala Sekolah, Waka Kurikulum, Waka Kesiswaan, Kaprog, Wali Kelas
        'judul_arahan',
        'arahan_pembinaan',
        'rekomendasi_tindakan',
        'tanggapan_guru',
        'status', // dibaca, ditanggapi, selesai
    ];

    public function keluhan(): BelongsTo
    {
        return $this->belongsTo(KeluhanKbm::class, 'keluhan_kbm_id');
    }

    public function targetGuru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_guru_user_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'selesai'    => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'ditanggapi' => 'bg-blue-100 text-blue-800 border-blue-300',
            default      => 'bg-purple-100 text-purple-800 border-purple-300',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'selesai'    => 'Selesai',
            'ditanggapi' => 'Sudah Ditanggapi Guru',
            default      => 'Menunggu Tanggapan Guru',
        };
    }
}
