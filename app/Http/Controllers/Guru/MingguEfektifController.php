<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MingguEfektif;
use App\Models\MataPelajaran;
use App\Models\Kelas;
use App\Models\KalenderAkademik;
use App\Models\User;
use App\Services\MingguEfektifService;

class MingguEfektifController extends Controller
{
    protected MingguEfektifService $service;

    public function __construct(MingguEfektifService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $isManager = ($user->isSuperAdmin() || $user->isWakaKurikulum() || $user->isAdmin());

        // Tahun Ajaran & Semester aktif
        $activeKalender = KalenderAkademik::where('is_aktif', true)->latest()->first() 
            ?? KalenderAkademik::orderBy('tahun_ajaran', 'desc')->first();
        $defaultTahun = \App\Models\PengaturanSekolah::getActiveTahunAjaran();
        $tahun = $request->query('tahun_ajaran', $defaultTahun);
        $semester = $request->query('semester', \App\Models\PengaturanSekolah::getActiveSemester());

        // Target Guru
        $targetGuruId = $user->id;
        $gurus = collect();
        if ($isManager) {
            $gurus = User::whereIn('role', ['guru', 'kepsek', 'waka'])->orderBy('name')->get();
            if ($request->filled('guru_id')) {
                $targetGuruId = (int)$request->query('guru_id');
            }
        }

        $selectedGuru = User::find($targetGuruId) ?? $user;

        // 1. Ambil penugasan mengajar guru sesuai jadwal aktif
        $assignments = $this->service->getGuruAssignments($targetGuruId, $tahun, $semester);

        // 2. Otomatis hitung dan sinkronkan ke database jika belum ada atau saat pertama kali dibuka
        $calculatedItems = [];
        $totalJpSeminggu = 0;
        $totalJamEfektifSemester = 0;

        foreach ($assignments as $assignment) {
            $calc = $this->service->calculateForAssignment($assignment);
            $record = $this->service->syncToDatabase($calc);

            $calculatedItems[] = [
                'record'        => $record,
                'assignment'    => $assignment,
                'calc'          => $calc,
            ];

            $totalJpSeminggu += $calc['jam_per_minggu'];
            $totalJamEfektifSemester += $calc['total_jam_efektif'];
        }

        // Ambil data minggu efektif tersimpan
        $mingguEfektifs = MingguEfektif::with(['mataPelajaran', 'guru'])
            ->where('guru_user_id', $targetGuruId)
            ->where('tahun_ajaran', $tahun)
            ->where('semester', $semester)
            ->latest()
            ->get();

        $kalenders = KalenderAkademik::orderBy('tahun_ajaran', 'desc')->get();

        return view('guru.minggu_efektif.index', compact(
            'calculatedItems',
            'mingguEfektifs',
            'assignments',
            'tahun',
            'semester',
            'kalenders',
            'activeKalender',
            'isManager',
            'gurus',
            'selectedGuru',
            'totalJpSeminggu',
            'totalJamEfektifSemester'
        ));
    }

    public function sync(Request $request)
    {
        $user = Auth::user();
        $isManager = ($user->isSuperAdmin() || $user->isWakaKurikulum() || $user->isAdmin());

        $tahun = $request->input('tahun_ajaran', \App\Models\PengaturanSekolah::getActiveTahunAjaran());
        $semester = $request->input('semester', \App\Models\PengaturanSekolah::getActiveSemester());

        $targetGuruId = $user->id;
        if ($isManager && $request->filled('guru_id')) {
            $targetGuruId = (int)$request->input('guru_id');
        }

        $synced = $this->service->autoSyncAllForGuru($targetGuruId, $tahun, $semester);

        return redirect()->route('guru.minggu-efektif.index', [
            'tahun_ajaran' => $tahun,
            'semester'     => $semester,
            'guru_id'      => $isManager ? $targetGuruId : null,
        ])->with('success', "Berhasil menghitung otomatis {$synced->count()} mapel berdasarkan jadwal mengajar & kalender akademik.");
    }

    public function create()
    {
        return redirect()->route('guru.minggu-efektif.index')
            ->with('info', 'Minggu efektif kini dihitung 100% otomatis berdasarkan jadwal mengajar dan kalender akademik sekolah.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'kelas'             => 'required|string',
            'tahun_ajaran'      => 'required|string',
            'semester'          => 'required|in:ganjil,genap',
            'jam_per_minggu'    => 'required|numeric|min:1',
            'bulans'            => 'required|array',
        ]);

        $user = Auth::user();
        $rincianBulanan = [];
        $totalMinggu = 0;
        $totalTidak = 0;

        foreach ($request->bulans as $bulan => $data) {
            $tm = intval($data['total_minggu'] ?? 0);
            $tne = intval($data['tidak_efektif'] ?? 0);
            $ef = max(0, $tm - $tne);

            $totalMinggu += $tm;
            $totalTidak += $tne;

            $rincianBulanan[] = [
                'bulan'         => $bulan,
                'total_minggu'  => $tm,
                'tidak_efektif' => $tne,
                'efektif'       => $ef,
                'keterangan'    => $data['keterangan'] ?? '-',
            ];
        }

        $totalEfektif = max(0, $totalMinggu - $totalTidak);
        $jamPerMinggu = intval($request->jam_per_minggu);
        $totalJam = $totalEfektif * $jamPerMinggu;

        $distribusiJam = [
            'tatap_muka'        => intval($request->tatap_muka ?? ($totalJam * 0.8)),
            'asesmen_formatif'  => intval($request->asesmen_formatif ?? ($totalJam * 0.1)),
            'asesmen_sumatif'   => intval($request->asesmen_sumatif ?? ($totalJam * 0.05)),
            'cadangan'          => intval($request->cadangan ?? ($totalJam * 0.05)),
        ];

        MingguEfektif::updateOrCreate(
            [
                'guru_user_id'      => $user->id,
                'mata_pelajaran_id' => $request->mata_pelajaran_id,
                'kelas'             => $request->kelas,
                'tahun_ajaran'      => $request->tahun_ajaran,
                'semester'          => $request->semester,
            ],
            [
                'total_minggu'        => $totalMinggu,
                'total_tidak_efektif' => $totalTidak,
                'total_efektif'       => $totalEfektif,
                'jam_per_minggu'      => $jamPerMinggu,
                'total_jam_efektif'   => $totalJam,
                'rincian_bulanan'     => $rincianBulanan,
                'distribusi_jam'      => $distribusiJam,
                'catatan'             => $request->catatan,
            ]
        );

        return redirect()->route('guru.minggu-efektif.index')->with('success', 'Rincian Minggu Efektif berhasil disimpan!');
    }

    public function show(MingguEfektif $mingguEfektif)
    {
        $mingguEfektif->load(['mataPelajaran', 'guru']);
        return view('guru.minggu_efektif.show', compact('mingguEfektif'));
    }

    public function print(MingguEfektif $mingguEfektif)
    {
        $mingguEfektif->load(['mataPelajaran', 'guru']);
        return view('guru.minggu_efektif.print', compact('mingguEfektif'));
    }

    public function destroy(MingguEfektif $mingguEfektif)
    {
        $user = Auth::user();
        if ($mingguEfektif->guru_user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $mingguEfektif->delete();
        return redirect()->route('guru.minggu-efektif.index')->with('success', 'Data Minggu Efektif berhasil dihapus.');
    }
}
