<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Model Kerjasama dengan Mitra DU/DI
 * Mencatat MoU / PKS antara sekolah dan dunia industri (DUDI).
 */
class Kerjasama extends Model
{
    use HasFactory;

    protected $table = 'kerjasamas';

    protected $fillable = [
        'dudi_id',
        'nama_mitra',
        'bidang_mitra',
        'alamat',
        'no_telp',
        'email',
        'nomor_mou',
        'bentuk_kerjasama',
        'tahun_mulai',
        'tahun_berakhir',
        'tanggal_mulai',
        'tanggal_berakhir',
        'file_kerjasama',
        'file_nama_asli',
        'link_drive',
        'pic_nama',
        'pic_jabatan',
        'pic_kontak',
        'status',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'bentuk_kerjasama' => 'array',
        'tanggal_mulai'    => 'date',
        'tanggal_berakhir' => 'date',
    ];

    /**
     * Relasi ke Master Mitra DU/DI Prakerin
     */
    public function dudi(): BelongsTo
    {
        return $this->belongsTo(Dudi::class, 'dudi_id');
    }

    /**
     * Relasi ke User yang mencatat
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Cek apakah kerjasama masih aktif berdasarkan status dan tahun
     */
    public function isAktif(): bool
    {
        if ($this->status === 'berakhir') {
            return false;
        }

        $currentYear = (int) date('Y');
        $endYear     = (int) $this->tahun_berakhir;

        if ($endYear > 0 && $endYear < $currentYear) {
            return false;
        }

        return true;
    }

    /**
     * Ambil URL publik file kerjasama
     */
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_kerjasama) {
            return null;
        }

        return Storage::disk('public')->url($this->file_kerjasama);
    }

    /**
     * Cek apakah file lokal ada
     */
    public function hasLocalFile(): bool
    {
        return !empty($this->file_kerjasama) && Storage::disk('public')->exists($this->file_kerjasama);
    }
}
