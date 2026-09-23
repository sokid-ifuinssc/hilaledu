<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TendikExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    use Exportable;

    public function __construct(private ?string $search = null) {}

    public function title(): string
    {
        return 'Data Tendik';
    }

    public function query()
    {
        return User::query()
            ->where('role', 'tendik')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('nip', 'like', "%{$this->search}%")
                        ->orWhere('username', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Lengkap',
            'Username',
            'Email',
            'NUPTK',
            'No. HP',
            'Jenis Kelamin',
            'Tahun Masuk',
            'Lulusan Tahun',
            'Alamat',
            'Desa / Kelurahan',
            'Kecamatan',
            'Kabupaten / Kota',
            'Provinsi',
            'Pendidikan SD',
            'Tahun Lulus SD',
            'Pendidikan SMP',
            'Tahun Lulus SMP',
            'Pendidikan SMA',
            'Tahun Lulus SMA',
            'Pendidikan S1',
            'Tahun Lulus S1',
            'Pendidikan S2',
            'Tahun Lulus S2',
            'Status',
            'Terdaftar',
        ];
    }

    public function map($tendik): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $tendik->name,
            $tendik->username,
            $tendik->email,
            $tendik->nip ?? '-',
            $tendik->no_hp ?? '-',
            $tendik->jenis_kelamin === 'L' ? 'Laki-laki' : ($tendik->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
            $tendik->tahun_masuk ?? '-',
            $tendik->lulusan_tahun ?? '-',
            $tendik->alamat ?? '-',
            $tendik->desa ?? '-',
            $tendik->kecamatan ?? '-',
            $tendik->kabupaten ?? '-',
            $tendik->provinsi ?? '-',
            $tendik->pendidikan_sd ?? '-',
            $tendik->tahun_lulus_sd ?? '-',
            $tendik->pendidikan_smp ?? '-',
            $tendik->tahun_lulus_smp ?? '-',
            $tendik->pendidikan_sma ?? '-',
            $tendik->tahun_lulus_sma ?? '-',
            $tendik->pendidikan_s1 ?? '-',
            $tendik->tahun_lulus_s1 ?? '-',
            $tendik->pendidikan_s2 ?? '-',
            $tendik->tahun_lulus_s2 ?? '-',
            $tendik->is_active ? 'Aktif' : 'Nonaktif',
            $tendik->created_at->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFD35400']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 28,
            'C' => 18,
            'D' => 25,
            'E' => 22,
            'F' => 18,
            'G' => 14,
            'H' => 14,
            'I' => 14,
            'J' => 30,
            'K' => 18,
            'L' => 18,
            'M' => 20,
            'N' => 18,
            'O' => 22,
            'P' => 14,
            'Q' => 22,
            'R' => 14,
            'S' => 22,
            'T' => 14,
            'U' => 28,
            'V' => 14,
            'W' => 28,
            'X' => 14,
            'Y' => 10,
            'Z' => 14,
        ];
    }
}
