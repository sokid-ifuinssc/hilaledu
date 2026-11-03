<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenSertifikatSiswa extends Model
{
    protected $table = 'dokumen_sertifikat_siswas';

    protected $fillable = [
        'siswa_id',
        'kategori',
        'jenis_sertifikat_id',
        'nama_dokumen',
        'nomor_dokumen',
        'file_path',
        'file_name',
        'file_size',
        'keterangan',
        'uploaded_by',
        'is_generated',
        'nama_kegiatan',
        'deskripsi_kegiatan',
        'predikat',
        'tempat_terbit',
        'tanggal_terbit',
        'pejabat_1_jabatan',
        'pejabat_1_nama',
        'pejabat_1_nip',
        'pejabat_2_jabatan',
        'pejabat_2_nama',
        'pejabat_2_nip',
    ];

    protected function casts(): array
    {
        return [
            'is_generated' => 'boolean',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function jenisSertifikat(): BelongsTo
    {
        return $this->belongsTo(JenisSertifikat::class, 'jenis_sertifikat_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes <= 0) {
            return 'PDF';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        return number_format($bytes / 1024, 1) . ' KB';
    }

    public static function getKategoriConfig(string $kategori): array
    {
        return match ($kategori) {
            'kepesantrenan' => [
                'key'         => 'kepesantrenan',
                'title'       => 'Sertifikat Kepesantrenan',
                'short'       => 'Kepesantrenan',
                'icon'        => 'bi-moon-stars-fill',
                'color'       => 'emerald',
                'is_multiple' => true,
                'placeholder' => 'Contoh: Sertifikat Khatam Juz / Sertifikat Keahlian Tahlil',
                'desc'        => 'Pengelolaan dan pengunduhan sertifikat kompetensi kepesantrenan siswa (dapat lebih dari satu sertifikat per siswa dengan nama berbeda).',
            ],
            'kewirausahaan' => [
                'key'         => 'kewirausahaan',
                'title'       => 'Sertifikat Kewirausahaan',
                'short'       => 'Kewirausahaan',
                'icon'        => 'bi-shop-window',
                'color'       => 'amber',
                'is_multiple' => true,
                'placeholder' => 'Contoh: Sertifikat Praktik Kewirausahaan / Produk Kreatif',
                'desc'        => 'Pengelolaan dan pengunduhan sertifikat kompetensi kewirausahaan siswa (dapat lebih dari satu sertifikat per siswa dengan nama berbeda).',
            ],
            'skl' => [
                'key'         => 'skl',
                'title'       => 'Surat Keterangan Lulus (SKL)',
                'short'       => 'SKL',
                'icon'        => 'bi-file-earmark-check-fill',
                'color'       => 'blue',
                'is_multiple' => false,
                'placeholder' => 'Surat Keterangan Lulus (SKL)',
                'desc'        => 'Dokumen resmi Surat Keterangan Lulus (SKL) siswa dalam format PDF (1 file per siswa).',
            ],
            'ijazah' => [
                'key'         => 'ijazah',
                'title'       => 'Dokumen Ijazah',
                'short'       => 'Ijazah',
                'icon'        => 'bi-mortarboard-fill',
                'color'       => 'indigo',
                'is_multiple' => false,
                'placeholder' => 'Ijazah SMK Plus Al-Hilal',
                'desc'        => 'Dokumen resmi Ijazah kelulusan siswa dalam format PDF (1 file per siswa).',
            ],
            default => [
                'key'         => 'kepesantrenan',
                'title'       => 'Sertifikat Kepesantrenan',
                'short'       => 'Kepesantrenan',
                'icon'        => 'bi-award-fill',
                'color'       => 'emerald',
                'is_multiple' => true,
                'placeholder' => 'Nama Sertifikat',
                'desc'        => 'Dokumen dan Sertifikat Siswa.',
            ],
        };
    }
}

