<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;

class PayrollKomponen extends Model
{
    protected $table = 'payroll_komponens';

    protected $fillable = [
        'kode',
        'nama',
        'jenis',
        'tipe',
        'nominal_default',
        'is_aktif',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'nominal_default' => 'decimal:2',
            'is_aktif'        => 'boolean',
        ];
    }
}
