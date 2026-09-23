<?php

namespace App\Http\Controllers\Tendik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PresensiHarianGuru;
use App\Models\KegiatanSekolah;
use App\Models\PetugasPiket;
use App\Models\User;
use App\Models\JadwalPelajaran;
use App\Services\PresensiHarianService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected PresensiHarianService $presensiService;

    public function __construct(PresensiHarianService $presensiService)
    {
        $this->presensiService = $presensiService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $todayDate = date('Y-m-d');
        $todayName = JadwalPelajaran::getHariIndonesia();
        $nowTime = now()->format('H:i:s');

        // Presensi Harian Tendik Hari Ini
        $presensiHarianHariIni = PresensiHarianGuru::where('guru_user_id', $user->id)
            ->where('tanggal', $todayDate)
            ->first();

        // Jadwal Kegiatan Sekolah Terdekat
        $hMinus1 = Carbon::parse($todayDate)->subDay()->toDateString();
        $hPlus7  = Carbon::parse($todayDate)->addDays(7)->toDateString();

        $kegiatanTerbaru = KegiatanSekolah::with(['author', 'editor', 'presensis' => function ($q) use ($user) {
                $q->where('guru_user_id', $user->id);
            }])
            ->where('is_active', true)
            ->whereBetween('tanggal_kegiatan', [$hMinus1, $hPlus7])
            ->orderBy('tanggal_kegiatan', 'asc')
            ->take(5)
            ->get();

        if ($kegiatanTerbaru->isEmpty()) {
            $kegiatanTerbaru = KegiatanSekolah::with(['author', 'editor', 'presensis' => function ($q) use ($user) {
                    $q->where('guru_user_id', $user->id);
                }])
                ->where('is_active', true)
                ->orderBy('tanggal_kegiatan', 'desc')
                ->take(3)
                ->get();
        }

        // Cek apakah Tendik ini bertugas sebagai Petugas Piket hari ini
        $isPetugasPiketHariIni = $user->isPetugasPiket($todayName, $todayDate);
        $piketData = null;

        if ($isPetugasPiketHariIni || $user->canAccessPiket()) {
            $rekapData = $this->presensiService->getRekapKehadiranDewanGuruHariIni($todayDate);
            $notifikasiIzinKeluar = $this->presensiService->getNotifikasiIzinKeluarHariIni($todayDate);
            $petugasHariIni = PetugasPiket::with('user')
                ->activeToday($todayName, $todayDate)
                ->get();
            $daftarGuru = User::where('role', 'guru')->where('is_active', true)->orderBy('name')->get();

            $piketData = [
                'items'                => $rekapData['items'],
                'rekap'                => $rekapData['rekap'],
                'notifikasiIzinKeluar' => $notifikasiIzinKeluar,
                'petugasHariIni'       => $petugasHariIni,
                'daftarGuru'           => $daftarGuru,
            ];
        }

        return view('tendik.dashboard', compact(
            'user',
            'todayDate',
            'todayName',
            'nowTime',
            'presensiHarianHariIni',
            'kegiatanTerbaru',
            'isPetugasPiketHariIni',
            'piketData'
        ));
    }
}
