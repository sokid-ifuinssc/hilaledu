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
