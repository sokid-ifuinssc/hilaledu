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
        'hari_transport_default',
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
            'hari_transport_default'  => 'integer',
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
            // Estimasi transport: hari hadir mengajar aktual dinamis x tarif transport per hari
            $transportPerHari = (float) ($this->transport_per_hari ?? 20000);
            $hariTransport = (int) ($this->hari_transport_default > 0 ? $this->hari_transport_default : ($this->user?->hari_hadir_bulan_ini ?? 0));
            $transport = $transportPerHari * $hariTransport;

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

        $masterKomponen = \App\Models\Payroll\PayrollKomponen::where('is_aktif', true)->get();
        $masterMap = [];
        foreach ($masterKomponen as $mk) {
            $masterMap[trim(mb_strtolower($mk->nama))] = (float) $mk->nominal_default;
        }

        $masterHonorJam = (float) ($masterKomponen->first(fn($k) => $k->tipe === 'per_jam' || $k->kode === 'HJM01' || str_contains(strtolower($k->nama), 'jam mengajar'))?->nominal_default ?? 35000);
        $masterTransport = (float) ($masterKomponen->first(fn($k) => $k->tipe === 'per_kehadiran' || $k->kode === 'TK01' || str_contains(strtolower($k->nama), 'transport'))?->nominal_default ?? 20000);
        $masterGajiPokok = (float) ($masterKomponen->first(fn($k) => $k->kode === 'GP01' || str_contains(strtolower($k->nama), 'gaji pokok'))?->nominal_default ?? 1800000);
        $masterKehadiran = (float) ($masterKomponen->first(fn($k) => $k->kode === 'TK01' || str_contains(strtolower($k->nama), 'kehadiran'))?->nominal_default ?? 250000);
        $masterBpjs      = (float) ($masterKomponen->first(fn($k) => $k->kode === 'PBP01' || str_contains(strtolower($k->nama), 'bpjs'))?->nominal_default ?? 45000);
        $masterKoperasi  = (float) ($masterKomponen->first(fn($k) => $k->kode === 'PKOP01' || str_contains(strtolower($k->nama), 'koperasi'))?->nominal_default ?? 50000);
        $masterInfaq     = (float) ($masterKomponen->first(fn($k) => $k->kode === 'PINF01' || str_contains(strtolower($k->nama), 'infaq') || str_contains(strtolower($k->nama), 'kas'))?->nominal_default ?? 25000);

        $tugas = $user->daftar_jabatan;

        $setting = self::firstOrNew(['user_id' => $user->id]);
        if (!$setting->exists) {
            $isGuru = $user->role === 'guru';
            $setting->gaji_pokok              = $isGuru ? 0 : $masterGajiPokok;
            $setting->honor_per_jam           = $isGuru ? $masterHonorJam : 0;
            $setting->jam_mengajar_default    = $isGuru ? ($user->total_jam_mengajar ?: 24) : 0;
            $setting->tunjangan_kehadiran     = $isGuru ? 0 : $masterKehadiran;
            $setting->transport_per_hari      = $masterTransport;
            $setting->hari_transport_default  = 0; // Terhitung dinamis dari presensi riil harian guru
            $setting->potongan_bpjs           = $masterBpjs;
            $setting->potongan_koperasi       = $masterKoperasi;
            $setting->potongan_lain           = $masterInfaq;
            $setting->atas_nama_rekening      = $user->name;
        }

        // Isi dan sinkronkan detail_tunjangan_tugas berdasarkan master komponen
        $detail = is_array($setting->detail_tunjangan_tugas) ? $setting->detail_tunjangan_tugas : [];
        if (!empty($tugas)) {
            foreach ($tugas as $t) {
                if (in_array($t, ['Guru', 'Tendik', 'Guru Pengajar', 'Siswa'])) continue;
                $key = trim(mb_strtolower($t));
                $nominal = $masterMap[$key] ?? (float)(\App\Models\TugasTambahan::where('nama', $t)->where('is_aktif', true)->value('nominal_gaji') ?? 0);
                if (!isset($detail[$t]) || (float)$detail[$t] <= 0) {
                    $detail[$t] = $nominal;
                }
            }
            $setting->detail_tunjangan_tugas = $detail;
            $setting->tunjangan_jabatan = array_sum($detail);
        }
        $setting->save();

        return $setting;
    }
}
