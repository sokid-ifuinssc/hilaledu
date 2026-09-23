<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class PayrollSetting extends Model
{
    protected $table = 'payroll_settings';

    protected $fillable = [
        'user_id',
        'gaji_pokok',
        'honor_per_jam',
        'jam_mengajar_default',
        'tunjangan_jabatan',
        'tunjangan_kehadiran',
        'tunjangan_lain',
        'potongan_bpjs',
        'potongan_koperasi',
        'potongan_lain',
        'rekening_bank',
        'nomor_rekening',
        'atas_nama_rekening',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'gaji_pokok'           => 'decimal:2',
            'honor_per_jam'        => 'decimal:2',
            'jam_mengajar_default' => 'integer',
            'tunjangan_jabatan'    => 'decimal:2',
            'tunjangan_kehadiran'  => 'decimal:2',
            'tunjangan_lain'       => 'decimal:2',
            'potongan_bpjs'        => 'decimal:2',
            'potongan_koperasi'    => 'decimal:2',
            'potongan_lain'        => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Hitung total penerimaan estimasi
     */
    public function totalEstimasiPenerimaanAttribute(): float
    {
        $honorJam = (float) $this->honor_per_jam * (int) $this->jam_mengajar_default;
        return (float) $this->gaji_pokok + $honorJam + (float) $this->tunjangan_jabatan + (float) $this->tunjangan_kehadiran + (float) $this->tunjangan_lain;
    }

    /**
     * Hitung total potongan estimasi
     */
    public function totalEstimasiPotonganAttribute(): float
    {
        return (float) $this->potongan_bpjs + (float) $this->potongan_koperasi + (float) $this->potongan_lain;
    }

    /**
     * Estimasi Gaji Bersih (Take Home Pay)
     */
    public function estimasiGajiBersihAttribute(): float
    {
        return max(0, $this->totalEstimasiPenerimaanAttribute() - $this->totalEstimasiPotonganAttribute());
    }

    /**
     * Sinkronkan tunjangan jabatan seorang pegawai berdasarkan seluruh tugas tambahan dan penugasan admin unit
     */
    public static function syncTunjanganForUser(User $user): ?self
    {
        if (!in_array($user->role, ['guru', 'tendik'])) {
            return null;
        }

        $tunjanganJabatan = 0;
        $tugas = $user->daftar_jabatan;

        if (!empty($tugas)) {
            $tunjanganJabatan = \App\Models\TugasTambahan::whereIn('nama', $tugas)
                ->where('is_aktif', true)
                ->sum('nominal_gaji');
        }

        $setting = self::firstOrNew(['user_id' => $user->id]);
        if (!$setting->exists) {
            $isGuru = $user->role === 'guru';
            $setting->gaji_pokok           = $isGuru ? 1500000 : 1800000;
            $setting->honor_per_jam        = $isGuru ? 35000 : 0;
            $setting->jam_mengajar_default = $isGuru ? 24 : 0;
            $setting->tunjangan_kehadiran  = $isGuru ? 200000 : 250000;
            $setting->potongan_bpjs        = 45000;
            $setting->potongan_koperasi    = 50000;
            $setting->potongan_lain        = 25000;
            $setting->atas_nama_rekening   = $user->name;
        }

        $setting->tunjangan_jabatan = $tunjanganJabatan;
        $setting->save();

        return $setting;
    }
}
