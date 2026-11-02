<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealisasiTugasTambahan extends Model
{
    protected $table = 'realisasi_tugas_tambahans';

    protected $fillable = [
        'guru_user_id',
        'program_kerja_id',
        'status_kesesuaian',
        'persentase_kesesuaian',
        'keterangan_kesesuaian',
        'tugas_tambahan',
        'tanggal_pelaksanaan',
        'judul_kegiatan',
        'uraian_kegiatan',
        'hasil_capaian',
        'realisasi_anggaran',
        'kendala_solusi',
        'foto_dokumentasi',
        'dokumen_pendukung',
        'status_validasi',
        'catatan_pimpinan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pelaksanaan'   => 'date:Y-m-d',
            'persentase_kesesuaian' => 'integer',
            'realisasi_anggaran'    => 'decimal:2',
        ];
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    public function programKerja(): BelongsTo
    {
        return $this->belongsTo(ProgramKerjaTugasTambahan::class, 'program_kerja_id');
    }

    public function getStatusKesesuaianBadgeAttribute(): string
    {
        return match ($this->status_kesesuaian) {
            'sesuai'          => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'sebagian_sesuai' => 'bg-amber-100 text-amber-800 border-amber-300',
            'tidak_sesuai'    => 'bg-rose-100 text-rose-800 border-rose-300',
            default           => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    public function getStatusKesesuaianLabelAttribute(): string
    {
        return match ($this->status_kesesuaian) {
            'sesuai'          => 'Sesuai Rencana (' . ($this->persentase_kesesuaian ?? 100) . '%)',
            'sebagian_sesuai' => 'Sebagian Sesuai (' . ($this->persentase_kesesuaian ?? 50) . '%)',
            'tidak_sesuai'    => 'Tidak Sesuai (' . ($this->persentase_kesesuaian ?? 0) . '%)',
            default           => 'Belum Ditentukan',
        };
    }

    public function getStatusValidasiBadgeAttribute(): string
    {
        return match ($this->status_validasi) {
            'disetujui_kepsek' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'diajukan'         => 'bg-blue-100 text-blue-800 border-blue-300',
            default            => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    public function getStatusValidasiLabelAttribute(): string
    {
        return match ($this->status_validasi) {
            'disetujui_kepsek' => 'Disetujui Kepala Sekolah',
            'diajukan'         => 'Menunggu Review',
            default            => 'Draft',
        };
    }
}
