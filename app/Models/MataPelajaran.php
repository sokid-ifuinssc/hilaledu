<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajarans';

    protected $fillable = [
        'kode',
        'nama',
        'kategori',     // A. KELOMPOK MATA PELAJARAN UMUM, B. KELOMPOK MATA PELAJARAN KEJURUAN
        'sub_kategori', // Dasar-dasar Program Keahlian, Mata Pelajaran [Konsentrasi Keahlian]***, Mata Pelajaran Pilihan****
        'urutan',
        'kelompok', // umum, kejuruan, muatan_lokal
        'tingkat',  // X, XI, XII, semua
        'jam_per_minggu',
        'guru_user_id',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
            'jam_per_minggu' => 'integer',
        ];
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalPelajaran::class, 'mata_pelajaran_id');
    }

    public function capaianPembelajaran(): HasMany
    {
        return $this->hasMany(CapaianPembelajaran::class, 'mata_pelajaran_id');
    }
}
