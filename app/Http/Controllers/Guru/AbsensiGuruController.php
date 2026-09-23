<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AbsensiGuru;
use App\Models\JadwalPelajaran;
use Carbon\Carbon;

class AbsensiGuruController extends Controller
{
    /**
     * Menu Informasi & Riwayat Kehadiran Guru (Filter Bulanan & Harian)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filterType = $request->query('filter_type', 'bulanan'); // bulanan / harian
        $bulan = $request->query('bulan', date('Y-m'));
        $tanggal = $request->query('tanggal', date('Y-m-d'));

        $query = AbsensiGuru::with(['jadwal.mataPelajaran', 'guru'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_absen', 'desc');

        // Jika bukan superadmin/waka kurikulum/kepsek yang melihat rekap sekolah, batasi ke user
        if (!$user->isSuperAdmin() && !$user->isWakaKurikulum() && !$user->isKepalaSekolah()) {
            $query->where('guru_user_id', $user->id);
        } else {
            if ($request->filled('guru_id')) {
                $query->where('guru_user_id', $request->guru_id);
            }
        }

        if ($filterType === 'harian' && $tanggal) {
            $query->where('tanggal', $tanggal);
        } else {
            $query->where('tanggal', 'like', "{$bulan}%");
        }

        $riwayat = $query->paginate(20);

        // Statistik
        $statQuery = AbsensiGuru::where('guru_user_id', $user->id);
        if ($filterType === 'harian' && $tanggal) {
            $statQuery->where('tanggal', $tanggal);
        } else {
            $statQuery->where('tanggal', 'like', "{$bulan}%");
        }
        $allStats = $statQuery->get();

        $totalHadir = $allStats->where('status', 'hadir')->count();
        $totalTerlambat = $allStats->where('status', 'terlambat')->count();
        $totalIzin = $allStats->where('status', 'izin')->count();
        $totalSakit = $allStats->where('status', 'sakit')->count();
        $totalTugasLuar = $allStats->where('status', 'tugas_luar')->count();
        $totalSesi = $allStats->count();

        $persentase = $totalSesi > 0 ? round((($totalHadir + $totalTerlambat + $totalTugasLuar) / $totalSesi) * 100, 1) : 100;

        return view('guru.absensi.index', compact(
            'riwayat',
            'filterType',
            'bulan',
            'tanggal',
            'totalHadir',
            'totalTerlambat',
            'totalIzin',
            'totalSakit',
            'totalTugasLuar',
            'totalSesi',
            'persentase'
        ));
    }

    /**
     * Simpan presensi guru untuk jadwal tertentu dengan logika toleransi 10 menit
     */
    public function store(Request $request)
    {
        $request->validate([
            'jadwal_pelajaran_id' => 'required|exists:jadwal_pelajarans,id',
            'status'              => 'required|in:hadir,izin,sakit,tugas_luar',
            'catatan'             => 'nullable|string',
            'lampiran'            => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:3072',
        ]);

        $user = Auth::user();
        $jadwal = JadwalPelajaran::findOrFail($request->jadwal_pelajaran_id);
        $today = date('Y-m-d');
        $now = now();

        // Cek apakah sudah absen hari ini untuk jadwal ini
        $existing = AbsensiGuru::where('jadwal_pelajaran_id', $jadwal->id)
            ->where('tanggal', $today)
            ->where('guru_user_id', $user->id)
            ->first();

        if ($existing) {
            return back()->with('warning', "Anda sudah melakukan presensi untuk sesi ini pada pukul {$existing->jam_absen} (Status: {$existing->status_label}).");
        }

        // Terapkan logika toleransi 10 menit
        $eval = AbsensiGuru::determineStatus($jadwal, $request->status, $now);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('absensi_bukti', 'public');
        }

        $absen = AbsensiGuru::create([
            'jadwal_pelajaran_id' => $jadwal->id,
            'guru_user_id'        => $user->id,
            'tanggal'             => $today,
            'jam_absen'           => $now->format('H:i:s'),
            'status'              => $eval['status'],
            'terlambat_menit'     => $eval['terlambat_menit'],
            'catatan'             => $request->catatan ?: $eval['catatan'],
            'lampiran_bukti'      => $lampiranPath,
        ]);

