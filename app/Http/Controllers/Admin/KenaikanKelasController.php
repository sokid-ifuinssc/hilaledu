<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PengaturanSekolah;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\TracerAlumni;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KenaikanKelasController extends Controller
{
    /**
     * Tampilan Utama Manajemen Kenaikan Kelas, Kelulusan Alumni & Tahun Ajaran Berkelanjutan
     */
    public function index(Request $request)
    {
        $tahunAjaranAktif = TahunAjaran::aktif() ?? TahunAjaran::latest('id')->first();

        // Kalkulasi Tahun Ajaran Baru Berikutnya Secara Berkelanjutan
        if ($tahunAjaranAktif) {
            $nextTahunMulai   = $tahunAjaranAktif->tahun_mulai ? ($tahunAjaranAktif->tahun_mulai + 1) : (int)date('Y');
            $nextTahunSelesai = $tahunAjaranAktif->tahun_selesai ? ($tahunAjaranAktif->tahun_selesai + 1) : ($nextTahunMulai + 1);
            $nextTahunNama    = "{$nextTahunMulai}/{$nextTahunSelesai}";
        } else {
            $currentYear      = (int)date('Y');
            $nextTahunMulai   = $currentYear;
            $nextTahunSelesai = $currentYear + 1;
            $nextTahunNama    = "{$nextTahunMulai}/{$nextTahunSelesai}";
        }

        // Pengelompokan Kelas per Tingkat
        $kelasX   = Kelas::with('jurusan')->where('tingkat', 'X')->orderBy('nama')->get();
        $kelasXI  = Kelas::with('jurusan')->where('tingkat', 'XI')->orderBy('nama')->get();
        $kelasXII = Kelas::with('jurusan')->where('tingkat', 'XII')->orderBy('nama')->get();
        $semuaKelas = Kelas::with('jurusan')->orderBy('tingkat')->orderBy('nama')->get();

        // 1. Data untuk Tab Kenaikan Kelas (Tingkat X & XI)
        $selectedKelasAsalId = $request->get('kelas_asal_id');
        $kelasAsal           = null;
        $rekomendasiKelas    = null;
        $siswaKelasAsal      = collect();
        $kelasTujuanOptions  = collect();

        if ($selectedKelasAsalId) {
            $kelasAsal = Kelas::with('jurusan')->find($selectedKelasAsalId);
            if ($kelasAsal) {
                // Ambil daftar siswa aktif di kelas asal
                $siswaKelasAsal = User::where('role', 'siswa')
                    ->where('kelas_id', $kelasAsal->id)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();

                // Target opsi kelas yang diperbolehkan naik
                if ($kelasAsal->tingkat === 'X') {
                    $kelasTujuanOptions = $kelasXI;
                    // Rekomendasikan kelas XI dengan jurusan yang sama
                    $rekomendasiKelas = $kelasXI->firstWhere('jurusan_id', $kelasAsal->jurusan_id);
                } elseif ($kelasAsal->tingkat === 'XI') {
                    $kelasTujuanOptions = $kelasXII;
                    // Rekomendasikan kelas XII dengan jurusan yang sama
                    $rekomendasiKelas = $kelasXII->firstWhere('jurusan_id', $kelasAsal->jurusan_id);
                }
            }
        }

        // 2. Data untuk Tab Kelulusan Kelas XII
        $selectedKelasXiiId = $request->get('kelas_xii_id', 'all');
        $queryXii = User::where('role', 'siswa')
            ->where('is_active', true);

        if ($selectedKelasXiiId && $selectedKelasXiiId !== 'all') {
            $queryXii->where('kelas_id', $selectedKelasXiiId);
        } else {
            $queryXii->whereIn('kelas_id', $kelasXII->pluck('id'));
        }

        $siswaKelasXII = $queryXii->with('kelas.jurusan')->orderBy('name')->get();
        $defaultTahunLulus = $tahunAjaranAktif?->tahun_selesai ?? date('Y');

        // 3. Data Ringkasan Alumni Terdaftar
        $alumniTahunList = TracerAlumni::select('tahun_lulus')
            ->distinct()
            ->orderBy('tahun_lulus', 'desc')
            ->pluck('tahun_lulus');

        $selectedTahunAlumni = $request->get('filter_tahun_alumni');
        $queryAlumni = TracerAlumni::with('user');
        if ($selectedTahunAlumni) {
            $queryAlumni->where('tahun_lulus', $selectedTahunAlumni);
        }
        $recentAlumni = $queryAlumni->orderBy('tahun_lulus', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'alumni_page')
            ->withQueryString();

        // 4. Statistik Keseluruhan
        $countSiswaX    = User::where('role', 'siswa')->where('is_active', true)->whereIn('kelas_id', $kelasX->pluck('id'))->count();
        $countSiswaXI   = User::where('role', 'siswa')->where('is_active', true)->whereIn('kelas_id', $kelasXI->pluck('id'))->count();
        $countSiswaXII  = User::where('role', 'siswa')->where('is_active', true)->whereIn('kelas_id', $kelasXII->pluck('id'))->count();
        $countAlumni    = TracerAlumni::count();
        $countTotalAktif= User::where('role', 'siswa')->where('is_active', true)->whereNotNull('kelas_id')->count();

        // Semester aktif
        $semesterAktif  = PengaturanSekolah::getActiveSemester();

        return view('admin.kenaikan_kelas.index', compact(
            'tahunAjaranAktif',
            'nextTahunNama',
            'nextTahunMulai',
            'nextTahunSelesai',
            'kelasX',
            'kelasXI',
            'kelasXII',
            'semuaKelas',
            'selectedKelasAsalId',
            'kelasAsal',
            'rekomendasiKelas',
            'siswaKelasAsal',
            'kelasTujuanOptions',
            'selectedKelasXiiId',
            'siswaKelasXII',
            'defaultTahunLulus',
            'alumniTahunList',
            'selectedTahunAlumni',
            'recentAlumni',
            'countSiswaX',
            'countSiswaXI',
            'countSiswaXII',
            'countAlumni',
            'countTotalAktif',
            'semesterAktif'
        ));
    }

    /**
     * Memproses Kenaikan Kelas Siswa (Promosi ke Tingkat Lebih Tinggi / Tinggal Kelas)
     */
    public function prosesNaikKelas(Request $request)
    {
        $request->validate([
            'kelas_asal_id'   => 'required|exists:kelas,id',
            'kelas_tujuan_id' => 'required|exists:kelas,id|different:kelas_asal_id',
            'siswa'           => 'required|array|min:1',
            'siswa.*'         => 'in:naik,tinggal',
        ], [
            'kelas_asal_id.required'   => 'Pilih kelas asal terlebih dahulu.',
            'kelas_tujuan_id.required' => 'Pilih kelas tujuan kenaikan kelas.',
            'kelas_tujuan_id.different'=> 'Kelas tujuan harus berbeda dengan kelas asal.',
            'siswa.required'           => 'Tidak ada siswa yang dipilih untuk diproses.',
        ]);

        $kelasAsal   = Kelas::findOrFail($request->kelas_asal_id);
        $kelasTujuan = Kelas::findOrFail($request->kelas_tujuan_id);

        $countNaik    = 0;
        $countTinggal = 0;

        DB::beginTransaction();
        try {
            foreach ($request->siswa as $userId => $status) {
                $user = User::where('id', $userId)->where('role', 'siswa')->first();
                if (!$user) {
                    continue;
                }

                if ($status === 'naik') {
                    // Update User
                    $user->update([
                        'kelas_id'  => $kelasTujuan->id,
                        'is_active' => true,
                    ]);

                    // Sinkronisasi Tabel Siswa
                    Siswa::where('user_id', $user->id)->update([
                        'kelas_id' => $kelasTujuan->id,
                        'status'   => 'aktif',
                    ]);

                    $countNaik++;
                } else {
                    // Tinggal kelas: tetap di kelas asal
                    $user->update([
                        'kelas_id'  => $kelasAsal->id,
                        'is_active' => true,
                    ]);

                    Siswa::where('user_id', $user->id)->update([
                        'kelas_id' => $kelasAsal->id,
                        'status'   => 'aktif',
                    ]);

                    $countTinggal++;
                }
            }

            DB::commit();

            return redirect()->route('superadmin.kenaikan-kelas.index', [
                'kelas_asal_id' => $kelasTujuan->id,
                'tab'           => 'promosi',
            ])->with('success', "Proses kenaikan kelas berhasil! {$countNaik} siswa berhasil naik ke kelas {$kelasTujuan->nama_lengkap}, dan {$countTinggal} siswa dinyatakan tinggal kelas.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses kenaikan kelas: ' . $e->getMessage());
        }
    }

    /**
     * Memproses Kelulusan Siswa Kelas XII & Otomatis Masuk ke Database Alumni (Tracer Study)
     */
    public function prosesKelulusan(Request $request)
    {
        $request->validate([
            'siswa_ids'     => 'required|array|min:1',
            'siswa_ids.*'   => 'exists:users,id',
            'tahun_lulus'   => 'required|numeric|digits:4',
            'status_tracer' => 'nullable|string|max:100',
        ], [
            'siswa_ids.required'   => 'Pilih minimal satu siswa kelas XII yang akan diluluskan.',
            'tahun_lulus.required' => 'Tahun kelulusan wajib diisi.',
            'tahun_lulus.digits'   => 'Tahun kelulusan harus 4 digit angka (contoh: 2027).',
        ]);

        $tahunLulus   = (int)$request->tahun_lulus;
        $statusTracer = $request->status_tracer ?: 'Belum Diisi';
        $countLulus   = 0;

        DB::beginTransaction();
        try {
            foreach ($request->siswa_ids as $userId) {
                $user = User::where('id', $userId)->where('role', 'siswa')->first();
                if (!$user) {
                    continue;
                }

                // 1. Lepas kelas_id dari user, biarkan akun tetap aktif agar bisa login Tracer Study
                $user->update([
                    'kelas_id'  => null,
                    'is_active' => true,
                ]);

                // 2. Update status profil siswa menjadi 'lulus' dan lepas kelas
                Siswa::where('user_id', $user->id)->update([
                    'kelas_id' => null,
                    'status'   => 'lulus',
                ]);

                // 3. Otomatis Catat ke Tabel Tracer Alumni
                TracerAlumni::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'tahun_lulus'     => $tahunLulus,
                        'status_saat_ini' => $statusTracer,
                    ]
                );

                $countLulus++;
            }

            DB::commit();

            return redirect()->route('superadmin.kenaikan-kelas.index', [
                'tab'                  => 'alumni',
                'filter_tahun_alumni' => $tahunLulus,
            ])->with('success', "Selamat! Sebanyak {$countLulus} siswa kelas XII resmi diluluskan dan otomatis terdaftar sebagai Alumni Angkatan {$tahunLulus}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses kelulusan siswa: ' . $e->getMessage());
        }
    }

    /**
     * Memproses Pergantian Tahun Ajaran Baru Berkelanjutan
     */
    public function gantiTahunAjaran(Request $request)
    {
        $request->validate([
            'nama'            => 'required|string|max:50',
            'tahun_mulai'     => 'required|numeric|digits:4',
            'tahun_selesai'   => 'required|numeric|digits:4|gt:tahun_mulai',
            'semester'        => 'required|in:ganjil,genap',
            'duplikasi_kelas' => 'nullable|boolean',
        ], [
            'nama.required'          => 'Nama tahun ajaran wajib diisi (contoh: 2027/2028).',
            'tahun_mulai.required'   => 'Tahun mulai wajib diisi.',
            'tahun_selesai.required' => 'Tahun selesai wajib diisi.',
            'tahun_selesai.gt'       => 'Tahun selesai harus lebih besar dari tahun mulai.',
            'semester.required'      => 'Pilih semester aktif awal.',
        ]);

        DB::beginTransaction();
        try {
            // 1. Nonaktifkan semua tahun ajaran lama
            TahunAjaran::query()->update(['is_aktif' => false]);

            // 2. Buat atau perbarui Tahun Ajaran Baru
            $tahunAjaranBaru = TahunAjaran::updateOrCreate(
                ['nama' => trim($request->nama)],
                [
                    'tahun_mulai'   => (int)$request->tahun_mulai,
                    'tahun_selesai' => (int)$request->tahun_selesai,
                    'is_aktif'      => true,
                ]
            );

            // 3. Sinkronkan Semester & Tahun Ajaran ke Tabel Pengaturan Sekolah
            if (Schema::hasTable('pengaturan_sekolahs')) {
                DB::table('pengaturan_sekolahs')->updateOrInsert(
                    ['key' => 'tahun_ajaran'],
                    ['value' => $tahunAjaranBaru->nama, 'updated_at' => now()]
                );
                DB::table('pengaturan_sekolahs')->updateOrInsert(
                    ['key' => 'semester'],
                    ['value' => $request->semester, 'updated_at' => now()]
                );
            }

            if (Schema::hasTable('pengaturan_sekolah')) {
                $cols = [];
                if (Schema::hasColumn('pengaturan_sekolah', 'tahun_ajaran')) {
                    $cols['tahun_ajaran'] = $tahunAjaranBaru->nama;
                }
                if (Schema::hasColumn('pengaturan_sekolah', 'semester')) {
                    $cols['semester'] = $request->semester;
                }
                if (!empty($cols)) {
                    DB::table('pengaturan_sekolah')->update($cols);
                }
            }

            // 4. Update relasi kelas aktif jika opsi duplikasi/kaitkan dipilih
            if ($request->boolean('duplikasi_kelas')) {
                Kelas::where('is_aktif', true)->update([
                    'tahun_ajaran_id' => $tahunAjaranBaru->id,
                ]);
            }

            DB::commit();

            return redirect()->route('superadmin.kenaikan-kelas.index', ['tab' => 'tahun_ajaran'])
                ->with('success', "Tahun Ajaran berhasil diperbarui secara berkelanjutan menjadi {$tahunAjaranBaru->nama} (Semester " . ucfirst($request->semester) . "). Sistem kini siap untuk aktivitas KBM tahun ajaran baru.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui tahun ajaran: ' . $e->getMessage());
        }
    }
}
