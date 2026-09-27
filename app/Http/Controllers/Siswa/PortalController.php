<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\JadwalPelajaran;
use App\Models\LaporanKbmPresensi;
use App\Models\KeluhanKbm;
use App\Models\KegiatanSekolah;
use App\Models\User;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\Kelas;

class PortalController extends Controller
{
    /**
     * Dashboard Portal Siswa
     */
    public function dashboard()
    {
        $user = Auth::user();
        $todayName = JadwalPelajaran::getHariIndonesia();
        $todayDate = date('Y-m-d');

        // Cari kelas siswa dari db_hilaledu.siswas
        $siswaData = Siswa::with('kelas')->where('user_id', $user->id)->first();
        $kelasNama = null;

        if ($siswaData && $siswaData->kelas) {
            $kelasNama = $siswaData->kelas->nama_kelas ?? $siswaData->kelas->nama;
        } else {
            // Fallback: coba cari di kelas berdasarkan kelas_id jika ada
            if ($user->kelas_id) {
                $k = Kelas::find($user->kelas_id);
                $kelasNama = $k ? ($k->nama_kelas ?? $k->nama) : 'X TKJT';
            } else {
                $kelasNama = 'X AKL'; // Fallback default siswa
            }
        }

        $studentUserIds = array_values(array_unique(array_filter([
            $user->id,
            $siswaData?->id,
            $siswaData?->user_id,
        ])));

        // 1. Jadwal Hari Ini untuk Kelas Siswa beserta Presensi Kehadiran Siswa
        $jadwalHariIni = JadwalPelajaran::with([
                'mataPelajaran',
                'guru',
                'tugasKbms' => function ($q) use ($todayDate) {
                    $q->where('tanggal', $todayDate);
                },
                'laporanKbm' => function ($q) use ($todayDate, $studentUserIds) {
                    $q->where('tanggal_realisasi', $todayDate)
                      ->with(['presensiSiswa' => function ($pq) use ($studentUserIds) {
                          $pq->whereIn('siswa_user_id', $studentUserIds);
                      }]);
                }
            ])
            ->where('kelas', $kelasNama)
            ->where('hari', $todayName)
            ->orderBy('jam_mulai')
            ->get();

        // Rekap Kehadiran Siswa Khusus Hari Ini
        $rekapHariIni = [
            'total_sesi' => $jadwalHariIni->count(),
            'hadir'      => 0,
            'terlambat'  => 0,
            'izin'       => 0,
            'sakit'      => 0,
            'alpa'       => 0,
            'belum'      => 0,
        ];

        foreach ($jadwalHariIni as $j) {
            $laporan = $j->laporanKbm->first();
            $presensi = $laporan ? $laporan->presensiSiswa->first() : null;
            $j->laporan_hari_ini = $laporan;
            $j->presensi_siswa_hari_ini = $presensi;

            if ($presensi) {
                if ($presensi->status === 'hadir') $rekapHariIni['hadir']++;
                elseif ($presensi->status === 'terlambat') $rekapHariIni['terlambat']++;
                elseif ($presensi->status === 'izin') $rekapHariIni['izin']++;
                elseif ($presensi->status === 'sakit') $rekapHariIni['sakit']++;
                elseif ($presensi->status === 'alpa') $rekapHariIni['alpa']++;
            } else {
                $rekapHariIni['belum']++;
            }
        }

        // 2. Jadwal Lengkap Seminggu (Senin - Sabtu)
        $jadwalLengkap = JadwalPelajaran::with(['mataPelajaran', 'guru'])
            ->where('kelas', $kelasNama)
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy('hari');

        // 3. Rekap Riwayat Presensi Siswa Ini di KBM
        $presensiSaya = LaporanKbmPresensi::with(['laporan.jadwal.mataPelajaran', 'laporan.guru'])
            ->whereIn('siswa_user_id', $studentUserIds)
            ->latest()
            ->take(10)
            ->get();

        $totalHadir = LaporanKbmPresensi::whereIn('siswa_user_id', $studentUserIds)->whereIn('status', ['hadir', 'terlambat'])->count();
        $totalSakit = LaporanKbmPresensi::whereIn('siswa_user_id', $studentUserIds)->where('status', 'sakit')->count();
        $totalIzin = LaporanKbmPresensi::whereIn('siswa_user_id', $studentUserIds)->where('status', 'izin')->count();
        $totalAlpa = LaporanKbmPresensi::whereIn('siswa_user_id', $studentUserIds)->where('status', 'alpa')->count();

        // 4. Notifikasi Kegiatan Sekolah
        $kegiatans = KegiatanSekolah::where('is_active', true)
            ->where('tanggal_kegiatan', '>=', now()->subDays(3)->format('Y-m-d'))
            ->orderBy('tanggal_kegiatan')
            ->take(5)
            ->get();

        // 5. Riwayat Keluhan Siswa Ini (Hanya bisa dilihat oleh siswa pengirim)
        $keluhanSaya = KeluhanKbm::with(['targetGuru', 'mataPelajaran', 'saranPerbaikan'])
            ->where('siswa_user_id', $user->id)
            ->latest()
            ->get();

        return view('siswa.dashboard', compact(
            'user',
            'siswaData',
            'kelasNama',
            'todayName',
            'todayDate',
            'jadwalHariIni',
            'rekapHariIni',
            'jadwalLengkap',
            'presensiSaya',
            'totalHadir',
            'totalSakit',
            'totalIzin',
            'totalAlpa',
            'kegiatans',
            'keluhanSaya'
        ));
    }

