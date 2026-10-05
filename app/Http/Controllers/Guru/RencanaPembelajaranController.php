<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CapaianPembelajaran;
use App\Models\TujuanPembelajaran;
use App\Models\AlurTujuanPembelajaran;
use App\Models\RencanaPembelajaran;
use App\Models\JadwalPelajaran;
use App\Models\MataPelajaran;

class RencanaPembelajaranController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $jadwals = JadwalPelajaran::with('mataPelajaran')
            ->where('guru_user_id', $user->id)
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $assignedMapels = collect();
        foreach ($jadwals as $jadwal) {
            if ($jadwal->mataPelajaran) {
                $kelasStr = strtoupper($jadwal->kelas);
                $tingkat = 'X';
                $fase = 'E';
                if (str_starts_with($kelasStr, 'XII')) {
                    $tingkat = 'XII';
                    $fase = 'F';
                } elseif (str_starts_with($kelasStr, 'XI')) {
                    $tingkat = 'XI';
                    $fase = 'F';
                } elseif (str_starts_with($kelasStr, 'X')) {
                    $tingkat = 'X';
                    $fase = 'E';
                }

                $key = $jadwal->mata_pelajaran_id . '_' . $tingkat;
                if (!$assignedMapels->has($key)) {
                    $assignedMapels->put($key, [
                        'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
                        'nama_mapel' => $jadwal->mataPelajaran->nama,
                        'tingkat' => $tingkat,
                        'fase' => $fase,
                        'label' => $jadwal->mataPelajaran->nama . ' - Kelas ' . $tingkat . ' (Fase ' . $fase . ')'
                    ]);
                }
            }
        }
        $assignedMapels = $assignedMapels->values();

        $activeKalender = \App\Models\KalenderAkademik::with(['events' => function ($q) {
                $q->orderBy('tanggal_mulai');
            }])
            ->where('is_aktif', true)
            ->first() ?: \App\Models\KalenderAkademik::getActiveCalendar();

        $selectedMapelId = $request->query('mapel_id');
        $selectedTingkat = $request->query('tingkat');

        if (!$selectedMapelId || !$selectedTingkat) {
            return view('guru.rencana_pembelajaran.pilih_mapel', compact('assignedMapels', 'activeKalender'));
        }

        $tab = $request->query('tab', 'cp'); // cp, tp, atp, rpp

        $mapelInfo = $assignedMapels->first(function($item) use ($selectedMapelId, $selectedTingkat) {
            return $item['mata_pelajaran_id'] == $selectedMapelId && $item['tingkat'] == $selectedTingkat;
        });

        if (!$mapelInfo) {
            return redirect()->route('guru.rencana-pembelajaran.index')->with('error', 'Mata Pelajaran tidak valid atau tidak ditugaskan pada Anda dalam jadwal.');
        }

        // 1. Capaian Pembelajaran (CP)
        $cpList = CapaianPembelajaran::with(['mataPelajaran', 'tujuanPembelajaran'])
            ->where('guru_user_id', $user->id)
            ->where('mata_pelajaran_id', $selectedMapelId)
            ->where('tingkat', $selectedTingkat)
            ->latest()
            ->get();

        // 2. Tujuan Pembelajaran (TP)
        $tpList = TujuanPembelajaran::whereHas('capaianPembelajaran', function ($q) use ($user, $selectedMapelId, $selectedTingkat) {
                $q->where('guru_user_id', $user->id)
                  ->where('mata_pelajaran_id', $selectedMapelId)
                  ->where('tingkat', $selectedTingkat);
            })
            ->with('capaianPembelajaran.mataPelajaran')
            ->orderBy('kode_tp')
            ->get();

        // 3. Alur Tujuan Pembelajaran (ATP)
        $atpList = AlurTujuanPembelajaran::with(['mataPelajaran', 'capaianPembelajaran', 'tujuanPembelajaran'])
            ->where('guru_user_id', $user->id)
            ->where('mata_pelajaran_id', $selectedMapelId)
            ->where('tingkat', $selectedTingkat)
            ->orderBy('fase')
            ->orderBy('tingkat')
            ->orderBy('semester')
            ->orderBy('alur_ke')
            ->get();

        // 4. Rencana Pembelajaran Harian (RPP / Modul Ajar)
        $rencanaList = RencanaPembelajaran::with(['jadwal.mataPelajaran', 'tujuanPembelajaran'])
            ->where('guru_user_id', $user->id)
            ->whereHas('jadwal', function($q) use ($selectedMapelId, $selectedTingkat) {
                $q->where('mata_pelajaran_id', $selectedMapelId)
                  ->where(function($q2) use ($selectedTingkat) {
                      $q2->where('kelas', 'LIKE', $selectedTingkat . ' %')
                         ->orWhere('kelas', 'LIKE', $selectedTingkat);
                  });
            })
            ->orderBy('tanggal_rencana', 'desc')
            ->paginate(15);

        // Jadwal mengajar untuk dropdown RPP (khusus mapel ini)
        $jadwalsManage = $jadwals->where('mata_pelajaran_id', $selectedMapelId)->filter(function($jadwal) use ($selectedTingkat) {
            $kelasStr = strtoupper($jadwal->kelas);
            if ($selectedTingkat == 'XII') return str_starts_with($kelasStr, 'XII');
            if ($selectedTingkat == 'XI') return str_starts_with($kelasStr, 'XI');
            if ($selectedTingkat == 'X') return str_starts_with($kelasStr, 'X');
            return false;
        });
        
        $mapels = $user->getMapelDiampu();

        return view('guru.rencana_pembelajaran.index', compact(
            'tab',
            'cpList',
            'tpList',
            'atpList',
            'rencanaList',
            'jadwals',
            'jadwalsManage',
            'mapelInfo',
            'mapels',
            'activeKalender',
            'selectedMapelId',
            'selectedTingkat'
        ));
    }
    // =========================================================
    // 1. Capaian Pembelajaran (CP)
    // =========================================================
    public function storeCp(Request $request)
    {
        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'fase'              => 'required|in:E,F',
            'tingkat'           => 'required|in:X,XI,XII',
            'elemen'            => 'required|string|max:150',
            'deskripsi'         => 'required|string',
            'tahun_ajaran'      => 'required|string',
            'semester'          => 'required|in:ganjil,genap',
        ]);

        $user = Auth::user();

        CapaianPembelajaran::create([
            'guru_user_id'      => $user->id,
            'mata_pelajaran_id' => $request->mata_pelajaran_id,
            'fase'              => $request->fase,
            'tingkat'           => $request->tingkat,
            'elemen'            => $request->elemen,
            'deskripsi'         => $request->deskripsi,
            'tahun_ajaran'      => $request->tahun_ajaran,
            'semester'          => $request->semester,
        ]);

        return redirect()->route('guru.rencana-pembelajaran.index', ['tab' => 'cp', 'mapel_id' => $request->mata_pelajaran_id ?? ($cp->mata_pelajaran_id ?? ''), 'tingkat' => $request->tingkat ?? ($cp->tingkat ?? '')])
            ->with('success', 'Capaian Pembelajaran (CP) berhasil ditambahkan!');
    }

    public function destroyCp(CapaianPembelajaran $cp)
    {
        $user = Auth::user();
        if ($cp->guru_user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403);
        }

        $cp->delete();
        return redirect()->route('guru.rencana-pembelajaran.index', ['tab' => 'cp', 'mapel_id' => $request->mata_pelajaran_id ?? ($cp->mata_pelajaran_id ?? ''), 'tingkat' => $request->tingkat ?? ($cp->tingkat ?? '')])
            ->with('success', 'Capaian Pembelajaran berhasil dihapus.');
    }

    // =========================================================
    // 2. Tujuan Pembelajaran (TP)
    // =========================================================
    public function storeTp(Request $request)
    {
        $request->validate([
            'capaian_pembelajaran_id' => 'required|exists:capaian_pembelajarans,id',
            'kode_tp'                 => 'required|string|max:30',
            'deskripsi'               => 'required|string',
            'kktp'                    => 'nullable|string',
            'alokasi_jp'              => 'required|numeric|min:1',
            'lingkup_materi'          => 'nullable|string|max:255',
            'semester'                => 'required|in:ganjil,genap',
        ]);

        TujuanPembelajaran::create([
            'capaian_pembelajaran_id' => $request->capaian_pembelajaran_id,
            'kode_tp'                 => $request->kode_tp,
            'deskripsi'               => $request->deskripsi,
            'kktp'                    => $request->kktp,
            'alokasi_jp'              => $request->alokasi_jp,
            'lingkup_materi'          => $request->lingkup_materi,
            'semester'                => $request->semester,
            'urutan'                  => TujuanPembelajaran::where('capaian_pembelajaran_id', $request->capaian_pembelajaran_id)->count() + 1,
        ]);

        return redirect()->route('guru.rencana-pembelajaran.index', ['tab' => 'tp', 'mapel_id' => $request->mapel_id ?? ($tp->capaianPembelajaran->mata_pelajaran_id ?? ''), 'tingkat' => $request->tingkat ?? ($tp->capaianPembelajaran->tingkat ?? '')])
            ->with('success', 'Tujuan Pembelajaran (TP) berhasil ditambahkan!');
    }

    public function destroyTp(TujuanPembelajaran $tp)
    {
        $tp->delete();
        return redirect()->route('guru.rencana-pembelajaran.index', ['tab' => 'tp', 'mapel_id' => $request->mapel_id ?? ($tp->capaianPembelajaran->mata_pelajaran_id ?? ''), 'tingkat' => $request->tingkat ?? ($tp->capaianPembelajaran->tingkat ?? '')])
            ->with('success', 'Tujuan Pembelajaran berhasil dihapus.');
    }

    // =========================================================
    // 3. Alur Tujuan Pembelajaran (ATP)
    // =========================================================
    public function storeAtp(Request $request)
    {
        $request->validate([
            'mata_pelajaran_id'        => 'required|exists:mata_pelajarans,id',
            'capaian_pembelajaran_id'  => 'nullable|exists:capaian_pembelajarans,id',
            'tujuan_pembelajaran_id'   => 'nullable|exists:tujuan_pembelajarans,id',
            'fase'                     => 'required|in:E,F',
            'tingkat'                  => 'required|in:X,XI,XII',
            'semester'                 => 'required|in:ganjil,genap',
            'alur_ke'                  => 'required|integer|min:1',
            'kode_atp'                 => 'required|string|max:30',
            'materi_pokok'             => 'required|string|max:255',
            'alokasi_jp'               => 'required|integer|min:1',
            'profil_pelajar_pancasila' => 'nullable|string|max:255',
            'keterangan'               => 'nullable|string',
        ]);

        $user = Auth::user();

        AlurTujuanPembelajaran::create([
            'guru_user_id'             => $user->id,
            'mata_pelajaran_id'        => $request->mata_pelajaran_id,
            'capaian_pembelajaran_id'  => $request->capaian_pembelajaran_id,
            'tujuan_pembelajaran_id'   => $request->tujuan_pembelajaran_id,
            'fase'                     => $request->fase,
            'tingkat'                  => $request->tingkat,
            'semester'                 => $request->semester,
            'alur_ke'                  => $request->alur_ke,
            'kode_atp'                 => $request->kode_atp,
            'materi_pokok'             => $request->materi_pokok,
            'alokasi_jp'               => $request->alokasi_jp,
            'profil_pelajar_pancasila' => $request->profil_pelajar_pancasila,
            'keterangan'               => $request->keterangan,
        ]);

        return redirect()->route('guru.rencana-pembelajaran.index', ['tab' => 'atp', 'mapel_id' => $request->mata_pelajaran_id ?? ($cp->mata_pelajaran_id ?? ($atp->mata_pelajaran_id ?? '')), 'tingkat' => $request->tingkat ?? ($cp->tingkat ?? ($atp->tingkat ?? ''))])
            ->with('success', 'Alur Tujuan Pembelajaran (ATP) berhasil ditambahkan!');
    }

    public function destroyAtp(AlurTujuanPembelajaran $atp)
    {
        $user = Auth::user();
        if ($atp->guru_user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403);
        }

        $atp->delete();
        return redirect()->route('guru.rencana-pembelajaran.index', ['tab' => 'atp', 'mapel_id' => $request->mata_pelajaran_id ?? ($cp->mata_pelajaran_id ?? ($atp->mata_pelajaran_id ?? '')), 'tingkat' => $request->tingkat ?? ($cp->tingkat ?? ($atp->tingkat ?? ''))])
            ->with('success', 'Alur Tujuan Pembelajaran berhasil dihapus.');
    }

    public function generateAtpFromTp(Request $request)
    {
        $request->validate([
            'capaian_pembelajaran_id' => 'required|exists:capaian_pembelajarans,id',
        ]);

        $user = Auth::user();
        $cp = CapaianPembelajaran::with('mataPelajaran', 'tujuanPembelajaran')->findOrFail($request->capaian_pembelajaran_id);

        if ($cp->guru_user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403);
        }

        $tps = $cp->tujuanPembelajaran()->orderBy('urutan')->get();
        if ($tps->isEmpty()) {
            return redirect()->route('guru.rencana-pembelajaran.index', ['tab' => 'atp', 'mapel_id' => $request->mata_pelajaran_id ?? ($cp->mata_pelajaran_id ?? ($atp->mata_pelajaran_id ?? '')), 'tingkat' => $request->tingkat ?? ($cp->tingkat ?? ($atp->tingkat ?? ''))])
                ->with('error', 'Capaian Pembelajaran ini belum memiliki Tujuan Pembelajaran (TP) untuk digenerate!');
        }

        $lastAlur = AlurTujuanPembelajaran::where('guru_user_id', $user->id)
            ->where('mata_pelajaran_id', $cp->mata_pelajaran_id)
            ->where('semester', $cp->semester)
            ->max('alur_ke') ?? 0;

        $count = 0;
        foreach ($tps as $tp) {
            $lastAlur++;
            AlurTujuanPembelajaran::create([
                'guru_user_id'             => $user->id,
                'mata_pelajaran_id'        => $cp->mata_pelajaran_id,
                'capaian_pembelajaran_id'  => $cp->id,
                'tujuan_pembelajaran_id'   => $tp->id,
                'fase'                     => $cp->fase,
                'tingkat'                  => $cp->tingkat,
                'semester'                 => $cp->semester,
                'alur_ke'                  => $lastAlur,
                'kode_atp'                 => 'ATP.' . ($tp->kode_tp ?: $lastAlur),
                'materi_pokok'             => $tp->lingkup_materi ?: substr($tp->deskripsi, 0, 100),
                'alokasi_jp'               => $tp->alokasi_jp ?: 4,
                'profil_pelajar_pancasila' => 'Bernalar Kritis, Mandiri',
                'keterangan'               => $tp->deskripsi,
            ]);
            $count++;
        }

        return redirect()->route('guru.rencana-pembelajaran.index', ['tab' => 'atp', 'mapel_id' => $request->mata_pelajaran_id ?? ($cp->mata_pelajaran_id ?? ($atp->mata_pelajaran_id ?? '')), 'tingkat' => $request->tingkat ?? ($cp->tingkat ?? ($atp->tingkat ?? ''))])
            ->with('success', "Berhasil memetakan {$count} Alur Tujuan Pembelajaran (ATP) dari TP terkait!");
    }

    // =========================================================
    // 4. Rencana Pembelajaran Harian (RPP / Modul Ajar)
    // =========================================================
    public function createRpp(Request $request)
    {
        $user = Auth::user();
        $selectedJadwalId = $request->query('jadwal_id');
        $mapelId = $request->query('mapel_id');
        $tingkat = $request->query('tingkat');

        $jadwalsQuery = JadwalPelajaran::with('mataPelajaran')
            ->where('guru_user_id', $user->id)
            ->orderBy('hari')
            ->orderBy('jam_mulai');
            
        if ($mapelId) {
             $jadwalsQuery->where('mata_pelajaran_id', $mapelId);
        }
        $jadwals = $jadwalsQuery->get();
        if ($tingkat) {
            $jadwals = $jadwals->filter(function($jadwal) use ($tingkat) {
                $kelasStr = strtoupper($jadwal->kelas);
                if ($tingkat == 'XII') return str_starts_with($kelasStr, 'XII');
                if ($tingkat == 'XI') return str_starts_with($kelasStr, 'XI');
                if ($tingkat == 'X') return str_starts_with($kelasStr, 'X');
                return true;
            });
        }

        // Jika filter di atas kosong, ambil seluruh jadwal guru tersebut
        if ($jadwals->isEmpty()) {
            $jadwals = JadwalPelajaran::with('mataPelajaran')
                ->where('guru_user_id', $user->id)
                ->orderBy('hari')
                ->orderBy('jam_mulai')
                ->get();
        }

        $semester = $request->query('semester', \App\Models\PengaturanSekolah::getActiveSemester());

        if (!$selectedJadwalId && $jadwals->isNotEmpty()) {
            $selectedJadwalId = $jadwals->first()->id;
        }

        // Hitung tanggal efektif untuk setiap jadwal menggunakan MingguEfektifService sesuai semester aktif/terpilih
        $mingguEfektifService = app(\App\Services\MingguEfektifService::class);
        $effectiveDatesMap = [];
        foreach ($jadwals as $j) {
            $effectiveDatesMap[$j->id] = $mingguEfektifService->getEffectiveDatesForJadwal($j, null, $semester);
        }

        $tps = TujuanPembelajaran::whereHas('capaianPembelajaran', function ($q) use ($user, $mapelId, $tingkat) {
                $q->where('guru_user_id', $user->id);
                if ($mapelId) $q->where('mata_pelajaran_id', $mapelId);
                if ($tingkat) $q->where('tingkat', $tingkat);
            })
            ->with('capaianPembelajaran.mataPelajaran')
            ->get();

        return view('guru.rencana_pembelajaran.create_rpp', compact(
            'jadwals',
            'tps',
            'selectedJadwalId',
            'mapelId',
            'tingkat',
            'effectiveDatesMap',
            'semester'
        ));
    }

    public function storeRpp(Request $request)
    {
        $request->validate([
            'jadwal_pelajaran_id'   => 'required|exists:jadwal_pelajarans,id',
            'tanggal_rencana'       => 'required|date',
            'pertemuan_ke'          => 'required|numeric|min:1',
            'materi_pokok'          => 'required|string|max:255',
            'tujuan_pembelajaran_id'=> 'nullable|exists:tujuan_pembelajarans,id',
            'aktivitas_pendahuluan' => 'nullable|string',
            'aktivitas_inti'        => 'nullable|string',
            'aktivitas_penutup'     => 'nullable|string',
            'media_sumber'          => 'nullable|string',
            'bentuk_asesmen'        => 'nullable|string|max:150',
            'catatan'               => 'nullable|string',
        ]);

        $user = Auth::user();

        $rencana = RencanaPembelajaran::updateOrCreate(
            [
                'jadwal_pelajaran_id' => $request->jadwal_pelajaran_id,
                'guru_user_id'        => $user->id,
                'tanggal_rencana'     => $request->tanggal_rencana,
            ],
            [
                'tujuan_pembelajaran_id' => $request->tujuan_pembelajaran_id,
                'pertemuan_ke'          => $request->pertemuan_ke,
                'materi_pokok'          => $request->materi_pokok,
                'aktivitas_pendahuluan' => $request->aktivitas_pendahuluan,
                'aktivitas_inti'        => $request->aktivitas_inti,
                'aktivitas_penutup'     => $request->aktivitas_penutup,
                'media_sumber'          => $request->media_sumber,
                'bentuk_asesmen'        => $request->bentuk_asesmen ?? 'Formatif (Observasi & Diskusi)',
                'catatan'               => $request->catatan,
            ]
        );

        return redirect()->route('guru.rencana-pembelajaran.index', ['tab' => 'rpp', 'mapel_id' => $request->mapel_id ?? ($rencana->jadwal->mata_pelajaran_id ?? ''), 'tingkat' => $request->tingkat ?? 'X'])
            ->with('success', 'Rencana Pembelajaran Harian (Modul Ajar) berhasil disimpan!');
    }

    public function showRpp(RencanaPembelajaran $rencana)
    {
        $rencana->load(['jadwal.mataPelajaran', 'tujuanPembelajaran.capaianPembelajaran', 'guru']);
        return view('guru.rencana_pembelajaran.show_rpp', compact('rencana'));
    }

    public function destroyRpp(RencanaPembelajaran $rencana)
    {
        $user = Auth::user();
        if ($rencana->guru_user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403);
        }

        $rencana->delete();
        return redirect()->route('guru.rencana-pembelajaran.index', ['tab' => 'rpp', 'mapel_id' => $request->mapel_id ?? ($rencana->jadwal->mata_pelajaran_id ?? ''), 'tingkat' => $request->tingkat ?? 'X'])
            ->with('success', 'Rencana Pembelajaran berhasil dihapus.');
    }
}
