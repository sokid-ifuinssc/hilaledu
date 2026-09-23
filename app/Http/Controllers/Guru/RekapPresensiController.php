<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RekapPresensiService;
use App\Models\User;
use App\Models\PengaturanSekolah;

class RekapPresensiController extends Controller
{
    protected RekapPresensiService $service;

    public function __construct(RekapPresensiService $service)
    {
        $this->service = $service;
    }

    /**
     * Halaman Sub-Menu Rekap Presensi Guru (KBM & Kegiatan Sekolah)
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $targetUserId = $currentUser->id;

        // Jika pimpinan (Kepsek / Waka / Superadmin), izinkan filter guru lain
        $isExecutive = $currentUser->isSuperAdmin() || $currentUser->isKepalaSekolah() || $currentUser->isWakaKurikulum();
        if ($isExecutive && $request->filled('guru_id')) {
            $targetUserId = (int) $request->input('guru_id');
        }

        $bulan = $request->query('bulan'); // default null (1 tahun ajaran) atau misal '2026-09'
        $periodeMode = $request->query('mode', 'bulan'); // 'bulan' atau 'tahun'
        
        if ($periodeMode === 'bulan' && empty($bulan)) {
            $bulan = date('Y-m');
        } elseif ($periodeMode === 'tahun') {
            $bulan = null;
        }

        $tahunAjaran = $request->query('tahun_ajaran', PengaturanSekolah::get('tahun_pelajaran', '2026/2027'));

        $rekap = $this->service->getRekapGuru($targetUserId, $bulan, $tahunAjaran);

        $gurus = $isExecutive ? User::where('role', 'guru')->orderBy('name')->get() : collect();

        return view('guru.rekap_presensi.index', compact(
            'rekap',
            'bulan',
            'tahunAjaran',
            'periodeMode',
            'isExecutive',
            'gurus',
            'targetUserId'
        ));
    }

    /**
     * Cetak Lembar Rekap Presensi Resmi Individual Guru (PDF)
     */
    public function print(Request $request)
    {
        $currentUser = Auth::user();
        $targetUserId = $currentUser->id;

        if (($currentUser->isSuperAdmin() || $currentUser->isKepalaSekolah() || $currentUser->isWakaKurikulum()) && $request->filled('guru_id')) {
            $targetUserId = (int) $request->input('guru_id');
        }

        $bulan = $request->query('bulan');
        $tahunAjaran = $request->query('tahun_ajaran', PengaturanSekolah::get('tahun_pelajaran', '2026/2027'));

        $rekap = $this->service->getRekapGuru($targetUserId, $bulan, $tahunAjaran);

        $settings = [
            'nama_sekolah'        => PengaturanSekolah::get('nama_sekolah', 'SMK Plus Al-Hilal Arjawinangun'),
            'nama_kepala_sekolah' => PengaturanSekolah::get('nama_kepala_sekolah', 'Mukhammad Mansyur, S.Pt'),
            'nip_kepala_sekolah'  => PengaturanSekolah::get('nip_kepala_sekolah', '6942767668130350'),
            'titimangsa'          => PengaturanSekolah::get('titimangsa', 'Arjawinangun, ' . date('d F Y')),
        ];

        return view('guru.rekap_presensi.print', compact('rekap', 'bulan', 'tahunAjaran', 'settings'));
    }
}
