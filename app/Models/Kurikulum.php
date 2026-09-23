<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Kurikulum extends Model
{
    protected $table = 'kurikulums';

    protected $fillable = [
        'tahun_ajaran',
        'semester',
        'jenjang',
        'jurusan',
        'jurusan_id',
        'kelas',
        'kelas_id',
        'mata_pelajaran_id',
        'kategori',     // A. KELOMPOK MATA PELAJARAN UMUM, B. KELOMPOK MATA PELAJARAN KEJURUAN
        'sub_kategori', // Dasar-dasar Program Keahlian, Mata Pelajaran [Konsentrasi Keahlian]***, Mata Pelajaran Pilihan****
        'urutan',
        'guru_user_id',
        'alokasi_jam',
        'keterangan',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'alokasi_jam' => 'integer',
            'urutan'      => 'integer',
            'is_aktif'    => 'boolean',
        ];
    }

    /**
     * Accessor Kategori dengan fallback ke data Master Mata Pelajaran
     */
    public function getKategoriAttribute($value): string
    {
        return !empty($value) ? $value : ($this->mataPelajaran?->kategori ?? 'A. KELOMPOK MATA PELAJARAN UMUM');
    }

    /**
     * Accessor Sub Kategori dengan fallback ke data Master Mata Pelajaran
     */
    public function getSubKategoriAttribute($value): ?string
    {
        return !empty($value) ? $value : ($this->mataPelajaran?->sub_kategori ?? null);
    }

    /**
     * Relasi ke Master Mata Pelajaran
     */
    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    /**
     * Relasi ke Guru Pengampu
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    /**
     * Relasi ke Model Kelas (jika terisi)
     */
    public function kelasModel(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Relasi ke Model Jurusan (jika terisi)
     */
    public function jurusanModel(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    /**
     * Scope Filter Jurusan (AKL, TKJT, TO)
     */
    public function scopeFilterJurusan($query, $jurusan)
    {
        if (!empty($jurusan) && $jurusan !== 'all') {
            return $query->where('jurusan', $jurusan);
        }
        return $query;
    }

    /**
     * Scope Filter Jenjang (X, XI, XII)
     */
    public function scopeFilterJenjang($query, $jenjang)
    {
        if (!empty($jenjang) && $jenjang !== 'all') {
            return $query->where('jenjang', $jenjang);
        }
        return $query;
    }

    /**
     * Scope Filter Kelas ('X TKJT', 'XI AKL', dll)
     */
    public function scopeFilterKelas($query, $kelas)
    {
        if (!empty($kelas) && $kelas !== 'all') {
            return $query->where('kelas', $kelas);
        }
        return $query;
    }

    /**
     * Scope Filter Tahun Ajaran & Semester
     */
    public function scopeTahunSemester($query, $tahun = null, $semester = null)
    {
        if ($tahun) {
            $query->where('tahun_ajaran', $tahun);
        }
        if ($semester) {
            $query->where('semester', $semester);
        }
        return $query;
    }

    /**
     * Hitung berapa Jam Pelajaran (JP) yang sudah dijadwalkan di jadwal_pelajarans
     */
    public function getTerjadwalJpAttribute(): int
    {
        $jadwals = JadwalPelajaran::where('kelas', $this->kelas)
            ->where('mata_pelajaran_id', $this->mata_pelajaran_id);

        if ($this->guru_user_id) {
            $jadwals->where('guru_user_id', $this->guru_user_id);
        }

        return (int) $jadwals->get()->sum(function ($j) {
            return max(1, ($j->jam_ke_selesai - $j->jam_ke_mulai + 1));
        });
    }

    /**
     * Hitung sisa jam pelajaran yang belum terjadwal
     */
    public function getSisaJpAttribute(): int
    {
        return max(0, $this->alokasi_jam - $this->terjadwal_jp);
    }

    /**
     * Status pemenuhan jadwal: 'lengkap', 'sebagian', 'belum'
     */
    public function getStatusJadwalAttribute(): string
    {
        $terjadwal = $this->terjadwal_jp;
        if ($terjadwal >= $this->alokasi_jam) {
            return 'lengkap';
        } elseif ($terjadwal > 0) {
            return 'sebagian';
        }
        return 'belum';
    }

    /**
     * Persentase keterisian jadwal (0 - 100%)
     */
    public function getPersentaseTerjadwalAttribute(): int
    {
        if ($this->alokasi_jam <= 0) return 100;
        return (int) min(100, round(($this->terjadwal_jp / $this->alokasi_jam) * 100));
    }
}
