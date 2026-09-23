<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KeluhanKbm extends Model
{
    protected $table = 'keluhan_kbms';

    protected $fillable = [
        'siswa_user_id',
        'target_guru_user_id',
        'mata_pelajaran_id',
        'kelas',
        'tanggal_kbm',
        'kategori_masalah',
        'isi_keluhan',
        'harapan_siswa',
        'status', // baru, diproses, selesai
        'catatan_tindak_lanjut',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kbm' => 'date',
        ];
    }

    public function targetGuru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_guru_user_id');
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_user_id');
    }

    public function saranPerbaikan(): HasMany
    {
        return $this->hasMany(SaranPerbaikan::class, 'keluhan_kbm_id');
    }

    /**
     * Label identitas pengirim resmi bersifat 100% anonim sesuai arahan
     */
    public function getPengirimAnonimAttribute(): string
    {
        return "Siswa {$this->kelas} (Identitas Dirahasiakan)";
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'selesai'   => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'diproses'  => 'bg-blue-100 text-blue-800 border-blue-300',
            default     => 'bg-amber-100 text-amber-800 border-amber-300',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'selesai'   => 'Selesai Ditindaklanjuti',
            'diproses'  => 'Sedang Ditindaklanjuti',
            default     => 'Keluhan Baru',
        };
    }
}
