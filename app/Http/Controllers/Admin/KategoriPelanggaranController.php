<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriPelanggaran;
use App\Models\JenisPelanggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KategoriPelanggaranController extends Controller
{
    public function index()
    {
        $kategoris = KategoriPelanggaran::withCount('jenisPelanggarans')->paginate(10);
        return view('admin.kategori-pelanggaran.index', compact('kategoris'));
    }

    public function create()
    {
        return view('admin.kategori-pelanggaran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'bobot_poin' => 'required|integer|min:1',
            'warna' => 'nullable|string|max:10',
            'deskripsi' => 'nullable|string',
        ]);

        KategoriPelanggaran::create($validated);

        return redirect()->route('admin.kategori-pelanggaran.index')
            ->with('success', 'Kategori pelanggaran berhasil ditambahkan.');
    }

    public function edit(KategoriPelanggaran $kategoriPelanggaran)
    {
        return view('admin.kategori-pelanggaran.edit', compact('kategoriPelanggaran'));
    }

    public function update(Request $request, KategoriPelanggaran $kategoriPelanggaran)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'bobot_poin' => 'required|integer|min:1',
            'warna' => 'nullable|string|max:10',
            'deskripsi' => 'nullable|string',
        ]);

        $kategoriPelanggaran->update($validated);

        return redirect()->route('admin.kategori-pelanggaran.index')
            ->with('success', 'Kategori pelanggaran berhasil diperbarui.');
    }

    public function destroy(KategoriPelanggaran $kategoriPelanggaran)
    {
        $kategoriPelanggaran->delete();

        return redirect()->route('admin.kategori-pelanggaran.index')
            ->with('success', 'Kategori pelanggaran berhasil dihapus.');
    }

    /**
     * Export kategori pelanggaran to CSV
     */
    public function export(): StreamedResponse
    {
        $kategoris = KategoriPelanggaran::withCount('jenisPelanggarans')->orderBy('nama')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="kategori_pelanggaran_' . date('Y-m-d') . '.csv"',
        ];

        return response()->streamDownload(function () use ($kategoris) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Nama', 'Bobot Poin', 'Warna', 'Deskripsi', 'Jumlah Jenis Pelanggaran']);

            foreach ($kategoris as $k) {
                fputcsv($handle, [
                    $k->nama,
                    $k->bobot_poin,
                    $k->warna ?? '',
                    $k->deskripsi ?? '',
                    $k->jenis_pelanggarans_count,
                ]);
            }

            fclose($handle);
        }, 'kategori_pelanggaran_' . date('Y-m-d') . '.csv', $headers);
    }

    /**
     * Download import template
     */
    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Nama*', 'Bobot Poin*', 'Warna (hex, contoh: #ef4444)', 'Deskripsi']);
            fputcsv($handle, ['Ringan', '5', '#22c55e', 'Pelanggaran ringan']);
            fputcsv($handle, ['Sedang', '15', '#f59e0b', 'Pelanggaran sedang']);
            fputcsv($handle, ['Berat', '30', '#ef4444', 'Pelanggaran berat']);

            fclose($handle);
        }, 'template_import_kategori_pelanggaran.csv');
    }

    /**
     * Import kategori pelanggaran from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
            rewind($handle);
        }

        // Skip header
        fgetcsv($handle);

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $lineNum = 1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $lineNum++;

                if (empty($row[0]) || str_starts_with($row[0], '---')) {
                    continue;
                }

                $nama = trim($row[0] ?? '');
                $bobotPoin = trim($row[1] ?? '');
                $warna = trim($row[2] ?? '');
                $deskripsi = trim($row[3] ?? '');

                if (empty($nama) || empty($bobotPoin)) {
                    $errors[] = "Baris {$lineNum}: Nama atau Bobot Poin kosong.";
                    $skipped++;
                    continue;
                }

                if (!is_numeric($bobotPoin) || (int)$bobotPoin < 1) {
                    $errors[] = "Baris {$lineNum}: Bobot Poin harus angka positif.";
                    $skipped++;
                    continue;
                }

                // Check duplicate nama
                if (KategoriPelanggaran::where('nama', $nama)->exists()) {
                    $errors[] = "Baris {$lineNum}: Kategori '{$nama}' sudah ada.";
                    $skipped++;
                    continue;
                }

                KategoriPelanggaran::create([
                    'nama' => $nama,
                    'bobot_poin' => (int) $bobotPoin,
                    'warna' => $warna ?: null,
                    'deskripsi' => $deskripsi ?: null,
                ]);

                $imported++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return redirect()->route('admin.kategori-pelanggaran.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }

        fclose($handle);

        $message = "Berhasil import {$imported} kategori pelanggaran.";
        if ($skipped > 0) {
            $message .= " {$skipped} baris dilewati.";
        }
        if (!empty($errors)) {
            $message .= ' Detail: ' . implode(' | ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' ... dan ' . (count($errors) - 5) . ' error lainnya.';
            }
        }

        return redirect()->route('admin.kategori-pelanggaran.index')
            ->with($imported > 0 ? 'success' : 'error', $message);
    }
}

