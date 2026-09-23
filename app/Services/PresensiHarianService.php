<?php

namespace App\Services;

use App\Models\User;
use App\Models\PresensiHarianGuru;
use App\Models\JadwalPelajaran;
use App\Models\AbsensiGuru;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PresensiHarianService
{
    /**
     * Catat Presensi Masuk Harian Guru (Mandiri atau Dibantu Piket)
     */
    public function recordPresensiMasuk(User $guru, ?Carbon $now = null, ?User $actor = null, array $extra = []): array
    {
        $now = $now ? $now->copy() : now();
        $todayDate = $now->format('Y-m-d');
        $nowTimeStr = $now->format('H:i:s');
        $hariName = JadwalPelajaran::getHariIndonesia($now);

        // Cek apakah sudah ada catatan hari ini
        $presensi = PresensiHarianGuru::firstOrNew([
            'guru_user_id' => $guru->id,
            'tanggal'      => $todayDate,
        ]);

        if ($presensi->exists && !empty($presensi->jam_masuk) && empty($extra['force_update'])) {
            return [
                'success'  => false,
                'message'  => "Guru {$guru->name} sudah melakukan presensi masuk pada pukul " . substr($presensi->jam_masuk, 0, 5) . " WIB.",
                'presensi' => $presensi,
            ];
        }

        // Ambil jadwal mengajar guru hari ini
        $jadwalsHariIni = JadwalPelajaran::with('mataPelajaran')
            ->where('guru_user_id', $guru->id)
            ->where('hari', $hariName)
            ->orderBy('jam_ke_mulai')
            ->orderBy('jam_mulai')
            ->get();

        $firstJadwal = $jadwalsHariIni->first();
        $hasJam1 = $jadwalsHariIni->first(fn($j) => $j->jam_ke_mulai <= 1 || $j->jam_mulai <= '07:15:00');

        // Batas waktu masuk normal: 07.00 - 07.30
        $batasMasuk = Carbon::parse("{$todayDate} 07:30:00");
        $jamMulaiMasuk = Carbon::parse("{$todayDate} 07:00:00");

        $statusMasuk = 'hadir';
        $terlambatMenit = 0;
        $catatan = $extra['catatan'] ?? null;
        $statusJeda = 'tidak_ada';
        $keteranganJeda = null;

        $mapelName = ($firstJadwal && $firstJadwal->mataPelajaran) ? $firstJadwal->mataPelajaran->nama : 'Mata Pelajaran';

        // Evaluasi Berdasarkan Jadwal Hari Ini:
        // Kondisi C: Belum absen masuk pagi, dan hadir masuk saat jam ke-3 (atau mendekati jam mapel pertamanya)
        $jamMulaiMapel = null;
        if ($firstJadwal) {
            if ($firstJadwal->jam_mulai) {
                $jamMulaiMapel = Carbon::parse("{$todayDate} {$firstJadwal->jam_mulai}");
            } elseif ($firstJadwal->jam_ke_mulai >= 3) {
                $jamMulaiMapel = Carbon::parse("{$todayDate} 08:30:00");
            }
        }

        if ($firstJadwal && $firstJadwal->jam_ke_mulai >= 3 && $jamMulaiMapel && $now->greaterThanOrEqualTo($jamMulaiMapel->copy()->subMinutes(30))) {
            // Hadir masuk pada jam ke-3 / jam mapel pertama
            if ($now->greaterThan($jamMulaiMapel->copy()->addMinutes(15))) {
                $statusMasuk = 'terlambat';
                $terlambatMenit = (int) abs($now->diffInMinutes($jamMulaiMapel));
                $catatan = $catatan ?: "Hadir terlambat {$terlambatMenit} menit untuk jam ke-{$firstJadwal->jam_ke_mulai} ({$mapelName}).";
            } else {
                $statusMasuk = 'hadir_sesuai_jam';
                $terlambatMenit = 0;
                $catatan = $catatan ?: "Hadir sesuai jam mapel (Jam ke-{$firstJadwal->jam_ke_mulai}: {$mapelName})";
            }
            $statusJeda = 'tidak_ada';
        } elseif ($now->greaterThan($batasMasuk)) {
            $statusMasuk = 'terlambat';
            $terlambatMenit = (int) abs($now->diffInMinutes($batasMasuk));
            $catatan = $catatan ?: "Hadir terlambat {$terlambatMenit} menit (Batas toleransi masuk 07.30 WIB).";
        } else {
            $statusMasuk = 'hadir';
            $terlambatMenit = 0;
            $catatan = $catatan ?: "Hadir tepat waktu.";

            // Kondisi B: Jadwal pertama jam ke-3 atau lebih, dan sudah hadir masuk di pagi hari (07.00 - 07.30)
            if ($firstJadwal && $firstJadwal->jam_ke_mulai >= 3) {
                $statusJeda = 'tugas_mandiri';
                $keteranganJeda = "Standby / Tugas Mandiri di sekolah sebelum jam ke-{$firstJadwal->jam_ke_mulai} ({$mapelName}).";
            }
        }

        $isByPiket = $actor && $actor->id !== $guru->id;

        // Simpan Presensi Harian
        $presensi->jam_masuk = $nowTimeStr;
        $presensi->status_masuk = $statusMasuk;
        $presensi->terlambat_masuk_menit = $terlambatMenit;
        $presensi->status_pelaksanaan_jeda = $statusJeda;
        $presensi->keterangan_pelaksanaan_jeda = $keteranganJeda;
        if ($catatan) $presensi->catatan = $catatan;
        if (!empty($extra['lampiran_bukti'])) $presensi->lampiran_bukti = $extra['lampiran_bukti'];
        if ($isByPiket) {
            $presensi->diabsenkan_oleh_user_id = $actor->id;
            $presensi->metode_masuk = 'piket';
        } else {
            $presensi->metode_masuk = 'mandiri';
        }
        $presensi->save();

        // SINKRONISASI KBM OTOMATIS:
        // Kondisi A: Jika ada jam mengajar bertepatan dengan jam ke-1 atau 07.00
        if ($hasJam1) {
            $this->syncKbmAttendance($hasJam1, $guru, $todayDate, $nowTimeStr, ($statusMasuk === 'terlambat' ? 'terlambat' : 'hadir'), $terlambatMenit, "Otomatis dihadirkan bersama presensi masuk pukul " . substr($nowTimeStr, 0, 5) . " WIB.");
        }

        // Kondisi C: Jika hadir langsung jam ke-3
        if ($statusMasuk === 'hadir_sesuai_jam' && $firstJadwal) {
            $this->syncKbmAttendance($firstJadwal, $guru, $todayDate, $nowTimeStr, 'hadir', 0, "Hadir tepat waktu sesuai jadwal mapel jam ke-{$firstJadwal->jam_ke_mulai}.");
        }

        return [
            'success'  => true,
            'message'  => "Presensi masuk berhasil dicatat: {$presensi->status_masuk_label} (" . substr($nowTimeStr, 0, 5) . " WIB).",
            'presensi' => $presensi,
        ];
    }

    /**
     * Catat Presensi Pulang Harian Guru
     */
    public function recordPresensiPulang(User $guru, ?Carbon $now = null, ?User $actor = null, array $extra = []): array
    {
        $now = $now ? $now->copy() : now();
        $todayDate = $now->format('Y-m-d');
        $nowTimeStr = $now->format('H:i:s');

        $presensi = PresensiHarianGuru::firstOrNew([
            'guru_user_id' => $guru->id,
            'tanggal'      => $todayDate,
        ]);

        // Aturan jam pulang:
        // - Mulai pukul 14.10 WIB: Tepat Waktu
        // - Sebelum jam 14.00 WIB: Pulang Cepat
        $jamPulangResmi = Carbon::parse("{$todayDate} 14:10:00");
        $jamBatasCepat  = Carbon::parse("{$todayDate} 14:00:00");

        $statusPulang = 'tepat_waktu';
        $pulangCepatMenit = 0;

        if ($now->lessThan($jamBatasCepat)) {
            $statusPulang = 'pulang_cepat';
            $pulangCepatMenit = (int) abs($now->diffInMinutes($jamPulangResmi));
        } else {
            $statusPulang = 'tepat_waktu';
            $pulangCepatMenit = 0;
        }

        $isByPiket = $actor && $actor->id !== $guru->id;

        $presensi->jam_pulang = $nowTimeStr;
        $presensi->status_pulang = $statusPulang;
        $presensi->pulang_cepat_menit = $pulangCepatMenit;
        if (!empty($extra['catatan'])) {
            $presensi->catatan = ($presensi->catatan ? $presensi->catatan . ' | ' : '') . $extra['catatan'];
        }
        if ($isByPiket) {
            $presensi->diabsenkan_oleh_user_id = $actor->id;
            $presensi->metode_pulang = 'piket';
        } else {
            $presensi->metode_pulang = 'mandiri';
        }
        $presensi->save();

        $msg = ($statusPulang === 'pulang_cepat')
            ? "Presensi pulang dicatat: Pulang Cepat ({$pulangCepatMenit} menit sebelum jam 14.10 WIB)."
            : "Presensi pulang dicatat: Pulang Tepat Waktu (" . substr($nowTimeStr, 0, 5) . " WIB).";

        return [
            'success'  => true,
            'message'  => $msg,
            'presensi' => $presensi,
        ];
    }

    /**
     * Update Status Pelaksanaan Jam Jeda KBM (Tugas Mandiri / Standby vs Izin Keluar)
     */
    public function updatePelaksanaanJeda(PresensiHarianGuru $presensi, string $status, ?string $alasan = null, ?string $jamKembali = null): array
    {
        if (!in_array($status, ['tugas_mandiri', 'standby', 'izin_keluar'])) {
            $status = 'tugas_mandiri';
        }

        $presensi->status_pelaksanaan_jeda = $status;

        if ($status === 'izin_keluar') {
            $presensi->izin_keluar_alasan = $alasan ?: 'Izin keluar sementara urusan dinas/pribadi.';
            $presensi->izin_keluar_jam_mulai = now()->format('H:i:s');
            $presensi->izin_keluar_jam_kembali = $jamKembali;
            $presensi->izin_keluar_status = 'diajukan'; // Pemicu notifikasi guru piket
            $presensi->keterangan_pelaksanaan_jeda = "Izin Keluar: {$presensi->izin_keluar_alasan} (Perkiraan kembali: " . ($jamKembali ? substr($jamKembali, 0, 5) . " WIB" : "-") . ")";
        } else {
            $presensi->keterangan_pelaksanaan_jeda = ($status === 'standby') ? 'Standby di Sekolah / Ruang Guru.' : 'Tugas Mandiri / Persiapan Perangkat Ajar di Sekolah.';
            $presensi->izin_keluar_status = 'disetujui_piket';
        }

        $presensi->save();

        return [
            'success'  => true,
            'message'  => ($status === 'izin_keluar') 
                ? 'Izin keluar sementara berhasil diajukan dan terkirim ke notifikasi Petugas Piket.'
                : 'Laporan pelaksanaan jam jeda diperbarui menjadi ' . $presensi->status_jeda_label . '.',
            'presensi' => $presensi,
        ];
    }

    /**
     * Override / Pengabsenan Guru oleh Petugas Piket (Tendik / Guru Piket)
     * Dapat mengabsenkan jika lupa / kendala teknis atau merubah izin, sakit, dinas luar, dll.
     */
    public function piketOverrideGuru(User $guru, string $status, ?string $catatan = null, ?string $lampiran = null, ?User $piketActor = null, ?string $tanggal = null): array
    {
        $tanggal = $tanggal ?: date('Y-m-d');
        $nowTimeStr = now()->format('H:i:s');
        $hariName = JadwalPelajaran::getHariIndonesia(Carbon::parse($tanggal));

        $presensi = PresensiHarianGuru::firstOrNew([
            'guru_user_id' => $guru->id,
            'tanggal'      => $tanggal,
        ]);

        $validStatuses = ['hadir', 'terlambat', 'hadir_sesuai_jam', 'izin', 'sakit', 'tugas_luar', 'alpa'];
        if (!in_array($status, $validStatuses)) {
            $status = 'hadir';
        }

        // Set status presensi harian
        $presensi->status_masuk = $status;
        if (empty($presensi->jam_masuk)) {
            $presensi->jam_masuk = $nowTimeStr;
        }
        if ($status === 'hadir' || $status === 'hadir_sesuai_jam') {
            $presensi->terlambat_masuk_menit = 0;
        }

        $piketName = $piketActor ? $piketActor->name : 'Petugas Piket';
        $presensi->catatan = $catatan ?: "Diperbarui oleh {$piketName} ({$presensi->status_masuk_label}).";
        if ($lampiran) $presensi->lampiran_bukti = $lampiran;
        if ($piketActor) $presensi->diabsenkan_oleh_user_id = $piketActor->id;
        $presensi->metode_masuk = 'piket';
        $presensi->save();

        // SINKRONISASI SELURUH JADWAL KBM HARI ITU:
        // Jika statusnya izin, sakit, atau tugas luar, seluruh KBM hari itu otomatis tercatat izin/sakit/tugas_luar
        $jadwals = JadwalPelajaran::where('guru_user_id', $guru->id)
            ->where('hari', $hariName)
            ->get();

        foreach ($jadwals as $j) {
            $kbmStatus = match($status) {
                'izin'       => 'izin',
                'sakit'      => 'sakit',
                'tugas_luar' => 'tugas_luar',
                'terlambat'  => 'terlambat',
                default      => 'hadir',
            };

            $this->syncKbmAttendance($j, $guru, $tanggal, $nowTimeStr, $kbmStatus, $presensi->terlambat_masuk_menit, $presensi->catatan, $lampiran);
        }

        return [
            'success'  => true,
            'message'  => "Kehadiran guru {$guru->name} berhasil diperbarui sebagai '{$presensi->status_masuk_label}' oleh {$piketName}.",
            'presensi' => $presensi,
        ];
    }

    /**
     * Petugas piket merespons / mengetahui izin keluar
     */
    public function piketTanggapiIzinKeluar(PresensiHarianGuru $presensi, string $status, ?string $catatanPiket = null, ?User $piketActor = null): array
    {
        $presensi->izin_keluar_status = in_array($status, ['disetujui_piket', 'ditolak_piket']) ? $status : 'disetujui_piket';
        $presensi->catatan_piket_izin_keluar = $catatanPiket;
        $presensi->save();

        $label = ($presensi->izin_keluar_status === 'disetujui_piket') ? 'disetujui / diketahui' : 'ditolak';
        return [
            'success'  => true,
            'message'  => "Izin keluar untuk {$presensi->guru->name} telah {$label} oleh Petugas Piket.",
            'presensi' => $presensi,
        ];
    }

    /**
     * Helper sinkronisasi record KBM AbsensiGuru
     */
    protected function syncKbmAttendance(JadwalPelajaran $jadwal, User $guru, string $tanggal, string $jam, string $status, int $terlambat = 0, ?string $catatan = null, ?string $lampiran = null): AbsensiGuru
    {
        $absensi = AbsensiGuru::firstOrNew([
            'jadwal_pelajaran_id' => $jadwal->id,
            'tanggal'             => $tanggal,
            'guru_user_id'        => $guru->id,
        ]);

        $absensi->jam_absen = $jam;
        $absensi->status = $status;
        $absensi->terlambat_menit = $terlambat;
        $absensi->catatan = $catatan ?: "Presensi dicatat otomatis.";
        if ($lampiran) $absensi->lampiran_bukti = $lampiran;
        $absensi->save();

        return $absensi;
    }

    /**
     * Dapatkan daftar izin keluar yang membutuhkan konfirmasi / pemberitahuan petugas piket hari ini
     */
    public function getNotifikasiIzinKeluarHariIni(?string $tanggal = null): Collection
    {
        $tanggal = $tanggal ?: date('Y-m-d');

        return PresensiHarianGuru::with('guru')
            ->where('tanggal', $tanggal)
            ->where('status_pelaksanaan_jeda', 'izin_keluar')
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Dapatkan data rekap status seluruh dewan guru hari ini
     */
    public function getRekapKehadiranDewanGuruHariIni(?string $tanggal = null): array
    {
        $tanggal = $tanggal ?: date('Y-m-d');
        $hari = JadwalPelajaran::getHariIndonesia(Carbon::parse($tanggal));

        $gurus = User::where('role', 'guru')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $presensis = PresensiHarianGuru::where('tanggal', $tanggal)
            ->get()
            ->keyBy('guru_user_id');

        $jadwals = JadwalPelajaran::with('mataPelajaran')
            ->where('hari', $hari)
            ->get()
            ->groupBy('guru_user_id');

        $items = collect();

        foreach ($gurus as $g) {
            $p = $presensis->get($g->id);
            $jadwalGuru = $jadwals->get($g->id, collect());
            $firstJadwal = $jadwalGuru->sortBy('jam_ke_mulai')->first();

            $items->push([
                'guru'                 => $g,
                'presensi'             => $p,
                'jadwals'              => $jadwalGuru,
                'first_jadwal'         => $firstJadwal,
                'has_presensi_masuk'   => $p && !empty($p->jam_masuk),
                'has_presensi_pulang'  => $p && !empty($p->jam_pulang),
                'status_masuk'         => $p ? $p->status_masuk : 'alpa',
                'status_masuk_label'   => $p ? $p->status_masuk_label : 'Belum Absen Masuk',
                'badge_masuk_class'    => $p ? $p->badge_masuk_class : 'bg-slate-100 text-slate-500 border-slate-200',
                'status_pulang'        => $p ? $p->status_pulang : 'belum_pulang',
                'status_pulang_label'  => $p ? $p->status_pulang_label : 'Belum Pulang',
                'badge_pulang_class'   => $p ? $p->badge_pulang_class : 'bg-slate-100 text-slate-500 border-slate-200',
                'status_jeda'          => $p ? $p->status_pelaksanaan_jeda : 'tidak_ada',
                'izin_keluar_status'   => $p ? $p->izin_keluar_status : null,
            ]);
        }

        $rekap = [
            'total_guru'       => $gurus->count(),
            'hadir_masuk'      => $items->whereIn('status_masuk', ['hadir', 'hadir_sesuai_jam'])->count(),
            'terlambat'        => $items->where('status_masuk', 'terlambat')->count(),
            'izin'             => $items->where('status_masuk', 'izin')->count(),
            'sakit'            => $items->where('status_masuk', 'sakit')->count(),
            'tugas_luar'       => $items->where('status_masuk', 'tugas_luar')->count(),
            'belum_hadir'      => $items->where('has_presensi_masuk', false)->count(),
            'pulang_tepat'     => $items->where('status_pulang', 'tepat_waktu')->count(),
            'pulang_cepat'     => $items->where('status_pulang', 'pulang_cepat')->count(),
            'total_izin_keluar'=> $items->where('status_jeda', 'izin_keluar')->count(),
        ];

        return [
            'items' => $items,
            'rekap' => $rekap,
            'hari'  => $hari,
            'tanggal' => $tanggal,
        ];
    }
}