        // Sinkronisasi otomatis ke Presensi Harian Guru:
        try {
            $harianService = app(\App\Services\PresensiHarianService::class);
            $presensiHarian = \App\Models\PresensiHarianGuru::where('guru_user_id', $user->id)->where('tanggal', $today)->first();

            // Kondisi A: Jika jadwal adalah jam ke-1, otomatis sinkron presensi masuk harian jika belum
            if (($jadwal->jam_ke_mulai <= 1 || $jadwal->jam_mulai <= '07:15:00') && (!$presensiHarian || empty($presensiHarian->jam_masuk))) {
                $harianService->recordPresensiMasuk($user, $now, $user, [
                    'catatan' => "Otomatis dihadirkan bersama absensi KBM jam ke-1 ({$jadwal->mataPelajaran->nama})."
                ]);
            }
            // Kondisi C: Jika jadwal jam ke-3+ dan guru belum absen masuk pagi, catat status hadir sesuai jam mapel
            elseif ($jadwal->jam_ke_mulai >= 3 && (!$presensiHarian || empty($presensiHarian->jam_masuk))) {
                $harianService->recordPresensiMasuk($user, $now, $user, [
                    'catatan' => "Hadir sesuai jam mapel (Jam ke-{$jadwal->jam_ke_mulai}: {$jadwal->mataPelajaran->nama})."
                ]);
            }
        } catch (\Throwable $e) {}

        $msg = "Presensi berhasil dicatat sebagai '{$absen->status_label}'!";
        if ($absen->status === 'terlambat') {
            return back()->with('warning', $msg . " ({$absen->catatan})");
        }

        return back()->with('success', $msg);
    }

    /**
     * Presensi Masuk Harian Guru (Batas 07.00 - 07.30)
     */
    public function presensiMasukHarian(Request $request, \App\Services\PresensiHarianService $service)
    {
        $user = Auth::user();
        $res = $service->recordPresensiMasuk($user, now(), $user, [
            'catatan' => $request->input('catatan'),
        ]);

        return back()->with($res['success'] ? 'success' : 'warning', $res['message']);
    }

    /**
     * Presensi Pulang Harian Guru (Mulai 14.10, jika < 14.00 pulang cepat)
     */
    public function presensiPulangHarian(Request $request, \App\Services\PresensiHarianService $service)
    {
        $user = Auth::user();
        $res = $service->recordPresensiPulang($user, now(), $user, [
            'catatan' => $request->input('catatan'),
        ]);

        return back()->with($res['success'] ? 'success' : 'warning', $res['message']);
    }

    /**
     * Update Status Pelaksanaan Jam Jeda KBM (Tugas Mandiri / Standby vs Izin Keluar)
     */
    public function updateJedaHarian(Request $request, \App\Services\PresensiHarianService $service)
    {
        $request->validate([
            'status'      => 'required|in:tugas_mandiri,standby,izin_keluar',
            'alasan'      => 'nullable|string|max:500',
            'jam_kembali' => 'nullable|string',
        ]);

        $user = Auth::user();
        $today = date('Y-m-d');
        $presensi = \App\Models\PresensiHarianGuru::where('guru_user_id', $user->id)->where('tanggal', $today)->first();

        if (!$presensi) {
            return back()->with('warning', 'Anda belum melakukan presensi masuk hari ini.');
        }

        $res = $service->updatePelaksanaanJeda($presensi, $request->status, $request->alasan, $request->jam_kembali);

        return back()->with('success', $res['message']);
    }

    /**
     * Cetak rekapitulasi kehadiran resmi
     */
    public function print(Request $request)
    {
        $user = Auth::user();
        $bulan = $request->query('bulan', date('Y-m'));

        $riwayat = AbsensiGuru::with(['jadwal.mataPelajaran', 'guru'])
            ->where('guru_user_id', $user->id)
            ->where('tanggal', 'like', "{$bulan}%")
            ->orderBy('tanggal')
            ->get();

        $totalHadir = $riwayat->where('status', 'hadir')->count();
        $totalTerlambat = $riwayat->where('status', 'terlambat')->count();
        $totalIzin = $riwayat->where('status', 'izin')->count();
        $totalSakit = $riwayat->where('status', 'sakit')->count();
        $totalTugasLuar = $riwayat->where('status', 'tugas_luar')->count();
        $totalSesi = $riwayat->count();
        $persentase = $totalSesi > 0 ? round((($totalHadir + $totalTerlambat + $totalTugasLuar) / $totalSesi) * 100, 1) : 100;

        return view('guru.absensi.print', compact(
            'riwayat',
            'bulan',
            'user',
            'totalHadir',
            'totalTerlambat',
            'totalIzin',
            'totalSakit',
            'totalTugasLuar',
            'totalSesi',
            'persentase'
        ));
    }
}
