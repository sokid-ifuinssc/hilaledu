<?php

namespace App\Exports;

use App\Models\JenisPelanggaran;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JenisPelanggaranExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return JenisPelanggaran::select('nama', 'poin', 'deskripsi')->get();
    }

    public function headings(): array
    {
        return [
            'Nama Pelanggaran',
            'Poin',
            'Deskripsi',
        ];
    }
}
