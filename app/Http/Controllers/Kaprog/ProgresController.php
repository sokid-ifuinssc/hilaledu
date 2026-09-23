<?php

namespace App\Http\Controllers\Kaprog;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\Pelanggaran;
use App\Models\ProgresPelanggaran;
use Illuminate\Http\Request;

class ProgresController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = ProgresPelanggaran::with([
            'pelanggaran.siswa.kelas.jurusan',
            'pelanggaran.jenisPelanggaran.kategori',
        ])->forKaprog($user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $progresList = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'perlu_approve' => ProgresPelanggaran::forKaprog($user->id)
                ->where('approval_kaprog', 'belum')
                ->where('status', '!=', 'selesai')
                ->count(),
            'perlu_laporan' => ProgresPelanggaran::forKaprog($user->id)
                ->where('approval_kaprog', 'disetujui')
                ->whereNull('laporan_kaprog')
                ->where('status', '!=', 'selesai')
                ->count(),
            'selesai' => ProgresPelanggaran::forKaprog($user->id)
                ->where('status', 'selesai')
                ->count(),
        ];

        return view('kaprog.progres.index', compact('progresList', 'stats'));
    }

    public function show(Pelanggaran $pelanggaran)
    {
        $pelanggaran->load([
            'siswa.kelas.jurusan',
            'jenisPelanggaran.kategori',
            'pencatat',
            'progresPelanggaran',
        ]);

        return view('kaprog.progres.show', compact('pelanggaran'));
    }

    public function approve(Request $request, Pelanggaran $pelanggaran)
    {
        $progres = $pelanggaran->progresPelanggaran;
        if (!$progres || !$progres->requiresApprovalFrom('kaprog')) {
            return back()->with('error', 'Tidak memerlukan persetujuan dari Kaprog.');
        }

        $validated = $request->validate([
            'aksi' => 'required|in:disetujui,ditolak',
            'catatan_kaprog' => 'nullable|string',
            'dokumen_kaprog' => 'nullable|file|max:5120',
        ]);

        $data = [
            'approval_kaprog' => $validated['aksi'],
            'approval_kaprog_at' => now(),
            'catatan_kaprog' => $validated['catatan_kaprog'],
        ];

        if ($request->hasFile('dokumen_kaprog')) {
            $data['dokumen_kaprog'] = $request->file('dokumen_kaprog')->store('progres-dokumen', 'public');
        }

        $progres->update($data);
        $progres->refreshStatus();

        // Notifikasi ke BK
        Notifikasi::kirim(
            $progres->created_by,
            'Persetujuan Kaprog',
            "Kaprog telah " . ($validated['aksi'] == 'disetujui' ? 'menyetujui' : 'menolak') . " progres pelanggaran siswa {$pelanggaran->siswa->nama_lengkap}.",
            'progres_approval',
            route('bk.progres.show', $pelanggaran)
        );

        return back()->with('success', 'Persetujuan berhasil disimpan.');
    }

    public function isiLaporan(Request $request, Pelanggaran $pelanggaran)
    {
        $progres = $pelanggaran->progresPelanggaran;
        if (!$progres) {
            return back()->with('error', 'Progres tidak ditemukan.');
        }

        $validated = $request->validate([
            'laporan_kaprog' => 'required|string',
            'dokumen_laporan_kaprog' => 'nullable|file|max:5120',
        ]);

        $data = ['laporan_kaprog' => $validated['laporan_kaprog']];

        if ($request->hasFile('dokumen_laporan_kaprog')) {
            $data['dokumen_laporan_kaprog'] = $request->file('dokumen_laporan_kaprog')->store('progres-dokumen', 'public');
        }

        $progres->update($data);
        $progres->refreshStatus();

        Notifikasi::kirim(
            $progres->created_by,
            'Laporan Kaprog',
            "Kaprog telah mengisi laporan untuk pelanggaran siswa {$pelanggaran->siswa->nama_lengkap}.",
            'progres_laporan',
            route('bk.progres.show', $pelanggaran)
        );

        return back()->with('success', 'Laporan berhasil disimpan.');
    }
}
