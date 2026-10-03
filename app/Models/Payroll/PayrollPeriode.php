<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class PayrollPeriode extends Model
{
    protected $table = 'payroll_periodes';

    protected $fillable = [
        'bulan',
        'tahun',
        'nama_periode',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_pembayaran',
        'status',
        'tampil_ke_guru',
        'total_penerimaan',
        'total_potongan',
        'total_dibayarkan',
        'catatan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'bulan'              => 'integer',
            'tahun'              => 'integer',
            'tampil_ke_guru'     => 'boolean',
            'tanggal_mulai'      => 'date',
            'tanggal_selesai'    => 'date',
            'tanggal_pembayaran' => 'date',
            'total_penerimaan'   => 'decimal:2',
            'total_potongan'     => 'decimal:2',
            'total_dibayarkan'   => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class, 'payroll_periode_id');
    }

    /**
     * Nama bulan Indonesia
     */
    public static function getNamaBulan(int $bulan): string
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $namaBulan[$bulan] ?? "Bulan $bulan";
    }

    /**
     * Hitung ulang total akumulasi periode dari data payroll
     */
    public function recalculateTotals(): void
    {
        $this->total_penerimaan = $this->payrolls()->sum('total_penerimaan');
        $this->total_potongan   = $this->payrolls()->sum('total_potongan');
        $this->total_dibayarkan = $this->payrolls()->sum('gaji_bersih');
        $this->save();
    }

    /**
     * Memastikan kolom tampil_ke_guru sudah ada di tabel payroll_periodes (Self-Healing Schema).
     * Mencegah 1054 Unknown column jika migrasi di hosting belum dijalankan.
     */
    public static function ensureColumnsExist(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('payroll_periodes')) {
                return;
            }

            if (!\Illuminate\Support\Facades\Schema::hasColumn('payroll_periodes', 'tampil_ke_guru')) {
                \Illuminate\Support\Facades\Schema::table('payroll_periodes', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->boolean('tampil_ke_guru')->default(true)->after('status');
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('PayrollPeriode schema check: ' . $e->getMessage());
        }
    }

    /**
     * Scope untuk mengambil periode yang diizinkan tampil ke guru secara aman.
     */
    public function scopeTampilGuru($query)
    {
        self::ensureColumnsExist();
        if (\Illuminate\Support\Facades\Schema::hasColumn('payroll_periodes', 'tampil_ke_guru')) {
            return $query->where('payroll_periodes.tampil_ke_guru', true);
        }
        return $query;
    }
}
