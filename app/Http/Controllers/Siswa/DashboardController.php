<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\JadwalPelajaran;
use App\Models\LaporanKbmPresensi;
use App\Models\KeluhanKbm;
use App\Models\KegiatanSekolah;
use App\Models\Kelas;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->with('kelas.jurusan')->first();
        $siswaData = $siswa;

        $todayName = JadwalPelajaran::getHariIndonesia();
        $todayDate = date('Y-m-d');

        $kelasNama = null;
        if ($siswa && $siswa->kelas) {
            $kelasNama = $siswa->kelas->nama_kelas ?? $siswa->kelas->nama;
        } elseif ($user->kelas_id) {
            $k = Kelas::find($user->kelas_id);
            $kelasNama = $k ? ($k->nama_kelas ?? $k->nama) : 'X TKJT';
        } else {
            $kelasNama = 'X AKL';
        }

        $studentUserIds = array_values(array_unique(array_filter([
            $user->id,
            $siswa?->id,
            $siswa?->user_id,
        ])));

        $jadwalHariIni = JadwalPelajaran::with(['mataPelajaran', 'guru'])
            ->where('hari', $todayName)
            ->where('kelas', $kelasNama)
            ->orderBy('jam_mulai')
            ->get();

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
            $laporan = $j->laporanKbms()->where('tanggal', $todayDate)->first();
            $presensi = null;
            if ($laporan) {
                $presensi = $laporan->presensis()->whereIn('siswa_user_id', $studentUserIds)->first();
            }
            $j->presensi_siswa_hari_ini = $presensi;
            $j->laporan_hari_ini = $laporan;

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

        $jadwalLengkap = JadwalPelajaran::with(['mataPelajaran', 'guru'])
            ->where('kelas', $kelasNama)
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy('hari');

        $presensiSaya = LaporanKbmPresensi::with(['laporan.jadwal.mataPelajaran', 'laporan.guru'])
            ->whereIn('siswa_user_id', $studentUserIds)
            ->latest()
            ->take(10)
            ->get();

        $totalHadir = LaporanKbmPresensi::whereIn('siswa_user_id', $studentUserIds)->whereIn('status', ['hadir', 'terlambat'])->count();
        $totalSakit = LaporanKbmPresensi::whereIn('siswa_user_id', $studentUserIds)->where('status', 'sakit')->count();
        $totalIzin = LaporanKbmPresensi::whereIn('siswa_user_id', $studentUserIds)->where('status', 'izin')->count();
        $totalAlpa = LaporanKbmPresensi::whereIn('siswa_user_id', $studentUserIds)->where('status', 'alpa')->count();

        $kegiatans = KegiatanSekolah::where('is_active', true)
            ->where('tanggal_kegiatan', '>=', now()->subDays(3)->format('Y-m-d'))
            ->orderBy('tanggal_kegiatan')
            ->take(5)
            ->get();

        $keluhanSaya = KeluhanKbm::with(['targetGuru', 'mataPelajaran', 'saranPerbaikan'])
            ->where('siswa_user_id', $user->id)
            ->latest()
            ->get();

        $tahunAjaran = TahunAjaran::aktif();

        $stats = [
            'poin_saat_ini' => $siswa?->poin ?? 0,
            'status_poin' => $siswa?->status_poin ?? 'Aman',
            'total_pelanggaran' => $siswa ? $siswa->pelanggarans()->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))->count() : 0,
            'pelanggaran_selesai' => $siswa ? $siswa->pelanggarans()->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))->where('status', 'selesai')->count() : 0,
            'pelanggaran_proses' => $siswa ? $siswa->pelanggarans()->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))->where('status', '!=', 'selesai')->count() : 0,
        ];

        $pelanggaranTerbaru = $siswa ? $siswa->pelanggarans()
            ->with(['jenisPelanggaran.kategori', 'progresPelanggaran'])
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->orderBy('tanggal_pelanggaran', 'desc')->limit(10)->get() : collect();

        return view('siswa.dashboard', compact(
            'user',
            'siswa',
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
            'keluhanSaya',
            'stats',
            'pelanggaranTerbaru'
        ));
    }
}
