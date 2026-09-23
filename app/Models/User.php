<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'password_plain',
        'role',
        'admin_role',
        'kelas_id',
        'is_active',
        'avatar',
        // Kolom legacy dari sistem lama
        'nip',
        'nama_lengkap',
        'no_hp',
        'jenis_kelamin',
        'foto',
        // Data Tambahan Guru & Siswa
        'tahun_masuk',
        'lulusan_tahun',
        'tugas_tambahan',
        'jabatan_utama',
        'alamat',
        'desa',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'nama_ayah',
        'nama_ibu',
        'no_hp_ortu',
        'pendidikan_sd',
        'tahun_lulus_sd',
        'pendidikan_smp',
        'tahun_lulus_smp',
        'pendidikan_sma',
        'tahun_lulus_sma',
        'pendidikan_s1',
        'tahun_lulus_s1',
        'pendidikan_s2',
        'tahun_lulus_s2',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'password_plain',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'tugas_tambahan'    => 'array',
        ];
    }

    /**
     * Normalisasi array tugas tambahan
     */
    public static function normalizeTugasTambahan($tugas): array
    {
        if (is_string($tugas)) {
            $tugas = json_decode($tugas, true);
        }
        if (empty($tugas) || !is_array($tugas)) {
            return [];
        }
        return array_values(array_unique(array_filter(array_map('trim', $tugas))));
    }

    /**
     * Accessor tugas_tambahan
     */
    public function getTugasTambahanAttribute($value): array
    {
        $list = self::normalizeTugasTambahan($value);

        if ($this->exists && in_array($this->role, ['guru', 'superadmin'])) {
            // 1. Cek Penugasan Kepala Sekolah
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('pengaturan_sekolah')) {
                    $setting = \App\Models\PengaturanSekolah::first();
                    if ($setting && $setting->kepala_sekolah_id == $this->id) {
                        if (!in_array('Kepala Sekolah', $list)) {
                            $list[] = 'Kepala Sekolah';
                        }
                    }
                }
            } catch (\Throwable $e) {}

            // 2. Cek Penugasan Kaprog Jurusan
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('jurusans')) {
                    $jurusans = \App\Models\Jurusan::where('kaprog_id', $this->id)->get();
                    foreach ($jurusans as $j) {
                        $singkatan = $j->singkatan;
                        $item = !empty($singkatan) ? "Kaprog {$singkatan}" : "Ketua Program Keahlian (Kaprog)";
                        if (!in_array($item, $list)) {
                            $list[] = $item;
                        }
                    }
                }
            } catch (\Throwable $e) {}

            // 3. Cek Penugasan Wali Kelas
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('kelas')) {
                    $kelases = \App\Models\Kelas::where('wali_kelas_id', $this->id)->get();
                    foreach ($kelases as $k) {
                        $namaKelas = $k->nama_kelas ?? $k->nama;
                        if (!empty($namaKelas)) {
                            $item = "Wali Kelas {$namaKelas}";
                            if (!in_array($item, $list)) {
                                $list[] = $item;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {}

            $list = array_values(array_unique(array_filter($list)));
        }

        return $list;
    }

    /**
     * Mutator tugas_tambahan
     */
    public function setTugasTambahanAttribute($value): void
    {
        $normalized = self::normalizeTugasTambahan($value);
        $this->attributes['tugas_tambahan'] = json_encode($normalized);
    }

    /**
     * Cek apakah user mengemban tugas tambahan tertentu di sekolah.
     */
    public function hasTugasTambahan(string $tugas): bool
    {
        $list = $this->tugas_tambahan;
        if (empty($list) || !is_array($list)) {
            return false;
        }
        return in_array($tugas, $list, true) || $this->hasRoleCategory($tugas);
    }

    /**
     * Cek apakah user memiliki kategori peran tertentu
     */
    public function hasRoleCategory(string $category): bool
    {
        foreach ($this->tugas_tambahan ?? [] as $tugas) {
            if ($tugas === $category || str_starts_with($tugas, $category . ' ')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Dapatkan daftar seluruh jabatan & tugas tambahan guru (termasuk tugas di sekolah & aplikasi).
     */
    public function getDaftarJabatanAttribute(): array
    {
        $jabatan = [];
        if (!empty($this->jabatan_utama)) {
            $jabatan[] = $this->jabatan_utama;
        }
        if (!empty($this->admin_role)) {
            $roleNames = [
                'akademik' => 'Admin Akademik & KBM',
                'prakerin' => 'Admin Prakerin / PKL',
                'bk'       => 'Admin Monitoring BK',
                'koperasi' => 'Admin Koperasi Sekolah',
                'keuangan' => 'Admin Keuangan & SPP',
                'tracer'   => 'Admin Tracer Study (BKK)',
            ];
            $jabatan[] = $roleNames[$this->admin_role] ?? ('Admin ' . ucfirst($this->admin_role));
        }
        if (!empty($this->tugas_tambahan) && is_array($this->tugas_tambahan)) {
            $jabatan = array_merge($jabatan, $this->tugas_tambahan);
        }
        return array_values(array_unique(array_filter($jabatan)));
    }

    /**
     * Format label string tugas tambahan untuk tampilan tabel.
     */
    public function getTugasTambahanLabelAttribute(): string
    {
        $list = [];
        if (!empty($this->admin_role)) {
            $roleNames = [
                'akademik' => 'Admin Akademik',
                'prakerin' => 'Admin Prakerin',
                'bk'       => 'Admin BK',
                'koperasi' => 'Admin Koperasi',
                'keuangan' => 'Admin Keuangan',
                'tracer'   => 'Admin Tracer',
            ];
            $list[] = $roleNames[$this->admin_role] ?? ('Admin ' . ucfirst($this->admin_role));
        }
        if (!empty($this->tugas_tambahan) && is_array($this->tugas_tambahan)) {
            $list = array_merge($list, $this->tugas_tambahan);
        }
        if (empty($list)) {
            return '-';
        }
        return implode(', ', array_unique($list));
    }

    /**
     * Kelas tempat siswa terdaftar.
     */
    public function kelas(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function presensiHarians(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PresensiHarianSiswa::class, 'siswa_id');
    }

    /**
     * Penugasan koordinator/admin aplikasi.
     */
    public function appCoordinators(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AppCoordinator::class, 'user_id');
    }

    /**
     * Aplikasi di mana user ditugaskan.
     */
    public function assignedApplications(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Application::class, 'app_coordinators', 'user_id', 'application_id')
            ->withPivot(['coordinator_role', 'assigned_at', 'assigned_by'])
            ->withTimestamps();
    }

    /**
     * Pengaturan gaji (HilalPay) untuk pegawai (Guru / Tendik).
     */
    public function payrollSetting(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Payroll\PayrollSetting::class, 'user_id');
    }

    /**
     * Riwayat slip gaji (HilalPay) pegawai.
     */
    public function payrolls(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Payroll\Payroll::class, 'user_id');
    }

    /**
     * Cek apakah user adalah admin aplikasi tertentu.
     */
    public function isAppAdmin($appId): bool
    {
        $id = $appId instanceof Application ? $appId->id : $appId;
        return $this->appCoordinators()
            ->where('application_id', $id)
            ->where('coordinator_role', 'admin_app')
            ->exists();
    }

    /**
     * Scope query untuk filter role atau tugas tambahan
     */
    public function scopeRole($query, string $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->where('role', $role);
            if ($role === 'kaprog') {
                $q->orWhere('tugas_tambahan', 'like', '%Kaprog%');
            } elseif ($role === 'wali_kelas') {
                $q->orWhere('tugas_tambahan', 'like', '%Wali Kelas%');
            } elseif ($role === 'guru_bk') {
                $q->orWhere('tugas_tambahan', 'like', '%Guru BK%');
            } elseif ($role === 'kepala_sekolah') {
                $q->orWhere('tugas_tambahan', 'like', '%Kepala Sekolah%');
            } elseif ($role === 'waka_kesiswaan') {
                $q->orWhere('tugas_tambahan', 'like', '%Kesiswaan%');
            }
        });
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is superadmin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->isSuperAdmin();
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    public function isTendik(): bool
    {
        return $this->role === 'tendik';
    }

    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    public function isKepalaSekolah(): bool
    {
        return $this->hasTugasTambahan('Kepala Sekolah') || $this->isSuperAdmin();
    }

    public function isKaprog(): bool
    {
        return $this->hasTugasTambahan('Kaprog') || $this->hasTugas('Kepala Program') || $this->isSuperAdmin();
    }

    public function isWaliKelas(): bool
    {
        return $this->hasTugasTambahan('Wali Kelas') || $this->isSuperAdmin();
    }

    public function isGuruBk(): bool
    {
        return $this->hasTugasTambahan('Guru BK') || ($this->role === 'admin' && $this->admin_role === 'bk') || $this->isSuperAdmin();
    }

    public function isWakaKesiswaan(): bool
    {
        return $this->hasTugasTambahan('Kesiswaan') || $this->isSuperAdmin();
    }

    public function hasTugas(string $keyword): bool
    {
        if ($this->isSuperAdmin()) return true;
        foreach ($this->tugas_tambahan ?? [] as $t) {
            if (stripos($t, $keyword) !== false) {
                return true;
            }
        }
        if (stripos($this->jabatan_utama ?? '', $keyword) !== false) {
            return true;
        }
        return false;
    }

    public function isWakaKurikulum(): bool
    {
        return $this->hasTugas('Kurikulum') || $this->isSuperAdmin();
    }

    public function isWakaSarpras(): bool
    {
        return $this->hasTugas('Sarpras') || $this->isSuperAdmin();
    }

    public function isWakaHubin(): bool
    {
        return $this->hasTugas('Humas') || $this->hasTugas('Hubin') || $this->hasTugas('Hubungan Industri') || $this->isSuperAdmin();
    }

    public function isPembinaOsis(): bool
    {
        return $this->hasTugas('OSIS') || $this->isSuperAdmin();
    }

    public function isBk(): bool
    {
        return $this->hasTugas('BK') || $this->hasTugas('Konseling') || ($this->role === 'admin' && $this->admin_role === 'bk') || $this->isSuperAdmin();
    }

    public function isBendaharaBos(): bool
    {
        return $this->hasTugas('Bendahara') || $this->hasTugas('BOS') || $this->isSuperAdmin();
    }

    public function pengelolaAkademik(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PengelolaAkademik::class, 'user_id');
    }

    public function isDesignatedTendik(): bool
    {
        if ($this->isSuperAdmin()) return true;
        if ($this->isTendik() && ($this->hasTugas('Akademik') || $this->hasTugas('Kurikulum'))) {
            return true;
        }
        try {
            return PengelolaAkademik::where('user_id', $this->id)->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Hak akses pengelolaan akademik: Jadwal, Mapel, dan Laporan Kehadiran/KBM
     */
    public function canManageAcademic(): bool
    {
        if ($this->isSuperAdmin()) return true;
        if ($this->isKepalaSekolah()) return true;
        if ($this->isWakaKurikulum()) return true;
        if ($this->isKaprog()) return true;
        if ($this->isBendaharaBos()) return true;
        if ($this->isDesignatedTendik()) return true;
        return false;
    }

    public function canManageJadwal(): bool
    {
        if ($this->isSuperAdmin() || $this->isKepalaSekolah() || $this->isWakaKurikulum() || $this->isKaprog() || $this->isBendaharaBos()) return true;
        try {
            $pa = PengelolaAkademik::where('user_id', $this->id)->first();
            return $pa ? (bool)$pa->can_manage_jadwal : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function canManageMapel(): bool
    {
        if ($this->isSuperAdmin() || $this->isKepalaSekolah() || $this->isWakaKurikulum() || $this->isKaprog() || $this->isBendaharaBos()) return true;
        try {
            $pa = PengelolaAkademik::where('user_id', $this->id)->first();
            return $pa ? (bool)$pa->can_manage_mapel : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function canViewLaporanKehadiran(): bool
    {
        if ($this->isSuperAdmin() || $this->isKepalaSekolah() || $this->isWakaKurikulum() || $this->isKaprog() || $this->isBendaharaBos()) return true;
        try {
            $pa = PengelolaAkademik::where('user_id', $this->id)->first();
            return $pa ? (bool)$pa->can_view_laporan_kehadiran : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function canViewLaporanKbm(): bool
    {
        if ($this->isSuperAdmin() || $this->isKepalaSekolah() || $this->isWakaKurikulum() || $this->isKaprog() || $this->isBendaharaBos()) return true;
        try {
            $pa = PengelolaAkademik::where('user_id', $this->id)->first();
            return $pa ? (bool)$pa->can_view_laporan_kbm : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Cek apakah user adalah Petugas Piket yang bertugas (Tendik maupun Guru)
     */
    public function isPetugasPiket(?string $hari = null, ?string $tanggal = null): bool
    {
        if ($this->isSuperAdmin()) return true;
        if ($this->hasTugas('Piket')) return true;

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('petugas_pikets')) {
                return PetugasPiket::where('user_id', $this->id)
                    ->where('is_aktif', true)
                    ->exists();
            }
        } catch (\Throwable $e) {}

        return false;
    }

    /**
     * Cek apakah user berhak mengakses dan bertindak pada panel Petugas Piket
     */
    public function canAccessPiket(): bool
    {
        if ($this->isSuperAdmin() || $this->isKepalaSekolah() || $this->isWakaKurikulum()) {
            return true;
        }

        return $this->isPetugasPiket();
    }

    public function canPostSchoolEvent(): bool
    {
        return $this->isSuperAdmin() || $this->isWakaKurikulum() || $this->isWakaSarpras() || $this->isWakaKesiswaan() || $this->isWakaHubin() || $this->isPembinaOsis();
    }

    public function hasAnyTugasTambahan(): bool
    {
        if ($this->isSuperAdmin()) return true;
        if ($this->canPostSchoolEvent()) return true;
        $tugas = $this->tugas_tambahan ?? [];
        if (is_array($tugas) && count(array_filter($tugas)) > 0) return true;
        if ($this->jabatan_utama && !in_array($this->jabatan_utama, ['Guru', 'Guru Pengajar', 'Siswa', 'Tendik'])) return true;
        return false;
    }

    /**
     * Booted method untuk mendengarkan event model User.
     * db_hilaledu adalah database tunggal terpadu (monolith).
     */
    protected static function booted()
    {
        static::saved(function ($user) {
            // Database tunggal db_hilaledu: tidak memerlukan sinkronisasi ke database eksternal.
        });
    }

    /**
     * Get the dashboard route for this user's role.
     */
    public function dashboardRoute(): string
    {
        return match ($this->role) {
            'superadmin' => 'superadmin.dashboard',
            'guru'       => 'guru.dashboard',
            'tendik'     => 'tendik.dashboard',
            'siswa'      => 'siswa.dashboard',
            default      => 'login',
        };
    }
}
