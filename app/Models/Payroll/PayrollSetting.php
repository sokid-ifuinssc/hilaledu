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
     * Sinkronkan tunjangan jabatan dan seluruh komponen gaji seorang pegawai dari Master Komponen
     * @param bool $forceUpdateMaster Jika true, nilai master komponen akan memperbarui setting pegawai
     */
    public static function syncTunjanganForUser(User $user, bool $forceUpdateMaster = false): ?self
    {
        if (!in_array($user->role, ['guru', 'tendik'])) {
            return null;
        }

        $masterKomponen = \App\Models\Payroll\PayrollKomponen::where('is_aktif', true)->get();
        $masterMap = [];
        foreach ($masterKomponen as $mk) {
            $masterMap[trim(mb_strtolower($mk->nama))] = (float) $mk->nominal_default;
        }

        $masterHonorJam = (float) ($masterKomponen->first(fn($k) => $k->tipe === 'per_jam' || $k->kode === 'HJM01' || str_contains(strtolower($k->nama), 'jam mengajar') || str_contains(strtolower($k->nama), 'honor jam'))?->nominal_default ?? 35000);
        $masterTransport = (float) ($masterKomponen->first(fn($k) => $k->tipe === 'per_kehadiran' || $k->kode === 'TK01' || str_contains(strtolower($k->nama), 'transport'))?->nominal_default ?? 20000);
        $masterGajiPokok = (float) ($masterKomponen->first(fn($k) => $k->kode === 'GP01' || str_contains(strtolower($k->nama), 'gaji pokok') || str_contains(strtolower($k->nama), 'pokok'))?->nominal_default ?? 1800000);
        $masterKehadiran = (float) ($masterKomponen->first(fn($k) => $k->kode === 'TK01' || str_contains(strtolower($k->nama), 'kehadiran'))?->nominal_default ?? 250000);
        $masterBpjs      = (float) ($masterKomponen->first(fn($k) => $k->kode === 'PBP01' || str_contains(strtolower($k->nama), 'bpjs'))?->nominal_default ?? 45000);
        $masterKoperasi  = (float) ($masterKomponen->first(fn($k) => $k->kode === 'PKOP01' || str_contains(strtolower($k->nama), 'koperasi'))?->nominal_default ?? 50000);
        $masterInfaq     = (float) ($masterKomponen->first(fn($k) => $k->kode === 'PINF01' || str_contains(strtolower($k->nama), 'infaq') || str_contains(strtolower($k->nama), 'kas'))?->nominal_default ?? 25000);

        $tugas = $user->daftar_jabatan;
        $isGuru = $user->role === 'guru';

        $setting = self::firstOrNew(['user_id' => $user->id]);
        if (!$setting->exists) {
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
        } else {
            // Jika force update aktif atau nilai masih 0 / default lama, sinkronkan nilai dari master
            if ($forceUpdateMaster) {
                if ($isGuru) {
                    $setting->transport_per_hari = $masterTransport;
                    $setting->gaji_pokok         = 0;
                    $setting->tunjangan_kehadiran= 0;
                    if ($masterHonorJam > 0) {
                        $setting->honor_per_jam  = $masterHonorJam;
                    }
                } else {
                    $setting->gaji_pokok         = $masterGajiPokok;
                    $setting->tunjangan_kehadiran= $masterKehadiran;
                }
                if ($masterBpjs > 0) $setting->potongan_bpjs = $masterBpjs;
                if ($masterKoperasi > 0) $setting->potongan_koperasi = $masterKoperasi;
                if ($masterInfaq > 0) $setting->potongan_lain = $masterInfaq;
            } else {
                // Auto-fill jika belum diatur (>0)
                if ($isGuru && ((float)$setting->transport_per_hari <= 0)) {
                    $setting->transport_per_hari = $masterTransport;
                }
                if ($isGuru && ((float)$setting->honor_per_jam <= 0)) {
                    $setting->honor_per_jam = $masterHonorJam;
                }
                if (!$isGuru && ((float)$setting->gaji_pokok <= 0)) {
                    $setting->gaji_pokok = $masterGajiPokok;
                }
                if (!$isGuru && ((float)$setting->tunjangan_kehadiran <= 0)) {
                    $setting->tunjangan_kehadiran = $masterKehadiran;
                }
            }
        }

        // Isi dan sinkronkan detail_tunjangan_tugas berdasarkan master komponen
        $detail = is_array($setting->detail_tunjangan_tugas) ? $setting->detail_tunjangan_tugas : [];
        if (!empty($tugas)) {
            foreach ($tugas as $t) {
                if (in_array($t, ['Guru', 'Tendik', 'Guru Pengajar', 'Siswa'])) continue;
                $key = trim(mb_strtolower($t));
                $nominal = $masterMap[$key] ?? (float)(\App\Models\TugasTambahan::where('nama', $t)->where('is_aktif', true)->value('nominal_gaji') ?? 0);
                if ($forceUpdateMaster && $nominal > 0) {
                    $detail[$t] = $nominal;
                } elseif (!isset($detail[$t]) || (float)$detail[$t] <= 0) {
                    $detail[$t] = $nominal;
                }
            }
            $setting->detail_tunjangan_tugas = $detail;
            $setting->tunjangan_jabatan = array_sum($detail);
        }
        $setting->save();

        // Sinkronkan juga ke payroll yang masih berstatus draft agar langsung terlihat oleh guru
        $draftPayrolls = Payroll::where('user_id', $user->id)->where('status', 'draft')->get();
        foreach ($draftPayrolls as $dp) {
            $periode = $dp->periode;
            if (!$periode) continue;

            $jamMengajar = $user->total_jam_mengajar ?: ($setting->jam_mengajar_default ?: 24);
            $kehadiran = $user->getHariHadirBulan((int)$periode->bulan, (int)$periode->tahun);

            $tarifHonor = (float) $setting->honor_per_jam;
            $totalHonor = $isGuru ? ($jamMengajar * $tarifHonor) : 0;
            $tarifTransport = (float) ($isGuru ? $setting->transport_per_hari : $setting->tunjangan_kehadiran);
            $totalTransport = $isGuru ? ($kehadiran * $tarifTransport) : $tarifTransport;

            $dp->jumlah_jam_mengajar = $jamMengajar;
            $dp->jumlah_kehadiran = $kehadiran;
            $dp->total_honor_jam = $totalHonor;
            $dp->save();

            // Perbarui item honor jam
            if ($isGuru && $totalHonor > 0) {
                $itemHonor = $dp->items()->where('nama_komponen', 'Honor Jam Mengajar')->first();
                if ($itemHonor) {
                    $itemHonor->update([
                        'nominal' => $totalHonor,
                        'keterangan' => "{$jamMengajar} Jam x Rp " . number_format($tarifHonor, 0, ',', '.'),
                    ]);
                } else {
                    PayrollItem::create([
                        'payroll_id'    => $dp->id,
                        'nama_komponen' => 'Honor Jam Mengajar',
                        'jenis'         => 'penerimaan',
                        'nominal'       => $totalHonor,
                        'keterangan'    => "{$jamMengajar} Jam x Rp " . number_format($tarifHonor, 0, ',', '.'),
                    ]);
                }
            }

            // Perbarui item transport & tunjangan kehadiran
            $itemTransport = $dp->items()->where(function($q) {
                $q->where('nama_komponen', 'like', '%transport%')
                  ->orWhere('nama_komponen', 'like', '%kehadiran%');
            })->first();

            $namaKomp = $isGuru ? 'Uang Transport Kehadiran / KBM' : 'Tunjangan Kehadiran & Transport';
            $ketKomp  = $isGuru ? "{$kehadiran} Hari Hadir Mengajar x Rp " . number_format($tarifTransport, 0, ',', '.') : 'Uang transport dan kehadiran';

            if ($itemTransport) {
                $itemTransport->update([
                    'nominal'    => $totalTransport,
                    'keterangan' => $ketKomp,
                ]);
            } elseif ($totalTransport > 0) {
                PayrollItem::create([
                    'payroll_id'    => $dp->id,
                    'nama_komponen' => $namaKomp,
                    'jenis'         => 'penerimaan',
                    'nominal'       => $totalTransport,
                    'keterangan'    => $ketKomp,
                ]);
            }

            $dp->recalculateTotals();
        }

        return $setting;
    }

    /**
     * Memastikan kolom-kolom penting di tabel payroll_settings sudah ada (Self-Healing Schema).
     * Mencegah 1054 Unknown column jika migrasi di server hosting belum dijalankan.
     */
    public static function ensureColumnsExist(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('payroll_settings')) {
                return;
            }

            if (!\Illuminate\Support\Facades\Schema::hasColumn('payroll_settings', 'transport_per_hari')) {
                \Illuminate\Support\Facades\Schema::table('payroll_settings', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->decimal('transport_per_hari', 12, 2)->default(0)->after('tunjangan_kehadiran');
                });
            }

            if (!\Illuminate\Support\Facades\Schema::hasColumn('payroll_settings', 'detail_tunjangan_tugas')) {
                \Illuminate\Support\Facades\Schema::table('payroll_settings', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->json('detail_tunjangan_tugas')->nullable()->after('tunjangan_jabatan');
                });
            }

            if (!\Illuminate\Support\Facades\Schema::hasColumn('payroll_settings', 'hari_transport_default')) {
                \Illuminate\Support\Facades\Schema::table('payroll_settings', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->integer('hari_transport_default')->nullable()->default(0)->after('transport_per_hari');
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('PayrollSetting schema check: ' . $e->getMessage());
        }
    }

    /**
     * Sinkronkan seluruh pegawai dari master komponen
     */
    public static function syncAllFromMasterKomponen(bool $forceUpdateMaster = true): int
    {
        self::ensureColumnsExist();
        $pegawais = User::whereIn('role', ['guru', 'tendik'])->get();
        $count = 0;
        foreach ($pegawais as $pegawai) {
            self::syncTunjanganForUser($pegawai, $forceUpdateMaster);
            $count++;
        }
        return $count;
    }
}
