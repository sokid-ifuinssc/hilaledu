<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiHarianGuru extends Model
{
    protected $table = 'presensi_harian_gurus';

    protected $fillable = [
        'guru_user_id',
        'tanggal',
        'jam_masuk',
        'status_masuk',
        'terlambat_masuk_menit',
        'jam_pulang',
        'status_pulang',
        'pulang_cepat_menit',
        'status_pelaksanaan_jeda',
        'keterangan_pelaksanaan_jeda',
        'izin_keluar_alasan',
        'izin_keluar_jam_mulai',
        'izin_keluar_jam_kembali',
        'izin_keluar_status',
        'catatan_piket_izin_keluar',
        'catatan',
        'lampiran_bukti',
        'diabsenkan_oleh_user_id',
        'metode_masuk',
        'metode_pulang',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'               => 'date',
            'terlambat_masuk_menit' => 'integer',
            'pulang_cepat_menit'    => 'integer',
        ];
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    public function diabsenkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diabsenkan_oleh_user_id');
    }

    /**
     * Label Status Masuk
     */
    public function getStatusMasukLabelAttribute(): string
    {
        return match ($this->status_masuk) {
            'hadir'            => 'Hadir Tepat Waktu',
            'terlambat'        => "Hadir Terlambat ({$this->terlambat_masuk_menit} mnt)",
            'hadir_sesuai_jam' => 'Hadir Sesuai Jam Mapel',
            'izin'             => 'Izin',
            'sakit'            => 'Sakit',
            'tugas_luar'       => 'Tugas Luar Dinas',
            'alpa'             => 'Tanpa Keterangan',
            default            => ucfirst($this->status_masuk),
        };
    }

    /**
     * Badge CSS Status Masuk
     */
    public function getBadgeMasukClassAttribute(): string
    {
        return match ($this->status_masuk) {
            'hadir'            => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'terlambat'        => 'bg-amber-100 text-amber-900 border-amber-300',
            'hadir_sesuai_jam' => 'bg-teal-100 text-teal-800 border-teal-300',
            'izin'             => 'bg-blue-100 text-blue-800 border-blue-300',
            'sakit'            => 'bg-purple-100 text-purple-800 border-purple-300',
            'tugas_luar'       => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            'alpa'             => 'bg-rose-100 text-rose-800 border-rose-300',
            default            => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    /**
     * Label Status Pulang
     */
    public function getStatusPulangLabelAttribute(): string
    {
        return match ($this->status_pulang) {
            'tepat_waktu'  => 'Pulang Tepat Waktu',
            'pulang_cepat' => "Pulang Cepat ({$this->pulang_cepat_menit} mnt)",
            'belum_pulang' => 'Belum Pulang',
            default        => ucfirst($this->status_pulang),
        };
    }

    /**
     * Badge CSS Status Pulang
     */
    public function getBadgePulangClassAttribute(): string
    {
        return match ($this->status_pulang) {
            'tepat_waktu'  => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'pulang_cepat' => 'bg-rose-100 text-rose-800 border-rose-300',
            'belum_pulang' => 'bg-slate-100 text-slate-600 border-slate-200',
            default        => 'bg-slate-100 text-slate-700 border-slate-300',
        };
    }

    /**
     * Label Status Pelaksanaan Jeda (Sebelum KBM Jam 3+)
     */
    public function getStatusJedaLabelAttribute(): string
    {
        return match ($this->status_pelaksanaan_jeda) {
            'tugas_mandiri' => 'Tugas Mandiri di Sekolah',
            'standby'       => 'Standby di Sekolah',
            'izin_keluar'   => 'Izin Keluar Sementara',
            default         => 'Normal / Mengajar Langsung',
        };
    }

    /**
     * Badge CSS Status Jeda
     */
    public function getBadgeJedaClassAttribute(): string
    {
        return match ($this->status_pelaksanaan_jeda) {
            'tugas_mandiri', 'standby' => 'bg-blue-50 text-blue-700 border-blue-200',
            'izin_keluar'              => 'bg-amber-100 text-amber-900 border-amber-300 font-bold',
            default                    => 'bg-slate-50 text-slate-600 border-slate-200',
        };
    }
}
