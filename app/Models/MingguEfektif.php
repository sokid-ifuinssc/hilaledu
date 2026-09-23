<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MingguEfektif extends Model
{
    protected $table = 'minggu_efektifs';

    protected $fillable = [
        'tahun_ajaran',
        'semester',
        'mata_pelajaran_id',
        'guru_user_id',
        'kelas',
        'total_minggu',
        'total_tidak_efektif',
        'total_efektif',
        'jam_per_minggu',
        'total_jam_efektif',
        'rincian_bulanan',
        'distribusi_jam',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'rincian_bulanan' => 'array',
            'distribusi_jam'  => 'array',
            'total_minggu'    => 'integer',
            'total_tidak_efektif' => 'integer',
            'total_efektif'   => 'integer',
            'jam_per_minggu'  => 'integer',
            'total_jam_efektif' => 'integer',
        ];
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_user_id');
    }

    /**
     * Hitung ulang minggu efektif & total jam otomatis
     */
    public function calculateTotals(): void
    {
        $rincian = $this->rincian_bulanan ?? [];
        $totalMinggu = 0;
        $totalTidak = 0;

        foreach ($rincian as $item) {
            $totalMinggu += intval($item['total_minggu'] ?? 0);
            $totalTidak  += intval($item['tidak_efektif'] ?? 0);
        }

        $this->total_minggu = $totalMinggu > 0 ? $totalMinggu : $this->total_minggu;
        $this->total_tidak_efektif = $totalTidak;
        $this->total_efektif = max(0, $this->total_minggu - $this->total_tidak_efektif);
        $this->total_jam_efektif = $this->total_efektif * intval($this->jam_per_minggu ?: 4);
    }
}
