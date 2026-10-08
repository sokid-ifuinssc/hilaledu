<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiMataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'nilai_mata_pelajarans';

    protected $fillable = [
        'mata_pelajaran_id',
        'kelas_id',
        'siswa_id',
        'guru_user_id',
        'tahun_ajaran',
        'semester',
        'nilai_tugas',
        'nilai_uts',
        'nilai_uas',
        'nilai_akhir',
        'predikat',
        'catatan',
        'is_sync_eskul',
    ];

    protected function casts(): array
    {
        return [
            'nilai_tugas'   => 'float',
            'nilai_uts'     => 'float',
            'nilai_uas'     => 'float',
            'nilai_akhir'   => 'float',
            'is_sync_eskul' => 'boolean',
        ];
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    /**
     * Hitung nilai akhir otomatis jika belum diisi manual
     */
    public function hitungNilaiAkhir(): float
    {
        $tugas = $this->nilai_tugas ?? 0;
        $uts   = $this->nilai_uts ?? 0;
        $uas   = $this->nilai_uas ?? 0;

        // Bobot standar Kurikulum Merdeka / SMK: Tugas/Formatif 40%, UTS/STS 30%, UAS/SAS 30%
        $count = 0;
        $sum = 0;
        if ($this->nilai_tugas !== null) { $sum += $tugas * 0.4; $count += 0.4; }
        if ($this->nilai_uts !== null)   { $sum += $uts * 0.3;   $count += 0.3; }
        if ($this->nilai_uas !== null)   { $sum += $uas * 0.3;   $count += 0.3; }

        if ($count > 0) {
            return round($sum / $count, 1);
        }

        return 0;
    }

    public static function tentukanPredikat(?float $nilai): string
    {
        if ($nilai === null || $nilai <= 0) return '-';
        if ($nilai >= 88) return 'A';
        if ($nilai >= 78) return 'B';
        if ($nilai >= 65) return 'C';
        return 'D';
    }
}