    /**
     * Tampilkan form input keluhan KBM (Suara Siswa Anonim)
     */
    public function createKeluhan()
    {
        $user = Auth::user();

        // Cari kelas siswa
        $siswaData = Siswa::with('kelas')->where('user_id', $user->id)->first();
        $kelasNama = $siswaData && $siswaData->kelas ? ($siswaData->kelas->nama_kelas ?? $siswaData->kelas->nama) : 'X AKL';

        // Guru-guru yang mengajar di kelas siswa ini
        $gurus = User::where('role', 'guru')->where('is_active', true)->orderBy('name')->get();
        $mapels = MataPelajaran::where('is_aktif', true)->orderBy('nama')->get();

        return view('siswa.keluhan_create', compact('kelasNama', 'gurus', 'mapels'));
    }

    /**
     * Simpan keluhan siswa dengan jaminan kerahasiaan identitas
     */
    public function storeKeluhan(Request $request)
    {
        $request->validate([
            'target_guru_user_id' => 'required|exists:users,id',
            'mata_pelajaran_id'   => 'nullable|exists:mata_pelajarans,id',
            'kelas'               => 'required|string',
            'tanggal_kbm'         => 'required|date',
            'kategori_masalah'    => 'required|string|max:100',
            'isi_keluhan'         => 'required|string|min:10',
            'harapan_siswa'       => 'nullable|string',
        ]);

        $user = Auth::user();

        KeluhanKbm::create([
            'siswa_user_id'       => $user->id, // Disimpan internal saja, strictly anonim di tampilan
            'target_guru_user_id' => $request->target_guru_user_id,
            'mata_pelajaran_id'   => $request->mata_pelajaran_id,
            'kelas'               => $request->kelas,
            'tanggal_kbm'         => $request->tanggal_kbm,
            'kategori_masalah'    => $request->kategori_masalah,
            'isi_keluhan'         => $request->isi_keluhan,
            'harapan_siswa'       => $request->harapan_siswa,
            'status'              => 'baru',
        ]);

        return redirect()->route('siswa.dashboard')
            ->with('success', 'Masukan / keluhan KBM Anda berhasil dikirimkan secara RAHASIA (Anonim). Identitas Anda 100% terjaga kerahasiaannya.');
    }

