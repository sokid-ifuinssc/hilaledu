<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RekapPresensiService;
use App\Models\User;
use App\Models\PengaturanSekolah;

class AdminRekapPresensiController extends Controller
{
    protected RekapPresensiService $service;

    public function __construct(RekapPresensiService $service)
    {
        $this->service = $service;
    }

    /**
     * Halaman Rekapitulasi Presensi Seluruh Guru untuk Admin & Kepala Sekolah
     */
    public function index(Request $request)
    {
        $bulan = $request->query('bulan');
        $periodeMode = $request->query('mode', 'bulan'); // 'bulan' atau 'tahun'
        $guruId = $request->query('guru_id');

        if ($periodeMode === 'bulan' && empty($bulan)) {
            $bulan = date('Y-m');
        } elseif ($periodeMode === 'tahun') {
            $bulan = null;
        }

        $tahunAjaran = $request->query('tahun_ajaran', PengaturanSekolah::get('tahun_pelajaran', '2026/2027'));

        // Jika memilih filter per guru tertentu, ambil detail per guru
        $guruDetail = null;
        if (!empty($guruId)) {
            $guruDetail = $this->service->getRekapGuru((int)$guruId, $bulan, $tahunAjaran);
        }

        $rekapSemua = $this->service->getRekapSemuaGuru($bulan, $tahunAjaran);
        $gurus = User::where('role', 'guru')->orderBy('name')->get();

        return view('admin.rekap_presensi.index', compact(
            'rekapSemua',
            'guruDetail',
            'bulan',
            'tahunAjaran',
            'periodeMode',
            'guruId',
            'gurus'
        ));
    }

    /**
     * Cetak / Export PDF Rekapitulasi Presensi Keseluruhan Dewan Guru
     */
    public function printAll(Request $request)
    {
        $bulan = $request->query('bulan');
        $tahunAjaran = $request->query('tahun_ajaran', PengaturanSekolah::get('tahun_pelajaran', '2026/2027'));

        $rekapSemua = $this->service->getRekapSemuaGuru($bulan, $tahunAjaran);

        $settings = [
            'nama_sekolah'        => PengaturanSekolah::get('nama_sekolah', 'SMK Plus Al-Hilal Arjawinangun'),
            'nama_kepala_sekolah' => PengaturanSekolah::get('nama_kepala_sekolah', 'Mukhammad Mansyur, S.Pt'),
            'nip_kepala_sekolah'  => PengaturanSekolah::get('nip_kepala_sekolah', '6942767668130350'),
            'nama_waka_kurikulum' => PengaturanSekolah::get('nama_waka_kurikulum', 'Sokid, S.T, M.Kom'),
            'nip_waka_kurikulum'  => PengaturanSekolah::get('nip_waka_kurikulum', '198501012010011005'),
            'titimangsa'          => PengaturanSekolah::get('titimangsa', 'Arjawinangun, ' . date('d F Y')),
        ];

        return view('admin.rekap_presensi.print_all', compact(
            'rekapSemua',
            'bulan',
            'tahunAjaran',
            'settings'
        ));
    }

    /**
     * Cetak Lembar Rekap Presensi Individual Guru oleh Admin / Kepala Sekolah
     */
    public function printGuru(Request $request, User $guru)
    {
        $bulan = $request->query('bulan');
        $tahunAjaran = $request->query('tahun_ajaran', PengaturanSekolah::get('tahun_pelajaran', '2026/2027'));

        $rekap = $this->service->getRekapGuru($guru->id, $bulan, $tahunAjaran);

        $settings = [
            'nama_sekolah'        => PengaturanSekolah::get('nama_sekolah', 'SMK Plus Al-Hilal Arjawinangun'),
            'nama_kepala_sekolah' => PengaturanSekolah::get('nama_kepala_sekolah', 'Mukhammad Mansyur, S.Pt'),
            'nip_kepala_sekolah'  => PengaturanSekolah::get('nip_kepala_sekolah', '6942767668130350'),
            'titimangsa'          => PengaturanSekolah::get('titimangsa', 'Arjawinangun, ' . date('d F Y')),
        ];

        return view('guru.rekap_presensi.print', compact('rekap', 'bulan', 'tahunAjaran', 'settings'));
    }
}
