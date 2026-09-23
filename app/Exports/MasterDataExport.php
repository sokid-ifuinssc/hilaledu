<?php

namespace App\Exports;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MasterDataExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        return [
            new JurusanSheet(),
            new KelasSheet(),
            new TahunAjaranSheet(),
        ];
    }
}

// ============ JURUSAN SHEET ============
class JurusanSheet implements
    \Maatwebsite\Excel\Concerns\FromQuery,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithMapping,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithColumnWidths,
    \Maatwebsite\Excel\Concerns\WithTitle
{
    public function title(): string { return 'Jurusan'; }

    public function query()
    {
        return Jurusan::orderBy('kode');
    }

    public function headings(): array
    {
        return ['No', 'Kode', 'Nama Jurusan', 'Singkatan', 'Ketua Jurusan', 'Status'];
    }

    public function map($j): array
    {
        static $no = 0; $no++;
        return [$no, $j->kode, $j->nama, $j->singkatan, $j->ketua_jurusan ?? '-', $j->is_aktif ? 'Aktif' : 'Nonaktif'];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFC0392B']]]];
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 10, 'C' => 35, 'D' => 12, 'E' => 30, 'F' => 10];
    }
}

// ============ KELAS SHEET ============
class KelasSheet implements
    \Maatwebsite\Excel\Concerns\FromQuery,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithMapping,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithColumnWidths,
    \Maatwebsite\Excel\Concerns\WithTitle
{
    public function title(): string { return 'Kelas'; }

    public function query()
    {
        return Kelas::with(['jurusan', 'tahunAjaran'])->orderBy('tingkat');
    }

    public function headings(): array
    {
        return ['No', 'Tingkat', 'Nama Kelas', 'Jurusan', 'Tahun Ajaran', 'Wali Kelas'];
    }

    public function map($k): array
    {
        static $no = 0; $no++;
        return [
            $no, $k->tingkat,
            $k->nama ?? $k->nama_kelas,
            $k->jurusan?->singkatan ?? '-',
            $k->tahunAjaran?->nama ?? '-',
            $k->wali_kelas ?? $k->wali_kelas_id ?? '-',
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF8E44AD']]]];
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 10, 'C' => 20, 'D' => 14, 'E' => 14, 'F' => 30];
    }
}

// ============ TAHUN AJARAN SHEET ============
class TahunAjaranSheet implements
    \Maatwebsite\Excel\Concerns\FromQuery,
    \Maatwebsite\Excel\Concerns\WithHeadings,
    \Maatwebsite\Excel\Concerns\WithMapping,
    \Maatwebsite\Excel\Concerns\WithStyles,
    \Maatwebsite\Excel\Concerns\WithColumnWidths,
    \Maatwebsite\Excel\Concerns\WithTitle
{
    public function title(): string { return 'Tahun Ajaran'; }

    public function query()
    {
        return TahunAjaran::orderByDesc('tahun_mulai');
    }

    public function headings(): array
    {
        return ['No', 'Nama', 'Tahun Mulai', 'Tahun Selesai', 'Status'];
    }

    public function map($ta): array
    {
        static $no = 0; $no++;
        return [$no, $ta->nama, $ta->tahun_mulai, $ta->tahun_selesai, $ta->is_aktif ? 'Aktif' : 'Nonaktif'];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFD4A843']]]];
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 15, 'C' => 14, 'D' => 14, 'E' => 10];
    }
}
