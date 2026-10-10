<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AnggotaEkstrakurikuler;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\MataPelajaran;
use App\Models\NilaiMataPelajaran;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuruNilaiController extends Controller
{
    /**
     * Dapatkan daftar mapel dan kelas yang diampu guru saat ini
     */
    private function getMapelDanKelasGuru(): \Illuminate\Support\Collection
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin() || $user->role === 'admin';
        $tahun = PengaturanSekolah::getActiveTahunAjaran();
        $semester = PengaturanSekolah::getActiveSemester();

        // 1. Ambil dari Jadwal Pelajaran aktif
        $jadwalQuery = JadwalPelajaran::with(['mataPelajaran'])
            ->when($tahun, fn($q) => $q->where('tahun_ajaran', $tahun))
            ->when($semester, fn($q) => $q->where('semester', $semester));

        if (!$isSuperAdmin) {
            $jadwalQuery->where('guru_user_id', $user->id);
        }

        $jadwals = $jadwalQuery->get();
        $kelases = Kelas::all();
        $mapelKelasPairs = collect();

        foreach ($jadwals as $j) {
            if ($j->mataPelajaran && !empty($j->kelas)) {
                $kls = $kelases->first(fn($k) => $k->nama_kelas === $j->kelas || $k->nama === $j->kelas);
                if ($kls) {
                    $key = "{$j->mata_pelajaran_id}_{$kls->id}";
                    if (!$mapelKelasPairs->has($key)) {
                        $mapelKelasPairs->put($key, [
                            'mapel' => $j->mataPelajaran,
                            'kelas' => $kls,
                        ]);
                    }
                }
            }
        }

        // 2. Jika jadwal belum lengkap, cek dari penugasan kurikulum atau mapel langsung
        if ($mapelKelasPairs->isEmpty()) {
            $kurQuery = Kurikulum::with(['mataPelajaran'])
                ->when($tahun, fn($q) => $q->where('tahun_ajaran', $tahun))
                ->when($semester, fn($q) => $q->where('semester', $semester));

            if (!$isSuperAdmin) {
                $kurQuery->where('guru_user_id', $user->id);
            }

            $kurs = $kurQuery->get();
            $kelases = Kelas::where('is_aktif', true)->get();

            foreach ($kurs as $k) {
                if ($k->mataPelajaran) {
                    $matchedKelases = $kelases->filter(function($kls) use ($k) {
                        return (stripos($kls->nama_kelas, $k->kelas) !== false) || (stripos($kls->tingkat, $k->tingkat ?? '') !== false);
                    });
                    if ($matchedKelases->isEmpty()) {
                        $matchedKelases = $kelases;
                    }
                    foreach ($matchedKelases as $kls) {
                        $key = "{$k->mata_pelajaran_id}_{$kls->id}";
                        if (!$mapelKelasPairs->has($key)) {
                            $mapelKelasPairs->put($key, [
                                'mapel' => $k->mataPelajaran,
                                'kelas' => $kls,
                            ]);
                        }
                    }
                }
            }
        }

        // 3. Fallback jika masih kosong (misal superadmin atau penugasan belum diset)
        if ($mapelKelasPairs->isEmpty()) {
            $mapels = MataPelajaran::limit(5)->get();
            $kelases = Kelas::where('is_aktif', true)->limit(3)->get();
            foreach ($mapels as $m) {
                foreach ($kelases as $kls) {
                    $key = "{$m->id}_{$kls->id}";
                    $mapelKelasPairs->put($key, [
                        'mapel' => $m,
                        'kelas' => $kls,
                    ]);
                }
            }
        }

        return $mapelKelasPairs->values();
    }

    /**
     * Dashboard / Daftar Kelas dan Mapel yang diampu untuk Input Nilai
     */
    public function index(Request $request)
    {
        $penugasanMengajar = $this->getMapelDanKelasGuru();
        $tahunAjaran = PengaturanSekolah::getActiveTahunAjaran();
        $semester = PengaturanSekolah::getActiveSemester();

        return view('guru.nilai.index', compact('penugasanMengajar', 'tahunAjaran', 'semester'));
    }

    /**
     * Halaman Input Nilai Spesifik untuk suatu Mata Pelajaran dan Kelas
     */
    public function input(Request $request, ?MataPelajaran $mataPelajaran = null, ?Kelas $kelas = null)
    {
        if (!$mataPelajaran || !$mataPelajaran->exists) {
            $mapelId = $request->query('mapel_id') ?: $request->input('mata_pelajaran_id');
            $mataPelajaran = MataPelajaran::findOrFail($mapelId);
        }

        if (!$kelas || !$kelas->exists) {
            $kelasId = $request->query('kelas_id') ?: $request->input('kelas_id');
            $kelas = Kelas::findOrFail($kelasId);
        }

        $tahunAjaran = $request->input('tahun_ajaran', PengaturanSekolah::getActiveTahunAjaran());
        $semester = $request->input('semester', PengaturanSekolah::getActiveSemester());

        // Siswa di kelas tersebut
        $siswas = Siswa::where('kelas_id', $kelas->id)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        if ($siswas->isEmpty()) {
            $siswas = Siswa::where('kelas_id', $kelas->id)->orderBy('nama_lengkap')->get();
        }

        // Data nilai yang sudah tersimpan
        $nilaiMap = NilaiMataPelajaran::where('mata_pelajaran_id', $mataPelajaran->id)
            ->where('kelas_id', $kelas->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->get()
            ->keyBy('siswa_id');

        return view('guru.nilai.input', [
            'mapel'       => $mataPelajaran,
            'kelas'       => $kelas,
            'siswas'      => $siswas,
            'nilaiMap'    => $nilaiMap,
            'tahunAjaran' => $tahunAjaran,
            'semester'    => $semester,
        ]);
    }

    /**
     * Simpan nilai siswa secara bulk/spreadsheet
     */
    public function store(Request $request, ?MataPelajaran $mataPelajaran = null, ?Kelas $kelas = null)
    {
        if (!$mataPelajaran || !$mataPelajaran->exists) {
            $mapelId = $request->input('mata_pelajaran_id');
            $mataPelajaran = MataPelajaran::findOrFail($mapelId);
        }

        if (!$kelas || !$kelas->exists) {
            $kelasId = $request->input('kelas_id');
            $kelas = Kelas::findOrFail($kelasId);
        }

        $ta = $request->input('tahun_ajaran', PengaturanSekolah::getActiveTahunAjaran());
        $sem = $request->input('semester', PengaturanSekolah::getActiveSemester());

        $nilaiInputs = $request->input('nilai', []);

        DB::beginTransaction();
        try {
            foreach ($nilaiInputs as $siswaId => $row) {
                $akhir = isset($row['nilai_akhir']) && $row['nilai_akhir'] !== '' ? (float)$row['nilai_akhir'] : null;

                $predikat = null;
                if ($akhir !== null) {
                    $predikat = NilaiMataPelajaran::tentukanPredikat($akhir);
                }

                $catatan = $row['catatan'] ?? null;

                NilaiMataPelajaran::updateOrCreate(
                    [
                        'mata_pelajaran_id' => $mataPelajaran->id,
                        'kelas_id'          => $kelas->id,
                        'siswa_id'          => $siswaId,
                        'tahun_ajaran'      => $ta,
                        'semester'          => $sem,
                    ],
                    [
                        'guru_user_id' => auth()->id(),
                        'nilai_tugas'  => $akhir,
                        'nilai_uts'    => $akhir,
                        'nilai_uas'    => $akhir,
                        'nilai_akhir'  => $akhir,
                        'predikat'     => $predikat,
                        'catatan'      => $catatan,
                    ]
                );
            }

            DB::commit();
            return back()->with('success', "Data nilai mata pelajaran {$mataPelajaran->nama} untuk kelas {$kelas->nama_kelas} berhasil disimpan!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan nilai: ' . $e->getMessage());
        }
    }

    /**
     * FITUR UTAMA: TARIK / SINKRONKAN NILAI & KEHADIRAN ESKUL
     * Menyambungkan nilai & absen eskul ke mapel (Team Work Project dan Project Pancasila)
     */
    public function syncEskul(Request $request, ?MataPelajaran $mataPelajaran = null, ?Kelas $kelas = null)
    {
        if (!$mataPelajaran || !$mataPelajaran->exists) {
            $mapelId = $request->input('mata_pelajaran_id');
            $mataPelajaran = MataPelajaran::findOrFail($mapelId);
        }

        if (!$kelas || !$kelas->exists) {
            $kelasId = $request->input('kelas_id');
            $kelas = Kelas::findOrFail($kelasId);
        }

        $namaLower = strtolower($mataPelajaran->nama);
        $isEskulMapel = str_contains($namaLower, 'team work') 
            || str_contains($namaLower, 'project pancasila') 
            || str_contains($namaLower, 'work project')
            || str_starts_with(strtoupper($mataPelajaran->kode ?? ''), 'TWP');

        if (!$isEskulMapel) {
            return back()->with('error', 'Penarikan nilai dan absensi ekstrakurikuler hanya diperbolehkan untuk mata pelajaran Team Work Project dan Project Pancasila.');
        }

        $ta = $request->input('tahun_ajaran', PengaturanSekolah::getActiveTahunAjaran());
        $sem = $request->input('semester', PengaturanSekolah::getActiveSemester());

        $siswas = Siswa::where('kelas_id', $kelas->id)->get();
        $syncedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($siswas as $siswa) {
                $sId = $siswa->user_id ?: $siswa->id;
                // Cari eskul yang diikuti siswa ini
                $keanggotaans = AnggotaEkstrakurikuler::with('ekstrakurikuler')
                    ->where(function($q) use ($siswa, $sId) {
                        $q->where('siswa_id', $sId)->orWhere('siswa_id', $siswa->id);
                    })
                    ->where('tahun_ajaran', $ta)
                    ->where('semester', $sem)
                    ->get();

                if ($keanggotaans->isEmpty()) {
                    continue;
                }

                // Ambil rata-rata nilai dan kehadiran dari eskul yang diikutinya
                $totalNilaiAngka = 0;
                $countNilai = 0;
                $eskulDetails = [];

                foreach ($keanggotaans as $k) {
                    $stats = $k->statistik_kehadiran;
                    $persenHadir = $stats['persentase'];
                    $nilaiEskul = $k->nilai_angka ?? ($persenHadir > 0 ? min(95, max(70, round($persenHadir * 0.9 + 10))) : 75);

                    $totalNilaiAngka += $nilaiEskul;
                    $countNilai++;

                    $eskulDetails[] = "{$k->ekstrakurikuler->nama} (Kehadiran: {$persenHadir}%, Nilai: {$nilaiEskul}, Predikat: {$k->predikat_nilai})";
                }

                $avgNilaiEskul = $countNilai > 0 ? round($totalNilaiAngka / $countNilai, 1) : 75;
                $predikat = NilaiMataPelajaran::tentukanPredikat($avgNilaiEskul);
                $catatan = "Diintegrasikan dari hasil Ekstrakurikuler: " . implode('; ', $eskulDetails);

                // Update nilai di tabel nilai_mata_pelajarans
                NilaiMataPelajaran::updateOrCreate(
                    [
                        'mata_pelajaran_id' => $mataPelajaran->id,
                        'kelas_id'          => $kelas->id,
                        'siswa_id'          => $sId,
                        'tahun_ajaran'      => $ta,
                        'semester'          => $sem,
                    ],
                    [
                        'guru_user_id'  => auth()->id(),
                        'nilai_tugas'   => $avgNilaiEskul,
                        'nilai_uts'     => $avgNilaiEskul,
                        'nilai_uas'     => $avgNilaiEskul,
                        'nilai_akhir'   => $avgNilaiEskul,
                        'predikat'      => $predikat,
                        'catatan'       => $catatan,
                        'is_sync_eskul' => true,
                    ]
                );

                $syncedCount++;
            }

            DB::commit();
            return back()->with('success', "Berhasil menyinkronkan nilai dan absensi Ekstrakurikuler untuk {$syncedCount} siswa ke mata pelajaran {$mataPelajaran->nama}!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyinkronkan nilai eskul: ' . $e->getMessage());
        }
    }
}
