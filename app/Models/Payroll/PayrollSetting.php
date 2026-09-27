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
        'detail_tunjangan_tugas',
        'tunjangan_kehadiran',
        'transport_per_hari',
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
            'gaji_pokok'              => 'decimal:2',
            'honor_per_jam'           => 'decimal:2',
            'jam_mengajar_default'    => 'integer',
            'tunjangan_jabatan'       => 'decimal:2',
            'detail_tunjangan_tugas'  => 'array',
            'tunjangan_kehadiran'     => 'decimal:2',
            'transport_per_hari'      => 'decimal:2',
            'tunjangan_lain'          => 'decimal:2',
            'potongan_bpjs'           => 'decimal:2',
            'potongan_koperasi'       => 'decimal:2',
            'potongan_lain'           => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Dapatkan nominal tunjangan tugas tambahan tertentu
     */
    public function getNominalTugas(string $namaTugas): float
    {
        $detail = $this->detail_tunjangan_tugas ?? [];
        if (is_array($detail) && array_key_exists($namaTugas, $detail)) {
            return (float) $detail[$namaTugas];
        }
        return 0;
    }

    /**
     * Hitung total penerimaan estimasi
     */
    public function totalEstimasiPenerimaanAttribute(): float
    {
        $isGuru = $this->user ? $this->user->role === 'guru' : true;

        if ($isGuru) {
            // Untuk guru, gaji pokok ditiadakan (0), diambil dari jam mengajar x honor per jam
            $honorJam = (float) $this->honor_per_jam * (int) $this->jam_mengajar_default;
            // Estimasi transport: hari kerja standar (asumsi 20 hari) x tarif transport per hari
            $transportPerHari = (float) ($this->transport_per_hari ?? 20000);
            $transport = $transportPerHari * 20;

            return $honorJam + (float) $this->tunjangan_jabatan + $transport + (float) $this->tunjangan_lain;
        }

        // Untuk tendik, tetap menggunakan gaji pokok
        return (float) $this->gaji_pokok + (float) $this->tunjangan_jabatan + (float) $this->tunjangan_kehadiran + (float) $this->tunjangan_lain;
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
            $setting->gaji_pokok           = $isGuru ? 0 : 1800000;
            $setting->honor_per_jam        = $isGuru ? 35000 : 0;
            $setting->jam_mengajar_default = $isGuru ? ($user->total_jam_mengajar ?: 24) : 0;
            $setting->tunjangan_kehadiran  = $isGuru ? 0 : 250000;
            $setting->transport_per_hari   = 20000;
            $setting->potongan_bpjs        = 45000;
            $setting->potongan_koperasi    = 50000;
            $setting->potongan_lain        = 25000;
            $setting->atas_nama_rekening   = $user->name;
        }

        // Jika belum ada detail_tunjangan_tugas, buat mapping awal
        if (empty($setting->detail_tunjangan_tugas) && !empty($tugas)) {
            $detail = [];
            foreach ($tugas as $t) {
                if ($t === 'Guru' || $t === 'Tendik') continue;
                $komp = \App\Models\TugasTambahan::where('nama', $t)->where('is_aktif', true)->first();
                $detail[$t] = $komp ? (float)$komp->nominal_gaji : 0;
            }
            $setting->detail_tunjangan_tugas = $detail;
            $setting->tunjangan_jabatan = array_sum($detail);
        } else {
            $setting->tunjangan_jabatan = is_array($setting->detail_tunjangan_tugas) 
                ? array_sum($setting->detail_tunjangan_tugas) 
                : $tunjanganJabatan;
        }
        $setting->save();

        return $setting;
    }
}
