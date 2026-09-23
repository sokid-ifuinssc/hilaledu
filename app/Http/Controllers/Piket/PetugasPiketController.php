<?php

namespace App\Http\Controllers\Piket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PetugasPiket;
use App\Models\PresensiHarianGuru;
use App\Models\JadwalPelajaran;
use App\Services\PresensiHarianService;
use Carbon\Carbon;

class PetugasPiketController extends Controller
{
    protected PresensiHarianService $presensiService;

    public function __construct(PresensiHarianService $presensiService)
    {
        $this->presensiService = $presensiService;
    }

    /**
     * Dashboard & Panel Kendali Petugas Piket
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Verifikasi hak akses piket
        if (!$user->canAccessPiket()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki penugasan sebagai Petugas Piket hari ini.');
        }

        $tanggal = $request->query('tanggal', date('Y-m-d'));
        $hari = JadwalPelajaran::getHariIndonesia(Carbon::parse($tanggal));

        // Petugas piket yang bertugas hari ini
        $petugasHariIni = PetugasPiket::with('user')
            ->activeToday($hari, $tanggal)
            ->get();

        // Notifikasi Izin Keluar (Guru jam 3+ yang izin keluar sementara saat jam jeda)
        $notifikasiIzinKeluar = $this->presensiService->getNotifikasiIzinKeluarHariIni($tanggal);

        // Rekapitulasi Presensi Seluruh Guru Hari Ini
        $rekapData = $this->presensiService->getRekapKehadiranDewanGuruHariIni($tanggal);

        $daftarGuru = User::where('role', 'guru')->where('is_active', true)->orderBy('name')->get();

        return view('piket.index', [
            'petugasHariIni'       => $petugasHariIni,
            'notifikasiIzinKeluar' => $notifikasiIzinKeluar,
            'items'                => $rekapData['items'],
            'rekap'                => $rekapData['rekap'],
            'hari'                 => $hari,
            'tanggal'              => $tanggal,
            'daftarGuru'           => $daftarGuru,
            'user'                 => $user,
        ]);
    }

    /**
     * Petugas Piket mengabsenkan guru (jika guru lupa / kendala teknis)
     */
    public function storeAbsenGuru(Request $request)
    {
        $request->validate([
            'guru_user_id' => 'required|exists:users,id',
            'tipe'         => 'required|in:masuk,pulang',
            'jam'          => 'nullable|date_format:H:i',
            'catatan'      => 'nullable|string|max:255',
        ]);

        $piketUser = Auth::user();
        if (!$piketUser->canAccessPiket()) {
            abort(403, 'Anda tidak berhak mengabsenkan guru.');
        }

        $guru = User::findOrFail($request->guru_user_id);
        $timeStr = $request->jam ? $request->jam . ':00' : now()->format('H:i:s');
        $customTime = Carbon::parse(date('Y-m-d') . ' ' . $timeStr);

        if ($request->tipe === 'masuk') {
            $res = $this->presensiService->recordPresensiMasuk($guru, $customTime, $piketUser, [
                'catatan'      => $request->catatan ?: "Diabsenkan oleh Petugas Piket ({$piketUser->name}) karena guru lupa/kendala teknis.",
                'force_update' => true,
            ]);
        } else {
            $res = $this->presensiService->recordPresensiPulang($guru, $customTime, $piketUser, [
                'catatan' => $request->catatan ?: "Diabsenkan pulang oleh Petugas Piket ({$piketUser->name}).",
            ]);
        }

        return back()->with($res['success'] ? 'success' : 'warning', $res['message']);
    }

    /**
     * Petugas Piket merubah status kehadiran guru (Izin, Sakit, Tugas Luar, Hadir Manual, dll)
     */
    public function updateStatusGuru(Request $request)
    {
        $request->validate([
            'guru_user_id'    => 'required|exists:users,id',
            'status'          => 'required|in:hadir,terlambat,izin,sakit,tugas_luar,alpa',
            'catatan'         => 'nullable|string|max:500',
            'lampiran_bukti'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:3072',
            'tanggal'         => 'nullable|date',
        ]);

        $piketUser = Auth::user();
        if (!$piketUser->canAccessPiket()) {
            abort(403, 'Anda tidak berhak merubah status guru.');
        }

        $guru = User::findOrFail($request->guru_user_id);
        $tanggal = $request->tanggal ?: date('Y-m-d');

        $lampiranPath = null;
        if ($request->hasFile('lampiran_bukti')) {
            $lampiranPath = $request->file('lampiran_bukti')->store('piket_bukti', 'public');
        }

        $res = $this->presensiService->piketOverrideGuru(
            $guru,
            $request->status,
            $request->catatan,
            $lampiranPath,
            $piketUser,
            $tanggal
        );

        return back()->with('success', $res['message']);
    }

    /**
     * Petugas Piket merespons / mengetahui izin keluar sementara guru
     */
    public function tanggapiIzinKeluar(Request $request, PresensiHarianGuru $presensi)
    {
        $request->validate([
            'status'         => 'required|in:disetujui_piket,ditolak_piket',
            'catatan_piket'  => 'nullable|string|max:255',
        ]);

        $piketUser = Auth::user();
        if (!$piketUser->canAccessPiket()) {
            abort(403, 'Anda tidak memiliki wewenang piket.');
        }

        $res = $this->presensiService->piketTanggapiIzinKeluar(
            $presensi,
            $request->status,
            $request->catatan_piket,
            $piketUser
        );

        return back()->with('success', $res['message']);
    }

    /**
     * Lembar Cetak Rekapitulasi Laporan Piket Harian
     */
    public function print(Request $request)
    {
        $tanggal = $request->query('tanggal', date('Y-m-d'));
        $hari = JadwalPelajaran::getHariIndonesia(Carbon::parse($tanggal));

        $petugasHariIni = PetugasPiket::with('user')
            ->activeToday($hari, $tanggal)
            ->get();

        $rekapData = $this->presensiService->getRekapKehadiranDewanGuruHariIni($tanggal);
        $notifikasiIzinKeluar = $this->presensiService->getNotifikasiIzinKeluarHariIni($tanggal);

        return view('piket.print', [
            'petugasHariIni'       => $petugasHariIni,
            'notifikasiIzinKeluar' => $notifikasiIzinKeluar,
            'items'                => $rekapData['items'],
            'rekap'                => $rekapData['rekap'],
            'hari'                 => $hari,
            'tanggal'              => $tanggal,
        ]);
    }
}
