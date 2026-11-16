<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Siswa extends Model
{
    protected $table = 'siswas';

    protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_hp',
        'nama_wali',
        'no_hp_wali',
        'kelas_id',
        'foto',
        'poin',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function pelanggarans()
    {
        return $this->hasMany(Pelanggaran::class);
    }

    public function getNamaAttribute(): string
    {
        return $this->nama_lengkap ?: ($this->user->name ?? 'Siswa');
    }

    public function getNamaLengkapAttribute($value): string
    {
        return $value ?: ($this->user?->nama_lengkap ?: ($this->user?->name ?? 'Siswa'));
    }

    public function kurangiPoin(int $poin): void
    {
        $current = (int) ($this->poin ?? 100);
        $this->poin = max(0, $current - max(0, $poin));
        $this->save();
    }

    public function tambahPoin(int $poin): void
    {
        $current = (int) ($this->poin ?? 100);
        $this->poin = min(100, $current + max(0, $poin));
        $this->save();
    }

    public function getStatusPoinAttribute(): string
    {
        $poin = (int) ($this->poin ?? 100);
        if ($poin >= 80) {
            return 'Sangat Baik';
        }
        if ($poin >= 70) {
            return 'Aman';
        }
        if ($poin >= 40) {
            return 'Perlu Pembinaan';
        }
        return 'Kritis';
    }

    public function getNamaOrtuDisplayAttribute(): string
    {
        if (!empty($this->nama_ortu)) {
            return $this->nama_ortu;
        }
        $ayah = $this->user?->nama_ayah;
        $ibu = $this->user?->nama_ibu;
        $parts = array_filter([$ayah, $ibu]);
        return !empty($parts) ? implode(' / ', $parts) : '-';
    }

    public function getAlamatDisplayAttribute(): string
    {
        if (!empty($this->alamat)) {
            return $this->alamat;
        }
        $u = $this->user;
        if (!$u) {
            return '-';
        }
        $parts = array_filter([
            $u->alamat,
            $u->desa ? 'Desa ' . $u->desa : null,
            $u->kecamatan ? 'Kec. ' . $u->kecamatan : null,
            $u->kabupaten,
        ]);
        return !empty($parts) ? implode(', ', $parts) : '-';
    }

    public function getNoHpOrtuDisplayAttribute(): string
    {
        if (!empty($this->no_hp_ortu)) {
            return $this->no_hp_ortu;
        }
        return $this->user?->no_hp_ortu ?: '-';
    }

    public function anggotaEkstrakurikulers()
    {
        return $this->hasMany(AnggotaEkstrakurikuler::class, 'siswa_id');
    }

    public function ekstrakurikulers()
    {
        return $this->belongsToMany(Ekstrakurikuler::class, 'anggota_ekstrakurikulers', 'siswa_id', 'ekstrakurikuler_id');
    }

    public function nilaiMataPelajarans()
    {
        return $this->hasMany(NilaiMataPelajaran::class, 'siswa_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
