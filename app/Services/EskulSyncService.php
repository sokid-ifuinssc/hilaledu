<?php

namespace App\Services;

use App\Models\Ekstrakurikuler;
use App\Models\Kelas;
use App\Models\JadwalPelajaran;
use App\Models\LaporanKbm;
use App\Models\LaporanKbmPresensi;
use App\Models\MataPelajaran;
use App\Models\NilaiMataPelajaran;
use App\Models\PengaturanSekolah;
use App\Models\PresensiHarianSiswa;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EskulSyncService
{
    /**
     * Cari Mapel "Team Work Project dan Project Pancasila" yang sesuai dengan tingkat kelas siswa.
     */
    public static function getTwpMapelForKelas(?Kelas $kelas): ?MataPelajaran
    {
        $tingkat = 'X';
        if ($kelas) {
            $nama = strtoupper($kelas->nama_kelas ?? $kelas->nama ?? '');
            if (str_starts_with($nama, 'XII') || $kelas->tingkat == 12 || $kelas->tingkat === '12') {
                $tingkat = 'XII';
            } elseif (str_starts_with($nama, 'XI') || $kelas->tingkat == 11 || $kelas->tingkat === '11') {
                $tingkat = 'XI';
            } else {
                $tingkat = 'X';
            }
        }

        // Cari berdasarkan kode spesifik dulu: TWP-X, TWP-XI, TWP-XII
        $mapel = MataPelajaran::where('kode', "TWP-{$tingkat}")->first();
        if ($mapel) {
            return $mapel;
        }

        // Fallback: cari nama mapel
        $mapel = MataPelajaran::where(function ($q) {
                $q->where('nama', 'like', '%Team Work Project%')
                  ->orWhere('nama', 'like', '%Project Pancasila%');
            })
            ->where('tingkat', $tingkat)
            ->first();

        if ($mapel) {
            return $mapel;
        }

        return MataPelajaran::where('nama', 'like', '%Team Work Project%')->first();
    }

    /**
     * Dapatkan kelas siswa baik dari User maupun tabel Siswa
     */
    public static function getKelasForSiswa(int $siswaUserId): ?Kelas
    {
        $user = User::find($siswaUserId);
        if ($user && $user->kelas_id) {
            $kelas = Kelas::find($user->kelas_id);
            if ($kelas) return $kelas;
        }

        $siswa = Siswa::where('user_id', $siswaUserId)->orWhere('id', $siswaUserId)->first();
        if ($siswa && $siswa->kelas_id) {
            $kelas = Kelas::find($siswa->kelas_id);
            if ($kelas) return $kelas;
        }

        return null;
    }

    /**
     * Sinkronkan satu presensi siswa eskul ke Laporan KBM & Presensi Harian Mapel TWP & P5.
     */
    public static function syncPresensiSiswa(Ekstrakurikuler $eskul, string $tanggal, int $siswaUserId, string $status, ?string $keterangan = null): void
    {
        try {
            $kelas = self::getKelasForSiswa($siswaUserId);
            if (!$kelas) {
                return;
            }

            $mapel = self::getTwpMapelForKelas($kelas);
            if (!$mapel) {
                return;
            }

            $ta = PengaturanSekolah::getActiveTahunAjaran();
            $sem = PengaturanSekolah::getActiveSemester();

            // 1. Cari atau buat Jadwal Pelajaran untuk kelas & mapel ini
            $jadwal = JadwalPelajaran::where('kelas', $kelas->nama_kelas)
                ->where('mata_pelajaran_id', $mapel->id)
                ->when($ta, fn($q) => $q->where('tahun_ajaran', $ta))
                ->when($sem, fn($q) => $q->where('semester', $sem))
                ->first();

            if (!$jadwal) {
                $jadwal = JadwalPelajaran::firstOrCreate(
                    [
                        'kelas'             => $kelas->nama_kelas,
                        'mata_pelajaran_id' => $mapel->id,
                        'tahun_ajaran'      => $ta,
                        'semester'          => $sem,
                    ],
                    [
                        'hari'           => $eskul->hari ?: 'Jumat',
                        'jam_ke_mulai'   => 1,
                        'jam_ke_selesai' => 2,
                        'jam_mulai'      => $eskul->jam_mulai ?: '13:30:00',
                        'jam_selesai'    => $eskul->jam_selesai ?: '15:00:00',
                        'guru_user_id'   => $eskul->pembina_guru_id,
                    ]
                );
            }

            // 2. Cari atau buat Laporan KBM pada tanggal tersebut
            $laporanKbm = LaporanKbm::firstOrCreate(
                [
                    'jadwal_pelajaran_id' => $jadwal->id,
                    'tanggal_realisasi'   => $tanggal,
                ],
                [
                    'guru_user_id'             => $eskul->pembina_guru_id ?: auth()->id(),
                    'status_pelaksanaan'       => 'sesuai_jadwal',
                    'kesesuaian_rencana'       => 'sesuai',
                    'catatan_kegiatan'         => "Pertemuan Terintegrasi Ekstrakurikuler: {$eskul->nama}",
                    'jumlah_siswa_total'       => 0,
                    'jumlah_siswa_hadir'       => 0,
                    'jumlah_siswa_tidak_hadir' => 0,
                ]
            );

            // 3. Masukkan Presensi Laporan KBM
            $statusNormalized = match (strtolower(trim($status))) {
                'hadir' => 'hadir',
                'izin'  => 'izin',
                'sakit' => 'sakit',
                'alpa'  => 'alpa',
                default => 'hadir',
            };

            LaporanKbmPresensi::updateOrCreate(
                [
                    'laporan_kbm_id' => $laporanKbm->id,
                    'siswa_user_id'  => $siswaUserId,
                ],
                [
                    'status'     => $statusNormalized,
                    'keterangan' => "Absensi Eskul {$eskul->nama}" . ($keterangan ? ": {$keterangan}" : ''),
                ]
            );

            // Perbarui rekap jumlah di LaporanKbm
            $totalSiswa = $laporanKbm->presensiSiswa()->count();
            $hadirSiswa = $laporanKbm->presensiSiswa()->where('status', 'hadir')->count();
            $laporanKbm->update([
                'jumlah_siswa_total'       => $totalSiswa,
                'jumlah_siswa_hadir'       => $hadirSiswa,
                'jumlah_siswa_tidak_hadir' => max(0, $totalSiswa - $hadirSiswa),
            ]);

            // 4. Catat juga di Presensi Harian Siswa
            PresensiHarianSiswa::updateOrCreate(
                [
                    'siswa_id' => $siswaUserId,
                    'tanggal'  => $tanggal,
                ],
                [
                    'kelas_id'     => $kelas->id,
                    'status'       => $statusNormalized,
                    'keterangan'   => "Kegiatan Eskul {$eskul->nama}",
                    'diinput_oleh' => auth()->id() ?: $eskul->pembina_guru_id,
                ]
            );
        } catch (\Throwable $e) {
            Log::error("Gagal syncPresensiSiswa Eskul ID {$eskul->id} ke Mapel TWP: " . $e->getMessage());
        }
    }

    /**
     * Sinkronkan nilai eskul siswa ke NilaiMataPelajaran untuk mapel TWP & P5.
     */
    public static function syncNilaiSiswa(Ekstrakurikuler $eskul, int $siswaUserId, ?float $nilaiAngka, ?string $nilaiHuruf, ?string $catatan = null): void
    {
        try {
            $kelas = self::getKelasForSiswa($siswaUserId);
            if (!$kelas) {
                return;
            }

            $mapel = self::getTwpMapelForKelas($kelas);
            if (!$mapel) {
                return;
            }

            $ta = PengaturanSekolah::getActiveTahunAjaran();
            $sem = PengaturanSekolah::getActiveSemester();

            if (empty($nilaiHuruf) && $nilaiAngka !== null) {
                if ($nilaiAngka >= 86) $nilaiHuruf = 'A';
                elseif ($nilaiAngka >= 76) $nilaiHuruf = 'B';
                elseif ($nilaiAngka >= 65) $nilaiHuruf = 'C';
                else $nilaiHuruf = 'D';
            }

            $predikat = $nilaiHuruf ?: ($nilaiAngka ? NilaiMataPelajaran::tentukanPredikat($nilaiAngka) : 'B');
            $keteranganCatatan = "Diintegrasikan dari Ekstrakurikuler {$eskul->nama}" . ($catatan ? ". Catatan: {$catatan}" : '');

            // Update di tabel nilai_mata_pelajarans dengan User ID siswa
            NilaiMataPelajaran::updateOrCreate(
                [
                    'mata_pelajaran_id' => $mapel->id,
                    'kelas_id'          => $kelas->id,
                    'siswa_id'          => $siswaUserId,
                    'tahun_ajaran'      => $ta,
                    'semester'          => $sem,
                ],
                [
                    'guru_user_id'  => $eskul->pembina_guru_id ?: auth()->id(),
                    'nilai_tugas'   => $nilaiAngka,
                    'nilai_uts'     => $nilaiAngka,
                    'nilai_uas'     => $nilaiAngka,
                    'nilai_akhir'   => $nilaiAngka,
                    'predikat'      => $predikat,
                    'catatan'       => $keteranganCatatan,
                    'is_sync_eskul' => true,
                ]
            );

            // Jika ada Siswa model yang ID-nya berbeda dari User ID, sinkronkan juga ID tersebut
            $siswa = Siswa::where('user_id', $siswaUserId)->first();
            if ($siswa && $siswa->id !== $siswaUserId) {
                NilaiMataPelajaran::updateOrCreate(
                    [
                        'mata_pelajaran_id' => $mapel->id,
                        'kelas_id'          => $kelas->id,
                        'siswa_id'          => $siswa->id,
                        'tahun_ajaran'      => $ta,
                        'semester'          => $sem,
                    ],
                    [
                        'guru_user_id'  => $eskul->pembina_guru_id ?: auth()->id(),
                        'nilai_tugas'   => $nilaiAngka,
                        'nilai_uts'     => $nilaiAngka,
                        'nilai_uas'     => $nilaiAngka,
                        'nilai_akhir'   => $nilaiAngka,
                        'predikat'      => $predikat,
                        'catatan'       => $keteranganCatatan,
                        'is_sync_eskul' => true,
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::error("Gagal syncNilaiSiswa Eskul ID {$eskul->id} ke Mapel TWP: " . $e->getMessage());
        }
    }
}
