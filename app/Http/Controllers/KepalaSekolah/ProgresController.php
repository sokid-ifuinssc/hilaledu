<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\Pelanggaran;
use App\Models\ProgresPelanggaran;
use Illuminate\Http\Request;

class ProgresController extends Controller
{
    /**
     * Determine role key based on current user
     */
    private function getRoleKey(): string
    {
        return auth()->user()->isWakaKesiswaan() ? 'waka' : 'kepsek';
    }

    private function getRoutePrefix(): string
    {
        return auth()->user()->isWakaKesiswaan() ? 'waka' : 'kepsek';
    }

    public function index(Request $request)
    {
        $roleKey = $this->getRoleKey();

        $query = ProgresPelanggaran::with([
            'pelanggaran.siswa.kelas.jurusan',
            'pelanggaran.jenisPelanggaran.kategori',
        ]);

        // Filter berdasarkan role
        if ($roleKey === 'waka') {
            $query->forWaka();
        } else {
            $query->forKepsek();
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $progresList = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $approvalField = "approval_{$roleKey}";
        $laporanField = "laporan_{$roleKey}";

        $baseQuery = ProgresPelanggaran::query();
        if ($roleKey === 'waka') {
            $baseQuery->forWaka();
        } else {
            $baseQuery->forKepsek();
        }

        $stats = [
            'perlu_approve' => (clone $baseQuery)
                ->where($approvalField, 'belum')
                ->where('status', '!=', 'selesai')
                ->count(),
            'perlu_laporan' => (clone $baseQuery)
                ->where($approvalField, 'disetujui')
                ->whereNull($laporanField)
                ->where('status', '!=', 'selesai')
                ->count(),
            'selesai' => (clone $baseQuery)
                ->where('status', 'selesai')
                ->count(),
        ];

        return view('kepsek.progres.index', compact('progresList', 'stats', 'roleKey'));
    }

    public function show(Pelanggaran $pelanggaran)
    {
        $pelanggaran->load([
            'siswa.kelas.jurusan',
            'jenisPelanggaran.kategori',
            'pencatat',
            'progresPelanggaran',
        ]);

        $roleKey = $this->getRoleKey();

        return view('kepsek.progres.show', compact('pelanggaran', 'roleKey'));
    }

    public function approve(Request $request, Pelanggaran $pelanggaran)
    {
        $roleKey = $this->getRoleKey();
        $progres = $pelanggaran->progresPelanggaran;

        if (!$progres || !$progres->requiresApprovalFrom($roleKey)) {
            return back()->with('error', 'Tidak memerlukan persetujuan dari Anda.');
        }

        $validated = $request->validate([
            'aksi' => 'required|in:disetujui,ditolak',
            "catatan_{$roleKey}" => 'nullable|string',
            "dokumen_{$roleKey}" => 'nullable|file|max:5120',
        ]);

        $data = [
            "approval_{$roleKey}" => $validated['aksi'],
            "approval_{$roleKey}_at" => now(),
            "catatan_{$roleKey}" => $validated["catatan_{$roleKey}"],
        ];

        if ($request->hasFile("dokumen_{$roleKey}")) {
            $data["dokumen_{$roleKey}"] = $request->file("dokumen_{$roleKey}")->store('progres-dokumen', 'public');
        }

        $progres->update($data);
        $progres->refreshStatus();

        $roleLabel = $roleKey === 'waka' ? 'Waka Kesiswaan' : 'Kepala Sekolah';

        Notifikasi::kirim(
            $progres->created_by,
            "Persetujuan {$roleLabel}",
            "{$roleLabel} telah " . ($validated['aksi'] == 'disetujui' ? 'menyetujui' : 'menolak') . " progres pelanggaran siswa {$pelanggaran->siswa->nama_lengkap}.",
            'progres_approval',
            route('bk.progres.show', $pelanggaran)
        );

        $prefix = $this->getRoutePrefix();
        return back()->with('success', 'Persetujuan berhasil disimpan.');
    }

    public function isiLaporan(Request $request, Pelanggaran $pelanggaran)
    {
        $roleKey = $this->getRoleKey();
        $progres = $pelanggaran->progresPelanggaran;

        if (!$progres) {
            return back()->with('error', 'Progres tidak ditemukan.');
        }

        $validated = $request->validate([
            "laporan_{$roleKey}" => 'required|string',
            "dokumen_laporan_{$roleKey}" => 'nullable|file|max:5120',
        ]);

        $data = ["laporan_{$roleKey}" => $validated["laporan_{$roleKey}"]];

        if ($request->hasFile("dokumen_laporan_{$roleKey}")) {
            $data["dokumen_laporan_{$roleKey}"] = $request->file("dokumen_laporan_{$roleKey}")->store('progres-dokumen', 'public');
        }

        $progres->update($data);
        $progres->refreshStatus();

        $roleLabel = $roleKey === 'waka' ? 'Waka Kesiswaan' : 'Kepala Sekolah';

        Notifikasi::kirim(
            $progres->created_by,
            "Laporan {$roleLabel}",
            "{$roleLabel} telah mengisi laporan untuk pelanggaran siswa {$pelanggaran->siswa->nama_lengkap}.",
            'progres_laporan',
            route('bk.progres.show', $pelanggaran)
        );

        return back()->with('success', 'Laporan berhasil disimpan.');
    }
}
