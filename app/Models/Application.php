<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Application extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'url',
        'database_name',
        'api_key',
        'sso_enabled',
        'auto_sync',
        'login_url',
        'sso_redirect_url',
        'status',
        'features',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features'    => 'array',
            'sso_enabled' => 'boolean',
            'auto_sync'   => 'boolean',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($app) {
            if (empty($app->api_key)) {
                $app->api_key = 'hila_' . Str::random(40);
            }
            if (empty($app->slug)) {
                $app->slug = Str::slug($app->name);
            }
        });
    }

    /**
     * Cek apakah aplikasi memiliki database terhubung.
     */
    public function hasDatabase(): bool
    {
        return !empty($this->database_name);
    }

    /**
     * Dapatkan nama koneksi dinamis Laravel untuk aplikasi ini.
     */
    public function getDynamicConnectionName(): string
    {
        return 'app_db_' . $this->id;
    }

    /**
     * Admins assigned to this application.
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'app_admins', 'application_id', 'user_id')
            ->withTimestamps()
            ->withPivot('assigned_at');
    }

    /**
     * Guru koordinator yang ditugaskan Admin HilalEdu ke aplikasi ini.
     */
    public function coordinators(): HasMany
    {
        return $this->hasMany(AppCoordinator::class);
    }

    public function coordinatorUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'app_coordinators', 'application_id', 'user_id')
            ->withPivot(['coordinator_role', 'assigned_at', 'assigned_by'])
            ->withTimestamps();
    }

    /**
     * Check if application is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get status label in Indonesian.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'active'      => 'Aktif',
            'inactive'    => 'Nonaktif',
            'coming_soon' => 'Segera Hadir',
            default       => 'Tidak Diketahui',
        };
    }

    /**
     * Get status badge class.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'active'      => 'badge-active',
            'inactive'    => 'badge-inactive',
            'coming_soon' => 'badge-coming',
            default       => 'badge-coming',
        };
    }
}
