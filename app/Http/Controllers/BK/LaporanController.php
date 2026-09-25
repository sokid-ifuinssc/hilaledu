<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\KategoriPelanggaran;
use App\Models\Pelanggaran;
use App\Models\TahunAjaran;
use App\Models\Periode;
use App\Models\TanggalPenting;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index()
    {
        $tahunAjaran = TahunAjaran::aktif();
        $tanggalPentings = $tahunAjaran ? TanggalPenting::where('tahun_ajaran_id', $tahunAjaran->id)->get() : collect();
        return view('bk.laporan.index', compact('tahunAjaran', 'tanggalPentings'));
    }

    public function mingguan(Request $request)
    {
        $tahunAjaran = TahunAjaran::aktif();

        $startOfWeek = $request->filled('tanggal') ? \Carbon\Carbon::parse($request->tanggal)->startOfWeek() : now()->startOfWeek();
        $endOfWeek = $startOfWeek->copy()->endOfWeek();

        $pelanggarans = Pelanggaran::with(['siswa.kelas.jurusan', 'jenisPelanggaran', 'progresPelanggaran'])
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->when($request->filled('kelas_id'), fn($q) => $q->whereHas('siswa', fn($s) => $s->where('kelas_id', $request->kelas_id)))
            ->whereBetween('tanggal_pelanggaran', [$startOfWeek, $endOfWeek])
            ->orderBy('tanggal_pelanggaran', 'desc')
            ->get();

        $kelasList = Kelas::with('jurusan')->orderBy('nama')->get();

        // Statistik per kategori
        $statistikKategori = $this->getStatistikKategori($pelanggarans);

        return view('bk.laporan.mingguan', compact('pelanggarans', 'startOfWeek', 'endOfWeek', 'kelasList', 'statistikKategori'));
    }

    public function bulanan(Request $request)
    {
        $tahunAjaran = TahunAjaran::aktif();

        $bulan = $request->filled('bulan') ? (int) $request->bulan : now()->month;
        $tahun = $request->filled('tahun') ? (int) $request->tahun : now()->year;

        $pelanggarans = Pelanggaran::with(['siswa.kelas.jurusan', 'jenisPelanggaran', 'progresPelanggaran'])
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->when($request->filled('kelas_id'), fn($q) => $q->whereHas('siswa', fn($s) => $s->where('kelas_id', $request->kelas_id)))
            ->whereMonth('tanggal_pelanggaran', $bulan)
            ->whereYear('tanggal_pelanggaran', $tahun)
            ->orderBy('tanggal_pelanggaran', 'desc')
            ->get();

        $kelasList = Kelas::with('jurusan')->orderBy('nama')->get();
        $statistikKategori = $this->getStatistikKategori($pelanggarans);

        return view('bk.laporan.bulanan', compact('pelanggarans', 'bulan', 'tahun', 'kelasList', 'statistikKategori'));
    }

    public function praUjian(Request $request)
    {
        $tahunAjaran = TahunAjaran::aktif();
        $jenisUjian = $request->get('jenis', 'uts');

        $tanggalPenting = $tahunAjaran
            ? TanggalPenting::where('tahun_ajaran_id', $tahunAjaran->id)->where('jenis', $jenisUjian)->first()
            : null;

        // Get pelanggaran dari awal semester sampai tanggal ujian
        $pelanggarans = collect();
        if ($tahunAjaran && $tanggalPenting) {
            $pelanggarans = Pelanggaran::with(['siswa.kelas.jurusan', 'jenisPelanggaran', 'progresPelanggaran'])
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->when($request->filled('kelas_id'), fn($q) => $q->whereHas('siswa', fn($s) => $s->where('kelas_id', $request->kelas_id)))
                ->whereBetween('tanggal_pelanggaran', [$tahunAjaran->tanggal_mulai, $tanggalPenting->tanggal_mulai])
                ->orderBy('tanggal_pelanggaran', 'desc')
                ->get();
        }

        $kelasList = Kelas::with('jurusan')->orderBy('nama')->get();
        $statistikKategori = $this->getStatistikKategori($pelanggarans);

        return view('bk.laporan.pra-ujian', compact('pelanggarans', 'tahunAjaran', 'tanggalPenting', 'jenisUjian', 'kelasList', 'statistikKategori'));
    }

    public function semester(Request $request)
    {
        $tahunAjaran = TahunAjaran::aktif();

        $pelanggarans = collect();
        if ($tahunAjaran) {
            $pelanggarans = Pelanggaran::with(['siswa.kelas.jurusan', 'jenisPelanggaran', 'progresPelanggaran'])
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->when($request->filled('kelas_id'), fn($q) => $q->whereHas('siswa', fn($s) => $s->where('kelas_id', $request->kelas_id)))
                ->orderBy('tanggal_pelanggaran', 'desc')
                ->get();
        }

        $kelasList = Kelas::with('jurusan')->orderBy('nama')->get();
        $statistikKategori = $this->getStatistikKategori($pelanggarans);

        // Rekap per kelas
        $rekapPerKelas = $pelanggarans->groupBy(fn($p) => $p->siswa->kelas->nama ?? 'Tanpa Kelas')
            ->map(fn($items) => [
                'jumlah' => $items->count(),
                'poin' => $items->sum('poin'),
                'siswa' => $items->pluck('siswa_id')->unique()->count(),
                'selesai' => $items->where('status', 'selesai')->count(),
            ])->sortByDesc('jumlah');

        return view('bk.laporan.semester', compact('pelanggarans', 'tahunAjaran', 'kelasList', 'statistikKategori', 'rekapPerKelas'));
    }

    public function akhirTahun(Request $request)
    {
        // Ambil semua tahun ajaran untuk dipilih
        $tahunAjarans = TahunAjaran::orderBy('tanggal_mulai', 'desc')->get();
        $tahunAjaranId = $request->get('tahun_ajaran_id');

        $tahunAjaran = $tahunAjaranId
            ? TahunAjaran::find($tahunAjaranId)
            : TahunAjaran::aktif();

        $pelanggarans = collect();
        if ($tahunAjaran) {
            $pelanggarans = Pelanggaran::with(['siswa.kelas.jurusan', 'jenisPelanggaran', 'progresPelanggaran'])
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->when($request->filled('kelas_id'), fn($q) => $q->whereHas('siswa', fn($s) => $s->where('kelas_id', $request->kelas_id)))
                ->orderBy('tanggal_pelanggaran', 'desc')
                ->get();
        }

        $kelasList = Kelas::with('jurusan')->orderBy('nama')->get();
        $statistikKategori = $this->getStatistikKategori($pelanggarans);

        // Rekap per kelas
        $rekapPerKelas = $pelanggarans->groupBy(fn($p) => $p->siswa->kelas->nama ?? 'Tanpa Kelas')
            ->map(fn($items) => [
                'jumlah' => $items->count(),
                'poin' => $items->sum('poin'),
                'siswa' => $items->pluck('siswa_id')->unique()->count(),
                'selesai' => $items->where('status', 'selesai')->count(),
            ])->sortByDesc('jumlah');

        // Rekap per bulan
        $rekapPerBulan = $pelanggarans->groupBy(fn($p) => $p->tanggal_pelanggaran->format('Y-m'))
            ->map(fn($items, $key) => [
                'label' => \Carbon\Carbon::parse($key . '-01')->translatedFormat('F Y'),
                'jumlah' => $items->count(),
                'poin' => $items->sum('poin'),
            ])->sortKeys();

        return view('bk.laporan.akhir-tahun', compact(
            'pelanggarans', 'tahunAjaran', 'tahunAjarans', 'kelasList',
            'statistikKategori', 'rekapPerKelas', 'rekapPerBulan'
        ));
    }

    /**
     * Cetak laporan (print view)
     */
    public function cetak(Request $request)
    {
        $jenis = $request->get('jenis', 'mingguan');

        // Gather data based on jenis
        switch ($jenis) {
            case 'mingguan':
                $data = $this->getDataMingguan($request);
                break;
            case 'bulanan':
                $data = $this->getDataBulanan($request);
                break;
            case 'semester':
                $data = $this->getDataSemester($request);
                break;
            case 'akhir_tahun':
                $data = $this->getDataAkhirTahun($request);
                break;
            default:
                $data = $this->getDataMingguan($request);
        }

        $data['jenis'] = $jenis;
        return view('bk.laporan.cetak', $data);
    }

    // ==== Private helpers ====

    private function getStatistikKategori($pelanggarans)
    {
        return $pelanggarans->groupBy(fn($p) => $p->jenisPelanggaran->nama ?? 'Lainnya')
            ->map(fn($items) => [
                'jumlah' => $items->count(),
                'poin' => $items->sum('poin'),
                'warna' => '#f87171',
            ]);
    }

    private function getDataMingguan(Request $request)
    {
        $tahunAjaran = TahunAjaran::aktif();
        $startOfWeek = $request->filled('tanggal') ? \Carbon\Carbon::parse($request->tanggal)->startOfWeek() : now()->startOfWeek();
        $endOfWeek = $startOfWeek->copy()->endOfWeek();

        $pelanggarans = Pelanggaran::with(['siswa.kelas.jurusan', 'jenisPelanggaran', 'progresPelanggaran'])
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->when($request->filled('kelas_id'), fn($q) => $q->whereHas('siswa', fn($s) => $s->where('kelas_id', $request->kelas_id)))
            ->whereBetween('tanggal_pelanggaran', [$startOfWeek, $endOfWeek])
            ->orderBy('tanggal_pelanggaran', 'desc')->get();

        return [
            'judul' => 'Laporan Mingguan',
            'periode' => $startOfWeek->format('d M Y') . ' - ' . $endOfWeek->format('d M Y'),
            'pelanggarans' => $pelanggarans,
            'statistikKategori' => $this->getStatistikKategori($pelanggarans),
        ];
    }

    private function getDataBulanan(Request $request)
    {
        $tahunAjaran = TahunAjaran::aktif();
        $bulan = $request->filled('bulan') ? (int) $request->bulan : now()->month;
        $tahun = $request->filled('tahun') ? (int) $request->tahun : now()->year;
        $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        $pelanggarans = Pelanggaran::with(['siswa.kelas.jurusan', 'jenisPelanggaran', 'progresPelanggaran'])
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->when($request->filled('kelas_id'), fn($q) => $q->whereHas('siswa', fn($s) => $s->where('kelas_id', $request->kelas_id)))
            ->whereMonth('tanggal_pelanggaran', $bulan)->whereYear('tanggal_pelanggaran', $tahun)
            ->orderBy('tanggal_pelanggaran', 'desc')->get();

        return [
            'judul' => 'Laporan Bulanan',
            'periode' => $namaBulan[$bulan] . ' ' . $tahun,
            'pelanggarans' => $pelanggarans,
            'statistikKategori' => $this->getStatistikKategori($pelanggarans),
        ];
    }

    private function getDataSemester(Request $request)
    {
        $tahunAjaran = TahunAjaran::aktif();
        $pelanggarans = collect();
        if ($tahunAjaran) {
            $pelanggarans = Pelanggaran::with(['siswa.kelas.jurusan', 'jenisPelanggaran', 'progresPelanggaran'])
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->when($request->filled('kelas_id'), fn($q) => $q->whereHas('siswa', fn($s) => $s->where('kelas_id', $request->kelas_id)))
                ->orderBy('tanggal_pelanggaran', 'desc')->get();
        }

        return [
            'judul' => 'Laporan Semester',
            'periode' => $tahunAjaran ? $tahunAjaran->nama . ' - ' . $tahunAjaran->semester : '-',
            'pelanggarans' => $pelanggarans,
            'statistikKategori' => $this->getStatistikKategori($pelanggarans),
            'rekapPerKelas' => $pelanggarans->groupBy(fn($p) => $p->siswa->kelas->nama ?? 'Tanpa Kelas')
                ->map(fn($items) => [
                    'jumlah' => $items->count(), 'poin' => $items->sum('poin'),
                    'siswa' => $items->pluck('siswa_id')->unique()->count(),
                    'selesai' => $items->where('status', 'selesai')->count(),
                ])->sortByDesc('jumlah'),
        ];
    }

    private function getDataAkhirTahun(Request $request)
    {
        $tahunAjaranId = $request->get('tahun_ajaran_id');
        $tahunAjaran = $tahunAjaranId ? TahunAjaran::find($tahunAjaranId) : TahunAjaran::aktif();
        $pelanggarans = collect();
        if ($tahunAjaran) {
            $pelanggarans = Pelanggaran::with(['siswa.kelas.jurusan', 'jenisPelanggaran', 'progresPelanggaran'])
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->when($request->filled('kelas_id'), fn($q) => $q->whereHas('siswa', fn($s) => $s->where('kelas_id', $request->kelas_id)))
                ->orderBy('tanggal_pelanggaran', 'desc')->get();
        }

        return [
            'judul' => 'Laporan Akhir Tahun',
            'periode' => $tahunAjaran ? $tahunAjaran->nama : '-',
            'pelanggarans' => $pelanggarans,
            'statistikKategori' => $this->getStatistikKategori($pelanggarans),
            'rekapPerKelas' => $pelanggarans->groupBy(fn($p) => $p->siswa->kelas->nama ?? 'Tanpa Kelas')
                ->map(fn($items) => [
                    'jumlah' => $items->count(), 'poin' => $items->sum('poin'),
                    'siswa' => $items->pluck('siswa_id')->unique()->count(),
                    'selesai' => $items->where('status', 'selesai')->count(),
                ])->sortByDesc('jumlah'),
        ];
    }
}
