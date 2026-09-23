<?php

namespace App\Http\Controllers\WaliKelas;

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
        ])->forWalikelas($user->id)
          ->where('jenis_tindakan', '!=', 'peringatan_lisan');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis_tindakan')) {
            $query->where('jenis_tindakan', $request->jenis_tindakan);
        }

        $progresList = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'perlu_approve' => ProgresPelanggaran::forWalikelas($user->id)
                ->whereIn('jenis_tindakan', ['sp1', 'sp2', 'sp3'])
                ->where('approval_walikelas', 'belum')
                ->where('status', '!=', 'selesai')
                ->count(),
            'perlu_laporan' => ProgresPelanggaran::forWalikelas($user->id)
                ->whereNull('laporan_walikelas')
                ->where('jenis_tindakan', '!=', 'peringatan_lisan')
                ->where('status', '!=', 'selesai')
                ->count(),
            'selesai' => ProgresPelanggaran::forWalikelas($user->id)
                ->where('status', 'selesai')
                ->count(),
        ];

        return view('walikelas.progres.index', compact('progresList', 'stats'));
    }

    public function show(Pelanggaran $pelanggaran)
    {
        $pelanggaran->load([
            'siswa.kelas.jurusan',
            'jenisPelanggaran.kategori',
            'pencatat',
            'progresPelanggaran',
        ]);

        return view('walikelas.progres.show', compact('pelanggaran'));
    }

    public function approve(Request $request, Pelanggaran $pelanggaran)
    {
        $progres = $pelanggaran->progresPelanggaran;
        if (!$progres || !$progres->requiresApprovalFrom('walikelas')) {
            return back()->with('error', 'Tidak memerlukan persetujuan dari Wali Kelas.');
        }

        $validated = $request->validate([
            'aksi' => 'required|in:disetujui,ditolak',
            'catatan_walikelas' => 'nullable|string',
            'dokumen_walikelas' => 'nullable|file|max:5120',
        ]);

        $data = [
            'approval_walikelas' => $validated['aksi'],
            'approval_walikelas_at' => now(),
            'catatan_walikelas' => $validated['catatan_walikelas'],
        ];

        if ($request->hasFile('dokumen_walikelas')) {
            $data['dokumen_walikelas'] = $request->file('dokumen_walikelas')->store('progres-dokumen', 'public');
        }

        $progres->update($data);
        $progres->refreshStatus();

        // Notifikasi ke BK
        Notifikasi::kirim(
            $progres->created_by,
            'Persetujuan Wali Kelas',
            "Wali Kelas telah " . ($validated['aksi'] == 'disetujui' ? 'menyetujui' : 'menolak') . " progres pelanggaran siswa {$pelanggaran->siswa->nama_lengkap}.",
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
            'laporan_walikelas' => 'required|string',
            'dokumen_laporan_walikelas' => 'nullable|file|max:5120',
        ]);

        $data = ['laporan_walikelas' => $validated['laporan_walikelas']];

        if ($request->hasFile('dokumen_laporan_walikelas')) {
            $data['dokumen_laporan_walikelas'] = $request->file('dokumen_laporan_walikelas')->store('progres-dokumen', 'public');
        }

        $progres->update($data);
        $progres->refreshStatus();

        // Notifikasi ke BK
        Notifikasi::kirim(
            $progres->created_by,
            'Laporan Wali Kelas',
            "Wali Kelas telah mengisi laporan untuk pelanggaran siswa {$pelanggaran->siswa->nama_lengkap}.",
            'progres_laporan',
            route('bk.progres.show', $pelanggaran)
        );

        return back()->with('success', 'Laporan berhasil disimpan.');
    }
}
