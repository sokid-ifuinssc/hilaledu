<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\JenisPelanggaran;
use App\Models\Kelas;
use App\Models\Notifikasi;
use App\Models\Pelanggaran;
use App\Models\ProgresPelanggaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class PelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $tahunAjaran = TahunAjaran::aktif();
        $tahunAjarans = TahunAjaran::orderBy('tanggal_mulai', 'desc')->get();
        $filterTahun = $request->get('tahun_ajaran_id', $tahunAjaran?->id);

        $query = Pelanggaran::with(['siswa.kelas.jurusan', 'jenisPelanggaran', 'pencatat', 'progresPelanggaran'])
            ->when($filterTahun && $filterTahun !== 'semua', fn($q) => $q->where('tahun_ajaran_id', $filterTahun));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('siswa', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $request->kelas_id));
        }

        $pelanggarans = $query->orderBy('tanggal_pelanggaran', 'desc')->paginate(15)->withQueryString();
        $kelasList = Kelas::with('jurusan')->orderBy('nama')->get();

        return view('bk.pelanggaran.index', compact('pelanggarans', 'kelasList', 'tahunAjarans', 'filterTahun'));
    }

    public function create(Request $request)
    {
        $siswas = Siswa::with('kelas.jurusan')->aktif()->orderBy('nama_lengkap')->get();
        $jenisPelanggarans = JenisPelanggaran::orderBy('nama')->get();
        $kelasList = Kelas::with('jurusan')->orderBy('nama')->get();
        $jenisTindakanOptions = ProgresPelanggaran::jenisTindakanOptions();

        // Jika ada siswa_id terpilih, ambil histori pelanggaran terakhir
        $historiSiswa = null;
        $suggestedTindakan = null;
        if ($request->filled('siswa_id')) {
            $lastProgres = ProgresPelanggaran::whereHas('pelanggaran', function ($q) use ($request) {
                $q->where('siswa_id', $request->siswa_id);
            })->orderBy('created_at', 'desc')->first();

            if ($lastProgres) {
                $suggestedTindakan = ProgresPelanggaran::getNextEskalasi($lastProgres->jenis_tindakan);
            }

            $historiSiswa = Pelanggaran::with(['jenisPelanggaran', 'progresPelanggaran'])
                ->where('siswa_id', $request->siswa_id)
                ->orderBy('tanggal_pelanggaran', 'desc')
                ->limit(10)
                ->get();
        }

        return view('bk.pelanggaran.create', compact(
            'siswas', 'jenisPelanggarans', 'kelasList',
            'jenisTindakanOptions', 'historiSiswa', 'suggestedTindakan'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'jenis_pelanggaran_id' => 'required|exists:jenis_pelanggarans,id',
            'tanggal_pelanggaran' => 'required|date',
            'waktu_pelanggaran' => 'nullable',
            'deskripsi' => 'required|string',
            'bukti' => 'nullable|image|max:2048',
            'jenis_tindakan' => 'required|in:peringatan_lisan,teguran_lisan,home_visit,pemanggilan_ortu,sp1,sp2,sp3',
        ]);

        $tahunAjaran = TahunAjaran::aktif();
        $jenisPelanggaran = JenisPelanggaran::findOrFail($validated['jenis_pelanggaran_id']);

        // Handle file upload
        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti-pelanggaran', 'public');
        }

        // Tentukan status awal pelanggaran
        $statusPelanggaran = $validated['jenis_tindakan'] === 'peringatan_lisan' ? 'selesai' : 'proses';

        $pelanggaran = Pelanggaran::create([
            'siswa_id' => $validated['siswa_id'],
            'jenis_pelanggaran_id' => $validated['jenis_pelanggaran_id'],
            'tanggal_pelanggaran' => $validated['tanggal_pelanggaran'],
            'waktu_pelanggaran' => $validated['waktu_pelanggaran'],
            'deskripsi' => $validated['deskripsi'],
            'bukti' => $buktiPath,
            'poin' => $jenisPelanggaran->poin,
            'dicatat_oleh' => auth()->id(),
            'tahun_ajaran_id' => $tahunAjaran->id,
            'status' => $statusPelanggaran,
        ]);

        // Kurangi poin siswa
        $siswa = Siswa::findOrFail($validated['siswa_id']);
        $siswa->kurangiPoin($jenisPelanggaran->poin);

        // Tentukan status progres awal
        $jenisTindakan = $validated['jenis_tindakan'];
        $statusProgres = match (true) {
            $jenisTindakan === 'peringatan_lisan' => 'selesai',
            in_array($jenisTindakan, ['sp1', 'sp2', 'sp3']) => 'menunggu_approval',
            in_array($jenisTindakan, ['pemanggilan_ortu']) => 'menunggu_cetak_surat',
            default => 'menunggu_laporan', // teguran_lisan, home_visit
        };

        // Buat progres pelanggaran
        $progres = ProgresPelanggaran::create([
            'pelanggaran_id' => $pelanggaran->id,
            'jenis_tindakan' => $jenisTindakan,
            'status' => $statusProgres,
            'created_by' => auth()->id(),
        ]);

        // Audit Trail
        AuditService::logPelanggaranCreated($pelanggaran, $jenisTindakan);
        AuditService::log($progres, 'created', null, $statusProgres, "Progres dibuat dengan tindakan: " . ProgresPelanggaran::jenisTindakanOptions()[$jenisTindakan]);

        // Kirim notifikasi berdasarkan jenis tindakan
        $kelas = $siswa->kelas;
        $this->kirimNotifikasi($progres, $pelanggaran, $siswa, $kelas);

        return redirect()->route('bk.pelanggaran.index')
            ->with('success', 'Pelanggaran berhasil dicatat dengan tindakan: ' . ProgresPelanggaran::jenisTindakanOptions()[$jenisTindakan]);
    }

    /**
     * Kirim notifikasi ke role yang terlibat berdasarkan jenis tindakan
     */
    private function kirimNotifikasi(ProgresPelanggaran $progres, Pelanggaran $pelanggaran, Siswa $siswa, $kelas)
    {
        $jenisTindakan = $progres->jenis_tindakan;
        $tindakanLabel = $progres->jenis_tindakan_label;

        // Notifikasi ke siswa
        if ($siswa->user_id) {
            Notifikasi::kirim(
                $siswa->user_id,
                'Pelanggaran Dicatat',
                "Anda tercatat melakukan pelanggaran dengan tindakan: {$tindakanLabel}.",
                'pelanggaran_baru',
                route('siswa.pelanggaran.show', $pelanggaran->id)
            );
        }

        // Notifikasi ke Wali Kelas (semua kecuali peringatan lisan)
        if ($kelas && $kelas->wali_kelas_id && $jenisTindakan !== 'peringatan_lisan') {
            $aksi = in_array($jenisTindakan, ['sp1', 'sp2', 'sp3']) ? 'Perlu persetujuan Anda.' : 'Perlu laporan dari Anda.';
            Notifikasi::kirim(
                $kelas->wali_kelas_id,
                "Progres Pelanggaran - {$tindakanLabel}",
                "Siswa {$siswa->nama_lengkap} ({$kelas->nama}): {$tindakanLabel}. {$aksi}",
                'progres_baru',
                route('walikelas.progres.show', $pelanggaran->id)
            );
        }

        // Notifikasi ke Kaprog (SP1, SP2, SP3)
        if (in_array($jenisTindakan, ['sp1', 'sp2', 'sp3']) && $kelas && $kelas->jurusan && $kelas->jurusan->kaprog_id) {
            Notifikasi::kirim(
                $kelas->jurusan->kaprog_id,
                "Progres Pelanggaran - {$tindakanLabel}",
                "Siswa {$siswa->nama_lengkap} ({$kelas->nama}): {$tindakanLabel}. Perlu persetujuan Anda.",
                'progres_baru',
                route('kaprog.progres.show', $pelanggaran->id)
            );
        }

        // Notifikasi ke Waka Kesiswaan (SP2, SP3)
        if (in_array($jenisTindakan, ['sp2', 'sp3'])) {
            $wakaUsers = User::role('waka_kesiswaan')->where('is_active', true)->get();
            foreach ($wakaUsers as $waka) {
                Notifikasi::kirim(
                    $waka->id,
                    "Progres Pelanggaran - {$tindakanLabel}",
                    "Siswa {$siswa->nama_lengkap}: {$tindakanLabel}. Perlu persetujuan Anda.",
                    'progres_baru',
                    route('waka.progres.show', $pelanggaran->id)
                );
            }
        }

        // Notifikasi ke Kepala Sekolah (SP3)
        if ($jenisTindakan === 'sp3') {
            $kepsekUsers = User::role('kepala_sekolah')->where('is_active', true)->get();
            foreach ($kepsekUsers as $kepsek) {
                Notifikasi::kirim(
                    $kepsek->id,
                    "Progres Pelanggaran - {$tindakanLabel}",
                    "Siswa {$siswa->nama_lengkap}: {$tindakanLabel}. Perlu persetujuan Anda.",
                    'progres_baru',
                    route('kepsek.progres.show', $pelanggaran->id)
                );
            }
        }
    }

    public function show(Pelanggaran $pelanggaran)
    {
        $pelanggaran->load(['siswa.kelas.jurusan', 'jenisPelanggaran', 'pencatat', 'progresPelanggaran']);

        // Histori semua pelanggaran siswa ini (lintas tahun ajaran)
        $historiPelanggaran = Pelanggaran::with(['jenisPelanggaran', 'tahunAjaran', 'progresPelanggaran'])
            ->where('siswa_id', $pelanggaran->siswa_id)
            ->orderBy('tanggal_pelanggaran', 'desc')
            ->get();

        return view('bk.pelanggaran.show', compact('pelanggaran', 'historiPelanggaran'));
    }

    public function edit(Pelanggaran $pelanggaran)
    {
        $siswas = Siswa::with('kelas.jurusan')->aktif()->orderBy('nama_lengkap')->get();
        $jenisPelanggarans = JenisPelanggaran::orderBy('nama')->get();
        return view('bk.pelanggaran.edit', compact('pelanggaran', 'siswas', 'jenisPelanggarans'));
    }

    public function update(Request $request, Pelanggaran $pelanggaran)
    {
        $validated = $request->validate([
            'deskripsi' => 'required|string',
            'tanggal_pelanggaran' => 'required|date',
        ]);

        $pelanggaran->update($validated);

        return redirect()->route('bk.pelanggaran.show', $pelanggaran)
            ->with('success', 'Pelanggaran berhasil diperbarui.');
    }

    /**
     * API endpoint: dapatkan histori pelanggaran siswa (untuk AJAX di form create)
     */
    public function getHistoriSiswa(Request $request)
    {
        $siswaId = $request->get('siswa_id');
        if (!$siswaId) {
            return response()->json(['histori' => [], 'suggested' => null]);
        }

        $histori = Pelanggaran::with(['jenisPelanggaran', 'progresPelanggaran'])
            ->where('siswa_id', $siswaId)
            ->orderBy('tanggal_pelanggaran', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'tanggal' => $p->tanggal_pelanggaran->format('d/m/Y'),
                    'pelanggaran' => $p->jenisPelanggaran->nama,
                    'poin' => $p->poin,
                    'jenis_tindakan' => $p->progresPelanggaran?->jenis_tindakan_label ?? '-',
                    'status' => $p->status_label,
                ];
            });

        // Suggest next eskalasi
        $lastProgres = ProgresPelanggaran::whereHas('pelanggaran', function ($q) use ($siswaId) {
            $q->where('siswa_id', $siswaId);
        })->orderBy('created_at', 'desc')->first();

        $suggested = $lastProgres ? ProgresPelanggaran::getNextEskalasi($lastProgres->jenis_tindakan) : null;

        return response()->json([
            'histori' => $histori,
            'suggested' => $suggested,
        ]);
    }
}
