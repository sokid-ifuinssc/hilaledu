<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Ekstrakurikuler extends Model
{
    use HasFactory;

    protected $table = 'ekstrakurikulers';

    protected $fillable = [
        'nama',
        'kode',
        'deskripsi',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'tempat',
        'pembina_guru_id',
        'ketua_siswa_id',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
        ];
    }

    protected static array $_pendingSyncData = [];

    protected static function booted()
    {
        static::updating(function ($eskul) {
            self::$_pendingSyncData[$eskul->id] = [
                'old_pembina_guru_id' => $eskul->getOriginal('pembina_guru_id'),
                'old_nama'            => $eskul->getOriginal('nama'),
            ];
        });

        static::saved(function ($eskul) {
            $pending = self::$_pendingSyncData[$eskul->id] ?? null;
            $originalPembinaId = $pending ? $pending['old_pembina_guru_id'] : null;
            $newPembinaId      = $eskul->pembina_guru_id;
            $originalNama      = $pending ? $pending['old_nama'] : null;
            $newNama           = $eskul->nama;
            unset(self::$_pendingSyncData[$eskul->id]);

            // Jika pembina lama diganti
            if ($originalPembinaId && ($originalPembinaId != $newPembinaId)) {
                self::removePembinaFromGuru((int) $originalPembinaId, (string) $originalNama, (int) $eskul->id);
            }

            // Jika pembina sama tapi nama eskul berubah
            if ($originalPembinaId && ($originalPembinaId == $newPembinaId) && $originalNama && ($originalNama !== $newNama)) {
                self::renamePembinaInGuru((int) $newPembinaId, (string) $originalNama, (string) $newNama);
            }

            // Tambahkan tugas tambahan ke guru pembina baru
            if ($newPembinaId && !empty($newNama)) {
                self::addPembinaToGuru((int) $newPembinaId, (string) $newNama);
            }
        });

        static::deleted(function ($eskul) {
            if ($eskul->pembina_guru_id) {
                self::removePembinaFromGuru((int) $eskul->pembina_guru_id, (string) $eskul->nama, (int) $eskul->id);
            }
        });
    }

    public static function addPembinaToGuru(int $guruId, string $namaEskul): void
    {
        $guru = User::find($guruId);
        if (!$guru) return;

        $rawTugas = json_decode($guru->attributes['tugas_tambahan'] ?? '[]', true) ?? [];
        $tugasBaru = "Pembina " . trim($namaEskul);

        if (!in_array($tugasBaru, $rawTugas)) {
            $rawTugas[] = $tugasBaru;
        }
        $rawTugas = array_values(array_unique(array_filter($rawTugas)));

        $jabatan = (empty($guru->jabatan_utama) || $guru->jabatan_utama === 'Guru Pengajar')
            ? $tugasBaru : $guru->jabatan_utama;

        DB::table('users')
            ->where('id', $guruId)
            ->update([
                'tugas_tambahan' => json_encode($rawTugas),
                'jabatan_utama'  => $jabatan,
            ]);
    }

    public static function removePembinaFromGuru(int $guruId, ?string $namaEskul, ?int $ignoreEskulId = null): void
    {
        $guru = User::find($guruId);
        if (!$guru) return;

        $rawTugas = json_decode($guru->attributes['tugas_tambahan'] ?? '[]', true) ?? [];
        $target1  = $namaEskul ? "Pembina " . trim($namaEskul) : null;

        $rawTugas = array_filter($rawTugas, fn($item) => $item !== $target1);

        $queryOther = self::where('pembina_guru_id', $guruId);
        if ($ignoreEskulId) {
            $queryOther->where('id', '!=', $ignoreEskulId);
        }
        $masihAdaEskulLain = $queryOther->exists();

        $jabatan = $guru->jabatan_utama;
        if (!$masihAdaEskulLain) {
            if ($jabatan === $target1) {
                $jabatan = 'Guru Pengajar';
            }
        }

        $rawTugas = array_values(array_unique($rawTugas));
        DB::table('users')
            ->where('id', $guruId)
            ->update([
                'tugas_tambahan' => json_encode($rawTugas),
                'jabatan_utama'  => $jabatan,
            ]);
    }

    public static function renamePembinaInGuru(int $guruId, string $oldNama, string $newNama): void
    {
        $guru = User::find($guruId);
        if (!$guru) return;

        $rawTugas = json_decode($guru->attributes['tugas_tambahan'] ?? '[]', true) ?? [];
        $oldTugas = "Pembina " . trim($oldNama);
        $newTugas = "Pembina " . trim($newNama);

        $rawTugas = array_map(fn($item) => $item === $oldTugas ? $newTugas : $item, $rawTugas);
        $rawTugas = array_values(array_unique(array_filter($rawTugas)));

        $jabatan = $guru->jabatan_utama === $oldTugas ? $newTugas : $guru->jabatan_utama;

        DB::table('users')
            ->where('id', $guruId)
            ->update([
                'tugas_tambahan' => json_encode($rawTugas),
                'jabatan_utama'  => $jabatan,
            ]);
    }

    public function pembina(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembina_guru_id');
    }

    public function pembinaGuru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembina_guru_id');
    }

    public function guruPembina(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembina_guru_id');
    }

    public function ketua(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ketua_siswa_id');
    }

    public function ketuaSiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ketua_siswa_id');
    }

    public function anggotas(): HasMany
    {
        return $this->hasMany(AnggotaEkstrakurikuler::class, 'ekstrakurikuler_id');
    }

    public function siswas(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'anggota_ekstrakurikulers', 'ekstrakurikuler_id', 'siswa_id')
            ->withPivot(['jabatan', 'tahun_ajaran', 'semester', 'status', 'nilai_angka', 'nilai_huruf', 'catatan_nilai'])
            ->withTimestamps();
    }

    public function rencanaKegiatans(): HasMany
    {
        return $this->hasMany(RencanaKegiatanEskul::class, 'ekstrakurikuler_id')->orderBy('pertemuan_ke');
    }

    public function rencanas(): HasMany
    {
        return $this->rencanaKegiatans();
    }

    public function laporanKegiatans(): HasMany
    {
        return $this->hasMany(LaporanKegiatanEskul::class, 'ekstrakurikuler_id')->orderByDesc('tanggal_kegiatan');
    }

    public function laporans(): HasMany
    {
        return $this->laporanKegiatans();
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(PresensiEskul::class, 'ekstrakurikuler_id');
    }

    public function getJadwalLengkapAttribute(): string
    {
        $jam = '';
        if ($this->jam_mulai) {
            $jam = ' (' . substr($this->jam_mulai, 0, 5);
            if ($this->jam_selesai) {
                $jam .= ' - ' . substr($this->jam_selesai, 0, 5);
            }
            $jam .= ' WIB)';
        }
        return ($this->hari ?: 'Jumat') . $jam . ($this->tempat ? ' @ ' . $this->tempat : '');
    }
}
