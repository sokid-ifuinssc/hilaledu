<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaturanSekolah extends Model
{
    protected $table = 'pengaturan_sekolah';

    protected $fillable = [
        'nama_sekolah',
        'npsn',
        'kepala_sekolah_id',
        'alamat',
        'email',
        'telepon',
        'website',
        'logo',
    ];

    public static function getSetting(): self
    {
        return self::firstOrCreate(
            [],
            [
                'nama_sekolah' => 'SMK PLUS AL HILAL',
            ]
        );
    }

    protected static function booted()
    {
        static::saved(function ($setting) {
            $oldKepalaId = $setting->getOriginal('kepala_sekolah_id');
            $newKepalaId = $setting->kepala_sekolah_id;

            // 1. Jika kepala sekolah lama diganti, hapus tugas "Kepala Sekolah" dari guru lama
            if ($oldKepalaId && ($oldKepalaId != $newKepalaId)) {
                $oldGuru = User::find($oldKepalaId);
                if ($oldGuru) {
                    $tugas = is_array($oldGuru->tugas_tambahan) ? $oldGuru->tugas_tambahan : [];
                    $tugas = array_filter($tugas, fn($t) => $t !== 'Kepala Sekolah');
                    $oldGuru->tugas_tambahan = array_values(array_unique($tugas));
                    if ($oldGuru->jabatan_utama === 'Kepala Sekolah') {
                        $oldGuru->jabatan_utama = 'Guru Pengajar';
                    }
                    $oldGuru->save();
                }
            }

            // 2. Tambahkan tugas "Kepala Sekolah" ke profil guru kepala sekolah baru
            if ($newKepalaId) {
                $newGuru = User::find($newKepalaId);
                if ($newGuru) {
                    $tugas = is_array($newGuru->tugas_tambahan) ? $newGuru->tugas_tambahan : [];
                    if (!in_array('Kepala Sekolah', $tugas)) {
                        $tugas[] = 'Kepala Sekolah';
                    }
                    $newGuru->tugas_tambahan = array_values(array_unique(array_filter($tugas)));
                    if (empty($newGuru->jabatan_utama) || $newGuru->jabatan_utama === 'Guru Pengajar') {
                        $newGuru->jabatan_utama = 'Kepala Sekolah';
                    }
                    $newGuru->save();
                }
            }
        });
    }

    public function kepalaSekolah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kepala_sekolah_id');
    }

    public static function normalizeTahunAjaran(?string $ta): string
    {
        if (empty($ta)) return '2026/2027';
        $clean = trim($ta);
        $clean = preg_replace('/[^\d\/]/', '', $clean);
        if (preg_match('/^(\d{4})\/(\d{4})$/', $clean)) {
            return $clean;
        }
        return $ta;
    }

    public static function getActiveTahunAjaran(): string
    {
        try {
            $ta = \App\Models\TahunAjaran::where('is_aktif', true)->orWhere('is_active', true)->first();
            if ($ta) {
                return $ta->nama ?? $ta->tahun ?? "{$ta->tahun_mulai}/{$ta->tahun_selesai}";
            }
        } catch (\Throwable $e) {}

        try {
            $kalenderTa = \App\Models\KalenderAkademik::where('is_aktif', true)->value('tahun_ajaran');
            if (!empty($kalenderTa)) {
                return $kalenderTa;
            }
        } catch (\Throwable $e) {}

        return '2026/2027';
    }

    public static function getActiveSemester(): string
    {
        try {
            $ta = \App\Models\TahunAjaran::where('is_aktif', true)->orWhere('is_active', true)->first();
            if ($ta && !empty($ta->semester)) {
                return strtolower($ta->semester);
            }
        } catch (\Throwable $e) {}

        return 'ganjil';
    }

    public static function get(string $key, $default = null)
    {
        if ($key === 'tahun_pelajaran' || $key === 'tahun_ajaran') {
            return self::getActiveTahunAjaran();
        }
        if ($key === 'semester') {
            return self::getActiveSemester();
        }
        $setting = self::getSetting();
        return $setting->{$key} ?? $default;
    }

    public static function getAllSettings(): array
    {
        $setting = self::getSetting();
        $kepala = $setting->kepalaSekolah;

        $result = [
            'nama_sekolah' => $setting->nama_sekolah ?? 'SMK PLUS AL HILAL',
            'npsn' => $setting->npsn ?? '69900000',
            'alamat' => $setting->alamat ?? 'Jl. Pesantren No. 1, Cirebon',
            'email' => $setting->email ?? 'info@smkplusalhilal.sch.id',
            'telepon' => $setting->telepon ?? '0231-123456',
            'website' => $setting->website ?? 'https://smkplusalhilal.sch.id',
            'logo' => $setting->logo ?? null,
            'kepala_sekolah' => $kepala ? ($kepala->nama_lengkap ?? $kepala->name) : 'Drs. H. Ahmad Fauzi, M.Pd.',
            'nip_kepala_sekolah' => $kepala ? ($kepala->nip ?? '-') : '-',
            'tahun_ajaran' => self::getActiveTahunAjaran(),
            'tahun_pelajaran' => self::getActiveTahunAjaran(),
            'semester' => self::getActiveSemester(),
            'semester_aktif' => self::getActiveSemester(),
        ];

        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('pengaturan_sekolah', 'key')) {
                foreach (static::all() as $item) {
                    if (!empty($item->key)) {
                        $result[$item->key] = ($item->tipe ?? '') === 'json' ? json_decode($item->value, true) : $item->value;
                    }
                }
            }
        } catch (\Throwable $e) {}

        return $result;
    }

    public static function getHilalEduAcademicSetting(): ?array
    {
        return [
            'tahun_ajaran' => self::getActiveTahunAjaran(),
            'semester' => self::getActiveSemester(),
            'source' => 'local_monolith',
        ];
    }

    public static function syncFromHilalEdu(): bool
    {
        return true;
    }

    public static function syncToHilalEdu(?string $tahunAjaran, ?string $semester): bool
    {
        if ($tahunAjaran) {
            self::set('tahun_ajaran', $tahunAjaran);
            self::set('tahun_pelajaran', $tahunAjaran);
        }
        if ($semester) {
            self::set('semester', strtolower($semester));
        }
        return true;
    }

    public static function set(string $key, $value): void
    {
        $setting = self::getSetting();
        if (\Illuminate\Support\Facades\Schema::hasColumn('pengaturan_sekolah', $key)) {
            $setting->{$key} = $value;
            $setting->save();
        }
    }
}

