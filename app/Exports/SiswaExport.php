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

class SiswaExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    use Exportable;

    public function __construct(private ?string $search = null) {}

    public function title(): string
    {
        return 'Data Siswa';
    }

    public function query()
    {
        return User::query()
            ->with('kelas.jurusan')
            ->where('role', 'siswa')
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
            'Kelas / Rombel',
            'Username',
            'Email',
            'NIS / NISN',
            'No. HP Siswa',
            'Jenis Kelamin',
            'Nama Ayah',
            'Nama Ibu',
            'No. HP Ortu / Wali',
            'Alamat',
            'Desa / Kelurahan',
            'Kecamatan',
            'Kabupaten / Kota',
            'Provinsi',
            'Pendidikan SD',
            'Tahun Lulus SD',
            'Pendidikan SMP',
            'Tahun Lulus SMP',
            'Status',
            'Terdaftar',
        ];
    }

    public function map($siswa): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $siswa->name,
            $siswa->kelas?->nama_lengkap ?? '-',
            $siswa->username,
            $siswa->email,
            $siswa->nip ?? '-',
            $siswa->no_hp ?? '-',
            $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : ($siswa->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
            $siswa->nama_ayah ?? '-',
            $siswa->nama_ibu ?? '-',
            $siswa->no_hp_ortu ?? '-',
            $siswa->alamat ?? '-',
            $siswa->desa ?? '-',
            $siswa->kecamatan ?? '-',
            $siswa->kabupaten ?? '-',
            $siswa->provinsi ?? '-',
            $siswa->pendidikan_sd ?? '-',
            $siswa->tahun_lulus_sd ?? '-',
            $siswa->pendidikan_smp ?? '-',
            $siswa->tahun_lulus_smp ?? '-',
            $siswa->is_active ? 'Aktif' : 'Nonaktif',
            $siswa->created_at->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'    => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill'    => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF27AE60']],
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
            'E' => 18,
            'F' => 18,
            'G' => 14,
            'H' => 22,
            'I' => 22,
            'J' => 20,
            'K' => 30,
            'L' => 18,
            'M' => 18,
            'N' => 20,
            'O' => 18,
            'P' => 22,
            'Q' => 14,
            'R' => 22,
            'S' => 14,
            'T' => 10,
            'U' => 14,
        ];
    }
}
