<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'jurusan_id', 'tahun_ajaran_id', 'tingkat',
        'nama', 'nama_kelas', 'wali_kelas', 'wali_kelas_id', 'is_aktif', 'ketua_kelas_id',
    ];

    protected function casts(): array
    {
        return ['is_aktif' => 'boolean'];
    }

    // Static storage to hold pre-save values during updating event
    protected static array $_pendingSyncData = [];

    protected static function booted()
    {
        static::saving(function ($kelas) {
            // Pastikan kolom nama dan nama_kelas selalu tersinkronisasi
            if (!empty($kelas->nama_kelas) && empty($kelas->nama)) {
                $kelas->nama = $kelas->nama_kelas;
            } elseif (!empty($kelas->nama) && empty($kelas->nama_kelas)) {
                $kelas->nama_kelas = $kelas->nama;
            } elseif (!empty($kelas->nama_kelas)) {
                $kelas->nama = $kelas->nama_kelas;
            }

            // Pastikan string nama wali_kelas selalu sinkron jika wali_kelas_id diisi
            if (!empty($kelas->wali_kelas_id)) {
                $guru = User::find($kelas->wali_kelas_id);
                if ($guru) {
                    $kelas->wali_kelas = $guru->name;
                }
            }
        });

        // Capture old values BEFORE update (getOriginal() masih valid di sini)
        static::updating(function ($kelas) {
            self::$_pendingSyncData[$kelas->id] = [
                'old_wali_kelas_id' => $kelas->getOriginal('wali_kelas_id'),
                'old_nama_kelas'    => $kelas->getOriginal('nama_kelas') ?? $kelas->getOriginal('nama'),
            ];
        });

        static::saved(function ($kelas) {
            $pending = self::$_pendingSyncData[$kelas->id] ?? null;
            $originalWaliKelasId = $pending ? $pending['old_wali_kelas_id'] : null;
            $newWaliKelasId      = $kelas->wali_kelas_id;
            $originalNamaKelas   = $pending ? $pending['old_nama_kelas'] : null;
            $newNamaKelas        = $kelas->nama_kelas ?? $kelas->nama;
            unset(self::$_pendingSyncData[$kelas->id]);

            // 1. Jika wali kelas lama berbeda dengan wali kelas baru, bersihkan penugasan dari guru lama
            if ($originalWaliKelasId && ($originalWaliKelasId != $newWaliKelasId)) {
                self::removeWaliKelasFromGuru((int) $originalWaliKelasId, (string) $originalNamaKelas, (int) $kelas->id);
            }

            // 2. Jika wali kelas sama tapi nama kelas berubah, update nama kelas di tugas tambahan guru
            if ($originalWaliKelasId && ($originalWaliKelasId == $newWaliKelasId) && $originalNamaKelas && ($originalNamaKelas !== $newNamaKelas)) {
                self::renameWaliKelasInGuru((int) $newWaliKelasId, (string) $originalNamaKelas, (string) $newNamaKelas);
            }

            // 3. Tambahkan tugas tambahan ke profil guru wali kelas baru
            if ($newWaliKelasId && !empty($newNamaKelas)) {
                self::addWaliKelasToGuru((int) $newWaliKelasId, (string) $newNamaKelas);
            }
        });

        static::deleted(function ($kelas) {
            if ($kelas->wali_kelas_id) {
                $nama = $kelas->nama_kelas ?? $kelas->nama;
                self::removeWaliKelasFromGuru((int) $kelas->wali_kelas_id, (string) $nama, (int) $kelas->id);
            }
        });
    }

    /**
     * Helper: Tambahkan tugas tambahan Wali Kelas ke profil Guru
     */
    public static function addWaliKelasToGuru(int $guruId, string $namaKelas): void
    {
        $guru = User::find($guruId);
        if (!$guru) return;

        // Ambil raw DB value langsung dari attributes agar tidak terkena live query accessor
        $rawTugas = json_decode($guru->attributes['tugas_tambahan'] ?? '[]', true) ?? [];
        $tugasBaru = "Wali Kelas {$namaKelas}";

        // Bersihkan "Wali Kelas" umum untuk menghindari duplikasi badge jika ada kelas spesifik
        $rawTugas = array_filter($rawTugas, fn($item) => $item !== 'Wali Kelas');

        // Tambahkan "Wali Kelas [Nama Kelas]" spesifik jika belum ada
        if (!in_array($tugasBaru, $rawTugas)) {
            $rawTugas[] = $tugasBaru;
        }
        $rawTugas = array_values(array_unique(array_filter($rawTugas)));

        // Jika jabatan utama kosong atau masih default Guru Pengajar, jadikan Wali Kelas
        $jabatan = (empty($guru->jabatan_utama) || $guru->jabatan_utama === 'Guru Pengajar')
            ? $tugasBaru : $guru->jabatan_utama;

        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $guruId)
            ->update(['tugas_tambahan' => json_encode($rawTugas), 'jabatan_utama' => $jabatan]);
    }

    /**
     * Helper: Hapus tugas tambahan Wali Kelas spesifik dari profil Guru
     */
    public static function removeWaliKelasFromGuru(int $guruId, ?string $namaKelas, ?int $ignoreKelasId = null): void
    {
        $guru = User::find($guruId);
        if (!$guru) return;

        // Ambil raw DB value langsung dari attributes agar tidak terkena live query accessor
        $rawTugas = json_decode($guru->attributes['tugas_tambahan'] ?? '[]', true) ?? [];
        $target1  = $namaKelas ? "Wali Kelas {$namaKelas}" : null;

        $rawTugas = array_filter($rawTugas, fn($item) => $item !== $target1);

        // Periksa apakah guru ini masih memegang kelas lain sebagai wali kelas
        $queryOther = self::where('wali_kelas_id', $guruId);
        if ($ignoreKelasId) {
            $queryOther->where('id', '!=', $ignoreKelasId);
        }
        $masihAdaKelasLain = $queryOther->exists();

        $jabatan = $guru->jabatan_utama;
        if (!$masihAdaKelasLain) {
            // Hapus juga "Wali Kelas" umum jika tidak memegang kelas manapun
            $rawTugas = array_filter($rawTugas, fn($item) => $item !== 'Wali Kelas');
            if ($jabatan === $target1) {
                $jabatan = 'Guru Pengajar';
            }
        }

        $rawTugas = array_values(array_unique($rawTugas));
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $guruId)
            ->update(['tugas_tambahan' => json_encode($rawTugas), 'jabatan_utama' => $jabatan]);
    }

    /**
     * Helper: Rename tugas tambahan ketika nama kelas berubah
     */
    public static function renameWaliKelasInGuru(int $guruId, string $oldNama, string $newNama): void
    {
        $guru = User::find($guruId);
        if (!$guru) return;

        // Ambil raw DB value langsung dari attributes agar tidak terkena live query accessor
        $rawTugas  = json_decode($guru->attributes['tugas_tambahan'] ?? '[]', true) ?? [];
        $oldTarget = "Wali Kelas {$oldNama}";
        $newTarget = "Wali Kelas {$newNama}";

        $rawTugas = array_map(fn($item) => $item === $oldTarget ? $newTarget : $item, $rawTugas);

        // Tidak perlu tambahkan "Wali Kelas" generik, cukup yang spesifik
        if (!in_array($newTarget, $rawTugas)) {
            $rawTugas[] = $newTarget;
        }
        $rawTugas = array_values(array_unique(array_filter($rawTugas)));

        $jabatan = $guru->jabatan_utama === $oldTarget ? $newTarget : $guru->jabatan_utama;

        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $guruId)
            ->update(['tugas_tambahan' => json_encode($rawTugas), 'jabatan_utama' => $jabatan]);
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function waliKelasGuru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    public function siswas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'kelas_id')->where('role', 'siswa');
    }

    public function ketuaKelas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ketua_kelas_id');
    }

    public function presensiHarians(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PresensiHarianSiswa::class, 'kelas_id');
    }

    /**
     * Nama tampilan kelas: "X TKJ 1"
     */
    public function getNamaLengkapAttribute(): string
    {
        $tingkat = trim($this->attributes['tingkat'] ?? '');
        $jurusan = trim($this->jurusan?->singkatan ?? '');
        $nama    = trim($this->attributes['nama_kelas'] ?? $this->attributes['nama'] ?? '');

        if (!empty($tingkat) && (str_starts_with($nama, $tingkat . ' ') || $nama === $tingkat)) {
            return $nama;
        }

        if (!empty($jurusan) && (str_starts_with($nama, $jurusan . ' ') || $nama === $jurusan)) {
            return trim("$tingkat $nama");
        }

        return trim(implode(' ', array_filter([$tingkat, $jurusan, $nama])));
    }
}
