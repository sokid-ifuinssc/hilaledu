<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;


    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'user_id',
        'aksi',
        'status_dari',
        'status_ke',
        'data_lama',
        'data_baru',
        'keterangan',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'data_lama' => 'array',
            'data_baru' => 'array',
        ];
    }

    // =====================
    // Relationships
    // =====================

    public function auditable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // =====================
    // Scopes
    // =====================

    public function scopeForModel($query, Model $model)
    {
        return $query->where('auditable_type', get_class($model))
                     ->where('auditable_id', $model->id);
    }

    public function scopeByAksi($query, string $aksi)
    {
        return $query->where('aksi', $aksi);
    }

    // =====================
    // Accessors
    // =====================

    public function getAksiLabelAttribute(): string
    {
        return match ($this->aksi) {
            'created' => 'Dibuat',
            'status_changed' => 'Status Berubah',
            'approval' => 'Persetujuan',
            'laporan' => 'Laporan Diisi',
            'cetak_surat' => 'Surat Dicetak',
            'rejected' => 'Ditolak',
            'closed' => 'Ditutup/Selesai',
            'revisi' => 'Direvisi',
            default => $this->aksi,
        };
    }

    public function getAksiBadgeAttribute(): string
    {
        return match ($this->aksi) {
            'created' => 'bg-blue-100 text-blue-800',
            'status_changed' => 'bg-yellow-100 text-yellow-800',
            'approval' => 'bg-green-100 text-green-800',
            'laporan' => 'bg-purple-100 text-purple-800',
            'cetak_surat' => 'bg-indigo-100 text-indigo-800',
            'rejected' => 'bg-red-100 text-red-800',
            'closed' => 'bg-emerald-100 text-emerald-800',
            'revisi' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
