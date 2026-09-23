<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajarans';

    protected $fillable = [
        'nama', 'tahun_mulai', 'tahun_selesai', 'semester',
        'tanggal_mulai', 'tanggal_selesai', 'is_aktif', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_aktif'  => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    /**
     * Set tahun ajaran ini sebagai aktif dan nonaktifkan yang lain.
     */
    public function setAsAktif(): void
    {
        static::query()->update(['is_aktif' => false, 'is_active' => false]);
        $this->update(['is_aktif' => true, 'is_active' => true]);
    }

    public static function aktif(): ?self
    {
        return static::where('is_aktif', true)->orWhere('is_active', true)->first();
    }
}
