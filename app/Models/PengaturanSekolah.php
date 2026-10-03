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

    // Static storage to hold pre-save old kepala_sekolah_id
    protected static array $_pendingKepalaSync = [];

    protected static function booted()
    {
        // Capture old value BEFORE update (getOriginal() masih valid di sini)
        static::updating(function ($setting) {
            self::$_pendingKepalaSync[$setting->id] = $setting->getOriginal('kepala_sekolah_id');
        });

        static::saved(function ($setting) {
            $oldKepalaId = self::$_pendingKepalaSync[$setting->id] ?? null;
            unset(self::$_pendingKepalaSync[$setting->id]);
            $newKepalaId = $setting->kepala_sekolah_id;

            // 1. Jika kepala sekolah lama diganti, hapus tugas "Kepala Sekolah" dari guru lama
            if ($oldKepalaId && ($oldKepalaId != $newKepalaId)) {
                $oldGuru = User::find($oldKepalaId);
                if ($oldGuru) {
                    // Ambil raw DB value (bukan accessor yang bisa terkena live query)
                    $rawTugas = json_decode($oldGuru->attributes['tugas_tambahan'] ?? '[]', true) ?? [];
                    $rawTugas = array_values(array_filter($rawTugas, fn($t) => $t !== 'Kepala Sekolah'));
                    \Illuminate\Support\Facades\DB::table('users')
                        ->where('id', $oldKepalaId)
                        ->update([
                            'tugas_tambahan' => json_encode($rawTugas),
                            'jabatan_utama'  => $oldGuru->jabatan_utama === 'Kepala Sekolah' ? 'Guru Pengajar' : $oldGuru->jabatan_utama,
                        ]);
                }
            }

            // 2. Tambahkan tugas "Kepala Sekolah" ke profil guru kepala sekolah baru
            if ($newKepalaId) {
                $newGuru = User::find($newKepalaId);
                if ($newGuru) {
                    // Ambil raw DB value (bukan accessor yang bisa terkena live query)
                    $rawTugas = json_decode($newGuru->attributes['tugas_tambahan'] ?? '[]', true) ?? [];
                    if (!in_array('Kepala Sekolah', $rawTugas)) {
                        $rawTugas[] = 'Kepala Sekolah';
                    }
                    $rawTugas = array_values(array_unique(array_filter($rawTugas)));
                    $jabatanBaru = (empty($newGuru->jabatan_utama) || $newGuru->jabatan_utama === 'Guru Pengajar')
                        ? 'Kepala Sekolah' : $newGuru->jabatan_utama;
                    \Illuminate\Support\Facades\DB::table('users')
                        ->where('id', $newKepalaId)
                        ->update([
                            'tugas_tambahan' => json_encode($rawTugas),
                            'jabatan_utama'  => $jabatanBaru,
                        ]);
                }
            }
        });
    }

    public function kepalaSekolah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kepala_sekolah_id');
    }

    /**
     * Ambil nama Kepala Sekolah secara dinamis dari relasi atau penugasan user di database
     */
    public function getKepalaSekolahAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }
        if ($this->kepala_sekolah_id) {
            $user = User::find($this->kepala_sekolah_id);
            if ($user) {
                return $user->name;
            }
        }
        $kepsek = User::where('is_active', true)->get()->first(function($u) {
            return in_array('Kepala Sekolah', $u->daftar_jabatan) || $u->jabatan_utama === 'Kepala Sekolah';
        });
        if ($kepsek) {
            return $kepsek->name;
        }
        return 'Muhammad Mansyur, S.Pt';
    }

    /**
     * Ambil NIP / NUPTK Kepala Sekolah
     */
    public function getNipKepalaSekolahAttribute($value): string
    {
        if (!empty($value) && $value !== '-') {
            return $value;
        }
        if ($this->kepala_sekolah_id) {
            $user = User::find($this->kepala_sekolah_id);
            if ($user && !empty($user->nip)) {
                return $user->nip;
            }
        }
        $kepsek = User::where('is_active', true)->get()->first(function($u) {
            return in_array('Kepala Sekolah', $u->daftar_jabatan) || $u->jabatan_utama === 'Kepala Sekolah';
        });
        if ($kepsek && !empty($kepsek->nip)) {
            return $kepsek->nip;
        }
        return '6942767668130350';
    }

    /**
     * Ambil user yang ditugaskan sebagai Bendahara Sekolah / Keuangan dari database
     */
    public static function getBendaharaSekolah(): ?User
    {
        return User::where('is_active', true)
            ->whereIn('role', ['guru', 'tendik', 'keuangan'])
            ->get()
            ->first(function($u) {
                foreach ($u->daftar_jabatan as $j) {
                    if (stripos($j, 'bendahara') !== false) {
                        return true;
                    }
                }
                return $u->role === 'keuangan';
            });
    }

    /**
     * URL Logo Sekolah yang selalu valid dan aman dari error 404
     */
    public function getLogoUrlAttribute(): string
    {
        if (!empty($this->logo) && file_exists(public_path('storage/' . $this->logo))) {
            return asset('storage/' . $this->logo);
        }
        if (file_exists(public_path('images/logo_smk.png'))) {
            return asset('images/logo_smk.png');
        }
        if (file_exists(public_path('images/logo.png'))) {
            return asset('images/logo.png');
        }
        return asset('images/logo.jpg');
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

    public static function getActiveTahunAjaranId(): ?int
    {
        try {
            $ta = \App\Models\TahunAjaran::where('is_aktif', true)->orWhere('is_active', true)->first();
            if ($ta) {
                return (int)$ta->id;
            }
            $activeTaString = self::getActiveTahunAjaran();
            $taByName = \App\Models\TahunAjaran::where('nama', $activeTaString)->first();
            if ($taByName) {
                return (int)$taByName->id;
            }
            return \App\Models\TahunAjaran::first()?->id;
        } catch (\Throwable $e) {
            return null;
        }
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

        // 1. Cek tabel key-value pengaturan_sekolahs
        try {
            $row = \Illuminate\Support\Facades\DB::table('pengaturan_sekolahs')->where('key', $key)->first();
            if ($row && $row->value !== null) {
                if ($row->tipe === 'json') {
                    $decoded = json_decode($row->value, true);
                    return $decoded !== null ? $decoded : $row->value;
                }
                // Jika format JSON tapi tipenya string/text
                if (is_string($row->value) && (str_starts_with(trim($row->value), '{') || str_starts_with(trim($row->value), '['))) {
                    $decoded = json_decode($row->value, true);
                    if ($decoded !== null) {
                        return $decoded;
                    }
                }
                return $row->value;
            }
        } catch (\Throwable $e) {}

        // 2. Cek kolom di tabel pengaturan_sekolah
        try {
            $setting = self::getSetting();
            if (isset($setting->{$key}) && $setting->{$key} !== null) {
                return $setting->{$key};
            }
        } catch (\Throwable $e) {}

        return $default;
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
            'kepala_sekolah' => $kepala ? ($kepala->nama_lengkap ?? $kepala->name) : 'Mukhammad Mansyur, S.Pt',
            'nip_kepala_sekolah' => $kepala ? ($kepala->nip ?? '-') : '-',
            'tahun_ajaran' => self::getActiveTahunAjaran(),
            'tahun_pelajaran' => self::getActiveTahunAjaran(),
            'semester' => self::getActiveSemester(),
            'semester_aktif' => self::getActiveSemester(),
        ];

        try {
            $items = \Illuminate\Support\Facades\DB::table('pengaturan_sekolahs')->get();
            foreach ($items as $item) {
                if (!empty($item->key)) {
                    if (($item->tipe ?? '') === 'json') {
                        $result[$item->key] = json_decode($item->value, true) ?? $item->value;
                    } elseif (is_string($item->value) && (str_starts_with(trim($item->value), '{') || str_starts_with(trim($item->value), '['))) {
                        $decoded = json_decode($item->value, true);
                        $result[$item->key] = $decoded !== null ? $decoded : $item->value;
                    } else {
                        $result[$item->key] = $item->value;
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

    public static function set(string $key, $value, ?string $label = null, string $tipe = 'text'): void
    {
        // 1. Simpan di tabel pengaturan_sekolah jika berupa kolom fisik
        try {
            $setting = self::getSetting();
            if (\Illuminate\Support\Facades\Schema::hasColumn('pengaturan_sekolah', $key)) {
                $setting->{$key} = is_array($value) ? json_encode($value) : $value;
                $setting->save();
            }
        } catch (\Throwable $e) {}

        // 2. Simpan juga di tabel key-value pengaturan_sekolahs
        try {
            $isJson = is_array($value) || is_object($value) || $tipe === 'json';
            $valStr = $isJson ? json_encode($value) : (string)$value;
            $tipeFinal = $isJson ? 'json' : $tipe;

            $exists = \Illuminate\Support\Facades\DB::table('pengaturan_sekolahs')->where('key', $key)->first();
            if ($exists) {
                \Illuminate\Support\Facades\DB::table('pengaturan_sekolahs')->where('key', $key)->update([
                    'value' => $valStr,
                    'label' => $label ?? $exists->label ?? $key,
                    'tipe'  => $tipeFinal,
                    'updated_at' => now(),
                ]);
            } else {
                \Illuminate\Support\Facades\DB::table('pengaturan_sekolahs')->insert([
                    'key'   => $key,
                    'value' => $valStr,
                    'label' => $label ?? $key,
                    'tipe'  => $tipeFinal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {}
    }
}

