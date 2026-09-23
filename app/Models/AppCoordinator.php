<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppCoordinator extends Model
{
    protected $table = 'app_coordinators';

    protected $fillable = [
        'application_id', 'user_id', 'coordinator_role', 'assigned_at', 'assigned_by',
    ];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Label teks peran yang user-friendly.
     */
    public function roleLabel(): string
    {
        return match ($this->coordinator_role) {
            'admin_app'   => 'Admin Aplikasi',
            'koordinator' => 'Koordinator Layanan',
            'pembimbing'  => 'Pembimbing',
            default       => ucfirst(str_replace('_', ' ', $this->coordinator_role ?? 'Koordinator')),
        };
    }

    /**
     * Style CSS badge untuk peran penugasan.
     */
    public function badgeStyle(): string
    {
        return match ($this->coordinator_role) {
            'admin_app'   => 'background:rgba(212,168,67,0.18);color:#d4a843;border:1px solid rgba(212,168,67,0.35);',
            'koordinator' => 'background:rgba(155,89,182,0.18);color:#bb8fce;border:1px solid rgba(155,89,182,0.35);',
            'pembimbing'  => 'background:rgba(52,152,219,0.18);color:#5dade2;border:1px solid rgba(52,152,219,0.35);',
            default       => 'background:rgba(255,255,255,0.08);color:var(--text-light);border:1px solid var(--border-color);',
        };
    }
}
