<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggaran extends Model
{
    use HasFactory;


    protected $fillable = [
        'siswa_id',
        'jenis_pelanggaran_id',
        'tanggal_pelanggaran',
        'waktu_pelanggaran',
        'deskripsi',
        'bukti',
        'poin',
        'dicatat_oleh',
        'tahun_ajaran_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pelanggaran' => 'date',
            'poin' => 'integer',
        ];
    }

    // Relationships
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function jenisPelanggaran()
    {
        return $this->belongsTo(JenisPelanggaran::class);
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function progresPelanggaran()
    {
        return $this->hasOne(ProgresPelanggaran::class);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable')->orderBy('created_at', 'desc');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByTahunAjaran($query, $tahunAjaranId)
    {
        return $query->where('tahun_ajaran_id', $tahunAjaranId);
    }

    public function scopeByPeriode($query, $tanggalMulai, $tanggalSelesai)
    {
        return $query->whereBetween('tanggal_pelanggaran', [$tanggalMulai, $tanggalSelesai]);
    }

    // Helpers
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'dicatat' => 'bg-yellow-100 text-yellow-800',
            'proses' => 'bg-blue-100 text-blue-800',
            'dikembalikan' => 'bg-red-100 text-red-800',
            'selesai' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'dicatat' => 'Dicatat',
            'proses' => 'Dalam Proses',
            'dikembalikan' => 'Dikembalikan',
            'selesai' => 'Selesai',
            default => $this->status,
        };
    }
}
