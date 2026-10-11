<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LoginLog extends Model
{
    use HasFactory;

    protected $table = 'login_logs';

    protected $fillable = [
        'user_id',
        'login_input',
        'user_name',
        'user_role',
        'ip_address',
        'status',
        'failure_reason',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'country',
        'country_code',
        'region',
        'city',
        'latitude',
        'longitude',
        'timezone',
        'isp',
        'is_suspicious',
        'login_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude'      => 'float',
            'longitude'     => 'float',
            'is_suspicious' => 'boolean',
            'login_at'      => 'datetime',
        ];
    }

    /**
     * Relasi ke Pengguna
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope sukses
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope gagal
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope mencurigakan
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    /**
     * Scope terbaru
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('login_at', '>=', now()->subDays($days));
    }

    /**
     * Label lokasi gabungan
     */
    public function getLocationLabelAttribute(): string
    {
        $parts = array_filter([$this->city, $this->region, $this->country]);
        return count($parts) > 0 ? implode(', ', $parts) : 'Lokasi Tidak Terdeteksi';
    }

    /**
     * Icon Perangkat
     */
    public function getDeviceIconAttribute(): string
    {
        return match (strtolower($this->device_type ?? '')) {
            'mobile'  => 'bi-phone',
            'tablet'  => 'bi-tablet',
            default   => 'bi-laptop',
        };
    }
}
