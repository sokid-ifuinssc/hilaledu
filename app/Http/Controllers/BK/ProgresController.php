<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\PengaturanSekolah;
use App\Models\ProgresPelanggaran;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class ProgresController extends Controller
{
    public function index(Request $request)
    {
        $tahunAjaran = TahunAjaran::aktif();
        $tahunAjarans = TahunAjaran::orderBy('tanggal_mulai', 'desc')->get();
        $filterTahun = $request->get('tahun_ajaran_id', $tahunAjaran?->id);

        $query = ProgresPelanggaran::with([
            'pelanggaran.siswa.kelas.jurusan',
            'pelanggaran.jenisPelanggaran',
            'pelanggaran.pencatat',
        ]);

        // Filter tahun ajaran
        $query->whereHas('pelanggaran', function ($q) use ($filterTahun) {
            if ($filterTahun && $filterTahun !== 'semua') {
                $q->where('tahun_ajaran_id', $filterTahun);
            }
        });

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter jenis tindakan
        if ($request->filled('jenis_tindakan')) {
            $query->where('jenis_tindakan', $request->jenis_tindakan);
        }

        // Filter kelas
        if ($request->filled('kelas_id')) {
            $query->whereHas('pelanggaran.siswa', fn($q) => $q->where('kelas_id', $request->kelas_id));
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('pelanggaran.siswa', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%"));
        }

        $progresList = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $kelasList = Kelas::with('jurusan')->orderBy('nama')->get();

        $stats = [
            'menunggu_approval' => ProgresPelanggaran::where('status', 'menunggu_approval')->count(),
            'menunggu_cetak_surat' => ProgresPelanggaran::where('status', 'menunggu_cetak_surat')->count(),
            'menunggu_laporan' => ProgresPelanggaran::where('status', 'menunggu_laporan')->count(),
            'selesai' => ProgresPelanggaran::where('status', 'selesai')->count(),
        ];

        return view('bk.progres.index', compact('progresList', 'kelasList', 'tahunAjarans', 'filterTahun', 'stats'));
    }

    public function show(Pelanggaran $pelanggaran)
    {
        $pelanggaran->load([
            'siswa.kelas.jurusan',
            'jenisPelanggaran',
            'pencatat',
            'progresPelanggaran',
        ]);

        // Histori semua pelanggaran siswa ini
        $historiPelanggaran = Pelanggaran::with(['jenisPelanggaran', 'tahunAjaran', 'progresPelanggaran'])
            ->where('siswa_id', $pelanggaran->siswa_id)
            ->orderBy('tanggal_pelanggaran', 'desc')
            ->get();

        $settings = PengaturanSekolah::getAllSettings();

        return view('bk.progres.show', compact('pelanggaran', 'historiPelanggaran', 'settings'));
    }

    public function cetakSurat(Request $request, Pelanggaran $pelanggaran)
    {
        $progres = $pelanggaran->progresPelanggaran;
        if (!$progres) {
            return back()->with('error', 'Progres tidak ditemukan.');
        }

        // Hanya update status jika belum pernah dicetak
        if (!$progres->surat_dicetak) {
            $progres->update([
                'surat_dicetak' => true,
                'surat_dicetak_at' => now(),
            ]);
            $progres->refreshStatus();
        }

        return $this->renderSurat($pelanggaran, $progres);
    }

    /**
     * Lihat/cetak ulang surat tanpa mengubah status (GET)
     */
    public function lihatSurat(Pelanggaran $pelanggaran)
    {
        $progres = $pelanggaran->progresPelanggaran;
        if (!$progres) {
            return back()->with('error', 'Progres tidak ditemukan.');
        }

        return $this->renderSurat($pelanggaran, $progres);
    }

    /**
     * Render view surat berdasarkan jenis tindakan
     */
    private function renderSurat(Pelanggaran $pelanggaran, ProgresPelanggaran $progres)
    {
        $pelanggaran->load(['siswa.kelas.jurusan', 'jenisPelanggaran', 'pencatat']);
        $settings = PengaturanSekolah::getAllSettings();

        // Hitung total poin dan pelanggaran siswa
        $totalPelanggaran = Pelanggaran::where('siswa_id', $pelanggaran->siswa_id)->count();
        $totalPoin = Pelanggaran::where('siswa_id', $pelanggaran->siswa_id)->sum('poin');
        $historiPelanggaran = Pelanggaran::with(['jenisPelanggaran', 'tahunAjaran', 'progresPelanggaran'])
            ->where('siswa_id', $pelanggaran->siswa_id)
            ->orderBy('tanggal_pelanggaran', 'asc')
            ->get();

        // Tentukan view surat berdasarkan jenis tindakan
        $tingkat = match ($progres->jenis_tindakan) {
            'sp1' => 1,
            'sp2' => 2,
            'sp3' => 3,
            default => 0,
        };

        if (in_array($progres->jenis_tindakan, ['sp1', 'sp2', 'sp3'])) {
            return view('bk.progres.surat-peringatan', compact('pelanggaran', 'progres', 'settings', 'tingkat', 'totalPelanggaran', 'totalPoin', 'historiPelanggaran'));
        }

        // Surat panggilan orang tua
        return view('bk.progres.surat-panggilan', compact('pelanggaran', 'progres', 'settings', 'totalPelanggaran', 'totalPoin'));
    }

    public function isiLaporan(Request $request, Pelanggaran $pelanggaran)
    {
        $progres = $pelanggaran->progresPelanggaran;
        if (!$progres) {
            return back()->with('error', 'Progres tidak ditemukan.');
        }

        $validated = $request->validate([
            'laporan_bk' => 'required|string',
            'dokumen_laporan_bk' => 'nullable|file|max:5120',
        ]);

        $data = ['laporan_bk' => $validated['laporan_bk']];

        if ($request->hasFile('dokumen_laporan_bk')) {
            $data['dokumen_laporan_bk'] = $request->file('dokumen_laporan_bk')->store('progres-dokumen', 'public');
        }

        $progres->update($data);
        $progres->refreshStatus();

        return back()->with('success', 'Laporan BK berhasil disimpan.');
    }

    public function selesaikan(Pelanggaran $pelanggaran)
    {
        $progres = $pelanggaran->progresPelanggaran;
        if (!$progres) {
            return back()->with('error', 'Progres tidak ditemukan.');
        }

        if (!$progres->canBeClosed()) {
            return back()->with('error', 'Progres belum dapat diselesaikan. Pastikan semua persyaratan sudah terpenuhi.');
        }

        $progres->update(['status' => 'selesai']);
        $pelanggaran->update(['status' => 'selesai']);

        return redirect()->route('bk.progres.index')
            ->with('success', 'Pelanggaran berhasil diselesaikan.');
    }
}
