<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jurusan extends Model
{
    protected $table = 'jurusans';

    protected $fillable = ['kode', 'nama', 'singkatan', 'kaprog_id', 'ketua_jurusan', 'is_aktif'];

    protected function casts(): array
    {
        return ['is_aktif' => 'boolean'];
    }

    protected static function booted()
    {
        static::saving(function ($jurusan) {
            if (!empty($jurusan->kaprog_id)) {
                $guru = User::find($jurusan->kaprog_id);
                if ($guru) {
                    $jurusan->ketua_jurusan = $guru->name;
                }
            }
        });

        static::saved(function ($jurusan) {
            $oldKaprogId = $jurusan->getOriginal('kaprog_id');
            $newKaprogId = $jurusan->kaprog_id;
            $singkatan   = $jurusan->singkatan;

            if ($oldKaprogId && ($oldKaprogId != $newKaprogId)) {
                $oldGuru = User::find($oldKaprogId);
                if ($oldGuru) {
                    $tugas = is_array($oldGuru->tugas_tambahan) ? $oldGuru->tugas_tambahan : [];
                    $tugas = array_filter($tugas, fn($t) => $t !== "Kaprog {$singkatan}" && $t !== "Ketua Program Keahlian (Kaprog) {$singkatan}");
                    $hasOther = self::where('kaprog_id', $oldGuru->id)->where('id', '!=', $jurusan->id)->exists();
                    if (!$hasOther) {
                        $tugas = array_filter($tugas, fn($t) => $t !== 'Ketua Jurusan / Kaprog');
                    }
                    $oldGuru->tugas_tambahan = array_values(array_unique($tugas));
                    $oldGuru->save();
                }
            }

            if ($newKaprogId) {
                $newGuru = User::find($newKaprogId);
                if ($newGuru) {
                    $tugas = is_array($newGuru->tugas_tambahan) ? $newGuru->tugas_tambahan : [];
                    // Hapus Kaprog generik jika ada spesifik
                    $tugas = array_filter($tugas, fn($t) => $t !== 'Ketua Jurusan / Kaprog' && $t !== 'Ketua Program Keahlian (Kaprog)');
                    $tugasSpesifik = "Kaprog {$singkatan}";
                    if (!in_array($tugasSpesifik, $tugas)) {
                        $tugas[] = $tugasSpesifik;
                    }
                    $newGuru->tugas_tambahan = array_values(array_unique(array_filter($tugas)));
                    if (empty($newGuru->jabatan_utama) || $newGuru->jabatan_utama === 'Guru Pengajar') {
                        $newGuru->jabatan_utama = $tugasSpesifik;
                    }
                    $newGuru->save();
                }
            }
        });

        static::deleted(function ($jurusan) {
            if ($jurusan->kaprog_id) {
                $guru = User::find($jurusan->kaprog_id);
                if ($guru) {
                    $singkatan = $jurusan->singkatan;
                    $tugas = is_array($guru->tugas_tambahan) ? $guru->tugas_tambahan : [];
                    $tugas = array_filter($tugas, fn($t) => $t !== "Kaprog {$singkatan}" && $t !== "Ketua Program Keahlian (Kaprog) {$singkatan}");
                    $hasOther = self::where('kaprog_id', $guru->id)->where('id', '!=', $jurusan->id)->exists();
                    if (!$hasOther) {
                        $tugas = array_filter($tugas, fn($t) => $t !== 'Ketua Jurusan / Kaprog');
                    }
                    $guru->tugas_tambahan = array_values(array_unique($tugas));
                    $guru->save();
                }
            }
        });
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    public function siswas(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(User::class, Kelas::class, 'jurusan_id', 'kelas_id')
            ->where('users.role', 'siswa');
    }

    public function kaprogGuru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kaprog_id');
    }
}
