<?php

namespace App\Imports;

use App\Models\JenisPelanggaran;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JenisPelanggaranImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Skip if required fields are missing
        if (!isset($row['nama_pelanggaran']) || !isset($row['poin'])) {
            return null;
        }

        return new JenisPelanggaran([
            'nama'      => $row['nama_pelanggaran'],
            'poin'      => $row['poin'],
            'deskripsi' => $row['deskripsi'] ?? null,
        ]);
    }
}
