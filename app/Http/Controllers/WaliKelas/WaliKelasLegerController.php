<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\AnggotaEkstrakurikuler;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiMataPelajaran;
use App\Models\PengaturanSekolah;
use App\Models\PresensiHarian;
use App\Models\Siswa;
use Illuminate\Http\Request;

class WaliKelasLegerController extends Controller
{
    /**
     * Dapatkan kelas yang dibimbing oleh wali kelas yang sedang login
     */
    protected function getKelasBimbingan(Request $request): ?Kelas
    {
        $user = auth()->user();

        // Jika ada request kelas_id dan user adalah superadmin / admin / guru
        if ($request->has('kelas_id') && ($user->isSuperAdmin() || $user->role === 'admin' || $user->role === 'guru')) {
            $k = Kelas::find($request->input('kelas_id'));
            if ($k) return $k;
        }

        // 1. Cari kelas di mana wali_kelas_id = user id
        $kelas = Kelas::where('wali_kelas_id', $user->id)->first();

        // 2. Jika belum, cek via user->kelas_id
        if (!$kelas && !empty($user->kelas_id)) {
            $kelas = Kelas::find($user->kelas_id);
        }

        // 3. Jika belum terhubung via ID, cari via nama wali_kelas
        if (!$kelas) {
            $kelas = Kelas::where('wali_kelas', $user->name)->first();
        }

        // 4. Jika superadmin / admin, fallback ke kelas pertama
        if (!$kelas && ($user->isSuperAdmin() || $user->role === 'admin')) {
            $kelas = Kelas::where('is_aktif', true)->first() ?: Kelas::first();
        }

        return $kelas;
    }

    /**
     * Helper untuk menghitung matriks Leger Nilai & Ranking Kelas
     */
    protected function buildLegerData(Kelas $kelas, string $tahunAjaran, string $semester): array
    {
        $siswas = Siswa::where('kelas_id', $kelas->id)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        if ($siswas->isEmpty()) {
            $siswas = Siswa::where('kelas_id', $kelas->id)->orderBy('nama_lengkap')->get();
        }

        // Ambil semua mapel aktif
        $mapels = MataPelajaran::where('is_aktif', true)->orderBy('nama')->get();
        if ($mapels->isEmpty()) {
            $mapels = MataPelajaran::orderBy('nama')->get();
        }

        // Ambil nilai yang sudah tersimpan untuk kelas ini
        $nilaiRecords = NilaiMataPelajaran::where('kelas_id', $kelas->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->get();

        $allSiswaIds = $siswas->pluck('id')->merge($siswas->pluck('user_id'))->filter()->unique()->values();

        // Ambil data keikutsertaan eskul siswa di kelas ini
        $eskulRecords = AnggotaEkstrakurikuler::with('ekstrakurikuler')
            ->whereIn('siswa_id', $allSiswaIds)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->get();

        // Ambil data absensi harian sekolah jika ada
        $presensiSekolah = collect();
        if (class_exists(PresensiHarian::class)) {
            $presensiSekolah = PresensiHarian::whereIn('siswa_id', $allSiswaIds)
                ->get();
        }

        $tempList = [];

        foreach ($siswas as $siswa) {
            $sKeys = array_filter([$siswa->user_id, $siswa->id]);
            $nilaiMap = [];
            $totalNilai = 0;
            $countMapelTerisi = 0;

            foreach ($mapels as $m) {
                $rec = $nilaiRecords->first(fn($n) => in_array($n->siswa_id, $sKeys) && $n->mata_pelajaran_id == $m->id);
                $akhir = $rec ? $rec->nilai_akhir : null;
                $nilaiMap[$m->id] = $akhir;

                if ($akhir !== null) {
                    $totalNilai += $akhir;
                    $countMapelTerisi++;
                }
            }

            $rataRata = $countMapelTerisi > 0 ? round($totalNilai / $countMapelTerisi, 1) : 0;

            // Rincian eskul
            $eskulList = [];
            $myEskuls = $eskulRecords->filter(fn($k) => in_array($k->siswa_id, $sKeys));
            foreach ($myEskuls as $k) {
                $eskulList[] = [
                    'nama'     => $k->ekstrakurikuler->nama ?? '-',
                    'nilai'    => $k->nilai_angka,
                    'predikat' => $k->nilai_huruf ?: $k->predikat_nilai,
                ];
            }

            // Rincian absensi sekolah
            $abs = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];
            if ($presensiSekolah->has($siswa->id)) {
                $userAbs = $presensiSekolah[$siswa->id];
                $abs['H'] = $userAbs->where('status', 'H')->count() + $userAbs->where('status', 'hadir')->count();
                $abs['I'] = $userAbs->where('status', 'I')->count() + $userAbs->where('status', 'izin')->count();
                $abs['S'] = $userAbs->where('status', 'S')->count() + $userAbs->where('status', 'sakit')->count();
                $abs['A'] = $userAbs->where('status', 'A')->count() + $userAbs->where('status', 'alpa')->count();
            }

            $tempList[] = [
                'siswa'        => $siswa,
                'total_nilai'  => $totalNilai,
                'rata_rata'    => $rataRata,
                'nilai_mapels' => $nilaiMap,
                'eskul_list'   => $eskulList,
                'absensi'      => $abs,
            ];
        }

        // Urutkan berdasarkan total nilai tertinggi untuk perankingan
        usort($tempList, fn($a, $b) => $b['total_nilai'] <=> $a['total_nilai']);

        $rankedList = [];
        foreach ($tempList as $idx => $row) {
            $row['rank'] = $idx + 1;
            $rankedList[] = $row;
        }

        return [
            'mapels'    => $mapels,
            'legerData' => $rankedList,
        ];
    }

