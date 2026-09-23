<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KegiatanSekolah extends Model
{
    protected $table = 'kegiatan_sekolahs';

    protected $fillable = [
        'author_user_id',
        'edited_by_user_id',
        'bidang', // waka_kurikulum, waka_sarpras, waka_kesiswaan, waka_hubin, pembina_osis
        'judul',
        'kategori',
        'tanggal_kegiatan',
        'tanggal_selesai',
        'waktu_mulai',
        'waktu_selesai',
        'tempat',
        'sasaran',
        'deskripsi',
        'lampiran',
        'is_active',
        'kalender_akademik_event_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kegiatan' => 'date',
            'tanggal_selesai'  => 'date',
            'is_active'        => 'boolean',
        ];
    }

    public function kalenderEvent(): BelongsTo
    {
        return $this->belongsTo(KalenderAkademikEvent::class, 'kalender_akademik_event_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by_user_id');
    }

    public function canBeEditedBy(?User $user): bool
    {
        if (!$user) return false;
        return $user->id === $this->author_user_id || $user->isSuperAdmin() || $user->canPostSchoolEvent();
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(KegiatanPresensi::class, 'kegiatan_sekolah_id');
    }

    public function getPresensiSayaAttribute(): ?KegiatanPresensi
    {
        if ($this->relationLoaded('presensis')) {
            return $this->presensis->first();
        }
        if (auth()->check()) {
            return $this->presensis()->where('guru_user_id', auth()->id())->first();
        }
        return null;
    }

    public function getBidangLabelAttribute(): string
    {
        return match ($this->bidang) {
            'waka_kurikulum' => 'Waka Kurikulum',
            'waka_sarpras'   => 'Waka Sarpras',
            'waka_kesiswaan' => 'Waka Kesiswaan',
            'waka_hubin'     => 'Waka Hubin (Humas/Industri)',
            'pembina_osis'   => 'Pembina OSIS',
            default          => strtoupper($this->bidang),
        };
    }

    public function getBidangColorAttribute(): string
    {
        return match ($this->bidang) {
            'waka_kurikulum' => 'bg-indigo-600 text-white',
            'waka_sarpras'   => 'bg-amber-600 text-white',
            'waka_kesiswaan' => 'bg-emerald-600 text-white',
            'waka_hubin'     => 'bg-cyan-600 text-white',
            'pembina_osis'   => 'bg-rose-600 text-white',
            default          => 'bg-gray-600 text-white',
        };
    }
}
