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

        static::saved(function ($kelas) {
            $originalWaliKelasId = $kelas->getOriginal('wali_kelas_id');
            $newWaliKelasId      = $kelas->wali_kelas_id;
            $originalNamaKelas   = $kelas->getOriginal('nama_kelas') ?? $kelas->getOriginal('nama');
            $newNamaKelas        = $kelas->nama_kelas ?? $kelas->nama;

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

        $tugas = is_array($guru->tugas_tambahan) ? $guru->tugas_tambahan : [];
        $tugasBaru = "Wali Kelas {$namaKelas}";

        // Bersihkan "Wali Kelas" umum untuk menghindari duplikasi badge jika ada kelas spesifik
        $tugas = array_filter($tugas, fn($item) => $item !== 'Wali Kelas');

        // Tambahkan "Wali Kelas [Nama Kelas]" spesifik jika belum ada
        if (!in_array($tugasBaru, $tugas)) {
            $tugas[] = $tugasBaru;
        }

        $guru->tugas_tambahan = array_values(array_unique(array_filter($tugas)));

        // Jika jabatan utama kosong atau masih default Guru Pengajar, jadikan Wali Kelas
        if (empty($guru->jabatan_utama) || $guru->jabatan_utama === 'Guru Pengajar') {
            $guru->jabatan_utama = $tugasBaru;
        }

        $guru->save();
    }

    /**
     * Helper: Hapus tugas tambahan Wali Kelas spesifik dari profil Guru
     */
    public static function removeWaliKelasFromGuru(int $guruId, ?string $namaKelas, ?int $ignoreKelasId = null): void
    {
        $guru = User::find($guruId);
        if (!$guru) return;

        $tugas = is_array($guru->tugas_tambahan) ? $guru->tugas_tambahan : [];
        $target1 = $namaKelas ? "Wali Kelas {$namaKelas}" : null;

        $tugas = array_filter($tugas, function ($item) use ($target1) {
            return $item !== $target1;
        });

        // Periksa apakah guru ini masih memegang kelas lain sebagai wali kelas
        $queryOther = self::where('wali_kelas_id', $guruId);
        if ($ignoreKelasId) {
            $queryOther->where('id', '!=', $ignoreKelasId);
        }
        $masihAdaKelasLain = $queryOther->exists();

        if (!$masihAdaKelasLain) {
            // Hapus juga "Wali Kelas" umum jika tidak memegang kelas manapun
            $tugas = array_filter($tugas, function ($item) {
                return $item !== 'Wali Kelas';
            });

            if ($guru->jabatan_utama === $target1) {
                $guru->jabatan_utama = 'Guru Pengajar';
            }
        }

        $guru->tugas_tambahan = array_values(array_unique($tugas));
        $guru->save();
    }

    /**
     * Helper: Rename tugas tambahan ketika nama kelas berubah
     */
    public static function renameWaliKelasInGuru(int $guruId, string $oldNama, string $newNama): void
    {
        $guru = User::find($guruId);
        if (!$guru) return;

        $tugas = is_array($guru->tugas_tambahan) ? $guru->tugas_tambahan : [];
        $oldTarget = "Wali Kelas {$oldNama}";
        $newTarget = "Wali Kelas {$newNama}";

        $tugas = array_map(function ($item) use ($oldTarget, $newTarget) {
            return $item === $oldTarget ? $newTarget : $item;
        }, $tugas);

        if (!in_array('Wali Kelas', $tugas)) {
            $tugas[] = 'Wali Kelas';
        }
        if (!in_array($newTarget, $tugas)) {
            $tugas[] = $newTarget;
        }

        $guru->tugas_tambahan = array_values(array_unique(array_filter($tugas)));

        if ($guru->jabatan_utama === $oldTarget) {
            $guru->jabatan_utama = $newTarget;
        }

        $guru->save();
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