    /**
     * LEGER NILAI DARI MASING-MASING MAPEL UNTUK WALI KELAS
     */
    public function leger(Request $request)
    {
        $kelas = $this->getKelasBimbingan($request);

        if (!$kelas) {
            return redirect()->route('walikelas.dashboard')
                ->with('error', 'Anda belum ditugaskan sebagai wali kelas pada rombel manapun.');
        }

        $tahunAjaran = $request->input('tahun_ajaran', PengaturanSekolah::getActiveTahunAjaran());
        $semester = $request->input('semester', PengaturanSekolah::getActiveSemester());

        $built = $this->buildLegerData($kelas, $tahunAjaran, $semester);
        $kelasList = (auth()->user()->isSuperAdmin() || auth()->user()->role === 'admin' || auth()->user()->hasAdminRole('akademik'))
            ? Kelas::orderBy('nama_kelas')->get()
            : collect();

        return view('walikelas.leger.index', [
            'kelas'       => $kelas,
            'kelasList'   => $kelasList,
            'mapels'      => $built['mapels'],
            'legerData'   => $built['legerData'],
            'tahunAjaran' => $tahunAjaran,
            'semester'    => $semester,
        ]);
    }

    /**
     * Cetak Leger Nilai Kelas Bimbingan (Landscape / Print Ready)
     */
    public function printLeger(Request $request)
    {
        $kelas = $this->getKelasBimbingan($request);

        if (!$kelas) {
            abort(404, 'Kelas bimbingan tidak ditemukan.');
        }

        $tahunAjaran = $request->input('tahun_ajaran', PengaturanSekolah::getActiveTahunAjaran());
        $semester = $request->input('semester', PengaturanSekolah::getActiveSemester());

        $built = $this->buildLegerData($kelas, $tahunAjaran, $semester);

        return view('walikelas.leger.print', [
            'kelas'       => $kelas,
            'mapels'      => $built['mapels'],
            'legerData'   => $built['legerData'],
            'tahunAjaran' => $tahunAjaran,
            'semester'    => $semester,
        ]);
    }

    /**
     * REKAP EKSTRAKURIKULER SISWA KELAS BIMBINGAN
     */
    public function eskul(Request $request)
    {
        $kelas = $this->getKelasBimbingan($request);

        if (!$kelas) {
            return redirect()->route('walikelas.dashboard')
                ->with('error', 'Anda belum ditugaskan sebagai wali kelas pada rombel manapun.');
        }

        $tahunAjaran = $request->input('tahun_ajaran', PengaturanSekolah::getActiveTahunAjaran());
        $semester = $request->input('semester', PengaturanSekolah::getActiveSemester());

        $siswas = Siswa::where('kelas_id', $kelas->id)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        if ($siswas->isEmpty()) {
            $siswas = Siswa::where('kelas_id', $kelas->id)->orderBy('nama_lengkap')->get();
        }

        $keanggotaans = AnggotaEkstrakurikuler::with('ekstrakurikuler')
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->get()
            ->groupBy('siswa_id');

        $rekapData = $siswas->map(function ($s) use ($keanggotaans) {
            $listEskul = $keanggotaans->get($s->id, collect());
            $eskulDetails = [];

            foreach ($listEskul as $k) {
                $stats = $k->statistik_kehadiran;
                $eskulDetails[] = [
                    'eskul_nama'           => $k->ekstrakurikuler->nama ?? '-',
                    'jabatan'              => $k->jabatan,
                    'persentase_kehadiran' => $stats['persentase'],
                    'hadir'                => $stats['hadir'],
                    'total_pertemuan'      => $stats['total'],
                    'nilai_angka'          => $k->nilai_angka,
                    'nilai_huruf'          => $k->nilai_huruf ?: $k->predikat_nilai,
                    'catatan_nilai'        => $k->catatan_nilai,
                ];
            }

            return [
                'siswa'         => $s,
                'jumlah_eskul'  => $listEskul->count(),
                'eskul_details' => $eskulDetails,
            ];
        });

        $kelasList = (auth()->user()->isSuperAdmin() || auth()->user()->role === 'admin' || auth()->user()->hasAdminRole('akademik'))
            ? Kelas::orderBy('nama_kelas')->get()
            : collect();

        return view('walikelas.ekstrakurikuler.index', [
            'kelas'       => $kelas,
            'kelasList'   => $kelasList,
            'rekapData'   => $rekapData,
            'tahunAjaran' => $tahunAjaran,
            'semester'    => $semester,
        ]);
    }
}
