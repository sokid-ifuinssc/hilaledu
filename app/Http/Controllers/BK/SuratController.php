<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\Pelanggaran;
use App\Models\PengaturanSekolah;
use App\Models\Rekomendasi;
use Illuminate\Http\Request;

class SuratController extends Controller
{
    public function teguran(Request $request)
    {
        $pelanggaranId = $request->get('pelanggaran_id');
        if (!$pelanggaranId) {
            return redirect()->route('bk.pelanggaran.index')
                ->with('error', 'Pilih pelanggaran terlebih dahulu untuk mencetak Surat Teguran. Klik "Detail" pada pelanggaran, lalu pilih tombol "Surat Teguran".');
        }
        $pelanggaran = Pelanggaran::with(['siswa.kelas.jurusan', 'jenisPelanggaran.kategori', 'progresPelanggaran'])->findOrFail($pelanggaranId);
        $settings = PengaturanSekolah::getAllSettings();

        return view('bk.surat.teguran', compact('pelanggaran', 'settings'));
    }

    public function peringatan(Request $request)
    {
        $pelanggaranId = $request->get('pelanggaran_id');
        if (!$pelanggaranId) {
            return redirect()->route('bk.pelanggaran.index')
                ->with('error', 'Pilih pelanggaran terlebih dahulu untuk mencetak Surat Peringatan. Klik "Detail" pada pelanggaran, lalu pilih tombol "Surat Peringatan".');
        }
        $tingkat = $request->get('tingkat', 1); // 1, 2, 3
        $pelanggaran = Pelanggaran::with(['siswa.kelas.jurusan', 'jenisPelanggaran.kategori', 'progresPelanggaran'])->findOrFail($pelanggaranId);
        $settings = PengaturanSekolah::getAllSettings();

        // Hitung total pelanggaran siswa
        $totalPelanggaran = Pelanggaran::where('siswa_id', $pelanggaran->siswa_id)->count();
        $totalPoin = Pelanggaran::where('siswa_id', $pelanggaran->siswa_id)->sum('poin');

        // Ambil semua histori pelanggaran siswa (lintas tahun ajaran)
        $historiPelanggaran = Pelanggaran::with(['jenisPelanggaran.kategori', 'tahunAjaran', 'progresPelanggaran'])
            ->where('siswa_id', $pelanggaran->siswa_id)
            ->orderBy('tanggal_pelanggaran', 'asc')
            ->get();

        return view('bk.surat.peringatan', compact('pelanggaran', 'settings', 'tingkat', 'totalPelanggaran', 'totalPoin', 'historiPelanggaran'));
    }

    /**
     * Tutup pelanggaran (setelah disetujui kepsek)
     */
    public function tutupPelanggaran(Request $request, Pelanggaran $pelanggaran)
    {
        $pelanggaran->update(['status' => 'selesai']);

        return redirect()->route('bk.pelanggaran.show', $pelanggaran)
            ->with('success', 'Pelanggaran berhasil ditutup/diselesaikan.');
    }
}

