<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Payroll extends Model
{
    protected $table = 'payrolls';

    protected $fillable = [
        'nomor_slip',
        'payroll_periode_id',
        'user_id',
        'gaji_pokok',
        'total_honor_jam',
        'total_tunjangan',
        'total_penerimaan',
        'total_potongan',
        'gaji_bersih',
        'jumlah_jam_mengajar',
        'jumlah_kehadiran',
        'status',
        'tanggal_dibayar',
        'metode_pembayaran',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'gaji_pokok'          => 'decimal:2',
            'total_honor_jam'     => 'decimal:2',
            'total_tunjangan'     => 'decimal:2',
            'total_penerimaan'    => 'decimal:2',
            'total_potongan'      => 'decimal:2',
            'gaji_bersih'         => 'decimal:2',
            'jumlah_jam_mengajar' => 'integer',
            'jumlah_kehadiran'    => 'integer',
            'tanggal_dibayar'     => 'date',
        ];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriode::class, 'payroll_periode_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class, 'payroll_id');
    }

    public function penerimaanItems(): HasMany
    {
        return $this->items()->where('jenis', 'penerimaan');
    }

    public function potonganItems(): HasMany
    {
        return $this->items()->where('jenis', 'potongan');
    }

    /**
     * Hitung ulang total dan gaji bersih dari item-item
     */
    public function recalculateTotals(): void
    {
        $penerimaan = $this->items()->where('jenis', 'penerimaan')->sum('nominal');
        $potongan   = $this->items()->where('jenis', 'potongan')->sum('nominal');

        $this->total_penerimaan = $penerimaan;
        $this->total_potongan   = $potongan;
        $this->gaji_bersih      = max(0, $penerimaan - $potongan);
        $this->save();

        // Update periode juga
        $this->periode?->recalculateTotals();
    }
}
