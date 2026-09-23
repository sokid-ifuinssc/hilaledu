<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanKbm extends Model
{
    protected $table = 'laporan_kbms';

    protected $fillable = [
        'jadwal_pelajaran_id',
        'rencana_pembelajaran_id',
        'guru_user_id',
        'tanggal_realisasi',
        'kesesuaian_rencana', // sesuai, sebagian, tidak_sesuai, materi_pengganti
        'keterangan_kesesuaian',
        'status_pelaksanaan', // sesuai_jadwal, ganti_hari, jam_tambahan, lainnya
        'keterangan_pelaksanaan',
        'catatan_kegiatan',
        'foto_dokumentasi',
        'jumlah_siswa_hadir',
        'jumlah_siswa_tidak_hadir',
        'jumlah_siswa_total',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_siswa_hadir' => 'integer',
            'jumlah_siswa_tidak_hadir' => 'integer',
            'jumlah_siswa_total' => 'integer',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalPelajaran::class, 'jadwal_pelajaran_id');
    }

    public function rencana(): BelongsTo
    {
        return $this->belongsTo(RencanaPembelajaran::class, 'rencana_pembelajaran_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    public function presensiSiswa(): HasMany
    {
        return $this->hasMany(LaporanKbmPresensi::class, 'laporan_kbm_id');
    }

    /**
     * Hitung ulang ringkasan jumlah hadir/tidak hadir siswa
     */
    public function updateStudentCounts(): void
    {
        $hadir = $this->presensiSiswa()->whereIn('status', ['hadir', 'terlambat'])->count();
        $tidakHadir = $this->presensiSiswa()->whereIn('status', ['sakit', 'izin', 'alpa'])->count();
        $total = $this->presensiSiswa()->count();

        $this->update([
            'jumlah_siswa_hadir'       => $hadir,
            'jumlah_siswa_tidak_hadir' => $tidakHadir,
            'jumlah_siswa_total'       => $total,
        ]);
    }
}