    /**
     * Tampilan Jadwal Lengkap Kelas Siswa
     */
    public function jadwal(Request $request)
    {
        $user = Auth::user();
        $siswaData = Siswa::with('kelas')->where('user_id', $user->id)->first();
        $kelasNama = null;

        if ($siswaData && $siswaData->kelas) {
            $kelasNama = $siswaData->kelas->nama_kelas ?? $siswaData->kelas->nama;
        } elseif ($user->kelas) {
            $kelasNama = $user->kelas->nama_kelas ?? $user->kelas->nama;
        } elseif ($user->kelas_id) {
            $k = Kelas::find($user->kelas_id);
            $kelasNama = $k ? ($k->nama_kelas ?? $k->nama) : 'X AKL';
        } else {
            $kelasNama = 'X AKL';
        }

        $tahunAjaran = \App\Models\PengaturanSekolah::getActiveTahunAjaran();
        $semester    = \App\Models\PengaturanSekolah::getActiveSemester();

        // Ambil jadwal lengkap seminggu penuh (Senin - Sabtu) hanya untuk kelas siswa ini
        $jadwalLengkap = JadwalPelajaran::with(['mataPelajaran', 'guru'])
            ->where('kelas', $kelasNama)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('jam_ke_mulai')
            ->get()
            ->groupBy('hari');

        // Ringkasan mapel & guru di kelas ini
        $ringkasanMapel = JadwalPelajaran::with(['mataPelajaran', 'guru'])
            ->where('kelas', $kelasNama)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->get()
            ->groupBy('mata_pelajaran_id');

        $totalJp = JadwalPelajaran::where('kelas', $kelasNama)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->get()
            ->sum(fn($j) => max(1, ($j->jam_ke_selesai - $j->jam_ke_mulai + 1)));

        // Temukan wali kelas untuk kelas ini
        $kelasModel = Kelas::where('nama_kelas', $kelasNama)->orWhere('nama', $kelasNama)->first();
        $waliKelas = null;
        if ($kelasModel && $kelasModel->wali_kelas_id) {
            $waliKelas = User::find($kelasModel->wali_kelas_id);
        }

        return view('siswa.jadwal', compact(
            'user',
            'siswaData',
            'kelasNama',
            'kelasModel',
            'waliKelas',
            'jadwalLengkap',
            'ringkasanMapel',
            'totalJp',
            'tahunAjaran',
            'semester'
        ));
    }

    /**
     * Cetak Jadwal Pelajaran Kelas Siswa
     */
    public function printJadwal(Request $request)
    {
        $user = Auth::user();
        $siswaData = Siswa::with('kelas')->where('user_id', $user->id)->first();
        $kelasNama = null;

        if ($siswaData && $siswaData->kelas) {
            $kelasNama = $siswaData->kelas->nama_kelas ?? $siswaData->kelas->nama;
        } elseif ($user->kelas) {
            $kelasNama = $user->kelas->nama_kelas ?? $user->kelas->nama;
        } elseif ($user->kelas_id) {
            $k = Kelas::find($user->kelas_id);
            $kelasNama = $k ? ($k->nama_kelas ?? $k->nama) : 'X AKL';
        } else {
            $kelasNama = 'X AKL';
        }

        $tahunAjaran = \App\Models\PengaturanSekolah::getActiveTahunAjaran();
        $semester    = \App\Models\PengaturanSekolah::getActiveSemester();

        $jadwalLengkap = JadwalPelajaran::with(['mataPelajaran', 'guru'])
            ->where('kelas', $kelasNama)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('jam_ke_mulai')
            ->get()
            ->groupBy('hari');

        $kelasModel = Kelas::where('nama_kelas', $kelasNama)->orWhere('nama', $kelasNama)->first();
        $waliKelas = null;
        if ($kelasModel && $kelasModel->wali_kelas_id) {
            $waliKelas = User::find($kelasModel->wali_kelas_id);
        }
        $settings = \App\Models\PengaturanSekolah::getAllSettings();

        return view('siswa.jadwal_print', compact(
            'user',
            'kelasNama',
            'kelasModel',
            'waliKelas',
            'jadwalLengkap',
            'tahunAjaran',
            'semester',
            'settings'
        ));
    }
}
