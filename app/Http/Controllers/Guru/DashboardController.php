<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\JadwalPelajaran;
use App\Models\AbsensiGuru;
use App\Models\LaporanKbm;
use App\Models\MingguEfektif;
use App\Models\PerangkatAjar;
use App\Models\KegiatanSekolah;
use App\Models\KeluhanKbm;
use App\Models\SaranPerbaikan;
use App\Models\CutiGuru;
use App\Models\Kelas;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $todayName = JadwalPelajaran::getHariIndonesia();
        $todayDate = date('Y-m-d');
        $nowTime = now()->format('H:i:s');

        // 1. Jadwal Hari Ini (Khusus Guru Login)
        $jadwalHariIni = JadwalPelajaran::with(['mataPelajaran', 'absensiGuru' => function ($q) use ($todayDate) {
                $q->where('tanggal', $todayDate);
            }, 'laporanKbm' => function ($q) use ($todayDate) {
                $q->where('tanggal_realisasi', $todayDate);
            }])
            ->where('guru_user_id', $user->id)
            ->where('hari', $todayName)
            ->orderBy('jam_mulai')
            ->get();

        // Cek juga jika user menggantikan guru lain yang cuti hari ini
        $cutiInval = CutiGuru::where('guru_pengganti_id', $user->id)
            ->whereIn('status', ['disetujui_waka', 'disetujui_kepsek'])
            ->where('tanggal_mulai', '<=', $todayDate)
            ->where('tanggal_selesai', '>=', $todayDate)
            ->pluck('guru_user_id');

        if ($cutiInval->isNotEmpty()) {
            $jadwalInval = JadwalPelajaran::with(['mataPelajaran', 'guru', 'absensiGuru' => function ($q) use ($todayDate) {
                    $q->where('tanggal', $todayDate);
                }])
                ->whereIn('guru_user_id', $cutiInval)
                ->where('hari', $todayName)
                ->orderBy('jam_mulai')
                ->get();
        } else {
            $jadwalInval = collect();
        }

        // 2. Jadwal Lengkap Seminggu Guru Ini
        $totalJadwalSeminggu = JadwalPelajaran::where('guru_user_id', $user->id)->count();

        // 3. Rekap Kehadiran Bulan Ini
        $currentMonth = date('Y-m');
        $rekapAbsensiBulanIni = AbsensiGuru::where('guru_user_id', $user->id)
            ->where('tanggal', 'like', "{$currentMonth}%")
            ->get();

        $totalHadirTepat = $rekapAbsensiBulanIni->where('status', 'hadir')->count();
        $totalTerlambat = $rekapAbsensiBulanIni->where('status', 'terlambat')->count();
        $totalIzin = $rekapAbsensiBulanIni->where('status', 'izin')->count();
        $totalSakit = $rekapAbsensiBulanIni->where('status', 'sakit')->count();
        $totalTugasLuar = $rekapAbsensiBulanIni->where('status', 'tugas_luar')->count();
        $totalKehadiran = $totalHadirTepat + $totalTerlambat + $totalTugasLuar;

        // 4. Jadwal Kegiatan Sekolah Terkini yang Diinput Pimpinan (Rentang 1 Minggu Sebelum Hari H)
        $hMinus1 = Carbon::parse($todayDate)->subDay()->toDateString();
        $hPlus7  = Carbon::parse($todayDate)->addDays(7)->toDateString();

        $kegiatanTerbaru = KegiatanSekolah::with(['author', 'editor', 'presensis' => function ($q) use ($user) {
                $q->where('guru_user_id', $user->id);
            }])
            ->withCount(['presensis as total_kehadiran' => function ($q) {
                $q->where('status', 'hadir');
            }])
            ->where('is_active', true)
            ->whereBetween('tanggal_kegiatan', [$hMinus1, $hPlus7])
            ->orderBy('tanggal_kegiatan', 'asc')
            ->take(6)
            ->get();

        // Fallback jika tidak ada kegiatan dalam rentang 1 minggu ke depan, ambil 3 terdekat
        if ($kegiatanTerbaru->isEmpty()) {
            $kegiatanTerbaru = KegiatanSekolah::with(['author', 'editor', 'presensis' => function ($q) use ($user) {
                    $q->where('guru_user_id', $user->id);
                }])
                ->withCount(['presensis as total_kehadiran' => function ($q) {
                    $q->where('status', 'hadir');
                }])
                ->where('is_active', true)
                ->orderBy('tanggal_kegiatan', 'desc')
                ->take(3)
                ->get();
        }

        // 5. Suara Siswa & Saran Perbaikan
        $keluhanSaya = KeluhanKbm::with(['mataPelajaran', 'saranPerbaikan'])
            ->where('target_guru_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Saran perbaikan pembinaan terbaru
        $saranSaya = SaranPerbaikan::with(['author', 'keluhan'])
            ->where('target_guru_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 6. Status Link Perangkat Ajar
        $perangkatAjar = PerangkatAjar::where('guru_user_id', $user->id)->latest()->first();

        // 7. Status Cuti
        $cutiAktif = CutiGuru::where('guru_user_id', $user->id)
            ->whereIn('status', ['disetujui_waka', 'disetujui_kepsek'])
            ->where('tanggal_mulai', '<=', $todayDate)
            ->where('tanggal_selesai', '>=', $todayDate)
            ->first();

        // 8. Papan Monitoring Kehadiran Guru Real-Time Seluruh Kelas
        $monitoringPayload = app(\App\Services\MonitoringKelasService::class)->getMonitoringKelasData(
            $request->input('hari'),
            $request->input('jam'),
            $request->input('tanggal')
        );

        $monitoringKelas = $monitoringPayload['monitoringKelas'];
        $rekapMonitoring = $monitoringPayload['rekap'];
        $hariPantau      = $monitoringPayload['hariPantau'];
        $jamPantau       = $monitoringPayload['jamPantau'];
        $isSimulasi      = $monitoringPayload['isSimulasi'];
        $periodInfo      = $monitoringPayload['periodInfo'];

        // Presensi Harian Guru Hari Ini & Jadwal Mengajar Terawal
        $presensiHarianHariIni = \App\Models\PresensiHarianGuru::where('guru_user_id', $user->id)
            ->where('tanggal', $todayDate)
            ->first();
        $firstJadwalHariIni = $jadwalHariIni->sortBy('jam_ke_mulai')->first();

        return view('guru.dashboard', compact(
            'jadwalHariIni',
            'jadwalInval',
            'todayName',
            'todayDate',
            'nowTime',
            'totalJadwalSeminggu',
            'totalHadirTepat',
            'totalTerlambat',
            'totalIzin',
            'totalSakit',
            'totalTugasLuar',
            'totalKehadiran',
            'kegiatanTerbaru',
            'keluhanSaya',
            'saranSaya',
            'perangkatAjar',
            'cutiAktif',
            'monitoringKelas',
            'rekapMonitoring',
            'hariPantau',
            'jamPantau',
            'isSimulasi',
            'periodInfo',
            'presensiHarianHariIni',
            'firstJadwalHariIni'
        ));
    }

    /**
     * Endpoint API JSON untuk pembaruan data monitoring kelas realtime tanpa refresh penuh
     */
    public function monitoringData(Request $request)
    {
        $payload = app(\App\Services\MonitoringKelasService::class)->getMonitoringKelasData(
            $request->input('hari'),
            $request->input('jam'),
            $request->input('tanggal')
        );

        return response()->json([
            'success'     => true,
            'timestamp'   => now()->format('H:i:s') . ' WIB',
            'hari'        => $payload['hariPantau'],
            'jam'         => $payload['jamPantau'],
            'is_simulasi' => $payload['isSimulasi'],
            'rekap'       => $payload['rekap'],
            'periodInfo'  => $payload['periodInfo'],
            'classes'     => $payload['monitoringKelas'],
        ]);
    }

    /**
     * Hitung status kehadiran guru di seluruh kelas sesuai jam berlangsung realtime
     */
    public function getMonitoringKelasData(?string $hari = null, ?string $jam = null, ?string $tanggal = null): array
    {
        return app(\App\Services\MonitoringKelasService::class)->getMonitoringKelasData($hari, $jam, $tanggal);
    }
}

