<?php

namespace App\Imports;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class MasterDataImport implements WithMultipleSheets
{
    use Importable;

    private JurusanSheetImport $jurusanSheet;
    private KelasSheetImport $kelasSheet;
    private TahunAjaranSheetImport $tahunAjaranSheet;

    public function __construct()
    {
        $this->jurusanSheet = new JurusanSheetImport();
        $this->kelasSheet = new KelasSheetImport();
        $this->tahunAjaranSheet = new TahunAjaranSheetImport();
    }

    public function sheets(): array
    {
        return [
            'Jurusan'       => $this->jurusanSheet,
            'Kelas'         => $this->kelasSheet,
            'Tahun Ajaran'  => $this->tahunAjaranSheet,
        ];
    }

    public function getSummary(): array
    {
        return [
            'jurusan'      => $this->jurusanSheet->getCount(),
            'kelas'        => $this->kelasSheet->getCount(),
            'tahun_ajaran' => $this->tahunAjaranSheet->getCount(),
        ];
    }
}

// ============ JURUSAN SHEET IMPORT ============
class JurusanSheetImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;
    private int $count = 0;

    public function model(array $row)
    {
        if (empty($row['kode'])) return null;

        $existing = Jurusan::where('kode', $row['kode'])->first();
        if ($existing) {
            $existing->update([
                'nama'          => $row['nama_jurusan'] ?? $existing->nama,
                'singkatan'     => $row['singkatan'] ?? $existing->singkatan,
                'ketua_jurusan' => $row['ketua_jurusan'] ?? $existing->ketua_jurusan,
            ]);
            $this->count++;
            return null;
        }

        $this->count++;
        return new Jurusan([
            'kode'          => $row['kode'],
            'nama'          => $row['nama_jurusan'],
            'singkatan'     => $row['singkatan'] ?? $row['kode'],
            'ketua_jurusan' => $row['ketua_jurusan'] ?? null,
            'is_aktif'      => true,
        ]);
    }

    public function getCount(): int { return $this->count; }
}

// ============ KELAS SHEET IMPORT ============
class KelasSheetImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;
    private int $count = 0;

    public function model(array $row)
    {
        if (empty($row['nama_kelas'])) return null;

        // Resolve jurusan by singkatan or kode
        $jurusan = Jurusan::where('singkatan', $row['jurusan'] ?? '')
            ->orWhere('kode', $row['jurusan'] ?? '')
            ->first();

        // Resolve tahun ajaran by nama
        $ta = TahunAjaran::where('nama', $row['tahun_ajaran'] ?? '')->first();

        $this->count++;

        return new Kelas([
            'tingkat'         => $row['tingkat'] ?? 'X',
            'nama_kelas'      => $row['nama_kelas'],
            'jurusan_id'      => $jurusan?->id,
            'tahun_ajaran_id' => $ta?->id,
            'wali_kelas'      => $row['wali_kelas'] ?? null,
        ]);
    }

    public function getCount(): int { return $this->count; }
}

// ============ TAHUN AJARAN SHEET IMPORT ============
class TahunAjaranSheetImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;
    private int $count = 0;

    public function model(array $row)
    {
        if (empty($row['nama'])) return null;

        $existing = TahunAjaran::where('nama', $row['nama'])->first();
        if ($existing) {
            $existing->update([
                'tahun_mulai'   => $row['tahun_mulai'] ?? $existing->tahun_mulai,
                'tahun_selesai' => $row['tahun_selesai'] ?? $existing->tahun_selesai,
            ]);
            $this->count++;
            return null;
        }

        $this->count++;
        return new TahunAjaran([
            'nama'          => $row['nama'],
            'tahun_mulai'   => $row['tahun_mulai'],
            'tahun_selesai' => $row['tahun_selesai'],
            'is_aktif'      => false,
        ]);
    }

    public function getCount(): int { return $this->count; }
}
