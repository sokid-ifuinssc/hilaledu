<?php

namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\AnggotaEkstrakurikuler;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\MataPelajaran;
use App\Models\NilaiMataPelajaran;
use App\Models\PengaturanSekolah;
use App\Models\PresensiHarian;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;

class WaliKelasLegerController extends Controller
{
    /**
     * Dapatkan kelas yang dibimbing oleh wali kelas / kaprog / kurikulum yang sedang login
     */
    public function getKelasBimbingan(Request $request): ?Kelas
    {
        $user = auth()->user();

        // Jika ada request kelas_id
        if ($request->filled('kelas_id')) {
            $k = Kelas::with(['jurusan', 'waliKelasGuru'])->find($request->input('kelas_id'));
            if ($k) {
                return $k;
            }
        }

        // 1. Cari kelas di mana wali_kelas_id = user id
        $kelas = Kelas::with(['jurusan', 'waliKelasGuru'])->where('wali_kelas_id', $user->id)->first();

        // 2. Jika belum, cek via user->kelas_id
        if (!$kelas && !empty($user->kelas_id)) {
            $kelas = Kelas::with(['jurusan', 'waliKelasGuru'])->find($user->kelas_id);
        }

        // 3. Jika belum terhubung via ID, cari via nama wali_kelas
        if (!$kelas) {
            $kelas = Kelas::with(['jurusan', 'waliKelasGuru'])->where('wali_kelas', $user->name)->first();
        }

        // 4. Jika Kaprog, cari kelas pertama di jurusannya
        if (!$kelas && $user->isKaprog()) {
            $jurusanIds = Jurusan::where('kaprog_id', $user->id)->pluck('id');
            if ($jurusanIds->isNotEmpty()) {
                $kelas = Kelas::with(['jurusan', 'waliKelasGuru'])
                    ->whereIn('jurusan_id', $jurusanIds)
                    ->where('is_aktif', true)
                    ->first();
            }
        }

        // 5. Fallback untuk superadmin / admin / kurikulum / kaprog / guru
        if (!$kelas) {
            $kelas = Kelas::with(['jurusan', 'waliKelasGuru'])->where('is_aktif', true)->orderBy('nama_kelas')->first() ?: Kelas::first();
        }

        return $kelas;
    }

