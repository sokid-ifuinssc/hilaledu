<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnggotaEkstrakurikuler extends Model
{
    use HasFactory;

    protected $table = 'anggota_ekstrakurikulers';

    protected $fillable = [
        'ekstrakurikuler_id',
        'siswa_id',
        'jabatan',
        'tahun_ajaran',
        'semester',
        'status',
        'nilai_angka',
        'nilai_huruf',
        'catatan_nilai',
    ];

    protected function casts(): array
    {
        return [
            'nilai_angka' => 'float',
        ];
    }

    public function ekstrakurikuler(): BelongsTo
    {
        return $this->belongsTo(Ekstrakurikuler::class, 'ekstrakurikuler_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    /**
     * Hitung total sesi pertemuan presensi eskul yang ada
     */
    public function getTotalPertemuanAttribute(): int
    {
        return PresensiEskul::where('ekstrakurikuler_id', $this->ekstrakurikuler_id)
            ->distinct('tanggal')
            ->count('tanggal');
    }

    /**
     * Hitung jumlah hadir siswa ini pada eskul
     */
    public function getJumlahHadirAttribute(): int
    {
        return PresensiEskul::where('ekstrakurikuler_id', $this->ekstrakurikuler_id)
            ->where('siswa_id', $this->siswa_id)
            ->where('status', 'Hadir')
            ->count();
    }

    /**
     * Hitung jumlah izin/sakit/alpa siswa ini
     */
    public function getStatistikKehadiranAttribute(): array
    {
        $presensi = PresensiEskul::where('ekstrakurikuler_id', $this->ekstrakurikuler_id)
            ->where('siswa_id', $this->siswa_id)
            ->get();

        $totalSesi = $this->total_pertemuan;
        $hadir = $presensi->where('status', 'Hadir')->count();
        $izin  = $presensi->where('status', 'Izin')->count();
        $sakit = $presensi->where('status', 'Sakit')->count();
        $alpa  = $presensi->where('status', 'Alpa')->count();

        $persen = $totalSesi > 0 ? round(($hadir / $totalSesi) * 100, 1) : 0;

        return [
            'total'      => $totalSesi,
            'total_sesi' => $totalSesi,
            'hadir'      => $hadir,
            'izin'       => $izin,
            'sakit'      => $sakit,
            'alpa'       => $alpa,
            'persentase' => $persen,
        ];
    }

    public function getPersentaseKehadiranAttribute(): float
    {
        return $this->statistik_kehadiran['persentase'] ?? 0;
    }

    /**
     * Predikat nilai eskul otomatis jika belum diisi manual
     */
    public function getPredikatNilaiAttribute(): string
    {
        if (!empty($this->nilai_huruf)) {
            return $this->nilai_huruf;
        }

        if ($this->nilai_angka !== null) {
            if ($this->nilai_angka >= 86) return 'A';
            if ($this->nilai_angka >= 76) return 'B';
            if ($this->nilai_angka >= 65) return 'C';
            return 'D';
        }

        // Jika belum ada nilai angka, estimasi dari kehadiran
        $persen = $this->persentase_kehadiran;
        if ($this->total_pertemuan > 0) {
            if ($persen >= 85) return 'A';
            if ($persen >= 70) return 'B';
            if ($persen >= 50) return 'C';
            return 'D';
        }

        return '-';
    }
}