    /**
     * Helper untuk menghitung matriks Leger Nilai & Ranking Kelas (Per Semester Berjalan)
     */
    public function buildLegerData(Kelas $kelas, string $tahunAjaran, string $semester): array
    {
        $siswas = Siswa::where('kelas_id', $kelas->id)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        if ($siswas->isEmpty()) {
            $siswas = Siswa::where('kelas_id', $kelas->id)->orderBy('nama_lengkap')->get();
        }

        if ($siswas->isEmpty()) {
            $siswas = User::where('role', 'siswa')
                ->where('kelas_id', $kelas->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        // Normalisasi nama dan nisn siswa
        foreach ($siswas as $s) {
            $s->nama = $s->nama_lengkap ?? $s->name ?? $s->nama;
            $s->nisn = $s->nisn ?: ($s->nis ?: '-');
        }

        // Ambil mapel yang relevan
        $mapelIds = NilaiMataPelajaran::where('kelas_id', $kelas->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->distinct()
            ->pluck('mata_pelajaran_id');

        if ($mapelIds->isEmpty()) {
            $mapelIds = \App\Models\JadwalPelajaran::where('kelas_id', $kelas->id)
                ->distinct()
                ->pluck('mata_pelajaran_id');
        }

        if ($mapelIds->isEmpty()) {
            $mapelIds = MataPelajaran::where('is_aktif', true)->pluck('id');
        }

        $mapels = MataPelajaran::whereIn('id', $mapelIds)->orderBy('kelompok')->orderBy('nama')->get();
        if ($mapels->isEmpty()) {
            $mapels = MataPelajaran::where('is_aktif', true)->orderBy('nama')->get();
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
            $presensiSekolah = PresensiHarian::whereIn('siswa_id', $allSiswaIds)->get();
        }

        $tempList = [];

        foreach ($siswas as $siswa) {
            $sKeys = array_filter([$siswa->user_id ?? null, $siswa->id]);
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
     * Helper untuk menghitung matriks Leger 6 Semester (Kumulatif: X Ganjil, X Genap, XI Ganjil, XI Genap, XII Ganjil, XII Genap)
     * Format: Nomor | Nama Siswa | Nama Mapel (X Ganjil, X Genap, XI Ganjil, XI Genap, XII Ganjil, XII Genap) | ... seluruh mapel yang ada
     */
    public function buildCumulativeLegerData(Kelas $kelas): array
    {
        $siswas = Siswa::where('kelas_id', $kelas->id)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        if ($siswas->isEmpty()) {
            $siswas = Siswa::where('kelas_id', $kelas->id)->orderBy('nama_lengkap')->get();
        }

        if ($siswas->isEmpty()) {
            $siswas = User::where('role', 'siswa')
                ->where('kelas_id', $kelas->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        foreach ($siswas as $s) {
            $s->nama = $s->nama_lengkap ?? $s->name ?? $s->nama;
            $s->nisn = $s->nisn ?: ($s->nis ?: '-');
        }

        // Ambil semua mapel yang relevan untuk jurusan kelas ini
        $jurusanSingkatan = $kelas->jurusan?->singkatan;
        $kurMapels = Kurikulum::with('mataPelajaran')
            ->where('is_aktif', true)
            ->when($jurusanSingkatan, function ($q) use ($jurusanSingkatan) {
                $q->where(function ($sub) use ($jurusanSingkatan) {
                    $sub->where('jurusan', $jurusanSingkatan)
                        ->orWhereNull('jurusan')
                        ->orWhere('jurusan', '')
                        ->orWhere('jurusan', 'Semua');
                });
            })
            ->get()
            ->pluck('mataPelajaran')
            ->filter()
            ->unique('nama')
            ->values();

        if ($kurMapels->isEmpty()) {
            $kurMapels = MataPelajaran::where('is_aktif', true)->get()->unique('nama')->values();
        }

        // Klasifikasi mapel ke kategori: Umum, Project (TWP/P5), Dasar-dasar Keahlian, Konsentrasi Keahlian
        $classifiedMapels = $kurMapels->map(function ($m) {
            $namaLower = strtolower($m->nama);
            $kodeUpper = strtoupper($m->kode ?? '');

            // 1. Team Work Project & Project Pancasila (Eskul terintegrasi)
            if (str_contains($namaLower, 'team work') || str_contains($namaLower, 'project pancasila') || str_contains($namaLower, 'work project') || str_starts_with($kodeUpper, 'TWP')) {
                $katGroup = 'project';
                $katLabel = 'Project Pancasila (Eskul)';
                $katPrint = 'B. PROJECT PANCASILA & EKSTRAKURIKULER';
                $order = 2;
            }
            // 2. Dasar-dasar Keahlian (Program Keahlian di kelas X)
            elseif (
                str_contains($namaLower, 'dasar') ||
                str_contains($namaLower, 'pemrograman dasar') ||
                str_contains($namaLower, 'sistem komputer') ||
                str_contains($namaLower, 'komputer jaringan dasar') ||
                str_contains($namaLower, 'manajemen file') ||
                str_contains($namaLower, 'digital mindset') ||
                str_contains($namaLower, 'etika profesi') ||
                str_contains($namaLower, 'perbankan dasar') ||
                str_contains($namaLower, 'administrasi umum') ||
                str_contains($namaLower, 'akuntansi dasar') ||
                str_contains($namaLower, 'ekonomi bisnis') ||
                str_contains($namaLower, 'teknik kerja bengkel las') ||
                str_contains($namaLower, 'teknik dasar pemeliharaan') ||
                str_contains($namaLower, 'k3lh') ||
                str_contains($namaLower, 'gambar teknik')
            ) {
                $katGroup = 'kejuruan_dasar';
                $katLabel = 'Dasar-dasar Keahlian';
                // Digabung saat cetak
                $katPrint = 'C. MATA PELAJARAN KEAHLIAN / KEJURUAN';
                $order = 3;
            }
            // 3. Umum
            elseif (
                str_contains($namaLower, 'agama') ||
                str_contains($namaLower, 'pancasila') ||
                str_contains($namaLower, 'bahasa indonesia') ||
                str_contains($namaLower, 'matematika') ||
                str_contains($namaLower, 'bahasa inggris') ||
                str_contains($namaLower, 'jasmani') ||
                str_contains($namaLower, 'pjok') ||
                str_contains($namaLower, 'sejarah') ||
                str_contains($namaLower, 'seni budaya') ||
                str_contains($namaLower, 'akhlak') ||
                str_contains($namaLower, 'fiqih') ||
                str_contains($namaLower, 'hadits') ||
                str_contains($namaLower, 'informatika') ||
                str_contains($namaLower, 'ilmu pengetahuan alam dan sosial') ||
                str_contains($namaLower, 'ipas') ||
                str_contains($namaLower, 'bimbingan konseling') ||
                str_contains($namaLower, 'bk')
            ) {
                $katGroup = 'umum';
                $katLabel = 'Mata Pelajaran Umum';
                $katPrint = 'A. KELOMPOK MATA PELAJARAN UMUM';
                $order = 1;
            }
            // 4. Konsentrasi Keahlian / Kejuruan lainnya
            else {
                $katGroup = 'kejuruan_konsentrasi';
                $katLabel = 'Konsentrasi Keahlian';
                // Digabung saat cetak
                $katPrint = 'C. MATA PELAJARAN KEAHLIAN / KEJURUAN';
                $order = 4;
            }

            return (object) [
                'id'          => $m->id,
                'nama'        => $m->nama,
                'kode'        => $m->kode,
                'kat_group'   => $katGroup,
                'kat_label'   => $katLabel,
                'kat_print'   => $katPrint,
                'order'       => $order,
            ];
        })->sortBy('order')->values();

        // Ambil seluruh nilai siswa yang ada di database
        $allSiswaIds = $siswas->pluck('id')->merge($siswas->pluck('user_id'))->filter()->unique()->values();
        $rawNilais = NilaiMataPelajaran::with(['mataPelajaran', 'kelas'])
            ->whereIn('siswa_id', $allSiswaIds)
            ->get();

        $rows = [];
        foreach ($siswas as $siswa) {
            $sKeys = array_filter([$siswa->user_id ?? null, $siswa->id]);
            $studentNilais = $rawNilais->filter(fn($n) => in_array($n->siswa_id, $sKeys));

            $matrix = [];
            $totalKumulatif = 0;
            $countKumulatif = 0;

            foreach ($classifiedMapels as $m) {
                $semesters = [
                    'X_ganjil'   => null,
                    'X_genap'    => null,
                    'XI_ganjil'  => null,
                    'XI_genap'   => null,
                    'XII_ganjil' => null,
                    'XII_genap'  => null,
                ];

                // Cari semua nilai untuk mapel dengan nama yang sama atau TWP
                $matchedRecs = $studentNilais->filter(function($n) use ($m) {
                    if (!$n->mataPelajaran) return false;
                    if ($n->mataPelajaran->nama === $m->nama) return true;
                    if ($m->kat_group === 'project') {
                        $nLower = strtolower($n->mataPelajaran->nama);
                        return str_contains($nLower, 'team work') || str_contains($nLower, 'project pancasila') || str_contains($nLower, 'work project');
                    }
                    return false;
                });

                foreach ($matchedRecs as $rec) {
                    $akhir = $rec->nilai_akhir;
                    if ($akhir === null) continue;

                    // Tentukan tingkat
                    $tingkat = $rec->kelas?->tingkat;
                    if (empty($tingkat)) {
                        $kode = $rec->mataPelajaran?->kode ?? '';
                        if (str_ends_with($kode, '-X')) $tingkat = 'X';
                        elseif (str_ends_with($kode, '-XI')) $tingkat = 'XI';
                        elseif (str_ends_with($kode, '-XII')) $tingkat = 'XII';
                        else $tingkat = $kelas->tingkat ?: 'X';
                    }

                    $sem = strtolower($rec->semester ?? 'ganjil');
                    $slotKey = "{$tingkat}_{$sem}";

                    if (array_key_exists($slotKey, $semesters)) {
                        $semesters[$slotKey] = $akhir;
                        $totalKumulatif += $akhir;
                        $countKumulatif++;
                    }
                }

                $matrix[$m->nama] = $semesters;
            }

            $rataKumulatif = $countKumulatif > 0 ? round($totalKumulatif / $countKumulatif, 1) : 0;

            $rows[] = [
                'siswa'        => $siswa,
                'grades'       => $matrix,
                'total_nilai'  => $totalKumulatif,
                'rata_rata'    => $rataKumulatif,
                'total_terisi' => $countKumulatif,
            ];
        }

        // Urutkan siswa berdasarkan total nilai tertinggi
        usort($rows, fn($a, $b) => $b['total_nilai'] <=> $a['total_nilai']);

        return [
            'mapels'         => $classifiedMapels,
            'cumulativeRows' => $rows,
            'semesterCols'   => [
                'X_ganjil'   => 'X Ganjil',
                'X_genap'    => 'X Genap',
                'XI_ganjil'  => 'XI Ganjil',
                'XI_genap'   => 'XI Genap',
                'XII_ganjil' => 'XII Ganjil',
                'XII_genap'  => 'XII Genap',
            ],
        ];
    }

    /**
     * LEGER NILAI DARI MASING-MASING MAPEL UNTUK WALI KELAS, KAPROG, DAN KURIKULUM
     */
    public function leger(Request $request)
    {
        $kelas = $this->getKelasBimbingan($request);

        if (!$kelas) {
            return redirect()->route('walikelas.dashboard')
                ->with('error', 'Data kelas tidak ditemukan.');
        }

        $tahunAjaran = $request->input('tahun_ajaran', PengaturanSekolah::getActiveTahunAjaran());
        $semester = $request->input('semester', PengaturanSekolah::getActiveSemester());

        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isWakaKurikulum() || $user->hasAdminRole('akademik')) {
            $kelasList = Kelas::with('jurusan')->where('is_aktif', true)->orderBy('nama_kelas')->get();
        } elseif ($user->isKaprog()) {
            $jurusanIds = Jurusan::where('kaprog_id', $user->id)->pluck('id');
            $kelasList = Kelas::with('jurusan')->where('is_aktif', true)
                ->when($jurusanIds->isNotEmpty(), fn($q) => $q->whereIn('jurusan_id', $jurusanIds))
                ->orderBy('nama_kelas')->get();
            if ($kelasList->isEmpty()) {
                $kelasList = Kelas::with('jurusan')->where('is_aktif', true)->orderBy('nama_kelas')->get();
            }
        } else {
            // Wali Kelas
            $kelasList = Kelas::with('jurusan')->where('wali_kelas_id', $user->id)->orWhere('id', $kelas->id)->get();
        }

        $built = $this->buildLegerData($kelas, $tahunAjaran, $semester);
        $cumulative = $this->buildCumulativeLegerData($kelas);

        return view('walikelas.leger.index', [
            'kelas'            => $kelas,
            'kelasList'        => $kelasList,
            'mapels'           => $built['mapels'],
            'legerData'        => $built['legerData'],
            'cumulativeMapels' => $cumulative['mapels'],
            'cumulativeRows'   => $cumulative['cumulativeRows'],
            'semesterCols'     => $cumulative['semesterCols'],
            'tahunAjaran'      => $tahunAjaran,
            'semester'         => $semester,
        ]);
    }

    /**
     * Cetak Leger Nilai (Landscape / Print Ready)
     * Format: No | Nama Siswa | [Nama Mapel (X Ganjil, X Genap, XI Ganjil, XI Genap, XII Ganjil, XII Genap)] | ...
     * Kategori Dasar-dasar Keahlian dan Keahlian digabung
     */
    public function printLeger(Request $request)
    {
        $kelas = $this->getKelasBimbingan($request);

        if (!$kelas) {
            abort(404, 'Kelas tidak ditemukan.');
        }

        $tahunAjaran = $request->input('tahun_ajaran', PengaturanSekolah::getActiveTahunAjaran());
        $semester = $request->input('semester', PengaturanSekolah::getActiveSemester());
        $mode = $request->input('mode', 'kumulatif');

        $built = $this->buildLegerData($kelas, $tahunAjaran, $semester);
        $cumulative = $this->buildCumulativeLegerData($kelas);

        return view('walikelas.leger.print', [
            'kelas'            => $kelas,
            'mapels'           => $built['mapels'],
            'legerData'        => $built['legerData'],
            'cumulativeMapels' => $cumulative['mapels'],
            'cumulativeRows'   => $cumulative['cumulativeRows'],
            'semesterCols'     => $cumulative['semesterCols'],
            'tahunAjaran'      => $tahunAjaran,
            'semester'         => $semester,
            'mode'             => $mode,
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

        if ($siswas->isEmpty()) {
            $siswas = User::where('role', 'siswa')
                ->where('kelas_id', $kelas->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        foreach ($siswas as $s) {
            $s->nama = $s->nama_lengkap ?? $s->name ?? $s->nama;
            $s->nisn = $s->nisn ?: ($s->nis ?: '-');
        }

        $allSiswaIds = $siswas->pluck('id')->merge($siswas->pluck('user_id'))->filter()->unique()->values();

        $keanggotaans = AnggotaEkstrakurikuler::with('ekstrakurikuler')
            ->whereIn('siswa_id', $allSiswaIds)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->get()
            ->groupBy('siswa_id');

        $rekapData = $siswas->map(function ($s) use ($keanggotaans) {
            $sKeys = array_filter([$s->user_id ?? null, $s->id]);
            $listEskul = collect();
            foreach ($sKeys as $sk) {
                if ($keanggotaans->has($sk)) {
                    $listEskul = $listEskul->merge($keanggotaans->get($sk));
                }
            }
            $listEskul = $listEskul->unique('id');

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

        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isWakaKurikulum() || $user->hasAdminRole('akademik')) {
            $kelasList = Kelas::with('jurusan')->where('is_aktif', true)->orderBy('nama_kelas')->get();
        } elseif ($user->isKaprog()) {
            $jurusanIds = Jurusan::where('kaprog_id', $user->id)->pluck('id');
            $kelasList = Kelas::with('jurusan')->where('is_aktif', true)
                ->when($jurusanIds->isNotEmpty(), fn($q) => $q->whereIn('jurusan_id', $jurusanIds))
                ->orderBy('nama_kelas')->get();
        } else {
            $kelasList = Kelas::with('jurusan')->where('wali_kelas_id', $user->id)->orWhere('id', $kelas->id)->get();
        }

        return view('walikelas.ekstrakurikuler.index', [
            'kelas'       => $kelas,
            'kelasList'   => $kelasList,
            'rekapData'   => $rekapData,
            'tahunAjaran' => $tahunAjaran,
            'semester'    => $semester,
        ]);
    }
}
